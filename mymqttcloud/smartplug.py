import time
import paho.mqtt.client as mqtt

userid = 6
smartplugid = 34
descibe = '{"device":"smartplug"}'

mqtt_user = "username"
mqtt_passwd = "password"
mqtt_host = "localhost"
mqtt_port = 1883

basetopic = "user/"+str(userid)+"/smartplug"+str(smartplugid)

def dump(obj):
   for attr in dir(obj):
       if hasattr( obj, attr ):
           print( "obj.%s = %s" % (attr, getattr(obj, attr)))
           
def on_connect(client, userdata, flags, rc):
    # Initialisation string
    mqttc.subscribe(basetopic+"/#")

    mqttc.publish(basetopic+"/describe",descibe,2)

def on_message(client, userdata, msg):
    # print msg.topic+": "+msg.payload
    if msg.topic==basetopic+"/state":
        print "change state to: "+str(msg.payload)

mqttc = mqtt.Client()
mqttc.on_connect = on_connect
mqttc.on_message = on_message

# Connect
try:
    mqttc.username_pw_set(mqtt_user, mqtt_passwd)
    mqttc.connect(mqtt_host, mqtt_port, 60)
    mqttc.loop_start()
except Exception:
    print "Could not connect to MQTT"
else:
    print "Connected to MQTT"

time.sleep(1)

# Loop
while 1:
    mqttc.publish(basetopic+"/power",150,2)
    time.sleep(5)

# Close
mqttc.loop_stop()
mqttc.disconnect()
