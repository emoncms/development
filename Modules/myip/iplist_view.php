<?php global $path; ?>

<br>

<p><i>To view your local emonBase IP address send a request from your emonBase periodically to: <a href="<?php echo $path; ?>myip/set.json"><?php echo $path; ?>myip/set.json?YOUR_API_KEY</a></i></p> 

<h1>http://<span id="localip"></span></h1> 

<p><i>To view your local emonBase emoncms opening firewall http port 80 on your router is required</i></p> 





<script>
    var path = "<?php echo $path; ?>";
    var result = {};
    $.ajax({ url: path+"myip/get.json", dataType: 'json', async: false, success: function(data) {result = data;} });

    $("#localip").html("<a href='http://"+result.ipaddress+"'>"+result.ipaddress+"</a>");
</script>
