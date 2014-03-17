<?php

$fp = fopen("importlock", "w");
if (! flock($fp, LOCK_EX | LOCK_NB)) { echo "Already running\n"; die; }

// Remote server settings

    // Host name of the remote server
    $host = "http://emoncms.org";

    // Remote server emoncms api key (read & write) found in input api helper tab
    $apikey = "";

    // Connection timeout - both should be set to less than buffer_send_interval
    // The number of seconds to wait while trying to connect.
    $connect_timeout = 3;
    // The maximum number of seconds to allow cURL functions to execute.
    $total_timeout = 3;
    

// Buffer settings

    // Maximum number of packets that can be sent in one request to emoncms
    // distributes load on server in the event of mass simultaneous upload from many monitors
    // Balance between availability of internet and max load.
    $max_upload_lines = 500;

    // Minimum interval for sending data to emoncms
    // if internet has been down for hours and several buffer blocks (of max_upload_lines) are full
    // this will set the time inbetween each sent block
    $buffer_send_interval = 5;
    


// Read from serial with data coming from RFM12PI with RFM12_Demo sketch
// All Emoncms code is released under the GNU Affero General Public License.

$c = stream_context_create(
    array(
        'dio' => array(
            'data_rate' => 9600,
            'data_bits' => 8,
            'stop_bits' => 1,
            'parity' => 0,
            'flow_control' => 0,
            'is_canonical' => 1
        )
    )
);

// ttyAMA0 is the standard serial port used on the raspberrypi
// Depending on your machine you may need to change this to i.e: /dev/ttyUSB0

if (PATH_SEPARATOR != ";") {
    $filename = "dio.serial:///dev/ttyAMA0";
} else {
    $filename = "dio.serial://dev/ttyAMA0";
}

$f = fopen($filename, "r+", false, $c);
stream_set_timeout($f, 0,1000);


// Buffer vars
$buffer = array();
$buffer[0] = "";
$bcount = 0;

$start_time = time();
$buffertime = microtime(true);

$val = 0;
while(true)
{
    
    $timeindex = time()-$start_time;
    $microtime = microtime(true);

    // On serial
    if ($instr = fgets($f))
    {
        $instr = trim($instr);
        
        // Split input string
        $parts = explode(' ',$instr);

        if ($instr!="" && count($parts))
        {
            // Start of packet description
            $packet = array($timeindex,0);

            // Type validation
            for ($i=0; $i<count($parts); $i++)
            {
                $packet[] = (float) $parts[$i];
            }

            $bi = count($buffer)-1;
            // The buffer is stored as a precompiled json string ready for sending to the remote server
            if ($buffer[$bi]!="") $buffer[$bi].=",";
            $buffer[$bi] .= json_encode($packet); 
            
            $bcount ++;
            
            // Segments the buffer into blocks in order to limit the max upload size to the server
            // if too many monitors try and upload a lot of data simultaneously the server may be
            // unable to handle it.
            if ($bcount>=$max_upload_lines) {
              $buffer[] = "";
              $bcount = 0;
            }
        }
        
        // Verbose output of buffer
        // print "-------------------------------\n";
        // for ($b=0; $b<count($buffer); $b++)
        // {
        // print $b.": ".$buffer[$b]."\n";
        // }
    }
    

    
    // If there is data in the buffer try sending every 1s
    if ($buffer[0]!="" && ($microtime-$buffertime)>=$buffer_send_interval)
    {
        $buffertime = $microtime;
        
        print "Sending ";
        // print "Sending: "."[".$buffer[0]."]&sentat=".$timeindex."\n";
        $response = request($host,"POST","/input/bulk.json",$apikey,"data=[".$buffer[0]."]&sentat=".$timeindex);
        
        if ($response=='ok')
        {
            print "ok\n";
            if (count($buffer)>1) {
                array_shift($buffer);
            } else {
                $buffer[0] = "";
                $bcount = 0;
            }
        } 
        else
        {
            print "failed\n";
        }
    }
    
    // Sleep for a little while so that script doesnt loop to fast
    usleep(10000);
}

fclose($f);

function request($host,$method,$path,$apikey,$data)
{
    $curl = curl_init($host.$path."?apikey=".$apikey);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
    
    // Timeouts - max time to wait before pronouncing request failure
    global $connect_timeout, $total_timeout;
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,$connect_timeout);
    curl_setopt($curl, CURLOPT_TIMEOUT,$total_timeout);
    $curl_response = curl_exec($curl);
    curl_close($curl);
    return $curl_response;
}
