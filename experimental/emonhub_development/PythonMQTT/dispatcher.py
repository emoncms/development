#!/usr/bin/python

import urllib2
import mosquitto, json, time

url = "http://localhost/emoncms"
apikey = "70e69594279c6961f2dae34ff1d45f4a"

broker = "127.0.0.1"
port = 1883

databuffer = []

def on_connect(mosq, obj, rc):
    mosq.subscribe("test")

def on_message(mosq, obj, msg):
    global databuffer
    
    d = json.loads(msg.payload)
    
    # Convert to minified emoncms bulk format
    packet = [d['time'],d['nodeid']]
    packet += d['bytedata']
    
    databuffer.append(packet)
    datastr = json.dumps(databuffer,separators=(',', ':'))

    # time that the request was sent at
    sentat = int(time.time())
    
    print "apikey="+apikey+"&data="+datastr+"&sentat="+str(sentat)
    
    req = urllib2.Request(
        url+'/node/multiple.json', 
        "apikey="+apikey+"&data="+datastr+"&sentat="+str(sentat)
    )
    
    try:
        response = urllib2.urlopen(req, timeout=60)
    except urllib2.HTTPError as e:
        pass
    except urllib2.URLError as e:
        pass
    except httplib.HTTPException:
        pass
    except Exception:
        pass
    else:
        print response.read()
        
        if (response.read()!='true'):
            databuffer = []
            pass

mqttc = mosquitto.Mosquitto()
mqttc.on_message = on_message
mqttc.on_connect = on_connect
 
#connect to broker
mqttc.connect(broker, port, 60, True)

mqttc.loop_forever()
