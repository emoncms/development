<?php

// 1) Get values from url
// Example url: http://yoursite.org/emon.php?L=20.0&C=20.0&R=20.0&P=20.0

//Last and current power consumption.
$lastvalue = $_GET["L"];
$currentvalue = $_GET["C"];

//change data
$rpChange = $_GET["R"];
$pfChange = $_GET["P"];

// 2) Open the database

//Connect to mysql
$con = mysql_connect("localhost","username","password");
if (!$con) {die('Could not connect: ' . mysql_error());}
//Select the database
mysql_select_db("databaseName",$con);

// 3) Insert the data!

//Inserts current power consumption data into DATA table
//This is consumption data that is updated on significant step change
//should be renamed to something more sensible like POWER
//A = power values, B = timestamp
$vtime = (time()*1000)-1000; // current time -1 second
mysql_query("INSERT INTO DATA (A, B) VALUES ($lastvalue,$vtime)");
$vtime = time()*1000; // current time
mysql_query("INSERT INTO DATA (A, B) VALUES ($currentvalue,$vtime)");

//Inserts event information into EVENT log table.
//RP = real power change, PF = power factor change, CRP = current real power, TIME = timestamp
mysql_query("INSERT INTO EVENT (RP, PF, TIME, CRP) VALUES ($rpChange,$pfChange,$vtime,$currentvalue)");

//close!!
mysql_close($con);

?>
