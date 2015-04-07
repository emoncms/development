var app_myenergy = {

    solarpower: 0,
    housepower: 0,
    
    feedname: "",
    
    live: false,
    
    windnow: 0,
    lastviewstart: 0,
    lastviewend: 0,
    
    house_data: [],
    solar_data: [],
    wind_data: [],
    
    annual_wind_gen: 3300,
    capacity_factor: 0.4,

    // Include required javascript libraries
    include: [
        "Lib/flot/jquery.flot.min.js",
        "Lib/flot/jquery.flot.time.min.js",
        "Lib/flot/jquery.flot.selection.min.js",
        "Modules/app/vis.helper.js",
        "Lib/flot/date.format.js"
    ],

    // App start function
    init: function()
    {
        if (app.config["myenergy"]!=undefined) {
            this.annual_wind_gen = 1*app.config["myenergy"].annualwindgen;
            this.solarpower = app.config["myenergy"].solarpower;
            this.housepower = app.config["myenergy"].housepower;
        } else {
            // Auto scan by feed names
            var feeds = app_myenergy.getfeedsbyid();
            for (z in feeds)
            {
                var name = feeds[z].name.toLowerCase();
                
                if (name.indexOf("house_power")!=-1) {
                    app_myenergy.housepower = z;
                }
                
                if (name.indexOf("solar_power")!=-1) {
                    app_myenergy.solarpower = z;
                }
            }
        }
        
        this.my_wind_cap = ((this.annual_wind_gen / 365) / 0.024) / this.capacity_factor;

        var timeWindow = (3600000*24.0*1);
        view.end = +new Date;
        view.start = view.end - timeWindow;
        
        var placeholder = $('#myenergy_placeholder');
        
        $("#myenergy_zoomout").click(function () {view.zoomout(); app_myenergy.draw();});
        $("#myenergy_zoomin").click(function () {view.zoomin(); app_myenergy.draw();});
        $('#myenergy_right').click(function () {view.panright(); app_myenergy.draw();});
        $('#myenergy_left').click(function () {view.panleft(); app_myenergy.draw();});
        $('.time').click(function () {view.timewindow($(this).attr("time")); app_myenergy.draw();});
        
        placeholder.bind("plotselected", function (event, ranges)
        {
            view.start = ranges.xaxis.from;
            view.end = ranges.xaxis.to;
            app_myenergy.draw();
        });
        
        /*
        $(document).on("socketio_msg",function( event, msg ) {
            var use_now = 1*nodes['6'].values[1] + 1*nodes['6'].values[2];
            var solar_now = 1*nodes['10'].values[2];
            if (solar_now<10) solar_now = 0;
            var totalgen = app_myenergy.windnow+solar_now;
            
            var balance = totalgen - use_now;
            
            $("#usenow").html(use_now);
            $("#solarnow").html(solar_now);
            $("#gridwindnow").html(Math.round(app_myenergy.windnow));
            $("#totalgen").html(Math.round(totalgen));
            
            $("#chargerate").html(Math.round(balance));
        });   
        */
        
        $("#myenergy-openconfig").click(function(){
            $("#myenergy-solarpower").val(app_myenergy.solarpower);
            $("#myenergy-housepower").val(app_myenergy.housepower);
            $("#myenergy-annualwind").val(app_myenergy.annual_wind_gen);
            $("#myenergy-windcap").html(Math.round(app_myenergy.my_wind_cap)+"W");
            $("#myenergy-prc3mw").html((100*app_myenergy.my_wind_cap / 5000000).toFixed(3));
            $("#myenergy-config").show();
        });
        
        $("#myenergy-configsave").click(function() {
            $("#myenergy-config").hide();
            app_myenergy.annual_wind_gen = $("#myenergy-annualwind").val();
            app_myenergy.solarpower = $("#myenergy-solarpower").val();
            app_myenergy.housepower = $("#myenergy-housepower").val();
            app_myenergy.my_wind_cap = ((app_myenergy.annual_wind_gen / 365) / 0.024) / app_myenergy.capacity_factor;
            
            // Save config to db
            var config = app.config;
            if (config==false) config = {};
            config["myenergy"] = {
                "annualwindgen": app_myenergy.annual_wind_gen,
                "solarpower": app_myenergy.solarpower,
                "housepower": app_myenergy.housepower
            };
            app.setconfig(config);
            
            var timeWindow = (3600000*24.0*1);
            view.end = +new Date;
            view.start = view.end - timeWindow;
            app_myenergy.draw();
            
        });
        
        $(window).resize(function(){
            app_myenergy.resize();
            app_myenergy.draw();
        });
    },

    show: function() 
    {
        this.livefn();
        this.live = setInterval(this.livefn,10000);
        
        $("body").css("background-color","#222");
        
        $(window).ready(function(){
            $("#footer").css('background-color','#181818');
            $("#footer").css('color','#999');
        });

        app_myenergy.resize();
        app_myenergy.draw();
    },
    
    resize: function() 
    {
        var top_offset = 0;
        var placeholder_bound = $('#myenergy_placeholder_bound');
        var placeholder = $('#myenergy_placeholder');

        var width = placeholder_bound.width();
        var height = $(window).height()*0.55;

        if (height>width) height = width;

        placeholder.width(width);
        placeholder_bound.height(height);
        placeholder.height(height-top_offset);
        
        if (width<=500) {
            $(".electric-title").css("font-size","16px");
            $(".power-value").css("font-size","38px");
            $(".power-value").css("padding-top","12px");
            $(".power-value").css("padding-bottom","8px");
            $(".midtext").css("font-size","14px");
            $("#balanceline").hide();
        } else if (width<=724) {
            $(".electric-title").css("font-size","18px");
            $(".power-value").css("font-size","64px");
            $(".power-value").css("padding-top","22px");
            $(".power-value").css("padding-bottom","12px");
            $(".midtext").css("font-size","18px");
            $("#balanceline").show();
        } else {
            $(".electric-title").css("font-size","22px");
            $(".power-value").css("font-size","85px");
            $(".power-value").css("padding-top","40px");
            $(".power-value").css("padding-bottom","20px");
            $(".midtext").css("font-size","20px");
            $("#balanceline").show();
        }
    },
    
    hide: function() 
    {
        clearInterval(this.windupdater)
    },
    
    livefn: function()
    {
    
        var feeds = app_myenergy.getfeedsbyid();
        
        var use_now = 0;
        var solar_now = 0;
        
        var national_wind = app_myenergy.getvalueremote(67088);
        var prc_of_capacity = national_wind / 8000;
        app_myenergy.wind_now = app_myenergy.my_wind_cap * prc_of_capacity;
        var wind_now = app_myenergy.wind_now;
        
        if (feeds[app_myenergy.housepower]!=undefined) use_now = 1*feeds[app_myenergy.housepower].value;
        if (feeds[app_myenergy.solarpower]!=undefined) solar_now = 1*feeds[app_myenergy.solarpower].value;
        
        /*
        var use_now = 0;
        var housefeeds = app_myenergy.housepower.split(",");
        for (z in housefeeds) {
            use_now += 1*feeds[housefeeds[z]].value;
        }
        
        var solar_now = 0;
        var solarfeeds = app_myenergy.solarpower.split(",");
        for (z in solarfeeds) {
            solar_now += 1*feeds[solarfeeds[z]].value;
        }*/
        
        var totalgen = solar_now + wind_now ;
        var balance = totalgen - use_now;
        
        $("#gridwindnow").html(Math.round(wind_now));
        $("#solarnow").html(Math.round(solar_now));
        $("#usenow").html(Math.round(use_now));
        
        $("#totalgen").html(Math.round(totalgen));
        $("#chargerate").html(Math.round(balance));
    },
    
    draw: function ()
    {
        var dp = 1;
        var units = "C";
        var fill = false;
        var plotColour = 0;
        
        var options = {
            lines: { fill: fill },
            xaxis: { mode: "time", timezone: "browser", min: view.start, max: view.end},
            //yaxis: { min: 0 },
            grid: {hoverable: true, clickable: true},
            selection: { mode: "x" }
        }
        
        var npoints = 1000;
        interval = Math.round(((view.end - view.start)/npoints)/1000);
        
        if (view.start!=this.lastviewstart || view.end!=this.lastviewend)
        {
            this.lastviewstart = view.start;
            this.lastviewend = view.end;
            
            if (app_myenergy.housepower) app_myenergy.house_data = this.getdata(app_myenergy.housepower,view.start,view.end,interval);
            if (app_myenergy.solarpower) app_myenergy.solar_data = this.getdata(app_myenergy.solarpower,view.start,view.end,interval);
            
            // This section loads the feed data, including summing if multiple are specified
            /*
            var tmp = [];
            var i = 0;
            var feeds = app_myenergy.housepower.split(",");
            for (z in feeds) {
                var feed = feeds[z];
                tmp[i] = this.getdata(feed,view.start,view.end,interval);
                i++;
            }
            
            var power = [];
            app_myenergy.house_data = [];
            for (z in tmp[0]) {
                for (s in tmp) {
                    if (tmp[s][z][1]!=null) power[s] = tmp[s][z][1];
                }
                var sum = 0; for (s in tmp) sum+= power[s];
                app_myenergy.house_data.push([tmp[0][z][0],sum]);
            }
            
            var tmp = [];
            var i = 0;
            var feeds = app_myenergy.solarpower.split(",");
            for (z in feeds) {
                var feed = feeds[z];
                tmp[i] = this.getdata(feed,view.start,view.end,interval);
                i++;
            }
            
            var power = [];
            app_myenergy.solar_data = [];
            for (z in tmp[0]) {
                for (s in tmp) {
                    if (tmp[s][z][1]!=null) power[s] = tmp[s][z][1];
                }
                var sum = 0; for (s in tmp) sum+= power[s];
                app_myenergy.solar_data.push([tmp[0][z][0],sum]);
            }
            */
            
            app_myenergy.wind_data = this.getdataremote(67088,view.start,view.end,interval);
        }
        
        var use = 0;
        var gen = 0;
        var wind = 0;
        
        
        
        var use_data = [];
        var gen_data = [];
        var mywind_data = [];
        var bal_data = [];
        var store_data = [];
        
        
        var store = 0;
        var mysolar = 0;
        
        var total_solar_kwh = 0;
        var total_wind_kwh = 0;
        var total_use_kwh = 0;
        var total_use_direct_kwh = 0;
        
        var npoints = 0;
        
        if (app_myenergy.house_data.length>2) {
            interval = (app_myenergy.house_data[1][0] - app_myenergy.house_data[0][0])/1000;
            npoints = app_myenergy.house_data.length;
        } else {
            interval = (app_myenergy.wind_data[1][0] - app_myenergy.wind_data[0][0])/1000;
            npoints = app_myenergy.wind_data.length;
        }
        var t = 0;
        
        for (var z=0; z<npoints; z++) {
            if (app_myenergy.house_data[z]!=undefined && app_myenergy.house_data[z][1]!=null) use = app_myenergy.house_data[z][1];
            if (app_myenergy.solar_data[z]!=undefined && app_myenergy.solar_data[z][1]!=null) mysolar = app_myenergy.solar_data[z][1];
            if (app_myenergy.wind_data[z]!=undefined && app_myenergy.wind_data[z][1]!=null) wind = app_myenergy.wind_data[z][1];
            
            var prc_of_capacity = wind / 8000;
            var mywind = app_myenergy.my_wind_cap * prc_of_capacity;
            
            gen = mysolar + mywind;
            
            var balance = gen - use;
            
            if (balance>=0) total_use_direct_kwh += (use*interval)/(1000*3600);
            if (balance<0) total_use_direct_kwh += (gen*interval)/(1000*3600);
            
            var store_change = (balance * interval) / (1000*3600);
            store += store_change;
            
            total_solar_kwh += (mysolar*interval)/(1000*3600);
            total_wind_kwh += (mywind*interval)/(1000*3600);
            total_use_kwh += (use*interval)/(1000*3600);
            
            var time = 0;
            if (app_myenergy.house_data[z]!=undefined) time = app_myenergy.house_data[z][0];
            if (app_myenergy.wind_data[z]!=undefined) time = app_myenergy.wind_data[z][0];
            use_data.push([time,use]);
            gen_data.push([time,gen]);
            bal_data.push([time,balance]);
            store_data.push([time,store]);
            mywind_data.push([time,mywind]);
            
            t += interval;
        }
        $("#total_solar_kwh").html(total_solar_kwh.toFixed(1));
        $("#total_wind_kwh").html(total_wind_kwh.toFixed(1));
        $("#total_gen_kwh").html((total_solar_kwh+total_wind_kwh).toFixed(1));
        
        $("#total_use_kwh").html((total_use_kwh).toFixed(1));
        $("#total_use_direct_kwh").html((total_use_direct_kwh).toFixed(1)+"kWh ("+Math.round(100*total_use_direct_kwh/total_use_kwh)+"%)");
        
        $("#total_use_via_store_kwh").html((total_use_kwh-total_use_direct_kwh).toFixed(1)+"kWh ("+Math.round(100*(1-(total_use_direct_kwh/total_use_kwh)))+"%)");
        
        
        options.xaxis.min = view.start;
        options.xaxis.max = view.end;
        
        $.plot($('#myenergy_placeholder'), 
            [
                {data:gen_data,color: "#dccc1f", lines:{lineWidth:0, fill:1.0}},
                {data:mywind_data,color: "#2ed52e", lines:{lineWidth:0, fill:1.0}},
                {data:use_data,color: "#0699fa",lines:{lineWidth:0, fill:0.8}},
                {data:store_data,yaxis:2, color: "#888"}
            ], options
        );
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
    },
    
    getdataremote: function(id,start,end,interval)
    {   
        var data = [];
        $.ajax({                                      
            url: path+"app/dataremote.json",
            data: "id="+id+"&start="+start+"&end="+end+"&interval="+interval+"&skipmissing=0&limitinterval=0",
            dataType: 'json',
            async: false,                      
            success: function(data_in) { data = data_in; } 
        });
        return data;
    },
    
    getvalueremote: function(id)
    {   
        var value = 0;
        $.ajax({                                      
            url: path+"app/valueremote.json",                       
            data: "id="+id, dataType: 'text', async: false,                      
            success: function(data_in) {
                value = data_in;
            } 
        });
        return value;
    }
}
