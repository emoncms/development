<?php global $path; ?>
    <div id="processlist-ui" style="padding:20px; background-color:#efefef; display:none">
    <div class="container">
    
    <div style="font-size:30px; padding-bottom:20px; padding-top:18px"><b><span id="inputname"></span></b> config</div>
    <p><?php echo _('Input processes are executed sequentially with the result being passed back for further processing by the next processor in the input processing list.'); ?></p>
    
        <table class="table">

            <tr>
                <th style='width:5%;'></th>
                <th style='width:5%;'><?php echo _('Order'); ?></th>
                <th><?php echo _('Process'); ?></th>
                <th><?php echo _('Arg'); ?></th>
                <th></th>
                <th><?php echo _('Actions'); ?></th>
            </tr>

            <tbody id="variableprocesslist"></tbody>

        </table>

        <table class="table">
        <tr><th>Add process:</th><tr>
        <tr>
            <td>
                <div class="input-prepend input-append">
                    <select id="process-select">
                    </select>

                    <span id="type-value" style="display:none">
                        <input type="text" id="value-input" style="width:125px" />
                    </span>

                    <span id="type-input" style="display:none">
                        <select id="input-select" style="width:180px;"></select>
                    </span>

                    <span id="type-feed">        
                        <select id="feed-select" style="width:140px;"></select>
                        
                        <input type="text" id="feed-name" style="width:150px;" placeholder="Feed name..." />
                        <input type="hidden" id="feed-tag"/>

                        <span class="add-on feed-engine-label">Feed engine: </span>
                        <select id="feed-engine">
                        <option value="PHPFINA" >Fixed Interval No Averaging (PHPFINA)</option>
                        <option value="PHPTIMESERIES" >Variable Interval No Averaging (PHPTIMESERIES)</option>
                        </select>


                        <select id="feed-interval" style="width:130px">
                            <option value="">Select interval</option>
                            <option value=5>5s</option>
                            <option value=10 selected>10s</option>
                            <option value=15>15s</option>
                            <option value=20>20s</option>
                            <option value=30>30s</option>
                            <option value=60>60s</option>
                            <option value=120>2 mins</option>
                            <option value=300>5 mins</option>
                            <option value=600>10 mins</option>
                            <option value=1200>20 mins</option>
                            <option value=1800>30 mins</option>
                            <option value=3600>1 hour</option>
                        </select>
                        
                    </span>
                    <button id="process-add" class="btn btn-info"><?php echo _('Add'); ?></button>
                </div>
            </td>
        </tr>
        <tr>
          <td id="description"></td>
        </tr>
        </table>
    </div>
    </div>
<br>
<div class="container">
<div class="span3 nodes-nodelist">
    <h3>Nodes</h3>
    <ul id="node-nav" class="nav nav-tabs nav-stacked"></ul>
    <p><a href="apidocs">API Documentation</a></p>

</div>

<div class="span8 nodes-rightpane">   
    <div id="template-node" style="display:none"> 
        
        <h3>Name: <span class="node-key">undefined</span></h3>
        
        <p><b>Node id:</b> <span class="node-id"></span></p>
        <p><b>Node key:</b> <span class="node-key">undefined</span></p>
        <p><b>Node hardware:</b> <span class="node-hardware">undefined</span></p>
        <p><b>Node firmware:</b> <span class="node-firmware">undefined</span></p>
        <br>
        <h4>Rx Variables</h4>
           
        <table class="table table-striped">
                         
            <tr>
                <th>Rx ID</th>
                <th>Name</th>
                <th>Last updated</th>
                <th>Value</th>
                <th></th>
            </tr>
            
            <tbody class="rx-variables"></tbody>
            
            <tr>
                <th>Tx ID</th>
                <th>Name</th>
                <th>Last updated</th>
                <th>Value</th>
                <th></th>
            </tr>
            
            <tbody class="tx-variables"></tbody>
            
            <tr id="template-variable" style="display:none">
                <td key="variable-id"></td>
                <td key="variable-name"></td>
                <td key="variable-time"></td>
                <td>
                    <span key="variable-value"></span>
                    <span key="variable-unit"></span>
                </td>
                <td><i class="icon-wrench" style="cursor:pointer"></i></td>
            </tr>
        
        </table>
        
    </div>
    
   <div id="node-content"></div>
</div>
</div>

<script type="text/javascript" src="<?php echo $path; ?>Modules/nodes/nodes.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/nodes/processlist.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/nodes/process_info.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/feed/feed.js"></script>

<script>

    var path = "<?php echo $path; ?>";
    var nodes = {};
    
    app_nodes.init();
    app_nodes.show();
    
    processlist_ui.init();
    processlist_ui.events();
    
    setInterval(update_nodes,5000);
    
    $("#node-content").on('click', '.icon-wrench', function() {
    
        var row = $(this).parent().parent();
        var tbody = row.parent();
        var nodediv = tbody.parent().parent();
        
        var nodeid = nodediv.attr("nid");
        var vid = row.attr("vid");
        var rxtx = tbody.attr("rxtx");
        
        console.log(nodeid+"/"+rxtx+"/"+vid);
        console.log(nodes);
        
        var processlist = [];
        if (nodes[nodeid][rxtx].processlists.length>vid) {
            processlist = nodes[nodeid][rxtx].processlists[vid];
        }
        
        processlist_ui.nodeid = nodeid;
        processlist_ui.vid = vid;
        processlist_ui.rxtx = rxtx;
        processlist_ui.variableprocesslist = processlist;
        
        processlist_ui.draw();

        var node = nodeid;
        if (nodes[nodeid].nodename!="") node = nodes[nodeid].nodename;
        var nvar = vid;
        if (nodes[nodeid][rxtx].names[vid]!=undefined) nvar = nodes[nodeid][rxtx].names[vid];
        $("#inputname").html(node+"/"+rxtx+"/"+nvar);
        $("#processlist-ui").show();
        window.scrollTo(0,0);
    });

    $.ajax({ 
        url: path+"nodes", 
        dataType: 'json', 
        async: true, 
        success: function(data) {
            nodes = data;
            // auto select first node
            for (var key in nodes) {
                app_nodes.selected_node = key;
                break;
            }
            app_nodes.draw_nodes();
            processlist_ui.draw_nodes_selector();
        }
    });
        
    function update_nodes()
    {
        $.ajax({ 
            url: path+"nodes", 
            dataType: 'json', 
            async: true, 
            success: function(data) {
                nodes = data;
                app_nodes.draw_nodes();
            }
        });
    }

</script>
