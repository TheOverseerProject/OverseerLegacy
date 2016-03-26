<?php
require_once("header.php");
require 'includes/chaincheck.php';
require 'includes/pricesandvaules.php';
require 'additem.php';
require 'monstermaker.php'; //for strife quests!

$max_items = 50; //number of items the player's inventory can hold

function phatLoot($userrow, $qrow, $currentrow, $realbasecost, $gaterow) {
	global $gristname, $totalgrists;
	$reward = rand(1,(100 - (($userrow['Luck'] + $userrow['Brief_Luck']) / 2))); //chance of getting an item instead of boons
	$landresult = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '" . $currentrow['grist_type'] . "'");
  $landrow = mysql_fetch_array($landresult);
  $landgate = highestGate($gaterow, $currentrow['house_build_grist']);
  $inflation = rand(-90,-50) + econonyLevel($currentrow['econony']);
  if ($inflation > 100) $inflation = 100;
  if (!empty($qrow['specialreward'])) {
  	if (strpos($qrow['specialreward'], "UNLOCK:") !== false) { //this quest unlocks a new merc type, who joins you as a reward
  		$unlockname = explode(":", $qrow['specialreward']);
  		if (!empty($currentrow['consort_name'])) {
				$mercname = str_replace("Consort", $currentrow['consort_name'], $unlockname[1]);
			} else $mercname = $unlockname[1];
  		echo "a new ally: $mercname!<br />The $mercname heads off to your house and waits patiently for your orders.<br />";
  		$newally = joinParty($userrow, $unlockname[1], $realbasecost, $currentrow['consort_name']);
  		if (strpos($currentrow['landallies'], $unlockname[1]) === false) {
  			$currentrow['landallies'] .= $unlockname[1] . "|";
  			echo "$mercname is now unlocked for hire on The Land of " . $currentrow['land1'] . " and " . $currentrow['land2'] . "!<br />";
  			mysql_query("UPDATE Players SET landallies = '" . mysql_real_escape_string($currentrow['landallies']) . "' WHERE username = '" . $currentrow['username'] . "'");
  		}
  	} else {
  	$thisgrist = $landrow['grist' . strval(rand(1,9))] . "_Cost"; //pick a random grist type from that land
  	$rewarditem = randomItem($thisgrist, floor($realbasecost / 20), $gristname, $totalgrists, $qrow['specialreward']);
  	$rewarditemcost = totalBooncost($rewarditem, $landrow, $gristname, $totalgrists, $currentrow['session_name']);
  	$basecost = $realbasecost - $rewarditemcost;
  	$basecost = ceil($basecost * (1 + ($inflation / 100)));
  	$rewardname = str_replace("\\", "", $rewarditem['name']);
  	if ($basecost <= 0) {
  		$basecost = 0;
  		$rewardnameex = $rewardname . " x1";
  		echo "$rewardnameex!</br>";
  	} else {
  		if (!empty($rewardname)) $rewardnameex = $rewardname . " x1, and";
  		echo "$rewardnameex $basecost Boondollars!</br>";
  	}
  	}
  } elseif ($reward < 10) { //10% chance normally of getting an item in return, 20% if max luck
  	$thisgrist = $landrow['grist' . strval(rand(1,9))] . "_Cost"; //pick a random grist type from that land
  	$rewarditem = randomItem($thisgrist, floor($realbasecost / 20), $gristname, $totalgrists, "");
  	$rewarditemcost = totalBooncost($rewarditem, $landrow, $gristname, $totalgrists, $currentrow['session_name']);
  	$basecost = $realbasecost - $rewarditemcost;
  	$basecost = ceil($basecost * (1 + ($inflation / 100)));
  	$rewardname = str_replace("\\", "", $rewarditem['name']);
  	if ($basecost <= 0) {
  		$basecost = 0;
  		echo "$rewardname x1!</br>";
  	}
  	else echo "$rewardname x1, and $basecost Boondollars!</br>";
  } else {
  	if ($realbasecost <= 0) {
  		$realbasecost = rand(1, $gaterow['gate' . strval($landgate)]);
  	}
  	$basecost = ceil($realbasecost * (1 + ($inflation / 100)));
  	echo "$basecost Boondollars!</br>";
  	$rewardname = "";
  }
  if (!empty($rewardname)) {
  	$rewarded = addItem($rewardname, $userrow); //player should always have inventory space because of just turning in an item unless it wasn't an item quest
  	if ($rewarded == "inv-1") {
  		$stored = storeItem($rewardname, 1, $userrow);
  		if ($stored > 0) {
  			echo "You have no room in your inventory for the item, so the consort offers to bring it to your house posthaste.<br />";
  		} else {
  			echo "...but you have no room in your inventory or storage for the item! The consort keeps it and gives you $rewarditemcost Boondollars instead.<br />";
  			$basecost += $rewarditemcost;
  		}
  	} else $userrow[$rewarded] = $rewardname;
  }
  mysql_query("UPDATE Players SET `Boondollars` = $userrow[Boondollars]+$basecost WHERE `Players`.`username` = '$userrow[username]'"); //reward player
  $userrow['Boondollars'] += $basecost;
  mysql_query("UPDATE `Players` SET `econony` = " . strval($currentrow['econony']+$realbasecost) . " WHERE `Players`.`username` = '$currentrow[username]'");
  return $userrow;
}

