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

  // 10000 inserts 52s
  // 100000 inserts 524s

  $timestore_adminkey = "sZ9R_j}5m5mJUv,N{8hhI=ihmuUf.0Q6";
  require "timestore_class.php";
  $timestore = new Timestore($timestore_adminkey);

  $time = time();
  $value = 1000;

  $timestore->create_node(308,10);

  $start = time();

  for ($i=0; $i<10000; $i++)
  {
    $timestore->post_values(308,$time*1000,array($value),null);
    $time+=10;
  }

  echo "Time: ".(time()-$start)."s \n";
  
