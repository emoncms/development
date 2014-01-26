<?php global $path; 

  if (!isset($_GET['apikey'])) $apikey = ""; else $apikey = $_GET['apikey'];

?>

<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/packetgen/packetgen.js"></script>

<!-- Make the button and label center aligned -->
<div style="margin: 0px auto; max-width:320px; padding:50px;">

  <!-- style and output the label -->
  <div style="font-weight:bold; font-size:32px; color:#aaa; padding-top:10px; float:left;">Heating:</div>
  
  <!-- draw the button -->
  <button id="heating" class="btn btn-large" style="float:right" status=1>On</button>
  
  <div style="clear:both"></div>
  <br>
  
  <!-- setpoint entry -->
  <div class="input-prepend input-append">
    <span class="add-on">Set point: </span>
    <input id="setpoint" type="number" style="width:80px">
    <button id="setsetpoint" class="btn" type="button">Set</button>
  </div>

</div>

<script>
  var path = "<?php echo $path; ?>";
  var apikey = "<?php echo $apikey ?>";
  packetgen.apikey = apikey;
  
  $("body").css('background-color','#222');
  
  // get current control packet state
  var packet = packetgen.get();
  
  // get heating status
  var setpoint = packet[4].value * 0.01;
  var status = packet[6].value;
  
  // Set setpoint initial value
  $("#setpoint").val(setpoint.toFixed(1));
  
  // Set initial button state
  
  // If the heating is on color the button green and change the text to on
  if (status=='true' || status==1) {
    // jquery items are chained which means apply all the listed properties to #heating
    $("#heating").attr('status',1).addClass('btn-success').html("On");
  }
  
  // If the heating is off color the button red and change the text to off
  if (status=='false' || status==0) {
    $("#heating").attr('status',0).addClass('btn-danger').html("Off");
  }
  
  // The on button click event
  $("#heating").click(function(){
  
    var status = $(this).attr('status');
    console.log(status);
    if (status == 1) {
      $("#heating").attr('status',0).removeClass('btn-success').addClass('btn-danger').html("Off");
      packet[4].value = 0;
      packet[6].value = 0;
      // save the updated control packet
      packetgen.set(packet,5);
    } else {
      $("#heating").attr('status',1).removeClass('btn-danger').addClass('btn-success').html("On");
      packet[4].value = $("#setpoint").val() * 100;
      packet[6].value = 1;
      // save the updated control packet
      packetgen.set(packet,5);
    }
  
  });
  
  // Set the setpoint
  $("#setsetpoint").click(function(){
    packet[4].value = $("#setpoint").val() * 100;
    packet[6].value = 1;
        
    packetgen.set(packet,5);
    
    var status = $("#heating").attr('status');
    if (status!=1) $("#heating").attr('status',1).removeClass('btn-danger').addClass('btn-success').html("On");
  });
  
</script>
