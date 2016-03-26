<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to do stuff.</br>";
} else {
	  if ($userrow['session_name'] != "Developers") {
    echo "Hey! This tool is for the developers only. Nice try, pal.";
  } else {
  	//echo "Begin cleanup!</br>";
  	
  	if ($_POST['dosessions']) {
  		echo "Beginning Sessions</br>";
$result = mysql_query("SELECT `name` FROM Sessions");
while ($row = mysql_fetch_array($result)) {
  $losername = mysql_real_escape_string($row['name']);
  $foundmsg = false;
  $msgresult = mysql_query("SELECT `username` FROM Players WHERE `Players`.`session_name` = '$losername'");
  while ($msgrow = mysql_fetch_array($msgresult)) $foundmsg = true;
  if (!$foundmsg) {
  	mysql_query("DELETE FROM `Sessions` WHERE `name` = '$losername'"); //Create entry in message table.
  	echo $losername . " session was empty and thus deleted</br>";
  }
}
mysql_query("OPTIMIZE TABLE `Sessions`");
echo "Sessions done</br>";
}
if ($_POST['domessages']) {
	echo "Beginning Messages</br>";
$result = mysql_query("SELECT `username` FROM Messages");
while ($row = mysql_fetch_array($result)) {
  $losername = mysql_real_escape_string($row['username']);
  $foundmsg = false;
  $msgresult = mysql_query("SELECT `username` FROM Players WHERE `Players`.`username` = '$losername'");
  while ($msgrow = mysql_fetch_array($msgresult)) $foundmsg = true;
  if (!$foundmsg) {
  	mysql_query("DELETE FROM `Messages` WHERE `username` = '$losername'"); //Create entry in message table.
  	echo $losername . " message row did not have matching player row</br>";
  }
}
mysql_query("OPTIMIZE TABLE `Messages`");
echo "Messages done</br>";
}
if ($_POST['doladders']) {
	echo "Beginning Echeladders</br>";
$result = mysql_query("SELECT `username` FROM Echeladders");
while ($row = mysql_fetch_array($result)) {
  $losername = mysql_real_escape_string($row['username']);
  $foundmsg = false;
  $msgresult = mysql_query("SELECT `username` FROM Players WHERE `Players`.`username` = '$losername'");
  while ($msgrow = mysql_fetch_array($msgresult)) $foundmsg = true;
  if (!$foundmsg) {
  	mysql_query("DELETE FROM `Echeladders` WHERE `username` = '$losername'"); //Create entry in message table.
  	echo $losername . " echeladder row did not have matching player row</br>";
  }
}
mysql_query("OPTIMIZE TABLE `Echeladders`");
echo "Echeladders done</br>";
}
if ($_POST['dopatterns']) {
	echo "Beginning Patterns</br>";
$result = mysql_query("SELECT `username` FROM Ability_Patterns");
while ($row = mysql_fetch_array($result)) {
  $losername = mysql_real_escape_string($row['username']);
  $foundmsg = false;
  $msgresult = mysql_query("SELECT `username` FROM Players WHERE `Players`.`username` = '$losername'");
  while ($msgrow = mysql_fetch_array($msgresult)) $foundmsg = true;
  if (!$foundmsg) {
  	mysql_query("DELETE FROM `Ability_Patterns` WHERE `username` = '$losername'"); //Create entry in message table.
  	echo $losername . " ability row did not have matching player row</br>";
  }
}
mysql_query("OPTIMIZE TABLE `Ability_Patterns`");
echo "Ability patterns done</br>";
}
if ($_POST['dodungeons']) {
	echo "Beginning Dungeons</br>";
$result = mysql_query("SELECT `username` FROM Dungeons");
while ($row = mysql_fetch_array($result)) {
  $losername = mysql_real_escape_string($row['username']);
  $foundmsg = false;
  $msgresult = mysql_query("SELECT `username` FROM Players WHERE `Players`.`username` = '$losername'");
  while ($msgrow = mysql_fetch_array($msgresult)) $foundmsg = true;
  if (!$foundmsg) {
  	mysql_query("DELETE FROM `Dungeons` WHERE `username` = '$losername'"); //Create entry in message table.
  	echo $losername . " echeladder row did not have matching player row</br>";
  }
}
mysql_query("OPTIMIZE TABLE `Dungeons`");
echo "Dungeons done</br>";
}
echo '<form action="tablecleanup.php" method="post">Select which tables to clean up:</br>
<input type="checkbox" name="dosessions" value="yes"> Sessions</br>
<input type="checkbox" name="domessages" value="yes"> Messages</br>
<input type="checkbox" name="doladders" value="yes"> Echeladders</br>
<input type="checkbox" name="dopatterns" value="yes"> Ability Patterns</br>
<input type="checkbox" name="dodungeons" value="yes"> Dungeons</br>
<input type="submit" value="Begin"></form>';
  }
}
?>