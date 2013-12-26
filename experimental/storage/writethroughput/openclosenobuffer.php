<?php

  if (file_exists("feed_1")) $bytes = filesize("feed_1"); else $bytes = 0;
  
  $ltime = time();
  $start = $ltime;
  
  while(true)
  {
    for ($i=0; $i<512; $i++)
    {
      $fh = fopen("feed_1", 'a');
      fwrite($fh, pack("If",100,100));
      fclose($fh);
    }

    $bytes += 4096;
    
    $now = time();
    if (($now-$ltime)>=1)
    {
      $ltime = $now;
      
      $elapsed = $now - $start;
      $speed = (int) (($bytes / $elapsed)/1024);
      echo "$bytes  $speed kb/s \n";
    }
  }
