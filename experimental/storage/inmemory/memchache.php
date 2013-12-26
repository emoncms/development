<?php

  $memcache = new Memcache;
  $memcache->connect('localhost', 11211);
  $time = time();

  $benchstart = microtime(true);
  for ($i=0; $i<100000; $i++) {
    $memcache->set('f1',array($time,1000));
  }
  echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";
