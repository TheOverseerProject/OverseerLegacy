<?php
function replacer($userrow,$string) {
  $string = str_replace("%username%", $userrow['username'], $string);
  $string = str_replace("%title%", $userrow['Class'] . " of " . $userrow['Aspect'], $string);
  $string = str_replace("%land1%", $userrow['land1'], $string);
  $string = str_replace("%land2%", $userrow['land2'], $string);
  if (empty($userrow['colour'])) {
    $string = str_replace("%colour%", "your favourite color", $string);
  } else {
    $string = str_replace("%colour%", "<favcolour>$userrow[colour]</favcolour>", $string);
  }
  $denizenresult = mysql_query("SELECT * FROM Titles WHERE `Titles`.`Class` = 'Denizen'");
  $denizenrow = mysql_fetch_array($denizenresult);
  $string = str_replace("%denizen%", $denizenrow[$userrow['Aspect']], $string);
  return $string;
}
function linkchecker($userrow,$explorow,$num) {
  $pass = True;
  $livestr = "link" . $num . "live";
  if ($explorow[$livestr] == 0 && $userrow['session_name'] != "Developers") $pass = False; //Link not live.
  //NOTE - Conditions on the link will be investigated for satisfaction here.
  return $pass;
}
require_once("header.php");
$max_links = 5; //Both standard and random event.
$max_enemies = 5;
if (empty($_SESSION['username'])) {
  echo "Log in to explore your dreams.</br>";
} elseif ($userrow['dreamingstatus'] == "Awake" && $userrow['Godtier'] == 0 && $userrow['exploration'] != "7thgateout") { //Allow waking players exploring the Denizen palace in.
  echo "You cannot explore dream locations with your waking self until you have ascended to the god tiers.";
} else {
  require_once("includes/SQLconnect.php");
  //Begin travel code here.
  $travel = False;
  if (!empty($_POST['newevent'])) {
    $travel = True;
    if (!empty($_POST['olddesc'])) echo "=" . $_POST['olddesc'] . "</br></br>";
    echo '<p class="courier">' . $_POST['oldevent'] . "</p></br>";
    if (strpos($_POST['newevent'], "random_") !== false) { //new event should be randomly selected using a keyword
    	$keyword = str_replace("random_", "", $_POST['newevent']) . "_";
    	$randresult = mysql_query("SELECT `name` FROM `Explore_" . $userrow['dreamingstatus'] . "` WHERE `Explore_" . $userrow['dreamingstatus'] . "`.`name` LIKE '" . $keyword . "%';");
    	$randtotal = 0;
    	$randarray = array(0 => "nothing");
    	while ($row = mysql_fetch_array($randresult)) {
    		$randtotal++;
    		$randarray[$randtotal] = $row['name'];
    	}
    	if ($randtotal > 0) {
	    	$roll = rand(1,$randtotal);
	    	$_POST['newevent'] = $randarray[$roll]; //done!
	}
    }
    mysql_query("UPDATE `Players` SET `exploration` = '" . $_POST['newevent'] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
    echo "=" . $_POST['newdesc'] . "</br></br>";
    $userrow['exploration'] = $_POST['newevent'];
    //We begin random transforms before anything else so that the transformed into event takes place rather than the original.
    //We do them here so that the random event can't be obtained by spam-refreshing the page.
    $exploresult = mysql_query("SELECT * FROM `Explore_" . $userrow['dreamingstatus'] . "` WHERE `Explore_" . $userrow['dreamingstatus'] . "`.`name` = '" . $userrow['exploration'] . "';");
    $explorow = mysql_fetch_array($exploresult);
    $i = 1;
    while ($i <= $max_links) {
      $randomstr = "random" . strval($i) . "chance";
      $randomnamestr = "random" . strval($i);
      $random = rand(1,100);
      if ($explorow[$randomstr] > 0) { //There's an event here.
	if ($random <= $explorow[$randomstr]) { //Random event selected. Change current event to selected random event.
	  mysql_query("UPDATE `Players` SET `exploration` = '" . $explorow[$randomnamestr] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  $exploresult = mysql_query("SELECT * FROM `Explore_" . $userrow['dreamingstatus'] . "` WHERE `Explore_" . $userrow['dreamingstatus'] . "`.`name` = '" . $explorow[$randomnamestr] . "';");
	  $explorow = mysql_fetch_array($exploresult);
	  $i = $max_links; //Done.
	} else { //Random event not selected.
	  $random -= $explorow[$randomstr];
	}
      }
      $i++;
    }
  }
  //End travel code here.
  if ($travel == False) { //We didn't move. If we did, we did this in the travel code to facilitate randoming.
    $exploresult = mysql_query("SELECT * FROM `Explore_" . $userrow['dreamingstatus'] . "` WHERE `Explore_" . $userrow['dreamingstatus'] . "`.`name` = '" . $userrow['exploration'] . "';");
    $explorow = mysql_fetch_array($exploresult);
  }
  $oldevent = replacer($userrow,$explorow['description']) . "</br>";
  echo '<p class="courier">' . $oldevent . "</p>";
  if (!empty($explorow['transform'])) mysql_query("UPDATE `Players` SET `exploration` = '" . $explorow['transform'] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Transform event.
  if ($explorow['boonreward'] != 0) mysql_query("UPDATE `Players` SET `Boondollars` = " . strval($userrow['Boondollars']+$explorow['boonreward']) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
  $i = 1;
  while ($i <= $max_links) {
    $linkstr = "link" . strval($i) . "name";
    $descstr = "link" . strval($i) . "desc";
    if (!empty($explorow[$linkstr])) {
      if (linkchecker($userrow,$explorow,$i)) { //Check to see if user gets this link.
	echo '<form action="explore.php" method="post">';
	echo '<input type="hidden" name="newevent" value="' . $explorow[$linkstr] . '" />';
	echo '<input type="hidden" name="oldevent" value="' . $oldevent . '" />';
	echo '<input type="hidden" name="newdesc" value="' . $explorow[$descstr] . '" />';
	if (!empty($_POST['newdesc'])) echo '<input type="hidden" name="olddesc" value="' . $_POST['newdesc'] . '" />';
	echo '<input type="submit" value="' . $explorow[$descstr] . '" /></form>';
      }
    }
    $i++;
  }
  if (!empty($explorow['strifelinksuccess']) && !empty($explorow['strifelinkfailure']) && !empty($explorow['strifelinkabscond'])) { //There's a strife link available.
    echo '<form action="strifebegin.php" method="post">';
    echo '<input type="hidden" name="success" value="' . $explorow['strifelinksuccess'] . '" />';
    echo '<input type="hidden" name="failure" value="' . $explorow['strifelinkfailure'] . '" />';
    echo '<input type="hidden" name="absconded" value="' . $explorow['strifelinkabscond'] . '" />';
    echo '<input type="hidden" name="land" value="' . $userrow['dreamingstatus'] . '" />';
    echo '<input type="hidden" name="gristtype" value="None">'; //Gristless enemies.
    $i = 1;
    while ($i <= $max_enemies) {
      $enemystr = "enemy" . $i;
      $griststr = "grist" . strval($i);
      echo '<input type="hidden" name="' . $griststr . '" value="None">'; //Gristless enemies.
      echo '<input type="hidden" name="' . $enemystr . '" value="' . $explorow[$enemystr] . '">';
      $i++;
    }
    echo '<input type="submit" value="' . $explorow['strifelinkdesc'] . '" /></form>';
  }
  if ($explorow['cansleep'] == 1) { //This event puts you straight to sleep.
    echo '<form action="dreamtransition.php" method="post">';
    echo '<input type="hidden" name="nextevent" value="' . $explorow['sleepevent'] . '" />';
    echo '<input type="hidden" name="sleep" value="sleep" />';
    echo '<input type="submit" value="==&gt;" /></form>';
  }
  if ($explorow['canleave'] == 1) { //This event can be exited from so you can head back to your room.
    echo '<form action="explore.php" method="post">';
    echo '<input type="hidden" name="newevent" value="wakeup" />';
    echo '<input type="hidden" name="oldevent" value="' . $oldevent . '" />';
    echo '<input type="hidden" name="newdesc" value=">You feel the game around you reconfiguring itself into a different saved state. Well, in the sense that the beginning of the game is a saved state." />';
    if (!empty($_POST['newdesc'])) echo '<input type="hidden" name="olddesc" value="' . $_POST['newdesc'] . '" />';
    echo '<input type="submit" value=">Start Over" /></form>';
  }
}
require_once("footer.php");
?>