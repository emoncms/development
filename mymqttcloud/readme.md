# MQTT Cloud Example

End to end demo of an MQTT Cloud control of ESP8266 Wifi Relay, including:

- Multi-user authentication and access list control using jpmens mosquitto auth plugin with MySQL database.
- Basic PHP server app and javascript client for user creation, login, device list and control.
- Simplified Wifi Relay firmware based on emonESP that runs on Martin Harizanov's [WIFI relay board](https://shop.openenergymonitor.com/wifi-mqtt-relay-thermostat).

![screenshot.png](screenshot.png)

Notes on setting up mosquitto & mosquitto-auth-plug + basic demos: [learn_mqtt/readme.md](learn_mqtt/readme.md)

### EmonESP WIFI Relay Firmware

See folder: EmonESP_WIFIRelay

### Demo Database setup

Create database:

    mysql -u username -p
    CREATE DATABASE mymqttcloud;

Create user table:

    CREATE TABLE users (`id` int(11) not null auto_increment primary key, `username` varchar(30), `salt` varchar(32), `hash` varchar(64), `pw` varchar(67), `super` int(11)) ENGINE=MYISAM;
    
Create access list table:
    
    CREATE TABLE acls (`id` int(11) not null auto_increment primary key, `username` varchar(30), `topic` text, `rw` int(11)) ENGINE=MYISAM;


