<?php
//NOTES: mainoff must be set outside this file before it is called. 
//werow is the row whose enemies this is checking against, used solely for echoing purposes. 
//since consumables only deal with status-inflicting effects, any effects that do other things alter the userrow as if we're on striferesolve.
if (empty($thisisconsumablepage)) $thisisconsumablepage = false;
$sresiststr = $statustr . ":RESIST"; //let's take care of this here
if (strpos($currentstatus, $sresiststr) !== false) {
	$statusarray = explode("|", $currentstatus);
	$p = 0;
	$instances = 0;
	while (!empty($statusarray[$p])) {
		$currentresist = explode(":", $statusarray[$p]);
		if ($currentresist[1] == "RESIST") {
			$resiststr = "resist_" . $currentresist[2];
			$enemyrow[$resiststr] = $currentresist[3]; //overwrite existing affinity resistance
		}
		$p++;
	}
}
$failedmessage = ""; //for relaying consumable effects that didn't work; these won't matter on striferesolve
while ($mainoff < 4) { //1 for main, 2 for off. 3 for bonus effects from HASEFFECT and etc. 4 means done.
	if ($mainoff == 1) { //Handle main hand effects.
		$effectarray = explode('|', $mainrow['effects']);
	} elseif ($mainoff == 2) { //Handle offhand effects.
		$effectarray = explode('|', $offrow['effects']);
	} else { //handle bonus effects from imbuements, roletechs, etc
		$effectarray = explode('|', $bonuseffects);
	}
	$effectnumber = 0;
	while (!empty($effectarray[$effectnumber])) {
		$currenteffect = $effectarray[$effectnumber];
		$currentarray = explode(':', $currenteffect); //Note that what each array entry means depends on the effect.
		if ($currentarray[0] == "RANDEFFECT") { //transform into a random effect, format is RANDEFFECT:<% chance>:<effectiveness>
			$currentarray[0] = getRandeffect();
			//for most effects, argument 2 is the amount of turns the effect will last if applicable
			if ($currentarray[0] == "BURNING") $currentarray[2] *= 100;
			if ($currentarray[0] == "POISON") $currentarray[2] /= 2;
			if ($currentarray[0] == "LIFESTEAL" || $currentarray[0] == "SOULSTEAL" || $currantarray[0] == "RECOIL") $currentarray[2] *= 20;
		}
		switch ($currentarray[0]) {
		case "TIMESTOP": //Format is TIMESTOP:<%chance>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Time'] / 100); //Enemy's time resistance reduces success chance.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) { //TIMESTOP has one argument: The chance of it working.
				$timestopped = True;
				$currentstatus = $currentstatus . $statustr . "TIMESTOP|";
				$message = $message . "$werow[$enemystr] is frozen in time!</br>";
			} else $failedmessage .= "$werow[$enemystr] resists the timestop effect!<br />";
			break;
		case "AFFINITY": //Format is AFFINITY:<affinity type>:<percentage>|
			if ($currentarray[1] == "All" && $userrow['aspect'] != "") $currentarray[1] == $userrow['aspect'];
			$resiststr = "resist_" . $currentarray[1]; //obscure affinities or RESIST status is taken care of at the top of this page
			$affinityfactor = $currentarray[2] / 100;
			if (!empty($enemyrow[$resiststr])) { //Apply resistance (it'll be 0 if the enemy doesn't resist, and nonexistent if no enemy can resist)
				$affinityfactor *= ((100 - $enemyrow[$resiststr]) / 100);
			}
			if ($currentarray[1] != $userrow['Aspect']) {
				$affinityfactor *= 0.8;
			}
			if ($affinityfactor < -1) $affinityfactor = -1; //Maximum of 100% absorption.
			$enemydamage = $enemydamage + floor($enemydamage * $affinityfactor);
			break;
		case "RESIST": //format is RESIST:<affinity type>:<change>:<%chance>|. Can be used to modify an enemy's resistance to an affinity.
			$roll = rand((1 + floor($luck/5)),100); //luck has a bit more of an effect on this
			$resiststr = "resist_" . $currentarray[1];
			$resistfactor = 1 - ($enemyrow[$resiststr] / 100); //if the enemy already has resistance to this, it'll be harder to change
			if ($roll > (100 - (intval($currentarray[3]) * $resistfactor))) {
				$currentstatus = $currentstatus . $statustr . "RESIST:$currentarray[1]:$currentarray[2]|";
				$message = $message . "$werow[$enemystr]'s resistance to $currentarray[1] is modified to $currentarray[2]%!</br>";
			} else $failedmessage .= "$werow[$enemystr]'s $currentarray[1] resistance remains the same.<br />";
			break;
		case "WATERYGEL": //Format is WATERYGEL:<%chance>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Life'] / 100); //Enemy's Life resistance reduces success chance.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) { //WATERYGEL has one argument: The chance of it working.
				$currentstatus = $currentstatus . $statustr . "WATERYGEL|";
				$message = $message . "$werow[$enemystr] appears to have lost some viscosity.</br>";
			} else $failedmessage .= "$werow[$enemystr] resists the watery gel effect!<br />";
			break;
		case "POISON": //Format is POISON:<%chance>:<%severity>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Doom'] / 100); //Enemy's Doom resistance reduces poison severity.
			if ($roll > (100 - intval($currentarray[1]))) { //Check to see if poison applies. Note that Doom resistance doesn't decrease this
				$severity = (ceil($currentarray[2] * $resistfactor * 100)) / 100; //Round to two decimal places.
				$currentstatus = $currentstatus . $statustr . "POISON:" . $severity . "|";
				$message = $message . "$werow[$enemystr] doesn't look too well...</br>";
			} else $failedmessage .= "$werow[$enemystr] resists the poison effect!<br />";
			break;
		case "SHRUNK": //Format is SIZECHANGE:<%chance>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Space'] / 100); //Space resistance reduces the chance of shrinking working
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$currentstatus = $currentstatus . $statustr . "SHRUNK|";
				$message = $message . "$werow[$enemystr] suddenly shrinks!</br>";
			} else $failedmessage .= "$werow[$enemystr] resists the shrinking effect!<br />";
			break;
		case "LOCKDOWN": //Format is LOCKDOWN:<%chance>:<turns>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Heart'] / 100); //Heart resistance reduces success chance
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$currentstatus = $currentstatus . $statustr . "LOCKDOWN:$currentarray[2]|"; //applying multiple instances will have no additional effect
				$message = $message . "$werow[$enemystr]'s connection to its abilities is severed!</br>";
			} else $failedmessage .= "$werow[$enemystr] resists the lockdown effect!<br />";
			break;
		case "CHARMED": //Format is CHARMED:<%chance>|
			$charmstr = ($statustr . "CHARMED|");
			if (strpos($currentstatus, $charmstr) === false) { //don't apply more than one instance period
				$roll = rand((1 + floor($luck/10)),100);
				$resistfactor = 1 - ($enemyrow['resist_Heart'] / 100); //Heart resistance reduces success chance
				if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
					$currentstatus = $currentstatus . $statustr . "CHARMED|";
					$message = $message . "$werow[$enemystr] is charmed temporarily to your side!</br>";
				} else $failedmessage .= "$werow[$enemystr] resists the charm effect!<br />";
			}
			break;
		case "LIFESTEAL": //Format is LIFESTEAL:<%chance>:<%absorbed>|. A lifesteal weapon, er, steals life.
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Blood'] / 100); //Blood resistance reduces the successful absorption
			if ($roll > (100 - intval($currentarray[1]))) {
				if ($resistfactor >= 0) {
					$message = $message . "Your attack siphons health gel from $werow[$enemystr]!</br>";
				} else {
					$message = $message . "Your attack siphons health gel from $werow[$enemystr]! Unfortunately, the gel harms you instead of healing you.</br>";
				}
				$healthgain = ceil(($enemydamage * ($currentarray[2] / 100)) * $resistfactor);
				//NOTE: We need to calculate this after all weapon effects that amplify damage.
				//Also, enemies with over 100 Blood resistance actually have poisonous health.
				if ($userrow[$healthvialstr] + $healthgain < $userrow['Gel_Viscosity']) { //Be careful not to overheal.
					$newhealth = $userrow[$healthvialstr] + $healthgain;
				} else {
					$newhealth = $userrow['Gel_Viscosity'];
				}
				$userrow[$healthvialstr] = $newhealth;
			}
			break;
		case "SOULSTEAL": //Format is SOULSTEAL:<%chance>:<%absorbed>|. works like Lifesteal except damage is converted to aspect vial
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Heart'] / 100); //Heart resistance reduces the successful absorption
			if ($roll > (100 - intval($currentarray[1]))) {
				if ($resistfactor >= 0) {
					$message = $message . "You absorb the damage on $werow[$enemystr] to fuel your aspect!</br>";
				} else {
					$message = $message . "You attempt to absorb the damage on $werow[$enemystr] as aspect power, but $werow[$enemystr]'s soul is so strong that it drains your Aspect Vial instead!</br>";
				}
				$healthgain = ceil(($enemydamage * ($currentarray[2] / 100)) * $resistfactor);
				//NOTE: We need to calculate this after all weapon effects that amplify damage.
				//much like lifesteal, over 100 heart resistance drains aspect instead
				if ($userrow['Aspect_Vial'] + $healthgain < $userrow['Gel_Viscosity']) { //Be careful not to overheal.
					$newhealth = $userrow['Aspect_Vial'] + $healthgain;
				} else {
					$newhealth = $userrow['Gel_Viscosity'];
				}
				$userrow['Aspect_Vial'] = $newhealth;
			}
			break;
		case "MISFORTUNE": //Format is MISFORTUNE:<%chance>|
			$roll = rand((1 + floor($luck/5)),100); //Luck is more effective at increasing this
			$resistfactor = 1 - ($enemyrow['resist_Light'] / 100); //Light resistance reduces the chance of misfortune working
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$currentstatus = $currentstatus . $statustr . "UNLUCKY|";
				$message = $message . "$werow[$enemystr] looks unlucky. Hm? What does unlucky look like? How should I know?</br>";
			} else $failedmessage .= "$werow[$enemystr] doesn't look any luckier or unluckier than before, whatever that means.<br />";
			break;
		case "RANDAMAGE": //format is RANDAMAGE:<%variance>|
			$roll = rand(($currentarray[1] * -1) + floor(($currentarray[1] * 2) * ($luck / 100)), $currentarray[1]); 
			//luck increases the minimum roll up to a maximum of... the maximum roll
			$enemydamage = $enemydamage * (($roll / 100) + 1); //multiply damage by this random percentage, which will stack
			break;
		case "BLEEDING": //Format is BLEEDING:<%chance>:<duration>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Blood'] / 100); //Enemy's Blood resistance reduces chance of application.
			//NOTE - May change this to modify duration instead of or as well as resisting application.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) { //Check to see if wound bleeds.
				$currentstatus = $currentstatus . $statustr . "BLEEDING:" . $currentarray[2] . "|";
				$message = $message . "$werow[$enemystr] is bleeding!</br>";
			} else $failedmessage .= "$werow[$enemystr] resists the bleeding effect!<br />";
			break;
		case "HOPELESS": //Format is HOPELESS:<%chance>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Hope'] / 100); //Enemy's Hope resistance reduces chance of application.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$currentstatus = $currentstatus . $statustr . "HOPELESS|";
				$message = $message . "$werow[$enemystr] looks dejected...</br>";
			} else $failedmessage .= "$werow[$enemystr] remains hopeful!<br />";
			break;
		case "DISORIENTED": //Format is DISORIENTED:<%chance>:<duration>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Mind'] / 100); //Enemy's Mind resistance reduces chance of application.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$currentstatus = $currentstatus . $statustr . "DISORIENTED:" . $currentarray[2] . "|";
				$message = $message . "$werow[$enemystr] is wandering around the battlefield in a daze!</br>";
			} else $failedmessage .= "$werow[$enemystr] resists the disoriented effect!<br />";
			break;
		case "DISTRACTED": //Format is DISTRACTED:<%chance>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Mind'] / 100); //Enemy's Mind resistance reduces chance of application.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$currentstatus = $currentstatus . $statustr . "DISTRACTED|";
				$message = $message . "$werow[$enemystr] looks away for a moment</br>";
			} else $failedmessage .= "$werow[$enemystr] manages to stay focused!<br />";
			break;
		case "ENRAGED": //Format is ENRAGED:<%chance>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Rage'] / 100); //Enemy's Rage resistance reduces chance of application.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$mellowstr = ($statustr . "MELLOW|");
				if (strpos($currentstatus, $mellowstr) !== False) { //Negate this instead of applying mellow
					$currentstatus = str_replace($mellowstr, "", $currentstatus);
				} else {
					$currentstatus = $currentstatus . $statustr . "ENRAGED|";
				}
				$message = $message . "$werow[$enemystr] looks really angry!</br>";
			} else $failedmessage .= "$werow[$enemystr] resists becoming enraged!<br />";
			break;
		case "MELLOW": //Format is MELLOW:<%chance>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Rage'] / 100); //Enemy's Rage resistance reduces chance of application.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$enragedstr = ($statustr . "ENRAGED|");
				if (strpos($currentstatus, $enragedstr) !== False) { //Negate this instead of applying mellow
					$currentstatus = str_replace($enragedstr, "", $currentstatus);
				} else {
					$justmellowed = True;
					$currentstatus = $currentstatus . $statustr . "MELLOW|";
				}
				$message = $message . "$werow[$enemystr] looks super chill, man...</br>";
			} else $failedmessage .= "$werow[$enemystr] resists the mellow effect!<br />";
			break;
		case "KNOCKDOWN": //Format is KNOCKDOWN:<%multiplier>|. Knockdown chance depends on damage dealt.
			//The multiplier effectively multiplies damage for the purposes of calculating the knockdown chance only.
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Breath'] / 100); //Enemy's Breath resistance reduces chance of application.
			//NOTE: Breath resistance covers "sudden force" in this case, so a weapon does not have to be Breath-y to have this effect.
			if (!empty($enemydamage)) $effdamage = $enemydamage;
			$target = 100 - ((($effdamage * (intval($currentarray[1])/100) * $resistfactor) / $userrow[$healthstr]) * 300); //33% effective damage = 100% chance
			if ($roll > $target) {
				$currentstatus = $currentstatus . $statustr . "KNOCKDOWN|";
				$message = $message . "$werow[$enemystr] is sent flying by the force of the blow!</br>";
			} else $failedmessage .= "$werow[$enemystr] stands firm!<br />";
			break;
		case "GLITCHED": //Format is GLITCHED:<%chance>|. NOTE - Glitching is a permanent status ailment. Please balance accordingly.
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Void'] / 100); //Enemy's Void resistance reduces chance of application.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$currentstatus = $currentstatus . $statustr . "GLITCHED|";
				$glitchstr = horribleMess();
				$message = $message . "$werow[$enemystr] appears somewhat $glitchstr</br>";
			} else $failedmessage .= "$werow[$enemystr] resists the glitchy effect!<br />";
			break;
		case "GLITCHY": //Format is GLITCHY:<%chance>|. Serves no purpose other than to randomly spew glitch messages :L
			$roll = rand(1,100);
			if ($roll < intval($currentarray[1]))
			$message = $message . generateGlitchString() . "<br />"; //yep, that's all it does
			break;
		case "RECOIL": //format is RECOIL:<%chance>:<%damage>|.
			$roll = rand(0,(100 - $luck)); //luck REDUCES chance for player to take recoil
			//possibly consider adding a roletech that helps resist?
			if ($roll > (100 - intval($currentarray[1]))) {
				$recoildamage += $enemydamage * ($currentarray[2] / 100);
				$message = $message . "You take some recoil damage from the attack!<br />";
				//note that recoil is applied AFTER enemy damage, and cannot kill the player
			}
			break;
		case "COMPOUNDHIT": //format is COMPOUNDHIT:<%effectiveness>|
			$enemydamage = $enemydamage * $numbersfactor * ($currentarray[1] / 100);
			break;
		case "PAYDAY": //format is PAYDAY:<% of enemy power>|
			$bonusboons += ceil($userrow[$maxpowerstr] * ($currentarray[1] / 100));
			break;
		case "AMMO": //Format is AMMO:<type of ammo>:<amount>:<enemy/round>|. Amount can be negative.
		if (empty($ammocheck[$mainoff])) {
			$ammoamount = intval($currentarray[2]);
			if (strpos($currentarray[1], "enemy0") !== false) { //putting enemy0 in front automatically causes it to target the current enemy; for example, enemy0health will increase the health of each enemy in turn
				$getstr = $werow[$enemystr] . " receives " . str_replace("enemy0", "", $currentarray[1]);
				$currentarray[1] = str_replace("enemy0", "enemy" . strval($i), $currentarray[1]);
			} else $getstr = "You receive $currentarray[1]";
			if ($ammoamount < 0) { //we'll handle negative values on a successful hit so that they can't be abused (as easily)
				$userrow[$currentarray[1]] -= $ammoamount; //Yes, the ammo type will literally be the name of a player field. This can lead to some interesting effects!
				if (intval($currentarray[3]) == 1) $ammocheck[$mainoff] = "good";
				if ($mainoff == 1)
				$message = $message . $getstr . " from your $mainrow[name]!<br />";
				if ($mainoff == 2)
				$message = $message . $getstr . " from your $offrow[name]!<br />";
				if ($mainoff == 3)
				$message = $message . $getstr . "!<br />";
			}
		}
		break;
		case "VARBOOST": //format is VARBOOST:<variable>:<% of which to add as damage>|
			$boost = floatval($currentarray[2]);
			$bonusdamage = ceil($userrow[$currentarray[1]] * ($boost / 100));
			if ($bonusdamage > 0) {
				$enemydamage += $bonusdamage;
				$message = $message . "Your $currentarray[1] inflicts bonus damage on $werow[$enemystr]!<br />";
			}
			break;
		case "BURNING": //Format is BURNING:<%chance>:<damage>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Rage'] / 100); //Enemy's Rage resistance reduces chance of application.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$frozenstr = ($statustr . "FROZEN|");
				if (strpos($currentstatus, $frozenstr) !== False) { //Negate this instead of applying burn
					$currentstatus = str_replace($frozenstr, "", $currentstatus);
					$message = $message . "The fire melts the ice around $werow[$enemystr].</br>";
				} else {
					$currentstatus = $currentstatus . $statustr . "BURNING:$currentarray[2]|";
					$message = $message . "$werow[$enemystr] catches fire!</br>";
				}
			} else $failedmessage .= "$werow[$enemystr] resists the burning effect!<br />";
			break;
		case "FREEZING": //Format is FREEZING:<%chance>|
			$roll = rand((1 + floor($luck/10)),100);
			$resistfactor = 1 - ($enemyrow['resist_Void'] / 100); //Enemy's Void resistance reduces chance of application.
			if ($roll > (100 - (intval($currentarray[1]) * $resistfactor))) {
				$burnstr = ($statustr . "BURNING");
				if (strpos($currentstatus, $burnstr) !== False) { //Negate this instead of applying burn
					$currentstatus = str_replace($burnstr, "", $currentstatus);
					$message = $message . "The sheer coldness puts out $werow[$enemystr]'s fire.</br>";
				} else {
					$currentstatus = $currentstatus . $statustr . "FROZEN|";
					$message = $message . "$werow[$enemystr] becomes encased in ice!</br>";
				}
			} else $failedmessage .= "$werow[$enemystr] resists the freezing effect!<br />";
			break;
		case "SMITE": //Format is SMITE:<% of bonus>|
			$bonusdamage = 0;
			if ($userrow[$descstr] == "It appears skeletal. You can only barely tell what type of enemy it is supposed to be.") {
				$bonusdamage = ceil($enemydamage * ($currentarray[1] / 100)); //treated as having -100 resist_Life, so double the damage at 100%
			} else {
				$bonusdamage = ceil($enemydamage * (($enemyrow['resist_Life'] * -1) / 100) * ($currentarray[1] / 100));
			}
			if ($bonusdamage > 0) { //smite has no effect on anything with positive/zero resist_Life
				$enemydamage += $bonusdamage;
				$message = $message . "Your weapon smites " . $werow[$enemystr] . "!<br />";
			}
			break;
		default:
			//assume this is some kind of player effect and apply it automatically, but only if we're on the consumable page
			if ($thisisconsumablepage) $currentstatus .= $effectarray[$effectnumber] . "|";
			break;
		}
		$effectnumber++;
	}
	$mainoff++;
}
if ($thisisconsumablepage) $message .= $failedmessage;

?>