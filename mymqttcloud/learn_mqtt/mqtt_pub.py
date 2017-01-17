import time
import paho.mqtt.client as mqtt

mqtt_user = "username"
mqtt_passwd = "password"
mqtt_host = "127.0.0.1"
mqtt_port = 1883

mqttc = mqtt.Client()

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
    mqttc.publish("test/hello","Hello World",2)
    time.sleep(1)

# Close
mqttc.loop_stop()
mqttc.disconnect()
