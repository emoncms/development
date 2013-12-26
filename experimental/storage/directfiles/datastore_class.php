<?php

/*
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

   ---------------------------------------------------------------------
   Emoncms - open source energy visualisation
   Part of the OpenEnergyMonitor project:
   http://openenergymonitor.org

   test commit
*/

class Datastore 
{

  private $basedir;

  public function __construct($basedir)
  {
    $this->basedir = $basedir;
  }

  public function create($feedid)
  {
    $fh = fopen($this->basedir."feed_$feedid", 'w');
    fclose($fh);
  }

  public function post($feedid,$time,$value)
  {
    $fh = fopen($this->basedir."feed_$feedid", 'a');
    fwrite($fh, pack("If",$time,$value));
    fclose($fh);
  }

  public function update($feedid,$time,$value)
  {
    $fh = fopen($this->basedir."feed_$feedid", 'c+');
    $filesize = filesize($this->basedir."feed_$feedid");

    // Search for entry where timestamp = time
    $pos = $this->binarysearch_exact($fh,$time,$filesize);

    if ($pos>=0) 
    {
      fseek($fh,$pos);
      fwrite($fh, pack("If",$time,$value));
      fclose($fh);
    } 
    else 
    {
      fclose($fh);
      $fh = fopen($this->basedir."feed_$feedid", 'a');
      fwrite($fh, pack("If",$time,$value));
      fclose($fh);
    }
  }

  // A slightly more efficient update method that is specifically for creating daily type feeds
  public function ulvc($feedid,$time,$value)
  {
    $filesize = filesize($this->basedir."feed_$feedid");
    
    if ($filesize == 0) {
      // if empty file: insert
      $this->post($feedid,$time,$value);
    } else {
      // else get time of last datapoint
      $fh = fopen($this->basedir."feed_$feedid", 'c+');
      fseek($fh,$filesize-8);
      $d = fread($fh,8);
      $array = unpack("Itime/fvalue",$d);

      if ($time==$array['time']) {
        // if last datapoint == update time: update value
        fseek($fh,$filesize-8);
        fwrite($fh, pack("If",$time,$value));
      } elseif ($time>$array['time']) {
        // if last datapoint == in past: insert new value
        fclose($fh);
        $this->post($feedid,$time,$value);
      } else {
        // value is back in time so ignore
      }
    }
  }

  public function get_feed_data($feedid,$start,$end,$dp)
  {
    //echo $feedid." ".$start." ".$end." ".$dp."<br>";
    $fh = fopen($this->basedir."feed_$feedid", 'rb');
    $filesize = filesize($this->basedir."feed_$feedid");
    $pos = $this->binarysearch($fh,$start,$filesize);

    // If we want one datapoint every 60s and the data rate is one 
    // every 10s then calculate the skipsize to be one 6.
    // select one datapoint out of every 6.
    $interval = ($end - $start) / $dp;
    $datainterval = 3600*24;
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

  private function binarysearch($fh,$time,$filesize)
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

  private function binarysearch_exact($fh,$time,$filesize)
  {
    if ($filesize==0) return -1;
    $start = 0; $end = $filesize-8;
    for ($i=0; $i<30; $i++)
    {
      $mid = $start + round(($end-$start)/16)*8;
      fseek($fh,$mid);
      $d = fread($fh,8);
      $array = unpack("Itime/fvalue",$d);
      if ($time==$array['time']) return $mid;
      if (($end-$start)==8) return -1;
      if ($time>$array['time']) $start = $mid; else $end = $mid;
    }
    return -1;
  }
}
