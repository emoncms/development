#!/usr/bin/python

"""

  This code is released under the GNU Affero General Public License.
  
  OpenEnergyMonitor project:
  http://openenergymonitor.org

"""

import serial, sys, string, time
from configobj import ConfigObj
import emonhub_coder as ehc
import redis
import json
import struct

settings = ConfigObj("emon-py.conf", file_error=True)
ehc.nodelist = settings['nodes']
ehc.defaultdatacode = settings['defaultdatacode']

ser = serial.Serial(settings['serial']['port'], settings['serial']['baud'])

r = redis.Redis(
    host=settings['redis']['host'], 
    port=settings['redis']['port'], 
    db=settings['redis']['db']
)

while 1:

    line = ser.readline()
    received = line.strip().split(' ')

    if ((received[0] == '>') or (received[0] == '->')):
        pass
    elif received[0] == '?':
        # self._log.warning("Misformed RX frame: " + str(received))
        pass
    else:
        if received[0]=='OK':
            received = received[1:]

        if settings['serial']['radio']=='rfm69':
            bytepart = received[:-1]
            # Extract rssi
            rssi = -1 * int(received[-1][1:-1])
        else:
	    bytepart = received
            rssi = 0

        try:
            # Only integers are expected
            bytepart = [int(val) for val in bytepart]
        except Exception:
            # self._log.warning("Misformed RX frame: " + str(bytepart))
            pass
        else:
            
            t = int(time.time())
            decoded = ehc.decode_frame(bytepart)
            
            nodeid = decoded[0]
            decoded = decoded[1:]
            
            try:
                nodes = json.loads(r.get("nodes"))
            except Exception:
                nodes = {}
                
            nodes[nodeid] = {}
            nodes[nodeid]['variables'] = []
            
            names = []
            if str(nodeid) in ehc.nodelist and 'names' in ehc.nodelist[str(nodeid)]:
                names = ehc.nodelist[str(nodeid)]['names']
                
            units = []
            if str(nodeid) in ehc.nodelist and 'units' in ehc.nodelist[str(nodeid)]:
                units = ehc.nodelist[str(nodeid)]['units']
                
            scale = []
            if str(nodeid) in ehc.nodelist and 'scale' in ehc.nodelist[str(nodeid)]:
                scale = ehc.nodelist[str(nodeid)]['scale']
            
            for i in range(len(decoded)):
                variable = {}
                
                scalevalue = 1
                if i<len(scale):
                    if (float(scale[i])!=1.0):
                         scalevalue = float(scale[i])
                
                decoded[i] = decoded[i] * scalevalue
                
                variable['value'] = decoded[i]
                
                if i<len(names):
                    variable['name'] = names[i]
                    
                if i<len(units):
                    variable['units'] = units[i]
                    
                nodes[nodeid]['variables'].append(variable)
            
            packetcsv = str(t)+","+str(nodeid)+","+','.join(map(str, decoded))
            
            r.rpush('buffer',packetcsv)
            print packetcsv
            
            nodes[nodeid]['variables'].append({"value":rssi, "units":"", "name":"RSSI"});
            
            # Node list
            r.set("nodes",json.dumps(nodes))
            
            
            
