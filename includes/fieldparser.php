<?php

function parseEnemydata($userrow) {
	$enemies = explode("|", $userrow['enemydata']);
	//$allenemies = count($enemies);
	//if ($allenemies > 50)
	$allenemies = 50;
	$actualenemies = $allenemies;
	$i = 0;
	while ($i < $allenemies) {
		if (!empty($enemies[$i])) {
			$thisenemy = explode(":", $enemies[$i]);
			$enstr = 'enemy' . strval($i + 1);
			$userrow[$enstr . 'name'] = $thisenemy[0];
			$userrow[$enstr . 'power'] = intval($thisenemy[1]);
			$userrow[$enstr . 'maxpower'] = intval($thisenemy[2]);
			$userrow[$enstr . 'health'] = intval($thisenemy[3]);
			$userrow[$enstr . 'maxhealth'] = intval($thisenemy[4]);
			$thisenemy[5] = str_replace("THIS IS A LINE", "|", $thisenemy[5]);
			$thisenemy[5] = str_replace("THIS IS A COLON", ":", $thisenemy[5]);
			$userrow[$enstr . 'desc'] = $thisenemy[5];
			$userrow[$enstr . 'category'] = $thisenemy[6];
		} else {
			$actualenemies--; //only the last entries should be empty if anything, hopefully this doesn't cause issues?
			$enstr = 'enemy' . strval($i + 1);
			$userrow[$enstr . 'name'] = "";
		}
		$i++;
	}
	//$userrow['maxenemies'] = $actualenemies; //"maxenemies" doesn't exist as a user field, but it's inserted into the userrow so that the function can return it. It's also for some reason causing a bug that prevents more than one enemy from existing, so it's commented out for now.
	return $userrow;
}

