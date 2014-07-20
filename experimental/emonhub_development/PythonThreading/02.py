#!/usr/bin/python

import os, sys
import threading
import time
import Queue

# Publisher thread - similar to a listener
class Pub(threading.Thread):
    def __init__(self,queue):
        threading.Thread.__init__(self)
        self._queue = queue
        self.stop = False
        
    def run(self):
        i = 0
        while not self.stop:
            i+=1
            print "Listener: "+str(i)
            self._queue.put(i)
            time.sleep(1.0)

# Subscriber thread - similar to a dispatcher
class Sub(threading.Thread):
    def __init__(self,queue):
        threading.Thread.__init__(self)
        self._queue = queue
        self.stop = False
        
    def run(self):
        while not self.stop:
            if not self._queue.empty():
                item = self._queue.get()
                print "Dispatching: "+str(item)
            time.sleep(2.0)
            

def EmonHub():
    
    queue = Queue.Queue(0)

    a = Pub(queue)
    a.start()
    
    b = Sub(queue)
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
