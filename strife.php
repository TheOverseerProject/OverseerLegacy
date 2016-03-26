<?php
require_once 'additem.php';
require_once 'monstermaker.php';
require_once 'includes/chaincheck.php';
require_once 'includes/glitches.php'; //For displaying glitchy nonsense
require_once("header.php");
require_once 'includes/fieldparser.php';
if (!empty($_SESSION['username'])) {
      if ($userrow['dreamingstatus'] == "Awake") {
	$healthcurrent = strval(floor(($userrow['Health_Vial'] / $userrow['Gel_Viscosity']) * 100));
      } else {
	$healthcurrent = strval(floor(($userrow['Dream_Health_Vial'] / $userrow['Gel_Viscosity']) * 100));
      }
      $aspectcurrent = strval(floor(($userrow['Aspect_Vial'] / $userrow['Gel_Viscosity']) * 100));
      }
      $healthname = strtolower($userrow['colour']);
      if (empty($healthname)) $healthname = "black";
      if (empty($aspectname)) $aspectname = "doom";
      $aspectname = strtolower($userrow['Aspect']);
echo '</body><head><script src="jquery.min.js"></script><style type="text/css"></style>
		<script src="raphael-min.js" type="text/javascript" charset="utf-8"></script>
		<script src="html5slider.js" type="text/javascript" charset="utf-8"></script>
		<script src="vials.js" type="text/javascript" charset="utf-8"></script>
		<script>window.onload = function () {
	drawVial("health", "' . $healthname . '", ' . strval($healthcurrent) . ');
	drawVial("aspect", "' . $aspectname . '", ' . strval($aspectcurrent) . ');
}</script></head><body>';
$max_enemies = 5; //Note that this is ALSO in monstermaker.php. That isn't ideal, but eh. (Also in striferesolve.php. Bluh. AND strifeselect.php. I should make a constants file at some stage)
if (empty($_SESSION['username'])) {
  echo "Log in to engage in strife.</br>";
  include("loginer.php");
} elseif ($userrow['sessionbossengaged'] == 1) {
  echo "You are currently fighting a session-wide boss! <a href='sessionboss.php'>Go here.</a></br>";
} else {
	$userrow = parseEnemydata($userrow);
  $userrow = parseLastfought($userrow);
  if ($userrow['dreamingstatus'] == "Prospit") {
    echo '<div style = "float: right;"><a href="combatbasics.php">The basics of "strife"</a></div></br>';
  } else {
    echo '<div style = "float: right;"><a href="combatbasics.php">The basics of strife</a></div>';
  }
  if ($userrow['enemy1name'] == "" && $userrow['enemy2name'] == "" && $userrow['enemy3name'] == "" && $userrow['enemy4name'] == "" && $userrow['enemy5name'] == "" && $userrow['aiding'] == "") { //No enemies, not aiding
    $up = 0;
    if ($userrow['dreamingstatus'] == "Prospit") {
      echo 'You are not currently engaged in "strife".</br>';
    } else {
      echo "You are not currently engaged in strife.</br>";
    }
    if ($userrow[$downstr] == 1 && $up != True) { //Player is not gaining a new encounter and IS down. Up is a variable in the header.
      if ($userrow['dreamingstatus'] == "Prospit") {
	echo "You're still exhausted from all that do-gooding! You will recover instead of earning your next encounter and you're far too tired to be helpful yet.</br>";
      } else {
	echo "You are still reeling from a recent defeat. You will recover instead of earning your next encounter and you cannot get into any more fights yet.</br>";
      }
    }
    echo "</br>Health Vial: " . strval(floor(($userrow[$healthvialstr] / $userrow['Gel_Viscosity']) * 100)) . "%";
    echo "</br>Aspect Vial: " . strval(floor(($userrow['Aspect_Vial'] / $userrow['Gel_Viscosity']) * 100)) . "%";
    if ($encounters > 0 && ($up == 1 || ($userrow['down'] != 1 && $userrow['dreamingstatus'] == "Awake") || ($userrow['dreamdown'] != 1 && $userrow['dreamingstatus'] != "Awake"))) { //Not down.
      if ($userrow['dreamingstatus'] == "Awake") { //Only do Land combat while awake.
				$chain = chainArray($userrow);
				echo '<form action="strifeselect.php" method="post">Select a Land to fight on:<select name="land"> '; //Only select the Land for combat at this stage.
		  	$locationstr = "Land of " . $userrow['land1'] . " and " . $userrow['land2'];
	  		echo '<option value="' . $userrow['username'] . '">' . $locationstr . '</option>';
        $totalchain = count($chain);
        $landcount = 1; //0 should be the user's land which we already printed
        while ($landcount < $totalchain) {
        	$currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $chain[$landcount] . "';");
	    		$currentrow = mysql_fetch_array($currentresult);
	  			$locationstr = "Land of " . $currentrow['land1'] . " and " . $currentrow['land2'];
	  			echo '<option value="' . $currentrow['username'] . '">' . $locationstr . '</option>';
	  			$landcount++;
        }
	if ($userrow['battlefield_access'] != 0) { //Player has handled their denizen or gone god tier. The battlefield is available as a zone.
	  echo '<option value="Battlefield">The Battlefield</option>';
	}
	echo '</select></br><input type="submit" value="Fight on this Land" /> </form>';
	echo '<form action="strifebegin.php" method="post">';
	echo '<input type="hidden" name="gristtype" value="' . $userrow['lastgristtype'] . '">';
	echo "Enemies fought last:</br>";
	$i = 1;
	while ($i <= $max_enemies) { //Fetch up the enemies the player last fought.
	  $oldgrist = $userrow['oldgrist' . strval($i)];
	  $oldenemy = $userrow['oldenemy' . strval($i)];
	  $griststr = "grist" . strval($i);
	  $enemystr = "enemy" . strval($i);
	  if ($oldgrist != "" || $oldenemy != "") {
	    if ($oldgrist != "None") {
	      echo $oldgrist . " " . $oldenemy . "</br>";
	    } else {
	      echo $oldenemy . "</br>";
	    }
	  }
	  echo '<input type="hidden" name="' . $griststr . '" value="' . $oldgrist . '">';
	  echo '<input type="hidden" name="' . $enemystr . '" value="' . $oldenemy . '">';
	  $i++;
	}
	mysql_query("UPDATE `Players` SET `correctgristtype` = '$userrow[lastgristtype]' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Should be safe.
	echo '<input type="hidden" name="land" value="LASTFOUGHT">';
	echo '<input type="submit" value="Fight these enemies again!" /> </form>';
      } else { //Sleeping strifes
	echo '<form action="strifeselect.php" method="post"><input type="hidden" name="land" value="' . $userrow['dreamingstatus'] . '">';
	if ($userrow['dreamingstatus'] == "Prospit") {
	  echo '<input type="submit" value="&quot;Fight&quot; on ' . $userrow['dreamingstatus'] . '" /> </form>';
	} else {
	  echo '<input type="submit" value="Fight on ' . $userrow['dreamingstatus'] . '" /> </form>';
	}
	echo '<form action="strifebegin.php" method="post">';
	echo '<input type="hidden" name="gristtype" value="None">';
	echo "Enemies fought last:</br>";
	$i = 1;
	while ($i <= $max_enemies) { //Fetch up the enemies the player last fought.
	  $oldenemy = $userrow['olddreamenemy' . strval($i)];
	  $griststr = "grist" . strval($i);
	  $enemystr = "enemy" . strval($i);
	  if ($oldenemy != "") echo $oldenemy . "</br>";
	  echo '<input type="hidden" name="' . $griststr . '" value="None">';
	  echo '<input type="hidden" name="' . $enemystr . '" value="' . $oldenemy . '">';
	  $i++;
	}
	echo '<input type="hidden" name="land" value="LASTFOUGHT">';
	echo '<input type="submit" value="Fight these enemies again!" /> </form>';
      }
      $sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
      $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'");
      $aidneeded = False;
      while ($row = mysql_fetch_array($sessionmates)) {
	if ($row['session_name'] == $userrow['session_name'] && $row['username'] != $userrow['username'] && $row['dreamingstatus'] == $userrow['dreamingstatus']) { //No aiding yourself!
	  //Note that we can only try to aid allies with the same current dreaming status.
	  if (!empty($row['enemydata'])) { //Ally is strifing
	    if ($row['noassist'] != 1 && $row['sessionbossengaged'] != 1) { //Player is able to receive assistance.
	      if ($aidneeded == False) {
		echo '<form action="strifeaid.php" method="post">Select an ally to aid:<select name="aid"> ';
		$aidneeded = True;
	      }
	      echo '<option value="' . $row['username'] . '">' . $row['username'] . '</option>'; //Add ally to list of aidable allies.
	    }
	  }
	}
      }
      if ($aidneeded == True) {
	echo '</select></br><input type="submit" value="Assist this ally" /> </form></br>';
      }
    }
    //Begin auto-assist form.
    $sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
    $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'");
    echo '<form action="strifeaid.php" method="post">Select an ally to auto-assist. You will be automatically made to assist this ally whenever they begin strifing and it is possible for you to aid them.</br>';
    if (!empty($userrow['autoassist'])) echo "You are currently auto-assisting: $userrow[autoassist]</br>";
    echo 'Select a player to auto-assist: <select name="autoassist"> ';
    echo '<option value="noautoassist">Nobody!</option>';
    while ($row = mysql_fetch_array($sessionmates)) {
      if ($row['session_name'] == $userrow['session_name'] && $row['username'] != $userrow['username']) { //No aiding yourself!
	echo '<option value="' . $row['username'] . '">' . $row['username'] . '</option>'; //Add ally to list of aidable allies.
      }
    }
    echo '</select></br><input type="submit" value="Auto-assist this ally" /> </form></br>';
    //End auto-assist form.
    $sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$userrow[session_name]'");
    $sessionrow = mysql_fetch_array($sessionresult);
    if ($sessionrow['sessionbossname'] == "") { //No current fight.
      echo '<a href="sessionbossvote.php">Vote on whole session boss strifes.</a>';
    } else {
      echo "Your session has engaged $sessionrow[sessionbossname]!</br>";
      echo '<form action="sessionboss.php" method="post"><input type="hidden" id="newfighter" name="newfighter" value="' . $sessionrow['sessionbossname'] . '"><input type="submit" value="Join the fight"></form>';
    }
  } else { //Enemies currently engaged or currently aiding ally: Strife!
    if ($userrow['aiding'] != "") {
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
      $aidrow = parseEnemydata($aidrow);
      $i = 1;
      while ($i <= $max_enemies) {
	$enemystr = "enemy" . strval($i) . "name";
	$powerstr = "enemy" . strval($i) . "power";
	$healthstr = "enemy" . strval($i) . "health";
	$maxhealthstr = "enemy" . strval($i) . "maxhealth";
	$descstr = "enemy" . strval($i) . "desc";
	if ($aidrow[$enemystr] != "") { //Enemy located.
	  echo "<b>" . $aidrow[$enemystr];
	  echo "</b>. Power: ";
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
      if ($userrow['cantabscond'] == 1) {
      	if (time() - $aidrow['bossbegintime'] > 86400) {
      		echo 'It has been over 24 hours since the main strifer began the boss fight. You can leave at any time.<br />';
      		echo '<form action="strifeabandon.php" method="post"><input type="hidden" name="abandon" value="abandon" /><input type="submit" value="Stop assisting" /></form></br>';
      	} else {
      		echo 'You are aiding in a boss fight and cannot leave until 24 hours has passed since it began.<br />';
      	}
      } else echo '<form action="strifeabandon.php" method="post"><input type="hidden" name="abandon" value="abandon" /><input type="submit" value="Stop assisting" /></form></br>';
      echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a> <a href="portfolio.php">Check combat capabilities</a> <a href="consumables.php">Use a consumable item</a>';
      if (!empty($_SESSION['adjective'])) echo " <a href='aspectpowers.php'>DO THE $_SESSION[adjective] THING</a>";
    } else {
    	$dontechostrife = false;
    	if ($userrow['dungeonstrife'] == 6) {
    		$qresult = mysql_query("SELECT `context` FROM `Consort_Dialogue` WHERE `ID` = $userrow[currentquest]");
	    	$qrow = mysql_fetch_array($qresult);
	    	if (strpos($qrow['context'], "questrescue") !== false) { //player is doing a rescue quest, so check to see if power has been reduced to 0
    			$i = 0;
    			$threatremains = false;
    			while ($i < $max_enemies && !$threatremains) {
    				$enemystr = "enemy" . strval($i) . "name";
    				$powerstr = "enemy" . strval($i) . "power";
    				if (!empty($userrow[$enemystr])) {
    					if ($userrow[$powerstr] > 0) $threatremains = true;
    				}
    				$i++;
    			}
    			if (!$threatremains) { //the day is saved!
    				echo "The threat has been completely neutralized! You should talk to the quest giver and claim your reward.<br />";
    				mysql_query("UPDATE `Players` SET `enemydata` = '' WHERE `Players`.`username` = '$username'");
    				echo "<a href='consortquests.php'>==&gt;</a></br>";
    				$dontechostrife = true;
    			}
	    	}
    	}
    	if (!$dontechostrife) {
      if ($userrow['dreamingstatus'] == "Prospit") {
	echo 'Current "opponents":</br>';
      } else {
	echo 'Current opponents:</br>';
      }
      $i = 1;
      while ($i <= $max_enemies) {
	$enemystr = "enemy" . strval($i) . "name";
	$powerstr = "enemy" . strval($i) . "power";
	$healthstr = "enemy" . strval($i) . "health";
	$maxhealthstr = "enemy" . strval($i) . "maxhealth";
	$descstr = "enemy" . strval($i) . "desc";
	$statustr = "ENEMY" . strval($i) . ":";
	if ($userrow[$enemystr] != "") { //Enemy located.
	  echo "<b>" . $userrow[$enemystr];
	  echo "</b>. Power: ";
	  echo strval($userrow[$powerstr]);
	  echo ". Health Vial: ";
	  $healthvial = floor(($userrow[$healthstr] / $userrow[$maxhealthstr]) * 100); //Computes % of max HP remaining.
	  if ($healthvial == 0) $healthvial = 1;
	  echo strval($healthvial);
	  echo "%</br>";
	  echo $userrow[$descstr] . "</br>";
	  //This section stores the messages that appear when an enemy has a status effect. DATA SECTION: Status messages.
	  if (strpos($userrow['strifestatus'], ($statustr . "TIMESTOP|")) !== False) {
		echo "Frozen in Time: This enemy will not act this round.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "WATERYGEL|")) !== False) {
		echo "Watery Health Gel: This enemy's Health Vial is easier to dislodge with basic attacks</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "POISON")) !== False) { // No "|": poison status has arguments after it
		echo "Poisoned: This enemy's health is being slowly sapped</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "SHRUNK|")) !== False) {
		echo "Shrunk: This enemy is at least twice as adorable as it was.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "UNLUCKY|")) !== False) {
		echo "Unlucky: Bad things keep happening!</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "BLEEDING")) !== False) { // No "|": bleeding status has arguments after it
		echo "Bleeding: This enemy is bleeding out, gradually losing health and power</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "HOPELESS|")) !== False) {
		echo "Hopeless: This enemy does not believe in itself.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "DISORIENTED")) !== False) {
		echo "Disoriented: This enemy is unable to cooperate with allies effectively.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "DISTRACTED|")) !== False) {
		echo "Distracted: This enemy is distracted and will take more damage on your next attack.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "ENRAGED|")) !== False) {
		echo "Enraged: This enemy is not paying sufficient attention to defending itself.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "MELLOW|")) !== False) {
		echo "Mellowed Out: Whoa...this dude's, like...totally peaced out, man. He thinks attacking is, like, totally lame and won't hit as hard.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "KNOCKDOWN|")) !== False) {
		echo "Knocked Over: This enemy will need to spend a turn getting back up.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "GLITCHED|")) !== False) {
		$glitchstr = generateStatusGlitchString();
		echo "Glitched Out: $glitchstr</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "BURNING")) !== False) {
		echo "Burning: This enemy is on fire and is steadily taking damage.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "FROZEN|")) !== False) {
		echo "Frozen: This enemy is frozen solid thanks to a layer of ice, and cannot act until it breaks out.</br>";
	  }
	  if (strpos($userrow['strifestatus'], ($statustr . "LOCKDOWN")) !== False) {
		echo "Locked Down: This enemy is unable to use any of its special abilities for the time being.</br>";
	  }
	}
	$i++;
      }
      echo '<div id="health"></div>';
      echo '<b>Health Vial: ' . strval($healthcurrent) . '%</b>';
      echo '<div id="aspect"></div>';
      echo '<b>Aspect Vial: ' . strval($aspectcurrent) . '%</b></br>';
      //echo "<img src='Images/vials/health" . $healthvialcolour . "/healthvial" . strval($healthcurrent) . $healthvialcolour . ".gif' alt='Health Vial: " . strval($healthcurrent) . "%'>";
      //echo "<img src='Images/vials/aspect" . $aspectvialcolour . "/aspectvial" . strval($aspectcurrent) . $aspectvialcolour . ".gif' alt='Aspect Vial: " . strval($aspectcurrent) . "%'></br>";
      if ($userrow['invulnerability'] > 0) echo "</br>Invulnerability: " . strval($userrow['invulnerability']) . " rounds.";
      if ($userrow['powerboost'] > 0) echo "</br>Power boost (entire battle): $userrow[powerboost]";
      if ($userrow['offenseboost'] > 0) echo "</br>Offense boost (entire battle): $userrow[offenseboost]";
      if ($userrow['defenseboost'] > 0) echo "</br>Defense boost (entire battle): $userrow[defenseboost]";
      if ($userrow['powerboost'] < 0) echo "</br>Power penalty (entire battle): $userrow[powerboost]";
      if ($userrow['offenseboost'] < 0) echo "</br>Offense penalty (entire battle): $userrow[offenseboost]";
      if ($userrow['defenseboost'] < 0) echo "</br>Defense penalty (entire battle): $userrow[defenseboost]";
      if ($userrow['motifcounter'] > 0 && $userrow['Aspect'] == "Time") { //Eternal boosts
	if ($userrow['temppowerboost'] > 0) echo "</br>Power boost of $userrow[temppowerboost]: INFINITY rounds.";
	if ($userrow['tempoffenseboost'] > 0) echo "</br>Offense boost of $userrow[tempoffenseboost]: INFINITY rounds.";
	if ($userrow['tempdefenseboost'] > 0) echo "</br>Defense boost of $userrow[tempdefenseboost]: INFINITY rounds.";
	if ($userrow['temppowerboost'] < 0) echo "</br>Power penalty of $userrow[temppowerboost]: INFINITY rounds.";
	if ($userrow['tempoffenseboost'] < 0) echo "</br>Offense penalty of $userrow[tempoffenseboost]: INFINITY rounds.";
	if ($userrow['tempdefenseboost'] < 0) echo "</br>Defense penalty of $userrow[tempdefenseboost]: INFINITY rounds.";
      } else {
	if ($userrow['temppowerboost'] > 0) echo "</br>Power boost of $userrow[temppowerboost]: $userrow[temppowerduration] rounds.";
	if ($userrow['tempoffenseboost'] > 0) echo "</br>Offense boost of $userrow[tempoffenseboost]: $userrow[tempoffenseduration] rounds.";
	if ($userrow['tempdefenseboost'] > 0) echo "</br>Defense boost of $userrow[tempdefenseboost]: $userrow[tempdefenseduration] rounds.";
	if ($userrow['temppowerboost'] < 0) echo "</br>Power penalty of $userrow[temppowerboost]: $userrow[temppowerduration] rounds.";
	if ($userrow['tempoffenseboost'] < 0) echo "</br>Offense penalty of $userrow[tempoffenseboost]: $userrow[tempoffenseduration] rounds.";
	if ($userrow['tempdefenseboost'] < 0) echo "</br>Defense penalty of $userrow[tempdefenseboost]: $userrow[tempdefenseduration] rounds.";
      }
	//This is where we print messages for the player's more abnormal status effects.
	if (strpos($userrow['strifestatus'], "PLAYER:NOCAP|") !== False) {
		echo "No damage cap: The damage you take from a single enemy is not currently capped.</br>";
	}
	if (strpos($userrow['strifestatus'], "PLAYER:POISON") !== False) {
		echo "Poisoned: You are taking damage over time.</br>";
	}
	if (strpos($userrow['strifestatus'], "PLAYER:CONFUSE") !== False) {
		echo "Confused: You may occasionally accidentally hurt yourself.</br>";
	}
	if (strpos($userrow['strifestatus'], "PLAYER:BLIND") !== False) {
		echo "Blinded: You have a 50% chance of missing an enemy this turn.</br>";
	}
	if (strpos($userrow['strifestatus'], "PLAYER:BONUSCONSUME") !== False) {
		echo "Bonus action: You have at least one bonus consumable action!</br>";
	}
	if (strpos($userrow['strifestatus'], "HASABILITY") !== False) { //NOTE - The convention of prefixing with PLAYER: has been removed as of this point.
		//Status effects with no prefix will be aassumed to belong to the player. PLAYER: prefix is still usable, however, if you wish.
		echo "Temporary ability: You possess one or more abilities for this combat only. Check the roletech page for details.</br>";
	}
      if ($userrow['equipped'] != "") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['equipped']]) {
	    $mainrow = $row; //We save this to check weapon-specific bonuses to various commands.
	  }
	}
      }
      if ($userrow['offhand'] != "" && $userrow['offhand'] != "2HAND") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['offhand']]) {
	    $offrow = $row;
	  }
	}
      }
      if ($userrow['headgear'] != "") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['headgear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['headgear']]) {
	  	if ($row['hybrid'] == 1) $row = convertHybrid($row, false);
	    $headrow = $row; //We save this to check weapon-specific bonuses to various commands.
	  }
	}
      }
      if ($userrow['facegear'] != "" && $userrow['facegear'] != "2HAND") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['facegear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['facegear']]) {
	  	if ($row['hybrid'] == 1) $row = convertHybrid($row, false);
	    $facerow = $row;
	  }
	}
      }
      if ($userrow['bodygear'] != "") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['bodygear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['bodygear']]) {
	  	if ($row['hybrid'] == 1) $row = convertHybrid($row, true);
	    $bodyrow = $row; //We save this to check weapon-specific bonuses to various commands.
	  }
	}
      }
      if ($userrow['accessory'] != "") {
	$itemname = str_replace("'", "\\\\''", $userrow[$userrow['accessory']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	while ($row = mysql_fetch_array($itemresult)) {
	  $itemname = $row['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemname == $userrow[$userrow['accessory']]) {
	  	if ($row['hybrid'] == 1) $row = convertHybrid($row, false);
	    $accrow = $row; //We save this to check weapon-specific bonuses to various commands.
	  }
	}
      }
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = 'Perfectly Generic Object'");
      $blankrow = mysql_fetch_array($itemresult);
      if (empty($mainrow) || $userrow['dreamingstatus'] != "Awake") $mainrow = $blankrow;
      if (empty($offrow) || $userrow['dreamingstatus'] != "Awake") $offrow = $blankrow;
      if (empty($headrow) || $userrow['dreamingstatus'] != "Awake") $headrow = $blankrow;
      if (empty($facerow) || $userrow['dreamingstatus'] != "Awake") $facerow = $blankrow;
      if (empty($bodyrow) || $userrow['dreamingstatus'] != "Awake") $bodyrow = $blankrow;
      if (empty($accrow) || $userrow['dreamingstatus'] != "Awake") $accrow = $blankrow;
      $aggrieve = $mainrow['aggrieve'] + ($offrow['aggrieve']/2) + $headrow['aggrieve'] + $facerow['aggrieve'] + $bodyrow['aggrieve'] + $accrow['aggrieve'];
      if ($aggrieve >= 0) $aggrieve = "+" . strval($aggrieve);
      $aggress = $mainrow['aggress'] + ($offrow['aggress']/2) + $headrow['aggress'] + $facerow['aggress'] + $bodyrow['aggress'] + $accrow['aggress'];
      if ($aggress >= 0) $aggress = "+" . strval($aggress);
      $assail = $mainrow['assail'] + ($offrow['assail']/2) + $headrow['assail'] + $facerow['assail'] + $bodyrow['assail'] + $accrow['assail'];
      if ($assail >= 0) $assail = "+" . strval($assail);
      $assault = $mainrow['assault'] + ($offrow['assault']/2) + $headrow['assault'] + $facerow['assault'] + $bodyrow['assault'] + $accrow['assault'];
      if ($assault >= 0) $assault = "+" . strval($assault);
      $abuse = $mainrow['abuse'] + ($offrow['abuse']/2) + $headrow['abuse'] + $facerow['abuse'] + $bodyrow['abuse'] + $accrow['abuse'];
      if ($abuse >= 0) $abuse = "+" . strval($abuse);
      $accuse = $mainrow['accuse'] + ($offrow['accuse']/2) + $headrow['accuse'] + $facerow['accuse'] + $bodyrow['accuse'] + $accrow['accuse'];
      if ($accuse >= 0) $accuse = "+" . strval($accuse);
      $abjure = $mainrow['abjure'] + ($offrow['abjure']/2) + $headrow['abjure'] + $facerow['abjure'] + $bodyrow['abjure'] + $accrow['abjure'];
      if ($abjure >= 0) $abjure = "+" . strval($abjure);
      $abstain = $mainrow['abstain'] + ($offrow['abstain']/2) + $headrow['abstain'] + $facerow['abstain'] + $bodyrow['abstain'] + $accrow['abstain'];
      if ($abstain >= 0) $abstain = "+" . strval($abstain);
      echo "</br>";
      if ($userrow['dreamingstatus'] == "Prospit") {
	echo '<form action="striferesolve.php" method="post" style="display: inline;">Select an aggressive action: <select name="offense">';
	if ($userrow['lastactive'] == "aggrieve") echo '<option value="aggrieve" selected>"AGGRIEVE" (' . $aggrieve . ')</option>';
	else echo '<option value="aggrieve">"AGGRIEVE" (' . $aggrieve . ')</option>';
	if ($userrow['lastactive'] == "aggress") echo '<option value="aggress" selected>"AGGRESS" (' . $aggress . ')</option>';
	else echo '<option value="aggress">"AGGRESS" (' . $aggress . ')</option>';
	if ($userrow['lastactive'] == "assail") echo '<option value="assail" selected>"ASSAIL" (' . $assail . ')</option>';
	else echo '<option value="assail">"ASSAIL" (' . $assail . ')</option>';
	if ($userrow['lastactive'] == "assault") echo '<option value="assault" selected>"ASSAULT" (' . $assault . ')</option>';
	else echo '<option value="assault">"ASSAULT" (' . $assault . ')</option>';
	echo '</select></br>';
	echo 'Select a passive action: <select name="defense">';
	if ($userrow['lastpassive'] == "abuse") echo '<option value="abuse" selected>"ABUSE" (' . $abuse . ')</option>'; 
	else echo '<option value="abuse">"ABUSE" (' . $abuse . ')</option>'; 
	if ($userrow['lastpassive'] == "accuse") echo '<option value="accuse" selected>"ACCUSE" (' . $accuse . ')</option>';
	else echo '<option value="accuse">"ACCUSE" (' . $accuse . ')</option>';
	if ($userrow['lastpassive'] == "abjure") echo '<option value="abjure" selected>"ABJURE" (' . $abjure . ')</option>'; 
	else echo '<option value="abjure">"ABJURE" (' . $abjure . ')</option>'; 
	if ($userrow['lastpassive'] == "abstain") echo '<option value="abstain" selected>"ABSTAIN" (' . $abstain . ')</option>';
	else echo '<option value="abstain">"ABSTAIN" (' . $abstain . ')</option>';
	echo '</select></br>';
	echo 'Enemy on which to focus: <select name="focusenemy">';
	$i = 1;
	$enstr = "enemy" . strval($i) . "name";
	while (!empty($userrow[$enstr])) {
	  echo '<option value="' . strval($i) . '">' . $userrow[$enstr] . '</option>';
	  $i++;
	  $enstr = "enemy" . strval($i) . "name";
	}
	echo '</select></br>';
	echo '<input type="hidden" name="redirect" value="redirect">';
	//echo '<input type="checkbox" name="repeat" value="repeat">AUTO-STRIFE! (Keep performing this action until you or an enemy dies, a turn passes with no damage, or 20 turns pass.)<br>';
	//DO NOT RE-ENABLE THE ABOVE. It fucks everything up. I'll test it personally some time later.
	echo '<input type="submit" value="Attack" /></form>';
      } else {
	echo '<form action="striferesolve.php" method="post" style="display: inline;">Select an aggressive action: <select name="offense">';
	if ($userrow['lastactive'] == "aggrieve") echo '<option value="aggrieve" selected>AGGRIEVE (' . $aggrieve . ')</option>';
	else echo '<option value="aggrieve">AGGRIEVE (' . $aggrieve . ')</option>';
	if ($userrow['lastactive'] == "aggress") echo '<option value="aggress" selected>AGGRESS (' . $aggress . ')</option>';
	else echo '<option value="aggress">AGGRESS (' . $aggress . ')</option>';
	if ($userrow['lastactive'] == "assail") echo '<option value="assail" selected>ASSAIL (' . $assail . ')</option>';
	else echo '<option value="assail">ASSAIL (' . $assail . ')</option>';
	if ($userrow['lastactive'] == "assault") echo '<option value="assault" selected>ASSAULT (' . $assault . ')</option>';
	else echo '<option value="assault">ASSAULT (' . $assault . ')</option>';
	echo '</select></br>';
	echo 'Select a passive action: <select name="defense">';
	if ($userrow['lastpassive'] == "abuse") echo '<option value="abuse" selected>ABUSE (' . $abuse . ')</option>'; 
	else echo '<option value="abuse">ABUSE (' . $abuse . ')</option>'; 
	if ($userrow['lastpassive'] == "accuse") echo '<option value="accuse" selected>ACCUSE (' . $accuse . ')</option>';
	else echo '<option value="accuse">ACCUSE (' . $accuse . ')</option>';
	if ($userrow['lastpassive'] == "abjure") echo '<option value="abjure" selected>ABJURE (' . $abjure . ')</option>'; 
	else echo '<option value="abjure">ABJURE (' . $abjure . ')</option>'; 
	if ($userrow['lastpassive'] == "abstain") echo '<option value="abstain" selected>ABSTAIN (' . $abstain . ')</option>';
	else echo '<option value="abstain">ABSTAIN (' . $abstain . ')</option>';
	echo '</select></br>';
	echo 'Enemy on which to focus: <select name="focusenemy">';
	$i = 1;
	$enstr = "enemy" . strval($i) . "name";
	while (!empty($userrow[$enstr])) {
	  echo '<option value="' . strval($i) . '">' . $userrow[$enstr] . '</option>';
	  $i++;
	  $enstr = "enemy" . strval($i) . "name";
	}
	echo '</select></br>';
	echo '<input type="hidden" name="redirect" value="redirect">';
	//echo '<input type="checkbox" name="repeat" value="repeat">AUTO-STRIFE! (Keep performing this action until you or an enemy dies, a turn passes with no damage, or 20 turns pass.)<br>';
	//DO NOT RE-ENABLE THE ABOVE. It fucks everything up. I'll test it personally some time later.
	echo '<input type="submit" value="Attack" /></form>';
      } //we won't even need the following now that auto-select is a thing!
      /*if ($userrow['lastactive'] != "" && $userrow['lastpassive'] != "") {
	echo '<form action="striferesolve.php" method="post">';
	echo '<input type="hidden" name="offense" value="' . $userrow['lastactive'] . '">';
	echo '<input type="hidden" name="defense" value="' . $userrow['lastpassive'] . '">';
	echo '<input type="hidden" name="redirect" value="redirect">';
	if ($userrow['dreamingstatus'] == "Prospit") {
	  echo '<input type="submit" value="Use commands from last round (&quot;' . $userrow['lastactive'] . '&quot; + &quot;' . $userrow['lastpassive'] . '&quot;)" /></form>';
	} else {
	  echo '<input type="submit" value="Use commands from last round (' . $userrow['lastactive'] . ' + ' . $userrow['lastpassive'] . ')" /></form>';
	}
      }*/
      echo '<form action="striferesolve.php" method="post" style="display: inline;"><input type="hidden" id="abscond" name="abscond" value="abscond"><input type="submit" value="Abscond"></form>';
      echo '</br>';
      if (!empty($userrow['allies'])) { //here, we'll explode the currentstatus to see if we have any NPC allies
    		$thisstatus = explode("|", $userrow['allies']);
    		$st = 0;
    		$npcechoed = false;
    		while (!empty($thisstatus[$st])) {
    			$statusarg = explode(":", $thisstatus[$st]);
    			if ($statusarg[0] == "PARTY") { //this is an ally's stats, and we only care about allies here
    				//format: ALLY:<basename>:<loyalty>:<nickname>:<desc>:<power>| with the last 3 args being optional
    				$npcresult = mysql_query("SELECT * FROM `Enemy_Types` WHERE `Enemy_Types`.`basename` = '$statusarg[1]'");
    				$npcrow = mysql_fetch_array($npcresult);
    				if (!empty($statusarg[5])) $npcpower = $statusarg[5];
    				else $npcpower = $npcrow['basepower'];
    				if (!empty($statusarg[3])) $npcname = $statusarg[3];
	    			else $npcname = $npcrow['basename'];
	    			if (!empty($statusarg[4])) $npcdesc = $statusarg[4];
	    			else $npcdesc = $npcrow['description'];
	    			$npcloyalty = $statusarg[2];
    				$aidpower += $npcpower;
    				if ($npcechoed == false) {
    					echo "NPC allies currently aiding you:<br />";
    					$npcechoed = true;
    				}
    				echo "<b>$npcname</b>. Power: $npcpower. Loyalty: $npcloyalty.<br />$npcdesc<br />";
    			}
    			$st++;
    		}
    	}
    	if ($npcechoed) echo "<br />";
      $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '$username'");
      while ($row = mysql_fetch_array($sessionmates)) {
	if ($row['aiding'] == $username) { //Aiding character.
	  echo "$row[username] is assisting you!</br>";
	}
      }
      echo '<a href="/">Home</a> | <a href="portfolio.php">Check combat capabilities</a> | <a href="consumables.php">Use a consumable item</a> | ';
      echo '<a href="fraymotifs.php">Use a Fraymotif</a>';
      if (!empty($_SESSION['adjective'])) echo " | <a href='aspectpowers.php'>DO THE $_SESSION[adjective] THING</a> | <a href='roletech.php'>Peruse and select roletechs</a></br>";
      if (!empty($message)) { //Message just generated!
	echo "Last round:</br>";
	echo $message;
      } elseif (!empty($userrow['strifemessage'])) {
	echo "Last round:</br>";
	echo $userrow['strifemessage'];
      }
    }
    }
  }
}
require_once("footer.php");
?>