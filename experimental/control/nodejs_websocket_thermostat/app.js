var mqtt = require('mqtt')

var app = require('http').createServer(handler)
  , io = require('socket.io').listen(app)
  , fs = require('fs')
  
app.listen(80);

function handler (req, res) {
  fs.readFile(__dirname + '/index.html',
  function (err, data) {
    if (err) {
      res.writeHead(500);
      return res.end('Error loading index.html');
    }

    res.writeHead(200);
    res.end(data);
  });
}

io.sockets.on('connection', function (socket) {

  var mqttclient = mqtt.createClient();

  // -----> to rfm12pi 
  socket.on('browser', function (setpoint) {
    mqttclient.publish('browser', ""+setpoint);
  });

  // <----- to browser
  mqttclient.subscribe('emonglcd');
  mqttclient.on('message', function(topic, setpoint) {
    console.log(setpoint); 
    socket.emit('emonglcd',setpoint); 
  });
   
});





