import threading
import time
import Queue
import serial

class Listener(threading.Thread):

    def __init__(self,queue,settings):
        threading.Thread.__init__(self)
        self._queue = queue
        self._settings = settings
        self.stop = False
        
        
        
    def run(self):
    
        self._s = serial.Serial(
            self._settings['Serial']['port'], 
            self._settings['Serial']['baud']
        )
    
        while not self.stop:
        
            # Read in line of readings from emontx serial
            f = self._s.readline()

            # Get an array out of the space separated string
            received = f.strip().split(' ')

            # If information message, discard
            if ((received[0] == '>') or (received[0] == '->')):
                pass
            # Else, process frame
            else:
                try:
                    # Only integers are expected
                    received = [int(val) for val in received]
                except Exception:
                    # print "Misformed RX frame: " + str(received)
                    pass
                else:
                
                    # time
                    t = int(time.time())
                    
                    # Get node ID
                    node = received[0]
                    
                    # Recombine transmitted chars into signed int
                    values = []
                    for i in range(1, len(received),1):
                        value = received[i]
                        values.append(value)
                
                    self._queue.put({'time':t, 'nodeid':node, 'bytedata':values})
                
            time.sleep(0.1)
