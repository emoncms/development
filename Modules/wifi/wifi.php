<?php

class Wifi
{
	public function start()
	{
	    exec('sudo ifup wlan0',$return);
	    return "wlan0 started";
	}
	
	public function stop()
	{
	    exec('sudo ifdown wlan0',$return);
	    return "wlan0 stopped";
	}
	
	public function restart()
	{
		exec('sudo ifdown wlan0',$return);
		exec('sudo ifup wlan0',$return);
		return "wlan0 restarted";
	}
	
    public function scan()
    {
	    $return = '';
	    exec('sudo ifup wlan0',$return);
	    exec('sudo wpa_cli scan',$return);
	    sleep(2);
	    exec('sudo wpa_cli scan_results',$return);
	    for($shift = 0; $shift < 4; $shift++ ) {
		    array_shift($return);
	    }
	    
	    $networks = array();
	    foreach($return as $network) {
		    $arrNetwork = preg_split("/[\t]+/",$network);
		    if (isset($arrNetwork[4]))
		    {
		        $ssid = $arrNetwork[4];
		        $networks[$ssid] = array(
		            "BSSID"=>$arrNetwork[0],
		            "CHANNEL"=>$arrNetwork[1], 
		            "SIGNAL"=>$arrNetwork[2],
		            "SECURITY"=>substr($arrNetwork[3],1,-1)
		        );
		    }   
	    }
	    return $networks;
    }
    
    public function info()
    {
        $return = "";
		exec('/sbin/ifconfig wlan0',$return);
		exec('/sbin/iwconfig wlan0',$return);
		$strWlan0 = implode(" ",$return);
		$strWlan0 = preg_replace('/\s\s+/', ' ', $strWlan0);
		
		$wlan = array();
		preg_match('/HWaddr ([0-9a-f:]+)/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['MacAddress'] = $result[1];
		preg_match('/inet addr:([0-9.]+)/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['IPAddress'] = $result[1];
		preg_match('/Mask:([0-9.]+)/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['SubNetMask'] = $result[1];
		preg_match('/RX packets:(\d+)/',$strWlan0,$result);
		if (isset($result[1])) $wlan['RxPackets'] = $result[1];
		preg_match('/TX packets:(\d+)/',$strWlan0,$result);
		if (isset($result[1])) $wlan['TxPackets'] = $result[1];
		preg_match('/RX Bytes:(\d+ \(\d+.\d+ MiB\))/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['RxBytes'] = $result[1];
		preg_match('/TX Bytes:(\d+ \(\d+.\d+ [K|M|G]iB\))/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['TxBytes'] = $result[1];
		preg_match('/ESSID:\"([a-zA-Z0-9\s]+)\"/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['SSID'] = str_replace('"','',$result[1]);
		preg_match('/Access Point: ([0-9a-f:]+)/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['BSSID'] = $result[1];
		preg_match('/Bit Rate:([0-9]+ Mb\/s)/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['Bitrate'] = $result[1];
		preg_match('/Frequency:(\d+.\d+ GHz)/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['Freq'] = $result[1];
		preg_match('/Link Quality=([0-9]+\/[0-9]+)/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['LinkQuality'] = $result[1];
		preg_match('/Signal Level=([0-9]+\/[0-9]+)/i',$strWlan0,$result);
		if (isset($result[1])) $wlan['SignalLevel'] = $result[1];
		
		if(strpos($strWlan0, "ESSID") !== false ) $wlan['status'] = "connected"; else $wlan['status'] = "disconnected"; 
		
		
		return $wlan;
	}

    public function getconfig()
    {
        exec('sudo cat /etc/wpa_supplicant/wpa_supplicant.conf',$return);
        $ssid = array();
        $psk = array();
        foreach($return as $a) {
	        if(preg_match('/SSID/i',$a)) {
		        $arrssid = explode("=",$a);
		        $ssid[] = str_replace('"','',$arrssid[1]);
	        }
	        if(preg_match('/\#psk/i',$a)) {
		        $arrpsk = explode("=",$a);
		        $psk[] = str_replace('"','',$arrpsk[1]);
	        }
        }
        $numSSIDs = count($ssid);

        $registered = array();
        for($i = 0; $i < $numSSIDs; $i++) {
            $registered[$ssid[$i]] = array();
            if (isset($psk[$i])) $registered[$ssid[$i]]["PSK"] = $psk[$i];
            $registered[$ssid[$i]]["SIGNAL"] = 0;
        }
        return $registered;
    }
    
    public function setconfig($networks)
    {    
	    $config = "ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev\nupdate_config=1\n\n";

        foreach ($networks as $ssid=>$network)
        {
		    $ssid = escapeshellarg($ssid);
		    
		    $psk = "";
		    if (isset($network->PSK) && $network->PSK!="") 
		    {
		        $psk = escapeshellarg($network->PSK);
		        $result = "";
		        exec('wpa_passphrase '.$ssid.' '.$psk, $result);
		        
		        foreach($result as $b) {
			        if("Passphrase must be 8..63 characters" != $b) {
				        $config .= "$b\n";
			        }
		        }
		    }
		    else
		    {
		        $config .= "network={\n  ssid=".'"'.$ssid.'"'."\n  key_mgmt=NONE\n}\n";
		    }
	    }
	    exec("echo '$config' > /tmp/wifidata",$return);
	    system('sudo cp /tmp/wifidata /etc/wpa_supplicant/wpa_supplicant.conf',$returnval);
	    $this->restart();
	    
	    return $config;
	}
}
