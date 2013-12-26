<?php

// Reads data from data file in a given range and at a given resolution
//
// Data is stored as a series of 4-byte values
// If we know the start time and the data interval then the timestamp for 
// every other data value can be worked out
//
// Storing data like this makes searching for datapoints really fast and easy
// as we know where in the file each data point is - no need to have any searching algorithms.
//
// This is how Mike Stirling implements data storage in timestore
//
// Reads 1000 datapoints over 5 hours of 10 second data in 85-88ms
// Reads 1000 datapoints over 200 hours of 10 second data in 93ms
// Reads 1000 datapoints over 2000 hours of 10 second data in 130ms
// Reads 1000 datapoints over 2600 hours of 10 second data in 124ms

$benchstart = microtime(true);

// Feed meta data
$feed = array('id'=>1, 'start'=>1372331129, 'interval'=>10);

$start = 1372331234;
$end = $start + 3600*200;

$data = get_feed_data($feed,$start,$end,1000);

echo json_encode($data);

echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";


// This function would be in the emoncms feed class
function get_feed_data($feed,$start,$end,$dp)
{
  // Open the feed data file
  $fh = fopen("feed_".$feed['id'], 'rb');

  $query_interval = ($end - $start) / $dp;

  // Calculate number of datapoints to skip to fetch the interval size we want 
  $skip_size = round($query_interval / $feed['interval']); 

  // Calculate the start position in the file
  $start_position = round(($start - $feed['start']) / $feed['interval']);

  $data = array();
  for ($i=0; $i<$dp; $i++)
  {
    $current_position = ($start_position + ($skip_size*$i));

    // datapoint pos to byte pos = mutiply by 4 bytes per datapoint
    $byte_position = $current_position*4;

    // seek datapoint in file
    fseek($fh,$byte_position);
    
    // read in 4 bytes
    $d = fread($fh,4);
 
    // unpack into an integer
    $array = unpack("i",$d);

    $time = $feed['start'] + ($current_position * $feed['interval']);
    $data[] = array($time, $array[1]);
  }

  fclose($fh);

  return $data;
}
