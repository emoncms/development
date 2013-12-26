# Storage engine test

All tested on a raspberrypi, running off the standard SanDisk SDHC 4Gb SD Card.

## MYSQL

  [https://github.com/emoncms/experimental/blob/master/storage/MYSQL/mysql.php](https://github.com/emoncms/experimental/blob/master/storage/MYSQL/mysql.php)

- InnoDB INSERT 1000 points 21s,25s,20s (Normalised to 100,000 ~ 2200s)
- InnoDB INSERT 10000 points 167s,183s (Normalised to 100,000 ~ 1750s)
- MYISAM INSERT 10000 points 15-17s (Normalised to 100,000 ~ 160s)
- MYISAM INSERT 100000 points 165s

**MYISAM | INNODB READ**

Benchmark of current emoncms mysql read function that selects given number of datapoints over a time window.

MYISAM results on the left | INNODB results on the right 

[https://github.com/emoncms/experimental/blob/master/storage/MYSQL/mysql_read.php](https://github.com/emoncms/experimental/blob/master/storage/MYSQL/mysql_read.php)

10000 datapoint table:

- 1000dp over 5 hours (average method) 232ms | 391ms
- 1000dp over 24 hours (average method) 424ms | 675ms

1000000 datapoint table: (115 days @ 10s)

- all 0.2 hours (all method) 40ms | 38ms
- all 0.5 hours (all method) 58ms | 55ms
- all over 1 hours (all method) 90ms | 82ms
- all over 1.3 hours (all method) 108ms | 100ms
- 1000dp over 3 hours (average method) 237ms | 272ms
- 1000dp over 5 hours (average method) 280ms | 327ms
- 1000dp over 24 hours (average method) 726 ms | 949ms
- 1000dp over 48 hours (average method) 1303 ms | 1767ms
- 1000dp over 52 hours (php loop method) 2875 ms | 2650ms
- 1000dp over 100 hours (php loop method) 3124 ms | 2882ms
- 1000dp over 200 hours (php loop method) 2934 ms | 2689ms
- 1000dp over 400 hours (php loop method) 2973 ms | 2749ms
- 1000dp over 2000 hours (php loop method) 2956 ms | 2762ms
- 1000dp over 2600 hours (php loop method) 2969 ms | 2767ms

PHP loop method timing may be quite a bit longer if the server is under heavy load.

- Initial benchmarking results on netbook: [http://emoncms.org/site/docs/developdatastorage](http://emoncms.org/site/docs/developdatastorage)
- Recent blog post on current emoncms implementation: [The current feed storage implementation](http://openenergymonitor.blogspot.co.uk/2013/05/the-current-emoncms-feed-storage.html)

## Timestore

Blog post on timestore: [Timestore timeseries database](http://openenergymonitor.blogspot.com/2013/06/timestore-timeseries-database.html)

[https://github.com/emoncms/experimental/blob/master/storage/timestore/timestore.php](https://github.com/emoncms/experimental/blob/master/storage/timestore/timestore.php)

- 10000 inserts 52s
- 100,000 inserts 524s

[https://github.com/emoncms/experimental/blob/master/storage/timestore/timestore_read.php](https://github.com/emoncms/experimental/blob/master/storage/timestore/timestore_read.php)

- Read 1000 datapoints over 5 hours: 45ms
- Read 10 datapoints over 5 hours 20ms

Includes layer averaging and mutiple layers so there is quite a bit more going on, so not directly comparable

## Direct file

- [Direct file write 100,000](https://github.com/emoncms/experimental/blob/master/storage/directfiles/write.php): 6-7s
- [Direct file write 100,000](https://github.com/emoncms/experimental/blob/master/storage/directfiles/write_openclose.php) open and close each time: 27,24,26s
- [Direct file read 1000](https://github.com/emoncms/experimental/blob/master/storage/directfiles/read.php) datapoints over 5 hours of 10 second data in 85-88ms
- Reads 1000 datapoints over 200 hours of 10 second data in 93ms
- Reads 1000 datapoints over 2000 hours of 10 second data in 130ms
- Reads 1000 datapoints over 2600 hours of 10 second data in 124ms

## Redis

Blog post: [Redis idea](http://openenergymonitor.blogspot.com/2013/06/idea-for-using-redis-in-memory-database.html)

- Redis (in-memory)

## Other ideas for storage format

- [Removing redundant datapoints - part 1](http://openenergymonitor.blogspot.com/2013/06/removing-redundant-datapoints-part-1.html)
- [Removing redundant datapoints – algorithm 1](http://openenergymonitor.blogspot.com/2013/06/removing-redundant-datapoints-algorithm.html)
