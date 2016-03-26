<?php
require_once 'includes/glitches.php'; //For displaying glitchy nonsense
function printEffects($currentarray) {
	switch ($currentarray[0]) {
	case 'TIMESTOP':
		echo "Stops time: $currentarray[1]% probability on hit.</br>";
		break;
	case 'AFFINITY':
		echo "$currentarray[1] affinity: $currentarray[2]%</br>";
		break;
	case 'RESIST':
		echo "This item has a $currentarray[3]% chance to modify an enemy's $currentarray[1] resistance to $currentarray[2]% on hit.<br />";
		break;
	case 'WATERYGEL':
		echo "Waters down enemy Health Gel: $currentarray[1]% probability on hit.</br>";
		break;
	case 'POISON':
		echo "Inflicts poisoning: $currentarray[1]% probability on hit, $currentarray[2]% severity.</br>";
		break;
	case 'STORAGE':
		echo "When placed in storage, this device functions as:<br />";
		$alleffects = explode(".", $currentarray[1]);
		$j = 0;
		while (!empty($alleffects[$j])) {
			$futurearray = explode("/", $alleffects[$j]);
			printStorageEffects($futurearray, false);
			$j++;
		}
		break;
	case 'CODEHOLDER':
		echo "Can contain a code.</br>";
		break;
	case 'DEPLOYABLE':
		echo "Can be deployed by a server.</br>";
		break;
	case 'OBSCURED':
		echo "The colors and patterns on the back of the captcha card make this item's code unreadable!</br>";
		break;
	case 'SHRUNK':
		echo "Has a $currentarray[1]% chance to shrink enemies.</br>";
		break;
	case 'LIFESTEAL':
		echo "Has a $currentarray[1]% chance to restore $currentarray[2]% of the damage it deals as health on hit.</br>";
		break;
	case 'SOULSTEAL':
		echo "Has a $currentarray[1]% chance to restore $currentarray[2]% of the damage it deals as Aspect Vial on hit.</br>";
		break;
	case 'LOCKDOWN':
		echo "Has a $currentarray[1]% chance to inflict Lockdown for $currentarray[2] turns.</br>";
		break;
	case 'CHARMED':
		echo "Has a $currentarray[1]% chance to charm an opponent, lulling them to your side temporarily.</br>";
		break;
	case 'RANDAMAGE':
		echo "Damage dealt will vary by +/- $currentarray[1]%.</br>";
		break;
	case 'MISFORTUNE':
		echo "Has a $currentarray[1]% chance to inflict grave misfortune on enemies.</br>";
		break;
	case 'BLEEDING':
		echo "Wounds inflicted by this weapon have a $currentarray[1]% chance to bleed for $currentarray[2] rounds.</br>";
		break;
	case 'HOPELESS':
		echo "Attacks with this weapon cause enemies to lose hope with a $currentarray[1]% probability.</br>";
		break;
	case 'DISORIENTED':
		echo "This weapon can disorient foes for $currentarray[2] rounds with a $currentarray[1]% chance.</br>";
		break;
	case 'DISTRACTED':
		echo "This weapon can distract enemies, increasing the damage they take that round if this effect triggers. $currentarray[1]% probability.</br>";
		break;
	case 'ENRAGED':
		echo "Getting hit by this weapon has a $currentarray[1]% chance of making the target angry.</br>";
		break;
	case 'MELLOW':
		echo "This weapon has a calming effect $currentarray[1]% of the time.</br>";
		break;
	case 'KNOCKDOWN':
		echo "This weapon can knock enemies over with powerful swings.</br>";
		break;
	case 'GHOSTER':
		echo "This item is capable of creating ghost images. You will use it to create a ghost image automatically if you encounter an item that you cannot captchalogue. Or, go to the SBURB Devices page to use it on an item in storage.</br>";
		break;
	case 'GLITCHED':
		$glitchstr = horribleMess();
		echo "This weapon can cause victims to experience software bugs, which $glitchstr</br>";
		break;
	case 'GLITCHY':
		$glitchstr = horribleMess() . horribleMess();
		echo "This item has a $currentarray[1]% chance of $glitchstr during strife.</br>";
		break;
	case 'RECOIL':
		echo "This weapon has a $currentarray[1]% chance to inflict $currentarray[2]% recoil damage to the wielder when it hits an enemy.<br />";
		break;
	case 'COMPOUNDHIT':
		echo "This weapon is more effective against multiple targets.<br />";
		break;
	case 'HYBRIDMOD':
		$divisor = 10 * ($currentarray[1] / 100);
		$hybridpercent = round(100 / $divisor);
		echo "The power level of this item when used as a wearable is effectively $hybridpercent% of what it would be as a weapon.<br />";
		break;
	case 'SHUNT':
		echo "Has an effect when placed in a Punch Card Shunt.<br />";
		break;
	case 'LUCK':
		echo "Passively increases luck by $currentarray[1]% when equipped.<br />";
		break;
	case 'GRANT':
		echo "Grants a unique effect when equipped.<br />";
		break;
	case 'DVISION':
		if ($currentarray[1] == 1)
		echo "Grants increased vision in dungeons when equipped.<br />";
		else
		echo "When equipped, automatically reveals the entire dungeon map.<br />";
		break;
	case 'SVISION':
		echo "When equipped, allows you to view many more details about a given session.<br />";
		break;
	case 'PAYDAY':
		echo "Grants $currentarray[1]% bonus boondollars at the end of a battle.<br />";
		break;
	case 'PIERCING':
		echo "$currentarray[1]% of this weapon's power can pierce through enemy defenses.<br />";
		break;
	case 'HASABILITY':
		$rtresult = mysql_query("SELECT `ID`,`Name` FROM `Abilities` WHERE `Abilities`.`ID` = $currentarray[1]");
		$rtrow = mysql_fetch_array($rtresult);
		if (!empty($rtrow['Name'])) $roletech = $rtrow['Name'];
		else $roletech = "UNKNOWN";
		echo "Grants access to the roletech $roletech when equipped.<br />";
		break;
	case 'AMMO':
		if (intval($currentarray[3]) == 1) $freqstr = "every round";
		else $freqstr = "for every target";
		$ammoamount = intval($currentarray[2]);
		if ($ammoamount > 0) {
			echo "Requires $ammoamount $currentarray[1] $freqstr to function.<br />";
		} else {
			$ammoamount = $ammoamount * -1;
			echo "Generates $ammoamount $currentarray[1] $freqstr.<br />";
		}
		break;
	case 'VARBOOST':
		echo "Inflicts bonus damage based on your $currentarray[1].<br />";
		break;
	case 'BURNING':
		echo "Has a $currentarray[1]% chance to set an enemy on fire, dealing $currentarray[2] damage per round.<br />";
		break;
	case 'FREEZING':
		echo "Has a $currentarray[1]% chance to freeze an enemy, keeping them from acting until they break free.<br />";
		break;
	case 'SMITE':
		echo "Inflicts $currentarray[1]% bonus damage against undead/skeletal enemies.<br />";
		break;
	case 'CONTAINER':
		echo "Adds $currentarray[1] units of storage space while in storage.<br />";
		break;
	case 'RANDEFFECT':
		echo "Has a $currentarray[1]% chance to invoke a random effect with an effectiveness of $currentarray[2].<br />";
		break;
	//properties that shouldn't be echoed go here so that they don't trigger the error
	case 'NOCONSORT':
	case 'UPGRADE': //only used for the upgrade encyclopedia at the moment
	case 'DESCVAR':
	case 'FLAVORCOST':
		break;
	default:
		echo "Property $currentarray[0] unrecognized. The devs have been notified.</br>";
		return false; //so that the debugger can send a message with the apropriate vars
		break;
	}
	return true;
}

