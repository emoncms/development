<?php global $path; ?>

<div id="content-block" style="display:none">
    <div class="topmenu">
        <div class="appname">My MQTT Cloud</div>
        <div style="float:right">
            <div class="username">---</div>
            <img class="logout" src="files/logout.png" style="width:22px; padding:9px; padding-bottom:0px; cursor:pointer">
        </div>
        <div style="clear:both"></div>
    </div>

    <div class="container">
        <div id="devices"></div>
    </div>
</div>

<div id="login-block" style="display:none; text-align:center">
  <div class="login-box">
  <h2>Login</h2>
  <p>MQTT Cloud Example</p>
  <p>
    <input id="username" type="text" placeholder="Username..."><br><br>
    <input id="password" type="password" placeholder="Password..."><br><br>
    <button id="login" class="btn">Login</button> or 
    <button id="register" class="btn">Register</button>
  </p>
  <div id="alert"></div>
  </div>
</div>

<script>
var path = "<?php echo $path; ?>";
var session = JSON.parse('<?php echo json_encode($session); ?>');

if (session && session.userid!=undefined) 
{
    load_content();
} else {
    load_login();
}

function load_content(){
    $("#login-block").hide();
    $("#content-block").show();
    $("body").css("background-color","#eee");
    $("body").css("color","#000");
    
    $(".username").html(session.username);
    $(".username").html(session.username);
    var userid = session.userid;
    
    setTimeout(function(){
        $(".userid").html(userid);
    },100);

    var devices = [];
    $.ajax({                                      
        url: path+"/device/list", dataType: 'json',
        success: function(result) {
            devices = result; 

            var out = "";
            for (var devicename in devices) {
                out += "<div class='device'><div class='device-inner'>";
                out += "<div style='float:right'>";
                out += "<button class='device-button' device='"+devicename+"' property='state' style='height:38px'>OFF</button>";
                out += "</div>";
                out += "<div class='device-title'>"+devices[devicename].title+"</div>";
                out += "<div class='device-name'>"+devicename+"</div>";
                out += "</div></div>";
            }

            $("#devices").html(out);
        }
    });
}

function load_login(){
    $("#content-block").hide();
    $("#login-block").show();
    $("body").css("background-color","#29abe2");
    $("body").css("color","#fff");
}

// -----------------------------------------------------------------------------------
// Authenticated block
// -----------------------------------------------------------------------------------
$("#devices").on("click",".device-button",function(){
    var devicename = $(this).attr("device");
    var property = $(this).attr("property");
    var topic = devicename+"/"+property;

    var state = 0;
    if ($(this).html()=="ON") {
        $(this).html("OFF"); state = 0;
    } else {
        $(this).html("ON");  state = 1;
    }
    
    console.log(topic+" "+state);
    $.ajax({type:"POST", url: path+"/mqtt", data: "topic="+topic+"&message="+state});  
});

// -----------------------------------------------------------------------------------
// Login block
// -----------------------------------------------------------------------------------
$("#login").click(function() {
    var username = $("#username").val();
    var password = $("#password").val();

    $.ajax({                                      
        url: path+"/login",                         
        data: "username="+username+"&password="+password,
        dataType: 'json',
        success: function(result) {
            if (result.userid!=undefined) {
                session = result;
                load_content();
            } else {
                $("#alert").html(result.message);
            }
        }
    });
});

$("#register").click(function() {
    var username = $("#username").val();
    var password = $("#password").val();

    $.ajax({                                      
        url: path+"/register",                         
        data: "username="+username+"&password="+password,
        dataType: 'json',
        success: function(result) {
            if (result.userid!=undefined) {
                session = result;
                load_content();
            } else {
                $("#alert").html(result.message);
            }
        }
    });
});

$(".logout").click(function() {
    $.ajax({                   
        url: path+"/logout",
        dataType: 'text',
        success: function(result) {
            load_login();
        }
    });
});

</script>
