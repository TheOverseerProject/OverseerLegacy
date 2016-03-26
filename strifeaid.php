<?php
require 'additem.php';
require 'monstermaker.php';
require_once 'includes/chaincheck.php';
require_once 'includes/fieldparser.php';
require_once("header.php");
$max_enemies = 5; //Note that this is ALSO in monstermaker.php. That isn't ideal, but eh. (Also in striferesolve.php. Bluh. AND strifeselect.php. I should make a constants file at some stage)
if (empty($_SESSION['username'])) {
  echo "Log in to assist in strife.</br>";
} elseif ($userrow['sessionbossengaged'] == 1) {
  echo "You are currently fighting a session-wide boss! <a href='sessionboss.php'>Go here.</a></br>";
} else {
  require_once("includes/SQLconnect.php");
  if (!empty($_POST['aid'])) {
    $aid = $_POST['aid'];
    $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '$aid'");
    while ($row = mysql_fetch_array($result)) { //Fetch the aid target's database row. We're going to need it several times.
      if ($row['username'] == $aid) {
	$aidrow = $row;
      }
    }
    //Check to make sure ally is still strifing
    if ($aidrow['session_name'] != $userrow['session_name']) {
      echo "And just what do you think YOU'RE doing?";
    } elseif (!empty($aidrow['enemydata'])) {
	    $aok = False;
	    if (($aidrow['dreamingstatus'] == $userrow['dreamingstatus'] && $userrow['dreamingstatus'] != "Awake") || $userrow['Godtier'] != 0)
	    $aok = true;
	    	$chain = chainArray($userrow);
        $totalchain = count($chain);
        $landcount = 1;
        while ($landcount < $totalchain && !$aok) {
        	if ($aid == $chain[$landcount]) $aok = true;
	  			$landcount++;
        }
        if (!$aok) { //player couldn't reach ally, so let's see if ally can reach player (will change when locations are implemented)
        	$chain = chainArray($aidrow);
        	$totalchain = count($chain);
        	$landcount = 1;
        	while ($landcount < $totalchain && !$aok) {
        		if ($username == $chain[$landcount]) $aok = true;
	  				$landcount++;
        	}
        }
        $onbattlefield = false; //the following is to check if the player the user wants to aid is on the battlefield
        $aidrow = parseEnemydata($aidrow);
      	if (!empty($aidrow['enemy1name'])) {
      		$enemyresult = mysql_query("SELECT * FROM Enemy_Types WHERE `Enemy_Types`.`basename` = '$aidrow[enemy1name]' LIMIT 1;");
      		while ($enemyrow = mysql_fetch_array($enemyresult)) {
      			if ($enemyrow['appearson'] == "Battlefield") $onbattlefield = true;
      		}
      	} //removed all the others because the first enemy will always be in slot 1
      	if ($onbattlefield) { //and if so...
      		if ($userrow['battlefield_access'] == 1) $aok = true; //only admit the user if they have battlefield access as well
      		else $aok = false;
      	}
	if ($aok) {
      mysql_query("UPDATE `Players` SET `aiding` = '$aid' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `encounters` = $userrow[encounters]-1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      echo '<a href="strife.php">You have begun aiding your ally.</a></br>';
      } else {
      if ($onbattlefield) echo "Your ally is fighting on the battlefield right now. You have other business to attend to before you can go there!";
      else echo "You can't reach the land your ally is fighting on from your available gates!</br>";
      }
    } else {
      echo '<a href="strife.php">Your ally is no longer strifing.</a></br>';
    }
  } elseif (!empty($_POST['autoassist'])) {
    $aid = $_POST['autoassist'];
    if ($aid == "noautoassist") {
      mysql_query("UPDATE `Players` SET `autoassist` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      echo "You will no longer automatically assist anyone.";
    } else {
      $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '$aid'");
      while ($row = mysql_fetch_array($result)) { //Fetch the aid target's database row. We're going to need it several times.
	if ($row['username'] == $aid) {
	  $aidrow = $row;
	}
      }
      if ($aidrow['session_name'] != $userrow['session_name']) {
	echo "And just what do you think YOU'RE doing?";
      } else {
	    $aok = False;
	    	$chain = chainArray($userrow);
        $totalchain = count($chain);
        $landcount = 1;
        while ($landcount < $totalchain && !$aok) {
        	if ($aid == $chain[$landcount]) $aok = true;
	  			$landcount++;
        }
        if (!$aok) { //player couldn't reach ally, so let's see if ally can reach player (will change when locations are implemented)
        	$chain = chainArray($aidrow);
        	$totalchain = count($chain);
        	$landcount = 1;
        	while ($landcount < $totalchain && !$aok) {
        		if ($username == $chain[$landcount]) $aok = true;
	  				$landcount++;
        	}
        }
	if ($aok) {
	mysql_query("UPDATE `Players` SET `autoassist` = '$aid' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	echo '<a href="strife.php">You will now automatically aid ' . $aid . ' if you have a spare encounter and are able to when they begin strifing.</a></br>';
	} else {
	echo "You won't be able to reach that ally with your current gate setup.</br>";
	}
      }
    }
  } else {
    echo "You shouldn't be here.";
  }
}
require_once("footer.php");
?>