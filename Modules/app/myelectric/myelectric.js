var app_myelectric = {

    powerfeed: false,
    dailyfeed: false,
    dailytype: false,

    daily_data: [],
    daily: [],
    
    liveupdate: false,
    refresh: true,
    
    // Include required javascript libraries
    include: [
        "Modules/app/graph.js"
    ],

    init: function()
    {
        if (app.config["myelectric"]!=undefined) {
            app_myelectric.powerfeed = app.config.myelectric.powerfeed;
            app_myelectric.dailyfeed = app.config.myelectric.dailyfeed;
            app_myelectric.dailytype = app.config.myelectric.dailytype;
        } else {
        
            // Auto scan by feed names
            var feeds = app_myelectric.getfeedsbyid();
            for (z in feeds)
            {
                var name = feeds[z].name.toLowerCase();
                
                if (name.indexOf("house_power")!=-1) {
                    app_myelectric.powerfeed = z;
                    console.log("match");
                 }
                
                if (name.indexOf("house_wh")!=-1) {
                    app_myelectric.dailyfeed = z;
                    app_myelectric.dailytype = 0;
                }
                
                if (name.indexOf("house_kwhd")!=-1) {
                    app_myelectric.dailyfeed = z;
                    app_myelectric.dailytype = 2;
                }
            }
        }
        
        $(window).resize(function(){
            app_myelectric.resize();
        });
        
        $("#openconfig").click(function(){
        
            // Load feed list, populate feed selectors and select the selected feed
            var feeds = app_myelectric.getfeedsbyid();
            
            var out = ""; 
            for (z in feeds) {
                out +="<option value="+feeds[z].id+">"+feeds[z].name+"</option>";
            }
            $("#powerfeed").html(out);
            
            $("#powerfeed").val(app_myelectric.powerfeed);
            $("#dailyfeed").html(out);
            $("#dailyfeed").val(app_myelectric.dailyfeed);
            $("#dailytype").val(app_myelectric.dailytype);
            
            // Switch to the config interface
            $("#config").show();
            $("#powerblock").hide();
            
            if (app_myelectric.liveupdate) clearInterval(app_myelectric.liveupdate);
        });

        $("#configsave").click(function(){
        
            app_myelectric.powerfeed = $("#powerfeed").val();
            app_myelectric.dailyfeed = $("#dailyfeed").val();
            app_myelectric.dailytype = $("#dailytype").val();
            
            // Save config to db
            var config = app.config;
            if (config==false) config = {};
            config["myelectric"] = {
                "powerfeed": app_myelectric.powerfeed,
                "dailyfeed": app_myelectric.dailyfeed,
                "dailytype": app_myelectric.dailytype
            };
            app.setconfig(config);
            
            // Restart interface update
            app_myelectric.liveupdate = setInterval(app_myelectric.liveupdate,5000);

            app_myelectric.refresh = true; 
            app_myelectric.update();
            
            // Switch to main view 
            $("#config").hide();
            $("#powerblock").show();
        });
    },
    
    show: function()
    {
        $("body").css('background-color','#222');
        $("#footer").css('background-color','#181818');
        $("#footer").css('color','#999');
        
        app_myelectric.resize();
        
        if (app_myelectric.powerfeed && app_myelectric.dailyfeed) {
            app_myelectric.update();
            app_myelectric.liveupdate = setInterval(app_myelectric.update,5000);
        }
    },
    
    resize: function() 
    {
        bound.width = $("#bound").width();
        $("#myCanvas").attr('width',bound.width);
        graph.width = bound.width;
        
        var windowheight = $(window).height();
        newheight = windowheight-320;
        if (newheight>350) newheight = 350;
        $("#bound").height(newheight);
        
        bound.height = newheight;
        $("#myCanvas").attr('height',bound.height);
        graph.height = bound.height;
        
        graph.draw("myCanvas",[app_myelectric.daily]);
        app_myelectric.refresh = true;
    },
    
    hide: function()
    {
        clearInterval(this.liveupdate)
    },
    
    update: function()
    {
        if (app_myelectric.refresh)
        {
            var interval = 86400;
            var ndays = Math.floor(graph.width / 40);
            var timeWindow = (3600000*24*ndays);	//Initial time window
            var now = +new Date;
            var start = (now - timeWindow)*0.001;
            var end = now*0.001;
            start = Math.floor(start / interval) * interval;
            end = Math.ceil(end / interval) * interval;
            
            app_myelectric.daily_data = app_myelectric.getdata(app_myelectric.dailyfeed,start*1000,end*1000,interval);
            if (app_myelectric.daily_data.success != undefined) app_myelectric.daily_data = [];
            
            app_myelectric.refresh = false;
        }
        
        var feeds = app_myelectric.getfeedsbyid();
        $("#power").html(feeds[app_myelectric.powerfeed].value);
        
        var daily_data_copy = eval(JSON.stringify(app_myelectric.daily_data));

        var daily = [];
        if (daily_data_copy.length>0)
        {
            // Watt hours elapsed
            if (app_myelectric.dailytype==0)
            {
                var lastday = daily_data_copy[daily_data_copy.length-1][0];
                daily_data_copy.push([lastday+24*3600*1000,feeds[app_myelectric.dailyfeed].value]);

                for (var z=1; z<daily_data_copy.length; z++)
                {
                    var kwh = (daily_data_copy[z][1] - daily_data_copy[z-1][1]) * 0.001;
                    daily.push([daily_data_copy[z][0],kwh]);
                }
                
                $("#kwhd").html((daily[daily.length-1][1]*1).toFixed(1));
            }
            // kWh elapsed
            else if (app_myelectric.dailytype==1)
            {
                var lastday = daily_data_copy[daily_data_copy.length-1][0];
                if (feeds[app_myelectric.dailyfeed].value!=0) {
                    daily_data_copy.push([lastday+24*3600*1000,feeds[app_myelectric.dailyfeed].value]);
                }
                for (var z=1; z<daily_data_copy.length; z++)
                {
                    var kwh = 0;
                    
                    if (daily_data_copy[z][1]!=null && daily_data_copy[z-1][1]!=null) {
                        kwh = (daily_data_copy[z][1] - daily_data_copy[z-1][1]);
                    }
                    daily.push([daily_data_copy[z][0],kwh]);
                }
                
                $("#kwhd").html((daily[daily.length-1][1]*1).toFixed(1));
            }
            // kWh per day
            else if (app_myelectric.dailytype==2)
            {
                // var lastday = daily_data_copy[daily_data_copy.length-1][0];
                // daily_data_copy.push([lastday+24*3600*1000,feeds[app_myelectric.dailyfeed].value]);
                daily = daily_data_copy;
                $("#kwhd").html((daily[daily.length-1][1]*1).toFixed(1));
            }
            // Power (Watts)
            else if (app_myelectric.dailytype==3)
            {
                for (var z=1; z<daily_data_copy.length; z++)
                {
                    var kwh = daily_data_copy[z][1]*0.024;
                    daily.push([daily_data_copy[z][0],kwh]);
                }
                $("#kwhd").html("---");
            }
        }
        
        app_myelectric.daily = daily;
        graph.draw("myCanvas",[app_myelectric.daily]);
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
            data: "id="+id+"&start="+start+"&end="+end+"&interval="+interval+"&skipmissing=0&limitinterval=0",
            dataType: 'json',
            async: false,                      
            success: function(data_in) { data = data_in; } 
        });
        return data;
    }
};
