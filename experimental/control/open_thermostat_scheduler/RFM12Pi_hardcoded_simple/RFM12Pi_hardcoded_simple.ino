#include <avr/wdt.h> 

#define RF_freq RF12_433MHZ
const int nodeID = 15;
const int networkGroup = 210;   

#define RF69_COMPAT 0 // set to 1 to use RFM69CW 
#include <JeeLib.h>

#define LED_PIN     9
#define COLLECT 0x20 // collect mode, i.e. pass incoming without sending acks

static char cmd;
static byte value, stack[66], top, sendLen;
static byte testbuf[66];

static void handleInput (char c) {
    if ('0' <= c && c <= '9') {
        value = 10 * value + c - '0';
    }
    else if (c == ',') {
        if (top < sizeof stack)
            stack[top++] = value;
        value = 0;
    } else if (c=='s') {
        cmd = c;
        sendLen = top;
        memcpy(testbuf, stack, top);
                
        value = top = 0;
        memset(stack, 0, sizeof stack);
    }
}

void setup() {
    Serial.begin(9600);
    rf12_initialize(nodeID, RF_freq, networkGroup);
    pinMode(LED_PIN, OUTPUT);
}

void loop() {
    if (Serial.available())
        handleInput(Serial.read());

    if (rf12_recvDone() && (rf12_crc == 0) ) {
        byte n = rf12_len;
        
        digitalWrite(LED_PIN, HIGH);      
        
        Serial.print((int) rf12_hdr & 0x1F);
        for (byte i = 0; i < n; ++i) {
            Serial.print(',');
            Serial.print((int) rf12_data[i]);
        }
        Serial.println();
        
        digitalWrite(LED_PIN, LOW);
    }

    if (cmd) {
        
        digitalWrite(LED_PIN, HIGH);

        byte header = cmd == 'a' ? RF12_HDR_ACK : 0;
        rf12_sendStart(header, testbuf, sendLen);
        cmd = 0;
        digitalWrite(LED_PIN, LOW);
    }
}
