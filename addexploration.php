<?php
require_once("header.php");

if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
	echo "What are you doing here?";
} else {
	if (!empty($_GET['event'])) {
		$editevent = $_GET['event'];
		$populate = true;
	} else {
		$editevent = "";
		$populate = false;
	}
	if (!empty($_GET['area'])) {
		$areastr = "Explore_" . $_GET['area'];
	} else {
		$areastr = "Explore_Derse";
		$populate = false;
	}
	
	if (!empty($_POST['name'])) {
		$blocked = false;
		if ($_POST['boonreward'] != 0 && empty($_POST['transform'])) {
			echo "When giving a boon reward, 'transform' must be set or else refreshing = infinite boondollars.</br>";
			$blocked = true;
		}
		if ($_POST['cansleep'] == 1 && empty($_POST['sleepevent'])) {
			echo "Please provide a sleepevent if a player can sleep at this event. 'wakeup' is default.</br>";
			$blocked = true;
		}
		if (!$blocked) {
			$areastr = "Explore_" . $_POST['exarea'];
			$fieldresult = mysql_query("SELECT * FROM `$areastr` LIMIT 1;");
		while ($field = mysql_fetch_field($fieldresult)) {
			$fname = $field->name;
				if ($fname == 'name') {
					$founditem = false;
					$editevent = $_POST['name'];
					$editresult = mysql_query("SELECT * FROM `$areastr` WHERE `$areastr`.`name` = '$editevent' LIMIT 1;");
					while($row = mysql_fetch_array($editresult)) {
						$founditem = true;
						$erow = $row;
					}
					if (!$founditem) {
						$updatequery = "INSERT INTO `$areastr` VALUES ('$editevent'";
					} else {
						$updatequery = "UPDATE `$areastr` SET ";
					}
				} else {
					if (!$founditem) {
						$updatequery .= ", '" . mysql_real_escape_string($_POST[$fname]) . "'";
					} else {
						$updatequery .= "`" . $fname . "` = '" . mysql_real_escape_string($_POST[$fname]) . "', ";
					}
				}
			}
		}
		if (!$blocked) {
			if (!$founditem) {
				$updatequery .= ");";
			} else {
				$updatequery = substr($updatequery, 0, -2);
				$updatequery .= " WHERE `$areastr`.`name` = '$editevent';";
			}
			echo $updatequery . "</br>";
			mysql_query($updatequery);
			//now test to see if it worked
			if (!$founditem) {
				$victory = false;
				$testresult = mysql_query("SELECT `name` FROM `$areastr` WHERE `$areastr`.`name` = '$editevent'");
				$testrow = mysql_fetch_array($testresult);
				if ($testrow['name'] == $editevent) {
					$victory = true;
					echo "Event added.<br />";
				} else {
					echo "Oops, something is wrong! The query didn't go through, and the event wasn't created. If all else fails, send that query to Blah!</br>";
				}
			} else {
				$victory = true;
				echo "Event updated.<br />";
			}
		}
	}
	
	if ($populate) {
		$editresult = mysql_query("SELECT * FROM `$areastr` WHERE `$areastr`.`name` = '$editevent' LIMIT 1;");
		while($row = mysql_fetch_array($editresult)) {
			$founditem = true;
			echo $row['name'] . " loaded</br>";
			$erow = $row;
		}
	}
	echo '<form action="addexploration.php" method="post" id="itemeditor"><table cellpadding="0" cellspacing="0"><tbody><tr><td align="right">Exploration Editor:</td><td> Let\'s Actually Do This Edition</td></tr>';
	if ($populate == false) {
		echo '<tr><td align="right">Area this event appears in:</td><td><select name="exarea"><option value="Derse">Derse</option><option value="Prospit">Prospit</option><option value="Battlefield">Battlefield</option></select></td></tr>';
	} else {
		echo '<input type="hidden" name="exarea" value="' . $_GET['area'] . '" />';
	}
	$fieldresult = mysql_query("SELECT * FROM `Explore_Prospit` LIMIT 1;");
	while ($field = mysql_fetch_field($fieldresult)) {
		echo '<tr><td align="right">';
		$fname = $field->name;
		if ($fname == "description") {
			echo $fname . ':</td><td><textarea name="description" rows="6" cols="40" form="itemeditor">';
			if ($founditem) echo $erow[$fname];
			elseif (!empty($_POST[$fname])) echo $_POST[$fname];
			echo '</textarea></td></tr>';
		} else {
			echo $fname . ':</td><td> <input type="text" name="' . $fname . '"';
			if ($founditem) echo ' value="' . $erow[$fname] . '"';
			elseif (!empty($_POST[$fname])) echo $_POST[$fname];
			echo '></td></tr>';
		}
	}
	echo '</table><input type="submit" value="Edit/Create"></form></br>';
	
}

require_once("footer.php");
?>