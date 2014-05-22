#!/usr/bin/python

import mosquitto, time, json

def on_connect(mosq, obj, rc):
    mosq.subscribe("noderx")

def on_message(mosq, obj, msg):
    print msg.payload

mqttc = mosquitto.Mosquitto()
mqttc.on_message = on_message
mqttc.on_connect = on_connect
 
mqttc.connect("127.0.0.1",1883, 60, True)

mqttc.loop_forever()
