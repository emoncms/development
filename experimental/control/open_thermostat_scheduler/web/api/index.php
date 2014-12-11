<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
    header('Content-Type: application/json');

    
    // 1) Very basic apikey authentication
    $apikey_auth = false;
    $apikey = "pass";
    
    if ($apikey_auth && $_GET['apikey']!=$apikey) {
        print "incorrect apikey"; 
        die; 
    }

    // 2) Load MQTT and redis
    require("phpMQTT.php");
    $mqtt = new phpMQTT("127.0.0.1", 1883, "Control");
    
    $redis = new Redis();
    $redis->connect("127.0.0.1");
    
    // 3) Parse query string
    $q = $_GET['q'];
    $parts = explode("/",$q);
    
   
    switch ($parts[0]) {
    
        case "nodes":
            switch (count($parts)) {
                case 1:
                    // if length = 1: load all nodes
                    // http://localhost/api/nodes -> {"room":{"battery":3.12,"temperature":18.12}}
                    $nodes = array();
                    foreach ($redis->keys("node:*") as $key) {
                        $keyparts = explode(":",$key);
                        $nodename = $keyparts[1];
                        $varname = $keyparts[2];
                        if (!isset($nodes[$nodename])) $nodes[$nodename] = array();
                        $nodes[$nodename][$varname] = $redis->get($key)*1;
                    }
                    print json_encode($nodes);
                    break;
                case 2:
                    // if length = 2: load all variables for node
                    // http://localhost/api/nodes/room -> {"battery":3.12,"temperature":18.12}
                    $node = array();
                    foreach ($redis->keys("node:".$parts[1].":*") as $key) {
                        $varname = str_replace("node:".$parts[1].":","",$key);
                        $node[$varname] = $redis->get($key)*1;
                    }
                    print json_encode($node);
                    break;
                case 3:
                    // if length = 3: load variable of node
                    // http://localhost/api/nodes/room/temperature -> 18.12
                    print $redis->get("node:".$parts[1].":".$parts[2]);
                    break;
            }
            break;
            
        case "send":
            // http://localhost/api/send/light/1 -> ok
            if ($mqtt->connect()) {
                // print "tx/".$parts[1]." ".$parts[2];
                $mqtt->publish("tx/".$parts[1],$parts[2],0);
                $mqtt->close();
            }
            print "ok";
            break;
            
       case "schedule":
       
            if ($parts[1]=="get")
            {
                print $redis->get("schedule");
            }
            
            if ($parts[1]=="set" && isset($_POST['schedule']))
            {
                print $_POST['schedule'];
                $schedule = stripslashes(json_encode(json_decode($_POST['schedule'])));
                $redis->set("schedule",$schedule);
            }
    }
