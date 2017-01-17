import time
import paho.mqtt.client as mqtt
import json
import redis

mqtt_user = "superuser"
mqtt_passwd = "password"
mqtt_host = "127.0.0.1"
mqtt_port = 1883
mqtt_topic = "user/#"

r = redis.Redis(host='localhost', port=6379, db=0)

def on_message(client, userdata, msg):
    print msg.topic+": "+msg.payload
    parts = msg.topic.split("/")
    
    userid = parts[1]
    
    # Load user device list from redis
    devices = r.get("devices:"+str(userid))
    if devices==None:
        devices = {}
    else:
        devices = json.loads(devices)
    
    devicename = parts[2]
    key = parts[3]
    
    # Device auto-detection
    if key=="describe":
        tmp = json.loads(msg.payload)
        devices[devicename] = {"device":tmp["device"], "title":tmp["device"]} 
        r.set("devices:"+str(userid),json.dumps(devices))
    else:
        # Device properties and sensors
        pass
    
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
