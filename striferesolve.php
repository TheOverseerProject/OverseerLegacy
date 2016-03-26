<?php
require 'additem.php'; //required for enemies who drop loot
require 'monstermaker.php'; //Required for enemies who summon more enemies
require_once 'includes/glitches.php'; //For displaying glitchy nonsense
require_once("header.php");
require_once("includes/grist_icon_parser.php");
require_once("includes/fieldparser.php");
//NOTE - This file assumes you're coming from the strife.php file.

function refreshSingular($slot, $target, $userrow) { //used for enemies that generate other enemies, so that it doesn't revert enemy data that has already been edited
	$dummyrow = refreshEnemydata($userrow);
	$enstr = 'enemy' . strval($slot);
	$tenstr = 'enemy' . strval($target);
	$userrow[$tenstr . 'name'] = $dummyrow[$enstr . 'name'];
	$userrow[$tenstr . 'health'] = $dummyrow[$enstr . 'health'];
	$userrow[$tenstr . 'power'] = $dummyrow[$enstr . 'power'];
	$userrow[$tenstr . 'maxhealth'] = $dummyrow[$enstr . 'maxhealth'];
	$userrow[$tenstr . 'maxpower'] = $dummyrow[$enstr . 'maxpower'];
	$userrow[$tenstr . 'desc'] = $dummyrow[$enstr . 'desc'];
	$userrow[$tenstr . 'category'] = $dummyrow[$enstr . 'category'];
	$stresult = mysql_query("SELECT `strifestatus` FROM `Players` WHERE `Players`.`username` = '" . $userrow['username'] . "'");
	$strow = mysql_fetch_array($stresult);
	$userrow['strifestatus'] = $strow['strifestatus'];
	return $userrow;
}
$userrow = parseEnemydata($userrow);
$max_enemies = 50;
if (empty($_SESSION['username'])) {
	echo "Log in to engage in strife.</br>";
	echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} elseif (empty($_POST['offense']) && empty($_POST['abscond'])) { //No attack command
	echo "What are you still doing here? Off with you!</br>";
	echo '<a href="strife.php">Continue</a><a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} elseif ($userrow['sessionbossengaged'] == 1) {
	echo "You are currently fighting a session-wide boss! <a href='sessionboss.php'>Go here.</a></br>";
} elseif (!empty($_POST['abscond']) && $userrow['cantabscond'] == 1) {
	mysql_query("UPDATE `Players` SET `strifemessage` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //This is apparently necessary for some inscrutable reason.
	mysql_query("UPDATE `Players` SET `strifemessage` = '" . mysql_real_escape_string("CAN'T ABSCOND, BRO!") . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //APOSTROPHEEEEEEEEES
	include("strife.php");
} elseif (!empty($_POST['abscond'])) {
	if ($userrow['dreamingstatus'] == "Prospit") {
		echo 'You abscond from "strife".</br>';
	} else {
		echo "You abscond from strife.</br>";
	}
	$luck = ceil($userrow['Luck'] + $userrow['Brief_Luck']); //Calculate the player's luck total. Paranoia: Make sure we don't somehow have a non-integer.
	if ($luck > 100) $luck = 100; //We work with luck as a percentage generally. This may be changed later.
	$userrow = terminateStrife($userrow, 2);
	if (!empty($userrow['allies'])) { //here, we'll explode the currentstatus to see if we have any NPC allies
   	$thisstatus = explode("|", $userrow['allies']);
   	$st = 0;
   	$npcaidmsg = "";
   	$reachedperm = false;
   	while (!empty($thisstatus[$st])) {
   		$statusarg = explode(":", $thisstatus[$st]);
   		if ($statusarg[0] == "PARTY") { //this is an ally's stats, and we only care about allies here
   			//format: <status>:<basename>:<loyalty>:<nickname>:<desc>:<power>| with the last 3 args being optional
   			$statusarg[2] -= 25; //subtract off the loyalty from absconding
   			if ($statusarg[2] <= 0) {
   				if (!empty($statusarg[3])) $npcname = $statusarg[3];
   				else $npcname = $statusarg[1];
   				echo $npcname . " loses all loyalty and keeps absconding even after reaching safety!<br />";
   				$userrow['allies'] = preg_replace('/' . $thisstatus[$st] . '/', '', $userrow['allies'], 1);
   			} else {
   				$replaced = implode(":", $statusarg);
   				$userrow['allies'] = preg_replace('/' . $thisstatus[$st] . '/', $replaced, $userrow['allies'], 1);
   			}
   		}
   		$st++;
   	}
  }
  mysql_query("UPDATE Players SET allies = '" . mysql_real_escape_string($userrow['allies']) . "' WHERE username = '$username'");
	include("strife.php");
} else {
	$dontcheckvictory = false;
	if ($_POST['focusenemy'] > 1 && !empty($userrow["enemy" . strval($_POST['focusenemy']) . "name"])) {
	  //this should swap the two enemy rows, putting the one the user wants to focus on at the top and therefore giving it less effective power
	  $userrow = refreshSingular($_POST['focusenemy'], 1, $userrow);
	  $userrow = refreshSingular(1, $_POST['focusenemy'], $userrow);
	  $userrow['strifestatus'] = str_replace("ENEMY" . strval($_POST['focusenemy']), "ENEMY0", $userrow['strifestatus']); //pretty sure this is necessary. yep.
	  $userrow['strifestatus'] = str_replace("ENEMY1", "ENEMY" . strval($_POST['focusenemy']), $userrow['strifestatus']);
	  $userrow['strifestatus'] = str_replace("ENEMY0", "ENEMY1", $userrow['strifestatus']);
	  //since we're refreshing enemy data from the database, there's no need to store it temporarily somewhere EXCEPT FOR STATUSES. yep.
	}
	if (empty($userrow['Class'])) $userrow['Class'] = "Default";
	$classresult = mysql_query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$userrow[Class]';");
	$classrow = mysql_fetch_array($classresult);
	$unarmedpower = floor($userrow['Echeladder'] * (pow(($classrow['godtierfactor'] / 100),$userrow['Godtier'])));
	$factor = ((612 - $userrow['Echeladder']) / 611);
	$unarmedpower = ceil($unarmedpower * ((($classrow['level1factor'] / 100) * $factor) + (($classrow['level612factor'] / 100) * (1 - $factor))));
	if  ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") { //User not actually strifing, get rid of them.
		if ($userrow['dreamingstatus'] == "Prospit") {
			echo "You are not currently engaged in &quot;strife&quot;.</br>";
		} else {
			echo "You are not currently engaged in strife.</br>";
		}
		echo '<a href="strife.php">Continue</a> <a href="/">Home</a>';
	} else {
		if (!empty($userrow['permstatus'])) {
			$thisstatus = explode("|", $userrow['permstatus']);
			$st = 0;
			while (!empty($thisstatus[$st])) {
				$statusarg = explode(":", $thisstatus[$st]);
				$thingyarray = explode(".", $statusarg[0]);
				$statusarg[0] = $thingyarray[0]; //remove duration from any tags that have it so they will function exactly like strifestatus
				$thisstatus[$st] = implode(":", $statusarg);
				$st++;
			}
			$perms = implode("|", $thisstatus) . "PERMS|"; //boom done
		}
		$currentstatus = $perms . $userrow['strifestatus']; //This has to be done up here so that the ability code can operate.
		//echo $currentstatus;
		//note that PERMS| is only so we can separate the two later when writing to the database.
		//perm statuses are first because new temp statuses are added to the end of currentstatus
    $abilities = loadAbilities($userrow); //it's a function now wooo
    $message = ""; //This variable contains combat messages. It is saved at the end of the file and strife.php reads it off.
    $offense = $_POST['offense'];
    $defense = $_POST['defense'];
    $luck = ceil($userrow['Luck'] + $userrow['Brief_Luck']); //Calculate the player's luck total. Paranoia: Make sure we don't somehow have a non-integer.
    if (!empty($abilities[19])) { //Light's Favour activates. Increase luck.
		$luck += floor($userrow['Echeladder'] / 30);
		$message = $message . "$abilities[19]</br>";
	}
    if ($luck > 100) $luck = 100; //We work with luck as a percentage generally. This may be changed later.
    $mainpower = 0;
    $offpower = 0;
    $headdef = 0;
    $facedef = 0;
    $bodydef = 0;
    $accdef = 0;
    $totaldef = 0;
    $powerlevel = 0;
    $spritepower = $userrow['sprite_strength'];
    $resistances = array(); //aspect-related resistances for use against aspect bosses
    if ($userrow['equipped'] != "" && $userrow['dreamingstatus'] == "Awake") {
		$mainrow = $_SESSION['mainrow']; //We save this to check weapon-specific bonuses to various commands.
		$mainpower = $mainrow['power'];
    } else {
		$mainpower = 0;
    }
    if ($userrow['offhand'] != "" && $userrow['offhand'] != "2HAND" && $userrow['dreamingstatus'] == "Awake") {
		$offrow = $_SESSION['offrow']; //We save this to check weapon-specific bonuses to various commands.
		$offpower = ($offrow['power'] / 2);
    } else {
		$offpower = 0;
    }
    if ($userrow['headgear'] != "" && $userrow['dreamingstatus'] == "Awake") {
		$headrow = $_SESSION['headrow']; //We save this to check weapon-specific bonuses to various commands.
		if ($headrow['hybrid'] == 1) $headrow = convertHybrid($headrow, false);
		$headdef = $headrow['power'];
		$resistances = wearableAffinity($resistances, $userrow['Aspect'], $headrow['effects']);
    } else {
		$headdef = 0;
    }
    if ($userrow['facegear'] != "" && $userrow['facegear'] != "2HAND" && $userrow['dreamingstatus'] == "Awake") {
		$facerow = $_SESSION['facerow']; //We save this to check weapon-specific bonuses to various commands.
		if ($facerow['hybrid'] == 1) $facerow = convertHybrid($facerow, false);
		$facedef = $facerow['power'];
		$resistances = wearableAffinity($resistances, $userrow['Aspect'], $facerow['effects']);
    } else {
		$facedef = 0;
    }
    if ($userrow['bodygear'] != "" && $userrow['dreamingstatus'] == "Awake") {
		$bodyrow = $_SESSION['bodyrow']; //We save this to check weapon-specific bonuses to various commands.
		if ($bodyrow['hybrid'] == 1) $bodyrow = convertHybrid($bodyrow, false);
		$bodydef = $bodyrow['power'];
		$resistances = wearableAffinity($resistances, $userrow['Aspect'], $bodyrow['effects']);
    } else {
		$bodydef = 0;
    }
	if ($userrow['accessory'] != "" && $userrow['dreamingstatus'] == "Awake") {
		$accrow = $_SESSION['accrow']; //We save this to check weapon-specific bonuses to various commands.
		if ($accrow['hybrid'] == 1) $accrow = convertHybrid($accrow, false);
		$accdef = $accrow['power'];
		$resistances = wearableAffinity($resistances, $userrow['Aspect'], $accrow['effects']);
    } else {
		$accdef = 0;
    }
    $totaldef = $headdef + $facedef + $bodydef + $accdef;
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = 'Perfectly Generic Object'");
    $blankrow = mysql_fetch_array($itemresult);
    //Define rows as effectively empty when player dreaming or has nothing equipped. Track number of blanks for the purposes of the Void roletech (and maybe some other stuff)
    $blanks = 0;
    $voidvalid = 0; //Roletech ID 15 "One with Nothing" only activates if both weapon slots are empty.
    if (empty($mainrow) || $userrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$voidvalid++;
		$mainrow = $blankrow;
    }
    if (empty($offrow) || $userrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$voidvalid++;
		$offrow = $blankrow;
    }
    if (empty($headrow) || $userrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$headrow = $blankrow;
    }
    if (empty($facerow) || $userrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$facerow = $blankrow;
    }
    if (empty($bodyrow) || $userrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$bodyrow = $blankrow;
    }
    if (empty($accrow) || $userrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$accrow = $blankrow;
    }
    if ($spritepower < 0) {
		$spritepower = 0;
    }
    //Assistance code starts here
    $aiddef = 0;
    $aidpower = 0;
    $aidsprite = 0;
    $aidaggrieve = 0;
    $aidaggress = 0;
    $aidassail = 0;
    $aidassault = 0;
    $aidabuse = 0;
    $aidaccuse = 0;
    $aidabjure = 0;
    $aidabstain = 0;
    $userescape = mysql_real_escape_string($username); //Add escape characters so we can find session correctly in database.
    $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '" . $userescape . "'");
    while ($row = mysql_fetch_array($sessionmates)) {
		if ($row['aiding'] == $username) { //Aiding character.
			$classresult = mysql_query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$row[Class]';");
			$classrow = $_SESSION['classrow'];
			$unarmedaidpower = floor($row['Echeladder'] * (pow(($classrow['godtierfactor'] / 100),$row['Godtier'])));
			$factor = ((612 - $row['Echeladder']) / 611);
			$unarmedaidpower = ceil($unarmedaidpower * ((($classrow['level1factor'] / 100) * $factor) + (($classrow['level612factor'] / 100) * (1 - $factor)))); //Finish calculating unarmed power.
			$unarmedaidpower = floor($unarmedaidpower * ($classrow['passivefactor'] / 100)); //User is not main strifer, apply passive modifier.
			$aidpower += $unarmedaidpower;
			$aidabilities = loadAbilities($row); //it's a function now wooo
			if ($row['equipped'] != "") {
				$equipname = str_replace("'", "\\\\''", $row[$row['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $equipname . "'");
				while ($itemrow = mysql_fetch_array($itemresult)) {
					$itemname = $itemrow['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $row[$row['equipped']]) {
						if ($row['dreamingstatus'] != "Awake") $itemrow = $blankrow; //Blank row if assister is asleep.
						$aidpower += $itemrow['power'];
						$aidmainrow = $itemrow; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			}
			if ($row['offhand'] != "" && $row['offhand'] != "2HAND") {
				$offname = str_replace("'", "\\\\''", $row[$row['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $offname . "'");
				while ($itemrow = mysql_fetch_array($itemresult)) {
					$itemname = $itemrow['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $row[$row['offhand']]) {
						if ($row['dreamingstatus'] != "Awake") $itemrow = $blankrow; //Blank row if assister is asleep.
						$aidpower += ($itemrow['power'] / 2);
						$aidoffrow = $itemrow;
					}
				}
			}
			if ($row['headgear'] != "") {
				$headname = str_replace("'", "\\\\''", $row[$row['headgear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $headname . "'");
				while ($itemrow = mysql_fetch_array($itemresult)) {
					$itemname = $itemrow['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $row[$row['headgear']]) {
						if ($row['dreamingstatus'] != "Awake") $itemrow = $blankrow; //Blank row if assister is asleep.
						if ($row['hybrid'] == 1) $itemrow = convertHybrid($itemrow, false);
						$aiddef += $itemrow['power'];
						$aidheadrow = $itemrow;
					}
				}
			}
			if ($row['facegear'] != "" && $row['facegear'] != "2HAND") {
				$facename = str_replace("'", "\\\\''", $row[$row['facegear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $facename . "'");
				while ($itemrow = mysql_fetch_array($itemresult)) {
					$itemname = $itemrow['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $row[$row['facegear']]) {
						if ($row['dreamingstatus'] != "Awake") $itemrow = $blankrow; //Blank row if assister is asleep.
						if ($row['hybrid'] == 1) $itemrow = convertHybrid($itemrow, false);
						$aiddef += $itemrow['power'];
						$aidfacerow = $itemrow;
					}
				}
			}
			if ($row['bodygear'] != "") {
				$bodyname = str_replace("'", "\\\\''", $row[$row['bodygear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $bodyname . "'");
				while ($itemrow = mysql_fetch_array($itemresult)) {
					$itemname = $itemrow['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $row[$row['bodygear']]) {
						if ($row['dreamingstatus'] != "Awake") $itemrow = $blankrow; //Blank row if assister is asleep.
						if ($row['hybrid'] == 1) $itemrow = convertHybrid($itemrow, true);
						$aiddef += $itemrow['power'];
						$aidbodyrow = $itemrow;
					}
				}
			}
			if ($row['accessory'] != "") {
				$accname = str_replace("'", "\\\\''", $row[$row['accessory']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $accname . "'");
				while ($itemrow = mysql_fetch_array($itemresult)) {
					$itemname = $itemrow['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $row[$row['accessory']]) {
						if ($row['dreamingstatus'] != "Awake") $itemrow = $blankrow; //Blank row if assister is asleep.
						if ($row['hybrid'] == 1) $itemrow = convertHybrid($itemrow, false);
						$aiddef += $itemrow['power'];
						$aidaccrow = $itemrow;
					}
				}
			}
			if ($row['sprite_strength'] > 0 && $row['dreamingstatus'] == "Awake") $aidsprite += $row['sprite_strength'];
			$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = 'Perfectly Generic Object'");
			$blankrow = mysql_fetch_array($itemresult);
			$aidblanks = 0;
			$aidvoidvalid = 0;
			if (empty($aidmainrow) || $row['dreamingstatus'] != "Awake") {
				$aidblanks++;
				$aidvoidvalid++;
				$aidmainrow = $blankrow; //Define rows as effectively empty when assister dreaming or has nothing equipped.
			}
			if (empty($aidoffrow) || $row['dreamingstatus'] != "Awake") {
				$aidblanks++;
				$aidvoidvalid++;
				$aidoffrow = $blankrow;
			}
			if (empty($aidheadrow) || $row['dreamingstatus'] != "Awake") {
			$aidblanks++;
			$aidheadrow = $blankrow;
			}
			if (empty($aidfacerow) || $row['dreamingstatus'] != "Awake") {
				$aidblanks++;
				$aidfacerow = $blankrow;
			}
			if (empty($aidbodyrow) || $row['dreamingstatus'] != "Awake") {
				$aidblanks++;
				$aidbodyrow = $blankrow;
			}
			if (empty($aidaccrow) || $row['dreamingstatus'] != "Awake") {
				$aidblanks++;
				$aidaccrow = $blankrow;
			}
			if (!empty($aidabilities[15]) && $aidblanks > 0 && $aidvoidvalid == 2) { //One with Nothing activates. Increase unarmed power for the purposes of power level.
				$message = $message . "$row[username]'s $aidabilities[15]</br>";
				if ($row['dreamingstatus'] == "Awake") {
					$aidvoidpower = 5 * $aidblanks * $row['Echeladder'];
				} else {
					$aidvoidpower = floor($row['Echeladder'] / 2);
				}
				$message = $message . "Void power boost: " . strval($aidvoidpower) . "</br>";
				$aidpower += $aidvoidpower;
			}
			if (!empty($aidabilities[17])) { //Blood Bonds activates. Increase power. We know it activated because this is an assister!
				$message = $message . "$row[username]'s $aidabilities[17]</br>";
				$aidbloodbond = 0;
				$testuserescape = mysql_real_escape_string($row['aiding']); //Add escape characters so we can find session correctly in database.
				$testsessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '" . $testuserescape . "'");
				while ($testrow = mysql_fetch_array($testsessionmates)) {
					if ($testrow['aiding'] == $row['aiding']) $aidbloodbond += $unarmedaidpower; //Grab everyone assisting. Grabs this player but NOT main strifer, so number comes out the same.
				}				
				$message = $message . "Blood bond strength:" . strval($aidbloodbond) . "</br>";
				$aidpower += $aidbloodbond;
			}
			$aidaggrieve += ($aidmainrow['aggrieve'] + floor($aidoffrow['aggrieve'] / 2) + $aidheadrow['aggrieve'] + $aidfacerow['aggrieve'] + $aidbodyrow['aggrieve'] + $aidaccrow['aggrieve']);
			$aidaggress += ($aidmainrow['aggress'] + floor($aidoffrow['aggress'] / 2) + $aidheadrow['aggress'] + $aidfacerow['aggress'] + $aidbodyrow['aggress'] + $aidaccrow['aggress']);
			$aidassail += ($aidmainrow['assail'] + floor($aidoffrow['assail'] / 2) + $aidheadrow['assail'] + $aidfacerow['assail'] + $aidbodyrow['assail'] + $aidaccrow['assail']);
			$aidassault += ($aidmainrow['assault'] + floor($aidoffrow['assault'] / 2) + $aidheadrow['assault'] + $aidfacerow['assault'] + $aidbodyrow['assault'] + $aidaccrow['assault']);
			$aidabuse += ($aidmainrow['abuse'] + floor($aidoffrow['abuse'] / 2) + $aidheadrow['abuse'] + $aidfacerow['abuse'] + $aidbodyrow['abuse'] + $aidaccrow['abuse']);
			$aidaccuse += ($aidmainrow['accuse'] + floor($aidoffrow['accuse'] / 2) + $aidheadrow['accuse'] + $aidfacerow['accuse'] + $aidbodyrow['accuse'] + $aidaccrow['accuse']);
			$aidabjure += ($aidmainrow['abjure'] + floor($aidoffrow['abjure'] / 2) + $aidheadrow['abjure'] + $aidfacerow['abjure'] + $aidbodyrow['abjure'] + $aidaccrow['abjure']);
			$aidabstain += ($aidmainrow['abstain'] + floor($aidoffrow['abstain'] / 2) + $aidheadrow['abstain'] + $aidfacerow['abstain'] + $aidbodyrow['abstain'] + $aidaccrow['abstain']);
		}
    }
    $bonuseffects = "";
    if (!empty($abilities[7])) { //Aspect Fighter activates, granting affinity.
			$bonuseffects .= "AFFINITY:" . $userrow['Aspect'] . ":5|";
			$message = $message . "Lv. 83 Knightskill Aspect Fighter activates! Your strikes are imbued with " . $userrow['Aspect'] . " affinity.";
		}
		$confusestr = "PLAYER:CONFUSE|";
	  if (strpos($currentstatus, $confusestr) !== False) { //Player is confused.
	  	$bonuseffects .= "RECOIL:50:25|"; //confusion causes 50% chance to deal 25% of damage done to enemy
	  }
	  $npcaidmsg = "";
    if (!empty($currentstatus)) { //here, we'll explode the currentstatus to see if we have any NPC allies
    	$thisstatus = explode("|", $currentstatus);
    	$st = 0;
    	while (!empty($thisstatus[$st])) {
    		$statusarg = explode(":", $thisstatus[$st]);
    		if ($statusarg[0] == "HASEFFECT") {
    			$rounds = intval($statusarg[1]);
    			if ($rounds > 0) {
    				$rounds--;
    				if ($rounds <= 0) {
    					$currentstatus = str_replace($thisstatus[$st] . "|", "", $currentstatus); //effect wears off
    					$message = $message . "An instance of $statusarg[2] imbuement has worn off.<br />";
    				} else {
    					$replacethis = "HASEFFECT:$statusarg[1]:$statusarg[2]";
							$replaceitwith = "HASEFFECT:$rounds:$statusarg[2]";
							$currentstatus = preg_replace('/' . $replacethis . '/', $replaceitwith, $currentstatus, 1); //ticks down 1 round
    				}
    			}
    			$imbue = str_replace("HASEFFECT:$statusarg[1]:", "", $thisstatus[$st]); //cuts off the haseffect tag and adds everything else to bonuseffects
    			$bonuseffects .= $imbue . "|";
    		} elseif ($statusarg[1] == "CHARMED") { //enemy has a charmed effect
    			$thischarm = substr($statusarg[0], 4); //find out which enemy it is
    			if (empty($charmed[intval($thischarm)])) { //only one instance of CHARMED can work at a time
    				$charmstr = "enemy" . $thischarm . "name";
    				$charmpstr = "enemy" . $thischarm . "power";
    				$aidpower += $userrow[$charmpstr];
    				$npcaidmsg .= $userrow[$charmstr] . " is charmed into contributing its power to your side!<br />";
    				$charmed[intval($thischarm)] = true;
    			}
    		}
    		$st++;
    	}
    }
    if (!empty($userrow['allies']) && $userrow['dreamstatus'] == "Awake") { //no allies while dreaming!
    	$thisstatus = explode("|", $userrow['allies']);
    	$st = 0;
    	while (!empty($thisstatus[$st])) {
    		$statusarg = explode(":", $thisstatus[$st]);
    		if ($statusarg[0] == "PARTY") { //this ally is in the active party
    			//format: <status>:<basename>:<loyalty>:<nickname>:<desc>:<power>| with the last 3 args being optional
    			$npcresult = mysql_query("SELECT * FROM `Enemy_Types` WHERE `Enemy_Types`.`basename` = '$statusarg[1]'");
    			$npcrow = mysql_fetch_array($npcresult);
    			if (!empty($statusarg[5])) $npcpower = $statusarg[5];
    			else $npcpower = $npcrow['basepower'];
    			if (!empty($statusarg[3])) $npcname = $statusarg[3];
    			else $npcname = $npcrow['basename'];
    			$aidpower += $npcpower;
    			$npcaidmsg .= $npcname . " contributes to the fight!<br />";
    			if (strpos($npcrow['spawnstatus'], "SPECIAL") !== false) {
    				$thatstatus = explode("|", $npcrow['spawnstatus']);
    				$ts = 0;
    				while (!empty($thatstatus[$ts])) {
    					$specialarg = explode(":", $thatstatus[$ts]);
    					if ($specialarg[0] == "SPECIAL") {
    						switch ($specialarg[1]) {
    							case NOCAP:
    								$bonuseffects .= "WATERYGEL:" . $specialarg[2] . "|";
    								break;
    							case POISON:
    								$bonuseffects .= "POISON:" . $specialarg[2] . ":" . $specialarg[3] . "|";
    								break;
    							case CONFUSE:
    								$bonuseffects .= "DISORIENTED:" . $specialarg[2] . ":" . $specialarg[3] . "|";
    								break;
    							case STUN:
    								$bonuseffects .= "KNOCKDOWN:" . $specialarg[2] . "|";
    								break;
    							case BURNING:
    								$bonuseffects .= "BURNING:" . $specialarg[2] . ":" . $specialarg[3] . "|";
    								break;
    							case HASEFFECT:
    								$imbue = str_replace("SPECIAL:HASEFFECT:", "", $thatstatus[$ts]); //cuts off the haseffect tag and adds everything else to bonuseffects
    								$bonuseffects .= $imbue . "|";
    								break;
    							default:
    								break;
    						}
    					}
    					$ts++;
    				}
    			}
    		}
    		$st++;
    	}
    }
    $loyaltydrain = 0;
    //Assistance code ends here
    $once = True;
    if (!empty($_POST['repeat'])) {
		$repeat = True;
    } else {
		$repeat = False;
    }
    $turncounter = 0;
    $nodamagecounter = 0;
    while ($once == True || $repeat == True) {
		$turncounter += 1;
		$nodamage = False;
		if (!empty($abilities[1]) && $offense == "aggress") { //Activate passive aggression: apply passive modifier. (ID 1)
			$message = $message . "$abilities[1]</br>";
			$unarmedpower = floor($unarmedpower * ($classrow['passivefactor'] / 100));
		} else {
			$unarmedpower = floor($unarmedpower * ($classrow['activefactor'] / 100)); //User is main strifer, apply active modifier.
		}
		$powerlevel = $unarmedpower + $userrow['powerboost'] + $userrow['temppowerboost'] + $mainpower + $offpower + $aidpower; //Sprite power added later.
		if (!empty($abilities[15]) && $blanks > 0 && $voidvalid == 2) { //One with Nothing activates. Increase unarmed power for the purposes of power level.
			$message = $message . "$abilities[15]</br>";
			if ($userrow['dreamingstatus'] == "Awake") {
				$voidpower = 5 * $blanks * $userrow['Echeladder'];
			} else {
				$voidpower = floor($userrow['Echeladder'] / 2);
			}
			$message = $message . "Void power boost: " . strval($voidpower) . "</br>";
			$powerlevel += $voidpower;
		}
		if (!empty($abilities[17])) { //Blood Bonds activates. Increase power. Note that it doesn't ACTUALLY trigger unless someone is found.
			$bloodbond = 0;
			$testuserescape = mysql_real_escape_string($username); //Add escape characters so we can find session correctly in database.
			$testsessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '" . $testuserescape . "'");
			while ($testrow = mysql_fetch_array($testsessionmates)) {
				if ($testrow['aiding'] == $username) $bloodbond += $unarmedpower; //Grab everyone assisting.
			}
			if ($bloodbond > 0) { //Ability actually triggers. Print message etc.
				$message = $message . "$abilities[17]</br>";
				$message = $message . "Blood bond strength:" . strval($bloodbond) . "</br>";
				$powerlevel += $bloodbond;
			}
		}
		$offensepower = $powerlevel + $userrow['offenseboost'] + $userrow['tempoffenseboost'];
		$defensepower = $powerlevel + $userrow['defenseboost'] + $userrow['tempdefenseboost'] + $totaldef + $aiddef;
		if (!empty($abilities[7])) { //Aspect Fighter (ID 7)
			$message = $message . "$abilities[7]</br>";
			$aspectrow = $_SESSION['aspectrow'];
			$offensebonus = floor($aspectrow['Damage'] + $aspectrow['Power_down'] + $aspectrow['Offense_up'] + floor($aspectrow['Power_up'] / 2) * ($unarmedpower / 612));
			$defensebonus = floor($aspectrow['Invulnerability'] + $aspectrow['Heal'] + $aspectrow['Defense_up'] + floor($aspectrow['Power_up'] / 2) * ($unarmedpower / 612));
			if ($offensebonus < 0) $offensebonus = 0; //Paranoia: If something goes wrong, set the value to zero.
			if ($defensebonus < 0) $defensebonus = 0;
			$offensepower += $offensebonus;
			$defensepower += $defensebonus;
			$message = $message . "Offense bonus: $offensebonus</br>Defense bonus: $defensebonus</br>"; //Special string: Print boost values.
		}
		if (!empty($abilities[3]) && $offense == "assault") { //Activate chaotic assault: randomize between -100 and 350 bonus power with a luck factor. (ID 3)
			$message = $message . "$abilities[3]</br>";
			$offensepower = ceil($offensepower + (rand((-100 + $luck),350) * ($userrow['Echeladder'] / 300)));
		}
		//Nullification of weapons occurs by blanking the weapon rows.
		//Set the last commands used.
		$userrow['lastactive'] = $offense;
		$userrow['lastpassive'] = $defense;
		switch ($offense) {
		case "aggrieve":
			$offensepower = ceil($offensepower * 1.05) + $mainrow['aggrieve'] + floor($offrow['aggrieve']/2) + $aidaggrieve + $headrow['aggrieve'] + $facerow['aggrieve'] + $bodyrow['aggrieve'] + $accrow['aggrieve'];
			break;
		case "aggress":
			$offensepower = ceil($offensepower * 1.2) + $mainrow['aggress'] + floor($offrow['aggress']/2) + $aidaggress + $headrow['aggress'] + $facerow['aggress'] + $bodyrow['aggress'] + $accrow['aggress'];
			$defensepower = floor($defensepower * 0.8);
			break;
		case "assail":
			$offensepower = ceil($offensepower * 1.5) + $mainrow['assail'] + floor($offrow['assail']/2) + $aidassail + $headrow['assail'] + $facerow['assail'] + $bodyrow['assail'] + $accrow['assail'];
			$defensepower = floor($defensepower * 0.66666666);
			break;
		case "assault":
			$offensepower = ceil($offensepower * 2) + $mainrow['assault'] + floor($offrow['assault']/2) + $aidassault + $headrow['assault'] + $facerow['assault'] + $bodyrow['assault'] + $accrow['assault'];
			$defensepower = floor($defensepower * 0.5);
			break;
		default:
			break;
		}
		switch ($defense) {
		case "abuse":
			$offensepower = ceil($offensepower * 1.1) + $mainrow['abuse'] + floor($offrow['abuse']/2) + $aidabuse + $headrow['abuse'] + $facerow['abuse'] + $bodyrow['abuse'] + $accrow['abuse'];
			break;
		case "accuse": //Accuse is a strict defensive action.
			$defensepower = ceil($defensepower * 1.05) + $mainrow['accuse'] + floor($offrow['accuse']/2) + $aidaccuse + $headrow['accuse'] + $facerow['accuse'] + $bodyrow['accuse'] + $accrow['accuse'];
			break;
		case "abjure":
			$defensepower = ceil($defensepower * 1.5) + $mainrow['abjure'] + floor($offrow['abjure']/2) + $aidabjure + $headrow['abjure'] + $facerow['abjure'] + $bodyrow['abjure'] + $accrow['abjure'];
			$offensepower = floor($offensepower * 0.66666666);
			break;
		case "abstain":
			$defensepower = ceil($defensepower * 2) + $mainrow['abstain'] + floor($offrow['abstain']/2) + $aidabstain + $headrow['abstain'] + $facerow['abstain'] + $bodyrow['abstain'] + $accrow['abstain'];
			$offensepower = floor($offensepower * 0.5);
			break;
		default:
			break;
		}
		if ($userrow['dreamingstatus'] == "Awake") {
			$offensepower = $offensepower + $spritepower + $aidsprite;
			$defensepower = $defensepower + $spritepower + $aidsprite;
		}
		if ($userrow['dreamingstatus'] == "Prospit") {
			echo 'You and your "opponents" trade a series of "blows".</br>';
		} else {
			echo "You and your opponents trade a series of blows.</br>";
		}
		$sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
		$sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '" . $username . "'");
		$aides = 0;
		while ($row = mysql_fetch_array($sessionmates)) {
			if ($row['aiding'] == $username) { //Aiding character.
				$message = $message . "$row[username] contributes to the fight!</br>";
				$aides += 1;
			}
		}
		if (!empty($npcaidmsg)) $message = $message . $npcaidmsg;
		$i = 1;
		$enemiesfought = 0;
		$alldead = True;
		$damage = 0;
		$enemydown = False;
		$targetedenemies = 0;
		while ($i <= $max_enemies) { 
			//first loop is to find out how many enemies exist at the beginning of the round, to deal with monsters that spawn more monsters
			//when defeated, such as the hydra heads. This prevents them from being targetable on the round that they spawn and prevents
			//practically infinite loops/rewards. Monsters spawned during the player damage loop can still inflict damage or use specials.
			$enemystr = "enemy" . strval($i) . "name";
			if ($userrow[$enemystr] != "") $targetedenemies++;
			$i++;
		}
		//echo "DEBUG: " . strval($targetedenemies) . " enemies targeted<br />";
		$i = 1;
		while ($i <= $max_enemies) {
			$statustr = "ENEMY" . strval($i) . ":";
			$enemystr = "enemy" . strval($i) . "name";
			$powerstr = "enemy" . strval($i) . "power";
			$maxpowerstr = "enemy" . strval($i) . "maxpower";
			$healthstr = "enemy" . strval($i) . "health";
			$maxhealthstr = "enemy" . strval($i) . "maxhealth";
			$descstr = "enemy" . strval($i) . "desc"; //Need this to check for nulls.
			$categorystr = "enemy" . strval($i) . "category";
			if ($userrow[$enemystr] != "") { //Enemy spotted!
				$enemyrowexists = False;
				if (empty($_SESSION[$userrow[$enemystr]])) {
					$enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE '" . $userrow[$enemystr] . "' LIKE CONCAT ('%', `Enemy_Types`.`basename`, '%')");
					while ($temprow = mysql_fetch_array($enemyresult)) { //Enemy showed up in the table.
						if ($enemyrowexists) { //if the query yielded multiple results...
							if (strlen($temprow['basename']) > strlen($enemyrow['basename'])) { //see if the new result has a longer name than the previous
								$enemyrow = $temprow; //if it does, this is the one we want
								//this should prevent mismatches like Lich Queen using the entry for Lich, or Imp King using the entry for Imp
							}
						} else {
							$enemyrowexists = True;
							$enemyrow = $temprow;
						}
					}
					if ($enemyrowexists) {
						$_SESSION[$userrow[$enemystr]] = $enemyrow;
					}
				} else {
					$enemyrow = $_SESSION[$userrow[$enemystr]];
					$enemyrowexists = True;
				}
				$disorientedstr = ($statustr . "DISORIENTED");
				if (strpos($currentstatus, $disorientedstr) !== False) { //This enemy is disoriented. It is treated as not existing for the purposes of volume bonuses.
					$message = $message . $userrow[$enemystr] . "is disoriented and cannot fight effectively with allies</br>";
					$numbersfactor = 1;
				} else {
					$numbersfactor = 1 + ($enemiesfought * 0.125);
					$enemiesfought++;
				}
				if ($i <= $targetedenemies) { //don't inflict damage/etc on an enemy that was spawned during this loop
				//echo "DEBUG: Targeting enemy in slot " . strval($i) . "</br>";
				//Calculate damage with a random element thrown in. 100 luck forces a max roll.
				$enemydamage = rand(floor($offensepower * (0.85 + ($luck * 0.003))),ceil($offensepower * 1.15)) - floor($userrow[$powerstr] * $numbersfactor);
				$mainoff = 1;
					while ($mainoff < 4) { //1 for main, 2 for off. 3 for bonus effects from HASEFFECT and etc. 4 means done.
						if ($mainoff == 1) { //Handle main hand effects.
							$effectarray = explode('|', $mainrow['effects']);
						} elseif ($mainoff == 2) { //Handle offhand effects.
							$effectarray = explode('|', $offrow['effects']);
						} else {
							$effectarray = explode('|', $bonuseffects);
						}
						$effectnumber = 0;
						while (!empty($effectarray[$effectnumber])) {
							$currenteffect = $effectarray[$effectnumber];
							$currentarray = explode(':', $currenteffect); //Note that what each array entry means depends on the effect.
							switch ($currentarray[0]) {
							case 'PIERCING': //Format is PIERCING:<% of power>|. We check for piercing here so that piercing damage can trigger on-damage effects if originally 0 damage.
								$roll = rand((1 + floor($luck/5)),100);
								$resistfactor = $enemyrow['resist_Breath']; //Enemy's breath resistance reduces success chance.
								if ($mainoff == 1) {
									$minpower = $mainrow['power']; //note that this is based on weapon power alone, so no cheesing with power boosts and such
								} else {
									$minpower = ceil($offrow['power'] / 2);
								}
								$minpower = ceil($minpower * (intval($currentarray[1]) / 100));
								if ($roll > $resistfactor && $enemydamage < $minpower) { //piercing has 100% base chance, reduced by enemy's breath resistance
									$enemydamage = $minpower;
									$message = $message . "You manage to pierce $userrow[$enemystr]'s defenses!</br>";
								}
								break;
							case 'AMMO': //Format is AMMO:<type of ammo>:<amount>:<enemy/round>|. Amount can be negative.
							if (empty($ammocheck[$mainoff])) {
								$ammoamount = intval($currentarray[2]);
								if (strpos($currentarray[1], "enemy0") !== false) { //putting enemy0 in front automatically causes it to target the current enemy; for example, enemy0health will drain the health of each enemy in turn
									$currentarray[1] = str_replace("enemy0", "enemy" . strval($i), $currentarray[1]);
								}
								if ($ammoamount > 0) { //we'll handle negative values on a successful hit so that they can't be abused (as easily)
									if ($userrow[$currentarray[1]] < $ammoamount) {
										if ($mainoff == 1)
										$message = $message . "You have run out of $currentarray[1] to use your $mainrow[name]!<br />";
										elseif ($mainoff == 2)
										$message = $message . "You have run out of $currentarray[1] to use your $offrow[name]!<br />";
										elseif ($mainoff == 3) //there's an "imbued" ammo requirement, could be used as a debuff or something
										$message = $message . "You cannot make an attack without $currentarray[1] at the moment!<br />";
										$enemydamage = 0; //weapon cannot damage enemy, but can still be used to defend
										if (intval($currentarray[3]) == 1) $ammocheck[$mainoff] = "bad";
									} else {
										$userrow[$currentarray[1]] -= $ammoamount; //Yes, the ammo type will literally be the name of a player field. This can lead to some interesting effects!
										if (intval($currentarray[3]) == 1) $ammocheck[$mainoff] = "good";
									}
								}
							} elseif ($ammocheck[$mainoff] == "bad") { //weapon only requires ammo per round, we already checked and we don't have enough
								$enemydamage = 0;
							}
								break;
							default:
								//We only care about piercing and similar effects here.
								break;
							}
							$effectnumber++;
						}
						$mainoff++;
					}
				$recoildamage = 0;
				$bonusboons = 0;
				//echo " and your damage is $enemydamage<br />";
				$waterygelstr = ($statustr . "WATERYGEL|");
				$enragedstr = ($statustr . "ENRAGED|");
				if (strpos($currentstatus, $waterygelstr) !== False) { //This enemy is suffering from watery health gel.
					$enemydamage = floor($enemydamage * 1.1); 
				}
				if (strpos($currentstatus, $enragedstr) !== False) { //This enemy is angry and defends poorly.
					$enemydamage += floor($userrow[$powerstr] * 0.1);
				}
				if (!empty($abilities[21])) { //Inevitability activates. Calculate bonus damage (ID 21)
					$bonusdamage = floor(2.5 * (1 - ($userrow[$healthstr] / $userrow[$maxhealthstr])) * $unarmedpower);
					//Bonus damage is 2.5 times the unarmed power divided by the ratio (i.e. if all HP was missing, it would deal 2x unarmed power)
					if ($bonusdamage > 0) {
						$message = $message . $abilities[21] . "</br>";
						$enemydamage += $bonusdamage;
					}
				}
				$crit = rand(1,10); //Need a LOT of luck to crit normally, and the chance is pretty shit.
				if ($userrow['motifcounter'] > 0 && $userrow['Aspect'] == "Light") {
					$crit = rand(10,100); //maxed luck AND light III means you're guaranteed to crit.
				} 
				if (!empty($abilities[23])) { //Fortune's Protection activates (ID 23). Regardless of other modifiers, grant a flat chance to score an instant critical. (Light guides your blow)
					$guidance = rand((1 + floor($luck / 10)),100); //Luck modifier here is small. I mean, it didn't work last time, did it?
					if ($guidance > (100 - floor($userrow['Echeladder'] / 50))) $crit = 100; //Every fifty rungs, increase the chance by 1%
				}
				if ($crit >= (55 - floor($luck * 0.455))) {
					$message = $message . "You land a critical hit on $userrow[$enemystr]!</br>";
					$enemydamage += rand(floor($offensepower * (0.85 + ($luck * 0.003))),ceil($offensepower * 1.15)); //Double the base damage before subtraction by adding it on again.
				} else { //if you were lucky enough to crit while blinded, let you have your moment
					$blindstr = "PLAYER:BLIND|";
	      	if (strpos($currentstatus, $blindstr) !== False) { //Player is blinded.
      			$roll = rand(1,100);
      			if ($roll > 50 - floor($luck / 2)) { //max luck negates blindness entirely
							$message = $message . "Your blindness causes you to miss $userrow[$enemystr] completely!</br>";
							$enemydamage = 0;
						}
					} 
				}
				if (!empty($abilities[22])) {
					$proc = rand(1,100);
					$chance = floor(($userrow['Echeladder'] + ($userrow['Godtier'] * 60)) / 15) + floor($luck / 18);
					if ($proc < $chance) { //40% chance of triggering at max rank. Godtier increases it by 4% per tier. Luck has a low influence.
						$strikes = 2 + floor(rand($luck,100) / 85) + floor(rand($userrow['Echeladder'],1111) / 666) + ceil($userrow['Godtier'] / 3);
						//Two strikes guaranteed. One is luck dependent, one is Echeladder dependent, and extras appear at god tiers 1, 4, 7, etc.
						$message = $message . $abilities[22] . "</br>";
						$message = $message . "Attacks performed: $strikes</br>";
						$enemydamage *= $strikes;
					}
				}
				if ($enemydamage < 0) $enemydamage = 0; //No healing enemies with attacks!
				$timestopped = False;
				if ($enemydamage != 0) { //Effects that trigger on hit go here. They only trigger if damage is dealt!
					//Check the main row weapon for effects.
					$mainoff = 1;
					$werow = $userrow;
					require('includes/strife_weaponeffects.php');
					$nodamage = False; //Damage was dealt.
					if (empty($justmellowed)) { //Damage was dealt and we didn't apply mellow
						$mellowstr = ($statustr . "MELLOW|");
						if (strpos($currentstatus, $mellowstr) !== False) { //Enemy struck, chance to un-mellow
							$savingthrow = rand(1,100);
							$resistfactor = ($enemyrow['resist_Rage'] / 2); //Enemy's Rage resistance improves the save.
							if ($savingthrow > (80 - $resistfactor)) {
								$currentstatus = str_replace($mellowstr, "", $currentstatus);
								$message = $message . "$userrow[$enemystr] manages to get riled up about the battle again</br>";
							}
						}
					}
					if (!empty($charmed[$i])) { //Damage was dealt and we didn't apply charmed this round
						$charmstr = ($statustr . "CHARMED|");
						if (strpos($currentstatus, $charmstr) !== False) { //Enemy struck, chance to return to normal
							$savingthrow = rand(1,100);
							$resistfactor = ($enemyrow['resist_Heart'] / 2); //Enemy's Heart resistance improves the save.
							if ($savingthrow > (50 - $resistfactor)) { //increased chance if struck
								$currentstatus = str_replace($charmstr, "", $currentstatus);
								$message = $message . "$userrow[$enemystr] snaps out of its trance and is once again your enemy</br>";
								$charmed[$i] = false; //power is still contributed, but the enemy gets to attack
							}
						}
					}
				} else { //Effects that trigger when damage is NOT dealt go here.
					$enragedstr = ($statustr . "ENRAGED|");
					if (strpos($currentstatus, $enragedstr) !== False) { //No damage, chance to calm down
						$savingthrow = rand(1,100);
						$resistfactor = ($enemyrow['resist_Rage'] / 2); //Enemy's Rage resistance improves the save.
						if ($savingthrow > (80 - $resistfactor)) {
							$currentstatus = str_replace($enragedstr, "", $currentstatus);
							$message = $message . "$userrow[$enemystr] manages to calm down</br>";
						}
					}
					$charmstr = ($statustr . "CHARMED|");
					if (strpos($currentstatus, $charmstr) !== False) { //Enemy NOT struck, chance to return to normal anyway
						$savingthrow = rand(1,100);
						$resistfactor = ($enemyrow['resist_Heart'] / 2); //Enemy's Heart resistance improves the save.
						if ($savingthrow > (80 - $resistfactor)) {
							$currentstatus = str_replace($charmstr, "", $currentstatus);
							$message = $message . "$userrow[$enemystr] snaps out of its trance and is once again your enemy</br>";
							$charmed[$i] = false; //power is still contributed, but the enemy gets to attack
						}
					}
				}
				$newenemyhealth = $userrow[$healthstr] - $enemydamage; //Subtract off the damage.
				$distractedstr = ($statustr . "DISTRACTED|");
				if (strpos($currentstatus, $distractedstr) !== False) { //This enemy is distracted. Double damage!
					$message = $message . $userrow[$enemystr] . "is distracted and takes double damage from the blow!</br>";
					$newenemyhealth = $newenemyhealth - $enemydamage;
					$currentstatus = str_replace($distractedstr, "", $currentstatus); //Distraction lasts one turn.
				}
			} else $newenemyhealth = $userrow[$healthstr]; //derp.
				if ($newenemyhealth <= 0) { //Enemy defeated.
					$enemydown = True;
					$repeat = False; //Don't do it again.
					require 'includes/strife_rewards.php';
					if ($bonusboons > 0) {
						echo "You also find $bonusboons on the ground near the enemy!<br />";
						$userrow['Boondollars'] += $bonusboons;
						//aaaand... that's it. EOT query should take care of updating the boons at the end
					}
					$loyaltydrain -= 1;
					if (strpos($currentstatus, $statustr) !== false) { //this enemy has statuses, let's clean 'em up
						$statusarray = explode("|", $currentstatus);
						$p = 0;
						while (!empty($statusarray[$p])) {
							if (strpos($statusarray[$p], $statustr) !== False) {
								$removethis = $statusarray[$p] . "|";
								$currentstatus = preg_replace('/' . $removethis . '/', '', $currentstatus, 1);
							}
							$p++;
						}
					}
	if (!empty($bossdead) && empty($alreadykilledone)) { //A dungeon boss was just slain. Grant Echeladder rungs!
	      $alreadykilledone = True;
	      $loyaltydrain -= 50; //defeating a boss is a serious accomplishment and your allies will respect you for it
	      if ($userrow['Echeladder'] < 612) {
		$rungs = climbEcheladder($userrow, 5);
		echo "Defeating the boss of this dungeon has earned you $rungs rungs on your Echeladder!<br>";
		$refreshresult = mysql_query("SELECT `Gel_Viscosity`,`Health_Vial`,`Dream_Health_Vial`,`Aspect_Vial`,`Boondollars` FROM `Players` WHERE `Players`.`username` = '$username'");
		$refreshrow = mysql_fetch_array($refreshresult); //kinda wish this wasn't necessary but that megaquery be dangerous yo
		$userrow['Echeladder'] += $rungs; //Set these values so that the regular level-up code will handle them properly.
		$userrow['Gel_Viscosity'] = $refreshrow['Gel_Viscosity'];
		$userrow['Health_Vial'] = $refreshrow['Health_Vial'];
		$userrow['Dream_Health_Vial'] = $refreshrow['Dream_Health_Vial'];
		$userrow['Aspect_Vial'] = $refreshrow['Aspect_Vial'];
		$userrow['Boondollars'] = $refreshrow['Boondollars'];
	      } else {
		echo "Your defeat of the dungeon's boss would provide you with Echeladder rungs, but you have already reached the top of yours.</br>";
	      }
	      echo "</br>";
	    }
	    $healthplus = floor((rand(1,5) * $userrow[$powerstr]) / 5) * floor((rand(0,4) + floor($luck/25)) / 4);
	    $damage -= $healthplus;
	    if ($healthplus > 0) {
	      if ($userrow['dreamingstatus'] == "Prospit") { //No enemies on Prospit.
		echo "</br>A prospitian kindly offers you a refreshing glass of home-made lemonade for a job well done!</br>";
	      } else {
		echo "</br>You retrieve some health gel from the enemy.</br>";
	      }
	    }
	    $userrow[$enemystr] = ""; //Make them disappear for EOT reasons.
					} else {
	    $alldead = False; //Enemy not dead, so they can retaliate. Probably.
		$timestopstr = ($statustr . "TIMESTOP|");
		$hopelessstr = ($statustr . "HOPELESS|");
		$knockdownstr = ($statustr . "KNOCKDOWN|");
		$glitchstr = ($statustr . "GLITCHED|");
		$frozenstr = ($statustr . "FROZEN|");
		$hopelessroll = rand(1,100);
		$glitchedroll = rand(1,100);
		$frozed = false;
		if (strpos($currentstatus, $frozenstr) !== False && strpos($currentstatus, $timestopstr) === False) { //Check this here so that the enemy can still attack if it breaks out. Timestop must be cleared in an earlier round first.
			if ($enemydamage > 0) $breakchance = 50;
			else $breakchance = 20;
			$breakchance = floor($breakchance * ($userrow[$powerstr] / $userrow[$maxpowerstr])); 
			//weakened enemies have less chance of breaking out; on the flip side, empowered enemies have a greater chance of breaking out
			$frozenroll = rand(1,100);
			if ($frozenroll < $breakchance) { //enemy breaks out of the ice
				$message = $message . $userrow[$enemystr] . " breaks out of the ice and attacks!</br>";
				$currentstatus = str_replace($frozenstr, "", $currentstatus);
			} else $frozed = true;
		}
		if (strpos($currentstatus, $timestopstr) !== False) { //This enemy is frozen in time.
			$message = $message . $userrow[$enemystr] . " is frozen in time!</br>";
			if ($timestopped == False) { //Enemy was not stopped by a weapon strike this round
				$currentstatus = str_replace($timestopstr, "", $currentstatus);
			} else {
				$timestopped = False;
			}
			$userrow[$healthstr] = $newenemyhealth; //Gotta update this here or it won't get done.
		} elseif ((strpos($currentstatus, $glitchstr) !== False) && $glitchedroll < 30) { //Glitching out stops the enemy from trying to get up.
			$message = $message . generateGlitchString();
			$userrow[$healthstr] = $newenemyhealth; //Gotta update this here or it won't get done.
		} elseif ($frozed == true) { //Time stopped/frozen enemies can't get up from knockdown.
			$message = $message . $userrow[$enemystr] . " remains frozen in ice and cannot move!<br />";
			$userrow[$healthstr] = $newenemyhealth; //Gotta update this here or it won't get done.
		} elseif (strpos($currentstatus, $knockdownstr) !== False) { //Time stopped/frozen enemies can't get up from knockdown.
			$message = $message . $userrow[$enemystr] . " picks itself back up.</br>";
			$currentstatus = str_replace($knockdownstr, "", $currentstatus);
			$userrow[$healthstr] = $newenemyhealth; //Gotta update this here or it won't get done.
		} elseif ((strpos($currentstatus, $hopelessstr) !== False) && $hopelessroll < 50) { //If enemy unable to attack, don't bother with this.
			$message = $message . $userrow[$enemystr] . " doesn't bother attacking this round, it wouldn't do anything anyway.</br>";
			$userrow[$healthstr] = $newenemyhealth; //Gotta update this here or it won't get done.
		} else {
			$playerdamage = floor($userrow[$powerstr] * $numbersfactor);
			$playerdefense = rand(floor($defensepower * (0.85 + ($luck * 0.003))),ceil($defensepower * 1.15));
			$playerdamage = floor($playerdamage - $playerdefense);
			$mellowstr = ($statustr . "MELLOW|");
			if (strpos($currentstatus, $mellowstr) !== False) { //This enemy is totally mellowed out.
				$playerdamage -= floor($userrow[$powerstr] * 0.1);
			}
			if (!empty($abilities[23])) { //Check for Fortune's Protection (ID 23)
				$guidance = rand(floor(1 + ($luck/10)),100);
				if ($guidance > (100 - floor($userrow['Echeladder'] / 40))) { //Every forty rungs, increase the chance by 1%. More likely to kick in than the autocrit (15% at max level)
					$message = $message . $abilities[23] . "</br>";
					$playerdamage = floor($playerdamage / 2); //Activate anticrit: halve the damage.
				}
			}
			if ($playerdamage < 0 || $userrow['invulnerability'] > 0) {
				if ($enemyrow['invulndrain'] != 0) { //Invuln drainer: Attack inflicts half damage.
					$playerdamage = floor($playerdamage / 2);
				} else {	
					$playerdamage = 0; //No healing the player with attacks, negate damage if player invulnerable.
				}
			}
			$shrunkstr = ($statustr . "SHRUNK|");
			if (strpos($currentstatus, $shrunkstr) !== False) { //This enemy is shrunk
				$playerdamage = floor($playerdamage / 1.5);
				$message = $message . "The $userrow[$enemystr] tries to hit you, but it's tiny so it doesn't do as much damage. Isn't that just precious?</br>";
			}			
			if (!empty($abilities[13]) && $playerdamage > 0) { //Spatial Warp activates. Cause some recoil. (ID 13)
				$recoil = floor($userrow[$powerstr] / 5);
				$recoil = min(($newenemyhealth - 1), $recoil);
				$newenemyhealth -= $recoil;
				$message = $message . "$abilities[13]</br>Recoil damage on $userrow[$enemystr]: $recoil</br>";
			}
			$userrow[$healthstr] = $newenemyhealth; //Update for repetition and checking end-of-turn effects.
			//Enemy isn't dead, so they can retaliate. 100 player luck forces a minimum roll.
			//Begin tracking special effects on standard damage (such as role abilities) here:
			if (!empty($abilities[2]) && $playerdamage > 0) { //Life's Bounty activates. Reduce damage. (ID 2)
				$playerdamage = floor($playerdamage * 0.85);
				$message = $message . "$abilities[2]</br>";
			} 
			if (!empty($abilities[4]) && $playerdamage > 0) { //Roll for Dissipate. (ID 4)
				echo "DEBUG: rolling for Dissipate<br />";
				$targetvalue = 100 - (1 + floor($userrow['Echeladder'] / 100) + ($userrow['Godtier'] * 6) + floor($luck/10));
				if ($targetvalue < 50) $targetvalue = 50; //Maximum 50% chance.
				$rand = rand(1,100);
				if ($rand > $targetvalue || $userrow['dissipatefocus'] == 1 || $dissipating == True) { //Ability triggers
					if ($userrow['dissipatefocus'] == 1) {
						$userrow['dissipatefocus'] = 0;
						$dissipating = True; //we want to avoid every hit.
					}
					$message = $message . "$abilities[4]</br>";
					$playerdamage = 0; //Dissipate is NOT invulnerability. Specials will strike through it.
				}
			}
			$nocapstr = "PLAYER:NOCAP|";
			if (strpos($currentstatus, $nocapstr) !== False) { //Player's massive damage cap is gone.
				$savingthrow = rand((1 + ceil($luck/5)),100); //Player gets a save every time they are struck.
				if ($savingthrow > 66) {
					$message = $message . "Your massive damage protection has returned.</br>";
					$currentstatus = str_replace($nocapstr, "", $currentstatus);
				}
			} else {
				if ($playerdamage > ($userrow['Gel_Viscosity'] / 3)) $playerdamage = floor($userrow['Gel_Viscosity'] / 3); //Massive damage safety net.
			}
			if (!empty($abilities[12]) && $playerdamage > 0) { //Battle Fury activates. Increase offense boost. (ID 12). Note that it activates AFTER the safety net.
				$offenseplus = ceil(ceil($playerdamage / 8) - min(ceil($userrow['offenseboost'] / (ceil($userrow['Echeladder'] / 75))), ceil($playerdamage / 10)));
				$offenseplus = $offenseplus * ($userrow['Godtier'] + 1); //Multiply by the "standard" non class affected godtier modifier.
				$userrow['offenseboost'] += $offenseplus;
				$message = $message . "$abilities[12]</br>";
				$message = $message . "Offense boost: $offenseplus</br>";
			}
			if ($playerdamage != 0) {
				$nodamage = False;
				if ((strpos($currentstatus, $hopelessstr) !== False)) { //Enemy has a chance to feel better about themselves
					$savingthrow = rand(1,100);
					$percent = ceil(($playerdamage / $userrow['Gel_Viscosity']) * 200);
					if ($savingthrow <= $percent) { //Higher chance the more of the player's HP they deal. Capped hits are 2/3 chance
						$message = $message . $userrow[$enemystr] . " is inspired by their latest attack and shakes off the feeling of hopelessness!</br>";
						$currentstatus = str_replace($hopelessstr, "", $currentstatus);
					}
				}
			}
			$blindstr = "PLAYER:BLIND|";
      				if (strpos($currentstatus, $blindstr) !== False) { //Player is blinded.
      					$message = $message . "Your vision has returned.</br>";
					$currentstatus = str_replace($blindstr, "", $currentstatus); //wears off after one turn
				} 
			$confusestr = "PLAYER:CONFUSE|";
      				if (strpos($currentstatus, $confusestr) !== False) { //Player is confused.
      					$savingthrow = rand(1,100);
      					if ($savingthrow + floor($luck / 2) > 75) {
      						$message = $message . "You have come back to your senses.</br>";
						$currentstatus = str_replace($confusestr, "", $currentstatus);
					}
				} 
			$lockeddown = false;
			$lockdownstr = $statustr . "LOCKDOWN";
			if (strpos($currentstatus, $lockdownstr) !== False) { //This enemy is locked down, and can't use abilities
				$statusarray = explode("|", $currentstatus);
				$p = 0;
				while (!empty($statusarray[$p])) {
					if (strpos($statusarray[$p], $lockdownstr) !== False) { //multiple instances do not add anything; essentially, applying multiple instances will reset the timer
						$currentbleed = explode(":", $statusarray[$p]);
						if (intval($currentbleed[2]) <= 0) { //lockdown has expired, or at least this instance of it
							$removethis = $statusarray[$p] . "|";
							$currentstatus = preg_replace('/' . $removethis . '/', '', $currentstatus, 1);
						} else {
							$lockeddown = true; 
							$replacethis = $statusarray[$p] . "|";
							$replaceitwith = $currentbleed[0] . ":" . strval(intval($currentbleed[1]) - 1);
							$currentstatus = preg_replace('/' . $replacethis . '/', $replaceitwith, $currentstatus, 1);
						}
					}
					$p++;
				}
				if ($lockeddown == false) $message = $message . $userrow[$enemystr] . " recovers from lockdown!<br />"; //if we're here, it can only mean all instances of lockdown just wore off
				else $message = $message . $userrow[$enemystr] . " remains locked down and can't use special abilities.<br />";
			}
			$damage += $playerdamage;
			require("includes/strife_enemyabilities.php");
	  }
	}
	}
	$i++;
      }
      if ((($userrow['Health_Vial'] - $damage) <= 0 && $userrow['dreamingstatus'] == "Awake") || ($userrow['Dream_Health_Vial'] - $damage <= 0 && $userrow['dreamingstatus'] != "Awake")) { //Dead.
	$chancething = rand(1,100) - floor($luck / 12); //This will be used for all effects with a chance of preventing death. Lower is better since the "chance" has to beat this value.
	if ($chancething < 1) $chancething = 1;
	if ($userrow['motifcounter'] > 0 && $userrow['Aspect'] == "Hope") { //But not really.
	  $message = $message . "You are felled by your opponent's attack! As your exhausted body falls to the ground, a shining white light fills it. Moments later, you spring back to your feet.</br>";
	  $damage = 0;
	  $userrow[$healthvialstr] = $userrow['motifcounter'] * 500;
	  if ($userrow[$healthvialstr] > $userrow['Gel_Viscosity']) $userrow[$healthvialstr] = $userrow['Gel_Viscosity'];
	  mysql_query("UPDATE `Players` SET `" . $healthvialstr . "` = $userrow[$healthvialstr] WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  $powerboost = $userrow['motifcounter'] * 100;
	  mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$powerboost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  $userrow['powerboost'] = $userrow['powerboost'] + $powerboost;
	  mysql_query("UPDATE `Players` SET `motifcounter` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $userrow['motifcounter'] = 0;
	} elseif (!empty($abilities[20]) && ($chancething <= ceil(($userrow['Aspect_Vial'] * 100) / $userrow['Gel_Viscosity']))) { //Hope Endures activated (ID 20)
	  $endured = True;
	  $message = $message . $abilities[20] . "</br>";
	  $damage = ($userrow[$healthvialstr] - 1); //So their health goes to one.
	  $aspectcost = floor($userrow['Aspect_Vial'] / 2);
	  mysql_query("UPDATE `Players` SET `Aspect_Vial` = $userrow[Aspect_Vial]-$aspectcost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  $userrow['Aspect_Vial'] = $userrow['Aspect_Vial'] - $aspectcost;
	} elseif (!empty($endured)) { //Hope Endures has activated. The player will not die.
	  $damage = ($userrow[$healthvialstr] - 1); //So their health goes to one.
	} else {
	  $damage = ($userrow[$healthvialstr] - 1); //So their health goes to one.
	  $repeat = False; //Don't do it again.
	  if ($userrow['dreamingstatus'] == "Prospit") {
	    echo "All that civic-minded work you've been doing has really tired you out!</br>";
	  } else {
	    echo "You are felled by your opponent's attack! You slink, crawl, and generally abscond from the fight before you are killed off.</br>";
	  }
	  $userrow = terminateStrife($userrow, 1);
	  $i = $max_enemies; //Combat is over, player loses.
	  $loyaltydrain += 100;
	}
      }
      //End-of-turn effects happen here, including damage. Note that we update userrow so that the repeat function works.
      $poisonstr = "PLAYER:POISON";
      if (strpos($currentstatus, $poisonstr) !== False) { //Player is poisoned. (Format: POISON:<%chance>:<%severity>|
			$statusarray = explode("|", $currentstatus);
			$p = 0;
			$severity = 0;
			while (!empty($statusarray[$p])) {
				if (strpos($statusarray[$p], $poisonstr) !== False) { //This is a poison instance. Yes, they stack.
					$currentpoison = explode(":", $statusarray[$p]);
					$savingthrow = rand(1,100);
					if ($savingthrow + $luck > 80) { //Player throws off this instance of poisoning.
						$removethis = $statusarray[$p] . "|";
						$currentstatus = preg_replace('/' . $removethis . '/', '', $currentstatus, 1);
						$message = $message . "You manage to fight off some of the poison!</br>";
					} else {
						$severity += floatval($currentpoison[2]);
						$message = $message . "You lose some health to poison!</br>";
					}
				}
				$p++;
			}
			$recoildamage += floor(($userrow['Gel_Viscosity'] * ($severity / 100))); //poison damage counts as recoil damage so that it doesn't kill the player
		}
      if ($userrow[$healthvialstr] - $damage < $userrow['Gel_Viscosity']) {
	$newhealth = $userrow[$healthvialstr] - $damage;
	if ($newhealth - $recoildamage < 0) $newhealth = 1; //recoil shouldn't kill the player
	else $newhealth -= $recoildamage;
	$userrow[$healthvialstr] = $newhealth;
	if ($damage > 0) $loyaltydrain++; //allies lost some loyalty due to player being hit
      } else {
	$newhealth = $userrow['Gel_Viscosity'];
	$userrow[$healthvialstr] = $userrow['Gel_Viscosity'];
      }
      if (!empty($userrow['allies']) && $userrow['dreamstatus'] == "Awake") { //here, we'll explode the currentstatus to see if we have any NPC allies
   	$thisstatus = explode("|", $userrow['allies']);
   	$st = 0;
   	$npcaidmsg = "";
   	$reachedperm = false;
   	while (!empty($thisstatus[$st])) {
   		$statusarg = explode(":", $thisstatus[$st]);
   		if ($statusarg[0] == "PARTY") { //this is an ally's stats, and we only care about allies here
   			//format: <status>:<basename>:<loyalty>:<nickname>:<desc>:<power>| with the last 3 args being optional
   			$statusarg[2] -= $loyaltydrain; //subtract off the loyalty
   			if ($statusarg[2] <= 0) {
   				if (!empty($statusarg[3])) $npcname = $statusarg[3];
   				else $npcname = $statusarg[1];
   				$message = $message . $npcname . " has lost all loyalty and absconds from the strife!<br />";
   				$userrow['allies'] = preg_replace('/' . $thisstatus[$st] . '/', '', $userrow['allies'], 1);
   			} else {
   				$replaced = implode(":", $statusarg);
   				$userrow['allies'] = preg_replace('/' . $thisstatus[$st] . '/', $replaced, $userrow['allies'], 1);
   			}
   		}
   		$st++;
   	}
  }
  //echo $userrow['allies'];
      if ($userrow['invulnerability'] > 0) {
	$userrow['invulnerability'] = $userrow['invulnerability'] - 1;
      }
      if ($userrow['motifcounter'] == 0 || $userrow['Aspect'] != "Time") { //Time III prevents boosts from ticking down.
	if ($userrow['temppowerduration'] > 0) {
	  $userrow['temppowerduration']--;
	} elseif ($userrow['temppowerboost'] != 0) {
	  if ($repeat) $powerlevel = $powerlevel - $userrow['temppowerboost']; //Remove boost in the case that we're repeating.
	  $userrow['temppowerboost'] = 0; //If we're repeating, make sure this doesn't happen again.
	}
	if ($userrow['tempoffenseduration'] > 0) {
	  $userrow['tempoffenseduration']--;
	} elseif ($userrow['tempoffenseboost'] != 0) {
	  if ($repeat) $offensepower = $offensepower - $userrow['tempoffenseboost']; //Remove boost in the case that we're repeating.
	  $userrow['tempoffenseboost'] = 0; //If we're repeating, make sure this doesn't happen again.
	}
	if ($userrow['tempdefenseduration'] > 0) {
	  $userrow['tempdefenseduration']--;
	} elseif ($userrow['tempdefenseboost'] != 0) {
	  if ($repeat) $defensepower = $defensepower - $userrow['tempdefenseboost']; //Remove boost in the case that we're repeating.
	  $userrow['tempdefenseboost'] = 0; //If we're repeating, make sure this doesn't happen again.
	}
      }
      $stunstr = "PLAYER:STUN|";
      if (strpos($currentstatus, $stunstr) !== False) { //Player is stunned.
		$currentstatus = str_replace($stunstr, "", $currentstatus); //wears off after one turn
		$message = $message . "You are stunned and cannot use a consumable or aspect power next round!</br>";
		$userrow['combatconsume'] = 1;
	} else $userrow['combatconsume'] = 0;
      $sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
      $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'");
      while ($row = mysql_fetch_array($sessionmates)) {
	if ($row['aiding'] == $username) { //Aiding character. Reset their consumable use.
	  if ($row['combatconsume'] != 0) mysql_query("UPDATE `Players` SET `combatconsume` = 0 WHERE `Players`.`username` = '" . $row['username'] . "' LIMIT 1 ;");
	  //Reset combat consumable use for player aiding. Only if they have actually used a consumable.
	}
      }
      //Begin checking passive abilities that trigger at end of turn here.
      if (!empty($abilities[11]) && ($userrow['powerboost'] < 0 || $userrow['offenseboost'] < 0 || $userrow['defenseboost'] < 0 || $userrow['temppowerboost'] < 0 || $userrow['tempoffenseboost'] < 0 || $userrow['tempdefenseboost'] < 0)) { //There's a boost below zero. Trigger Blockhead (ID 11)
	$message = $message . "$abilities[11]</br>";
	$boosttypes = array(0 => "powerboost", "offenseboost", "defenseboost", "temppowerboost", "tempoffenseboost", "tempdefenseboost");
	$type = 0;
	while ($type < count($boosttypes)) {
	  $boost = $boosttypes[$type];
	  if ($userrow[$boost] < 0) {
	    $userrow[$boost] += floor($userrow['Echeladder'] / 2);
	    if ($userrow[$boost] > 0) $userrow[$boost] = 0;
	  }
	  $type++;
	}
      }
      //Finish checking passive abilities that trigger at end of turn here.
	require_once("includes/strife_t3motifs.php");
      //End-of-turn effects for player end here. Check end of turn on monsters here.

	$i = 1;
	while ($i <= $max_enemies) {
	  $enemystr = "enemy" . strval($i) . "name";
	  $enemyrowexists = False;
	  if ($userrow[$enemystr] != "") { //Enemy still exists after this round of combat.
	    $healthstr = "enemy" . strval($i) . "health";
	    $maxhealthstr = "enemy" . strval($i) . "maxhealth";
	    $powerstr = "enemy" . strval($i) . "power";
	    $maxpowerstr = "enemy" . strval($i) . "maxpower";
		$descstr = "enemy" . strval($i) . "desc";
		$statustr = "ENEMY" . strval($i) . ":";
		if (empty($_SESSION[$userrow[$enemystr]])) {
			$enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE '" . $userrow[$enemystr] . "' LIKE CONCAT ('%', `Enemy_Types`.`basename`, '%')");
			while ($temprow = mysql_fetch_array($enemyresult)) { //Enemy showed up in the table.
				if ($enemyrowexists) { //if the query yielded multiple results...
					if (strlen($temprow['basename']) > strlen($enemyrow['basename'])) { //see if the new result has a longer name than the previous
						$enemyrow = $temprow; //if it does, this is the one we want
						//this should prevent mismatches like Lich Queen using the entry for Lich, or Imp King using the entry for Imp
					}
				} else {
					$enemyrowexists = True;
					$enemyrow = $temprow;
				}
			}
			if ($enemyrowexists) {
				$_SESSION[$userrow[$enemystr]] = $enemyrow;
			}
		} else {
			$enemyrow = $_SESSION[$userrow[$enemystr]];
			$enemyrowexists = True;
		}
		if ($enemyrowexists == True) {
			$poisonstr = ($statustr . "POISON");
			$waterygelstr = ($statustr . "WATERYGEL|");
			$unluckystr = ($statustr . "UNLUCKY|");
			$bleedingstr = ($statustr . "BLEEDING");
			$disorientedstr = ($statustr . "DISORIENTED");
			$burningstr = ($statustr . "BURNING|");
		if (strpos($currentstatus, $poisonstr) !== False) { //This enemy is poisoned. (Format: POISON:<%chance>:<%severity>|
			$statusarray = explode("|", $currentstatus);
			$p = 0;
			$severity = 0;
			while (!empty($statusarray[$p])) {
				if (strpos($statusarray[$p], $poisonstr) !== False) { //This is a poison instance. Yes, they stack.
					$currentpoison = explode(":", $statusarray[$p]);
					$savingthrow = rand(1,100);
					if ($savingthrow + $enemyrow['resist_Doom'] > 100) { //Enemy throws off this instance of poisoning
						$removethis = $statusarray[$p] . "|";
						$currentstatus = preg_replace('/' . $removethis . '/', '', $currentstatus, 1);
						$message = $message . $userrow[$enemystr] . " fights off some of the poison!</br>";
					} else {
						$severity += floatval($currentpoison[2]);
						$message = $message . $userrow[$enemystr] . " loses some health to poison!</br>";
					}
				}
				$p++;
			}
			$newhealth = floor($userrow[$healthstr] - ($userrow[$maxhealthstr] * ($severity / 100)));
			if ($newhealth < 1) $newhealth = 1;
			$userrow[$healthstr] = $newhealth;
		}
		if (strpos($currentstatus, $burningstr) !== False) { //This enemy is burned. (Format: BURNING:<damage>| where damage is a static number)
			$statusarray = explode("|", $currentstatus);
			$p = 0;
			$firedamage = 0;
			while (!empty($statusarray[$p])) {
				if (strpos($statusarray[$p], $burningstr) !== False) { //This is a burn instance. Yes, they stack.
					$currentburn = explode(":", $statusarray[$p]);
					$savingthrow = rand(1,100);
					if ($savingthrow + ceil($enemyrow['resist_Rage'] / 2) > 75) { //Enemy throws off this instance of burning
						$removethis = $statusarray[$p] . "|";
						$currentstatus = preg_replace('/' . $removethis . '/', '', $currentstatus, 1);
						$message = $message . $userrow[$enemystr] . " manages to put out some of the fire!</br>";
					} else {
						$firedamage += intval($currentburn[2]);
						$message = $message . $userrow[$enemystr] . " is hurt by the fire!</br>";
					}
				}
				$p++;
			}
			$distractroll = rand(1,100);
			if ($distractroll + $enemyrow['resist_Mind'] < ($firedamage / $userrow[$maxhealthstr])) { 
				//chance to distract increases with the percentage of max health the fire is doing in damage, decreases with mind resistance
				$message = $message . "The fire sends " . $userrow[$enemystr] . " into a brief moment of panic!</br>";
				$currentstatus .= $statustr . "DISTRACTED|";
			}
			$newhealth = $userrow[$healthstr] - $firedamage;
			if ($newhealth < 1) $newhealth = 1;
			$userrow[$healthstr] = $newhealth;
		}
		if (strpos($currentstatus, $bleedingstr) !== False) { //This enemy is bleeding. Drain off some health and power.
			$statusarray = explode("|", $currentstatus);
			$p = 0;
			$instances = 0;
			while (!empty($statusarray[$p])) {
				if (strpos($statusarray[$p], $bleedingstr) !== False) { //This is a bleed instance. Yes, they stack.
					$currentbleed = explode(":", $statusarray[$p]);				
					$message = $message . $userrow[$enemystr] . " loses some blood or blood analogue!</br>";
					if (intval($currentbleed[2]) <= 0) { //Bleeding has expired.
						$removethis = $statusarray[$p] . "|";
						$currentstatus = preg_replace('/' . $removethis . '/', '', $currentstatus, 1);
						$message = $message . "One of " . $userrow[$enemystr] . "'s wounds is no longer bleeding!</br>";
					} else {
						$instances++;
						$replacethis = $statusarray[$p] . "|";
						$replaceitwith = $currentbleed[0] . ":" . $currentbleed[1] . ":" . strval(intval($currentbleed[2]) - 1);
						$currentstatus = preg_replace('/' . $replacethis . '/', $replaceitwith, $currentstatus, 1);
					}
				}
				$p++;
			}
			//Bleed off 1% of their max health and power per instance every round
			$newhealth = floor($userrow[$healthstr] - ($userrow[$maxhealthstr] * ($instances / 100)));
			$newpower = floor($userrow[$powerstr] - ($userrow[$maxpowerstr] * ($instances / 100)));
			if ($newhealth < 1) $newhealth = 1;
			if ($newpower < 1) $newpower = 1;
			$userrow[$healthstr] = $newhealth;
			$userrow[$powerstr] = $newpower;
		}
		if (strpos($currentstatus, $disorientedstr) !== False) { //This enemy is disoriented (only effect is tickdown here)
			$statusarray = explode("|", $currentstatus);
			$p = 0;
			$stilldisoriented = False;
			while (!empty($statusarray[$p])) {
				if (strpos($statusarray[$p], $disorientedstr) !== False) { //This is a disorientation instance. (Note that the longest duration one effectively applies)
					$currentdisoriented = explode(":", $statusarray[$p]);
					if (intval($currentdisoriented[2]) <= 0) { //Disorientation has expired.
						$removethis = $statusarray[$p] . "|";
						$currentstatus = preg_replace('/' . $removethis . '/', '', $currentstatus, 1);
					} else {
						$stilldisoriented = True;
					}
					if (!$stilldisoriented) {
						$message = $message . $userrow[$enemystr] . " appears to focus on the battle again!</br>";
					}
				}
				$p++;
			}
		}
		if (strpos($currentstatus, $unluckystr) !== False) { //This enemy is unlucky.
			$misfortune = rand(1,100);
			$newhealth = $userrow[$healthstr]; //Set defaults here.
			$newpower = $userrow[$powerstr];
			if ($misfortune != 1) $misfortune = floor($misfortune - ($luck / 10));
			if ($misfortune < 1) $misfortune = 2; //Only a NATURAL 1 can cause the pinata effect. Note that a nonzero luck value does give a second "natural 1" number
			if ($misfortune == 1) { //Natural 1. Enemy turns into a pinata, no resistances. Whee!
				$message = $message . "The " . $userrow[$enemystr] . " spontaneously winks out of existence. How unfortunate!</br>";
				$newdesc = 'It is a small paper replica of a ' . $userrow[$enemystr] . ' with a note pinned to it that says "Pinata. Enjoy! -The Management"';
				$newhealth = 1;
				$newpower = 0;
				$userrow[$descstr] = $newdesc;
			} elseif ($misfortune <= 2) { //Two. Chance of this goes up greatly with more luck.
				$message = $message . "The $userrow[$enemystr] is struck by lightning!</br>";
				$damage = $userrow['Echeladder'] * 250;
				$newhealth = floor($userrow[$healthstr] - $damage);
				if ($newhealth < 1) $newhealth = 1;
			} elseif ($misfortune <= 10) {
				$message = $message . "The ground gives way beneath $userrow[$enemystr] and it plummets, shaking it up pretty bad when it lands.</br>";
				$damage = $userrow['Echeladder'] * 100;
				$newhealth = floor($userrow[$healthstr] - $damage);
				if ($newhealth < 1) $newhealth = 1;
			} elseif ($misfortune <= 25) {
				$message = $message . "A meteor hits $userrow[$enemystr]. What are the chances?</br>";
				$damage = 30000;
				$newhealth = floor($userrow[$healthstr] - $damage);
				if ($newhealth < 1) $newhealth = 1;
			} elseif ($misfortune <= 50) {
				$message = $message . "$userrow[$enemystr] trips over and falls. Hilarious!</br>";
				$damage = floor($userrow[$healthstr] / 100);
				$powerdown = 2200;
				$newhealth = floor($userrow[$healthstr] - $damage);
				if ($newhealth < 1) $newhealth = 1;
				$newpower = floor($userrow[$powerstr] - $powerdown);
				if ($newpower < 1) $newpower = 1;
				$userrow[$healthstr] = $newhealth;
				$userrow[$powerstr] = $newpower;
			} else { //At the moment, a roll of 51 or above means the enemy has evaded misfortune for this round.
				$message = $message . "";
			}
			$userrow[$healthstr] = $newhealth;
			$userrow[$powerstr] = $newpower;
			$savingthrow = rand(1,100);
			if ($savingthrow > floor(81 + ($luck / 10))) { //Enemy throws off the debuff (Note that resistance doesn't factor here, but the player's luck does!)
				$removethis = $unluckystr . "|";
				$currentstatus = str_replace($removethis, '', $currentstatus); //Throws off all instances. We can't stack this debuff.
				$message = $message . $userrow[$enemystr] . " appears less unlucky. This concept is just as visually nebulous as the idea that it appeared unlucky in the first place.</br>";
			}
		}
		      if ($userrow['motifcounter'] == 0 || $userrow['Aspect'] != "Void") { //Void III stops positive effects the enemy generates.
				if ($enemyrow['powerrecover'] > 0 && $userrow[$powerstr] < $userrow[$maxpowerstr]) { //Enemy recovers from some power loss every turn and has lost some.
		$message = $message . $userrow[$enemystr] . " recovers " . $enemyrow['powerrecover'] . " lost power!</br>";
		$newpower = $userrow[$powerstr] + $enemyrow['powerrecover'];
		if ($newpower > $userrow[$maxpowerstr]) $newpower = $userrow[$maxpowerstr];
		$userrow[$powerstr] = $newpower;
	      }
	      if ($enemyrow['healthrecover'] > 0 && $userrow[$healthstr] < $userrow[$maxhealthstr]) { //Enemy has health regeneration.
		$message = $message . $userrow[$enemystr] . " recovers " . $enemyrow['healthrecover'] . " health!</br>";
		$newhealth = $userrow[$healthstr] + $enemyrow['healthrecover'];
		if ($newhealth > $userrow[$maxhealthstr]) $newhealth = $userrow[$maxhealthstr];
		$userrow[$healthstr] = $newhealth;
	      }
	      if ($enemyrow['boostdrain'] > 0 && ($userrow['powerboost'] > 0 || $userrow['offenseboost'] > 0 || $userrow['defenseboost'] > 0 || $userrow['temppowerboost'] > 0
						  || $userrow['tempoffenseboost'] > 0 || $userrow['tempdefenseboost'] > 0)) { //Enemy drains a certain amount of boost per turn.
		$message = $message . $userrow[$enemystr] . " causes your power boost to dwindle!</br>";
		if ($userrow['powerboost'] > 0) {
		  $newboost = $userrow['powerboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  $userrow['powerboost'] = $newboost;
		}
		if ($userrow['offenseboost'] > 0) {
		  $newboost = $userrow['offenseboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  $userrow['offenseboost'] = $newboost;
		}
		if ($userrow['defenseboost'] > 0) {
		  $newboost = $userrow['defenseboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  $userrow['defenseboost'] = $newboost;
		}
		if ($userrow['temppowerboost'] > 0) {
		  $newboost = $userrow['temppowerboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  $userrow['temppowerboost'] = $newboost;
		}
		if ($userrow['tempoffenseboost'] > 0) {
		  $newboost = $userrow['tempoffenseboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  $userrow['tempoffenseboost'] = $newboost;
		}
		if ($userrow['tempdefenseboost'] > 0) {
		  $newboost = $userrow['tempdefenseboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  $userrow['tempdefenseboost'] = $newboost;
		}
	      }
	      if ($enemyrow['invulndrain'] != 0 && $userrow['invulnerability'] > 0) { //Paranoia: Negate even if 1. (In case changes occur later)
		$message = $message . $userrow[$enemystr] . " negates your invulnerability!</br>";
		$userrow['invulnerability'] = 0;
	      }
	    }
	  }
	}
		  $i++;
      }
      //End-of-turn effects...end here. We set all the modified combat values here instead of updating them when they change.
	  //This is done by pulling the user's row and checking for differences.
	  //Strife status string updated here. Special case because of how I originally did it, this will probably be fixed in future.
	  if (substr($currentstatus, 0, 1) == "|") {
	  	$currentstatus = substr($currentstatus, 1);
	  	//echo $currentstatus;
	  }
	  if (!$alldead) {
	  	$oops = 1;
	  	$topshift = 1;
			while ($topshift <= $max_enemies) { //shift the status effect slot thingies down if enemies were removed so that the status effects still line up
				while (empty($userrow["enemy" . strval($topshift) . "name"]) && $topshift < $max_enemies) $topshift++;
				if ($oops != $topshift && !empty($userrow["enemy" . strval($topshift) . "name"])) {
					$blargh1 = "ENEMY" . strval($oops);
					$blargh2 = "ENEMY" . strval($topshift);
					$currentstatus = str_replace($blargh2, $blargh1, $currentstatus); //pretty sure this is necessary. yep.
				}
				$oops++;
				$topshift++;
			}
	  }
	  if (strpos($currentstatus, "PERMS|") !== false) {
	  	$splitstatus = explode("PERMS|", $currentstatus); //and THIS is why we put in the PERMS| thing
	  	$userrow['strifestatus'] = $splitstatus[1]; //moving perms to the front because new statuses are added to currentstatus at the end
	  } else {
	  	$userrow['strifestatus'] = $currentstatus;
	  }
	  $oldresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$username' LIMIT 1;");
	  $colresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$username' LIMIT 1;");
	  $oldrow = mysql_fetch_array($oldresult);
	  $megaquery = "UPDATE `Players` SET `strifestatus` = '" . mysql_real_escape_string($userrow['strifestatus']) . "'";
	  while ($column = mysql_fetch_field($colresult)) {
		if (($userrow[$column->name] != $oldrow[$column->name]) && !strpos($column->name,"inv") && !strpos($column->name,"abstratus") && $column->name != "strifestatus") {
			//This entry has been changed, and is not an item or abstratus. (Addition of items and abstrati is handled via separate functions,
			//and we don't want to interfere with those)
			if (is_numeric($userrow[$column->name])) { //Item is a number. Convert to string.
				$newvalue = strval($userrow[$column->name]);
			} else { //Not a number. Place quotes around it.
				$newvalue = "'" . mysql_real_escape_string($userrow[$column->name]) . "'";
			}
			$megaquery = $megaquery . ", `" . $column->name . "` = " . $newvalue;
			//This will produce a megaquery that does all these changes at once in the future.
	    }		
	  }
	  $megaquery = $megaquery . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;";
	  mysql_query($megaquery);
	  writeEnemydata($userrow);
      if ($once) $once = False; //Don't run through more than once unless repeat is true.
      if ($nodamage == True) $nodamagecounter++;
      if ($nodamagecounter >= 5) $repeat = False; //No damage for five turns, stop repeating.
      if ($turncounter >= 20) $repeat = False; //20 turns, stop repeating.
    }
    if (!$dontcheckvictory) { //something happened mid-calculation that ended strife, such as TH's "deny invuln" thing
    if ($alldead) { //They're dead, Dave.
      $sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
      $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'");
      while ($row = mysql_fetch_array($sessionmates)) {
				if ($row['aiding'] == $username) { //Aiding character.
	  			$aidrow = $row;
	  			echo "</br>Victory! $aidrow[username] gains another Echeladder rung.";
	  			$rungs = climbEcheladder($aidrow, 1);
	  			if ($rungs == 0) {
	    			echo " Or at least they would, if they weren't already maxed out.";
	  			}
	  			mysql_query("UPDATE `Players` SET `aiding` = '' WHERE `Players`.`username` = '" . $row['username'] . "' LIMIT 1 ;"); //Player no longer assisting.
				}
      }
      echo "</br>Victory! You climb another rung on your Echeladder.";
      $rungs = climbEcheladder($userrow, 1);
    	if ($rungs == 0) {
				echo " Or at least you would, if you weren't already at the top of it!";
				if (!empty($abilities[19]) && $userrow['Luck'] < 20) { //Light's Favour catchup activates. This is to set the luck of players at 612 who have insufficient quantities.
	  			echo "</br>Your Luck catches up with you!";
	  			mysql_query("UPDATE `Players` SET `Luck` = 20 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
				}
      }
      $userrow = terminateStrife($userrow, 0);
    } else {
    	$userrow = refreshEnemydata($userrow);
      include("strife.php");
      if ($userrow[$downstr] != 1 && $enemydown != True) { //Print this if neither player nor enemy slain
	mysql_query("UPDATE `Players` SET `strifemessage` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //This is apparently necessary for some inscrutable reason.
	mysql_query("UPDATE `Players` SET `strifemessage` = '" . mysql_real_escape_string($message) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //APOSTROPHEEEEEEEEES
      } else {
	mysql_query("UPDATE `Players` SET `strifemessage` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //This is apparently necessary for some inscrutable reason.
	mysql_query("UPDATE `Players` SET `strifemessage` = '" . mysql_real_escape_string($message) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //APOSTROPHEEEEEEEEES
	echo "</br>" . $message;
      }
    }
    }
  }
}
require_once("footer.php");
?>