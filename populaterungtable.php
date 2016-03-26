<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to use consumable items.</br>";
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} else {
 
  $username=$_SESSION['username'];
  $result = mysql_query("SELECT * FROM Players");
  while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
    if ($row['username'] == $username) {
      $userrow = $row;
    }
  }
  if ($userrow['session_name'] != "Developers") {
    echo "Hey! This tool is for the developers only. Nice try, pal.";
  } else {
    $players = mysql_query("SELECT * FROM Players");
    while ($row = mysql_fetch_array($players)) {
      mysql_query("INSERT INTO `Echeladders` (`username`) VALUES ('" . mysql_real_escape_string($row['username']) . "');");
      echo $row['username'] . " added.</br>";
    }
  }
}
?>