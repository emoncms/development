<!-- Author: Trystan Lea, part of the openenergymonitor project GNU General Public Licence -->

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Graph of Energy Consumption</title>
 
    <link rel="stylesheet" type="text/css" href="style.css" />
        <!--[if IE]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->
    <script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="flot/jquery.flot.pie.js"></script> 

 </head>
 <body>

 <div id="bound">
   <div id="header"></div>
   <div id="box">
     <div id="topd">
       <div id="title">Event Log</div> 
       <div id="menu">
         <A href="infer.php">Event</A>
       </div>
     </div>

     <div id="maintext">
 
     <p>This page automatically tags events with likely cause</p>

     <?php
     include 'DB.php';
     
     $ctime = time();
     $stime = strtotime("today")*1000;
     $etime = strtotime("now")*1000;
     $con = mysql_connect('localhost', 'username', 'password');
     
     //Load events table
     $db = mysql_select_db('openener_power', $con);
     $result = mysql_query("select * from EVENT where (TIME>($stime) && TIME<($etime)) order by TIME Desc");

     $eventTypes = mysql_query("select * from eventTypes");



     $num=mysql_numrows($result);
     echo "<p>Number of events: $num </p>";
     ?>

     <table border="0" cellspacing="2" cellpadding="5">
     <tr>
     <th>Time</th>
     <th>Real Power</th>
     <th>RP Change</th>
     <th>PF Change</th>
     <th>Event</th>
     
     <th>2L</th>
     <th>1L</th>
     <th>F</th>
     <th>SP</th>
     <th>IH</th>
     <th>WM</th>
     <th>4L</th>
     </tr>
     
     <?

$i=0;
$numberOfLoadTypes = mysql_numrows($eventTypes);

