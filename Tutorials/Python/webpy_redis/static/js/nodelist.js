
var nodelist = {};
$.ajax({ url: "nodes", dataType: 'json', async: false, success: function(data) {nodelist = data;} });

var out = "";
for (z in nodelist) {

  var parts = nodelist[z].data.split(',');
  out += "<tr><td>"+nodelist[z].id+"</td><td>"+nodelist[z].data+"</td><td>"+parts.length+" bytes</td></tr>";
}
$("#nodelist").html(out);
