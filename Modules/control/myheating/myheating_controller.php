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

  function myheating_controller()
  {
    global $mysqli,$session, $route;

    $result = false;

    if ($route->format == 'html')
    {
      if ($route->action == "view" && $session['write']) $result = view("Modules/myheating/myheating_view.php",array());
      if ($route->action == "graph" && $session['write']) $result = view("Modules/myheating/graph_view.php",array());    
      if ($route->action == "button" && $session['write']) $result = view("Modules/myheating/button.php",array()); 
      if ($route->action == "setpoint" && $session['write']) $result = view("Modules/myheating/setpoint.php",array());    
    }

    return array('content'=>$result);
  }

?>
