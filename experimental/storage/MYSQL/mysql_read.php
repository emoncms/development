<?php

  /*
  MYISAM | INNODB

  10000 datapoint table:

  1000dp over 5 hours (average method) 232ms | 391ms
  1000dp over 24 hours (average method) 424ms | 675ms

  1000000 datapoint table: (115 days @ 10s)

  all 0.2 hours (all method) 40ms | 38ms
  all 0.5 hours (all method) 58ms | 55ms
  all over 1 hours (all method) 90ms | 82ms
  all over 1.3 hours (all method) 108ms | 100ms
  1000dp over 3 hours (average method) 237ms | 272ms
  1000dp over 5 hours (average method) 280ms | 327ms
  1000dp over 24 hours (average method) 726 ms | 949ms
  1000dp over 48 hours (average method) 1303 ms | 1767ms
  1000dp over 52 hours (php loop method) 2875 ms | 2650ms
  1000dp over 100 hours (php loop method) 3124 ms | 2882ms
  1000dp over 200 hours (php loop method) 2934 ms | 2689ms
  1000dp over 400 hours (php loop method) 2973 ms | 2749ms
  1000dp over 2000 hours (php loop method) 2956 ms | 2762ms
  1000dp over 2600 hours (php loop method) 2969 ms | 2767ms
  */

  error_reporting(E_ALL);      
  ini_set('display_errors', 'on');

  $start = time();
  $end = $start + 3600*1.3;

  $benchstart = microtime(true);
  $data = get_data(1000,$start*1000,$end*1000,1000);

  echo json_encode($data);

  echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";

  function get_data($feedid,$start,$end,$dp)
  {
    $mysqli = new mysqli("localhost","root","raspberry","emoncms");

    $feedid = intval($feedid);
    $start = floatval($start);
    $end = floatval($end);
    $dp = intval($dp);

    if ($end == 0) $end = time()*1000;

    $feedname = "feed_".trim($feedid)."";
    $start = $start/1000; $end = $end/1000;

    $data = array();
    $range = $end - $start;
    if ($range > 180000 && $dp > 0) // 50 hours
    {
      echo "PHP RESOLUTION METHOD\n\n";
      $td = $range / $dp;
      $stmt = $mysqli->prepare("SELECT time, data FROM $feedname WHERE time BETWEEN ? AND ? LIMIT 1");
      $t = $start; $tb = 0;
      $stmt->bind_param("ii", $t, $tb);
      $stmt->bind_result($dataTime, $dataValue);
      for ($i=0; $i<$dp; $i++)
      {
        $tb = $start + intval(($i+1)*$td);
        $stmt->execute();
        if ($stmt->fetch()) {
          if ($dataValue!=NULL) { // Remove this to show white space gaps in graph      
            $time = $dataTime * 1000;
            $data[] = array($time, $dataValue);
          }
        }
        $t = $tb;
      }
    } else {
      if ($range > 5000 && $dp > 0)
      {
        echo "MYSQL AVERAGE METHOD\n\n";
        $td = intval($range / $dp);
        $sql = "SELECT FLOOR(time/$td) AS time, AVG(data) AS data".
          " FROM $feedname WHERE time BETWEEN $start AND $end".
          " GROUP BY 1";
      } else {
        echo "MYSQL ALL METHOD\n\n";
        $td = 1;
        $sql = "SELECT time, data FROM $feedname".
          " WHERE time BETWEEN $start AND $end ORDER BY time DESC";
      }
     
      $result = $mysqli->query($sql);
      if($result) {
        while($row = $result->fetch_array()) {
          $dataValue = $row['data'];
          if ($dataValue!=NULL) { // Remove this to show white space gaps in graph      
            $time = $row['time'] * 1000 * $td;  
            $data[] = array($time , $dataValue); 
          }
        }
      }
    }

    return $data;
  }
