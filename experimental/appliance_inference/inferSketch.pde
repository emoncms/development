//Example Sketch for Appliance inference
//Author: Trystan Lea
//Licence GNU General Public Licence
//Project openenergymonitor.org

#include <VirtualWire.h> // library for RF RX/TX
#include <Ethernet.h>
#include "EmonEvent.h"

//Ethernet setup values
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
byte ip[] = { 192, 168, 1, 44 };
byte gateway[] = { 192, 168, 1, 254 };
byte server[] = { 85, 92, 86, 84 };
//Setup a client
Client client(server, 80);

double realPower = 0,
       powerFactor = 0.95;

//Create an event detector
// 4 - window width, 0.5 - change threshold
EmonEvent rpe(4,2.0);

unsigned long ltime;

double change;
double lavp,avp,pf,lpf,pfchange,lastSent;
int curState,lastState;

void setup()  
{
    //Start ethernet, usb serial and xbee serial.
  Ethernet.begin(mac, ip, gateway);
  Serial.begin(9600);

  vw_set_ptt_inverted(true); // Required for DR3100
  vw_setup(2000);	 // Bits per sec
  vw_set_rx_pin(4);
  vw_rx_start();       // Start the receiver PLL running  
  delay(1000);
}

void loop()
{
 
  //Insert energy monitoring code in place of this
  //example variation code.
  if ((millis()-ltime)>60000) 
  {
    realPower = realPower + 120;
    powerFactor = powerFactor;
    ltime = millis();
  }
    
  lastState = curState;
  //Find whether realPower is stable or changing
  curState = rpe.getState(realPower);

  //If state is stable
  if(curState == 0)
  {
    lavp = avp;
    avp = rpe.average;

    lpf = pf;
    pf = powerFactor; 
  }
    
  //If last state was changing and current state is stable
  if (lastState == 1 && curState == 0) 
  { 
    //Calculate realPower change
    change = avp-lavp;
    //Calculate power factor change
    pfchange = pf - lpf;
    if (abs(change)>5.0)
    {
      if (client.connect()) 
      {
        client.print("GET http://power.openenergymonitor.org/emon.php?L=");
        client.print(lavp);
        client.print("&C=");
        client.print(avp);
        client.print("&R=");
        client.print(change);
        client.print("&P=");
        client.print(pfchange);
        client.println();
        client.stop();
        Serial.println("sent data");
      } 
      else {Serial.println("no connection");}  

      lastSent = avp;
      delay(5000);
    }
  }
}
