
var processlist_ui =
{
    variableprocesslist: [],
    variableid: 0,
    nodeid: 10,
    
    processlist: {
        "log":{ type:"feed", name:"Log to feed", group:"Main", engines:["PHPFINA","PHPTIMESERES"] },
        "whaccumulator":{ type:"feed", name:"Wh Accumulator", group:"Power", engines:["PHPFINA","PHPTIMESERES"] },
        "scale":{ type:"value", name:"x", group:"Calibration" },
        "offset":{ type:"value", name:"+", group:"Calibration" },
        "powertokwh":{ type:"feed", name:"Power to kWh", group:"Power", engines:["PHPFINA","PHPTIMESERES"] },
        "accumulator":{ type:"feed", name: "Accumulator", group: "Power", engines:["PHPFINA","PHPTIMESERIES"] },
        
        "multiplyinput":{ type:"input", name: "x input", group: "Input"},
        "divideinput":{ type:"input", name: "/ input", group: "Input"},
        "addinput":{ type:"input", name: "+ input", group: "Input"},
        "subtractinput":{ type:"input", name: "- input", group: "Input"},

        "resettozero": { type:"none", name: "Reset to zero", group: "Misc"},
        "allowpositive": {type:"none", name:"Allow positive", group: "Misc"},
        "allownegative": {type:"none", name:"Allow negative", group: "Misc"},
    },
    
    feedlist:[],
    inputlist:[],
    
    init: function()
    {
        // --------------------------------------------------------------------------------------------------
        // Order processes by group
        // --------------------------------------------------------------------------------------------------
        var processgroups = [];
        for (z in processlist_ui.processlist)
        {
            var group = processlist_ui.processlist[z].group;
            if (!processgroups[group]) processgroups[group] = {};
            processgroups[group][z] = processlist_ui.processlist[z];
        }

        // --------------------------------------------------------------------------------------------------
        // Build process list dropdown menu
        // --------------------------------------------------------------------------------------------------
        var out = "";
        for (group in processgroups) {
            out += "<optgroup label='"+group+"'>";
            for (process in processgroups[group]) {
                out += "<option value="+process+">"+processgroups[group][process].name+"</option>";
            }
            out += "</optgroup>";
        }
        $("#process-select").html(out);
    
        // --------------------------------------------------------------------------------------------------
        // Fetch feeds and populate feed drop down list
        // --------------------------------------------------------------------------------------------------
        $.ajax({ url: path+"feed/list.json", dataType: 'json', async: true, success: function(result) {
            
            var feeds = {};
            for (z in result) feeds[result[z].id] = result[z];
            
            processlist_ui.feedlist = feeds;
            // Feedlist
            var out = "<option value=-1>CREATE NEW:</option>";
            for (i in processlist_ui.feedlist) {
              out += "<option value="+processlist_ui.feedlist[i].id+">"+processlist_ui.feedlist[i].name+"</option>";
            }
            $("#feed-select").html(out);
        }});
    },
    
    draw_nodes_selector: function()
    {
        var out = "";
 
        for (nodeid in nodes) {
        
            var node = nodeid;
            if (nodes[nodeid].nodename!="") node = nodes[nodeid].nodename;
            
            for (var v=0; v<nodes[nodeid].rx.names.length; v++) {
                var nvar = v;
                if (nodes[nodeid].rx.names[v]!=undefined) nvar = nodes[nodeid].rx.names[v];
                out += "<option value="+nodeid+"/rx/"+v+">"+node+"/rx/"+nvar+"</option>";
            }
            
            for (var v=0; v<nodes[nodeid].tx.names.length; v++) {
                var nvar = v;
                if (nodes[nodeid].tx.names[v]!=undefined) nvar = nodes[nodeid].tx.names[v];
                out += "<option value="+nodeid+"/tx/"+v+">"+node+"/tx/"+nvar+"</option>";
            } 
        }
        $("#input-select").html(out);
    },

    'draw':function()
    {
        var i = 0;
        var out="";
        
        if (this.variableprocesslist.length==0) {
            out += "<tr class='alert'><td></td><td></td><td><b>You have no processes defined</b></td><td></td><td></td><td></td></tr>";
        } else {
        
            for (processid in this.variableprocesslist)
            { 
                var processkey = this.variableprocesslist[processid].key;
                if (this.processlist[processkey]!=undefined)
                {
                    out += '<tr>';
                    out += '<td>';
                    if (processid > 0) {
                        out += '<a class="move-process" href="#" title="Move up" processid='+processid+' moveby=-1 ><i class="icon-arrow-up"></i></a>';
                    }
                    if (processid < this.variableprocesslist.length-1) {
                        out += '<a class="move-process" href="#" title="Move up" processid='+processid+' moveby=1 ><i class="icon-arrow-down"></i></a>';
                    }
                    out += '</td>';

                    // Process name and argument
                    var arg = "";
                    var lastvalue = "";
                    
                    if (this.processlist[processkey].type=="input") {
                        var nodevarstr = this.variableprocesslist[processid].nodevar;
                        var nodevar = nodevarstr.split("/");
                        
                        var nodeid = nodevar[0]
                        var rxtx = nodevar[1];
                        var vid = nodevar[2];
                        
                        var node = nodeid;
                        if (nodes[nodeid].nodename!="") node = nodes[nodeid].nodename;
                        var nvar = vid;
                        if (nodes[nodeid][rxtx].names[vid]!=undefined) nvar = nodes[nodeid][rxtx].names[vid];
                        
                        arg = node+"/tx/"+nvar;
                        lastvalue = 0;
                        if (nodes[nodeid][rxtx].values!=undefined && nodes[nodeid][rxtx].values[vid]!=undefined) lastvalue = nodes[nodeid][rxtx].values[vid];
                    }
                    
                    if (this.processlist[processkey].type=="feed") {
                        var feedid = this.variableprocesslist[processid].feedid;

                        if (processlist_ui.feedlist[feedid]!=undefined) {
                            arg = "<a class='label label-info' href='"+path+"vis/auto?feedid="+feedid+"'>";
                            // if (processlist_ui.feedlist[feedid].tag) arg += processlist_ui.feedlist[feedid].tag+": ";
                            arg += processlist_ui.feedlist[feedid].name;
                            arg += "</a>";
                            lastvalue = "<span style='color:#888; font-size:12px'>(feedvalue:"+(processlist_ui.feedlist[feedid].value*1).toFixed(2)+")</span>";
                        } else {
                            arg = "missing feed id:"+feedid;
                        }
                    }
                    
                    
                    if (this.processlist[processkey].type=="value") {
                        arg = this.variableprocesslist[processid].value;
                    }
                    
                    out += "<td>"+(parseInt(processid)+1)+"</td><td>"+this.processlist[processkey].name+"</td><td>"+arg+"</td><td>"+lastvalue+"</td>";
                    
                    // Delete process button (icon)
                    out += '<td><a href="#" class="delete-process" title="Delete" processid='+processid+'><i class="icon-trash"></i></a></td>';

                    out += '</tr>';
                }
            }
        }
        $('#variableprocesslist').html(out);
    },


    'events':function()
    {
        $("#processlist-ui #feed-engine").change(function(){
            var engine = $(this).val();
            $("#feed-interval").hide();
            if (engine=="PHPFINA") $("#feed-interval").show();
        });

        $('#processlist-ui #process-add').click(function() 
        {
            var processkey = $('#process-select').val();
            var process = processlist_ui.processlist[processkey];

            if (process.type=="none") {
                processlist_ui.variableprocesslist.push({key:processkey});
            }
                        
            if (process.type=="value") {
                var value = $("#value-input").val();
                processlist_ui.variableprocesslist.push({key:processkey,value:value});
            }
            
            if (process.type=="input") {
                var nodevar = $("#input-select").val();
                processlist_ui.variableprocesslist.push({key:processkey,nodevar:nodevar});
            }
            
            if (process.type=="feed") {
                var feedid = $("#feed-select").val();
                
                if (feedid==-1) {
                    var feedname = $('#feed-name').val();
                    var feedtag = $('#feed-tag').val();
                    var engine = $('#feed-engine').val();
                    var interval = $('#feed-interval').val();
                    
                    if (feedname == '') {
                        alert('ERROR: Please enter a feed name');
                        return false;
                    }
                    var datatype = 1;
                    var engine = 5;
                    
                    var result = {};
                    
                    $.ajax({ 
                        url: path+"feed/create.json", 
                        data: "name="+feedname+"&datatype="+datatype+"&engine="+engine+"&options="+JSON.stringify({interval:interval}), 
                        dataType: 'text', 
                        async: false, 
                        success: function(data){
                        
                            result = JSON.parse(data);
                            
                            if (result.feedid==undefined && result.success==undefined) {
                                alert('ERROR: Feed could not be created, '+data);
                            }
                            
                            if (result.success!=undefined && result.success==false) {
                                alert('ERROR: Feed could not be created, '+result.message);
                            }
                            
                        }
                    });
                    
                    feedid = result.feedid;
                    processlist_ui.feedlist[feedid] = {'id':feedid, 'name':feedname, 'tag':"", 'value':''};
                    
                    // ---------------
                    // Redraw feedlist
                    // ---------------
                    var out = "<option value=-1>CREATE NEW:</option>";
                    for (i in processlist_ui.feedlist) {
                        out += "<option value="+processlist_ui.feedlist[i].id+">"+processlist_ui.feedlist[i].name+"</option>";
                    }
                    $("#feed-select").html(out);
                }
                
                processlist_ui.variableprocesslist.push({key:processkey,feedid:feedid});
            }
            
            processlist_ui.draw();
            
            var nodeid = processlist_ui.nodeid;
            var rxtx = processlist_ui.rxtx;
            var vid = processlist_ui.vid;
            
            $.ajax({ 
                url: path+"nodes/set/"+nodeid+"/"+rxtx+"/"+vid+"/processlist",
                data: "val="+JSON.stringify(processlist_ui.variableprocesslist), 
                dataType: 'text', async: false, 
                success: function(data){
                    console.log(data);
                }
            });
        });
        
        $('#processlist-ui #process-select').change(function() {
            var processkey = $(this).val();
            
            $("#description").html("");
            $("#type-value").hide();
            $("#type-input").hide();
            $("#type-feed").hide();
            
            if (processlist_ui.processlist[processkey].type=="value") $("#type-value").show();
            if (processlist_ui.processlist[processkey].type=="input") $("#type-input").show();
            if (processlist_ui.processlist[processkey].type=="feed") 
            {
                $("#type-feed").show();
                processlist_ui.showfeedoptions(processkey);
            }
            // $("#description").html(process_info[processid]);
        });

        $('#processlist-ui #feed-select').change(function() {
            var feedid = $("#feed-select").val();
            
            if (feedid!=-1) {
                $("#feed-name").hide();
                $("#feed-interval").hide();
                $("#feed-engine").hide();
                $(".feed-engine-label").hide();
            } else {
                $("#feed-name").show();
                $("#feed-interval").show();   
                $("#feed-engine").show();
                $(".feed-engine-label").show();
            }
        });

        $('#processlist-ui .table').on('click', '.delete-process', function() {
            processlist_ui.variableprocesslist.splice($(this).attr('processid'),1);
            
            var processid = $(this).attr('processid')*1;
            processlist_ui.draw();
            
            $.ajax({ 
                url: path+"nodes/set/10/rx/0/processlist", 
                data: "val="+JSON.stringify(processlist_ui.variableprocesslist), 
                dataType: 'text', async: false, 
                success: function(data){
                    console.log(data);
                }
            });
        });

        $('#processlist-ui .table').on('click', '.move-process', function() {

            var processid = $(this).attr('processid')*1;
            var curpos = parseInt(processid);
            var moveby = parseInt($(this).attr('moveby'));
            var newpos = curpos + moveby;
            if (newpos>=0 && newpos<processlist_ui.variableprocesslist.length)
            { 
                processlist_ui.variableprocesslist = processlist_ui.array_move(processlist_ui.variableprocesslist,curpos,newpos);
                processlist_ui.draw();
            }
            
            $.ajax({ 
                url: path+"nodes/set/10/rx/0/processlist", 
                data: "val="+JSON.stringify(processlist_ui.variableprocesslist), 
                dataType: 'text', async: false, 
                success: function(data){
                    console.log(data);
                }
            });
        });
    },
    
    'showfeedoptions':function(processkey)
    {
        var engines = processlist_ui.processlist[processkey].engines;   // 5:PHPFINA, 6:PHPFIWA

        // Start by hiding all feed engine options
        $("#feed-engine option").hide();

        // Show only the feed engine options that are available
        for (engine in engines) $("#feed-engine option[value="+engine+"]").show(); 

        // Select the first feed engine in the engines array by default
        $("#feed-engine").val(engines[0]);

        // If there's only one feed engine to choose from then dont show feed engine selector
        if (engines.length==1) {
            $("#feed-engine, .feed-engine-label").hide(); 
        } else {
            $("#feed-engine, .feed-engine-label").show();
        }

        $("#feed-interval").show();
        $("#feed-interval").val(10);
    },

    'array_move':function(array,old_index, new_index) 
    {
        if (new_index >= array.length) {
            var k = new_index - array.length;
            while ((k--) + 1) {
                array.push(undefined);
            }
        }
        array.splice(new_index, 0, array.splice(old_index, 1)[0]);
        return array; // for testing purposes
    },
    
    'drawinline': function (variableprocesslist) { 

      if (!variableprocesslist) return "";
      
      var out = "";
    
      for (var processid in variableprocesslist)
      {
        var processkey = variableprocesslist[processid].key;
        var type = this.processlist[processkey].type;
        var color = "";
        if (type=='value') color = 'important';
        if (type=='input') color = 'warning';
        if (type=='feed') color = 'info';
        
        if (type=='feed') { 
          var feedid = variableprocesslist[processid].feedid;
          out += "<a href='"+path+"vis/auto?feedid="+feedid+"'<span class='label label-"+color+"' title='"+type+":"+feedid+"' style='cursor:pointer'>"+processkey+"</span></a> "; 
        } else {
          out += "<span class='label label-"+color+"' title='"+type+"' style='cursor:default'>"+processkey+"</span> ";
        }
        
      }
      
      return out;
    }    
    
}
