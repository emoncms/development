<?php

  $redis = new Redis();
  $redis->connect("127.0.0.1");
  
  $i = 0;
  while(true)
  {
    if ((time()-$ltime)>1)
    {
      $ltime = time();
      
      print $i."\n";
      $i=0;
    }
    
    if ($redis->llen('myqueue')>0)
    {    
      // check if there is an item in the queue to process
      $line_str = $redis->lpop('myqueue'); $i++;
    }
  }
  

