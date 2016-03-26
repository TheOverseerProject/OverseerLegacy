<?php
function logDebugMessage($debugmsg) { //putting this in the header so that it can quickly be added to any page
	$time = time();
	$debugmsg = mysql_real_escape_string("($time)" . $debugmsg);
	mysql_query("UPDATE `System` SET `debuglog` = CONCAT(`debuglog`,'<br />$debugmsg') WHERE 1");
}

function logModMessage($debugmsg, $id) { //putting this in the header so that it can quickly be added to any page
	$time = time();
	if ($id != 0) {
		$debugmsg = "<a href='http://www.theoverseerproject.com/submissions.php?view=$id'>(ID: $id @ $time)</a> " . $debugmsg;
	} else {
		$debugmsg = "(ID: N/A @ $time) " . $debugmsg;
	}
	$debugmsg = mysql_real_escape_string($debugmsg);
	mysql_query("UPDATE `System` SET `modlog` = CONCAT(`modlog`,'<br />$debugmsg') WHERE 1");
}

function chargeEncounters($userrow, $encounters, $effectticks) {
	if ($userrow['encounters'] >= $encounters) {
		$newenc = $userrow['encounters'] -= $encounters;
		$encspent = $userrow['encountersspent'] += $encounters;
		mysql_query("UPDATE `Players` SET `encounters` = $newenc, `encountersspent` = $encspent WHERE `Players`.`username` = '" . $userrow['username'] . "'");
		if ($effectticks > 0) {
			$statusarray = explode("|", $userrow['permstatus']);
			$i = 0;
			while (!empty($statusarray[$i])) {
				$currentarray = explode(":", $statusarray[$i]);
				if ($currentarray[0] != "ALLY") { //allies are always permanent until their loyalty drops to 0, but that's handled elsewhere
					$duration = explode(".", $currentarray[0]);
					$ticks = intval($duration[1]);
					if ($ticks != -1) {
						if ($ticks > $effectticks) {
							$ticks -= $effectticks;
							$statusarray[$i] = str_replace($currentarray[0], $duration[0] . "." . strval($ticks));
							if (!empty($duration[2])) $statusarray[$i] .= "." . $duration[2]; //I doubt wearables will have durations but just in case
						} else {
							$statusarray[$i] = ""; //this effect wears off
						}
					}
				}
				$i++;
			}
			$newstatus = implode("|", $statusarray);
			$newstatus = preg_replace("/\\|{2,}/","|",$newstatus); //eliminate all blanks
			if ($newstatus == "|") $newstatus = "";
			if ($newstatus != $userrow['permstatus']) {
				mysql_query("UPDATE `Players` SET `permstatus` = '$newstatus' WHERE `Players`.`username` = '" . $userrow['username'] . "'");
			}
		}
		return true;
	} else return false; //false return means the user doesn't have enough encounters and was not charged any
}

