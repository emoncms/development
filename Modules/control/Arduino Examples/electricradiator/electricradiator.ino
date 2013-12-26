#include <JeeLib.h>	     //https://github.com/jcw/jeelib

typedef struct
{
  byte blankchar;
  byte hour;
  byte minute;
  byte second;
  int target_temperature;
  int hysteresis;

} EmoncmsPayload;
EmoncmsPayload emoncms;

typedef struct
{
  int temperature;
  int battery;

} LPTPayload;
LPTPayload lab_front_right;

double setpoint,hysteresis,temp;

boolean state = 0;

void setup ()
{
  Serial.begin(9600);
  Serial.println("PacketGen Reciever Example");
  rf12_initialize(30,RF12_868MHZ,1); // NodeID, Frequency, Group
  
  pinMode(5, OUTPUT);
  digitalWrite(5,LOW);
}

void loop ()
{
  if (rf12_recvDone() && rf12_crc == 0 && (rf12_hdr & RF12_HDR_CTL) == 0)
  {
    int node_id = (rf12_hdr & 0x1F);
    
    if (node_id == 5)
    {
      lab_front_right = *(LPTPayload*) rf12_data;
      temp = lab_front_right.temperature * 0.01;
      Serial.println("RX LTN");
    }
    
    if (node_id == 15)
    {
      emoncms = *(EmoncmsPayload*) rf12_data;
      setpoint = emoncms.target_temperature * 0.01;
      hysteresis = emoncms.hysteresis * 0.01; 
      Serial.println("RX CONTROL");
    }
    
    if (node_id == 5 || node_id == 15)
    {
      Serial.print(temp);
      Serial.print(" ");
      Serial.print(setpoint);
      Serial.print(" ");
      Serial.print(hysteresis);
      Serial.print(" ");
      
      if (temp>(setpoint+(hysteresis*0.5))) state = 0;
      if (temp<(setpoint-(hysteresis*0.5))) state = 1;
      
      Serial.println(state);
      
      if (state) digitalWrite(5,HIGH); else digitalWrite(5,LOW);
    }
    
  }
}
