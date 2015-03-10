var app = {

    basepath: path+"Modules/app/",

    include: {},
    
    loaded: {},

    load: function(appname) 
    {
        app.loaded[appname] = true;
        // We load here the html of the app into an dedicated element for that app
        // when an app is loaded its html remains in the dom even when viewing another app
        // an app is just hidden and shown depending on the visibility settings.
        
        // we check here if the app has been loaded to the dom, if not we load it
        
        var appdom = $("#app_"+appname);
        
        if (appdom.length) return true;
        
    
        var html = "";
        $.ajax({url: app.basepath+appname+"/"+appname+".html", async: false, cache: false, success: function(data) {html = data;} });
        
        $("#content").append('<div class="apps" id="app_'+appname+'" style="display:none"></div>');
        $("#app_"+appname).html(html);

        $.ajax({
            url: app.basepath+appname+"/"+appname+".js",
            dataType: 'script',
            async: false
        });
        
        // ----------------------------------------------------------
        // Included javascript loader
        // ----------------------------------------------------------
        var include = window["app_"+appname].include;
        for (i in include) {
            var file = include[i];
            if (app.include[file]==undefined)
            {
                app.include[file] = true;
                $.ajax({
                    url: path+file,
                    dataType: 'script',
                    async: false
                });
            }
        }
        
        window["app_"+appname].init();
        
        return true;
    },
    
    show: function(appname)
    {
        if (app.loaded[appname]==undefined) app.load(appname); 
        $(".apps").hide();
        $("#app_"+appname).show();
        if (window["app_"+appname]!=undefined) window["app_"+appname].show();
    },
    
    hide: function(appname)
    {
        $("#app_"+appname).hide();
        if (window["app_"+appname]!=undefined) window["app_"+appname].hide();
    },
    
    getconfig: function()
    {
        var config = {};
        var apikeystr = "";
        if (window.apikey!=undefined) apikeystr = "?apikey="+apikey;
        $.ajax({ url: path+"app/getconfig.json"+apikeystr, dataType: 'json', async: false, success: function(data) {config = data;} });
        app.config = config;
        return config;
    },
    
    setconfig: function(config)
    {
        $.ajax({ url: path+"app/setconfig.json", data: "data="+JSON.stringify(config), async: false, success: function(data){} });
    }
};
