<?php
require_once("header.php");
if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
	"You don't get to have a debug.</br>";
} else {
	if (empty($_GET['user'])) $_GET['user'] = $username;
	$yourresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '" . $_GET['user'] . "' ;");
	$row = mysql_fetch_array($yourresult);
	$youresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '" . $_GET['user'] . "' ;");
	$accexists = false;
	while ($col = mysql_fetch_field($youresult)) {
		$accexists = true;
		$feld = $col->name;
		echo "$feld = " . $row[$feld] . " </br>";
	}
	if (!$accexists) echo "Your player row doesn't exist! That can't be good.";
	$yourresult = mysql_query("SELECT * FROM `Consort_Dialogue` LIMIT 1 ;");
	$row = mysql_fetch_array($yourresult);
	$youresult = mysql_query("SELECT * FROM `Consort_Dialogue` LIMIT 1 ;");
	//$accexists = false;
	while ($col = mysql_fetch_field($youresult)) {
		$accexists = true;
		$feld = $col->name;
		echo "$feld = " . $row[$feld] . " </br>";
	}
}
?>