for ($i=0; $i<$numberOfLoadTypes; $i++)
{
  $loadName[$i] = mysql_result($eventTypes ,$i, "name");
  $loadNumberMax[$i] = mysql_result($eventTypes ,$i, "max");
  //Checks todo
  $rpCheckOn[$i] = mysql_result($eventTypes ,$i, "rpCheckOn"); 
  $rpCheckOff[$i] = mysql_result($eventTypes ,$i, "rpCheckOff");
  $pfCheckOn[$i] = mysql_result($eventTypes ,$i, "pfCheckOn"); 
  $pfCheckOff[$i] = mysql_result($eventTypes ,$i, "pfCheckOff");
  //RealPower On/Off clauses
  $rpLoadOnMin[$i] = mysql_result($eventTypes ,$i, "rpLoadOnMin"); 
  $rpLoadOnMax[$i] = mysql_result($eventTypes ,$i, "rpLoadOnMax"); 
  $rpLoadOffMin[$i] = mysql_result($eventTypes ,$i, "rpLoadOffMin"); 
  $rpLoadOffMax[$i] = mysql_result($eventTypes ,$i, "rpLoadOffMax"); 
  //PowerFactor On/Off clauses
  $pfLoadOnMin[$i] = mysql_result($eventTypes ,$i, "pfLoadOnMin"); 
  $pfLoadOnMax[$i] = mysql_result($eventTypes ,$i, "pfLoadOnMax"); 
  $pfLoadOffMin[$i] = mysql_result($eventTypes ,$i, "pfLoadOffMin"); 
  $pfLoadOffMax[$i] = mysql_result($eventTypes ,$i, "pfLoadOffMax"); 
  //Number of these loads on right now.
  $loadNumber[$i] = 0; $loadPower[$i] = 0; $loadTime[$i] = 0; $loadKWH[$i] = 0;
}


     $ei=($num-1);
    
     $power = 0;
     $ltime = 0;

     $tkwh = 0;
     $ttime_s=0;
     
     $time=mysql_result($result,$ei,"TIME")/1000;
     
     //Here's the while loop --------------------------------------------
     while ($ei >-1) {
     $event="";
     $error="";
     $ne=0;

     //Reads the data in from mysql
     $rpChange=mysql_result($result,$ei,"RP");
     $pfChange=mysql_result($result,$ei,"PF");
     $ltime = $time;
     $time=mysql_result($result,$ei,"TIME")/1000;
     $crp=mysql_result($result,$ei,"CRP");

      //For all load types.
     for ($i=0; $i<$numberOfLoadTypes; $i++)
     {     
     //=================Appliance On Check==============================================
     //Reset checks
     $rpInRange = 0;
     $pfInRange = 0;

     //RealPower check
     if ($rpCheckOn[$i] == 1)
     { if ($rpChange>$rpLoadOnMin[$i] && $rpChange<$rpLoadOnMax[$i]) $rpInRange = 1;
     } else { $rpInRange = 1;}
     //PowerFactor check
     if ($pfCheckOn[$i] == 1)
     { if ($pfChange>$pfLoadOnMin[$i] && $pfChange<$pfLoadOnMax[$i])  $pfInRange = 1;
     } else { $pfInRange = 1;}
     //...
     if ($rpInRange == 1 && $pfInRange == 1)
     {
       $event = "$loadName[$i] on"; $ne++;
       if ($loadNumber[$i]<$loadNumberMax[$i]) {$loadNumber[$i]++; $loadPower[$i]+= $rpChange;}
       else { $error = "max error"; }
     }

     //=================Appliance Off Check==============================================
     //Reset checks
     $rpInRange = 0;
     $pfInRange = 0;

     //RealPower check
     if ($rpCheckOff[$i] == 1)
     { if ($rpChange<$rpLoadOffMin[$i] && $rpChange>$rpLoadOffMax[$i]) $rpInRange = 1;
     } else { $rpInRange = 1;}
     //PowerFactor check
     if ($pfCheckOff[$i] == 1)
     { if ($pfChange<$pfLoadOffMin[$i] && $pfChange>$pfLoadOffMax[$i])  $pfInRange = 1;
     } else { $pfInRange = 1;}
     //...
     if ($rpInRange == 1 && $pfInRange == 1)
     {
       $event = "$loadName[$i] off"; $ne++;
       if ($loadNumber[$i]>0) {$loadNumber[$i]--; $loadPower[$i] = 0;}
       else { $error = "min error"; }
     }
     //=====================================================================================

     //Load time and kwh accumulation ---------------------------------

	$loadTime[$i] += $loadNumber[$i] * ( $time - $ltime );
        $loadKWH[$i] += ((($time - $ltime)/3600)*($loadPower[$i]/1000));
     }
     //----------------------------------------------------------------

     //Power accumulation, to test discrepancy with actual power.
     $power += $rpChange;
     //Total kwh and time calculation.
     $ttime_s += ( $time - $ltime );
     $tkwh += ((($time - $ltime)/3600)*($crp/1000));

     //If more than one event type is registered for an event print error.
     if ($ne>1) $error = "too many events";

     //If real power change is the same as the real power, register that a restart has probably occured.
     if ($rpChange == $crp) $error = "probable restart";
     ?>

     <tr>
       <td><? echo date("d/m/y h:i:s",($time)); ?></td>
       <td><? echo $crp; ?></td>
       <td><? echo $rpChange; ?></td>
       <td><? echo $pfChange; ?></td>
       <td><? echo $event; ?></td>
    
       
       <?
       //loadNumber output
       for ($i=0; $i<$numberOfLoadTypes; $i++)
       {
         //if ($loadNumber[$i]!=0) 
         echo "<td> $loadNumber[$i] </td>";
       }
       ?>

       <td><? echo $error; ?></td>    
     </tr>

     <?$ei--;}?>

     </table>
     
     <h3>Current Status</h3>
     <p>The number of the following that should be on...</p>

     <?
     //Prints out 'final' how many loads of each type are currently on.
     for ($i=0; $i<$numberOfLoadTypes; $i++) {echo "$loadName[$i] on: $loadNumber[$i] <br/>"; }
     ?>
     
   <br/>
      <?php 
     //Prints out total kwh and time for each load type.
       for ($i=0; $i<$numberOfLoadTypes; $i++)
       {
         echo "$loadName[$i]: ";
         
         echo "<b>";
         echo number_format($loadKWH[$i],2);
         echo "kWh";
         echo "</b>";
         
         echo " on for ";
         
         echo "<b>";
         echo (int)($loadTime[$i] / 60.0);
         echo "mins";
         echo "</b>";
         echo "<br/><br/>";

         $TotalkWh += $loadKWH[$i];
         
         if ($loadKWH[$i]<0.01) {$loadKWH[$i]=0.001;}
       }
     ?> 
    <br/>
    
     <?php
     $TotalkWh = number_format($TotalkWh,2);
     echo "Total kWh of all above: "; 
     echo "<b>";
     echo $TotalkWh ;
     echo "kWh";
     echo "</b>";
     ?>
     <br/><br/>
     
     <?php 
     $tkwh = number_format($tkwh,2);
     echo "Total kWh: $tkwh";?><br/>
     <?php 
     $time_m = $ttime_s / 60.0;
     echo "Total time: "; 
     echo (int)$time_m;
     echo "mins";
     ?><br/><br/>
     
     <?php 
     $tunaccounted = $tkwh-$TotalkWh;
     $tunaccounted = number_format($tunaccounted,2);
     echo "Total unaccounted kWh: $tunaccounted";?><br/>
     
     <?
     
     $baseLoadkWh = 0.067*($ttime_s/3600);
     $baseLoadkWh = number_format($baseLoadkWh,2);
     echo "BaseLoad 67W kWh: $baseLoadkWh";?>
     
     <h3>Appliance kWh breakdown pie-chart:</h3>
     
     <div id="placeholder" style="width:400px;height:400px;"></div>
     <script id="source" language="javascript" type="text/javascript">
     
     
     var data = [
        { label: "<?php echo $loadName[0] ?>",  data: <?php echo $loadKWH[0] ?>},
        { label: "<?php echo $loadName[1] ?>",  data: <?php echo $loadKWH[1] ?>},
        { label: "<?php echo $loadName[2] ?>",  data: <?php echo $loadKWH[2] ?>},
        { label: "<?php echo $loadName[3] ?>",  data: <?php echo $loadKWH[3] ?>},
        { label: "<?php echo $loadName[4] ?>",  data: <?php echo $loadKWH[4] ?>},
        { label: "<?php echo $loadName[5] ?>",  data: <?php echo $loadKWH[5] ?>},
        { label: "<?php echo $loadName[6] ?>",  data: <?php echo $loadKWH[6] ?>},
       
     ];
     
     
	
     $.plot($("#placeholder"), data,
     {
        series: {
            pie: { 
                show: true
            }
        }
     });
     </script>
     <p>Return to application inference here:  <A href="infer.php">Infer</A></p>

   </div>
 </div>
 <div id="footer"></div>
</div>




  </body>

</html>  
