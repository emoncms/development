<!--
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->

<?php
  global $path, $embed;
?>

 <!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.time.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/flot/jquery.flot.selection.min.js"></script>

<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/vis/visualisations/common/api.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/vis/visualisations/common/inst.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/vis/visualisations/common/proc.js"></script>

<?php if (!$embed) { ?>
<h2>Raw data: <?php echo $feedidname; ?></h2>
<?php } ?>

    <div id="graph_bound" style="width:100%; height:400px; position:relative; ">
      <div id="graph"></div>
      <div style="position:absolute; top:20px; left:40px;">

        <input class="time" type="button" value="D" time="1"/>
        <input class="time" type="button" value="W" time="7"/>
        <input class="time" type="button" value="M" time="30"/>
        <input class="time" type="button" value="Y" time="365"/> | 

        <input id="zoomin" type="button" value="+"/>
        <input id="zoomout" type="button" value="-"/>
        <input id="left" type="button" value="<"/>
        <input id="right" type="button" value=">"/>

      </div>

        <h3 style="position:absolute; top:0px; left:410px;"><span id="stats"></span></h3>
    </div>

<script id="source" language="javascript" type="text/javascript">

  var feedid = "<?php echo $feedid; ?>";
  var feedname = "<?php echo $feedidname; ?>";
  var path = "<?php echo $path; ?>";
  var apikey = "<?php echo $apikey; ?>";
  var valid = "<?php echo $valid; ?>";

  var plotfill = <?php echo $fill; ?>;
  if (plotfill==1) plotfill = true; else plotfill = false;
  var units = "<?php echo $units; ?>";

  var embed = <?php echo $embed; ?>;
  $('#graph').width($('#graph_bound').width());
  $('#graph').height($('#graph_bound').height());
  if (embed) $('#graph').height($(window).height());

  var timeWindow = (3600000*24.0*7);				//Initial time window
  var start = ((new Date()).getTime())-timeWindow;		//Get start time
  var end = (new Date()).getTime();				//Get end time

  var graph_data = [];
  vis_feed_data();

  $(window).resize(function(){
    $('#graph').width($('#graph_bound').width());
    if (embed) $('#graph').height($(window).height());
    plot();
  });

  function vis_feed_data()
  {
    graph_data = [];
    graph_data = get_feed_data(feedid,start,end,1000);

    var stats = power_stats(graph_data);
    var out = "Average: "+stats['average'].toFixed(0)+units;
    if (units=='W') out+= " | "+stats['kwh'].toFixed(2)+" kWh";
    $("#stats").html(out);   
    plot();
  }

  function plot()
  {
    var i = parseInt(graph_data.length / 2.0);
    var mp = i;

    var line = []; var li = 0;

    if (graph_data[i]!=undefined && graph_data[i+10]!=undefined)
    {

      var starti = 0;
      var startx = graph_data[0][0];
      var starty = graph_data[0][1];

      var threshold = 7.0;

      for (var z=1; z<graph_data.length; z++)
      {
        var currentx = graph_data[z][0];
        var currenty = graph_data[z][1];

        var m = (currenty - starty) / (currentx - startx)
        var c = starty - m * startx;

        var square_deviation_sum = 0;
        var n = 0;
        for (var d=starti; d<=z; d++)
        {
          var y = m * graph_data[d][0] + c;
          var diff = y - graph_data[d][1];
          square_deviation_sum += diff * diff;
          n++;
        }
        var stdev = Math.sqrt(square_deviation_sum / n);

        if (stdev>threshold) {
          starti = z-1;
          startx = graph_data[z-1][0];
          starty = graph_data[z-1][1];
          line[li] = [startx,starty]; li++;
        }

      }
    }

    //var line = [[ts,intercept],[te,slope*(te-ts)+intercept]];
    console.log(graph_data.length);
    console.log(li);
    // -----------------------------------------------------------------------------------------
    //console.log(graph_data);
    var plot = $.plot($("#graph"), [
      {data: graph_data, lines: { show: true, fill: plotfill }},
      {data: line, yaxis: 2, color: "#000", lines: { show: true, fill: plotfill }}], {
      grid: { show: true, hoverable: true, clickable: true },
      xaxis: { mode: "time", localTimezone: true, min: start, max: end },
      selection: { mode: "xy" }
    });
  }

  //--------------------------------------------------------------------------------------
  // Graph zooming
  //--------------------------------------------------------------------------------------
  $("#graph").bind("plotselected", function (event, ranges) { start = ranges.xaxis.from; end = ranges.xaxis.to; vis_feed_data(); });
  //----------------------------------------------------------------------------------------------
  // Operate buttons
  //----------------------------------------------------------------------------------------------
  $("#zoomout").click(function () {inst_zoomout(); vis_feed_data();});
  $("#zoomin").click(function () {inst_zoomin(); vis_feed_data();});
  $('#right').click(function () {inst_panright(); vis_feed_data();});
  $('#left').click(function () {inst_panleft(); vis_feed_data();});
  $('.time').click(function () {inst_timewindow($(this).attr("time")); vis_feed_data();});
  //-----------------------------------------------------------------------------------------------
</script>

