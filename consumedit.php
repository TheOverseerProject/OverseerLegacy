<?php
require_once("header.php");
require_once("includes/grist_icon_parser.php");

if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
	echo "What are you doing here?";
} else {
	if (!empty($_GET['editcode'])) {
		$editcode = $_GET['editcode'];
		$populate = true;
	} else {
		$editcode = "00000000";
		$populate = false;
	}
	
	if (!empty($_POST['publishlog'])) {
		$sysresult = mysql_query("SELECT `addlog` FROM `System` WHERE 1");
		$sysrow = mysql_fetch_array($sysresult);
		if ($sysrow['addlog'] != "") {
			if (!empty($_POST['publishtitle']))
			$titletext = mysql_real_escape_string($_POST['publishtitle']);
			else {
				$titletext = "Auto-posted addlog number ";
				$titletext .= strval(rand(1000000,9999999));
			}
			$datetext = mysql_real_escape_string(date("Y-m-d H:i:s"));
			$nametext = mysql_real_escape_string($username);
			if (!empty($_POST['publishbody'])) 
			$leadintext = $_POST['publishbody'];
			else $leadintext = "This is an automatically generated addlog of items that were created using the on-site Item Editor. The person posting this is too lazy to actually include a message, so enjoy these items:";
			$bodytext = mysql_real_escape_string($leadintext . "</br>" . $sysrow['addlog']);
			mysql_query("INSERT INTO `News` (`date`, `title`, `postedby`, `news`) VALUES ('$datetext', '$titletext', '$nametext', '$bodytext')");
			echo $bodytext; //in case it fails to post
			mysql_query("UPDATE `System` SET `addlog` = '' WHERE 1");
			echo "</br>News has been posted, and the addlog has been cleared.</br>";
		} else "ERROR: Addlog is empty. Someone might have beaten you to it!</br>";
	}
	
	if (!empty($_POST['captchalogue_code'])) {
		$blocked = false;
		if ((!empty($_POST['offense_exact_temp']) || !empty($_POST['offense_scale_temp']) || !empty($_POST['defense_exact_temp']) || !empty($_POST['defense_scale_temp'])) && empty($_POST['temp_timer'])) {
			echo "A positive temp_timer value is required when adding a temporary boost.</br>";
			$blocked = true;
		}
		if ($_POST['outsideuse'] == 1 && $_POST['donotconsume'] == 1) {
			echo "Infinite-use consumables that can be used outside of strife are overpowered! Please set outsideuse to 0 if you set donotconsume to 1.</br>";
			$blocked = true;
		}
		if ($_POST['luck'] > 100) {
			echo "A consumable can't grant more than 100 luck.</br>";
			$blocked = true;
		}
		if (empty($_POST['number_targets'])) {
			echo "Even if the consumable doesn't effect enemies, number_targets should be greater than zero. Better safe than sorry on this one.</br>";
			$blocked = true;
		}
		if (strlen($_POST['captchalogue_code']) != 8 || strpos($_POST['captchalogue_code'], " ") || strpos($_POST['captchalogue_code'], "-")) {
			echo "That is an invalid captchalogue code.</br>";
			$blocked = true;
		} else {
			$editcode = $_POST['captchalogue_code'];
		$editresult = mysql_query("SELECT `captchalogue_code`,`name`,`consumable` FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '$editcode' LIMIT 1;");
		$irow = mysql_fetch_array($editresult);
		if ($irow['captchalogue_code'] != $editcode) {
			echo "No item with that code was found!<br />";
			$blocked = true;
		}
		if ($irow['consumable'] == 0) {
			$makeconsumable = true;
		}
		}
		if (!$blocked) {
			$editname = str_replace("'", "", $irow['name']);
			$editname = str_replace("\\", "", $editname); //consumables don't have apostrophes or backslashes
			$fieldresult = mysql_query("SELECT * FROM `Consumables` LIMIT 1;");
		while ($field = mysql_fetch_field($fieldresult)) {
			$fname = $field->name;
				if ($fname == 'name') {
					$founditem = false;
					$editcode = $_POST['captchalogue_code'];
					$editresult = mysql_query("SELECT * FROM `Consumables` WHERE `Consumables`.`name` = '$editname' LIMIT 1;");
					while($row = mysql_fetch_array($editresult)) {
						$founditem = true;
						$erow = $row;
					}
					if (!$founditem) {
						$updatequery = "INSERT INTO `Consumables` VALUES ('$editname'";
					} else {
						$updatequery = "UPDATE `Consumables` SET ";
					}
				} else {
					if (!$founditem) {
						$updatequery .= ", '" . mysql_real_escape_string($_POST[$fname]) . "'";
					} else {
						$updatequery .= "`" . $fname . "` = '" . mysql_real_escape_string($_POST[$fname]) . "', ";
					}
				}
			}
			if (!$founditem) {
				$updatequery .= ");";
			} else {
				$updatequery = substr($updatequery, 0, -2);
				$updatequery .= " WHERE `Consumables`.`name` = '$editname';";
			}
			echo $updatequery . "</br>";
			mysql_query($updatequery);
			//now test to see if it worked
			if (!$founditem) {
				$victory = false;
				$testresult = mysql_query("SELECT `name` FROM `Consumables` WHERE `Consumables`.`name` = '$editname'");
				$testrow = mysql_fetch_array($testresult);
				if ($testrow['name'] == $editname) {
					$victory = true;
					$sysresult = mysql_query("SELECT `addlog` FROM `System` WHERE 1");
					$sysrow = mysql_fetch_array($sysresult);
					$sysrow['addlog'] .= "</br>" . $username . " - Added consumable effect for " . $editname;
					if (!empty($_POST['devcomments'])) $sysrow['addlog'] .= " (" . $_POST['devcomments'] . ")";
					mysql_query("UPDATE `System` SET `addlog` = '" . mysql_real_escape_string($sysrow['addlog']) . "' WHERE 1");
					echo "Addlog updated.</br>";
				} else {
					echo "Oops, something is wrong! The query didn't go through, and the consumable row wasn't created. If all else fails, send that query to Blah!</br>";
				}
			} else {
				$victory = true;
				$sysresult = mysql_query("SELECT `addlog` FROM `System` WHERE 1");
					$sysrow = mysql_fetch_array($sysresult);
					$sysrow['addlog'] .= "</br>" . $username . " - Edited consumable effect for " . $editname;
					if (!empty($_POST['devcomments'])) $sysrow['addlog'] .= " (" . $_POST['devcomments'] . ")";
					mysql_query("UPDATE `System` SET `addlog` = '" . mysql_real_escape_string($sysrow['addlog']) . "' WHERE 1");
					echo "Addlog updated.</br>";
			}
			if ($victory && $makeconsumable) {
				mysql_query("UPDATE `Captchalogue` SET `consumable` = 1 WHERE `Captchalogue`.`captchalogue_code` = '$editcode' LIMIT 1;");
				echo "Note: This item wasn't originally marked as consumable. It has been updated automatically.<br />";
			}
		}
	}
	
	$founditem = false;
	if ($populate) {
		$editresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '$editcode' LIMIT 1;");
		while($row = mysql_fetch_array($editresult)) {
			echo $row['name'] . " recognized<br />";
			$editname = str_replace("'", "", $row['name']);
			$editname = str_replace("\\", "", $editname); //consumables don't have apostrophes or backslashes
			$editresult = mysql_query("SELECT * FROM `Consumables` WHERE `Consumables`.`name` = '$editname' LIMIT 1;");
			$row = mysql_fetch_array($editresult);
			if ($row['name'] == $editname) {
				$founditem = true;
				echo "Consumable row loaded<br />";
				$erow = $row;
			} else {
				echo "Consumable row does not yet exist. One will be created upon submitting the following form.<br />";
				echo "If this is already a functional consumable, then its effect is probably hardcoded and having a consumable row would be superfluous. If you want to have a hardcoded consumable edited, please talk to Blah or Overseer!<br />";
			}
		}
	}
	echo '<form action="consumedit.php" method="post" id="itemeditor"><table cellpadding="0" cellspacing="0"><tbody><tr><td align="right">Consumable Editor:</td><td> Merry Christmas Edition</td></tr>';
	if ($populate) echo '<input type="hidden" name="populate" value="yes">';
	else echo '<input type="hidden" name="populate" value="no">';
	echo '<tr><td align="right">Code of item:</td><td> <input type="text" name="captchalogue_code" value="' . $editcode . '" /></td></tr>';
	$fieldresult = mysql_query("SELECT * FROM `Consumables` LIMIT 1;");
	while ($field = mysql_fetch_field($fieldresult)) {
		echo '<tr><td align="right">';
		$fname = $field->name;
		if ($fname == "message_battle" || $fname == "message_outside" || $fname == "message_aid") {
			echo $fname . ':</td><td><textarea name="' . $fname . '" rows="6" cols="40" form="itemeditor">';
			if ($founditem) echo $erow[$fname];
			elseif (!empty($_POST[$fname])) echo $_POST[$fname];
			echo '</textarea></br>';
		} elseif ($fname != "name") {
			echo $fname . ':</td><td> <input type="text" name="' . $fname . '"';
			if ($founditem) echo ' value="' . $erow[$fname] . '"';
			elseif (!empty($_POST[$fname])) echo ' value="' . $_POST[$fname] . '"';
			elseif ($fname == "number_targets") echo ' value="5"';
			elseif ($fname == "allypercentage") echo ' value="100"';
			echo '></td></tr>';
		}
	}
	echo '</tbody></table>';
	echo 'Dev comments about the item: Same as the item editor; if you want to say anything about your edit/addition, speak now or forever hold your peace.</br><textarea name="devcomments" rows="6" cols="40" form="itemeditor"></textarea></br>';
	echo '<input type="submit" value="Edit/Create"></form></br>';
	$sysresult = mysql_query("SELECT `addlog` FROM `System` WHERE 1");
	$sysrow = mysql_fetch_array($sysresult);
	if (empty($sysrow['addlog'])) $sysrow['addlog'] = " Empty!";
	echo "Current addlog:" . $sysrow['addlog'];
	echo "</br>When you're done with your batch of items, please use the following form to publish the current addlog into a news post.</br>(Note: All fields are optional and will be filled with placeholders if left blank.)</br>";
	echo '<form action="itemedit.php" method="post" id="publishaddlog"><input type="hidden" name="publishlog" value="yes">Title: <input type="text" name="publishtitle"></br>Body: <textarea name="publishbody" rows="6" cols="40" form="publishaddlog"></textarea></br><input type="submit" value="Publish addlog"></form>';
}

require_once("footer.php");
?>