var app_myenergy = {

    config: {
        solarpower: false,
        housepower: false
    },
    
    feedname: "",
    
    live: false,
    
    annual_ecar_miles: 10000,
    ecar_miles_per_kwh: 4.0,
    daily_traduse: 6,
    annual_onsite_solar_gen: 1754,
    capacity_factor: 0.30,

    windnow: 0,
    lastviewstart: 0,
    lastviewend: 0,
    
    house_data: [],
    solar_data: [],
    wind_data: [],

    // Include required javascript libraries
    include: [
        "static/flot/jquery.flot.min.js",
        "static/flot/jquery.flot.time.min.js",
        "static/flot/jquery.flot.selection.min.js",
        "static/vis.helper.js",
        "static/flot/date.format.js"
    ],

    // App start function
    init: function()
    {
        this.annual_energy_req = (this.daily_traduse*365) + (this.annual_ecar_miles/this.ecar_miles_per_kwh);
        this.annual_wind_gen = this.annual_energy_req - this.annual_onsite_solar_gen;
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
    },

    show: function() 
    {
        this.livefn();
        this.live = setInterval(this.livefn,10000);
        
        $("body").css("background-color","#111");

        var top_offset = 0;
        var placeholder_bound = $('#myenergy_placeholder_bound');
        var placeholder = $('#myenergy_placeholder');

        var width = placeholder_bound.width();
        var height = placeholder_bound.height();

        placeholder.width(width);
        placeholder_bound.height(height);
        placeholder.height(height-top_offset);

        app_myenergy.draw();

        /*
        $(window).resize(function(){
            var width = placeholder_bound.width();
            var height = width * 0.5;

            placeholder.width(width);
            placeholder_bound.height(height);
            placeholder.height(height-top_offset);

            options.xaxis.min = view.start;
            options.xaxis.max = view.end;
            $.plot(placeholder, [{data:data,color: plotColour}], options);
        });
        */
    },
    
    hide: function() 
    {
        clearInterval(this.windupdater)
    },
    
    livefn: function()
    {
        var solar_now = 0;
        var use_now = 0;
        var feeds = {};
        $.ajax({                                      
            url: path+"feed/list.json",
            dataType: 'json',
            async: false,                      
            success: function(data_in) { feeds = data_in; } 
        });

        for (z in feeds)
        {
            var name = feeds[z].name.toLowerCase();
            
            if (name.indexOf("solar_power")!=-1) {
                solar_now = feeds[z].value;
            }
            
            if (name.indexOf("house_power")!=-1) {
                use_now = feeds[z].value;
            }
        }
        
        var totalgen = solar_now;
        var balance = totalgen - use_now;
        
        $("#solarnow").html(solar_now);
        $("#usenow").html(use_now);
        
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
        
        var npoints = 1500;
        interval = Math.round(((view.end - view.start)/npoints)/1000);
        
        if (view.start!=this.lastviewstart || view.end!=this.lastviewend)
        {
            this.lastviewstart = view.start;
            this.lastviewend = view.end;
            
            app_myenergy.solar_data = this.getdata(this.config.solarpower,view.start,view.end,interval);
            app_myenergy.house_data = this.getdata(this.config.housepower,view.start,view.end,interval);
        }
        
        var use = 0;
        var gen = 0;
        var wind = 0;
        
        var interval = (app_myenergy.house_data[1][0] - app_myenergy.house_data[0][0])/1000;
        var t = 0;
        
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
        
        for (z in app_myenergy.house_data) {
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
            
            var time = app_myenergy.house_data[z][0];
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
    
    getdata: function(id,start,end,interval)
    {
        var data = [];
        $.ajax({                                      
            url: path+"feed/history.json",                         
            data: "id="+id+"&start="+start+"&end="+end+"&interval="+interval,
            dataType: 'json',
            async: false,                      
            success: function(data_in) { data = data_in; } 
        });
        return data;
    }
}
