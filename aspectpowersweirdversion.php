<?php
if (empty($_SESSION['username'])) {
  echo "Log in to manipulate your aspect.</br>";
} elseif (empty($_SESSION['adjective'])) {
  echo "You have not accepted your title yet!</br>";
} else {
  $con = mysql_connect("localhost","theovers_DC","pi31415926535");
  if (!$con) {
    echo "Connection failed.\n";
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("theovers_HS", $con);
  //Pull the class and aspect rows, since these will be used a lot.
  $aspectresult = mysql_query("SELECT * FROM `Aspect_modifiers` WHERE `Aspect_modifiers`.`Aspect` = '$userrow[Aspect]';");
  $aspectrow = mysql_fetch_array($aspectresult);
  $classresult = mysql_query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$userrow[Class]';");
  $classrow = mysql_fetch_array($classresult);
  $unarmedpower = floor($userrow['Echeladder'] * (pow(($classrow['godtierfactor'] / 100),$userrow['Godtier'])));
  $factor = ((612 - $userrow['Echeladder']) / 611);
  $unarmedpower = ceil($unarmedpower * ((($classrow['level1factor'] / 100) * $factor) + (($classrow['level612factor'] / 100) * (1 - $factor)))); //Finish calculating unarmed power.
  //This will register which abilities the player has in $abilities. The standard check is if (!empty($abilities[ID of ability to be checked for>]))
  $abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') 
AND `Abilities`.`Rungreq` BETWEEN 0 AND $userrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $userrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
  $abilities = array(0 => "Null ability. No, not void.");
  while ($temp = mysql_fetch_array($abilityresult)) {
    $abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
    //as pulling the correct element out of the abilities array via the ID. Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
  }
  if (!empty($_POST['name'])) { //User creating a pattern.
    if ((intval($_POST['damage']) + intval($_POST['powerdown']) + intval($_POST['offenseup']) + intval($_POST['defenseup']) + intval($_POST['invuln']) + intval($_POST['heal']) + intval($_POST['aspectvial'])) == 100) { //Percentage checks out
      $slot = intval($_POST['slot']);
      if ($slot >= 1 && $slot <= 4) {
	$namestr = "pattern" . strval($slot) . "name";
	$damagestr = "pattern" . strval($slot) . "damage";
	$powerdownstr = "pattern" . strval($slot) . "powerdown";
	$offenseupstr = "pattern" . strval($slot) . "offenseup";
	$defenseupstr = "pattern" . strval($slot) . "defenseup";
	$temporarystr = "pattern" . strval($slot) . "temporary";
	$invulnstr = "pattern" . strval($slot) . "invuln";
	$healstr = "pattern" . strval($slot) . "heal";
	$maxtargetstr = "pattern" . strval($slot) . "maxtargets";
	$aspectvialstr = "pattern" . strval($slot) . "aspectvial";
	$_POST['damage'] = intval($_POST['damage']);
	$_POST['powerdown'] = intval($_POST['powerdown']);
	$_POST['offenseup'] = intval($_POST['offenseup']);
	$_POST['defenseup'] = intval($_POST['defenseup']);
	$_POST['temporary'] = intval($_POST['temporary']); //This goes to zero if it's dumb.
	$_POST['invuln'] = intval($_POST['invuln']);
	$_POST['heal'] = intval($_POST['heal']);
	$_POST['aspectvial'] = intval($_POST['aspectvial']);
	if ($_POST['damage'] < 0 || $_POST['powerdown'] < 0 || $_POST['offenseup'] < 0 || $_POST['defenseup'] < 0 || $_POST['invuln'] < 0 || $_POST['heal'] < 0 || $_POST['aspectvial'] < 0) {
	  echo "You may not set any values below 0%.</br>";
	} else {
	  if (intval($_POST['maxtargets']) < 1) $_POST['maxtargets'] = "1"; //Assume one target if this is derpy.
	  if (intval($_POST['maxtargets']) > 5) $_POST['maxtargets'] = "5";
	  mysql_query("UPDATE `Ability_Patterns` SET `$namestr` = '" . mysql_real_escape_string($_POST['name']) . "' WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Ability_Patterns` SET `$damagestr` = " . strval($_POST['damage']) . " WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Ability_Patterns` SET `$powerdownstr` = " . strval($_POST['powerdown']) . " WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Ability_Patterns` SET `$offenseupstr` = " . strval($_POST['offenseup']) . " WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Ability_Patterns` SET `$defenseupstr` = " . strval($_POST['defenseup']) . " WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Ability_Patterns` SET `$temporarystr` = " . strval($_POST['temporary']) . " WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Ability_Patterns` SET `$invulnstr` = " . strval($_POST['invuln']) . " WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Ability_Patterns` SET `$healstr` = " . strval($_POST['heal']) . " WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Ability_Patterns` SET `$maxtargetstr` = " . strval($_POST['maxtargets']) . " WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Ability_Patterns` SET `$aspectvialstr` = " . strval($_POST['aspectvial']) . " WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1 ;");
	  echo "Pattern $_POST[name] has been saved.</br>";
	}
      } else {
	echo "You must select a slot from 1 to 4 to save this pattern in!</br>";
      }
    } else {
      echo "The percentages must add to 100%!</br>";
    }
  }
  if (!empty($_POST['usepattern'])) { //User accessing a pattern.
    //Constants defining how much each function gets. 10000 equates to one per Echeladder rung. Less is better, as you can see.
    $powerupdivider = 10000;
    $healdivider = 1000;
    $damagedivider = 250;
    $powerdowndivider = 4000;
    $invulndivider = 2000000;
    $slot = intval($_POST['usepattern']);
    if ($slot < 1 || $slot > 4) { //Out of bounds.
      echo "That is not a valid ability.</br>";
    } else {
      $patternresult = mysql_query("SELECT * FROM `Ability_Patterns` WHERE `Ability_Patterns`.`username` = '$username'");
      $patternrow = mysql_fetch_array($patternresult);
      $namestr = "pattern" . strval($slot) . "name";
      if (empty($patternrow[$namestr])) {
	echo "That ability slot is not currently in use.</br>";
      } else {
	$damagestr = "pattern" . strval($slot) . "damage";
	$powerdownstr = "pattern" . strval($slot) . "powerdown";
	$offenseupstr = "pattern" . strval($slot) . "offenseup";
	$defenseupstr = "pattern" . strval($slot) . "defenseup";
	$temporarystr = "pattern" . strval($slot) . "temporary";
	$invulnstr = "pattern" . strval($slot) . "invuln";
	$healstr = "pattern" . strval($slot) . "heal";
	$maxtargetstr = "pattern" . strval($slot) . "maxtargets";
	$aspectvialstr = "pattern" . strval($slot) . "aspectvial";
	if ($userrow['enemy1name'] != "" || $userrow['enemy2name'] != "" || $userrow['enemy3name'] != "" || $userrow['enemy4name'] != "" || $userrow['enemy5name'] != "" || $userrow['aiding'] != "") { //Player strifing.
	  if (!empty($_POST['target']) && ($patternrow[$damagestr] == 0 || $patternrow[$powerdownstr] == 0)) {
	    $target = $_POST['target'];
	  } else {
	    $target = $username; //Default to targeting the user. Note that the user is necessarily targeted with positive effects where negative effects are also dumped on enemies.
	  }
	  if (($userrow['aiding'] != "" && ($patternrow[$damagestr] == 0 || $patternrow[$powerdownstr] == 0)) || $target != $username) {
	    $unarmedpower = floor($unarmedpower * ($classrow['passivefactor'] / 100)); //User is assisting and using a damaging ability OR user is targeting someone else: passive ability use.
	  } else {
	    $unarmedpower = floor($unarmedpower * ($classrow['activefactor'] / 100)); //User is targeting themselves and not currently assisting with a damage ability: active ability use
	  }
	  if (!empty($abilities[10]) && $target != $username) { //Hey! Listen! Multiply ability effectiveness by 1.2 (ID 10)
	    echo "$abilities[10]</br>";
	    $unarmedpower = ceil($unarmedpower * 1.2);
	  }
	  $targetresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$target';");
	  $targetrow = mysql_fetch_array($targetresult);
	  $cost = 100 - floor($patternrow[$aspectvialstr] * ($aspectrow['Aspect_vial'] / 100) * ($classrow['Aspect_vial'] / 100));
	  if ($cost < 10) $cost = 10; //No ability may cost less than 10% of the aspect vial.
	  $cost = floor(($cost / 100) * $userrow['Gel_Viscosity']);
	  if ($targetrow['session_name'] != $userrow['session_name']) {
	    echo "You may not use abilities on players not in your session.</br>";
	  } elseif ($targetrow['dreamingstatus'] != $userrow['dreamingstatus'] && $userrow['Godtier'] == 0) { //God tiers can buff ALL the things
	    echo "You cannot currently reach that player to use an ability on them!</br>";
	  } elseif ($targetrow['aiding'] != $username && $userrow['aiding'] != $targetrow['username'] && ($userrow['aiding'] != $targetrow['aiding'] || empty($userrow['aiding'])) && $targetrow['username'] != $username && $userrow['sessionbossengaged'] != 1) {
	    //User and target not in the same strife (either user aids target, target aids user, or user and target both aid the same person, or user targets themselves).
	    echo "While strifing, you may not use abilities on those not participating in your strife.</br>";
	  } elseif ($userrow['sessionbossengaged'] != $targetrow['sessionbossengaged']) { //Handle session boss case. Note that the user is guaranteed to be strifing.
	    echo "While strifing, you may not use abilities on those not participating in your strife.</br>";
	  } elseif ($userrow['combatconsume'] == 1) { //Already used a consumable or ability this round.
	    echo "You have already used a consumable or aspect ability during this round of strife!</br>";
	  } elseif ($cost > $userrow['Aspect_Vial']) {
	    echo "You do not have enough Aspect Vial remaining to use that ability!</br>";
	    $cost = 0;
	  } else {
	    mysql_query("UPDATE `Players` SET `combatconsume` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Set ability as used.
	    echo "You successfully use $patternrow[$namestr]"; //Have to print this first in order to print resistance messages after.
	    if ($patternrow[$aspectvialstr] != 0) mysql_query("UPDATE `Ability_Patterns` SET `aspectvialuses` = $patternrow[aspectvialuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;"); //Check for whether the aspect vial reduction option was used.
	    if ($target == $username) echo "!";
	    if ($target != $username) echo " on $target.";
	    echo "</br>";
	    if ($userrow['firstaspectuse'] == 0) {
	      if ($userrow['Echeladder'] < 612) {
		$result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $username . "' LIMIT 1;"); //Recalculate userrow so that we add to right values.
		//This is inefficient, but occurs once at most for every player so it should probably be fine.
		while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
		  if ($row['username'] == $username) { //Paranoia: Double-check.
		    $userrow = $row;
		  }
		}
		$rungs = 612 - $userrow['Echeladder'];
		if ($rungs > 10) $rungs = 10;
		mysql_query("UPDATE `Players` SET `firstaspectuse` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		echo "Your first use of THE $_SESSION[adjective] THING has earned you $rungs rungs on your Echeladder!";
		mysql_query("UPDATE `Players` SET `Echeladder` = $userrow[Echeladder]+$rungs WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		$hpup = 0; //Paranoia: Handle weird Echeladder values.
		if ($userrow['Echeladder'] == 1) $hpup = 125; //First rung: +5, rungs 3, 4, and 5: +10
		if ($userrow['Echeladder'] > 1 && $userrow['Echeladder'] < 5) $hpup = 150 - ((5 - $userrow['Echeladder']) * 5);
		if ($userrow['Echeladder'] >= 5) $hpup = (15 * $rungs); //Most rungs: +15.
		mysql_query("UPDATE `Players` SET `Gel_Viscosity` = $userrow[Gel_Viscosity]+$hpup WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$hpup WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		mysql_query("UPDATE `Players` SET `Dream_Health_Vial` = $userrow[Dream_Health_Vial]+$hpup WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		mysql_query("UPDATE `Players` SET `Aspect_Vial` = $userrow[Aspect_Vial]+$hpup WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		$rungcounter = $rungs - 1;
		$boondollars = 0;
		while ($rungcounter >= 0) {
		  $boondollars += ($userrow['Echeladder'] + $rungcounter) * 55;
		  $rungcounter--;
		}
		mysql_query("UPDATE `Players` SET `Boondollars` = $userrow[Boondollars]+$boondollars WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		$echeresult = mysql_query("SELECT * FROM Echeladders WHERE `Echeladders`.`username` = '" . $username . "'");
		$echerow = mysql_fetch_array($echeresult);
		$echestr = "rung" . strval($userrow['Echeladder'] + $rungs);
		if ($echerow[$echestr] != "") echo "</br>You scrabble madly up your Echeladder, coming to rest on rung: $echerow[$echestr]!";
		$abilities = mysql_query("SELECT * FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') 
AND `Abilities`.`Rungreq` BETWEEN $userrow[Echeladder]+1 AND $userrow[Echeladder]+$rungs AND `Abilities`.`Godtierreq` = 0 ORDER BY `Abilities`.`Rungreq` DESC;");
		while ($ability = mysql_fetch_array($abilities)) {
		  echo "</br>You obtain new roletech: Lv. $ability[Rungreq] $ability[Name]!";
		}
		if ($rungs < 10) echo "</br>You have at long last reached the top of your Echeladder!";
		echo "</br>";
		echo "Gel Viscosity: +$hpup";
		echo "!</br>Boondollars earned: $boondollars";
	      } else {
		mysql_query("UPDATE `Players` SET `firstaspectuse` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		echo "Your first use of THE $_SESSION[adjective] THING would provide you with Echeladder rungs, but you have already reached the top of yours.</br>";
	      }
	    }
	    //Damage and power reduction first.
	    if ($userrow['aiding'] != "") {
	      $aidresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$userrow[aiding]';");
	      $striferow = mysql_fetch_array($aidresult);
	    } else {
	      $striferow = $userrow;
	    }
	    if ($patternrow[$damagestr] > 0 || $patternrow[$powerdownstr] > 0) { //Calculate damage-y stuff here.
	      $targetcount = 0;
	      $i = 1;
	      while ($targetcount < $patternrow[$maxtargetstr] && $i <= $max_enemies) {
		$enemystr = "enemy" . strval($i) . "name";
		if ($striferow[$enemystr] != "") $targetcount++;
		$i++;
	      }
	      if ($patternrow[$maxtargetstr] > 1) mysql_query("UPDATE `Ability_Patterns` SET `multitargetuses` = $patternrow[multitargetuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;"); //Multiple targets selected, increment multitarget usage.
	      $multifactor = floor(($targetcount - 1) * ($classrow['Multitarget'] / 100) * ($aspectrow['Multitarget'] / 100)) + 1;
	      $i = 1;
	      $j = 1;
	      while ($j <= $patternrow[$maxtargetstr] && $i <= $max_enemies) { //j increments on successful enemy hit, i increments on any slot passed.
		$enemystr = "enemy" . strval($i) . "name";
		if ($striferow[$enemystr] != "") {
		  $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $userrow[$enemystr] . "'");
		  $enemyrow = mysql_fetch_array($enemyresult);
		  if ($patternrow[$damagestr] > 0) {
		    //Only increment "damaging ability used" value on the first enemy hit.
		    if ($j == 1) mysql_query("UPDATE `Ability_Patterns` SET `damageuses` = $patternrow[damageuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
		    $backup = $patternrow[$damagestr];
		    $patternrow[$damagestr] = floor(($patternrow[$damagestr] / 100) * ($unarmedpower / $damagedivider) * $aspectrow['Damage'] * $classrow['Damage'] * (1 / $multifactor));
		    $healthstr = "enemy" . strval($i) . "health";
		    $maxhealthstr = "enemy" . strval($i) . "maxhealth";
		    if (!empty($enemyrow)) { //Not a grist enemy.
		      if ($enemyrow['massiveresist'] != 100 && $patternrow[$damagestr] > (floor($striferow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
			echo $userrow[$enemystr] . " resists the massive damage!</br>";
			$patternrow[$damagestr] = floor($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'];
		      }
		    }
		    if ($patternrow[$damagestr] > $striferow[$healthstr]) $patternrow[$damagestr] = $striferow[$healthstr] - 1;
		    $newhealth = $striferow[$healthstr] - $patternrow[$damagestr];
		    mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $newhealth WHERE `Players`.`username` = '$striferow[username]' LIMIT 1 ;");
		    $patternrow[$damagestr] = $backup;
		  }
		  if ($patternrow[$powerdownstr] > 0) {
		    if ($j == 1) mysql_query("UPDATE `Ability_Patterns` SET `powerdownuses` = $patternrow[powerdownuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
		    $backup = $patternrow[$powerdownstr];
		    $patternrow[$powerdownstr] = floor(($patternrow[$powerdownstr] / 100) * ($unarmedpower / $powerdowndivider) * $aspectrow['Power_down'] * $classrow['Power_down'] * (1 / $multifactor));
		    $powerstr = "enemy" . strval($i) . "power";
		    if (!empty($enemyrow)) { //Not a grist enemy.
		      if ($enemyrow['reductionresist'] != 0 && $patternrow[$powerdownstr] > $enemyrow['reductionresist']) { //Enemy resists power reduction.
			echo $userrow[$enemystr] . " resists the power reduction!</br>";
			$patternrow[$powerdownstr] = $enemyrow['reductionresist'];
		      }
		    }
		    if ($patternrow[$powerdownstr] > $striferow[$powerstr]) $patternrow[$powerdownstr] = $striferow[$powerstr];
		    mysql_query("UPDATE `Players` SET `" . $powerstr . "` = $striferow[$powerstr]-$patternrow[$powerdownstr] WHERE `Players`.`username` = '$striferow[username]' LIMIT 1 ;");
		    $patternrow[$powerdownstr] = $backup;
		  }
		  $j++;
		}
		$i++;
	      }
	    }
	    $powerboost = 0;
	    $temp = False;
	    if ($patternrow[$temporarystr] != 0) { //Temporary boost. Scale it up accordingly.
	      mysql_query("UPDATE `Ability_Patterns` SET `temporaryuses` = $patternrow[temporaryuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
	      $temp = True;
	      $factor = 1 + ($aspectrow['Temporary'] / $patternrow[$temporarystr]);
	      $patternrow[$offenseupstr] = floor($patternrow[$offenseupstr] * $factor);
	      $patternrow[$defenseupstr] = floor($patternrow[$defenseupstr] * $factor);
	    }
	    if ($patternrow[$offenseupstr] >= $patternrow[$defenseupstr]) {
	      $powerboost = $patternrow[$defenseupstr];
	      $patternrow[$offenseupstr] = $patternrow[$offenseupstr] - $patternrow[$defenseupstr];
	      $patternrow[$defenseupstr] = 0;
	    } else {
	      $powerboost = $patternrow[$offenseupstr];
	      $patternrow[$defenseupstr] = $patternrow[$defenseupstr] - $patternrow[$offenseupstr];
	      $patternrow[$offenseupstr] = 0;
	    }
	    //Scale the boosts here.
	    $powerboost = floor(($powerboost / 100) * ($unarmedpower / $powerupdivider) * $aspectrow['Power_up'] * $classrow['Power_up']);
	    $patternrow[$offenseupstr] = floor(($patternrow[$offenseupstr] / 100) * ($unarmedpower / $powerupdivider) * $aspectrow['Offense_up'] * $classrow['Offense_up']);
	    $patternrow[$defenseupstr] = floor(($patternrow[$defenseupstr] / 100) * ($unarmedpower / $powerupdivider) * $aspectrow['Defense_up'] * $classrow['Defense_up']);
	    if ($patternrow[$offenseupstr] != 0) mysql_query("UPDATE `Ability_Patterns` SET `offenseupuses` = $patternrow[offenseupuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
	    if ($patternrow[$defenseupstr] != 0) mysql_query("UPDATE `Ability_Patterns` SET `defenseupuses` = $patternrow[defenseupuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
	    if ($powerboost != 0) mysql_query("UPDATE `Ability_Patterns` SET `powerupuses` = $patternrow[powerupuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
	    if ($temp == False) {
	      if ($patternrow[$offenseupstr] != 0) mysql_query("UPDATE `Players` SET `offenseboost` = $targetrow[offenseboost]+$patternrow[$offenseupstr] WHERE `Players`.`username` = '$target'");
	      if ($patternrow[$defenseupstr] != 0) mysql_query("UPDATE `Players` SET `defenseboost` = $targetrow[defenseboost]+$patternrow[$defenseupstr] WHERE `Players`.`username` = '$target'");
	      if ($powerboost != 0) mysql_query("UPDATE `Players` SET `powerboost` = $targetrow[powerboost]+$powerboost WHERE `Players`.`username` = '$target'");
	    } else { //Temp boosted.
	      if ($patternrow[$offenseupstr] > $targetrow['tempoffenseboost']) {
		mysql_query("UPDATE `Players` SET `tempoffenseboost` = " . strval($patternrow[$offenseupstr]) . " WHERE `Players`.`username` = '$target' LIMIT 1 ;");
	      }
	      if ($patternrow[$temporarystr] < $targetrow['tempoffenseduration'] || $targetrow['tempoffenseduration'] == 0) {
		mysql_query("UPDATE `Players` SET `tempoffenseduration` = " . strval($patternrow[$temporarystr]) . " WHERE `Players`.`username` = '$target' LIMIT 1 ;");
	      }
	      if ($patternrow[$defenseupstr] > $targetrow['tempdefenseboost']) {
		mysql_query("UPDATE `Players` SET `tempdefenseboost` = " . strval($patternrow[$defenseupstr]) . " WHERE `Players`.`username` = '$target' LIMIT 1 ;");
	      }
	      if ($patternrow[$temporarystr] < $targetrow['tempdefenseduration'] || $targetrow['tempdefenseduration'] == 0) {
		mysql_query("UPDATE `Players` SET `tempdefenseduration` = " . strval($patternrow[$temporarystr]) . " WHERE `Players`.`username` = '$target' LIMIT 1 ;");
	      }
	      if ($powerboost > $targetrow['temppowerboost']) {
		mysql_query("UPDATE `Players` SET `temppowerboost` = " . strval($powerboost) . " WHERE `Players`.`username` = '$target' LIMIT 1 ;");
	      }
	      if ($patternrow[$temporarystr] < $targetrow['temppowerduration'] || $targetrow['temppowerduration'] == 0) {
		mysql_query("UPDATE `Players` SET `temppowerduration` = " . strval($patternrow[$temporarystr]) . " WHERE `Players`.`username` = '$target' LIMIT 1 ;");
	      }
	    }
	    if ($patternrow[$invulnstr] != 0) {
	      mysql_query("UPDATE `Ability_Patterns` SET `invulnuses` = $patternrow[invulnuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
	      $patternrow[$invulnstr] = floor(($patternrow[$invulnstr] / 100) * ($unarmedpower / $invulndivider) * $aspectrow['Invulnerability'] * $classrow['Invulnerability']);
	      mysql_query("UPDATE `Players` SET `invulnerability` = $targetrow[invulnerability] + $patternrow[$invulnstr] WHERE `Players`.`username` = '$target' LIMIT 1 ;");
	    }
	    if ($patternrow[$healstr] != 0) {
	      mysql_query("UPDATE `Ability_Patterns` SET `healuses` = $patternrow[healuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
	      $patternrow[$healstr] = floor(($patternrow[$healstr] / 100) * ($unarmedpower / $healdivider) * $aspectrow['Heal'] * $classrow['Heal']);
	      $vial = "Dream_Health_Vial";
	      if ($targetrow['dreamingstatus'] == "Awake") $vial = "Health_Vial";
	      if ($patternrow[$healstr] + $targetrow[$vial] > $targetrow['Gel_Viscosity']) $patternrow[$healstr] = $targetrow['Gel_Viscosity'] - $targetrow[$vial];
	      mysql_query("UPDATE `Players` SET `$vial` = " . strval($targetrow[$vial] + $patternrow[$healstr]) . " WHERE `Players`.`username` = '$target' LIMIT 1 ;");
	    }
	    echo "</br>";
	    mysql_query("UPDATE `Players` SET `Aspect_Vial` = " . strval($userrow['Aspect_Vial'] - $cost) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  }
	} else { //Player not strifing.
	  if ($patternrow[$damagestr] != 0 || $patternrow[$powerdownstr] != 0 || $patternrow[$temporarystr] != 0 || $patternrow[$invulnstr] != 0) { //Ability only usable during combat.
	    echo "You may not use that pattern while not engaged in strife.</br>";
	  } else {
	    if (!empty($_POST['target'])) {
	      $target = $_POST['target'];
	      $unarmedpower = ($unarmedpower * ($classrow['passivefactor'] / 100)); //Buffing others counts as passive
	    } else {
	      $target = $username; //Default to targeting the user.
	      $unarmedpower = ($unarmedpower * ($classrow['activefactor'] / 100)); //Buffing self counts as active
	    }
	    if (!empty($abilities[10]) && $target != $username) { //Hey! Listen! Multiply ability effectiveness by 1.2 (ID 10)
	      echo "$abilities[10]</br>";
	      $unarmedpower = ceil($unarmedpower * 1.2);
	    }
	    $targetresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$target';");
	    $targetrow = mysql_fetch_array($targetresult);
	    $cost = 100 - floor($patternrow[$aspectvialstr] * ($aspectrow['Aspect_vial'] / 100) * ($classrow['Aspect_vial'] / 100));
	    if ($cost < 10) $cost = 10; //No ability may cost less than 10% of the aspect vial.
	    $cost = floor(($cost / 100) * $userrow['Gel_Viscosity']);
	    if ($targetrow['session_name'] != $userrow['session_name']) {
	      echo "You may not use abilities on players not in your session.</br>";
	    } elseif ($targetrow['dreamingstatus'] != $userrow['dreamingstatus'] && $userrow['Godtier'] == 0) {
	      echo "You cannot currently reach that player to use an ability on them!</br>";
	    } elseif ($targetrow['noassist'] == 1) {
	      echo "That player cannot currently be assisted.</br>";
	    } elseif ($cost > $userrow['Aspect_Vial']) {
	      echo "You do not have enough Aspect Vial remaining to use that ability!</br>";
	      $cost = 0;
	    } else {
	      if ($patternrow[$aspectvialstr] != 0) mysql_query("UPDATE `Ability_Patterns` SET `aspectvialuses` = $patternrow[aspectvialuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;"); //Check aspect vial reduction.
	      $powerboost = 0;
	      if ($patternrow[$offenseupstr] >= $patternrow[$defenseupstr]) {
		$powerboost = $patternrow[$defenseupstr];
		$patternrow[$offenseupstr] = $patternrow[$offenseupstr] - $patternrow[$defenseupstr];
		$patternrow[$defenseupstr] = 0;
	      } else {
		$powerboost = $patternrow[$offenseupstr];
		$patternrow[$defenseupstr] = $patternrow[$defenseupstr] - $patternrow[$offenseupstr];
		$patternrow[$offenseupstr] = 0;
	      }
	      //Scale the boosts here.
	      $powerboost = floor(($powerboost / 100) * ($unarmedpower / $powerdivider) * $aspectrow['Power_up'] * $classrow['Power_up']);
	      $patternrow[$offenseupstr] = floor(($patternrow[$offenseupstr] / 100) * ($unarmedpower / $powerdivider) * $aspectrow['Offense_up'] * $classrow['Offense_up']);
	      $patternrow[$defenseupstr] = floor(($patternrow[$defenseupstr] / 100) * ($unarmedpower / $powerdivider) * $aspectrow['Defense_up'] * $classrow['Defense_up']);
	      if ($patternrow[$offenseupstr] != 0) {
		mysql_query("UPDATE `Players` SET `offenseboost` = $targetrow[offenseboost]+$patternrow[$offenseupstr] WHERE `Players`.`username` = '$target'");
		mysql_query("UPDATE `Ability_Patterns` SET `offenseupuses` = $patternrow[offenseupuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
	      }
	      if ($patternrow[$defenseupstr] != 0) {
		mysql_query("UPDATE `Players` SET `defenseboost` = $targetrow[defenseboost]+$patternrow[$defenseupstr] WHERE `Players`.`username` = '$target'");
		mysql_query("UPDATE `Ability_Patterns` SET `defenseupuses` = $patternrow[defenseupuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
	      }
	      if ($powerboost != 0) {
		mysql_query("UPDATE `Players` SET `powerboost` = $targetrow[powerboost]+$powerboost WHERE `Players`.`username` = '$target'");
		mysql_query("UPDATE `Ability_Patterns` SET `powerupuses` = $patternrow[powerupuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
	      }
	      if ($patternrow[$healstr] != 0) {
		mysql_query("UPDATE `Ability_Patterns` SET `healuses` = $patternrow[healuses]+1 WHERE `Ability_Patterns`.`username` = '$username' LIMIT 1;");
		$patternrow[$healstr] = floor(($patternrow[$healstr] / 100) * ($unarmedpower / $healdivider) * $aspectrow['Heal'] * $classrow['Heal']);
		$vial = "Dream_Health_Vial";
		if ($targetrow['dreamingstatus'] == "Awake") $vial = "Health_Vial";
		if ($patternrow[$healstr] + $targetrow[$vial] > $targetrow['Gel_Viscosity']) $patternrow[$healstr] = $targetrow['Gel_Viscosity'] - $targetrow[$vial];
		mysql_query("UPDATE `Players` SET `$vial` = " . strval($targetrow[$vial] + $patternrow[$healstr]) . " WHERE `Players`.`username` = '$target' LIMIT 1 ;");
	      }
	      echo "You successfully use $patternrow[$namestr] on ";
	      if ($target == $username) echo "yourself";
	      if ($target != $username) echo "$target";
	      echo ".</br>";
	      if ($userrow['firstaspectuse'] == 0) {
		if ($userrow['Echeladder'] < 612) {
		  $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $username . "' LIMIT 1;"); //Recalculate userrow so that we add to right values.
		  //This is inefficient, but occurs once at most for every player so it should probably be fine.
		  while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
		    if ($row['username'] == $username) { //Paranoia: Double-check.
		      $userrow = $row;
		    }
		  }
		  $rungs = 612 - $userrow['Echeladder'];
		  if ($rungs > 10) $rungs = 10;
		  mysql_query("UPDATE `Players` SET `firstaspectuse` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  echo "Your first use of THE $_SESSION[adjective] THING has earned you $rungs rungs on your Echeladder!";
		  mysql_query("UPDATE `Players` SET `Echeladder` = $userrow[Echeladder]+$rungs WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  $hpup = 0; //Paranoia: Handle weird Echeladder values.
		  if ($userrow['Echeladder'] == 1) $hpup = 125; //First rung: +5, rungs 3, 4, and 5: +10
		  if ($userrow['Echeladder'] > 1 && $userrow['Echeladder'] < 5) $hpup = 150 - ((5 - $userrow['Echeladder']) * 5);
		  if ($userrow['Echeladder'] >= 5) $hpup = (15 * $rungs); //Most rungs: +15.
		  mysql_query("UPDATE `Players` SET `Gel_Viscosity` = $userrow[Gel_Viscosity]+$hpup WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$hpup WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `Dream_Health_Vial` = $userrow[Dream_Health_Vial]+$hpup WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `Aspect_Vial` = $userrow[Aspect_Vial]+$hpup WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  $rungcounter = $rungs - 1;
		  $boondollars = 0;
		  while ($rungcounter >= 0) {
		    $boondollars += ($userrow['Echeladder'] + $rungcounter) * 55;
		    $rungcounter--;
		  }
		  mysql_query("UPDATE `Players` SET `Boondollars` = $userrow[Boondollars]+$boondollars WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  $echeresult = mysql_query("SELECT * FROM Echeladders WHERE `Echeladders`.`username` = '" . $username . "'");
		  $echerow = mysql_fetch_array($echeresult);
		  $echestr = "rung" . strval($userrow['Echeladder'] + $rungs);
		  if ($echerow[$echestr] != "") echo "</br>You scrabble madly up your Echeladder, coming to rest on rung: $echerow[$echestr]!";
		  $abilities = mysql_query("SELECT * FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') 
AND `Abilities`.`Rungreq` BETWEEN $userrow[Echeladder]+1 AND $userrow[Echeladder]+$rungs AND `Abilities`.`Godtierreq` = 0 ORDER BY `Abilities`.`Rungreq` DESC;");
		  while ($ability = mysql_fetch_array($abilities)) {
		    echo "</br>You obtain new roletech: Lv. $ability[Rungreq] $ability[Name]!";
		  }
		  if ($rungs < 10) echo "</br>You have at long last reached the top of your Echeladder!";
		  echo "</br>";
		  echo "Gel Viscosity: +$hpup";
		  echo "!</br>Boondollars earned: $boondollars</br>";
		} else {
		  mysql_query("UPDATE `Players` SET `firstaspectuse` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  echo "Your first use of THE $_SESSION[adjective] THING would provide you with Echeladder rungs, but you have already reached the top of yours.</br>";
		}
	      }
	      mysql_query("UPDATE `Players` SET `Aspect_Vial` = " . strval($userrow['Aspect_Vial'] - $cost) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    }
	  }
	}
      }
    }
  }
  if (empty($cost)) $cost = 0;
  $userrow['Aspect_Vial'] = $userrow['Aspect_Vial'] - $cost;
  $patternresult = mysql_query("SELECT * FROM `Ability_Patterns` WHERE `Ability_Patterns`.`username` = '$username'");
  $patternrow = mysql_fetch_array($patternresult);
  echo '<a href="aspecthelp.php">Information on aspect patterns and powers</a> | <a href="strife.php">Strife</a></br>';
  echo "Aspect vial: " . strval(floor(($userrow['Aspect_Vial'] / $userrow['Gel_Viscosity']) * 100)) . "%</br>";
  echo "Your currently defined patterns:</br>";
  $slot = 1;
  $max_patterns = 4; //LOL
  while ($slot <= $max_patterns) {
    $namestr = "pattern" . strval($slot) . "name";
    if (!empty($patternrow[$namestr])) {
      $damagestr = "pattern" . strval($slot) . "damage";
      $powerdownstr = "pattern" . strval($slot) . "powerdown";
      $offenseupstr = "pattern" . strval($slot) . "offenseup";
      $defenseupstr = "pattern" . strval($slot) . "defenseup";
      $temporarystr = "pattern" . strval($slot) . "temporary";
      $invulnstr = "pattern" . strval($slot) . "invuln";
      $healstr = "pattern" . strval($slot) . "heal";
      $maxtargetstr = "pattern" . strval($slot) . "maxtargets";
      $aspectvialstr = "pattern" . strval($slot) . "aspectvial";
      echo "Pattern $slot: $patternrow[$namestr]</br>";
      if ($patternrow[$damagestr] != 0) echo "Damage: $patternrow[$damagestr]%</br>";
      if ($patternrow[$powerdownstr] != 0) echo "Power reduction: $patternrow[$powerdownstr]%</br>";
      if ($patternrow[$offenseupstr] != 0) echo "Offense boost: $patternrow[$offenseupstr]%</br>";
      if ($patternrow[$defenseupstr] != 0) echo "Defense boost: $patternrow[$defenseupstr]%</br>";
      if ($patternrow[$temporarystr] != 0) echo "Temporary boost: $patternrow[$temporarystr] rounds</br>";
      if ($patternrow[$invulnstr] != 0) echo "Invulnerability: $patternrow[$invulnstr]%</br>";
      if ($patternrow[$healstr] != 0) echo "Healing: $patternrow[$healstr]%</br>";
      if ($patternrow[$maxtargetstr] != 0) echo "Maximum number of targets: $patternrow[$maxtargetstr]</br>";
      if ($patternrow[$aspectvialstr] != 0) echo "Points allocated to reduce cost: $patternrow[$aspectvialstr]%</br>";
      $cost = 100 - floor($patternrow[$aspectvialstr] * ($aspectrow['Aspect_vial'] / 100) * ($classrow['Aspect_vial'] / 100));
      if ($cost < 10) $cost = 10; //No ability may cost less than 10% of the aspect vial.
      echo "Cost: $cost%</br>";
      echo '<form action="aspectpowers.php" method="post"><input type="hidden" name="usepattern" value="' . strval($slot) . '">';
      if ($patternrow[$damagestr] == 0 && $patternrow[$powerdownstr] == 0) echo 'Target a player (defaults to self): <input type="text" id="target" name="target"></br>'; //Strictly buffing ability.
      echo '<input type="submit" value="Use this pattern"></form>';
      echo "</br>";
    }
    $slot++;
  }
  echo 'Aspect pattern editor v0.0.1a. Enter percentages to allocate to each possible effect you can perform with basic aspect manipulation.</br>';
  echo 'You may have up to four patterns and they may be edited at any time.</br>';
  echo '<form action="aspectpowers.php" method="post">';
  echo 'Name of ability: <input id="name" name="name" type="text" /></br>';
  echo 'Slot for this ability: <select name="slot"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option></select></br>';
  echo 'Percentage of ability allocated to:</br>';
  echo 'Damage: <input id="damage" name="damage" type="text" />%</br>';
  echo 'Power reduction: <input id="powerdown" name="powerdown" type="text" />%</br>';
  echo 'Offense boosting: <input id="offenseup" name="offenseup" type="text" />%</br>';
  echo 'Defense boosting: <input id="defenseup" name="defenseup" type="text" />%</br>';
  echo 'Invulnerability: <input id="invuln" name="invuln" type="text" />%</br>';
  echo 'Healing: <input id="heal" name="heal" type="text" />%</br>';
  echo 'Reducing cost: <input id="aspectvial" name="aspectvial" type="text" />%</br></br>';
  echo 'Maximum number of targets: <input id="maxtargets" name="maxtargets" type="text" /></br>';
  echo 'Temporary boost (leave empty to make boost entire battle rather than a number of turns): <input id="temporary" name="temporary" type="text" /></br>';
  echo '<input type="submit" value="Create it!">';
  //Code for pattern hints will go here.
  $hint = False;
  if ($patternrow['damageuses'] >= 5) {
    $hint = True;
    $effectiveness = $aspectrow['Damage'] * $classrow['Damage']; //Note that 10k is the average.
  }
}