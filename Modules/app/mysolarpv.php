<?php global $path; ?>

<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.time.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/flot/jquery.flot.selection.min.js"></script>

<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/vis/visualisations/vis.helper.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/flot/date.format.js"></script>

<script type="text/javascript" src="<?php echo $path; ?>Modules/app/mysolarpv.js"></script>

<style>

.page {
    width: 1170px;
    height: auto;
    margin: 0px auto;
    text-align: left;
    
    padding:20px;
    margin-top:0px;
}

  .electric-title {
    font-weight:bold; 
    font-size:22px; 
    color:#aaa; 
    padding-top:20px
  }
  
  .power-value {
    font-weight:bold; 
    font-size:100px; 
    color:#0699fa; 
    padding-top:45px;
    padding-bottom:30px;
  }
  
  .kwh-value {
    font-weight:normal; 
    font-size:22px; 
    color:#0699fa; 
    padding-top:15px;
  }
  
  .midtext {
    font-weight:normal; 
    font-size:22px; 
    color:#0699fa;
    padding-top:15px;
  }
  
  .visnavblock {
    color:#0699fa;
    font-size:18px;
  }
  
  .visnav {

  }
  
  .visnav:hover {
    color:#0699fa;
    cursor: pointer;
  }
  
</style>

<div class="page">
<div class="block">

    <table style="width:100%">
    <tr>
        <td>
            <div class="electric-title">POWER NOW</div>
            <div class="power-value"><span id="usenow">0</span>W</div>
        </td>
        <td style="text-align:left">
            <div class="electric-title">BALANCE</div>
            <div class="midtext"><span id="balance"></span></div>
        </td>
        <td style="text-align:right">
            <div class="electric-title">SOLAR PV</div>
            <div class="power-value" style="color:#dccc1f"><span id="solarnow">0</span>W</div>
        </td>
        
    </tr>
    </table>
    <br>

    <div class="visnavblock" style="padding-bottom:5px;">
        <b>VIEW</b> | 
        <span class='visnav time' time='1'>D</span> | 
        <span class='visnav time' time='7'>W</span> | 
        <span class='visnav time' time='30'>M</span> | 
        <span class='visnav time' time='365'>Y</span> | 
        <span id='mysolarpv_zoomin' class='visnav' >+</span> | 
        <span id='mysolarpv_zoomout' class='visnav' >-</span> | 
        <span id='mysolarpv_left' class='visnav' ><</span> | 
        <span id='mysolarpv_right' class='visnav' >></span>
        
        <span id='balanceline' class='visnav' style="float:right">Show energy balance</span>
    </div>

    <div id="mysolarpv_placeholder_bound" style="width:100%; height:500px;">
        <div id="mysolarpv_placeholder"></div>
    </div>
    
    <br><br>
    <table style="width:100%">
    
    <tr style="border-bottom: 1px solid #0699fa; border-top: 1px solid #0699fa;">
        <td><div class="midtext" style="padding-bottom:15px">In this window:</div></td><td></td>
    </tr>
    <tr>
    
        <td>
            <div class="midtext">House use: <b><span id="total_use_kwh">0</span>kWh</b></div>
            <div class="midtext">Solar generation: <b><span id="total_solar_kwh">0</span>kWh</b></div>
        </td>
    
        <td>
            <div class="midtext">Supplied direct from solar: <b><span id="total_use_direct_kwh">0</span></b></div>
            <div class="midtext">Supplied via grid: <b><span id="total_use_via_store_kwh">0</span></b></div>
        </td>
    
    </tr>
    </table>

    <!--
    <br><br>
    <div id="mysolarpv_bargraph_bound" style="width:100%; height:500px;">
        <div id="mysolarpv_bargraph" style="height:500px"></div>
    </div>
    
    -->
<br><br>
<p style="color:#888; text-align:center"><b>Configuration:</b> This dashboard automatically looks for feeds named or containing the words: solar_power, house_power. To use this dashboard add these names to the relevant feeds.</p>
</div>
</div>

<script>
    var path = "<?php echo $path; ?>";

    var feeds = {};
    $.ajax({                                      
        url: path+"feed/list.json",
        dataType: 'json',
        async: false,                      
        success: function(data_in) { feeds = data_in; } 
    });
    
    for (z in feeds)
    {
       var name = feeds[z].name.toLowerCase();
       if (name.indexOf("solar_power")!=-1) app_mysolarpv.config.solarpower = feeds[z].id;
       if (name.indexOf("house_power")!=-1) app_mysolarpv.config.housepower = feeds[z].id;
    }

    app_mysolarpv.init();
    app_mysolarpv.show();
</script>
