<?php
//Enemy-specific special attacks and anything else that happens on their turn that isn't specifically covered elsewhere will go here in a switch statement on the enemy's name.
			//THIS IS A DATA SECTION - It stores data for enemy specials.
			if (!($userrow['motifcounter'] > 0 && $userrow['Aspect'] == "Void") && $lockeddown == false) { //Level 3 void turns this off.
			switch ($userrow[$enemystr]) {
			case "Typheus":
				$roll = rand(1,100);
				if ($roll >= 90) {
					$message = $message . "Typheus engulfs the chamber in fire!</br>";
					if ($userrow['invulnerability'] == 0) $damage += aspectDamage($resistances, "Breath", $userrow[$powerstr] / 10, 2);
				} elseif ($roll >= 70) {
					$message = $message . "Typheus dazes you with a powerful gust of wind.</br>";
					$debuff = aspectDamage($resistances, "Breath", 500, 4);
					$userrow['temppowerboost'] = $userrow['temppowerboost'] - $debuff;
					if ($userrow['temppowerboost'] < 0 && $userrow['temppowerduration'] < 4) $userrow['temppowerduration'] = 4; //One round will be subtracted off later.
					$userrow['tempdefenseboost'] = $userrow['tempdefenseboost'] - $debuff;
					if ($userrow['tempdefenseboost'] < 0 && $userrow['tempdefenseduration'] < 4) $userrow['tempdefenseduration'] = 4; //One round will be subtracted off later.
				}
				break;
			case "Sophia":
				$message = $message . "Sophia focuses inward and gathers herself.</br>";
				$userrow[$powerstr] += 500;
				$userrow[$maxpowerstr] += 500;
				break;
			case "Hemera":
				$roll = rand(1,100);
				if ($roll >= 85) {
					$message = $message . "Hemera draws Life from the surroundings to knit her wounds.</br>";
					$userrow[$healthstr] += 2000;
					if ($userrow[$healthstr] > $userrow[$maxhealthstr]) $userrow[$healthstr] = $userrow[$maxhealthstr];
				} elseif ($roll >= 50) {
					$message = $message . "Hemera reaches above you and gives your Health Vial a good flick.</br>";
					$damage += aspectDamage($resistances, "Life", floor($userrow['Gel_Viscosity'] / 8), 4);
				}
				break;
			case "Abraxas":
				$roll = rand(1,100);
					if ($roll <= (($userrow[$healthstr] / $userrow[$maxhealthstr]) * 100)) { //More likely to trigger the less wounded Abraxas is.
					$message = $message . "Abraxas strikes out at you with the shining light of Hope.</br>";
					if ($userrow['invulnerability'] == 0) $damage += aspectDamage($resistances, "Hope", floor(($userrow[$healthstr] / $userrow[$maxhealthstr]) * 3000), 3);
				}
				break;
			case "Cetus":
				$roll = rand(1,100);
				if ($roll == 100) {
					$message = $message . "Cetus emits a massive shining laser from her gaping maw, almost completely obliterating you.</br>";
					$damage = $userrow['Health_Vial'] - 1;
				} elseif ($roll >= 80) {
					$message = $message . "Cetus calls down ancient spirits of Light to inflict heavy damage.</br>";
					if ($userrow['invulnerability'] == 0) $damage += aspectDamage($resistances, "Light", rand(1500,2500), 4);
				} elseif ($roll >= 60) {
					$message = $message . "Cetus invokes a blinding, searing flare.</br>";
					if ($userrow['invulnerability'] == 0) $damage += aspectDamage($resistances, "Light", rand(400,600), 2);
					$userrow['temppowerboost'] = $userrow['temppowerboost'] - aspectDamage($resistances, "Light", 413, 4);
					if ($userrow['temppowerboost'] < 0 && $userrow['temppowerduration'] < 6) $userrow['temppowerduration'] = 6; //One round will be subtracted off later.
				} elseif ($roll >= 40) {
					$message = $message . "Cetus invokes the power of fortune to make this round's wounds more grievous.</br>";
					if ($damage > 0) $damage = floor($damage * 1.3);
				} elseif ($roll >= 20) {
					$message = $message . "Cetus emits a massive shining laser from her gaping maw, but it mostly misses.</br>";
					$damage += aspectDamage($resistances, "Light", rand(100,300), 1);
				}
				break;
			case "Metis":
				$roll = rand(1,100);
				if ($roll >= 90) {
					$message = $message . "Metis predicts your strikes perfectly, flowing around them at the speed of thought.</br>"; //Undo damage player dealt.
					$userrow[$healthstr] = $newenemyhealth + $enemydamage; //Update for repetition and checking end-of-turn effects.
				} elseif ($roll >= 75) {
					$message = $message . "Metis reaches out to you telepathically, clouding your thoughts.</br>";
					$userrow['temppowerboost'] = $userrow['temppowerboost'] - aspectDamage($resistances, "Mind", 800, 2);
					if ($userrow['temppowerboost'] < 0 && $userrow['temppowerduration'] < 3) $userrow['temppowerduration'] = 3; //One round will be subtracted off later.
				} elseif ($roll >= 50) {
					$message = $message . "Metis focuses her mind, enhancing her awareness.</br>";
					$userrow[$powerstr] += 250;
					$userrow[$maxpowerstr] += 250;
				}
				break;
			case "Armok":
				$playerdamage = floor($userrow[$powerstr] * $numbersfactor * 0.5) - rand(floor($defensepower * (0.85 + ($luck * 0.003))),ceil($defensepower * 1.15)); //Second strike weaker.
				if ($playerdamage < 0 || $userrow['invulnerability'] > 0) $playerdamage = 0; //No healing the player with attacks!
				if ($playerdamage > ($userrow['Gel_Viscosity'] / 6)) $playerdamage = floor(($userrow['Gel_Viscosity'] / 6) - 1); //Massive damage safety net.
				if ($playerdamage != 0) $nodamage = False;
				if ($userrow['invulnerability'] == 0) $damage += aspectDamage($resistances, "Blood", $playerdamage, 4);
				break;
			case "Moros":
				$factor = floor(((1 / ($userrow['Health_Vial'] / $userrow['Gel_Viscosity'])) - 1) / 2);
				$damage += aspectDamage($resistances, "Doom", $damage * $factor, 2);
				break;
			//NOTE - Lyssa has no special ability.
			case "Nyx":
				$roll = rand(1,100);
				if ($roll >= 50 && $userrow['equipped'] == "" && $userrow['offhand'] == "") { //One with Nothing is active. Nyx's special abilities do not apply.
					$message = $message . "Nyx's attempt to interfere with your weapon proficiency fails, since you fight with naught but your fists.</br>";
				} else {
					if ($roll >= 75) {
						$message = $message . "A black...mist? floats out from Nyx, making her harder to strike with your weapon.</br>";
						$userrow['tempoffenseboost'] = $userrow['tempoffenseboost'] - aspectDamage($resistances, "Void", 1200, 2);
						if ($userrow['tempoffenseboost'] < 0 && $userrow['tempoffenseduration'] < 4) $userrow['tempoffenseduration'] = 4; //One round will be subtracted off later.
					} elseif ($roll >= 50) {
						$message = $message . "Nyx taps you briefly on the head. You suddenly feel less proficient with your weapon...</br>";
						$userrow['temppowerboost'] = $userrow['temppowerboost'] - aspectDamage($resistances, "Void", 400, 2);
						if ($userrow['temppowerboost'] < 0 && $userrow['temppowerduration'] < 11) $userrow['temppowerduration'] = 11; //One round will be subtracted off later.
					}
				}
				break;
			//NOTE - Echidna chooses not to use special abilities.
			case "Hephaestus":
				$roll = rand(1,100);
				if ($roll >= 75) {
					$message = $message . "Hephaestus strikes you with his mighty hammer from everywhere and everywhen at once!</br>";
					if ($userrow['invulnerability'] == 0) $damage += aspectDamage($resistances, "Time", floor($userrow[$powerstr] / 12), 2);
				}
				break;
			case "Kraken":
				$roll = rand(1,100);
				if ($roll >= 66) {
					$message = $message . "The Kraken unleashes Lv. 27 Battle Technique: Tendrilfondle! It is beyond embarrassing.</br>";
					if ($userrow['invulnerability'] == 0) $damage = $damage + 127;
				}
				break;
			case "Hekatonchire":
				$roll = ceil(rand(0,3) / 3);
				if ($roll > 0) {
					if ($roll == 1) {
						$message = $message . "The Hekatonchire lashes out at you with 1 additional arm!</br>";
					} else {
						$message = $message . "The Hekatonchire lashes out at you with " . strval($roll) . " additional arms!</br>";
					}
					if ($userrow['invulnerability'] == 0) $damage = $damage + (350 * $roll);
				}
				break;
			case "True Hekatonchire":
				$difrow = refreshSingular($i, $i, $userrow); //pull the row from the database so we can tell just how much damage was done to it this round
				$difference = $difrow[$healthstr] - $userrow[$healthstr];
				if ($difference > 0) {
				  $roll = rand(0,rand(0,49));
				  if ($roll > 0) {
				    $message = $message . "The True Hekatonchire forms a defensive wall using " . strval($roll) . " of its arms, blocking some of the damage!<br />";
				    $blocked = $roll * 35;
				    if ($blocked > $difference) $blocked = $difference;
				    $userrow[$healthstr] += $blocked;
				  }
				}
				$roll = rand(0,rand(0,49));
				if ($roll > 0) {
					if ($userrow['invulnerability'] == 0) {
						if ($roll == 1) {
							$message = $message . "The True Hekatonchire lashes out at you with 1 additional arm!</br>";
						} else {
							$message = $message . "The True Hekatonchire lashes out at you with " . strval($roll) . " additional arms!</br>";
						}
						$damage = $damage + (35 * $roll); //this can add up quickly
					} else {
						echo "Noticing you are now impervious to damage, the True Hekatonchire decides to pick you up and toss you out of the room instead.<br />";
						//player is forcibly ejected from strife as if they absconded lolol
						$userrow = terminateStrife($userrow, 2);
						if ($userrow['dungeonstrife'] == 2) { //User strifing in a dungeon
							$userrow['dungeonstrife'] = 1;
							echo "You land rather violently outside of the room, taking no damage thanks to your invulnerability. However, it quickly wears off before you can re-enter the boss battle.</br>";
						}
						$dontcheckvictory = true;
					}
				}
				break;
			case "Blurred Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Blurred Head strikes at you so fast, you can hardly see it coming!<br />";
					if ($userrow['invulnerability'] == 0) {
						if ($damage > 250) $damage = $damage * 2; //doubles damage that it did this round
						else $damage = 250; //...and a 250 minimum if it didn't do much of anything
					}
				}
				break;
			case "Cosmic Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Cosmic Head evacuates all of the air around you, choking you briefly!<br />";
					$damage = $damage + 300; //hits through invuln because you still need to breathe
				}
				break;
			case "Vented Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Vented Head unleashes a powerful gust of wind at you";
					$chance = rand(1,100);
					if ($chance > 50) {
					  $currentstatus .= "PLAYER:STUN|";
					  $message = $message . ", sending you tumbling around the room!<br />";
					} else {
					  $message = $message . "! You barely manage to brace yourself and stay standing.<br />";
					}
				}
				break;
			case "Shining Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Shining Head fires a blinding beam of light at you, making it harder to see!<br />";
					$currentstatus .= "PLAYER:BLIND|";
				}
				break;
			case "Aural Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Aural Head roars a majestic, proud roar!";
					$chance = rand(1,100);
					if ($chance > 50) {
					  $currentstatus .= "PLAYER:NOCAP|";
					  $message = $message . " Your hope starts to waver!<br />";
					} else {
					  $message = $message . " You remain unfazed.<br />";
					}
				}
				break;
			case "Diseased Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Diseased Head spews a cloud of toxic gas!";
					$chance = rand(1,100);
					if ($chance > 50) {
					  $currentstatus .= "PLAYER:POISON:2|";
					  $message = $message . " You are poisoned by its vileness!<br />";
					} else {
					  $message = $message . " You hold your breath just in time, resisting the poison.<br />";
					}
				}
				break;
			case "High Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The High Head breathes a puff of gnarly smoke!";
					$chance = rand(1,100);
					if ($chance > 50) {
					  $currentstatus .= "PLAYER:CONFUSE|";
					  $message = $message . " You start to feel a bit dizzy...<br />";
					} else {
					  $message = $message . " You manage to resist the influence.<br />";
					}
				}
				break;
			case "Threatening Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Threatening Head gazes at you with its piercing eyes!";
					$chance = rand(1,100);
					if ($chance > 50) {
					  $aspectdamage = rand(100,1000);
					  $userrow['Aspect_Vial'] -= $aspectdamage;
					  if ($userrow['Aspect_Vial'] < 0) $userrow['Aspect_Vial'] = 0;
					  $message = $message . " You feel disheartened, losing some aspect vial!<br />";
					} else {
					  $message = $message . " You are unaffected.<br />";
					}
				}
				break;
			case "Healthy Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Healthy Head wafts a soothing fog, curing the entire hydra of most of its ailments!";
					$alleffects = explode("|", $currentstatus);
					$newstatus = "";
					$i = 0;
					while (!empty($alleffects[$i])) {
					  $thiseffect = explode(":", $alleffects[$i]);
					  if (strpos($thiseffect[0], "ENEMY") === false || $thiseffect[1] == "GLITCHED" || $thiseffect[1] == "UNLUCKY" || $thiseffect[1] == "TIMESTOP") {
					    $newstatus .= $alleffects[$i] . "|";
					  }
					  $i++;
					}
					$currentstatus = $newstatus;
				}
				break;
			case "Bleeding Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Bleeding Head spits a very potent acid!";
					if ($userrow['invulnerability'] == 0) {
					  $message . " It really burns! Like, a lot!<br />";
					  $damage = $damage + 500;
					} else {
					  $message . " The acid eats through your invulnerability and negates it!<br />";
					  $userrow['invulnerability'] = 0;
					}
				}
				break;
			case "Faceless Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Faceless Head breathes a chilling breath... somehow... making it harder for you to deal damage!<br />";
					$userrow['offenseboost'] -= rand(100,200);
				}
				break;
			case "Screaming Hydra Head":
				$roll = rand(1,7);
				if ($roll == 7) { //all hydra abilities trigger on a 1/7 chance.
					$message = $message . "The Screaming Head shrieks and bellows a raging blast of flame at you! The resulting burn makes you more sensitive to damage!<br />";
					$userrow['defenseboost'] -= rand(100,200);
				}
				break;
			case "Lich Queen":
				$roll = rand(1,100);
				if ($roll >= 90) { //Some sort of blast
					$message = $message . "The Lich Queen assaults you with terrible majyyks!</br>";
					$damage = $damage + 666 + 666; //Hits through invuln. Fun! Yes, it's coded as two 666's. That's just how I roll.
				} elseif ($roll >= 65) { //Summon Liches
					$quantity = floor(rand(3,6) / 3); //Mostly 1, sometimes 2
					if ($quantity == 1) $message = $message . "The Lich Queen summons a minion to her side!</br>";
					if ($quantity != 1) $message = $message . "The Lich Queen summons minions to her side!</br>";
					$dungeonresult = mysql_query("SELECT `dungeonland` FROM `Dungeons` WHERE `Dungeons`.`username` = '" . $userrow['currentdungeon'] . "' LIMIT 1;");
					$dungeonrow = mysql_fetch_array($dungeonresult);
					$landresult = mysql_query("SELECT `grist_type` FROM `Players` WHERE `Players`.`username` = '$dungeonrow[dungeonland]' LIMIT 1;");
					$landrow = mysql_fetch_array($landresult);
					$summongristresult = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '$landrow[grist_type]' LIMIT 1;");
					$summongristrow = mysql_fetch_array($summongristresult);
					while ($quantity > 0) {
						$material = rand(1,9); //NOTE - Only affects health of summon. Does not affect power.
						$griststring = "grist" . strval($material);
						$userrow['strifestatus'] = $currentstatus; //necessary because of spawnstatus
						$slot = generateEnemy($userrow,$landrow['grist_type'],$summongristrow[$griststring],"Lich",False);
						if ($slot != -1) { //Success
							$userrow = refreshSingular($slot, $slot, $userrow);
							$currentstatus = $userrow['strifestatus']; //necessary because of spawnstatus
							$powerstr = "enemy" . strval($slot) . "power";
							$maxpowerstr = "enemy" . strval($slot) . "maxpower";
							$healthstr = "enemy" . strval($slot) . "health";
							$maxhealthstr = "enemy" . strval($slot) . "maxhealth";
							$userrow[$powerstr] = 13333;
							$userrow[$maxpowerstr] = 13333;
							$userrow[$healthstr] = $material * 1998;
							$userrow[$maxhealthstr] = $material * 1998;
						}
						$quantity--;
					}
				} elseif ($roll >= 50) { //Reprieve! Nothing happens.
				} elseif ($roll >= 40) { //Heal self and minions.
					$message = $message . "The Lich Queen strengthens unlife within the room, increasing the health of basically everything except you.</br>";
					$counter = 1;
					while ($counter <= $max_enemies) {
						$healthstr = "enemy" . strval($counter) . "health";
						$userrow[$healthstr] += 1250; //Yes, this can overheal. 
						$counter++;
					}
				} else { //Small power buff to all surviving minions
					$message = $message . "The Lich Queen attempts to empower any minions she might have accrued, regardless of whether she has actually accrued any.</br>";
					$slot = 2; //Does not self-empower
					while ($slot <= $max_enemies) {
						$powerstr = "enemy" . strval($slot) . "power";
						$maxpowerstr = "enemy" . strval($slot) . "maxpower";
						$userrow[$powerstr] += 250;
						$userrow[$maxpowerstr] += 250;
						$slot++;
					}
				}
				break;
			case "Progenitor":
				$roll = rand(1,100);
				if ($roll >= 85) { //Thunder Wave lol
					$message = $message . "Progenitor shoots a wave of electricity at you!";
					$chance = rand(1,100);
					if ($chance < 75) {
						$currentstatus .= "PLAYER:STUN|";
						$message = $message . " The electricity hinders your movements!</br>";
					} else {
						$message = $message . " You manage to resist becoming paralyzed!</br>";
					}
				} elseif ($roll >= 70) { //Build a machine
					$robotype = rand(1,3);
					if ($robotype == 1) $roboname = "Construct";
					if ($robotype == 2) $roboname = "Autoturret";
					if ($robotype == 3) $roboname = "Metamorpher";
					$message = $message . "Progenitor dashes around the room, gathering up spare parts and assembling another $roboname before your eyes!</br>";
					$dungeonresult = mysql_query("SELECT `dungeonland` FROM `Dungeons` WHERE `Dungeons`.`username` = '" . $userrow['currentdungeon'] . "' LIMIT 1;");
					$dungeonrow = mysql_fetch_array($dungeonresult);
					$landresult = mysql_query("SELECT `grist_type` FROM `Players` WHERE `Players`.`username` = '$dungeonrow[dungeonland]' LIMIT 1;");
					$landrow = mysql_fetch_array($landresult);
					$summongristresult = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '$landrow[grist_type]' LIMIT 1;");
					$summongristrow = mysql_fetch_array($summongristresult);
					$material = rand(1,9); //NOTE - Only affects health of summon. Does not affect power.
					$griststring = "grist" . strval($material);
					$userrow['strifestatus'] = $currentstatus; //necessary because of spawnstatus
					$slot = generateEnemy($userrow,$landrow['grist_type'],$summongristrow[$griststring],$roboname,False);
					if ($slot != -1) { //Success
						$userrow = refreshSingular($slot, $slot, $userrow);
						$currentstatus = $userrow['strifestatus']; //necessary because of spawnstatus
						$powerstr = "enemy" . strval($slot) . "power";
						$maxpowerstr = "enemy" . strval($slot) . "maxpower";
						$healthstr = "enemy" . strval($slot) . "health";
						$maxhealthstr = "enemy" . strval($slot) . "maxhealth";
						if ($robotype == 1) $robopower = 10000;
						if ($robotype == 2) $robopower = 8000;
						if ($robotype == 3) $robopower = 12500;
						$userrow[$powerstr] = $robopower;
						$userrow[$maxpowerstr] = $robopower;
						$userrow[$healthstr] = $material * $robopower;
						$userrow[$maxhealthstr] = $material * $robopower;
					} else echo "Error spawning Progenitor minion!<br />";
				} elseif ($roll >= 30) { //Reprieve! Nothing happens.
				} else { //Small power buff to all surviving minions
					$message = $message . "Progenitor grabs a hammer and wrench and begins to modify itself and any other still-functioning machines in the room, repairing and strengthening them!</br>";
					$slot = 1; //DOES self-empower
					while ($slot <= $max_enemies) {
						$healthstr = "enemy" . strval($slot) . "health";
						$powerstr = "enemy" . strval($slot) . "power";
						$userrow[$healthstr] += rand(500,2000);
						$userrow[$powerstr] += rand(1,25) * 10;
						$slot++;
					}
				}
				break;
			case "The Bug":
				$roll = rand(1,100);
				if ($roll >= 75) {
					$message = $message . "The Bug wails a glitchy battle cry, shuffling a lot of variables around!</br>";
					$damage = rand(0, $damage * 2);
					$buggedluck = rand(-100,100);
					$buggedbluck = rand(-100,100);
					$buggedaspect = rand(0, $userrow['Gel_Viscosity']);
					$buggedoff = rand($userrow['Echeladder'] * -1, $userrow['Echeladder']);
					$buggedoff += $userrow['offenseboost'];
					$buggeddef = rand($userrow['Echeladder'] * -1, $userrow['Echeladder']);
					$buggeddef += $userrow['defenseboost'];
					$buggedboon = rand(-1000, 1000);
					$buggedboon += $userrow['Boondollars'];
					$buggedbuild = rand(-1000, 1000);
					$buggedbuild += $userrow['Build_Grist'];
					$bugpower = rand(-9999, 9999);
					$bugpower += $userrow[$powerstr];
					$bughealth = rand(-99999, 99999);
					$bughealth += $userrow[$healthstr];
					$userrow['offenseboost'] = $buggedoff;
					$userrow['defenseboost'] = $buggeddef;
					$userrow[$powerstr] = $bugpower;
					$userrow[$healthstr] = $bughealth;
					$userrow['Aspect_Vial'] = $buggedaspect;
				} elseif ($roll >= 60) {
					$randomresult = mysql_query("SELECT `basename` FROM `Enemy_Types`");
					$countr = 0;
					while ($randrow = mysql_fetch_array($randomresult)) {
						$countr++;
					}
					$whodat = rand(1,$countr);
					$randomresult = mysql_query("SELECT `basename` FROM `Enemy_Types` LIMIT $whodat,1");
					$randrow = mysql_fetch_array($randomresult);
					$randenemy = $randrow['basename'];
					if (!empty($randenemy)) { //this happens sometimes, no idea why, but might as well treat that as a failure to activate
					$userrow['strifestatus'] = $currentstatus; //necessary because of spawnstatus
					$slot = generateEnemy($userrow,$userrow['grist_type'],"None",$randenemy,true);
					if ($slot != -1) {
						$userrow = refreshSingular($slot, $slot, $userrow);
						$currentstatus = $userrow['strifestatus']; //necessary because of spawnstatus
						$message = $message . "A rip in time and space opens, summoning a $randenemy to the battle!</br>";
					}
					}
				}
				break;
			case "Blade Cloud":
				$summonroll = (floor(100 * $userrow[$powerstr] / $userrow[$maxpowerstr]) + floor(100 * $userrow[$healthstr] / $userrow[$maxhealthstr])) / 2;
				$roll = rand(1,100);
				if ($roll > $summonroll) { //chance increases as blade cloud is weakened, either power-wise or health-wise
					$amount = floor(rand(3,9) / 3); //summons 1-2 or rarely 3 blades
					if ($amount == 1) echo "A blade falls out of the Blade Cloud!<br />";
					else echo "Some blades fall out of the Blade Cloud!<br />";
					$thisone = 0;
					while ($thisone < $amount) {
						$userrow['strifestatus'] = $currentstatus; //necessary because of spawnstatus
						$slot = generateEnemy($userrow,"None","None","Animated Blade",false);
						if ($slot != -1) $userrow = refreshSingular($slot, $slot, $userrow);
						$currentstatus = $userrow['strifestatus']; //necessary because of spawnstatus
						$thisone++;
					}
				}
				$absresult = mysql_query("SELECT `power` FROM `Captchalogue` WHERE `abstratus` LIKE 'bladekind%' OR `abstratus` LIKE '%, bladekind%'");
				$allblades = 0;
				while ($arow = mysql_fetch_array($absresult)) {
					$allblades++;
				}
				$choice = rand(1,$allblades);
				$absresult = mysql_query("SELECT `name`,`power` FROM `Captchalogue` WHERE `abstratus` LIKE 'bladekind%' OR `abstratus` LIKE '%, bladekind%' LIMIT $choice, 1");
				$arow = mysql_fetch_array($absresult);
				$arow['name'] = str_replace("\\", "", $arow['name']);
				$message = $message . $arow['name'] . " flies out of the Blade Cloud and stabs you!<br />";
				if ($userrow['invulnerability'] == 0) { //I COULD have it hit through invuln, but I'm not THAT mean
				  $damage = $damage + ceil($arow['power'] / 8); //max damage from this attack: 1250
				}
				break;
			case "Consort Necromancer":
				$roll = rand(1,100);
				if ($roll <= 25) {
					$quantity = floor(rand(0,9) / 3); //can be anywhere up to 3, with a chance of failing
					if ($quantity == 0) $message = $message . "The Necromancer attempts to raise more skeletons, but fails!<br />";
					elseif ($quantity == 1) $message = $message . "The Necromancer raises another skeletal minion!<br />";
					else $message = $message . "The Necromancer raises some skeletal minions!<br />";
					while ($quantity > 0) {
						$userrow['strifestatus'] = $currentstatus; //necessary because of spawnstatus
						$slot = generateEnemy($userrow,"None","None","Skeletal Consort",False);
						if ($slot != -1) { //Success
							$userrow = refreshSingular($slot, $slot, $userrow);
							$currentstatus = $userrow['strifestatus'];
						}
						$quantity--;
					}
				} elseif ($roll <= 35) {
					$message = $message . "The Necromancer curses you! It's just a string of insults, though. Unless you're emotionally sensitive, it doesn't do anything.<br />";
					//lol no effect
				} elseif ($roll <= 45) {
					$message = $message . "The Necromancer throws a bone at you! Oh man. This consort's really pulling out all the stops here.<br />";
					$damage = $damage + ceil($userrow['Gel_Viscosity'] / 100); //adds 1% damage
				} elseif ($roll <= 55) {
					$newroll = rand(1,100);
					if ($newroll <= 10) {
						$message = $message . "The Necromancer consults a dark grimoire and recites an incantation! Despite it stuttering a few times, you still feel the forces of death pressing against your soul.<br />";
						$damage = $damage + 100;
					} else {
						$message = $message . "The Necromancer consults a dark grimoire and recites an incantation, but totally butchers it so nothing happens. How adorably pitiful.<br />";
						//also no effect
					}
				}
				break;
			case "Burning Building":
				$roll = rand(1,100);
				if ($roll <= 10) {
					$userrow['strifestatus'] = $currentstatus; //necessary because of spawnstatus
					$slot = generateEnemy($userrow,"None","None","Burning Building",false);
					if ($slot != -1) {
						$userrow = refreshSingular($slot, $slot, $userrow);
						$currentstatus = $userrow['strifestatus']; //necessary because of spawnstatus
						$message = $message . "The fire spreads to another nearby building!</br>";
					}
				} elseif ($roll <= 30) {
					$message = $message . "The fire rages higher. The smoke is almost too much to bear!<br />";
					$damage = $damage * 2;
					$currentstatus .= $statustr . "BURNING:100|"; //apply another burning instance
				}
				break;
			default:
				break;
			}
			$specialstr = $statustr . "SPECIAL";
			if (strpos($currentstatus, $specialstr) !== false) { //this enemy has softcoded specials.
				$thisstatus = explode("|", $currentstatus);
				$st = 0;
				while (!empty($thisstatus[$st])) {
					$specialarray = explode(":", $thisstatus[$st]);
					$tstatustr = $specialarray[0] . ":";
					if ($tstatustr == $statustr && $specialarray[1] == "SPECIAL") {
						//generally, they'll look like this: ENEMY#:SPECIAL:<effect name>:<chance>:<additional parameters>|
						$roll = rand(1,100); //go ahead and do this here
						$chance = intval($specialarray[3]); //players won't have any additional resistance by default
						switch ($specialarray[2]) { //similar to weapon effects
							case NOCAP:
								if ($roll <= $chance) {
									$message .= $userrow[$enemystr] . "'s attack increases your vulnerability to massive damage!<br />";
									$currentstatus .= "PLAYER:NOCAP|";
								}
								break;
							case POISON:
								if ($roll <= $chance) {
									$message .= $userrow[$enemystr] . "'s attack poisons you!<br />";
									$currentstatus .= "PLAYER:POISON:" . $specialarray[4] . "|";
								}
								break;
							case BLIND:
								if ($roll <= $chance) {
									$message .= $userrow[$enemystr] . "'s attack blinds you temporarily!<br />";
									$currentstatus .= "PLAYER:BLIND|";
								}
								break;
							case CONFUSE:
								if ($roll <= $chance) {
									$message .= $userrow[$enemystr] . "'s attack clouds your mind!<br />";
									$currentstatus .= "PLAYER:CONFUSED|";
								}
								break;
							case STUN:
								if ($roll <= $chance) {
									$message .= $userrow[$enemystr] . "'s attack stuns you!<br />";
									$currentstatus .= "PLAYER:STUN|";
								}
								break;
							case BURNING:
								if ($roll <= $chance) {
									$message .= $userrow[$enemystr] . "'s attack sets you on fire!<br />";
									$currentstatus .= "PLAYER:BURNING:" . $specialarray[4] . "|";
								}
								break;
							default:
								break;
						}
					}
				}
			}
	    } ?>