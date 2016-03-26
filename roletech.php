<?php
require_once("header.php");
require_once("includes/fieldparser.php");
if (empty($_SESSION['username'])) {
  echo "Log in to access roletech.</br>";
} elseif (empty($_SESSION['adjective'])) {
  echo "You have not accepted your title yet!</br>";
} else {
  

	$compugood = true;
	  if (strpos($userrow['storeditems'], "DREAMBOT") !== false && $userrow['dreamingstatus'] != "Awake") { 
	//items in storage with the DREAMBOT tag will grant access to computability as one's dreamself
	$dreambot == true;
} else {
	$dreambot == false;
	$compugood = false;
}
	if ($dreambot) {
		if (strpos($userrow['storeditems'], "ISCOMPUTER.") == 0) { //dreambot checks for a computer in storage, regardless of player computability
			//echo "Your dreambot can't use the SBURB server program without access to a computer in storage!<br />";
			$compugood = false;
		}
	} else {
  if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
  	if ($userrow['hascomputer'] < 3) {
  		//if ($compugood == true) echo "You don't have a hands-free computer equipped, so you can't use the SBURB server program during strife.</br>";
  		$compugood = false;
  	}
  }
  if ($userrow['indungeon'] != 0 && $userrow['hascomputer'] < 2) {
  	//if ($compugood == true) echo "You don't have a portable computer in your inventory, so you can't use the SBURB server program while away from home.</br>";
  	$compugood = false;
  }
  if ($userrow['hascomputer'] == 0) {
  	//if ($compugood == true) echo "You need a computer in storage or your inventory to use the SBURB server program.</br>";
  	$compugood = false;
  }
	}
  
  //Note that passive abilities are processed in the places in the code where the things they affect occur.
  if (!empty($_POST['ability'])) { //Active ability used.
  	$bonusconsumable = false;
    $id = $_POST['ability'];
    $usage = mysql_query("SELECT * FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') AND `Abilities`.`Rungreq` 
BETWEEN 0 AND $userrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $userrow[Godtier] AND `Abilities`.`ID` = $id;"); //Pulls ID ability IF the player has it.
 	$currentstatus = $userrow['strifestatus'];
  	if (!empty($currentstatus)) { //Check for any instances of HASABILITY
    	$thisstatus = explode("|", $currentstatus);
    	$st = 0;
    	while (!empty($thisstatus[$st])) {
    		$statusarg = explode(":", $thisstatus[$st]);
    		if ($statusarg[0] == "HASABILITY") { //This is an ability the player possesses.
				$abilityid = intval($statusarg[1]);
    			if ($abilityid == $id) { //This instance of HASABILITY corresponds to the ability being used
					$usage = mysql_query("SELECT * FROM `Abilities` WHERE `Abilities`.`ID` = $id;"); //Ability use is legal; go for it!
				}
    		}
    		$st++;
    	}
	}
	if ($abilityrow = mysql_fetch_array($usage)) {
      if ($abilityrow['Active'] == 1) { //Ability is active
	$strifing = False;
	if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
	  $strifing = True; //Calculate this here.
	  $userrow = parseEnemydata($userrow);
      if ($userrow['combatconsume'] == 1) { //Already used a consumable this round.
	  	$bonusconsumestr = "PLAYER:BONUSCONSUME|";
		if (strpos($userrow['strifestatus'], $bonusconsumestr) !== False) { //Player has a bonus consumable usage
			$bonusconsumable = true;
			$userrow['combatconsume'] = 0;
		}
	  }
	}
	if ($userrow['Aspect_Vial'] >= $abilityrow['Aspect_Cost']) {
	  if ($userrow['combatconsume'] == 0 || $strifing == False) { //User has an action or isn't strifing
	    $targetfound = False;
	    if ($abilityrow['targets'] == 1) { //Check to see if the chosen target can be reached.
	      if (!empty($_POST['target']) && mysql_real_escape_string($_POST['target']) != $username) { //Target is another player.
		$target = mysql_real_escape_string($_POST['target']);
		$targetresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$target' AND `Players`.`session_name` = '$userrow[session_name]' LIMIT 1;");
		if ($targetrow = mysql_fetch_array($targetresult)) { //Player found.
		  if ($targetrow['noassist'] == 1) {
		    echo "That player cannot currently be assisted.</br>";
		  } elseif ($targetrow['dreamingstatus'] != $userrow['dreamingstatus'] && $userrow['Godtier'] == 0) { //God tiers can buff ALL the things
		    echo "You cannot currently reach that player to use an ability on them!</br>";
		  } elseif ($strifing && $targetrow['aiding'] != $username && $userrow['aiding'] != $targetrow['username'] && ($userrow['aiding'] != $targetrow['aiding'] || empty($userrow['aiding'])) && $userrow['sessionbossengaged'] != 1) {
		    //User and target not in the same strife (either user aids target, target aids user, or user and target both aid the same person).
		    echo "While strifing, you may not use abilities on those not participating in your strife.</br>";
		  } elseif ($userrow['sessionbossengaged'] == 1 && $userrow['sessionbossengaged'] != $targetrow['sessionbossengaged']) { //Handle session boss case.
		    echo "While strifing, you may not use abilities on those not participating in your strife.</br>";
		  } else { //Success.
		    $targetfound = True;
		  }
		  $targetrow = parseEnemydata($targetrow);
		} else {
		  echo "Player $target was not found in your session.</br>";
		}
	      } else {
		$target = $username;
		$targetrow = $userrow;
		$targetfound = True; //Self always targetable. Unless you're a Sylph, lol. (Add exception for this later when Sylph abilities are actually a thing)
	      }
	    }
		if ($bonusconsumable) { //This indicates that we used up a bonus consumable instance that was confirmed above.
			$statusarray = explode("|", $userrow['strifestatus']);
			$p = 0;
			$instancefound = false;
			while (!empty($statusarray[$p]) && !$instancefound) {
				if (strpos($statusarray[$p], $bonusconsumestr) !== False) { //This is one of the bonus consume instances.
					$instancefound = true;
					$removethis = $statusarray[$p] . "|";
					$userrow['strifestatus'] = preg_replace('/' . $removethis . '/', '', $userrow['strifestatus'], 1);
					mysql_query("UPDATE `Players` SET `Players`.`strifestatus` = '$userrow[strifestatus]' WHERE `Players`.`username` = '$username' LIMIT 1;");
				}
				$p++;
			}
		}
	    //We have succeeded at this stage. Calculate player power, it is relevant for some roletechs.
	    $aspectresult = mysql_query("SELECT * FROM `Aspect_modifiers` WHERE `Aspect_modifiers`.`Aspect` = '$userrow[Aspect]';");
	    $aspectrow = mysql_fetch_array($aspectresult);
	    $classresult = mysql_query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$userrow[Class]';");
	    $classrow = mysql_fetch_array($classresult);
	    $unarmedpower = floor($userrow['Echeladder'] * (pow(($classrow['godtierfactor'] / 100),$userrow['Godtier'])));
	    $factor = ((612 - $userrow['Echeladder']) / 611);
	    $unarmedpower = ceil($unarmedpower * ((($classrow['level1factor'] / 100) * $factor) + (($classrow['level612factor'] / 100) * (1 - $factor)))); //Finish calculating unarmed power.
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
	    $powerlevel = $unarmedpower + $mainpower + $offpower;
	    $luck = ceil($userrow['Luck'] + $userrow['Brief_Luck']);
	    switch ($id) { //Data for what abilities do contained here. This switch statement performs the abilities; slap the code in here. COMMENT EACH CASE WITH THE NAME OF THE ABILITY.
	    case 5: //Dissipate (focus)
	      if ($strifing) {
		echo "$abilityrow[Usagestr]</br>";
		mysql_query("UPDATE `Players` SET `dissipatefocus` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      } else {
		echo "You may only use that ability while strifing.</br>";
	      }
	      break;
	    case 6: //Esauna
	      if ($targetfound) {
		echo "$abilityrow[Usagestr]</br>";
		if ($targetrow['powerboost'] < 0) mysql_query("UPDATE `Players` SET `powerboost` = 0 WHERE `Players`.`username` = '$target' LIMIT 1 ;");
		if ($targetrow['offenseboost'] < 0) mysql_query("UPDATE `Players` SET `offenseboost` = 0 WHERE `Players`.`username` = '$target' LIMIT 1 ;");
		if ($targetrow['defenseboost'] < 0) mysql_query("UPDATE `Players` SET `defenseboost` = 0 WHERE `Players`.`username` = '$target' LIMIT 1 ;");
		if ($targetrow['temppowerboost'] < 0) mysql_query("UPDATE `Players` SET `temppowerboost` = 0 WHERE `Players`.`username` = '$target' LIMIT 1 ;");
		if ($targetrow['tempoffenseboost'] < 0) mysql_query("UPDATE `Players` SET `tempoffenseboost` = 0 WHERE `Players`.`username` = '$target' LIMIT 1 ;");
		if ($targetrow['tempdefenseboost'] < 0) mysql_query("UPDATE `Players` SET `tempdefenseboost` = 0 WHERE `Players`.`username` = '$target' LIMIT 1 ;");
		$currentstatus = $targetrow['strifestatus']; //Start removing any effect defined in here.
		$nocapstr = "PLAYER:NOCAP|"; //Implement this as a nice loop later.
		if (strpos($targetrow['strifestatus'], $nocapstr) !== False) { //Player's damage is uncapped.
			$currentstatus = str_replace($nocapstr, "", $currentstatus);
		}
		//Below: update the strife status.
		mysql_query("UPDATE `Players` SET `strifestatus` = '$currentstatus' WHERE `Players`.`username` = '" . $target . "' LIMIT 1 ;");
	      }
	      break;
	    case 8: //Seek Fortune's Path (NOTE - requires computability unless advising during session boss fights).
	      if ($strifing) {
	        if ($userrow['sessionbossengaged'] == 1) {
	          $focus = (rand(40,65) * ($userrow['Health_Vial'] / $userrow['Gel_Viscosity']));
	          if ($focus < 20) $focus = 20;
	          if ($userrow['sessionbossfocus'] <= $focus) {
	            echo "$abilityrow[Usagestr]</br>";
	 	    $sessionmates = mysql_query("SELECT `username`,`Brief_Luck` FROM `Players` WHERE `Players`.`session_name` = '$userrow[session_name]' AND `Players`.`sessionbossengaged` = 1;");
		    while ($chumrow = mysql_fetch_array($sessionmates)) {
		      mysql_query("UPDATE `Players` SET `Brief_Luck` = $chumrow[Brief_Luck]+10 WHERE `Players`.`username` = '$chumrow[username]' LIMIT 1;");
		      echo "$chumrow[username] has been fortuitously advised.</br>";
		    }
	          } else {
	            echo "You attempt to divine fortuitously, but are interrupted by having to stop $userrow[sessionbossname] from killing you. There's just too much attention on you right now for you to direct the battle flow.</br>";
	        /*} else {
		  echo "You attempt to divine fortuitously, but are interrupted by having to stop things from killing you.</br>";*/
		}
	      } elseif (!$compugood) {
		echo "Without a computer, you can only advise yourself!</br>";
		mysql_query("UPDATE `Players` SET `Brief_Luck` = $userrow[Brief_Luck]+10 WHERE `Players`.`username` = '$username' LIMIT 1;");
		echo "$username has been fortuitously advised.</br>";
	      } else {
		echo "$abilityrow[Usagestr]</br>";
		$sessionmates = mysql_query("SELECT `username`,`Brief_Luck` FROM `Players` WHERE `Players`.`session_name` = '$userrow[session_name]' AND `Players`.`hascomputer` = 1;");
		while ($chumrow = mysql_fetch_array($sessionmates)) {
		  mysql_query("UPDATE `Players` SET `Brief_Luck` = $chumrow[Brief_Luck]+10 WHERE `Players`.`username` = '$chumrow[username]' LIMIT 1;");
		  echo "$chumrow[username] has been fortuitously advised.</br>";
		}
	      }
	      }
	      break;
	    case 18: //Temporal Doppelgänger
	      if ($userrow['encounters'] > 0) {
		if ($strifing) {
		  echo "$abilityrow[Usagestr]</br>";      
		  $i = 1;
		  while ($i <= $max_enemies) {
		    $enemystr = "enemy" . strval($i) . "name";
		    $powerstr = "enemy" . strval($i) . "power";
		    $healthstr = "enemy" . strval($i) . "health";
		    $enemydamage = rand(floor($powerlevel * (0.85 + ($luck * 0.003))),ceil($powerlevel * 1.15)) - floor($userrow[$powerstr]);
		    if ($enemydamage < 0) $enemydamage = 0; //No healing the enemy!
		    $newenemyhealth = $userrow[$healthstr] - $enemydamage;
		    if ($newenemyhealth < 1) $newenemyhealth = 1; //Abilities can't be fatal.
		    //mysql_query("UPDATE `Players` SET `" . $healthstr . "` = '" . strval($newenemyhealth) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		    $userrow[$healthstr] = $newenemyhealth;
		  $i++;
		  }
		} else {
		  echo "A version of you with slight temporal displacement appears. You ask how the future/past/alternate timeline is going. You say it's going pretty well, thank you. You ask how things are in the present. You say they're doing okay. It's pretty chill.</br>";
		}
		mysql_query("UPDATE `Players` SET `encounters` = $userrow[encounters]-1 WHERE `Players`.`username` = '$username' LIMIT 1;");
	      } else {
		echo "Unfortunately, nothing happens. You just don't have any time left to abuse.</br>";
	      }
	      break;
	    default:
	      break;
	    }
	    if ($abilityrow['targets'] != 1 || $targetfound) { //Counts as an action if a target was found or no targeting happens.
	      if ($strifing) mysql_query("UPDATE `Players` SET `combatconsume` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Only do this if strifing.
	      mysql_query("UPDATE `Players` SET `Aspect_Vial` = $userrow[Aspect_Vial]-$abilityrow[Aspect_Cost] WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      $userrow['Aspect_Vial'] -= $abilityrow['Aspect_Cost']; //Only do this on success.
	      writeEnemydata($targetrow);
	    }
	  } else {
	    echo "You have already used your action for this round of strife!</br>";
	  }
	} else {
	  echo "You do not have the required Aspect Vial to use this ability!</br>";
	}
      } else {
	echo "That ability is not an active ability!</br>";
      }
    } else {
      echo "You do not have that ability!</br>";
    }
  }
  $abilities = mysql_query("SELECT * FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') AND `Abilities`.`Rungreq` 
BETWEEN 0 AND $userrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $userrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
  //Note that other special restrictions may be checked at some stage.
    $aspectvial = floor(($userrow['Aspect_Vial'] / $userrow['Gel_Viscosity']) * 100);
	echo "Aspect Vial: $aspectvial%</br>";
	$tempfound = false;
  	$currentstatus = $userrow['strifestatus'];
  	if (!empty($currentstatus)) { //Check for any instances of HASABILITY
    	$thisstatus = explode("|", $currentstatus);
    	$st = 0;
    	while (!empty($thisstatus[$st])) {
    		$statusarg = explode(":", $thisstatus[$st]);
    		if ($statusarg[0] == "HASABILITY") { //This is an ability the player possesses.
				if ($tempfound == false) {
					echo "You have temporary roletechs:</br></br>";
					$tempfound = true;
				}
				$abilityid = intval($statusarg[1]);
    			$abilityresult = mysql_query("SELECT * FROM `Abilities` WHERE `Abilities`.`ID` = $abilityid LIMIT 1;");
				$ability = mysql_fetch_array($abilityresult);
				echo "$ability[Name]:</br>";
				echo "$ability[Description]</br>";
				echo "Class: $ability[Class]</br>";
				echo "Aspect: $ability[Aspect]</br>";
				if ($ability['Rungreq'] > 0) echo "Rung (not) required: $ability[Rungreq]</br>";
				//Will need code to print out the god tier requirement here.
				if ($ability['Aspect_Cost'] != 0) {
					$aspectcost = ceil(($ability['Aspect_Cost'] / $userrow['Gel_Viscosity']) * 100);
					echo "Aspect Vial expended to use this ability: $aspectcost%</br>";
				}
				if ($ability['Active'] == 1) {
					echo '<form action="roletech.php" method="post">';
					if ($ability['targets'] == 1) echo 'Target (default to self if blank): <input type="text" id="target" name="target"></br>';
					echo '<input type="hidden" name="ability" id="ability" value="' . strval($ability['ID']) . '">'; //Send the ability's ID off for processing.
					echo '<input type="submit" value="Use it!"></form></br>';
				}
				echo "</br>";
    		}
    		$st++;
    	}
	}
  echo "Your available roletech:</br></br>";
  $tech = False;
  while ($ability = mysql_fetch_array($abilities)) {
    $tech = True;
    echo "$ability[Name]:</br>";
    echo "$ability[Description]</br>";
    echo "Class: $ability[Class]</br>";
    echo "Aspect: $ability[Aspect]</br>";
    if ($ability['Rungreq'] > 0) echo "Rung required: $ability[Rungreq]</br>";
    //Will need code to print out the god tier requirement here.
    if ($ability['Aspect_Cost'] != 0) {
      $aspectcost = ceil(($ability['Aspect_Cost'] / $userrow['Gel_Viscosity']) * 100);
      echo "Aspect Vial expended to use this ability: $aspectcost%</br>";
    }
    if ($ability['Active'] == 1) {
      echo '<form action="roletech.php" method="post">';
      if ($ability['targets'] == 1) echo 'Target (default to self if blank): <input type="text" id="target" name="target"></br>';
      echo '<input type="hidden" name="ability" id="ability" value="' . strval($ability['ID']) . '">'; //Send the ability's ID off for processing.
      echo '<input type="submit" value="Use it!"></form></br>';
    }
    echo "</br>";
  }
  if ($tech == False) echo "None!";
}
require_once("footer.php");
?>