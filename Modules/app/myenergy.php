<?php global $path; ?>

<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.time.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/flot/jquery.flot.selection.min.js"></script>

<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/vis/visualisations/vis.helper.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/flot/date.format.js"></script>

<script type="text/javascript" src="<?php echo $path; ?>Modules/app/myenergy.js"></script>

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

    <table class="table">
    <tr>
        <td>
            <div class="electric-title">POWER NOW</div>
            <div class="power-value"><span id="usenow">0</span>W</div>
        </td>
        <td style="text-align:left">
            <div class="electric-title">VIRTUAL STORE</div>
            <div class="midtext">CHARGE RATE:<b> <span id="chargerate">0</span>W</b></div>
        </td>
        <td style="text-align:right">
            <div class="electric-title">RENEWABLE GENERATION</div>
            <div class="power-value"><span id="totalgen">0</span>W</div>
            <div class="midtext" style="color:#dccc1f">Onsite solar: <b><span id="solarnow">0</span>W</b></div>
            <!--<div class="midtext" style="color:#2ed52e">Wind from grid: <b><span id="gridwindnow">0</span>W</b></div>-->
        </td>
        
    </tr>
    </table>

    <div class="visnavblock" style="padding-bottom:5px;">
        <b>VIEW</b> | 
        <span class='visnav time' time='1'>D</span> | 
        <span class='visnav time' time='7'>W</span> | 
        <span class='visnav time' time='30'>M</span> | 
        <span class='visnav time' time='365'>Y</span> | 
        <span id='myenergy_zoomin' class='visnav' >+</span> | 
        <span id='myenergy_zoomout' class='visnav' >-</span> | 
        <span id='myenergy_left' class='visnav' ><</span> | 
        <span id='myenergy_right' class='visnav' >></span>
    </div>

    <div id="myenergy_placeholder_bound" style="width:100%; height:500px;">
        <div id="myenergy_placeholder"></div>
    </div>
    
    <p>In this view</p>
    <table class="table">
    <tr>
    
    <td>
    <div class="midtext">Total gen: <b><span id="total_gen_kwh">0</span>kWh</b></div>
    <div class="midtext">Total solar: <b><span id="total_solar_kwh">0</span>kWh</b></div>
    <div class="midtext">Total wind: <b><span id="total_wind_kwh">0</span>kWh</b></div>
    
    </td>
    
    <td>
    <div class="midtext">Total demand: <b><span id="total_use_kwh">0</span>kWh</b></div>
    <div class="midtext">Supplied direct: <b><span id="total_use_direct_kwh">0</span></b></div>
    <div class="midtext">Supplied via store: <b><span id="total_use_via_store_kwh">0</span></b></div>
    </td>
    
    </tr>
    </table>
    <!--
    <br><br>
    <pre style="height:400px">
        <div id="myenergy_out"></div>
    </pre>
    <br><br>-->
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
       if (name.indexOf("solar_power")!=-1) app_myenergy.config.solarpower = feeds[z].id;
       if (name.indexOf("house_power")!=-1) app_myenergy.config.housepower = feeds[z].id;
   }

    app_myenergy.init();
    app_myenergy.show();
</script>
