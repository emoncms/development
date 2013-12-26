<?php

  /*

  Timestore: dedicated timeseries database written in C by Mike Stirling

  To install timestore:

    git clone http://mikestirling.co.uk/git/timestore.git
    cd timestore
    make
    cd src
    sudo ./timestore -d

  Fetch the admin key:

    cd /var/lib/timestore
    nano adminkey.txt

  Insert admin key below.

  
  */

  // Select 1000 over 5 hours 45ms
  // Select 10 over 1 hours 20ms 

  $timestore_adminkey = "sZ9R_j}5m5mJUv,N{8hhI=ihmuUf.0Q6";
  require "timestore_class.php";
  $timestore = new Timestore($timestore_adminkey);

  $benchstart = microtime(true);

  $start = time();
  $end = $start + 3600*5;

  echo $timestore->get_series(308,0,1000,$start,$end,null);

  echo "Time: ".((microtime(true)-$benchstart)*1000)."ms \n";
  
