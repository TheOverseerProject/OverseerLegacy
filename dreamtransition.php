<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to do the sleepy thing.</br>";
} elseif ($userrow['enemydata'] != "" || $userrow['aiding'] != "") { 
  echo "You can't sleep while strifing!</br>";
} elseif ($userrow['indungeon'] == 1 && $userrow['dreamingstatus'] == "Awake") {
	echo "You can't sleep while in a dungeon!</br>";
} else {
  $abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') 
AND `Abilities`.`Rungreq` BETWEEN 0 AND $userrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $userrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
  $abilities = array(0 => "Null ability. No, not void.");
  while ($temp = mysql_fetch_array($abilityresult)) {
    $abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
    //as pulling the correct element out of the abilities array via the ID. Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
  }
  if (!empty($_POST['sleep'])) {
    if ($userrow['dreamingstatus'] != "Awake") {
      echo "You drift off to sleep, awakening as your waking self moments later.</br>";
      mysql_query("UPDATE `Players` SET `dreamingstatus` = 'Awake' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `powerboost` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `offenseboost` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `defenseboost` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `temppowerboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `tempoffenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `tempdefenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `Brief_Luck` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `strifesuceessexplore` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `strifefailureexlpore` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `correctgristtype` = '$userrow[lastgristtype]' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      if (!empty($_POST['nextevent'])) mysql_query("UPDATE `Players` SET `exploration` = '" . $_POST['nextevent'] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      if ($userrow['encountersspent'] > 0) { //Player spent encounters while asleep.
	$newhp = $userrow['Health_Vial'] + ceil(($userrow['Gel_Viscosity'] / 5) * $userrow['encountersspent']);
	if ($newhp > $userrow['Gel_Viscosity']) $newhp = $userrow['Gel_Viscosity'];
	mysql_query("UPDATE `Players` SET `Health_Vial` = $newhp WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	$newaspect = $userrow['Aspect_Vial'] + ceil(($userrow['Gel_Viscosity'] / 8) * $userrow['encountersspent']);
	if (!empty($abilities[9])) $newaspect = $newaspect + ceil(($userrow['Gel_Viscosity'] / 16) * $userrow['encountersspent']); //Aspect Connection (ID 9): 1.5x recovery.
	if ($newaspect > $userrow['Gel_Viscosity']) $newaspect = $userrow['Gel_Viscosity'];
	mysql_query("UPDATE `Players` SET `Aspect_Vial` = $newaspect WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `encountersspent` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	echo "Spending time as your dream self has let your waking self rest, allowing them to recover health.</br>";
	echo "Resting in one state has strengthened your connection to your Aspect.</br>";
	if (!empty($abilities[9])) echo $abilities[9]; //Aspect Connection occurrence message.
      }
    } else {
      if ($userrow['dreamer'] == "Unawakened") {
	if ($userrow['encounters'] > 0) { //Player has encounters.
	  $newhp = $userrow['Health_Vial'] + ceil($userrow['Gel_Viscosity'] / 8);
	  if ($newhp > $userrow['Gel_Viscosity']) $newhp = $userrow['Gel_Viscosity'];
	  mysql_query("UPDATE `Players` SET `Health_Vial` = $newhp WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $newaspect = $userrow['Aspect_Vial'] + ceil($userrow['Gel_Viscosity'] / 12);
	  if (!empty($abilities[9])) $newaspect = $newaspect + ceil($userrow['Gel_Viscosity'] / 24); //Aspect Connection (ID 9): 1.5x recovery.
	  if ($newaspect > $userrow['Gel_Viscosity']) $newaspect = $userrow['Gel_Viscosity'];
	  mysql_query("UPDATE `Players` SET `Aspect_Vial` = $newaspect WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `encounters` = $userrow[encounters]-1 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `powerboost` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `offenseboost` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `defenseboost` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `temppowerboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `tempoffenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `tempdefenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `Brief_Luck` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `strifesuceessexplore` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  mysql_query("UPDATE `Players` SET `strifefailureexlpore` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  echo "You head back to your dwelling and sleep, feeling marginally better when you awaken.</br>";
	  if (!empty($abilities[9])) echo $abilities[9]; //Aspect Connection occurrence message.
	} else {
	  echo "You do not have any encounters remaining and therefore cannot encounter any sleep.</br>";
	}
      } else {
	echo "You drift off to sleep, awakening as your dream self moments later.</br>";
	mysql_query("UPDATE `Players` SET `dreamingstatus` = '$userrow[dreamer]' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `powerboost` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `offenseboost` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `defenseboost` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `temppowerboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `tempoffenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `tempdefenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `Brief_Luck` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `strifesuceessexplore` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `strifefailureexlpore` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	mysql_query("UPDATE `Players` SET `correctgristtype` = 'None' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Stops anti-cheating going off for Prospit/Derse encounters.
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
      }
    }
    echo '<a href="overview.php">==&gt;</a>';
  } else {
    echo "You're not supposed to be here.";
  }
}
require_once("footer.php");
?>