<?php
require_once("header.php");

if (empty($_SESSION['username'])) {
	echo "Log in to reset your Echeladder.<br />";
} elseif ($userrow['Echeladder'] < 612) {
	echo "You can only do this if your rung is at 612 (or higher due to a glitch or something).<br />";
} else {
	if (!empty($_POST['newrung'])) {
		if ($_POST['newrung'] > 5 && $_POST['newrung'] < 612) {
			$oldhealth = $userrow['Health_Vial'];
			$olddhealth = $userrow['Dream_Health_Vial'];
			$oldmaxhealth = $userrow['Gel_Viscosity'];
			$oldaspect = $userrow['Aspect_Vial'];
			$oldhpercent = $oldhealth / $oldmaxhealth;
			$olddpercent = $olddhealth / $oldmaxhealth;
			$oldapercent = $oldaspect / $oldmaxhealth;
			$newrung = $_POST['newrung'];
			$fixhealth = ($newrung * 15) - 30;
			$newhealth = ceil($fixhealth * $oldhpercent);
			if ($newhealth < 1) $newhealth = 1;
			$newdhealth = ceil($fixhealth * $olddpercent);
			if ($newdhealth < 1) $newdhealth = 1;
			$newaspect = ceil($fixhealth * $oldapercent);
			if ($newaspect < 0) $newaspect = 0;
			mysql_query("UPDATE `Players` SET `Echeladder` = $newrung, `Gel_Viscosity` = $fixhealth, `Health_Vial` = $newhealth, `Dream_Health_Vial` = $newdhealth, `Aspect_Vial` = $newaspect WHERE `Players`.`username` = '" . $username . "' LIMIT 1;");
			echo "Your rung has been updated. Have a nice day!";
		} else echo "You cannot make that your Echeladder rung.<br />";
	} else {
		echo "Echeladder Rung Resetter. Enter a rung between 6-611 to revert your Echeladder back to it.<br />";
		echo '<form action="rungreset.php" method="post">New rung: <input type="text" name="newrung" /><br /><input type="submit" value="Reset" /></form>';
	}
}

require_once("footer.php");
?>