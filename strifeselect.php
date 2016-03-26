<?php
require 'additem.php';
require 'monstermaker.php';
require_once 'includes/chaincheck.php';
require_once("header.php");
require_once 'includes/fieldparser.php';
$max_enemies = 5; //Note that this is ALSO in monstermaker.php. That isn't ideal, but eh. (Also in striferesolve.php. Bluh. AND strifeselect.php. I should make a constants file at some stage)
if (empty($_SESSION['username'])) {
  echo "Log in to engage in strife.</br>";
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} elseif ($userrow['sessionbossengaged'] == 1) {
  echo "You are currently fighting a session-wide boss! <a href='sessionboss.php'>Go here.</a></br>";
} else {
  require_once("includes/SQLconnect.php");
  if (empty($_POST['land'])) {
    echo "You need to select a Land to fight on first!";
  } else {
  	$userrow = parseLastfought($userrow);
    if ($_POST['land'] != "Prospit" && $_POST['land'] != "Derse" && $_POST['land'] != "Battlefield") { //To make sure no-one tries to make an account called these things to fuck everything up.
      $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['land'] . "';");
      if ($row = mysql_fetch_array($result)) {
	if ($row['username'] == $_POST['land']) {
	  if ($username == $_POST['land']) { //if the player chose their own land, always admit (and don't bother checking the chain)
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
	  if ($row['session_name'] == $userrow['session_name'] && $userrow['dreamingstatus'] == "Awake" && $aok == True) {
	    $landrow = $row;
	  } else {
	    $_POST['land'] = "Cheatyland";
	  }
	}
      }
    }
    if(empty($landrow)) { //A land row was not found. This is probably because the player selected a special location.
      switch ($_POST['land']) {
      case "Prospit":
	mysql_query("UPDATE `Players` SET `correctgristtype` = 'None' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	echo 'Select enemies to "fight":';
	echo '<form action="strifebegin.php" method="post">';
	echo '<input type="hidden" name="gristtype" value="None">';
	$enemies = 1;
	while ($enemies <= $max_enemies) {
	  $griststr = "grist" . strval($enemies);
	  echo '<input type="hidden" name="' . $griststr . '" value="None">'; //Gristless enemies.
	  $enemystr = "enemy" . strval($enemies);
	  echo '<select name="' . $enemystr . '">';
	  echo '<option value=""></option>';
	  $result2 = mysql_query("SELECT * FROM Enemy_Types ORDER BY `basepower`");
	  while ($row = mysql_fetch_array($result2)) {
	    $enemytype = $row['basename'];
	    if ($row['appearson'] == "Prospit") {
	      if ($userrow['olddreamenemy' . strval($enemies)] == $enemytype) echo '<option value="' . $enemytype . '" selected>' . $enemytype . ' ("Power": ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	      else echo '<option value="' . $enemytype . '">' . $enemytype . ' ("Power": ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	    }
	  }
	  echo '</select></br>';
	  $enemies++;
	}
	echo '<input type="hidden" name="land" value="' . $_POST['land'] . '">';
	echo '<input type="submit" value="&quot;Fight&quot; it!" /> </form></br>';
	echo '<form method="post" action="strifebegin.php">Alternatively, use the following to fill out all five enemy slots:</br>';
	echo '<input type="hidden" name="gristtype" value="None">'; //Gristless enemies.
		  $griststr = "gristall";
	  echo '<input type="hidden" name="' . $griststr . '" value="None">'; //Gristless enemies.
	  $enemystr = "enemyall";
	  echo '<select name="' . $enemystr . '">';
	  echo '<option value=""></option>';
	  $result2 = mysql_query("SELECT * FROM Enemy_Types ORDER BY `basepower`");
	  while ($row = mysql_fetch_array($result2)) {
	    $enemytype = $row['basename'];
	    if ($row['appearson'] == "Prospit") {
	      echo '<option value="' . $enemytype . '">' . $enemytype . ' ("Power": ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	    }
	  }
	  echo '</select></br>';
	  echo '<input type="hidden" name="land" value="' . $_POST['land'] . '">';
	echo '<input type="submit" value="&quot;Fight&quot; it!" /> </form>';
	break;
      case "Derse":
	mysql_query("UPDATE `Players` SET `correctgristtype` = 'None' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	echo "Select enemies to fight:";
	echo '<form action="strifebegin.php" method="post">';
	echo '<input type="hidden" name="gristtype" value="None">';
	$enemies = 1;
	while ($enemies <= $max_enemies) {
	  $griststr = "grist" . strval($enemies);
	  echo '<input type="hidden" name="' . $griststr . '" value="None">'; //Gristless enemies.
	  $enemystr = "enemy" . strval($enemies);
	  echo '<select name="' . $enemystr . '">';
	  echo '<option value=""></option>';
	  $result2 = mysql_query("SELECT * FROM Enemy_Types ORDER BY `basepower`");
	  while ($row = mysql_fetch_array($result2)) {
	    $enemytype = $row['basename'];
	    if ($row['appearson'] == "Derse") {
	    	if ($userrow['olddreamenemy' . strval($enemies)] == $enemytype) echo '<option value="' . $enemytype . '" selected>' . $enemytype . ' (Power: ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	      else echo '<option value="' . $enemytype . '">' . $enemytype . ' (Power: ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	    }
	  }
	  echo '</select></br>';
	  $enemies++;
	}
	echo '<input type="hidden" name="land" value="' . $_POST['land'] . '">';
	echo '<input type="submit" value="Fight it!" /> </form></br>';
	echo '<form method="post" action="strifebegin.php">Alternatively, use the following to fill out all five enemy slots:</br>';
	echo '<input type="hidden" name="gristtype" value="None">'; //Gristless enemies.
		  $griststr = "gristall";
	  echo '<input type="hidden" name="' . $griststr . '" value="None">'; //Gristless enemies.
	  $enemystr = "enemyall";
	  echo '<select name="' . $enemystr . '">';
	  echo '<option value=""></option>';
	  $result2 = mysql_query("SELECT * FROM Enemy_Types ORDER BY `basepower`");
	  while ($row = mysql_fetch_array($result2)) {
	    $enemytype = $row['basename'];
	    if ($row['appearson'] == "Derse") {
	      echo '<option value="' . $enemytype . '">' . $enemytype . ' (Power: ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	    }
	  }
	  echo '</select></br>';
	  echo '<input type="hidden" name="land" value="' . $_POST['land'] . '">';
	echo '<input type="submit" value="Fight it!" /> </form></br>';
	break;
      case "Battlefield":
	mysql_query("UPDATE `Players` SET `correctgristtype` = 'None' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	echo "Select enemies to fight:";
	echo '<form action="strifebegin.php" method="post">';
	echo '<input type="hidden" name="gristtype" value="None">'; //Gristless enemies.
	$enemies = 1;
	while ($enemies <= $max_enemies) {
	  $griststr = "grist" . strval($enemies);
	  echo '<input type="hidden" name="' . $griststr . '" value="None">'; //Gristless enemies.
	  $enemystr = "enemy" . strval($enemies);
	  echo '<select name="' . $enemystr . '">';
	  echo '<option value=""></option>';
	  $result2 = mysql_query("SELECT * FROM Enemy_Types ORDER BY `basepower`");
	  while ($row = mysql_fetch_array($result2)) {
	    $enemytype = $row['basename'];
	    if ($row['appearson'] == "Battlefield") {
	      if ($userrow['oldenemy' . strval($enemies)] == $enemytype) echo '<option value="' . $enemytype . '" selected>' . $enemytype . ' (Power: ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	      else echo '<option value="' . $enemytype . '">' . $enemytype . ' (Power: ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	    }
	  }
	  echo '</select></br>';
	  $enemies++;
	}
	echo '<input type="hidden" name="land" value="' . $_POST['land'] . '">';
	echo '<input type="submit" value="Fight it!" /> </form></br>';
	echo '<form method="post" action="strifebegin.php">Alternatively, use the following to fill out all five enemy slots:</br>';
	echo '<input type="hidden" name="gristtype" value="None">'; //Gristless enemies.
		  $griststr = "gristall";
	  echo '<input type="hidden" name="' . $griststr . '" value="None">'; //Gristless enemies.
	  $enemystr = "enemyall";
	  echo '<select name="' . $enemystr . '">';
	  echo '<option value=""></option>';
	  $result2 = mysql_query("SELECT * FROM Enemy_Types ORDER BY `basepower`");
	  while ($row = mysql_fetch_array($result2)) {
	    $enemytype = $row['basename'];
	    if ($row['appearson'] == "Battlefield") {
	      echo '<option value="' . $enemytype . '">' . $enemytype . ' (Power: ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	    }
	  }
	  echo '</select></br>';
	  	echo '<input type="hidden" name="land" value="' . $_POST['land'] . '">';
	echo '<input type="submit" value="Fight it!" /> </form></br>';
	break;
      case "Cheatyland":
	echo "Your ill-advised attempt to travel to another session predictably ends poorly. Now stop fucking around with inspect elements.</br>";
	break;
      default:
	echo $_POST['land'];
	echo ": Land not found.</br>"; //Ruh roh, raggy!
	break;
      }
    } else { //We have the player details we need.
      mysql_query("UPDATE `Players` SET `correctgristtype` = '" . $landrow['grist_type'] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
      echo "Select enemies to fight:";
      echo '<form action="strifebegin.php" method="post">';
      echo '<input type="hidden" name="gristtype" value="' . $landrow['grist_type'] .'">';
      $enemies = 1;
      while ($enemies <= $max_enemies) {
	$griststr = "grist" . strval($enemies);
	$stringwhoseuseisobviousandsingular = "Enemy " . strval($enemies) . ":";
	echo $stringwhoseuseisobviousandsingular . '<select name="' . $griststr . '">';
	echo '<option value=""></option>';
	$i = 1;
	$gristresult = mysql_query("SELECT * FROM Grist_Types");
	while ($row = mysql_fetch_array($gristresult)) {
	  if ($row['name'] == $landrow['grist_type']) {
	    $gristrow = $row;
	  }
	}
	while ($i < 10) {
	  $griststr = "grist" . strval($i);
	  if ($userrow['oldgrist' . strval($enemies)] == $gristrow[$griststr]) echo '<option value="' . $gristrow[$griststr] . '" selected>' . $gristrow[$griststr] . ' (x' . strval($i) . ')</option>'; //Produce an entry for this grist type.
	  else echo '<option value="' . $gristrow[$griststr] . '">' . $gristrow[$griststr] . ' (x' . strval($i) . ')</option>'; //Produce an entry for this grist type.
	  $i++;
	}
	echo '</select>';
	$enemystr = "enemy" . strval($enemies);
	echo '<select name="' . $enemystr . '">';
	echo '<option value=""></option>';
	$result2 = mysql_query("SELECT * FROM Enemy_Types ORDER BY `basepower`");
	while ($row = mysql_fetch_array($result2)) {
	  $enemytype = $row['basename'];
	  if ($row['appearson'] == "Lands") {
	  	if ($userrow['oldenemy' . strval($enemies)] == $enemytype) echo '<option value="' . $enemytype . '" selected>' . $enemytype . ' (Base power: ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	    else echo '<option value="' . $enemytype . '">' . $enemytype . ' (Base power: ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	  }
	}
	echo '</select></br>';
	$enemies++;
      }
      echo '<input type="hidden" name="land" value="' . $_POST['land'] . '">';
      echo '<input type="submit" value="Fight it!" /> </form></br>';
      echo '<form method="post" action="strifebegin.php">Alternatively, use the following to fill out all five enemy slots:</br>';
      echo '<input type="hidden" name="gristtype" value="' . $landrow['grist_type'] .'">';
      	$griststr = "gristall";
	echo '<select name="' . $griststr . '">';
	echo '<option value=""></option>';
	$i = 1;
	while ($i < 10) {
	  $griststr = "grist" . strval($i);
	  echo '<option value="' . $gristrow[$griststr] . '">' . $gristrow[$griststr] . ' (x' . strval($i) . ')</option>'; //Produce an entry for this grist type.
	  $i++;
	}
	echo '</select>';
	$enemystr = "enemyall";
	echo '<select name="' . $enemystr . '">';
	echo '<option value=""></option>';
	$result2 = mysql_query("SELECT * FROM Enemy_Types ORDER BY `basepower`");
	while ($row = mysql_fetch_array($result2)) {
	  $enemytype = $row['basename'];
	  if ($row['appearson'] == "Lands") {
	    echo '<option value="' . $enemytype . '">' . $enemytype . ' (Base power: ' . $row['basepower'] . ')</option>'; //Produce an option in the dropdown menu for this type of enemy.
	  }
	}
	echo '</select></br>';
	      echo '<input type="hidden" name="land" value="' . $_POST['land'] . '">';
      echo '<input type="submit" value="Fight it!" /> </form></br>';
    }
  }
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
}
require_once("footer.php");
?>
