var app_heatpump = {

    config: {
        kwh: false,
        power: false,
        flow: false,
        return: false,
        ambient: false,
        room: false
    },
    
    maxpower: 6000,
    speed: 0,
    
    updater: false,
    ctx: false,
    time: 0,
    dtime: 1000/25,
    
    // Include required javascript libraries
    include: [
        "static/flot/jquery.flot.min.js",
        "static/flot/jquery.flot.time.min.js",
        "static/flot/jquery.flot.selection.min.js",
        "static/vis.helper.js",
        "static/flot/date.format.js",
        "static/roundedrect.js"
    ],

    init: function()
    {
        $("body").css("background-color","#222");

        var canvas = document.getElementById("heatpump-diagram");
        var ctx = canvas.getContext("2d");
        this.ctx = ctx;
        
        this.draw_heatpump_outline();
        
        this.feedupdate();
        this.feedupdater = setInterval(this.feedupdate,5000);
        this.updater = setInterval(this.update,this.dtime);

        var top_offset = 0;
        var placeholder_bound = $('#placeholder_bound');
        var placeholder = $('#placeholder');

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

        if (this.config.kwh) {
            
            var whdata = this.getdata(this.config.kwh,view.start,view.end,interval);
            var kwhd = [];
            
            for (var day=1; day<whdata.length; day++) {
                var kwh = whdata[day][1] - whdata[day-1][1];
                kwhd.push([whdata[day][0],kwh]);
            }

            $('#placeholder_wh').width(width)
            $.plot($('#placeholder_wh'), [{data:kwhd, color:"#0699fa"}], {bars:{show:true, barWidth:3600*1000*20}, xaxis:{mode:"time",timezone: "browser"}});
            
            $("#kwhgraph").show();
        }
        
        var series = [];

        var dp = 1;
        var units = "C";
        var fill = false;
        var plotColour = 0;

        this.draw();

        $("#zoomout").click(function () {view.zoomout(); app_heatpump.draw();});
        $("#zoomin").click(function () {view.zoomin(); app_heatpump.draw();});
        $('#right').click(function () {view.panright(); app_heatpump.draw();});
        $('#left').click(function () {view.panleft(); app_heatpump.draw();});
        $('.time').click(function () {view.timewindow($(this).attr("time")); app_heatpump.draw();});

        placeholder.bind("plotselected", function (event, ranges)
        {
            view.start = ranges.xaxis.from;
            view.end = ranges.xaxis.to;
            app_heatpump.draw();
        });
        
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
    
    close: function() {
        //console.log("Updater: "+app_heatpump.updater);
        clearInterval(app_heatpump.updater);
        $("body").css("background-color","#fff");
    },
    
    feedupdate: function()
    {
       var feeds = {};
       $.ajax({                                      
            url: "feed/list.json",
            dataType: 'json',
            async: true,                      
            success: function(data_in) { feeds = data_in; 
            
                for (z in feeds)
                {
                    if (feeds[z].id==app_heatpump.config.power) {
                        $(".heatpump-power").html((feeds[z].value*1).toFixed(0));
                        app_heatpump.speed = (feeds[z].value*1 / app_heatpump.maxpower);
                        if (feeds[z].value<50) app_heatpump.speed = 0;
                    }
                    
                    if (feeds[z].id==app_heatpump.config.flow) 
                        $(".heatpump-flow").html((feeds[z].value*1).toFixed(1));
                    if (feeds[z].id==app_heatpump.config.return) 
                        $(".heatpump-return").html((feeds[z].value*1).toFixed(1));
                    if (feeds[z].id==app_heatpump.config.ambient) 
                        $(".heatpump-ambient").html((feeds[z].value*1).toFixed(1));
                    if (feeds[z].id==app_heatpump.config.room) 
                        $(".room-temperature").html((feeds[z].value*1).toFixed(1));
                } 
            
            } 
       });
    },
    
    update: function()
    {
        var ctx = app_heatpump.ctx;
        
        ctx.fillStyle="#222"
        ctx.beginPath();
        ctx.arc(250,250,115,0,2*Math.PI);
        ctx.fill();

        a = app_heatpump.time*10*app_heatpump.speed;
        
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
        
        app_heatpump.time += (app_heatpump.dtime*0.001);
        
        console.log(".");
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
        if (this.config.power) series.push({data:this.getdata(this.config.power,view.start,view.end,interval), color:"rgb(10,150,230)", yaxis:2, lines:{fill:true}, label: "Power"});
        if (this.config.flow) series.push({data:this.getdata(this.config.flow,view.start,view.end,interval), color:"rgb(10,120,210)", label: "Flow"});
        if (this.config.return) series.push({data:this.getdata(this.config.return,view.start,view.end,interval), color:"rgb(10,100,190)", label: "Return"});
        if (this.config.ambient) series.push({data:this.getdata(this.config.ambient,view.start,view.end,interval), color:"rgb(10,80,170)", label: "Outside"});
        if (this.config.room) series.push({data:this.getdata(this.config.room,view.start,view.end,interval), color:"rgb(10,60,150)", label: "Room"});


        options.xaxis.min = view.start;
        options.xaxis.max = view.end;
        $.plot(placeholder, series, options);
        
        //$(".tickLabel").each(function(i) { $(this).css("color", "#0699fa"); });
    },
    
    getdata: function(id,start,end,interval)
    {
        var data = [];
        $.ajax({                                      
            url: "feed/average.json",                         
            data: "id="+id+"&start="+start+"&end="+end+"&interval="+interval,
            dataType: 'json',
            async: false,                      
            success: function(data_in) { data = data_in; } 
        });
        return data;
    }    
    
}
