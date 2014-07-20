#!/usr/bin/python

import os, sys
import threading
import time
import Queue
import serial
import urllib2
import json

class Listener(threading.Thread):
    def __init__(self,queue):
        threading.Thread.__init__(self)
        self._queue = queue
        self.stop = False
        
        self._s = serial.Serial("/dev/ttyUSB0",9600)
        
    def run(self):
        i = 0
        while not self.stop:
            f = self._s.readline()
            received = f.strip().split(' ')
            
            if received:
                # time
                t = int(time.time())
                
                # Get node ID
                node = received[0]
                
                values = []
                for i in range(1, len(received),1):
                    value = received[i]
                    values.append(value)
                
                self._queue.put({'time':t, 'nodeid':node, 'bytedata':values})
                
            time.sleep(0.1)

class Dispatcher(threading.Thread):
    def __init__(self,queue):
        threading.Thread.__init__(self)
        self._queue = queue
        self.stop = False
        
    def run(self):
    
        databuffer = []
        url = "http://localhost/emoncms"
        apikey = "70e69594279c6961f2dae34ff1d45f4a"
    
        while not self.stop:
            if not self._queue.empty():
            
                d = self._queue.get()

                # Convert to minified emoncms bulk format
                packet = [d['time'],d['nodeid']]
                packet += d['bytedata']
                
                databuffer.append(packet)
                datastr = json.dumps(databuffer,separators=(',', ':'))

                # time that the request was sent at
                sentat = int(time.time())
                
                print "apikey="+apikey+"&data="+datastr+"&sentat="+str(sentat)
                
                req = urllib2.Request(
                    url+'/node/multiple.json', 
                    "apikey="+apikey+"&data="+datastr+"&sentat="+str(sentat)
                )
                
                try:
                    response = urllib2.urlopen(req, timeout=60)
                except urllib2.HTTPError as e:
                    pass
                except urllib2.URLError as e:
                    pass
                except httplib.HTTPException:
                    pass
                except Exception:
                    pass
                else:
                    print response.read()
                    
                    if (response.read()!='true'):
                        databuffer = []
                        pass
                        
            time.sleep(0.1)
            

def EmonHub():
    
    queue = Queue.Queue(0)

    a = Listener(queue)
    a.start()
    
    b = Dispatcher(queue)
    b.start()

    while 1:
        try:
            time.sleep(0.1)
        except KeyboardInterrupt:
            print "Stopping threads"
            a.stop = True
            b.stop = True
            break
    
    
if __name__ == '__main__':
    EmonHub()
