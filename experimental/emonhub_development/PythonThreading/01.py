#!/usr/bin/python

import os, sys
import threading
import time
import Queue

class Dispatcher(threading.Thread):

    def __init__(self,queue):
        threading.Thread.__init__(self)
        self._queue = queue
        self.stop = False
        
    def run(self):
        # Loop until we stop is false (our exit signal)
        while not self.stop:
            # Check if there is an item waiting in the queue and fetch it
            if not self._queue.empty():
                item = self._queue.get()
                # Dispatch!
                print "Dispatch: "+str(item)
            
            time.sleep(0.1)
            

def EmonHub():
    
    # We use a queue to pass data from the main thread to the dispatcher
    queue = Queue.Queue(0)
    
    # Create and start a dispatcher
    dispatcher = Dispatcher(queue)
    dispatcher.start()

    i=0
    while 1:
        try:
            # Main thread loop
            # Increment example variable and push to queue
            i+=1
            print "Listener: "+str(i)
            queue.put(i)
            
            time.sleep(1)
            
        except KeyboardInterrupt:
            # Important: we need to send a stop signal to the dispatcher thread in order to stop it
            print "Stopping threads"
            dispatcher.stop = True
            break
    
    
if __name__ == '__main__':
    EmonHub()
