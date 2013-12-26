import serial
import redis
import time

userid = 1

r = redis.Redis(host='localhost', port=6379, db=0)

s = serial.Serial("/dev/ttyUSB0",9600)

s.write('8b')
s.write('1g')

while 1:
  line = s.readline()
  line = line.strip('\n\r')
  parts = line.split(' ')
  
  if parts[0] == 'OK':
  
    utc = int(time.time())

    nodeid = int(parts[1])
    datacsv = ','.join(str(i) for i in parts[2:])
    
    r.hmset("node:%d" % nodeid,{'time':utc, 'data':datacsv})
    print str(nodeid)+"["+datacsv+"]"
    
