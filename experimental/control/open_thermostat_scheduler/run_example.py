import mosquitto, time

data = {}
state = 0
setpoint = 1234

def on_message(mosq, obj, msg):
    topic_parts = msg.topic.split("/")
    
    nodename = topic_parts[1]
    varname = topic_parts[2]
    
    if not nodename in data:
        data[nodename] = {}
        
    data[nodename][varname] = msg.payload
   
def get(nodename,varname):
    if nodename in data:
        if varname in data[nodename]:
            return float(data[nodename][varname])
    return False
    
def tx(nodename,valarray):
    mqttc.publish('tx/'+nodename,','.join(str(x) for x in valarray))


mqttc = mosquitto.Mosquitto()
mqttc.on_message = on_message
mqttc.connect("127.0.0.1",1883, 60, True)
mqttc.subscribe("rx/#", 0)

while 1:

    laststate = state
    
    if get("room","temperature") > 16.5:
       state = 0;
       
    if get("room","temperature") < 15.8:
       state = 1;
    
    if state != laststate:
        tx("light",[state,setpoint])

    mqttc.loop(0)
    time.sleep(0.1)
