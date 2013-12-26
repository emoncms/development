<?php global $path; ?>

<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/packetgen/packetgen.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/feed/feed.js"></script>
        
<meta charset="utf-8">
<br>
<div id="bound" style="width:100%;">
<canvas id="myCanvas" ></canvas>
</div>

<div id="coords" style="color:#aaa"></div>
<script>
  var path = "<?php echo $path; ?>";
  var apikey = "";
  
  var bound = {};
  bound.width = $("#bound").width();
  bound.height = $(window).height()-100;
  
  $("#myCanvas").attr('width',bound.width);
  $("#myCanvas").attr('height',bound.height);
  
  $(window).resize(function(){
    bound.width = $("#bound").width();
    bound.height = $(window).height()-100;
    
    $("#myCanvas").attr('width',bound.width);
    $("#myCanvas").attr('height',bound.height); 
    draw();
  });

  $("body").css('background-color','#222');
  
  
  var timeWindow = (3600000*14);	//Initial time window
  var start = +new Date - timeWindow;	//Get start time
  var end = +new Date - (3600000*8);				    //Get end time

  var graph = {
    width: bound.width,
    height: 300,
    
    xaxis: { start:start, end:end, ticksize: 3600000 },
    yaxis: { start:0, end:200, ticksize: 25 },
    
    data: feed.get_timestore_average(364,start,end,60)
  };

  var c=document.getElementById("myCanvas");
  var ctx=c.getContext("2d");
  draw();
  
  function draw()
  {
    graph.width = bound.width;
    ctx.clearRect(0,0,bound.width,bound.height);
    
    var grd=ctx.createLinearGradient(0,0,0,graph.height+40);
    grd.addColorStop(0,"#333");
    grd.addColorStop(1,"#000");

    ctx.fillStyle=grd;
    ctx.fillRect(0,0,graph.width,graph.height+40);    
    
    
    ctx.fillStyle = "rgba(255,255,255,1)";
    ctx.textAlign    = "left";
    ctx.font = "bold 16px arial";
    ctx.fillText("SOLAR PV Generation",20,35);
    
    ctx.fillStyle = "rgba(255,255,255,0.6)";
    ctx.textAlign    = "left";
    ctx.font = "bold 24px arial";
    ctx.fillText((400).toFixed(1)+'W',20,60);
    
    
    // Draw the data
    ctx.beginPath();
    for (z in graph.data)
    {
      // Calculate canvas pixel position of each datapoint
      px = ((graph.data[z][0]-graph.xaxis.start) / (graph.xaxis.end-graph.xaxis.start)) * graph.width;
      py = graph.height -(((graph.data[z][1]-graph.yaxis.start) / (graph.yaxis.end-graph.yaxis.start)) * graph.height);

      // Draw line segments
      if (z==0) ctx.moveTo(px,py);
      if (z!=0) ctx.lineTo(px,py);    
    }
    
    // Render the white plot line
    ctx.strokeStyle = "#fff";
    ctx.lineWidth = 3;
    ctx.stroke();
    
    // Complete polygon in which to color fill
    ctx.lineTo(graph.width,graph.height);
    ctx.lineTo(0,graph.height);    
    ctx.closePath();
    
    // Render the yellow fill
    var grd=ctx.createLinearGradient(0,220,0,280);
    grd.addColorStop(0,"rgba("+255+",230,"+0+",0.8)");
    grd.addColorStop(1,"rgba("+255+",230,"+0+",0.4)");

    ctx.fillStyle=grd;
    ctx.fill();
    
    // Axes
    ctx.beginPath();
    
    // Minor tick's (10mins)
    var tickstart = Math.ceil(graph.xaxis.start / 600000) * 600000;
    var tenmin = 0, px = 0;
    while (px<graph.width)
    {
      px = (((tickstart+tenmin*600000)-graph.xaxis.start) / (graph.xaxis.end-graph.xaxis.start)) * graph.width;
      ctx.moveTo(px,graph.height+34);
      ctx.lineTo(px,graph.height+40);
      tenmin++;
    }
    
    ctx.closePath();
    ctx.strokeStyle = "#aaa";
    ctx.lineWidth = 1;
    ctx.stroke();
    ctx.beginPath();
    
    // Majour tick's (1h)
    ctx.textAlign    = "center";
    ctx.font = "bold 12px arial";
    ctx.fillStyle = "#aaa";
    
    var tickstart = Math.ceil(graph.xaxis.start / graph.xaxis.ticksize) * graph.xaxis.ticksize;
    var h = 0, px = 0;
    while (px<graph.width)
    {
      px = (((tickstart+h*3600000)-graph.xaxis.start) / (graph.xaxis.end-graph.xaxis.start)) * graph.width;
      ctx.moveTo(px,graph.height+30);
      ctx.lineTo(px,graph.height+40);
      
      var hour = new Date(tickstart+h*3600000).getHours();
      
      ctx.fillText(hour,px,graph.height+24);
      h++;
    }
    
    ctx.closePath();
    ctx.strokeStyle = "#aaa";
    ctx.lineWidth = 2;
    ctx.stroke();

    ctx.textAlign    = "right";
    // Majour y-axis 
    ctx.beginPath();
   
    for (var yval = 0; yval<=graph.yaxis.end; yval+= graph.yaxis.ticksize)
    {
      py = graph.height -(((yval-graph.yaxis.start) / (graph.yaxis.end-graph.yaxis.start)) * graph.height);
      ctx.moveTo(graph.width-5,py);
      ctx.lineTo(graph.width,py);
       ctx.fillText(yval+"W",graph.width-10,py+4);
    }
    ctx.closePath();
    ctx.stroke();
    

  }
  
</script>


