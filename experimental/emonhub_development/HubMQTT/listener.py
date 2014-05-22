#!/usr/bin/python

import serial, sys, string, mosquitto, json, time
from configobj import ConfigObj

settings = ConfigObj("emonhub.conf", file_error=True)

ser = serial.Serial(settings['Serial']['port'], settings['Serial']['baud'])

mqttc = mosquitto.Mosquitto()
mqttc.connect(settings['Mosquitto']['broker'], settings['Mosquitto']['port'], 60, True)

while 1:

    # Read in line of readings from emontx serial
    f = ser.readline()

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
            
            # Construct json with received data
            jsonstr = json.dumps({'time':t, 'nodeid':node, 'bytedata':values})
            
            print jsonstr
            
            mqttc.publish('test',jsonstr)
