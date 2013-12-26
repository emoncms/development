import MySQLdb
import MySQLdb.cursors
import json
from Feed import Feed

db = MySQLdb.connect(host="localhost",user="root",passwd="",db="emoncms",cursorclass=MySQLdb.cursors.DictCursor)
cursor = db.cursor()

feed = Feed(cursor)
print json.dumps(feed.list(3))
