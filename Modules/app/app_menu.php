<?php

    global $session, $user;
    
    $menu_left[] = array(
        'name'=>"Apps", 
        'path'=>"app/mysolarpv" , 
        'session'=>"write", 
        'order' => 5,
        'dropdown'=>array(
            array("My Electric","app#myelectric"),
            array("My Solar","app#mysolarpv"),
            array("My Heatpump","app#myheatpump"),
            array("My Solar&Wind","app#myenergy")
        )
    );
