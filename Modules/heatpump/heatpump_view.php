<?php global $path; ?>

<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.time.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/flot/jquery.flot.selection.min.js"></script>

<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/vis/visualisations/vis.helper.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/flot/date.format.js"></script>

<script type="text/javascript" src="<?php echo $path; ?>Modules/heatpump/roundedrect.js"></script>

<script type="text/javascript" src="<?php echo $path; ?>Modules/heatpump/heatpump.js"></script>

<style>
.page {
    width: 1170px;
    height: auto;
    margin: 0px auto;
    text-align: left;
    
    padding:20px;
    margin-top:0px;
}

h3 { 
    color: #0699fa;
    margin:0px;
    padding:0px;
}

hr {
    background: #0699fa;
    border: 0; height: 1px;
    padding: 0px;
}
</style>

    <div class="page">
       
       <h3>MY HEATPUMP</h3>
       
       <div style="position:relative">
       
       <canvas id="heatpump-diagram" width="1200" height="500"></canvas>
       
       <span style="position:absolute; top:65px; left:140px; color:#0699fa">HEATPUMP POWER: <b><span class="heatpump-power"></span>W</b></span>
       <span style="position:absolute; top:140px; left:515px; color:#0699fa">FLOW: <b><span class="heatpump-flow"></span>C</b></span>
       <span style="position:absolute; top:340px; left:515px; color:#0699fa">RETURN: <b><span class="heatpump-return"></span>C</b></span>
       
       <span style="position:absolute; top:420px; left:190px; color:#0699fa">OUTSIDE: <b><span class="heatpump-ambient"></span>C</b></span>
       <span style="position:absolute; top:95px; left:890px; color:#0699fa">ROOM: <b><span class="room-temperature"></span>C</b></span>
       </div>

    </div>
    <div style="background-color:#efefef; border-top: 5px solid #aaa">
    <div class="page">
       <div id="kwhgraph" style="display:none; width:100%">
           <h3>DAILY HEATPUMP ELECTRICITY CONSUMPTION: (kWh)</h3>
           <div id="placeholder_wh_bound" style="width:100%; height:400px; position:relative;">
                <div id="placeholder_wh" style="width:100%; height:400px"></div>
           </div>
           <br><br>
       </div>
       
       <h3>DETAILED VIEW:</h3>
       <div id="placeholder_bound" style="width:100%; height:400px; position:relative; padding-top:40px;">
            <div style="position:absolute; top:0px; left:450px;">
            <button class='btn time' type='button' time='1'>D</button>
            <button class='btn time' type='button' time='7'>W</button>
            <button class='btn time' type='button' time='30'>M</button>
            <button class='btn time' type='button' time='365'>Y</button>
            <button id='zoomin' class='btn' >+</button>
            <button id='zoomout' class='btn' >-</button>
            <button id='left' class='btn' ><</button>
            <button id='right' class='btn' >></button>
            </div>
       
            <span style="position:absolute; top:5px; left:0px;">POWER (Watts)</span>
            <span style="position:absolute; top:5px; right:0px;">Temperature (C)</span>
            <div id="placeholder" style="width:100%; height:400px"></div>
       </div><br>
       <p style="color:#888; text-align:center"><b>Configuration:</b> This dashboard automatically looks for feeds named or containing the words: heatpump_power, heatpump_kwh, heatpump_flow_temp, heatpump_return_temp, ambient_temp and room_temp. If heatpump_kwh is not available it will hide the daily kwh graph.</p>
   </div>
   
   </div>
   
   <script>
   
   var feeds = {};
   $.ajax({                                      
        url: "feed/list.json",
        dataType: 'json',
        async: false,                      
        success: function(data_in) { feeds = data_in; } 
   });
   
   for (z in feeds)
   {
       var name = feeds[z].name.toLowerCase();
       if (name.indexOf("heatpump_power")!=-1) app_heatpump.config.power = feeds[z].id;
       if (name.indexOf("heatpump_kwh")!=-1) app_heatpump.config.kwh = feeds[z].id;
       if (name.indexOf("heatpump_flow_temp")!=-1) app_heatpump.config.flow = feeds[z].id;
       if (name.indexOf("heatpump_return_temp")!=-1) app_heatpump.config.return = feeds[z].id;
       if (name.indexOf("ambient_temp")!=-1) app_heatpump.config.ambient = feeds[z].id;
       if (name.indexOf("room_temp")!=-1) app_heatpump.config.room = feeds[z].id;
   }
   
   app_heatpump.init();
   
   </script>
