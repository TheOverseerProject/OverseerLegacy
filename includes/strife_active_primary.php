<?php
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
	if ($userrow[$enemystr] != "") { //Enemy located.
	  echo $userrow[$enemystr];
	  echo ". Power: ";
	  echo strval($userrow[$powerstr]);
	  echo ". Health Vial: ";
	  $healthvial = floor(($userrow[$healthstr] / $userrow[$maxhealthstr]) * 100); //Computes % of max HP remaining.
	  if ($healthvial == 0) $healthvial = 1;
	  echo strval($healthvial);
	  echo "%</br>";
	  echo $userrow[$descstr] . "</br>";
	}
	$i++;
      }
    if ($userrow['dreamingstatus'] == "Awake") {
	  $healthcurrent = strval(floor(($userrow['Health_Vial'] / $userrow['Gel_Viscosity']) * 100));
    } else {
	  $healthcurrent = strval(floor(($userrow['Dream_Health_Vial'] / $userrow['Gel_Viscosity']) * 100));
    }
    $aspectcurrent = strval(floor(($userrow['Aspect_Vial'] / $userrow['Gel_Viscosity']) * 100));
     
      echo "<img style = 'float:left;' src='Images/vials/health" . $healthvialcolour . "/healthvial" . strval($healthcurrent) . $healthvialcolour . ".gif' alt='Health Vial: " . strval($healthcurrent) . "%'>";
      echo "<img style = 'float:left;' src='Images/vials/aspect" . $aspectvialcolour . "/aspectvial" . strval($aspectcurrent) . $aspectvialcolour . ".gif' alt='Aspect Vial: " . strval($aspectcurrent) . "%'></br><br/><br/><br/>";
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
      if ($userrow['dreamingstatus'] == "Prospit") {
	echo '<form action="striferesolve.php" method="post">Select an aggressive action: <select name="offense">';
	echo '<option value="aggrieve">"AGGRIEVE" (' . $aggrieve . ')</option><option value="aggress">"AGGRESS" (' . $aggress . ')</option>';
	echo '<option value="assail">"ASSAIL" (' . $assail . ')</option><option value="assault">"ASSAULT" (' . $assault . ')</option>';
	echo '</select></br>';
	echo 'Select a passive action: <select name="defense">';
	echo '<option value="abuse">"ABUSE" (' . $abuse . ')</option><option value="accuse">"ACCUSE" (' . $accuse . ')</option>';
	echo '<option value="abjure">"ABJURE" (' . $abjure . ')</option><option value="abstain">"ABSTAIN" (' . $abstain . ')</option>';
	echo '</select></br>';
	echo '<input type="submit" value="&quot;Attack&quot;" /></form>';
      } else {
	echo '<form action="striferesolve.php" method="post">Select an aggressive action: <select name="offense">';
	echo '<option value="aggrieve">AGGRIEVE (' . $aggrieve . ')</option><option value="aggress">AGGRESS (' . $aggress . ')</option>';
	echo '<option value="assail">ASSAIL (' . $assail . ')</option><option value="assault">ASSAULT (' . $assault . ')</option>';
	echo '</select></br>';
	echo 'Select a passive action: <select name="defense">';
	echo '<option value="abuse">ABUSE (' . $abuse . ')</option><option value="accuse">ACCUSE (' . $accuse . ')</option>';
	echo '<option value="abjure">ABJURE (' . $abjure . ')</option><option value="abstain">ABSTAIN (' . $abstain . ')</option>';
	echo '</select></br>';
	echo '<input type="hidden" name="redirect" value="redirect">';
	//echo '<input type="checkbox" name="repeat" value="repeat">AUTO-STRIFE! (Keep performing this action until you or an enemy dies, a turn passes with no damage, or 20 turns pass.)<br>';
	//DO NOT RE-ENABLE THE ABOVE. It fucks everything up. I'll test it personally some time later.
	echo '<input type="submit" value="Attack" /></form>';
      }
      if ($userrow['lastactive'] != "" && $userrow['lastpassive'] != "") {
	echo '<form action="striferesolve.php" method="post">';
	echo '<input type="hidden" name="offense" value="' . $userrow['lastactive'] . '">';
	echo '<input type="hidden" name="defense" value="' . $userrow['lastpassive'] . '">';
	echo '<input type="hidden" name="redirect" value="redirect">';
	if ($userrow['dreamingstatus'] == "Prospit") {
	  echo '<input type="submit" value="Use commands from last round (&quot;' . $userrow['lastactive'] . '&quot; + &quot;' . $userrow['lastpassive'] . '&quot;)" /></form>';
	} else {
	  echo '<input type="submit" value="Use commands from last round (' . $userrow['lastactive'] . ' + ' . $userrow['lastpassive'] . ')" /></form>';
	}
      }
      echo '<form action="striferesolve.php" method="post"><input type="hidden" id="abscond" name="abscond" value="abscond"><input type="submit" value="Abscond"></form>';
      echo '</br>';
      $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`aiding` = '$username'");
      while ($row = mysql_fetch_array($sessionmates)) {
	if ($row['aiding'] == $username) { //Aiding character.
	  echo "$row[username] is assisting you!</br>";
	}
      }
      echo '<a href="/">Home</a> | <a href="portfolio.php">Check combat capabilities</a> | <a href="consumables.php">Use a consumable item</a> | ';
      echo '<a href="fraymotifs.php">Use a Fraymotif</a>';
      if (!empty($_SESSION['adjective'])) echo " | <a href='aspectpowers.php'>DO THE $_SESSION[adjective] THING</a> | <a href='roletech.php'>Peruse and select roletechs</a></br>";
      if (!empty($userrow['strifemessage'])) {
	echo "Last round:</br>";
	echo $userrow['strifemessage'];
      }
	  
	  ?>