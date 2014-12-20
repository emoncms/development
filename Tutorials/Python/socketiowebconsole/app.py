from gevent import monkey
monkey.patch_all()

import time
from threading import Thread
from flask import Flask, render_template, session, request, redirect
from flask.ext.socketio import SocketIO, emit
import mosquitto
import os

app = Flask(__name__)
app.debug = True
app.config['SECRET_KEY'] = 'secret!'
socketio = SocketIO(app)
thread = None
username="demo"
password="demo"

def on_message(mosq, obj, msg):
    socketio.emit('my response',{'topic':msg.topic,'payload':msg.payload},namespace='/test') 

def background_thread():
    while mqttc.loop() == 0:
        pass
    
@app.route('/')
def index():
    session['valid'] = session.get('valid',0)
    if session['valid']:
        return render_template('index.html')
    else:
        return render_template('login.html')
        
@app.route('/login',methods = ['POST','GET'])
def login():
    if request.form['username']==username and request.form['password']==password:
        session['valid'] = session.get('valid',0)
        session['valid'] = True
    return redirect("/")
    
@app.route('/logout',methods = ['POST','GET'])
def logout():
    session.clear()
    return redirect("/")

@socketio.on('my event', namespace='/test')
def test_message(message):
    pass

@socketio.on('connect', namespace='/test')
def test_connect():
    pass

@socketio.on('disconnect', namespace='/test')
def test_disconnect():
    pass
    
# emit('my response', {'data': 'Connected', 'count': 0})    

# Start MQTT (Mosquitto)
mqttc = mosquitto.Mosquitto()
mqttc.on_message = on_message
mqttc.connect("127.0.0.1",1883, 60, True)
mqttc.subscribe("rx/#", 0)

if thread is None:
    thread = Thread(target=background_thread)
    thread.start()

if __name__ == '__main__':
    socketio.run(app,host='0.0.0.0',port=80)

