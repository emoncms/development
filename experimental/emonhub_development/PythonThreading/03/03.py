#!/usr/bin/python

import os, sys
import threading
import time
import Queue
from configobj import ConfigObj

from listener import Listener
from dispatcher import Dispatcher

def EmonHub():
    
    # Load settings
    settings = ConfigObj("emonhub.conf", file_error=True)
    
    # Create queue for node packets
    queue = Queue.Queue(0)

    # Create and start serial listener
    a = Listener(queue,settings)
    a.start()
    
    # Create and start http dispatcher
    b = Dispatcher(queue,settings)
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
