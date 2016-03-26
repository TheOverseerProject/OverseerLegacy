<?php
require 'additem.php';
require 'monstermaker.php';
require_once("header.php");
$max_enemies = 5; //Note that this is ALSO in monstermaker.php. That isn't ideal, but eh. (Also in striferesolve.php. Bluh. AND strifeselect.php. I should make a constants file at some stage)
if (empty($_SESSION['username'])) {
  echo "Log in to stop assisting in strife.</br>";
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} elseif ($userrow['cantabscond'] == 1) {
	$aidresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$userrow[aiding]'");
	$aidrow = mysql_fetch_array($aidresult);
	if (time() - $aidrow['bossbegintime'] > 86400) {
		mysql_query("UPDATE `Players` SET `aiding` = '', `cantabscond` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
  	echo '<a href="strife.php">You are no longer aiding your ally.</a></br>';
  	if ($userrow['dungeonstrife'] == 2) { //User strifing in a dungeon
			mysql_query("UPDATE `Players` SET `dungeonstrife` = 1 WHERE `Players`.`username` = '$username' LIMIT 1;");
			echo "You flee back the way you came.</br>";
			echo "<a href='dungeons.php'>==&gt;</a></br>";
		}
		if ($userrow['dungeonstrife'] == 4) { //User fighting dungeon guardian
	    mysql_query("UPDATE `Players` SET `dungeonstrife` = 3 WHERE `Players`.`username` = '$username' LIMIT 1;");
	    echo "You flee from the guardian. Perhaps you should prepare a bit more before trying to enter the dungeon...</br>";
	    echo "<a href='dungeons.php#display'>==&gt;</a></br>";
		}
	} else {
		echo '<a href="strife.php">CAN\'T ABSCOND, BRO!</a>';
	}
} else {
  require_once("includes/SQLconnect.php");
  mysql_query("UPDATE `Players` SET `aiding` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
  echo '<a href="strife.php">You are no longer aiding your ally.</a></br>';
  if ($userrow['dungeonstrife'] == 2) { //User strifing in a dungeon
		mysql_query("UPDATE `Players` SET `dungeonstrife` = 1 WHERE `Players`.`username` = '$username' LIMIT 1;");
		echo "You flee back the way you came.</br>";
		echo "<a href='dungeons.php'>==&gt;</a></br>";
	}
	if ($userrow['dungeonstrife'] == 4) { //User fighting dungeon guardian
	    mysql_query("UPDATE `Players` SET `dungeonstrife` = 3 WHERE `Players`.`username` = '$username' LIMIT 1;");
	    echo "You flee from the guardian. Perhaps you should prepare a bit more before trying to enter the dungeon...</br>";
	    echo "<a href='dungeons.php#display'>==&gt;</a></br>";
	}
}
require_once("footer.php");
?>