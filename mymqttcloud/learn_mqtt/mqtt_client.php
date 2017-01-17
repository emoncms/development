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
    "port"=>1883,
    "basetopic"=>"user"
);

$mqtt_client = new Mosquitto\Client();

$connected = false;
$last_retry = 0;
    
$mqtt_client->onConnect('connect');
$mqtt_client->onDisconnect('disconnect');
$mqtt_client->onSubscribe('subscribe');
$mqtt_client->onMessage('message');
    
while(true){
    try { 
        $mqtt_client->loop(); 
    } catch (Exception $e) {
    
    }
    
    if (!$connected && (time()-$last_retry)>5.0) {
        $last_retry = time();
        try {
            $mqtt_client->setCredentials($mqtt_server['user'],$mqtt_server['password']);
            $mqtt_client->connect($mqtt_server['host'], $mqtt_server['port'], 5);
            $topic = $mqtt_server['basetopic']."/#";
            print "Subscribing to: ".$topic."\n";
            $mqtt_client->subscribe($topic,2);
        } catch (Exception $e) {
        
        }
        print "Not connected, retrying connection\n";
    }
}
    
function connect($r, $message) {
    global $connected;
    $connected = true;
    print "Connecting to MQTT server: {$message}: code: {$r}\n";
}

function subscribe() {

}

function unsubscribe() {

}

function disconnect() {
    global $connected;
    $connected = false;
    print "Disconnected cleanly";
}

function message($message) {
    print "message\n";
}
