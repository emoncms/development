// Read from serial with data coming from RFM12PI with RFM12_Demo sketch 
// All Emoncms code is released under the GNU Affero General Public License.
var mqtt = require('mqtt');

var serialport = require("serialport");
var SerialPort = serialport.SerialPort;

var sp = new SerialPort("/dev/ttyUSB0", { 
  parser: serialport.parsers.readline("\n") 
});

sp.on("open", function () {

  var mqttclient = mqtt.createClient();
  mqttclient.subscribe('browser');
  
  // <---- send emonglcd data to app
  sp.on('data', function(data) {
  
    if (data[0]=='O' && data[1]=='K')
    {
      var parts = data.split(' ');
      var nodeid = parseInt(parts[1]);
      var values = [];

      for(var i=2; i<((parts.length)-1); i+=2) {
        var value = parseInt(parts[i]) + parseInt(parts[i+1])*256;
        if (value>32768) value += -65536;
        values.push(value);
      }
      
      mqttclient.publish('emonglcd',""+values[0]);
      console.log("RX: "+values[0]);
    }
  });

  // ----> send browser data to emonglcd
  mqttclient.on('message', function(topic, message) {
  
    var val = parseInt(message);
    var p2 = val >> 8;
    var p1 = val - (p2<<8);
    if (p2<0) p2 = 256 + p2;
    var str = p1+","+p2+",";
    
    sp.write(str+"s\n", function(err, results) {});
    console.log("TX: "+str+"s");
  });
  
});