if (empty($_SESSION['username'])) {
  echo "Log in to go on consort quests.</br>";
} elseif ($userrow['dreamingstatus'] != "Awake") {
	echo "You won't find any quests on your dream moon.</br>";
} else {
	
	if (!empty($_POST['questapproval'])) {
		if ($_POST['questapproval'] != "no") {
			$newquest = intval($_POST['questapproval']);
			if ($newquest == $userrow['currentquest']) {
				$userrow['questland'] = $_GET['land'];
				//mysql_query("UPDATE `Players` SET `questland` = '" . $_GET['land'] . "' WHERE `Players`.`username` = '$username' ");
				//the quest was already "accepted" when it was received, this is just a formality
				echo "Quest accepted!</br>";
			} else echo "You seem to be trying to start a quest that you were never given.</br>";
		} else {
			mysql_query("UPDATE `Players` SET `currentquest` = 0, `questland` = '' WHERE `Players`.`username` = '$username' ");
			echo "You turn the poor sap down and set off to find a different quest.</br>";
			$userrow['currentquest'] = 0;
			$userrow['questland'] = "";
		}
	}
	
	$gateresult = mysql_query("SELECT * FROM Gates"); //we'll need this to determine the level of the shops
  $gaterow = mysql_fetch_array($gateresult); //Gates only has one row.
  $result2 = mysql_query("SELECT * FROM `Players` LIMIT 1;"); //document grist types now so we don't have to do it later
  $reachgrist = False;
  $terminateloop = False;
  $totalgrists = 0;
  while (($col = mysql_fetch_field($result2)) && $terminateloop == False) {
    $gristtype = $col->name;
    if ($gristtype == "Build_Grist") { //Reached the start of the grists.
      $reachgrist = True;
    }
    if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
      $reachgrist = False;
      $terminateloop = True;
    }
    if ($reachgrist == True) {
      $gristname[$totalgrists] = $gristtype;
      $totalgrists++;
    }
  }
