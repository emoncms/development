<?php

  // redis publisher via list (we could redis pub/sub)
  
  $redis = new Redis();
  $redis->connect("127.0.0.1");
  
  $i = 0;
  while (true)
  {
    if ((time()-$ltime)>1)
    {
      $ltime = time();
      
      print $i."\n";
      $i=0;
    }
  
    $redis->rpush('myqueue',"hello"); $i++;
  }
