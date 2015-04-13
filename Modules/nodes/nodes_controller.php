<?php
    /*
     All Emoncms code is released under the GNU Affero General Public License.
     See COPYRIGHT.txt and LICENSE.txt.

        ---------------------------------------------------------------------
        Emoncms - open source energy visualisation
        Part of the OpenEnergyMonitor project:
        http://openenergymonitor.org
    */

    // no direct access
    defined('EMONCMS_EXEC') or die('Restricted access');


function nodes_controller()
{
    global $route,$redis,$mysqli,$feed_settings;
    $result = false;
    
    $emoncms_config_file = "/home/pi/data/emoncms.conf";
    
    include "Modules/feed/feed_model.php";
    $feed = new Feed($mysqli,$redis, $feed_settings);

    include "Modules/nodes/process.php";
    $process = new Process($mysqli,$feed);
    
    if ($route->action == 'view') {
        $route->format = "html";
        $result =  view("Modules/nodes/nodes.php", array());
        return array('content'=>$result, 'fullwidth'=>true);
    } elseif ($route->action == 'apidocs') {
        $route->format = "html";
        $result =  view("Modules/nodes/apidocs.html", array());
        return array('content'=>$result, 'fullwidth'=>false);
    }
    
    if ($route->method == 'GET' || $route->method == 'POST')
    {
        $route->format = "json";
        $url = explode("/",rtrim($_GET['q'],"/"));

        $input = false;
        $input = file_get_contents('php://input');
        if (isset($_GET['val'])) $input = $_GET['val'];
            
        $nodeid = false;
        $rxtx = false;
        $varid = false;
        $prop = false;

        if (isset($url[1]) && is_numeric($url[1])) $nodeid = $url[1];
        
        if (isset($url[2])) {
            if (is_numeric($url[2])) {
                $varid = $url[2];
            } elseif ($url[2]=="rx") {
                $rxtx = "rx";
                if (isset($url[3]) && is_numeric($url[3])) $varid = $url[3];
            } elseif ($url[2]=="tx") {
                $rxtx = "tx";
                if (isset($url[3]) && is_numeric($url[3])) $varid = $url[3];
            }
        }
        
        $propid = 1;
        if ($nodeid!==false) $propid++;
        if ($varid!==false) $propid++;
        if ($rxtx!==false) $propid++;
        if (isset($url[$propid])) $prop = $url[$propid];
        
        if (!$redis->exists("config")) {
            $config = json_decode(file_get_contents($emoncms_config_file));
            $redis->set("config",json_encode($config));
        } else {
            $config = json_decode($redis->get("config"));
        }
            
        if ($route->method == 'POST')
        {
            $config_changed = false;
            
            // if ($nodeid===false && $varid===false && $prop!==false) {
            //     if ($prop=="config") $redis->set("config",$input);
            // }

            if ($nodeid!==false && $varid===false && $prop!==false) 
            {
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
                    
                if ($prop=="values") {
                    $time = time();
                    $values = explode(",",$input);
                    
                    $nodes = json_decode($redis->get("nodes"));
                    if ($nodes==null) $nodes = new stdClass;
                    
                    if (!isset($nodes->$nodeid)) {
                        $nodes->$nodeid = new stdClass;
                        $nodes->$nodeid->rx = new stdClass;
                        $nodes->$nodeid->rx->time = array();
                        $nodes->$nodeid->rx->values = array();
                        $nodes->$nodeid->tx = new stdClass;
                        $nodes->$nodeid->tx->time = array();
                        $nodes->$nodeid->tx->values = array();
                    }
                    
                    if ($rxtx!==false) {
                        $nodes->$nodeid->$rxtx->time = $time;
                        $nodes->$nodeid->$rxtx->values = $values;
                        
                        $processlists = $config->$nodeid->$rxtx->processlists;
                        $process->nodes = $nodes;
                        for ($id=0; $id<count($processlists); $id++)
                        {
                            $process->input($time,$values[$id],$processlists[$id]);
                        }
                    }
                    $redis->set("nodes",json_encode($nodes));
                    $result = $nodes;
                    
                } else {
                    if ($prop=="nodename") $config->$nodeid->nodename = $input;
                    if ($prop=="hardware") $config->$nodeid->hardware = $input;
                    if ($prop=="firmware") $config->$nodeid->firmware = $input;
                    if ($prop=="names" && $rxtx!==false) $config->$nodeid->$rxtx->names = explode(",",$input);
                    if ($prop=="units" && $rxtx!==false) $config->$nodeid->$rxtx->units = explode(",",$input);
                    $result = $config;
                    $config_changed = true;
                }
            }
            
            if ($nodeid!==false && $varid!==false && $rxtx!==false && $prop!==false) 
            {   
                if ($prop=="name") $config->$nodeid->$rxtx->names[$varid] = $input;
                if ($prop=="unit") $config->$nodeid->$rxtx->units[$varid] = $input;
                if ($prop=="processlist") $config->$nodeid->$rxtx->processlists[$varid] = json_decode($input);
                $result = $config;
                $config_changed = true;
            }
            
            if ($config_changed) 
            {
                $redis->set("config",json_encode($config));
                $fh = fopen($emoncms_config_file,"w");
                fwrite($fh,json_encode($config,JSON_PRETTY_PRINT));
                fclose($fh);
            } 
        }

        if ($route->method == 'GET')
        {
            $nodes = json_decode($redis->get("nodes"));
            if (!$config) $config = new stdClass;
            if (!$nodes) $nodes = new stdClass;
            
            // GET ALL
            // returns full list of nodes with configuration
            if ($nodeid===false && $varid===false && $prop===false) 
            {
                foreach ($nodes as $nid=>$node) {
                    $config->$nid->rx->time = $nodes->$nid->rx->time;
                    $config->$nid->rx->values = $nodes->$nid->rx->values;
                    $config->$nid->tx->time = $nodes->$nid->tx->time;
                    $config->$nid->tx->values = $nodes->$nid->tx->values;
                }
                $result = $config;
            }
            
            
            if ($nodeid!==false && isset($config->$nodeid))
            {
                // GET NODE
                // returns json object with all node properties for requested node
                if ($varid===false && $prop===false && $rxtx===false) {
                    $node = $config->$nodeid;
                    $node->rx->time = $nodes->$nodeid->rx->time;
                    $node->rx->values = $nodes->$nodeid->rx->values;
                    $node->tx->time = $nodes->$nodeid->tx->time;
                    $node->tx->values = $nodes->$nodeid->tx->values;
                    $result = $node;
                }
                
                if ($varid===false && $prop===false && $rxtx!==false) {
                    $node = $config->$nodeid;
                    $node->$rxtx->time = $nodes->$nodeid->$rxtx->time;
                    $node->$rxtx->values = $nodes->$nodeid->$rxtx->values;
                    if ($rxtx=="rx") unset($node->tx); else unset($node->rx);
                    $result = $node;
                }
                
                // returns only requested property of requested node
                if ($varid===false && $prop!==false) {
                    if ($prop=="nodename") $result = $config->$nodeid->nodename;
                    if ($prop=="firmware") $result = $config->$nodeid->firmware;
                    if ($prop=="hardware") $result = $config->$nodeid->hardware;
                    
                    if ($rxtx!==false) {
                        if ($prop=="names") $result = $config->$nodeid->$rxtx->names;
                        if ($prop=="units") $result = $config->$nodeid->$rxtx->units;
                        if ($prop=="values") $result = $nodes->$nodeid->$rxtx->values;
                        if ($prop=="time") $result = $nodes->$nodeid->$rxtx->time;
                    }
                }
                
                // GET NODE:VAR
                if ($varid!==false && $prop===false && $rxtx!==false) {
                    $result = array("name"=>"","value"=>"","unit"=>"");
                    if (count($config->$nodeid->$rxtx->names)>$varid)
                        $result["name"] = $config->$nodeid->$rxtx->names[$varid];
                    if (count($nodes->$nodeid->$rxtx->values)>$varid)
                        $result["value"] = (float) $nodes->$nodeid->$rxtx->values[$varid];
                    if (count($config->$nodeid->$rxtx->units)>$varid)
                        $result["unit"] = $config->$nodeid->$rxtx->units[$varid];
                }
                
                if ($varid!==false && $prop!==false) {
                    $result = "";
                    if ($prop=="name" && count($config->$nodeid->$rxtx->names)>$varid) 
                        $result = $config->$nodeid->$rxtx->names[$varid];
                    if ($prop=="unit" && count($config->$nodeid->$rxtx->units)>$varid) 
                        $result = $config->$nodeid->$rxtx->units[$varid];
                    if ($prop=="value" && count($nodes->$nodeid->$rxtx->values)>$varid) 
                        $result = (float) $nodes->$nodeid->$rxtx->values[$varid];
                }
            }
        }
    }
    
    return array('content'=>$result, 'fullwidth'=>true);
}
