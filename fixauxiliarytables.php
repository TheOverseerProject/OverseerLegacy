<?php
session_start();
if (empty($_SESSION['username'])) {
  echo "Log in to fix the overseer's derps</br>";
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} else {
 $con = mysql_connect("localhost","theovers_DC","pi31415926535");
  if (!$con) {
    echo "Connection failed.\n";
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("theovers_HS", $con);
  $username=$_SESSION['username'];
  $result = mysql_query("SELECT `username` FROM Players");
  if ($username != "The Overseer") {
    echo "Hey! This tool is for The Overseer only. Nice try, pal.";
  } else {
    while($row = mysql_fetch_array($result)) {
      $echeresult = mysql_query("SELECT `username` FROM Echeladders WHERE `Echeladders`.`username` = '$row[username]' LIMIT 1;");
      if (!($echerow = mysql_fetch_array($echeresult))) {
	mysql_query("INSERT INTO `Echeladders` (`username`) VALUES ('$row[username]');"); //Give the player an Echeladder. Players love echeladders.
	mysql_query("INSERT INTO `Messages` (`username`) VALUES ('$row[username]');"); //Create entry in message table.
	mysql_query("INSERT INTO `Ability_Patterns` (`username`) VALUES ('$row[username]');"); //Create entry in pattern table.
	echo "$row[username] fixed.</br>";
      }
    }
  }
}
?>