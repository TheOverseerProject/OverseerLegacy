<?php
require_once("header.php");

if ($userrow['session_name'] != "Developers") {
	echo "denied.";
} else {
	if (!empty($_POST['admn'])) {
		$sresult = mysql_query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '" . $_POST['sesn'] . "' LIMIT 1;");
		$srow = mysql_fetch_array($sresult);
		if ($srow['name'] == $_POST['sesn']) {
			$presult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '" . $_POST['admn'] . "' LIMIT 1;");
			$prow = mysql_fetch_array($presult);
			if ($prow['username'] == $_POST['admn']) {
				if ($prow['session_name'] == $srow['name']) {
					mysql_query("UPDATE `Players` SET `admin` = 1 WHERE `Players`.`username` = '" . $prow['username'] . "' LIMIT 1;");
					if (!empty($_POST['head'])) {
						mysql_query("UPDATE `Sessions` SET `admin` = '" . $prow['username'] . "' WHERE `Sessions`.`name` = '" . $srow['name'] . "' LIMIT 1;");
					}
					echo "Done! " . $prow['username'] . " is now admin of session " . $srow['name'] . "<br />";
				} else echo "ERROR: That player is not in that session<br />";
			} else echo "ERROR: Player " . $_POST['admn'] . " not found<br />";
		} else echo "ERROR: Session " . $_POST['sesn'] . " not found<br />";
	}
	
	echo "Admin re-assigner v. Lazy Blahsadfeguie.<br />";
	echo '<form action="adminassign.php" method="post">Session: <input type="text" name="sesn" /><br />';
	echo 'New admin: <input type="text" name="admn" /><br />';
	echo '<input type="checkbox" name="head" value="yes" /> Replace existing head admin<br />';
	echo '<input type="submit" value="Kaboom!" /></form>';
}

?>