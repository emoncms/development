
# Read from serial with data coming from RFM12PI with RFM12_Demo sketch
# All Emoncms code is released under the GNU Affero General Public License.

import serial, sys, string
import time, datetime, httplib

# Domain you want to post to: localhost would be an emoncms installation on your own laptop
# this could be changed to emoncms.org to post to emoncms.org
domain = "localhost"

# Location of emoncms in your server, the standard setup is to place it in a folder called emoncms
# To post to emoncms.org change this to blank: ""
emoncmspath = "emoncms"

# Write apikey of emoncms account
apikey = "c1e88193f765298f64e945b23d3b83f0"

# Set this to the serial port of your emontx and baud rate, 9600 is standard emontx baud rate
ser = serial.Serial('COM3', 9600)

# ser.write("4b\n")
# ser.write("210g\n")

print "Python emoncms serial link"

while 1:

    # Read in line of readings from emontx serial
    linestr = ser.readline()

    # Remove the new line at the end
    linestr = linestr.rstrip()

    # print "DATA RX:"+linestr

    # Split the line at the whitespaces
    values = linestr.split(' ')

    if values[0]=="OK":
        nodeid = int(values[1])
        nameid = 1

        datacsv = []
        for i in range(2,(len(values)-1),2):
            # Get 16-bit integer
            value = int(values[i]) + int(values[i+1])*256
            if value>32768: value -= -65536
            datacsv.append(str(value))
            
        req = "node="+str(nodeid)+"&csv="+','.join(datacsv)
        print req

        # Send to emoncms
        conn = httplib.HTTPConnection(domain)
        conn.request("GET", "/"+emoncmspath+"/input/post.json?apikey="+apikey+"&"+req)
        #response = conn.getresponse()
        #print response.read()
