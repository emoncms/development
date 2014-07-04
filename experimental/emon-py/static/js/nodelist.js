
var nodes = {};

setInterval(update,5000);

update();

function update()
{
    $.ajax({                                      
        url: 'nodes',
        dataType: 'json',
        async: false,                
        success: function(result) { nodes = result; } 
    });
    
    var out = "";
    for (n in nodes)
    {
        out += "<tr class=dark><td><b>Node: "+n+"</b></td><td></td><td></td></tr>";

        for (v in nodes[n].variables)
        {
            var variable = nodes[n].variables[v];
            
            var name = "Variable "+v;
            if (variable.name!=undefined) name = variable.name;
            
            var value = "";
            if (variable.value!=undefined) value = variable.value;
            
            var units = "";
            if (variable.units!=undefined) units = variable.units;
            
            vid = 1*v + 1
            out += "<tr><td></td><td><a href='graph?nid="+n+"&vid="+vid+"'>"+name+"</a></td><td>"+value+" "+units+"</td></tr>";
        }
    }
    
    $("#nodes").html(out);
}
    
