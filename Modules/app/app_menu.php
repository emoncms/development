<?php

    global $session, $user;
    
    
    $apikey = "";
    if ($session['write']) $apikey = "?apikey=".$user->get_apikey_write($session['userid']);
    
    $menu_left[] = array(
        'name'=>"Apps", 
        'path'=>"app/mysolarpv" , 
        'session'=>"write", 
        'order' => 5,
        'dropdown'=>array(
            array("My Electric","app$apikey#myelectric"),
            array("My Solar","app$apikey#mysolarpv"),
            array("My Heatpump","app$apikey#myheatpump"),
            array("My Solar&Wind","app$apikey#myenergy")
        )
    );
