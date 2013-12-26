<?php

  $fh = fopen("feed_1", 'rb');

  $size = filesize("feed_1");

  for ($i=0; $i<$size; $i+=8)
  {
    $d = fread($fh,8); 
    $array = unpack("Itime/fvalue",$d);
    echo json_encode($array)."\n";
  }
