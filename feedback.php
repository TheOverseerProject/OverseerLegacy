<?php
require_once("header.php");
require 'designix.php';
require_once("includes/grist_icon_parser.php");

function getBonus($b) {
	switch ($b) {
		case 0: return "aggrieve";
		case 1: return "aggress";
		case 2: return "assail";
		case 3: return "assault";
		case 4: return "abuse";
		case 5: return "accuse";
		case 6: return "abjure";
		case 7: return "abstain";
		default: return "what the hell are you smoking";
	}
}

function initGrists() {
	$result2 = mysql_query("SELECT * FROM `Captchalogue` LIMIT 1;"); //document grist types now so we don't have to do it later
  $reachgrist = False;
  $terminateloop = False;
  $totalgrists = 0;
  while (($col = mysql_fetch_field($result2)) && $terminateloop == False) {
    $gristcost = $col->name;
    $gristtype = substr($gristcost, 0, -5);
    if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
      $reachgrist = True;
    }
    if ($gristcost == "End_Of_Grists") { //Reached the end of the grists.
      $reachgrist = False;
      $terminateloop = True;
    }
    if ($reachgrist == True) {
      $gristname[$totalgrists] = $gristtype;
      $totalgrists++;
    }
  }
  return $gristname;
}

function totalGristcost($countrow, $gristname, $totalgrists) {
	$i = 0;
	$totalcost = 0;
	while ($i < $totalgrists) {
		//echo $gristname[$i] . " - " . strval($countrow[$gristname[$i] . '_Cost']) . "</br>";
		$totalcost = $totalcost + $countrow[$gristname[$i] . '_Cost'];
		$i++;
	}
	return $totalcost;
}

function heaviestBonus($workrow) {
	$bonusrow['abstain'] = $workrow['abstain'];
	$bonusrow['abjure'] = $workrow['abjure'];
	$bonusrow['accuse'] = $workrow['accuse'];
	$bonusrow['abuse'] = $workrow['abuse'];
	$bonusrow['aggrieve'] = $workrow['aggrieve'];
	$bonusrow['aggress'] = $workrow['aggress'];
	$bonusrow['assail'] = $workrow['assail'];
	$bonusrow['assault'] = $workrow['assault'];
	$bestbonus = max($bonusrow);
	if ($bestbonus == 0) return "none";
	elseif ($bonusrow['abstain'] == $bestbonus) return "abstain";
	elseif ($bonusrow['abjure'] == $bestbonus) return "abjure";
	elseif ($bonusrow['accuse'] == $bestbonus) return "accuse";
	elseif ($bonusrow['abuse'] == $bestbonus) return "abuse";
	elseif ($bonusrow['aggrieve'] == $bestbonus) return "aggrieve";
	elseif ($bonusrow['aggress'] == $bestbonus) return "aggress";
	elseif ($bonusrow['assail'] == $bestbonus) return "assail";
	elseif ($bonusrow['assault'] == $bestbonus) return "assault";
}