function climbEcheladder($userrow, $rungups) {
	$rungs = 612 - $userrow['Echeladder'];
	if ($rungs > $rungups) $rungs = $rungups;
	$hpup = 0; //Paranoia: Handle weird Echeladder values.
	$rungcounter = $rungs - 1;
	$boondollars = 0;
	while ($rungcounter >= 0) {
	  $boondollars += ($userrow['Echeladder'] + $rungcounter) * 55;
	  if ($userrow['Echeladder'] + $rungcounter == 1) $hpup += 5; //First rung: +5, rungs 3, 4, and 5: +10
		if ($userrow['Echeladder'] + $rungcounter > 1 && $userrow['Echeladder'] < 5) $hpup += 10;
		if ($userrow['Echeladder'] + $rungcounter >= 5) $hpup += 15; //Most rungs: +15.
		$rungcounter--;
	}
	if ($rungs > 0) {
		mysql_query("UPDATE `Players` SET `Echeladder` = $userrow[Echeladder]+$rungs, `Boondollars` = $userrow[Boondollars]+$boondollars, `Gel_Viscosity` = $userrow[Gel_Viscosity]+$hpup, `Health_Vial` = $userrow[Health_Vial]+$hpup, `Dream_Health_Vial` = $userrow[Dream_Health_Vial]+$hpup, `Aspect_Vial` = $userrow[Aspect_Vial]+$hpup WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
		$echestr = "rung" . strval($userrow['Echeladder'] + $rungs);
		$echeresult = mysql_query("SELECT `$echestr` FROM Echeladders WHERE `Echeladders`.`username` = '" . $userrow['username'] . "'");
		$echerow = mysql_fetch_array($echeresult);
		$echestr = "rung" . strval($userrow['Echeladder'] + $rungs);
		if ($echerow[$echestr] != "") {
			if ($rungs > 1) echo "</br>You scrabble madly up your Echeladder, coming to rest on rung: $echerow[$echestr]!";
			else echo "</br>You ascend to rung: $echerow[$echestr]!";
		}
		$levelerabilities = mysql_query("SELECT * FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') 
	AND `Abilities`.`Rungreq` BETWEEN $userrow[Echeladder]+1 AND $userrow[Echeladder]+$rungs AND `Abilities`.`Godtierreq` = 0 ORDER BY `Abilities`.`Rungreq` DESC;");
		while ($levelerability = mysql_fetch_array($levelerabilities)) {
	  	echo "</br>You obtain new roletech: Lv. $levelerability[Rungreq] $levelerability[Name]!";
		}
		if ($userrow['Echeladder'] + $rungs == 612) echo "</br>You have at long last reached the top of your Echeladder!";
		echo "</br>";
		echo "Gel Viscosity: +$hpup";
		echo "!</br>Boondollars earned: $boondollars";
	}
	return $rungs; //returns the actual amount of rungs ascended. 0 probably means the player is at the top of the echeladder
}

function loadAbilities($userrow) {
	//This will register which abilities the player has in $abilities. The standard check is if (!empty($abilities[ID of ability to be checked for>]))
  $abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') 
	AND `Abilities`.`Rungreq` BETWEEN 0 AND $userrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $userrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
  $abilities = array(0 => "Null ability. No, not void.");
  while ($temp = mysql_fetch_array($abilityresult)) {
		$abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
		//as pulling the correct element out of the abilities array via the ID. Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
  }
  $currentstatus = $userrow['strifestatus'] . "|" . $userrow['permstatus'];
  $currentstatus = preg_replace("/\\|{2,}/","|",$currentstatus); //eliminate all blanks
	if (!empty($currentstatus)) { //Check for any instances of HASABILITY
    $thisstatus = explode("|", $currentstatus);
    $st = 0;
    while (!empty($thisstatus[$st])) {
    	$statusarg = explode(":", $thisstatus[$st]);
    	if ($statusarg[0] == "HASABILITY") { //This is an ability the player possesses.
				$abilityid = intval($statusarg[1]);
    		$abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`ID` = $abilityid LIMIT 1;");
				($temp = mysql_fetch_array($abilityresult));
				$abilities[$temp['ID']] = $temp['Usagestr'];
    	}
    	$st++;
    }
	}
	return $abilities;
}

function convertHybrid($workrow, $isbodygear) { //when wearable defense is calculated, it will go here if it's a hybrid (both a weapon and wearable) and cut the power down
	$bonusrow['abstain'] = $workrow['abstain'];
	$bonusrow['abjure'] = $workrow['abjure'];
	$bonusrow['accuse'] = $workrow['accuse'];
	$bonusrow['abuse'] = $workrow['abuse'];
	$bonusrow['aggrieve'] = $workrow['aggrieve'];
	$bonusrow['aggress'] = $workrow['aggress'];
	$bonusrow['assail'] = $workrow['assail'];
	$bonusrow['assault'] = $workrow['assault'];
	$hybridmod = specialArray($workrow['effects'], "HYBRIDMOD");
	$divisor = 30;
  if ($hybridmod[0] == "HYBRIDMOD") {
    $divisor = $divisor * ($hybridmod[1] / 100);
  }
	if ($isbodygear) $divisor = $divisor / 3;
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

function terminateStrife($userrow, $result) {
	//result can have 3 values: 0 is victory, 1 is defeat, 2 is abscond. The latter two will attempt to summon another aide.
	//there is a special value -1 which will eject EVERYONE from strife as if they absconded, and doesn't affect dungeon strife or exploration
	if ($result > 0) {
		$newfighter = "";
		$aides = 0;      
		$sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
		$sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'");
		$backup = $sessionmates; //Save this query for later.
		while ($row = mysql_fetch_array($sessionmates)) {
			if ($row['aiding'] == $username) { //Aiding character.
				$aides += 1;
			}
		}
		$sessionmates = $backup;
		while ($row = mysql_fetch_array($sessionmates)) {
			if ($row['aiding'] == $username) { //Aiding character.
				if ($newfighter == "" && rand(1,$aides) == 1) { //Character has been selected to be the next target.
					$newfighter = $row['username'];
				}
				$aides -= 1; //One aide removed.
			}
		}
		$sessionmates = $backup;
		while ($row = mysql_fetch_array($sessionmates)) {
			if ($row['aiding'] == $username) { //Aiding character.
				if ($row['username'] == $newfighter) { //Character needs to be given this encounter.
					mysql_query("UPDATE `Players` SET `aiding` = '' WHERE `Players`.`username` = '" . $row['username'] . "' LIMIT 1 ;");
					$p = 1;
					while ($p <= $max_enemies) {
						$aidenemystr = "enemy" . strval($p) . "name";
						$aidpowerstr = "enemy" . strval($p) . "power";
						$aidmaxpowerstr = "enemy" . strval($p) . "maxpower";
						$aidhealthstr = "enemy" . strval($p) . "health";
						$aidmaxhealthstr = "enemy" . strval($p) . "maxhealth";
						$aiddescstr = "enemy" . strval($p) . "desc"; //Need this to check for nulls.
						$aidcategorystr = "enemy" . strval($p) . "category";
						$row[$aidenemystr] = $userrow[$aidenemystr];
						$row[$aidpowerstr] = $userrow[$aidpowerstr];
						$row[$aidmaxpowerstr] = $userrow[$aidmaxpowerstr];
						$row[$aidhealthstr] = $userrow[$aidhealthstr];
						$row[$aidmaxhealthstr] = $userrow[$aidmaxhealthstr];
						$row[$aiddescstr] = $userrow[$aiddescstr];
						$row[$aidcategorystr] = $userrow[$aidcategorystr];
						writeEnemydata($row);
						$p++;
					}
				} else {
					mysql_query("UPDATE `Players` SET `aiding` = '" . $newfighter . "' WHERE `Players`.`username` = '" . $row['username'] . "' LIMIT 1 ;"); //Player assists new combatant.
				}
			}
		}
		if ($result == 2) { //player absconded/was ejected
			if (!empty($userrow['strifesuccessexplore']) && !empty($userrow['strifefailureexplore'])) { //User exploring!
				mysql_query("UPDATE `Players` SET `exploration` = '" . $userrow['strifeabscondexplore'] . "', `strifesuccessexplore` = '', `strifefailureexplore` = '', `strifeabscondexplore` = '' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
				echo ' <a href="explore.php">Continue exploring</a></br>';
			}
			if ($userrow['dungeonstrife'] == 2) { //User strifing in a dungeon
				mysql_query("UPDATE `Players` SET `dungeonstrife` = 1 WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
				echo "You flee back the way you came.</br>";
				echo "<a href='dungeons.php'>==&gt;</a></br>";
			}
			if ($userrow['dungeonstrife'] == 4) { //User fighting dungeon guardian
	    	mysql_query("UPDATE `Players` SET `dungeonstrife` = 3 WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
	    	echo "You flee from the guardian. Perhaps you should prepare a bit more before trying to enter the dungeon...</br>";
	    	echo "<a href='dungeons.php#display'>==&gt;</a></br>";
			}
			if ($userrow['dungeonstrife'] == 6) { //User strifing for a quest
		    mysql_query("UPDATE `Players` SET `dungeonstrife` = 5 WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
		    echo "You abscond, but the quest is still on for you to try again when you are better prepared...</br>";
	  	  echo "<a href='consortquests.php'>==&gt;</a></br>";
			}
		} elseif ($result == 1) { //player was defeated
			if ($userrow['dreamingstatus'] == "Awake") $downstr = "down";
			else $downstr = "dreamdown";
			mysql_query("UPDATE `Players` SET `" . $downstr . "` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  	$userrow[$downstr] = 1; //Makes messages appear.
			if (!empty($userrow['strifesuccessexplore']) && !empty($userrow['strifefailureexplore'])) { //User exploring!
	    	mysql_query("UPDATE `Players` SET `exploration` = '" . $userrow['strifefailureexplore'] . "', `strifesuccessexplore` = '', `strifefailureexplore` = '', `strifeabscondexplore` = '' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
	    	echo ' <a href="explore.php">Continue exploring</a></br>';
	  	}
			if ($userrow['dungeonstrife'] == 2) { //User strifing in a dungeon
		    mysql_query("UPDATE `Players` SET `dungeonstrife` = 1 WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
		    $userrow['dungeonstrife'] = 1;
	    	echo "You flee back the way you came.</br>";
	  	  echo "<a href='dungeons.php#display'>==&gt;</a></br>";
		  }
		  if ($userrow['dungeonstrife'] == 4) { //User fighting dungeon guardian
	    	mysql_query("UPDATE `Players` SET `dungeonstrife` = 3 WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
	  	  $userrow['dungeonstrife'] = 3;
		    echo "You flee from the guardian. Perhaps you should prepare a bit more before trying to enter the dungeon...</br>";
		    echo "<a href='dungeons.php#display'>==&gt;</a></br>";
	  	}
	  	if ($userrow['dungeonstrife'] == 6) { //User strifing for a quest
	    	mysql_query("UPDATE `Players` SET `dungeonstrife` = 5 WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
	    	$userrow['dungeonstrife'] = 5;
	    	$qresult = mysql_query("SELECT `context` FROM `Consort_Dialogue` WHERE `ID` = $userrow[currentquest]");
	    	$qrow = mysql_fetch_array($qresult);
	    	if (strpos($qrow['context'], "questrescue") !== false)
	    	echo "This quest's challenge has gotten the better of you! It looks as though you will not have a second chance, unfortunately...<br />";
	    	else
	  	  echo "You have failed the quest! You should come back and try again after you're rested up and fully prepared.</br>";
		    echo "<a href='consortquests.php'>==&gt;</a></br>";
		  }
		}
	} elseif ($result == 0) { //player is victorious!
		if (!empty($userrow['strifesuccessexplore']) && !empty($userrow['strifefailureexplore'])) { //User exploring!
			mysql_query("UPDATE `Players` SET `exploration` = '" . $userrow['strifesuccessexplore'] . "', `strifesuccessexplore` = '', `strifefailureexplore` = '', `strifeabscondexplore` = '' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
			echo ' <a href="explore.php">Continue exploring</a></br>';
    }
		if ($userrow['dungeonstrife'] == 2) { //User strifing in a dungeon
			echo "</br>You have successfully defeated the dungeon foes!</br>";
			echo "<a href='dungeons.php#display'>==&gt;</a></br>";
    } elseif ($userrow['dungeonstrife'] == 4) { //User strifing against dungeon guardian
			echo "</br>You have successfully defeated the dungeon guardian! The entrance lies before you...</br>";
			echo "<a href='dungeons.php#display'>==&gt;</a></br>";
    } elseif ($userrow['dungeonstrife'] == 6) { //User strifing for a quest
      $qresult = mysql_query("SELECT `context` FROM `Consort_Dialogue` WHERE `ID` = $userrow[currentquest]");
	    $qrow = mysql_fetch_array($qresult);
	    if (strpos($qrow['context'], "questrescue") !== false) { //whoops, you weren't supposed to kill them all!
	    	echo "<br />...however, defeating all the enemies has caused you to fail the quest!<br />";
	    	mysql_query("UPDATE `Players` SET `dungeonstrife` = 5 WHERE `Players`.`username` = '" . $userrow['username'] . "'");
	    	$userrow['dungeonstrife'] = 5;
	    } else echo "</br>You have successfully cleared the quest! You should talk to the quest giver and claim your reward.</br>";
			echo "<a href='consortquests.php'>==&gt;</a></br>";
    } else {
			echo '</br><a href="strife.php">Strife again</a></br>';
    }
	} else { //special case
		$sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
		if ($userrow['enemydata'] == "") $exstr = " AND `Players`.`aiding` = '" . $userrow['username'] . "'";
		else $exstr = " AND (`Players`.`username` = '" . $userrow['aiding'] . "' OR `Players`.`aiding` = '" . $userrow['aiding'] . "')";
		$sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'" . $exstr);
		while ($row = mysql_fetch_array($sessionmates)) {
			mysql_query("UPDATE `Players` SET `enemydata` = '', `aiding` = '' WHERE `username` = '" . $row['username'] . "'");
		}
	}
	$i = 1;
	$max_enemies = 50;
	while ($i <= $max_enemies) {
		$userrow['enemy' . strval($i) . 'name'] = "";
		$i++;
	}
	mysql_query("UPDATE `Players` SET `powerboost` = 0, `offenseboost` = 0, `defenseboost` = 0, `temppowerboost` = 0, 
	`tempoffenseboost` = 0, `tempdefenseboost` = 0, `Brief_Luck` = 0, `invulnerability` = 0, `buffstrip` = 0, `noassist` = 0, 
	`cantabscond` = 0, `motifcounter` = 0, `combatconsume` = 0, `strifestatus` = '', `enemydata` = '' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;"); //Power boosts wear off.
	$userrow['powerboost'] = 0;
  $userrow['offenseboost'] = 0;
  $userrow['defenseboost'] = 0;
  $userrow['temppowerboost'] = 0;
  $userrow['tempoffenseboost'] = 0;
  $userrow['tempdefenseboost'] = 0;
  $userrow['Brief_Luck'] = 0;
  $userrow['invulnerability'] = 0;
  $userrow['buffstrip'] = 0;
  $userrow['noassist'] = 0;
  $userrow['cantabscond'] = 0;
  $userrow['motifcounter'] = 0;
  $userrow['strifestatus'] = "";
  $userrow['sessionbossengaged'] = 0; //Just in case.
  $userrow['enemydata'] = "";
	return $userrow; //in case something changed and the megaquery tries to reverse it
}

function getRandeffect() {
	$r = rand(1,22);
	switch ($r) {
		case 1: return "TIMESTOP"; break;
		case 2: return "POISON"; break;
		case 3: return "WATERYGEL"; break;
		case 4: return "SHRUNK"; break;
		case 5: return "LOCKDOWN"; break;
		case 6: return "CHARMED"; break;
		case 7: return "LIFESTEAL"; break;
		case 8: return "SOULSTEAL"; break;
		case 9: return "MISFORTUNE"; break;
		case 10: return "BLEEDING"; break;
		case 11: return "HOPELESS"; break;
		case 12: return "DISORIENTED"; break;
		case 13: return "DISTRACTED"; break;
		case 14: return "ENRAGED"; break;
		case 15: return "MELLOW"; break;
		case 16: return "KNOCKDOWN"; break;
		case 17: return "GLITCHED"; break;
		case 18: return "GLITCHY"; break; //lol
		case 19: return "BURNING"; break;
		case 20: return "FREEZING"; break;
		case 21: return "SMITE"; break;
		case 22: return "RECOIL"; break;
		default: return "GLITCHY"; break; //bugged = bugged
	}
}

function wearableAffinity($resistances, $aspect, $effects) {
	if (strpos($effects, "AFFINITY") !== false) {
		$tag = explode("|", $effects);
		$i = 0;
		while (!empty($tag[$i])) {
			$arg = explode(":", $tag[$i]);
			if ($arg[0] == "AFFINITY") {
				$affadd = $arg[2];
				if ($arg[1] != $aspect) $affadd = floor($affadd * 0.8);
				$resistances[$arg[1]] += $affadd;
				if ($resistances[$arg[1]] > 100) $resistances[$arg[1]] = 100;
			}
			$i++;
		}
	}
	return $resistances;
}

function aspectDamage($resistances, $aspect, $damage, $resfactor = 1) {
	$resfactor *= 100;
	$damage = $damage * ($resfactor - $resistances[$aspect]) / $resfactor;
	return ceil($damage);
}
?>