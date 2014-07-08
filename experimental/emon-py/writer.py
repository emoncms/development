"""

  This code is released under the GNU Affero General Public License.
  
  OpenEnergyMonitor project:
  http://openenergymonitor.org

"""

import sys, time, string, json, redis, struct
from configobj import ConfigObj
from pyfina import pyfina

settings = ConfigObj("emon-py.conf", file_error=True)
nodelist = settings['nodes']

r = redis.Redis(
    host=settings['redis']['host'], 
    port=settings['redis']['port'], 
    db=settings['redis']['db']
)

pyfina = pyfina(settings['data']['dir'])

while 1:

    buffers = {}
    
    while r.llen('buffer'):
        csv = r.lpop('buffer').split(',')
        
        timestamp = int(csv[0])
        nodeid = int(csv[1])
        
        csv = csv[2:]
        
        if str(nodeid) in nodelist and 'interval' in nodelist[str(nodeid)]:
            intervals = nodelist[str(nodeid)]['interval']
        
        vid = 0
        for value in csv:
            vid += 1
            interval = int(intervals[vid-1])
            if interval>0:
                filename = str(nodeid)+"."+str(vid)
                pyfina.prepare(filename,timestamp,value,interval)
    
    bytes = pyfina.save()
    
    print "Bytes written: " + str(bytes)

    time.sleep(float(settings['data']['saveinterval']))
