<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function heatpump_controller()
{
    global $session,$route,$mysqli;

    $result = false;

    if ($route->format == 'html')
    {
        if ($route->action == "" && $session['write']) $result = view("Modules/heatpump/heatpump_view.php",array());
    }

    return array('content'=>$result, 'fullwidth'=>true);
}

