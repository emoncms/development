#include <JeeLib.h>	     //https://github.com/jcw/jeelib

typedef struct
{
  byte target;
  byte heating_on;
  int setpoint;
} ControlPayload;
ControlPayload ctrl;

typedef struct
{
  int temperature;
  int battery;

} LPTPayload;
LPTPayload tempnode;

byte heating_on = 0;
byte state = 0;
double setpoint = 18.0;
double hysteresis = 0.1;
double room_temperature = 18.0;

void setup ()
{
  Serial.begin(9600);
  Serial.println("Open Thermostat");
  rf12_initialize(30,RF12_433MHZ,210); // NodeID, Frequency, Group
  
  pinMode(3, OUTPUT);
  digitalWrite(3,LOW);
}

void loop ()
{
  if (rf12_recvDone() && rf12_crc == 0 && (rf12_hdr & RF12_HDR_CTL) == 0)
  {
    int node_id = (rf12_hdr & 0x1F);
    
    byte packetrx = 0;
    
    if (node_id == 18)
    {
      tempnode = *(LPTPayload*) rf12_data;
      room_temperature = tempnode.temperature * 0.01;
      Serial.print("RX LTN: ");
      Serial.println(room_temperature);
      packetrx = 1;
    }
    
    
    if (node_id == 15)
    {
      ctrl = *(ControlPayload*) rf12_data;
      if (ctrl.target==30) {
        
        heating_on = ctrl.heating_on;
        setpoint = ctrl.setpoint * 0.01;
        Serial.print("RX CONTROL: ");
        Serial.print(heating_on);
        Serial.print(" ");
        Serial.println(setpoint);
        
        //delay(50);
      
        //int i = 0; while (!rf12_canSend() && i<10) {rf12_recvDone(); i++;}
        //rf12_sendStart(0, &ctrl, sizeof ctrl);
        //rf12_sendWait(2);
        packetrx = 1;
      }
    }
    
    if (packetrx)
    {
      Serial.print(room_temperature);
      Serial.print(" ");
      Serial.print(setpoint);
      Serial.print(" ");
      Serial.print(hysteresis);
      Serial.print(" ");
      
      if (room_temperature>(setpoint+(hysteresis*0.5))) state = 0;
      if (room_temperature<(setpoint-(hysteresis*0.5))) state = 1;
      
      Serial.println(state);
      
      if (state && heating_on) digitalWrite(3,HIGH); else digitalWrite(3,LOW);
    }
    
  }
}
