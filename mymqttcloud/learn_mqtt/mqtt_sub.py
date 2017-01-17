import time
import paho.mqtt.client as mqtt
import json

mqtt_user = "username"
mqtt_passwd = "password"
mqtt_host = "127.0.0.1"
mqtt_port = 1883
mqtt_topic = "test/#"

def on_message(client, userdata, msg):
    print msg.topic+": "+msg.payload
    print msg
    
def on_connect(client, userdata, flags, rc):
    mqttc.subscribe(mqtt_topic)

mqttc = mqtt.Client()
mqttc.on_message = on_message
mqttc.on_connect = on_connect

# Connect
try:
    mqttc.username_pw_set(mqtt_user, mqtt_passwd)
    mqttc.connect(mqtt_host, mqtt_port, 60)
    mqttc.loop_start()
except Exception:
    print "Could not connect to MQTT"
else:
    print "Connected to MQTT"

# Loop
while 1:
    time.sleep(1)

# Close
mqttc.loop_stop()
mqttc.disconnect()
