#!/usr/bin/python

import mosquitto, time, json

def on_connect(mosq, obj, rc):
    mosq.subscribe("test")

def on_message(mosq, obj, msg):
    d = json.loads(msg.payload)
    print "State: "+str(d['state'])

mqttc = mosquitto.Mosquitto()
mqttc.on_message = on_message
mqttc.on_connect = on_connect
 
mqttc.connect("127.0.0.1",1883, 60, True)

while 1:
    mqttc.loop(0)
    time.sleep(0.1)
    print "s"

