<?php

  if (file_exists("feed_1")) $bytes = filesize("feed_1"); else $bytes = 0;
  
  $fh = fopen("feed_1", 'a');
  
  $ltime = time();
  $start = $ltime;
  
  while(true)
  {

    $buf = "";
    for ($i=0; $i<512*10; $i++)
    {
      $buf .= pack("If",100,100);
    }

    fwrite($fh, $buf);

    $bytes += 4096*10;
    
    $now = time();
    if (($now-$ltime)>=1)
    {
      $ltime = $now;
      
      $elapsed = $now - $start;
      $speed = (int) (($bytes / $elapsed)/1024);
      echo "$bytes  $speed kb/s \n";
    }
  }
  fclose($fh);
