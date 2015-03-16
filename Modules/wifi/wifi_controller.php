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

function wifi_controller()
{
    global $session,$route;
    
    require "wifi.php";
    $wifi = new Wifi();

    if ($route->action=="") $result = view("Modules/wifi/view.html", array());
    
    // wifi/scan
    if ($route->action=="scan") $result = $wifi->scan();
    
    // wifi/info
    if ($route->action=="info") $result = $wifi->info();    
    
    // wifi/start stop restart
    if ($route->action=="start") $result = $wifi->start();
    if ($route->action=="stop") $result = $wifi->stop();
    if ($route->action=="restart") $result = $wifi->restart();
    
    if ($route->action=="getconfig") $result = $wifi->getconfig();
    
    if ($route->action=="setconfig") {
        $result = $wifi->setconfig(json_decode($_POST['networks']));
    }
    
    return array('content'=>$result);
}
