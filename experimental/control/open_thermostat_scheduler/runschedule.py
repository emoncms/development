import mosquitto, time, json, redis, datetime

setpoint = 0
days = ['mon','tue','wed','thu','fri','sat','sun']

r = redis.Redis(host='localhost', port=6379, db=0)

heating = {}
heating['state'] = r.get("app/heating/state")
heating['manualsetpoint'] = r.get("app/heating/manualsetpoint")
heating['mode'] = r.get("app/heating/mode")
heating['schedule'] = json.loads(r.get("app/heating/schedule"))

def on_message(mosq, obj, msg):
    
    if msg.topic=="app/heating/state":
        heating['state'] = msg.payload
        
    if msg.topic=="app/heating/manualsetpoint":
        heating['manualsetpoint'] = msg.payload
        
    if msg.topic=="app/heating/mode":
        heating['mode'] = msg.payload
            
    if msg.topic=="app/heating/schedule":
        heating['schedule'] = json.loads(msg.payload)
            
    update()
    
mqttc = mosquitto.Mosquitto()
mqttc.on_message = on_message
mqttc.connect("127.0.0.1",1883, 60, True)
mqttc.subscribe("app/#", 0)

def update():
    global setpoint
    
    t = datetime.datetime.now().time()
    timenow = t.hour + (t.minute/60.0)
    today = days[datetime.datetime.today().weekday()]
    lastsetpoint = setpoint
    
    if heating['mode']=="manual":
        setpoint = float(heating['manualsetpoint'])
    
    for period in heating['schedule'][today]:
        if period['start']<=timenow and period['end']>timenow:
            if heating['mode']=="schedule":
                setpoint = float(period['setpoint'])
    
    if lastsetpoint!=setpoint and heating['mode']=="schedule":
        print "tx/heating "+str(heating['state'])+","+str(int(setpoint*100))
        mqttc.publish('tx/heating',str(heating['state'])+","+str(int(setpoint*100)))
    
t = 0
lt = 0

update()
while 1:
    mqttc.loop(0)
    
    if (t-lt)>10.0:
        lt = t
        update()
        
    time.sleep(0.1)
    t = t + 0.1
