<?php

  // Implementation of simpified emoncms input processing using timestore by Mike Stirling for disk storage of feed data and redis for temporary storage of last value data.

  // Licence: GNU GPL, Author Trystan Lea

  error_reporting(E_ALL);      
  ini_set('display_errors', 'on');

  require 'Predis/Autoloader.php';
  Predis\Autoloader::register();

  $userid = 4;

  $timestore_adminkey = "sZ9R_j}5m5mJUv,N{8hhI=ihmuUf.0Q6";
  require "timestore/Modules/feed/timestore_class.php";
  $timestore = new Timestore($timestore_adminkey);

  $redis = new Predis\Client();

  $mysqli = new mysqli("localhost","root","raspberry","emoncms");

  // Fetch input descriptor
  $result = $mysqli->query("SELECT id,nodeid,name,processList,record FROM input WHERE `userid` = '$userid'");
  $dbinputs = array();
  while ($row = $result->fetch_object()) {
      if ($row->nodeid==null) $row->nodeid = 0;
      if (!isset($dbinputs[$row->nodeid])) $dbinputs[$row->nodeid] = array();
      $dbinputs[$row->nodeid][$row->name] = array('id'=>$row->id, 'processList'=>$row->processList, 'record'=>$row->record);
  }

  echo json_encode($dbinputs);

  // Create a stream context that configures the serial port
  // And enables canonical input.
  $c = stream_context_create(array('dio' =>
    array('data_rate' => 9600,
          'data_bits' => 8,
          'stop_bits' => 1,
          'parity' => 0,
          'flow_control' => 0,
          'is_canonical' => 1)));

  // Are we POSIX or Windows?  POSIX platforms do not have a
  // Standard port naming scheme so it could be /dev/ttyUSB0
  // or some long /dev/tty.serial_port_name_thingy on OSX.
  if (PATH_SEPARATOR != ";") {
    $filename = "dio.serial:///dev/ttyAMA0";
  } else {
    $filename = "dio.serial://dev/ttyAMA0";
  }

  // Open the stream for read and write and use it.
  $f = fopen($filename, "r+", false, $c);
  stream_set_timeout($f, 0,1000);

  if (!$f) die;

  while(true)
  {
    $data = fgets($f);
    if ($data && $data!="\n")
    {
      echo "DATA RX:".$data;
      $values = explode(' ',$data);
      $nodeid = (int) $values[1];
      $nameid = 1;
      $time = time();
      $tmp = array();

      //---------------------------------------------------------------------------------------------------------------------
      // REGISTER INPUTS
      //---------------------------------------------------------------------------------------------------------------------
      for($i=2; $i<(count($values)-1); $i+=2)
      {
        // Get 16-bit integer
        $int16 = $values[$i] + $values[$i+1]*256;
        if ($int16>32768) $int16 = -65536 + $int16;
        $value = $int16;

        $name = $nameid;

        if (!isset($dbinputs[$nodeid][$name])) {
          $mysqli->query("INSERT INTO input (userid,name,nodeid) VALUES ('$userid','$name','$nodeid')");
          $dbinputs[$nodeid][$name] = true;
        } else { 
          $inputid = $dbinputs[$nodeid][$name]['id'];
          $redis->set("input_$inputid",$value);
          if ($dbinputs[$nodeid][$name]['processList']) $tmp[] = array('value'=>$value,'processList'=>$dbinputs[$nodeid][$name]['processList']);
        }

        $nameid++;
      }

      //---------------------------------------------------------------------------------------------------------------------
      // PROCESS INPUTS
      //---------------------------------------------------------------------------------------------------------------------
      foreach ($tmp as $i) 
      {
        $processList = $i['processList'];
        $value = $i['value'];

        // 1. For each item in the process list
        $pairs = explode(",",$processList);
        foreach ($pairs as $pair)    			        
        {
          $inputprocess = explode(":", $pair); 				                // Divide into process id and arg
          $processid = (int) $inputprocess[0];						            // Process id
          $arg =       (int) $inputprocess[1];						            // Process Arg

          if ($processid==2) $value = $value * $arg;                                       // scale
          if ($processid==3) $value = $value + $arg;                                       // offset

          if ($processid==6)  $value = $value * $redis->get("input_$arg");                 // x input
          if ($processid==11) $value = $value + $redis->get("input_$arg");                 // + input
          if ($processid==12) $value = $value / $redis->get("input_$arg");                 // / input
          if ($processid==22) $value = $value - $redis->get("input_$arg");                 // - input

          if ($processid==1)
          {
            $feedid = $arg;
            $feedname = "feed_".trim($feedid)."";

            // a. Insert data value in feed table
            $timestore->post_values($feedid,$time*1000,array($value),null);

            // b. Update feeds table
            $redis->set($feedname,json_encode(array('time'=>$time,'value'=>$value)));
          }
        }
      }
    }
  }

  fclose($f);

