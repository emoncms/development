<?php

  $fh = fopen("feed_1", 'a');
  fwrite($fh, pack("If",time(),100));
  fclose($fh);
