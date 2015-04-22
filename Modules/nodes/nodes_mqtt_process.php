<?php
    // This code is released under the GNU Affero General Public License.
    // OpenEnergyMonitor project:
    // http://openenergymonitor.org
    
    $emoncms_config_file = "/home/pi/data/emoncms.conf";
    $emonhub_config_file = "/home/pi/data/emonhub.conf";
        
    $topic = "emonhub/rx/#";
    
    define('EMONCMS_EXEC', 1);

    $fp = fopen("/home/pi/data/nodes_mqtt_process_lock", "w");
    if (! flock($fp, LOCK_EX | LOCK_NB)) { echo "Already running\n"; die; }
    
    chdir("/var/www/emoncms");
    
    require "Modules/log/EmonLogger.php";
    
    include "Modules/nodes/ConfObj.php";
    
    require "process_settings.php";
    $mysqli = @new mysqli($server,$username,$password,$database);
    $redis = new Redis();
    $redis->connect("127.0.0.1");
    $redis->del("config");
    $redis->del("nodes");
    

    
    include "Modules/feed/feed_model.php";
    $feed = new Feed($mysqli,$redis,$feed_settings);
    
    include "Modules/nodes/process.php";
    $process = new Process($mysqli,$feed);
     
    $config = load_config();
    
    // ----------------------------------------------------------------
    // MQTT Client
    // ----------------------------------------------------------------
    require("Lib/phpMQTT.php");
    $mqtt = new phpMQTT("127.0.0.1", 1883, "Emoncms input subscriber");
    if(!$mqtt->connect()) exit(1);
    
    $topics = array();
    $topics[$topic] = array("qos"=>0, "function"=>"procmsg");
    
    print "subscribing to $topic\n";
    $mqtt->subscribe($topics,0);

    $last = time();

    while(true){
    
        $mqtt->proc(0);
        usleep(100000);
        
        // Reload config every 5 seconds
        $now = time();
        if (($now - $last)>5.0) {
            print "reloading config\n";
            $last = $now;
            $config = load_config();
        }
    
    }
    $mqtt->close();
    // ----------------------------------------------------------------
    
    function procmsg($topic,$input)
    {  
        global $redis, $config, $emoncms_config_file, $emonhub_config_file, $process, $feed;
        
        $time = time();
        $t = explode("/",$topic);
        
        // Node id is topic level 2
        $nodeid = (int) $t[2];
        // Rxtx
        $rxtx = "rx";
        // Property is topic level 3
        $prop = $t[3];
        
        if ($prop=="values")
        {   
            $config_changed = false;
            
            if ($config==null) {
                $config = new stdClass;
                $config_changed = true;
            }
            
            if (!isset($config->$nodeid)) {
                $config->$nodeid = new stdClass;
                $config->$nodeid->nodename = "";
                $config->$nodeid->hardware = "";
                $config->$nodeid->firmware = "";
                $config->$nodeid->rx = new stdClass;
                $config->$nodeid->rx->names = array();
                $config->$nodeid->rx->units = array();
                $config->$nodeid->rx->processlists = array();
                $config->$nodeid->tx = new stdClass;
                $config->$nodeid->tx->names = array();
                $config->$nodeid->tx->units = array();
                $config->$nodeid->tx->processlists = array();
                $config_changed = true;
            }
                
            $time = time();
            $values = explode(",",$input);
            
            $nodes = json_decode($redis->get("nodes"));
            if ($nodes==null) $nodes = new stdClass;
            
            if (!isset($nodes->$nodeid)) {
                $nodes->$nodeid = new stdClass;
                $nodes->$nodeid->rx = new stdClass;
                $nodes->$nodeid->rx->time = 0;
                $nodes->$nodeid->rx->values = array();
                $nodes->$nodeid->tx = new stdClass;
                $nodes->$nodeid->tx->time = 0;
                $nodes->$nodeid->tx->values = array();
            }
            
            if ($rxtx!==false) {
                $nodes->$nodeid->$rxtx->time = $time;
                $nodes->$nodeid->$rxtx->values = $values;

                if (isset($config->$nodeid->$rxtx->processlists))
                {
                    $processlists = $config->$nodeid->$rxtx->processlists;
                    // $process->nodes = $config;
                    for ($id=0; $id<count($processlists); $id++)
                    {
                        $process->input($time,$values[$id],$processlists[$id]);
                    }
                }
            }
            $redis->set("nodes",json_encode($nodes));
            $result = $nodes;
            
            if ($config_changed) {
                print "saving config";
                $redis->set("config",json_encode($config));
                $fh = fopen($emoncms_config_file,"w");
                fwrite($fh,json_encode($config,JSON_PRETTY_PRINT));
                fclose($fh);
            }
        }  
    }

    function load_config()
    {
        global $redis, $emoncms_config_file, $emonhub_config_file;

        // 1) Load config from emoncms side        
        if (!$redis->exists("config")) {
            $config = json_decode(file_get_contents($emoncms_config_file));
            $redis->set("config",json_encode($config));
        } else {
            $config = json_decode($redis->get("config"));
        }
        
        $before = json_encode($config);
        
        // 2) Load config from emonhub
        $emonhubconf = confobj_parse(file_get_contents($emonhub_config_file));
        $emonhubnodes = $emonhubconf->nodes;
        
        // 3) Run through emoncms config to check if any of the values are different, copy over from emonhub nodes
        foreach ($emonhubnodes as $nid=>$node) {
            if (!isset($config->$nid)) {
                $config->$nid = new stdClass;
                $config->$nid->nodename = "";
                $config->$nid->hardware = "";
                $config->$nid->firmware = "";
                $config->$nid->rx = new stdClass;
                $config->$nid->rx->names = array();
                $config->$nid->rx->units = array();
                $config->$nid->rx->processlists = array();
                $config->$nid->tx = new stdClass;
                $config->$nid->tx->names = array();
                $config->$nid->tx->units = array();
                $config->$nid->tx->processlists = array();
            }
            
            if (isset($emonhubnodes->$nid->nodename)) $config->$nid->nodename = $emonhubnodes->$nid->nodename;
            if (isset($emonhubnodes->$nid->hardware)) $config->$nid->hardware = $emonhubnodes->$nid->hardware;
            if (isset($emonhubnodes->$nid->firmware)) $config->$nid->firmware = $emonhubnodes->$nid->firmware;
            
            if (!isset($config->$nid->rx)) $config->$nid->rx = new stdClass;
            if (isset($emonhubnodes->$nid->rx->names)) $config->$nid->rx->names = $emonhubnodes->$nid->rx->names;
            if (isset($emonhubnodes->$nid->rx->units)) $config->$nid->rx->units = $emonhubnodes->$nid->rx->units;
            
            if (!isset($config->$nid->tx)) $config->$nid->tx = new stdClass;
            if (isset($emonhubnodes->$nid->tx->names)) $config->$nid->tx->names = $emonhubnodes->$nid->tx->names;
            if (isset($emonhubnodes->$nid->tx->units)) $config->$nid->tx->units = $emonhubnodes->$nid->tx->units;
        }
        
        if (json_encode($config)!=$before) {
            $redis->set("config",json_encode($config));
            $fh = fopen($emoncms_config_file,"w");
            fwrite($fh,json_encode($config,JSON_PRETTY_PRINT));
            fclose($fh);
        }
        
        return $config;
    }
