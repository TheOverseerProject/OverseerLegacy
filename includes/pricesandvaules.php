<?php

function randomItem($griststring, $costcap, $gristname, $totalgrists, $specialstring) {
	$totalpool = 0;
	if (!empty($specialstring)) {
		$specialstring = "(" . $specialstring . ")";
		//echo "SELECT `name` FROM `Captchalogue` WHERE `Captchalogue`.`$griststring` > 0 $specialstring";
	} else {
		$specialstring = "`Captchalogue`.`$griststring` > 0";
	}
	$poolresult = mysql_query("SELECT `name` FROM `Captchalogue` WHERE $specialstring AND `Captchalogue`.`effects` NOT LIKE '%NOCONSORT|%'");
	while (mysql_fetch_array($poolresult)) $totalpool++;
	$foundone = false;
	$attempts = 0;
	$attemptscap = 100;
	while (!$foundone && $attempts < $attemptscap) {
		$pick = rand(1,$totalpool);
		$pick--;
		$pickresult = mysql_query("SELECT * FROM `Captchalogue` WHERE $specialstring AND `Captchalogue`.`effects` NOT LIKE '%NOCONSORT|%' LIMIT $pick,1");
		$pickrow = mysql_fetch_array($pickresult);
		//echo $pickrow['name'] . " found</br>";
		$thiscost = totalGristcost($pickrow, $gristname, $totalgrists);
		if ($thiscost <= $costcap) $foundone = true;
		$attempts++;
	}
	if ($foundone) return $pickrow;
	else return false;
}

function totalGristcost($countrow, $gristname, $totalgrists) {
	$i = 0;
	$totalcost = 0;
	while ($i < $totalgrists) {
		//echo $gristname[$i] . " - " . strval($countrow[$gristname[$i] . '_Cost']) . "</br>";
		$totalcost = $totalcost + $countrow[$gristname[$i] . '_Cost'];
		$i++;
	}
	return $totalcost;
}

function totalBooncost($countrow, $landrow, $gristname, $totalgrists, $sessionname) {
	$i = 0;
	$totalcost = 0;
	while ($i < $totalgrists) {
		if ($countrow[$gristname[$i] . "_Cost"] != 0) {
			$gristvalue = 0;
			if ($gristname[$i] == "Build_Grist") $gristvalue = 20;
			elseif ($gristname[$i] == "Artifact_Grist") $gristvalue = 0;
			else {
				$j = 0;
				$match = false;
				while ($j < 9 && !$match) {
					$j++;
					if ($landrow['grist' . strval($j)] == $gristname[$i]) $match = true;
				}
				if ($match) $gristvalue = 20 * ($j + 1);
				else $gristvalue = avgGristtier($sessionname, $gristname[$i]); //grist can't be found on this land, so it's worth a ton to the consorts
			}
			//echo "gristvalue of " . $gristname[$i] . " is " . strval($gristvalue) . "</br>";
			$totalcost = $totalcost + ($countrow[$gristname[$i] . '_Cost'] * $gristvalue);
		}
		$i++;
	}
	return $totalcost;
}

function getDialogue($dtype, $userrow, $land1, $land2, $gate = 1) {
	$totalpool = 0;
	if ($gate < 1) $gate = 1;
	$poolresult = mysql_query("SELECT `ID` FROM `Consort_Dialogue` WHERE `Consort_Dialogue`.`context` = '$dtype' AND `gate` <= $gate");
	while (mysql_fetch_array($poolresult)) $totalpool++;
	$pick = rand(1,$totalpool);
	$pick--;
	$pickresult = mysql_query("SELECT * FROM `Consort_Dialogue` WHERE `Consort_Dialogue`.`context` = '$dtype' AND `gate` <= $gate LIMIT $pick,1");
	$pickrow = mysql_fetch_array($pickresult);
	if (!empty($pickrow['dialogue']))
	$pickrow = parseDialogue($pickrow, $userrow, $land1, $land2);
	else $pickrow['dialogue'] = "I AM ERROR.";
	return $pickrow;
}

