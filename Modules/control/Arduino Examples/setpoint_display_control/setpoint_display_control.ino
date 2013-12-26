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

typedef struct { int setpoint; } EmonGLCDPayload;         // neat way of packaging data for RF comms
EmonGLCDPayload emonglcd;

typedef struct { int setpoint; } EmoncmsPayload;         // neat way of packaging data for RF comms
EmoncmsPayload emoncms;

const int upswitchpin=16;           // digital pin of up switch - low when pressed
const int downswitchpin=19;         // digital pin of down switch - low when pressed


void setup()
{
  Serial.begin(9600);
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
    
    int S2 = digitalRead(upswitchpin);    //low when pressed
    int S3 = digitalRead(downswitchpin);  //low when pressed
    
    if (S2) setpoint += 0.1;
    if (S3) setpoint -= 0.1;
    
    if (S2 || S3)
    {
      // Serial.print("Tx: "); Serial.println(setpoint); delay(15);
      emonglcd.setpoint = (int) (setpoint * 100);
      rf12_sendNow(0, &emonglcd, sizeof emonglcd);
      rf12_sendWait(2);
      delay(10);
    }
  } 
  
  if ((millis()-slow_update)>10000)
  {
    slow_update = millis();
  }
}
