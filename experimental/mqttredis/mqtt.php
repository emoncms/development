<?php

  error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); 
  
  require('SAM/php_sam.php');
  
  $conn = new SAMConnection();
  $conn->connect(SAM_MQTT, array(SAM_HOST => '127.0.0.1', SAM_PORT => 1883));

  $i = 0;
  while (true)
  {
    if ((time()-$ltime)>1)
    {
      $ltime = time();
      
      print $i."\n";
      $i=0;
    }
  
    $msg_rawserial = new SAMMessage("hello");
    $conn->send('topic://myqueue', $msg_rawserial);
    $i++;
  }
