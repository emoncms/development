<?php

  define('EMONCMS_EXEC', 1);

  require "route.php";
  $route = new Route(get('q'));
  
  $redis = new Redis();
  $redis->connect("127.0.0.1");
  
  if ($route->format == 'json')
  {
    if ($route->controller == 'input')
    {
      if ($route->action == 'post')
      {
      
        $array = array(
          "time"=>time(),
          "apikey"=>get('apikey'),
          "nodeid"=> (int) get('node'),
          "csv"=> get('csv')
        );
        
        $msg = json_encode($array);
        
        $buflength = $redis->llen('buffer');
        
        if ($buflength<1000)
        {
          $redis->rpush('buffer',$msg);
        }
      }
    }
  }

  function get($index)
  {
    $val = null;
    if (isset($_GET[$index])) $val = $_GET[$index];
    return $val;
  }
