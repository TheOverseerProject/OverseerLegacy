<?php
require 'monstermaker.php';
require 'additem.php';
require 'includes/chaincheck.php';
//require 'includes/fieldparser.php'; //monstermaker includes fieldparser already
require_once("header.php");
$canusespecibus = True;
function initGrists() {
	$result2 = mysql_query("SELECT * FROM `Captchalogue` LIMIT 1;"); //document grist types now so we don't have to do it later
  $reachgrist = False;
  $terminateloop = False;
  $totalgrists = 0;
  while (($col = mysql_fetch_field($result2)) && $terminateloop == False) {
    $gristcost = $col->name;
    $gristtype = substr($gristcost, 0, -5);
    if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
      $reachgrist = True;
    }
    if ($gristcost == "End_Of_Grists") { //Reached the end of the grists.
      $reachgrist = False;
      $terminateloop = True;
    }
    if ($reachgrist == True) {
      $gristname[$totalgrists] = $gristtype;
      $totalgrists++;
    }
  }
  return $gristname;
}
function roomlink ($roomarray,$newrow,$newcol,$oldrow,$oldcol) {
  $newentry = strval($newrow) . "," . strval($newcol);
  $oldentry = strval($oldrow) . "," . strval($oldcol);
  if (empty($roomarray[$newentry])) { //Create this entry.
    $roomarray[$newentry] = "LINK:" . $oldentry;
  } else {
    $linkstr = "LINK:" . $oldentry;
    /*if (strpos($roomarray[$newentry],$linkstr) !== False)*/ $roomarray[$newentry] = $linkstr . "|" . $roomarray[$newentry]; //Don't perform link if already linked. (that check bugs out!)
  }
  $linkstr = "LINK:" . $newentry;
  /*if (strpos($roomarray[$oldentry],$linkstr) !== False)*/ $roomarray[$oldentry] = $linkstr . "|" . $roomarray[$oldentry];
  return $roomarray;
}
function allthesestairs ($roomarray,$newrow,$newcol,$newfloor) {
  $newentry = strval($newrow) . "," . strval($newcol);
  if (empty($roomarray[$newentry])) { //Create this entry.
    $roomarray[$newentry] = "STAIRS:" . $newfloor;
  } else {
    $linkstr = "STAIRS:" . $newfloor;
    $roomarray[$newentry] = $linkstr . "|" . $roomarray[$newentry];
  }
  return $roomarray;
}
//Some notes on the arguments of these functions: $distance is how far "into" the dungeon the room is. $gate is the gate number (1, 3, 5). $land is the Land the dungeon is located on.
function generateLoot($roomarray,$row,$col,$distance,$gate,$lootonly,$boonbucks,$trow) {
  global $gristname;
  //NOTE - Loot is always items or boons, generally speaking. The facility to loot grist directly is just there for...reasons.
  $entry = strval($row) . "," . strval($col);
  //Breakdown of Boondollar formula: random averages to 1250. 1 in 5 chance of boon loot. Raw average of 3750 at gate 1, 11,250 at gate 3, 33,750 at gate 5.
  $boons = ceil(floor(rand(1,5) / 5) * pow(3,$gate) * (1 + ($distance / 8)) * rand(500,2000));
  if ($boonbucks) $boons = $gate * ($gate + 1) * 500000 * ceil($gate / 2); //Triangle number of the gate multiplied by three million, multiplied again by half-ish the gate
  if ($boons == 0 || $lootonly) { //Generate an item as the loot. If it's a lootonly drop we don't want Boondollars from it. lootonly overrides boonbucks!
  if (!empty($trow['maxloot'])) {
  	$min = $trow['minloot'];
  	$max = $trow['maxloot'];
  } else {
    switch ($gate) {
    case 1:
      $min = 5;
      $max = 1000;
      break;
    case 3:
      $min = 1000;
      $max = 125000;
      break;
    case 5:
      $min = 100000;
      $max = 400000;
      break;
    default:
      $min = 0;
      $max = 999999999999; //Pick first item. This is a bugged result anyway.
      break;
    }
  }
  $exstr = "";
  if ($lootonly) {
  	if (!empty($trow['bossloots'])) {
  		$exstr = "WHERE " . $trow['bossloots'];
  		//echo "DEBUG: Lootonly query: SELECT `name` FROM `Captchalogue` $exstr<br />";
  		$min = -999999999999; //ensure that min/max doesn't affect this selection since it's manually set
      $max = 999999999999;
  	} else {
  		$exstr = "WHERE `Captchalogue`.`lootonly` = 1";
  	}
  } else {
  	if (!empty($trow['loots'])) {
  		$exstr = "WHERE " . $trow['loots'];
  	}
  }
    $selected = False;
    $itemsresult = mysql_query("SELECT `name` FROM `Captchalogue` $exstr");
    $totalitems = 0;
    while ($row = mysql_fetch_array($itemsresult)) $totalitems++;
    $item = rand(1,$totalitems); //Starting point for the item search.
    $item--; //otherwise it will eliminate 1 item from the search and if there's only 1 item dats bad
    $loopies = $totalitems;
    $min = ceil($min * (1 + ($distance / 16)));
    $max = ceil($max * (1 + ($distance / 16)));
    while (!$selected) {
      $loopies--;
      $itemresult = mysql_query("SELECT * FROM Captchalogue $exstr LIMIT " . $item . " , 1 ;");
      $itemrow = mysql_fetch_array($itemresult);
      $itemname = $itemrow['name'];
      //if ($debugprintbossloots) echo "checking $itemname<br />";
      $total = 0;
      $grist = 0;
      while (!empty($gristname[$grist])) {
        $gristcost = $gristname[$grist] . "_Cost";
        $total += $itemrow[$gristcost];
        $grist++;
      }
      if ($total >= $min && $total <= $max) { //Item has an acceptable grist cost.
	$selected = True;
	$roomarray[$entry] = $roomarray[$entry] . "|LOOT|ITEM:" . $itemname;
      } else {
	if ($loopies < -8) { //We've looped more times than there are possible items. An item has clearly not been found! Relax the constraints. We will recheck the current item again after this.
	  $min = floor($min / 2);
	  $max = ceil($max * 2); //Ceil not necessary, but symmetry is pretty.
	  $loopies = $totalitems;
	} elseif ($item >= $totalitems) { //We hit the end of the database.
	  $item = 0; //Wraparound
	} else {
	  $item++; //Check the next item to see if it works.
	}
      }
    }
  } else {
    $roomarray[$entry] = $roomarray[$entry] . "|LOOT|Boondollars:" . strval($boons);
  }
  return $roomarray;
}
function generateEncounter($roomarray,$row,$col,$distance,$gate,$enemies,$isboss,$trow) {
  $square = strval($row) . "," . strval($col);
  if ($isboss > 0) {
  	if (!empty($trow['boss'])) {
  		$roomarray[$square] = $roomarray[$square] . "|ENTRANCE|ENCOUNTER|BOSS:True|" . $trow['boss'];
  		//this string includes the ENEMYX: bits to allow for multiple enemies in the boss encounter
  	} else {
    switch ($gate) { //Select the boss enemy to be fought. When more are introduced, add randomization.
    case 1:
      $boss = "Kraken";
      break;
    case 3:
      if ($isboss == 1) $boss = "Hekatonchire";
      if ($isboss == 2) { //go ahead and put all of the starting hydra heads here so they generate consistently
      	$boss = "Hydra";
      	$hcount = 2; //1 is the hydra itself
      	while ($hcount <= 8) { //7 heads in total~
      		$boss .= "|ENEMY" . strval($hcount) . ":";
      		$randomresult = mysql_query("SELECT `basename` FROM `Enemy_Types` WHERE `Enemy_Types`.`basename` LIKE '%Hydra Head'");
					$countr = 0;
					while ($randrow = mysql_fetch_array($randomresult)) {
						$countr++;
					}
					$whodat = rand(1,$countr);
					$whodat--;
					$randomresult = mysql_query("SELECT `basename` FROM `Enemy_Types` WHERE `Enemy_Types`.`basename` LIKE '%Hydra Head' LIMIT $whodat,1");
					$randrow = mysql_fetch_array($randomresult);
					$randenemy = $randrow['basename'];
					if (empty($randenemy)) echo "DEBUGNOTE: Tried to spawn G3F3 boss thing with ID of $whodat, returned empty. This is no cause for alarm if you are not a dev, unless you see this message a bunch of times.<br />";
					$boss .= $randenemy;
      		$hcount++;
      	}
      }
      break;
    case 5:
      if ($isboss == 1) $boss = "Lich Queen";
      if ($isboss == 2) $boss = "True Hekatonchire";
      break;
    default:
      $boss = "The Bug";
      break;
    }
    $roomarray[$square] = $roomarray[$square] . "|ENTRANCE|ENCOUNTER|BOSS:True|ENEMY1:" . $boss;
  	}
  } else {
    if ($enemies < 1) $enemies = 1; //Paranoia - At least one enemy.
    //Some notes - The min and max power are base, i.e. for step zero. Maximum is double these. Boss values are pitched a cut above that.
    if (!empty($trow['maxpower'])) {
  		$min = $trow['minpower'];
  		$max = $trow['maxpower'];
  	} else {
    switch ($gate) {
    case 1:
      $min = 1;
      $max = 400;
      break;
    case 3:
      $min = 400;
      $max = 3500;
      break;
    case 5:
      $min = 4000;
      $max = 6500;
      break;
    default:
      $min = 0;
      $max = 999999999999; //Pick first item. This is a bugged result anyway.
      break;
    }
  	}
    if (($distance / 12) > 1.5) {
      $multiplier = 2.5;
    } else {
      $multiplier = 1 + ($distance / 12);
    }
    $min = floor($min * $multiplier);
    $max = ceil($max * $multiplier);
    $realmin = ceil(($min - 1) / 9); //Any enemy with at least 1/9 of the minimum can receive increased tiering to bump it up.
    $roomarray[$square] = $roomarray[$square] . "|ENCOUNTER";
    //Code to add encounter tag to array goes here.
    while ($enemies > 0) {
      $realenemies = ($enemies - 1); //Shifts the index back so enemy 1 receives zero modifier.
      $realmax = floor($max * (1 / (1 + ($realenemies * 0.125)))); //This ensures that the later enemies will not be overpowering given the numbers factor. Note that enemies are generated backwards!
      if (!empty($trow['enemies'])) { //see if we're using a dungeon template that has custom specific enemies
      	$ecount = 0;
      	$equery = "SELECT * FROM `Enemy_Types` WHERE (";
      	$eexplode = explode("|", $trow['enemies']);
      	while (!empty($eexplode[$ecount])) {
      		$equery .= "basename = '" . mysql_real_escape_string($eexplode[$ecount]) . "' OR ";
      		$ecount++;
      	}
      	$equery = substr($equery, 0, -4);
      	$equery .= ") AND `basepower` > $realmin AND `basepower` < $realmax";
      } else { //if not, populate the dungeon with underlings from lands/dungeons
      	$equery = "SELECT * FROM `Enemy_Types` WHERE `basepower` > $realmin AND `basepower` < $realmax AND (`appearson` = 'Lands' OR `appearson` = 'Dungeons')";
      }
      $potentialresult = mysql_query($equery);
      $options = 0;
      while ($potentialrow = mysql_fetch_array($potentialresult)) $options++;
      $potentialresult = mysql_query($equery);
      while (($potentialrow = mysql_fetch_array($potentialresult)) && $options > 0) {
	$selected = floor(rand(1,$options) / $options); //1 in $options chance
	if ($selected) {
	  $options = 0;
	  $tier = 1;
	  while ((($potentialrow['basepower'] * $tier) + ($tier * $tier)) <= $realmax && $tier < 10) $tier++; //Bump the tier up until either it maxxes or the power does.
	  $tier--; //Subtract off the tier addition that violated the loop condition.
	  $roomarray[$square] = $roomarray[$square] . "|ENEMY" . strval($enemies) . ":" . $potentialrow['basename'] . "|TIER" . strval($enemies) . ":" . strval($tier);
	  //Code to add enemy to array goes here.
	} else {
	  $options--;
	}
      }
      $enemies--;
    }
  }
  return $roomarray;
}
function generateDoor($roomarray,$row,$col,$brow,$bcol,$gate) {
	$square = strval($row) . "," . strval($col);
	$bsquare = strval($brow) . "," . strval($bcol); //the square that the door is blocking
	$totaldoors = 0;
	$doorresult = mysql_query("SELECT `ID` FROM `Dungeon_Doors` WHERE `Dungeon_Doors`.`gate` <= $gate");
	while (mysql_fetch_array($doorresult)) $totaldoors++;
	if ($totaldoors > 0) { //if there ARE any results
		$door = rand(1,$totaldoors);
		$doorresult = mysql_query("SELECT * FROM `Dungeon_Doors` WHERE `Dungeon_Doors`.`gate` <= $gate LIMIT " . $door . " , 1 ;");
		$drow = mysql_fetch_array($doorresult); //SHOULD return exactly 1 row
		if (strpos($drow['keys'], "|") === false) {
			$spawnkey = $drow['keys'];
		} else {
			$boom = explode("|", $drow['keys']);
			$keycount = count($boom);
			$spawnkeyn = rand(0,$keycount);
			$spawnkey = $boom[$spawnkeyn];
		}
		$foundaroom = false;
		$tries = 100 - ($gate * 10); //there will be a chance the key won't spawn, so that the player has to either create the key themselves or find another way to get past the door
		while (!$foundaroom && $tries > 0) { //here we pick random rooms until we find one that isn't empty. 
		//since this function is called during generation, the key won't spawn beyond the door because there are no rooms beyond it yet.
			$rcol = rand(1,10);
			$rrow = rand(1,10);
			$rsquare = strval($rcol) . "," . strval($rrow);
			if (!empty($roomarray[$rsquare])) {
				$foundaroom = true;
				$roomarray[$rsquare] .= "|LOOT|ITEM:" . $spawnkey; //place the key. Door rows shouldn't have keys that are too valuable to appear in the dungeon.
			} else $tries--;
		}
		$roomarray[$square] = str_replace("LINK:" . $bsquare, "LINK:" . $bsquare . ":" . $drow['ID'], $roomarray[$square]);
	}
	return $roomarray;
}

