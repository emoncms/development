
var nid = urlParams['nid'];
var vid = urlParams['vid'];

var interval = 3600*24;
var top_offset = 0;

var placeholder_bound = $('#placeholder_bound');
var placeholder = $('#placeholder').width(placeholder_bound.width()).height($('#placeholder_bound').height()-top_offset);

var data = [];

var timeWindow = (3600000*12.0);
view.start = +new Date - timeWindow;
view.end = +new Date;

var options = {
    lines: {show: true },
    xaxis: { mode: "time", timezone: "browser", min: view.start, max: view.end},
    grid: {hoverable: true, clickable: true},
    selection: { mode: "x" }
}
    
$(window).resize(function(){
    placeholder.width(placeholder_bound.width());
    options.xaxis.min = view.start;
    options.xaxis.max = view.end;
    $.plot(placeholder, [data], options);
});

$(function() {

    draw();

    $("#zoomout").click(function () {view.zoomout(); draw();});
    $("#zoomin").click(function () {view.zoomin(); draw();});
    $('#right').click(function () {view.panright(); draw();});
    $('#left').click(function () {view.panleft(); draw();});
    $('.time').click(function () {view.timewindow($(this).attr("time")); draw();});

    placeholder.bind("plotselected", function (event, ranges)
    {
        view.start = ranges.xaxis.from;
        view.end = ranges.xaxis.to;
        draw();
    });

    placeholder.bind("plothover", function (event, pos, item)
    {
        if (item) {
            var mdate = new Date(item.datapoint[0]);
            if (units=='') $("#stats").html(item.datapoint[1].toFixed(1)+" | "+mdate.format("ddd, mmm dS, yyyy"));
            if (units=='kWh') $("#stats").html(item.datapoint[1].toFixed(1)+" kWh | "+mdate.format("ddd, mmm dS, yyyy"));
            if (units=='C') $("#stats").html(item.datapoint[1].toFixed(1)+" C | "+mdate.format("ddd, mmm dS, yyyy"));
            if (units=='V') $("#stats").html(item.datapoint[1].toFixed(1)+" V | "+mdate.format("ddd, mmm dS, yyyy"));
            if (units=='A') $("#stats").html(item.datapoint[1].toFixed(2)+" A | "+mdate.format("ddd, mmm dS, yyyy"));
            if (units=='Hz') $("#stats").html(item.datapoint[1].toFixed(2)+" Hz | "+mdate.format("ddd, mmm dS, yyyy"));
        }
    });

    function draw()
    {
        data = [];

        var d = new Date()
        var n = d.getTimezoneOffset();
        var offset = n / -60;
        
        offset = 0;

        var datastart = (Math.round((view.start/1000.0)/interval) * interval)+3600*offset;
        
        $.ajax({                                      
            url: 'data?nid='+nid+'&vid='+vid+'&start='+view.start+'&end='+view.end,
            dataType: 'json',
            async: false,                
            success: function(result) { data = result; } 
        });
        
        options.xaxis.min = view.start;
        options.xaxis.max = view.end;
        $.plot(placeholder, [data], options);
    }
});