function writeEnemydata($userrow) {
	//echo "begin enemy data write<br />";
	$i = 0;
	$endatastr = "";
	if (empty($userrow['maxenemies'])) $userrow['maxenemies'] = 50;
	while ($i < $userrow['maxenemies']) {
		$enstr = 'enemy' . strval($i + 1);
		//echo $enstr . ":";
		if (!empty($userrow[$enstr . 'name'])) { //name will be blanked when enemy is defeated, so we'll blank all of its stats. All other enemies will shift down a slot.
			$endatastr .= $userrow[$enstr . 'name'] . ":";
			$endatastr .= strval($userrow[$enstr . 'power']) . ":";
			$endatastr .= strval($userrow[$enstr . 'maxpower']) . ":";
			$endatastr .= strval($userrow[$enstr . 'health']) . ":";
			$endatastr .= strval($userrow[$enstr . 'maxhealth']) . ":";
			$userrow[$enstr . 'desc'] = str_replace("|", "THIS IS A LINE", $userrow[$enstr . 'desc']);
			$userrow[$enstr . 'desc'] = str_replace(":", "THIS IS A COLON", $userrow[$enstr . 'desc']);
			$endatastr .= $userrow[$enstr . 'desc'] . ":";
			$endatastr .= $userrow[$enstr . 'category'];
			$endatastr .= "|";
			//echo $endatastr . "<br />";
		}
		$i++;
	}
	$endatastr = mysql_real_escape_string($endatastr); //yeeeeah
	//echo "final countdown: " . "UPDATE `Players` SET `enemydata` = '$endatastr' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;";
	mysql_query("UPDATE `Players` SET `enemydata` = '$endatastr' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
}

function refreshEnemydata($userrow) { //a necessary function for functions like generateEnemy, so that they don't continually overwrite the same slot
	$dataresult = mysql_query("SELECT `enemydata` FROM `Players` WHERE `Players`.`username` = '" . $userrow['username'] . "'");
	$row = mysql_fetch_array($dataresult);
	$userrow['enemydata'] = $row['enemydata'];
	$userrow = parseEnemydata($userrow);
	return $userrow;
}

function endStrife($userrow) { //a quick function to reset all strife values and ensure they don't return via megaquery
	mysql_query("UPDATE `Players` SET `powerboost` = 0, `offenseboost` = 0, `defenseboost` = 0, `temppowerboost` = 0, 
 `tempoffenseboost` = 0, `tempdefenseboost` = 0, `Brief_Luck` = 0, `invulnerability` = 0, `buffstrip` = 0, `noassist` = 0, 
`cantabscond` = 0, `motifcounter` = 0, `strifestatus` = '', `sessionbossengaged` = 1 WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;"); //Power boosts wear off.
  $userrow['powerboost'] = 0;
  $userrow['offenseboost'] = 0;
  $userrow['defenseboost'] = 0;
  $userrow['temppowerboost'] = 0;
  $userrow['tempoffenseboost'] = 0;
  $userrow['tempdefenseboost'] = 0;
  $userrow['Brief_Luck'] = 0;
  $userrow['invulnerability'] = 0;
  $userrow['buffstrip'] = 0;
  $userrow['noassist'] = 0;
  $userrow['cantabscond'] = 0;
  $userrow['motifcounter'] = 0;
  $userrow['strifestatus'] = "";
  $userrow['sessionbossengaged'] = 0; //Just in case.
  return $userrow;
}

function freeSpecibi($userabs, $userslots, $echothem) {
	$abs = explode("|", $userabs);
	$i = 0;
	$hasabs = count($abs);
	$free = $userslots;
	while ($i < $hasabs) {
		if (!empty($abs[$i])) {
			$free--;
			if ($echothem) echo $abs[$i] . "<br />";
		}
		$i++;
	}
	return $free;
}

function addSpecibus($userrow, $newabs) { //this function assumes you've already checked if the user has a free slot because reasons
	$abs = $userrow['abstratus1'];
	if (substr($abs,0,-1) != "|" && !empty($abs)) {
		$abs .= "|";
	}
	$abs .= $newabs;
	mysql_query("UPDATE `Players` SET `abstratus1` = '$abs' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
	$userrow['abstratus1'] = $abs;
	return $userrow;
}

function matchesAbstratus($userabs, $abstr) {
	$itemabs = explode(", ", $abstr);
	$abs = explode("|", $userabs);
	$totalitem = count($itemabs);
	$totaluser = count($abs);
	$i = 0;
	$j = 0;
	while ($i < $totalitem) {
		$j = 0;
		while ($j < $totaluser) {
			if ($itemabs[$i] == $abs[$j]) return true; //found a matching abstratus, we're done here
			else $j++;
		}
		$i++;
	}
	return false;
}

function parseLastfought($userrow) {
	$enemies = explode("|", $userrow['oldenemydata']);
	$allenemies = count($enemies);
	$actualenemies = $allenemies;
	$i = 0;
	while ($i < $allenemies) {
		if (!empty($enemies[$i])) {
			$thisenemy = explode(":", $enemies[$i]);
			$enstr = strval($i + 1);
			$userrow['oldenemy' . $enstr] = $thisenemy[0];
			$userrow['oldgrist' . $enstr] = $thisenemy[1];
			$userrow['olddreamenemy' . $enstr] = $thisenemy[2];
		} else {
			$actualenemies--; //only the last entry should be empty if anything, hopefully this doesn't cause issues?
		}
		$i++;
	}
	$userrow['lastenemies'] = $actualenemies; //"lastenemies" doesn't exist as a user field, but it's inserted into the userrow so that the function can return it
	return $userrow;
}

function writeLastfought($userrow) {
	//echo "writing last fought!<br />";
	if (empty($userrow['lastenemies'])) $userrow['lastenemies'] = 50;
	$i = 0;
	$endatastr = "";
	while ($i < $userrow['lastenemies']) {
		$enstr = strval($i + 1);
		//echo "checking slot $enstr<br />";
		if (!empty($userrow['oldenemy' . $enstr]) || !empty($userrow['olddreamenemy' . $enstr])) { //name will be blanked when enemy is defeated, so we'll blank all of its stats
			//echo "data found: ";
			$endatastr .= $userrow['oldenemy' . $enstr] . ":";
			$endatastr .= $userrow['oldgrist' . $enstr] . ":";
			$endatastr .= $userrow['olddreamenemy' . $enstr];
			$endatastr .= "|";
			//echo $endatastr . "<br />";
		}
		$i++;
	}
	//echo "final countdown: $endatastr<br />";
	mysql_query("UPDATE `Players` SET `oldenemydata` = '$endatastr' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
}

function hydraSplitChance($abs) {
  switch ($abs) {
    case "bladekind":
    case "chainsawkind":
      return 95; 
      break;
    case "axekind":
    case "knifekind":
    case "scythekind":
      return 80; 
      break;
    case "polearmkind":
    case "ninjakind":
    case "razorkind":
      return 60; 
      break;
    case "boomerangkind":
    case "laserkind":
    case "sicklekind":
      return 50; 
      break;
    case "scissorkind":
    case "shearkind":
      return 40;
      break;
    case "hammerkind":
    case "clubkind":
    case "flamethrowerkind":
    case "pankind":
    case "rockkind":
    case "staffkind":
    case "yoyokind":
      return 10; 
      break;
    case "bunnykind":
    case "cakekind":
    case "fabrickind":
    case "fancysantakind":
    case "inflatablekind":
      return 1;
      break;
    case "metakind":
    case "pillowkind":
      return 0;
      break;
    default:
      return 25;
      break;
  }
}

?>