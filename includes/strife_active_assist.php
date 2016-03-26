<?php
      $aiding = $userrow['aiding'];
      $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '$aiding'");
      while ($row = mysql_fetch_array($sessionmates)) { //Look for whoever we're aiding.
	if ($row['username'] == $aiding) {
	  $aidrow = $row;
	}
      }
      if ($aidrow['dreamingstatus'] == "Prospit") {
	echo 'You are currently assisting ' . $aiding . ', who is engaged in "strife" against the following "opponents":</br>';
      } else {
	echo "You are currently assisting $aiding, who is engaged in strife against the following opponents:</br>";
      }
      $i = 1;
      while ($i <= $max_enemies) {
	$enemystr = "enemy" . strval($i) . "name";
	$powerstr = "enemy" . strval($i) . "power";
	$healthstr = "enemy" . strval($i) . "health";
	$maxhealthstr = "enemy" . strval($i) . "maxhealth";
	$descstr = "enemy" . strval($i) . "desc";
	if ($aidrow[$enemystr] != "") { //Enemy located.
	  echo $aidrow[$enemystr];
	  echo ". Power: ";
	  echo strval($aidrow[$powerstr]);
	  echo ". Health Vial: ";
	  $healthvial = floor(($aidrow[$healthstr] / $aidrow[$maxhealthstr]) * 100); //Computes % of max HP remaining.
	  if ($healthvial == 0) $healthvial = 1;
	  echo strval($healthvial);
	  echo "%</br>";
	  echo $aidrow[$descstr] . "</br></br>";
	}
	$i++;
      }
      if ($aidrow['dreamingstatus'] == "Prospit") {
	echo 'Your power is being contributed to the "combat".';
      } else {
	echo 'Your power is being contributed to the combat.';
      }
      echo '<form action="strifeabandon.php" method="post"><input type="hidden" name="abandon" value="abandon" /><input type="submit" value="Stop assisting" /></form></br>';
      echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a> <a href="portfolio.php">Check combat capabilities</a> <a href="consumables.php">Use a consumable item</a>';
      if (!empty($_SESSION['adjective'])) echo " <a href='aspectpowers.php'>DO THE $_SESSION[adjective] THING</a>";
	  
	  ?>