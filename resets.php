<?php
require_once("header.php");
require 'additem.php';
if (empty($_SESSION['username'])) {
  echo "Log in to reset shit.</br>";
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} else {
  $sessionname = $userrow['session_name'];
	$sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$sessionname'");
	$sessionrow = mysql_fetch_array($sessionresult);
	$challenge = $sessionrow['challenge'];
  //Begin resetting code here.
  if (!empty($_POST['reset'])) {
    $resetti = $_POST['reset'];
    switch ($resetti) {
    case "specibi":
      if ($userrow['Boondollars'] < 10000) {
	echo "You can't afford that!</br>";
      } elseif ($userrow['abstratus1'] == "") {
	echo "You don't have any assigned specibi that need resetting!</br>";
      } elseif ($challenge == 1) {
      	$wepstring = substr($userrow['abstratus1'], 0, strlen($userrow['abstratus1']) - 4);
      	echo "You can't reset your strife specibus in challenge mode. Hope you like " . $wepstring . "s dude!</br>";
      } else {
	$i = 16;
	while ($i >= 1) { //Magic number: Number of abstrati. This is the only place it will be referenced.
	  $abstrastr = "abstratus" . strval($i);
	  if ($userrow[$abstrastr] != "") {
	  mysql_query("UPDATE `Players` SET `" . $abstrastr . "` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $i = 0;
	  }
	  $i--;
	}
	mysql_query("UPDATE `Players` SET `equipped` = '', `offhand` = '', `Boondollars` = $userrow[Boondollars]-10000 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	echo "Specibus successfully reset!</br>NOTE: Because of your portfolio change, your strife specibus has ejected all of your weapons, unequipping them. Be sure to gear up properly before going into strife!</br>";
      }
      break;
    case "classpect":
      if ($userrow['Echeladder'] > 10) {
        echo "You have achieved too much with your current title to change it. Your fate is sealed!</br>";
      } else {
        mysql_query("UPDATE `Players` SET `Class` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
        mysql_query("UPDATE `Players` SET `Aspect` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	echo "Classpect reset. Now be more careful next time! :P</br>";
      }
      break;
    case "gristtype":
      if ($userrow['Echeladder'] > 10) {
        echo "You are too high on your echeladder to terraform your land.</br>";
      } elseif ($userrow['house_build_grist'] > 100) {
        echo "You have already built your house too high on your land. Terraforming now would destroy it!</br>";
      } else {
        $gristresult = mysql_query("SELECT * FROM Grist_Types");
        echo '<form action="resets.php" method="post">Choose a new grist category: <select name="newgrist">';
	while ($gristrow = mysql_fetch_array($gristresult)) {
  	echo '<option value="' . $gristrow['name'] . '">' . $gristrow['name'] . ' - ';
  	$i = 1;
  	while ($i <= 9) { //Nine types of grist. Magic numbers >_>
    	  $griststr = "grist" . strval($i);
    	  echo $gristrow[$griststr];
    	  if ($i != 9) echo ", ";
    	  $i++;
  	  }
  	echo '</option>';
	}
	echo '</select><input type="submit" value="Terraform it!" /></form></br>';
      }
      break;
    case "landswap":
      $landone = $userrow['land1'];
      $landtwo = $userrow['land2'];
      mysql_query("UPDATE `Players` SET `land1` = '$landtwo' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `land2` = '$landone' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      echo "Done! Your land shall now be referred to as the Land of " . $landtwo . " and " . $landone . ".</br>";
      break;
    default:
      break;
    }
  }

  if (!empty($_POST['newgrist'])) {
      if ($userrow['Echeladder'] > 10) {
        echo "You are too high on your echeladder to terraform your land.</br>";
      } elseif ($userrow['House_Build_Grist'] > 100) {
        echo "You have already built your house too high on your land. Terraforming now would destroy it!</br>";
      } else {
        mysql_query("UPDATE `Players` SET `grist_type` = '" . $_POST['newgrist'] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	echo "Your land has been terraformed and its grist types have changed.</br>";
      }
  }
  //End resetting code here.
  echo "Resetting machine. Please select a character aspect to reset.";
  echo '<form action="resets.php" method="post"><select name="reset">';
  echo '<option value="specibi">Reset Strife Specibi - 10000 Boondollars</option>';
  echo '<option value="classpect">Reset Class and Aspect - Rung 10 or lower</option>';
  echo '<option value="gristtype">Reset Grist Type - Rung 10 or lower, no gates</option>';
  echo '<option value="landswap">Swap your two land names - Free to all</option>';
  echo '</select><input type="submit" value="Reset it!" /> </form>';
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
}
require_once("footer.php");
?>