import web
import redis
import json

urls = (
    '/', 'index',
    '/nodes', 'nodes'
)

render = web.template.render('views')

r = redis.Redis(host='localhost', port=6379, db=0)

class index:
    def GET(self):
        return render.nodelist()

class nodes:        
    def GET(self):
    
        nodes = []
        nodekeys = r.keys('*')
    
        for x in range(len(nodekeys)):
          parts = nodekeys[x].split(':')
          nodeid = parts[1]
          nodehash = r.hgetall(nodekeys[x])
          nodehash['id'] = nodeid
          nodes.append(nodehash)
          
        web.header('Content-Type', 'application/json')
        return json.dumps(nodes)
        
if __name__ == "__main__":
    app = web.application(urls, globals())
    app.run()
