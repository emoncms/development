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
            save("tx/".$parts[1],$parts[2]);
            print "ok";
            break;
            
       case "app":
            $method = $parts[count($parts)-1];
            $copy = $parts;
            unset($copy[count($parts)-1]);
            $key = implode("/",$copy);
       
            if ($method=="get") {
            
                if ($key=="app/heating") {
                    
                }
                
                if ($key=="app/heating/state") print $redis->get("app/heating/state");
                if ($key=="app/heating/manualsetpoint") print $redis->get("app/heating/manualsetpoint");
                if ($key=="app/heating/mode") print $redis->get("app/heating/mode");
                if ($key=="app/heating/schedule") print $redis->get("app/heating/schedule");
            }
            
            if ($method=="set") {
            
                if ($key=="app/heating") {
                    $heating = json_decode(prop('val'));
                    if (isset($heating->state)) save("app/heating/state",$heating->state);
                    if (isset($heating->manualsetpoint)) save("app/heating/manualsetpoint",$heating->manualsetpoint);
                    if (isset($heating->mode)) save("app/heating/mode",$heating->mode);
                    if (isset($heating->schedule)) save("app/heating/schedule",$heating->schedule);
                    print "ok";
                }
                
                if ($key=="app/heating/state") save("app/heating/state",prop('val'));
                if ($key=="app/heating/manualsetpoint") save("app/heating/manualsetpoint",prop('val'));
                if ($key=="app/heating/mode") save("app/heating/mode",prop('val'));
                if ($key=="app/heating/schedule") save("app/heating/schedule",prop('val'));
            }
    }
    
    function prop($index)
    {
        $val = null;
        if (isset($_GET[$index])) $val = $_GET[$index];
        if (isset($_POST[$index])) $val = $_POST[$index];
        if (get_magic_quotes_gpc()) $val = stripslashes($val);
        return $val;
    }
    
    function save($topic,$payload) {
        global $redis, $mqtt;
        $redis->set($topic,$payload);
        if ($mqtt->connect()) {
            $mqtt->publish($topic,$payload,0);
            $mqtt->close();
        }
    }
