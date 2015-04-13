<?php

class Process
{
    // Available processes
    private $processes = array(
        "scale","offset","allowpositive","allownegative","resettozero",
        "log","powertokwh","accumulator","whaccumulator",
        "addinput","subtractinput","multiplyinput","divideinput"
    );
    
    private $mysqli;
    private $feed;
    
    public $nodes;
    
    public function __construct($mysqli,$feed)
    {
        $this->mysqli = $mysqli;
        $this->feed = $feed;
    }
    
    public function input($time,$value,$processlist)
    {   
        foreach ($processlist as $process)
        {
            if (isset($process->key) && in_array($process->key,$this->processes))
            {
                $fn = $process->key;
                $value = $this->$fn($time,$value,$process);
            }
        }
    }

    private function scale($time,$value,$process)
    {
        if (!isset($process->value)) return $value;
        return $value * $process->value;
    }
    
    private function offset($time,$value,$process)
    {
        if (!isset($process->value)) return $value;
        return $value + $process->value;
    }
    
    private function allowpositive($time, $value, $process)
    {
        if ($value<0) $value = 0;
        return $value;
    }

    private function allownegative($time, $value, $process)
    {
        if ($value>0) $value = 0;
        return $value;
    }
    
    private function resettozero($time, $value, $process)
    {
        $value = 0;
        return $value;
    }

    private function log($time,$value,$process)
    {
        if (!isset($process->feedid)) return $value;
        $this->feed->insert_data($process->feedid,time(),$time,$value);
        return $value;
    }
    
    public function powertokwh($time_now,$value,$process)
    {
        if (!isset($process->feedid)) return $value;
        $feedid = $process->feedid;
        
        $new_kwh = 0;

        // Get last value
        $last = $this->feed->get_timevalue($feedid);
        if (!isset($last['value'])) $last['value'] = 0;
        $last_kwh = $last['value']*1;
        $last_time = $last['time']*1;

        // only update if last datapoint was less than 2 hour old
        // this is to reduce the effect of monitor down time on creating
        // often large kwh readings.
        if ($last_time && (time()-$last_time)<7200)
        {
            // kWh calculation
            $time_elapsed = ($time_now - $last_time);
            $kwh_inc = ($time_elapsed * $value) / 3600000.0;
            $new_kwh = $last_kwh + $kwh_inc;
        } else {
            // in the event that redis is flushed the last time will
            // likely be > 7200s ago and so kwh inc is not calculated
            // rather than enter 0 we enter the last value
            $new_kwh = $last_kwh;
        }

        $padding_mode = "join";
        $this->feed->insert_data_padding_mode($feedid, $time_now, $time_now, $new_kwh, $padding_mode);
        return $value;
    }
    
    public function accumulator($time,$value,$process)
    {
        if (!isset($process->feedid)) return $value;
        $feedid = $process->feedid;
        
        $last = $this->feed->get_timevalue($feedid);
        $value = $last['value'] + $value;
        $padding_mode = "join";
        $this->feed->insert_data_padding_mode($feedid, $time, $time, $value, $padding_mode);
        return $value;
    }
    
    public function whaccumulator($time,$value,$process)
    {
        if (!isset($process->feedid)) return $value;
        $feedid = $process->feedid;
        
        $max_power = 25000;
        $totalwh = $value;
        
        global $redis;
        if (!$redis) return $value; // return if redis is not available

        if ($redis->exists("process:whaccumulator:$feedid")) {
            $last_input = $redis->hmget("process:whaccumulator:$feedid",array('time','value'));
    
            $last_feed  = $this->feed->get_timevalue($feedid);
            $totalwh = $last_feed['value'];
            
            $time_diff = $time - $last_feed['time'];
            $val_diff = $value - $last_input['value'];
            
            $power = ($val_diff * 3600) / $time_diff;
            
            if ($val_diff>0 && $power<$max_power) $totalwh += $val_diff;
            
            $padding_mode = "join";
            $this->feed->insert_data_padding_mode($feedid, $time, $time, $totalwh, $padding_mode);
            
        }
        $redis->hMset("process:whaccumulator:$feedid", array('time' => $time, 'value' => $value));

        return $totalwh;
    }
    
    // Input
    
    private function addinput($time, $value, $process)
    {
        if (!isset($process->nodevar)) return $value;
        $nodevar = explode("/",$process->nodevar);
        $nodeid = $nodevar[0];
        $rxtx = $nodevar[1];
        $vid = $nodevar[2];
    
        $inputval = $this->nodes->$nodeid->$rxtx->values[$vid];
        $value += $inputval;
        return $value;
    }
    
    private function subtractinput($time, $value, $process)
    {
        if (!isset($process->nodevar)) return $value;
        $nodevar = explode("/",$process->nodevar);
        $nodeid = $nodevar[0];
        $rxtx = $nodevar[1];
        $vid = $nodevar[2];
    
        $inputval = $this->nodes->$nodeid->$rxtx->values[$vid];
        $value -= $inputval;
        return $value;
    }
    
    private function multiplyinput($time, $value, $process)
    {
        if (!isset($process->nodevar)) return $value;
        $nodevar = explode("/",$process->nodevar);
        $nodeid = $nodevar[0];
        $rxtx = $nodevar[1];
        $vid = $nodevar[2];
    
        $inputval = $this->nodes->$nodeid->$rxtx->values[$vid];
        $value *= $inputval;
        return $value;
    }
    
    private function divideinput($time, $value, $process)
    {
        if (!isset($process->nodevar)) return $value;
        $nodevar = explode("/",$process->nodevar);
        $nodeid = $nodevar[0];
        $rxtx = $nodevar[1];
        $vid = $nodevar[2];
    
        $inputval = $this->nodes->$nodeid->$rxtx->values[$vid];
        $value /= $inputval;
        return $value;
    }
}
