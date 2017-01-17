<?php

// Install Mosquitto Client on PHP 7.0 from source:
// https://github.com/mgdm/Mosquitto-PHP
// sudo sh -c 'echo "extension=mosquitto.so" > /etc/php/7.0/cli/conf.d/20-mosquitto.ini'
// sudo sh -c 'echo "extension=mosquitto.so" > /etc/php/7.0/apache2/conf.d/20-mosquitto.ini'

// MQTT Client installation
// https://github.com/emoncms/emoncms/blob/master/docs/RaspberryPi/MQTT.md

$mqtt_server = array (
    "user"=>"superuser",
    "password"=>"password",
    "host"=>"127.0.0.1",
    "port"=>1883
);

$mqtt_client = new Mosquitto\Client();    
$mqtt_client->setCredentials($mqtt_server['user'],$mqtt_server['password']);
$mqtt_client->connect($mqtt_server['host'], $mqtt_server['port'], 5);
$mqtt_client->publish("user/1/smartplug34/status",0);
    
