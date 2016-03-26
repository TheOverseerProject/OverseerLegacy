<?php
require_once 'additem.php';
require_once 'monstermaker.php';
require_once 'includes/chaincheck.php';
require_once("header.php");
require_once 'includes/fieldparser.php';
$max_enemies = 5; //Note that this is ALSO in monstermaker.php. That isn't ideal, but eh. (Also in striferesolve.php. Bluh. AND strifeselect.php. I should make a constants file at some stage)
if (empty($_SESSION['username'])) {
  echo "Log in to engage in strife.</br>";
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} elseif ($userrow['enemydata'] != "" || $userrow['aiding'] != "") { //an awesome side-effect of condensing the strife data is that the "is player strifing?" code can be considerably shortened, yay!
  //User already strifing
  echo "You are already engaged in strife!</br>";
  include("strife.php");
} elseif ($userrow['correctgristtype'] != $_POST['gristtype'] && $_POST['gristtype'] != "None") {
  echo "Okay, I'm sorry about the last message being all abrasive. Here, how about this: ERROR - Grist type mismatch - $userrow[correctgristtype] expected, $_POST[gristtype] found.</br>";
} elseif ($userrow['sessionbossengaged'] == 1) {
  echo "You are currently fighting a session-wide boss! <a href='sessionboss.php'>Go here.</a></br>";
} else {
  require_once("includes/SQLconnect.php");
  $userrow = parseEnemydata($userrow);
	  if ($username == $_POST['land']) { //if the player chose their own land, always admit (and don't bother checking the chain)
	    $aok = True;
	    } elseif ($_POST['land'] == "Prospit" || $_POST['land'] == "Derse" || $_POST['land'] == "Battlefield" || $_POST['land'] == "LASTFOUGHT") { //well if you got this far with them...
	    	$aok = True;
	    } else {
	    $aok = False;
	    	$chain = chainArray($userrow);
        $totalchain = count($chain);
        $landcount = 1; //0 should be the user's land which we already printed
        while ($landcount < $totalchain && !$aok) {
        	if ($_POST['land'] == $chain[$landcount]) $aok = true;
	  			$landcount++;
        }
	    }
  if ($userrow['encounters'] > 0 && $userrow[$downstr] != 1 && $aok) { //User has an encounter and isn't down. Also isn't trying to cheat gates.
  if (!empty($_POST['gristall']) && !empty($_POST['enemyall'])) { //user is using the "fill all" box
  	$fillcount = 1;
  	while ($fillcount <= $max_enemies) {
  		$_POST['grist' . $fillcount] = $_POST['gristall'];
  		$_POST['enemy' . $fillcount] = $_POST['enemyall'];
  		$fillcount++;
  	}
  }
    $enemyexists = False;
    $enemies = 1;
    while ($enemies <= $max_enemies) {
      $griststr = "grist" . strval($enemies);
      $enemystr = "enemy" . strval($enemies);
      $oldgriststr = "oldgrist" . strval($enemies);
      $oldenemystr = $oldenemyprestr . strval($enemies);
      if (!empty($_POST[$enemystr]) && !empty($_POST[$griststr])) { //Enemy selected for this combat slot.
	$cheatyface = True;
	$enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $_POST[$enemystr] . "';");
	if ($enemyrow = mysql_fetch_array($enemyresult)) { 
	  if (($userrow['battlefield_access'] == 1 && $enemyrow['appearson'] == "Battlefield" && $_POST[$griststr] == "None") || $enemyrow['appearson'] == "Event" || ($userrow['dreamingstatus'] == "Awake" && $enemyrow['appearson'] == "Lands") || ($enemyrow['appearson'] == $userrow['dreamingstatus'] && $_POST[$griststr] == "None")) $cheatyface = False; //Event foes have different protected setups.
	}
	if ($cheatyface == False) {
	  $enemyexists = True;
	  generateEnemy($userrow,$_POST['gristtype'],$_POST[$griststr],$_POST[$enemystr],False); //Make the enemy and assign them to combat.
	  $userrow = refreshEnemydata($userrow);
	  if (empty($_POST['noprevious'])) {
	    $result2 = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '" . $_POST[$enemystr] . "'");
	    $row = mysql_fetch_array($result2); //Only enemies on Lands use the overly complicated grist dropping system.
	    if ($_POST[$griststr] != "None" || $userrow['dreamingstatus'] == "Awake") {
	    	$userrow[$oldgriststr] = $_POST[$griststr];
	    }
	    $userrow[$oldenemystr] = $_POST[$enemystr];
	  }
	} else {
	  echo "The $_POST[$enemystr] fails to be generated because you are attempting to generate one in a place where it shouldn't be.</br>";
	  $userrow[$oldenemystr] = "";
	  if ($userrow['dreamingstatus'] == "Awake") {
	  	$userrow[$oldgriststr] = "";
	  }
	}
      } else { //Empty it.
	$userrow[$oldenemystr] = "";
	if ($userrow['dreamingstatus'] == "Awake") {
		$userrow[$oldgriststr] = "";
	}
      }
      $enemies++;
    }
    if ($enemyexists) {
      if ($userrow['dreamingstatus'] == "Awake") mysql_query("UPDATE `Players` SET `lastgristtype` = '" . $_POST['gristtype'] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      //Above: Set the grist type used for this encounter IF the player is awake.
      mysql_query("UPDATE `Players` SET `combatmotifuses` = " . strval(floor($userrow['Echeladder'] / 100) + $userrow['Godtier']) . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `strifemessage` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Empty combat messages.
      chargeEncounters($userrow, 1, 1); //by this point, the user is guaranteed to have encounters
      if (!empty($_POST['success']) && !empty($_POST['failure'])) { //User exploring!
	mysql_query("UPDATE `Players` SET `strifesuccessexplore` = '" . $_POST['success'] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `strifefailureexplore` = '" . $_POST['failure'] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `strifeabscondexplore` = '" . $_POST['absconded'] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      }
      if (!empty($_POST['noassist'])) {
	echo "You will be unable to receive assistance in this fight.</br>";
	mysql_query("UPDATE `Players` SET `noassist` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      }
      if (!empty($_POST['stripbuffs'])) {
	echo "As the battle begins, you are mysteriously stripped of all ongoing power boosts.</br>";
	mysql_query("UPDATE `Players` SET `buffstrip` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `powerboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Power boosts wear off.
	mysql_query("UPDATE `Players` SET `offenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `defenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      }
      if (empty($_POST['noassist'])) { //Assistance allowed. Check for auto-assisters.
	  
    require_once("includes/SQLconnect.php");
	$assisters = mysql_query("SELECT * FROM Players WHERE `Players`.`autoassist` = '$username'") or die(mysql_error());
	while ($assistrow = mysql_fetch_array($assisters)) {
	  //We must process encounters in case they have earned any.
	  $up = False;
	  $time = time();
	  $interval = 1200; //This is where the interval between encounter ticks is set.
	  $lasttick = $assistrow['lasttick'];
	  $encounters = $assistrow['encounters'];
	  if ($lasttick != 0) {
	    while ($time - $lasttick > $interval) { //Attempt to tick up once per 20 minutes.
	      $encounters += 1;
	      $lasttick += $interval;
	    }
	  } else { //Player has not had a tick yet.
	    $lasttick = $time;
	  }
	  if ($encounters > $assistrow['encounters'] && ($assistrow['down'] == 1 || $assistrow['dreamdown'] == 1)) { //Both downs recover after a single encounter is earned.
	    $encounters -= 1;
	    mysql_query("UPDATE `Players` SET `down` = 0, `dreamdown` = 0 WHERE `Players`.`username` = '" . $assistrow['username'] . "' LIMIT 1 ;"); //Player recovers.
	    $up = True;
	  }
	  if ($encounters > 100) $encounters = 100;
	  if ($lasttick != $assistrow['lasttick']) {
	    mysql_query("UPDATE `Players` SET `encounters` = $encounters, `lasttick` = $lasttick WHERE `Players`.`username` = '" . $assistrow['username'] . "' LIMIT 1 ;");
	    $assistrow['encounters'] = $encounters;
	  }
	  if (($assistrow['down'] == 1 && $assistrow['dreamingstatus'] == "Awake") || ($assistrow['dreamdown'] == 1 && $assistrow['dreamingstatus'] != "Awake")) {
	    echo "$assistrow[username] is down and cannot assist you at this time.</br>";
	  } elseif ($assistrow['encounters'] <= 0) {
	    echo "$assistrow[username] is unable to assist you as they are out of encounters!</br>";
	  } elseif ($assistrow['dreamingstatus'] != $userrow['dreamingstatus'] && $assistrow['Godtier'] == 0)  {
	    echo "$assistrow[username] is currently unable to reach you to assist you!</br>";
	  } elseif ($assistrow['indungeon'] == 1) {
	  	echo "$assistrow[username] is unable to assist you as they are in a dungeon!</br>";
	  } elseif (!empty($assistrow['enemydata']) || !empty($assistrow['aiding'])) { //should also check aiding here in case the person is aiding someone else manually
	    echo "$assistrow[username] is unable to assist you as they are currently strifing!</br>";
	  } else {
	    echo "$assistrow[username] has automatically begun assisting you.</br>";
	    mysql_query("UPDATE `Players` SET `aiding` = '$username' WHERE `Players`.`username` = '" . $assistrow['username'] . "' LIMIT 1 ;");
	    mysql_query("UPDATE `Players` SET `encounters` = $assistrow[encounters]-1 WHERE `Players`.`username` = '" . $assistrow['username'] . "' LIMIT 1 ;");
	  }
	}
      }
      writeLastfought($userrow);
      if ($userrow['dreamingstatus'] == "Prospit") {
	echo '<a href="strife.php">&quot;Strife&quot; initiated.</a></br>';
		$userrow = mysql_fetch_array(mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_SESSION['username'] . "' LIMIT 1;")) or die(mysql_error());
		include("strife.php");
      } else {
	echo '<a href="strife.php">Strife initiated.</a></br>';
	$userrow = mysql_fetch_array(mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_SESSION['username'] . "' LIMIT 1;"));
	include("strife.php");
      }
    } else {
      if ($userrow['dreamingstatus'] == "Prospit") {
	echo "You must select an &quot;enemy&quot; to fight!</br>";
      } else {
	echo "You must select an enemy to fight!</br>";
      }
      echo '<a href="strife.php">Try again.</a></br>';
    }
  } else {
    if ($userrow['dreamingstatus'] == "Prospit") {
      echo "You have no encounters remaining or you're down! Either way, you can't &quot;strife&quot; right now.</br>";
    } else {
      if ($aok) {
      echo "You have no encounters remaining or you're down! Either way, you can't strife right now.</br>";
      } else {
      echo "Fool me once, shame on you. Fool me twice...</br>";
      }
    }
    if (!empty($_POST['success']) && !empty($_POST['failure'])) { //User exploring!
      mysql_query("UPDATE `Players` SET `exploration` = '" . $_POST['failure'] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
    }
  }
}
require_once("footer.php");
?>