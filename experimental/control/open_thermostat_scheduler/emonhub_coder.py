"""

  This code is released under the GNU Affero General Public License.
  
  OpenEnergyMonitor project:
  http://openenergymonitor.org

"""

import struct
import logging

# Initialize logger
log = logging.getLogger("EmonHub")

# Initialize nodes data
nodelist = {}
defaultdatacode = 'i'


def check_datacode(datacode):

    # Data types & sizes (number of bytes)
    datacodes = {'b': '1', 'h': '2', 'i': '4', 'l': '4', 'q': '8', 'f': '4', 'd': '8',
                 'B': '1', 'H': '2', 'I': '4', 'L': '4', 'Q': '8', 'c': '1', '?': '1'}

    # if datacode is valid return the data size in bytes
    if datacode in datacodes:
        return int(datacodes[datacode])
    # if not valid return False
    else:
        return False


def decode(datacode, frame):
    # Ensure little-endian & standard sizes used
    e = '<'

    # set the base data type to bytes
    b = 'B'

    # get data size from data code
    s = int(check_datacode(datacode))

    try:
        result = struct.unpack(e + datacode[0], struct.pack(e + b*s, *frame))
        return result[0]
    except:
        log.info("Unable to decode as datacode incorrect for value")
        return False
        
def decode_frame(node,bytedata):

    if node in nodelist and 'codes' in nodelist[node]:
        datacodes = nodelist[node]['codes']
        datasizes = []
        for code in datacodes:
            datasizes.append(check_datacode(code))

        if len(bytedata) != sum(datasizes):
            print "RX data length: "+str(len(bytedata))+" is not valid for data codes "+str(datacodes)
            return False
        else:
            count = len(datacodes)
            datacode = False
    else:
        if node in nodelist and nodelist[node]['code']:
            datacode = nodelist[node]['code']
        else:
            datacode = defaultdatacode
        if not datacode:
            return received
        elif len(bytedata) % check_datacode(datacode) != 0:
            print "RX data length: "+str(len(bytedata))+" is not valid for data code "+str(datacode)
            return False
        else:
            count = len(bytedata) / check_datacode(datacode)
    
    datascale = []
    if node in nodelist and 'scale' in nodelist[node]:
        datascale = nodelist[node]['scale']
        
    # Decode the string of data one value at a time into "decoded"
    decoded = []
    bytepos = int(0)
    #v = 0
    for i in range(0, count, 1):
        dc = datacode
        scale = 1
        if not datacode:
            dc = datacodes[i]
            if i<len(datascale): 
                scale = float(datascale[i])
        size = int(check_datacode(dc))
        value = decode(dc, [int(v) for v in bytedata[bytepos:bytepos+size]])
        value = value * scale
        bytepos += size
        decoded.append(value)

    return decoded
