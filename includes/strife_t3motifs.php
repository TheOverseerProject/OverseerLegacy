<?php
if ($userrow['motifcounter'] > 0) { //Player's tier 3 fraymotif is active. The effect of the fraymotif is stored and performed here. "motifvar" is a wildcard variable that may or
	//may not be used depending on what the specific fraymotif does. Some motifs have their effect elsewhere in the file.
	$motifresult = mysql_query("SELECT * FROM Fraymotifs WHERE `Fraymotifs`.`Aspect` = '" . $userrow['Aspect'] . "'");
	$motifrow = mysql_fetch_array($motifresult);
	if (!empty($motifrow['solo3'])) {
	  $usagestr = "Turn $userrow[motifcounter] of $motifrow[solo3]:</br>";
	} else {
	  $usagestr = "Turn $userrow[motifcounter] of $userrow[Aspect] III:</br>";
	}
	switch ($userrow['Aspect']) {
	case "Breath": //Breathless Battaglia
	  if (($userrow['motifcounter'] % 5) != 0) { //Drain power.
	    $usagestr = $usagestr . "The fraymotif steals the breath from your enemies.</br>";
	    $enemies = 1;
	    $powerdrain = 1000;
	    $powerdrained = 0;
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      if (!empty($userrow[$enemystr])) {
		$enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $userrow[$enemystr] . "'");
		$enemyrow = mysql_fetch_array($enemyresult);
		$powerstr = "enemy" . strval($enemies) . "power";
		if (!empty($enemyrow)) { //Not a grist enemy.
		  if ($enemyrow['reductionresist'] != 0 && $powerdrain > $enemyrow['reductionresist']) { //Enemy resists power reduction.
		    $usagestr = $usagestr . $userrow[$enemystr] . " resists the power reduction!</br>";
		    $powerdrain = $enemyrow['reductionresist'];
		  }
		}
		if ($powerdrain > $userrow[$powerstr]) $powerdrain = $userrow[$powerstr];
		$userrow[$powerstr] = $userrow[$powerstr]-$powerdrain; //Update this so that any further uses (say from Sophia power boosting) work as intended.
		$powerdrained = $powerdrained + $powerdrain;
	      }
	      $enemies++;
	    }
	    mysql_query("UPDATE `Players` SET `motifvar` = $userrow[motifvar]+$powerdrained WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		$userrow['motifvar'] += $powerdrained;
	  } else { //Use drained power to attack.
	    $usagestr = $usagestr . "The stolen power is unleashed in a massive tornado!</br>";
	    $enemies = 1;
	    $damage = $userrow['motifvar'] * 10; //Results in 40k after four rounds of stealing from one enemy, or a whopping 200k after four turns of stealing from five.
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $userrow[$enemystr] . "'");
	      $enemyrow = mysql_fetch_array($enemyresult);
	      $healthstr = "enemy" . strval($enemies) . "health";
	      $maxhealthstr = "enemy" . strval($enemies) . "maxhealth";
	      if (!empty($enemyrow)) { //Not a grist enemy.
		if ($enemyrow['massiveresist'] != 100 && $damage > (floor($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
		  $usagestr = $usagestr . $userrow[$enemystr] . " resists the massive damage!</br>";
		  $damage = floor(($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist']);
		}
	      }
	      if ($damage > $userrow[$healthstr]) $damage = $userrow[$healthstr] - 1;
	      $userrow[$healthstr] = $userrow[$healthstr]-$damage;
	      $enemies++;
	    }
		$userrow['motifvar'] = 0;
	  }
	  break;
	case "Heart":
 	  $usagestr = $usagestr . "The melody brings up reserves of power from deep within you.</br>";
	  $aspectregen = $userrow['motifcounter'] * 600;
	  if ($aspectregen > 6000) $aspectregen = 6000; //Rampup is complete after ten turns.
	  $newaspect = $userrow['Aspect_Vial'] + $aspectregen;
	  if ($newaspect > $userrow['Gel_Viscosity']) $newaspect = $userrow['Gel_Viscosity'];
	  $userrow['Aspect_Vial'] = $newaspect; //Juuuust in case.
	  break;
	case "Life":
	  $usagestr = $usagestr . "Life infuses you, regenerating your health.</br>";
	  $regen = $userrow['motifcounter'] * 200;
	  if ($regen > 2000) $regen = 2000; //Rampup is complete after ten turns.
	  $newhealth = $userrow[$healthvialstr] + $regen;
	  if ($newhealth > $userrow['Gel_Viscosity']) $newhealth = $userrow['Gel_Viscosity'];
	  $userrow[$healthvialstr] = $newhealth; //Juuuust in case.
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
	  $userrow['powerboost'] += $powerboost; //Juuuust in case.
	  break;
	case "Blood":
	  $usagestr = $usagestr . "Life force is drained from your enemies, flowing through you as power.</br>";
	  $damage = 8000;
	  $boost = 0;
	  $enemies = 1;
	  while ($enemies <= $max_enemies) {
	    $enemystr = "enemy" . strval($enemies) . "name";
	    if (!empty($userrow[$enemystr])) {
	      $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $userrow[$enemystr] . "'");
	      $enemyrow = mysql_fetch_array($enemyresult);
	      $healthstr = "enemy" . strval($enemies) . "health";
	      $maxhealthstr = "enemy" . strval($enemies) . "maxhealth";
	      if (!empty($enemyrow)) { //Not a grist enemy.
		if ($enemyrow['massiveresist'] != 100 && $damage > (floor($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
		  $usagestr = $usagestr . $userrow[$enemystr] . " resists the massive damage!</br>";
		  $damage = floor(($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist']);
		}
	      }
	      if ($damage > $userrow[$healthstr]) $damage = $userrow[$healthstr] - 1;
	      $userrow[$healthstr] = $userrow[$healthstr] - $damage;
	      $boost = $boost + floor($damage / 20);
	    }
	    $enemies++;
	  }
	  $userrow['powerboost'] += $boost; //Juuuust in case.
	  break;
	case "Doom":
	  $usagestr = $usagestr . "The slow, toxic inevitability of Death afflicts your enemies.</br>";
	  $enemies = 1;
	  while ($enemies <= $max_enemies) {
	    $enemystr = "enemy" . strval($enemies) . "name";
	    $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $userrow[$enemystr] . "'");
	    $enemyrow = mysql_fetch_array($enemyresult);
	    $healthstr = "enemy" . strval($enemies) . "health";
	    $maxhealthstr = "enemy" . strval($enemies) . "maxhealth";
	    $damage = ceil($userrow[$maxhealthstr] * 0.0625 * $userrow['motifcounter']);
	    if (!empty($enemyrow)) { //Not a grist enemy.
	      if ($enemyrow['massiveresist'] != 100 && $damage > (floor($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
		$usagestr = $usagestr . $userrow[$enemystr] . " resists the massive damage!</br>";
		$damage = floor(($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist']);
	      }
	    }
	    if ($damage > $userrow[$healthstr]) $damage = $userrow[$healthstr] - 1;
	    $userrow[$healthstr] = $userrow[$healthstr]-$damage;
	    $enemies++;
	  }
	  break;
	case "Rage":
	  if ($userrow['motifcounter'] == 1) {
	    $usagestr = $usagestr . "A deep, primordial fury wells up within you.</br>";
	  } else {
	    $usagestr = $usagestr . "The fury slowly subsides.</br>";
	  }
	  $offenseboost = (10 - $userrow['motifcounter']) * 1200;
	  if ($offenseboost <= 0) {
	    $offenseboost = 0; //Rampdown is complete after ten turns.
		$userrow['motifcounter'] = 0;
	  }
	  $userrow['offenseboost'] = $offenseboost; //Juuuust in case.
	  break;
	case "Void": //Suppresses special monster abilities. Handled in the monster ability area.
	  $usagestr = $usagestr . "The power of Void suppresses enemy abilities.</br>";
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
	  $usagestr = $usagestr . $itemstr . " appears out of thin air and assaults your enemies, then disappears.</br>";
	  $enemies = 1;
	  while ($enemies <= $max_enemies) {
	    $enemystr = "enemy" . strval($enemies) . "name";
	    $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $userrow[$enemystr] . "'");
	    $enemyrow = mysql_fetch_array($enemyresult);
	    $healthstr = "enemy" . strval($enemies) . "health";
	    $maxhealthstr = "enemy" . strval($enemies) . "maxhealth";
	    if (!empty($enemyrow)) { //Not a grist enemy.
	      if ($enemyrow['massiveresist'] != 100 && $damage > (floor($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
		$usagestr = $usagestr . $userrow[$enemystr] . " resists the massive damage!</br>";
		if ($zilly >= 95) {
		  $usagestr = $usagestr . "But THE GLORY OF " . $zillystr . " penetrates your foe's resistance!"; //Treat resistance as though it's half as effective.
		  if ($damage > floor(($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'] * 2)) $damage = floor(($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'] * 2);
		} else {
		  $damage = floor(($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist']);
		}
	      }
	    }
	    if ($damage > $userrow[$healthstr]) $damage = $userrow[$healthstr] - 1;
	    $userrow[$healthstr] = $userrow[$healthstr]-$damage;
	    $enemies++;
	  }
	  break;
	case "Time": //Prevents boosts from ticking down as well, this is handled elsewhere. Prints "INFINITY TURNS" as the duration.
	  $usagestr = $usagestr . "The sonata extends and magnifies your temporary boosts.</br>";
	  $userrow['temppowerboost'] += 50;
	  $userrow['tempoffenseboost'] += 50;
	  $userrow['tempdefenseboost'] += 50;
	  break;
	default:
	  $message = $message . "Player aspect $userrow[Aspect] unrecognized. This is probably a bug.</br>";
	  break;
	}
	$userrow['motifcounter']++;
	$message = $message . $usagestr;
      } ?>