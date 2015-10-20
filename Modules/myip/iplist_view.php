<?php global $path; ?>

<br>
<h2>My Local IP</h2>
<p>A local emonBase base-station is required and port forwarding needs to be configured to use this feature</p>

<table class="table">
    <tr>
        <th>WAN: If you're away</th>
        <th>LAN: If you're at home</th>
        <th>Last updated</th>
    </tr>
    <tr>
        <td><span id="localip"></span></td>
        <td><span class="lanip"></span></td>
        <td><span id="lastupdated"></span>s ago</td>
    </tr>
</table>

 
<br><br>
<p><b>API Example:</b></p>
<pre>
<?php echo $path; ?>myip/set.json?apikey=<span id="apikey"></span>
</pre>
<p><i>To view your local emonBase's emoncms via the Internet, you must open port 80 (HTTP) on your router's firewall. This is also known as port forwarding.</i></p>



<script>
    var path = "<?php echo $path; ?>";
    
    var result = {};
    $.ajax({ url: path+"user/get.json", dataType: 'json', async: false, success: function(data) {result = data;} });
    
    $("#apikey").html(result.apikey_write);
    
    var result = {};
    $.ajax({ url: path+"myip/get.json", dataType: 'json', async: false, success: function(data) {result = data;} });

    $("#localip").html("<a href='http://"+result.ipaddress+"'>http://"+result.ipaddress+"</a>");
    $("#lastupdated").html(result.time);
    $(".lanip").html("http://"+result.lanip+"/emoncms<br>ssh pi@"+result.lanip);
</script>
