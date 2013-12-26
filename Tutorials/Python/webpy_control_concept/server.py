import web
import json

urls = (
    '/', 'index',
    '/setpoint', 'setpoint'
)

render = web.template.render('views')

class index:
    def GET(self):
        return render.thermostat()

class setpoint:        
    def GET(self):
          
        # Get the 'value' = 18.5
        
        # MQTT pub update
        
        web.header('Content-Type', 'application/json')
        return json.dumps(result)
        
if __name__ == "__main__":
    app = web.application(urls, globals())
    app.run()