function printStorageEffects($currentarray, $fromjumper) {
	switch ($currentarray[0]) {
	case 'ISCOMPUTER':
		echo "- A computer that works from storage, allowing you to perform basic computing tasks while idle.<br />";
		break;
	case 'PUNCHDESIGNIX':
		echo "- A device that can punch captchalogue cards with codes for the purpose of alchemy.<br />";
		break;
	case 'CRUXTRUDER':
		echo "- A device that can produce fresh, uncarved Cruxite Dowels.<br />";
		break;
	case 'TOTEMLATHE':
		echo "- A device that can carve Cruxite Dowels and combine codes for the purpose of alchemy.<br />";
		break;
	case 'ALCHEMITER':
		echo "- A device that can alchemize items.<br />";
		break;
	case 'HOLOPAD':
		echo "- A device that can preview items.<br />";
		break;
	case 'HOLOPLUS':
		echo "- A preview device that gives you many more details than usual about items.<br />";
		break;
	case 'LASERSTATION':
		echo "- A device that can read captcha codes.<br />";
		break;
	case 'JUMPER':
		echo "- A jumper extension for your Alchemiter, allowing you to equip various upgrades.<br />";
		break;
	case 'USELESS':
		echo "- An \"upgrade\" that renders your Alchemiter functionally useless.<br />";
		break;
	case 'GRISTWIRE':
		echo "- A program that grants the ability to wire grist to other players.<br />";
		break;
	case 'RECYCLER':
		echo "- A device that breaks down items into their component grists.<br />";
		break;
	case 'GLITCHGATE':
		echo "- Some kind of strange gate that may or may not take you somewhere cool! Or dangerous. Or both.<br />";
		break;
	case 'DREAMBOT':
		echo "- A dreambot that gives you access to your computer while you sleep!<br />";
		break;
	case 'ADVSESSIONVIEW':
		echo "- A device that lets you see many more details about any given session, including your own.<br />";
		break;
	case 'CAPTCHACOMBINE':
		echo "- A device that automatically calculates the combined result of any two given codes.<br />";
		break;
	case 'REMOTEHOLO':
		echo "- A Holopad that accepts manually-typed codes, rather than taking cards or totems.<br />";
		break;
	case 'MANUALCHEMITER':
		echo "- An Alchemiter that allows you to type in the item's code manually.<br />";
		break;
	case 'CAPTCHASCAN':
		echo "- A device that can scan your sylladex and combine item codes.<br />";
		break;
	case 'CRUXBLEND':
		echo "- A device that can rapidly destroy Cruxite Dowels.<br />";
		break;
	case 'CARDSHRED':
		echo "- A device that can rapidly destroy Captchalogue Cards.<br />";
		break;
	case 'SENDIFICATOR':
		echo "- A device that allows you to transport items \"$currentarray[1]\" or smaller directly to the storage of another player from your session.<br />";
		break;
	case 'COMBOFINDER':
		if (intval($currentarray[2]) == 0) $amountstr = "all";
		else $amountstr = $currentarray[2];
		echo "- A device that scans your $currentarray[1], randomly combining codes. It then returns $amountstr working recipes. This process costs $currentarray[3] encounter(s).<br />";
		break;
	default:
		return true;
		break;
	}
	return false;
}

