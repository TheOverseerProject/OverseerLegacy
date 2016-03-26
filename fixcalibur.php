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
  $msgresult = mysql_query("SELECT * FROM Messages WHERE `Messages`.`username` = '$losername'");
  while ($msgrow = mysql_fetch_array($msgresult)) {
    $counter = 1;
    $unreads = 0;
    while ($counter <= 50) {
    	$msgstring = $msgrow['msg' . strval($counter)];
    	$boom = explode("|",$msgstring);
    	if (empty($boom[3]) && !empty($msgstring)) $unreads++;
    	$counter++;
    }
    mysql_query("UPDATE `Players` SET `newmessage` = $unreads WHERE `Players`.`username` = '$losername' LIMIT 1;");
    echo $losername . " has " . strval($unreads) . " unreads</br>";
  }
}
}
}
?>