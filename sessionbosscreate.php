<?php
require_once("header.php");
require("monstermaker.php");
if (empty($_SESSION['username'])) {
  echo "Log in to engage bosses.</br>";
} else {
  
  //Need a check to make sure the boss fight is ready to happen, and also one to make sure there's not already a fight happening.
  $max_enemies = 5; //I'll put this in the header someday...
  $sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$userrow[session_name]'");
  $sessionrow = mysql_fetch_array($sessionresult);
  $bossresult = mysql_query("SELECT * FROM Enemy_Types where `Enemy_Types`.`basename` = '" . $_POST['bossname'] . "'");
  $bossvalues = mysql_fetch_array($bossresult);
  switch ($_POST['bossname']) {
  case "The Black King":
    //Security check to ensure it only works if the required votes exist goes here
    $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'");
    $kingvotes = 0;
    $chumroll = 0;
    $power = 0;
    $health = 0;
    while ($buddyrow = mysql_fetch_array($sessionmates)) {
      if ($buddyrow['kingvote'] == 1) $kingvotes++;
      $chumroll++;
    }
    if ((($kingvotes / $chumroll) * 100 <= 49.99999) && $username != "The Overseer") { //Need at least 50/50
      echo 'Not enough users vote in favour to initiate strife against the Black King!</br>';
    } elseif ($sessionrow['sessionbossname'] != "") { //Session already fighting boss
      echo 'A session-wide boss is already being fought!</br>';
    } elseif (($sessionrow['checkmate'] == 1 || $sessionrow['battlefieldtotal'] < $chumroll * $powerperplayer || $userrow[$downstr] == 1) && $username != "The Overseer") {
      echo "You are not able to fight this boss right now.</br>";
    } else {
	mysql_query("UPDATE Players SET `enemydata` = '' WHERE `Players`.`kingvote` = 1 AND `Players`.`session_name` = '$userrow[session_name]'");
      $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'");
      while ($buddyrow = mysql_fetch_array($sessionmates)) {
        if ($buddyrow['kingvote'] == 1 && $buddyrow['username'] != $userrow['username']) {
       	 generateEnemy($buddyrow,"None","None","The Black King",false);
       	 $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '$userrow[username]' LIMIT 1;");
      	 $datacheck = mysql_fetch_array($result);
      	 $datacheck = parseEnemydata($datacheck);
      	 mysql_query("UPDATE `Players` SET `Players`.`sessionbossinitialhealth` = $datacheck[enemy1health], `Players`.`sessionbossinitialpower` = $datacheck[enemy1power] WHERE `Players`.`username` = '$buddyrow[username]' LIMIT 1;");
       	} else {
       	  mysql_query("UPDATE Players SET `enemydata` = '' WHERE `Players`.`username` = '$username' LIMIT 1;");
       	  $userrow['enemydata'] = "";
       	  $userrow = parseEnemydata($userrow); //lol
       	  generateEnemy($userrow,"None","None","The Black King",false);
 	 $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $username . "' LIMIT 1;");
 	 while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
 	   if ($row['username'] == $username) { //Paranoia: Double-check.
  	    $userrow = $row;
   	   }
	  }
	  $userrow = parseEnemydata($userrow);
	  $health += $userrow['enemy1maxhealth'];
          $power += $userrow['enemy1maxpower'];
          mysql_query("UPDATE `Players` SET `Players`.`sessionbossinitialhealth` = $userrow[enemy1health], `Players`.`sessionbossinitialpower` = $userrow[enemy1power] WHERE `Players`.`username` = '$userrow[username]' LIMIT 1;");
       	}
      }
      $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`kingvote` = 1 AND `Players`.`session_name` = '$userrow[session_name]'");
      while ($buddyrow = mysql_fetch_array($sessionmates)) {
      	if ($buddyrow['username'] != $userrow['username']) { //Userrow contributions are added above.
        	$buddyrow = parseEnemydata($buddyrow);
   	     $health += $buddyrow['enemy1maxhealth'];
   	     $power += $buddyrow['enemy1maxpower'];
   	}
      }
      //BK's stats are set up here. They are hard-coded because the calculation is a bit weird, and all session-wide bosses will be hardcoded.
      mysql_query("UPDATE Sessions SET `sessionbossname` = 'The Black King' WHERE `Sessions`.`name` = '$userrow[session_name]'");
      mysql_query("UPDATE Sessions SET `sessionbosshealth` = $health WHERE `Sessions`.`name` = '$userrow[session_name]'");
      mysql_query("UPDATE Sessions SET `sessionbossmaxhealth` = $health WHERE `Sessions`.`name` = '$userrow[session_name]'");
      mysql_query("UPDATE Sessions SET `sessionbosspower` = $power WHERE `Sessions`.`name` = '$userrow[session_name]'");
      mysql_query("UPDATE Sessions SET `sessionbossmaxpower` = $power WHERE `Sessions`.`name` = '$userrow[session_name]'");
      //All session players who voted to engage the king do so. Query affects only players in your session with a positive vote.
      mysql_query("UPDATE Players SET `sessionbossengaged` = 1 WHERE `Players`.`kingvote` = 1 AND `Players`.`session_name` = '$userrow[session_name]'");
      $focus = floor(100 / $chumroll);
      mysql_query("UPDATE Players SET `sessionbossfocus` = $focus WHERE `Players`.`kingvote` = 1 AND `Players`.`session_name` = '$userrow[session_name]'");
      mysql_query("UPDATE `Players` SET `powerboost` = 0 WHERE `Players`.`kingvote` = 1 AND `Players`.`session_name` = '$userrow[session_name]'"); //Power boosts wear off.
	mysql_query("UPDATE `Players` SET `offenseboost` = 0 WHERE `Players`.`kingvote` = 1 AND `Players`.`session_name` = '$userrow[session_name]'");
	mysql_query("UPDATE `Players` SET `defenseboost` = 0 WHERE `Players`.`kingvote` = 1 AND `Players`.`session_name` = '$userrow[session_name]'");
	mysql_query("UPDATE `Players` SET `sessionbossleader` = 1 WHERE `Players`.`username` = '$username'"); //This player is now the leader.
      echo "<a href='sessionboss.php'>The Black King has been engaged</a>. (No, not to the Black Queen, dumby)</br>";
    }
    break;
  default:
    break;
  }
}
require_once("footer.php");
?>