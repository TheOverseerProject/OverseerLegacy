<?php
session_start();
if (empty($_SESSION['username'])) {
  echo "Log in to use consumable items.</br>";
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} else {
 $con = mysql_connect("localhost","theovers_DC","pi31415926535");
  if (!$con) {
    echo "Connection failed.\n";
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("theovers_HS", $con);
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
      mysql_query("INSERT INTO `Ability_Patterns` (`username`) VALUES ('" . mysql_real_escape_string($row['username']) . "');");
      echo $row['username'] . " added.</br>";
    }
  }
}
?>