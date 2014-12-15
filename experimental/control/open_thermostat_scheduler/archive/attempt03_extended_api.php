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
    
        "node"=>array(
            "room"=>array(
                "temperature"=>"float",
                "battery"=>"int"
            )
        ),
        
        "app"=>array(
            "heating"=>array(
                "state"=>"bool",
                "mode"=>"text",
                "setpoint"=>"float"
                //"schedule"=>"json"
            )
        )
    );
    
    // 3) Parse query string
    $q="";
    if (isset($_GET['q'])) $q = $_GET['q'];
    $parts = explode("/",$q);
   
    switch ($method) {
        case "GET":
            $node = getfromkey($keys,$q);
            if (gettype($node)=="string") {
                $node = $redis->get($q);
                print $node;
            } else {
                $array = keyscan(array(),$node,"");
                foreach ($array as $key) {
                    $val = $redis->get($q."/".$key);
                    $node = setbykey($node,$key,$val);
                }
                print json_encode($node);
            }
            break;
        
        case "POST":
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
    
    function keyscan($array,$a,$keystr)
    {
        if (gettype($a)=="array") {
            foreach ($a as $k=>$v) {
                $array = keyscan($array,$a[$k],$keystr.$k."/");
            }
        }
        
        if (gettype($a)=="string") {
            $keystr = substr($keystr,0,-1);
            $array[] = $keystr;
        }
        return $array;
    }

    function setbykey($keys,$keystr,$val)
    {
        $ar = explode("/",$keystr);
        $t = array();
        $t[0] = $keys;
        for ($x=0; $x<count($ar)-1; $x++) $t[$x+1] = $t[$x][$ar[$x]];
        $t[$x][$ar[$x]] = $val;
        for ($x=count($ar)-2; $x>=0; $x--) $t[$x][$ar[$x]] = $t[$x+1];
        return $t[0];
    }
    
    function getfromkey($keys,$keystr)
    {
        $a = explode("/",$keystr);
        $t = array($keys);
        for ($i=0; $i<count($a); $i++) {
            if (isset($t[$i][$a[$i]])) {
                $t[$i+1] = $t[$i][$a[$i]];
            } else {
                return false;
            }
        }
        return $t[$i];
    }
