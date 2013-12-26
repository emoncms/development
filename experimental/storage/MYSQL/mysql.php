<?php

  // ALTER TABLE `feed_1` ENGINE=INNODB
  // ALTER TABLE `feed_1` ENGINE=MYISAM

  // InnoDB INSERT 21s,25s,20s  1000 points
  // InnoDB INSERT 167s,183s    10000 points

  // MYISAM INSERT 15-17s       10000 points
  // MYISAM INSERT 165s         100000 points
  // MYISAM INSERT 1622s        1000000 points

  $mysqli = new mysqli("localhost","root","raspberry","emoncms");
								
  $mysqli->query("DROP TABLE feed_1000");	
  $mysqli->query("CREATE TABLE feed_1000 (time INT UNSIGNED, data float, INDEX ( `time` )) ENGINE = MYISAM");

  $time = time();
  $value = 120;

  $benchstart = time();
  for ($i=0; $i<10000; $i++)
  {
    $result = $mysqli->query("INSERT INTO feed_1000 (`time`,`data`) VALUES ('$time','$value')");
    $time+=10;
  }
  echo "Time: ".(time()-$benchstart)."s \n";


