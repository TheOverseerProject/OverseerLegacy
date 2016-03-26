<?php
require_once("header.php");
require_once 'time.php';

if (empty($_SESSION['username'])) {
  echo "Log in to view stuff that happened to you.</br>";
} else {


  $result = mysql_query("SELECT * FROM Logs WHERE `Logs`.`username` = '$username'");
  while ($row = mysql_fetch_array($result)) {
    if ($row['username'] == $username) {
      echo "It is currently " . produceIST(initTime($con)) . "</br>";
      echo "Events for " . $username . ":</br>";
      $result2 = mysql_query("SELECT * FROM Logs");
      $col = mysql_fetch_field($result2); //Skip the username.
      while ($col = mysql_fetch_field($result2)) {
	$log = $col->name;
	echo $row[$log];
	echo "</br>";
      }
    }
  }
}
require_once("footer.php");
echo '</br><a href="/">Home</a> <a href="controlpanel.php">Control Panel</a>';
?>