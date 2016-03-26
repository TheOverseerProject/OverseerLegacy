<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Trying to waste your time whilst not logged in is... a waste of time?</br>";
} else {
  require_once("includes/SQLconnect.php");
  if ($userrow['encounters'] > 0) {
    mysql_query("UPDATE `Players` SET `encounters` = $userrow[encounters]-1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
    mysql_query("UPDATE `Players` SET `encountersspent` = $userrow[encountersspent]+1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
    echo "You sit around wasting time.";
  } else {
    echo "You haven't got any time to sit around wasting!";
  }
  echo '</br><a href="overview.php">==&gt;</a>';
  //header('location:/overview.php');
  require_once("footer.php");
}