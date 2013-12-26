import web
import MySQLdb
import MySQLdb.cursors
import json
from Feed import Feed

db = MySQLdb.connect(host="localhost",user="root",passwd="",db="emoncms",cursorclass=MySQLdb.cursors.DictCursor)
mysql = db.cursor()

urls = (
    '/', 'index',
    '/feed/list', 'feeds'
)

class index:
    def GET(self):
        pass
        
class feeds:
  def GET(self):
    web.header('Content-Type', 'application/json')
    feed = Feed(mysql)
    return json.dumps(feed.list(3))
        
if __name__ == "__main__":
    app = web.application(urls, globals())
    app.run()
