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
    
    $method = $_SERVER['REQUEST_METHOD'];

    $keys = array(
        "rx/room/temperature"=>array("type"=>"float"),
        "rx/room/battery"=>array("type"=>"int"),
        
        "tx/heating"=>array("type"=>"csv"),
        
        "app/heating/state"=>array("type"=>"bool"),
        "app/heating/mode"=>array("type"=>"text","options"=>array("manual","schedule")),
        "app/heating/manualsetpoint"=>array("type"=>"float"),
        "app/heating/schedule"=>array("type"=>"json")
    );
    
    // 3) Parse query string
    $q = $_GET['q'];
    $parts = explode("/",$q);
   
    switch ($method) {
        case "GET":
          
            if (isset($keys[$q])) {
                //print "GET ".$q." = ";
                print $redis->get($q);
            } else {
                print "ERROR key not recognised";
            }
            
            break;
        
        case "POST":
            if (isset($keys[$q])) 
            {
                $valid = true;
                $value = file_get_contents('php://input');
                if ($keys[$q]["type"]=="float") $value = (float) $value;
                if ($keys[$q]["type"]=="int") $value = (int) $value;
                if ($keys[$q]["type"]=="bool") $value = (int) (boolean) $value;
                if ($keys[$q]["type"]=="json") $value = json_encode(json_decode($value));
                if ($keys[$q]["type"]=="text" && !in_array($value,$keys[$q]["options"])) $valid = false;
                if ($keys[$q]["type"]=="csv") {
                    $values = explode(",",$value);
                    for ($i=0; $i<count($values); $i++) $values[$i] = (float) $values[$i];
                    $value = implode(",",$values);
                }
                
                if ($valid) {
                    //print "POST ".$q." <= ".$value;
                    save($q,$value);
                } else {
                    print "ERROR value not valid";
                }
            } else {
                print "ERROR key not recognised";
            }
            break;
    }
    
    print "\n\n";
    
    function save($topic,$payload) {
        global $redis, $mqtt;
        $redis->set($topic,$payload);
        if ($mqtt->connect()) {
            $mqtt->publish($topic,$payload,0);
            $mqtt->close();
        }
    }
