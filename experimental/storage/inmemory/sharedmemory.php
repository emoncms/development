<?php
  $time = time();
  $shmid = shmop_open(864, 'c', 0755, 1024);

  $benchstart = microtime(true);
  for ($i=0; $i<10000; $i++) {
    shmop_write($shmid, "$time,1800", 0);
  }
  echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";

  $size = shmop_size($shmid);
  echo shmop_read($shmid, 0, $size);

  shmop_close($shmid);
