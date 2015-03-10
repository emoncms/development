<?php global $path; ?>

<script>
    var path = "http://"+window.location.host+"/"; 
</script>

<link href="<?php echo $path; ?>Modules/app/style.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo $path; ?>Modules/app/app.js"></script>
        
<div id="content"></div>

<script>

console.log(path);

var config = app.getconfig();

var nodes = {};
  
var appname = "myelectric";
req = (window.location.hash).substring(1).split("/");

appname = req[0];
if (appname=="") appname = "myelectric";

app.show(appname);

$(window).on('hashchange', function() {
    app.hide(appname);
    req = (window.location.hash).substring(1).split("/");
    appname = req[0];
    app.load(appname);
    app.show(appname);
});

$(document).ready(function(){

});

</script>
