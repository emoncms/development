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
  
  if (PATH_SEPARATOR != ";") {
    $filename = "dio.serial:///dev/ttyUSB0";
  } else {
    $filename = "dio.serial://dev/ttyUSB0";
  }

  $serial = fopen($filename, "r+", false, $c);
  stream_set_timeout($f, 0,1000);

  while($conn && $serial)// && $serial
  {
    $data = fgets($serial);
    if ($data && $data!="\n")
    { 
      if ($data[0]=='O' && $data[1]=='K') { 
        echo "DATA RX:".$data;
        
        $parts = explode(' ',$data);
        $nodeid = (int) $parts[1];
        
        // Pack individual bytes into a binary string
        $bin = "";
        for($i=2; $i<count($parts); $i++) 
          $bin .= pack("C",intval($parts[$i]));
        
        // Unpack the binary string into signed shorts
        $values = unpack("s*",$bin);
        
        echo "  Decoded Node: $nodeid:[".implode($values,',')."]\n";

        
        $msg_state = new SAMMessage(implode($values,','));
        $conn->send('topic://serialread', $msg_state);
      }
    }
  }

  fclose($serial);

