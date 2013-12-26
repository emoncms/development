<?php

  // Read from serial with data coming from RFM12PI with RFM12_Demo sketch 
  // All Emoncms code is released under the GNU Affero General Public License.

  error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); 
  
  require('SAM/php_sam.php');
  
  $conn = new SAMConnection();
  $conn->connect(SAM_MQTT, array(SAM_HOST => '127.0.0.1', SAM_PORT => 1883));
  
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

  $f = fopen($filename, "r+", false, $c);
  stream_set_timeout($f, 0,1000);

  while(true)
  {
    $data = fgets($f);
    if ($data && $data!="\n") {
      echo "DATA RX:".$data;
      $msg_rawserial = new SAMMessage($data);
      $conn->send('topic://rawserial', $msg_rawserial);
    }
  }

  fclose($f);

