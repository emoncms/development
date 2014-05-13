import threading
import time
import Queue
import httplib, urllib2
import json

class Dispatcher(threading.Thread):

    def __init__(self,queue,settings):
        threading.Thread.__init__(self)
        self._queue = queue
        self._settings = settings
        self.stop = False
        
    def run(self):
    
        url = self._settings['Server']['url']
        apikey = self._settings['Server']['apikey']
    
        databuffer = []
    
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
