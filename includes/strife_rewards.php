<?php

$foundspace = False;
					$j = 0;
					while ($foundspace != True) {
						$char = substr($userrow[$enemystr],$j,1);
						if ($char == " ") { //Found the gap between grist type and enemy. Note that depending on enemy, these values may not be used.
							$grist = substr($userrow[$enemystr],0,$j);
							$enemytype = substr($userrow[$enemystr],($j+1));
							$foundspace = True;
						} elseif ($j > 18) { //Timeout: Doesn't need to be longer than the longest grist.
							$foundspace = True;
						} else {
							$j++;
						}
					}
							$gristrow = $_SESSION[$userrow[$categorystr]];
							$rarity = 1;
							$typestr = "grist" . strval($rarity);
							while ($gristrow[$typestr] != $grist && $rarity < 10) { //Nine types of grist.
								$rarity++;
								$typestr = "grist" . strval($rarity);
							}
						switch ($userrow[$enemystr]) { //Catches specical case enemies with special interactions that aren't "gristed"
						case "The Mother of All Hangovers":
							echo "Wow. You manage to somehow bludgeon your hangover into submission. Because that makes so much sense.</br>";
							break;
						case "Kraken":
							$bossdead = True;
							if (!empty($userrow['pesternoteUsername'])) sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "Just defeated a Kraken to clear a gate 1 dungeon!");
							echo "You have slain the Kraken! The boss door opens, allowing you to exit the dungeon. Also you get stuff: ";
							$buildgrist = 10500 + (rand(0,1) * 1000) + (rand(0,1) * 6000) + (floor(rand(0,2) / 2) * 20000) + (rand(0,$luck) * 90);
							echo "<img src='Images/Grist/".gristNameToImagePath("Build_Grist")."' height='15' width='15' alt = 'xcx'/> $buildgrist";
							$userrow['Build_Grist'] = $userrow['Build_Grist'] + $buildgrist;
							$gristlevel = 1;
							while ($gristlevel <= 3) {
								$gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
								$totalgrists = 0;
								while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
								$totalgrists--; //It counts from zero
								$typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
								$selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
								$selectedrow = mysql_fetch_array($selectedresult);
								$gristloot = 7000 + (rand(0,1) * 500) + (rand(0,1) * 3000) + (floor(rand(0,2) / 2) * 7500) + (rand(0,$luck) * 80);
								if ($gristlevel <= 9) { //Paranoia: Grist type exists.
									$typestr = "grist" . strval($gristlevel);
									$gristdroptype = $selectedrow[$typestr];
									if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
									$userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
								}
								$gristlevel++;
							}
							echo ".</br>";
							break;
						case "Hekatonchire":
							$bossdead = True;
							if (!empty($userrow['pesternoteUsername'])) sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "Just defeated a Hekatonchire to clear a gate 3 dungeon!");
							echo "You have slain the Hetako- no wait, Hekakon- no, Hekatonsh...Hekatom...look, just take the reward, okay? ";
							$buildgrist = 21000 + (rand(0,1) * 2000) + (rand(0,1) * 12000) + (floor(rand(0,2) / 2) * 40000) + (rand(0,$luck) * 180);
							echo "<img src='Images/Grist/".gristNameToImagePath("Build_Grist")."' height='15' width='15' alt = 'xcx'/> $buildgrist";
							$userrow['Build_Grist'] = $userrow['Build_Grist'] + $buildgrist;
							$gristlevel = 1;
							while ($gristlevel <= 6) {
								$gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
								$totalgrists = 0;
								while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
								$totalgrists--; //It counts from zero
								$typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
								$selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
								$selectedrow = mysql_fetch_array($selectedresult);
								$gristloot = 14000 + (rand(0,1) * 1000) + (rand(0,1) * 6000) + (floor(rand(0,2) / 2) * 15000) + (rand(0,$luck) * 160);
								if ($gristlevel <= 9) { //Paranoia: Grist type exists.
									$typestr = "grist" . strval($gristlevel);
									$gristdroptype = $selectedrow[$typestr];
									if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
									$userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
								}
								$gristlevel++;
							}
							echo ".</br>";
							break;
						case "True Hekatonchire":
							$bossdead = True;
							if (!empty($userrow['pesternoteUsername'])) sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "Just defeated a True Hekatonchire to clear a 5-floor gate 5 dungeon!");
							echo "You have slain the True Hetakomabob! Yeah, you've decided to forget trying to pronounce these names and just snag the loot: ";
							$buildgrist = 105000 + (rand(0,1) * 10000) + (rand(0,1) * 60000) + (floor(rand(0,2) / 2) * 200000) + (rand(0,$luck) * 900);
							echo "<img src='Images/Grist/".gristNameToImagePath("Build_Grist")."' height='15' width='15' alt = 'xcx'/> $buildgrist";
							$userrow['Build_Grist'] = $userrow['Build_Grist'] + $buildgrist;
							$gristlevel = 1;
							while ($gristlevel <= 9) {
								$gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
								$totalgrists = 0;
								while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
								$totalgrists--; //It counts from zero
								$typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
								$selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
								$selectedrow = mysql_fetch_array($selectedresult);
								$gristloot = 70000 + (rand(0,1) * 5000) + (rand(0,1) * 30000) + (floor(rand(0,2) / 2) * 75000) + (rand(0,$luck) * 800);
								if ($gristlevel <= 9) { //Paranoia: Grist type exists.
									$typestr = "grist" . strval($gristlevel);
									$gristdroptype = $selectedrow[$typestr];
									if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
									$userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
								}
								$gristlevel++;
							}
							echo ".</br>";
							break;
						case "Lich Queen":
		$bossdead = True;
		if (!empty($userrow['pesternoteUsername'])) sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "Just defeated a Lich Queen to clear a gate 5 dungeon!");
		echo "CONGRATULATIONS! You have slain the Queen, thus- oh, hang on. Wrong Queen. Er, here, have some grist or something.</br>";
		$buildgrist = 66666 + (rand(0,1) * 6666) + (rand(0,1) * 26666) + (floor(rand(0,2) / 2) * 66666) + (rand(0,$luck) * 666);
		echo "<img src='Images/Grist/".gristNameToImagePath("Build_Grist")."' height='15' width='15' alt = 'xcx'/> $buildgrist";
		$userrow['Build_Grist'] = $userrow['Build_Grist'] + $buildgrist;
		$gristlevel = 1;
		while ($gristlevel <= 9) {
		  $gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
		  $totalgrists = 0;
		  while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
		  $totalgrists--; //It counts from zero
		  $typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
		  $selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
		  $selectedrow = mysql_fetch_array($selectedresult);
		  $gristloot = 25000 + (rand(0,1) * 2000) + (rand(0,1) * 6666) + (floor(rand(0,4) / 4) * 33000) + (rand(0,$luck) * 666);
		  if ($gristlevel <= 9) { //Paranoia: Grist type exists.
		    $typestr = "grist" . strval($gristlevel);
		    $gristdroptype = $selectedrow[$typestr];
		    if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
		    $userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
		  }
		  $gristlevel++;
		}
		break;
		case "Progenitor":
		$bossdead = True;
		if (!empty($userrow['pesternoteUsername'])) sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "Just defeated a Progenitor to clear the Automaton Factory!");
		echo "Finally having taken more damage than it can handle, Progenitor falls to pieces in front of you. As it collapses into piles of grist, you hear a soft, synthetic voice: \"I don't hate you.\"</br>";
		$gristlevel = 1;
		while ($gristlevel <= 3) {
		  $gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
		  $totalgrists = 0;
		  while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
		  $totalgrists--; //It counts from zero
		  $typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
		  $selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
		  $selectedrow = mysql_fetch_array($selectedresult);
		  $gristloot = 75000 + (rand(0,1) * 5000) + (rand(0,1) * 12500) + (floor(rand(0,4) / 4) * 75000) + (rand(0,$luck) * 1000);
		  if ($gristlevel <= 9) { //Paranoia: Grist type exists.
		    $typestr = "grist" . strval($gristlevel);
		    $gristdroptype = $selectedrow[$typestr];
		    if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
		    $userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
		  }
		  $gristlevel++;
		}
		break;
		case "Hydra":
		$bossdead = True;
		if (!empty($userrow['pesternoteUsername'])) sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "Just defeated a Hydra to clear a 3-floor gate 3 dungeon!");
		echo "The Hydra's body has taken enough damage that it can no longer support the heads! The entire thing collapses, leaving behind a treasure trove of grist.</br>";
		$buildgrist = 49000 + (rand(0,1) * 7777) + (rand(0,1) * 27777) + (floor(rand(0,2) / 2) * 77777) + (rand(0,$luck) * 777);
		echo "<img src='Images/Grist/".gristNameToImagePath("Build_Grist")."' height='15' width='15' alt = 'xcx'/> $buildgrist";
		$userrow['Build_Grist'] = $userrow['Build_Grist'] + $buildgrist;
		$gristlevel = 1;
		while ($gristlevel <= 9) {
		  $gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
		  $totalgrists = 0;
		  while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
		  $totalgrists--; //It counts from zero
		  $typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
		  $selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
		  $selectedrow = mysql_fetch_array($selectedresult);
		  $gristloot = 21000 + (rand(0,1) * 1400) + (rand(0,1) * 7777) + (floor(rand(0,4) / 4) * 28000) + (rand(0,$luck) * 777);
		  if ($gristlevel <= 9) { //Paranoia: Grist type exists.
		    $typestr = "grist" . strval($gristlevel);
		    $gristdroptype = $selectedrow[$typestr];
		    if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
		    $userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
		  }
		  $gristlevel++;
		}
		$hcount = 1;
		$foundthehydra = false;
			while ($hcount <= $max_enemies) { //drop all heads' HP to 0
				$headstr = "enemy" . strval($hcount) . "name";
				$headhp = "enemy" . strval($hcount) . "health";
				if (strpos($userrow[$headstr], "Hydra Head") !== false) {
					$userrow[$headhp] = 0; 
					if ($foundthehydra == false) { //go ahead and execute reward for heads before this hydra in the list, in case the user switched focus around
					echo "<br />The $userrow[$headstr] is severed and crashes to the ground, degenerating into grist: ";
		  $gristlevel = 1;
		while ($gristlevel <= 3) {
		  $gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
		  $totalgrists = 0;
		  while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
		  $totalgrists--; //It counts from zero
		  $typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
		  $selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
		  $selectedrow = mysql_fetch_array($selectedresult);
		  $gristloot = 5000 + (rand(0,1) * 500) + (rand(0,1) * 2500) + (floor(rand(0,4) / 4) * 5000) + (rand(0,$luck) * 100);
		  if ($gristlevel <= 9) { //Paranoia: Grist type exists.
		    $typestr = "grist" . strval($gristlevel);
		    $gristdroptype = $selectedrow[$typestr];
		    if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
		    $userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
		  }
		  $gristlevel++;
		}
		$userrow[$headstr] = ""; //DED.
					}
				} elseif ($headstr == $enemystr) $foundthehydra = true;
				$hcount++;
			}
		break;
		case "Blurred Hydra Head":
		case "Cosmic Hydra Head":
		case "Vented Hydra Head":
		case "Shining Hydra Head":
		case "Aural Hydra Head":
		case "Diseased Hydra Head":
		case "High Hydra Head":
		case "Threatening Hydra Head":
		case "Healthy Hydra Head":
		case "Bleeding Hydra Head":
		case "Faceless Hydra Head":
		case "Screaming Hydra Head":
		  echo "The $userrow[$enemystr] is severed and crashes to the ground, degenerating into grist: ";
		  $gristlevel = 1;
		while ($gristlevel <= 3) {
		  $gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
		  $totalgrists = 0;
		  while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
		  $totalgrists--; //It counts from zero
		  $typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
		  $selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
		  $selectedrow = mysql_fetch_array($selectedresult);
		  $gristloot = 5000 + (rand(0,1) * 500) + (rand(0,1) * 2500) + (floor(rand(0,4) / 4) * 5000) + (rand(0,$luck) * 100);
		  if ($gristlevel <= 9) { //Paranoia: Grist type exists.
		    $typestr = "grist" . strval($gristlevel);
		    $gristdroptype = $selectedrow[$typestr];
		    if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
		    $userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
		  }
		  $gristlevel++;
		}
		echo "<br />";
		$splitchance = 15;
		$mainabs = explode(", ", $mainrow['abstratus']);
		$noti = 0;
		$totalabs = 0;
		$totalchance = 0;
		while (!empty($mainabs[$noti])) {
		  $totalabs++;
		  $totalchance += hydraSplitChance($mainabs[$noti]);
		  $noti++;
		}
		$offabs = explode(", ", $offrow['abstratus']);
		$noti = 0;
		while (!empty($offabs[$noti])) {
		  $totalabs++;
		  $totalchance += hydraSplitChance($offabs[$noti]);
		  $noti++;
		}
		if ($totalabs > 0) {
		  $splitchance = ceil($totalchance / $totalabs);
		}
		$roll = rand(1,100);
		if ($bossdead == true) $splitchance = 0; //hydra is dead, don't split
		if ($roll < $splitchance) {
		  echo "When you look up at the hydra again after collecting your grist, you notice that there are now two heads in the old one's place! It seems the sever was so clean that the hydra was able to regenerate its lost appendages twofold!<br />";
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
					if (!empty($randenemy)) { //this happens sometimes, no idea why, but might as well treat that as a failure to activate
					$userrow['strifestatus'] = $currentstatus; //necessary because of spawnstatus
					$slot = generateEnemy($userrow,$userrow['grist_type'],"None",$randenemy,true);
					if ($slot != -1) {
						$userrow = refreshSingular($slot, $slot, $userrow);
						$currentstatus = $userrow['strifestatus'];
						$alldead = false; //newly-spawned head won't be targeted this turn
						//echo "DEBUG: Spawned head into slot " . strval($slot) . "</br>";
						//$message = $message . "A rip in time and space opens, summoning a $randenemy to the battle!</br>";
					}
					} else echo "DEBUGNOTE: Tried to spawn hydra head with ID of $whodat, returned empty.<br />";
					$whodat = rand(1,$countr);
					$whodat--;
					$randomresult = mysql_query("SELECT `basename` FROM `Enemy_Types` WHERE `Enemy_Types`.`basename` LIKE '%Hydra Head' LIMIT $whodat,1");
					$randrow = mysql_fetch_array($randomresult);
					$randenemy = $randrow['basename'];
					if (!empty($randenemy)) { //this happens sometimes, no idea why, but might as well treat that as a failure to activate
					$slot = generateEnemy($userrow,$userrow['grist_type'],"None",$randenemy,true);
					if ($slot != -1) {
						$userrow = refreshSingular($slot, $slot, $userrow);
						$alldead = false;
						//echo "DEBUG: Spawned head into slot " . strval($slot) . "</br>";
						//$message = $message . "A rip in time and space opens, summoning a $randenemy to the battle!</br>";
					}
					}	else echo "DEBUGNOTE: Tried to spawn hydra head with ID of $whodat, returned empty.<br />";
		} else { //see if there are any other heads remaining
			$hcount = 1;
			$thehydra = "enemy0name";
			$moreheads = false;
			$nonhydrafoes = false;
			while ($hcount <= $max_enemies) {
				$headstr = "enemy" . strval($hcount) . "name";
				if (strpos($userrow[$headstr], "Hydra Head") !== false && $headstr != $enemystr) $moreheads = true; 
				//the enemy that triggered this hasn't been blanked yet so don't count it
				elseif ($userrow[$headstr] == "Hydra") $thehydra = $headstr; //go ahead and set this as the body so we can blank it later
				else $nonhydrafoes = true;
				$hcount++;
			}
			if ($moreheads == false && $bossdead == false && $thehydra != "enemy0name") { //that's it! hydra is down, and there was a hydra to begin with (so not The Bug)
				$userrow[$thehydra] = ""; //blank the name so the body disappears
				$bossdead = True;
				if ($nonhydrafoes) $alldead = true; //it's a long story
		if (!empty($userrow['pesternoteUsername'])) sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "Just defeated a Hydra to clear a 3-floor gate 3 dungeon!");
		echo "Rendered headless, the Hydra briefly runs around like a chicken before stopping dead. The entire thing collapses, leaving behind a treasure trove of grist.</br>";
		$buildgrist = 49000 + (rand(0,1) * 7777) + (rand(0,1) * 27777) + (floor(rand(0,2) / 2) * 77777) + (rand(0,$luck) * 777);
		echo "<img src='Images/Grist/".gristNameToImagePath("Build_Grist")."' height='15' width='15' alt = 'xcx'/> $buildgrist";
		$userrow['Build_Grist'] = $userrow['Build_Grist'] + $buildgrist;
		$gristlevel = 1;
		while ($gristlevel <= 9) {
		  $gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
		  $totalgrists = 0;
		  while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
		  $totalgrists--; //It counts from zero
		  $typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
		  $selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
		  $selectedrow = mysql_fetch_array($selectedresult);
		  $gristloot = 21000 + (rand(0,1) * 1400) + (rand(0,1) * 7777) + (floor(rand(0,4) / 4) * 28000) + (rand(0,$luck) * 777);
		  if ($gristlevel <= 9) { //Paranoia: Grist type exists.
		    $typestr = "grist" . strval($gristlevel);
		    $gristdroptype = $selectedrow[$typestr];
		    if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
		    $userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
		  }
		  $gristlevel++;
		}
			}
		}
		break;
			case "The Bug":
				$bossdead = True;
		if (!empty($userrow['pesternoteUsername'])) sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "Just defeated The Bug to clear a gate " . horribleMess() . " dungeon!");
		echo "After a harrowing b" . horribleMess() . ", The Bug finally expl" . horribleMess() . " into a pile of artifa" . horribleMess() . " and various grist. You're not sure if you just helped " . horribleMess() . "bugfix the game, or destroyed " . horribleMess() . " vital bit of code, but at least you g" . horribleMess() . horribleMess() . "</br>";
		$buildgrist = rand(1000000,9999999);
		echo "<img src='Images/Grist/".gristNameToImagePath("Artifact_Grist")."' height='15' width='15' alt = 'xcx'/> $buildgrist";
		$userrow['Artifact_Grist'] = $userrow['Artifact_Grist'] + $buildgrist;
		$gristlevel = 1;
		while ($gristlevel <= 9) {
		  $gristtyperesult = mysql_query("SELECT `name` FROM `Grist_Types`");
		  $totalgrists = 0;
		  while ($countrow = mysql_fetch_array($gristtyperesult)) $totalgrists++;
		  $totalgrists--; //It counts from zero
		  $typeselected = rand(0,$totalgrists); //Starting point for the grist type search.
		  $selectedresult = mysql_query("SELECT * FROM `Grist_Types` LIMIT " . $typeselected . " , 1 ;");
		  $selectedrow = mysql_fetch_array($selectedresult);
		  $gristloot = rand(1,9999999);
		  if ($gristlevel <= 9) { //Paranoia: Grist type exists.
		    $typestr = "grist" . strval($gristlevel);
		    $gristdroptype = $selectedrow[$typestr];
		    if ($gristloot > 0) echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
		    $userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
		  }
		  $gristlevel++;
		}
		break;
		case "Blade Cloud":
		  echo "Whatever winds were holding the Blade Cloud together have finally died down completely, causing all of its blades to fall to the gro--DEAR GOD SO MANY BLADES THERE ARE JUST SO MANY GOD DAMN IT'S SO POINTY JESUS FUCK THIS HURTS SO MUUUUUCH DEAR GOD THEY'RE ALL OVER THE ENTIRETY OF EVERYTHING EVER JESUS FUCK JUST MAKE IT STOOOOOP<br />";
		  $userrow['Health_Vial'] = 1; //player is rained upon by blades. like how WOULDN'T that cause you serious pain
		  $userrow['down'] = 1; //player is KO'ed, essentially costing them the necessary encounter
		  $keepgoing = true;
		  while ($keepgoing) {
		  	$maxx = pow(rand(1,100),2);
		  	$absresult = mysql_query("SELECT `power` FROM `Captchalogue` WHERE `power` < $maxx AND (`abstratus` LIKE 'bladekind%' OR `abstratus` LIKE '%, bladekind%')");
		  	$allblades = 0;
		  	while ($arow = mysql_fetch_array($absresult)) {
		    	$allblades++;
		  	}
		    $choice = rand(1,$allblades);
		    $absresult = mysql_query("SELECT `name` FROM `Captchalogue` WHERE `power` < $maxx AND (`abstratus` LIKE 'bladekind%' OR `abstratus` LIKE '%, bladekind%') LIMIT $choice, 1");
		    $arow = mysql_fetch_array($absresult);
		    $arow['name'] = str_replace("\\", "", $arow['name']);
		    $invstr = addItem($arow['name'], $userrow);
		    if ($invstr != "inv-1") {
      		$userrow[$invstr] = $arow['name'];
		    } else {
		      $keepgoing = false;
		    }
		  }
		  echo "When the blade rain finally ends, you find not only that you've barely survived it, but also that all of your previously empty captchalogue cards are somehow filled with random swords. Wonderful.<br />";
		  break;
		case "Animated Blade":
			echo "The Animated Blade clatters to the floor. You did so much damage to it that you doubt even your Recycler would accept it.<br />";
			break;
	      default:
		$denizenresult = mysql_query("SELECT * FROM Titles WHERE `Titles`.`Class` = 'Denizen';");
		$denizenrow = mysql_fetch_array($denizenresult);
		if ($userrow[$enemystr] == $denizenrow[$userrow['Aspect']]) { //Denizen defeated.
		  echo "You have defeated your denizen. You may now access the Battlefield, and you have contributed the Hoard to the session's victory requirement.</br>";
		  	if (!empty($userrow['pesternoteUsername'])) sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "Defeated $userrow[$enemystr], my Denizen.");
			$gateresult = mysql_query("SELECT * FROM Gates");
		  $gaterow = mysql_fetch_array($gateresult);
		  if ($userrow['buffstrip'] == 1 && $userrow['noassist'] == 1 && $userrow['house_build_grist'] >= $gaterow['gate7']) { //Legit
			$userrow['denizendown'] = 1;
			$userrow['battlefield_access'] = 1;
		  } else {
		    echo "...buuut you cheated, so you get nothing. Tough 8r8k! ::::P";
		  }
		} else {
		  if (!empty($enemyrow)) {
			if ($enemyrow['appearson'] == "Prospit") {
				echo '</br>The ' . $userrow[$enemystr] . ' is "defeated"!';
			} elseif ($enemyrow['appearson'] == "Battlefield") {
				if ($userrow['battlefield_access'] == 1 || $userrow['dreamingstatus'] != "Awake") {
						echo '</br>The ' . $userrow[$enemystr] . " is defeated! Derse's war machine grows weaker.</br>";
			$sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$userrow[session_name]';");
			$sessionrow = mysql_fetch_array($sessionresult);
			$sessionrow['battlefieldtotal'] = $sessionrow['battlefieldtotal']+$userrow[$maxpowerstr];
			mysql_query("UPDATE `Sessions` SET `battlefieldtotal` = $sessionrow[battlefieldtotal] WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1 ;");
		      } else {
			echo "You're not supposed to be fighting battlefield enemies.</br>";
		      }
		    } else {
		      echo '</br>The ' . $userrow[$enemystr] . ' is defeated!';
		    }
		    if ($enemyrow['maxboons'] > 0) { //Enemy drops Boondollars
		      $boondollars = rand($enemyrow['minboons'], $enemyrow['maxboons']);
		      $boondollars = $boondollars - ($boondollars % 5);
		      if ($userrow['dreamingstatus'] == "Prospit") {
			echo ' You collect the "spoils": ';
		      } else {
			echo " You collect the spoils: ";
		      }
		      echo "$boondollars Boondollars.</br>";
		      $userrow['Boondollars'] = $userrow['Boondollars'] + $boondollars;
		    }
		  } else {
		    echo 'Enemy type "' . $enemytype . '" unrecognized. This is probably a bug, please submit a report!</br>';
		    logDebugMessage($username . " - defeated unrecognized enemy called $enemytype");
		  }
		}
		break;
	      }
		  					if (empty($gristrow) && $userrow[$categorystr] != "None") {
		  						echo "Grist type $userrow[$categorystr] not recognized. This is probably a bug, please report!";
		  						logDebugMessage($username . " - unrecognized grist type $userrow[$categorystr] from $enemytype");
		  					}
					if (empty($enemytype)) $enemytype = "HERPDERP";
					$gristquery = "UPDATE `Players` SET ";
					$dropped = False;
					switch ($enemytype) { //Legacy: doesn't actually do anything.
					default:
						if (!empty($enemyrow['drops'])) { //Enemy has drops defined.
							$dropped = True;
							echo " You collect the spoils: ";
							$droparray = explode("|", $enemyrow['drops']);
							$dropnumber = 0;
							$gristarray = array(0,0,0,0,0,0,0,0,0,0);
							while (!empty($droparray[$dropnumber])) {
								$currentdrop = $droparray[$dropnumber];
								$currentarray = explode(":", $currentdrop);
								switch ($currentarray[$droptype]) {
								case "GRIST":
									if (($rarity + $currentarray[$droptier] - 1) < 10) { //Grist type exists.
										$roll = rand(1,100);
										if ($roll > (100 - $currentarray[$dropchance])) {
											if ($currentarray[$droptier] == 0) { //Build Grist is "tier 0"
												$gristarray[0] += $currentarray[$dropquantity];
											} else {
												$gristnumber = $rarity + $currentarray[$droptier] - 1; //"Tier 0" non-BG drop is the enemy's native type
												$gristarray[$gristnumber] += $currentarray[$dropquantity];
											}
										}
									}
									break;
								default:
									break;
								}
								$dropnumber++;
							}
							$gristnum = 0;
							while ($gristnum < 10) {
								if ($gristnum == 0) {
									$gristdroptype = "Build_Grist";
								} else {
									$typestr = "grist" . strval($gristnum);
									$gristdroptype = $gristrow[$typestr];
								}
								$gristloot = $gristarray[$gristnum];
								if ($gristloot > 0) { //We got some of this grist. Hooray!
									echo "<img src='Images/Grist/".gristNameToImagePath("$gristdroptype")."' height='15' width='15' alt = 'xcx'/> $gristloot";
									$userrow[$gristdroptype] = $userrow[$gristdroptype] + $gristloot;
								}
								$gristnum++;
							}
						}
						if (strrpos($userrow[$descstr], "It also appears to be wielding ")) { //imp/ogre/whatever drops his weapon and strife card
							$itemplace = strrpos($userrow[$descstr], "wielding ");
							$itemplace += 9;
							$itemstart = strlen($userrow[$descstr]) - $itemplace;
							$itemname = substr($userrow[$descstr], ($itemstart * -1), ($itemstart - 1));
							$itemuname = str_replace("'", "\\\\''", $itemname);
							$lootresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '$itemuname' ;");
							$lootrow = mysql_fetch_array($lootresult);
							$abstratus = explode(',', $lootrow['abstratus']);
							$userrow = addSpecibus($userrow, $abstratus[0]);
							$newitem = addItem($lootrow['name'],$userrow);
							$userrow[$newitem] = str_replace("\\", "", $lootrow['name']); //that friggin EOT query
							$userrow['abstrati']++; //actually give the player a new strife slot
							mysql_query("UPDATE `Players` SET `abstrati` = " . strval($userrow['abstrati']) . " WHERE `Players`.`username` = '$username' LIMIT 1;");
							//OVERSEER: I'm just grabbing the first item from the abstratus listing. That should be the kind if it has more than one, right?
							if ($newabs != "abstratus-1") {
								echo "It dropped a strife card, which contained " . $abstratus[0] . "! You waste no time putting it in your strife portfolio.</br>";
							} else {
								echo "It dropped a strife card, but your strife portfolio is too full to equip it!</br>";
				}
				if ($newitem != "inv-1") {
					echo "It dropped its " . $itemname . "! You captchalogue it posthaste.</br>";
				} else {
					echo "It dropped its " . $itemname . "! But your inventory is full, so you leave it behind.</br>";
				}
			}
	    }
	    

?>