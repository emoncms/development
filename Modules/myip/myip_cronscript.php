<?php 

$ips = getIPs();

file_get_contents("http://emoncms.org/myip/set.json?apikey=YOUR_API_KEY&lanip=".$ips[0]);

function getIPs($withV6 = true) {
    preg_match_all('/inet'.($withV6 ? '6?' : '').' addr: ?([^ ]+)/', `ifconfig`, $ips);
    return $ips[1];
}
