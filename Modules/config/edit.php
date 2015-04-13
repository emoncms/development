<?php global $path; ?>

<br>
<h3>EmonHub Config Editor</h3>
<button id="show-logview" style="float:right">Log viewer</button>
<button id="show-editor" style="float:right">Editor</button>

<div id="editor">
    <button class="save">Save</button><br><br>
    <textarea id="configtextarea" style="width:100%; height:600px"></textarea>
    <button class="save">Save</button>
</div>

<div id="logview" style="display:none">
    <br><br>
    <pre id="logviewpre"></pre>
    <button class="logrefresh">Refresh</button>
</div>

<script>

var path = "<?php echo $path; ?>";

var config = "";

$.ajax({ 
    url: path+"config/get", 
    dataType: 'text', async: false, 
    success: function(data) {
        config = data;
    } 
});

$("#configtextarea").val(config);

$(".save").click(function(){
    config = $("#configtextarea").val();
    $.ajax({ type: "POST", url: path+"config/set", data: "config="+config, async: false, success: function(data){console.log(data);} });
});

$(".logrefresh").click(function(){
    log_refresh();
});

$("#show-editor").click(function(){
    $("#editor").show();
    $("#logview").hide();
});

$("#show-logview").click(function(){
    log_refresh();
    $("#logview").show();
    $("#editor").hide();
});

function log_refresh()
{
    $.ajax({ 
        url: path+"config/getlog", 
        dataType: 'text', async: false, 
        success: function(data) {
            $("#logviewpre").html(data);
        } 
    });
}


</script>
