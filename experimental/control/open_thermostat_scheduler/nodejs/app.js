var settings = {
    username: "demo",
    password: "demo"
};

var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);

var bodyParser = require('body-parser'); // for reading POSTed form data into `req.body`
var expressSession = require('express-session');
var cookieParser = require('cookie-parser'); // the session is stored in a cookie, so we use this to parse it

var mqtt = require('mqtt')
// var mqttclient = mqtt.createClient();  
// mqttclient.subscribe('rx/#');

var redis = require("redis"),
        redisclient = redis.createClient();

// must use cookieParser before expressSession
app.use(cookieParser());

app.use(expressSession({
    secret:'a',
    resave: false,
    saveUninitialized: true
}));

var jsonParser = bodyParser.json()
var rawParser = bodyParser.raw()
var textParser = bodyParser.text()
var urlencodedParser = bodyParser.urlencoded({ extended: false })

app.get('/', function(req, res){
  if (!req.session.username) { 
      res.redirect('/login');
  } else {
      res.sendFile(__dirname + '/heating.html');
  }
});

app.get('/test', function(req, res){
    res.sendFile(__dirname + '/heating.html');
});

app.get('/jquery-1.9.0.min.js', function(req, res){
    res.sendFile(__dirname + '/jquery-1.9.0.min.js');
});

app.get('/api/*', function(req, res){
    res.set('Content-Type', 'text/plain');
    if (!req.session.username) {
        res.send("Please login to access content");
    } else {
        var key = req.params[0];
        redisclient.get(key, function (err, reply) {
            res.send(reply);
        });
    }
});

app.post('/api/*', textParser, function(req, res){
    if (!req.session.username) {
        res.send("Please login to set content");
    } else {
        var key = req.params[0];
        redisclient.set(key,req.body);
        
        var mqttclient = mqtt.createClient();
        mqttclient.publish(key,req.body);
        mqttclient.end();
        
        res.send("ok");
    }
});

app.get('/login', function(req, res){
    res.sendFile(__dirname + '/login.html');
});

app.post('/login', urlencodedParser, function(req, res){
  if (req.body.username == settings.username && req.body.password == settings.password) {
      req.session.username = settings.username;
  } else {
      req.session.destroy(function(err) {
          // cannot access session here
      });
  }
  res.redirect('/');
});

app.get('/logout', function(req, res){
  req.session.destroy(function(err) {
    // cannot access session here
  })
  res.redirect('/');
});

io.on('connection', function(socket){
  console.log("user connected");
  // console.log(socket.request.headers.cookie);
  
  var mqttclient = mqtt.createClient();
  
  mqttclient.subscribe('rx/#');
  mqttclient.on('message', function(topic, payload) {
    socket.emit("server",{topic:topic,payload:payload});
  });
  
  socket.on('client', function (msg) {
    redisclient.set(msg.topic,msg.payload);
    mqttclient.publish(msg.topic,""+msg.payload);
  });
  
  socket.on('disconnect', function () {
    mqttclient.end();
    console.log("user disconnected");
  });
  
});



http.listen(3000, function(){
  console.log('listening on *:3000');
});
