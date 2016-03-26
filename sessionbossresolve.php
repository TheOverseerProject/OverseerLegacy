<?php
require_once("header.php");

function refreshSingular($slot, $buddyrow) { //used for enemies that generate other enemies, so that it doesn't revert enemy data that has already been edited
	$dummyrow = refreshEnemydata($buddyrow);
	$enstr = 'enemy' . strval($slot);
	$buddyrow[$enstr . 'name'] = $dummyrow[$enstr . 'name'];
	$buddyrow[$enstr . 'health'] = $dummyrow[$enstr . 'health'];
	$buddyrow[$enstr . 'power'] = $dummyrow[$enstr . 'power'];
	$buddyrow[$enstr . 'maxhealth'] = $dummyrow[$enstr . 'maxhealth'];
	$buddyrow[$enstr . 'maxpower'] = $dummyrow[$enstr . 'maxpower'];
	$buddyrow[$enstr . 'desc'] = $dummyrow[$enstr . 'desc'];
	$buddyrow[$enstr . 'category'] = $dummyrow[$enstr . 'category'];
	return $buddyrow;
}
if (empty($_SESSION['username'])) {
	echo "Log in to engage in strife.</br>";
	echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} else {
$max_enemies = 5;
$downarray = array();
$bosslog = "";
$bossdamagedealt = 0;
$sessioname = $userrow['session_name'];
$sessionresult = mysql_query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
$sessionrow = mysql_fetch_array($sessionresult);
$sceptrearc = false;
$earthquake = false;
$batterup = false;
$timestopstr = ($statustr . "TIMESTOP|");
$hopelessstr = ($statustr . "HOPELESS|");
$knockdownstr = ($statustr . "KNOCKDOWN|");
$glitchstr = ($statustr . "GLITCHED|");
$hopelessroll = rand(1,100);
$glitchedroll = rand(1,100);
$chumroll = 0;
$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "';");
while ($buddyrow = mysql_fetch_array($sessionmates)) { //This loop accrues focus points and sums damage/power reduction inflicted.
	$chumroll++;
}
$currentstatus = $sessionrow['sessionbossstatus'];
if (strpos($currentstatus, $timestopstr) !== False) { //This enemy is frozen in time.
	//Messages handling these statuses are handled down below.
} elseif ((strpos($currentstatus, $glitchstr) !== False) && $glitchedroll < 30) { //Glitching out stops the enemy from trying to get up.
	$bosslog = $bosslog . $userrow['enemy1name'] . " tries to " . generateGlitchString(); //lol glitchy text all over the log
} elseif (strpos($currentstatus, $knockdownstr) !== False) { //Time stopped enemies can't get up from knockdown.
	//Not here!
} elseif ((strpos($currentstatus, $hopelessstr) !== False) && $hopelessroll < 50) { //If enemy unable to attack, don't bother with this.
	$bosslog = $bosslog . $userrow['enemy1name'] . " can't be bothered with special attacks this round.</br>"; //lol glitchy text all over the log
} else {
	switch($sessionrow['sessionbossname']) { //DATA SECTION: Select any specials that may apply this round
	case 'The Black King':
		$sceptreselector = rand(1,100);
		if ($sceptreselector <= ($userrow[$healthvialstr] * 15 / $userrow['Gel_Viscosity'])) { //More likely the less wounded the leader is, 15% at best
			$sceptrearc = true;
			$bosslog = $bosslog . "The Black King sweeps his massive sceptre in a devastating arc!</br>";
		}
		if (!$sceptrearc) { //Only one special per round, and he prefers the big swing.
			$earthquakeselector = rand(1,100);
			if (canFly($userrow)) $earthquakeselector -= 15;
			if ($earthquakeselector > 85) {
				$earthquake = true;
				$bosslog = $bosslog . "The Black King slams the ground, creating a violent quake that throws everyone off-balance!</br>";
			}
		}
		if (!$sceptrearc && !$earthquake) {
			$meteorselector = rand(1,100);
			if ($meteorselector > 90) { //Meteor!
				$batterup = true;
				$meteorstrike = false; //false means miss, true means hit, empty means nonexistent
				$bosslog = $bosslog . "The Black King uses his sceptre to swat a giant meteor at you like a baseball.</br>";
			}
		}
		break;
	default:
		break;
	}
}

