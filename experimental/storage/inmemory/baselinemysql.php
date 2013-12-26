<?php

  $time = time();
  $mysqli = new mysqli("localhost","root","pass","db");

  $benchstart = microtime(true);
  for ($i=0; $i<10000; $i++) {
    $mysqli->query("UPDATE feeds SET value = '1800', time = '$time' WHERE id='31'");
  }
  echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";
