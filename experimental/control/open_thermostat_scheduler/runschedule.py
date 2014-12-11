import mosquitto, time, json
import redis
import datetime

r = redis.Redis(host='localhost', port=6379, db=0)

data = {}
state = 0
setpoint = 1234
    
mqttc = mosquitto.Mosquitto()
mqttc.connect("127.0.0.1",1883, 60, True)

schedule = r.get("schedule")
schedule = json.loads(schedule)

setpoint = 0
state = 1

days = ['mon','tue','wed','thu','fri','sat','sun']

while 1:

    manual_state = r.get("state")
    manual_setpoint = r.get("setpoint")
    manual_heating = r.get("manual_heating")
    
    schedule = r.get("schedule")
    schedule = json.loads(schedule)
    
    t = datetime.datetime.now().time()
    timenow = t.hour + (t.minute/60.0)
    today = days[datetime.datetime.today().weekday()]
    
    current_key = 0
    for period in schedule[today]:
        if period['start']<=timenow and period['end']>timenow:
            if not manual_heating:
                setpoint = period['setpoint']
           
    print setpoint
    
    mqttc.publish('tx/heating',str(state)+","+str(setpoint*100))
    time.sleep(5.0);
