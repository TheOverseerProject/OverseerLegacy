<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
include("tumblr.php");
} else {
	//if($username == "The Overseer") sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "I LOADED THE INDEX PAGE WOOOOOO");
  echo '<a href="overview.php"><img src="/Images/title/playnew.png" width="200" /></a>';
  echo '<a href="strife.php"><img src="/Images/title/strife.png" width="200" /></a>';
  $sql = "SELECT *  FROM `Players`
WHERE `session_name` LIKE '$userrow[session_name]'
AND `enemydata` != '';";
  $sessionmates = mysql_query($sql);

  echo '<a href="grist.php"><img src="/Images/title/gristly.png" width="200" /></a>';
  echo '<a href="porkhollow.php"><img src="/Images/title/booney.png" width="200" /></a><br/>';
  
    while ($row = mysql_fetch_array($sessionmates)) {
    
		if ($row['username'] != $username) {
		  echo "$row[username] is strifing right now!</br>";
		} else {
		  echo "You are strifing right now!</br>";
		}
      }
    $sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "'");
  $sessionrow = mysql_fetch_array($sessionresult);
  if ($sessionrow['admin'] == $username && $userrow['admin'] == 0) {
    $userrow['admin'] = 1;
    mysql_query("UPDATE `Players` SET `admin` = 1 WHERE `Players`.`username` = '$username' LIMIT 1;");
    echo "You were set as the session's head admin, but you were not marked as an admin yourself. We have just attempted to fix this, but if you have gotten this message more than once, Blahdev/Babby Overseer would appreciate it if you reported it to him.</br>";
    }
  
  //echo '<a href="events.php">Event Log</a></br>'; -- Will work on event log later.
}
require_once("footer.php");
?>