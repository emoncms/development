/*
   All emon_widgets code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org

    Author: Trystan Lea: trystan.lea@googlemail.com
    If you have any questions please get in touch, try the forums here:
    http://openenergymonitor.org/emon/forum
 */
 
var thermostat = [];

function thermostat_widgetlist()
{
  var widgets = {
    "thermostat":
    {
      "offsetx":-80,"offsety":-80,"width":160,"height":160,
      "menu":"Widgets",
      "options":["feed","max","scale","units","type"],
      "optionstype":["feed","value","value","value","value"],
      "optionsname":[_Tr("Feed"),_Tr("Max value"),_Tr("Scale"),_Tr("Units"),_Tr("Type")],
      "optionshint":[_Tr("Feed value"),_Tr("Max value to show"),_Tr("Scale to show"),_Tr("Units to show"),_Tr("Type to show")]            
    }
  }
  return widgets;
}

function thermostat_init()
{
  setup_widget_canvas('thermostat');

  $('.thermostat').each(function(index)
  {
    var id = "can-"+$(this).attr("id");
    $("#"+id).mousemove(function(event){
      if(event.offsetX==undefined) // this works for Firefox
      {
        thermostat[id].mx = (event.pageX - $(event.target).offset().left);
        thermostat[id].my = (event.pageY - $(event.target).offset().top);
      } else {
        thermostat[id].mx = event.offsetX;
        thermostat[id].my = event.offsetY;
      }
    });
    
    thermostat[id] = {a:18.5,at:0,avel:0,mx:0,my:0};
  });

}

function thermostat_draw()
{
  $('.thermostat').each(function(index)
  {
    var id = "can-"+$(this).attr("id");
    
    var width = $(this).width();
    var height = $(this).height();
    var midx = width / 2.0;
    var midy = height / 2.0;
        
    var lastat = thermostat[id].at;
    thermostat[id].at = Math.atan((midx-thermostat[id].mx)/(midy-thermostat[id].my));
    var change = thermostat[id].at - lastat;
    
    var angular_force = 0;
    if (change>-0.5 && change<0.5) angular_force = (change*0.02);

    var acel = angular_force - 0.05 * thermostat[id].avel;
    thermostat[id].avel += acel;
    thermostat[id].a += thermostat[id].avel;


    drawthermostat(widgetcanvas[id],0,0,$(this).width(),$(this).height(),thermostat[id].a);

  });
}

function thermostat_slowupdate()
{

}

function thermostat_fastupdate()
{ 
  thermostat_draw();
}

  function drawthermostat(ctx,x,y,width,height,angle)
  {
    ctx.clearRect(0,0,width,height);
    
    var midx = width / 2.0;
    var midy = height / 2.0;
    var size = 200;
    
    red = parseInt(angle*10);
    blue = parseInt(255-angle*10); 
       
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
      var atick = angle+z*0.049087385;
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
      var atick = angle+z*0.196349541;
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
    ctx.fillText(angle.toFixed(1)+'°C',midx,midy+(size*0.125));
  }
