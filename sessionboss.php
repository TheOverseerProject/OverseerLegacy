<?php

function convertHybridb($workrow, $isbodygear) { //when wearable defense is calculated, it will go here if it's a hybrid (both a weapon and wearable) and cut the power down
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

require_once 'log.php';
require_once 'additem.php';
require_once 'monstermaker.php';
require_once 'includes/chaincheck.php';
require_once 'includes/glitches.php'; //For displaying glitchy nonsense
require_once("header.php");
require_once 'includes/fieldparser.php';
$max_enemies = 5; //Note that this is ALSO in monstermaker.php. That isn't ideal, but eh. (Also in striferesolve.php. Bluh. AND strifeselect.php. I should make a constants file at some stage)
if ($userrow['sessionbossleader'] == 1 && !empty($_POST['newleader'])) {
	$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "' AND `Players`.`username` = $_POST[newleader] LIMIT 1;");
	if ($buddyrow = mysql_fetch_array($sessionmates)) {
		mysql_query("UPDATE `Players` SET `sessionbossleader` = 0 WHERE `Players`.`username` = '$userrow[username]' LIMIT 1;");
		$userrow['sessionbossleader'] = 0;
		mysql_query("UPDATE `Players` SET `sessionbossleader` = 1 WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1;");
		echo "";
	} else {
		echo "ERROR: Player $buddyrow[username] not found in your session.</br>";
	}
}
if (empty($_SESSION['username'])) {
  echo "Log in to engage in strife.</br>";
  include("loginer.php");
} elseif ($userrow[$downstr] != 0) {
	echo "You are down!</br>";
	if ($userrow['sessionbossleader'] == 1) {
		echo '<form action="sessionboss.php" method="post">Pass leadership to: <input id="newleader" name="newleader" type="text" /><input type="submit" value="Make this player the leader" /></form></br>';
	}
} else {
$sessioname = $userrow['session_name'];
$sessionresult = mysql_query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
$sessionrow = mysql_fetch_array($sessionresult);
$userrow = parseEnemydata($userrow);
	if (!empty($_POST['offense'])) { //Changing actions.
		$newactive = $_POST['offense'];
		$newpassive = $_POST['defense'];
		mysql_query("UPDATE `Players` SET `sessionbossattack` = '$newactive', `sessionbossdefense` = '$newpassive' WHERE `Players`.`username` = '$username' LIMIT 1;");
	}
	if (!empty($_POST['execute']) && $userrow['sessionbossleader'] == 1) {
		require_once("sessionbossresolve.php");
		$sessionrow['lastround'] = $lastround;
	}
	if (!empty($_SESSION['username'])) {
echo '</body><head><script src="jquery.min.js"></script><style type="text/css"></style>
		<script src="raphael-min.js" type="text/javascript" charset="utf-8"></script>
		<script src="html5slider.js" type="text/javascript" charset="utf-8"></script>
		<script src="vials.js" type="text/javascript" charset="utf-8"></script>
		<script>window.onload = function () {';
		$sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "' AND `Players`.`sessionbossengaged` = 1;");
		while ($buddyrow = mysql_fetch_array($sessionmates)) {
		      if ($buddyrow['dreamingstatus'] == "Awake") {
			$healthcurrent = strval(floor(($buddyrow['Health_Vial'] / $buddyrow['Gel_Viscosity']) * 100));
		      } else {
			$healthcurrent = strval(floor(($buddyrow['Dream_Health_Vial'] / $buddyrow['Gel_Viscosity']) * 100));
		      }
		      $aspectcurrent = strval(floor(($buddyrow['Aspect_Vial'] / $buddyrow['Gel_Viscosity']) * 100));
		      $healthname = strtolower($buddyrow['colour']);
		      $aspectname = strtolower($buddyrow['Aspect']);
		      if (empty($healthname)) $healthname = "black";
		      if (empty($aspectname)) $aspectname = "doom";
			echo 'drawVial("health' . $buddyrow[username] . '", "' . $healthname . '", ' . strval($healthcurrent) . ');
			drawVial("aspect' . $buddyrow[username] . '", "' . $aspectname . '", ' . strval($aspectcurrent) . ');';
		}
echo '}</script></head><body>';
}

    echo '<div style = "float: right;"><a href="sessionbossbasics.php">The basics of session boss strife</a></div>';
	echo "Your opponent is $sessionrow[sessionbossname]</br>";
      $i = 1;
      while ($i <= 1) { //Session boss, only look at the first one...
	$enemystr = "enemy" . strval($i) . "name";
	$powerstr = "enemy" . strval($i) . "power";
	$healthstr = "enemy" . strval($i) . "health";
	$maxhealthstr = "enemy" . strval($i) . "maxhealth";
	$descstr = "enemy" . strval($i) . "desc";
	$statustr = "ENEMY" . strval($i) . ":";
	if ($userrow[$enemystr] != "") { //Enemy located
	  echo "Power: ";
	  echo strval($sessionrow['sessionbosspower']);
	  echo "</br> Health Vial: ";
	  $healthvial = floor(($sessionrow['sessionbosshealth'] / $sessionrow['sessionbossmaxhealth']) * 100); //Computes % of max HP remaining.
	  if ($healthvial == 0) $healthvial = 1;
	  echo strval($healthvial);
	  echo "%</br>";
	  echo "Your focus: $userrow[sessionbossfocus]%</br>";
	  echo $userrow[$descstr] . "</br>";
	  //This section stores the messages that appear when an enemy has a status effect. DATA SECTION: Status messages.
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "TIMESTOP|")) !== False) {
		echo "Frozen in Time: This enemy will not act this round.</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "WATERYGEL|")) !== False) {
		echo "Watery Health Gel: This enemy's Health Vial is easier to dislodge with basic attacks</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "SHRUNK|")) !== False) {
		echo "Shrunk: This enemy is at least twice as adorable as it was.</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "UNLUCKY|")) !== False) {
		echo "Unlucky: Bad things keep happening!</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "HOPELESS|")) !== False) {
		echo "Hopeless: This enemy does not believe in itself.</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "DISORIENTED")) !== False) {
		echo "Disoriented: This enemy is disoriented and will miss a player next round.</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "DISTRACTED|")) !== False) {
		echo "Distracted: This enemy is distracted and cannot focus as effectively.</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "ENRAGED|")) !== False) {
		echo "Enraged: This enemy is not paying sufficient attention to defending itself.</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "MELLOW|")) !== False) {
		echo "Mellowed Out: Whoa...this dude's, like...totally peaced out, man. He thinks attacking is, like, totally lame and won't hit as hard.</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "KNOCKDOWN|")) !== False) {
		echo "Knocked Over: This enemy will need to spend a turn getting back up.</br>";
	  }
	  if (strpos($sessionrow['sessionbossstatus'], ($statustr . "GLITCHED|")) !== False) {
		$glitchstr = generateGlitchString();
		echo "Glitched Out: This enemy $glitchstr</br>";
	  }
	}
	$i++;
      }
      echo "</br><div class='grister'>";
      $sessionmates = mysql_query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "' AND `Players`.`sessionbossengaged` = 1;");
      while ($buddyrow = mysql_fetch_array($sessionmates)) {
      	if ($buddyrow['dreamingstatus'] == "Awake") {
		$healthcurrent = strval(floor(($buddyrow['Health_Vial'] / $buddyrow['Gel_Viscosity']) * 100));
	} else {
		$healthcurrent = strval(floor(($buddyrow['Dream_Health_Vial'] / $buddyrow['Gel_Viscosity']) * 100));
      	}
      	$aspectcurrent = strval(floor(($buddyrow['Aspect_Vial'] / $buddyrow['Gel_Viscosity']) * 100));
      	echo '<div class="grist $buddyrow[username]">';
      	echo "$buddyrow[username]</br>";
      	if ($buddyrow['username'] == $username && !empty($newactive)) {
      		echo "$newactive/$newpassive</br>";
      	} else {
      		echo "$buddyrow[sessionbossattack]/$buddyrow[sessionbossdefense]</br>";
      	}
      	echo '<div id="health' . $buddyrow['username'] . '"></div>';
      	echo '<b>Health Vial: ' . strval($healthcurrent) . '%</b>';
      	echo '<div id="aspect' . $buddyrow['username'] . '"></div>';
      	echo '<b>Aspect Vial: ' . strval($aspectcurrent) . '%</b></br>';
      	echo "Focus: $buddyrow[sessionbossfocus]%</br>";
      	if ($buddyrow['combatconsume'] == 1) echo "Consumable action used!</br>";
      	echo '</div>';
      }
      echo "</div>";
      if ($userrow['invulnerability'] > 0) echo "</br>Invulnerability: " . strval($userrow['invulnerability']) . " rounds.";
      if ($userrow['powerboost'] > 0) echo "</br>Power boost (entire battle): $userrow[powerboost]";
      if ($userrow['offenseboost'] > 0) echo "</br>Offense boost (entire battle): $userrow[offenseboost]";
      if ($userrow['defenseboost'] > 0) echo "</br>Defense boost (entire battle): $userrow[defenseboost]";
      if ($userrow['powerboost'] < 0) echo "</br>Power penalty (entire battle): $userrow[powerboost]";
      if ($userrow['offenseboost'] < 0) echo "</br>Offense penalty (entire battle): $userrow[offenseboost]";
      if ($userrow['defenseboost'] < 0) echo "</br>Defense penalty (entire battle): $userrow[defenseboost]";
      if ($userrow['motifcounter'] > 0 && $userrow['Aspect'] == "Time") { //Eternal boosts
	if ($userrow['temppowerboost'] > 0) echo "</br>Power boost of $userrow[temppowerboost]: INFINITY rounds.";
	if ($userrow['tempoffenseboost'] > 0) echo "</br>Offense boost of $userrow[tempoffenseboost]: INFINITY rounds.";
	if ($userrow['tempdefenseboost'] > 0) echo "</br>Defense boost of $userrow[tempdefenseboost]: INFINITY rounds.";
	if ($userrow['temppowerboost'] < 0) echo "</br>Power penalty of $userrow[temppowerboost]: INFINITY rounds.";
	if ($userrow['tempoffenseboost'] < 0) echo "</br>Offense penalty of $userrow[tempoffenseboost]: INFINITY rounds.";
	if ($userrow['tempdefenseboost'] < 0) echo "</br>Defense penalty of $userrow[tempdefenseboost]: INFINITY rounds.";
      } else {
	if ($userrow['temppowerboost'] > 0) echo "</br>Power boost of $userrow[temppowerboost]: $userrow[temppowerduration] rounds.";
	if ($userrow['tempoffenseboost'] > 0) echo "</br>Offense boost of $userrow[tempoffenseboost]: $userrow[tempoffenseduration] rounds.";
	if ($userrow['tempdefenseboost'] > 0) echo "</br>Defense boost of $userrow[tempdefenseboost]: $userrow[tempdefenseduration] rounds.";
	if ($userrow['temppowerboost'] < 0) echo "</br>Power penalty of $userrow[temppowerboost]: $userrow[temppowerduration] rounds.";
	if ($userrow['tempoffenseboost'] < 0) echo "</br>Offense penalty of $userrow[tempoffenseboost]: $userrow[tempoffenseduration] rounds.";
	if ($userrow['tempdefenseboost'] < 0) echo "</br>Defense penalty of $userrow[tempdefenseboost]: $userrow[tempdefenseduration] rounds.";
      }
	//This is where we print messages for the player's more abnormal status effects.
	if (strpos($sessionrow['sessionbossstatus'], "PLAYER:NOCAP|") !== False) {
		echo "No damage cap: The damage you take from a single enemy is not currently capped.</br>";
	}
      if ($userrow['equipped'] != "") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['equipped']]) {
	    $mainrow = $row; //We save this to check weapon-specific bonuses to various commands.
	  }
	}
      }
      if ($userrow['offhand'] != "" && $userrow['offhand'] != "2HAND") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['offhand']]) {
	    $offrow = $row;
	  }
	}
      }
      if ($userrow['headgear'] != "") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['headgear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['headgear']]) {
	  	if ($row['hybrid'] == 1) $row = convertHybridb($row, false);
	    $headrow = $row; //We save this to check weapon-specific bonuses to various commands.
	  }
	}
      }
      if ($userrow['facegear'] != "" && $userrow['facegear'] != "2HAND") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['facegear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['facegear']]) {
	  	if ($row['hybrid'] == 1) $row = convertHybridb($row, false);
	    $facerow = $row;
	  }
	}
      }
      if ($userrow['bodygear'] != "") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['bodygear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['bodygear']]) {
	  	if ($row['hybrid'] == 1) $row = convertHybridb($row, true);
	    $bodyrow = $row; //We save this to check weapon-specific bonuses to various commands.
	  }
	}
      }
      if ($userrow['accessory'] != "") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['accessory']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['accessory']]) {
	  	if ($row['hybrid'] == 1) $row = convertHybridb($row, false);
	    $accrow = $row; //We save this to check weapon-specific bonuses to various commands.
	  }
	}
      }
      echo "</br>Currently selected actions: ";
      if (!empty($newactive)) {
      	echo $newactive . "/" . $newpassive;
      } else {
        echo $userrow['sessionbossattack'] . "/" . $userrow['sessionbossdefense'];
      }
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = 'Perfectly Generic Object'");
      $blankrow = mysql_fetch_array($itemresult);
      if (empty($mainrow) || $userrow['dreamingstatus'] != "Awake") $mainrow = $blankrow;
      if (empty($offrow) || $userrow['dreamingstatus'] != "Awake") $offrow = $blankrow;
      if (empty($headrow) || $userrow['dreamingstatus'] != "Awake") $headrow = $blankrow;
      if (empty($facerow) || $userrow['dreamingstatus'] != "Awake") $facerow = $blankrow;
      if (empty($bodyrow) || $userrow['dreamingstatus'] != "Awake") $bodyrow = $blankrow;
      if (empty($accrow) || $userrow['dreamingstatus'] != "Awake") $accrow = $blankrow;
      $aggrieve = $mainrow['aggrieve'] + ($offrow['aggrieve']/2) + $headrow['aggrieve'] + $facerow['aggrieve'] + $bodyrow['aggrieve'] + $accrow['aggrieve'];
      if ($aggrieve >= 0) $aggrieve = "+" . strval($aggrieve);
      $aggress = $mainrow['aggress'] + ($offrow['aggress']/2) + $headrow['aggress'] + $facerow['aggress'] + $bodyrow['aggress'] + $accrow['aggress'];
      if ($aggress >= 0) $aggress = "+" . strval($aggress);
      $assail = $mainrow['assail'] + ($offrow['assail']/2) + $headrow['assail'] + $facerow['assail'] + $bodyrow['assail'] + $accrow['assail'];
      if ($assail >= 0) $assail = "+" . strval($assail);
      $assault = $mainrow['assault'] + ($offrow['assault']/2) + $headrow['assault'] + $facerow['assault'] + $bodyrow['assault'] + $accrow['assault'];
      if ($assault >= 0) $assault = "+" . strval($assault);
      $abuse = $mainrow['abuse'] + ($offrow['abuse']/2) + $headrow['abuse'] + $facerow['abuse'] + $bodyrow['abuse'] + $accrow['abuse'];
      if ($abuse >= 0) $abuse = "+" . strval($abuse);
      $accuse = $mainrow['accuse'] + ($offrow['accuse']/2) + $headrow['accuse'] + $facerow['accuse'] + $bodyrow['accuse'] + $accrow['accuse'];
      if ($accuse >= 0) $accuse = "+" . strval($accuse);
      $abjure = $mainrow['abjure'] + ($offrow['abjure']/2) + $headrow['abjure'] + $facerow['abjure'] + $bodyrow['abjure'] + $accrow['abjure'];
      if ($abjure >= 0) $abjure = "+" . strval($abjure);
      $abstain = $mainrow['abstain'] + ($offrow['abstain']/2) + $headrow['abstain'] + $facerow['abstain'] + $bodyrow['abstain'] + $accrow['abstain'];
      if ($abstain >= 0) $abstain = "+" . strval($abstain);
      echo "</br>";
      if ($userrow['dreamingstatus'] == "Prospit") {
	echo '<form action="sessionboss.php" method="post" style="display: inline;">Select an aggressive action: <select name="offense">';
	if ($userrow['lastactive'] == "aggrieve") echo '<option value="aggrieve" selected>"AGGRIEVE" (' . $aggrieve . ')</option>';
	else echo '<option value="aggrieve">"AGGRIEVE" (' . $aggrieve . ')</option>';
	if ($userrow['lastactive'] == "aggress") echo '<option value="aggress" selected>"AGGRESS" (' . $aggress . ')</option>';
	else echo '<option value="aggress">"AGGRESS" (' . $aggress . ')</option>';
	if ($userrow['lastactive'] == "assail") echo '<option value="assail" selected>"ASSAIL" (' . $assail . ')</option>';
	else echo '<option value="assail">"ASSAIL" (' . $assail . ')</option>';
	if ($userrow['lastactive'] == "assault") echo '<option value="assault" selected>"ASSAULT" (' . $assault . ')</option>';
	else echo '<option value="assault">"ASSAULT" (' . $assault . ')</option>';
	echo '</select></br>';
	echo 'Select a passive action: <select name="defense">';
	if ($userrow['lastpassive'] == "abuse") echo '<option value="abuse" selected>"ABUSE" (' . $abuse . ')</option>'; 
	else echo '<option value="abuse">"ABUSE" (' . $abuse . ')</option>'; 
	if ($userrow['lastpassive'] == "accuse") echo '<option value="accuse" selected>"ACCUSE" (' . $accuse . ')</option>';
	else echo '<option value="accuse">"ACCUSE" (' . $accuse . ')</option>';
	if ($userrow['lastpassive'] == "abjure") echo '<option value="abjure" selected>"ABJURE" (' . $abjure . ')</option>'; 
	else echo '<option value="abjure">"ABJURE" (' . $abjure . ')</option>'; 
	if ($userrow['lastpassive'] == "abstain") echo '<option value="abstain" selected>"ABSTAIN" (' . $abstain . ')</option>';
	else echo '<option value="abstain">"ABSTAIN" (' . $abstain . ')</option>';
	echo '</select></br>';
	echo '<input type="hidden" name="redirect" value="redirect">';
	//echo '<input type="checkbox" name="repeat" value="repeat">AUTO-STRIFE! (Keep performing this action until you or an enemy dies, a turn passes with no damage, or 20 turns pass.)<br>';
	//DO NOT RE-ENABLE THE ABOVE. It fucks everything up. I'll test it personally some time later.
	echo '<input type="submit" value="Attack" /></form>';
      } else {
	echo '<form action="sessionboss.php" method="post" style="display: inline;">Select an aggressive action: <select name="offense">';
	if ($userrow['lastactive'] == "aggrieve") echo '<option value="aggrieve" selected>AGGRIEVE (' . $aggrieve . ')</option>';
	else echo '<option value="aggrieve">AGGRIEVE (' . $aggrieve . ')</option>';
	if ($userrow['lastactive'] == "aggress") echo '<option value="aggress" selected>AGGRESS (' . $aggress . ')</option>';
	else echo '<option value="aggress">AGGRESS (' . $aggress . ')</option>';
	if ($userrow['lastactive'] == "assail") echo '<option value="assail" selected>ASSAIL (' . $assail . ')</option>';
	else echo '<option value="assail">ASSAIL (' . $assail . ')</option>';
	if ($userrow['lastactive'] == "assault") echo '<option value="assault" selected>ASSAULT (' . $assault . ')</option>';
	else echo '<option value="assault">ASSAULT (' . $assault . ')</option>';
	echo '</select></br>';
	echo 'Select a passive action: <select name="defense">';
	if ($userrow['lastpassive'] == "abuse") echo '<option value="abuse" selected>ABUSE (' . $abuse . ')</option>'; 
	else echo '<option value="abuse">ABUSE (' . $abuse . ')</option>'; 
	if ($userrow['lastpassive'] == "accuse") echo '<option value="accuse" selected>ACCUSE (' . $accuse . ')</option>';
	else echo '<option value="accuse">ACCUSE (' . $accuse . ')</option>';
	if ($userrow['lastpassive'] == "abjure") echo '<option value="abjure" selected>ABJURE (' . $abjure . ')</option>'; 
	else echo '<option value="abjure">ABJURE (' . $abjure . ')</option>'; 
	if ($userrow['lastpassive'] == "abstain") echo '<option value="abstain" selected>ABSTAIN (' . $abstain . ')</option>';
	else echo '<option value="abstain">ABSTAIN (' . $abstain . ')</option>';
	echo '</select></br>';
	echo '<input type="hidden" name="redirect" value="redirect">';
	//echo '<input type="checkbox" name="repeat" value="repeat">AUTO-STRIFE! (Keep performing this action until you or an enemy dies, a turn passes with no damage, or 20 turns pass.)<br>';
	//DO NOT RE-ENABLE THE ABOVE. It fucks everything up. I'll test it personally some time later.
	echo '<input type="submit" value="Lock in these commands" /></form>';
      } //we won't even need the following now that auto-select is a thing!
      /*if ($userrow['lastactive'] != "" && $userrow['lastpassive'] != "") {
	echo '<form action="striferesolve.php" method="post">';
	echo '<input type="hidden" name="offense" value="' . $userrow['lastactive'] . '">';
	echo '<input type="hidden" name="defense" value="' . $userrow['lastpassive'] . '">';
	echo '<input type="hidden" name="redirect" value="redirect">';
	if ($userrow['dreamingstatus'] == "Prospit") {
	  echo '<input type="submit" value="Use commands from last round (&quot;' . $userrow['lastactive'] . '&quot; + &quot;' . $userrow['lastpassive'] . '&quot;)" /></form>';
	} else {
	  echo '<input type="submit" value="Use commands from last round (' . $userrow['lastactive'] . ' + ' . $userrow['lastpassive'] . ')" /></form>';
	}
      }*/
      echo "       CAN'T ABSCOND, BRO!";
      echo '</br>';
      if ($userrow['sessionbossleader'] == 1) {
      	echo '</br></br>';
      	echo '<form action="sessionboss.php" method="post" style="display: inline;"><input type="hidden" id="execute" name="execute" value="execute"><input type="submit" value="Execute the current strife round!"></form></br>';
      	echo '<form action="sessionboss.php" method="post">Pass leadership to: <input id="newleader" name="newleader" type="text" /><input type="submit" value="Make this player the leader" /></form></br>';
      }
      echo '<a href="/">Home</a> | <a href="portfolio.php">Check combat capabilities</a> | <a href="consumables.php">Use a consumable item</a> | ';
      echo '<a href="fraymotifs.php">Use a Fraymotif</a>';
      if (!empty($_SESSION['adjective'])) echo " | <a href='aspectpowers.php'>DO THE $_SESSION[adjective] THING</a> | <a href='roletech.php'>Peruse and select roletechs</a>";
	echo "</br>Last round:</br>";
	echo $sessionrow['lastround'];
}
require_once("footer.php");
?>