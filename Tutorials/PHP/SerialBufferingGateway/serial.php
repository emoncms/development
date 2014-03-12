<?php

$fp = fopen("importlock", "w");
if (! flock($fp, LOCK_EX | LOCK_NB)) { echo "Already running\n"; die; }

$host = "http://emoncms.org";

// Enter your emoncms write api key here:
$apikey = "";

$time = microtime(true);
$lastrxtime = $time;
$lastsendtime = $time;
$lastsendsuccess = $time;

$buffer = "[";

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

$lastsend = time();

while(true)
{
    $time = microtime(true);

    if ($instr = fgets($f))
    {
        $instr = trim($instr);

        // Split input string
        $parts = explode(' ',$instr);

        if ($instr!="" && count($parts))
        {
            // Start of packet description
            $packet = array(time()-$lastsend,0);

            // Type validation
            for ($i=0; $i<count($parts); $i++)
            {
                $packet[] = (float) $parts[$i];
            }

            if ($buffer!="[") $buffer.=",";
            $buffer .= json_encode($packet);

            print "Request ";
            print memory_get_usage()." ".memory_get_usage(true)." ";
            //print $buffer."]";
            $response = request($host,"POST","/input/bulk.json",$apikey,$buffer."]");

            if ($response=='ok') {
                print "ok\n";
                $lastsend = time();
                $buffer = "[";
            } else {
                print "failed\n";
            }

        }
    }
    
    // Sleep for a little while so that script doesnt loop to fast
    usleep(250000);
}

fclose($f);

function request($host,$method,$path,$apikey,$data)
{
    $curl = curl_init($host.$path."?apikey=".$apikey);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_POSTFIELDS,"data=".$data);
    
    // Timeouts - max time to wait before pronouncing request failure
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,3);
    curl_setopt($curl, CURLOPT_TIMEOUT, 3);
    $curl_response = curl_exec($curl);
    curl_close($curl);
    return $curl_response;
}

