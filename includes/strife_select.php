<?php
  //free to engage strife. Render the options for engaging in strife
    $up = 0;
	echo '<div style = "margin-left:20px;">';
    if ($userrow['dreamingstatus'] == "Prospit") {
      echo 'You are not currently engaged in "strife".</br></div>';
    } else {
      echo "You are not currently engaged in strife.</br></div>";
    }
    if ($userrow[$downstr] == 1 && $up != True) { //Player is not gaining a new encounter and IS down. Up is a variable in the header.
      if ($userrow['dreamingstatus'] == "Prospit") {
	    echo "You're still exhausted from all that do-gooding! You will recover instead of earning your next encounter and you're far too tired to be helpful yet.</br>";
      } else {
	    echo "You are still reeling from a recent defeat. You will recover instead of earning your next encounter and you cannot get into any more fights yet.</br>";
      }
    }
	if ($userrow['dreamingstatus'] == "Awake") {
	$healthcurrent = strval(floor(($userrow['Health_Vial'] / $userrow['Gel_Viscosity']) * 100));
      } else {
	$healthcurrent = strval(floor(($userrow['Dream_Health_Vial'] / $userrow['Gel_Viscosity']) * 100));
    }
    $aspectcurrent = strval(floor(($userrow['Aspect_Vial'] / $userrow['Gel_Viscosity']) * 100));
	
	echo "<div class = healthvial><img src='Images/vials/health" . $healthvialcolour . "/healthvial" . strval($healthcurrent) . $healthvialcolour . ".gif' alt='Health Vial: " . strval($healthcurrent) . "%'></div>";
	echo "<div class = healthvial><img src='Images/vials/aspect" . $aspectvialcolour . "/aspectvial" . strval($aspectcurrent) . $aspectvialcolour . ".gif' alt='Aspect Vial: " . strval($aspectcurrent) . "%'></div>";
	echo "<br/><br/><br/><br/>";
    
    if ($encounters > 0 && ($up == 1 || ($userrow['down'] != 1 && $userrow['dreamingstatus'] == "Awake") || ($userrow['dreamdown'] != 1 && $userrow['dreamingstatus'] != "Awake"))) { //Not down.
      if ($userrow['dreamingstatus'] == "Awake") { //Only do Land combat while awake.
	    echo '<div class = landselect_wrapper><form action="strifeselect.php" method="post">
		<input type="submit" class = landselectbutton value="Fight on this Land" /> 
		Select a Land to fight on:<br/><select name="land"> '; //Only select the Land for combat at this stage.
        $gateresult = mysql_query("SELECT * FROM Gates"); //begin new chain-following code, shamelessly copypasted and trimmed down from Dungeons
        $gaterow = mysql_fetch_array($gateresult); //Gates only has one row.
        $currentrow = $userrow;
        $done = False;
        while (!$done) {
	      $locationstr = "Land of " . $currentrow['land1'] . " and " . $currentrow['land2'];
	      echo '<option value="' . $currentrow['username'] . '">' . $locationstr . '</option>';
	      if (!empty($currentrow['server_player']) && $currentrow['server_player'] != $username) {
	      $currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '$currentrow[server_player]';");
	      $currentrow = mysql_fetch_array($currentresult);
	      if ($currentrow['house_build_grist'] < $gaterow["gate2"]) $done = True; //This house is unreachable. Chain is broken here.
	      } else { //Player has no server, gates go nowhere. This is not canonical behaviour, but canonical behaviour is impossible since it relies on prediction. Alternatively, loop is complete.
	      //Note that if gate 1 has not been reached, then gate 2 wasn't either and the Land was never accessed in the first place!
	        $done = True; //No further steps.
	      }
        }
	    if ($userrow['battlefield_access'] != 0) { //Player has handled their denizen or gone god tier. The battlefield is available as a zone.
	      echo '<option value="Battlefield">The Battlefield</option>';
	    }
		echo '</select></form></div>';
		echo '<div class = landselect_wrapper> <form action="strifebegin.php" method="post"> <input type="submit" class =landselectbutton  value="Fight these enemies again!" /> ';
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
		  echo '</form> </div>';
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
		echo '<input type="submit" value="Fight these enemies again!" /> </form>';
		}
		$sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
		$sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'");
		$aidneeded = False;
		while ($row = mysql_fetch_array($sessionmates)) {
		  if ($row['session_name'] == $userrow['session_name'] && $row['username'] != $userrow['username'] && $row['dreamingstatus'] == $userrow['dreamingstatus']) { //No aiding yourself!
	      //Note that we can only try to aid allies with the same current dreaming status.
			if (!empty($row['enemy1name']) || !empty($row['enemy2name']) || !empty($row['enemy3name']) || !empty($row['enemy4name']) || !empty($row['enemy5name'])) { //Ally is strifing
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
		echo '</select></br><input type="submit" value="Assist this ally" /> </form></div></br>';
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
	echo '<a href="combatbasics.php" style = "margin-right:30px;">The basics of strife</a>';
      echo '<a href="sessionbossvote.php">Vote on whole session boss strifes.</a>';
    } else {
      echo "Your session has engaged $sessionrow[sessionbossname]!</br>";
      echo '<form action="sessionboss.php" method="post"><input type="hidden" id="newfighter" name="newfighter" value="' . $sessionrow['sessionbossname'] . '"><input type="submit" value="Join the fight"></form>';
    }
	
	?>