<?php

  /*

  Select a given number of datapoints between a start and end 
  time from feed data stored in a simple binary file

  The datapoint selection algorithm starts by finding the start
  time position in the file via a binary search.

  When the feed data is created a new line is added to the end of
  the feed data file, the time field is by the nature of timeseries
  data already in ascending order and unique, this makes the time
  field a suitable index for using the binary search approach to 
  query.

  One the start position in the data file is found the algorithm then
  reads a datapoint every x steps. 

  */

  $benchstart = microtime(true);

  // Query parameters
  $start = 1372424866 - (24*3600*300);
  $end = 1372424866;

  $data = get_feed_data(31,$start,$end,1000);

  echo json_encode($data);
  echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";


  function get_feed_data($feedid,$start,$end,$dp)
  {
    //echo $feedid." ".$start." ".$end." ".$dp."<br>";
    $fh = fopen("feed_$feedid", 'rb');
    $filesize = filesize("feed_$feedid");
    $pos = binarysearch($fh,$start,$filesize);

    // If we want one datapoint every 60s and the data rate is one 
    // every 10s then calculate the skipsize to be one 6.
    // select one datapoint out of every 6.
    $interval = ($end - $start) / $dp;
    $datainterval = 5;
    $skipsize = round($interval / $datainterval);
    if ($skipsize<1) $skipsize = 1;

    $data = array();

    $i=0;
    do {
      // Skip skipsize number of datapoints to the next position


      if ($pos>$filesize-8) return $data;
      fseek($fh,$pos);

      // Read the datapoint at this position
      $d = fread($fh,8);

      // Itime = unsigned integer (I) assign to 'time'
      // fvalue = float (f) assign to 'value' 
      $array = unpack("Itime/fvalue",$d);

      // and add to the data array
      $data[] = array($array['time']*1000,$array['value']);
      $i++;
      $pos += (8*$skipsize);
    } while ($array['time']<=$end && $i<1000);

    fclose($fh);
    return $data;

  }

  function binarysearch($fh,$time,$filesize)
  {
    // Binary search works by finding the file midpoint and then asking if
    // the datapoint we want is in the first half or the second half
    // it then finds the mid point of the half it was in and asks which half
    // of this new range its in, until it narrows down on the value.
    // This approach usuall finds the datapoint you want in around 20
    // itterations compared to the brute force method which may need to
    // go through the whole file that may be millions of lines to find a
    // datapoint.
    $start = 0; $end = $filesize-8;

    // 30 here is our max number of itterations
    // the position should usually be found within
    // 20 itterations.
    for ($i=0; $i<30; $i++)
    {
      // Get the value in the middle of our range
      $mid = $start + round(($end-$start)/16)*8;
      fseek($fh,$mid);
      $d = fread($fh,8);
      $array = unpack("Itime/fvalue",$d);

      // echo "S:$start E:$end M:$mid $time ".$array['time']." ".($time-$array['time'])."\n";

      // If it is the value we want then exit
      if ($time==$array['time']) return $mid;

      // If the query range is as small as it can be 1 datapoint wide: exit
      if (($end-$start)==8) return ($mid-8);

      // If the time of the last middle of the range is
      // more than our query time then next itteration is lower half
      // less than our query time then nest ittereation is higher half
      if ($time>$array['time']) $start = $mid; else $end = $mid;
    }
  }
