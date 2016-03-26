<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to do stuff.</br>";
} else {
	  if ($userrow['session_name'] != "Developers") {
    echo "Hey! This tool is for the developers only. Nice try, pal.";
  } else {
$result = mysql_query("SELECT `username` FROM Players");
while ($row = mysql_fetch_array($result)) {
  $losername = $row['username'];
  $foundmsg = false;
  $msgresult = mysql_query("SELECT `username` FROM Messages WHERE `Messages`.`username` = '$losername'");
  while ($msgrow = mysql_fetch_array($msgresult)) $foundmsg = true;
  if (!$foundmsg) {
  	mysql_query("INSERT INTO `Messages` (`username`) VALUES ('$losername');"); //Create entry in message table.
  	echo $losername . " lacked message table</br>";
  }
  $foundmsg = false;
  $msgresult = mysql_query("SELECT `username` FROM Echeladders WHERE `Echeladders`.`username` = '$losername'");
  while ($msgrow = mysql_fetch_array($msgresult)) $foundmsg = true;
  if (!$foundmsg) {
  	mysql_query("INSERT INTO `Echeladders` (`username`) VALUES ('$losername');"); //Give the player an Echeladder. Players love echeladders.
  	echo $losername . " lacked echeladder table</br>";
  }
  $foundmsg = false;
  $msgresult = mysql_query("SELECT `username` FROM Ability_Patterns WHERE `Ability_Patterns`.`username` = '$losername'");
  while ($msgrow = mysql_fetch_array($msgresult)) $foundmsg = true;
  if (!$foundmsg) {
  	mysql_query("INSERT INTO `Ability_Patterns` (`username`) VALUES ('$losername');"); //Create entry in pattern table.
  	echo $losername . " lacked ability pattern table</br>";
  }
  }
}
}
?>