function descvarConvert($userrow, $desc, $var) { //no idea if ANY of this stuff is going to get used but might as well throw it in
	if (strpos($var, "DESCVAR") !== false) {
		$vararray = explode("|", $var);
		$i = 0;
		while (!empty($vararray[$i])) {
			$varararray = explode(":", $vararray[$i]);
			if ($varararray[0] == "DESCVAR") {
				$newthing = "";
				$replacer = $varararray[1];
				switch ($varararray[2]) {
				case 'TIME': //this is the one thing that will get used for sure
					$time = time();
					$seconds = $time % 60;
  				$minutes = floor($time/60) % 60;
  				$hours = floor($time/3600) % 24;
  				$hourstr = strval($hours);
	  			while (strlen($hourstr) < 2) $hourstr = "0" . $hourstr;
	  			$minutestr = strval($minutes);
  				while (strlen($minutestr) < 2) $minutestr = "0" . $minutestr;
  				$secondstr = strval($seconds);
	  			while (strlen($secondstr) < 2) $secondstr = "0" . $secondstr;
	  			$newthing = $hourstr . ":" . $minutestr . ":" . $secondstr;
					break;
				case 'SESSION':
					if (empty($sessionrow['name'])) {
						$sessionresult = mysql_query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "'");
						$sessionrow = mysql_fetch_array($sessionresult);
					}
					$newthing = $sessionrow[$varararray[3]];
					break;
				case 'CLIENT':
					if (empty($clientrow['name'])) {
						$sessionresult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '" . $userrow['client_player'] . "'");
						$clientrow = mysql_fetch_array($sessionresult);
					}
					$newthing = $clientrow[$varararray[3]];
					break;
				default:
					$newthing = $userrow[$varararray[2]];
					break;
				}
				$desc = str_replace("%DESCVAR_" . $replacer . "%", $newthing, $desc);
			}
			$i++;
		}
	}
	return $desc;
}

function npcEffects($npcstatus, $baseworth, $uniqueeffects) {
	$worth = $baseworth;
	$lolreturn = array(0 => "", 1 => 0, 2 => "", 3 => 0);
	if (strpos($npcstatus, "SPECIAL") !== false) {
  	$thatstatus = explode("|", $npcstatus);
  	$ts = 0;
  	while (!empty($thatstatus[$ts])) {
  		$specialarg = explode(":", $thatstatus[$ts]);
  		if ($specialarg[0] == "SPECIAL") {
  			switch ($specialarg[1]) {
  				case 'NOCAP':
  					$lolreturn[0] .= "This ally can water down enemy Health Gel: $specialarg[2]% probability on hit.</br>";
  					$worth += intval($specialarg[2]) * 10;
  					break;
  				case 'POISON':
  					$lolreturn[0] .= "This ally can inflict poisoning: $specialarg[2]% probability on hit, $specialarg[3]% severity.</br>";
  					$worth += intval($specialarg[2]) * intval($specialarg[3]) * 3;
  					break;
  				case 'CONFUSE':
  					$lolreturn[0] .= "This ally can disorient foes for $specialarg[3] rounds with a $specialarg[2]% chance.</br>";
  					$worth += intval($specialarg[2]) * intval($specialarg[3]) * 2;
  					break;
  				case 'STUN':
  					$lolreturn[0] .= "This ally can knock enemies over, stunning them for the round.<br />";
  					$worth += intval($specialarg[2]) * 5;
  					break;
  				case 'BURNING':
  					$lolreturn[0] .= "Has a $specialarg[2]% chance to set an enemy on fire, dealing $specialarg[3] damage per round.<br />";
  					$worth += intval($specialarg[2]) * intval($specialarg[3]);
  					break;
  				case 'HASEFFECT':
  					$imbue = str_replace("SPECIAL:HASEFFECT:", "", $thatstatus[$ts]); //cuts off the haseffect tag and adds everything else to bonuseffects
  					$lolreturn[0] .= "!!!REPLACE!!!";
  					$lolreturn[2] .= $imbue . "|";
  					$uniqueeffects++;
  					//printEffects(explode(":", $imbue));
  					$worth = $worth * 1.5;
  					break;
    			default:
    				break;
    		}
    	}
    	$ts++;
    }
	}
	$lolreturn[1] = floor($worth);
	$lolreturn[3] = $uniqueeffects;
	return $lolreturn;
}

?>