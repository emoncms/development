<?php

$client = stream_socket_client("tcp://127.0.0.1:1330", $errno, $errorMessage);

if ($client === false) {
    throw new UnexpectedValueException("Failed to connect: $errorMessage");
}
$ltime = time(); $i=0;
for (;;) {
  $d = fgets($client);
  //echo $d;
  $i++;
  
  if ((time()-$ltime)>=1)
  {
    $ltime = time();
    echo $i." l/s\n";
    $i = 0;
  }

  if (feof($client)) break;
}

fclose($client);
