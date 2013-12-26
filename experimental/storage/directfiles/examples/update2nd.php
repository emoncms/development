<?php

  $fh = fopen("feed_1", 'c');
  fseek($fh,8);
  fwrite($fh, pack("If",time(),1040));
  fclose($fh);
