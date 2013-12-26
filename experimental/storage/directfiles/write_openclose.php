<?php

// Writes 100,000 in 27,24,26s on the raspberrypi

// Data is stored as a series of 4-byte values
// If we know the start time and the data interval then the timestamp for 
// every other data value can be worked out

$myFile = "feed_1";

$value = 120;

$start = time();

for ($i=0; $i<100000; $i++)
{
  $fh = fopen($myFile, 'a');
  fwrite($fh, pack("i",$value));
  fclose($fh);
}

echo "Time: ".(time()-$start)."s \n";
