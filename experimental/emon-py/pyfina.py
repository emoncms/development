"""

  This code is released under the GNU Affero General Public License.
  
  OpenEnergyMonitor project:
  http://openenergymonitor.org

"""

import struct, math, os

class pyfina(object):

    # Data directory of pyfina files
    datadir = ""
    
    # Cache meta data in memory
    metadata_cache = {}
    
    # Buffer timeseries data
    buffers = {}
    
    def __init__(self,datadir):
        self.buffers = {}
        self.datadir = datadir

    # Prepare inserts datapoint into in-memory data buffer
    # Data is then saved to disk using the save method 
    def prepare(self,filename,timestamp,value,interval):
        
        if not value:
            return False
        
        # 1) Load meta data
        
        meta = self.get_meta(filename)
        
        if not meta:
            meta = {
                'interval': interval,
                'start_time': math.floor(timestamp / interval) * interval
            }
            self.create_meta(filename,meta)

        # Load meta data
        meta['npoints'] = self.get_npoints(filename)
        
        # 2) Write datapoint to buffer and padding if needed
        
        pos = int(math.floor((timestamp - meta['start_time']) / meta['interval']))
        last_pos = meta['npoints'] - 1;
        
        # Implementation does not currently allow for updating existing values
        # Ensure that new value is a new value
        if pos>last_pos:
            
            npadding = (pos - last_pos)-1
            
            if not filename in self.buffers:
                self.buffers[filename] = ""
            
            if npadding>0:
                for n in range(npadding):
                    self.buffers[filename] += struct.pack("f",float('nan'))
            
            self.buffers[filename] += struct.pack("f",float(value))


    # Save data in data buffers to disk
    # Writing data in larger blocks saves reduces disk write load as 
    # filesystems have a minimum IO size which are usually 512 bytes or more.
    def save(self):
        byteswritten = 0
        for name, data in self.buffers.iteritems():
            fh = open(self.datadir+name+".dat","ab")
            fh.write(data)
            fh.close()
            
            byteswritten += len(data)

        # Reset buffers
        self.buffers = {}
        
        return byteswritten
        
        
    def get_npoints(self,filename):

        bytesize = 0
        
        if os.path.isfile(self.datadir+filename+".dat"):
            bytesize += os.stat(self.datadir+filename+".dat").st_size
            
        if filename in self.buffers:
            bytesize += len(self.buffers[filename])
            
        return int(math.floor(bytesize / 4.0))
        
       
    def create_meta(self,filename,meta):
        # Create meta data file
        fh = open(self.datadir+filename+".meta","ab")
        fh.write(struct.pack("I",meta['start_time']))
        fh.write(struct.pack("I",meta['interval']))
        fh.close()
        
        # Save metadata to cache
        self.metadata_cache[filename] = meta
        
          
    def get_meta(self,filename):
    
        # Load metadata from cache if it exists
        if filename in self.metadata_cache:
            return self.metadata_cache[filename]
            
        elif os.path.isfile(self.datadir+filename+".meta"):
            # Open and read meta data file
            # The start_time and interval are saved as two consequative unsigned integers
            fh = open(self.datadir+filename+".meta","rb")
            tmp = struct.unpack("II",fh.read(8))
            fh.close()
            
            meta = {'start_time': tmp[0], 'interval': tmp[1]}
            
            # Save to metadata_cache so that we dont need to open the file next time
            self.metadata_cache[filename] = meta
            return meta
        else:
            return False
            
            
    def data(self,filename,start,end):

        start = float(start) / 1000.0
        end = float(end) / 1000.0
        outinterval = int((end - start) / 800)
        
        meta = self.get_meta(filename)
        bytesize = os.stat(self.datadir+filename+".dat").st_size
        meta['npoints'] = int(bytesize/4.0)

        # If start is 0 then set start to the start time of the feed
        if start==0 or start<meta['start_time']:
            start = meta['start_time']
            
        # If end is 0 set the end time to the end of the feed
        if end==0:
            end = meta['start_time'] + (meta['interval'] * meta['npoints'])
            
        startpos = math.ceil((start - meta['start_time']) / meta['interval'])
        skip_size = round(outinterval / meta['interval']);
        
        if skip_size<1:
            skip_size = 1
            
        data = []
        timestamp = 0
        i = 0
        
        fh = open(self.datadir+filename+".dat","rb")
        while timestamp<=end:
            # position steps forward by skipsize every loop
            pos = int(startpos + (i * skip_size))

            # Exit the loop if the position is beyond the end of the file
            if (pos > meta['npoints']-1):
                break;

            # read from the file
            fh.seek(pos*4)
            val = struct.unpack("f",fh.read(4))

            # calculate the datapoint time
            timestamp = int(meta['start_time'] + pos * meta['interval'])

            # add to the data array if its not a nan value
            if not math.isnan(val[0]):
                data.append([timestamp*1000,val[0]])

            i += 1
        
        return data
