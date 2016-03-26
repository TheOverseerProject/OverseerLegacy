<?php
require_once("header.php");

if ($userrow['session_name'] != "Developers") {
	echo "denied.";
} else {
	if (!empty($_POST['query'])) {
		$query = $_POST['query'];
		if ($_POST['teststring'] == "test\\'s") { //auto-escape detected, fix before executing query
		$query = str_replace("\\'", "'", $query);
		$query = str_replace("\\\"", "\"", $query);
		$query = str_replace("\\\\", "\\", $query);
		}
		echo $query . "<br />";
		if (strpos($_POST['query'], "SELECT") !== false) {
			$result = mysql_query($query);
			if (!$result) echo "We got an error... " . mysql_error();
			else {
				echo strval(mysql_num_rows($result)) . " row(s) returned.<br />";
				while ($row = mysql_fetch_array($result)) {
					print_r($row);
					echo "<br />";
				}
			}
		} elseif (strpos($_POST['query'], "INSERT") !== false) {
			$result = mysql_query($query);
			if (!$result) echo "We got an error... " . mysql_error();
			else {
				echo strval(mysql_affected_rows()) . " row(s) inserted.<br />";
			}
		} elseif (strpos($_POST['query'], "UPDATE") !== false) {
			$result = mysql_query($query);
			if (!$result) echo "We got an error... " . mysql_error();
			else {
				echo strval(mysql_affected_rows()) . " row(s) affected.<br />";
			}
		} else echo "I don't think it's safe to do that kind of thing from here.<br />";
		echo "<br />";
	}
	
	echo "New from Blahsadfeguie Inc., it's I Can't Believe it's Not PHPmyadmin! 99% of squirrels can't tell the difference!<br /><br />";
	echo '<form action="blahsquirrel.php" method="post" id="blahsql">Query to execute:<br /><textarea name="query" rows="6" cols="40" form="blahsql"></textarea><br />';
	echo '<input type="hidden" name="teststring" value="test\'s" />';
	echo '<input type="submit" value="Execute it!" /></form>';
}

?>