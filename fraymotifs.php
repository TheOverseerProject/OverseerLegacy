<?php
require_once("header.php");
require_once("includes/fieldparser.php");
if (empty($_SESSION['username'])) {
  echo "Log in to purchase and use fraymotifs.</br>";
} else {
	$userrow = parseEnemydata($userrow);
  //This will register which abilities the player has in $abilities. The standard check is if (!empty($abilities[ID of ability to be checked for>]))
  //We check abilities in this file because some interact with fraymotifs.
  $abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') 
AND `Abilities`.`Rungreq` BETWEEN 0 AND $userrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $userrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
  $abilities = array(0 => "Null ability. No, not void.");
  while ($temp = mysql_fetch_array($abilityresult)) {
    $abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
    //as pulling the correct element out of the abilities array via the ID. Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
  }
  //Some fraymotifs use the player's current base attacking power (Echeladder + power boosts + weapons). This is calculated here and stored in $powerlevel
  if ($userrow['equipped'] != "") {
    $itemname = str_replace("'", "\\\\''", $userrow[$userrow['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
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
  if ($userrow['offhand'] != "" && $userrow['offhand'] != $equippedmain && $userrow['offhand'] != "2HAND") {
    $itemname = str_replace("'", "\\\\''", $userrow[$userrow['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
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
  $max_enemies = 5; //I'M SO GOOD AT CODING. -_-
  //Begin fraymotif purchasing code here.
  if (!empty($_POST['buymotif'])) {
    $purchase = $_POST['buymotif'];
    switch ($purchase) {
    case "solo1":
      $requirement = 10000000;
      break;
    case "solo2":
      $requirement = 100000000;
      break;
    case "solo3":
      $requirement = 1000000000;
      break;
    default:
      $requirement = 10000000000;
      break;
    }
    if ($userrow['Boondollars'] < $requirement) { //Player cannot afford motif.
      echo "You can't afford to purchase that!</br>";
    } else {
      echo "You successfully purchase the fraymotif!</br>";
      mysql_query("UPDATE `Players` SET `" . $purchase . "` = 1 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `Boondollars` = $userrow[Boondollars]-$requirement WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      $newmotif = $purchase;
    }
  }
  //End fraymotif purchasing code here. Begin fraymotif usage code here.
  //NOTE - The below stack of switch statements is effectively where fraymotif effect is stored for lookup.
  if (!empty($_POST['usemotif'])) {
    $usagestr = "";
    $fail = False;
    if ($userrow['fraymotifuses'] == 0) { //User is out of fraymotifs
      echo "You have no more fraymotif uses left!</br>";
      $fail = True;
    } elseif ($userrow[$_POST['usemotif']] == 0) { //HAAAAAX!
      echo "Niice try, iidiiot.</br>";
      $fail = True;
    } elseif ($userrow['combatmotifuses'] == 0) {
      echo "Okay, I'm sure you thought getting into a fight and waiting a day for all your fraymotifs to come back DURING that fight was clever. And it kind of was. Unfortunately, it's also a stupid tactic to need to balance around so you're not allowed to do it.</br>";
      $fail = True;
    } else {
      //Check for enemy flags and stuff here. The first two arrays are used to store the new values for health/power resulting from a fraymotif.
      $monsterpowers = array(1 => $userrow['enemy1power'], $userrow['enemy2power'], $userrow['enemy3power'], $userrow['enemy4power'], $userrow['enemy5power']);
      $monsterhealth = array(1 => $userrow['enemy1health'], $userrow['enemy2health'], $userrow['enemy3health'], $userrow['enemy4health'], $userrow['enemy5health']);
      $oldpowers = array(1 => $userrow['enemy1power'], $userrow['enemy2power'], $userrow['enemy3power'], $userrow['enemy4power'], $userrow['enemy5power']);
      $oldhealth = array(1 => $userrow['enemy1health'], $userrow['enemy2health'], $userrow['enemy3health'], $userrow['enemy4health'], $userrow['enemy5health']);
      $reductionresist = array(1 => 0, 0, 0, 0, 0);
      $massiveresist = array(1 => 100, 100, 100, 100, 100);
      $i = 1;
      while ($i <= $max_enemies) {
	$enemystr = "enemy" . strval($i) . "name";
	$enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $userrow[$enemystr] . "'");
	if ($enemyrow = mysql_fetch_array($enemyresult)) { //Didn't have grist appended to it.
	  if ($enemyrow['reductionresist'] != 0) { //Enemy resists having their power reduced.
	    $reductionresist[$i] = $enemyrow['reductionresist'];
	  }
	  if ($enemyrow['massiveresist'] != 100) { //Enemy resists massive damage.
	    $massiveresist[$i] = $enemyrow['massiveresist'];
	  }
	}
	$i++;
      }
      $powerlevel = ($userrow['Echeladder'] * pow(2,$userrow['Godtier'])) + $userrow['powerboost'] + $userrow['temppowerboost'] + $mainpower + $offpower;
      $offensepower = $powerlevel + $userrow['offenseboost'] + $userrow['tempoffenseboost'];
      $defensepower = $powerlevel + $userrow['defenseboost'] + $userrow['tempdefenseboost'];
      $luck = ceil($userrow['Luck'] + $userrow['Brief_Luck']); //Calculate the player's luck total. Paranoia: Make sure we don't somehow have a non-integer.
      if (!empty($abilities[19])) { //Light's Favour activates. Increase luck.
	$luck += floor($userrow['Echeladder'] / 30);
	echo "$abilities[19]</br>";
      }
      if ($luck > 100) $luck = 100; //We work with luck as a percentage generally. This may be changed later.
      $motif = $_POST['usemotif'];
      if ($motif == "Blood(motif)") $motif = "Blood"; //Turn blood back so we can check for it properly.
      $motifresult = mysql_query("SELECT * FROM Fraymotifs WHERE `Fraymotifs`.`Aspect` = '" . $userrow['Aspect'] . "'");
      while ($row = mysql_fetch_array($motifresult)) {
	if ($row['Aspect'] == $userrow['Aspect']) $motifrow = $row;
      }
      $motifresult = mysql_query("SELECT * FROM Fraymotifs");
      while ($col = mysql_fetch_field($motifresult)) {
	$motifaspect = $col->name;
	if ($motifaspect == $motif) {
	  if (empty($motifrow[$motifaspect])) {
	    $motifname = "Unnamed Fraymotif";
	  } else {
	    $motifname = $motifrow[$motifaspect];
	  }
	}
      }
      switch ($motif) {
      case "solo1":
	switch ($userrow['Aspect']) { //We're using a solo motif. Grab the player's aspect and go!
	case "Breath": //Feathercadence
	  $usagestr = "Your enemies are buffeted by powerful winds, making it harder for them to fight.";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $powerstr = "enemy" . strval($i) . "power";
	    $newpower = floor($userrow[$powerstr] / 2);
	    $monsterpowers[$i] = $newpower;
	    //mysql_query("UPDATE `Players` SET `" . $powerstr . "` = " . strval($newpower) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$powerstr] = $newpower;
	    $i++;
	  }
	  break;
	case "Heart":
	  $usagestr = "Your spirit is bolstered, empowering your strikes and parries.";
	  $boost = 1500;
	  mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  break;
	case "Life": //Gaea's Anthem
	  $usagestr = "You are infused with the rhythm of Life, completely healing your wounds.";
	  mysql_query("UPDATE `Players` SET `" . $healthvialstr . "` = " . $userrow['Gel_Viscosity'] . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  break;
	case "Hope":
	  $usagestr = "The health and power you have give you hope that you may yet prevail.";
	  $boost = floor(($userrow['Health_Vial'] + (($userrow['offenseboost'] + $userrow['defenseboost']) / 2) + ($userrow['Echeladder'] * pow(2,$userrow['Godtier'])) + $userrow['powerboost']) / 12);
	  $boost = $boost + floor(((($userrow['tempoffenseboost'] + $userrow['tempdefenseboost']) / 2) + $userrow['temppowerboost']) / 12);
	  mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  break;
	case "Light":
	  $usagestr = "Fortune favours you utterly as you dance with the Light.";
	  $lucky = 100;
	  mysql_query("UPDATE `Players` SET `Brief_Luck` = $userrow[Brief_Luck]+$lucky WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  break;
	case "Mind":
	  $usagestr = "You lash out at the minds of your enemies, injuring them and impairing their judgment.";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $powerstr = "enemy" . strval($i) . "power";
	    $healthstr = "enemy" . strval($i) . "health";
	    $newpower = $userrow[$powerstr] - 1200;
	    $newhealth = $userrow[$healthstr] - 12000;
	    if ($newpower < 0) $newpower = 0;
	    if ($newhealth < 1) $newhealth = 1;
	    $monsterpowers[$i] = $newpower;
	    $monsterhealth[$i] = $newhealth;
	    //mysql_query("UPDATE `Players` SET `" . $powerstr . "` = " . $newpower . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$powerstr] = $newpower;
	    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . $newhealth . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$healthstr] = $newhealth;
	    $i++;
	  }
	  break;
	case "Blood":
	  $usagestr = "You strike your enemies hard, their wounds inspiring your bloodlust.";
	  $i = 1;
	  $boost = 0;
	  while ($i <= $max_enemies) {
	    $healthstr = "enemy" . strval($i) . "health";
	    $newhealth = $userrow[$healthstr] - 9001;
	    if ($newhealth < 1) $newhealth = 1;
	    $monsterhealth[$i] = $newhealth;
	    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . $newhealth . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$healthstr] = $newhealth;
	    $boost = $boost + floor(($userrow[$healthstr] - $newhealth) / 30);
	    $i++;
	  }
	  mysql_query("UPDATE `Players` SET `powerboost` = " . strval($userrow['powerboost'] + $boost) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  break;
	case "Doom":
	  $usagestr = "You strike your opponents with dark tendrils, bringing them closer to death.";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $healthstr = "enemy" . strval($i) . "health";
	    $newhealth = floor(($userrow[$healthstr] / 5) * 2);
	    $monsterhealth[$i] = $newhealth;
	    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . $newhealth . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$healthstr] = $newhealth;
	    $i++;
	  }
	  break;
	case "Rage": //Allaggressimo
	  $usagestr = "A quick, lively rhythm enrages you as you notice the wounds you have taken, inspiring you to hit things harder.";
	  $offenseboost = floor(($userrow['Gel_Viscosity'] - $userrow['Health_Vial']) / 2);
	  mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]+$offenseboost WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  break;
	case "Void": //Missing Notes
	  $usagestr = "A bunch of things that look like glitchy blocks of pixels appear around your enemies, which messes them up pretty bad.";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $healthstr = "enemy" . strval($i) . "health";
	    $powerstr = "enemy" . strval($i) . "power";
	    $newhealth = rand(1,$userrow[$healthstr]);
	    $newpower = rand(1,$userrow[$powerstr]);
	    $monsterpowers[$i] = $newpower;
	    $monsterhealth[$i] = $newhealth;
	    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . $newhealth . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$healthstr] = $newhealth;
	    //mysql_query("UPDATE `Players` SET `" . $powerstr . "` = " . $newpower . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$powerstr] = $newpower;
	    $i++;
	  }
	  break;
	case "Space":
	  $usagestr = "You bend space around a single enemy, grievously injuring them.";
	  $highestpower = -1;
	  $target = "";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $enemystr = "enemy" . strval($i) . "name";
	    $powerstr = "enemy" . strval($i) . "power";
	    $healthstr = "enemy" . strval($i) . "health";
	    if ($userrow[$powerstr] > $highestpower && !empty($userrow[$enemystr])) {
	      $highestpower = $userrow[$powerstr];
	      $target = $healthstr;
	      $j = $i;
	    }
	    $i++; 
	  }
	  $newhealth = $userrow[$target] - 61200;
	  if ($newhealth < 1) $newhealth = 1;
	  $monsterhealth[$j] = $newhealth;
	  //mysql_query("UPDATE `Players` SET `" . $target . "` = " . $newhealth . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $userrow[$healthstr] = $newhealth;
	  break;
	case "Time":
	  $usagestr = "You strike your foes, then turn back time to strike them again!"; //Tehcnically deals flat damage rather than simulating an attack, but still.
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $healthstr = "enemy" . strval($i) . "health";
	    $damage = $powerlevel;
	    if (($userrow[$healthstr] - $damage) < 1) $damage = $userrow[$healthstr] - 1;
	    $monsterhealth[$i] = $userrow[$healthstr] - $damage;
	    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . ($userrow[$healthstr] - $damage) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$healthstr] = ($userrow[$healthstr] - $damage);
	    $i++;
	  }
	  break;
	default:
	  echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	  $fail = True;
	  break;
	}
	break;
      case "solo2":
	switch ($userrow['Aspect']) { //We're using a solo motif. Grab the player's aspect and go!
	case "Breath": //Pneumatic Progression
	  $usagestr = "Pockets of highly pressurized air slam into your enemies, inflicting heavy damage.";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $healthstr = "enemy" . strval($i) . "health";
	    $newhealth = $userrow[$healthstr] - 50000;
	    if ($newhealth < 1) $newhealth = 1;
	    $monsterhealth[$i] = $newhealth;
	    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . $newhealth . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$healthstr] = $newhealth;
	    $i++;
	  }
	  break;
	case "Heart":
	  $usagestr = "With perfect form you strike each enemy in turn, combining your power and theirs into a devastating blow.";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $powerstr = "enemy" . strval($i) . "power";
	    $healthstr = "enemy" . strval($i) . "health";
	    $newhealth = $userrow[$healthstr] - (($userrow[$powerstr] * 2) + ($powerlevel * 2));
	    if ($newhealth < 1) $newhealth = 1;
	    $monsterhealth[$i] = $newhealth;
	    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . $newhealth . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$healthstr] = $newhealth;
	    $i++;
	  }
	  break;
	case "Life":
	  $usagestr = "The power of the fraymotif protects your life utterly, making you seem immortal.";
	  $invuln = 7;
	  mysql_query("UPDATE `Players` SET `invulnerability` = " . strval($userrow['invulnerability'] + $invuln) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  break;
	case "Hope":
	  $usagestr = "You discourage one of your enemies so completely that they no longer possess the will to fight.";
	  $highestpower = 0;
	  $target = "";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $enemystr = "enemy" . strval($i) . "name";
	    $powerstr = "enemy" . strval($i) . "power";
	    if ($userrow[$powerstr] > $highestpower && !empty($userrow[$enemystr])) {
	      $highestpower = $userrow[$powerstr];
	      $target = $powerstr;
	      $j = $i;
	    }
	    $i++; 
	  }
	  $monsterpowers[$j] = 0;
	  //mysql_query("UPDATE `Players` SET `" . $target . "` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $userrow[$target] = 0;
	  break;
	case "Light":
	  $usagestr = "A barrage of scintillating colours strike your foes, invoking a bizarre assortment of effects!";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $powerstr = "enemy" . strval($i) . "power";
	    $healthstr = "enemy" . strval($i) . "health";
	    $maxhealthstr = "enemy" . strval($i) . "maxhealth";
	    $namestr = "enemy" . strval($i) . "name";
	    $descstr = "enemy" . strval($i) . "desc";
	    $rolls = 1;
	    $damage = 0;
	    if (!empty($userrow[$namestr])) {
	      while ($rolls > 0) {
		$roll = rand(floor(1+($luck/50)),8);
		switch ($roll) {
		case 1:
		  $usagestr = $usagestr . "</br>The " . $userrow[$namestr] . " is incinerated!";
		  $damage += 10000;
		  break;
		case 2:
		  $usagestr = $usagestr . "</br>The " . $userrow[$namestr] . " is coated in acid!";
		  $damage += 20000;
		  break;
		case 3:
		  $usagestr = $usagestr . "</br>The " . $userrow[$namestr] . " is struck by electricity!";
		  $damage += 40000;
		  break;
		case 4:
		  $usagestr = $usagestr . "</br>The " . $userrow[$namestr] . " looks ill...";
		  $damage += ceil(($userrow[$maxhealthstr] / 5) * 2);
		  break;
		case 5:
		  $usagestr = $usagestr . "</br>The " . $userrow[$namestr] . " is turned to stone!";
		  //mysql_query("UPDATE `Players` SET `" . $powerstr . "` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		  $userrow[$powerstr] = 0; //In case it rolls berserk later.
		  $monsterpowers[$i] = 0;
		  break;
		case 6:
		  $usagestr = $usagestr . "</br>The " . $userrow[$namestr] . " goes berserk and attacks everything!";
		  $j = 1;
		  while ($j <= $max_enemies) {
		    $healthstr2 = "enemy" . strval($j) . "health";
		    $newhealth2 = $userrow[$healthstr2] - ($userrow[$powerstr] * 2);
		    if ($newhealth2 < 1) $newhealth2 = 1;
		    //mysql_query("UPDATE `Players` SET `" . $healthstr2 . "` = " . $newhealth2 . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		    $monsterhealth[$i] = $newhealth2;
		    $userrow[$healthstr2] = $newhealth2; //So that damage is recorded properly.
		    $j++;
		  }
		  break;
		case 7:
		  if ($reductionresist[$i] == 0 && $massiveresist[$i] == 100) { //Enemy has neither power reduction resistance or damage resistance.
		    $usagestr = $usagestr . "</br>The " . $userrow[$namestr] . " is sent to another dimension!";
		    $newdesc = 'It is a small paper replica of a ' . $userrow[$namestr] . ' with a note pinned to it that says "Piñata. Enjoy! -The Management"';
		    //mysql_query("UPDATE `Players` SET `" . $powerstr . "` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = 1 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		    //mysql_query("UPDATE `Players` SET `" . $descstr . "` = '" . $newdesc . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		    $userrow[$descstr] = $newdesc;
		    $rolls = 1; //It's gone, that'll do.
		    $userrow[$healthstr] = 1;
		    $userrow[$powerstr] = 0;
		  } else {
		    $usagestr = $usagestr . "</br>The " . $userrow[$namestr] . "resists the dimensional phasing.";
		    $rolls += 1; //Try again.
		  }
		  break;
		case 8:
		  $rolls += 2; //Two extra rolls! Yay!
		  break;
		default:
		  break;
		}
		$rolls--;
	      }
	      if ($damage > 0) {
		$newhealth = $userrow[$healthstr] - $damage;
		if ($newhealth < 1) $newhealth = 1;
		//mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . $newhealth . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		$userrow[$healthstr] = $newhealth;
		$monsterhealth[$i] = $monsterhealth[$i] - $damage;
		if ($monsterhealth[$i] < 1) $monsterhealth[$i] = 1; //Needs to be done this way because berserk ALSO inflicts damage.
		$userrow[$healthstr] = $newhealth;
	      }
	    }
	    $i++;
	  }
	  break;
	case "Mind":
	  $usagestr = "You bring your mind into perfect focus, able to read the actions of your opponents like an open book and react accordingly.";
	  $invuln = 3;
	  $powerboost = 1800;
	  mysql_query("UPDATE `Players` SET `invulnerability` = " . strval($userrow['invulnerability']+$invuln) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `powerboost` = " . strval($userrow['powerboost']+$powerboost) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  break;
	case "Blood":
	  $usagestr = "The mournful tune slows the pulses of your opponents, their movements becoming sluggish.";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $powerstr = "enemy" . strval($i) . "power";
	    $newpower = $userrow[$powerstr] - 3000;
	    if ($newpower < 0) $newpower = 0;
	    $monsterpowers[$i] = $newpower;
	    //mysql_query("UPDATE `Players` SET `" . $powerstr . "` = " . strval($newpower) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	    $userrow[$powerstr] = $newpower;
	    $i++;
	  }
	  break;
	case "Doom":
	  $usagestr = "Dark power curses an enemy, sealing their fate.";
	  $highestpower = 0;
	  $target = "";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $namestr = "enemy" . strval($i) . "name";
	    $powerstr = "enemy" . strval($i) . "power";
	    $healthstr = "enemy" . strval($i) . "health";
	    if ($userrow[$powerstr] > $highestpower && $userrow[$namestr] != "") {
	      $highestpower = $userrow[$powerstr];
	      $target = $healthstr;
	      $j = $i;
	    }
	    $i++; 
	  }
	  $monsterhealth[$j] = 1;
	  //mysql_query("UPDATE `Players` SET `" . $target . "` = 1 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $userrow[$target] = 1;
	  break;
	case "Rage":
	  $usagestr = "Unbridled rage courses through your enemies, causing them to turn on each other.";
	  $i = 1;
	  $j = 1;
	  while ($i <= $max_enemies) {
	    $powerstr = "enemy" . strval($i) . "power";
	    $namestr = "enemy" . strval($i) . "name";
	    if ($userrow[$namestr] != "") { //Enemy exists, perform attacks.
	      while ($j <= $max_enemies) {
		$healthstr = "enemy" . strval($j) . "health";
		$newhealth = $userrow[$healthstr] - ceil($userrow[$powerstr] / 2);
		if ($newhealth < 1) $newhealth = 1;
		$monsterhealth[$j] = $monsterhealth[$j] - ceil($userrow[$powerstr] / 2);
		if ($monsterhealth[$j] < 1) $monsterhealth[$j] = 1; //Needs to be done this way because damage is inflicted multiple times.
		//mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . $newhealth . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		$userrow[$healthstr] = $newhealth; //So that damage is recorded properly.
		$j++;
	      }
	    }
	    $i++;
	  }
	  break;
	case "Void":
	  $usagestr = "You fade from sight, becoming impossible to hit and gaining a significant advantage in attacking.";
	  $invuln = 3;
	  $tempoffenseboost = 5000;
	  $tempoffenseduration = 3;
	  mysql_query("UPDATE `Players` SET `invulnerability` = " . strval($userrow['invulnerability']+$invuln) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  if ($tempoffenseboost > $userrow['tempoffenseboost']) {
	    mysql_query("UPDATE `Players` SET `tempoffenseboost` = " . strval($tempoffenseboost) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  }
	  if ($tempoffenseduration < $userrow['tempoffenseduration'] || $userrow['tempoffenseduration'] == 0) {
	    mysql_query("UPDATE `Players` SET `tempoffenseduration` = " . strval($tempoffenseduration) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  }
	  break;
	case "Space":
	  $temppowerboost = 15000;
	  $temppowerduration = 1;
	  $usagestr = "You prepare to perform a rapid series of teleporting strikes, inflicting massively increased damage and becoming basically impossible to hit.";
	  if ($temppowerboost > $userrow['temppowerboost']) {
	    mysql_query("UPDATE `Players` SET `temppowerboost` = " . strval($temppowerboost) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  }
	  if ($temppowerduration < $userrow['temppowerduration'] || $userrow['temppowerduration'] == 0) {
	    mysql_query("UPDATE `Players` SET `temppowerduration` = " . strval($temppowerduration) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  }
	  break;
	case "Time":
	  $usagestr = "Your motions accelerate impossibly, allowing you to perform an incredible number of attacks before your opponents get a chance to retaliate.";
	  $invuln = 7;
	  mysql_query("UPDATE `Players` SET `invulnerability` = " . strval($userrow['invulnerability'] + $invuln) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  break;
	default:
	  echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	  $fail = True;
	  break;
	}
	break;
      case "solo3":
	//NOTE - This will set "motifactive" on, which will provide a specific effect at the end of every turn depending on aspect. The usage message is all that is coded here.
	switch ($userrow['Aspect']) {
	case "Breath": //Breathless Battaglia
	  if ($userrow['motifcounter'] == 0) {
	    $randomthing = rand(1,3);
	    switch ($randomthing) {
	    case 1:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/heir-conditioning' target='_blank'>windy rhythm</a> begins playing.";
	      break;
	    case 2:
	      $usagestr = "<a href='http://homestuck.bandcamp.com/track/heir-transparent' target='_blank'>The melody of an ancient Heir</a> begins playing.";
	      break;
	    case 3:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/rex-duodecim-angelus' target='_blank'>towering, thunderous song</a> begins playing.";
	      break;
	    default:
	      break;
	    }
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Heart":
	  if ($userrow['motifcounter'] == 0) {
	    $usagestr = "A <a href='http://homestuck.bandcamp.com/track/valhalla' target='_blank'>soul-stirring melody</a> begins playing.";
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Life":
	  if ($userrow['motifcounter'] == 0) {
	    $randomthing = rand(1,2);
	    switch ($randomthing) {
	    case 1:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/sburban-jungle-2' target='_blank'>melody of new beginnings</a> begins playing.";
	      break;
	    case 2:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/song-of-life' target='_blank'>lifey song</a> begins playing.";
	      break;
	    default:
	      break;
	    }
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Hope":
	  if ($userrow['motifcounter'] == 0) {
	    $randomthing = rand(1,2);
	    switch ($randomthing) {
	    case 1:
	      $usagestr = "An <a href='http://homestuck.bandcamp.com/track/skaian-skirmish' target='_blank'>uprising, uplifting song</a> begins playing.";
	      break;
	    case 2:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/savior-of-the-dreaming-dead' target='_blank'>melody of triumph against all odds</a> begins playing.";
	      break;
	    default:
	      break;
	    }
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Light":
	  if ($userrow['motifcounter'] == 0) {
	    $usagestr = "<a href='http://homestuck.bandcamp.com/track/aggrievance' target='_blank'>The song of an ancient Seer</a> begins playing.";
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Mind":
	  if ($userrow['motifcounter'] == 0) {
	    $randomthing = rand(1,2);
	    switch ($randomthing) {
	    case 1:
	      $usagestr = "An <a href='http://homestuck.bandcamp.com/track/versus' target='_blank'>intense, yet focused theme</a> begins playing.";
	      break;
	    case 2:
	      $usagestr = "An <a href='http://homestuck.bandcamp.com/track/bl1nd-just1c3-1nv3st1g4t1on' target='_blank'>investigative song</a> begins playing.";
	      break;
	    default:
	      break;
	    }
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Blood":
	  if ($userrow['motifcounter'] == 0) {
	    $usagestr = "<a href='http://homestuck.bandcamp.com/track/showdown' target='_blank'>The tune of an ancient Knight</a> begins playing.";
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Doom":
	  if ($userrow['motifcounter'] == 0) {
	    $randomthing = rand(1,3);
	    switch ($randomthing) {
	    case 1:
	      $usagestr = "<a href='http://homestuck.bandcamp.com/track/dance-of-thorns' target='_blank'>The best Homestuck song ever</a> begins playing.";
	      break;
	    case 2:
	      $usagestr = "An <a href='http://homestuck.bandcamp.com/track/an-unbreakable-union' target='_blank'>ominous-sounding song</a> begins playing.";
	      break;
	    case 3:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/blackest-heart' target='_blank'>soul-blackening dirge</a> begins playing.";
	      break;
	    default:
	      break;
	    }
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Rage":
	  if ($userrow['motifcounter'] == 0) {
	    $randomthing = rand(1,2);
	    switch ($randomthing) {
	    case 1:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/knifes-edge' target='_blank'>speedy, rousing tune</a> begins playing.";
	      break;
	    case 2:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/chaotic-strength' target='_blank'>fast, furious song</a> begins playing.";
	      break;
	    default:
	      break;
	    }
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Void":
	  if ($userrow['motifcounter'] == 0) {
	    $randomthing = rand(1,2);
	    switch ($randomthing) {
	    case 1:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/pumpkin-cravings' target='_blank'>bizarre tune</a> begins playing.";
	      break;
	    case 2:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/black-rose-green-sun' target='_blank'>song of strength and emptiness</a> begins playing.";
	      break;
	    default:
	      break;
	    }
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Space":
	  if ($userrow['motifcounter'] == 0) {
	    $randomthing = rand(1,2);
	    switch ($randomthing) {
	    case 1:
	      $usagestr = "A <a href='http://homestuck.bandcamp.com/track/atomic-bonsai' target='_blank'>lively, powerful rhythm</a> begins playing.";
	      break;
	    case 2:
	      $usagestr = "<a href='http://homestuck.bandcamp.com/track/sunslammer' target='_blank'>The song of an ancient Witch</a> begins playing.";
	      break;
	    default:
	      break;
	    }
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	case "Time":
	  if ($userrow['motifcounter'] == 0) {
	    $randomthing = rand(1,3);
	    switch ($randomthing) {
	    case 1:
	      $usagestr = "An <a href='http://homestuck.bandcamp.com/track/time-on-my-side' target='_blank'>incredibly funky jam</a> begins playing.";
	      break;
	    case 2:
	      $usagestr = "A <a http://homestuck.bandcamp.com/track/swing-of-the-clock' target='_blank'>curious ticking piece</a> begins playing.";
	      break;
	    case 3:
	      $usagestr = "<a http://homestuck.bandcamp.com/track/beatdown-round-2-2' target='_blank'>The song of an ancient Knight</a> begins playing.";
	      break;
	    default:
	      break;
	    }
	    mysql_query("UPDATE `Players` SET `motifcounter` = 1, `motifvar` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  } else {
	    echo "A battle theme is already playing!</br>";
	    $fail = True;
	  }
	  break;
	default:
	  echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	  $fail = True;
	  break;
	}
	break;
      default:
	$combo = False; //We look for an assister who can perform the combomotif
	$sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
	while ($row = mysql_fetch_array($sessionmates)) {
	  if ($row['aiding'] == $username) { //Aiding character.
	    $useraspect = $userrow['Aspect'];
	    if ($useraspect == "Blood") $useraspect = "Blood(motif)";
	    if ($row['Aspect'] == $motif && $row[$useraspect] == 1) $combo = True; //Applicable player found.
	  }
	}
	if ($combo == False) { //No assisting player with the correct aspect and fraymotif
	  echo "You are not being assisted by any player able to help you perform this fraymotif!</br>";
	  $fail = True;
	} else { //Gogogo!
	  switch ($motif) { //We're using a combo motif. Grab the non-player part of the combo.
	  case "Breath":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Heart":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Life":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Hope":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Light":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Mind":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Blood":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Doom":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Rage":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Void":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Space":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  case "Time":
	    switch ($userrow['Aspect']) { //We're using a combo motif. Grab the player part of the combo and go!
	    case "Breath":
	      break;
	    case "Heart":
	      break;
	    case "Life":
	      break;
	    case "Hope":
	      break;
	    case "Light":
	      break;
	    case "Mind":
	      break;
	    case "Blood":
	      break;
	    case "Doom":
	      break;
	    case "Rage":
	      break;
	    case "Void":
	      break;
	    case "Space":
	      break;
	    case "Time":
	      break;
	    default:
	      echo "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.";
	      $fail = True;
	      break;
	    }
	    break;
	  default:
	    echo "Aspect $motif unrecognized. This is probably a bug.";
	    $fail = True;
	    break;
	  }
	}
	break;
      }
    }
    if ($fail == False) {
      $userrow['fraymotifuses'] -= 1;
      $userrow['combatmotifuses'] -= 1;
      mysql_query("UPDATE `Players` SET `fraymotifuses` = " . $userrow['fraymotifuses'] . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `combatmotifuses` = " . $userrow['combatmotifuses'] . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      echo "You use the fraymotif, $motifname:</br>$usagestr</br>";
      //Check for enemy resists here.
      $i = 1;
      while ($i <= $max_enemies) {
	$enemystr = "enemy" . strval($i) . "name";
	$healthstr = "enemy" . strval($i) . "health";
	$maxhealthstr = "enemy" . strval($i) . "maxhealth";
	$powerstr = "enemy" . strval($i) . "power";
	if ($reductionresist[$i] != 0 && ($oldpowers[$i] - $monsterpowers[$i]) > $reductionresist[$i]) { //Enemy resists power reduction applied.
	  echo $userrow[$enemystr] . " resists the power reduction!</br>";
	  //mysql_query("UPDATE `Players` SET `" . $powerstr . "` = " . ($oldpowers[$i] - $reductionresist[$i]) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $userrow[$powerstr] = ($oldpowers[$i] - $reductionresist[$i]);
	}
	if ($massiveresist[$i] != 100 && (($oldhealth[$i] - $monsterhealth[$i]) > (floor($userrow[$maxhealthstr] / 100) * $massiveresist[$i]))) { //Enemy resists massive damage applied.
	  echo $userrow[$enemystr] . " resists the massive damage!</br>";
	  //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = " . ($oldhealth[$i] - (floor($userrow[$maxhealthstr] / 100) * $massiveresist[$i])) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $userrow[$healthstr] = ($oldhealth[$i] - (floor($userrow[$maxhealthstr] / 100) * $massiveresist[$i]));
	}
	$i++;
      }
    }
    writeEnemydata($userrow);
  }
  //End fraymotif usage code here.
  if (empty($userrow['Aspect'])) {
    echo "You have not accepted your title yet!</br>";
  } else {
    echo "WARNING - Currently, only the solo fraymotifs actually do anything. Although I doubt anyone can raise the cash for anything else, they currently do nothing.</br>";
    $time = time();
    $interval = 86400; //This is where the interval between fraymotif ticks is set. The reset is currently once per day.
    if (!empty($abilities[16])) $interval = floor($interval * 0.9); //Temporal Warp is active, cooldown is 90%
    $lasttick = $userrow['fraymotiftimer'];
    if ($lasttick != 0) {
      if ($time - $lasttick > $interval) { //Reset usages once per day
	$usages = floor($userrow['Echeladder'] / 100) + $userrow['Godtier'];
	mysql_query("UPDATE `Players` SET `fraymotifuses` = $usages WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	$userrow['fraymotifuses'] = $usages;
		while ($time - $lasttick > $interval) {
			$lasttick += $interval;
		}
      }
    } else { //Player has not had a tick yet.
      $usages = floor($userrow['Echeladder'] / 100) + $userrow['Godtier'];
      mysql_query("UPDATE `Players` SET `fraymotifuses` = $usages WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      $userrow['fraymotifuses'] = $usages;
      $lasttick = $time;
    }
    mysql_query("UPDATE `Players` SET `fraymotiftimer` = $lasttick WHERE `Players`.`username` = '$username' LIMIT 1 ;");
    if ($userrow['enemydata'] != "") {
      //Player is strifing: Provide the fraymotif use menu. Note that you cannot use a fraymotif while assisting.
      echo '<a href="strife.php">Strife</a></br>';
      echo "Select a fraymotif to use. Fraymotif uses remaining: $userrow[fraymotifuses]. Fraymotif uses reset in: " . strval(produceTimeString($interval - ($time - $lasttick))) . "</br>";
      echo '<form action="fraymotifs.php" method="post"><select name="usemotif">';
      $motifresult = mysql_query("SELECT * FROM Fraymotifs WHERE `Fraymotifs`.`Aspect` = '" . $userrow['Aspect'] . "'");
      while ($row = mysql_fetch_array($motifresult)) {
	if ($row['Aspect'] == $userrow['Aspect']) $motifrow = $row;
      }
      $motifresult = mysql_query("SELECT * FROM Fraymotifs");
      while ($col = mysql_fetch_field($motifresult)) {
	$motifaspect = $col->name;
	if (empty($motifrow[$motifaspect])) {
	  $motifname = "Unnamed Fraymotif - ";
	} else {
	  $motifname = $motifrow[$motifaspect] . " - ";
	}
	switch ($motifaspect) {
	case "solo1":
	  $motifname = $motifname . "Solo Fraymotif I";
	  break;
	case "solo2":
	  $motifname = $motifname . "Solo Fraymotif II";
	  break;
	case "solo3":
	  $motifname = $motifname . "Solo Fraymotif III";
	  break;
	default:
	  $motifname = $motifname . "Combined Fraymotif (" . $userrow['Aspect'] . " and " . $motifaspect . ")";
	  break;
	}
	if ($motifaspect == "Blood") $motifaspect = "Blood(motif)"; //Hack -- Blood is already used for blood grist, so we need a new name.
	if (empty($newmotif)) $newmotif = "";
	if ($motifaspect != "Aspect" && ($userrow[$motifaspect] == 1 || $motifaspect == $newmotif)) { //Exclude aspect field. Ensure player owns this fraymotif.
	  echo '<option value = "' . $motifaspect . '">' . $motifname . '</option>'; //Add option to use this fraymotif.
	}
      }
      echo '</select></br><input type="submit" value="Use it!" /> </form>';
    } else { //Player is not strifing: Provide the fraymotif purchase menu.
      echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
      echo "Fraymotif shop: Please select a fraymotif to purchase.</br>";
      echo '<form action="fraymotifs.php" method="post">Fraymotif to purchase:<select name="buymotif">';
      $motifresult = mysql_query("SELECT * FROM Fraymotifs WHERE `Fraymotifs`.`Aspect` = '" . $userrow['Aspect'] . "'");
      while ($row = mysql_fetch_array($motifresult)) {
	if ($row['Aspect'] == $userrow['Aspect']) $motifrow = $row;
      }
      $motifresult = mysql_query("SELECT * FROM Fraymotifs");
      while ($col = mysql_fetch_field($motifresult)) {
	$motifaspect = $col->name;
	if (empty($motifrow[$motifaspect])) {
	  $motifname = "Unnamed Fraymotif - ";
	} else {
	  $motifname = $motifrow[$motifaspect] . " - ";
	}
	switch ($motifaspect) {
	case "solo1":
	  $motifname = $motifname . "Solo Fraymotif I - 10,000,000 Boondollars";
	  break;
	case "solo2":
	  $motifname = $motifname . "Solo Fraymotif II - 100,000,000 Boondollars";
	  break;
	case "solo3":
	  $motifname = $motifname . "Solo Fraymotif III - 1,000,000,000 Boondollars";
	  break;
	default:
	  $motifname = $motifname . "Combined Fraymotif (" . $userrow['Aspect'] . " and " . $motifaspect . ") - 10,000,000,000 Boondollars";
	  break;
	}
	if ($motifaspect == "Blood") $motifaspect = "Blood(motif)"; //Hack -- Blood is already used for blood grist, so we need a new name.
	if (empty($newmotif)) $newmotif = "";
	if ($motifaspect != "Aspect" && $userrow[$motifaspect] != 1 && $motifaspect != $newmotif) { //Exclude aspect field. Ensure player does not already own this fraymotif.
	  echo '<option value = "' . $motifaspect . '">' . $motifname . '</option>'; //Add option to purchase this fraymotif.
	}
      }
      echo '</select></br><input type="submit" value="Purchase it!" /> </form>';
      echo "Fraymotif uses remaining: $userrow[fraymotifuses]. Fraymotif uses reset in: " . strval(produceTimeString($interval - ($time - $lasttick))) . "</br>";
      echo "Fraymotifs available:</br>";
      $motifresult = mysql_query("SELECT * FROM Fraymotifs WHERE `Fraymotifs`.`Aspect` = '" . $userrow['Aspect'] . "'");
      while ($row = mysql_fetch_array($motifresult)) {
	if ($row['Aspect'] == $userrow['Aspect']) $motifrow = $row;
      }
      $motifresult = mysql_query("SELECT * FROM Fraymotifs");
      while ($col = mysql_fetch_field($motifresult)) {
	$motifaspect = $col->name;
	if (empty($motifrow[$motifaspect])) {
	  $motifname = "Unnamed Fraymotif - ";
	} else {
	  $motifname = $motifrow[$motifaspect] . " - ";
	}
	switch ($motifaspect) {
	case "solo1":
	  $motifname = $motifname . "Solo Fraymotif I";
	  break;
	case "solo2":
	  $motifname = $motifname . "Solo Fraymotif II";
	  break;
	case "solo3":
	  $motifname = $motifname . "Solo Fraymotif III";
	  break;
	default:
	  $motifname = $motifname . "Combined Fraymotif (" . $userrow['Aspect'] . " and " . $motifaspect . ")";
	  break;
	}
	if ($motifaspect == "Blood") $motifaspect = "Blood(motif)"; //Hack -- Blood is already used for blood grist, so we need a new name.
	if (empty($newmotif)) $newmotif = "";
	if ($motifaspect != "Aspect" && ($userrow[$motifaspect] == 1 || $motifaspect == $newmotif)) { //Exclude aspect field. Ensure player owns this fraymotif.
	  echo $motifname . "</br>"; //Display it!
	}
      }
    }
  }
}
require_once("footer.php");
?>