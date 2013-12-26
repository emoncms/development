<?php
  // Read from serial with data coming from RFM12PI with RFM12_Demo sketch 
  // All Emoncms code is released under the GNU Affero General Public License.

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

  $f = fopen($filename, "r+", false, $c);
  stream_set_timeout($f, 0,1000);

  while(true)
  {
    $data = fgets($f);
    if ($data && $data!="\n")
    {
      echo "Raw data: $data";
      $parts = explode(' ',$data);
      $nodeid = (int) $parts[1];
      
      // Pack individual bytes into a binary string
      $bin = "";
      for($i=2; $i<count($parts); $i++) 
        $bin .= pack("C",intval($parts[$i]));
      
      // Unpack the binary string into signed shorts
      $values = unpack("s*",$bin);
      
      echo "  Decoded Node: $nodeid:[".implode($values,',')."]\n";
    }
  }

  fclose($f);

