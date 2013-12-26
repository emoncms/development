## In memory storage: PHP shared memory vs Redis vs MYSQL

To start with I created baseline test using MYSQL updating a feed's last time and value in the feeds table row for that feed. This took around: 4800 – 5100 ms to update a row 10000 times.

We would imagine Redis doing a lot better as its in-memory, it isnt writing to disk each time which is much slower than memory access. Redis did indeed perform faster completing the same number of updates to a key-value pair in 1900 – 2350ms. Im a little surprised thought that it was only 2.3x as fast and not much faster, but then there is a lot going on Redis has its own server which needs to be accessed from the PHP client this is going to slow things down a bit, I tested both the phpredis client and Predis. Phpredis was between 500-1000 ms faster than the Predis client and is written in c.

How fast can in-memory storage be? A general program variable is also a form of in-memory storage, a quick test suggests that it takes 21ms to write to a program variable 10000 times, much better than 2.3x faster thats 230x faster! The problem with in program variables is that if they are written to in one script say an instance of input/post they cannot be accessed by another instance serving feed/list, we need some form of storage that can be accessed across different instances of scripts.

The difference between 21ms and 1900-2350ms for redis is intriguingly large and so I thought I would search for other ways of storing data in-memory that would allow access between different application scripts and instances.

I came across the PHP shared memory functions which are similar to the flat file access but for memory, the results of a simple test are encouraging showing a write time of 48ms for 10000 updates. So from a performance perspective using php shared memory looks like a better way of doing things. 

The issue though is implementation, mysql made it really easy to search for the feed rows that you wanted (either by selecting by feed id or by feeds that belong to a user or feeds that are public), I'm a little unsure about how best to implement the similar functionality in redis but it looks like it may be possible by just storing each feed meta data roughly like this: feed_1: {time:1300,value:20}. 

Shared memory though looks like it could be quite a bit more complicated to implement, but then it does appear to be much faster. Maybe the 2.3x speed improvement over mysql offered by redis is fast enough? and its probably much faster in high-concurancy situations. I think more testing and attempts at writing full implementations using each approach is needed before a definitive answer can be reached.
