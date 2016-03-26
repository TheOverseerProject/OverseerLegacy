<?php
require_once("header.php");
require_once("includes/fieldparser.php");
require_once("includes/glitches.php");
require 'additem.php';
$alreadywritten = false; //lol.
if (empty($_SESSION['username'])) {
  echo "Log in to use consumable items.</br>";
} elseif ($userrow['dreamingstatus'] != "Awake") {
  echo "Your dream self can't access your sylladex!";
} else {
	$userrow = parseEnemydata($userrow);
  $abilities = loadAbilities($userrow);
  $max_enemies = 5; //Again, this is not ideal.
  //--Begin consuming code here.--
  $dontechoconsumemenu = false; //in case there's an item that makes it impossible for further consumption
  if (!empty($_POST['consume'])) { //Consuming time!
    $fail = False;
    $donotconsume = False;
    if (strpos($_POST['consume'], "inv") === false) { //player is trying to consume from outside their inventory!
    	echo "Look at you, trying to be clever! Unfortunately, you can only consume items from your inventory.<br />";
    	$fail = true;
    }
    if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {//User strifing
      if ($userrow['combatconsume'] == 1) { //Already used a consumable this round.
	  	$bonusconsumestr = "PLAYER:BONUSCONSUME|";
		if (strpos($userrow['strifestatus'], $bonusconsumestr) !== False) { //Player has a bonus consumable usage
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
		} else { //Only triggers if no bonus consumable usage was found.
			echo "You have already used a consumable during this round of strife!";
			$fail = True;
		}
      }
    }
    if ($fail != True) {
      switch ($userrow[$_POST['consume']]) { //Determine the consumable and act accordingly.
      case "Klatchian Coffee":
      	echo "As you drink the coffee, everything begins to clear up... AAAAAAAA-<br />";
      	if ($userrow['powerboost'] != 0) {
      		$offense = $userrow['powerboost'] * -1; //negates the power boost and gives it to offense
      		$defense = $userrow['powerboost']; //retains same penalty/boost for defense
      		mysql_query("UPDATE Players SET powerboost = 0, offenseboost = $offense, defenseboost = $defense WHERE username = '$username'");
      	}
      	if ($userrow['temppowerboost'] != 0) {
      		$tempoffense = $userrow['temppowerboost'] * -1; //negates the power boost and gives it to offense
      		$tempdefense = $userrow['temppowerboost']; //retains same penalty/boost for defense
      		$duration = ceil($userrow['temppowerduration'] / 2);
      		mysql_query("UPDATE Players SET temppowerboost = 0, temppowerduration = 0, tempoffenseboost = $tempoffense, tempoffenseduration = $duration, tempdefenseboost = $tempdefense, tempdefenseduration = $duration WHERE username = '$username'");
      	}
      	if ($userrow['invulnerability'] > 0) { //removes invuln so it can't be easily abused for ungodly offense with no consequence
      		mysql_query("UPDATE Players SET invulnerability = 0 WHERE username = '$username'");
      	}
      	break;
      case "Cruxite Faygo":
      	echo "This Faygo has a very unique taste to it. The only way to describe it is... miracles.<br />";
      	if ($userrow['Echeladder'] < 612) { //Below Echeladder cap
      		echo "Whoa! Shortly after downing the Faygo, you ascend one rung on your Echeladder. It's a miracle!";
      		climbEcheladder($userrow, 1);
      	} else {
					echo "...but nothing else happens. You don't seem to be in need of a miracle anymore, or at least not the kind of miracle that this provides.<br />";
					if (!empty($abilities[19]) && $userrow['Luck'] < 20) { //Light's Favour catchup activates. This is to set the luck of players at 612 who have insufficient quantities.
	  				echo "Your Luck catches up with you, however.<br />";
	  				mysql_query("UPDATE `Players` SET `Luck` = 20 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
					}
      	}
      	break;
      case "Fruit Gushers - Ultimate Aquasteel Neptunium Destrucalamitous Chaos Revivalation of the Crosbitonome": //dear god this is a long name
      	$boost = 49999; //yes, this overwrites any existing boost, as if you need anything else
      	$health = $userrow['Gel_Viscosity'];
      	$status = $userrow['strifestatus'] . "PLAYER:CONFUSE|";
      	mysql_query("UPDATE `Players` SET `powerboost` = $boost, `offenseboost` = 0, `defenseboost` = 0, `temppowerboost` = 0, `tempoffenseboost` = 0, `tempdefenseboost` = 0, `Health_Vial` = $health, `Aspect_Vial` = $health, `strifestatus` = '$status' WHERE `Players`.`username` = '$username'");
      	echo "Much like how the name broke the character limit, you're pretty sure you broke some kind of power limit by eating these gushers.<br />";
      	break;
      case "Fizzy Cola Gushers Extreme":
      	echo "Mere moments after ingesting, your entire being is overrun with a bubbling sensation! You feel empowered, but your senses become hazy...<br />";
      	$boost = $userrow['Echeladder'] * 2;
      	$boost += $userrow['powerboost'];
      	$status = $userrow['strifestatus'] . "PLAYER:CONFUSE|";
      	mysql_query("UPDATE `Players` SET `powerboost` = $boost, `strifestatus` = '$status' WHERE `Players`.`username` = '$username'");
      	break;
      case "Faygo - Fountain of Youth Flavor":
      	if ($userrow['Aspect_Vial'] > 0) {
      		$diff = $userrow['Gel_Viscosity'] - $userrow['Health_Vial'];
      		if ($userrow['Aspect_Vial'] > $diff) $asdrain = $diff;
      		else $asdrain = $userrow['Aspect_Vial'];
      		mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$asdrain, `Aspect_Vial` = $userrow[Aspect_Vial]-$asdrain WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      		echo "You drink the Fountain of Youth Faygo. It draws on your Aspect Vial to replenish your health!<br />";
      	} else {
      		echo "You would drink this, but you're not sure you could magic up any youth out of thin air with your Aspect Vial completely depleted.<br />";
      		$donotconsume = true;
      	}
      	break;
      case "Dev Request Flag":
      	echo "You hold up the Dev Request Flag, which shines a piercing white light in every direction. ";
      	if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
      		$devresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = 'Blahdev'");
      		$devrow = mysql_fetch_array($devresult);
      		if ($devrow['enemydata'] == "" && $devrow['aiding'] == "" && $username != "Blahdev") {
      			echo "Blahdev emerges from the Furthest Ring with his " . $devrow[$devrow['equipped']] . " to aid you in your current strife!<br />";
      			if ($userrow['aiding'] != "") $aidman = $userrow['aiding'];
      			else $aidman = $username;
      			mysql_query("UPDATE `Players` SET `aiding` = '$aidman' WHERE `Players`.`username` = 'Blahdev'");
      			$donotconsume = true;
      		} else {
      			$devresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = 'The Overseer'");
      			$devrow = mysql_fetch_array($devresult);
      			if ($devrow['enemydata'] == "" && $devrow['aiding'] == "" && $username != "The Overseer") {
      				echo "The Overseer emerges from the Furthest Ring with his " . $devrow[$devrow['equipped']] . " to aid you in your current strife!<br />";
      				if ($userrow['aiding'] != "") $aidman = $userrow['aiding'];
      				else $aidman = $username;
      				mysql_query("UPDATE `Players` SET `aiding` = '$aidman' WHERE `Players`.`username` = 'The Overseer'");
      				$donotconsume = true;
      			} else {
      				echo "Unfortunately, nobody answers the call. Maybe they're busy... you'll have to try again later!<br />";
      				$donotconsume = true;
      			}
      		}
      	} else { //uh oh, player isn't strifing! (or they're up against the black king)
      		if ($userrow['sessionbossengaged'] == 1) {
      			echo "Blahdev emerges from the Furthest Ring and immediately notices you're up against the Black King.<br />";
      			echo "\"Sorry bro,\" he apologizes as he ducks back into the void, \"you're on your own for this one!\"<br />";
      			$donotconsume = true;
      		} else {
      			echo "Blahdev emerges from the Furthest Ring. He spends a good long time scanning the area for any threats, but when he sees none, he angrily seizes your Dev Request Flag and snaps it in half.<br />";
      			echo "As he disappears back into the void, you hear him shout: \"Don't abuse the power if you're not prepared to waste millions of grist!\"<br />";
      		}
      	}
      	break;
      case "Four In The Mourning Perfume":
      	$healthlost = $userrow['Gel_Viscosity'] - $userrow['Health_Vial'];
      	$heal = ceil($healthlost / 20); //heals 5% of lost health
      	echo "You apply the perfume to your person with dignity, and you feel just fresh enough to overlook some of your more glaring wounds.<br />";
      	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      	break;
      case "Quantum Escape Portal Key":
        if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {//User strifing
          echo "You quickly throw the escape key onto the floor and step on it, pressing it down. A massive portal in time and space opens, sucking in you and any of your allies who happen to also be here.<br />";
					$userrow = terminateStrife($userrow, -1);
		if (!empty($userrow['strifesuccessexplore']) && !empty($userrow['strifefailureexplore'])) { //User exploring!
			mysql_query("UPDATE `Players` SET `exploration` = '" . $userrow['strifeabscondexplore'] . "', `strifesuccessexplore` = '', `strifefailureexplore` = '', `strifeabscondexplore` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			echo ' <a href="explore.php">Continue exploring</a></br>';
		}
	if ($userrow['dungeonstrife'] == 2) { //User strifing in a dungeon
		mysql_query("UPDATE `Players` SET `dungeonstrife` = 1 WHERE `Players`.`username` = '$username' LIMIT 1;");
		echo "The portal dumps everyone back into the other room.</br>";
		echo "<a href='dungeons.php'>==&gt;</a></br>";
	}
	if ($userrow['dungeonstrife'] == 4) { //User fighting dungeon guardian
	    mysql_query("UPDATE `Players` SET `dungeonstrife` = 3 WHERE `Players`.`username` = '$username' LIMIT 1;");
	    echo "In escaping the dungeon guardian, you're unsure if you will ever be able to find that same dungeon again.</br>";
	    echo "<a href='dungeons.php#display'>==&gt;</a></br>";
	}          
        } elseif ($userrow['indungeon'] == 1) {
          echo "You place the escape key against a wall and press it in. Moments later, a portal opens in the wall and you jump through it, landing outside of the dungeon.<br />";
          mysql_query("UPDATE `Players` SET `indungeon` = 0 WHERE `Players`.`username` = '$username' LIMIT 1;");
        } else {
          echo "Using this now won't help you with anything. You decide to hang onto it for the time being.<br />";
          $donotconsume = true;
        }
        break;
      case "Fetch Modus Upgrade: Huge Items":
      	if (itemSize("huge") > itemSize($userrow['moduspower'])) {
      		echo "You install the 'Huge Items' upgrade to your Fetch Modus. You can now captchalogue bigger items than before! There may be some objects that are still too big for it, though.<br />";
      		mysql_query("UPDATE `Players` SET `moduspower` = 'huge' WHERE `Players`.`username` = '$username' LIMIT 1");
      	} else {
      		echo "Either you already have that upgrade installed, or you have one that makes it obsolete.<br />";
      		$donotconsume = true;
      	}
      	break;
      case "Fetch Modus Upgrade: Immense Items":
      	if (itemSize("immense") > itemSize($userrow['moduspower'])) {
      		echo "You install the 'Immense Items' upgrade to your Fetch Modus. You can now captchalogue all but the biggest of items!<br />";
      		mysql_query("UPDATE `Players` SET `moduspower` = 'immense' WHERE `Players`.`username` = '$username' LIMIT 1");
      	} else {
      		echo "Either you already have that upgrade installed, or you have one that makes it obsolete.<br />";
      		$donotconsume = true;
      	}
      	break;
      case "Fetch Modus Upgrade: Ginormous Items":
      	if (itemSize("ginormous") > itemSize($userrow['moduspower'])) {
      		echo "You install the 'Ginormous Items' upgrade to your Fetch Modus. You feel there is nothing your modus can't captchalogue now!<br />";
      		mysql_query("UPDATE `Players` SET `moduspower` = 'ginormous' WHERE `Players`.`username` = '$username' LIMIT 1");
      	} else {
      		echo "Either you already have that upgrade installed, or you have one that makes it obsolete.<br />";
      		$donotconsume = true;
      	}
      	break;
      case "Encyclopedia of SBURB Device Upgrades":
      	$athenresult = mysql_query("SELECT `atheneum` FROM Sessions WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1;");
      	$athenrow = mysql_fetch_array($athenresult);
      	$newatheneum = $athenrow['atheneum'];
      	$upresult = mysql_query("SELECT `captchalogue_code`, `name` FROM `Captchalogue` WHERE `Captchalogue`.`effects` LIKE '%UPGRADE|%'");
      	echo "You flip through the Encyclopedia of SBURB Device Upgrades. Inside, you find codes for the following items:<br />";
      	while ($uprow = mysql_fetch_array($upresult)) {
      		if (!strrpos($athenrow['atheneum'], $uprow['captchalogue_code'])) {
      			$newatheneum .= $uprow['captchalogue_code'] . "|";
      		}
      		$uprow['name'] = str_replace("\\", "", $uprow['name']);
      		echo $uprow['name'] . "<br />";
      	}
      	echo "These codes have been automatically added to your session's Atheneum!<br />";
      	mysql_query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1 ;");
      	$donotconsume = true; //allow the player to use this at any time if new upgrades are added
      	break;
      case "? Block":
        $content = rand(0,1);
	if ($content == 0) { //just a coin (one boondollar)
	  echo "You punch the ? Block. A single coin pops out. Lucky you...?</br>";
	  mysql_query("UPDATE `Players` SET `Boondollars` = " . strval($userrow['Boondollars']+1) . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  } else {
	  	$totalconsumes = 0;
	  	$consumeresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`consumable` = 1  AND `Captchalogue`.`abstratus` = 'notaweapon' ;");
	  	while (mysql_fetch_row($consumeresult)) $totalconsumes++; //figure out how many non-weapon consumables there are in the game
	  	$chosenconsume = rand(1,$totalconsumes); //pick one at random
	  	$consumeresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`consumable` = 1 AND `Captchalogue`.`abstratus` = 'notaweapon' LIMIT " . $chosenconsume . " , 1 ;"); //this should return only the item that it chose
	  	$crow = mysql_fetch_array($consumeresult);
	  	echo "You punch the ? Block. A " . $crow['name'] . " pops out of it, which you quickly captchalogue. Nice!";
	  	$blockloot = addItem($crow['name'],$userrow);
	  	if ($blockloot == "inv-1") { //no inventory space, so we'll replace the item that was just used with the new item. Not ideal, but it's better than being all "you can't hold it lol" when you JUST used up an item.
	  		mysql_query("UPDATE `Players` SET `" . $_POST['consume'] . "` = '" . $crow['name'] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  		$userrow[$_POST['consume']] = $crow['name'];
	  		$donotconsume = 1; //this is needed so it doesn't clear the item we just added
	  	}
	  }
	break;
	case "Rolled-Up Poster":
	  	$totalconsumes = 0;
	  	$consumeresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `Captchalogue`.`name` LIKE '%Poster%' AND `Captchalogue`.`abstratus` = 'notaweapon' ;");
	  	while (mysql_fetch_row($consumeresult)) $totalconsumes++; //figure out how many non-weapon consumables there are in the game
	  	$chosenconsume = rand(1,$totalconsumes); //pick one at random
	  	$consumeresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `Captchalogue`.`name` LIKE '%Poster%' AND `Captchalogue`.`abstratus` = 'notaweapon' LIMIT " . $chosenconsume . " , 1 ;"); //this should return only the item that it chose
	  	$crow = mysql_fetch_array($consumeresult);
	  	echo "You can't hold in your hype any longer. You unroll the poster, and discover that it is a " . $crow['name'] . "! How exciting! You decide to captchalogue it for now.<br />";
	  	$blockloot = addItem($crow['name'],$userrow);
	  	if ($blockloot == "inv-1") { //no inventory space, so we'll replace the item that was just used with the new item. Not ideal, but it's better than being all "you can't hold it lol" when you JUST used up an item.
	  		mysql_query("UPDATE `Players` SET `" . $_POST['consume'] . "` = '" . $crow['name'] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  		$userrow[$_POST['consume']] = $crow['name'];
	  		$donotconsume = 1; //this is needed so it doesn't clear the item we just added
	  	}
	break;
	case "Barrelfull of Water":
		echo "You drink all the water in the barrel. All of it. You begin to suspect that there may be a camel somewhere in your ancestry.</br>";
		$blockloot = addItem("Empty Barrel",$userrow);
	  	if ($blockloot == "inv-1") { //no inventory space, so we'll replace the item that was just used with the new item. Not ideal, but it's better than being all "you can't hold it lol" when you JUST used up an item.
	  		mysql_query("UPDATE `Players` SET `" . $_POST['consume'] . "` = 'Empty Barrel' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  		$userrow[$_POST['consume']] = "Empty Barrel";
	  		$donotconsume = 1; //this is needed so it doesn't clear the item we just added
	  	}
		break;
	case "Lens of Truth":
		$donotconsume = 1;
		if ($userrow['indungeon'] != 0) {
			echo "You hold up the Lens of Truth, examining each junction from your current position and revealing what's inside the rooms!</br>";
			$dungeonresult = mysql_query("SELECT * FROM `Dungeons` WHERE `Dungeons`.`username` = '$username'");
			$drow = mysql_fetch_array($dungeonresult);
			$dquery = "UPDATE `Dungeons` SET ";
			$updated = false;
			if ($drow['dungeonrow'] > 1) {
				$squarename = strval($drow['dungeonrow'] - 1) . "," . strval($drow['dungeoncol']);
				if ($drow[$squarename] != "" && strpos($drow[$squarename], "VISITED") === false) {
					$drow[$squarename] .= "|VISITED";
					$updated = true;
					$dquery .= "`$squarename` = '" . $drow[$squarename] . "', ";
				}
			}
			if ($drow['dungeonrow'] < 10) {
				$squarename = strval($drow['dungeonrow'] + 1) . "," . strval($drow['dungeoncol']);
				if ($drow[$squarename] != "" && strpos($drow[$squarename], "VISITED") === false) {
					$drow[$squarename] .= "|VISITED";
					$updated = true;
					$dquery .= "`$squarename` = '" . $drow[$squarename] . "', ";
				}
			}
			if ($drow['dungeoncol'] > 1) {
				$squarename = strval($drow['dungeonrow']) . "," . strval($drow['dungeoncol'] - 1);
				if ($drow[$squarename] != "" && strpos($drow[$squarename], "VISITED") === false) {
					$drow[$squarename] .= "|VISITED";
					$updated = true;
					$dquery .= "`$squarename` = '" . $drow[$squarename] . "', ";
				}
			}
			if ($drow['dungeoncol'] < 10) {
				$squarename = strval($drow['dungeonrow']) . "," . strval($drow['dungeoncol'] + 1);
				if ($drow[$squarename] != "" && strpos($drow[$squarename], "VISITED") === false) {
					$drow[$squarename] .= "|VISITED";
					$updated = true;
					$dquery .= "`$squarename` = '" . $drow[$squarename] . "', ";
				}
			}
			if ($updated) {
				$dquery = substr($dquery, 0, -2);
				$dquery .= " WHERE `Dungeons`.`username` = '$username' LIMIT 1;";
				mysql_query($dquery);
			} else echo "...but you already explored all those rooms yourself, so you wonder what even was the point.</br>";
		} else echo "You hold up the Lens of Truth for a moment, but aside from a neat tinted filter, you don't see anything interesting.</br>";
		break;
      case "Kero-Kero Cola":
      	if ($userrow['sessionbossengaged'] == 1) {
	  echo "You down the Kero-Kero Cola, finishing with a burp that sounds remarkably like a ribbit. You and all of your friends feel much better!";
	  $actualheal = 1000;
	  $aidresult = mysql_query("SELECT * FROM Players WHERE `Players`.`sessionbossengaged` = 1 AND `Players`.`session_name` = '" . $userrow['session_name'] . "' ;"); //Look up all players also fighting this session's BK
	  while ($aidrow = mysql_fetch_array($aidresult)) {
	  	$heal = $actualheal;
	  	if ($heal + $aidrow['Health_Vial'] > $aidrow['Gel_Viscosity'] && $heal > 0) $heal = $aidrow['Gel_Viscosity'] - $aidrow['Health_Vial']; //don't let consumable overheal player
	    mysql_query("UPDATE `Players` SET `Health_Vial` = $aidrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $aidrow['username'] . "' LIMIT 1 ;");
	  }
	  $heal = $actualheal;
	  if ($heal + $userrow['Health_Vial'] > $userrow['Gel_Viscosity'] && $heal > 0) $heal = $userrow['Gel_Viscosity'] - $userrow['Health_Vial']; //don't let consumable overheal player  
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  }
	if ($userrow['enemydata'] != "") {
	  echo "You down the Kero-Kero Cola, finishing with a burp that sounds remarkably like a ribbit. You and all of your friends feel much better!";
	  $heal = 1000;
	  $aidresult = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '" . $username . "'"); //Look up all players aiding this player.
	  while ($aidrow = mysql_fetch_array($aidresult)) {
	  	$heal = $actualheal;
	  	if ($heal + $aidrow['Health_Vial'] > $aidrow['Gel_Viscosity'] && $heal > 0) $heal = $aidrow['Gel_Viscosity'] - $aidrow['Health_Vial']; //don't let consumable overheal player
	    mysql_query("UPDATE `Players` SET `Health_Vial` = $aidrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $aidrow['username'] . "' LIMIT 1 ;");
	  }
	  $heal = $actualheal;
	  if ($heal + $userrow['Health_Vial'] > $userrow['Gel_Viscosity'] && $heal > 0) $heal = $userrow['Gel_Viscosity'] - $userrow['Health_Vial']; //don't let consumable overheal player  
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	} elseif ($userrow['aiding'] != "") {
	  echo "You down the Kero-Kero Cola, finishing with a burp that sounds remarkably like a ribbit. You and all of your friends feel much better!";
	  $heal = 1000;
	  $aidresult = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '" . $userrow['aiding'] . "'"); //Look up all players aiding the same player as this player.
	  while ($aidrow = mysql_fetch_array($aidresult)) {
	  	$heal = $actualheal;
	  	if ($heal + $aidrow['Health_Vial'] > $aidrow['Gel_Viscosity'] && $heal > 0) $heal = $aidrow['Gel_Viscosity'] - $aidrow['Health_Vial']; //don't let consumable overheal player
	    mysql_query("UPDATE `Players` SET `Health_Vial` = $aidrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $aidrow['username'] . "' LIMIT 1 ;");
	  }
	  $targetresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $userrow['aiding'] . "'"); //Look up the aid target.
	  $targetrow = mysql_fetch_array($targetresult);
	  $heal = $actualheal;
	  if ($heal + $targetrow['Health_Vial'] > $targetrow['Gel_Viscosity'] && $heal > 0) $heal = $targetrow['Gel_Viscosity'] - $targetrow['Health_Vial']; //don't let consumable overheal player  
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $targetrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $targetrow['username'] . "' LIMIT 1 ;");
	} else {
	  echo "It seems like an awful waste to drink this thing outside of strife, although you're not entirely sure why.";
	  $donotconsume = True;
	}
	break;
      case "Pan Galactic Gargle Blaster":
	if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {//User strifing
	  echo "Against your better judgement, you take a sip of the Pan Galactic Gargle Blaster. The effect is almost immediate: you feel like you got hit by a slice of lemon wrapped around a solid gold brick, and collapse on the spot.</br>";
	  echo "You slowly and painfully come to an unknown amount of time later. The enemies you were fighting have all left, probably out of pity. You crawl back to your dwelling and spend some time recovering from that hellish drink.";
	  $k = 1;
	  while ($k <= $max_enemies) {
	    $enemystr = "enemy" . strval($k) . "name";
	    if ($k == 1) {
	    	$userrow[$enemystr] = 'The Mother of All Hangovers';
	      /*mysql_query("UPDATE `Players` SET `" . $enemystr . "` = 'The Mother of All Hangovers' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "power` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "health` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "maxhealth` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "desc` = 'Good luck with that.' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "category` = 'None' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");*/
	    } else {
	    	$userrow[$enemystr] = '';
	      //mysql_query("UPDATE `Players` SET `" . $enemystr . "` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    }
	    $k++;
	  }
	  mysql_query("UPDATE `Players` SET `aiding` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	} else {
	  echo "Against your better judgement, you take a sip of the Pan Galactic Gargle Blaster. The effect is almost immediate: you feel like you got hit by a spaceship, and collapse on the spot.</br>";
	  echo "You slowly and painfully come to an unknown amount of time later. It's a good thing you weren't out and about when you drank this, because you'll need as much time as you can get to overcome your monumental headache.";
	}
	$boost = $userrow['powerboost'];
	if ($boost > 0) $boost = $boost * -1;
	$boost -= 100;
	$harm = $userrow['Health_Vial'] - 1;
	mysql_query("UPDATE `Players` SET `down` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	//mysql_query("UPDATE `Players` SET `enemy1name` = 'The Mother of All Hangovers' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	$userrow['enemy1name'] = 'The Mother of All Hangovers';
	$userrow['enemy1power'] = 9999999999;
 	$userrow['enemy1maxpower'] = 9999999999;
	$userrow['enemy1health'] = 9999999999;
 	$userrow['enemy1maxhealth'] = 9999999999;
	$userrow['enemy1desc'] = "Good luck with that.";
	$userrow['enemy1category'] = "None";
	/*mysql_query("UPDATE `Players` SET `enemy1power` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `enemy1health` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `enemy1maxhealth` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `enemy1desc` = 'Good luck with that.' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `enemy1category` = 'None' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");*/
	mysql_query("UPDATE `Players` SET `combatmotifuses` = " . strval(floor($userrow['Echeladder'] / 100) + $userrow['Godtier']) . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
	case "Candy Corn Liquor":
		if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
			echo "You consider drinking the potent sugary brew, but your opponents won't give you the opportunity to construct a fort. Everyone knows you can't drink alcohol of this caliber without a proper fort!</br>";
			$donotconsume = true;
		} else {
	echo "You chug the potent sugary brew. Fortunately you had the foresight to construct a fort beforehand, so you hide safely in there as the alcohol takes effect. You feel exceptionally drowsy...</br>";
	if ($userrow['dreamer'] == "Unawakened") echo "...and then you wake up a few hours later with a mild headache. Perhaps you lack the IMAGINATION to use this liquor to its fullest effect.</br>";
	else {
	echo "You drift off to sleep, awakening as your dream self moments later. Your IMAGINATION stat soars thanks to the candy corn liquor!</br>";
	mysql_query("UPDATE `Players` SET `dreamingstatus` = '$userrow[dreamer]', `powerboost` = $userrow[Echeladder], `offenseboost` = 0, `defenseboost` = 0, `temppowerboost` = 0, `tempoffenseboost` = 0, `tempdefenseboost` = 0, `Brief_Luck` = 0, `strifesuceessexplore` = '', `strifefailureexlpore` = '', `correctgristtype` = 'None' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	if ($userrow['encountersspent'] > 0) { //Player spent encounters while awake.
	  $newhp = $userrow['Dream_Health_Vial'] + ceil(($userrow['Gel_Viscosity'] / 5) * $userrow['encountersspent']);
	  if ($newhp > $userrow['Gel_Viscosity']) $newhp = $userrow['Gel_Viscosity'];
	  mysql_query("UPDATE `Players` SET `Dream_Health_Vial` = $newhp WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $newaspect = $userrow['Aspect_Vial'] + ceil(($userrow['Gel_Viscosity'] / 8) * $userrow['encountersspent']);
	  if (!empty($abilities[9])) $newaspect = $newaspect + ceil(($userrow['Gel_Viscosity'] / 16) * $userrow['encountersspent']); //Aspect Connection (ID 9): 1.5x recovery.
	  if ($newaspect > $userrow['Gel_Viscosity']) $newaspect = $userrow['Gel_Viscosity'];
	  mysql_query("UPDATE `Players` SET `Aspect_Vial` = $newaspect WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `encountersspent` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  echo "Spending time as your waking self has let your dream self rest, allowing them to recover health.</br>";
	  echo "Resting in one state has strengthened your connection to your Aspect.</br>";
	  if (!empty($abilities[9])) echo $abilities[9]; //Aspect Connection occurrence message.
	}
	echo '<a href="overview.php">==&gt;</a>';
	$dontechoconsumemenu = true;
	}
		}
	break;
	case "Pushing-Potion":
		if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
			echo "You consider drinking the Pushing-Potion, but your enemies won't give you the chance.</br>";
			$donotconsume = true;
		} else {
	echo "You drink the potion, and immediately feel as if a great weight is making you sink through the floor. You collapse on the spot.</br>";
	if ($userrow['dreamer'] == "Unawakened") echo "...and then you wake up a few hours later, reeling from the bizarre images you saw in your sleep. If only you could dream of a better place...</br>";
	else {
	echo "You drift off to sleep, awakening as your dream self moments later.</br>";
	mysql_query("UPDATE `Players` SET `dreamingstatus` = '$userrow[dreamer]', `powerboost` = 0, `offenseboost` = 0, `defenseboost` = 0, `temppowerboost` = 0, `tempoffenseboost` = 0, `tempdefenseboost` = 0, `Brief_Luck` = 0, `strifesuceessexplore` = '', `strifefailureexlpore` = '', `correctgristtype` = 'None' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	if ($userrow['encountersspent'] > 0) { //Player spent encounters while awake.
	  $newhp = $userrow['Gel_Viscosity']; //the item gives a full heal of the dreamself
	  mysql_query("UPDATE `Players` SET `Dream_Health_Vial` = $newhp WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $newaspect = $userrow['Aspect_Vial'] + ceil(($userrow['Gel_Viscosity'] / 8) * $userrow['encountersspent']);
	  if (!empty($abilities[9])) $newaspect = $newaspect + ceil(($userrow['Gel_Viscosity'] / 16) * $userrow['encountersspent']); //Aspect Connection (ID 9): 1.5x recovery.
	  if ($newaspect > $userrow['Gel_Viscosity']) $newaspect = $userrow['Gel_Viscosity'];
	  mysql_query("UPDATE `Players` SET `Aspect_Vial` = $newaspect WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `encountersspent` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  echo "You feel totally rejuvenated somehow, regardless of how much time you spent as your waking self!</br>";
	  echo "Resting in one state has strengthened your connection to your Aspect.</br>";
	  if (!empty($abilities[9])) echo $abilities[9]; //Aspect Connection occurrence message.
	}
	echo '<a href="overview.php">==&gt;</a>';
	$dontechoconsumemenu = true;
	}
		}
	break;
      case "Nominomicon":
	if ($userrow['enemydata'] == "" && $userrow['aiding'] == "") { //User is not strifing
	  echo "You give the Nominomicon a good read. It gives you insight into the darkest corners of existence AND makes your fingers deliciously sticky!";
	  $donotconsume = True;
	} else {
	  if ($userrow['aiding'] == "") { //Player is main strifer
	    echo "You om nom nom the Nominomicon. It consumes some of your vitality to project sticky, unspeakable tentacles at your opponents.";
	    $enemies = 1;
	    $harm = floor($userrow['Health_Vial'] / 2);
	    mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $healthstr = "enemy" . strval($enemies) . "health";
	      if ($harm > $userrow[$healthstr]) $harm = $userrow[$healthstr] - 1;
	      if ($userrow[$enemystr] != "") {
	      	//mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $userrow[$healthstr]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      	$userrow[$healthstr] -= $harm;
	      }
	      $enemies++;
	    }
	  } else { //Player is aiding
	    echo "You om nom nom the Nominomicon. It consumes some of your vitality to project sticky, unspeakable tentacles at your ally's opponents.";	   
	    $enemies = 1;
	    $allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $userrow['aiding'] . "'");
	    while ($row = mysql_fetch_array($allyresult)) {
	      if ($row['username'] = $userrow['aiding']) $allyrow = $row;
	    }
	    $allyrow = parseEnemydata($allyrow);
	    $harm = floor($userrow['Health_Vial'] / 2);
	    mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $healthstr = "enemy" . strval($enemies) . "health";
	      if ($harm > $allyrow[$healthstr]) $harm = $allyrow[$healthstr] - 1;
	      if ($allyrow[$enemystr] != "") {
	      	//mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $allyrow[$healthstr]-$harm WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
	      	$allyrow[$healthstr] -= $harm;
	      }
	      $enemies++;
	    }
	    writeEnemydata($allyrow);
	  }
	}
	break;
      case "New Year's Eve Bomb":
	if ($userrow['enemydata'] == "" && $userrow['aiding'] == "") { //User is not strifing
	  echo "You examine the bomb carefully. You are 100% sure this one isn't a dud!";
	  $donotconsume = True;
	} else {
	  if ($userrow['aiding'] == "") { //Player is main strifer
	    if (rand(1,100) <= 95 || $userrow['sessionbossengaged'] == 1) {
	      echo "You throw the New Year's Eve Bomb at your enemies. It's a dud! HOW COULD THIS HAPPEN.";
	    } else {
	      echo "You throw the New Year's Eve Bomb at your enemies. It goes off in a massive super-amazing display of pyrotechnics, almost completely obliterating them.";
	      $enemies = 1;
	      while ($enemies <= $max_enemies) {
		$enemystr = "enemy" . strval($enemies) . "name";
		$healthstr = "enemy" . strval($enemies) . "health";
		if ($userrow[$enemystr] != "") {
			//mysql_query("UPDATE `Players` SET `" . $healthstr . "` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			$userrow[$healthstr] = 1;
		}
		$enemies++;
	      }
	    }
	  } else { //Player is aiding
	  	$allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $userrow['aiding'] . "'");
	    while ($row = mysql_fetch_array($allyresult)) {
	      if ($row['username'] = $userrow['aiding']) $allyrow = $row;
	    }
	    $allyrow = parseEnemydata($allyrow);
	    if (rand(1,100) <= 95 || $userrow['sessionbossengaged'] == 1) {
	      echo "You throw the New Year's Eve Bomb at your ally's enemies. It's a dud! HOW COULD THIS HAPPEN.";
	    } else {
	      echo "You throw the New Year's Eve Bomb at your enemies. It goes off in a massive super-amazing display of pyrotechnics, almost completely obliterating them.";
	      $enemies = 1;
	      while ($enemies <= $max_enemies) {
		$enemystr = "enemy" . strval($enemies) . "name";
		$healthstr = "enemy" . strval($enemies) . "health";
		if ($allyrow[$enemystr] != "") {
			//mysql_query("UPDATE `Players` SET `" . $healthstr . "` = 1 WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
			$allyrow[$healthstr] = 1;
		}
		$enemies++;
	      }
	      writeEnemydata($allyrow);
	    }
	  }
	}
	break;
      case "Mtn Dew Dorito Cupcake":
	$boost = rand(0,200) - 100;
	if ($boost >= 0) {
	  echo "You eat the...whatever the fuck this is. It feels like it fortifies you!";
	} else {
	  echo "You eat the...whatever this is. Ick. You feel all shitty and ARTIFACTED now.";
	}
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Energized Protodermis Energy Drink":
	$boost = 50;
	$heal = 500;
	if ($heal > $userrow['Gel_Viscosity'] - $userrow['Health_Vial']) $heal = $userrow['Gel_Viscosity'] - $userrow['Health_Vial'];
	$harmchance = rand(1,5);
	if ($harmchance == 1) $heal = floor($userrow['Health_Vial'] / 3) * -2;
	if ($heal >= 0) {
	  echo "You take a swig of the energy drink. You feel your body start to rapidly repair and strengthen itself!";
	} else {
	  echo "You take a swig of the energy drink. You feel stronger, but DAMN does it burn your innards!";
	}
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
	case "Faygo - Gushing Golden Apple Flavor":
		$effect = rand(1,5);
		echo "You drink the golden liquid. ";
		switch ($effect) {
		case 1: //healing
			$heal = rand(50,250);
			if ($heal > $userrow['Gel_Viscosity'] - $userrow['Health_Vial']) $heal = $userrow['Gel_Viscosity'] - $userrow['Health_Vial'];
			mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			echo "You begin to feel much better than before. It seems to have healed you!";
			break;
		case 2: //aspect vial
			$heal = rand(50,250);
			if ($heal > $userrow['Gel_Viscosity'] - $userrow['Aspect_Vial']) $heal = $userrow['Gel_Viscosity'] - $userrow['Aspect_Vial'];
			mysql_query("UPDATE `Players` SET `Aspect_Vial` = $userrow[Aspect_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			echo "You feel energized by your aspect. It seems to have restored your Aspect Vial!";
			break;
		case 3: //power boost
			$boost = rand(50,200);
			mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			echo "You feel more powerful. It seems to have increased your combat ability!";
			break;
		case 4: //offense boost
			$boost = rand(50,200);
			mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			echo "You feel stronger. It seems to have increased your offensive power!";
			break;
		case 5: //defense boost
			$boost = rand(50,200);
			mysql_query("UPDATE `Players` SET `defenseboost` = $userrow[defenseboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			echo "You feel tougher. It seems to have increased your defensive power!";
			break;
		}
		break;
	case "Bertie Bott's Every Flavour Jellybeans":
		echo "You eat the every-flavor jellybeans. Most of the flavors are hard to place, and so are their effects...";
			$heal = rand(-50,50);
			if ($heal > $userrow['Gel_Viscosity'] - $userrow['Health_Vial']) $heal = $userrow['Gel_Viscosity'] - $userrow['Health_Vial'];
			if ($heal + $userrow['Health_Vial'] < 0) $heal = ($userrow['Health_Vial'] - 1) * -1;
			mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			$aheal = rand(-50,50);
			if ($aheal > $userrow['Gel_Viscosity'] - $userrow['Aspect_Vial']) $aheal = $userrow['Gel_Viscosity'] - $userrow['Aspect_Vial'];
			if ($aheal + $userrow['Aspect_Vial'] < 0) $aheal = ($userrow['Aspect_Vial'] - 1) * -1;
			mysql_query("UPDATE `Players` SET `Aspect_Vial` = $userrow[Aspect_Vial]+$aheal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			$boost = rand(-50,50);
			mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			$boost = rand(-50,50);
			mysql_query("UPDATE `Players` SET `defenseboost` = $userrow[defenseboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		break;
	case "Malpractice Core":
		echo "Against your better judgment, you activate the Malpractice Core. Several sharp and probably not sterilized instruments protrude from it, and proceed to perform a very painful operation...<br />";
		//does a series of random effects weighted towards the negative
			$heal = rand(-200,rand(-100,100));
			if ($heal > $userrow['Gel_Viscosity'] - $userrow['Health_Vial']) $heal = $userrow['Gel_Viscosity'] - $userrow['Health_Vial'];
			if ($heal + $userrow['Health_Vial'] < 0) $heal = ($userrow['Health_Vial'] - 1) * -1;
			mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			$aheal = rand(-200,rand(-100,100));
			if ($aheal > $userrow['Gel_Viscosity'] - $userrow['Aspect_Vial']) $aheal = $userrow['Gel_Viscosity'] - $userrow['Aspect_Vial'];
			if ($aheal + $userrow['Aspect_Vial'] < 0) $aheal = ($userrow['Aspect_Vial'] - 1) * -1;
			mysql_query("UPDATE `Players` SET `Aspect_Vial` = $userrow[Aspect_Vial]+$aheal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			$boost = rand(-200,rand(-100,100));
			mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			$boost = rand(-200,rand(-100,100));
			mysql_query("UPDATE `Players` SET `defenseboost` = $userrow[defenseboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
			$donotconsume = true;
		break;
      default:
	$consearch = str_replace("'", "", $userrow[$_POST['consume']]); //just die already nobody loves you
	$consumethis = mysql_query("SELECT * FROM Consumables WHERE `Consumables`.`name` = '" . $consearch . "'"); //find the consumable in the consumable database
	$crow = mysql_fetch_array($consumethis);
	if ($crow['name'] != $consearch) { //if an entry doesn't exist for the consumable
	    echo "Whatever you just tried to consume, it wasn't a consumable.";
	    logDebugMessage($username . " - tried to consume $consearch, no hardcoded effect or consumable row");
	    $fail = True;
	    } else {
	    $noenemies = False;
	    $noheal = False;
	    $randpercent = $crow['randompercentage'] / 100;
	    if ($crow['donotconsume'] == "1") $donotconsume = True;
	    $affectrow = $userrow;
	    $allyheal = 1;
	    if (!empty($_POST['target'])) {
	      if ($userrow['sessionbossengaged'] == 1) { //make sure the player is fighting BK
	        $targetresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['target'] . "' LIMIT 1 ;");
	        $affectrow = mysql_fetch_array($targetresult);
	      	if ($affectrow['sessionbossengaged'] != 1 || $affectrow['session_name'] != $userrow['session_name']) { //make sure the target is in the same session and fighting BK
	          echo "That player isn't currently fighting the Black King in your session!";
	          $fail = True;
	          }
	        } else {
		echo "Cheaters never win, and winners never cheat!";
		$fail = True;
		}
	      }
	    if ($affectrow['Aspect'] != $crow['aspect_restrict'] && $crow['aspect_restrict'] != "") { //user/ally is not compatible aspect, nothing happens
		echo "It seems that only a hero of " . $crow['aspect_restrict'] . " can unlock the true potential of this item...";
		$fail = True;
		}
	    if ($userrow['enemydata'] == "" && $userrow['aiding'] == "") { //user not strifing
        	if ($fail == False) echo $crow['message_outside'];
		$noenemies = True;
        	if ($crow['outsideuse'] == 0) {
            	    $fail = True;
            	    }
        	} else {
        	$targets = $crow['number_targets'];
        	if ($crow['selfifnotarget'] == 1) $noheal = True; //don't perform actions on player if this item is meant to be used on enemies
        	if ($userrow['aiding'] == "") { //user is main strifer; I THINK that this will be blank if fighting black king
		    if ($fail == False) echo $crow['message_battle'];
		    if ($crow['battleuse'] == 0) {
			$fail = True;
			}
		    } else {
	            if ($fail == False) echo $crow['message_aiding'];
		    if ($crow['aiduse'] == 0) {
			$fail = True;
			} elseif ($crow['allypercentage'] != 0) { //set the target of the comsumable to the player the user is aiding (for healing and enemies) 
			$allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $userrow['aiding'] . "'");
	  		$affectrow = mysql_fetch_array($allyresult);
			$allyheal = $crow['allypercentage'] / 100;
			}
		    }
		}
	    if ($fail == False) {
	    	$affectrow = parseEnemydata($affectrow);
		if ($noenemies == False) {
		    $debuff = $crow['debuff_exact'];
		    $debuff = floor(rand($debuff - ($debuff * $randpercent), $debuff + ($debuff * $randpercent)) * $allyheal);
		    $damage = $crow['damage_exact'];
		    $damage = floor(rand($damage - ($damage * $randpercent), $damage + ($damage * $randpercent)) * $allyheal);
		    $k = 1;
		    while ($k <= $targets) {
	        $enemystr = "enemy" . strval($k) . "name";
		    	$powerstr = "enemy" . strval($k) . "power";
		    	$maxpowerstr = "enemy" . strval($k) . "maxpower";
	        $healthstr = "enemy" . strval($k) . "health";
	        $maxhealthstr = "enemy" . strval($k) . "maxhealth";
					$statustr = "ENEMY" . strval($k) . "|";
	        $tempdebuff = $debuff;
	        $tempdamage = $damage;
	        $enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $affectrow[$enemystr] . "'");
		  		$enemyrow = mysql_fetch_array($enemyresult);
	        if ($crow['debuff_scale'] != 0) $tempdebuff += floor($affectrow[$powerstr] * ($crow['debuff_scale'] / 100));
	        if ($crow['damage_scale'] != 0) $tempdamage += floor($affectrow[$maxhealthstr] * ($crow['damage_scale'] / 100));
	        if ($tempdebuff > $affectrow[$powerstr]) $tempdebuff = $affectrow[$powerstr] - 1;
	        if ($tempdamage > $affectrow[$healthstr]) $tempdamage = $affectrow[$healthstr] - 1;
	        if (!empty($enemyrow)) { //Not a grist enemy.
		      	if ($enemyrow['massiveresist'] != 100 && $tempdamage > (floor($affectrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'])) { //Enemy resists massive damage applied.
							echo $affectrow[$enemystr] . " resists the massive damage!</br>";
							$tempdamage = floor($userrow[$maxhealthstr] / 100) * $enemyrow['massiveresist'];
		      	}
	       	}
		    	if (!empty($crow['effects'])) { //Code for special effects (generally status). NOTE - bar these if not strifing later.
		    		$thisisconsumablepage = true;
		    		$mainoff = 3; //start it on bonuseffects
		    		$bonuseffects = $crow['effects'];
		    		$currentstatus = $affectrow['strifestatus'];
		    		$werow = $affectrow;
		    		require('includes/strife_weaponeffects.php');
						mysql_query("UPDATE `Players` SET `strifestatus` = '" . mysql_real_escape_string($currentstatus) . "' WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1;");
						echo $message;
		    	}
				if ($tempdebuff != 0) {
					//mysql_query("UPDATE `Players` SET `" . $powerstr . "` = $affectrow[$powerstr]-$tempdebuff WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
					$affectrow[$powerstr] -= $tempdebuff;
				}
		    	if ($tempdamage != 0) {
		    		//mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $affectrow[$healthstr]-$tempdamage WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
		    		$affectrow[$healthstr] -= $tempdamage;
		    	}
		    	$k++;
		    	}
		    	writeEnemydata($affectrow);
		    	$alreadywritten = true;
		    }
		if ($noheal == False) {
		    $heal = $crow['heal_exact'];
		    $heal = floor(rand($heal - ($heal * $randpercent), $heal + ($heal * $randpercent)) * $allyheal);
		    if ($crow['heal_scale'] != 0) $heal += floor($userrow['Gel_Viscosity'] * ($crow['heal_scale'] / 100));
		    if ($heal * -1 > $affectrow['Health_Vial'] && $heal < 0) $heal = ($affectrow['Health_Vial'] - 1) * -1; //don't let consumable kill player
		    if ($heal + $affectrow['Health_Vial'] > $affectrow['Gel_Viscosity'] && $heal > 0) $heal = $affectrow['Gel_Viscosity'] - $affectrow['Health_Vial']; //don't let consumable overheal player
		    if ($heal != 0) mysql_query("UPDATE `Players` SET `Health_Vial` = $affectrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
		    $asvial = $crow['asvial_exact'];
		    $asvial = floor(rand($asvial - ($asvial * $randpercent), $asvial + ($asvial * $randpercent)) * $allyheal);
		    if ($crow['asvial_scale'] != 0) $asvial += floor($userrow['Gel_Viscosity'] * ($crow['asvial_scale'] / 100));
		    if ($asvial * -1 > $affectrow['Aspect_Vial'] && $asvial < 0) $asvial = ($affectrow['Aspect_Vial'] * -1); //don't let consumable reduce aspect vial below 0
		    if ($asvial + $affectrow['Aspect_Vial'] > $affectrow['Gel_Viscosity'] && $asvial > 0) $asvial = $affectrow['Gel_Viscosity'] - $affectrow['Aspect_Vial']; //don't let consumable overcharge aspect vial
		    if ($asvial != 0) mysql_query("UPDATE `Players` SET `Aspect_Vial` = $affectrow[Aspect_Vial]+$asvial WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
		    if ($crow['invuln'] != 0) mysql_query("UPDATE `Players` SET `invulnerability` = $crow[invuln] WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
		    if ($crow['luck'] != 0) mysql_query("UPDATE `Players` SET `Brief_Luck` = $crow[luck] WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
		    $powerboost = $crow['power_exact']; //here comes the strife-wide power boosts, which always affect the user even if aiding
		    $powerboost = floor(rand($powerboost - ($powerboost * $randpercent), $powerboost + ($powerboost * $randpercent)));
		    if ($crow['power_scale'] != 0) $powerboost += floor(($userrow['Echeladder'] * pow(2,$userrow['Godtier'])) * ($crow['power_scale'] / 100));
		    if ($crow['alcoholic'] == 1) $powerboost -= $userrow['powerboost'] * 2; //gettin' drunk
		    if ($powerboost != 0) mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$powerboost WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
		    $offenseboost = $crow['offense_exact'];
		    $offenseboost = floor(rand($offenseboost - ($offenseboost * $randpercent), $offenseboost + ($offenseboost * $randpercent)));
		    if ($crow['offense_scale'] != 0) $offenseboost += floor(($userrow['Echeladder'] * pow(2,$userrow['Godtier'])) * ($crow['offense_scale'] / 100));
		    if ($offenseboost != 0) mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]+$offenseboost WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
		    $defenseboost = $crow['defense_exact'];
		    $defenseboost = floor(rand($defenseboost - ($defenseboost * $randpercent), $defenseboost + ($defenseboost * $randpercent)));
		    if ($crow['defense_scale'] != 0) $defenseboost += floor(($userrow['Echeladder'] * pow(2,$userrow['Godtier'])) * ($crow['defense_scale'] / 100));
		    if ($defenseboost != 0) mysql_query("UPDATE `Players` SET `defenseboost` = $userrow[defenseboost]+$defenseboost WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
		    $offenseboost = $crow['offense_exact_temp'];
		    $defenseboost = $crow['defense_exact_temp'];
		    $offenseboost = floor(rand($offenseboost - ($offenseboost * $randpercent), $offenseboost + ($offenseboost * $randpercent)) * $allyheal);
		    $defenseboost = floor(rand($defenseboost - ($defenseboost * $randpercent), $defenseboost + ($defenseboost * $randpercent)) * $allyheal);
		    if ($crow['offense_scale_temp'] != 0) $offenseboost += floor(($userrow['Echeladder'] * pow(2,$userrow['Godtier'])) * ($crow['offense_scale_temp'] / 100));
		    if ($crow['defense_scale_temp'] != 0) $defenseboost += floor(($userrow['Echeladder'] * pow(2,$userrow['Godtier'])) * ($crow['defense_scale_temp'] / 100));
		    $powerboost = 0; //make sure an old powerboost doesn't carry over!
		    if ($offenseboost > 0 && $defenseboost > 0) { //now see if we can just make a powerboost instead
			if ($offenseboost > $defenseboost) {
			    $powerboost = $defenseboost;
			    $offenseboost -= $defenseboost;
			    } else {
			    $powerboost = $offenseboost;
			    $defenseboost -= $offenseboost;
			    }
			}
		    if ($offenseboost < 0 && $defenseboost < 0) {
			if ($offenseboost < $defenseboost) {
			    $powerboost = $defenseboost;
			    $offenseboost -= $defenseboost;
			    } else {
			    $powerboost = $offenseboost;
			    $defenseboost -= $offenseboost;
			    }
			}
		    if ($powerboost != 0) {
			if ($affectrow['temppowerboost'] < $powerboost) mysql_query("UPDATE `Players` SET `temppowerboost` = $powerboost WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
			if ($affectrow['temppowerduration'] > $crow['temp_timer'] || $affectrow['temppowerduration'] == 0) mysql_query("UPDATE `Players` SET `temppowerduration` = $crow[temp_timer] WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
			}
		    if ($offenseboost != 0) {
			if ($affectrow['tempoffenseboost'] < $offenseboost) mysql_query("UPDATE `Players` SET `tempoffenseboost` = $offenseboost WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
			if ($affectrow['tempoffenseduration'] > $crow['temp_timer'] || $affectrow['tempoffenseduration'] == 0) mysql_query("UPDATE `Players` SET `tempoffenseduration` = $crow[temp_timer] WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
			}
		    if ($defenseboost != 0) {
			if ($affectrow['tempdefenseboost'] < $defenseboost) mysql_query("UPDATE `Players` SET `tempdefenseboost` = $defenseboost WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
			if ($affectrow['tempdefenseduration'] > $crow['temp_timer'] || $affectrow['tempdefenseduration'] == 0) mysql_query("UPDATE `Players` SET `tempdefenseduration` = $crow[temp_timer] WHERE `Players`.`username` = '" . $affectrow['username'] . "' LIMIT 1 ;");
			}
		    }
		}
		if (!empty($crow['replacement'])) {
			$donotconsume = true;
			mysql_query("UPDATE `Players` SET `" . $_POST['consume'] . "` = '" . $crow['replacement'] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		}
	    }
	break;
      }
    }
    echo "</br>";
    if (!$fail) {
      if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") { 
        mysql_query("UPDATE `Players` SET `combatconsume` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
        if ($alreadywritten == false) writeEnemydata($userrow);
	}
      }
    if (!$fail && !$donotconsume) {
      $consumed = $_POST['consume'];
      mysql_query("UPDATE `Players` SET `" . $_POST['consume'] . "` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      autoUnequip($userrow,"none",$_POST['consume']);
    }
  }
  //--End consuming code here.--
  if (!$dontechoconsumemenu) {
  echo "Consumables Consumer v0.0.1a. Please select a consumable to consume.";
  echo '<form action="consumables.php" method="post"><select name="consume">';
  $reachinv = false;
  $invresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '$username' LIMIT 1 ;");
  while ($col = mysql_fetch_field($invresult)) {
    $invslot = $col->name;
    if ($invslot == "inv1") { //Reached the start of the inventory.
      $reachinv = True;
    }
    if ($invslot == "abstratus1") { //Reached the end of the inventory.
      $reachinv = False;
    }
    if ($reachinv == True && $userrow[$invslot] != "") { //This is a non-empty inventory slot.
    $itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $captchalogue = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($row = mysql_fetch_array($captchalogue)) {
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$invslot] && $row['consumable'] != 0 && $invslot != $consumed) { //Item found in captchalogue database, and it is a consumable. Wasn't just nommed.
	  echo '<option value = "' . $invslot . '">' . $userrow[$invslot] . '</option>';
	}
      }
    }
  }
  echo '</select>';
  if ($userrow['sessionbossengaged'] == 1) {
     echo '</br>Player to use this on: <select name="target">';
     echo '<option value = "' . $username . '">' . $username . '</option>';
     $allyresult = mysql_query("SELECT `username`,`sessionbossengaged` FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "' ;");
     while ($allyrow = mysql_fetch_array($allyresult)) {
       if ($allyrow['sessionbossengaged'] == 1) echo '<option value = "' . $allyrow['username'] . '">' . $allyrow['username'] . '</option>';
     }
   echo '</select>';
   }
  echo '<input type="submit" value="Consume it!" /> </form>';
  echo '<a href="strife.php">Strife</a></br>';
}
}
require_once("footer.php");
?>