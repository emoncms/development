<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function app_controller()
{
    global $session,$route,$mysqli;

    $result = false;
    
    include "Modules/app/myelectric_model.php";
    $myelectric = new MyElectric($mysqli);

    if ($route->format == 'html')
    {
        if ($route->action == "myheatpump" && $session['write']) $result = view("Modules/app/myheatpump.php",array());
        
        if ($route->action == "myelectric" && $session['write']) $result = view("Modules/app/myelectric.php",array());
        
        if ($route->action == "mysolarpv" && $session['write']) $result = view("Modules/app/mysolarpv.php",array());
        
        if ($route->action == "myenergy" && $session['write']) $result = view("Modules/app/myenergy.php",array());
    }
    
    if ($route->format == 'json')
    {
        if ($route->action == "set" && $session['write']) $result = $myelectric->set_mysql($session['userid'],get('data'));
        
        if ($route->action == "get" && $session['read']) $result = $myelectric->get_mysql($session['userid']);
    }

    return array('content'=>$result, 'fullwidth'=>true);
}

