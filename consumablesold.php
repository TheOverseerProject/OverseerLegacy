<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to use consumable items.</br>";
} elseif ($userrow['dreamingstatus'] != "Awake") {
  echo "Your dream self can't access your sylladex!";
} else {
  
  $max_enemies = 5; //Again, this is not ideal.
  //--Begin consuming code here.--
  if (!empty($_POST['consume'])) { //Consuming time!
    $fail = False;
    $donotconsume = False;
    if ($userrow['enemy1name'] != "" || $userrow['enemy2name'] != "" || $userrow['enemy3name'] != "" || $userrow['enemy4name'] != "" && $userrow['enemy5name'] != "" || $userrow['aiding'] != "") {//User strifing
      if ($userrow['combatconsume'] == 1) { //Already used a consumable this round.
	echo "You have already used a consumable or aspect ability during this round of strife!";
	$fail = True;
      } else {
	mysql_query("UPDATE `Players` SET `combatconsume` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      }
    }
    if ($fail != True) {
      switch ($userrow[$_POST['consume']]) { //Determine the consumable and act accordingly.
      case "Medkit":
	if ($userrow['aiding'] == "") { //User not currently aiding.
	  echo "You open the medkit and apply various bandages and salves to yourself. Naturally, your health improves.";
	  $heal = 100;
	  if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	} else {
	  echo "You open the medkit and use the contents to perform first aid on your ally. It's a lot more effective than trying to do so on yourself.";
	  $allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
	  while ($row = mysql_fetch_array($allyresult)) {
	    if ($row['username'] = $userrow['aiding']) $allyrow = $row;
	  }
	  $heal = 175;
	  if ($allyrow['Health_Vial'] + $heal > $allyrow['Gel_Viscosity']) $heal = ($allyrow['Gel_Viscosity'] - $allyrow['Health_Vial']); //No overheals.
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $allyrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
	}
	break;
      case "Kero-Kero Cola":
	if ($userrow['enemy1name'] != "" || $userrow['enemy2name'] != "" || $userrow['enemy3name'] != "" || $userrow['enemy4name'] != "" && $userrow['enemy5name'] != "") {
	  echo "You down the Kero-Kero Cola, finishing with a burp that sounds remarkably like a ribbit. You and all of your friends feel much better!";
	  $heal = 1000;
	  $aidresult = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '" . $username . "'"); //Look up all players aiding this player.
	  while ($aidrow = mysql_fetch_array($aidresult)) {
	    mysql_query("UPDATE `Players` SET `Health_Vial` = $aidrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $aidrow['username'] . "' LIMIT 1 ;");
	  }
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	} elseif ($userrow['aiding'] != "") {
	  echo "You down the Kero-Kero Cola, finishing with a burp that sounds remarkably like a ribbit. You and all of your friends feel much better!";
	  $heal = 1000;
	  $aidresult = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '" . $userrow['aiding'] . "'"); //Look up all players aiding the same player as this player.
	  while ($aidrow = mysql_fetch_array($aidresult)) {
	    mysql_query("UPDATE `Players` SET `Health_Vial` = $aidrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $aidrow['username'] . "' LIMIT 1 ;");
	  }
	  $targetresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $userrow['aiding'] . "'"); //Look up the aid target.
	  $targetrow = mysql_fetch_array($targetresult);
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $targetrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $targetrow['username'] . "' LIMIT 1 ;");
	} else {
	  echo "It seems like an awful waste to drink this thing outside of strife, although you're not entirely sure why.";
	  $donotconsume = True;
	}
	break;
      case "Faygonight Brew":
	echo "You drink the darkened Faygo. Aside from a little kick in the flavor, and a brief stabbing urge to murder someone, nothing happens.";
	break;
      case "Prohibition Moonshine":
	echo "You down the jug of exceptionally powerful bootleg alcohol. Your Imagination stat soars! Only that's not a stat in this game so instead you get some health. Also very drunk.";
	$boost = $userrow['powerboost'];
	if ($boost > 0) $boost = $boost * -1;
	$boost -= 25;
	$heal = 250;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Pan Galactic Gargle Blaster":
	if ($userrow['enemy1name'] != "" || $userrow['enemy2name'] != "" || $userrow['enemy3name'] != "" || $userrow['enemy4name'] != "" && $userrow['enemy5name'] != "" || $userrow['aiding'] != "") {//User strifing
	  echo "Against your better judgement, you take a sip of the Pan Galactic Gargle Blaster. The effect is almost immediate: you feel like you got hit by a spaceship, and collapse on the spot.</br>";
	  echo "You slowly and painfully come to an unknown amount of time later. The enemies you were fighting have all left, probably out of pity. You crawl back to your dwelling and spend some time recovering from that hellish drink.";
	  $k = 1;
	  while ($k <= $max_enemies) {
	    $enemystr = "enemy" . strval($k) . "name";
	    if ($k == 1) {
	      mysql_query("UPDATE `Players` SET `" . $enemystr . "` = 'The Mother of All Hangovers' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "power` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "health` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "maxhealth` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "desc` = 'Good luck with that.' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      mysql_query("UPDATE `Players` SET `enemy" . strval($i) . "category` = 'None' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    } else {
	      mysql_query("UPDATE `Players` SET `" . $enemystr . "` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
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
	mysql_query("UPDATE `Players` SET `enemy1name` = 'The Mother of All Hangovers' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `enemy1power` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `enemy1health` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `enemy1maxhealth` = 9999999999 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `enemy1desc` = 'Good luck with that.' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `enemy1category` = 'None' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `combatmotifuses` = " . strval(floor($userrow['Echeladder'] / 100) + $userrow['Godtier']) . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
	/*case "Candy Corn Liquor":
	echo "You chug the potent sugary brew. Fortunately you had the foresight to construct a fort beforehand, so you hide safely in there as the alcohol takes effect. You feel exceptionally drowsy...";
//This will put you to sleep, giving your dream self a huge power boost.
	break;*/
      case "Faygo - Neurotic Nebula flavour":
	echo "You drink the celestially-themed Faygo. It totally blows your mind, and you feel like an insignificant speck on the face of the cosmos.</br>";
	echo "Then you remember that you're playing a game where you create a new universe upon winning, and you suddenly feel much better!";
	$heal = 150;
	$boost = 20;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Starman":
	if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "") { //User is not personally strifing
	  echo "Okay, I'm going to be nice to you here since this thing is so expensive. If you use it now, it'll wear off in a few seconds and be wasted.";
	  $donotconsume = True;
	} else {
	  echo "You pull the starman out of the captchalogue card and touch it. A rainbow coating washes over you, making you completely invulnerable to damage!";
	  $invuln = 5;
	  $tempoffenseboost = ($userrow['Echeladder'] * pow(2,$userrow['Godtier'])) * 3;
	  $tempoffenseduration = 5;
	  mysql_query("UPDATE `Players` SET `invulnerability` = " . strval($userrow['invulnerability']+$invuln) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  if ($tempoffenseboost > $userrow['tempoffenseboost']) {
	    mysql_query("UPDATE `Players` SET `tempoffenseboost` = " . strval($tempoffenseboost) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  }
	  if ($tempoffenseduration < $userrow['tempoffenseduration'] || $userrow['tempoffenseduration'] == 0) {
	    mysql_query("UPDATE `Players` SET `tempoffenseduration` = " . strval($tempoffenseduration) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  }
	}
	break;
      case "Nominomicon":
	if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") { //User is not strifing
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
	      if ($userrow[$enemystr] != "") mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $userrow[$healthstr]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      $enemies++;
	    }
	  } else { //Player is aiding
	    echo "You om nom nom the Nominomicon. It consumes some of your vitality to project sticky, unspeakable tentacles at your ally's opponents.";	   
	    $enemies = 1;
	    $allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
	    while ($row = mysql_fetch_array($allyresult)) {
	      if ($row['username'] = $userrow['aiding']) $allyrow = $row;
	    }
	    $harm = floor($userrow['Health_Vial'] / 2);
	    mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $healthstr = "enemy" . strval($enemies) . "health";
	      if ($harm > $allyrow[$healthstr]) $harm = $allyrow[$healthstr] - 1;
	      if ($allyrow[$enemystr] != "") mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $allyrow[$healthstr]-$harm WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
	      $enemies++;
	    }
	  }
	}
	break;
      case "Doritos":
	echo "You snack on some Doritos. They're not the healthiest thing ever, but they fill you up.";
	break;
      case "Spearmint Candy Cane":
	echo "You eat the giant candy cane. Okay, I'm pretty impressed now.";
	break;
      case "Chocolate Coin":
	echo "You unwrap the chocolate coin and eat it.";
	break;
      case "Sodabomb":
	if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") { //User is not strifing
	  echo "You somehow manage to consume the Sodabomb without it exploding. You immediately wish you hadn't as a series of small sugary explosions rock your digestive system.";
	  $boost = rand(10,50) * -1;
	  $harm = 50;
	  if ($harm >= $userrow['Health_Vial']) $harm = $userrow['Health_Vial'] - 1;
	  mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	} else {
	  if ($userrow['aiding'] == "") { //Player is main strifer
	    echo "You lob the Sodabomb at your enemies. It explodes as soon as it touches the ground, showering them with aluminium shrapnel and shards of razor sharp, red-hot candy.";
	    $enemies = 1;
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $harm = rand(100,500);
	      $healthstr = "enemy" . strval($enemies) . "health";
	      if ($harm > $userrow[$healthstr]) $harm = $userrow[$healthstr] - 1;
	      if ($userrow[$enemystr] != "") mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $userrow[$healthstr]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      $enemies++;
	    }
	  } else { //Player is aiding
	    echo "You lob the Sodabomb at your ally's enemies. It explodes as soon as it touches the ground, showering them with aluminium shrapnel and shards of razor sharp, red-hot candy.";	   
	    $enemies = 1;
	    $allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
	    while ($row = mysql_fetch_array($allyresult)) {
	      if ($row['username'] = $userrow['aiding']) $allyrow = $row;
	    }
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $harm = rand(100,500);
	      $healthstr = "enemy" . strval($enemies) . "health";
	      if ($harm > $allyrow[$healthstr]) $harm = $allyrow[$healthstr] - 1;
	      if ($allyrow[$enemystr] != "") mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $allyrow[$healthstr]-$harm WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
	      $enemies++;
	    }
	  }
	}
	break;
      case "Pork Chop":
	echo "You eat the pork chop. Um, good for you I guess?";
	break;
      case "Blue Jello Shots":
	echo "You quickly consume the jello shots. The ectoplasm reacts poorly with the alcohol, making you more than a little tipsy, but at least they restore some health.";
	$heal = 600;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	$boost = $userrow['powerboost'];
	if ($boost > 0) $boost = $boost * -1;
	$boost -= 20;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Blueberry Pie":
	echo "You eat the entire pie. Yumptious! Or scrummy, one of the two.";
	break;
      case "Lucky Charms":
	if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") {
	echo "You pour yourself a sizable bowl of the cereal, letting the marshmallows accumulate before you sit down to have a nice, quiet breakfast.</br>";
	echo "That was delicious! You should really take breaks like this more often.";
	} else {
	echo "You pour the contents of the cereal box into your mouth. Bowls? You don't have time for that, you're in the middle of strife!</br>";
	echo "The cereal is delicious even without milk, but the chewiness of the marshmallows will leave your teeth feeling awkward the whole battle.";
	}
	break;
      case "Titan's Medicinal Applicator":
      	$donotconsume = true;
	if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") {
	echo "You raise the spoon to your mouth, and it fills with medicine, anticipating usage. You apprehensively give it a taste-test...</br>";
	echo "Eeee-yuck! This spoon's medicine tastes so terrible you're not sure if you could stomach it unless it was an emergency.";
	} else {
	echo "You feed yourself some medicine from the end of the spoon. It tastes absolutely horrible, but you do feel a little better.";
	$heal = 50;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	}
	break;
      case "Shamrock Block":
	echo "You quickly apply a light coating of the green cream to your skin. Feeling lucky yet? Or just damp?";
	$luckboost = 5;
	if ($userrow['Brief_Luck'] + $luckboost > 100) $luckboost = (100 - $userrow['Brief_Luck']);
	mysql_query("UPDATE `Players` SET `Brief_Luck` = $userrow[Brief_Luck]+$luckboost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Bandage":
	echo "You apply the bandage to your wounds, reducing the bleeding somewhat.";
	$heal = 10;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Blue ecto-bandage":
	echo "You apply the ectoplasm-coated bandage to your wounds. The ectoplasm numbs the pain and accelerates the healing process fivefold!";
	$heal = 50;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "One Use Band-Aid Brand Healing Tome":
	echo "You begin to read from the healing tome, and as you do, a vortex of magic swirls around you, patching up some of your wounds. Magically. The now-empty tome drops to the ground, having served its purpose.";
	$heal = 25;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Medi-gel":
	echo "You apply the medi-gel to your wounds, and it immediately goes to work sterilizing and healing them.";
	$heal = 100;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Stimpak":
	echo "You inject the stimpak into your body. The medicine removes some of your wounds and helps you to ignore others.";
	$heal = 30;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Green Apple Pop Rocks":
	echo "You place the pop rocks in your mouth, where they predictably start popping. After a while, they are all gone. This makes you sad.";
	break;
      case "Hershey's Chocolate Hugs":
	echo "You chew on the hugs, and the chocolate immediately hugs your teeth back. You feel a little bit warmer on the inside.";
	break;
      case "Jelly Donuts":
	echo 'After biting into the first "jelly donut", you confirm that they are in fact rice balls. They are still delicious, though.';
	break;
      case "Poppa Rocks":
	echo "The pop rocks jump around wildly in your mouth, and continue to do so in your stomach. You begin to get a bit jumpy yourself!";
	$offenseboost = rand(1,30);
	$defenseboost = rand(1,30);
	mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]+$offenseboost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `defenseboost` = $userrow[defenseboost]+$defenseboost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;     
      case "Ogredose":
	echo "You take a deep breath and ingest yourself with the thick, rancid goop. The pain is nigh unbearable, but you feel an ogre-sized surge of energy.";
	$offenseboost = 30;
	$harm = floor($userrow['Health_Vial'] / 2);
	mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]+$offenseboost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Blue Gelatin Man":
	echo 'You consume the gelatin person. Your initial plan to make amusing "help meeee..." noises as you eat him goes out the window when he turns out to taste terrible.';
	$heal = 275;
	$defenseboost = -10;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `defenseboost` = $userrow[defenseboost]+$defenseboost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      case "Iced Tea":
	echo "You drink the tea. It is cool and refreshing, huzzah!";
	break;
      case "2 of Spades / Licorice Scotty Dogs":
	echo "You eat the Scotty Dogs, feeling like a right proper gentleman the whole time. This encourages you!";
	$boost = 20;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Magnetic Wodka":
	echo "You drink the wodka. It's so useless you somehow manage to not even get drunk!";
	break;
      case "Apple Juice":
	echo "You snap into the bottle of the yellow nectar that is the apple juice. You feel refreshed!";
	break;
      case "Healing Slime Donuts":
	echo "You nom the blue donuts, the slime oozing into your mouth. The taste is a little odd, but the effect is pleasant!";
	$heal = 50;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Fruit Gushers - Massive Tropical Brain Hemorrhage flavour":
	echo "You eat the Fruit Gushers. Apart from a slight sugar rush, nothing much happens.";
	break;
      case "Fruit Gushers - Hellacious Blue Phlegm Aneurysm flavour":
	echo "You eat the Fruit Gushers. You feel revitalized!";
	$heal = 10;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Fruit Gushers - Rockin' Poprock Rigor Mortis flavour":
	echo "You consume the poppin' fruity candy. It restores some health, but the constant popping is getting distracting.";
	$heal = 250;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	$boost = -15;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Froot Gushers - XTrEM Nacho Cheese Concussion flavour":
	echo "You eat the cheesy candy. Aside from some slight artifacting, they taste great!";
	$heal = 200;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Fruit Gushers - Gnarly Cotton Candy Doomsday Prophecy Flavor":
	echo "You eat the doom-flavoured sugary fruity candy. Fortunately, the doom appears to apply to your opponents and not you.";
	$boost = 20;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Fruit Gushers - Spooky Scary Skeletons Candy Corn Catastrophe flavour":
	echo "You eat the Fruit Gushers. Their inherent spookiness fortifies you for your next combat. YES IT MAKES SENSE SHUT UP";
	$boost = 5;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Fruit Gushers - Ultimate Uranium Nuclear Apeshit Apocalypse Flavour":
	echo "You eat the radioactive candy. You gain superpowers! Also radiation poisoning.";
	$boost = ceil($userrow['Echeladder'] / 5);
	$harm = floor($userrow['Health_Vial'] / 10);
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Fruit Gushers - Bodacious Black Liquid Sorrow flavour":
	echo "You choke down the poisonous candy for some reason. Predictably, you don't feel well.";
	$harm = floor($userrow['Health_Vial'] / 3);
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Fruit Gushers - Hellacious Hard Light Head Trauma flavour":
	echo "You consume the copious candy. Hard light coats you, forming a barrier.";
	$defboost = 100;
	mysql_query("UPDATE `Players` SET `defenseboost` = $userrow[defenseboost]+$defboost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Fruit Gushers - Carbonated Crimson Corruption Flavor":
	echo "You down the blood-red gushers, and a spearhead of sugar courses through your veins. You have a sudden urge to appease some sanguine deity.";
	$atkboost = 100;
	$defboost = -50;
	mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]+$atkboost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `defenseboost` = $userrow[defenseboost]+$defboost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Fizzy Gushers - Bubble Cola Bomb Flavor":
	echo "You pop a box of the hyper-sugary snacks. As the first wave of sugar enters your system, everything seems to slow down around you.";
	$boost = 20;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Mountain Dew":
	echo "You skull the entire bottle of Mountain Dew. You feel simultaneously hyper-alert and very ill.";
	$harm = floor($userrow['Health_Vial'] / 20);
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	$boost = 2;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Mountain Dew of Premonition":
	echo "You drink the Dew, and it goes straight to your brain. Your frontal lobe is now swarming with various ambiguous interpretations of the future.";
	echo "You feel like you might be able to avoid a bit of damage, if you're lucky.";
	$heal = 20;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	$boost = 10;
	mysql_query("UPDATE `Players` SET `defenseboost` = $userrow[defenseboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Elixir of Caffeinated Healing":
	echo "You pour the off-colored liquid down your gullet. You're wide-awake now, and feeling marginally better.";
	$heal = rand(1,100);
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	$boost = 15;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Mountain Dew: Pitch Black":
	echo "You attempt to drink the pitch black Mountain Dew. Unfortunately it has the consistency of pitch and you can't get it out of the bottle.";
	$donotconsume = True;
	break;
      case "Faygo - Red Pop flavour":
	echo "You drink the Faygo. It's motherfuckin' miraculous, but it doesn't actually do anything for you.";
	break;
      case "Red Faygo-yo":
	echo "You extract some of the Faygo from the side of your yo-yo and drink it. The supply replenishes almost immediately.";
	$donotconsume = True; //Don't consume the yo-yo
	break;
      case "Coca-Cola Can":
	echo "You drink the Coca-Cola. After noticing our use of their copyrighted brand name, they sue the project into oblivion and it is forced to shut down. Thanks a lot. Oh, also nothing happens.";
	break;
      case "Nuka-Cola":
	echo "You drink the Nuka-Cola. It gives you a sizable chunk of rads, but you feel pumped up anyhow.";
	$boost = ceil(($userrow['Echeladder'] * pow(2,$userrow['Godtier'])) / 2);
	$harm = floor($userrow['Health_Vial'] / 8);
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Nuka-Cola Quantum":
	echo "You drink the Nuka-Cola Quantum. You feel twice as strong as before, but you're also seeing double.";
	$boost = ceil(($userrow['Echeladder'] * pow(2,$userrow['Godtier'])));
	$harm = floor($userrow['Health_Vial'] / 4);
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Red Potion":
	echo "The action seems to pause as you whip out your bottle of Red Potion, uncork it, and down the whole thing. You feel a rush of relief as your health vial refills a bit.";
	$heal = 400;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Faygo - Blitzin' Blue Raspberry flavour":
	echo "You drink the Faygo. A miraculous explosion of healing and flavour washes over you.";
	$heal = 20;
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Faygo - Aquamarine Sugar Bomb flavour":
	echo "You drink the Faygo. The incredibly pure sugar fused with sprite slime rushes through your system, accelerating damn near everything.";
	$heal = 1000;
	$boost = 100;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	if ($userrow['Health_Vial'] + $heal > $userrow['Gel_Viscosity']) $heal = ($userrow['Gel_Viscosity'] - $userrow['Health_Vial']); //No overheals.
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]+$heal WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Wicked Elixir":
	echo "You kick the wicked elixir. You feel sicker, but a bit slicker after drinking the wicked elixir. You also begin to wonder who let Dr. Seuss into the item database.";
	$harm = 200;
	if ($harm > $userrow['Health_Vial']) $harm = $userrow['Health_Vial'] - 1;
	$boost = 50;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Betty Crocker Barbasol Bomb":
	if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") { //User is not strifing
	  echo "As you pick up the bomb to examine it, it explodes in your face. THE SHAVING CREAM, IT BUUUUURNS!";
	  $boost = rand(1,5) * -1;
	  mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	} else {
	  if ($userrow['aiding'] == "") { //Player is main strifer
	    echo "You lob the Barbasol Bomb at your enemies. It explodes, covering them in obscuring foam and making it harder for them to fight.";
	    $enemies = 1;
	    $afflict = rand(1,5);
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $powerstr = "enemy" . strval($enemies) . "power";
	      if ($userrow[$enemystr] != "") mysql_query("UPDATE `Players` SET `" . $powerstr . "` = $userrow[$powerstr]-$afflict WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      $enemies++;
	    }
	  } else { //Player is aiding
	    echo "You lob the Barbasol Bomb at your ally's enemies. It explodes, covering them in obscuring foam and making it harder for them to fight.";
	    $enemies = 1;
	    $afflict = rand(1,5);
	    $allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
	    while ($row = mysql_fetch_array($allyresult)) {
	      if ($row['username'] = $userrow['aiding']) $allyrow = $row;
	    }
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $powerstr = "enemy" . strval($enemies) . "power";
	      if ($allyrow[$enemystr] != "") mysql_query("UPDATE `Players` SET `" . $powerstr . "` = $allyrow[$powerstr]-$afflict WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
	      $enemies++;
	    }
	  }
	}
	break;
      case "Crocker Cream Grenade":
	if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") { //User is not strifing
	  echo "As you pick up the grenade to examine it, it explodes in your face. THE SHAVING CREAM, IT BUUUUURNS! Oh, so does the fire.";
	  $boost = rand(5,10) * -1;
	  if ($userrow['Health_Vial'] <= 100) { //Ensure we don't kill them with fire.
	    $harm = $userrow['Health_Vial'] - 1;
	  } else {
	    $harm = 100;
	  }
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	} else {
	  if ($userrow['aiding'] == "") { //Player is main strifer
	    echo "You lob the Cream Grenade at your enemies. It explodes, covering them in obscuring, flaming foam and making it harder for them to fight. Also, y'know, burning them.";
	    $enemies = 1;
	    $afflict = rand(5,10);
	    $damage = rand(50,150);
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $powerstr = "enemy" . strval($enemies) . "power";
	      $healthstr = "enemy" . strval($enemies) . "health";
	      if ($userrow[$enemystr] != "") {
		mysql_query("UPDATE `Players` SET `" . $powerstr . "` = $userrow[$powerstr]-$afflict WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		if ($userrow[$healthstr] <= $damage) {
		mysql_query("UPDATE `Players` SET `" . $healthstr . "` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		} else {
		  mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $userrow[$healthstr]-$damage WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		}
	      }
	      $enemies++;
	    }
	  } else { //Player is aiding
	    echo "You lob the Cream Grenade at your ally's enemies. It explodes, covering them in obscuring, flaming foam and making it harder for them to fight. Also, y'know, burning them.";
	    $enemies = 1;
	    $afflict = rand(5,10);
	    $damage = rand(50,150);
	    $allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
	    while ($row = mysql_fetch_array($allyresult)) {
	      if ($row['username'] = $userrow['aiding']) $allyrow = $row;
	    }
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $powerstr = "enemy" . strval($enemies) . "power";
	      $healthstr = "enemy" . strval($enemies) . "health";
	      if ($allyrow[$enemystr] != "") {
		mysql_query("UPDATE `Players` SET `" . $powerstr . "` = $allyrow[$powerstr]-$afflict WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
		if ($allyrow[$healthstr] <= $damage) {
		  mysql_query("UPDATE `Players` SET `" . $healthstr . "` = 1 WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
		} else {
		  mysql_query("UPDATE `Players` SET `" . $healthstr . "` = $allyrow[$healthstr]-$damage WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
		}
	      }
	      $enemies++;
	    }
	  }
	}
	break;
      case "New Year's Eve Bomb":
	if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") { //User is not strifing
	  echo "You examine the bomb carefully. You are 100% sure this one isn't a dud!";
	  $donotconsume = True;
	} else {
	  if ($userrow['aiding'] == "") { //Player is main strifer
	    if (rand(1,100) <= 95) {
	      echo "You throw the New Year's Eve Bomb at your enemies. It's a dud! HOW COULD THIS HAPPEN.";
	    } else {
	      echo "You throw the New Year's Eve Bomb at your enemies. It goes off in a massive super-amazing display of pyrotechnics, almost completely obliterating them.";
	      $enemies = 1;
	      while ($enemies <= $max_enemies) {
		$enemystr = "enemy" . strval($enemies) . "name";
		$healthstr = "enemy" . strval($enemies) . "health";
		if ($userrow[$enemystr] != "") mysql_query("UPDATE `Players` SET `" . $healthstr . "` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		$enemies++;
	      }
	    }
	  } else { //Player is aiding
	    if (rand(1,100) <= 95) {
	      echo "You throw the New Year's Eve Bomb at your ally's enemies. It's a dud! HOW COULD THIS HAPPEN.";
	    } else {
	      echo "You throw the New Year's Eve Bomb at your enemies. It goes off in a massive super-amazing display of pyrotechnics, almost completely obliterating them.";
	      $enemies = 1;
	      while ($enemies <= $max_enemies) {
		$enemystr = "enemy" . strval($enemies) . "name";
		$healthstr = "enemy" . strval($enemies) . "health";
		if ($allyrow[$enemystr] != "") mysql_query("UPDATE `Players` SET `" . $healthstr . "` = 1 WHERE `Players`.`username` = '" . $allyrow['username'] . "' LIMIT 1 ;");
		$enemies++;
	      }
	    }
	  }
	}
	break;
      case "Gamzee's Faygo Cupcakes":
	if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") { //User is not strifing
	  echo "You consume the cupcakes and proceed to mellow right out. Whooooooooooooaaaaaaaa...";
	  $afflict = floor($userrow['Echeladder'] / 2);
	  mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]-$afflict WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	} else {
	  if ($userrow['aiding'] == "") { //Player is main strifer
	    echo "You share out your cupcakes with your opponents. You all feel considerably more mellow now.";
	    $enemies = 1;
	    $total = 1;
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $powerstr = "enemy" . strval($enemies) . "power";
	      $afflict = 30;
	      if ($userrow[$enemystr] != "") {
		mysql_query("UPDATE `Players` SET `" . $powerstr . "` = $userrow[$powerstr]-$afflict WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		$total++;
	      }
	      $enemies++;
	    }
	    $afflict = floor($userrow['Echeladder'] / (2 * $total));
	    mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]-$afflict WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  } else { //Player is aiding
	    echo "You share out your cupcakes with your ally's opponents. You all feel considerably more mellow now.";
	    $enemies = 1;
	    $total = 1;
	    while ($enemies <= $max_enemies) {
	      $enemystr = "enemy" . strval($enemies) . "name";
	      $powerstr = "enemy" . strval($enemies) . "power";
	      $afflict = 30;
	      if ($allyrow[$enemystr] != "") {
		mysql_query("UPDATE `Players` SET `" . $powerstr . "` = $allyrow[$powerstr]-$afflict WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		$total++;
	      }
	      $enemies++;
	    }
	    $afflict = floor($userrow['Echeladder'] / (2 * $total));
	    mysql_query("UPDATE `Players` SET `offenseboost` = $userrow[offenseboost]-$afflict WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  }
	}
	break;
      case "Hand-Shaped Poison Faygo Bottle":
	echo "You drink the poisonous sugary swill. It's a miracle this crap doesn't kill you.";
	$harm = floor($userrow['Health_Vial'] / 2);
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Bottle of Vodka":
	echo "You down the entire bottle of vodka. This...wasn't a good idea."; //It negates your entire positive power boost or doubles a negative one, then subtracts a further ten.
	$boost = $userrow['powerboost'];
	if ($boost > 0) $boost = $boost * -1;
	$boost -= 10;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "Ethyl Coma":
	echo "You snap into the bottle of Ethyl Coma. Your vitals enter a state of limbo, and you're left wondering whether the world around you is real or an illusion brought upon by a desperate, dying brain.";
	$boost = $userrow['powerboost'];
	if ($boost > 0) $boost = $boost * -1;
	$boost -= 100;
	mysql_query("UPDATE `Players` SET `powerboost` = $userrow[powerboost]+$boost WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	break;
      case "nachons":
	echo "You eat the nachons. just how HIGH do you have to BE to do something like that?";
	$harm = floor($userrow['Health_Vial'] / 20);
	mysql_query("UPDATE `Players` SET `Health_Vial` = $userrow[Health_Vial]-$harm WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
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
      case "Candy Corn":
	echo "You eat the pieces of candy corn. Delicious!";
	break;
      case "Mentos":
	echo "You work your way through the packet of Mentos. Your breath now smells super-fresh, and the enemies you'll be fighting really appreciate it. Good on you!";
	break;
      case "Lemon":
	echo "You...eat the lemon. It's...sour. Unsurprisingly.";
	break;
      default:
	echo "Whatever you just tried to consume, it wasn't a consumable.";
	$fail = True;
	break;
      }
    }
    echo "</br>";
    if (!$fail && !$donotconsume) {
      $consumed = $_POST['consume'];
      mysql_query("UPDATE `Players` SET `" . $_POST['consume'] . "` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      if ($userrow['equipped'] == $_POST['consume']) mysql_query("UPDATE `Players` SET `equipped` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //If consumable equipped or offhand
      if ($userrow['offhand'] == $_POST['consume']) mysql_query("UPDATE `Players` SET `offhand` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //...then unequip that slot.
    }
  }
  //--End consuming code here.--
  echo "Consumables Consumer v0.0.1a. Please select a consumable to consume.";
  echo '<form action="consumables.php" method="post"><select name="consume">';
  $reachinv = false;
  $invresult = mysql_query("SELECT * FROM Players");
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
  echo '</select> <input type="submit" value="Consume it!" /> </form>';
  echo '<a href="strife.php">Strife</a></br>';
}
?>