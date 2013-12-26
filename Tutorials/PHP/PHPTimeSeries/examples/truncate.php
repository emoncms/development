<?php

  $fh = fopen("feed_1", 'a');
  fseek($fh,16);
  echo ftell($fh);
  ftruncate($fh , 32 );
