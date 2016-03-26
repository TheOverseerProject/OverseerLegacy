<?php
require 'time.php'; //This is now necessary so the header can keep track of your timer.

// mobile detection code
function mdetect(){
  $useragent=$_SERVER['HTTP_USER_AGENT'];
  if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
    return (bool)true;
  } else {
    return false;
  }
}

require_once("includes/SQLconnect.php");
if (empty($_SESSION['username'])) {

  $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = 'default'");
  $userrow = mysql_fetch_array($result);
  //Should stop the "userrow undefined" spam.
} else {

  $username=$_SESSION['username'];
  $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $username . "' LIMIT 1;");

  while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
    if ($row['username'] == $username) { //Paranoia: Double-check.
      $userrow = $row;
    }
  }
  $bossmax = 5000000; //This is the value that is assigned to the first enemy in the list when a session-wide boss is being fought. (Massive damage resist is handled differently)
  //Begin setting string-based vars here.
  $healthvialcolour = "black"; //These are the defaults.
  $aspectvialcolour = "doom";
  if ($userrow['colour'] == "Purple") $healthvialcolour = "purple";
  if ($userrow['Aspect'] == "Breath") $aspectvialcolour = "breath";
  if ($userrow['Aspect'] == "Light") $aspectvialcolour = "light";
  if ($userrow['dreamingstatus'] == "Awake") {
    $healthvialstr = "Health_Vial";
    $oldenemyprestr = "oldenemy";
    $downstr = "down";
  } else {
    $healthvialstr = "Dream_Health_Vial";
    $oldenemyprestr = "olddreamenemy";
    $downstr = "dreamdown";
  }
  $max_enemies = 5; //Define this here.
  if ($userrow['sessionbossengaged'] == 1) { //Player is engaged with session-wide boss, check to see if shit happens.
    $bosstimer = 300; //Five minutes to act.
    $bosstime = time();
    $timeout = 30; //Number of seconds file has to perform boss actions.
    $sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1;");
    $sessionrow = mysql_fetch_array($sessionresult);
    if ($sessionrow['sessionbossname'] == "") { //No boss! Paranoia: disengage.
      mysql_query("UPDATE Players SET `sessionbossengaged` = 0 WHERE `Players`.`session_name` = '$username'");
    } else {
      $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'");
      $chumroll = 0;
      while ($buddyrow = mysql_fetch_array($sessionmates)) $chumroll++;
      //Fish up the boss's entry in the enemy listing. It contains vital statistics. NOTE - The massive damage threshold should be divided by the number of players in the session and is a soft cap
      $bossresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = $sessionrow[sessionbossname]");
      $bossrow = mysql_fetch_array($bossresult);
      //Below we check: Whether there needs to be an action, and whether either the mutex is free or the mutex user has timed out.
      //(If the mutex has been acquired for $timeout seconds or more, we assume the original person is not going to complete the boss update and try again)
      $turns = 0;
      if ($bosstime - $bosstimer > $sessionrow['actiontimer'] && ($sessionrow['mutexplayer'] == "" || $bosstime - $sessionrow['mutextimer'] >= $timeout)) { //This user triggers boss.
	mysql_query("UPDATE Sessions SET `mutexplayer` = '$username' WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1;");
	mysql_query("UPDATE Sessions SET `mutextimer` = $bosstime WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1;");
	while ($bosstime - $bosstimer > $sessionrow['actiontimer']) { //This user has triggered the next attack. Or if for some reason no-one has checked in forever, the next <MATH> attacks.
	  $turns++;
	  $message = $sessionrow['combatlog'];
	  $fighters = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]' AND `Players`.`sessionbossengaged` = 1");
	  //Routine for boss's turn goes here. Everything will happen here (data contained here). We will make an array using the fighter names as indexes and assign damage and power reduction.
	  $bossdamage = 0;
	  $bossreduc = 0;
	  //The below arrays store information about anyone who is targeted for damage or other effects.
	  $damagedealt = array($username => 0);
	  $powerdamage = array($username => 0); //This one is the amount of power reduction inflicted.
	  $aggro = array($username => 0); //Percentage of boss aggro received.
	  $health = array($username => $userrow[$healthvialstr]);
	  $powerreduc = array($username => 0);
	  $offensereduc = array($username => 0);
	  $defensereduc = array($username => 0);
	  $fighterindex = array(1 => $username);
	  $bosspower = $sessionrow['sessionbosspower']; //Power to be allocated to attacks by the boss.
	  while ($fightrow = mysql_fetch_array($fighters)) { //Attackers do stuff here.
	    $abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$fightrow[Aspect]','All') AND `Abilities`.`Class` IN ('$fightrow[Class]','All') 
AND `Abilities`.`Rungreq` BETWEEN 0 AND $fightrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $fightrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
	    $abilities = array(0 => "Null ability. No, not void.");
	    while ($temp = mysql_fetch_array($abilityresult)) {
	      $abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
	      //as pulling the correct element out of the abilities array via the ID. Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
	    }
	    if ($fightrow['username'] != $username) $fighterindex[] = $fightrow['username']; //Add this player to the index of fighters.
	    $damage = 0;
	    $reduc = 0;
	    if ($fightrow['enemy1health'] != $bossmax) { //Damage dealt
	      $damage = $bossmax - $fightrow['enemy1health'];
	      if ($damage < 0) $damage = 0; //Paranoia: If the damage somehow goes negative we set it to zero.
	      $massivedamageresist =  ($sessionrow['sessionbossmaxhealth'] * ($bossrow['massiveresist'] / (100 * $chumroll)));
	      $message = $message . "DEBUG ($fightrow[username]'s personal values): Bossmax (should be 5000000): $fightrow[enemy1maxhealth]. Bosscurrent (should be 5000000 - damage dealt): $fightrow[enemy1health].</br>";
	      if ($damage > $massivedamageresist) { //Soft cap on damage.
		$reducingdamage = ($damage - $massivedamageresist);
		$damage = $massivedamageresist; //Base: The damage cap.
		$message = $message . $fightrow['username'] . " triggers massive damage resistance! ";
		$softcapfactor = ($massivedamageresist / 2);
		while ($reducingdamage >= $softcapfactor) { //Soft cap: For every $softcapfactor chunk of damage to be inflicted, double the previous requirement must be dealt.
		  $reducingdamage = $reducingdamage / 2;
		  $reducingdamage -= $softcapfactor;
		  $damage += $softcapfactor;
		}
		$damage += ($reducingdamage / 2);
	      }
	      $message = $message . $fightrow['username'] . " inflicts $damage damage!</br>";
	      $bossdamage += $damage;
	      $damagedealt[$fightrow['username']] = $damage;
	    }
	    if ($fightrow['enemy1power'] != $bossmax) { //Power reduced
	      $reduc = $bossmax - $fightrow['enemy1power'];
	      if ($reduc > $bossrow['reductionresist']) $reduc = $bossrow['reductionresist'];
	      $message = $message . $fightrow['username'] . " inflicts $reduc power reduction!</br>";
	      $bossreduc += $reduc;
	      $powerdamage[$fightrow['username']] = $reduc;
	    }
	  }
	  if ($bossdamage > $sessionrow['sessionbosshealth']) { //Boss has been slain!
	    //Boss is dead code here.
	    $message = $message . "$sessionrow[sessionbossname] has been defeated! Congratulations!</br>";
	    mysql_query("UPDATE Sessions SET `sessionbossname` = '' WHERE `Sessions`.`name` = '$userrow[session_name]'");
	    mysql_query("UPDATE Sessions SET `checkmate` = 1 WHERE `Sessions`.`name` = '$userrow[session_name]'");
	    $p = 1;
	    while ($p <= $max_enemies) {
	      $enemystr = "enemy" . strval($p) . "name";
	      mysql_query("UPDATE Players SET `$enemystr` = '' WHERE `Players`.`sessionbossengaged` = 1 AND `Players`.`session_name` = '$userrow[session_name]'");
	      $p++;
	    }
	    mysql_query("UPDATE Players SET `Echeladder` = 612 WHERE `Players`.`sessionbossengaged` = 1 AND `Players`.`session_name` = '$userrow[session_name]'"); //Maxxes out if it wasn't already
	    mysql_query("UPDATE Players SET `sessionbossengaged` = 0 WHERE `Players`.`session_name` = '$userrow[session_name]'");
	    mysql_query("UPDATE Players SET `sessionbossdefeated` = '$sessionrow[sessionbossname]' WHERE `Players`.`session_name` = '$userrow[session_name]'");
	    $sessionrow['actiontimer'] = $bosstime; //End ze loop.
	  } else {
	    $fighters = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]' AND `Players`.`sessionbossengaged` = 1");
	    $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '$sessionrow[sessionbossname]'");
	    $enemyrow = mysql_fetch_array($enemyresult);
	    while ($fightrow = mysql_fetch_array($fighters)) { //Boss performs attacks starting here.
	      $abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$fightrow[Aspect]','All') AND `Abilities`.`Class` IN ('$fightrow[Class]','All') 
AND `Abilities`.`Rungreq` BETWEEN 0 AND $fightrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $fightrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
	      $abilities = array(0 => "Null ability. No, not void.");
	      while ($temp = mysql_fetch_array($abilityresult)) {
		$abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
		//as pulling the correct element out of the abilities array via the ID. An ability with an empty usage message will be unusable since the empty function will spit empty at you.
	      }
	      $fraction = (($damagedealt[$fightrow['username']] + ($powerdamage[$fightrow['username']] * 5)) / ($bossdamage + ($bossreduc * 5))); //Aggro goes to most damaging players by default.
	      $fightername = $fightrow['username'];
	      $aggro[$fightername] = floor($fraction * 100); //We use this later to work out who is targeted by special effects.
	      $message = $message . "DEBUG (aggro assignment to $fightrow[username]): $aggro[$fightername]</br>";
	      $luck = ceil($fightrow['Luck'] + $fightrow['Brief_Luck']); //Calculate the player's luck total. Paranoia: Make sure we don't somehow have a non-integer.
	      if (!empty($abilities[19])) { //Light's Favour activates. Increase luck.
		$luck += floor($fightrow['Echeladder'] / 30);
		$message = $message . "$abilities[19]</br>";
	      }
	      if ($luck > 100) $luck = 100; //We work with luck as a percentage generally. This may be changed later.
	      $playerdamage = floor($fraction * $bosspower * (rand(85,(115 - floor($luck * 0.3))) / 100));
	      $playerdamage -= $fightrow['sessionbossdefense'];
	      if (!empty($abilities[23])) { //Check for Fortune's Protection (ID 23)
		$guidance = rand(floor(1 + ($luck/10)),100);
		if ($guidance > (100 - floor($fightrow['Echeladder'] / 40))) { //Every forty rungs, increase the chance by 1%. More likely to kick in than the autocrit (15% at max level)
		  $message = $message . $abilities[23] . "</br>";
		  $playerdamage = floor($playerdamage / 2); //Activate anticrit: halve the damage.
		}
	      }
	      if ($playerdamage < 0) $playerdamage = 0; //No healing player with attacks.
	      //Begin tracking special effects on standard damage (such as role abilities) here:
	      if (!empty($abilities[13]) && $playerdamage > 0) { //Spatial Warp activates. Cause some recoil. Note that against BK it's a "flat" value.
		$recoil = 3130 + rand(0,2000);
		$bossdamage += $recoil;
		$message = $message . $fightrow['username'] . "'s $abilities[13]</br>Recoil damage on $userrow[$enemystr]: $recoil</br>";
	      }
	      if (!empty($abilities[2]) && $playerdamage > 0) { //Life's Bounty activates. Reduce damage. (ID 2)
		$playerdamage = floor($playerdamage * 0.85);
		$message = $message . "$fightrow[username]: $abilities[2]</br>";
	      }
	      if (!empty($abilities[4]) && $playerdamage > 0) { //Roll for Dissipate. (ID 4)
		$targetvalue = 100 - (1 + floor($userrow['Echeladder'] / 100) + ($userrow['Godtier'] * 6) + floor($luck/10));
		if ($targetvalue < 50) $targetvalue = 50; //Maximum 50% chance.
		$rand = rand(1,100);
		if ($rand > $targetvalue || $fightrow['dissipatefocus'] == 1) { //Ability triggers
		  mysql_query("UPDATE `Players` SET `dissipatefocus` = 0 WHERE `Players`.`username` = '" . $fightrow['username'] . "' LIMIT 1 ;");
		  //We don't update the actual array item, so dissipatefocus will trigger for every enemy in the combat.
		  $message = $message . "$fightrow[username]: $abilities[4]</br>";
		  $playerdamage = 0;
		}
	      }
	      if ($playerdamage > $fightrow['Gel_Viscosity'] / 2) $playerdamage = floor($fightrow['Gel_Viscosity'] / 2); //Safety net.
	      if ($fightrow['invulnerability'] > 0 && $playerdamage > $fightrow['Gel_Viscosity'] / 3) $playerdamage = floor($fightrow['Gel_Viscosity'] / 3); //Invuln restores the old net.
	      if (!empty($abilities[12]) && $playerdamage > 0) { //Battle Fury activates. Increase offense boost. (ID 12). Note that it activates AFTER the safety net.
		//Formula: First divide incoming damage by 12. Then subtract the lower of the already existing offense boost (divided according to echeladder) and the damage / 15
		$offenseplus = ceil(ceil($playerdamage / 12) - min(ceil($fightrow['offenseboost'] / ceil($fightrow['Echeladder'] / 100)), ceil($playerdamage / 13)));
		$offenseplus = $offenseplus * ($fightrow['Godtier'] + 1); //Multiply by the "standard" non class affected godtier modifier.
		mysql_query("UPDATE `Players` SET `offenseboost` = $fightrow[offenseboost]+$offenseplus WHERE `Players`.`username` = '" . $fightrow['username'] . "' LIMIT 1 ;");
		$fightrow['offenseboost'] += $offenseplus;
		$message = $message . "$fightrow[username]: $abilities[12]</br>";
		$message = $message . "Offense boost: $offenseplus</br>";
	      }
	      if ($playerdamage > 0) $message = $message . "$sessionrow[sessionbossname] inflicts $playerdamage damage on $fightrow[username]!</br>"; //Only do this if the player takes positive damage.
	      if ($fightrow['dreamingstatus'] == "Awake") {
		$fightervialstr = "Health_Vial";
		$fighterdownstr = "down";
	      } else {
		$fightervialstr = "Dream_Health_Vial";
		$fighterdownstr = "dreamdown";
	      }
	      if ($playerdamage >= $fightrow[$fightervialstr]) { //Dead.
		if ($fightrow['motifcounter'] > 0 && $fightrow['Aspect'] = "Hope") { //Hope III is running
		  $message = $message . "$fightrow[username] has been KOed! As they fall to the ground, a shining light envelopes them, restoring their strength.</br>";
		  $fightrow[$fightervialstr] = $fightrow['motifcounter'] * 500;
		  if ($fightrow[$fightervialstr] > $fightrow['Gel_Viscosity']) $fightrow[$fightervialstr] = $fightrow['Gel_Viscosity'];
		  mysql_query("UPDATE `Players` SET `" . $fightervialstr . "` = $fightrow[$fightervialstr] WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		  $powerboost = $fightrow['motifcounter'] * 100;
		  mysql_query("UPDATE `Players` SET `powerboost` = $fightrow[powerboost]+$powerboost WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		  $fightrow['powerboost'] = $fightrow['powerboost'] + $powerboost;
		  mysql_query("UPDATE `Players` SET `motifcounter` = 0 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		  $fightrow['motifcounter'] = 0;
		}
		if (!empty($abilities[20]) && ($chancething <= ceil(($fightrow['Aspect_Vial'] * 100) / $fightrow['Gel_Viscosity']))) { //Hope Endures activated (ID 20)
		  $message = $message . $fightrow['username'] . "'s " . $abilities[20] . "</br>";
		  mysql_query("UPDATE `Players` SET `$fightervialstr` = 1 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;"); //Health to 1
		  $aspectcost = floor($fightrow['Aspect_Vial'] / 2);
		  mysql_query("UPDATE `Players` SET `Aspect_Vial` = $userrow[Aspect_Vial]-$aspectcost WHERE `Players`.`username` = '" . $fightrow['username'] . "' LIMIT 1 ;");
		  $fightrow['Aspect_Vial'] = $fightrow['Aspect_Vial'] - $aspectcost;
		} else {
		  //Handle player KO here.
		  $message = $message . "$fightrow[username] has been KOed!</br>";
		  mysql_query("UPDATE `Players` SET `$fightervialstr` = 1 WHERE `Players`.`username` = '$fightrow[$username]' LIMIT 1 ;"); //Health to 1
		  mysql_query("UPDATE `Players` SET `" . $fighterdownstr . "` = 1 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  mysql_query("UPDATE Players SET `sessionbossengaged` = 0 WHERE `Players`.`username` = '$fightrow[$username]' LIMIT 1;"); //No longer fighting
		  mysql_query("UPDATE `Players` SET `combatconsume` = 0 WHERE `Players`.`username` = '$fightrow[$username]' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `sessionbossactiontaken` = 0 WHERE `Players`.`username` = '$fightrow[$username]' LIMIT 1 ;");
		  $k = 1;
		  while ($k <= $max_enemies) {
		    $enemystr = "enemy" . strval($k) . "name";
		    mysql_query("UPDATE `Players` SET `" . $enemystr . "` = '' WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;"); //Remove enemies from player profile.
		    $k++;
		  }
		  mysql_query("UPDATE `Players` SET `powerboost` = 0 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;"); //Power boosts wear off.
		  mysql_query("UPDATE `Players` SET `offenseboost` = 0 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `defenseboost` = 0 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `temppowerboost` = 0 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `tempoffenseboost` = 0 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `tempdefenseboost` = 0 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `Brief_Luck` = 0 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `invulnerability` = 0 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `motifcounter` = 0 WHERE `Players`.`username` = '$fightrow[$username]' LIMIT 1 ;");
		  $remainingfighters = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'");
		  $chumroll = 0;
		  while ($remainrow = mysql_fetch_array($remainingfighters)) {
		    if ($remainrow['sessionbossengaged'] == 1) $chumroll++;
		  }
		  if ($chumroll == 0) { //No-one left fighting
		    $message = $message . "$sessionrow[sessionbossname] has defeated all attackers, so the strife has been concluded.</br>";
		    mysql_query("UPDATE Sessions SET `sessionbossname` = '' WHERE `Sessions`.`name` = '$userrow[session_name]'");
		    $sessionrow['actiontimer'] = $bosstime; //End ze loop.
		  }
		}
	      } else {
		mysql_query("UPDATE `Players` SET `$fightervialstr` = $fightrow[$fightervialstr]-$playerdamage WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;"); //Damage is dealt.
	      }
	      //Handle boost reduction here.
	      if ($fightrow['motifcounter'] == 0 || $fightrow['Aspect'] != "Void") { //Do not strip boosts and invuln if void III is running.
		if ($enemyrow['boostdrain'] > 0 && ($fightrow['powerboost'] > 0 || $fightrow['offenseboost'] > 0 || $fightrow['defenseboost'] > 0 || $fightrow['temppowerboost'] > 0
						    || $fightrow['tempoffenseboost'] > 0 || $fightrow['tempdefenseboost'] > 0)) { //Enemy drains a certain amount of boost per turn.
		  if ($fightrow['powerboost'] > 0) {
		    $newboost = $fightrow['powerboost'] - $enemyrow['boostdrain'];
		    if ($newboost < 0) $newboost = 0;
		    mysql_query("UPDATE `Players` SET `powerboost` = " . $newboost . " WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  }
		  if ($fightrow['offenseboost'] > 0) {
		    $newboost = $fightrow['offenseboost'] - $enemyrow['boostdrain'];
		    if ($newboost < 0) $newboost = 0;
		    mysql_query("UPDATE `Players` SET `offenseboost` = " . $newboost . " WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  }
		  if ($fightrow['defenseboost'] > 0) {
		    $newboost = $fightrow['defenseboost'] - $enemyrow['boostdrain'];
		    if ($newboost < 0) $newboost = 0;
		    mysql_query("UPDATE `Players` SET `defenseboost` = " . $newboost . " WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  }
		  if ($fightrow['temppowerboost'] > 0) {
		    $newboost = $fightrow['temppowerboost'] - $enemyrow['boostdrain'];
		    if ($newboost < 0) $newboost = 0;
		    mysql_query("UPDATE `Players` SET `temppowerboost` = " . $newboost . " WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  }
		  if ($fightrow['tempoffenseboost'] > 0) {
		    $newboost = $fightrow['tempoffenseboost'] - $enemyrow['boostdrain'];
		    if ($newboost < 0) $newboost = 0;
		    mysql_query("UPDATE `Players` SET `tempoffenseboost` = " . $newboost . " WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  }
		  if ($fightrow['tempdefenseboost'] > 0) {
		    $newboost = $userrow['tempdefenseboost'] - $enemyrow['boostdrain'];
		    if ($newboost < 0) $newboost = 0;
		    mysql_query("UPDATE `Players` SET `tempdefenseboost` = " . $newboost . " WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;");
		  }
		}
		mysql_query("UPDATE `Players` SET `invulnerability` = 0 WHERE `Players`.`username` = '" . $fightrow[$username] . "' LIMIT 1 ;"); //Knock off the invuln.
	      }
	      //Finish handling boost reduction here.
	    }
	    if ($bossdamage == 0 && $bossreduc == 0) { //No player inflicted damage or lowered power. Select a random player to smite.
	      $target = rand(1,count($fighterindex));
	      $message = $message . "$sessionrow[sessionbossname] OBLITERATES $fighterindex[$target]! Looks like not engaging gave them a pretty big opening.</br>";
	      //KO the obliterated target here.
	      mysql_query("UPDATE `Players` SET `Health_Vial` = 1 WHERE `Players`.`username` = '$fighterindex[$target]' LIMIT 1 ;"); //Health to 1
	      mysql_query("UPDATE `Players` SET `Dream_Health_Vial` = 1 WHERE `Players`.`username` = '$fighterindex[$target]' LIMIT 1 ;"); //Dream health to 1. Obliterated means obliterated :p
	      mysql_query("UPDATE `Players` SET `down` = 1 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `dreamdown` = 1 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `sessionbossengaged` = 0 WHERE `Players`.`username` = '$fighterindex[$target]' LIMIT 1;"); //No longer fighting
	      mysql_query("UPDATE `Players` SET `combatconsume` = 0 WHERE `Players`.`username` = '$fighterindex[$target]' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `sessionbossactiontaken` = 0 WHERE `Players`.`username` = '$fighterindex[$target]' LIMIT 1 ;");
	      $k = 1;
	      while ($k <= $max_enemies) {
		$enemystr = "enemy" . strval($k) . "name";
		mysql_query("UPDATE `Players` SET `" . $enemystr . "` = '' WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;"); //Remove enemies from player profile.
		$k++;
	      }
	      mysql_query("UPDATE `Players` SET `powerboost` = 0 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;"); //Power boosts wear off.
	      mysql_query("UPDATE `Players` SET `offenseboost` = 0 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `defenseboost` = 0 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `temppowerboost` = 0 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `tempoffenseboost` = 0 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `tempdefenseboost` = 0 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `Brief_Luck` = 0 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `invulnerability` = 0 WHERE `Players`.`username` = '" . $fighterindex[$target] . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `motifcounter` = 0 WHERE `Players`.`username` = '$fighterindex[$target]' LIMIT 1 ;");
	      $remainingfighters = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'");
	      $chumroll = 0;
	      while ($remainrow = mysql_fetch_array($remainingfighters)) {
		if ($remainrow['sessionbossengaged'] == 1) $chumroll++;
	      }
	      if ($chumroll == 0) { //No-one left fighting
		$message = $message . "$sessionrow[sessionbossname] has defeated all attackers, so the strife has been concluded.</br>";
		mysql_query("UPDATE Sessions SET `sessionbossname` = '' WHERE `Sessions`.`name` = '$userrow[session_name]'");
		$sessionrow['actiontimer'] = $bosstime; //End ze loop.
	      }
	    }
	    //Enemy executes EOT effects here (special boss effects will be placed here in future)
	    if ($enemyrow['boostdrain'] > 0) $message = $message . "$sessionrow[sessionbossname] reduces your power boosts!</br>"; //This is actually done in the place where damage is inflicted.
	    if ($enemyrow['healthrecover'] > 0 && $sessionrow['sessionbosshealth'] < $sessionrow['sessionbossmaxhealth']) {
	      $message = $message . "$sessionrow[sessionbossname] recovers $enemyrow[healthrecover] health!</br>";
	      $bossdamage -= $enemyrow['healthrecover'];
	    }
	    if ($enemyrow['invulndrain'] == 1) $message = $message . "$sessionrow[sessionbossname] removes your invulnerability effects!</br>"; //This is done after damage too.
	    if ($enemyrow['powerrecover'] > 0 && $sessionrow['sessionbosspower'] < $sessionrow['sessionbossmaxpower']) {
	      $message = $message . "$sessionrow[sessionbossname] recovers $enemyrow[powerrecover] power!</br>";
	      $powerdamage -= $enemyrow['powerrecover'];
	    }
	    //Boss doing stuff ends here. Begin performing end-of-turn routines on fighters (tick down temporary boosts, reset consumable and action uses, regenerate aspect vial)
	    $fighters = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]' AND `Players`.`sessionbossengaged` = 1");
	    while ($fightrow = mysql_fetch_array($fighters)) {
	      $abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$fightrow[Aspect]','All') AND `Abilities`.`Class` IN ('$fightrow[Class]','All') 
AND `Abilities`.`Rungreq` BETWEEN 0 AND $fightrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $fightrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
	      $abilities = array(0 => "Null ability. No, not void.");
	      while ($temp = mysql_fetch_array($abilityresult)) {
		$abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
		//as pulling the correct element out of the abilities array via the ID. An ability with an empty usage message will be unusable since the empty function will spit empty at you.
	      }
	      if ($fightrow['dreamingstatus'] == "Awake") { //Set dreaming status strings for fighter.
		$fightervialstr = "Health_Vial";
		$fighterdownstr = "down";
	      } else {
		$fightervialstr = "Dream_Health_Vial";
		$fighterdownstr = "dreamdown";
	      }
	      $luck = ceil($fightrow['Luck'] + $fightrow['Brief_Luck']); //Calculate the player's luck total. Paranoia: Make sure we don't somehow have a non-integer.
	      if (!empty($abilities[19])) { //Light's Favour activates. Increase luck.
		$luck += floor($fightrow['Echeladder'] / 30);
		$message = $message . "$abilities[19]</br>";
	      }
	      if ($luck > 100) $luck = 100; //We work with luck as a percentage generally. This may be changed later. (Need luck for space motif)
	      if ($fightrow['motifcounter'] == 0 || $fightrow['Aspect'] != "Time") { //Do not tick boosts if Time III is running
		if ($fightrow['temppowerduration'] > 0) {
		  mysql_query("UPDATE `Players` SET `temppowerduration` = $fightrow[temppowerduration]-1 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		} elseif ($fightrow['temppowerboost'] != 0) {
		  mysql_query("UPDATE `Players` SET `temppowerboost` = 0 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		}
		if ($fightrow['tempoffenseduration'] > 0) {
		  mysql_query("UPDATE `Players` SET `tempoffenseduration` = $fightrow[tempoffenseduration]-1 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		} elseif ($fightrow['tempoffenseboost'] != 0) {
		  mysql_query("UPDATE `Players` SET `tempoffenseboost` = 0 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		}
		if ($fightrow['tempdefenseduration'] > 0) {
		  mysql_query("UPDATE `Players` SET `tempdefenseduration` = $fightrow[tempdefenseduration]-1 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		} elseif ($fightrow['tempdefenseboost'] != 0) {
		  mysql_query("UPDATE `Players` SET `tempdefenseboost` = 0 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		}
	      }
	      if ($fightrow['Aspect_Vial'] + ceil($fightrow['Gel_Viscosity'] / 5) > $fightrow['Gel_Viscosity']) {
		$newaspectvial = $fightrow['Gel_Viscosity'];
	      } else {
		$newaspectvial = $fightrow['Aspect_Vial'] + ceil($fightrow['Gel_Viscosity'] / 5);
	      }
	      mysql_query("UPDATE `Players` SET `Aspect_Vial` = $newaspectvial WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `combatconsume` = 0 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `sessionbossactiontaken` = 0 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `sessionbossdefense` = 0 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;"); //Reset this.
	      //Begin checking passive abilities that trigger at end of turn here.
	      if (!empty($abilities[11]) && ($fightrow['powerboost'] < 0 || $fightrow['offenseboost'] < 0 || $fightrow['defenseboost'] < 0 || $fightrow['temppowerboost'] < 0 || $fightrow['tempoffenseboost'] < 0 || $fightrow['tempdefenseboost'] < 0)) { //There's a boost below zero. Trigger Blockhead (ID 11)
		$message = $message . "$abilities[11]</br>";
		$boosttypes = array(0 => "powerboost", "offenseboost", "defenseboost", "temppowerboost", "tempoffenseboost", "tempdefenseboost");
		$type = 0;
		while ($type < count($boosttypes)) {
		  $boost = $boosttypes[$type];
		  if ($fightrow[$boost] < 0) {
		    $fightrow[$boost] += floor($fightrow['Echeladder'] / 2);
		    if ($fightrow[$boost] > 0) $fightrow[$boost] = 0;
		    mysql_query("UPDATE `Players` SET `$boost` = $fightrow[$boost] WHERE `Players`.`username` = '" . $fightrow['username'] . "' LIMIT 1 ;");
		  }
		  $type++;
		}
	      }
	      //Finish checking passive abilities that trigger at end of turn here.
	      //We do tier 3 motifs here, they may have different effects. Note that they do not affect aggro or count towards "maximum damage" and any damage dealt is not massive resisted.
	      if ($fightrow['motifcounter'] > 0) { //Motif is active.
		$motifresult = mysql_query("SELECT * FROM Fraymotifs WHERE `Fraymotifs`.`Aspect` = '" . $fightrow['Aspect'] . "'");
		$motifrow = mysql_fetch_array($motifresult);
		$usagestr = "Turn $fightrow[motifcounter] of $fightrow[username]'s $motifrow[solo3]:</br>";
		switch ($fightrow['Aspect']) {
		case "Breath": //Breathless Battaglia
		  if (($fightrow['motifcounter'] % 5) != 0) { //Drain power.
		    $message = $message . "$fightrow[username]'s fraymotif continues! " . "The fraymotif steals the breath from $sessionrow[sessionbossname].</br>";
		    $powerdrain = 2500;
		    $powerdamage += $powerdrain;
		    mysql_query("UPDATE `Players` SET `motifvar` = $fightrow[motifvar]+$powerdrain WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		  } else { //Use drained power to attack.
		    $message = $message . "$fightrow[username]'s fraymotif continues! " . "The stolen power is unleashed in a massive tornado!</br>";
		    $damage = $fightrow['motifvar'] * 5; //Results in 50k damage every five rounds.
		    $bossdamage += $damage;
		    mysql_query("UPDATE `Players` SET `motifvar` = 0 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;"); //Power expended.
		  }
		  break;
		case "Heart":
		  $message = $message . "$fightrow[username]'s fraymotif continues! " . "The melody brings up reserves of power from deep within $fightrow[username].</br>";
		  $aspectregen = $fightrow['motifcounter'] * 200;
		  if ($aspectregen > 2000) $aspectregen = 2000; //Rampup is complete after ten turns.
		  $newaspect = $fightrow['Aspect_Vial'] + $aspectregen;
		  if ($newaspect > $fightrow['Gel_Viscosity']) $newaspect = $fightrow['Gel_Viscosity'];
		  mysql_query("UPDATE `Players` SET `Aspect_Vial` = $newaspect WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		  $fightrow['Aspect_Vial'] = $newaspect; //Juuuust in case.
		  break;
		case "Life":
		  $message = $message . "$fightrow[username]'s fraymotif continues! " . "Life infuses $fightrow[username], regenerating $fightrow[username]'s health.</br>";
		  $regen = $fightrow['motifcounter'] * 200;
		  if ($regen > 2000) $regen = 2000; //Rampup is complete after ten turns.
		  $newhealth = $fightrow[$fightervialstr] + $regen;
		  if ($newhealth > $fightrow['Gel_Viscosity']) $newhealth = $fightrow['Gel_Viscosity'];
		  mysql_query("UPDATE `Players` SET `$fightervialstr` = $newhealth WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		  $fightrow[$fightervialstr] = $newhealth; //Juuuust in case.
		  break;
		case "Hope":
		  $message = $message . "$fightrow[username]'s fraymotif continues! " . "The aura of Hope surrounding $fightrow[username] grows stronger.</br>";
		  //Hope's effect is handled up in the death code.
		  break;
		case "Light": //This enables critical hits. Handled in enemy damage code.
		  $message = $message . "$fightrow[username]'s fraymotif continues! " . "The song makes $fightrow[username]'s attacks feel luckier, somehow.";
		  break;
		case "Mind":
		  $message = $message . "$fightrow[username]'s fraymotif continues! The music helps $fightrow[username] relax and focus on the task at hand. That being delivering a righteous smackdown.</br>";
		  $powerboost = 1000;
		  mysql_query("UPDATE `Players` SET `powerboost` = $fightrow[powerboost]+$powerboost WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		  $fightrow['powerboost'] += $powerboost; //Juuuust in case.
		  break;
		case "Blood":
		  $message = $message . "$fightrow[username]'s fraymotif continues! Life force is drained from $sessionrow[sessionbossname], flowing through $fightrow[username] as power.</br>";
		  $damage = 8000;
		  $bossdamage += $damage;
		  $boost = floor($damage / 20);
		  mysql_query("UPDATE `Players` SET `powerboost` = $fightrow[powerboost]+$boost WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		  $fightrow['powerboost'] += $boost; //Juuuust in case.
		  break;
		case "Doom":
		  $message = $message . "$fightrow[username]'s fraymotif continues! " . "The slow, toxic inevitability of Death afflicts $sessionrow[sessionbossname].</br>";
		  $enemies = 1;
		  $damage = ceil(2000 * $fightrow['motifcounter']); //Operates on bossmax rather than the actual max because the actual max is damn near impossible to balance for.
		  //Assuming bossmax of five million, this inflicts 2500 damage times the number of rounds the motif has been going for.
		  $bossdamage += $damage;
		  break;
		case "Rage":
		  if ($fightrow['motifcounter'] == 1) {
		    $message = $message . "$fightrow[username]'s fraymotif continues! " . "A deep, primordial fury wells up within $fightrow[username].</br>";
		  } else {
		    $message = $message . "$fightrow[username]'s fraymotif continues! " . "$fightrow[username]'s fury slowly subsides.</br>";
		  }
		  $offenseboost = (10 - $fightrow['motifcounter']) * 1200;
		  if ($offenseboost <= 0) {
		    $offenseboost = 0; //Rampdown is complete after ten turns.
		    mysql_query("UPDATE `Players` SET `motifcounter` = 0 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;"); //Fraymotif over.
		  }
		  mysql_query("UPDATE `Players` SET `offenseboost` = $offenseboost WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;");
		  $fightrow['offenseboost'] = $offenseboost; //Juuuust in case.
		  break;
		case "Void": //Suppresses special monster abilities. Handled in the monster ability area.
		 $message = $message . "$fightrow[username]'s fraymotif continues! " . "The power of Void protects $fightrow[username] from $sessinorow[sessionbossname]'s abilities.</br>";
		  break;
		  //NOTE - Just space and time to do here, then need to go apply Hope, Time, and Void to the calculation.
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
		    $itemstr = "THISTLES OF ZILLYWICH";
		    $zillystr = "ZILLYWICH";
		    $damage = 15000; 
		    break;
		  case 96:
		    $itemstr = "FLINTLOCKS OF ZILLYHAU";
		    $zillystr = "ZILLYHAU";
		    $damage = 18000; 
		    break;
		  case 97:
		    $itemstr = "BLUNDERBUSS OF ZILLYWIGH";
		    $zillystr = "ZILLYWIGH";
		    $damage = 21000; 
		    break;
		  case 98:
		    $itemstr = "CUTLASS OF ZILLYWAIR";
		    $zillystr = "ZILLYWAIR";
		    $damage = 24000; 
		    break;
		  case 99:
		    $itemstr = "BATTLESPORK OF ZILLYWUT";
		    $zillystr = "ZILLYWUT";
		    $damage = 27000; 
		    break;
		  case 100:
		    $itemstr = "WARHAMMER OF ZILLYHOO";
		    $zillystr = "ZILLYHOO";
		    $damage = 30000; 
		    break;
		  default: //Code to randomly select an item goes here since we didn't get a zillyweapon. :(
		    $itemresult = ("SELECT * FROM Captchalogue WHERE `Captchalogue`.`power` = 9999;");
		    $backup = $itemresult;
		    $items = 0;
		    while ($itemrow = mysql_fetch_array($itemresult)) {
		      $items++;
		    }
		    $itemresult = $backup;
		    $randomthing = 2;
		    while ($randomthing != 1) {
		      while ($itemrow = mysql_fetch_array($itemresult)) {
			$randomthing = rand(1,$items);
			$items--;
		      }
		    }
		    $itemstr = $itemrow['name'];
		    $damage = $itemrow['power'] + floor(($itemrow['aggrieve'] + $itemrow['aggress'] + $itemrow['assail'] + $itemrow['assault']) / 20); 
		    break;
		  }
		  $message = $message . "$fightrow[username]'s fraymotif continues! " . $itemstr . " appears out of thin air and assaults $sessionrow[sessionbossname], then disappears.</br>";
		  $bossdamage += $damage;
		  break;
		case "Time": //Prevents boosts from ticking down as well, this is handled elsewhere. Prints "INFINITY TURNS" as the duration.
		  $usagestr = $usagestr . "The sonata extends and magnifies your temporary boosts.</br>";
		  $fightrow['temppowerboost'] += 50;
		  $fightrow['tempoffenseboost'] += 50;
		  $fightrow['tempdefenseboost'] += 50;
		  mysql_query("UPDATE `Players` SET `temppowerboost` = $fightrow[temppowerboost] WHERE `Players`.`username` = '" . $fightrow['username'] . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `tempoffenseboost` = $fightrow[tempoffenseboost] WHERE `Players`.`username` = '" . $fightrow['username'] . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `tempdefenseboost` = $fightrow[tempdefenseboost] WHERE `Players`.`username` = '" . $fightrow['username'] . "' LIMIT 1 ;");
		  break;
		default:
		  $message = $message . "Player $fightrow[username]'s aspect $fightrow[Aspect] unrecognized. This is probably a bug.</br>";
		  break;
		}
		mysql_query("UPDATE `Players` SET `motifcounter` = $fightrow[motifcounter]+1 WHERE `Players`.`username` = '$fightrow[username]' LIMIT 1 ;"); //Increment the motif counter by one.
		$message = $message . $usagestr;
	      }
	    }
	    //Finish end of turn checks on players here. Finally, update enemy's values. (This happens down here so EOTs on player fraymotifs can be taken into account)
	    //May check damage and power against maximums here. May not. Probably should to handle health vial.
	    if ($sessionrow['sessionbosshealth'] - $bossdamage > $sessionrow['sessionbossmaxhealth']) $bossdamage = $sessionrow['sessionbosshealth'] - $sessionrow['sessionbossmaxhealth'];
	    if ($sessionrow['sessionbosshealth'] < $bossdamage) $bossdamage = $sessionrow['sessionbosshealth'] - 1; //Cannot have been slain if he wasn't above.
	    mysql_query("UPDATE Sessions SET `sessionbosshealth` = $sessionrow[sessionbosshealth]-$bossdamage WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1;"); //Do damage
	    mysql_query("UPDATE Sessions SET `sessionbosspower` = $sessionrow[sessionbosspower]-$powerdamage WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1;"); //Reduce power
	    mysql_query("UPDATE Players SET `enemy1power` = $bossmax WHERE `Players`.`kingvote` = 1 AND `Players`.`session_name` = '$userrow[session_name]'"); //Reset dummies on all fighters.
	    mysql_query("UPDATE Players SET `enemy1health` = $bossmax WHERE `Players`.`kingvote` = 1 AND `Players`.`session_name` = '$userrow[session_name]'");
	    $sessionrow['actiontimer'] += $bosstimer;
	    $message = $message . "----------</br>";
	    if ($turns >= 5) $sessionrow['actiontimer'] = time(); //Only execute five turns in succession.
	  }
	}
	mysql_query("UPDATE Sessions SET `actiontimer` = $sessionrow[actiontimer] WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1;"); //Update boss's action timer.
	mysql_query("UPDATE Sessions SET `mutexplayer` = '' WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1;"); //Free the mutex.
	mysql_query("UPDATE Sessions SET `combatlog` = '$message' WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1;"); //Log the round.
	$result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $username . "' LIMIT 1;");
	while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
	  if ($row['username'] == $username) { //Paranoia: Double-check.
	    $userrow = $row; //Reload the user row since it may well have been changed in several places.
	  }
	}
      }
    }
  } //This ends the code that executes if user is battling a session boss.
  //NOTE - Time's encounter reducing ability activates here, but performing an ability search when that's the only thing that ever will affect the timer constantly seems wasteful.
  //So we just check directly.
  $up = False;
  $time = time();
  if ($userrow['Aspect'] == "Time") {
    $interval = 1080;
  } else { 
    $interval = 1200; //This is where the interval between encounter ticks is set.
  }
  $lasttick = $userrow['lasttick'];
  $encounters = $userrow['encounters'];
  if ($lasttick != 0) {
    while ($time - $lasttick > $interval) { //Attempt to tick up once per 20 minutes.
      $encounters += 1;
      $lasttick += $interval;
    }
  } else { //Player has not had a tick yet.
    $lasttick = $time;
  }
  if ($encounters > $userrow['encounters'] && ($userrow['down'] == 1 || $userrow['dreamdown'] == 1)) { //Both downs recover after a single encounter is earned.
    $encounters -= 1;
    mysql_query("UPDATE `Players` SET `down` = 0, `dreamdown` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Player recovers.
    $up = True;
  }
  if ($encounters > 100) $encounters = 100;
  if ($lasttick != $userrow['lasttick']) {
    mysql_query("UPDATE `Players` SET `encounters` = $encounters, `lasttick` = $lasttick WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
    $userrow['encounters'] = $encounters;
  }
}
