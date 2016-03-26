<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to engage in strife.</br>";
} else {
  
  $sessionresult = mysql_query("SELECT name, combatlog FROM Sessions WHERE `Sessions`.`name` = '$userrow[session_name]'");
  $sessionrow = mysql_fetch_array($sessionresult);
  echo "The combat log is in chronological order from top to bottom.</br>";
  echo "Log of boss combat for $sessionrow[name]:</br>";
  echo $sessionrow['combatlog'];
}
require_once("footer.php");
?>