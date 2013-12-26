<?php
  
  // Saved state of control packet
  $packet = array(
    'setpoint'=>array('type'=>'int', 'value'=>16.5)
  );
  
  $packet['setpoint']['value'] = (float) $_GET['val'];
  echo "State set to: ".$packet['setpoint']['value'];
  
  require('SAM/php_sam.php');
  $conn = new SAMConnection();
  $conn->connect(SAM_MQTT, array(SAM_HOST => '127.0.0.1', SAM_PORT => 1883));
  
  $msg_state = new SAMMessage($packet['setpoint']['value']);
  $conn->send('topic://state', $msg_state);
  $conn->disconnect();
