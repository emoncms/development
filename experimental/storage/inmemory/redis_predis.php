<?php

  require 'Predis/Autoloader.php';
  Predis\Autoloader::register();

  $redis = new Predis\Client();

  $feeds = array();

  $time = time();
  $benchstart = microtime(true);
  for ($i=0; $i<100000; $i++) {

    //$feeds[0] = array($time,1800);
    $redis->set('f1',"$time,1800");
    //$redis->set('feed_1',json_encode(array('time'=>$time,'value'=>1800)));
  }
  echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";