function makeBoss($roomarray,$bossrow,$bosscol,$length,$gate,$bosstype,$trow) {
	$roomarray = generateEncounter($roomarray,$bossrow,$bosscol,$length,$gate,1,$bosstype,$trow); //Generate the boss encounter.
	$roomarray = generateLoot($roomarray,$bossrow,$bosscol,$length,$gate,False,False,$trow); //Phat lewtz
	$roomarray = generateLoot($roomarray,$bossrow,$bosscol,$length,$gate,False,False,$trow);
	$roomarray = generateLoot($roomarray,$bossrow,$bosscol,$length,$gate,True,False,$trow); //Phat loot-only special lewtz
	$roomarray = generateLoot($roomarray,$bossrow,$bosscol,$length,$gate,False,True,$trow); //SWAG
	return $roomarray;
}

function makeSidepath($roomarray,$entryrow,$entrycol,$baselength,$gate,$reps,$trow) {
  $branchdir = rand(1,4);
  $branchstart = $branchdir;
  $pass = false;
  while (!$pass) {
    $oldrow = $entryrow;
    $oldcol = $entrycol;
    switch ($branchdir) {
      case 1: //North
        if ($oldrow < 10) $oldrow++;
	break;
      case 2: //East
	if ($oldcol < 10) $oldcol++;
	break;
      case 3: //South
	if ($oldrow > 1) $oldrow--;
	break;
      case 4: //West
	if ($oldcol > 1) $oldcol--;
	break;
    }
    $oldroom = strval($oldrow) . "," . strval($oldcol);
    if (empty($roomarray[$oldroom])) {
      $pass = true;
    } else {
      $branchdir++;
      if ($branchdir > 4) $branchdir = 1;
      if ($branchdir == $branchstart) { //clearly there isn't a path to go from here
        $branchdir = 0; //make this a transportalizer because we're cool like that
        $pass = true;
        while (!empty($roomarray[$oldroom]) || strpos($roomarray[$oldroom], "ENTRANCE") !== false) {
          $oldrow = rand(1,10);
          $oldcol = rand(1,10);
          $oldroom = strval($oldrow) . "," . strval($oldcol);
        }
      }
    }
  }
  $roomarray = roomlink($roomarray,$oldrow,$oldcol,$entryrow,$entrycol); //link function will link two spaces, creating the "new" or first one if it did not exist.
  if ($reps <= 4) {
    if (rand($reps,4) != 4) {
      $reps++;
      if (rand(1,2) == 2) $roomarray = generateEncounter($roomarray,$oldrow,$oldcol,$baselength,$gate,rand(1,rand(1,5)),False,$trow); //Create encounter. No. of opponents weighted to 2.
      $roomarray = makeSidepath($roomarray,$oldrow,$oldcol,$baselength,$gate,$reps,$trow); //keep going
    } else { //this is the "treasure room"
      $roomarray = generateEncounter($roomarray,$oldrow,$oldcol,$baselength,$gate,rand(2,rand(2,5)),False,$trow);
      $roomarray = generateLoot($roomarray,$oldrow,$oldcol,$baselength,$gate,False,False,$trow);
      if (rand(1,2) == 2) $roomarray = generateLoot($roomarray,$oldrow,$oldcol,$baselength,$gate,False,False,$trow);
      if (rand(1,4) == 4) $roomarray = generateLoot($roomarray,$oldrow,$oldcol,$baselength,$gate,False,False,$trow);
    }
  }
  return $roomarray;
}

function makeDungeon($userrow,$gate,$floor,$finalfloor,$land,$basedistance,$lolname,$trow) {
	$dgnstring = $lolname . "_" . strval($floor);
	mysql_query("DELETE FROM `Dungeons` WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;"); //Wipe the dungeon row.
	mysql_query("INSERT INTO `Dungeons` (`username`) VALUES ('$dgnstring');");
	//Remake it, but empty. Note that this means a dungeon row will appear if the user has never entered a dungeon before.
	//Procedurally generate a dungeon here. Don't forget to reload the user row to reflect the new "in a dungeon" status
	if (!empty($trow['name'])) {
		$branchchance = $trow['chance_branch'];
		$enemychance = $trow['chance_enemy'];
		$lootchance = $trow['chance_loot'];
		$transchance = $trow['chance_trans'];
	} else {
		$branchchance = 15;
		$enemychance = 50;
		$lootchance = 33;
		$transchance = 10;
	}
	$entryrow = rand(3,8); 
	$entrycol = rand(3,8);
	$entry = strval($entryrow) . "," . strval($entrycol);
	$roomarray = array(); //The arguments will consist of all flags that room receives. Empty argument? Nonexistent room.
	if ($floor == 1)
	$roomarray[$entry] = "ENTRANCE|VISITED";
	else
	$roomarray[$entry] = "STAIRS:" . $lolname . "_" . strval($floor - 1) . "|VISITED"; //instead of an entrance, put stairs to the previous floor! ain't I clever
	$i = 0;
	$north = 1;
	$east = 2;
	$south = 3;
	$west = 4;
	$possibilities = array($north => False, False, False, False); //North, East, South, West, checking in clockwise direction.
	while ($i <= 4) {
	  $possibilities[rand(1,4)] = True; //1 in 64 chance of single arm, 6 in 64 for four arms. Probabilities for 2 and 3 are similar, exact values not important. 
	  $i++;
	}
	//Paranoia: Handle all border cases so that arms never appear going off the playing area. (Entrance should never be on the edge though)
	if ($entryrow == 1) $possibilities[$south] = False;
	if ($entryrow == 10) $possibilities[$north] = False;
	if ($entrycol == 1) $possibilities[$west] = False;
	if ($entrycol == 10) $possibilities[$east] = False;
	$i = $north; //Start at north
	$armlength = array($north => 0, 0, 0, 0); //Track this so we can put proportionate rewards down: the longer the path, the better the loot and the tougher the monsters!
	$furthestroom = array($north => "0,0", "0,0", "0,0", "0,0"); //This stores the room at the end of each "arm" with rooms. The boss room is placed adjacent to the one with the most distance.
	while ($i <= 4) { //Handle each arm in turn
	  $oldrow = $entryrow;
	  $oldcol = $entrycol;
	  switch ($i) {
	  case 1: //North
	    $oldrow++;
	    break;
	  case 2: //East
	    $oldcol++;
	    break;
	  case 3: //South
	    $oldrow--;
	    break;
	  case 4: //West
	    $oldcol--;
	    break;
	  default:
	    //ERROR ERROR
	    logDebugMessage($username . " - dungeon generator tried to make arm in direction $i");
	    break;
	  }
	  $oldroom = strval($oldrow) . "," . strval($oldcol);
	  if ($possibilities[$i] && empty($roomarray[$oldroom])) { //Generate this arm if it is a) to be generated, and b) another room didn't appear blocking it.
	    $roomarray = roomlink($roomarray,$oldrow,$oldcol,$entryrow,$entrycol); //link function will link two spaces, creating the "new" or first one if it did not exist.
	    $previousdir = $i; //We start off coming from that direction.
	    $continue = 1;
	    while ($continue && $armlength[$i] < 13) { //This will turn up as false if continue ends up as zero. Paranoia: We're not supposed to be continuing if armlength is 13 or higher.
	      $continue = rand(0,24 - ($armlength[$i] * 2)); //Fail to perpetuate this arm on a 0. Guaranteed to terminate after twelve steps.
	      $teleport = rand(1,100);
	      if ($teleport <= $transchance) { //default: 1 in 10 chance
		$randomrow = rand(1,10);
		$randomcol = rand(1,10);
		$randomsquare = strval($randomrow) . "," . strval($randomcol);
		while (strpos($roomarray[$randomsquare],"ENTRANCE") !== False && strpos($roomarray[$randomsquare],"STAIRS") !== False) { //Keep re-selecting if we hit the entrance/stairs. 1% chance per loop
		  $randomrow = rand(1,10);
		  $randomcol = rand(1,10);
		  $randomsquare = strval($randomrow) . "," . strval($randomcol);
		}
		$previousdir = 0; //All bets are off!
		$roomarray = roomlink($roomarray,$randomrow,$randomcol,$oldrow,$oldcol); //link function links two spaces, creating the "new" or first one if it did not exist.
		$oldrow = $randomrow;
		$oldcol = $randomcol;
	      } else {
		$direction = rand(1,4);
		if ($direction == 4) $direction = 0;
		if ((($direction == (($previousdir + 2) % 4)) || rand(1,2) == 2) && $previousdir != 0) $direction = $previousdir;
		if ($direction == 0) $direction = 4;
		//If we picked the direction we came from, we go straight. Also a 1 in 2 chance to just go straight anyway, since we prefer that on the whole. If previousdir is 0, we don't care where
		//we go, so this isn't an issue.
		$newrow = $oldrow;
		$newcol = $oldcol;
		switch ($direction) {
		case 1: //North
		  $newrow++;
		  break;
		case 2: //East
		  $newcol++;
		  break;
		case 3: //South
		  $newrow--;
		  break;
		case 4: //West
		  $newcol--;
		  break;
		default:
		  echo "ERROR: Unsupported direction $direction</br>";
		  logDebugMessage($username . " - dungeon generator tried to continue arm in direction $direction");
		  //ERROR ERROR
		  break;
		}
		if ($newrow < 1 || $newrow > 10 || $newcol < 1 || $newcol > 10) { //Hit a wall: We are done.
		  $continue = 0;
		  if (rand(1,100) <= $branchchance*3 && strpos($roomarray[$oldroom], "ENTRANCE") === false) $roomarray = makeSidepath($roomarray,$oldrow,$oldcol,$armlength[$i]+$basedistance,$gate,1,$trow); //good chance of branching off into a side path anyway
		} else {
		  $newthing = strval($newrow) . "," . strval($newcol);
		  $wasempty = False;
		  if (!empty($roomarray[$newthing])) {
		    $continue = 0; //Room already exists: Terminate branch, but still link the two targets.
		  } else { //Room does not already exist: Check for adding enemies and phat lootz
		    $wasempty = True;
		  }
		  $roomarray = roomlink($roomarray,$newrow,$newcol,$oldrow,$oldcol); //ink function links two spaces, creating the "new" or first one if it did not exist.
		  if ($wasempty) $furthestroom[$i] = (strval($newrow) . "," . strval($newcol)); //Room was empty: Is now the furthest along room for this arm of the dungeon.
		  if ($wasempty && rand(1,100) <= $lootchance) $roomarray = generateLoot($roomarray,$newrow,$newcol,$armlength[$i]+$basedistance,$gate,False,False,$trow); //Room was empty: 1 in 3 chance of loot.
		  if ($wasempty && rand(1,100) <= $enemychance) { //Room was empty: 1 in 2 chance of hostiles.
		    $roomarray = generateEncounter($roomarray,$newrow,$newcol,$armlength[$i]+$basedistance,$gate,rand(1,rand(1,5)),False,$trow); //Create encounter. No. of opponents weighted to 2.
		    if (rand(1,100) <= $lootchance*1.5) $roomarray = generateLoot($roomarray,$newrow,$newcol,$armlength[$i]+$basedistance,$gate,False,False,$trow); //1 in 2 chance of encounter guarding some loot. This stacks.
		  }
		  //if ($wasempty && rand(1,4) == 4) $roomarray = generateDoor($roomarray,$oldrow,$oldcol,$newrow,$newcol,$gate); //doors are broken and won't be a thing until later
		  if ($wasempty && rand(1,100) <= $branchchance && strpos($roomarray[$oldroom], "ENTRANCE") === false) $roomarray = makeSidepath($roomarray,$oldrow,$oldcol,$armlength[$i]+$basedistance,$gate,1,$trow); //side path to a treasure room!
		  $previousdir = $direction; //Set the previous direction.
		  $oldrow = $newrow;
		  $oldcol = $newcol;
		}
	      }
	      $armlength[$i]++;
	    }
	  }
	  $i++;
	}
	$i = 1;
	$length = 0;
	while ($i <= 4) {
	  if ($armlength[$i] > $length) {
	    $longest = $i;
	    $length = $armlength[$i];
	  }
	  $i++;
	}
	$longestcoords = explode(",", $furthestroom[$longest]); //0 is the row, 1 is the col.
	$northone = strval(intval($longestcoords[0]) + 1) . "," . strval($longestcoords[1]);
	$southone = strval(intval($longestcoords[0]) - 1) . "," . strval($longestcoords[1]);
	$eastone = strval($longestcoords[0]) . "," . strval(intval($longestcoords[1]) + 1);
	$westone = strval($longestcoords[0]) . "," . strval(intval($longestcoords[1]) - 1);
	if (empty($roomarray[$northone]) && intval($longestcoords[0]) != 10) { //North room is empty and not off the map. other checks are similar.
	  $bossrow = intval($longestcoords[0]) + 1;
	  $bosscol = intval($longestcoords[1]);
	  $roomarray = roomlink($roomarray,(intval($longestcoords[0]) + 1),intval($longestcoords[1]),intval($longestcoords[0]),intval($longestcoords[1]));
	} elseif (empty($roomarray[$southone]) && intval($longestcoords[0]) != 1) {
	  $bossrow = intval($longestcoords[0]) - 1;
	  $bosscol = intval($longestcoords[1]);
	  $roomarray = roomlink($roomarray,(intval($longestcoords[0]) - 1),intval($longestcoords[1]),intval($longestcoords[0]),intval($longestcoords[1]));
	} elseif (empty($roomarray[$eastone]) && intval($longestcoords[1]) != 10) {
	  $bossrow = intval($longestcoords[0]);
	  $bosscol = intval($longestcoords[1]) + 1;
	  $roomarray = roomlink($roomarray,intval($longestcoords[0]),(intval($longestcoords[1]) + 1),intval($longestcoords[0]),intval($longestcoords[1]));
	} elseif (empty($roomarray[$westone]) && intval($longestcoords[1]) != 1) {
	  $bossrow = intval($longestcoords[0]);
	  $bosscol = intval($longestcoords[1]) - 1;
	  $roomarray = roomlink($roomarray,intval($longestcoords[0]),(intval($longestcoords[1]) - 1),intval($longestcoords[0]),intval($longestcoords[1]));
	} else {
	  $randomrow = rand(1,10);
		$randomcol = rand(1,10);
		$randomsquare = strval($randomrow) . "," . strval($randomcol);
		$randomchances = 0;
		while (!empty($roomarray[$randomsquare])) { //Keep re-selecting if we hit an existing room.
		  $randomrow = rand(1,10);
		  $randomcol = rand(1,10);
		  $randomsquare = strval($randomrow) . "," . strval($randomcol);
		  $randomchances++;
		  if ($randomchances > 100) { //we probably aren't going to find a room(?!), so break the loop
		  	break;
		  }
		}
		if ($randomchances <= 100) {
			$roomarray = roomlink($roomarray,$randomrow,$randomcol,intval($longestcoords[0]),intval($longestcoords[1])); //link function links two spaces, creating the "new" or first one if it did not exist.
			$bossrow = $randomrow;
			$bosscol = $randomcol;
		} else $roomarray[$furthestroom[$longest]] .= "|DESCRIPTION:This room is totally barren, except for a sticky note on the wall. It reads, \"Dear $username, Unfortunately, this dungeon seems to be so complex that we couldn't find room for the boss/stairs, despite our every effort to ensure this wouldn't happen. We offer our sincerest apology and hope that you have not totally lost faith in dungeon diving. -The Management\"";
	}
	  if ($finalfloor) {
	    if ($floor == $gate) $bosstype = 2; //max amount of floors for this dungeon means toughest possible boss
	    else $bosstype = 1;
	    $roomarray = makeBoss($roomarray,$bossrow,$bosscol,($armlength[$longest] + 1 + $basedistance),$gate,$bosstype,$trow);
	  }
	  else $roomarray = allthesestairs($roomarray,$bossrow,$bosscol,$lolname . "_" . strval($floor+1));
	$i = 1;
	$j = 1;
	while ($i <= 10) {
	  while ($j <= 10) {
	    $tile = strval($i) . "," . strval($j);
	    if (!empty($roomarray[$tile])) mysql_query("UPDATE `Dungeons` SET `$tile` = '" . mysql_real_escape_string($roomarray[$tile]) . "' WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;");
	    $j++;
	  }
	  $j = 1;
	  $i++;
	}
	mysql_query("UPDATE `Dungeons` SET `dungeonrow` = $entryrow,`dungeoncol` = $entrycol,`dungeongate` = $gate,`dungeonland` = '$land' WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;");
	if ($floor == 1) {
	  mysql_query("UPDATE `Players` SET `dungeonrow` = $entryrow,`dungeoncol` = $entrycol WHERE `Players`.`username` = '" . $userrow['username'] . "'"); //put the player on the entrance
	}
	return $armlength[$longest];
}