if (empty($_SESSION['username'])) {
  echo "Log in to suggest items or alchemical combinations.</br>";
} elseif ($userrow['modlevel'] <= -3 && ($_GET['type'] == "item" || $_GET['type'] == "itemadv")) {
	echo "You have been banned from submitting items, likely due to abuse or spam.<br />";
} else {
  echo '<!DOCTYPE html><html><head><style>urgent{color: #0000CC;}</style></head><body>';
  $sessionname = $userrow['session_name'];
	$sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$sessionname'");
	$sessionrow = mysql_fetch_array($sessionresult);
	$challenge = $sessionrow['challenge'];
	if (empty($challenge)) $challenge = 0;
  
  $feedback = $userrow['feedback'];
  if (!empty($_POST['itemsubmission'])) { //User is submitting an item.
  	$base = 0;
  	$consume = 0;
  	$loot = 0;
  	$ref = 0;
  	$editid = intval($_POST['editing']);
  	if ($editid > 0) {
  		$yourfbresult = mysql_query("SELECT * FROM Feedback WHERE `Feedback`.`ID` = $editid LIMIT 1;");
    	$fbrow = mysql_fetch_array($yourfbresult);
  	} else $editid = 0;
  	$aok = True;
  	if (empty($_POST['ignoresearch'])) {
  	if ($_POST['operate'] == "and" || $_POST['operate'] == "or") {
  		if (!empty($_POST['item1'])) {
	  		$i1name = str_replace("\\", "", $_POST['item1']);
  			$i1name = str_replace("'", "\\\\''", $i1name);
  			$item1result = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` COLLATE latin1_swedish_ci = '$i1name'");
  			$i1row = mysql_fetch_array($item1result);
  			$i1name = str_replace("\\\\''", "\\'", $i1name);
  			if ($i1row['name'] != $i1name) {
  				if (strlen($i1name) == 8) {
	  				$item1result = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '$i1name'");
  					$i1row = mysql_fetch_array($item1result);
  					if ($i1row['captchalogue_code'] != $i1name) {
  						echo "Submission error: could not find the first item in database. Make sure you have the correct name/code.</br>";
  						$aok = false;
  					}
  				} else {
  					echo "Submission error: could not find the first item in database. Make sure you have the name spelled correctly, or consider supplying that item's code instead.</br>";
  					$aok = false;
  				}
	  		}
  		} else {
  			echo "Submission error: please supply the first item's name/code.</br>";
  			$aok = false;
  		}
  		if (!empty($_POST['item2'])) {
	  		$i2name = str_replace("\\", "", $_POST['item2']);
  			$i2name = str_replace("'", "\\\\''", $i2name);
  			$item1result = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` COLLATE latin1_swedish_ci = '$i2name'");
  			$i2row = mysql_fetch_array($item1result);
  			$i2name = str_replace("\\\\''", "\\'", $i2name);
  			if ($i2row['name'] != $i2name) {
  				if (strlen($i2name) == 8) {
  					$item1result = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '$i2name'");
  					$i2row = mysql_fetch_array($item1result);
  					if ($i2row['captchalogue_code'] != $i2name) {
  						echo "Submission error: could not find the second item in database. Make sure you have the correct name/code.</br>";
  						$aok = false;
  					}
  				} else {
  					echo "Submission error: could not find the second item in database. Make sure you have the name spelled correctly, or consider supplying that item's code instead.</br>";
  					$aok = false;
  				}
	  		}
  		} else {
  			echo "Submission error: please supply the second item's name/code.</br>";
  			$aok = false;
  		}
  	} elseif (!empty($_POST['item1']) || !empty($_POST['item2'])) {
  		echo "Submission error: you haven't selected an operation for this item's recipe, but you seem to have supplied components. Please either leave the component item fields blank or choose an operation.</br>";
  		$aok = false;
  	}
  	if ($aok) { //so far so good
  		if ($_POST['operate'] == "base") {
  			$items = "Base Item";
  			$base = 1;
  		} elseif ($_POST['operate'] == "loot") {
  			$items = "Loot Only";
  			$loot = 1;
  		} elseif ($_POST['operate'] == "none") {
  			$items = "No recipe";
  		} else { //irows should exist or else we wouldn't be here
  			$i1row['power'] += $i1row[heaviestBonus($i1row)];
  			$i2row['power'] += $i2row[heaviestBonus($i2row)];
  			if ($i1row['power'] == 0 || $i2row['power'] == 0) { //one/both of them doesn't have a power so let's give an approximation based on grist cost
					$gristname = initGrists();
					$totalgrists = count($gristname);
					if ($i1row['power'] == 0) {
						$i1row['power'] = floor(sqrt(totalGristcost($i1row, $gristname, $totalgrists) * 8));
					}
					if ($i2row['power'] == 0) {
						$i2row['power'] = floor(sqrt(totalGristcost($i2row, $gristname, $totalgrists) * 8));
					}
				}
				$reccpower = ($i1row['power'] + $i2row['power']) * 1.5;
				if ($_POST['operate'] == "and") {
					$op = " && ";
					$newcode = andcombine($i1row['captchalogue_code'], $i2row['captchalogue_code']);
				}
				if ($_POST['operate'] == "or") {
					$op = " || ";
					$newcode = orcombine($i1row['captchalogue_code'], $i2row['captchalogue_code']);
				}
				if ($_POST['operate'] == "nochange") {
					if ($editid == 0) {
						echo "Submission error: Please select an operation.<br />";
						$aok = false;
					}
				} else {
					if ($newcode == $i1row['captchalogue_code'] || $newcode == $i2row['captchalogue_code']) {
						echo "Submission error: Given components do not interact; i.e. combining their codes with either operation will not yield a unique result. Please change one of the components and try again.</br>";
						$aok = false;
					}
					$items = $i1row['name'] . $op . $i2row['name'];
					if (empty($_POST['code'])) {
						echo "Note: no code was supplied, so the correct code will be provided automatically.</br>";
					} elseif ($newcode != $_POST['code']) {
						echo "Note: supplied code did not match the combination using the operation specified. The correct code will be provided automatically.</br>";
					}
				}
  			}
  		}
  	} else {
  		if ($_POST['operate'] == "and") {
				$op = " && ";
			} elseif ($_POST['operate'] == "or") {
				$op = " || ";
			} else {
				echo "Submission error: please choose an operation.</br>";
				$aok = false;
			}
			if (empty($_POST['item1']) || empty($_POST['item2'])) {
				echo "Submission error: one or both of the component fields left blank.</br>";
				$aok = false;
			}
			$items = $_POST['item1'] . $op . $_POST['item2'];
  	}
    if (empty($_POST['newitem']) && $editid != 0) $newitem = mysql_real_escape_string($fbrow['name']);
    else $newitem = mysql_real_escape_string($_POST['newitem']);
    if (empty($_POST['newdesc']) && $editid != 0) $newdesc = mysql_real_escape_string($fbrow['description']);
    else $newdesc = mysql_real_escape_string($_POST['newdesc']);
    if (empty($items) && $editid != 0) $items = mysql_real_escape_string($fbrow['recipe']);
    else $items = mysql_real_escape_string($items);
    if (empty($newcode)) {
    	if ($editid != 0) $newcode = $fbrow['code'];
    	else $newcode = $_POST['code'];
    }
    if (empty($_POST['power']) && $editid != 0) $newpower = $fbrow['power'];
    elseif (!empty($_POST['power'])) {
    	$newpower = intval($_POST['power']);
    	if ($newpower == 0) {
    		echo "Submission error: please put an exact integer into the power field, or leave it blank if you aren't sure (or if the item shouldn't have a power level).</br>";
    		$aok = False;
    	}
    } else $newpower = 0;
    if (empty($reccpower)) $reccpower = 0;
    if (empty($_POST['other']) && $editid != 0) $newother = mysql_real_escape_string($fbrow['comments']);
    else $newother = mysql_real_escape_string($_POST['other']);
    if ($newpower > 9999) {
      echo "Submission error: new item's power level cannot exceed 9999. Use additional comments to convey combat bonuses or uncertainty</br>";
      $aok = False;
      }
    if (strlen($newcode) != 8 && strlen($newcode) != 0) {
      echo "Submission error: captcha code is not exactly 8 letters, please double check it or leave it blank</br>";
      $aok = False;
      }
    if ($newitem == "") {
      echo "Submission error: please give this item a name</br>";
      $aok = False;
    } else {
      $existresult = mysql_query("SELECT `name` FROM `Captchalogue` WHERE `name` = '" . mysql_real_escape_string($newitem) . "'");
      $existrow = mysql_fetch_array($existresult);
      if ($existrow['name'] == $newitem) {
      	echo "Submission error: an item with that name ($newitem) already exists; if you're sure your submission is different enough from the existing item to warrant both of them being in the game, you must change the name<br />";
      	$aok = false;
      }
    }
    if ($newdesc == "") {
      echo "Submission error: please give this item a description, it can be as vague or as short as you want as long as we can tell what it is</br>";
      $aok = False;
      }
    if (strlen($newcode) == 8 && $aok) {
      $existresult = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $newcode . "' LIMIT 1;");
      $existrow = mysql_fetch_array($existresult);
      if ($existrow['captchalogue_code'] == $newcode) {
        echo 'Submission error: the submission\'s code refers to <a href="inventory.php?holocode=' . $newcode . '">an item that already exists</a>. Make sure you\'ve given the correct code.</br>';
	$aok = False;
	} elseif ($challenge == 1 && strrpos($sessionrow['atheneum'], $newcode) === false) {
		echo "Submission error: in Challenge Mode, you cannot submit a code that you haven't tried. Please double check the code or use a non-challenge account to suggest this.</br>";
		$aok = false;
      }
    }
    if ($newcode == "" && $challenge == 1) {
    	echo "Submission error: in Challenge Mode, you cannot submit an item without supplying the code. This is so that the item priority isn't abused.</br>";
			$aok = false;
    }
    if (strrpos($newother, "bladekind")) echo "ahahaha bladekind you so funny</br>";
    if ($_POST['advanced'] == "yes") $advanced = true;
    else $advanced = false;
		$bonuses = "";
		$grists = "";
		$abstratus = "";
		$size = "";
		if ($advanced) {
			if ($_POST['consumable'] == "yes") $consume = 1;
			if ($_POST['catalogue'] == "yes") $base = 1;
			if ($_POST['lootonly'] == "yes") $loot = 1;
			if ($_POST['refrance'] == "yes") $ref = 1;
			$i = 0;
			while ($i < 8) {
				$bname = getBonus($i);
				if (!empty($_POST[$bname]) && intval($_POST[$bname]) != 0) {
					if (intval($_POST[$bname]) > 9999 || intval($_POST[$bname]) < -9999) {
						echo "Submission error: $bname, like all bonuses, cannot be above 9999 or below -9999<br />";
						$aok = false;
					} else {
						$bonuses .= "$bname:" . strval($_POST[$bname]) . "|";
					}
				}
				$i++;
			}
			if ($bonuses == "" && $editid != 0) $bonuses = $fbrow['bonuses'];
			$gristname = initGrists();
			$totalgrists = count($gristname);
			$i = 0;
			while ($i < $totalgrists) {
				if (intval($_POST[$gristname[$i]]) != 0) {
					$grists .= $gristname[$i] . ":" . strval($_POST[$gristname[$i]]) . "|";
				}
				$i++;
			}
			if ($grists == "" && $editid != 0) $grists = $fbrow['grists'];
			if (!empty($_POST['abstratus'])) $abstratus = $_POST['abstratus'];
			elseif ($editid != 0) $abstratus = $fbrow['abstratus'];
			else $abstratus = "notaweapon";
			$size = $_POST['size'];
		}
    if ($aok) {
    	$editid = intval($_POST['editing']);
    	if ($editid == 0) {
    		$systemresult = mysql_query("SELECT * FROM System");
    		$systemrow = mysql_fetch_array($systemresult);
    		$newid = $systemrow['totalsubmissions'];
    		$currenttime = time();
      	mysql_query("INSERT INTO `Feedback` (`ID`, `user`, `type`, `name`, `code`, `recipe`, `power`, `recpower`, `description`, `comments`, `urgent`, `lastupdated`, `bonuses`, `grists`, `abstratus`, `size`, `consumable`, `catalogue`, `lootonly`, `refrance`) VALUES ($newid, '$username', 'item', '$newitem', '$newcode', '$items', " . strval($newpower) . ", " . strval($reccpower) . ", '$newdesc', '$newother', $challenge, $currenttime, '$bonuses', '$grists', '$abstratus', '$size', $consume, $base, $loot, $ref)");
      	//echo "INSERT INTO `Feedback` (`ID`, `user`, `type`, `name`, `code`, `recipe`, `power`, `recpower`, `description`, `comments`, `urgent`, `lastupdated`, `bonuses`, `grists`, `abstratus`, `size`, `consumable`, `catalogue`, `lootonly`, `refrance`) VALUES ($newid, '$username', 'item', '$newitem', '$newcode', '$items', $newpower, $reccpower, '$newdesc', '$newother', $challenge, $currenttime, '$bonuses', '$grists', '$abstratus', '$size', $consume, $base, $loot, $ref)";
      	mysql_query("UPDATE `System` SET `totalsubmissions` = " . strval($newid + 1) . " WHERE 1");
      	echo 'Item submitted! (ID: ' . strval($newid) . ') <a href="submissions.php?view=' . strval($newid) . '">You can view your suggestion here.</a></br>';
    	} else {
    		$yourfbresult = mysql_query("SELECT * FROM Feedback WHERE `Feedback`.`ID` = $editid LIMIT 1;");
    		$fbrow = mysql_fetch_array($yourfbresult);
    		if ($fbrow['ID'] == $editid) {
    			if (($fbrow['user'] == $username || $userrow['session_name'] == "Developers" || $userrow['session_name'] == "Itemods") && $fbrow['type'] == "item") {
    					$realbody = "Submission was edited by $username |";
    				$realbody = mysql_real_escape_string($fbrow['usercomments'] . $realbody);
    				$currenttime = time();
    				mysql_query("UPDATE `Feedback` SET `name` = '$newitem', `code` = '$newcode', `recipe` = '$items', `power` = '$newpower', `recpower` = '$reccpower', `description` = '$newdesc', `comments` = '$newother', `usercomments` = '$realbody', `defunct` = 0, `clarify` = 0, `greenlight` = 0, `lastupdated` = $currenttime, `bonuses` = '$bonuses', `grists` = '$grists', `abstratus` = '$abstratus', `size` = '$size', `consumable` = $consume, `catalogue` = $base, `lootonly` = $loot, `refrance` = $ref WHERE `Feedback`.`ID` = $editid LIMIT 1;");
      			echo 'Item updated! (ID: ' . strval($editid) . ') <a href="submissions.php?view=' . strval($editid) . '">You can view your suggestion here.</a></br>';
    			} else echo "Submission error: Either that's not your submission, or you tried to edit a non-item.</br>";
    		} else echo "Submission error: the submission you tried to edit no longer exists.</br>";
    	}
    }
  }
  if (!empty($_POST['newart'])) { //User is submitting art.
    $systemresult = mysql_query("SELECT * FROM System");
    $systemrow = mysql_fetch_array($systemresult);
    $newid = $systemrow['totalsubmissions'];
    $newitem = mysql_real_escape_string(str_replace(';', ':', $_POST['artitem']));
    $newdesc = mysql_real_escape_string(str_replace(';', ':', $_POST['newart']));
    $newother = mysql_real_escape_string("Reward requested: " . $_POST['reward']);
    $aok = True;
    if ($newitem == "") {
      echo "Submission error: please give the name/code of the item you are submitting art for</br>";
      $aok = False;
      }
    if ($newdesc == "") {
      echo "Submission error: please give a link to the art you are submitting</br>";
      $aok = False;
      }
    if ($aok) {
      mysql_query("INSERT INTO `Feedback` (`ID`, `user`, `type`, `name`, `description`, `comments`) VALUES ('" . $newid . "', '" . $username . "', 'art', '" . $newitem . "', '" . $newdesc . "', '" . $newother . "')");
      mysql_query("UPDATE `System` SET `totalsubmissions` = " . strval($newid + 1) . " WHERE 1");
      echo "Art submitted! (ID: " . strval($newid) . ") </br>";
    }
  }
  if (!empty($_POST['bugdesc'])) { //User is submitting a bug report.
    $systemresult = mysql_query("SELECT * FROM System");
    $systemrow = mysql_fetch_array($systemresult);
    $newid = $systemrow['totalsubmissions'];
    $newother = mysql_real_escape_string($_POST['bugdesc']);
    $aok = True;
    if ($newother == "") {
      echo "Submission error: you left the bug report blank!</br>";
      $aok = False;
      }
    if ($aok) {
    $currenttime = time();
      mysql_query("INSERT INTO `Feedback` (`ID`, `user`, `type`, `comments`, `lastupdated`) VALUES ('" . $newid . "', '" . $username . "', 'bug', '" . $newother . "', '" . strval($currenttime) . "')");
      mysql_query("UPDATE `System` SET `totalsubmissions` = " . strval($newid + 1) . " WHERE 1");
      echo "Bug report submitted! (ID: " . strval($newid) . ") </br>";
    }
  }
  if (!empty($_POST['gamefeedback'])) { //User is submitting a suggestion.
    $systemresult = mysql_query("SELECT * FROM System");
    $systemrow = mysql_fetch_array($systemresult);
    $newid = $systemrow['totalsubmissions'];
    $newother = mysql_real_escape_string($_POST['gamefeedback']);
    $aok = True;
    if ($newother == "") {
      echo "Submission error: you left the feedback blank!</br>";
      $aok = False;
      }
    if ($aok) {
    $currenttime = time();
      mysql_query("INSERT INTO `Feedback` (`ID`, `user`, `type`, `comments`, `lastupdated`) VALUES ('" . $newid . "', '" . $username . "', 'misc', '" . $newother . "', '" . strval($currenttime) . "')");
      mysql_query("UPDATE `System` SET `totalsubmissions` = " . strval($newid + 1) . " WHERE 1");
      echo "Feedback accepted! (ID: " . strval($newid) . ") </br>";
    }
  }
  if (!empty($_POST['qprompt'])) {
  	$systemresult = mysql_query("SELECT * FROM System");
    $systemrow = mysql_fetch_array($systemresult);
    $newid = $systemrow['totalsubmissions'];
    $newprompt = mysql_real_escape_string($_POST['qprompt']);
    $newreqs = mysql_real_escape_string($_POST['qreqs']);
    $newreward = mysql_real_escape_string($_POST['qreward']);
    $aok = true;
    if ($newprompt == "") {
    	echo "Submission error: The quest needs a prompt!</br>";
    	$aok = false;
    }
    if ($newreqs == "") {
    	echo "Submission error: You did not specify what must be turned in to complete the quest.</br>";
    	$aok = false;
    }
    if ($aok) {
      mysql_query("INSERT INTO `Feedback` (`ID`, `user`, `type`, `recipe`, `description`, `comments`) VALUES ('" . $newid . "', '" . $username . "', 'ques', '" . $newreward . "', '" . $newprompt . "', '" . $newreqs . "')");
      mysql_query("UPDATE `System` SET `totalsubmissions` = " . strval($newid + 1) . " WHERE 1");
      echo "Quest (suggestion) accepted! (ID: " . strval($newid) . ") </br>";
    }
  }
  //echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
  echo "Feedback Submission</br>Please try not to flood with too many requests. It does take some time to process each one.</br></br>";
  
  if ($_GET['type'] == "item") {
  echo "Object application form. Please fill out as many fields as accurately and completely as you can to facilitate speedy approval of your item.</br>";
  echo "Things to remember:</br>";
  echo "- Submitting the captcha code is optional. If the item has a recipe, the correct code will be supplied automatically.</br>";
  echo '- A "base" item (one available in the catalogue) does not need to include a captcha code or recipe unless you want one anyway.</br>';
  echo "- If the code is a combination, please make sure that you have tried both operations (&& and ||) first. Sometimes, you'll find an already existing item close to what you intended to submit.</br>";
  echo "- Likewise, once the item has been added, if the submitted recipe doesn't work, <b>try the opposite operation with the same components</b> before saying it doesn't work.</br>";
  echo '- <a href="http://the-overseer.wikia.com/wiki/Alchemy">This alchemy guide</a> may be a helpful read if you\'re uncertain about something.</br>';
  if ($challenge == 1) echo "<urgent>As a Challenge Mode player, your item submissions will be prioritized.</urgent></br>";
  /*$aotwresult = mysql_query("SELECT * FROM `System` WHERE 1 ;");
  while ($sysrow = mysql_fetch_array($aotwresult)) $aotwstring = $sysrow['abstratusoftheweek'];
  if (empty($aotwstring)) $aotwstring = "None yet. Go vote for one!";
  echo "This week's Abstratus of the Week is: $aotwstring </br>";
  echo 'Submit an item from the AotW to earn a small reward! <a href="http://overseerforums.forumotion.com/t186-abstratus-of-the-week-poll-week-1">You can vote for the next AotW and read up on further details here.</a></br>';
  echo "(The reward is usually boondollars, but if you want to request something specific, please put it in the comments section)";*/
  echo '<form action="feedback.php?type=item" method="post" id="newitem"><input type="hidden" name="itemsubmission" value="yes">Submission to edit (if any): ';
  if ($userrow['session_name'] == "Developers" || $userrow['session_name'] == "Itemods") {
  	echo '<input name="editing" type="text">';
  } else {
  	echo '<select name="editing"><option value="0">None, this is a new submission</option>';
  	$userfbresult = mysql_query("SELECT * FROM Feedback WHERE `Feedback`.`user` = '$username' AND `Feedback`.`type` = 'item' ;");
  	while ($fbrow = mysql_fetch_array($userfbresult)) {
  		echo '<option value="' . strval($fbrow['ID']) . '">' . $fbrow['name'] . " (ID " . strval($fbrow['ID']) . ")</option>";
  	}
  	echo '</select>';
  }
  echo '</br>If you are editing a submission, leave a field blank to retain its current details.';
  if (!empty($_GET['newcode'])) $nucodetext = 'value="' . $_GET['newcode'] . '"';
  else $nucodetext = " ";
  echo '</br>Suggested item code (if any): <input id="code" name="code" type="text"' . $nucodetext . '/><br />';
  echo 'First item used to make this item (code or name): <input id="item1" name="item1" type="text" /><br />';
  echo 'Second item used to make this item (code or name): <input id="item2" name="item2" type="text" /><br />';
  echo '<input type="checkbox" name="ignoresearch" value="yes"> One or both of these have not yet been added to the game</br>';
  echo 'Operation to use: <select name="operate"><option value="nochange">Select one / No change</option><option value="base">None, this is a base item</option><option value="loot">None, this is dungeon loot</option><option value="and">&&</option><option value="or">||</option></select><br />';
  echo 'New item\'s name: <input id="newitem" name="newitem" type="text" /><br />';
  echo 'New item\'s description:</br><textarea name="newdesc" rows="6" cols="40" form="newitem"></textarea><br />';
  echo 'Comments on the new item. This field is for suggestions like command bonuses, abstratus the item should have, grist to be used, etc. The more details supplied, the better:</br><a href="http://the-overseer.wikia.com/wiki/List_of_item_effects">(You can find a full list of possible item effects here.)</a><br /><textarea name="other" rows="6" cols="40" form="newitem"></textarea><br />';
  echo 'Keep in mind that submitting grist types is a good way to get your items greenlit faster. Don\'t worry about exact costs, as those can be easily supplied by the devs.</br>';
  echo 'Suggested level of power (if weapon) or defense (if wearable): <input id="power" name="power" type="text" /><br />';
  echo '<input type="submit" name="button" value="Suggest it!" /></form></br>';
  } elseif ($_GET['type'] == "art") {
  echo "Art submission form. Ensure that the name of the item the artwork is for is correct before submitting. Art should be the sort of thing that would be used as an inventory icon.</br>";
  echo "Please do not submit artwork for items that don't exist yet! Including the item's captchalogue code as well as the name is useful.</br>";
  echo "Artwork should fit well with the Homestuck art style, though it doesn't necessarily have to match it.</br>";
  echo "Art submitted earns the submitter a reward if the art is used. Please keep rewards sensible, 'ONE ZILLION OF EVERY GRIST' will earn you a random reward of my choosing instead.</br>";
  echo "Submitting art means you're agreeing to let me use it as an inventory icon for the item in question. Don't worry though, the artwork is still yours to use as you like!</br>";
  echo "All artwork should be submitted after being placed on the following empty captchalogue card (right click and choose 'Save image as...' to download it):</br>";
  echo '<img src="Images/Items/emptycard.png"></br>';
  echo '<form action="feedback.php?type=art" method="post">Item that this artwork is for: <input id="artitem" name="artitem" type="text" /><br />';
  echo 'Link to the artwork: <input id="newart" name="newart" type="text" /><br />';
  echo 'Reward requested: <input id="reward" name="reward" type="text" /><br />';
  echo '<input type="submit" value="Submit it!" /></form></br>';
  } elseif ($_GET['type'] == "bug") {
  echo 'Bug report form. Please enter as much detail about the bug encountered as possible. Wait a few minutes and attempt to reproduce before filing a bug report:</br>';
  echo 'Sometimes I create and fix bugs within the span of a couple minutes while experimenting!</br>';
  //echo 'NOTE: Most bug reports are answered on <a href="http://babbyoverseer.tumblr.com">the item dev blog</a>, so keep an eye on it!</br>';
  echo '<form action="feedback.php?type=bug" method="post" id="bugreport">Bug encountered, in as much detail as possible, with how to reproduce it if possible: </br><textarea name="bugdesc" rows="6" cols="40" form="bugreport"></textarea><br />';
  echo '<input type="submit" name="button" value="Report it!" /></form></br>';
  } elseif ($_GET['type'] == "misc") {
  echo 'Game feedback form. Keep it constructive, please: Criticism is fine, but scathing personal attacks will simply be ignored, so do not bother.</br>';
  echo 'Improvement suggestions to go along with negative feedback are always appreciated.</br>';
  //echo 'NOTE: Like bugs, most game feedback is answered on <a href="http://babbyoverseer.tumblr.com">the item dev blog</a>, so keep an eye on it!</br>';
  echo '<form action="feedback.php?type=misc" method="post" id="feedback">What you want to say about the game: </br><textarea name="gamefeedback" rows="6" cols="40" form="feedback"></textarea><br />';
  echo '<input type="submit" name="button" value="Evaluate it!" /></form></br>';
  } elseif ($_GET['type'] == "itemadv") {
  	echo "Advanced object application form. Please fill out as many fields as accurately and completely as you can to facilitate speedy approval of your item.</br>";
  echo "Things to remember:</br>";
  echo "- Submitting the captcha code is optional. If the item has a recipe, the correct code will be supplied automatically.</br>";
  echo '- A "base" item (one available in the catalogue) does not need to include a captcha code or recipe unless you want one anyway.</br>';
  echo "- If the code is a combination, please make sure that you have tried both operations (&& and ||) first. Sometimes, you'll find an already existing item close to what you intended to submit.</br>";
  echo "- Likewise, once the item has been added, if the submitted recipe doesn't work, <b>try the opposite operation with the same components</b> before saying it doesn't work.</br>";
  echo '- <a href="http://the-overseer.wikia.com/wiki/Alchemy">This alchemy guide</a> may be a helpful read if you\'re uncertain about something.</br>';
  if ($challenge == 1) echo "<urgent>As a Challenge Mode player, your item submissions will be prioritized.</urgent></br>";
  echo '<form action="feedback.php?type=itemadv" method="post" id="newitem"><input type="hidden" name="itemsubmission" value="yes"><input type="hidden" name="advanced" value="yes">Submission to edit (if any): ';
  if ($userrow['session_name'] == "Developers" || $userrow['session_name'] == "Itemods") {
  	echo '<input name="editing" type="text">';
  } else {
  	echo '<select name="editing"><option value="0">None, this is a new submission</option>';
  	$userfbresult = mysql_query("SELECT * FROM Feedback WHERE `Feedback`.`user` = '$username' AND `Feedback`.`type` = 'item' ;");
  	while ($fbrow = mysql_fetch_array($userfbresult)) {
  		echo '<option value="' . strval($fbrow['ID']) . '">' . $fbrow['name'] . " (ID " . strval($fbrow['ID']) . ")</option>";
  	}
  	echo '</select>';
  }
  echo '</br>If you are editing a submission, leave a field blank to retain its current details.';
  if (!empty($_GET['newcode'])) $nucodetext = 'value="' . $_GET['newcode'] . '"';
  else $nucodetext = " ";
  echo '</br>Suggested item code (if any): <input id="code" name="code" type="text"' . $nucodetext . '/><br />';
  echo 'First item used to make this item (code or name): <input id="item1" name="item1" type="text" /><br />';
  echo 'Second item used to make this item (code or name): <input id="item2" name="item2" type="text" /><br />';
  echo '<input type="checkbox" name="ignoresearch" value="yes"> One or both of these have not yet been added to the game</br>';
  echo 'Operation to use: <select name="operate"><option value="nochange">Select one / No change</option><option value="none">No recipe</option><option value="and">&&</option><option value="or">||</option></select><br />';
  echo 'New item\'s name: <input id="newitem" name="newitem" type="text" /><br />';
  echo '<input type="checkbox" name="consumable" value="yes" /> This item is a consumable<br /><input type="checkbox" name="catalogue" value="yes" /> This item is available from the Item Catalogue<br /><input type="checkbox" name="lootonly" value="yes" /> This item is specifically meant to drop from dungeon bosses<br /><input type="checkbox" name="refrance" value="yes" /> This item is a reference item<br />';
  echo 'New item\'s description:</br><textarea name="newdesc" rows="6" cols="40" form="newitem"></textarea><br />';
  echo 'Power: <input id="power" name="power" type="text" /><br />';
  echo 'Bonuses: (don\'t include plusses for positive bonuses, only minuses for negative ones)<br />';
  echo 'Aggrieve: <input name="aggrieve" type="text" /><br />';
  echo 'Aggress: <input name="aggress" type="text" /><br />';
  echo 'Assail: <input name="assail" type="text" /><br />';
  echo 'Assault: <input name="assault" type="text" /><br />';
  echo 'Abuse: <input name="abuse" type="text" /><br />';
  echo 'Accuse: <input name="accuse" type="text" /><br />';
  echo 'Abjure: <input name="abjure" type="text" /><br />';
  echo 'Abstain: <input name="abstain" type="text" /><br />';
  echo 'Abstratus: <input name="abstratus" type="text" /> (This is the item\'s entire abstratus/wearable designation as it would appear on the inventory page)<br />';
  echo 'Size: <select name="size"><option value="miniature">miniature (1)</option><option value="tiny">tiny (5)</option><option value="small">small (10)</option><option value="average" selected>average (20)</option><option value="large">large (40)</option><option value="huge">huge (100)</option><option value="immense">immense (250)</option><option value="ginormous">ginormous (1000)</option></select>';
  echo ' (note that large is used for two-handed weapons and headgear that covers the face; huge and above cannot be equipped and are mostly for notaweapon fluff)';
  $gristname = initGrists();
	$totalgrists = count($gristname);
	$i = 0;
	echo '<br />Grist types:<br />Note that grist costs will be autobalanced based on the given power level, so instead of costs, think of these as ratios. For every grist that you want to assign to this item, put down a number representing its weight among the total grist costs. For example: putting 2 in Build_Grist and 1 in Amber will make the item cost twice as much build as amber. If you don\'t care about ratios, just put 1\'s in the grists you want.<br />';
	while ($i < $totalgrists) {
		echo "<img src='/Images/Grist/".gristNameToImagePath($gristname[$i])."' height='15' width='15' alt = 'xcx'/>";
		echo $gristname[$i] . ': <input type="text" name="' . $gristname[$i] . '" /><br />';
		$i++;
	}
  echo 'Comments on the new item. Things such as consumable or extra item effects go here.<br /><a href="http://the-overseer.wikia.com/wiki/List_of_item_effects">(You can find a full list of possible item effects here.)</a></br><textarea name="other" rows="6" cols="40" form="newitem"></textarea><br />';
  echo '<input type="submit" name="button" value="Suggest it!" /></form></br>';
  } elseif ($_GET['type'] == "quest") {
    echo 'Consort quest suggestion form. Current quest types include single-item fetch quests, strife quests, rescue quests (reduce all enemies to 0 power), and dungeon quests. Quests can also trigger another quest on completion for quest chains.</br>';
  echo 'Item fetch quests can check for items of the following properties (and they can stack):</br>';
  echo '- The item name contains a keyword</br>
  - The item is a particular abstratus or other designation (headgear, computer, etc)</br>
  - The item has a specified kind of grist in its cost</br>
  - The item is above a specified power level (or below if negative)</br>
  - The item is (or isn\'t) a base item</br>
  - The item is (or isn\'t) a consumable</br>
  - The item is a given size (usually "average" or "small" for one-handed weapons, "large" for two-handed, and there are others as well)</br>';
  echo '<form action="feedback.php?type=quest" method="post" id="conquest">Quest prompt (spoken by the consort; be sure for fetch quests to at least drop a hint about what kind of item the consort desires):</br><textarea name="qprompt" rows="6" cols="40" form="conquest"></textarea></br>';
  echo 'Item requirements, enemies to fight, enemies to rescue, or description of dungeon:</br><textarea name="qreqs" rows="6" cols="40" form="conquest"></textarea></br>';
  echo 'Reward (can be any description of item, specific or otherwise; for example, "any base item" or "a bladekind weapon with a power of 100 or less". For dungeon quests, list what types of loot should be found in the dungeon and any specific loot the boss should drop. If this quest should trigger another quest on completion, say so here. Leave the field blank if you want the reward to be the default of boondollars.):</br><textarea name="qreward" rows="6" cols="40" form="conquest"></textarea></br>';
  echo '<input type="submit" value="Suggest it!"></form></br>';
  }
  echo '<form action="feedback.php" method="get">Select the kind of feedback you want to submit: <select name="type">';
  echo '<option value="item">Item suggestion (basic)</option><option value="itemadv">Item suggestion (advanced)</option><option value="art">Art submission</option><option value="quest">Consort quest</option><option value="bug">Bug report</option><option value="misc">Misc. game feedback</option></select>';
  echo '<input type="submit" value="Go"></form>';
}
require_once("footer.php");
?>