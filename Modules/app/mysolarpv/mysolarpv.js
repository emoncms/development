var app_mysolarpv = {

    solarpower: false,
    housepower: false,
    
    feedname: "",
    
    live: false,

    windnow: 0,
    lastviewstart: 0,
    lastviewend: 0,
    show_balance_line: 0,
    
    house_data: [],
    solar_data: [],
    wind_data: [],

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
        if (app.config["mysolarpv"]!=undefined) {
            this.solarpower = app.config["mysolarpv"].solarpower;
            this.housepower = app.config["mysolarpv"].housepower;
        } else {
            // Auto scan by feed names
            var feeds = app_mysolarpv.getfeedsbyid();
            for (z in feeds)
            {
                var name = feeds[z].name.toLowerCase();
                
                if (name.indexOf("house_power")!=-1) {
                    app_mysolarpv.housepower = z;
                }
                
                if (name.indexOf("solar_power")!=-1) {
                    app_mysolarpv.solarpower = z;
                }
            }
        }
        
        var timeWindow = (3600000*24.0*1);
        view.end = +new Date;
        view.start = view.end - timeWindow;
        
        var placeholder = $('#mysolarpv_placeholder');
        
        $("#mysolarpv_zoomout").click(function () {view.zoomout(); app_mysolarpv.draw();});
        $("#mysolarpv_zoomin").click(function () {view.zoomin(); app_mysolarpv.draw();});
        $('#mysolarpv_right').click(function () {view.panright(); app_mysolarpv.draw();});
        $('#mysolarpv_left').click(function () {view.panleft(); app_mysolarpv.draw();});
        $('.time').click(function () {view.timewindow($(this).attr("time")); app_mysolarpv.draw();});
        
        $("#balanceline").click(function () { 
            if ($(this).html()=="Show energy balance") {
                app_mysolarpv.show_balance_line = 1;
                app_mysolarpv.draw();
                $(this).html("Hide energy balance");
            } else {
                app_mysolarpv.show_balance_line = 0;
                app_mysolarpv.draw();
                $(this).html("Show energy balance");
            }
        });
        
        placeholder.bind("plotselected", function (event, ranges)
        {
            view.start = ranges.xaxis.from;
            view.end = ranges.xaxis.to;
            app_mysolarpv.draw();
        });

        $("#mysolarpv-openconfig").click(function(){
            $("#mysolarpv-solarpower").val(app_mysolarpv.solarpower);
            $("#mysolarpv-housepower").val(app_mysolarpv.housepower);
            $("#mysolarpv-config").show();
        });
        
        $("#mysolarpv-configsave").click(function() {
            $("#mysolarpv-config").hide();
            app_mysolarpv.solarpower = parseInt($("#mysolarpv-solarpower").val());
            app_mysolarpv.housepower = parseInt($("#mysolarpv-housepower").val());
            
            // Save config to db
            var config = app.config;
            if (config==false) config = {};
            config["mysolarpv"] = {
                "solarpower": app_mysolarpv.solarpower,
                "housepower": app_mysolarpv.housepower
            };
            app.setconfig(config);
            
            var timeWindow = (3600000*24.0*1);
            view.end = +new Date;
            view.start = view.end - timeWindow;
            app_mysolarpv.draw();
            
        });
 
        $(window).resize(function(){
            app_mysolarpv.resize();
            app_mysolarpv.draw();
        });
        
        /*
        $(document).on("socketio_msg",function( event, msg ) {
            var use_now = 1*nodes['6'].values[1] + 1*nodes['6'].values[2];
            var solar_now = 1*nodes['10'].values[2];
            if (solar_now<10) solar_now = 0;
            var totalgen = app_mysolarpv.windnow+solar_now;
            
            var balance = totalgen - use_now;
            
            $("#usenow").html(use_now);
            $("#solarnow").html(solar_now);
            $("#gridwindnow").html(Math.round(app_mysolarpv.windnow));
            $("#totalgen").html(Math.round(totalgen));
            
            $("#chargerate").html(Math.round(balance));
        });   
        */
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

        app_mysolarpv.resize();
        app_mysolarpv.draw();
        app_mysolarpv.draw_bargraph();
    },
    
    resize: function() 
    {
        var top_offset = 0;
        var placeholder_bound = $('#mysolarpv_placeholder_bound');
        var placeholder = $('#mysolarpv_placeholder');

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
        clearInterval(this.live);
    },
    
    livefn: function()
    {
        var feeds = app_mysolarpv.getfeedsbyid();
        var use_now = 0;
        if (feeds[app_mysolarpv.housepower]!=undefined) use_now = feeds[app_mysolarpv.housepower].value;
        var solar_now = 0;
        if (feeds[app_mysolarpv.solarpower]!=undefined) solar_now = feeds[app_mysolarpv.solarpower].value;
        
        var balance = solar_now - use_now;
        
        if (balance>0) $("#balance").html("<span style='color:#2ed52e'>EXPORTING: <b>"+Math.round(Math.abs(balance))+"W</b></span>");
        if (balance==0) $("#balance").html("");
        if (balance<0) $("#balance").html("<span style='color:#d52e2e'>IMPORTING: <b>"+Math.round(Math.abs(balance))+"W</b></span>");
        
        $("#solarnow").html(solar_now);
        $("#usenow").html(use_now);
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
            yaxis: { min: 0 },
            grid: {hoverable: true, clickable: true},
            selection: { mode: "x" }
        }
        
        var npoints = 1500;
        interval = Math.round(((view.end - view.start)/npoints)/1000);
        
        if (view.start!=this.lastviewstart || view.end!=this.lastviewend)
        {
            this.lastviewstart = view.start;
            this.lastviewend = view.end;
            
            if (this.solarpower) app_mysolarpv.solar_data = this.getdata(this.solarpower,view.start,view.end,interval);
            if (this.housepower) app_mysolarpv.house_data = this.getdata(this.housepower,view.start,view.end,interval);
        }
        
        var use_data = [];
        var gen_data = [];
        var bal_data = [];
        var store_data = [];
        
        if (app_mysolarpv.house_data.length!=0) {

        var interval = (app_mysolarpv.house_data[1][0] - app_mysolarpv.house_data[0][0])/1000;
        var t = 0;
        
        var store = 0;
        var use = 0;
        var mysolar = 0;
        
        var total_solar_kwh = 0;
        var total_use_kwh = 0;
        var total_use_direct_kwh = 0;
        
        for (z in app_mysolarpv.house_data) {
            if (app_mysolarpv.house_data[z]!=undefined && app_mysolarpv.house_data[z][1]!=null) use = app_mysolarpv.house_data[z][1];
            if (app_mysolarpv.solar_data[z]!=undefined && app_mysolarpv.solar_data[z][1]!=null) mysolar = app_mysolarpv.solar_data[z][1];
            
            var balance = mysolar - use;
            
            if (balance>=0) total_use_direct_kwh += (use*interval)/(1000*3600);
            if (balance<0) total_use_direct_kwh += (mysolar*interval)/(1000*3600);
            
            var store_change = (balance * interval) / (1000*3600);
            store += store_change;
            
            total_solar_kwh += (mysolar*interval)/(1000*3600);
            total_use_kwh += (use*interval)/(1000*3600);
            
            var time = app_mysolarpv.house_data[z][0];
            use_data.push([time,use]);
            gen_data.push([time,mysolar]);
            bal_data.push([time,balance]);
            store_data.push([time,store]);
            
            t += interval;
        }
        $("#total_solar_kwh").html(total_solar_kwh.toFixed(1));
        
        $("#total_use_kwh").html((total_use_kwh).toFixed(1));
        $("#total_use_direct_kwh").html((total_use_direct_kwh).toFixed(1)+"kWh ("+Math.round(100*total_use_direct_kwh/total_use_kwh)+"%)");
        
        $("#total_use_via_store_kwh").html((total_use_kwh-total_use_direct_kwh).toFixed(1)+"kWh ("+Math.round(100*(1-(total_use_direct_kwh/total_use_kwh)))+"%)");
        
        }
        options.xaxis.min = view.start;
        options.xaxis.max = view.end;
        
        var series = [
            {data:gen_data,color: "#dccc1f", lines:{lineWidth:0, fill:1.0}},
            {data:use_data,color: "#0699fa",lines:{lineWidth:0, fill:0.8}}
        ];
        
        if (app_mysolarpv.show_balance_line) series.push({data:store_data,yaxis:2, color: "#888"});
        
        $.plot($('#mysolarpv_placeholder'),series,options);
    },
    
    draw_bargraph: function() {
        /*
        var timeWindow = (3600000*24.0*365);
        var end = +new Date;
        var start = end - timeWindow;
        var interval = 3600*24;
        
        var kwh_data = this.getdata(69211,start,end,interval);
        var kwhd_data = [];
        
        for (var day=1; day<kwh_data.length; day++)
        {
            var kwh = kwh_data[day][1] - kwh_data[day-1][1];
            if (kwh_data[day][1]==null || kwh_data[day-1][1]==null) kwh = 0;
            kwhd_data.push([kwh_data[day][0],kwh]);
        }
    
        var options = {
            bars: { show: true, align: "center", barWidth: 0.75*3600*24*1000, fill: true},
            xaxis: { mode: "time", timezone: "browser"},
            grid: {hoverable: true, clickable: true},
            selection: { mode: "x" }
        }
        
        var series = [];
        
        series.push({
            data: kwhd_data,
            color: "#dccc1f",
            lines: {lineWidth:0, fill:1.0}
        });
        
        $.plot($('#mysolarpv_bargraph'),series,options);
        */
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
}