if ($userrow['house_build_grist'] < $gaterow['gate1']) echo "You need to have access to the first gate in order to find a consort who will give you a quest.</br>";
else {
	$chain = chainArray($userrow);
	$totalchain = count($chain);
	if ($userrow['currentquest'] != 0 && !empty($userrow['questland'])) { //user is on a quest
		$currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $userrow['questland'] . "';");
	  $currentrow = mysql_fetch_array($currentresult);
	  $locationstr = "Land of " . $currentrow['land1'] . " and " . $currentrow['land2'];
	  $questresult = mysql_query("SELECT * FROM Consort_Dialogue WHERE `Consort_Dialogue`.`ID` = " . strval($userrow['currentquest']));
	  $qrow = mysql_fetch_array($questresult);
	  $qrow = parseDialogue($qrow, $userrow, $currentrow['land1'], $currentrow['land2']);
	  if (!empty($currentrow['consort_name'])) $consort = $currentrow['consort_name'];
		else $consort = "consort";
		if (!empty($_POST['turnindungeonquest'])) {
			if ($qrow['context'] == "questdungeon" || $qrow['context'] == "questdungeon+") {
				$room = strval($userrow['dungeonrow']) . "," . strval($userrow['dungeoncol']);
				$dgnresult = mysql_query("SELECT `$room` FROM `Dungeons` WHERE `Dungeons`.`username` = '" . $userrow['currentdungeon'] . "' LIMIT 1;");
				$dgnrow = mysql_fetch_array($dgnresult);
				if ((!(strpos($dgnrow[$room], "QUESTGOAL:" . strval($userrow['currentquest']) . ":") === false) || !(strpos($dgnrow[$room], "QUESTGOAL:" . strval($userrow['currentquest']) . "|") === false)) && strpos($dgnrow[$room], "ENCOUNTER|") === false) {
					//user is at the quest goal and any encounter here was taken care of
					$realbasecost = $qrow['req_power'] * 1000; //standard base cost for dungeon quests is variable; should be based on the difficulty
					$newquest = 0;
					if ($qrow['linked'] != 0) {
						$nextresult = mysql_query("SELECT * FROM `Consort_Dialogue` WHERE `ID` = $qrow[linked]");
						$nextrow = mysql_fetch_array($nextresult);
						$nextrow = parseDialogue($nextrow, $userrow, $currentrow['land1'], $currentrow['land2']);
						if (strpos($nextrow['context'], "quest") !== false) { //linked dialogue entry is another quest
							if ($userrow['availablequests'] > 0) {
								$newquest = $nextrow['ID']; //if no result found, it'll stay 0 I believe
								echo $nextrow['dialogue'];
								echo "<br />Your reward for the last quest: ";
							} else echo "The consort is impressed with your victory over the dungeon! You are rewarded with: ";
						} else {
							echo $nextrow['dialogue']; //linked entry is probably a victory message, feel free to add in more cases later
							echo "<br />Your reward for the last quest: ";
						}
					} else echo "The consort is impressed with your victory over the dungeon! You are rewarded with: ";
					if ($newquest == 0) $newquestland = "";
					else {
						$newquestland = $userrow['questland'];
						mysql_query("UPDATE `Players` SET `availablequests` = $userrow[availablequests]-1 WHERE `Players`.`username` = '$username'");
					}
					$userrow = phatLoot($userrow, $qrow, $currentrow, $realbasecost, $gaterow); //will echo rewards
					mysql_query("UPDATE `Players` SET `dungeonstrife` = 0, `currentquest` = $newquest, `questland` = '$newquestland' WHERE `Players`.`username` = '$username'");


					$KABLOOEY = explode("|", $dgnrow[$room]); 
					for ($i = 1; $i <= count($KABLOOEY); $i++) {
					  if ( !(strpos($KABLOOEY[$i], "QUESTGOAL") === false) ) {
					    unset($KABLOOEY[$i]);
					    break;
					  }
					}
					$dgnsquareminusgoal = implode("|", $KABLOOEY);
					mysql_query("UPDATE `Dungeons` SET `$room` = '$dgnsquareminusgoal' WHERE `Dungeons`.`username` = '" . $userrow['currentdungeon'] . "' LIMIT 1;");


					echo '<a href="consortquests.php">==&gt;</a>';
				} else echo "You must be on the goal square for your current quest in order to turn it in.<br />";
			} else echo "You are not on a dungeon quest.";
		} elseif (!empty($_POST['questitem'])) {
			if ($qrow['context'] != "quest" && $qrow['context'] != "quest+") {
				echo "The quest you are undertaking isn't an item fetch quest!<br />";
			} else {
			$itemsearchname = str_replace("'", "\\\\''", $userrow[$_POST['questitem']]);
			$questitemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '$itemsearchname' LIMIT 1");
			$qirow = mysql_fetch_array($questitemresult);
			$truename = str_replace("\\", "", $qirow['name']);
			if ($truename == $userrow[$_POST['questitem']]) {
				echo "The $consort appraises your $truename.</br>";
				$victory = true; //innocent until proven guilty.
				$failreason = "";
				if (strpos($_POST['questitem'], "inv") === false) { //player is trying to consume from outside their inventory!
    			$victory = false;
    			$failreason = "You don't actually have one of those, silly!";
    		}
				if (!empty($qrow['req_keyword']) && $victory) {
					$victory = false;
					//echo "Searching for keyword(s) " . $qrow['req_keyword'] . "</br>";
					$boom = explode("|", $qrow['req_keyword']);
					$boomcount = count($boom);
					$counter = 0;
					while ($counter <= $boomcount && !$victory) {
						if (strripos($qirow['name'], $boom[$counter]) !== false) $victory = true; //item matches one of the keywords
						$counter++;
					}
					if (!$victory and empty($failreason)) $failreason = "It seems that this item isn't quite what they had in mind.";
				}
				if (!empty($qrow['req_abstratus']) && $victory) {
					$victory = false;
					//echo "Searching for abstratus(i) " . $qrow['req_abstratus'] . "</br>";
					$boom = explode("|", $qrow['req_abstratus']);
					$boomcount = count($boom);
					$counter = 0;
					while ($counter <= $boomcount && !$victory) {
						if (strripos($qirow['abstratus'], $boom[$counter]) !== false) $victory = true; //item matches one of the abstrati
						$counter++;
					}
					if (!$victory and empty($failreason)) $failreason = "This isn't the right kind of item.";
				}
				if (!empty($qrow['req_grist']) && $victory) {
					$victory = false;
					//echo "Searching for grist(s) " . $qrow['req_grist'] . "</br>";
					$boom = explode("|", $qrow['req_grist']);
					$boomcount = count($boom);
					$counter = 0;
					while ($counter <= $boomcount && !$victory) {
						if ($boom[$counter] == "Artifact_Grist" && $qirow[$boom[$counter] . '_Cost'] < 0) $victory = true; //if artifact is required, checks if negative
						elseif ($qirow[$boom[$counter] . '_Cost'] > 0) $victory = true; //item costs one of the required grists
						$counter++;
					}
					if (!$victory and empty($failreason)) $failreason = "Something about the item's style is off.";
				}
				if (!empty($qrow['req_base']) && $victory) {
					//echo "Item's base status is important</br>";
					if ($qrow['req_base'] == "yes" && $qirow['catalogue'] == 0) {
						$victory = false;
						$failreason = "Perhaps they're looking for something a bit more mundane.";
					}
					if ($qrow['req_base'] == "no" && $qirow['catalogue'] == 1) {
						$victory = false;
						$failreason = "Perhaps they're looking for something a bit less mundane.";
					}
					//if (!$victory and empty($failreason)) $failreason = "It seems that this item isn't quite what they had in mind.";
				}
				if (!empty($qrow['req_consume']) && $victory) {
					//echo "Item's consumable status is important</br>";
					if ($qrow['req_consume'] == "yes" && $qirow['consumable'] == 0) {
						$victory = false;
						$failreason = "Perhaps an item is required that can be used in a different way.";
					}
					if ($qrow['req_consume'] == "no" && $qirow['consumable'] == 1) {
						$victory = false;
						$failreason = "Perhaps they're looking for something less expendable.";
					}
					//if (!$victory) echo "Failed</br>";
				}
				if ($qrow['req_power'] != 0 && $victory) {
					//echo "Item's power is important</br>";
					if ($qrow['req_power'] > 0 && $qrow['req_power'] > $qirow['power']) {
						$victory = false;
						$failreason = "This item is not strong enough for their purposes.";
					}
					if ($qrow['req_power'] < 0 && $qrow['req_power'] < $qirow['power']) {
						$victory = false;
						$failreason = "This item is not shitty enough for their purposes, apparently.";
					}
					//if (!$victory) echo "Failed</br>";
				}
				if (!empty($qrow['req_size']) && $victory) {
					//echo "Item's size is important</br>";
					if ($qrow['req_size'] != $qirow['size']) {
						$victory = false;
						$failreason = "This item isn't the right size for their purposes.";
					}
					//if (!$victory) echo "Failed</br>";
				}
				if ($victory) { //moment of truth
					$newquest = 0;
					if ($qrow['linked'] != 0) {
						$nextresult = mysql_query("SELECT * FROM `Consort_Dialogue` WHERE `ID` = $qrow[linked]");
						$nextrow = mysql_fetch_array($nextresult);
						$nextrow = parseDialogue($nextrow, $userrow, $currentrow['land1'], $currentrow['land2']);
						if (strpos($nextrow['context'], "quest") !== false) { //linked dialogue entry is another quest
							if ($userrow['availablequests'] > 0) {
								$newquest = $nextrow['ID']; //if no result found, it'll stay 0 I believe
								echo $nextrow['dialogue'];
							} else echo "The consort is overjoyed, this item is perfect!";
						} else echo $nextrow['dialogue']; //linked entry is probably a victory message, feel free to add in more cases later
					} else echo "The consort is overjoyed, this item is perfect!";
					echo "<br />";
					$reward = rand(1,(100 - (($userrow['Luck'] + $userrow['Brief_Luck']) / 2))); //chance of getting an item instead of boons
					$landresult = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '" . $currentrow['grist_type'] . "'");
  				$landrow = mysql_fetch_array($landresult);
  				$landgate = highestGate($gaterow, $currentrow['house_build_grist']);
  				$offercost = totalGristcost($qirow, $gristname, $totalgrists);
  				if ($offercost <= $gaterow['gate' . strval($landgate)]) { //see if the consort has access to wealth sufficient to pay for the item
  					$realbasecost = totalBooncost($qirow, $landrow, $gristname, $totalgrists, $currentrow['session_name']);
  					autoUnequip($userrow,"none",$_POST['questitem']);
  					mysql_query("UPDATE Players SET `$_POST[questitem]` = '' WHERE `Players`.`username` = '$username'"); //reward player and clear quest
  					$userrow[$_POST['questitem']] = "";
  					if ($newquest == 0) $newquestland = "";
						else {
							$newquestland = $userrow['questland'];
							mysql_query("UPDATE `Players` SET `availablequests` = $userrow[availablequests]-1 WHERE `Players`.`username` = '$username'");
						}
						echo "In exchange for the $truename, you are given ";
  					$userrow = phatLoot($userrow, $qrow, $currentrow, $realbasecost, $gaterow); //will echo rewards
  					compuRefresh($userrow);
  					mysql_query("UPDATE `Players` SET `currentquest` = $newquest, `questland` = '$newquestland' WHERE `Players`.`username` = '$username'");
  					echo '<a href="consortquests.php">==&gt;</a>';
  				} else echo 'Unfortunately, they don\'t seem to be capable of rewarding you with anything worth nearly as much as the offering. They insist that you keep it and try to find something more affordable.</br><a href="consortquests.php">==&gt;</a>';
				} else echo 'The consort turns it away. ' . $failreason . '</br><a href="consortquests.php">==&gt;</a>';
			} else echo 'The item you\'re trying to offer doesn\'t seem to exist!</br><a href="consortquests.php">==&gt;</a>';
			}
		} elseif (!empty($_POST['gostrife'])) {
			if ($qrow['context'] != "queststrife" && $qrow['context'] != "queststrife+" && $qrow['context'] != "questrescue" && $qrow['context'] != "questrescue+") {
				echo "The quest you are undertaking isn't a strife quest!<br />";
			} elseif (!empty($userrow['enemydata']) || !empty($userrow['aiding'])) {
				echo "You are already engaged in strife!<br />";
			} elseif ($userrow['encounters'] < 1) {
				echo "You cannot engage any more enemies as you are out of encounters.<br />";
			} else {
			if (!empty($qrow['req_grist'])) {
				$enemygrists = explode("|", $qrow['req_grist']);
				$result1 = mysql_query("SELECT `username`,`grist_type` FROM `Players` WHERE `Players`.`username` = '$userrow[questland]'");
				$prow = mysql_fetch_array($result1);
				$result2 = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '$prow[grist_type]'");
				$lrow = mysql_fetch_array($result2);
			}
			$enemynames = explode("|", $qrow['req_keyword']);
			$i = 0;
			echo "The consort leads you to the enemies:<br />";
			while (!empty($enemynames[$i])) {
				if (!empty($enemygrists[$i])) {
					$tier = 'grist' . strval($enemygrists[$i]);
					$slot = generateEnemy($userrow,$lrow['name'],$lrow[$tier],$enemynames[$i],true);
					echo $lrow[$tier] . " " . $enemynames[$i];
				} else {
					$slot = generateEnemy($userrow,"None","None",$enemynames[$i],true);
					echo $enemynames[$i];
				}
				$userrow = refreshEnemydata($userrow);
				if ($slot == -1) {
					echo " - An error occurred while generating this enemy. The devs have been notified.";
					logDebugMessage($username . " - enemy $i ($enemygrists[$i] $enemynames[$i]) for quest $qrow[ID] failed to generate");
				}
				echo "<br />";
				$i++;
			}
			$newenc = $userrow['encounters'] - 1;
			mysql_query("UPDATE `Players` SET `dungeonstrife` = 6, `encounters` = $newenc WHERE `Players`.`username` = '$username'"); 
			//using dungeonstrife because I highly doubt you'll be fighting dungeon enemies and quest enemies at the same time!
			echo '<a href="strife.php">==&gt;</a>';
			}
		} elseif ($userrow['dungeonstrife'] == 6 && empty($userrow['enemydata']) && empty($userrow['aiding'])) { //returning from strife quest victory
			$realbasecost = $qrow['req_power'] * 1000; //standard base cost for strife quests is variable; should be based on the power of the enemies involved
			$newquest = 0;
			if ($qrow['linked'] != 0) {
				$nextresult = mysql_query("SELECT * FROM `Consort_Dialogue` WHERE `ID` = $qrow[linked]");
				$nextrow = mysql_fetch_array($nextresult);
				$nextrow = parseDialogue($nextrow, $userrow, $currentrow['land1'], $currentrow['land2']);
				if (strpos($nextrow['context'], "quest") !== false) { //linked dialogue entry is another quest
					if ($userrow['availablequests'] > 0) {
						$newquest = $nextrow['ID']; //if no result found, it'll stay 0 I believe
						echo $nextrow['dialogue'];
						echo "<br />Your reward for the last quest: ";
					} else echo "For winning the strife, the consort happily rewards you with ";
				} else {
					echo $nextrow['dialogue']; //linked entry is probably a victory message, feel free to add in more cases later
					echo "<br />Your reward for the last quest: ";
				}
			} else echo "For winning the strife, the consort happily rewards you with ";
			if ($newquest == 0) $newquestland = "";
			else {
				$newquestland = $userrow['questland'];
				mysql_query("UPDATE `Players` SET `availablequests` = $userrow[availablequests]-1 WHERE `Players`.`username` = '$username'");
			}
			$userrow = phatLoot($userrow, $qrow, $currentrow, $realbasecost, $gaterow); //will echo rewards
			mysql_query("UPDATE `Players` SET `dungeonstrife` = 0, `currentquest` = $newquest, `questland` = '$newquestland' WHERE `Players`.`username` = '$username'");
			echo '<a href="consortquests.php">==&gt;</a>';
		} else {
			if (empty($qrow['dialogue'])) $questtext = "Can you get me an item please?</br>";
	  	else $questtext = $qrow['dialogue'];
			echo "You have an ongoing quest on the $locationstr.</br>";
			if ($userrow['session_name'] == "Developers" || $userrow['session_name'] == "Itemods") echo "This quest's ID is: $qrow[ID]<br />";
			echo "The $consort's request:</br>";
			echo "<b>\"" . $qrow['dialogue'] . "\"</b></br></br>";
			switch ($qrow['context']) {
			case 'quest': //item fetch quest
			case 'quest+': //the + marks quests that are linked to from other quests, prevents it from being selected as a starting quest
				echo '<form action="consortquests.php" method="post">Offer the ' . $consort  . ' an item?</br>';
				echo '<select name="questitem">';
				$itemcount = 1;
				while ($itemcount <= $max_items) {
					$itemstring = 'inv' . strval($itemcount);
					if (!empty($userrow[$itemstring])) echo '<option value="' . $itemstring . '">' . $userrow[$itemstring] . '</option>';
					$itemcount++;
				}
				echo '</select></br><input type="submit" value="Offer this item"></form></br>';
				break;
			case 'queststrife': //strife quest
			case 'queststrife+':
				if (!empty($userrow['enemydata']) || !empty($userrow['aiding'])) {
					if ($userrow['dungeonstrife'] == 6) echo "You are currently strifing with this quest's enemies! <a href='strife.php'>Go here.</a><br />";
					else echo "You should finish up your current strife before taking on this quest!<br />";
				} else {
					if ($userrow['dungeonstrife'] == 5) {
						echo "You may have failed this time, but you can always try again!<br />";
						mysql_query("UPDATE `Players` SET `dungeonstrife` = 0 WHERE `Players`.`username` = '$username'");
					}
					echo "You are tasked with defeating the following:<br />";
					if (!empty($qrow['req_grist'])) {
						$enemygrists = explode("|", $qrow['req_grist']);
						$result1 = mysql_query("SELECT `username`,`grist_type` FROM `Players` WHERE `Players`.`username` = '$userrow[questland]'");
						$prow = mysql_fetch_array($result1);
						$result2 = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '$prow[grist_type]'");
						$lrow = mysql_fetch_array($result2);
					}
					$enemynames = explode("|", $qrow['req_keyword']);
					$i = 0;
					while (!empty($enemynames[$i])) {
						if (!empty($enemygrists[$i])) {
							$tier = 'grist' . strval($enemygrists[$i]);
							echo $lrow[$tier] . " " . $enemynames[$i];
						} else {
							echo $enemynames[$i];
						}
						echo "<br />";
						$i++;
					}
					echo '<form action="consortquests.php" method="post"><input type="hidden" name="gostrife" value="gostrife" /><input type="submit" value="Engage!" /></form>';
				}
				break;
			case 'questrescue': //"rescue" quest (you have to reduce the enemy's power to 0 rather than its health)
			case 'questrescue+':
				if (!empty($userrow['enemydata']) || !empty($userrow['aiding'])) {
					if ($userrow['dungeonstrife'] == 6) echo "You are currently strifing with this quest's enemies! <a href='strife.php'>Go here.</a><br />";
					else echo "You should finish up your current strife before taking on this quest!<br />";
				} elseif ($userrow['dungeonstrife'] == 5) {
					echo "You have failed this quest.<br />";
					mysql_query("UPDATE `Players` SET `dungeonstrife` = 0, `currentquest` = 0 WHERE `Players`.`username` = '$username'");
					echo '<a href="consortquests.php">==&gt;</a>';
				} else {
					echo "You are tasked with neutralizing the following:<br />";
					if (!empty($qrow['req_grist'])) {
						$enemygrists = explode("|", $qrow['req_grist']);
						$result1 = mysql_query("SELECT `username`,`grist_type` FROM `Players` WHERE `Players`.`username` = '$userrow[questland]'");
						$prow = mysql_fetch_array($result1);
						$result2 = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '$prow[grist_type]'");
						$lrow = mysql_fetch_array($result2);
					}
					$enemynames = explode("|", $qrow['req_keyword']);
					$i = 0;
					while (!empty($enemynames[$i])) {
						if (!empty($enemygrists[$i])) {
							$tier = 'grist' . strval($enemygrists[$i]);
							echo $lrow[$tier] . " " . $enemynames[$i];
						} else {
							echo $enemynames[$i];
						}
						echo "<br />";
						$i++;
					}
					echo "Warning: You will fail this quest if you abscond or KO the enemies! To succeed, you must reduce their power level(s) to zero.<br />";
					echo '<form action="consortquests.php" method="post"><input type="hidden" name="gostrife" value="gostrife" /><input type="submit" value="Engage!" /></form>';
				}
				break;
			case 'questdungeon': //the objective is to go to a specific area in a dungeon
			case 'questdungeon+':
				if ($userrow['indungeon'] != 0) {
						echo "You're already in a dungeon.<br />";
				} else {
					echo "Your task is to brave the perils of the dungeon " . $qrow['req_keyword'] . ".<br />";
					$dgnstring = $userrow['questland'] . ":" . $qrow['gate'];
					echo '<form action="dungeons.php" method="post"><input type="hidden" name="questdungeon" value="yes" /><input type="hidden" name="newdungeon" value="' . $dgnstring . '" />';
					echo '<input type="submit" value="Enter the dungeon (Cost: 3 encounters)" /></form>';
				}
				break;
			}
			echo '<form action="consortquests.php" method="post"><input type="hidden" name="questapproval" value="no"><input type="submit" value="Give up and abandon this quest"></form>';
			echo "You have $userrow[availablequests] other quests available.</br>";
		}
	} else {
	if (empty($_GET['land'])) {
		echo "You have $userrow[availablequests] quests available.</br>";
		echo "Like encounters, quests will accumulate over time. You will gain 1 quest every 30 minutes, and can acquire up to 50 at once.</br>";
		echo "One quest is spent for every quest prompted to you, and the quest will be available until you complete it or decide to decline.</br>";
		echo "Currently, possible quests can require either delivery of a type of item hinted at by the consort, a successful strife against certain enemies, or braving a special dungeon for that quest.</br></br>";
		echo 'New quests are being added all the time, so if you have an idea <a href="feedback.php?type=quest">feel free to submit it</a>!</br></br>';
		echo '<form action="consortquests.php" method="get">Select a Land on which to seek a quest:<select name="land"> ';
		$locationstr = "Land of " . $userrow['land1'] . " and " . $userrow['land2'];
	  echo '<option value="' . $userrow['username'] . '">' . $locationstr . '</option>';
    $landcount = 1; //0 should be the user's land which we already printed
    while ($landcount < $totalchain) {
    	$currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $chain[$landcount] . "';");
	    $currentrow = mysql_fetch_array($currentresult);
	  	$locationstr = "Land of " . $currentrow['land1'] . " and " . $currentrow['land2'];
	  	echo '<option value="' . $currentrow['username'] . '">' . $locationstr . '</option>';
	  	$landcount++;
    }
    echo '</select><input type="submit" value="Quest here (Cost: 1 available quest)"></form>';
	} else {
		$aok = false;
		if ($_GET['land'] == $username) $aok = true;
		else {
			$landcount = 1;
			while ($landcount < $totalchain && !$aok) {
				if ($chain[$landcount] == $_GET['land']) $aok = true; //verify that the chosen land is accessible by the user
				$landcount++;
			}
		}
		if (!$aok) echo "You can't reach that player's land with your current gate setup!</br>";
		else { //good to go!
			$currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_GET['land'] . "';");
	    $currentrow = mysql_fetch_array($currentresult);
	    $locationstr = "Land of " . $currentrow['land1'] . " and " . $currentrow['land2'];
			//echo "Location: $locationstr</br>";
			if (!empty($currentrow['consort_name'])) $consort = $currentrow['consort_name'];
			else $consort = "consort";
		if ($userrow['availablequests'] > 0) {
			$userrow['availablequests'] -= 1;
			mysql_query("UPDATE Players SET `availablequests` = $userrow[availablequests] WHERE `Players`.`username` = '$username'");
			echo "You have $userrow[availablequests] quests available.</br>";
			$i = 1;
			$gate = 0;
			while ($i <= 7) {
				$gstring = 'gate' . strval($i);
				if ($currentrow['house_build_grist'] < $gaterow[$gstring]) {
					$gate = $i - 1; //the user is at this gate
					$i = 7; //terminate the loop
				}
				$i++;
			}
			if ($currentrow['house_build_grist'] >= $gaterow['gate7']) $gate = 7;
			echo "You search the $locationstr for a while, hoping to come across a consort village. Eventually, you are approached by a $consort with a request for you...</br>";
			$rquest = rand(1,100); //first, determine a random quest type so that each type has controlled weight, rather than basing it off of quantity of available quests
			if ($rquest <= 33) $thisquest = "quest"; //1/3 chance of fetch quest
			elseif ($rquest <= 66) $thisquest = "queststrife"; //1/3 chance of strife quest
			elseif ($rquest <= 83) $thisquest = "questrescue"; //1/6 chance of rescue quest
			elseif ($rquest <= 100) $thisquest = "questdungeon"; //1/6 chance of dungeon quest
			else $thisquest = "quest"; //technically bugged, default to fetch quest
			$qrow = getDialogue($thisquest, $userrow,  $currentrow['land1'], $currentrow['land2'], $gate);
			echo "<b>\"" . $qrow['dialogue'] . "\"</b></br></br>";
			$questid = $qrow['ID'];
			if ($userrow['session_name'] == "Developers" || $userrow['session_name'] == "Itemods") echo "This quest's ID is: $questid<br />";
			echo "Will you accept this quest?</br>";
			mysql_query("UPDATE `Players` SET `currentquest` = $questid, `questland` = '" . $_GET['land'] .  "' WHERE `Players`.`username` = '$username' "); //set this here so the player can't do weird things
			echo '<form action="consortquests.php?land=' . $_GET['land'] . '" method="post"><input type="hidden" name="questapproval" value="' . strval($questid) . '"><input type="submit" value="Accept it!"></form>';
			echo '<form action="consortquests.php?land=' . $_GET['land'] . '" method="post"><input type="hidden" name="questapproval" value="no"><input type="submit" value="Find another quest (Cost: 1 available quest)"></form></br>';
			echo '<a href="consortquests.php">Return to land selection</a>';
		} else echo "You search the $locationstr for a while, but you can't seem to find anyone in need of assistance. Try checking back in half an hour.</br>";
		}
	}
	}
}
}
require_once("footer.php");
?>