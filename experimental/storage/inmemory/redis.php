<?php

  $redis = new Redis();
  $redis->connect('127.0.0.1');

  $time = time();
  $benchstart = microtime(true);
  for ($i=0; $i<10000; $i++) {

    $redis->set('f1',"$time,1800");
  }
  echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";


