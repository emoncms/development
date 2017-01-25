<!-- Author: Trystan Lea, part of the openenergymonitor project GNU General Public Licence -->

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Graph of Energy Consumption</title>
 
    <link rel="stylesheet" type="text/css" href="style.css" />

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

     <?php
     include 'DB.php';
     
     $con = mysql_connect('localhost', 'username', 'password');
     //Load events table
     $db = mysql_select_db('openener_power', $con);
     $eventTypes = mysql_query("select * from eventTypes");
     ?>

     <table border="0" cellspacing="1" cellpadding="1">
     <tr>
     <th>name</th>
     <th>max</th>
     <th>rpOn</th>
     <th>rpOff</th>
     <th>pfOn</th>
     <th>pfOff</th>
     <th>rpOnMin</th>
     <th>rpOnMax</th>
     <th>rpOffMin</th>
     <th>rpOffMax</th>
     <th>pfOnMin</th>
     <th>pfOnMax</th>
     <th>pfOffMin</th>
     <th>pfOffMax</th>
     
     </tr>
     
  
   <?php

     $i=0;
     $numberOfLoadTypes = mysql_numrows($eventTypes);

     for ($i=0; $i<$numberOfLoadTypes; $i++)
     {
     
     ?>
<tr>
  <td><?php echo mysql_result($eventTypes ,$i, "name");?></td>
  <td><?php echo  mysql_result($eventTypes ,$i, "max");?></td>
 
  <td><?php echo mysql_result($eventTypes ,$i, "rpCheckOn"); ?></td>
  <td><?php echo mysql_result($eventTypes ,$i, "rpCheckOff");?></td>
  <td><?php echo mysql_result($eventTypes ,$i, "pfCheckOn"); ?></td>
  <td><?php echo mysql_result($eventTypes ,$i, "pfCheckOff");?></td>
  
  <td><?php echo mysql_result($eventTypes ,$i, "rpLoadOnMin");?></td>
  <td><?php echo mysql_result($eventTypes ,$i, "rpLoadOnMax"); ?></td>
  <td><?php echo mysql_result($eventTypes ,$i, "rpLoadOffMin");?></td>
  <td><?php echo mysql_result($eventTypes ,$i, "rpLoadOffMax");?></td> 
  
  <td><?php echo mysql_result($eventTypes ,$i, "pfLoadOnMin");?></td> 
  <td><?php echo mysql_result($eventTypes ,$i, "pfLoadOnMax")?></td>
  <td><?php echo mysql_result($eventTypes ,$i, "pfLoadOffMin");?></td> 
  <td><?php echo mysql_result($eventTypes ,$i, "pfLoadOffMax");?></td> 
 </tr>
 <?php } ?>

   
   
    
       
       </table>

   </div>
 </div>
 <div id="footer"></div>
</div>




  </body>

</html>  
