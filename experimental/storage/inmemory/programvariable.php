<?php

  $feeds = array();

  $time = time();
  $benchstart = microtime(true);
  for ($i=0; $i<10000; $i++) {
    $feeds[0] = array($time,1800);
  }
  echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";
