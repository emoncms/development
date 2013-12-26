<?php

  while(true)
  {
    $fh = fopen("feed_1", 'a');
    fwrite($fh, pack("If",100,100));
    fclose($fh);
  }
