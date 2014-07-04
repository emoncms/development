"""

  This code is released under the GNU Affero General Public License.
  
  OpenEnergyMonitor project:
  http://openenergymonitor.org

"""

import web
import json
import redis
from pyfina import pyfina
import time
from configobj import ConfigObj

settings = ConfigObj("emon-py.conf", file_error=True)
nodelist = settings['nodes']

r = redis.Redis(
    host=settings['redis']['host'], 
    port=settings['redis']['port'], 
    db=settings['redis']['db']
)

pyfina = pyfina(settings['data']['dir'])

urls = (
    '/', 'index',
    '/nodes', 'nodes',
    '/graph', 'graph',
    '/data', 'data'
)

render = web.template.render('views')

class index:
    def GET(self):
        return render.nodelist()

class graph:        
    def GET(self):
        return render.graph()

class nodes:        
    def GET(self):
        web.header('Content-Type', 'application/json')
        return r.get("nodes")
        
class data:
    def GET(self):
        params = web.input()
        
        nid = params['nid']
        vid = params['vid']
        start = params['start']
        end = params['end']
        
        filename = str(nid)+"."+str(vid)
        
        web.header('Content-Type', 'application/json')
        return json.dumps(pyfina.data(filename,start,end))
        
        
if __name__ == "__main__":    
    app = web.application(urls, globals())
    app.run()
