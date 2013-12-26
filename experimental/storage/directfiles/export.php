<?php

// Exports 178Mb MYSQL MYISAM Table to 67Mb flat file
// 67Mb flat file compresses to 18.5Mb with .tar.gz compression

$mysqli = new mysqli("localhost","root","pass","db");

$result = $mysqli->query("SELECT * FROM feed_1 ORDER BY time Asc");

$fh = fopen("feed_1", 'a');
while($row = $result->fetch_array())
{
  fwrite($fh, pack("If",$row[0],$row[1]));
}
fclose($fh);
