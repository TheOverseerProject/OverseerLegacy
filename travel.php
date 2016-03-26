<?php

//NOTE - this file is now defunct since I decided to make travel abstract.

require 'time.php';
require 'location.php';
session_start();
if (empty($_SESSION['username'])) {
  echo "Log in to view and change your location.</br>";
  echo '</br><a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} else {
  $con = mysql_connect("localhost","theovers_DC","pi31415926535");
  if (!$con)
    {
      echo "Connection failed.\n";
      die('Could not connect: ' . mysql_error());
    }
  
  mysql_select_db("theovers_HS", $con);
  $username=$_SESSION['username'];
  $result = mysql_query("SELECT * FROM Players");
  while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
    if ($row[username] == $username) {
      $userrow = $row;
    }
  }

  //Location changing code will go here, making a list of places that can be visited. Lair is visitable with seven gates; Battlefield is visitable once Lair is cleared.
  
  $result = mysql_query("SELECT * FROM Players");
  while ($row = mysql_fetch_array($result)) {
    if ($row[username] == $userrow[location]) {
      $locationrow = $row;
    }
  }
  $locationstr = location($userrow,$locationrow);
  echo "Current location: $locationstr </br>";
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
?>