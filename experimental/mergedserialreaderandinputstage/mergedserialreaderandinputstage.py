#!/usr/bin/python
# -*- coding: utf-8 -*-

# All Emoncms code is released under the GNU Affero General Public License.

import serial, sys, string
import time, datetime
import MySQLdb, MySQLdb.cursors

db = MySQLdb.connect(host="localhost",user="root",passwd="raspberry",db="emoncms")
cur = db.cursor(MySQLdb.cursors.DictCursor) 
cur.execute("SELECT id,nodeid,name,processList,record FROM input WHERE `userid` = '1'")
db.autocommit(True)

dbinputs = {}
for row in cur.fetchall():
  nodeid = int(row['nodeid'])
  nameid = int(row['name'])
  
  try:
    dbinputs[nodeid]
  except KeyError:
    dbinputs[nodeid] = {}

  dbinputs[nodeid][nameid] = {'id':int(row['id']), 'processList':row['processList'], 'record':row['record']}

# Set this to the serial port of your emontx and baud rate, 9600 is standard emontx baud rate
ser = serial.Serial('/dev/ttyAMA0', 9600)

while 1:

  # Read in line of readings from emontx serial
  linestr = ser.readline()

  # Remove the new line at the end
  linestr = linestr.rstrip()

  print "DATA RX:"+linestr

  # Split the line at the whitespaces
  values = linestr.split(' ')
  nodeid = int(values[1])
  nameid = 1
  unixtime = time.time()

  test = []

  for i in range(2,(len(values)-1),2):

    # Get 16-bit integer
    value = int(values[i]) + int(values[i+1])*256
    if value>32768: value -= -65536


    if dbinputs[nodeid][nameid]['processList']!=None:
      test.append({'value':value,'processList':dbinputs[nodeid][nameid]['processList']})
   
    nameid += 1

  for item in test:

    processList = item['processList']
    value = item['value']
    # 1. For each item in the process list
    pairs = processList.split(',')

    for pair in pairs:

      inputprocess = pair.split(':')
      processid = int(inputprocess[0]) # Process ID
      arg       = float(inputprocess[1]) # Process Arg

      if processid==2: value = value * arg; # scale

      if processid==1:
        feedid = int(arg)
        feedname = "feed_"+str(feedid)

        # a. Insert data value in feed table
        cur.execute("INSERT INTO "+feedname+" (time,data) VALUES(%s,%s)",(unixtime,value))

        # b. Update feeds table
        updatetime = time.strftime('%Y-%m-%d %H:%M:%S')
        cur.execute("""UPDATE feeds SET value = %s, time = %s WHERE id = %s""",(value,str(updatetime),feedid))
        print "UPDATE feeds SET value = '"+str(value)+"', time = '"+updatetime+"' WHERE id='"+str(feedid)+"'"

