#!/usr/bin/python
# -*- coding: utf-8 -*-

# Read from serial with data coming from RFM12PI with RFM12_Demo sketch 
# All Emoncms code is released under the GNU Affero General Public License.

import serial, sys, string
import time, datetime

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

  for i in range(2,(len(values)-1),2):

    # Get 16-bit integer
    value = int(values[i]) + int(values[i+1])*256
    if value>32768: value -= -65536

    print str(nodeid)+" "+str(nameid)+" "+str(value)
   
    nameid += 1
