
var app_nodes = {
    // note variable nodes is global!

    selected_node: 0,
    // updater: false,

    init: function()
    {   
        $("#node-nav").on("click","li",function() {
            var nid = $(this).attr("nid");
            app_nodes.selected_node = nid;
            $(".node").hide();
            $(".node[nid="+nid+"]").show();
        });
        /*
        $("#node-content").on("click",".node-key",function() {
            var nodeid = $(this).parent().parent().attr("nid");
            var value = $(this).html();
            console.log("node key click "+nodeid);
            $(this).html("<input class='vedit' type='text' value='"+value+"' />");
        });
        
        $("#node-content").on("change",".vedit",function() {
            console.log("value changed");
            console.log($(this).value());
        });*/
    },
    
    show: function() {
        
    },
    
    hide: function() {
        // clearInterval(app_nodes.updater);
    },

    draw_nodes: function ()
    {
        $("#node-nav").html("");
        $("#node-content").html("");
        var template_node = $("#template-node").html();
        var template_variable = $("#template-variable").html();
        
        for (z in nodes)
        {
            // node entry in left hand side navigation list
            $("#node-nav").append("<li nid="+z+"><a>"+z+": "+nodes[z].nodename+"</a></li>");
            
            // make a copy of node info & variables block template, set its nodeid
            var display = "";
            if (app_nodes.selected_node!=z) display = "display:none";
            $("#node-content").append("<div class='node' nid="+z+" style='"+display+"'>"+template_node+"</div>");
            
            // select the copied block
            var node = $(".node[nid="+z+"]");
            node.find(".node-id").html(z);              // set the nodeid
            node.find(".node-key").html(nodes[z].nodename);
            node.find(".node-firmware").html(nodes[z].firmware);
            node.find(".node-hardware").html(nodes[z].hardware);
            
            var modes = ["rx","tx"];
            for (i in modes)
            {   
                var rxtx = modes[i];
                
                // TX Variables
                if (nodes[z][rxtx]!=undefined)
                {
                    var varnum = 0;
                    if (nodes[z][rxtx].names!=undefined && nodes[z][rxtx].names.length>varnum) varnum = nodes[z][rxtx].names.length;
                    if (nodes[z][rxtx].values!=undefined && nodes[z][rxtx].values.length>varnum) varnum = nodes[z][rxtx].values.length;
                    if (nodes[z][rxtx].units!=undefined &&nodes[z][rxtx].units.length>varnum) varnum = nodes[z][rxtx].units.length;

                    var variables = "";
                    for (var v=0; v<varnum; v++) {
                        variables += "<tr vid="+v+">"+template_variable+"</tr>";
                    }
                    var tbody = node.find("."+rxtx+"-variables");
                    tbody.html(variables);
                    tbody.attr("rxtx",rxtx);
                    
                    for (var v=0; v<varnum; v++) {
                        var row = tbody.find("tr[vid="+v+"]");
                        row.find("td[key=variable-id]").html(v);
                        if (nodes[z][rxtx].names) row.find("td[key=variable-name]").html(nodes[z][rxtx].names[v]);
                        if (nodes[z][rxtx].values!=undefined && v<nodes[z][rxtx].values.length) row.find("span[key=variable-value]").html(nodes[z][rxtx].values[v]);
                        if (nodes[z][rxtx].units) row.find("span[key=variable-unit]").html(nodes[z][rxtx].units[v]);
                        row.find("td[key=variable-time]").html(app_nodes.list_format_updated(nodes[z][rxtx].time));
                    }
                }
            }
        }
    },

    update: function ()
    {  

    },

    list_format_updated: function (time)
    {
      time = time * 1000;
      var now = (new Date()).getTime();
      var update = (new Date(time)).getTime();
      var lastupdate = (now-update)/1000;

      var secs = (now-update)/1000;
      var mins = secs/60;
      var hour = secs/3600

      var updated = secs.toFixed(0)+"s ago";
      if (secs>180) updated = mins.toFixed(0)+" mins ago";
      if (secs>(3600*2)) updated = hour.toFixed(0)+" hours ago";
      if (hour>24) updated = "inactive";

      var color = "rgb(255,125,20)";
      if (secs<25) color = "rgb(50,200,50)"
      else if (secs<60) color = "rgb(240,180,20)"; 

      return "<span style='color:"+color+";'>"+updated+"</span>";
    }
}
