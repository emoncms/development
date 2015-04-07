var app_myheatpump = {

    kwh: false,
    power: false,
    flow: false,
    return: false,
    ambient: false,
    room: false,
    
    maxpower: 6000,
    speed: 0,
    
    updater: false,
    ctx: false,
    time: 0,
    dtime: 1000/25,
    
    // Include required javascript libraries
    include: [
        "Lib/flot/jquery.flot.min.js",
        "Lib/flot/jquery.flot.time.min.js",
        "Lib/flot/jquery.flot.selection.min.js",
        "Modules/app/vis.helper.js",
        "Lib/flot/date.format.js",
        "Modules/app/roundedrect.js"
    ],

    init: function()
    {
        $("body").css("background-color","#222");
        
        // Auto scan by feed names
        var feeds = app_myheatpump.getfeedsbyid();
        for (z in feeds)
        {
            var name = feeds[z].name.toLowerCase();
            
            if (name.indexOf("heatpump_power")!=-1) app_myheatpump.power = z;  
            if (name.indexOf("heatpump_flow_temp")!=-1) app_myheatpump.flow = z;
            if (name.indexOf("heatpump_return_temp")!=-1) app_myheatpump.return = z;
            if (name.indexOf("heatpump_room_temp")!=-1) app_myheatpump.room = z;
            if (name.indexOf("ambient_temp")!=-1) app_myheatpump.ambient = z;
            if (name.indexOf("heatpump_kwh")!=-1) app_myheatpump.kwh = z;
        }

        this.feedupdate();
        this.feedupdater = setInterval(this.feedupdate,5000);
        this.updater = setInterval(this.update,this.dtime);

        $("#myheatpump_zoomout").click(function () {view.zoomout(); app_myheatpump.draw();});
        $("#myheatpump_zoomin").click(function () {view.zoomin(); app_myheatpump.draw();});
        $('#myheatpump_right').click(function () {view.panright(); app_myheatpump.draw();});
        $('#myheatpump_left').click(function () {view.panleft(); app_myheatpump.draw();});
        $('.myheatpump_time').click(function () {view.timewindow($(this).attr("time")); app_myheatpump.draw();});

        $('#myheatpump_placeholder').bind("plotselected", function (event, ranges)
        {
            view.start = ranges.xaxis.from;
            view.end = ranges.xaxis.to;
            app_myheatpump.draw();
        });
    },
    
    show: function()
    {
        var canvas = document.getElementById("heatpump-diagram");
        var ctx = canvas.getContext("2d");
        this.ctx = ctx;
        
        this.draw_heatpump_outline();
        
        var top_offset = 0;
        var placeholder_bound = $('#myheatpump_placeholder_bound');
        var placeholder = $('#myheatpump_placeholder');
        
        var width = placeholder_bound.width();
        var height = width * 0.38;

        placeholder.width(width);
        placeholder_bound.height(height);
        placeholder.height(height-top_offset);

        var ndays = 50;
        var timeWindow = (3600000*24*ndays);	//Initial time window
        var start = +new Date - timeWindow;	//Get start time
        view.end = +new Date;				    //Get end time

        var d = new Date()
        var n = d.getTimezoneOffset();
        var offset = n / -60;

        var interval = 3600*24;
        view.start = (Math.round((start/1000.0)/interval) * interval)*1000;

        if (this.kwh) {
            
            var whdata = this.getdata(this.kwh,view.start,view.end,interval);
            var kwhd = [];
            
            for (var day=1; day<whdata.length; day++) {
                var kwh = whdata[day][1] - whdata[day-1][1];
                kwhd.push([whdata[day][0],kwh]);
            }

            $('#myheatpump_placeholder_wh').width(width)
            $.plot($('#myheatpump_placeholder_wh'), [{data:kwhd, color:"#0699fa"}], {bars:{show:true, barWidth:3600*1000*20}, xaxis:{mode:"time",timezone: "browser"}});
            
            $("#myheatpump_kwhgraph").show();
        }
        
        var series = [];

        var dp = 1;
        var units = "C";
        var fill = false;
        var plotColour = 0;

        app_myheatpump.draw();
    },
    
    draw_heatpump_outline: function() {
        var ctx = this.ctx;
        ctx.lineWidth = 5;
        ctx.strokeStyle="#0699fa";

        ctx.roundRect(100,100,400,300, 20).stroke();
        ctx.roundRect(800,130,300,240, 20).stroke();
        ctx.lineWidth = 2
        ctx.beginPath();
        for (var i=0; i<10; i++)
        {
            ctx.moveTo(800+i*30,150);
            ctx.lineTo(800+i*30,350);
        }
        ctx.stroke();

        ctx.lineWidth = 5;
        ctx.beginPath();
        ctx.moveTo(501,175);
        ctx.lineTo(800,175);
        ctx.moveTo(501,325);
        ctx.lineTo(800,325);;
        ctx.stroke();

        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(250,250,120,0,2*Math.PI);
        ctx.stroke();
    },
    
    hide: function() {
        clearInterval(app_myheatpump.updater);
        clearInterval(app_myheatpump.feedupdater);
        $("body").css("background-color","#fff");
    },
    
    feedupdate: function()
    {
       var feeds = {};
       $.ajax({                                      
            url: path+"feed/list.json",
            dataType: 'json',
            async: true,                      
            success: function(data_in) { feeds = data_in; 
            
                for (z in feeds)
                {
                    if (feeds[z].id==app_myheatpump.power) {
                        $(".heatpump-power").html((feeds[z].value*1).toFixed(0));
                        app_myheatpump.speed = (feeds[z].value*1 / app_myheatpump.maxpower);
                        if (feeds[z].value<50) app_myheatpump.speed = 0;
                    }
                    
                    if (feeds[z].id==app_myheatpump.flow) 
                        $(".heatpump-flow").html((feeds[z].value*1).toFixed(1));
                    if (feeds[z].id==app_myheatpump.return) 
                        $(".heatpump-return").html((feeds[z].value*1).toFixed(1));
                    if (feeds[z].id==app_myheatpump.ambient) 
                        $(".heatpump-ambient").html((feeds[z].value*1).toFixed(1));
                    if (feeds[z].id==app_myheatpump.room) 
                        $(".room-temperature").html((feeds[z].value*1).toFixed(1));
                } 
            
            } 
       });
    },
    
    update: function()
    {
        var ctx = app_myheatpump.ctx;
        
        ctx.fillStyle="#222"
        ctx.beginPath();
        ctx.arc(250,250,115,0,2*Math.PI);
        ctx.fill();

        a = app_myheatpump.time*10*app_myheatpump.speed;
        
        ctx.fillStyle="#0699fa";
        for (var i=0; i<4; i++){
            ctx.beginPath();
            ctx.arc(250,250,110,a+2*Math.PI*i*0.25,a+2*Math.PI*i*0.25+0.6,0);
            ctx.arc(250,250,20,a+2*Math.PI*i*0.25+0.6,a+2*Math.PI*i*0.25,1);
            ctx.closePath();
            ctx.stroke();
        }
        
        ctx.beginPath();
        ctx.arc(250,250,15,0,2*Math.PI);
        ctx.stroke();
        
        app_myheatpump.time += (app_myheatpump.dtime*0.001);
    },
    
    draw: function()
    {
        var options = {
            lines: { fill: false },
            xaxis: { mode: "time", timezone: "browser", min: view.start, max: view.end},
            yaxes: [
                {position:'right'},
                {position:'left'},
                {position:'left'},
                {position:'left'},
                {position:'left'}
            ],
            //yaxis: { min: 0 },
            grid: {hoverable: true, clickable: true},
            selection: { mode: "x" },
            legend: { show: "true" }
        }
        
        var npoints = 800;
        interval = Math.round(((view.end - view.start)/npoints)/1000);
        
        series = [];
        if (this.power) series.push({data:this.getdata(this.power,view.start,view.end,interval), color:"rgb(10,150,230)", yaxis:2, lines:{fill:true}, label: "Power"});
        if (this.flow) series.push({data:this.getdata(this.flow,view.start,view.end,interval), color:"rgb(10,120,210)", label: "Flow"});
        if (this.return) series.push({data:this.getdata(this.return,view.start,view.end,interval), color:"rgb(10,100,190)", label: "Return"});
        if (this.ambient) series.push({data:this.getdata(this.ambient,view.start,view.end,interval), color:"rgb(10,80,170)", label: "Outside"});
        if (this.room) series.push({data:this.getdata(this.room,view.start,view.end,interval), color:"rgb(10,60,150)", label: "Room"});


        options.xaxis.min = view.start;
        options.xaxis.max = view.end;
        $.plot($('#myheatpump_placeholder'), series, options);
        
        //$(".tickLabel").each(function(i) { $(this).css("color", "#0699fa"); });
    },
    
    getfeedsbyid: function()
    {
        var apikeystr = "";
        if (apikey!="") apikeystr = "?apikey="+apikey;
        
        var feeds = {};
        $.ajax({                                      
            url: path+"feed/list.json"+apikeystr,
            dataType: 'json',
            async: false,                      
            success: function(data_in) { feeds = data_in; } 
        });
        
        var byid = {};
        for (z in feeds) byid[feeds[z].id] = feeds[z];
        return byid;
    },
    
    getdata: function(id,start,end,interval)
    {
        var apikeystr = "";
        if (apikey!="") apikeystr = "?apikey="+apikey;
        
        var data = [];
        $.ajax({                                      
            url: path+"feed/data.json"+apikeystr,                         
            data: "id="+id+"&start="+start+"&end="+end+"&interval="+interval+"&skipmissing=1&limitinterval=1",
            dataType: 'json',
            async: false,                      
            success: function(data_in) { data = data_in; } 
        });
        return data;
    } 
    
}
