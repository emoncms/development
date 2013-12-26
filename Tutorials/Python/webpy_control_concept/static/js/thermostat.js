var c=document.getElementById("myCanvas");
var ctx=c.getContext("2d");

var midx = 400, midy = 300, size = 200;

var mx = 0, my = 0;
var a = 18.5;
var at = 0;

var avel = 0;

setInterval(loop,33);

$("#myCanvas").mousemove(function(e){
  mx = e.clientX;
  my = e.clientY;
});

function loop()
{
  lastat = at;
  at = Math.atan((midx-mx)/(midy-my));
  change = at - lastat;
  if (change>-0.5 && change<0.5) angular_force = (change*0.02);
  
  acel = angular_force - 0.05 * avel;
  avel = avel + acel;
  a = a + avel;
  draw(a);  
}

function draw(a)
{
  ctx.clearRect(0,0,800,600);
  
  red = parseInt(a*10);
  blue = parseInt(255-a*10); 
     
  ctx.beginPath();
  ctx.arc(midx, midy, size, 0, 2 * Math.PI, false);
  ctx.closePath();
  ctx.fillStyle = "rgba("+red+",0,"+blue+",1)";
  ctx.fill();
  
  ctx.lineWidth = 20;
  ctx.strokeStyle = "rgba("+red+",0,"+blue+",0.5)";
  ctx.stroke();
  
  ctx.beginPath();
  ctx.arc(midx, midy, 100, 0, 2 * Math.PI, false);
  ctx.closePath();
  ctx.fillStyle = "rgba(255,255,255,0.4)";
  ctx.fill();
  
  for (z = 0; z< 128; z++)
  {
    atick = a+z*0.049087385;
    ctx.beginPath();
    x = Math.sin(atick)*120;
    y = Math.cos(atick)*120;
    ctx.moveTo(midx+x,midy+y);
    x = Math.sin(atick)*180;
    y = Math.cos(atick)*180;
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
    x = Math.sin(atick)*130;
    y = Math.cos(atick)*130;
    ctx.moveTo(midx+x,midy+y);
    x = Math.sin(atick)*170;
    y = Math.cos(atick)*170;
    ctx.lineTo(midx+x,midy+y);

    
    ctx.lineWidth = 5;
    ctx.strokeStyle = 'rgba(255,255,255,0.4)';
    ctx.stroke();
    ctx.closePath();
  }


  ctx.fillStyle = "rgba(255,255,255,0.6)";
  ctx.textAlign    = "center";
  ctx.font = "bold "+(size*0.28)+"px arial";
  ctx.fillText(a.toFixed(1)+'°C',midx,midy+(size*0.125));
}
