#!/usr/bin/python
import mosquitto, time, json, serial

ser = serial.Serial("/dev/ttyUSB0",9600)

def on_connect(mosq, obj, rc):
    mosq.subscribe("nodetx")

def on_message(mosq, obj, msg):
    d = json.loads(msg.payload)
    txstr = ','.join(map(str, d))+",s"
    print "Sending data: "+txstr
    ser.write(txstr)
    
def on_readline(line):

    # Get an array out of the space separated string
    received = line.strip().split(' ')

    # If information message, discard
    if ((received[0] == '>') or (received[0] == '->')):
        print line
        pass
    # Else, process frame
    else:
        try:
            # Only integers are expected
            received = [int(val) for val in received]
        except Exception:
            # print "Misformed RX frame: " + str(received)
            pass
        else:
        
            # time
            t = int(time.time())
            
            # Get node ID
            node = received[0]
            
            # Recombine transmitted chars into signed int
            values = []
            for i in range(1, len(received),1):
                value = received[i]
                values.append(value)
            
            # Construct json with received data
            jsonstr = json.dumps({'time':t, 'nodeid':node, 'bytedata':values})
            
            print jsonstr
            
            mqttc.publish('noderx',jsonstr)

mqttc = mosquitto.Mosquitto()
mqttc.on_message = on_message
mqttc.on_connect = on_connect
 
mqttc.connect("127.0.0.1",1883, 60, True)

linebuff = ""

while 1:

    # A 'non blocking' readline 
    w = ser.inWaiting()
    if w:
        charbuf = ser.read(w)
        for char in charbuf:
            if char=='\n':
                on_readline(linebuff)
                linebuff = ""
            else:
                linebuff += char
    
    # A 'non blocking' call to mqtt loop
    mqttc.loop(0)
    
    # Main loop sleep control
    time.sleep(0.1)
