<?php
require_once("header.php");
function convertHybrid($workrow, $isbodygear) { //when wearable defense is calculated, it will go here if it's a hybrid (both a weapon and wearable) and cut the power down
	$bonusrow['abstain'] = $workrow['abstain'];
	$bonusrow['abjure'] = $workrow['abjure'];
	$bonusrow['accuse'] = $workrow['accuse'];
	$bonusrow['abuse'] = $workrow['abuse'];
	$bonusrow['aggrieve'] = $workrow['aggrieve'];
	$bonusrow['aggress'] = $workrow['aggress'];
	$bonusrow['assail'] = $workrow['assail'];
	$bonusrow['assault'] = $workrow['assault'];
	if ($isbodygear) $divisor = 10;
	else $divisor = 30;
	$workrow['power'] = ceil($workrow['power'] / $divisor);
	$bestbonus = max($bonusrow);
	if ($bestbonus == 0) $bestname = "none";
	elseif ($bonusrow['abstain'] == $bestbonus) $bestname = "abstain";
	elseif ($bonusrow['abjure'] == $bestbonus) $bestname = "abjure";
	elseif ($bonusrow['accuse'] == $bestbonus) $bestname = "accuse";
	elseif ($bonusrow['abuse'] == $bestbonus) $bestname = "abuse";
	elseif ($bonusrow['aggrieve'] == $bestbonus) $bestname = "aggrieve";
	elseif ($bonusrow['aggress'] == $bestbonus) $bestname = "aggress";
	elseif ($bonusrow['assail'] == $bestbonus) $bestname = "assail";
	elseif ($bonusrow['assault'] == $bestbonus) $bestname = "assault";
	if ($bestname == "abstain" || $workrow['abstain'] < 0) $workrow['abstain'] = ceil($workrow['abstain'] / $divisor);
	else $workrow['abstain'] = 0;
	if ($bestname == "abjure" || $workrow['abjure'] < 0) $workrow['abjure'] = ceil($workrow['abjure'] / $divisor);
	else $workrow['abjure'] = 0;
	if ($bestname == "accuse" || $workrow['accuse'] < 0) $workrow['accuse'] = ceil($workrow['accuse'] / $divisor);
	else $workrow['accuse'] = 0;
	if ($bestname == "abuse" || $workrow['abuse'] < 0) $workrow['abuse'] = ceil($workrow['abuse'] / $divisor);
	else $workrow['abuse'] = 0;
	if ($bestname == "aggrieve" || $workrow['aggrieve'] < 0) $workrow['aggrieve'] = ceil($workrow['aggrieve'] / $divisor);
	else $workrow['aggrieve'] = 0;
	if ($bestname == "aggress" || $workrow['aggress'] < 0) $workrow['aggress'] = ceil($workrow['aggress'] / $divisor);
	else $workrow['aggress'] = 0;
	if ($bestname == "assail" || $workrow['assail'] < 0) $workrow['assail'] = ceil($workrow['assail'] / $divisor);
	else $workrow['assail'] = 0;
	if ($bestname == "assault" || $workrow['assault'] < 0) $workrow['assault'] = ceil($workrow['assault'] / $divisor);
	else $workrow['assault'] = 0;
	return $workrow;
}
if (empty($_SESSION['username'])) {
  echo "Log in to access this developer's tool.</br>";
} else {
	require_once("includes/SQLconnect.php");
	if ($userrow['session_name'] != "Doodlefluffer") {
		echo "This is a balancing tool. It is for use by developers. It's super boring, so you're not missing out on much.</br>";
	} else {
		$database = mysql_query("SELECT * FROM `Players`");
		$i = 1;
		$rungarrays = array();
		while ($i <= 612) {
			$rungarrays[$i] = array();
		}
		while ($userrow = mysql_fetch_array($database)) {
			$mainpower = 0;
			$offpower = 0;
			$headdef = 0;
			$facedef = 0;
			$bodydef = 0;
			$accdef = 0;
			$totaldef = 0;
			$powerlevel = 0;
			$spritepower = $userrow['sprite_strength'];
			if ($userrow['equipped'] != "" && $userrow['dreamingstatus'] == "Awake") {
				$equipname = str_replace("'", "\\\\''", $userrow[$userrow['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $equipname . "'");
				while ($row = mysql_fetch_array($itemresult)) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['equipped']]) {
						$mainpower = $row['power'];
						$mainrow = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			} else {
				$mainpower = 0;
			}
			if ($userrow['offhand'] != "" && $userrow['offhand'] != "2HAND" && $userrow['dreamingstatus'] == "Awake") {
				$offname = str_replace("'", "\\\\''", $userrow[$userrow['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $offname . "'");
				while ($row = mysql_fetch_array($itemresult)) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['offhand']]) {
						$offpower = ($row['power'] / 2);
						$offrow = $row;
					}
				}
			} else {
				$offpower = 0;
			}
			if ($userrow['headgear'] != "" && $userrow['dreamingstatus'] == "Awake") {
				$headname = str_replace("'", "\\\\''", $userrow[$userrow['headgear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $headname . "'");
				while ($row = mysql_fetch_array($itemresult)) {
				$itemname = $row['name'];
				$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['headgear']]) {
						if ($row['hybrid'] == 1) $row = convertHybrid($row, false);
						$headdef = $row['power'];
						$headrow = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			} else {
				$headdef = 0;
			}
			if ($userrow['facegear'] != "" && $userrow['facegear'] != "2HAND" && $userrow['dreamingstatus'] == "Awake") {
				$facename = str_replace("'", "\\\\''", $userrow[$userrow['facegear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $facename . "'");
				while ($row = mysql_fetch_array($itemresult)) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['facegear']]) {
						if ($row['hybrid'] == 1) $row = convertHybrid($row, false);
						$facedef = $row['power'];
						$facerow = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			} else {
				$facedef = 0;
			}
			if ($userrow['bodygear'] != "" && $userrow['dreamingstatus'] == "Awake") {
				$bodyname = str_replace("'", "\\\\''", $userrow[$userrow['bodygear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $bodyname . "'");
				while ($row = mysql_fetch_array($itemresult)) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['bodygear']]) {
						if ($row['hybrid'] == 1) $row = convertHybrid($row, true);
						$bodydef = $row['power'];
						$bodyrow = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			} else {
				$bodydef = 0;
			}
			if ($userrow['accessory'] != "" && $userrow['dreamingstatus'] == "Awake") {
				$accname = str_replace("'", "\\\\''", $userrow[$userrow['accessory']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $accname . "'");
				while ($row = mysql_fetch_array($itemresult)) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['accessory']]) {
						if ($row['hybrid'] == 1) $row = convertHybrid($row, false);
						$accdef = $row['power'];
						$accrow = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			} else {
				$accdef = 0;
			}
			$totaldef = $headdef + $facedef + $bodydef + $accdef;
			$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = 'Perfectly Generic Object'");
			$blankrow = mysql_fetch_array($itemresult);
			$spritepower = 0; //Not counting sprite power in these calcs.
			$powerlevel = $unarmedpower + $mainpower + $offpower; //Note that this will be relatively undervalued because it does not count boosts yet.
			$rungarrays[$userrow['Echeladder']][] = $powerlevel;
		}
		var_dump($rungarrays);
		echo "derp derp derp";
	}
}