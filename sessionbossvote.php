<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to vote on or initiate boss fights.</br>";
} else {
  
  //Process forms here
  if (!empty($_POST['kingvote'])) {
    if ($_POST['kingvote'] == "yes") {
      $userrow['kingvote'] = 1;
      mysql_query("UPDATE `Players` SET `kingvote` = 1 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
    } else {
      $userrow['kingvote'] = 0;
      mysql_query("UPDATE `Players` SET `kingvote` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
    }
  }
  //End form processing here
  $sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$userrow[session_name]'");
  $sessionrow = mysql_fetch_array($sessionresult);
  if ($sessionrow['sessionbossname'] == "") { //No boss being fought
    $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'");
    $kingvotes = 0;
    $chumroll = 0;
    $powerperplayer = 400000; //Amount of power worth of Dersite army dudes each player must defeat in order to fight BK
    while ($buddyrow = mysql_fetch_array($sessionmates)) {
      if ($buddyrow['kingvote'] == 1) $kingvotes++;
      $chumroll++;
    }
    $boss = False;
    if ($userrow['battlefield_access'] == 1 && $sessionrow['checkmate'] == 0 && $sessionrow['battlefieldtotal'] >= $chumroll * $powerperplayer) {
      $boss = True;
      if ($userrow['kingvote'] == 0) {
	echo '<form action="sessionbossvote.php" method="post"><input type="hidden" id="kingvote" name="kingvote" value="yes"><input type="submit" value="Vote for a fight against The Black King"></form>';
      } else {
	echo '<form action="sessionbossvote.php" method="post"><input type="hidden" id="kingvote" name="kingvote" value="no"><input type="submit" value="Cancel your vote for fighting The Black King"></form>';
      }
    }
    if ($sessionrow['checkmate'] == 0) {
      if ($sessionrow['battlefieldtotal'] >= $chumroll * $powerperplayer) {
	echo "Black King votes: $kingvotes/$chumroll</br>";
	if ((($kingvotes / $chumroll) * 100) > 50 && $userrow['kingvote'] == 1 && $userrow[$downstr] == 0) { //Only users who have voted for the king may initiate the battle.
	  echo '<form action="sessionbosscreate.php" method="post"><input type="hidden" id="bossname" name="bossname" value="The Black King"><input type="submit" value="STRIFE!"> (NOTE: Whoever begins the strife will be designated the "leader" of the combat. They will be responsible for actually locking in and executing every strife round.</form>';
	}
	if ($username == "The Overseer") { //For testing.
	  echo '<form action="sessionbosscreate.php" method="post"><input type="hidden" id="bossname" name="bossname" value="The Black King"><input type="submit" value="STRIFE!"></form>';
	}
      }
    } else {
      echo 'Your session has already defeated The Black King.</br>';
    }
    if (!$boss) echo "You cannot vote for any session bosses at present!";
  } else {
    echo "Your session is currently strifing against $sessionrow[sessionbossname]! ";
    if ($userrow['sessionbossengaged'] == 0 && $userrow[$downstr] == 0) echo '<form action="sessionboss.php" method="post"><input type="hidden" id="newfighter" name="newfighter" value="' . $sessionrow['sessionbossname'] . '"><input type="submit" value="Join the fight"></form>';
  }
} 
require_once("footer.php");
?>