<?php

  error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); 
  
  require('SAM/php_sam.php');
  
  $conn = new SAMConnection();
  $conn->connect(SAM_MQTT, array(SAM_HOST => '127.0.0.1', SAM_PORT => 1883));
  $subUp = $conn->subscribe('topic://state') OR die('could not subscribe');
  
  $c = stream_context_create(array('dio' =>
    array('data_rate' => 9600,
          'data_bits' => 8,
          'stop_bits' => 1,
          'parity' => 0,
          'flow_control' => 0,
          'is_canonical' => 1)));

  // ttyAMA0 is the standard serial port used on the raspberrypi 
  // Depending on your machine you may need to change this to i.e: /dev/ttyUSB0
  
  if (PATH_SEPARATOR != ";") {
    $filename = "dio.serial:///dev/ttyUSB0";
  } else {
    $filename = "dio.serial://dev/ttyUSB0";
  }

  $serial = fopen($filename, "r+", false, $c);
  stream_set_timeout($f, 0,1000);

  while($conn && $serial)// && $serial
  {
    $msgUp = $conn->receive($subUp);
    if ($msgUp) {
      $val = $msgUp->body;
      if ($val) 
      {
        echo $val."\n";
        $val = (int) $val;
        
        // Convert to csv byte values
        $p2 = $val >> 8;
        $p1 = $val - ($p2<<8);
        $str = $p1.",".$p2.",";
        
        // Send to rfm12pi running RFM12Demo
        fprintf($serial,$str."s");
        
        usleep(100);
      }
    }
  }

  fclose($serial);

