<?php
function highestGate($gaterow, $grist) {
	$gatecount = 0;
	while ($gatecount < 7) { //going off the assumption that there will never be more than 7 gates
		if ($grist < $gaterow['gate' . strval($gatecount + 1)]) return $gatecount;
		$gatecount++;
	}
	return 7;
}

function canFly($checkrow) {
	if ($checkrow['Godtier'] > 0) return true; //player is godtier and can fly no matter what
	$invcheck = 1;
	$inv_slots = 50;
	while ($invcheck <= $inv_slots) {
		$invstring = 'inv' . strval($invcheck);
		if (!empty($checkrow[$invstring])) {
			$chname = str_replace("'", "\\\\''", $checkrow[$invstring]);
			$chresult = mysql_query("SELECT `name`,`abstratus` FROM Captchalogue WHERE `Captchalogue`.`name` = '$chname' LIMIT 1;");
			$chrow = mysql_fetch_array($chresult);
			if (strrpos($chrow['abstratus'], "flying")) return true;
		}
		$invcheck++;
	}
	return false;
}

function chainArray($startrow) {
  require_once("includes/SQLconnect.php");
  $gateresult = mysql_query("SELECT * FROM Gates"); //begin new chain-following code, shamelessly copypasted and trimmed down from Dungeons
  $gaterow = mysql_fetch_array($gateresult); //Gates only has one row.
  $gaterow['gate0'] = 0;
  $fly = canFly($startrow); //flying items disregard just about all gate-checking limitations
  $currentrow = $startrow;
  $currentrow['highgate'] = highestGate($gaterow, $currentrow['house_build_grist']);
  $minus3row = $currentrow;
  $minus2row = $currentrow;
  $minus1row = $currentrow;
  $done = False;
  $step = 1;
  $chain[0] = $currentrow['username'];
  $count = 1;
  $subgates = array();
  $subgate = array();
  $subgatecount = 0;
  $countb = 1;
  $canusesubgate = false;
  $sgates = mysql_query("SELECT `username` FROM Players WHERE session_name = '" . $userrow['session_name'] . "' AND storeditems LIKE '%SUBGATE.%'");
  while ($sgaterow = mysql_fetch_array($sgates)) {
  	$subgates[$sgaterow['username']] = 1; //user has a subgate
  	if ($sgaterow['username'] == $startrow['username']) {
  		$subgates[$sgaterow['username']] = 2;
  		$canusesubgate = true;
  	}
  	$subgatecount++;
  	$subgate[$subgatecount] = $sgaterow['username'];
  }
  while (!$done) {
  	if (!empty($currentrow['server_player']) && $currentrow['server_player'] != $startrow['username']) {
  		$minus3row = $minus2row;
  		$minus2row = $minus1row;
  		$minus1row = $currentrow;
    	$currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '$currentrow[server_player]';");
    	$currentrow = mysql_fetch_array($currentresult);
    	$currentrow['highgate'] = highestGate($gaterow, $currentrow['house_build_grist']);
    	if ($step == 1 && $minus1row['highgate'] >= 1) $clientcan = true;
    	if ($step == 2 && $minus2row['highgate'] >= 3) $clientcan = true;
    	if ($step == 3 && $minus3row['highgate'] >= 5) $clientcan = true;
      if (($currentrow['highgate'] < ($step * 2) || !$clientcan) && !$fly) {
      	$step++; //check for the next highest gate pair
      	if ($step > 3) $done = True; //This house is unreachable. Chain is broken here.
      } else {
      	if ($subgates[$currentrow['username']] == 1) {
      		$subgates[$currentrow['username']] = 2; //subgate can be reached via regular gates
      		$canusesubgate = true;
      	}
      	$step = 1; //this land is reachable, so the chain can continue from there!
      	$chain[$count] = $currentrow['username'];
      	$count++;
      	if ($count > 100) $done = true; //give up there will never be that many players
      }
  	} else { 
    	$done = True; //No further steps.
	 	}
	 	if ($done == true && $canusesubgate) {
	 		while ($countb <= $subgatecount) {
	 			if ($subgates[$subgate[$countb]] == 1) { //user with a subgate that can't be reached via standard gates
	 				$currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '$subgate[$countb]';");
    			$currentrow = mysql_fetch_array($currentresult);
    			$minus3row = $currentrow;
  				$minus2row = $currentrow;
  				$minus1row = $currentrow;
    			$subgates[$subgate[$countb]] = 2;
    			$countb = $subgatecount + 1; //break from here
    			$done = false; //and go back to the gate calculation to add everything that user can reach as well
	 			}
	 			$countb++;
	 		}
	 	}
  }
  return $chain;
}
?>