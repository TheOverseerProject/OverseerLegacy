<?php
require_once("header.php");
require_once("includes/chaincheck.php");
require_once("includes/pricesandvaules.php");
require_once("includes/effectprinter.php");

if (empty($_SESSION['username'])) {
  echo "Log in to interact with your allies.</br>";
} elseif ($userrow['dreamingstatus'] != "Awake") {
  echo "You can't communicate with your allies while asleep!";
} elseif ($userrow['enemydata'] != "" || $userrow['aiding'] != "" || $userrow['indungeon'] != 0) {
	echo "You're too busy to communicate with your allies.";
} else {
	$chain = chainArray($userrow);
	$totalchain = count($chain);
	if (!empty($_POST['land'])) { //player is attempting to hire a mercenary
		if ($userrow['availablequests'] == 0) {
			echo "You don't have any opportunities to find allies at the moment.<br />";
		} elseif ($_POST['boons'] <= 0) {
			echo "You won't be hiring anybody unless you offer at least some Boondollars!<br />";
		} elseif ($_POST['boons'] > $userrow['Boondollars']) {
		    echo "You don't have that many Boondollars!<br />";
		} else {
			$aok = false;
			$gateresult = mysql_query("SELECT * FROM Gates");
  		$gaterow = mysql_fetch_array($gateresult); //Gates only has one row.
			if ($_POST['land'] == $username) {
				$yogate = highestGate($gaterow, $userrow['house_build_grist']);
				if ($yogate >= 1 || canFly($userrow)) {
					$aok = true;
				} else {
					echo "You won't be able to find any consorts unless you can reach the first gate.<br />";
				}
			} elseif ($_POST['land'] == "Battlefield") {
				if ($userrow['battlefield_access'] != 0) {
					$aok = true;
				} else {
					echo "You can't reach the Battlefield yet.<br />";
				}
			} else {
				$i = 0;
				while ($i < $totalchain) {
					if ($chain[$i] == $_POST['land']) $aok = true; //chainarray should ensure that the player can reach this land
					$i++;
				}
				if (!$aok) echo "You can't reach that land.<br />";
			}
			if ($aok) { //preliminary checks pass, let's look for someone to hire
				$landresult = mysql_query("SELECT * FROM Players WHERE username = '" . $_POST['land'] . "'");
				$landrow = mysql_fetch_array($landresult);
				$landrow = mercRefresh($landrow); //might as well do this here
				$landname = "The Land of " . $landrow['land1'] . " and " . $landrow['land2'];
				$offer = $_POST['boons'];
				echo "You visit a village tavern on $landname and put $offer Boondollars on the table.<br />";
				$mercsresult = mysql_query("SELECT * FROM Enemy_Types WHERE appearson = 'Ally' AND minboons < $offer ORDER BY basepower ASC");
				$types = 0;
				$lowest = $offer;
				while ($row = mysql_fetch_array($mercsresult)) {
					$types++;
					$minboon[$types] = $row['minboons'];
					$allyname[$types] = $row['basename'];
					if ($minboon[$types] < $lowest && $row['maxboons'] > $offer) $lowest = $minboon[$types];
				}
				$hireval = rand($lowest, $offer);
				$i = $types;
				while ($i > 0) {
					if ($hireval > $minboon[$i] && strpos($landrow['landallies'], $allyname[$i])) {
						$hired = $allyname[$i];
						$i = 0;
					}
					$i--;
				}
				if (!empty($hired)) { //found someone willing to work for that much
					$newally = joinParty($userrow, $hired, $offer, $landrow['consort_name']);
					if (!empty($landrow['consort_name'])) {
						$mercname = str_replace("Consort", $landrow['consort_name'], $hired);
					} else $mercname = $hired;
					echo "A $mercname steps forward, eager to serve you for your offered payment. Your party grows in number.<br />";
					$userrow['allies'] .= $newally . "|";
					$userrow['Boondollars'] -= $offer;
					mysql_query("UPDATE Players SET Boondollars = $userrow[Boondollars], availablequests = $userrow[availablequests]-1 WHERE username = '$username'");
				} else {
					echo "Nobody even reacts. It seems there are no mercenaries on this land that are willing to work for that much.<br />";
				}
			}
		}
	}
	$newallystr = "";
	$partyformstr = "";
	$partyechostr = "";
	$partymems = 0;
	if (!empty($_POST['partyaction'])) {
   	$operatingon = intval(str_replace("ally", "", $_POST['partymember']));
   	$action = $_POST['partyaction'];
   	if ($action == "rename" || $action == "redesc") {
   		if (!empty($_POST['partytext'])) {
   			$acttext = $_POST['partytext'];
   			$acttext = str_replace(":", "&#58;", $acttext); // this should display as a : without breaking the string formatting.
   			$acttext = str_replace("|", "&#124;", $acttext); //why didn't I think of this before?
   			$acttext = str_replace("<", "&lt;", $acttext);
   			$acttext = str_replace(">", "&gt;", $acttext);
   			$acttext = str_replace("\\", "", $acttext); //apostrophes will get escaped later
   			$acttext = str_replace("^", "&#94;", $acttext); //also have to change all of these because allies rely on regexp and these will break it
   			$acttext = str_replace("$", "&#36;", $acttext);
   			$acttext = str_replace("(", "&#40;", $acttext);
   			$acttext = str_replace(")", "&#41;", $acttext);
   			$acttext = str_replace("[", "&#91;", $acttext);
   			$acttext = str_replace("{", "&#123;", $acttext);
   			$acttext = str_replace(".", "&#46;", $acttext); //yes even this, better safe than sorry
   			$acttext = str_replace("*", "&#42;", $acttext); //this is disasterisk
   			$acttext = str_replace("+", "&#43;", $acttext);
   			$acttext = str_replace("?", "&#63;", $acttext);
   		} else echo "Name/description can't be blank.<br />";
   	}
	} else $operatingon = 0;
	$plevel = 5; //can be increased from roletechs later
	$pulchritude = $userrow['Echeladder'] * $plevel;
	$occupied = 0;
	$speffect = "";
	$speffects = 0;
	if (!empty($userrow['allies'])) { //here, we'll explode the ally string to see if we have any NPC allies
	//format: <task>:<basename>:<loyalty>:<nickname>:<desc>:<power>| with the last 3 args being optional
   	$thisstatus = explode("|", $userrow['allies']);
   	$st = 0;
   	$npcaidmsg = "";
   	while (!empty($thisstatus[$st])) {
   		$justpickedup = false;
   		$statusarg = explode(":", $thisstatus[$st]);
   		$npcresult = mysql_query("SELECT * FROM `Enemy_Types` WHERE `Enemy_Types`.`basename` = '$statusarg[1]'");
   		$npcrow = mysql_fetch_array($npcresult);
   		if (!empty($statusarg[5])) $npcpower = intval($statusarg[5]);
   		else $npcpower = $npcrow['basepower'];
   		if (!empty($statusarg[3])) $npcname = $statusarg[3];
   		else $npcname = $npcrow['basename'];
   		$partymems++;
   		$removeme = false;
   		if ($partymems == $operatingon) {
   			switch ($action) {
   				case "rename":
   					if (!empty($acttext)) {
   						echo $npcname . " has been renamed to $acttext.<br />";
   						$statusarg[3] = $acttext;
   						$npcname = $acttext;
   					}
   					break;
   				case "redesc":
   					if (!empty($acttext)) {
   						echo $npcname . "'s description has been updated.<br />";
   						$statusarg[4] = $acttext;
   					}
   					break;
   				case "dropoff":
   					$statusarg[0] = "IDLE";
   					echo "$npcname leaves your party and stands by at your house, awaiting further instructions.<br />";
   					break;
   				case "pickup":
   					$statusarg[0] = "PARTY";
   					echo "$npcname joins your active party, ready to strife by your side!<br />";
   					$newallypower = $npcpower;
   					$justpickedup = true;
   					break;
   				case "dismiss":
   					$removeme = true;
   					echo "$npcname is released into the wild. Goodbye, $npcname!<br />";
   					break;
   			}
   		}
   		if (!$removeme) {
   			if ($npcname != $npcrow['basename']) $partyechostr .= $npcname . " - ";
   			$partyechostr .= $npcrow['basename'] . ". Power: $npcpower. Loyalty: " . strval($statusarg[2]) . "<br />";
   			if (!empty($statusarg[4])) $partyechostr .= $statusarg[4];
   			else $partyechostr .= $npcrow['description'];
   			$partyechostr .= "<br />";
   			$lolreturn = npcEffects($npcrow['spawnstatus'], $npcpower, $speffects);
   			$worth = $lolreturn[1];
   			$partyechostr .= $lolreturn[0];
   			$speffect .= $lolreturn[2];
   			$speffects = $lolreturn[3];
   			$partyechostr .= "Pulchritude required to control: $worth<br />Status: ";
  	 		if ($statusarg[0] == "PARTY") {
  	 			$occupied += $worth;
  	 			if ($justpickedup)
  	 			$partyechostr .= "!!!PENDING!!!"; //otherwise, the status won't reflect properly this pageload
  	 			else
	   			$partyechostr .= "In party.";
   			} else {
   				$partyechostr .= "Idle.";
   			}
   			$partyechostr .= "<br /><br />";
   			$partyformstr .= "<option value='ally" . strval($partymems) . "'>$npcname</option>";
   			$newallybit = implode(":", $statusarg);
   			$newallystr .= $newallybit . "|";
   		}
   		$st++;
		}
		if ($newallystr != $userrow['allies']) {
			if ($occupied > $pulchritude) { //this should only happen because of an ally you just tried to add to the party
				echo "...except you don't quite have the pulchritude to command an army of such power! You drop off the newly-added party member again before things get out of hand.<br />";
				$occupied -= $newallypower;
				$partyechostr = str_replace("!!!PENDING!!!", "Idle.", $partyechostr);
				//don't update the allystr
			} else {
				$partyechostr = str_replace("!!!PENDING!!!", "In party.", $partyechostr);
				$newallystr = mysql_real_escape_string($newallystr);
				mysql_query("UPDATE Players SET allies = '$newallystr' WHERE username = '$username'");
			}
		}
	}
	echo "Followers<br /><br />";
	echo "Party's total power / available Pulchritude: $occupied / $pulchritude<br />(The combined power level of all followers in your active party cannot exceed your Pulchritude stat.)<br /><br />";
	echo "The following allies are available for your cause:<br />";
	if ($partymems == 0) echo "None. It's just you at the moment!<br /><br />";
	else {
		$omgwhy = explode("!!!REPLACE!!!", $partyechostr);
		$bluuuh = explode("|", $speffect);
		$argh = 0;
		while (!empty($omgwhy[$argh])) {
			echo $omgwhy[$argh];
			if (!empty($bluuuh[$argh])) {
				$derp = explode(":", $bluuuh[$argh]);
				printEffects($derp); //all this just to make sure this lines up right since it directly echoes
			}
			$argh++;
		}
		echo "<form action='mercenaries.php' method='post'>Action: ";
		echo "<select name='partyaction'><option value='rename'>Rename</option><option value='redesc'>Redescribe</option><option value='pickup'>Add to party</option><option value='dropoff'>Drop off at house</option><option value='dismiss'>Dismiss</option></select>";
		echo "<select name='partymember'>" . $partyformstr . "</select>";
		echo "<input type='text' name='partytext' /><input type='submit' value='Go' /></form>";
	}
	echo "<br />Hire additional allies:<br />To hire an ally, first choose a land and a Boondollar amount.<br />";
	echo "The more Boondollars you offer, the better your chance of getting a higher-ranking ally, or one with a higher starting loyalty.<br />";
	echo "Note: Not all ally types will be available right off the bat. Boosting your land's economy or doing specific quests may unlock new types.<br />";
	echo "It costs 1 available quest to hire a mercenary. You have $userrow[availablequests] quests remaining.<br /><br />";
	echo '<form action="mercenaries.php" method="post">Land to search: <select name="land"> ';
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
	if ($userrow['battlefield_access'] != 0) { //Player has handled their denizen or gone god tier. The battlefield is available as a zone.
	  echo '<option value="Battlefield">The Battlefield</option>';
	}
	echo '</select><br />';
	echo 'Boondollars to offer: <input type="text" name="boons" /><br /><input type="submit" value="Search (cost: 1 available quest)" /></form>';
}

require_once("footer.php");
?>