// Read from serial with data coming from RFM12PI with RFM12_Demo sketch 
// All Emoncms code is released under the GNU Affero General Public License.

var serialport = require("serialport");
var SerialPort = serialport.SerialPort; // localize object constructor

var sp = new SerialPort("/dev/ttyAMA0", { 
  parser: serialport.parsers.readline("\n") 
});

sp.on("open", function () {

  sp.on('data', function(data) {
    console.log('DATA RX: ' + data);
    var values = data.split(' ');
    var nodeid = parseInt(values[1]);

    if (!isNaN(nodeid)) 
    {
      var nameid = 1;

      for(var i=2; i<((values.length)-1); i+=2)
      {
        // Get 16-bit integer
        var value = parseInt(values[i]) + parseInt(values[i+1])*256;
        if (value>32768) value += -65536;

        console.log(nodeid+" "+nameid+" "+value);
        nameid++;
      }
    }
  });  

  sp.write("ls\n", function(err, results) {
    console.log('err ' + err);
    console.log('results ' + results);
  });  
});
