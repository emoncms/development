#include <JeeLib.h>
#include <GLCD_ST7565.h>
#include <avr/pgmspace.h>
#include "utility/font_helvB24.h"

GLCD_ST7565 glcd;

#define MYNODE 20            // Should be unique on network, node ID 30 reserved for base station
#define freq RF12_433MHZ     // frequency - match to same frequency as RFM12B module (change to 868Mhz or 915Mhz if appropriate)
#define group 5 

unsigned long fast_update, slow_update;

double setpoint = 0;
double cval;

typedef struct { int setpoint; } EmoncmsPayload;         // neat way of packaging data for RF comms
EmoncmsPayload emoncms;

void setup()
{
  delay(500); 				   //wait for power to settle before firing up the RF
  rf12_initialize(MYNODE, freq,group);
  delay(100);				   //wait for RF to settle befor turning on display
  glcd.begin(0x20);
  glcd.backLight(200);
}

void loop()
{
  
  if (rf12_recvDone())
  {
    if (rf12_crc == 0 && (rf12_hdr & RF12_HDR_CTL) == 0)  // and no rf errors
    {
      int node_id = (rf12_hdr & 0x1F);

      if (node_id == 15)			//Assuming 15 is the emonBase node ID
      {
        emoncms = *(EmoncmsPayload*) rf12_data;
        setpoint = emoncms.setpoint * 0.01;
        fast_update = 0; // force display update
      } 
    }
  }

  // Display update every 200ms
  if ((millis()-fast_update)>200)
  {
    fast_update = millis();
    
    cval += (setpoint - cval) * 0.50;
    
    glcd.clear();
    glcd.fillRect(0,0,128,64,0);
    
    char str[50];
    glcd.setFont(font_helvB24);
    dtostrf(cval,0,1,str);
    strcat(str,"C");   
    glcd.drawString(3,15,str);
  
    glcd.refresh();
  } 
  
  if ((millis()-slow_update)>10000)
  {
    slow_update = millis();
  }
}
