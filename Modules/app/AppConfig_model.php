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

class AppConfig
{
    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }
    
    public function set($userid,$json)
    {
        $userid = (int) $userid;
        
        $data = json_decode($json);
        if (!$data) return array('success'=>false);
        
        // Input sanitisation
        $outdata = array();
        foreach ($data as $appname=>$properties)
        {
            $appname = preg_replace("/[^A-Za-z0-9]/",'',$appname);
            
            if (gettype($properties)=="object") {
                $outdata[$appname] = array();
                foreach ($properties as $property=>$value)
                {
                    $property = preg_replace("/[^A-Za-z0-9]/",'',$property);
                    $value = preg_replace("/[^A-Za-z0-9,]/",'',$value);
                    
                    $outdata[$appname][$property] = $value;
                }
            }
        }

        // Re-encode for storage in db text field
        $json = json_encode($outdata);
        
        $result = $this->mysqli->query("SELECT `userid` FROM app_config WHERE `userid`='$userid'");
        if ($result->num_rows) {
            $this->mysqli->query("UPDATE app_config SET `data`='$json' WHERE `userid`='$userid'");
            return true;
            
        } else {
            $this->mysqli->query("INSERT INTO app_config (`userid`,`data`) VALUES ('$userid','$json')");
            return true;
        }
        
        return array('success'=>false);
    }
    
    public function get($userid)
    {
        $userid = (int) $userid;
        $result = $this->mysqli->query("SELECT `data` FROM app_config WHERE `userid`='$userid'");
        if ($row = $result->fetch_array()) {
            return json_decode($row['data']);
        } else {
            return false;
        }
        
    }

}
