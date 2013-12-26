<?php

// Writes 100,000 datapoints in 6-7s on raspberrypi 
// Writes 1000,000 datapoints in 59s on raspberrypi 

// Data is stored as a series of 4-byte values
// If we know the start time and the data interval then the timestamp for 
// every other data value can be worked out

$myFile = "feed_1";

$value = 120;

$start = time();

$fh = fopen($myFile, 'a');
for ($i=0; $i<1000000; $i++)
{
  fwrite($fh, pack("i",$value));
}
fclose($fh);

echo "Time: ".(time()-$start)."s \n";
