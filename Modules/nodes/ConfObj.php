<?php

function confobj_parse($confobjstr)
{
    $lines = explode("\n",$confobjstr);

    $levelarray = array();

    $json = new stdClass;

    foreach ($lines as $line)
    {
        $line = str_replace(" ","",$line);
        $line = str_replace("]","",$line);
        $tmp = explode("#",$line);
        $line = $tmp[0];
        
        if ($line!="")
        {
            if ($line[0]=="[") {
                $level = (int) substr_count($line,"[");
                $line = str_replace("[","",$line);
                
                $levelarray[$level-1] = $line;
                $levelarray = array_slice($levelarray,0,$level);
                
            } else {
                
                $keyvalue = explode("=",$line);
                $key = $keyvalue[0];
                $value = trim($keyvalue[1]);
                
                $a = $levelarray;
                $a[] = $key;

                $arr = explode(",",$value);
                if (count($arr)>1) $value = $arr;
                    
                // Level 1
                if (isset($a[0]) && !isset($json->$a[0])) 
                    if (count($a)>1) 
                        $json->$a[0] = new stdClass;
                    else 
                        $json->$a[0] = $value;
                
                // Level 2
                if (isset($a[1]) && !isset($json->$a[0]->$a[1])) 
                    if (count($a)>2) 
                        $json->$a[0]->$a[1] = new stdClass; 
                    else 
                        $json->$a[0]->$a[1] = $value;

                // Level 3
                if (isset($a[2]) && !isset($json->$a[0]->$a[1]->$a[2])) 
                    if (count($a)>3)
                        $json->$a[0]->$a[1]->$a[2] = new stdClass;
                    else
                        $json->$a[0]->$a[1]->$a[2] = $value;     

                // Level 4
                if (isset($a[3]) && !isset($json->$a[0]->$a[1]->$a[2]->$a[3])) 
                    if (count($a)>4)
                        $json->$a[0]->$a[1]->$a[2]->$a[3] = new stdClass;
                    else
                        $json->$a[0]->$a[1]->$a[2]->$a[3] = $value;  
            }
        }
    }
    return $json;
}