$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $sessioname . "' AND `Players`.`sessionbossengaged` = 1;");
$processedbuddies = 0;
while ($buddyrow = mysql_fetch_array($sessionmates)) {
$buddyrow = parseEnemydata($buddyrow);
if (!empty($someshittyvariablethatdoesntexist)) {
	echo "WHAT DID YOU DO?";
} else {  
	if (empty($buddyrow['Class'])) $buddyrow['Class'] = "Default";
	$classresult = mysql_query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$buddyrow[Class]';");
	$classrow = mysql_fetch_array($classresult);
	$aspectresult = mysql_query("SELECT * FROM `Aspect_modifiers` WHERE `Aspect_modifiers`.`Aspect` = '$buddyrow[Aspect]';");
	$aspectrow = mysql_fetch_array($aspectresult);
	$unarmedpower = floor($buddyrow['Echeladder'] * (pow(($classrow['godtierfactor'] / 100),$buddyrow['Godtier'])));
	$factor = ((612 - $buddyrow['Echeladder']) / 611);
	$unarmedpower = ceil($unarmedpower * ((($classrow['level1factor'] / 100) * $factor) + (($classrow['level612factor'] / 100) * (1 - $factor))));
    //This will register which abilities the player has in $abilities. The standard check is if (!empty($abilities[ID of ability to be checked for>]))
    $abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$buddyrow[Aspect]','All') AND `Abilities`.`Class` IN ('$buddyrow[Class]','All') 
	AND `Abilities`.`Rungreq` BETWEEN 0 AND $buddyrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $buddyrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
    $abilities = array(0 => "Null ability. No, not void.");
    while ($temp = mysql_fetch_array($abilityresult)) {
		$abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
		//as pulling the correct element out of the abilities array via the ID. Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
    }
    $message = ""; //This variable contains combat messages. It is saved at the end of the file and strife.php reads it off.
    $offense = $buddyrow['sessionbossattack'];
    $defense = $buddyrow['sessionbossdefense'];
    $luck = ceil($buddyrow['Luck'] + $buddyrow['Brief_Luck']); //Calculate the player's luck total. Paranoia: Make sure we don't somehow have a non-integer.
    if (!empty($abilities[19])) { //Light's Favour activates. Increase luck.
		$luck += floor($buddyrow['Echeladder'] / 30);
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
    $spritepower = $buddyrow['sprite_strength'];
	$currentstatus = $sessionrow['sessionbossstatus'];
	$individualstatus = $buddyrow['strifestatus'];
    if ($buddyrow['equipped'] != "" && $buddyrow['dreamingstatus'] == "Awake") {
		$equipname = str_replace("'", "\\\\''", $buddyrow[$buddyrow['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $equipname . "'");
		while ($row = mysql_fetch_array($itemresult)) {
			$itemname = $row['name'];
			$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
			if ($itemname == $buddyrow[$buddyrow['equipped']]) {
				$mainrow = $row; //We save this to check weapon-specific bonuses to various commands.
			}
		}
		$mainpower = $mainrow['power'];
    } else {
		$mainpower = 0;
    }
    if ($buddyrow['offhand'] != "" && $buddyrow['offhand'] != "2HAND" && $buddyrow['dreamingstatus'] == "Awake") {
		$offname = str_replace("'", "\\\\''", $buddyrow[$buddyrow['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $offname . "'");
		while ($row = mysql_fetch_array($itemresult)) {
			$itemname = $row['name'];
			$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
			if ($itemname == $buddyrow[$buddyrow['offhand']]) {
				$offrow = $row;
			}
		}
		$offpower = ($offrow['power'] / 2);
    } else {
		$offpower = 0;
    }
    if ($buddyrow['headgear'] != "" && $buddyrow['dreamingstatus'] == "Awake") {
		$headname = str_replace("'", "\\\\''", $buddyrow[$buddyrow['headgear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $headname . "'");
		while ($row = mysql_fetch_array($itemresult)) {
			$itemname = $row['name'];
			$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
			if ($itemname == $buddyrow[$buddyrow['headgear']]) {
				$headrow = $row; //We save this to check weapon-specific bonuses to various commands.
			}
		}
		if ($headrow['hybrid'] == 1) $headrow = convertHybrid($headrow, false);
		$headdef = $headrow['power'];
    } else {
		$headdef = 0;
    }
    if ($buddyrow['facegear'] != "" && $buddyrow['facegear'] != "2HAND" && $buddyrow['dreamingstatus'] == "Awake") {
		$facename = str_replace("'", "\\\\''", $buddyrow[$buddyrow['facegear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $facename . "'");
		while ($row = mysql_fetch_array($itemresult)) {
			$itemname = $row['name'];
			$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
			if ($itemname == $buddyrow[$buddyrow['facegear']]) {		
				$facerow = $row; //We save this to check weapon-specific bonuses to various commands.
			}
		}
		if ($facerow['hybrid'] == 1) $facerow = convertHybrid($facerow, false);
		$facedef = $facerow['power'];
    } else {
		$facedef = 0;
    }
    if ($buddyrow['bodygear'] != "" && $buddyrow['dreamingstatus'] == "Awake") {
		$bodyname = str_replace("'", "\\\\''", $buddyrow[$buddyrow['bodygear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $bodyname . "'");
		while ($row = mysql_fetch_array($itemresult)) {
			$itemname = $row['name'];
			$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
			if ($itemname == $buddyrow[$buddyrow['bodygear']]) {
				$bodyrow = $row; //We save this to check weapon-specific bonuses to various commands.
			}
		}
		if ($bodyrow['hybrid'] == 1) $bodyrow = convertHybrid($bodyrow, false);
		$bodydef = $bodyrow['power'];
    } else {
		$bodydef = 0;
    }
	if ($buddyrow['accessory'] != "" && $buddyrow['dreamingstatus'] == "Awake") {
		$accname = str_replace("'", "\\\\''", $buddyrow[$buddyrow['accessory']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $accname . "'");
		while ($row = mysql_fetch_array($itemresult)) {
			$itemname = $row['name'];
			$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
			if ($itemname == $buddyrow[$buddyrow['accessory']]) {
				$accrow = $row; //We save this to check weapon-specific bonuses to various commands.
			}
		}
		if ($accrow['hybrid'] == 1) $accrow = convertHybrid($accrow, false);
		$accdef = $accrow['power'];
    } else {
		$accdef = 0;
    }
    $totaldef = $headdef + $facedef + $bodydef + $accdef;
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = 'Perfectly Generic Object'");
    $blankrow = mysql_fetch_array($itemresult);
    //Define rows as effectively empty when player dreaming or has nothing equipped. Track number of blanks for the purposes of the Void roletech (and maybe some other stuff)
    $blanks = 0;
    $voidvalid = 0; //Roletech ID 15 "One with Nothing" only activates if both weapon slots are empty.
    if (empty($mainrow) || $buddyrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$voidvalid++;
		$mainrow = $blankrow;
    }
    if (empty($offrow) || $buddyrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$voidvalid++;
		$offrow = $blankrow;
    }
    if (empty($headrow) || $buddyrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$headrow = $blankrow;
    }
    if (empty($facerow) || $buddyrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$facerow = $blankrow;
    }
    if (empty($bodyrow) || $buddyrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$bodyrow = $blankrow;
    }
    if (empty($accrow) || $buddyrow['dreamingstatus'] != "Awake") {
		$blanks++;
		$accrow = $blankrow;
    }
    if ($spritepower < 0) {
		$spritepower = 0;
    }
	$nodamage = False;
	if (!empty($abilities[1]) && $offense == "aggress") { //Activate passive aggression: apply passive modifier. (ID 1)
		$message = $message . "$abilities[1]</br>";
		$unarmedpower = floor($unarmedpower * ($classrow['passivefactor'] / 100));
	} else {
		$unarmedpower = floor($unarmedpower * ($classrow['activefactor'] / 100)); //User is main strifer, apply active modifier.
	}
	$powerlevel = $unarmedpower + $buddyrow['powerboost'] + $buddyrow['temppowerboost'] + $mainpower + $offpower + $aidpower; //Sprite power added later.
	if (!empty($abilities[15]) && $blanks > 0 && $voidvalid == 2) { //One with Nothing activates. Increase unarmed power for the purposes of power level.
		$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[15]</br>";
			if ($buddyrow['dreamingstatus'] == "Awake") {
				$voidpower = 5 * $blanks * $buddyrow['Echeladder'];
			} else {
				$voidpower = floor($buddyrow['Echeladder'] / 2);
			}
			$bosslog = $bosslog . $buddyrow['username'] . "'s Void power boost: " . strval($voidpower) . "</br>";
			$powerlevel += $voidpower;
		}
		if (!empty($abilities[17])) { //Blood Bonds activates. Increase power according to the number of players fighting.
			$bloodbond = 0;
			$testuserescape = mysql_real_escape_string($username); //Add escape characters so we can find session correctly in database.
			$testsessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '" . $testuserescape . "'");
			while ($testrow = mysql_fetch_array($testsessionmates)) {
				if ($testrow['sessionbossengage'] == 1) $bloodbond += $unarmedpower; //Grab everyone assisting.
			}
			if ($bloodbond > 0) { //Ability actually triggers. Print message etc.
				$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[17]</br>";
				$bosslog = $bosslog . $buddyrow['username'] . "'s Blood bond strength:" . strval($bloodbond) . "</br>";
				$powerlevel += $bloodbond;
			}
		}
		$offensepower = $powerlevel + $buddyrow['offenseboost'] + $buddyrow['tempoffenseboost'];
		$defensepower = $powerlevel + $buddyrow['defenseboost'] + $buddyrow['tempdefenseboost'] + $totaldef + $aiddef;
		if (!empty($abilities[7])) { //Aspect Fighter (ID 7)
			$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[7]</br>";
			$offensebonus = floor($aspectrow['Damage'] + $aspectrow['Power_down'] + $aspectrow['Offense_up'] + floor($aspectrow['Power_up'] / 2) * ($unarmedpower / 612));
			$defensebonus = floor($aspectrow['Invulnerability'] + $aspectrow['Heal'] + $aspectrow['Defense_up'] + floor($aspectrow['Power_up'] / 2) * ($unarmedpower / 612));
			if ($offensebonus < 0) $offensebonus = 0; //Paranoia: If something goes wrong, set the value to zero.
			if ($defensebonus < 0) $defensebonus = 0;
			$offensepower += $offensebonus;
			$defensepower += $defensebonus;
			$bosslog = $bosslog . $buddyrow['username'] . "'s Offense bonus: $offensebonus, Defense bonus: $defensebonus</br>"; //Special string: Print boost values.
		}
		if (!empty($abilities[3]) && $offense == "assault") { //Activate chaotic assault: randomize between -100 and 350 bonus power with a luck factor. (ID 3)
			$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[3]</br>";
			$offensepower = ceil($offensepower + (rand((-100 + $luck),350) * ($buddyrow['Echeladder'] / 300)));
		}
		//Nullification of weapons occurs by blanking the weapon rows.
		//Set the last commands used.
		//mysql_query("UPDATE `Players` SET `lastactive` = '$offense', `lastpassive` = '$defense' WHERE `Players`.`username` = '$username' LIMIT 1;");
		$buddyrow['lastactive'] = $offense;
		$buddyrow['lastpassive'] = $defense;
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
		if ($buddyrow['dreamingstatus'] == "Awake") {
			$offensepower = $offensepower + $spritepower + $aidsprite;
			$defensepower = $defensepower + $spritepower + $aidsprite;
		}
		$i = 1;
		$enemiesfought = 0;
		$alldead = True;
		$damage = 0;
		$enemydown = False;
		$statustr = "ENEMY" . strval($i) . ":";
		$enemystr = "enemy" . strval($i) . "name";
		$powerstr = "enemy" . strval($i) . "power";
		$maxpowerstr = "enemy" . strval($i) . "maxpower";
		$healthstr = "enemy" . strval($i) . "health";
		$maxhealthstr = "enemy" . strval($i) . "maxhealth";
		$descstr = "enemy" . strval($i) . "desc"; //Need this to check for nulls.
		$categorystr = "enemy" . strval($i) . "category";
		if ($buddyrow[$enemystr] != "") { //Enemy spotted! (failsafe: If no fragment, do nothing.
			$enemyrowexists = False;
				if (empty($_SESSION[$buddyrow[$enemystr]])) {
					$enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE '" . $buddyrow[$enemystr] . "' LIKE CONCAT ('%', `Enemy_Types`.`basename`, '%')");
					if ($enemyrow = mysql_fetch_array($enemyresult)) { //Enemy showed up in the table.
						$enemyrowexists = True;
						$_SESSION[$buddyrow[$enemystr]] = $enemyrow;
					}
				} else {
					$enemyrow = $_SESSION[$buddyrow[$enemystr]];
					$enemyrowexists = True;
				}
				if ($earthquake) { //Earthquake!
					if (canFly($buddyrow)) {
						$offensepower += 1025;
						$defensepower += 1025;
					} else {
						$offensepower -= 1025;
						$defensepower -= 4000;
					}
				}
				//Calculate damage with a random element thrown in. 100 luck forces a max roll.
				$enemydamage = rand(floor($offensepower * (0.85 + ($luck * 0.003))),ceil($offensepower * 1.15)) - floor($buddyrow[$powerstr]);
				$recoildamage = 0;
				$waterygelstr = ($statustr . "WATERYGEL|");
				$enragedstr = ($statustr . "ENRAGED|");
				if (strpos($currentstatus, $waterygelstr) !== False) { //This enemy is suffering from watery health gel.
					$enemydamage = floor($enemydamage * 1.1); 
				}
				if (strpos($currentstatus, $enragedstr) !== False) { //This enemy is angry and defends poorly.
					$enemydamage += floor($buddyrow[$powerstr] * 0.1);
				}
				if (!empty($abilities[21])) { //Inevitability activates. Calculate bonus damage (ID 21)
					$bonusdamage = floor(2.5 * (1 - ($sessionrow['sessionbosshealth'] / $sessionrow['sessionbossmaxhealth'])) * $unarmedpower);
					//Bonus damage is 2.5 times the unarmed power divided by the ratio (i.e. if all HP was missing, it would deal 2x unarmed power)
					if ($bonusdamage > 0) {
						$bosslog = $bosslog . $buddyrow['username'] . "'s " . $abilities[21] . "</br>";
						$enemydamage += $bonusdamage;
					}
				}
				$crit = rand(1,10); //Need a LOT of luck to crit normally, and the chance is pretty shit.
				if ($buddyrow['motifcounter'] > 0 && $buddyrow['Aspect'] == "Light") {
					$crit = rand(10,100); //maxed luck AND light III means you're guaranteed to crit.
				} 
				if (!empty($abilities[23])) { //Fortune's Protection activates (ID 23). Regardless of other modifiers, grant a flat chance to score an instant critical. (Light guides your blow)
					$guidance = rand((1 + floor($luck / 10)),100); //Luck modifier here is small. I mean, it didn't work last time, did it?
					if ($guidance > (100 - floor($buddyrow['Echeladder'] / 50))) $crit = 100; //Every fifty rungs, increase the chance by 1%
				}
				if ($crit >= (55 - floor($luck * 0.455))) {
					$bosslog = $bosslog . $buddyrow['username'] . " lands a critical hit on $buddyrow[$enemystr]!</br>";
					$enemydamage += rand(floor($offensepower * (0.85 + ($luck * 0.003))),ceil($offensepower * 1.15)); //Double the base damage before subtraction by adding it on again.
				}
				if (!empty($abilities[22])) { //Broken Record, I'm pretty sure.
					$proc = rand(1,100);
					$chance = floor(($buddyrow['Echeladder'] + ($buddyrow['Godtier'] * 60)) / 15) + floor($luck / 18);
					if ($proc < $chance) { //40% chance of triggering at max rank. Godtier increases it by 4% per tier. Luck has a low influence.
						$strikes = 2 + floor(rand($luck,100) / 85) + floor(rand($buddyrow['Echeladder'],1111) / 666) + ceil($buddyrow['Godtier'] / 3);
						//Two strikes guaranteed. One is luck dependent, one is Echeladder dependent, and extras appear at god tiers 1, 4, 7, etc.
						$bosslog = $bosslog . $buddyrow['username'] . "'s " . $abilities[22] . "</br>";
						$bosslog = $bosslog . "Attacks performed: $strikes</br>";
						$enemydamage *= $strikes;
					}
				}
				if ($enemydamage < 0) $enemydamage = 0; //No healing enemies with attacks!
				$timestopped = False;
				if ($enemydamage != 0) { //Effects that trigger on hit go here. They only trigger if damage is dealt!
					//Check the main row weapon for effects.
					$mainoff = 1;
					while ($mainoff < 3) { //1 for main, 2 for off. 3 means done.
						if ($mainoff == 1) { //Handle main hand effects.
							$effectarray = explode('|', $mainrow['effects']);
						} else { //Handle offhand effects.
							$effectarray = explode('|', $offrow['effects']);
						}
						if (!empty($abilities[7])) { //Aspect Fighter activates, granting affinity.
							$effectarray[] = "AFFINITY:" . $buddyrow['Aspect'] . ":5";
							if ($echoaspectfighter == false) { //because there's no need to repeat this five times
								$bosslog = $bosslog . $buddyrow['username'] . "'s Lv. 83 Knightskill Aspect Fighter activates! $buddyrow[username]'s strikes are imbued with " . $buddyrow['Aspect'] . " affinity.</br>";
								$echoaspectfighter = true;
							}
						}
						$effectnumber = 0;
						while (!empty($effectarray[$effectnumber])) {
							$currenteffect = $effectarray[$effectnumber];
							$currentarray = explode(':', $currenteffect); //Note that what each array entry means depends on the effect.
							switch ($currentarray[0]) {
							case TIMESTOP: //Format is TIMESTOP:<%chance>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Time'] / 100); //Enemy's time resistance reduces success chance.
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) { //TIMESTOP has one argument: The chance of it working.
									$timestopped = True;
									$individualstatus = $individualstatus . $statustr . "TIMESTOP|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s attack causes $buddyrow[$enemystr] to stop for a second, but it quickly recovers.</br>";
								}
								break;
							case AFFINITY: //Format is AFFINITY:<affinity type>:<percentage>|
								if ($currentarray[1] == "All" && $buddyrow['aspect'] != "") $currentarray[1] == $buddyrow['aspect'];
								$resiststr = "resist_" . $currentarray[1];
								$affinityfactor = $currentarray[2] / 100;
								if (!empty($enemyrow[$resiststr])) { //Apply resistance (it'll be 0 if the enemy doesn't resist, and nonexistent if no enemy can resist)
									$affinityfactor *= ((100 - $enemyrow[$resiststr]) / 100);
								}
								if ($currentarray[1] == $buddyrow['Aspect']) {
									$affinityfactor *= 1.2;
								}
								if ($affinityfactor < -1) $affinityfactor = -1; //Maximum of 100% absorption.
								$enemydamage = $enemydamage + floor($enemydamage * $affinityfactor);
								break;
							case WATERYGEL: //Format is WATERYGEL:<%chance>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Life'] / 100); //Enemy's Life resistance reduces success chance.
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) { //WATERYGEL has one argument: The chance of it working.
									$individualstatus = $individualstatus . $statustr . "WATERYGEL|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s attack seems to affect $buddyrow[$enemystr]'s Health Vial slightly.</br>";
								}
								break;
							case POISON: //Format is POISON:<%chance>:<%severity>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Doom'] / 100); //Enemy's Doom resistance reduces poison severity.
								if ($roll > (100 - intval($currentarray[1]))) { //Check to see if poison applies. Note that Doom resistance doesn't decrease this
									$severity = (ceil($currentarray[2] * $resistfactor * 100)) / 100; //Round to two decimal places.
									$individualstatus = $individualstatus . $statustr . "POISON:" . $severity . "|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s blow poisons $buddyrow[$enemystr]!</br>";
								}
								break;
							case SHRUNK: //Format is SIZECHANGE:<%chance>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Space'] / 100); //Space resistance reduces the chance of shrinking working
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
									$individualstatus = $individualstatus . $statustr . "SHRUNK|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s strike makes $buddyrow[$enemystr]'s outline waver a little.</br>";
								}
								break;
							case LIFESTEAL: //Format is LIFESTEAL:<%chance>:<%absorbed>|. A lifesteal weapon, er, steals life.
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Blood'] / 100); //Blood resistance reduces the successful absorption
								if ($roll > (100 - intval($currentarray[1]))) {
									if ($resistfactor >= 0) {
										$bosslog = $bosslog . $buddyrow['username'] . "'s attack siphons health gel from $buddyrow[$enemystr]!</br>";
									} else {
										$bosslog = $bosslog . $buddyrow['username'] . "'s attack siphons health gel from $buddyrow[$enemystr]! Unfortunately, the gel harms instead of healing.</br>";
									}
									$healthgain = ceil(($enemydamage * ($currentarray[2] / 100)) * $resistfactor);
									//NOTE: We need to calculate this after all weapon effects that amplify damage.
									//Also, enemies with over 100 Blood resistance actually have poisonous health.
									if ($buddyrow[$healthvialstr] + $healthgain < $buddyrow['Gel_Viscosity']) { //Be careful not to overheal.
										$newhealth = $buddyrow[$healthvialstr] + $healthgain;
									} else {
										$newhealth = $buddyrow['Gel_Viscosity'];
									}
									$buddyrow[$healthvialstr] = $newhealth;
									//mysql_query("UPDATE `Players` SET `" . $healthvialstr . "` = $newhealth WHERE `Players`.`username` = '$username` LIMIT 1;");
								}
								break;
							case MISFORTUNE: //Format is MISFORTUNE:<%chance>|
								$roll = rand((1 + floor($luck/5)),100); //Luck is more effective at increasing this
								$resistfactor = 1 - ($enemyrow['resist_Light'] / 100); //Light resistance reduces the chance of misfortune working
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
									$individualstatus = $individualstatus . $statustr . "UNLUCKY|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s blow makes $buddyrow[$enemystr] seem a little ill at ease...</br>";
								}
								break;
							case RANDAMAGE: //format is RANDAMAGE:<%variance>|
								$roll = rand(($currentarray[1] * -1) + floor(($currentarray[1] * 2) * ($luck / 100)), $currentarray[1]); 
								//luck increases the minimum roll up to a maximum of... the maximum roll
								$enemydamage = $enemydamage * (($roll / 100) + 1); //multiply damage by this random percentage, which will stack
								break;
							case BLEEDING: //Format is BLEEDING:<%chance>:<duration>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Blood'] / 100); //Enemy's Blood resistance reduces chance of application.
								//NOTE - May change this to modify duration instead of or as well as resisting application.
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) { //Check to see if wound bleeds.
									$individualstatus = $individualstatus . $statustr . "BLEEDING:" . $currentarray[2] . "|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s weapon inflicts a weeping wound on $buddyrow[$enemystr]!</br>";
								}
								break;
							case HOPELESS: //Format is HOPELESS:<%chance>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Hope'] / 100); //Enemy's Hope resistance reduces chance of application.
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
									$individualstatus = $individualstatus . $statustr . "HOPELESS|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s attack seems to have rattled $buddyrow[$enemystr]</br>";
								}
								break;
							case DISORIENTED: //Format is DISORIENTED:<%chance>:<duration>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Mind'] / 100); //Enemy's Mind resistance reduces chance of application.
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
									$individualstatus = $individualstatus . $statustr . "DISORIENTED:" . $currentarray[2] . "|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s strike momentarily stuns $buddyrow[$enemystr]</br>";
								}
							case DISTRACTED: //Format is DISTRACTED:<%chance>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Mind'] / 100); //Enemy's Mind resistance reduces chance of application.
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
									$individualstatus = $individualstatus . $statustr . "DISTRACTED|";
									$bosslog = $bosslog . $buddyrow['username'] . " distracts $buddyrow[$enemystr] just a little bit</br>";
								}
								break;
							case ENRAGED: //Format is ENRAGED:<%chance>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Rage'] / 100); //Enemy's Rage resistance reduces chance of application.
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
									$individualstatus = $individualstatus . $statustr . "ENRAGED|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s hit is unusually aggravating!</br>";
								}
								break;
							case MELLOW: //Format is MELLOW:<%chance>|
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Rage'] / 100); //Enemy's Rage resistance reduces chance of application.
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
									$justmellowed = True;
									$individualstatus = $individualstatus . $statustr . "MELLOW|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s hit is unusually calming!</br>";
								}
								break;
							case KNOCKDOWN: //Format is KNOCKDOWN:<%multiplier>|. Knockdown chance depends on damage dealt.
								//The multiplier effectively multiplies damage for the purposes of calculating the knockdown chance only.
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Breath'] / 100); //Enemy's Breath resistance reduces chance of application.
								//NOTE: Breath resistance covers "sudden force" in this case, so a weapon does not have to be Breath-y to have this effect.
								$target = 100 - ((($enemydamage * (intval($currentarray[1])/100) * $resistfactor) / $buddyrow[$healthstr]) * 300); //33% effective damage = 100% chance
								if ($roll > $target) {
									$individualstatus = $individualstatus . $statustr . "KNOCKDOWN|";
									$bosslog = $bosslog . $buddyrow['username'] . "'s attack staggers $buddyrow[$enemystr] slightly</br>";
								}
								break;
							case GLITCHED: //Format is GLITCHED:<%chance>|. NOTE - Glitching is a permanent status ailment. Please balance accordingly.
								$roll = rand((1 + floor($luck/10)),100);
								$resistfactor = 1 - ($enemyrow['resist_Void'] / 100); //Enemy's Void resistance reduces chance of application.
								if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
									$individualstatus = $individualstatus . $statustr . "GLITCHED|";
									$glitchstr = horribleMess();
									$bosslog = $bosslog . $buddyrow['username'] . "'s hit looks kinda $glitchstr</br>";
								}
								break;
							case RECOIL: //format is RECOIL:<%chance>:<%damage>|.
								$roll = rand(0,(100 - $luck)); //luck REDUCES chance for player to take recoil
								//possibly consider adding a roletech that helps resist?
								if ($roll > (100 - intval($currentarray[1]))) {
									$recoildamage += $enemydamage * ($currentarray[2] / 100);
									$bosslog = $bosslog . $buddyrow['username'] . "'s attack causes some recoil damage!<br />";
									//note that recoil is applied AFTER enemy damage, and cannot kill the player
								}
								break;
							default:
								break;
							}
							$effectnumber++;
						}
						$mainoff++;
					}
					$nodamage = False; //Damage was dealt.
				} else { //Effects that trigger when damage is NOT dealt go here.
					//nope.avi
				}
				$newenemyhealth = $buddyrow[$healthstr] - $enemydamage; //Subtract off the damage.
				$distractedstr = ($statustr . "DISTRACTED|");
				if (strpos($currentstatus, $distractedstr) !== False) { //This enemy is distracted. Double damage!
					$newenemyhealth = $newenemyhealth - $enemydamage;
				}
				if (!empty($noonecares)) { //NOTE - this always fails. Just easier than reformatting.
					echo "what did you doooooooo</br>";
	  } else {
	    if ($newenemyhealth <= 0) { //Forced back.
	        $forcedback = true;
		$newenemyhealth = 0; //Player has knocked off their entire health allotment for the round!
		$bosslog = $bosslog . $buddyrow['username'] . " deals enough damage this round to force $buddyrow[enemy1name] back!</br>";
	    }
		$timestopstr = ($statustr . "TIMESTOP|");
		$hopelessstr = ($statustr . "HOPELESS|");
		$knockdownstr = ($statustr . "KNOCKDOWN|");
		$glitchstr = ($statustr . "GLITCHED|");
		$hopelessroll = rand(1,100);
		$glitchedroll = rand(1,100);
		if (strpos($currentstatus, $timestopstr) !== False) { //This enemy is frozen in time.
			$buddyrow[$healthstr] = $newenemyhealth; //Otherwise it wouldn't get updated
		} elseif ((strpos($currentstatus, $glitchstr) !== False) && $glitchedroll < 30) { //Glitching out stops the enemy from trying to get up.
			$buddyrow[$healthstr] = $newenemyhealth;
			$bosslog = $bosslog . generateGlitchString(); //lol glitchy text all over the log
		} elseif (strpos($currentstatus, $knockdownstr) !== False) { //Time stopped enemies can't get up from knockdown.
			$buddyrow[$healthstr] = $newenemyhealth;
		} elseif ((strpos($currentstatus, $hopelessstr) !== False) && $hopelessroll < 50) { //If enemy unable to attack, don't bother with this.
			$buddyrow[$healthstr] = $newenemyhealth;
		} elseif (!empty($forcedback) && $forcedback === true && $newenemyhealth == 0) {
			$buddyrow[$healthstr] = $newenemyhealth;
		} else {
			$playerdamage = floor($buddyrow[$powerstr]);
			$playerdefense = rand(floor($defensepower * (0.85 + ($luck * 0.003))),ceil($defensepower * 1.15));
			$playerdamage = floor($playerdamage - $playerdefense);
			$mellowstr = ($statustr . "MELLOW|");
			if (strpos($currentstatus, $mellowstr) !== False) { //This enemy is totally mellowed out.
				$playerdamage -= floor($buddyrow[$powerstr] * 0.1);
			}
			if (!empty($abilities[23])) { //Check for Fortune's Protection (ID 23)
				$guidance = rand(floor(1 + ($luck/10)),100);
				if ($guidance > (100 - floor($buddyrow['Echeladder'] / 40))) { //Every forty rungs, increase the chance by 1%. More likely to kick in than the autocrit (15% at max level)
					$bosslog = $bosslog . $buddyrow['username'] . "'s" . $abilities[23] . "</br>";
					$playerdamage = floor($playerdamage / 2); //Activate anticrit: halve the damage.
				}
			}
			if ($batterup && !$meteorstrike) {
				$meteordamage = 0;
				$victimroll = rand(1,100);
				if ($victimroll > (100 / ($chumroll - $processedbuddies + 1))) {
					$meteorstrike = true;
					$meteordamage = rand(3000,4000);
					if (($playerdamage + $meteordamage) <= 0) { //If the player still didn't take any damage, it's dodged!
						$meteordodged = true;
						$bosslog = $bosslog . $buddyrow['username'] . " manages to dodge the meteor!</br>";
					} else {
						$bosslog = $bosslog . $buddyrow['username'] . " is struck by the meteor!</br>";
					}
				}
			}
			if ($playerdamage < 0 || $buddyrow['invulnerability'] > 0) {
				if ($enemyrow['invulndrain'] != 0) { //Invuln drainer: Attack inflicts 2/3 damage
					$playerdamage = floor($playerdamage / 1.5);
				} else {	
					$playerdamage = 0; //No healing the player with attacks, negate damage if player invulnerable.
				}
			}
			$shrunkstr = ($statustr . "SHRUNK|");
			if (strpos($currentstatus, $shrunkstr) !== False) { //This enemy is shrunk
				$playerdamage = floor($playerdamage / 1.5);
			}			
			if (!empty($abilities[13]) && $playerdamage > 0) { //Spatial Warp activates. Cause some recoil. (ID 13)
				$recoil = floor($buddyrow[$powerstr] / 5);
				$recoil = min(($newenemyhealth - 1), $recoil);
				$newenemyhealth -= $recoil;
				$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[13]</br>Recoil damage on $buddyrow[$enemystr]: $recoil</br>";
			}
			//mysql_query("UPDATE `Players` SET `" . $healthstr . "` = '" . strval($newenemyhealth) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Update HP
			$buddyrow[$healthstr] = $newenemyhealth; //Update for repetition and checking end-of-turn effects.
			//Enemy isn't dead, so they can retaliate. 100 player luck forces a minimum roll.
			//Begin tracking special effects on standard damage (such as role abilities) here:
			if (!empty($abilities[2]) && $playerdamage > 0) { //Life's Bounty activates. Reduce damage. (ID 2)
				$playerdamage = floor($playerdamage * 0.85);
				$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[2]</br>";
			} 
			if (!empty($abilities[4]) && $playerdamage > 0) { //Roll for Dissipate. (ID 4)
				$targetvalue = 100 - (1 + floor($buddyrow['Echeladder'] / 100) + ($buddyrow['Godtier'] * 6) + floor($luck/10));
				if ($targetvalue < 50) $targetvalue = 50; //Maximum 50% chance.
				$rand = rand(1,100);
				if ($rand > $targetvalue || $buddyrow['dissipatefocus'] == 1 || $dissipating == True) { //Ability triggers
					if ($buddyrow['dissipatefocus'] == 1) {
						$buddyrow['dissipatefocus'] = 0;
						$dissipating = True; //we want to avoid every hit.
					}
					//mysql_query("UPDATE `Players` SET `dissipatefocus` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
					$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[4]</br>";
					$playerdamage = 0; //Dissipate is NOT invulnerability. Specials will strike through it.
				}
			}
			$nocapstr = "PLAYER:NOCAP|";
			if (strpos($individualstatus, $nocapstr) !== False) { //Player's massive damage cap is gone.
				$savingthrow = rand((1 + ceil($luck/5)),100); //Player gets a save every time they are struck.
				if ($savingthrow > 66) {
					$bosslog = $bosslog . $buddyrow['username'] . "'s massive damage protection has returned.</br>";
					$individualstatus = str_replace($nocapstr, "", $individualstatus);
				}
			} else {
				if ($playerdamage > ($buddyrow['Gel_Viscosity'] / 2.5)) $playerdamage = floor($buddyrow['Gel_Viscosity'] / 2.5); //Massive damage safety net.
			}
			if (empty($meteordodged)) $playerdamage += $meteordamage; //Being hit by a meteor pisses you off!
			if (!empty($abilities[12]) && $playerdamage > 0) { //Battle Fury activates. Increase offense boost. (ID 12). Note that it activates AFTER the safety net.
				$offenseplus = ceil(ceil($playerdamage / 8) - min(ceil($buddyrow['offenseboost'] / (ceil($buddyrow['Echeladder'] / 75))), ceil($playerdamage / 10)));
				$offenseplus = $offenseplus * ($buddyrow['Godtier'] + 1); //Multiply by the "standard" non class affected godtier modifier.
				//mysql_query("UPDATE `Players` SET `offenseboost` = $buddyrow[offenseboost]+$offenseplus WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
				$buddyrow['offenseboost'] += $offenseplus;
				$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[12]</br>";
				$message = $message . "Offense boost: $offenseplus</br>";
			}
			if ($playerdamage <= 500) $playerdamage = rand(200,500); //Always does at least a bit of damage
			$damage += $playerdamage;
	  }
	}
      if ((($buddyrow['Health_Vial'] - $damage) <= 0 && $buddyrow['dreamingstatus'] == "Awake") || ($buddyrow['Dream_Health_Vial'] - $damage <= 0 && $buddyrow['dreamingstatus'] != "Awake")) { //Dead.
	$chancething = rand(1,100) - floor($luck / 12); //This will be used for all effects with a chance of preventing death. Lower is better since the "chance" has to beat this value.
	if ($chancething < 1) $chancething = 1;
	if ($buddyrow['motifcounter'] > 0 && $buddyrow['Aspect'] == "Hope") { //But not really.
	  $bosslog = $bosslog . $buddyrow['username'] . "takes lethal damage! As their exhausted body falls to the ground, a shining white light fills it. Moments later, they spring back to their feet.</br>";
	  $damage = 0;
	  $buddyrow[$healthvialstr] = $buddyrow['motifcounter'] * 500;
	  if ($buddyrow[$healthvialstr] > $buddyrow['Gel_Viscosity']) $buddyrow[$healthvialstr] = $buddyrow['Gel_Viscosity'];
	  mysql_query("UPDATE `Players` SET `" . $healthvialstr . "` = $buddyrow[$healthvialstr] WHERE `Players`.`username` = '" . $buddyrow['username'] . "' LIMIT 1 ;");
	  $powerboost = $buddyrow['motifcounter'] * 100;
	  mysql_query("UPDATE `Players` SET `powerboost` = $buddyrow[powerboost]+$powerboost WHERE `Players`.`username` = '" . $buddyrow['username'] . "' LIMIT 1 ;");
	  $buddyrow['powerboost'] = $buddyrow['powerboost'] + $powerboost;
	  mysql_query("UPDATE `Players` SET `motifcounter` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $buddyrow['motifcounter'] = 0;
	} elseif (!empty($abilities[20]) && ($chancething <= ceil(($buddyrow['Aspect_Vial'] * 100) / $buddyrow['Gel_Viscosity']))) { //Hope Endures activated (ID 20)
	  $endured = True;
	  $bosslog = $bosslog . $buddyrow['username'] . "'s" . $abilities[20] . "</br>";
	  $damage = ($buddyrow[$healthvialstr] - 1); //So their health goes to one.
	  $aspectcost = floor($buddyrow['Aspect_Vial'] / 2);
	  mysql_query("UPDATE `Players` SET `Aspect_Vial` = $buddyrow[Aspect_Vial]-$aspectcost WHERE `Players`.`username` = '" . $buddyrow['username'] . "' LIMIT 1 ;");
	  $buddyrow['Aspect_Vial'] = $buddyrow['Aspect_Vial'] - $aspectcost;
	} elseif (!empty($endured)) { //Hope Endures has activated. The player will not die.
	  $damage = ($buddyrow[$healthvialstr] - 1); //So their health goes to one.
	} else {
	  $damage = ($buddyrow[$healthvialstr] - 1); //So their health goes to one.
	  $repeat = False; //Don't do it again.
	  $bosslog = $bosslog . $buddyrow['username'] . "is KOed!</br>";
	  if ($buddyrow['dreamingstatus'] == "Awake") {
	  	$downstr = "down";
	  } else {
	  	$downstr = "dreamdown";
	  }
	  mysql_query("UPDATE `Players` SET `" . $downstr . "` = 1 WHERE `Players`.`username` = '" . $buddyrow['username'] . "' LIMIT 1 ;");
	  $buddyrow[$downstr] = 1; //Makes messages appear.
	  $downarray[$buddyrow['username']] = true;
	  //Blanking happens at the end of the round
	  $buddyrow['sessionbossengage'] = 0;
	  mysql_query("UPDATE `Players` SET `sessionbossengaged` = 0 WHERE `Players`.`username` = '" . $buddyrow['username'] . "' LIMIT 1 ;"); //Paranoia
	  $buddyrow = endStrife($buddyrow); //Combat is over, player loses.
	}
      }
      //End-of-turn effects happen here, including damage.
      if ($buddyrow[$healthvialstr] - $damage < $buddyrow['Gel_Viscosity']) {
	$newhealth = $buddyrow[$healthvialstr] - $damage;
	if ($newhealth - $recoildamage < 0) $newhealth = 1; //recoil shouldn't kill the player
	else $newhealth -= $recoildamage;
	//$EOTquery = "UPDATE `Players` SET `" . $healthvialstr . "` = $newhealth"; //Set damage (or healing as the case may be)
	$buddyrow[$healthvialstr] = $newhealth;
      } else {
	$newhealth = $buddyrow['Gel_Viscosity'];
	//$EOTquery = "UPDATE `Players` SET `" . $healthvialstr . "` = $buddyrow[Gel_Viscosity]";
	$buddyrow[$healthvialstr] = $buddyrow['Gel_Viscosity'];
      }
        if ($sceptrearc) $buddyrow[$healthvialstr] = floor($buddyrow[$healthvialstr] / 2);
	$bosslog = $bosslog . $buddyrow[$enemystr] . " inflicts $damage damage on " . $buddyrow['username'] . "!</br>";
	$bossdamagedealt += $damage;
      if ($buddyrow['invulnerability'] > 0) {
	//$EOTquery = $EOTquery . ", `invulnerability` = $buddyrow[invulnerability]-1";
	$buddyrow['invulnerability'] = $buddyrow['invulnerability'] - 1;
      }
      if ($buddyrow['motifcounter'] == 0 || $buddyrow['Aspect'] != "Time") { //Time III prevents boosts from ticking down.
	if ($buddyrow['temppowerduration'] > 0) {
	  //$EOTquery = $EOTquery . ", `temppowerduration` = $buddyrow[temppowerduration]-1";
	  $buddyrow['temppowerduration']--;
	} elseif ($buddyrow['temppowerboost'] != 0) {
	  if ($repeat) $powerlevel = $powerlevel - $buddyrow['temppowerboost']; //Remove boost in the case that we're repeating.
	  $buddyrow['temppowerboost'] = 0; //If we're repeating, make sure this doesn't happen again.
	  //$EOTquery = $EOTquery . ", `temppowerboost` = 0";
	}
	if ($buddyrow['tempoffenseduration'] > 0) {
	  //$EOTquery = $EOTquery . ", `tempoffenseduration` = $buddyrow[tempoffenseduration]-1";
	  $buddyrow['tempoffenseduration']--;
	} elseif ($buddyrow['tempoffenseboost'] != 0) {
	  if ($repeat) $offensepower = $offensepower - $buddyrow['tempoffenseboost']; //Remove boost in the case that we're repeating.
	  $buddyrow['tempoffenseboost'] = 0; //If we're repeating, make sure this doesn't happen again.
	  //$EOTquery = $EOTquery . ", `tempoffenseboost` = 0";
	}
	if ($buddyrow['tempdefenseduration'] > 0) {
	  //$EOTquery = $EOTquery . ", `tempdefenseduration` = $buddyrow[tempdefenseduration]-1";
	  $buddyrow['tempdefenseduration']--;
	} elseif ($buddyrow['tempdefenseboost'] != 0) {
	  if ($repeat) $defensepower = $defensepower - $buddyrow['tempdefenseboost']; //Remove boost in the case that we're repeating.
	  $buddyrow['tempdefenseboost'] = 0; //If we're repeating, make sure this doesn't happen again.
	  //$EOTquery = $EOTquery . ", `tempdefenseboost` = 0";
	}
      }
      //$EOTquery = $EOTquery . ", `combatconsume` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;";
	  $buddyrow['combatconsume'] = 0;
      //mysql_query($EOTquery);
      //Begin checking passive abilities that trigger at end of turn here.
      if (!empty($abilities[11]) && ($buddyrow['powerboost'] < 0 || $buddyrow['offenseboost'] < 0 || $buddyrow['defenseboost'] < 0 || $buddyrow['temppowerboost'] < 0 || $buddyrow['tempoffenseboost'] < 0 || $buddyrow['tempdefenseboost'] < 0)) { //There's a boost below zero. Trigger Blockhead (ID 11)
$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[11]</br>";
	$boosttypes = array(0 => "powerboost", "offenseboost", "defenseboost", "temppowerboost", "tempoffenseboost", "tempdefenseboost");
	$type = 0;
	while ($type < count($boosttypes)) {
	  $boost = $boosttypes[$type];
	  if ($buddyrow[$boost] < 0) {
	    $buddyrow[$boost] += floor($buddyrow['Echeladder'] / 2);
	    if ($buddyrow[$boost] > 0) $buddyrow[$boost] = 0;
	    //mysql_query("UPDATE `Players` SET `$boost` = $buddyrow[$boost] WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  }
	  $type++;
	}
      }
      //Finish checking passive abilities that trigger at end of turn here.
      if ($buddyrow['motifcounter'] > 0) { //Player's tier 3 fraymotif is active. The effect of the fraymotif is stored and performed here. "motifvar" is a wildcard variable that may or
	//may not be used depending on what the specific fraymotif does. Some motifs have their effect elsewhere in the file.
	$motifresult = mysql_query("SELECT * FROM Fraymotifs WHERE `Fraymotifs`.`Aspect` = '" . $buddyrow['Aspect'] . "'");
	$motifrow = mysql_fetch_array($motifresult);
	if (!empty($motifrow['solo3'])) {
	  $usagestr = "Turn $buddyrow[motifcounter] of $buddyrow[username]'s $motifrow[solo3]:</br>";
	} else {
	  $usagestr = "Turn $buddyrow[motifcounter] of $buddyrow[username]'s $buddyrow[Aspect] III:</br>";
	}
	switch ($buddyrow['Aspect']) {
	case "Breath": //Breathless Battaglia
	  if (($buddyrow['motifcounter'] % 5) != 0) { //Drain power.
	    $usagestr = $usagestr . "The fraymotif steals the breath from $buddyrow[$enemystr].</br>";
	    $enemies = 1;
	    $powerdrain = 1000;
	    $powerdrained = 0;
	      $enemystr = "enemy" . strval($enemies) . "name";
	      if (!empty($buddyrow[$enemystr])) {
		$enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $buddyrow[$enemystr] . "'");
		$enemyrow = mysql_fetch_array($enemyresult);
		$powerstr = "enemy" . strval($enemies) . "power";
		if (!empty($enemyrow)) { //Not a grist enemy.
		  if ($enemyrow['reductionresist'] != 0 && $powerdrain > $enemyrow['reductionresist']) { //Enemy resists power reduction.
		    $usagestr = $usagestr . $buddyrow[$enemystr] . " resists the power reduction!</br>";
		    $powerdrain = $enemyrow['reductionresist'];
		  }
		}
		if ($powerdrain > $buddyrow[$powerstr]) $powerdrain = $buddyrow[$powerstr];
		//mysql_query("UPDATE `Players` SET `" . $powerstr . "` = $buddyrow[$powerstr]-$powerdrain WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		$buddyrow[$powerstr] = $buddyrow[$powerstr]-$powerdrain; //Update this so that any further uses (say from Sophia power boosting) work as intended.
		$powerdrained = $powerdrained + $powerdrain;
	      }
	    mysql_query("UPDATE `Players` SET `motifvar` = $buddyrow[motifvar]+$powerdrained WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1 ;");
		$buddyrow['motifvar'] += $powerdrained;
	  } else { //Use drained power to attack.
	    $usagestr = $usagestr . "The stolen power is unleashed in a massive tornado!</br>";
	    $enemies = 1;
	    $damage = $buddyrow['motifvar'] * 10; //Results in 40k after four rounds of stealing from one enemy, or a whopping 200k after four turns of stealing from five.
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $buddyrow[$enemystr] . "'");
	      $enemyrow = mysql_fetch_array($enemyresult);
	      $healthstr = "enemy" . strval($enemies) . "health";
	      $maxhealthstr = "enemy" . strval($enemies) . "maxhealth";
	      if (!empty($enemyrow)) { //Not a grist enemy.
		if ($enemyrow['massiveresist'] != 100 && $damage > (floor($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
		  $usagestr = $usagestr . $buddyrow[$enemystr] . " resists the massive damage!</br>";
		  $damage = floor(($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist']);
		}
	      }
	      if ($damage > $buddyrow[$healthstr]) $damage = $buddyrow[$healthstr] - 1;
	      //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $buddyrow[$healthstr]-$damage WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	      $buddyrow[$healthstr] = $buddyrow[$healthstr]-$damage;
	    //mysql_query("UPDATE `Players` SET `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Power expended.
		$buddyrow['motifvar'] = 0;
	  }
	  break;
	case "Heart":
 	  $usagestr = $usagestr . "The melody brings up reserves of power from deep within you.</br>";
	  $aspectregen = $buddyrow['motifcounter'] * 600;
	  if ($aspectregen > 6000) $aspectregen = 6000; //Rampup is complete after ten turns.
	  $newaspect = $buddyrow['Aspect_Vial'] + $aspectregen;
	  if ($newaspect > $buddyrow['Gel_Viscosity']) $newaspect = $buddyrow['Gel_Viscosity'];
	  //mysql_query("UPDATE `Players` SET `Aspect_Vial` = $newaspect WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $buddyrow['Aspect_Vial'] = $newaspect; //Juuuust in case.
	  break;
	case "Life":
	  $usagestr = $usagestr . "Life infuses you, regenerating your health.</br>";
	  $regen = $buddyrow['motifcounter'] * 200;
	  if ($regen > 2000) $regen = 2000; //Rampup is complete after ten turns.
	  $newhealth = $buddyrow[$healthvialstr] + $regen;
	  if ($newhealth > $buddyrow['Gel_Viscosity']) $newhealth = $buddyrow['Gel_Viscosity'];
	  //mysql_query("UPDATE `Players` SET `$healthvialstr` = $newhealth WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $buddyrow[$healthvialstr] = $newhealth; //Juuuust in case.
	  break;
	case "Hope":
	  $usagestr = $usagestr . "The aura of Hope surrounding you grows stronger.</br>"; //Hope's effect is handled up in the death code.
	  break;
	case "Light": //This enables critical hits. Handled in enemy damage code.
	  $usagestr = $usagestr . "The song makes your attacks feel luckier, somehow.";
	  break;
	case "Mind":
	  $usagestr = $usagestr . "The music helps you relax and focus on the task at hand. That being delivering a righteous smackdown.</br>";
	  $powerboost = 1000;
	  //mysql_query("UPDATE `Players` SET `powerboost` = $buddyrow[powerboost]+$powerboost WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $buddyrow['powerboost'] += $powerboost; //Juuuust in case.
	  break;
	case "Blood":
	  $usagestr = $usagestr . "Life force is drained from $buddyrow[enemy1name], flowing through you as power.</br>";
	  $damage = 8000;
	  $boost = 0;
	  $enemies = 1;
	    $enemystr = "enemy" . strval($enemies) . "name";
	    if (!empty($buddyrow[$enemystr])) {
	      $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $buddyrow[$enemystr] . "'");
	      $enemyrow = mysql_fetch_array($enemyresult);
	      $healthstr = "enemy" . strval($enemies) . "health";
	      $maxhealthstr = "enemy" . strval($enemies) . "maxhealth";
	      if (!empty($enemyrow)) { //Not a grist enemy.
		if ($enemyrow['massiveresist'] != 100 && $damage > (floor($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
		  $usagestr = $usagestr . $buddyrow[$enemystr] . " resists the massive damage!</br>";
		  $damage = floor(($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist']);
		}
	      }
	      if ($damage > $buddyrow[$healthstr]) $damage = $buddyrow[$healthstr] - 1;
	      //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $buddyrow[$healthstr]-$damage WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	      $buddyrow[$healthstr] = $buddyrow[$healthstr] - $damage;
	      $boost = $boost + floor($damage / 20);
	    }
	  //mysql_query("UPDATE `Players` SET `powerboost` = $buddyrow[powerboost]+$boost WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $buddyrow['powerboost'] += $boost; //Juuuust in case.
	  break;
	case "Doom":
	  $usagestr = $usagestr . "The slow, toxic inevitability of Death afflicts $buddyrow[enemy1name]</br>";
	  $enemies = 1;
	    $enemystr = "enemy" . strval($enemies) . "name";
	    $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $buddyrow[$enemystr] . "'");
	    $enemyrow = mysql_fetch_array($enemyresult);
	    $healthstr = "enemy" . strval($enemies) . "health";
	    $maxhealthstr = "enemy" . strval($enemies) . "maxhealth";
	    $damage = ceil($buddyrow[$maxhealthstr] * 0.0625 * $buddyrow['motifcounter']);
	    if (!empty($enemyrow)) { //Not a grist enemy.
	      if ($enemyrow['massiveresist'] != 100 && $damage > (floor($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
		$usagestr = $usagestr . $buddyrow[$enemystr] . " resists the massive damage!</br>";
		$damage = floor(($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist']);
	      }
	    }
	    if ($damage > $buddyrow[$healthstr]) $damage = $buddyrow[$healthstr] - 1;
	    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $buddyrow[$healthstr]-$damage WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $buddyrow[$healthstr] = $buddyrow[$healthstr]-$damage;
	  break;
	case "Rage":
	  if ($buddyrow['motifcounter'] == 1) {
	    $usagestr = $usagestr . "A deep, primordial fury wells up within $buddyrow[username].</br>";
	  } else {
	    $usagestr = $usagestr . "$buddyrow[username]'s fury slowly subsides.</br>";
	  }
	  $offenseboost = (10 - $buddyrow['motifcounter']) * 1200;
	  if ($offenseboost <= 0) {
	    $offenseboost = 0; //Rampdown is complete after ten turns.
	    //mysql_query("UPDATE `Players` SET `motifcounter` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Fraymotif over.
		$buddyrow['motifcounter'] = 0;
	  }
	  //mysql_query("UPDATE `Players` SET `offenseboost` = $offenseboost WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $buddyrow['offenseboost'] = $offenseboost; //Juuuust in case.
	  break;
	case "Void": //Suppresses special monster abilities. Handled in the monster ability area.
	  $usagestr = $usagestr . "The power of Void suppresses $buddyrow[enemy1name]'s abilities slightly.</br>";
	  break;
	case "Space":
	  $zillystr = "";
	  if (empty($luck)) $luck = 0; //Paranoia: If something dumb happens to luck, make it zero. Negatives are fine since rand can handle them anyhow.
	  $zilly = rand((1+floor($luck/3)),100); //Max luck increases chance of zillyweapon.
	  if ($zilly < 1) $zilly = 1; //Negative luck = FANCY SANTAS LOL
	  switch ($zilly) { //NOTE - Damage will be decided upon in this loop.
	  case 1:
	    $itemstr = "ZILLY SANTA";
	    $zillystr = "actually this thing is a piece of shit";
	    $damage = 1;
	  case 95:
	    $itemstr = "THE THISTLES OF ZILLYWICH";
	    $zillystr = "ZILLYWICH";
	    $damage = 15000; 
	    break;
	  case 96:
	    $itemstr = "THE FLINTLOCKS OF ZILLYHAU";
	    $zillystr = "ZILLYHAU";
	    $damage = 18000; 
	    break;
	  case 97:
	    $itemstr = "THE BLUNDERBUSS OF ZILLYWIGH";
	    $zillystr = "ZILLYWIGH";
	    $damage = 21000; 
	    break;
	  case 98:
	    $itemstr = "THE CUTLASS OF ZILLYWAIR";
	    $zillystr = "ZILLYWAIR";
	    $damage = 24000; 
	    break;
	  case 99:
	    $itemstr = "THE BATTLESPORK OF ZILLYWUT";
	    $zillystr = "ZILLYWUT";
	    $damage = 27000; 
	    break;
	  case 100:
	    $itemstr = "THE WARHAMMER OF ZILLYHOO";
	    $zillystr = "ZILLYHOO";
	    $damage = 30000; 
	    break;
	  default: //Code to randomly select an item goes here since we didn't get a zillyweapon. :(
	    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`power` = 9999;");
	    $items = 0;
	    while ($itemrow = mysql_fetch_array($itemresult)) {
	      $items++;
	    }
	    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`power` = 9999;");
	    $randomthing = 4; //Guaranteed to be random.
	    while ($randomthing != 1 && $items > 0) {
	      $itemrow = mysql_fetch_array($itemresult);
	      $randomthing = rand(1,$items);
	      $items--;
	    }
	    $itemstr = $itemrow['name'];
	    $damage = $itemrow['power'] + floor(($itemrow['aggrieve'] + $itemrow['aggress'] + $itemrow['assail'] + $itemrow['assault']) / 20); 
	    break;
	  }
	  $usagestr = $usagestr . $itemstr . " appears out of thin air and assaults $buddyrow[username], then disappears.</br>";
	  $enemies = 1;
	    $enemystr = "enemy" . strval($enemies) . "name";
	    $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $buddyrow[$enemystr] . "'");
	    $enemyrow = mysql_fetch_array($enemyresult);
	    $healthstr = "enemy" . strval($enemies) . "health";
	    $maxhealthstr = "enemy" . strval($enemies) . "maxhealth";
	    if (!empty($enemyrow)) { //Not a grist enemy.
	      if ($enemyrow['massiveresist'] != 100 && $damage > (floor($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
		$usagestr = $usagestr . $buddyrow[$enemystr] . " resists the massive damage!</br>";
		if ($zilly >= 95) {
		  $usagestr = $usagestr . "But THE GLORY OF " . $zillystr . " penetrates your foe's resistance!"; //Treat resistance as though it's half as effective.
		  if ($damage > floor(($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'] * 2)) $damage = floor(($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'] * 2);
		} else {
		  $damage = floor(($buddyrow[$maxhealthstr] / 100) * $enemyrow['massiveresist']);
		}
	      }
	    }
	    if ($damage > $buddyrow[$healthstr]) $damage = $buddyrow[$healthstr] - 1;
	    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $buddyrow[$healthstr]-$damage WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $buddyrow[$healthstr] = $buddyrow[$healthstr]-$damage;
	  break;
	case "Time": //Prevents boosts from ticking down as well, this is handled elsewhere. Prints "INFINITY TURNS" as the duration.
	  $usagestr = $usagestr . "The sonata extends and magnifies $buddyrow[username]'s temporary boosts.</br>";
	  $buddyrow['temppowerboost'] += 50;
	  $buddyrow['tempoffenseboost'] += 50;
	  $buddyrow['tempdefenseboost'] += 50;
	  //mysql_query("UPDATE `Players` SET `temppowerboost` = $buddyrow[temppowerboost] WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  //mysql_query("UPDATE `Players` SET `tempoffenseboost` = $buddyrow[tempoffenseboost] WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  //mysql_query("UPDATE `Players` SET `tempdefenseboost` = $buddyrow[tempdefenseboost] WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  break;
	default:
	  $message = $message . "Player aspect $buddyrow[Aspect] unrecognized. This is probably a bug.</br>";
	  break;
	}
	//mysql_query("UPDATE `Players` SET `motifcounter` = $buddyrow[motifcounter]+1 WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Increment the motif counter by one.
	$buddyrow['motifcounter']++;
	$bosslog = $bosslog . $usagestr;
      }
      //End-of-turn effects for player end here. Check end of turn on monsters here.

	$i = 1;
	  $enemystr = "enemy" . strval($i) . "name";
	  $enemyrowexists = False;
	  if ($buddyrow[$enemystr] != "") { //Enemy still exists after this round of combat. (sanity check: avoid weirdness if fragment does not exist)
	    $healthstr = "enemy" . strval($i) . "health";
	    $maxhealthstr = "enemy" . strval($i) . "maxhealth";
	    $powerstr = "enemy" . strval($i) . "power";
	    $maxpowerstr = "enemy" . strval($i) . "maxpower";
		$descstr = "enemy" . strval($i) . "desc";
		$statustr = "ENEMY" . strval($i) . ":";
		if (empty($_SESSION[$buddyrow[$enemystr]])) {
			$enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE '" . $buddyrow[$enemystr] . "' LIKE CONCAT ('%', `Enemy_Types`.`basename`, '%')");
			if ($enemyrow = mysql_fetch_array($enemyresult)) { //Enemy showed up in the table.
				$enemyrowexists = True;
				$_SESSION[$buddyrow[$enemystr]] = $enemyrow;
			}
		} else {
			$enemyrow = $_SESSION[$buddyrow[$enemystr]];
			$enemyrowexists = True;
		}
		if ($enemyrowexists == True) {
			//Only poison and bleeding are handled here, other status effects are processed at recombination.
			$poisonstr = ($statustr . "POISON");
			$bleedingstr = ($statustr . "BLEEDING");
			if (strpos($individualstatus, $poisonstr) !== False) { //This enemy is poisoned. (Format: POISON:<%chance>:<%severity>|
				$statusarray = explode("|", $individualstatus);
				$p = 0;
				$severity = 0;
				while (!empty($statusarray[$p])) {
					if (strpos($statusarray[$p], $poisonstr) !== False) { //This is a poison instance. Yes, they stack.
						$currentpoison = explode(":", $statusarray[$p]);
						$savingthrow = rand(1,100);
						if ($savingthrow + $enemyrow['resist_Doom'] > 100) { //Enemy throws off this instance of poisoning
							$removethis = $statusarray[$p] . "|";
							$individualstatus = preg_replace('/' . $removethis . '/', '', $individualstatus, 1);
							$bosslog = $bosslog . $userrow[$enemystr] . " fights off some of $buddyrow[username]'s poison!</br>";
						} else {
							$severity += floatval($currentpoison[2]);
							$bosslog = $bosslog . $userrow[$enemystr] . " loses some health to $buddyrow[username]'s poison!</br>";
						}
					}
					$p++;
				}
				$newhealth = floor($buddyrow[$healthstr] - ($buddyrow[$maxhealthstr] * ($severity / (150 * $chumroll)))); //Half damage from poison.
				if ($newhealth < 1) $newhealth = 1;
				$buddyrow[$healthstr] = $newhealth;
			}
			if (strpos($individualstatus, $bleedingstr) !== False) { //This enemy is bleeding. Drain off some health and power.
				$statusarray = explode("|", $individualstatus);
				$p = 0;
				$instances = 0;
				while (!empty($statusarray[$p])) {
					if (strpos($statusarray[$p], $bleedingstr) !== False) { //This is a bleed instance. Yes, they stack.
						$currentbleed = explode(":", $statusarray[$p]);				
						$bosslog = $bosslog . $buddyrow[$enemystr] . " loses some blood or blood analogue!</br>";
						if (intval($currentbleed[2]) <= 0) { //Bleeding has expired.
							$removethis = $statusarray[$p] . "|";
							$individualstatus = preg_replace('/' . $removethis . '/', '', $individualstatus, 1);
							$bosslog = $bosslog . "One of " . $userrow[$enemystr] . "'s wounds is no longer bleeding!</br>";
						} else {
							$instances++;
							$replacethis = $statusarray[$p] . "|";
							$replaceitwith = $currentbleed[0] . ":" . $currentbleed[1] . ":" . strval(intval($currentbleed[2]) - 1);
							$individualstatus = preg_replace('/' . $replacethis . '/', $replaceitwith, $individualstatus, 1);
						}
					}
					$p++;
				}
				//Bleed off 0.5% of their max health and power per instance every round
				$newhealth = floor($userrow[$healthstr] - ($userrow[$maxhealthstr] * ($instances / (200 * $chumroll))));
				$newpower = floor($userrow[$powerstr] - ($userrow[$maxpowerstr] * ($instances / (200 * $chumroll))));
				$buddyrow[$healthstr] = $newhealth;
				$buddyrow[$powerstr] = $newpower;
			}
		}
		      if ($buddyrow['motifcounter'] == 0 || $buddyrow['Aspect'] != "Void") { //Void III stops positive effects the enemy generates, at least on the part near the Void player.
			  //NOTE - Messages are handled in the post-recombination end of turn area.
	      if ($enemyrow['boostdrain'] > 0 && ($buddyrow['powerboost'] > 0 || $buddyrow['offenseboost'] > 0 || $buddyrow['defenseboost'] > 0 || $buddyrow['temppowerboost'] > 0
						  || $buddyrow['tempoffenseboost'] > 0 || $buddyrow['tempdefenseboost'] > 0)) { //Enemy drains a certain amount of boost per turn.
		if ($buddyrow['powerboost'] > 0) {
		  $newboost = $buddyrow['powerboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  //mysql_query("UPDATE `Players` SET `powerboost` = " . $newboost . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  $buddyrow['powerboost'] = $newboost;
		}
		if ($buddyrow['offenseboost'] > 0) {
		  $newboost = $buddyrow['offenseboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  //mysql_query("UPDATE `Players` SET `offenseboost` = " . $newboost . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  $buddyrow['offenseboost'] = $newboost;
		}
		if ($buddyrow['defenseboost'] > 0) {
		  $newboost = $buddyrow['defenseboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  //mysql_query("UPDATE `Players` SET `defenseboost` = " . $newboost . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  $buddyrow['defenseboost'] = $newboost;
		}
		if ($buddyrow['temppowerboost'] > 0) {
		  $newboost = $buddyrow['temppowerboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  //mysql_query("UPDATE `Players` SET `temppowerboost` = " . $newboost . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  $buddyrow['temppowerboost'] = $newboost;
		}
		if ($buddyrow['tempoffenseboost'] > 0) {
		  $newboost = $buddyrow['tempoffenseboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  //mysql_query("UPDATE `Players` SET `tempoffenseboost` = " . $newboost . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  		  		$buddyrow['tempoffenseboost'] = $newboost;
		}
		if ($buddyrow['tempdefenseboost'] > 0) {
		  $newboost = $buddyrow['tempdefenseboost'] - $enemyrow['boostdrain'];
		  if ($newboost < 0) $newboost = 0;
		  //mysql_query("UPDATE `Players` SET `tempdefenseboost` = " . $newboost . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  		$buddyrow['tempdefenseboost'] = $newboost;
		}
	      }
	      if ($enemyrow['invulndrain'] != 0 && $buddyrow['invulnerability'] > 0) { //Paranoia: Negate even if 1. (In case changes occur later)
				  //mysql_query("UPDATE `Players` SET `invulnerability` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		$buddyrow['invulnerability'] = 0;
	      }
	    }
	  }
	  	}
	  //End of turn effects for this player end here. We set all the modified combat values here instead of updating them when they change.
	  //This is done by pulling the user's row and checking for differences.
	  //Strife status string updated here. Special case because of how I originally did it, this will probably be fixed in future.
	  //mysql_query("UPDATE `Players` SET `strifestatus` = '$currentstatus' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  $buddyrow['strifestatus'] = $individualstatus;
	  $oldresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1;");
	  $colresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1;");
	  $oldrow = mysql_fetch_array($oldresult);
	  $buddyrow['Aspect_Vial'] += ($buddyrow['Gel_Viscosity'] * 0.1);
	  if (!empty($abilities[9])) { //Gogo Aspect Connection!
	  	$bosslog = $bosslog . $buddyrow['username'] . "'s $abilities[9]</br>";
	  	$buddyrow['Aspect_Vial'] += ($buddyrow['Gel_Viscosity'] * 0.05);
	  }
	  if ($buddyrow['Aspect_Vial'] >= $buddyrow['Gel_Viscosity']) $buddyrow['Aspect_Vial'] = $buddyrow['Gel_Viscosity'];
	  $megaquery = "UPDATE `Players` SET `strifestatus` = '" . mysql_real_escape_string($individualstatus) . "'";
	  while ($column = mysql_fetch_field($colresult)) {
		if (($buddyrow[$column->name] != $oldrow[$column->name]) && !strpos($column->name,"inv") && !strpos($column->name,"abstratus") && $column->name != "strifestatus") {
			//This entry has been changed, and is not an item or abstratus. (Addition of items and abstrati is handled via separate functions,
			//and we don't want to interfere with those)
			if (is_numeric($buddyrow[$column->name])) { //Item is a number. Convert to string.
				$newvalue = strval($buddyrow[$column->name]);
			} else { //Not a number. Place quotes around it.
				$newvalue = "'" . mysql_real_escape_string($buddyrow[$column->name]) . "'";
			}
			$megaquery = $megaquery . ", `" . $column->name . "` = " . $newvalue;
			//mysql_query("UPDATE `Players` SET `" . $column->name . "` = $newvalue WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			//This will produce a megaquery that does all these changes at once in the future.
	    }		
	  }
	  $megaquery = $megaquery . " WHERE `Players`.`username` = '" . $buddyrow['username'] . "' LIMIT 1 ;";
	  mysql_query($megaquery);
	  writeEnemydata($buddyrow);	  
}

$processedbuddies++;	 
}
if (!empty($meteorstrike) && !$meteorstrike) {
	$bosslog = $bosslog . "The meteor sails into orbit around Skaia.</br>";
}
//Recombination happens here.
$enemystr = "enemy1name";
$healthstr = "enemy1health";
$powerstr = "enemy1power";
$maxhealthstr = "enemy1maxhealth";
$maxpowerstr = "enemy1maxpower";
$sessioname = $userrow['session_name'];
$sessionresult = mysql_query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
$sessionrow = mysql_fetch_array($sessionresult);
$healthtotal = $sessionrow['sessionbosshealth'];
$powertotal = $sessionrow['sessionbosspower'];
$chumroll = 0;
$fighters = 0;
$damagedealt = 0;
$youlose = true;
$focusvalue = array();
$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $sessioname . "';");
$currentlog = $bosslog;
while ($buddyrow = mysql_fetch_array($sessionmates)) { //This loop accrues focus points and sums damage/power reduction inflicted.
	if ($buddyrow['sessionbossengaged'] == 1 && strcmp($buddyrow['enemydata'], "") != 0 && empty($downarray[$buddyrow['username']])) {
		$youlose = false;
		$buddyrow = parseEnemydata($buddyrow);
		$healthreduc = ($buddyrow['sessionbossinitialhealth'] - $buddyrow[$healthstr]);
		$powerreduc = ($buddyrow['sessionbossinitialpower'] - $buddyrow[$powerstr]);
		$focusvalue[$buddyrow['username']] = 0;
		$focusvalue[$buddyrow['username']] += $healthreduc; //Each point of damage is one point of focus
		$focusvalue[$buddyrow['username']]+= 3 * $powerreduc; //Each point of power reduction is three points of focus
		$healthtotal -= $healthreduc;
		$damagedealt += $healthreduc;
		$powertotal -= $powerreduc;
		$fighters++;
		$currentlog = $currentlog . $buddyrow['username'] . " inflicts $healthreduc damage";
		if ($powerreduc > 0) echo " and $powerreduc power reduction";
		$currentlog = $currentlog . " on $buddyrow[enemy1name]</br>";
		if ($focusvalue[$buddyrow['username']] < 0) $focusvalue[$buddyrow['username']] = 0;
		if (!empty($downarray[$buddyrow['username']])) {
			$buddyrow['enemydata'] = "";
		}
		$buddyrow = writeEnemydata($buddyrow);
	}
	$chumroll++;
}
if ($youlose) { //Everyone's been KOed! Whoops.
	$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $sessioname . "' = 1;");
	while ($buddyrow = mysql_fetch_array($sessionmates)) {
		mysql_query("UPDATE `Players` SET `sessionbossfocus` = 0, `enemydata` = '', `sessionbossengaged` = 0, `sessionbossleader` = 0 WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1;");
	}
	mysql_query("UPDATE `Sessions` SET `sessionbossname` = '' WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
	$lastround = mysql_real_escape_string($currentlog . $statuslog . "You have lost to $sessionrow[sessionbossname]! Enemies throughout the Incipisphere wink out of existence temporarily.</br>");
	$currentlog = $currentlog . $statuslog . "You have lost to $sessionrow[sessionbossname]! Enemies throughout the Incipisphere wink out of existence temporarily.</br>---------------</br>---------------</br>---------------</br>" . $sessionrow['combatlog'];
	$currentlog = mysql_real_escape_string($currentlog);
	mysql_query("UPDATE `Sessions` SET `combatlog` = '$currentlog' WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
	mysql_query("UPDATE `Sessions` SET `lastround` = '$lastround' WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
} else {
//Status effects are checked and applied here. Checking of current effects happens first (any status effect successfully applied
//kicks in at the end of the round)
$statuslog = "";
$bossresult = mysql_query("SELECT * FROM `Enemy_Types` WHERE `Enemy_Types`.`basename` = '$sessionrow[sessionbossname]' LIMIT 1;");
$bossrow = mysql_fetch_array($bossresult); //Need aspect resistances for dealing with statuses
$effectarray = explode("|", $currentstatus);
$distaction = false; //These two need to be confirmed: they affect focus generation later down.
$disoriented = false;
$chooser = 0;
foreach($effectarray as $effect) { //DATA SECTION: Handle end of turn status
	$currentarray = explode(':', $effect); //Note that what each array entry means depends on the effect.
	$removethis = $effect . "|";
	switch ($currentarray[1]) { //[0] is "ENEMY1"
	case TIMESTOP: //Remove it, it's been there a turn.
		$currentstatus = str_replace($removethis,"",$currentstatus);
		break;
	case WATERYGEL: //Roll a save
		$savingthrow = rand(1,100);
		if ($savingthrow > 80) { //Enemy throws off the debuff (Note that resistance doesn't factor here)
			$currentstatus = str_replace($removethis, '', $currentstatus); //Throws off all instances. We can't stack this debuff.
			$statuslog = $statuslog . $bossrow['basename'] . "'s health gel becomes more solid!</br>";
		}
	case SHRUNK: //Roll a save. Note that this status is usually permanent.
		$savingthrow = rand(1,100);
		if ($savingthrow > 95) {
			$currentstatus = str_replace($removethis, '', $currentstatus); //Throws off all instances. We can't stack this debuff.
			$statuslog = $statuslog . $bossrow['basename'] . " returns to normal size!</br>";
		}
		break;
	case MISFORTUNE: //Roll for effects, THEN roll a save.
		$misfortune = rand(1,100);
		if ($misfortune <= 2) { //2% chance but this seriously messes the boss up.
			$statuslog = $statuslog . "$bossrow[basename] is struck by lightning!</br>";
			$damage = 612 * 413;
			$healthtotal = floor($healthtotal - $damage);
		} elseif ($misfortune <= 10) {
			$statuslog = $statuslog . "The ground gives way beneath $bossrow[basename] and it plummets, shaking it up pretty bad when it lands.</br>";
			$damage = 413 * 100;
			$healthtotal = floor($healthtotal - $damage);
		} elseif ($misfortune <= 25) {
			$statuslog = $statuslog . "A meteor hits $bossrow[basename]. What are the chances?";
			if ($bossrow['basename'] == "The Black King") {
				$statuslog = $statuslog . " (actually, I guess pretty high, what with this whole Reckoning thing)";
			}
			echo "</br>";
			$damage = 30000;
			$healthtotal = floor($healthtotal - $damage);
		} elseif ($misfortune <= 50) {
			$statuslog = $statuslog . "$bossrow[basename] trips over and falls. Hilarious!</br>";
			$damage = floor($healthtotal / 100);
			$powerdown = 2200;
			$healthtotal = floor($healthtotal - $damage);
			$powertotal = floor($powertotal - $powerdown);
			if ($powertotal < 1) $powertotal = 1;
		} else { //At the moment, a roll of 51 or above means the enemy has evaded misfortune for this round.
			if ($bossrow['basename'] == "The Black King" && $misfortune <= 66) { //Additional chance for a meteor to hit him.
				$statuslog = $statuslog . "A meteor hits $bossrow[basename]. What are the chances?";
				$statuslog = $statuslog . " (actually, I guess pretty high, what with this whole Reckoning thing)";
				echo "</br>";
				$damage = 30000;
				$healthtotal = floor($healthtotal - $damage);
			}
		}
		$savingthrow = rand(1,100);
		if ($savingthrow >= 80) { //Enemy throws off the debuff
			$currentstatus = str_replace($removethis, '', $currentstatus); //Throws off all instances. We can't stack this debuff.
			$statuslog = $statuslog . $bossrow['basename'] . " appears less unlucky. This concept is just as visually nebulous as the idea that it appeared unlucky in the first place.</br>";
		}
		break;
	case HOPELESS: //Save is somewhat different
		$savingthrow = rand(1,100);
		$percent = ceil(($bossdamagedealt / (6666 * $chumroll)) * 200);
		if ($savingthrow <= $percent) { //More damage dealt = more chance to throw this off!
			$statuslog = $statuslog . $bossrow['basename'] . " is inspired by its latest attacks and shakes off the feeling of hopelessness!</br>";
			$currentstatus = str_replace($removethis, "", $currentstatus);
		}
		break;
	case DISORIENTED: //Now only lasts one turn, I'm afraid!
		$currentstatus = str_replace($removethis,"",$currentstatus);
		$disoriented = true;
		break;
	case DISTRACTED: //Still only lasts one turn.
		$currentstatus = str_replace($removethis,"",$currentstatus);
		$distaction = true;
		break;
	case ENRAGED: //Guaranteed to save if no damage is dealt.
		$savingthrow = rand(1,100);
		$resistfactor = ($bossrow['resist_Rage'] / 2); //Enemy's Rage resistance improves the save.
		//Note - If each fighter does 15k damage, the save is impossible with zero resistance.
		if ($savingthrow > (($damagedealt / ($fighters * 150)) - $resistfactor)) {
			$currentstatus = str_replace($removethis, "", $currentstatus);
			$statuslog = $statuslog . "$bossrow[basename] manages to calm down</br>";
		}
		break;
	case MELLOW: //Format is MELLOW:<%chance>|
		$savingthrow = rand(1,100);
		$resistfactor = ($bossrow['resist_Rage'] / 2); //Enemy's Rage resistance improves the save.
		//Note - If each fighter does 15k damage, the save is guaranteed with zero resistance.
		if ($savingthrow < (($damagedealt / ($fighters * 150)) + $resistfactor)) {
			$currentstatus = str_replace($removethis, "", $currentstatus);
			$statuslog = $statuslog . "$bossrow[basename] manages to get riled up again</br>";
		}
		break;
	case KNOCKDOWN: //Lasts one turn, get rid of it, yadda yadda.
		$currentstatus = str_replace($removethis,"",$currentstatus);
		break;
	case GLITCHED: //As SHRUNK, now has a small chance of being recovered from.
		$savingthrow = rand(1,100);
		if ($savingthrow > 95) {
			$currentstatus = str_replace($removethis, '', $currentstatus); //Throws off all instances. We can't stack this debuff.
			$statuslog = $statuslog . $bossrow['basename'] . " appears less buggy</br>";
		}
		break;
	default:
		break;
	}

}
$numberof = array();
$successful = array();
$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $sessioname . "' AND `Players`.`sessionbossengaged` = 1;");
while ($buddyrow = mysql_fetch_array($sessionmates)) {
	$individualstatus = $buddyrow['strifestatus'];
	$currentarray = explode('|', $individualstatus);
	foreach($currentarray as $tag) { //Increment each tagged thingy.
		if (strpos($tag, "POISON") === false && strpos($tag, "BLEEDING") === false) { //Handled in the individual end of rounds
			$thingy = explode(':', $tag);
			$tag = $thingy[1]; //[0] will be "ENEMY1:"
			if (empty($numberof[$tag])) {
				$numberof[$tag] = 1;
			} else {
				if (!empty($thingy[2])) {
					$numberof[$tag] += $thingy[2];
				} else {
					$numberof[$tag]++;
				}
			}
		}
	}
}
foreach($numberof as $effect => $count) {
	if ($count >= floor($chumroll * 1.2) + 1) { //Status effect succeeds
		$currentstatus = $currentstatus . "ENEMY1:" . $effect . "|";
		$successful[$effect] = true;
	switch ($effect) {
	case 'TIMESTOP': //Format is TIMESTOP:<%chance>|
		$statuslog = $statuslog . "$bossrow[basename] is frozen in time!</br>";
		break;
	case 'WATERYGEL': //Format is WATERYGEL:<%chance>|
		$statuslog = $statuslog . "$bossrow[basename] appears to have lost some viscosity.</br>";
		break;
	case 'SHRUNK': //Format is SIZECHANGE:<%chance>|
		$statuslog = $statuslog . "$bossrow[basename] suddenly shrinks!</br>";
		break;
	case 'MISFORTUNE': //Format is MISFORTUNE:<%chance>|
		$statuslog = $statuslog . "$bossrow[basename] looks unlucky. Hm? What does unlucky look like? How should I know?</br>";
		break;
	case 'HOPELESS': //Format is HOPELESS:<%chance>|
		$statuslog = $statuslog . "$bossrow[basename] looks dejected...</br>";
		break;
	case 'DISORIENTED': //Format is DISORIENTED:<%chance>:<duration>|
		$statuslog = $statuslog . "$bossrow[basename] is wandering around the battlefield in a daze!</br>";
		break;
	case 'DISTRACTED': //Format is DISTRACTED:<%chance>|
		$statuslog = $statuslog . "$bossrow[basename] looks away for a moment</br>";
		break;
	case 'ENRAGED': //Format is ENRAGED:<%chance>|
		$statuslog = $statuslog . "$bossrow[basename] looks really angry!</br>";
		break;
	case 'MELLOW': //Format is MELLOW:<%chance>|
		$statuslog = $statuslog . "$bossrow[basename] looks super chill, man...</br>";
		break;
	case 'KNOCKDOWN': //Format is KNOCKDOWN:<%multiplier>|. Knockdown chance depends on damage dealt.
		$statuslog = $statuslog . "$bossrow[basename] is sent flying by the force of your attacks!</br>";
		break;
	case 'GLITCHED': //Format is GLITCHED:<%chance>|. NOTE - Glitching is a permanent status ailment. Please balance accordingly.
		$statuslog = $statuslog . "$bossrow[basename] appears somewhat $glitchstr</br>";
		break;
	default:
		echo "$effect unrecognized. Please submit a bug report!</br>";
		break;
	}
	}
}
mysql_query("UPDATE `Sessions` SET `sessionbossstatus` = '$currentstatus' WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
if (!empty($successful)) { //A status effect succeeded
	$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $sessioname . "' AND `Players`.`sessionbossengaged` = 1;");
	while ($buddyrow = mysql_fetch_array($sessionmates)) {
		foreach($successful as $effect => $whocares) {
			$effect = "ENEMY1:" . $effect . "|";
			$buddyrow['strifestatus'] = str_replace($effect,"",$buddyrow['strifestatus']);
		}
		mysql_query("UPDATE `Players` SET `strifestatus` = '$buddyrow[strifestatus]' WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1;");
	}
}

//Committing of combined health/power occurs here.
if ($enemyrow['powerrecover'] > 0 && $powertotal < $sessionrow['sessionbossmaxpower']) { //Enemy recovers from some power loss every turn and has lost some.
		$newpower = $powertotal + ($enemyrow['powerrecover'] * $chumroll);
		if ($newpower > $sessionrow['sessionbossmaxpower']) $newpower = $sessionrow['sessionbossmaxpower'];
		//mysql_query("UPDATE `Players` SET `" . $powerstr . "` = " . $newpower . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		$pluspower = $newpower - $powertotal;
		$powertotal = $newpower;
		$statuslog = $statuslog . $bossrow['basename'] . " recovers $pluspower power</br>";
}
if ($enemyrow['healthrecover'] > 0 && $healthtotal < $sessionrow['sessionbossmaxhealth'] && $healthtotal > ($sessionrow['sessionbossmaxhealth'] / 20)) { //Enemy recovers from some health loss every turn and has lost some.
		$newhealth = $healthtotal + ($enemyrow['healthrecover'] * $chumroll);
		if ($newhealth > $sessionrow['sessionbossmaxhealth']) $newhealth = $sessionrow['sessionbossmaxhealth'];
		//mysql_query("UPDATE `Players` SET `" . $powerstr . "` = " . $newpower . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		$plushealth = $newhealth - $healthtotal;
		$healthtotal = $newhealth;
		$statuslog = $statuslog . $bossrow['basename'] . " regenerates $plushealth health</br>";
}
mysql_query("UPDATE `Sessions` SET `sessionbosshealth` = $healthtotal, `sessionbosspower` = $powertotal WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");

$sessionresult = mysql_query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
$sessionrow = mysql_fetch_array($sessionresult);
$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $sessioname . "' AND `Players`.`sessionbossengaged` = 1;");
$crushtheweaklings = rand(1,100);
if ($crushtheweaklings > 85) {
	$statuslog = $statuslog . $bossrow['basename'] . " decides to go after weaker players next round.</br>";
	$highest = max($focusvalue);
	foreach ($focusvalue as &$value) {
		$value = $highest - $value;
	}
	unset($value);
}
while ($buddyrow = mysql_fetch_array($sessionmates)) { //This loop normalizes and sets the focus for the next round
	if (empty($downarray[$buddyrow['username']]) && $buddyrow['sessionbossengaged'] == 1) {
		$buddyrow = parseEnemydata($buddyrow);
		if (array_sum($focusvalue) != 0) {
			$focus = floor(($focusvalue[$buddyrow['username']] / array_sum($focusvalue)) * 100);
		} else {
			$focus = floor(100 / $fighters);
		}
		if ($focus < 1) $focus = 1;
		if ($disoriented) {
			$randomthing = rand(1,100);
			if ($randomthing <= floor(100 / ($fighters - $chooser))) { //Player chosen
				$focus = 0;
				$statuslog = $statuslog . $buddyrow['enemy1name'] . " fails to attack $buddyrow[username] due to disorientation!</br>";
			} else {
				$chooser++;
			}
		}
		if ($distaction) $focus = floor($focus * 0.8);
		mysql_query("UPDATE `Players` SET `sessionbossfocus` = $focus WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1;");
		$buddyrow['sessionbossfocus'] = $focus;
		if ($buddyrow['sessionbossleader'] == 1) $newfocus = $focus; //Display tweak.
		//It then assigns the new health and power values to the player.
		$buddyrow[$healthstr] = floor(($healthtotal * $focus) / 100);
		$buddyrow[$powerstr] = floor(($powertotal * $focus) / 100);
		$buddyrow[$maxhealthstr] = floor(($sessionrow['sessionbossmaxhealth'] * $focus) / 100);
		$buddyrow[$maxpowerstr] = floor(($sessionrow['sessionbossmaxpower'] * $focus) / 100);
		$buddyrow['sessionbossinitialhealth'] = $buddyrow[$healthstr];
		$buddyrow['sessionbossinitialpower'] = $buddyrow[$powerstr];
		mysql_query("UPDATE `Players` SET `Players`.`sessionbossinitialhealth` = $buddyrow[sessionbossinitialhealth], `Players`.`sessionbossinitialpower` = $buddyrow[sessionbossinitialpower] WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1;");
		$buddyrow = writeEnemydata($buddyrow);
	}
}
if ($healthtotal <= 1500 * $fighters) $healthtotal = 0;
if ($healthtotal <= 0) {
	$statuslog = $statuslog . "$bossrow[basename] has been defeated!</br>";
	$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $sessioname . "' AND `Players`.`sessionbossengaged` = 1;");
	while ($buddyrow = mysql_fetch_array($sessionmates)) {
		mysql_query("UPDATE `Players` SET `sessionbossfocus` = 0, `enemydata` = '', `sessionbossengaged` = 0, `kingvote` = 0, `sessionbossleader` = 0 WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1;");
	}
	switch ($bossrow['basename']) { //DATA: Individual victory text and routine for each boss.
	case 'The Black King':
		mysql_query("UPDATE `Sessions` SET `sessionbossname` = '', `checkmate` = 1 WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
		$statuslog = $statuslog . "The Reckoning slows quickly to a halt as his lifeless body collapses, shrinking as the once mighty Black sceptre shatters into pieces. Skaia glows bright, as if in jubilation at your success. Congratulations! You have won Sburb!</br>";
		break;
	default:
		$currentlog = $currentlog . "However, we aren't sure what $bossrow[basename] is, so you'd better submit a bug report.</br>";
		break;
	}
}
$lastround = $currentlog . $statuslog;
$lastescaped = mysql_real_escape_string($lastround);
$currentlog = $currentlog . $statuslog . "---------------</br>" . $sessionrow['combatlog'];
$currentlog = mysql_real_escape_string($currentlog);
mysql_query("UPDATE `Sessions` SET `combatlog` = '$currentlog' WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
mysql_query("UPDATE `Sessions` SET `lastround` = '$lastescaped' WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
}
}

//require_once("footer.php");
?>