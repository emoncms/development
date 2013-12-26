// All Emoncms code is released under the GNU Affero General Public License.

var serialport = require("serialport");
var SerialPort = serialport.SerialPort; // localize object constructor

var userid = 2;

var mysql = require('mysql');
var connection = mysql.createConnection({host:'localhost',user:'root',password:'raspberry',database:'emoncms'});

connection.connect(function(err) {
  console.log("mysql connected");
});

var dbinputs = {};

connection.query("SELECT id,nodeid,name,processList,record FROM input WHERE `userid` = '"+userid+"'", function(err, rows) 
{
  for (z in rows)
  {
    if (rows[z].nodeid==null) rows[z].nodeid = 0;
    if (dbinputs[rows[z].nodeid]==undefined) dbinputs[rows[z].nodeid] = {};
    dbinputs[rows[z].nodeid][rows[z].name] = {'id':rows[z].id, 'processList':rows[z].processList, 'record':rows[z].record};
  }
  //console.log(dbinputs);
});

var sp = new SerialPort("/dev/ttyAMA0", { 
  parser: serialport.parsers.readline("\n") 
});

sp.on("open", function () {
  console.log('Serial Port Open');

  sp.on('data', function(data) {

    console.log('DATA RX: ' + data);

    var values = data.split(' ');
    var nodeid = parseInt(values[1]);

    if (!isNaN(nodeid)) 
    {
      var nameid = 1;
      //unixtime = time.time()

      var d = new Date();
      var time = parseInt(d.getTime()/1000.0);
      var updatetime = d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate()+" "+d.getHours()+':'+d.getMinutes()+':'+d.getSeconds();

      var test = []

      for(var i=2; i<((values.length)-1); i+=2)
      {
        // Get 16-bit integer
        var value = parseInt(values[i]) + parseInt(values[i+1])*256;
        if (value>32768) value += -65536;

        if (dbinputs[nodeid]==undefined) dbinputs[nodeid] = {};

        if (dbinputs[nodeid][nameid]==undefined) {
          console.log("Create input");
          connection.query("INSERT INTO input (userid,name,nodeid) VALUES ('"+userid+"','"+nameid+"','"+nodeid+"')", function(err, rows) {});
          dbinputs[nodeid][nameid] = true;
        } else { 
          //$input->set_timevalue($dbinputs[$nodeid][$name]['id'],$time,$value);
          if (dbinputs[nodeid][nameid]['processList']) test.push({'value':value,'processList':dbinputs[nodeid][nameid]['processList']});
        }
        //console.log(nodeid+" "+nameid+" "+value+" "+dbinputs[nodeid][nameid]['processList']);
        nameid++;
      }

      for (z in test) 
      {
        var processList = test[z]['processList'];
        var value = test[z]['value'];

        // 1. For each item in the process list
        var pairs = processList.split(',');
        for (i in pairs)    			        
        {
          var inputprocess = pairs[i].split(':');	       	            // Divide into process id and arg
          var processid = parseInt(inputprocess[0]);						      // Process id
          var arg =       parseInt(inputprocess[1]);						      // Process Arg

          if (processid==2) value = value * arg;                      // scale

          if (processid==1)
          {
            feedid = arg;
            feedname = "feed_"+feedid+"";

            // a. Insert data value in feed table
            connection.query("INSERT INTO "+feedname+" (`time`,`data`) VALUES ('"+time+"','"+value+"')", function(err, rows) {});

            // b. Update feeds table
            connection.query("UPDATE feeds SET value = '"+value+"', time = '"+updatetime+"' WHERE id='"+feedid+"'", function(err, rows) {});

            //console.log("UPDATE feeds SET value = '"+value+"', time = '"+updatetime+"' WHERE id='"+feedid+"'\n");
          }
        }
      }
    }
  });  

  sp.write("ls\n", function(err, results) {
    console.log('err ' + err);
    console.log('results ' + results);
  });  
});
