<?php
require_once("header.php");

if ($userrow['session_name'] != "Developers") {
	echo "denied.";
} else {
	echo "2.2 update dungeon fixing script start!<br />";
	$dfixresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`indungeon` = 1 AND `Players`.`currentdungeon` = ''");
	while ($row = mysql_fetch_array($dfixresult)) {
		echo "Fixing player " . $row['username'] . "...";
		$dresult = mysql_query("SELECT * FROM `Dungeons` WHERE `Dungeons`.`username` = '" . $row['username'] . "'");
		$drow = mysql_fetch_array($dresult);
		mysql_query("UPDATE `Players` SET `currentdungeon` = '" . $row['username'] . "', `dungeonrow` = " . strval($drow['dungeonrow']) . ", `dungeoncol` = " . strval($drow['dungeoncol']) . ", `olddungeonrow` = " . strval($drow['olddungeonrow']) . ", `olddungeoncol` = " . strval($drow['olddungeoncol']) . " WHERE `Players`.`username` = '" . $row['username'] . "'");
		//echo "UPDATE `Players` SET `currentdungeon` = '" . $row['username'] . "', `dungeonrow` = " . strval($drow['dungeonrow']) . ", `dungeoncol` = " . strval($drow['dungeoncol']) . ", `olddungeonrow` = " . strval($drow['olddungeonrow']) . ", `olddungeoncol` = " . strval($drow['olddungeoncol']) . " WHERE `Players`.`username` = '" . $row['username'] . "'";
		echo "Done!<br />";
	}
	echo "That's everyone!<br />";
}

require_once("footer.php");
?>