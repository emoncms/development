<?php global $path; ?>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/packetgen/packetgen.js"></script>

<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/feed/feed.js"></script>
        
<meta charset="utf-8">
<div style="font-size:32px; color:#fff; padding-top:20px; padding-bottom:15px;">My Heating</div>

<div id="bound" style="width:100%;">
<canvas id="myCanvas" ></canvas>
</div>

<div id="coords" style="color:#aaa"></div>
<script>

  var mousedown = false;
  
  var path = "<?php echo $path; ?>";
  var packet = packetgen.get();
  
  var feeds = feed.list();
  var tmp = {};
  for (z in feeds)
  {
    tmp[feeds[z]['id']] = parseFloat(feeds[z]['value']);
  }
  var feeds = tmp;
  
  var bound = {};
  bound.width = $("#bound").width();
  bound.height = $(window).height()-100;
  
  $("#myCanvas").attr('width',bound.width);
  $("#myCanvas").attr('height',bound.height);

  $("#coords").html("width: "+bound.width+" height: "+bound.height);
  
  $(window).resize(function(){
    bound.width = $("#bound").width();
    bound.height = $(window).height()-100;
    
    $("#coords").html("width: "+bound.width+" height: "+bound.height);
    
    $("#myCanvas").attr('width',bound.width);
    $("#myCanvas").attr('height',bound.height); 
    
    draw(a);
  });

  $("body").css('background-color','#222');

  var c=document.getElementById("myCanvas");
  var ctx=c.getContext("2d");
  
  var midx = bound.width/2.0, midy = bound.height/2.0;
  var size = bound.width/2.15;
  if (midx>midy) size = bound.height/2.15;
  
  var mx = 0, my = 0;
  var a = - (packet[4].value*0.01);
  var at = 0;
  
  var avel = 0;
  var lasta = 0;
  

 
  setInterval(loop,33);
  $('#myCanvas').bind('touchmove',function(e){
      e.preventDefault();
      var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
      //CODE GOES HERE
      console.log(touch.pageY+' '+touch.pageX);
      mx = touch.pageX;
      my = touch.pageY;
  });
  
  $("#myCanvas").mousemove(function(e){
    mx = e.clientX;
    my = e.clientY;
  });
  
  $(window).mousedown(function(e){
    mousedown = true;
  });
  
  $(window).mouseup(function(e){
    mousedown = false;
    console.log(mousedown);
  });
 
  setInterval(update,5000);
  
  function update()
  {
    feeds = feed.list();
    tmp = {};
    for (z in feeds)
    {
      tmp[feeds[z]['id']] = parseFloat(feeds[z]['value']);
    }
    feeds = tmp;
    draw(a);
  }
  
  function loop()
  {
    lastat = at;
    at = Math.atan((midx-mx)/(midy-my));
    change = at - lastat;
    if (change>-0.5 && change<0.5) angular_force = (change*0.02);
    
   //acel = 0;
    //avel = 0;
    acel = angular_force - 0.05 * avel;
    avel = avel + acel;
    
    
    a = a + avel;
    draw(a);  
    
    if (Math.abs(lasta-a)>=0.1) {      
      lasta = a;
      
      packet[4].value = (-a) * 100;
      packetgen.set(packet,5);
    }
  }
  
  function draw(a)
  {
    midx = bound.width/2.0, midy = bound.height/2.0;
    size = bound.width/2.15;
    if (midx>midy) size = bound.height/2.15;
  
    ctx.clearRect(0,0,bound.width,bound.height);
    
    ctx.beginPath();
    ctx.moveTo(0,5);
    ctx.lineTo(bound.width,5);
    ctx.closePath();
    
    ctx.lineWidth = 2;
    ctx.strokeStyle = "#111";
    ctx.stroke();
    
    //---------------------------------------
    
    ctx.fillStyle = "rgba(255,255,255,1)";
    ctx.textAlign    = "left";
    ctx.font = "bold 16px arial";
    ctx.fillText("POWER",0,35);
    
    ctx.fillStyle = "rgba(255,255,255,0.6)";
    ctx.textAlign    = "left";
    ctx.font = "bold 24px arial";
    ctx.fillText((0).toFixed(0)+'W',0,60);
    //ctx.fillText((feeds['16012']).toFixed(0)+'W',0,60);

    //---------------------------------------
    
    ctx.fillStyle = "rgba(255,255,255,1)";
    ctx.textAlign    = "left";
    ctx.font = "bold 16px arial";
    ctx.fillText("INTERNAL",100,35);
    
    ctx.fillStyle = "rgba(255,255,255,0.6)";
    ctx.textAlign    = "left";
    ctx.font = "bold 24px arial";
    ctx.fillText((0).toFixed(1)+'°C',100,60);
    //ctx.fillText((feeds['16010']).toFixed(1)+'°C',100,60);
    //---------------------------------------
    
    ctx.fillStyle = "rgba(255,255,255,1)";
    ctx.textAlign    = "left";
    ctx.font = "bold 16px arial";
    ctx.fillText("EXTERNAL",200,35);
    
    ctx.fillStyle = "rgba(255,255,255,0.6)";
    ctx.textAlign    = "left";
    ctx.font = "bold 24px arial";
    ctx.fillText((0).toFixed(1)+'°C',200,60);
    //ctx.fillText((feeds['14972']).toFixed(1)+'°C',200,60);

    //---------------------------------------
    
    red = parseInt(0-a*10);
    blue = parseInt(255+a*10); 
       
    ctx.beginPath();
    ctx.arc(midx, midy, size, 0, 2 * Math.PI, false);
    ctx.closePath();
    ctx.fillStyle = "rgba("+red+",0,"+blue+",1)";
    ctx.fill();
    
    ctx.lineWidth = 0.1*size;
    ctx.strokeStyle = "rgba("+red+",0,"+blue+",0.5)";
    ctx.stroke();
    
    ctx.beginPath();
    ctx.arc(midx, midy, size*0.5, 0, 2 * Math.PI, false);
    ctx.closePath();
    ctx.fillStyle = "rgba(255,255,255,0.4)";
    ctx.fill();
    
    for (z = 0; z< 128; z++)
    {
      atick = a+z*0.049087385;
      ctx.beginPath();
      x = Math.sin(atick)*0.6*size;
      y = Math.cos(atick)*0.6*size;
      ctx.moveTo(midx+x,midy+y);
      x = Math.sin(atick)*0.9*size;
      y = Math.cos(atick)*0.9*size;
      ctx.lineTo(midx+x,midy+y);

      
      ctx.lineWidth = 2;
      ctx.strokeStyle = 'rgba(255,255,255,0.4)';
      ctx.stroke();
      ctx.closePath();
    }
    
    for (z = 0; z< 32; z++)
    {
      atick = a+z*0.196349541;
      ctx.beginPath();
      x = Math.sin(atick)*0.65*size;
      y = Math.cos(atick)*0.65*size;
      ctx.moveTo(midx+x,midy+y);
      x = Math.sin(atick)*0.85*size;
      y = Math.cos(atick)*0.85*size;
      ctx.lineTo(midx+x,midy+y);

      
      ctx.lineWidth = 5;
      ctx.strokeStyle = 'rgba(255,255,255,0.4)';
      ctx.stroke();
      ctx.closePath();
    }


    ctx.fillStyle = "rgba(255,255,255,0.6)";
    ctx.textAlign    = "center";
    ctx.font = "bold "+(size*0.28)+"px arial";
    
    var t = -a;
    ctx.fillText(t.toFixed(1)+'°C',midx,midy+(size*0.125));
  }
  
</script>
