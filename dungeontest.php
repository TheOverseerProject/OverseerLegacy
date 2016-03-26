<?php
require_once("includes/SQLconnect.php");
echo "let's see if there are deungeons</br>";
$dungeonresult = mysql_query("SELECT * FROM `Dungeons`");
while ($drow = mysql_fetch_array($dungeonresult)) {
	echo $drow['username'] . "</br>";
}
?>