function findStairs($dungeonrow,$oldfloor) { //this function searches for the stairs leading to a particular dungeon row
  $stairstr = "STAIRS:" . $oldfloor;
  $i = 1;
  while ($i <= 10) {
    $j = 1;
    while ($j <= 10) {
      $searchstr = strval($i) . "," . strval($j);
      if (strpos($dungeonrow[$searchstr], $stairstr) !== false) {
        return $searchstr; //found the stairs!
      }
      $j++;
    }
    $i++;
  }
  return "0,0"; //no stairs found, lolwut
}

if (empty($_SESSION['username'])) {
  echo "Log in to go dungeon diving.</br>";
} elseif ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
  //User currently strifing. Send them back to the strife page!
  echo "You can't explore the dungeon while strifing!</br>";
} elseif ($userrow['dreamingstatus'] != "Awake") {
  echo "Okay look, I know you probably told one of your friends that you could run a dungeon in your sleep or something. Trust me on this: Don't try it.";
} elseif ($userrow['dungeonstrife'] == 6) { //since this will probably screw with dungeons one way or another, forbid them entirely
	echo "You still have a consort quest to turn in! You should do that before getting yourself lost in a dungeon.<br />";
} else {
	$dgnvision = -1;
	if (strpos($userrow['permstatus'], "DVISION") !== false) {
		$statusarray = explode("|", $userrow['permstatus']);
		$i = 0;
		while (!empty($statusarray[$i])) {
			$currentarray = explode(":", $statusarray[$i]);
			if (strpos($currentarray[0], "DVISION") !== false) {
				$dgnvision = intval($currentarray[1]);
			}
			$i++;
		}
	}
  $gristname = initGrists();
  $allowallescape = true;
  if ($_GET['emergency'] == "escape") {
  	if ($userrow['session_name'] == "Developers" || $allowallescape == true) {
    	mysql_query("UPDATE `Players` SET `indungeon` = 0 WHERE `Players`.`username` = '$username' LIMIT 1;");
	$userrow['indungeon'] = 0;
  	} else echo "You can't do that, sorry!<br />";
  }
  echo '<a id="display"></a>'; //This tag is at the very top of the page.
  $dungeonrows = 10;
  $dungeoncols = 10;
  $dgnstring = $userrow['currentdungeon'];
  if (!empty($_POST['ascend'])) { //user is taking some stairs
    if ($userrow['indungeon'] == 0) { //Player not in a dungeon.
      echo "You are not currently in a dungeon, so you can't use a transportalizer.</br>";
    } else {
      $playertile = strval($userrow['dungeonrow']) . "," . strval($userrow['dungeoncol']);
      $dungeonresult = mysql_query("SELECT `$playertile` FROM `Dungeons` WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;");
      $dungeonrow = mysql_fetch_array($dungeonresult);
      if (strpos($dungeonrow[$playertile],"STAIRS") !== False) { //are there stairs here?
	$flags = explode("|", $dungeonrow[$playertile]);
	$i = 0;
	while (!empty($flags[$i])) {
          $flag = $flags[$i];
          $args = explode(":", $flag);
	  if ($args[0] == "STAIRS") { //these are the stairs we just detected (there should never be more than one flight of stairs in the same room)
	    if ($args[1] == $_POST['ascend']) { //make sure the floor the user is trying to get to matches the stairs' destination
	      $floorresult = mysql_query("SELECT * FROM `Dungeons` WHERE `Dungeons`.`username` = '" . $_POST['ascend'] . "'");
	      $floorrow = mysql_fetch_array($floorresult);
	      $newlocation = findStairs($floorrow,$userrow['currentdungeon']);
	      if ($newlocation != "0,0") {
	        $dgnstring = $args[1];
	        mysql_query("UPDATE `Players` SET `currentdungeon` = '$dgnstring' WHERE `Players`.`username` = '$username' LIMIT 1;");
	        $coords = explode(",", $newlocation);
	        mysql_query("UPDATE `Players` SET `dungeonrow` = " . strval($coords[0]) . ",`dungeoncol` = " . strval($coords[1]) . " WHERE `Players`.`username` = '$username' LIMIT 1;");
	        $userrow['dungeonrow'] = $coords[0];
	        $userrow['dungeoncol'] = $coords[1];
	        echo "The transportalizer whisks you away to another floor of the dungeon.<br />";
	      } else {
	        echo "ERROR: Matching staircase not found on destination floor!<br />";
	        logDebugMessage($username . " - in a dungeon, tried to ascend to $floorrow[name] from $userrow[currentdungeon], failed");
	      }
	    } else {
	      echo "That transportalizer doesn't lead there!<br />";
	    }
	  }
	  $i++;
	}
      } else {
	echo "You can't use a transportalizer that isn't there!</br>";
      }
    }
  }
  if (!empty($_POST['exitdungeon'])) {
    if ($userrow['indungeon'] == 0) { //Player not in a dungeon.
      echo "You are not currently in a dungeon, so you can't exit one.</br>";
    } else {
      $playertile = strval($userrow['dungeonrow']) . "," . strval($userrow['dungeoncol']);
      $dungeonresult = mysql_query("SELECT `$playertile` FROM `Dungeons` WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;");
      $dungeonrow = mysql_fetch_array($dungeonresult);
      if (strpos($dungeonrow[$playertile],"ENTRANCE") !== False) {
	mysql_query("UPDATE `Players` SET `indungeon` = 0 WHERE `Players`.`username` = '$username' LIMIT 1;");
	$userrow['indungeon'] = 0;
      } else {
	echo "You may only exit the dungeon while standing on the entrance!</br>";
      }
    }
  }
  if (!empty($_POST['joinmate'])) { //player is joining a sessionmate's dungeon
    if ($userrow['indungeon'] != 0) { //Player already IN a dungeon.
      echo "You are already in a dungeon!</br>";
    } elseif ($userrow['encounters'] < 3) {
      echo "You don't have enough encounters to travel that far.</br>";
    } else {
      $dgnstring = $_POST['joinmate'];
      $start = 0;
      $abort = strlen($dgnstring) * -1;
      $ochar = "x";
      while ($ochar != "_" && $start > $abort) {
      	$start--;
      	$ochar = substr($dgnstring, $start, 1);
      }
      if ($start > $abort) { //dungeon is numbered
        $thisdungeon = substr($dgnstring, 0, $start); //extract the username from the dungeon name
      } else {
        $thisdungeon = $dgnstring;
      }
      $materesult = mysql_query("SELECT `username`,`session_name` FROM `Players` WHERE `Players`.`username` = '$thisdungeon'"); //find the player whose dungeon this is
      while ($materow = mysql_fetch_array($materesult)) {
      	if ($materow['session_name'] != $userrow['session_name']) { //this player isn't in the same session as the user! either that or the user is in a special dungeon
      		$dgnstring = "LOLHAX"; //force it to print the error. this'll confuse "hackers"
      	}
      }
      $dungeonresult = mysql_query("SELECT `username`,`dungeonland`,`dungeonrow`,`dungeoncol` FROM `Dungeons` WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;");
      $dungeonrow = mysql_fetch_array($dungeonresult);
      if ($dungeonrow['username'] == $dgnstring) { //dungeon found
      	$chain = chainArray($userrow);
      	$totalchain = count($chain);
      	$landcount = 0;
        $aok = false;
        while ($landcount < $totalchain && !$aok) { //check to see if the user can reach the land this dungeon is on
        	if ($dungeonrow['dungeonland'] == $chain[$landcount]) $aok = true;
	  			$landcount++;
        }
        if ($aok) {
        	chargeEncounters($userrow, 3, 0); //not actually getting into strife
        	mysql_query("UPDATE `Players` SET `indungeon` = 1, `currentdungeon` = '$dgnstring', `dungeonrow` = $dungeonrow[dungeonrow], `dungeoncol` = $dungeonrow[dungeoncol] WHERE `Players`.`username` = '$username' LIMIT 1;");
					//note that the original dungeonrow/dungeoncol variables will always mark the dungeon's initial entrance
					$dungeongen = true;
        } else echo "You can't reach the land that dungeon is on!<br />";
      } else echo "ERROR: Dungeon not found.<br />";
    }
  }
  if (!empty($_POST['newdungeon'])) { //Player generating a dungeon.
    if ($userrow['indungeon'] != 0) { //Player already IN a dungeon.
      echo "You are already in a dungeon!</br>";
    } elseif ($userrow['encounters'] < 3) {
      echo "You fail to encounter a dungeon.</br>";
    } else {
      if ($userrow['dungeonstrife'] <= 4) mysql_query("UPDATE `Players` SET `dungeonstrife` = 0 WHERE `Players`.`username` = '$username' LIMIT 1;"); //Paranoia: Ensure dungeonstrife not active.
      //Check the input here.
      $tfound = false;
      $trow = array();
      if ($_POST['questdungeon'] == "yes") {
      	if ($userrow['currentquest'] != 0) {
      		$qresult = mysql_query("SELECT * FROM Consort_Dialogue WHERE ID = " . strval($userrow['currentquest']));
      		$qrow = mysql_fetch_array($qresult);
      		if ($qrow['context'] == "questdungeon" || $qrow['context'] == "questdungeon+") {
      			$tresult = mysql_query("SELECT * FROM Dungeon_Templates WHERE name = '" . mysql_real_escape_string($qrow['req_keyword']) . "'");
						$trow = mysql_fetch_array($tresult);
						if ($trow['name'] != $qrow['req_keyword']) {
							echo "ERROR: Cannot find dungeon template '$template'; generating normal dungeon instead<br />";
							logDebugMessage($userrow['username'] . " - tried to spawn dungeon of template $template and could not find template row");
						} else {
							$tfound = true;
							$questgoal = intval($qrow['req_abstratus']); //contains a dialogue ID that will serve as the goal for this quest
							if ($qrow['req_base'] == "yes") $onboss = true;
							else $onboss = false;
						}
      		} else echo "The quest you are on either doesn't exist or is not a dungeon quest.<br />";
      	} else echo "You are not currently on a quest.<br />";
      }
      $gateresult = mysql_query("SELECT * FROM Gates");
      $gaterow = mysql_fetch_array($gateresult); //Gates only has one row.
      $currentrow = $userrow;
      $done = False;
      $access = False;
      $fly = canFly($userrow);
      while (!$done) { //Note that we break out of everything once access is set, since it sets $done to be true down there.
	$gates = 0;
	$i = 1;
	while ($i <= 7 && !$access) {
	  $gatestr = "gate" . strval($i);
	  if ($gaterow[$gatestr] <= $currentrow['house_build_grist'] || $fly) {
	    if ($_POST['newdungeon'] == $currentrow['username'] . ":" . strval($i)) {
	      $gate = $i; //May as well set this here.
	      $land = $currentrow['username'];
	      $access = True;
	      if ($tfound) { //we're loading up a quest dungeon, so make sure this is the land the player is questing on
	    		if ($currentrow['username'] != $userrow['questland']) {
	    			$access = false;
	    			echo "That land isn't the land you're questing on.<br />";
	    		}
	    	}
	    }
	    $gates++;
	  } else {
	    $i = 7; //We are done.
	  }
	  $i++;
	}
	if (!empty($currentrow['server_player']) && $currentrow['server_player'] != $username && !$access) {
	  $currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '$currentrow[server_player]';");
	  $currentrow = mysql_fetch_array($currentresult);
	  if ($currentrow['house_build_grist'] < $gaterow["gate2"] && !$fly) $done = True; //This house is unreachable. Chain is broken here.
	} else { //Player has no server, gates go nowhere. This is not canonical behaviour, but canonical behaviour is impossible since it relies on prediction. Alternatively, loop is complete.
	  //Note that if gate 1 has not been reached, then gate 2 wasn't either and the Land was never accessed in the first place! ($access being true also cancels out here)
	  $done = True; //No further steps.
	}
      }
      if (strpos($userrow['storeditems'], "GLITCHGATE.") !== false) {
      	$access = true; //always admit the player if they have the glitch gate (hey, it's bugged anyway)
      	if (empty($gate)) $gate = 6; //if we're here because of the glitch gate, and the player doesn't have gate 6, $gate won't be set
      	if (empty($land)) $land = $username; //same as above, gotta set land manually or else gristless enemies
      }
      //Finish checking input here. $access must be True for success
      if ($access) {
	chargeEncounters($userrow, 3, 1); //strifing with dungeon guardian = 1 encounter
	mysql_query("DELETE FROM `Dungeons` WHERE `Dungeons`.`username` = '$username' LIMIT 1;"); //ensure that old dungeons are cleaned up; new dungeons will never just have the player's username
	$dungeongen = True;
	$lolname = $username;
	$materesult = mysql_query("SELECT `username` FROM `Players` WHERE `Players`.`currentdungeon` LIKE '" . $lolname . "_%' AND `Players`.`indungeon` = 1 AND `Players`.`username` != '" . $lolname . "'");
	$i = 0;
	while ($materow = mysql_fetch_array($materesult)) {
	  $lolname = $materow['username'];
	  $materesult = mysql_query("SELECT `username` FROM `Players` WHERE `Players`.`currentdungeon` LIKE '" . $lolname . "_%' AND `Players`.`indungeon` = 1 AND `Players`.`username` != '" . $lolname . "'");
	  //this will keep going between players until it finds one whose dungeon isn't occupied
	  $i++;
	  if ($i > 100) {
	    break; //...or unless something is clearly wrong
	    echo "Infinite loop protection activated!<br />";
	  }
	}
	$floors = rand(1,$gate);
	$i = 0;
	$finalfloor = false;
	$fulldistance = 0;
	while ($i < $floors) {
	  $i++;
	  if ($i == $floors) {
	  	$finalfloor = true;
	  }
	  $thisdistance = makeDungeon($userrow,$gate,$i,$finalfloor,$land,$fulldistance,$lolname,$trow);
	  $fulldistance += $thisdistance;
	  if ($tfound && $finalfloor) { //on a quest
	  	$finalfloorresult = mysql_query("SELECT * FROM `Dungeons` WHERE `Dungeons`.`username` = '" . $lolname . "_" . strval($i) . "'");
	  	$ffrow = mysql_fetch_array($finalfloorresult);
	  	if (!$onboss) { //goal specified, spawn it somewhere on the final floor
	  		$row = rand(1,10);
	  		$col = rand(1,10);
	  		$room = strval($row) . "," . strval($col);
	  		$attempts = 0;
	  		while (empty($ffrow[$room]) && $attempts < 100) {
	  			$row = rand(1,10);
	  			$col = rand(1,10);
	  			$room = strval($row) . "," . strval($col);
	  			$attempts++;
	  		}
	  	} else {
	  		$row = 1;
	  		$col = 1;
	  		$bossroom = "";
	  		while ($row <= 10 && empty($bossroom)) {
	  			while ($col <= 10 && empty($bossroom)) {
	  				$room = strval($row) . "," . strval($col);
	  				//echo $room . " -- " . $ffrow[$room] . "<br />";
	  				if (strpos($ffrow[$room], "|BOSS") !== false) {
	  					$bossroom = $room;
	  				}
	  				$col++;
	  			}
	  			$col = 1; //nearly forgot this, lol.
	  			$row++;
	  		}
	  		$room = $bossroom;
	  	}
	  	if (empty($ffrow[$room])) { //couldn't find the boss
	  		echo "ERROR: Could not spawn quest goal in the dungeon.<br />";
	  		logDebugMessage($userrow['username'] . " - goal specified for quest $questgoal, found nowhere to spawn (dungeon: " . $lolname . "_" . strval($i) . ")");
	  	} else {
	  		if (!empty($questgoal)) {
	  			$goalresult = mysql_query("SELECT * FROM Consort_Dialogue WHERE ID = " . strval($questgoal) . " LIMIT 1;");
	  			$goalrow = mysql_fetch_array($goalresult);
	  			if (!empty($goalrow['req_keyword'])) { //there are enemies in the goal room. may or may not be a separate encounter from any existing enemies
	  				$ffrow[$room] .= "ENCOUNTER|";
						$enemygrists = explode("|", $goalrow['req_grist']);
						$enemynames = explode("|", $goalrow['req_keyword']);
						$count = 0;
						while (!empty($enemynames[$count])) {
							$estr = "ENEMY" . strval($count) . ":";
							$gstr = "TIER" . strval($count) . ":";
							$ffrow[$room] .= $estr . $enemynames[$count] . "|";
							if (!empty($enemygrists[$count])) {
								$ffrow[$room] .= $gstr . $enemygrists[$count] . "|";
							}
							$i++;
						}
					}
		  		$ffrow[$room] .= "|QUESTGOAL:" . strval($userrow['currentquest']) . ":" . $goalrow['dialogue'] . "|";
	  		} else {
	  			$ffrow[$room] .= "|QUESTGOAL:" . strval($userrow['currentquest']) . "|";
	  		}
	  	}
	  	$ffrow[$room] = preg_replace("/\\|{2,}/","|",$ffrow[$room]); //eliminate all blanks
	  	mysql_query("UPDATE `Dungeons` SET `$room` = '" . mysql_real_escape_string($ffrow[$room]) . "' WHERE `Dungeons`.`username` = '" . $lolname . "_" . strval($i) . "' LIMIT 1;");
	 	}
	}
	mysql_query("UPDATE `Players` SET `indungeon` = 1, `currentdungeon` = '" . $lolname . "_1' WHERE `Players`.`username` = '$username' LIMIT 1;");
	$dgnstring = $username . "_1";
      } else {
	echo "You do not have access to that gate for dungeoneering purposes.</br>";
      }
    }
  }
  if ($userrow['dungeonstrife'] != 0 && $userrow['indungeon'] != 0) { //User returning from dungeon-based strife. Paranoia: Make sure actually in dungeon.
  	if ($userrow['dungeonstrife'] != 6) //Paranoia: make sure this doesn't overwrite the player's quest completion status
    mysql_query("UPDATE `Players` SET `dungeonstrife` = 0 WHERE `Players`.`username` = '$username' LIMIT 1;"); //We'll be handling this here.
    if ($userrow['dungeonstrife'] == 1) { //Failure.
      mysql_query("UPDATE `Players` SET `dungeonrow` = $userrow[olddungeonrow] WHERE `Players`.`username` = '$username' LIMIT 1;"); //RUN AWAY!
      mysql_query("UPDATE `Players` SET `dungeoncol` = $userrow[olddungeoncol] WHERE `Players`.`username` = '$username' LIMIT 1;");
      $userrow['dungeonrow'] = $userrow['olddungeonrow']; //I don't think this is actually necessary, but just in case
      $userrow['dungeoncol'] = $userrow['olddungeoncol'];
      header('location:/dungeons.php');
    } elseif ($userrow['dungeonstrife'] == 2) { //Victory!
      echo "You have defeated the enemies guarding this room!</br>";
      $room = strval($userrow['dungeonrow']) . "," . strval($userrow['dungeoncol']);
      $dungeonresult = mysql_query("SELECT `dungeonrow`,`dungeoncol`,`$room` FROM `Dungeons` WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;");
      $dungeonrow = mysql_fetch_array($dungeonresult);
      mysql_query("UPDATE `Dungeons` SET `$room` = '" . "CLEARED|" . mysql_real_escape_string($dungeonrow[$room]) . "' WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;");
      if (strpos($dungeonrow[$room], "LOOT|") !== false) { //there is loot to be looted
      	echo "<form action='dungeons.php#display' method='post'><input type='hidden' name='targetrow' value='$userrow[dungeonrow]'><input type='hidden' name='targetcol' value='$userrow[dungeoncol]'>";
      	echo "<input type='submit' value='Loot the room'></form>";
      }
    } elseif ($userrow['dungeonstrife'] == 3) { //Failure (dungeon guardian)
      mysql_query("UPDATE `Players` SET `indungeon` = 0 WHERE `Players`.`username` = '$username' LIMIT 1;");
			$userrow['indungeon'] = 0;
			header('location:/dungeons.php');
    } elseif ($userrow['dungeonstrife'] == 4) { //Victory (dungeon guardian)!
      echo "You enter the dungeon. The danger has only just begun...</br>";
    }
  }
  if (!empty($_POST['targetrow']) && !empty($_POST['targetcol']) && $userrow['dungeonstrife'] == 0) { //User is in a dungeon. Ignore movement attempts if user just returned from dungeon strife.
    if ($userrow['indungeon'] == 0) { //...or not.
      echo "You are not currently exploring a dungeon!</br>";
    } else {
      $row = $_POST['targetrow'];
      $col = $_POST['targetcol'];
      if ($row < 1 || $row > 10 || $col < 1 || $col > 10) {
	echo "That location is out of bounds.</br>";
      } else {
	$newroom = strval($row) . "," . strval($col);
	$dungeonresult = mysql_query("SELECT `$newroom`,`dungeongate`,`dungeonrow`,`dungeoncol`,`dungeonland` FROM `Dungeons` WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;"); //land needed if encounter appears
	$dungeonrow = mysql_fetch_array($dungeonresult);
	if ($dgnvision == 1) { //player has permanent "lens of truth" effect
		$dungeonresult = mysql_query("SELECT * FROM `Dungeons` WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;");
		$drow = mysql_fetch_array($dungeonresult);
		$dquery = "UPDATE `Dungeons` SET ";
		$updated = false;
		if ($userrow['dungeonrow'] > 1) {
			$squarename = strval($userrow['dungeonrow'] - 1) . "," . strval($userrow['dungeoncol']);
			if ($drow[$squarename] != "" && strpos($drow[$squarename], "VISITED") === false) {
				$drow[$squarename] .= "|VISITED";
				$updated = true;
				$dquery .= "`$squarename` = '" . $drow[$squarename] . "', ";
			}
		}
		if ($userrow['dungeonrow'] < 10) {
			$squarename = strval($userrow['dungeonrow'] + 1) . "," . strval($userrow['dungeoncol']);
			if ($drow[$squarename] != "" && strpos($drow[$squarename], "VISITED") === false) {
				$drow[$squarename] .= "|VISITED";
				$updated = true;
				$dquery .= "`$squarename` = '" . $drow[$squarename] . "', ";
			}
		}
		if ($userrow['dungeoncol'] > 1) {
			$squarename = strval($userrow['dungeonrow']) . "," . strval($userrow['dungeoncol'] - 1);
			if ($drow[$squarename] != "" && strpos($drow[$squarename], "VISITED") === false) {
				$drow[$squarename] .= "|VISITED";
				$updated = true;
				$dquery .= "`$squarename` = '" . $drow[$squarename] . "', ";
			}
		}
		if ($userrow['dungeoncol'] < 10) {
			$squarename = strval($userrow['dungeonrow']) . "," . strval($userrow['dungeoncol'] + 1);
			if ($drow[$squarename] != "" && strpos($drow[$squarename], "VISITED") === false) {
				$drow[$squarename] .= "|VISITED";
				$updated = true;
				$dquery .= "`$squarename` = '" . $drow[$squarename] . "', ";
			}
		}
		if ($updated) {
			$dquery = substr($dquery, 0, -2);
			$dquery .= " WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;";
			mysql_query($dquery);
		}
	}
	$ourgate = $dungeonrow['dungeongate']; //seems pointless but this is a very important step trust me
	$oldroom = strval($userrow['dungeonrow']) . "," . strval($userrow['dungeoncol']);
	$previous = "";
	$newflags = "";
	$connection = False; //This will be set to true when a connection to the previous room is found.
	$encounterslain = False;
	$alreadyencountered = False;
	$encounter = False;
	$clearencounter = False;
	$failedencounter = False;
	if ($newroom == $oldroom) $connection = True; //Rooms are connected to themselves automatically.
	$encounter = False; //This is set to true if an encounter is...er, encountered.
	$flags = explode("|", $dungeonrow[$newroom]);
	$i = 0;
	while (!empty($flags[$i])) {
	  $flag = $flags[$i];
	  switch ($flag) {
	  case 'CLEARED':
	    $clearencounter = True;
	    $flag = ""; //Disappears after use.
	    break; //This has no arguments. Must appear before encounter to be cleared.
	  case 'ENCOUNTER':
	    $previous = $flag;
	    if ($encounter) {
	      $alreadyencountered = True; //There's already an encounter loaded in.
	    } else {
	      $encounter = True;
	      if ($clearencounter) {
		$encounter = False; //Encounter not actually set off
		$clearencounter = False;
		$encounterslain = True;
		$flag = ""; //Scrap the encounter.
	      } elseif ($encounterslain == True) { //Last encounter was defeated. This one has not been yet.
		$encounterslain = False;
	      }
	      if ($encounter) { //Encounter being initiated at this stage.
		if ($userrow['encounters'] > 0 && $userrow[$downstr] == 0) {
		  $encounterargs = array();
		  mysql_query("UPDATE `Players` SET `combatmotifuses` = " . strval(floor($userrow['Echeladder'] / 100) + $userrow['Godtier']) . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		  mysql_query("UPDATE `Players` SET `strifemessage` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Empty combat messages.
		  chargeEncounters($userrow, 1, 1);
		} else {
		  echo "There are enemies in this room, but you do not have any encounters remaining or you're still down. You are therefore unable to fight them, and are forced to turn back.</br>";
		  $row = $userrow['dungeonrow'];
		  $col = $userrow['dungeoncol'];
		  echo "<form action='dungeons.php#display' method='post'>";
		  echo "<input type='submit' value='Go back'></form>"; //Form does nothing, since player has already been moved back once they click.
		  $failedencounter = True;
		}
	      }
	    }
	    break;
	  case 'TRAP':
	    $previous = $flag;
	    break;
	  case 'LOOT':
	    $previous = $flag;
	    if (!$encounter) $flag = ""; //Scrap the loot flag since it's going to be collected.
	    break;
	  case 'PUZZLE':
	    $previous = $flag;
	    break;
	  case 'VISITED':
	    break;
	  case 'ENTRANCE':
	    break;
	  case "": //Paranoia: If we get an empty entry somehow, just ignore it.
	    break; 
	    //Many flags have arguments after them. The default part processes assuming that what it receives is an argument for the previous non-argument flag. For instance:
	    //LOOT|Boondollars:500|Build_Grist:750|ITEM:Starman|ENCOUNTER|ENEMY1:Imp|TIER1:9|ENEMY2:Ogre|TIER2:3 will do the following:
	    //Place loot of 500 Boondollars, 750 Build Grist, and a starman in the room,
	    //and place an encounter consisting of a tier 9 imp and a tier 3 ogre with grist type according to the Land. If a room is absconded from:
	    //The code will save all enemies the player is currently strifing and place them into the string with additional syntax:
	    //ENCOUNTER|ENEMY1:SPECIFIC|NAME1:Rainbow Imp|HEALTH1:500|POWER1:92|CATEGORY1:Amber|DESC1:<description> will produce an imp with the specified qualities (thus preserving prototyping).
	    //Note that we don't need tier: we save the enemy directly and if we don't meet a standard enemy string (IMP, OGRE, BASILISK, etc) we assume a specific opponent.
	    //If tier is missing without a specific tag, we assume a gristless enemy.
	    //There are a few specific encounter flags: |BOSS, |NOASSIST, |BUFFSTRIP, and |CANTABSCOND. |BOSS applies all of the last three.
	    //Note that loot tags BEFORE encounter tags mean the player obtains the loot before fighting, and loot tags AFTER encounter tags mean the player obtains the loot afterward.
	    //The above is true in general: Things "occur" in the order they are parsed, with some events blocking others if they appear before them.
	    //DIRECT_SAVE must have an argument, it will be checked for emptiness to see if it's a direct thing.
	    //BOSS:TRUE is another special flag, marking the enemy as the dungeon boss.
	    //LINK is a special case. A single flag of |LINK:2,3 links the room to room 2,3. IMPORTANT: ALL LINKS MUST BE BEFORE ALL OTHER CONTENT.
	    //IF THE CONFIRMING LINK IS AFTER ANY ROOM CONTENT, THAT ROOM CONTENT WILL BE IGNORED AS THE PARSER THINKS YOU GOT TO THE ROOM ILLEGALLY AT THAT POINT.
	    //Some flags like ENTRANCE and VISITED do not affect what happens when we enter the room. These are set to not override the "previous" thing and to basically skip everything
	    //as they are not used during parsing.
	  default:
	    $argument = explode(":", $flag); //$argument[0] is the thing, $argument[1] is the value of the thing. There is potential for more arguments.
	    if ($argument[0] == "LINK") { //It's a link, check it.
	      if ($argument[1] == $oldroom) {
	      	if (empty($argument[2])) $connection = True; //This room is indeed connected to the other one.
	      	elseif (!empty($argument[2]) && !empty($_POST['dooritem'])) {
	      		$doorresult = mysql_query("SELECT * FROM `Dungeon_Doors` WHERE `Dungeon_Doors`.`ID` = $flags[2]"); //look up the door
			$drow = mysql_fetch_array($doorresult);
			if (!empty($drow['ID'])) {
				$itemname = str_replace("'", "\\\\''", $userrow[$_POST['dooritem']]);
				$itemresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `Captchalogue`.`name` = '$itemname' LIMIT 1"); //look up the item used
				$irow = mysql_fetch_array($itemresult);
				if (!empty($irow['name'])) {
					$keys = explode("|", $drow['keys']);
					$keyn = count($keys);
					$k = 0;
					while ($k <= $keyn) {
						if ($irow['name'] == $keys[$k]) { //the item used is one of the keys required
							$connection = true;
							$k = $keyn;
							echo "You successfully unlock the door with the key and pass through it.</br>";
						} else $k++;
					}
					if (!$connection) { //this wasn't the key, so let's see if we can break the door down
						if (strpos($irow['abstratus'], "explosivekind") === false) { //only explosives can use their full power no matter what
							$irow['power'] = $irow['power'] / 2; //effective power of weapon is halved
							if ($irow['size'] == "average") $irow['power'] = $irow['power'] / 2; //halved again if the item is average-sized
							if ($irow['size'] == "small") $irow['power'] = $irow['power'] / 4; //cut to 1/4 if the weapon is small
							$i = 1;
  							$absmatch = matchesAbstratus($userrow['abstratus1'], $irow['abstratus']);
  							if (!$absmatch) $irow['power'] = $irow['power'] / 10; //the user has no idea how to use this, so they take a significant penalty
						}
						if ($irow['power'] > $drow['strength']) {
							$connection = true;
							echo "You succeed at breaking down the door! You are able to pass through it.</br>";
						}
					}
					if ($connection) { //one way or another, the door is open.
						$flag = "LINK:" . $oldroom; //remove the door from the link, it is gone for good.
					} else echo "Try as you might, you are unable to get the door open with that item.<br />";
				} else echo "ERROR: The item you selected could not be found.<br />";
			} else {
			  echo "Upon closer inspection, that door does not actually exist, so you are able to enter the next room with no problems.<br />";
			  $connection = true;
			  $flag = "LINK:" . $oldroom; //remove the door from the link, it is gone for good.
			}
	      	} else {
	    		echo "A locked door blocks your path to the $blockstr.</br>";
			$doorresult = mysql_query("SELECT * FROM `Dungeon_Doors` WHERE `Dungeon_Doors`.`ID` = $flag[2]");
			$drow = mysql_fetch_array($doorresult);
			if (!empty($drow['ID'])) {
				echo $drow['description'] . "</br>";
				echo '<form action="dungeons.php#display" method="post"> Select an item to use on the door: <select name="dooritem">';
				$citem = 1;
				if (empty($max_items)) $max_items = 50;
				while ($citem <= $max_items) {
					$invstring = 'inv' . strval($citem);
					if (!empty($userrow[$invstring])) echo '<option value="' . $invstring . '">' . $userrow[$invstring] . '</option>';
					$citem++;
				}
				echo '</select><input type="hidden" name="targetrow" value="' . strval($coords[0]) . '"><input type="hidden" name="targetcol" value="' . strval($coords[1]) . '"><input type="submit" value="Try it!"></form>';
			} else {
			  echo "ERROR: Unknown door ID $flag[2]. Please submit a bug report, but for now, you may pass through this area.</br>";
			  logDebugMessage($username . " - dungeon generated a door with ID $flag[2] which doesn't exist");
			  echo '<form action="dungeons.php#display" method="post"><input type="hidden" name="targetrow" value="' . strval($coords[0]) . '"><input type="hidden" name="targetcol" value="' . strval($coords[1]) . '"><input type="hidden" name="dooritem" value="inv1"><input type="submit" value="Advance"></form>';
			}
	      	}
	      }
	    } elseif ($argument[0] == "QUESTGOAL") {
	    	if (!$encounter && $argument[1] == $userrow['currentquest']) {
	    		if (!empty($argument[2])) echo $argument[2];
	    		else echo "You have completed the quest!";
					echo "<br /><form action='consortquests.php' method='post'><input type='hidden' name='turnindungeonquest' value='yes' /><input type='submit' value='Claim the quest reward!' /></form><br />";
					$isquestcomplete = true;
				}
	    } elseif ($connection) { //Connection confirmed: do shit. Note that if this is never set to true, nothing ever happens.
	      switch ($previous) {
	      case 'ENCOUNTER':
		if ($encounterslain) {
		  $flag = ""; //Scrap this encounter; it was slain!
		} elseif ($alreadyencountered || $failedencounter) {
		  //Do nothing. Do not touch the encounter array, it already has an encounter loaded. Or we failed at encountering in which case it doesn't exist anyway.
		} else {
		  $encounterargs[$argument[0]] = $argument[1];
		}
		break;
	      case 'TRAP':
		switch ($argument[0]) {
		  //Add a case for each type of trap that exists.
		default:
		  break;
		}
	      case 'LOOT':
		if (!$encounter) { //No encounter before this loot.
		  if ($argument[0] == "ITEM") { //Loot is an item.
		    //Add item to player's inventory.
		    $colonargs = 1;
		    $itemname = "";
		    while (!empty($argument[$colonargs])) {
		      if ($colonargs != 1) $itemname = $itemname . ":";
		      $itemname = $itemname . $argument[$colonargs];
		      $colonargs++;
		    }
		    $itemslot = addItem($itemname,$userrow);
		    if ($itemslot != "inv-1") $userrow[$itemslot] = $itemname;
		    $itemname = str_replace("\\", "", $itemname); //Remove escape characters. (addItem does this too, so we do the removal afterwards.
		    //require_once("includes/SQLconnect.php");
		    if ($itemslot != "inv-1") { //Give them the item and check to see if they got it. inv-1 is the failure return.
		    if ($itemname == "Soviet Russia") echo "In the room, " . $itemname . " x1 finds you!</br>";
		      else echo "You find " . $itemname . " x1 in the room!</br>";
		      $flag = ""; //Loot collected, blank the flag.
		    } else { //Failure.
		      echo "You see " . $itemname . " x1 in the room, but do not have room in your Sylladex to retrieve it.</br>";
		      $flag = "LOOT|$flag"; //Reinstate loot designation since, well, there's still loot. If multiple items cannot be collected this may result in redundant loot flags. Oh well.
		    }
		  } else { //Loot is a quantity (Boondollars, grist, even things like heals and aspect vial restoration eventually). Note that it must be properly spelled.
		    if ($argument[0] == "Boondollars" && (intval($argument[1]) % 1000000) == 0) { //Loot is boonbucks
		      $boonbux = ($argument[1] / 1000000); //Condition guarantees this will be an integer.
		      echo "You discover $boonbux Boonbucks in a chest in the room!</br>";
		    } else {
		      echo "You loot $argument[1] $argument[0] from the room!</br>";
		    }
		    mysql_query("UPDATE `Players` SET `$argument[0]` = " . strval($userrow[$argument[0]]+$argument[1]) . " WHERE `Players`.`username` = '$username' LIMIT 1;");
		    //Increment the quantity here. $argument[0] is the quantity to be incremented.
		    $flag = ""; //Loot collected, blank the flag.
		  }
		}
		break;
	      case 'PUZZLE':
		switch ($argument[0]) {
		  //Add a case for each puzzle. Not quite sure how puzzling will be handled at this stage if at all.
		default:
		  break;
		}
		break;
	      case 'DESCRIPTION':
		echo $argument[1] . "</br>";
		break;
		case 'STAIRS': //don't need to deal with these here
			break;
	      default:
	      	if ($argument[0] != "STAIRS") {//really don't know why this is necessary, but it is
		echo "ERROR: Flag expected for argument $argument[0].</br>";
		logDebugMessage($username . " - dungeon expects flag for argument $argument[0], apparently failed to deliver");
	      	}
		break;
	      }
	    }
	    break;
	  }
	  if ($flag != "" && $newflags != "") $flag = "|" . $flag; //If neither flag nor list of flags is empty, add the | back to the front of the flag.
	  $newflags = $newflags . $flag; //If flag is blanked or modified, $newflags reflects this. $newflags is then made the new flag list. Dur.
	  $i++;
	}
	if ($connection && !strpos($newflags,"VISITED")) {
		$newflags = $newflags . "|VISITED"; //Mark this location as having been visited, because it was. Don't double up though.
		if (strpos($newflags,"STAIRS") !== false) { //there are stairs on this tile that we haven't visited before.
			//Note that this shouldn't repeat when the user ascends because the stairs on the next floor are already marked 'visited'.
			echo 'You reach these stairs for the first time and, realizing there\'s at least one more floor to this dungeon, you decide to take a quick break. You recover 3 encounters.<br />';
			$encounters = 100 - $userrow['encounters'];
			if ($encounters > 3) $encounters = 3;
			mysql_query("UPDATE Players SET encounters = $userrow[encounters]+$encounters WHERE Players.username = '$username'");
		}
	}
	//ABOVE: Note that that function will not evaluate to 0 under any circumstances since there needs to either be a link in or the tile needs to be the entrance.
	if (!empty($encounterargs)) { //Enemies in this room. Generate 'em!
	  mysql_query("UPDATE `Players` SET `dungeonstrife` = 2 WHERE `Players`.`username` = '$username' LIMIT 1;"); //This is set to 1 by striferesolve if the player fails.
	  //see if there are any players in the same room who are also engaging enemies, and select only the one who is the main strifer
	  $allyquery = mysql_query("SELECT `username` FROM `Players` WHERE `Players`.`currentdungeon` = '$dgnstring' AND `Players`.`dungeonrow` = $row AND `Players`.`dungeoncol` = $col AND `Players`.`aiding` = '' AND `Players`.`indungeon` = 1");
	  $allyrow = mysql_fetch_array($allyquery);
	  if (!empty($allyrow['username'])) { //found someone!
	    echo "You enter the room to find " . $allyrow['username'] . " engaging in strife!<br />";
	    mysql_query("UPDATE `Players` SET `aiding` = '" . $allyrow['username'] . "' WHERE `Players`.`username` = '$username' LIMIT 1;"); //help them out
	  } else {
	  echo "As you examine the room, you are ";
	  $random = rand(1,10);
	  if ($ourgate != 1 && $ourgate != 3 && $ourgate != 5) $random = 0; //always produce the bugged result if the dungeon is bugged :L
	  switch ($random) { //Let's produce a random verb!
	  case 1:
	    echo "assailed";
	    break;
	  case 2:
	    echo "attacked";
	    break;
	  case 3:
	    echo "assaulted";
	    break;
	  case 4:
	    echo "approached";
	    break;
	  case 5:
	    echo "appraised";
	    break;
	  case 6:
	    echo "aggressed";
	    break;
	  case 7:
	    echo "angered";
	    break;
	  case 8:
	    echo "aggrieved";
	    break;
	  case 9:
	    echo "abused";
	    break;
	  case 10:
	    echo "arraigned";
	    break;
	  default:
	    echo "flim-flammed"; 
	    break;
	  }
	  echo " by enemies!</br>";
	  $i = 1;
	  while ($i <= $max_enemies) {
	    $enemyflag = "ENEMY" . strval($i);
	    if (!empty($encounterargs[$enemyflag])) { //Enemy at this location.
	      if ($encounterargs[$enemyflag] == "SPECIFIC") {
		$nameflag = "NAME" . strval($i);
		$powerflag = "POWER" . strval($i);
		$healthflag = "HEALTH" . strval($i);
		$descflag = "DESC" . strval($i);
		$categoryflag = "CATEGORY" . strval($i);
		$namestr = "enemy" . strval($i) . "name";
		$powerstr = "enemy" . strval($i) . "power";
		$maxpowerstr = "enemy" . strval($i) . "maxpower";
		$healthstr = "enemy" . strval($i) . "health";
		$maxhealthstr = "enemy" . strval($i) . "maxhealth";
		$descstr = "enemy" . strval($i) . "desc";
		$categorystr = "enemy" . strval($i) . "category";
		mysql_query("UPDATE `Players` SET `" . $namestr . "` = '" . mysql_real_escape_string($encounterargs[$nameflag]) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		mysql_query("UPDATE `Players` SET `" . $powerstr . "` = '" . strval($encounterargs[$powerflag]) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		mysql_query("UPDATE `Players` SET `" . $maxpowerstr . "` = '" . strval($encounterargs[$powerflag]) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		mysql_query("UPDATE `Players` SET `" . $healthstr . "` = '" . strval($encounterargs[$healthflag]) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		mysql_query("UPDATE `Players` SET `" . $maxhealthstr . "` = '" . strval($encounterargs[$healthflag]) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		mysql_query("UPDATE `Players` SET `" . $descstr . "` = '" . mysql_real_escape_string($encounterargs[$descflag]) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
		mysql_query("UPDATE `Players` SET `" . $categorystr . "` = '" . $encounterargs[$categoryflag] . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	      } else {
		if (!empty($encounterargs["TIER" . strval($i)])) $tier = intval($encounterargs["TIER" . strval($i)]);
		if (!empty($tier)) { //Grist enemy.
		  $gristtype = mysql_query("SELECT `grist_type` FROM `Players` WHERE `Players`.`username` = '$dungeonrow[dungeonland]' LIMIT 1;"); //Pull grist type for this dungeon's Land.
		  $gristrow = mysql_fetch_array($gristtype);
		  $gristtype = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '$gristrow[grist_type]' LIMIT 1;");
		  $typerow = mysql_fetch_array($gristtype);
		  $griststr = "grist" . strval($tier); //Pull the correct tier of grist.
		  $grist = $typerow[$griststr];
		} else { //Gristless enemy.
		  $gristrow = array("grist_type" => "None");
		  $grist = "None";
		}
		//Code to blank grist type if enemy not a grist enemy will go here.
		$monsterpower = generateEnemy($userrow,$gristrow['grist_type'],$grist,$encounterargs[$enemyflag],True); //Make the enemy and assign them to combat.
		$userrow = refreshEnemydata($userrow);
		if ($monsterpower != -1) { //Success!
		  if (!empty($tier)) {
		    echo $grist . " " . $encounterargs[$enemyflag];
		  } else {
		    echo $encounterargs[$enemyflag];
		  }
		  echo "</br>";
		}
	      }
	    }
	    $i++;
	  }
	  }
	  if (!empty($encounterargs['BOSS'])) { //BOSS BATTLE
	    echo '<a href="http://homestuck.bandcamp.com/track/cascade" target="_blank">Music befitting an epic struggle</a> begins playing.</br>';
	    mysql_query("UPDATE `Players` SET `noassist` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    mysql_query("UPDATE `Players` SET `cantabscond` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    mysql_query("UPDATE `Players` SET `buffstrip` = 1 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    mysql_query("UPDATE `Players` SET `powerboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Power boosts wear off.
	    mysql_query("UPDATE `Players` SET `offenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    mysql_query("UPDATE `Players` SET `defenseboost` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	    mysql_query("UPDATE `Players` SET `bossbegintime` = " . strval(time()) . " WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
	  }
	  echo '<a href="strife.php">==&gt;</a></br>';
	}
	if ($connection) {
	  mysql_query("UPDATE `Dungeons` SET `$newroom` = '" . mysql_real_escape_string($newflags) . "' WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;"); //Set the flags for this room on entry. 
	  //Note that "entry" may mean performing an action in the room (i.e. "entering" the room from itself) at some stage.
	  mysql_query("UPDATE `Players` SET `olddungeonrow` = $userrow[dungeonrow] WHERE `Players`.`username` = '$username' LIMIT 1;"); //Save these for things like fleeing the room.
	  mysql_query("UPDATE `Players` SET `olddungeoncol` = $userrow[dungeoncol] WHERE `Players`.`username` = '$username' LIMIT 1;");
	  mysql_query("UPDATE `Players` SET `dungeonrow` = $row WHERE `Players`.`username` = '$username' LIMIT 1;");
	  mysql_query("UPDATE `Players` SET `dungeoncol` = $col WHERE `Players`.`username` = '$username' LIMIT 1;");
	  $userrow['olddungeonrow'] = $userrow['dungeonrow'];
	  $userrow['olddungeoncol'] = $userrow['dungeoncol'];
	  $userrow['dungeonrow'] = $row;
	  $userrow['dungeoncol'] = $col;
	} else {
	  echo "The room you just tried to enter is not available from the one you were trying to leave.</br>";
	}
      }
    }
  }
  if (empty($failedencounter)) $failedencounter = False;
  if ($userrow['indungeon'] == 0) { //Select a dungeon to go to. NOTE: Form submits string formatted like username:gate, with the username corresponding to the Land the dungeon is on.
    if (!empty($dungeongen)) { //Just generated a dungeon.
      //header('location:/dungeons.php'); //Reload the page now that we're in a dungeon.
      if (!empty($_POST['joinmate'])) {
      	echo "You shortly arrive at the dungeon that your ally discovered. The dungeon guardian has already been taken care of, so you stroll right through the front gate!<br />";
      	echo "<a href='dungeons.php'>==&gt;</a>";
      } else {
      	if (!empty($trow['guardian'])) {
      		$guardian = $trow['guardian'];
      		$gtier = $trow['gtier'];
      	} else {
      switch ($gate) {
      	case 1:
      		$guardian = "Basilisk";
      		$gtier = 1;
      		break;
      	case 3:
      		$guardian = "Giclops";
      		$gtier = 1;
      		break;
      	case 5:
      		$guardian = "Acheron";
      		$gtier = 2;
      		break;
      	default: //bugged "somehow"
      		$guardian = "Acheron";
      		$gtier = 9;
      		break;
      }
      }
      if ($guardian == "NONE") {
      	echo 'You find yourself at the entrance to a dungeon. It is unguarded, so you are able to enter with little difficulty.<br />';
      	echo "<a href='dungeons.php'>==&gt;</a>";
      } else {
      	if ($gtier != 0) { //guardian is tiered
      		$gristtype = mysql_query("SELECT `grist_type` FROM `Players` WHERE `Players`.`username` = '$land' LIMIT 1;"); //Pull grist type for this dungeon's Land.
		  		$gristrow = mysql_fetch_array($gristtype);
		  		$gristtype = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '$gristrow[grist_type]' LIMIT 1;");
		  		$typerow = mysql_fetch_array($gristtype);
		  		$griststr = "grist" . strval($gtier); //Pull the correct tier of grist.
		  		$grist = $typerow[$griststr];
      	} else {
      		$gristrow['grist_type'] = "None";
      		$grist = "None";
      	}
		  	$monsterpower = generateEnemy($userrow,$gristrow['grist_type'],$grist,$guardian,True);
		  	$userrow = refreshEnemydata($userrow);
		  	mysql_query("UPDATE `Players` SET `dungeonstrife` = 4 WHERE `Players`.`username` = '$username' LIMIT 1;"); //This is set to 3 by striferesolve if the player fails.
      	echo 'You find yourself at the entrance to a dungeon. An underling stands before it, likely tasked with keeping out thieves who might steal the treasures within.</br>';
	      echo '<a href="strife.php">The underling notices you and initiates strife!</a></br>';
      	mysql_query("UPDATE `Players` SET `strifemessage` = '' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Empty combat messages.
      }
      }
    } else {
      echo "You are not currently exploring a dungeon.</br>";
      $gateresult = mysql_query("SELECT * FROM Gates");
      $gaterow = mysql_fetch_array($gateresult); //Gates only has one row.
      $currentrow = $userrow;
      $fly = canFly($userrow);
      $done = False;
      echo '<form action="dungeons.php#display" method="post"><select name="newdungeon">';
      while (!$done) {
	$gates = 0;
	$i = 1;
	while ($i <= 7) {
	  $gatestr = "gate" . strval($i);
	  if ($gaterow[$gatestr] <= $currentrow['house_build_grist'] || $fly) {
	    if ($i == 1 || $i == 3 || $i == 5) {
	      $locationstr = "Land of " . $currentrow['land1'] . " and " . $currentrow['land2'] . " - Gate " . strval($i);
	      echo '<option value="' . $currentrow['username'] . ":" . strval($i) . '">' . $locationstr . '</option>';
	    }
	    $gates++;
	  } else {
	    $i = 7; //We are done.
	  }
	  $i++;
	}
	//Code to add options to the dropdown for each relevant gate goes here. Will also need to set player's gate total in an array and conclude if any player doesn't have at least their second gate.
	//Basically, checking on gate access here.
	if (!empty($currentrow['server_player']) && $currentrow['server_player'] != $username && empty($usedname[$currentrow['username']])) {
	  $usedname[$currentrow['username']] = true;
	  $currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '$currentrow[server_player]';");
	  $currentrow = mysql_fetch_array($currentresult);
	  if ($currentrow['house_build_grist'] < $gaterow["gate2"] && !$fly) $done = True; //This house is unreachable. Chain is broken here.
	} else { //Player has no server, gates go nowhere. This is not canonical behaviour, but canonical behaviour is impossible since it relies on prediction. Alternatively, loop is complete.
	  //Note that if gate 1 has not been reached, then gate 2 wasn't either and the Land was never accessed in the first place!
	  //Note also that if we reach anywhere we've already been, this triggers
	  $done = True; //No further steps.
	}
      }
      if (strpos($userrow['storeditems'], "GLITCHGATE.") !== false) echo '<option value="' . $username . ':6">Unknown Gate</option>';
      echo '</select> <input type="submit" value="Explore a dungeon at this location (cost: 3 encounters)" /> </form>';
      $materesult = mysql_query("SELECT `username`,`currentdungeon` FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "' AND `Players`.`indungeon` = 1");
      $firstone = true;
      $chain = chainArray($userrow);
      $totalchain = count($chain);
      while ($materow = mysql_fetch_array($materesult)) {
      	$dgnresult = mysql_query("SELECT `dungeonland` FROM `Dungeons` WHERE `username` = '" . $materow['currentdungeon'] . "'");
      	$dgnrow = mysql_fetch_array($dgnresult);
        $landcount = 0;
        $aok = false;
        while ($landcount < $totalchain && !$aok) { //check to see if the user can reach the land this dungeon is on
        	if ($dgnrow['dungeonland'] == $chain[$landcount]) $aok = true;
	  			$landcount++;
        }
        if ($aok) {
        	if ($firstone) {
          	echo '<br /><form action="dungeons.php#display" method="post">At least one of your sessionmates are currently dungeon diving:<br /><select name="joinmate">';
          	$firstone = false;
        	}	
	        $start = 0;
        	$abort = strlen($materow['currentdungeon']) * -1;
        	$ochar = "x";
        	while ($ochar != "_" && $start > $abort) {
          	$start--;
          	$ochar = substr($materow['currentdungeon'], $start, 1);
        	}
      	  if ($start > $abort) { //dungeon is numbered
    	      $thisdungeon = substr_replace($materow['currentdungeon'], "_1", $start); //only make the first floor available
  	      } else { //dungeon is not numbered, allow user to join same floor their mate is on
	          $thisdungeon = $materow['currentdungeon'];
        	}
        	echo '<option value="' . $thisdungeon . '">' . $materow['username'] . '</option>';
        }
      }
      if (!$firstone) echo '</select><input type="submit" value="Join them" /></form>';
    }
  } else { //User already inside a dungeon.
    $dungeonresult = mysql_query("SELECT * FROM `Dungeons` WHERE `Dungeons`.`username` = '$dgnstring' LIMIT 1;"); //Need to display whole dungeon.
    $dungeonrow = mysql_fetch_array($dungeonresult);
    $row = $userrow['dungeonrow'];
    $col = $userrow['dungeoncol'];
    $currentroom = strval($row) . ',' . strval($col);
    if (empty($dungeonrow['username'])) {
    	echo "ERROR: Dungeon not found! <a href='dungeons.php?emergency=escape'>Click here to escape from limbo.</a>";
    	logDebugMessage($username . " - found themselves in a dungeon that doesn't exist: $dgnstring");
    } elseif (empty($dungeonrow[$currentroom])) {
    	echo "ERROR: You seem to have glitched out of bounds! <a href='dungeons.php?emergency=escape'>Click here to escape from limbo.</a>";
    	logDebugMessage($username . " - found themselves trapped in an empty room: $dgnstring ($currentroom)");
    }
    $i = $dungeonrows;
    $j = 1;
    //Begin code to find out which tiles are adjacent to visited ones here. (As well as checking which rooms need transportalizer symbols)
    $adjacentarray = array();
    $transportarray = array();
    while ($i >= 1) {
      while ($j <= $dungeoncols) {
	$room = strval($i) . "," . strval($j);
	if (strpos($dungeonrow[$room],"VISITED") !== False) {
	  $flags = explode("|", $dungeonrow[$room]);
	  $k = 0;
	  while (!empty($flags[$k])) {
	    $flag = explode(":", $flags[$k]);
	    switch ($flag[0]) {
	    case "LINK":
	      $coords = explode(",", $flag[1]); //coords[0] is the x coord, 1 is the y.
	      if ($coords[0] > $i + 1 || $coords[0] < $i - 1 || $coords[1] > $j + 1 || $coords[1] < $j - 1 || ($coords[0] != $i && $coords[1] != $j)) { //Not adjacent. Room has a transportalizer.
		$transportarray[$flag[1]] = True;
		$transportarray[$room] = True;
	      }
	      $adjacentarray[$flag[1]] = True;
	      break;
	    default:
	      //We only care about links here!
	      break;
	    }
	    $k++;
	  }
	}
	$j++;
      }
      $j = 1;
      $i--;
    }
    //End code to check adjacency here. (Note that adjacency includes transportalization)
    $playerarray = array();
    $symbolarray = array();
    $allyquery = mysql_query("SELECT `username`,`symbol`,`indungeon`,`dungeonrow`,`dungeoncol` FROM `Players` WHERE `Players`.`currentdungeon` = '$dgnstring'");
    while ($allyrow = mysql_fetch_array($allyquery)) { //found someone!
      if ($allyrow['username'] != $username && $allyrow['indungeon'] == 1) { //somebody who isn't you and is actually in a dungeon
        $thisroom = strval($allyrow['dungeonrow']) . "," . strval($allyrow['dungeoncol']);
        $playerarray[$thisroom] = $allyrow['username'];
        $symbolarray[$thisroom] = $allyrow['symbol'];
      }
    }
    //May add coordinate tiles around the edges. In this case, both i and j will go down to 0 as coordinate tiles are placed.
    $i = $dungeonrows;
    $j = 1;
    $onentrance = False;
    $borderstr = "1px solid black;";
    echo '<table cellspacing="0" cellpadding="0">';
    while ($i >= 1) {
      echo '<tr>';
      while ($j <= $dungeoncols) {
	$room = strval($i) . "," . strval($j);
	echo '<td style="width:64;height:64;line-height:0px;';
	if ((strpos($dungeonrow[$room],("LINK:" . strval($i) . "," . strval($j-1))) === False)) { //Rooms not connected.
	  echo 'border-left:' . $borderstr;
	} elseif ((strpos($dungeonrow[$room],"VISITED")) === False && (strpos($dungeonrow[(strval($i) . "," . strval($j-1))],"VISITED")) === False && $dgnvision != 0) { //Neither room seen.
	  echo 'border-left:' . $borderstr;
	}
	if ((strpos($dungeonrow[$room],("LINK:" . strval($i-1) . "," . strval($j))) === False)) { //Rooms not connected.
	  echo 'border-bottom:' . $borderstr;
	} elseif ((strpos($dungeonrow[$room],"VISITED")) === False && (strpos($dungeonrow[(strval($i-1) . "," . strval($j))],"VISITED")) === False && $dgnvision != 0) { //Neither room seen.
	  echo 'border-bottom:' . $borderstr;
	} 
	if ((strpos($dungeonrow[$room],("LINK:" . strval($i) . "," . strval($j+1))) === False)) { //Rooms not connected.
	  echo 'border-right:' . $borderstr;
	} elseif ((strpos($dungeonrow[$room],"VISITED")) === False && (strpos($dungeonrow[(strval($i) . "," . strval($j+1))],"VISITED")) === False && $dgnvision != 0) { //Neither room seen.
	  echo 'border-right:' . $borderstr;
	} 
	if ((strpos($dungeonrow[$room],("LINK:" . strval($i+1) . "," . strval($j))) === False)) { //Rooms not connected.
	  echo 'border-top:' . $borderstr;
	} elseif ((strpos($dungeonrow[$room],"VISITED")) === False && (strpos($dungeonrow[(strval($i+1) . "," . strval($j))],"VISITED")) === False && $dgnvision != 0) { //Neither room seen.
	  echo 'border-top:' . $borderstr;
	} 
	//player location will be handled differently below
	if (strpos($dungeonrow[$room],"ENTRANCE") !== False && strpos($dungeonrow[$room],"BOSS") === false) { //This is the entrance tile, and there isn't an undefeated boss on it.
	  echo 'background-image:url(/Images/Dungeontiles/entrancetile.png);';
	  if ($room == $currentroom) $onentrance = true;
	} elseif (strpos($dungeonrow[$room],"VISITED") !== False || ($dgnvision == 0 && !empty($dungeonrow[$room]))) { //Tile visited. We have information about it.
	  if (strpos($dungeonrow[$room],"STAIRS") !== False) { //This is the stairs.
	    echo 'background-image:url(/Images/Dungeontiles/stairtile.png);';
	  } elseif (!empty($transportarray[$room])) { //Transportalizer in room.
	    if (strpos($dungeonrow[$room],"ENCOUNTER") !== False && strpos($dungeonrow[$room],"CLEARED") === False) {
	      if (strpos($dungeonrow[$room],"BOSS") !== False) { //This is a boss tile (Probably unique).
		echo 'background-image:url(/Images/Dungeontiles/bosstransport.png);';
	      } elseif (strpos($dungeonrow[$room],"LOOT") !== False) {
		echo 'background-image:url(/Images/Dungeontiles/enemyloottransport.png);';
	      } else {
		echo 'background-image:url(/Images/Dungeontiles/enemytransport.png);';
	      }
	    } elseif (strpos($dungeonrow[$room],"LOOT") !== False) {
	      echo 'background-image:url(/Images/Dungeontiles/loottransport.png);';
	    } else {
	      echo 'background-image:url(/Images/Dungeontiles/transporttile.png);';
	    }
	  } else { //No transportalizer
	    if (strpos($dungeonrow[$room],"ENCOUNTER") !== False && strpos($dungeonrow[$room],"CLEARED") === False) {
	      if (strpos($dungeonrow[$room],"BOSS") !== False) { //This is a boss tile (Probably unique).
		echo 'background-image:url(/Images/Dungeontiles/bosstile.png);';
	      } elseif (strpos($dungeonrow[$room],"LOOT") !== False) {
		echo 'background-image:url(/Images/Dungeontiles/enemyloot.png);';
	      } else {
		echo 'background-image:url(/Images/Dungeontiles/enemytile.png);';
	      }
	    } elseif (strpos($dungeonrow[$room],"LOOT") !== False) {
	      echo 'background-image:url(/Images/Dungeontiles/loottile.png);';
	    } else {
	      echo 'background-image:url(/Images/Dungeontiles/blanktile.png);';
	    }
	  }
	} elseif (!empty($adjacentarray[$room])) { //Tile not visited, but tile connected to tile has been visited.
	  if (!empty($transportarray[$room])) { //Transportalizer in room.
	    if (strpos($dungeonrow[$room],"BOSS") !== False) { //This is a boss tile (Probably unique).
	      echo 'background-image:url(/Images/Dungeontiles/bosstransport.png);';
	    } else {
	      echo 'background-image:url(/Images/Dungeontiles/unknowntransport.png);';
	    }
	  } else {
	    if (strpos($dungeonrow[$room],"BOSS") !== False) { //This is a boss tile (Probably unique).
	      echo 'background-image:url(/Images/Dungeontiles/bosstile.png);';
	    } else {
	      echo 'background-image:url(/Images/Dungeontiles/unknowntile.png);';
	    }
	  }
	} else { //Tile not visited.
	  echo 'background-image:url(/Images/Dungeontiles/emptyspace.png);';
	}
	echo 'background-repeat:no-repeat;">';
	if ($room == $currentroom) { //This is the current room.
	  echo "<img src='/Images/symbols/" . $userrow['symbol'] . "' title='You'>";
	} elseif (!empty($playerarray[$room])) { //somebody else is in this room
	  echo "<img src='/Images/symbols/" . $symbolarray[$room] . "' title='" . $playerarray[$room] . "'>";
	} else {
	  echo "<img src='/Images/symbols/nobody.png'>";
	}
	echo '</td>';
	$j++;
      }
      echo "</tr>";
      $j = 1; //Reset it again for the next actual loop
      $i--;
    }
    echo '</table>';
    //what follows USED to be where links were printed, but instead they set variables for links to be printed later
    if (!$failedencounter && empty($encounterargs)) { //Failed encounters and actual encounters disable movement.
      $flags = explode("|", $dungeonrow[$currentroom]);
      $i = 0;
      $transrows = 0;
      while (!empty($flags[$i])) {
	$flag = explode(":", $flags[$i]);
	switch ($flag[0]) {
	case "DESCRIPTION": //Note that descriptions must be placed before links. Otherwise the description gets printed in the middle of the buttons or below 'em.
	  $colons = 1;
	  while (!empty($flag[$colons])) { //We print every "argument" so that colons in the description work okay.
	    if ($colons > 1) echo ":"; //Add in the colon that was exploded out. Exploded colon. Ouch.
	    echo $flag[$colons];
	    $colons++;
	  }
	  echo "</br>";
	  break;
	case "STAIRS":
	  echo "<form action='dungeons.php#display' method='post'><input type='hidden' name='ascend' value='$flag[1]' /><input type='submit' value='Transportalize to the next floor'></form>";
	  //aaaand that's it! looks like you didn't have to warn me about the stairs after all dawg
	break;
	case "LINK":
	  $coords = explode(",", $flag[1]);
	  if ($coords[0] > $row + 1 || $coords[0] < $row - 1 || $coords[1] > $col + 1 || $coords[1] < $col - 1 || ($coords[0] != $row && $coords[1] != $col)) { //Not adjacent. This movement is done by transportalizer.
	    $transrows++;
	    $transrow[$transrows] = $coords[0];
	    $transcol[$transrows] = $coords[1];
	  } else { //Adjacent.
	  $blockstr = "";
	    if ($coords[0] == $row + 1) {
	    	if (empty($flag[2])) {
	    	  $northrow = $coords[0];
	    	  $northcol = $coords[1];
	    	} else $blockstr = "north";
	    }
	    if ($coords[0] == $row - 1) {
	    	if (empty($flag[2])) {
	    	  $southrow = $coords[0];
	    	  $southcol = $coords[1];
	    	} else $blockstr = "south";
	    }
	    if ($coords[1] == $col + 1) {
	    	if (empty($flag[2])) {
	    	  $eastrow = $coords[0];
	    	  $eastcol = $coords[1];
	    	} else $blockstr = "east";
	    }
	    if ($coords[1] == $col - 1) {
	    	if (empty($flag[2])) {
	    	  $westrow = $coords[0];
	    	  $westcol = $coords[1];
	    	} else $blockstr = "west";
	    }
	    //echo "</form>"; //NOTE - If link appears to self somehow, won't be printed because who cares. NOTE - Fix multiple buttons appearing on multilink.
	  }
	  	  if (!empty($blockstr)) {
	    		echo "A locked door blocks your path to the $blockstr.</br>";
					$doorresult = mysql_query("SELECT * FROM `Dungeon_Doors` WHERE `Dungeon_Doors`.`ID` = $flag[2]");
					$drow = mysql_fetch_array($doorresult);
					if (!empty($drow['ID'])) {
						echo $drow['description'] . "</br>";
						echo '<form action="dungeons.php#display" method="post"> Select an item to use on the door: <select name="dooritem">';
						$citem = 1;
						if (empty($max_items)) $max_items = 50;
						while ($citem <= $max_items) {
							$invstring = 'inv' . strval($citem);
							if (!empty($userrow[$invstring])) echo '<option value="' . $invstring . '">' . $userrow[$invstring] . '</option>';
							$citem++;
						}
						echo '</select><input type="hidden" name="targetrow" value="' . strval($coords[0]) . '"><input type="hidden" name="targetcol" value="' . strval($coords[1]) . '"><input type="submit" value="Try it!"></form>';
					} else echo "ERROR: Unknown door ID $flag[2]</br>";
	    	}
	break;
	default:
	  //We only care about links here!
	  break;
	}
	$i++;
      }
    }
    echo "</br>";
    if ($onentrance && empty($encounterargs)) {
      echo "</br>";
      if ($isquestcomplete) {
	echo "<form action='consortquests.php' method='post'><input type='hidden' name='turnindungeonquest' value='yes'><input type='submit' value='Claim the quest reward before leaving the dungeon!'></form>";
      } else {
	echo "<form action='dungeons.php#display' method='post'><input type='hidden' name='exitdungeon' value='yep'><input type='submit' value='Exit the dungeon (WARNING - ALL DUNGEON CONTENT WILL DISAPPEAR!)'></form>";
      }
    }
    echo "<div style='float:right;position:relative; top:-" . strval($dungeonrows * 64) . "px;'>";
    echo "<table style='width:64;height:64;line-height:0px;'><tr><td><img src='/Images/symbols/nobody.png'></td><td>";
    if (!empty($northrow)) {
      echo "<form action='dungeons.php#display' method='post'><input type='hidden' name='targetrow' value='$northrow'><input type='hidden' name='targetcol' value='$northcol'>";
      echo "<input type='image' src='/Images/Dungeontiles/dgnbtn_north.png' alt='North'></form>";
    } else echo "<img src='/Images/Dungeontiles/dgnbtn_north_blocked.png'>";
    echo "</td><td><img src='/Images/symbols/nobody.png'></td></tr><tr><td>";
    if (!empty($westrow)) {
      echo "<form action='dungeons.php#display' method='post'><input type='hidden' name='targetrow' value='$westrow'><input type='hidden' name='targetcol' value='$westcol'>";
      echo "<input type='image' src='/Images/Dungeontiles/dgnbtn_west.png' alt='West'></form>";
    } else echo "<img src='/Images/Dungeontiles/dgnbtn_west_blocked.png'>";
    echo "</td><td>";
    /*if (!empty($transrow)) {
      echo "<form action='dungeons.php#display' method='post'><input type='hidden' name='targetrow' value='$transrow'><input type='hidden' name='targetcol' value='$transcol'>";
      echo "<input type='submit' value='Transportalize!'></form>";
    } else*/ echo "<img src='/Images/symbols/nobody.png'>";
    echo "</td><td>";
    if (!empty($eastrow)) {
      echo "<form action='dungeons.php#display' method='post'><input type='hidden' name='targetrow' value='$eastrow'><input type='hidden' name='targetcol' value='$eastcol'>";
      echo "<input type='image' src='/Images/Dungeontiles/dgnbtn_east.png' alt='East'></form>";
    } else echo "<img src='/Images/Dungeontiles/dgnbtn_east_blocked.png'>";
    echo "</td></tr><tr><td><img src='/Images/symbols/nobody.png'></td><td>";
    if (!empty($southrow)) {
      echo "<form action='dungeons.php#display' method='post'><input type='hidden' name='targetrow' value='$southrow'><input type='hidden' name='targetcol' value='$southcol'>";
      echo "<input type='image' src='/Images/Dungeontiles/dgnbtn_south.png' alt='South'></form>";
    } else echo "<img src='/Images/Dungeontiles/dgnbtn_south_blocked.png'>";
    echo "</td><td><img src='/Images/symbols/nobody.png'></td></tr></table></br>";
    if ($transrows > 0) {
      $i = 1;
      while ($i <= $transrows) {
        echo "<form action='dungeons.php#display' method='post'><input type='hidden' name='targetrow' value='$transrow[$i]'><input type='hidden' name='targetcol' value='$transcol[$i]'>";
        echo "<input type='submit' value='Transportalize to " . strval($transrow[$i]) . "," . strval($transcol[$i]) . "'></form>";
        $i++;
      }
    }
    echo "</div>";
  }
}
require_once("footer.php");
?>