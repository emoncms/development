import web
import json

urls = (
    '/', 'index',
    '/nodes', 'nodes'
)

render = web.template.render('views')

class index:
    def GET(self):
        return render.nodelist()

class nodes:        
    def GET(self):
          
        nodes = [
          {"id":10, "data": "100,200,300", "length":"3"},
          {"id":20, "data": "100,200,300", "length":"3"}
        ]
          
        web.header('Content-Type', 'application/json')
        return json.dumps(nodes)
        
if __name__ == "__main__":
    app = web.application(urls, globals())
    app.run()
