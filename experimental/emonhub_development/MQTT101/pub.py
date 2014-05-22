#!/usr/bin/python

import mosquitto, time, json

mqttc = mosquitto.Mosquitto()
mqttc.connect("127.0.0.1",1883, 60, True)

bytevalues = [0]
state = 0

while 1:

    state += 1
    if state > 1:
        state = 0
        
    bytevalues[0] = state

    mqttc.publish('nodetx',json.dumps(bytevalues))
   
    time.sleep(2.0)