function parseDialogue($pickrow, $userrow, $land1, $land2) {
	$pickrow['dialogue'] = str_replace("[user]", $userrow['username'], $pickrow['dialogue']);
	if (empty($userrow['Class'])) $pickrow['dialogue'] = str_replace("[class]", "whoever", $pickrow['dialogue']);
	else $pickrow['dialogue'] = str_replace("[class]", $userrow['Class'], $pickrow['dialogue']);
	if (empty($userrow['Aspect'])) $pickrow['dialogue'] = str_replace("[aspect]", "whatever", $pickrow['dialogue']);
	else $pickrow['dialogue'] = str_replace("[aspect]", $userrow['Aspect'], $pickrow['dialogue']);
	$pickrow['dialogue'] = str_replace("[landfull]", "The Land of $land1 and $land2", $pickrow['dialogue']);
	if (strrpos($pickrow['dialogue'], "[landshort]") !== false) {
		$landshort = abbreviateLand($land1, $land2);
		$pickrow['dialogue'] = str_replace("[landshort]", $landshort, $pickrow['dialogue']);
	}
	$wedone = false;
	$btagcount = substr_count($pickrow['dialogue'], "[");
	$tagcount = 0;
	while (!$wedone) {
		$startloc = strpos($pickrow['dialogue'], "[");
		$endloc = strpos($pickrow['dialogue'], "]");
		if ($startloc !== false && $endloc !== false && $endloc > $startloc) {
			$ftag = substr($pickrow['dialogue'], $startloc, $endloc - $startloc + 1);
			$tag = substr($pickrow['dialogue'], $startloc + 1, $endloc - $startloc - 1);
			if ($ftag != $userrow[$tag]) //potential infinite loop!
			$pickrow['dialogue'] = str_replace($ftag, $userrow[$tag], $pickrow['dialogue']);
			else
			$pickrow['dialogue'] = str_replace($ftag, $tag, $pickrow['dialogue']); //basically returns the tag without the brackets so the parser can continue
			$tagcount++;
			if ($tagcount > 100) $wedone = true; //there probably won't ever be more than 100 tags in a dialogue, but this is just in case something hacky happens
		} else $wedone = true;
	}
	if ($btagcount != $tagcount) //there were more replacements than tags, so something is wrong here
	$pickrow['dialogue'] = "Dialogue parse error: Number of replacements did not equal number of tags. Make sure no editable player fields contain square brackets. If you're not sure where the problem is or can't change it, contact a developer ASAP.";
	return $pickrow;
}

function abbreviateLand($land1, $land2) {
	$landshort = "LO";
	$boom = explode(" ", $land1);
	$bcount = 0;
	while ($bcount <= count($boom)) {
		$landshort .= strtoupper(substr($boom[$bcount], 0, 1));
		$bcount++;
	}
	$landshort .= "A";
	$boom = explode(" ", $land2);
	$bcount = 0;
	while ($bcount <= count($boom)) {
		$landshort .= strtoupper(substr($boom[$bcount], 0, 1));
		$bcount++;
	}
	return $landshort;
}

function avgGristtier($sessionname,$gristname) {
	$playersresult = mysql_query("SELECT `grist_type` FROM Players WHERE `Players`.`session_name` = '$sessionname'");
	$totallands = 0;
	$totaltier = 0;
	while ($playerrow = mysql_fetch_array($playersresult)) {
		$gristresult = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '" . $playerrow['grist_type'] . "'");
		$gristrow = mysql_fetch_array($gristresult);
		$i = 1;
		while ($i <= 9) {
			$griststring = "grist" . strval($i);
			if ($gristrow[$griststring] == $gristname) {
				$totallands++;
				$totaltier += $i;
				$i = 10;
			}
			$i++;
		}
	}
	if ($totallands != 0) $thevalue = round((($totaltier / $totallands) + 1) * 25);
	else $thevalue = 300;
	return $thevalue;
}

function econonyLevel($exp) {
	$level = floor(pow($exp / 1000, 1/3));
	return $level;
}

function joinParty($userrow, $hired, $offer, $consort) {
	$mercresult = mysql_query("SELECT * FROM Enemy_Types WHERE basename = '$hired'");
	$mercrow = mysql_fetch_array($mercresult);
	$baseloyalty = $mercrow['basehealth'] / $mercrow['basepower']; //don't round it just yet
	$offerpercent = $offer / $mercrow['maxboons']; //higher offer = higher loyalty and vice versa
	$loyalty = ceil($baseloyalty * $offerpercent);
	if ($loyalty < 1) $loyalty = 1;
	if (!empty($consort)) {
		$mercname = str_replace("Consort", $consort, $mercrow['basename']);
		$mercrow['description'] = str_replace("consort", $consort, $mercrow['description']);
	} else $mercname = $mercrow['basename'];
	$newally = "IDLE:" . $mercrow['basename'] . ":" . strval($loyalty) . ":" . $mercname . ":" . $mercrow['description'] . ":" . strval($mercrow['basepower']);				
	$userrow['allies'] .= mysql_real_escape_string($newally . "|");
	mysql_query("UPDATE Players SET `allies` = '" . $userrow['allies'] . "' WHERE username = '" . $userrow['username'] . "'");
	return $newally;
}

function mercRefresh($userrow) { //essentially holds data on "default" consort types available on a land and when they become available
	$startallies = $userrow['landallies'];
	if (strpos($userrow['landallies'], "Consort") === false) {
		$userrow['landallies'] .= "Consort|";
	}
	if (strpos($userrow['landallies'], "Consort Guard") === false) {
		if (econonyLevel($userrow['econony']) > 10)
		$userrow['landallies'] .= "Consort Guard|";
	}
	if (strpos($userrow['landallies'], "Consort Knight") === false) {
		if (econonyLevel($userrow['econony']) > 20)
		$userrow['landallies'] .= "Consort Knight|";
	}
	if ($startallies != $userrow['landallies'])
	mysql_query("UPDATE Players SET landallies = '" . $userrow['landallies'] . "' WHERE username = '" . $userrow['username'] . "'");
	return $userrow;
}

?>