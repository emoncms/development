<?php

$shmid = shmop_open(0, 'c', 0755, 1024);

$time = time();
$value = 1800;

// Write benchmark

$benchstart = microtime(true);

for ($i=0; $i<1000; $i++) 
{
  shmop_write($shmid,pack("If",$time,$value), 0);
}

echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";

// Reading example

$d = shmop_read($shmid, 0, 8);
$array = unpack("Itime/fvalue",$d);
echo json_encode($array)."\n";

shmop_delete($shmid);
shmop_close($shmid);
