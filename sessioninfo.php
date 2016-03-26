<?php
function highestGate($gaterow, $grist) {
	$gatecount = 0;
	while ($gatecount < 7) { //going off the assumption that there will never be more than 7 gates
		if ($grist < $gaterow['gate' . strval($gatecount + 1)]) return $gatecount;
		$gatecount++;
	}
	return 7;
}
require_once("header.php");
require_once("includes/fieldparser.php");
if (!empty($_SESSION['username'])) $session = $userrow['session_name']; //automatically look up the user's session if logged in
if (!empty($_GET['session'])) $session = $_GET['session'];
echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
echo '<form action="sessioninfo.php" method="post">';
echo 'Session to retrieve info about: <input id="session" name="session" type="text" /><input type="submit" value="Examine it!" /> </form></br>';
if (!empty($_POST['session'])) $session = $_POST['session'];
if (!empty($session)) { //Session to examine
  $sessionesc = str_replace("'", "''", $session); //Add escape characters so we can find session correctly in database.
  $sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$sessionesc'");
  $sessionexists = False;
  while ($row = mysql_fetch_array($sessionresult)) {
    if ($row['name'] == $session) {
      $sessionexists = True;
      $sessionrow = $row; //This is currently not needed but may be useful later.
    }
  }
  if ($sessionexists != True) {
    echo "ERROR - Session does not exist.</br>";
  } else {
  	$gateresult = mysql_query("SELECT * FROM Gates"); //begin new chain-following code, shamelessly copypasted and trimmed down from Dungeons
  	$gaterow = mysql_fetch_array($gateresult); //Gates only has one row.
  	$gaterow['gate0'] = 0;
  	$adv = false;
  	if (strpos($userrow['storeditems'], "ADVSESSIONVIEW.") !== false || strpos($userrow['permstatus'], "SVISION") !== false) $adv = true;
    $sessionurl = str_replace("#", "%23", $session);
    $sessionurl = str_replace(" ", "%20", $sessionurl);
    echo '<a href="http://www.theoverseerproject.com/sessioninfo.php?session=' . $sessionurl . '">Permanent link to this page.</a></br>';
    echo "This session's head admin: " . $sessionrow['admin'] . "<br />";
    if (!empty($sessionrow['exchangeland']))
    echo "Player whose land hosts the Stock Exchange: " . $sessionrow['exchangeland'] . "<br />";
    else
    echo "This session's Stock Exchange is not yet available.<br />";
    echo "Dersite army power destroyed by this session: $sessionrow[battlefieldtotal]</br></br>";
    if ($sessionrow['checkmate'] == 1) echo "This session has successfully defeated The Black King!</br></br>";
    $sessionplayers = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$sessionesc'");
    while ($row = mysql_fetch_array($sessionplayers)) {
      if ($row['session_name'] == $session) { //Paranoia: Player is a participant in this session.
	echo "Player: $row[username]";
	if (!empty($row['Class']) && !empty ($row['Aspect'])) echo ", $row[Class] of $row[Aspect]";
	echo "</br>Dream status: $row[dreamer]</br>";
	$status = str_replace("\'", "'", $row['status']);
	echo "Currently: $status</br>";
	$echeresult = mysql_query("SELECT * FROM Echeladders WHERE `Echeladders`.`username` = '" . $row['username'] . "'");
	$echerow = mysql_fetch_array($echeresult);
	$echestr = "rung" . strval($row['Echeladder']);
	$echename = $echerow[$echestr];
	echo "Echeladder height: $row[Echeladder] ($echename)</br>";
	echo "Currently equipped weapons: ";
	if ($row['equipped'] != "") echo $row[$row['equipped']];
	if ($row['offhand'] != "" && $row['offhand'] != "2HAND") echo ", " . $row[$row['offhand']];
	echo "</br>";
	echo "Currently wearing: ";
	$addcomma = False;
	if ($row['headgear'] != "") {
	  echo $row[$row['headgear']];
	  $addcomma = True;
	  }
	if ($row['facegear'] != "" && $row['facegear'] != "2HAND") {
	  if ($addcomma == True) echo ", ";
	  echo $row[$row['facegear']];
	  $addcomma = True;
	  }
	if ($row['bodygear'] != "") {
	  if ($addcomma == True) echo ", ";
	  echo $row[$row['bodygear']];
	  $addcomma = True;
	  } else {
	  if ($addcomma == True) echo ", ";
	  echo "Basic Clothes";
	  $addcomma = True;
	  }
	if ($row['accessory'] != "") {
	  if ($addcomma == True) echo ", ";
	  echo $row[$row['accessory']];
	  }
	echo "</br>";
	echo "Sprite: $row[sprite_name]</br>";
	echo "$row[sprite_name]'s power: $row[sprite_strength]</br>";
	echo "Power bonus for enemies who receive this player's prototypings: $row[prototyping_strength]</br>";
	echo "Server player: ";
	if ($row['server_player'] != "") {
	  echo $row['server_player'];
	} else {
	  echo "None.";
	}
	echo "</br>";
	echo "Client player: ";
	if ($row['client_player'] != "") {
	  echo $row['client_player'];
	} else {
	  echo "None.";
	}
	echo "</br>";
	echo "Land: Land of $row[land1] and $row[land2]</br>";
	echo "Grist types available on this player's Land: ";
	$gristresult = mysql_query("SELECT * FROM Grist_Types");
	while ($gristrow = mysql_fetch_array($gristresult)) {
	  if ($gristrow['name'] == $row['grist_type']) $gristtype = $gristrow;
	}
	$i = 1;
	while ($i <= 9) { //Nine types of grist. Magic numbers >_>
	  $griststr = "grist" . strval($i);
	  echo $gristtype[$griststr];
	  if ($i != 9) {
	    echo ", ";
	  } else {
	    echo ".</br>";
	  }
	  $i++;
	}
	if ($adv) { //user has a session viewer upgrade
		echo "Highest gate reached: " . strval(highestGate($gaterow, $row['house_build_grist'])) . "<br />";
		echo "Dreaming status: " . $row['dreamingstatus'] . "<br />";
		if (!empty($row['aiding'])) echo "Currently assisting in the strife of: " . $row['aiding'] . '<br />';
		else {
		echo "Strifing against: ";
		$strifestring = "";
		$row = parseEnemydata($row);
		$e = 1;
		while ($e <= 5) {
			$enamestr = 'enemy' . strval($e) . 'name';
			if (!empty($row[$enamestr])) {
				if ($strifestring == "") $strifestring = $row[$enamestr];
				else $strifestring .= ", " . $row[$enamestr];
			}
			$e++;
		}
		if ($strifestring == "") $strifestring = "Nobody.";
		echo $strifestring . '<br />';
		}
		if ($row['indungeon'] != 0) echo "Currently exploring a dungeon.<br />";
		echo "Land wealth: " . strval($row['econony']) . "<br />";
		echo "Consorts: " . $row['consort_name'] . "<br />";
	}
	echo "</br>";
      }
    }
  }
}
require_once("footer.php");
?>