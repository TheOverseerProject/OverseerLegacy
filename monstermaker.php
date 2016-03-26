<?php
require_once('includes/fieldparser.php'); //monstermaker implies fieldparser
function generateEnemy($userrow,$gristtype,$grist,$enemytype,$canusespecibus,$tospooky = false) { //Takes a userrow, enemy, and grist type and level, and adds the enemy to the user's enemies.
  //Grist entries are blank for non-grist enemies.
  $max_enemies = 50; //Max number of enemies per encounter. May need changing. Probably not.
  require_once("includes/SQLconnect.php");
  //$userrow = parseEnemydata($userrow);
  $username=$_SESSION['username'];
  $i = 1;
  $power = -1;
  $slot = -1;
  while($i <= $max_enemies) {
    $enemystr = "enemy" . strval($i) . "name";
    if ($userrow[$enemystr] == "") { //Room for an enemy here
      $slot = $i;
      $powerstr = "enemy" . strval($i) . "power";
      $maxpowerstr = "enemy" . strval($i) . "maxpower";
      $healthstr = "enemy" . strval($i) . "health";
      $maxhealthstr = "enemy" . strval($i) . "maxhealth";
      $descstr = "enemy" . strval($i) . "desc";
      $categorystr = "enemy" . strval($i) . "category";
      $enemyresult = mysql_query("SELECT * FROM Enemy_Types");
      while ($row = mysql_fetch_array($enemyresult)) {
	if ($row['basename'] == $enemytype) {
	  $enemyrow = $row;
	}
      }
      $power = $enemyrow['basepower']; //Set initial power.
      $health = $enemyrow['basehealth']; //Set initial health.
      if ($grist != "None") { //Enemy was assigned a grist type.
	$gristresult = mysql_query("SELECT * FROM Grist_Types");
	while ($row = mysql_fetch_array($gristresult)) {
	  if ($row['name'] == $gristtype) {
	    $gristrow = $row;
	  }
	}
	$rarity = 1;
	$raritystr = "grist" . strval($rarity);
	while ($gristrow[$raritystr] != $grist && $rarity < 10) {
	  $rarity++;
	  $raritystr = "grist" . strval($rarity);
	}
	$power = ($power * $rarity) + ($rarity * $rarity);
	$health = ($health * $rarity) + ($rarity * $rarity);
      }
      $sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
      $allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'");
      $chumroll = 0;
      while ($row = mysql_fetch_array($allyresult)) {
	if ($row['session_name'] == $userrow['session_name']) { //User in session found
	  $chumroll++;
	}
      }
      if ($tospooky) $prototypings = 0;
      else $prototypings = $enemyrow['prototypings'];
      $description = $enemyrow['description'];
      $prototyped = False;
      $protopower = 1;
      $protopositive = 0;
      if ($prototypings > 0) $description = $description . " It has appearance aspects from: ";
      while ($prototypings > 0) {
	$random = rand(1,$chumroll); //Pick a random player's prototypings.
	$sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
	$allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'");
	while ($random > 0 && $allyrow = mysql_fetch_array($allyresult)) {
	  if ($allyrow['session_name'] == $userrow['session_name']) $random--;
	}
	$preentry = $allyrow['pre_entry_prototypes'];
	while ($preentry > 0) {
	  $prototyped = True;
	  $preentrystr = "prototype_item_" . strval($preentry);
	  $description = $description . $allyrow[$preentrystr] . ", ";
	  $preentry--;
	}
	//Set prototyping strength here. If it's negative, divide. Otherwise, multiply.
	if ($allyrow['prototyping_strength'] != 0) {
	  if ($allyrow['prototyping_strength'] > 0) {
	    $protopower = $protopower * $allyrow['prototyping_strength'];
	    $protopositive += 1;
	  } else {
	    $protopower = floor($protopower / ($allyrow['prototyping_strength'] * -1));
	  }
	}
	$prototypings--;
      }
      if ($prototyped == True) { //Prototyping has occurred.
	$description = substr($description,0,-2) . ".";
	if ($protopositive > 0) $protopower = floor(pow($protopower,(1/$protopositive)) * $protopositive);
	$protopower -= 1; //So that it's zero for no-prototype enemies.
	$power = $power + $protopower;
	$health = $health + $protopower; //NOTE - Since weapons do not scale health, prototyping scales health relatively poorly. (Also note the uranium imp in the flash)
      } elseif ($enemyrow['prototypings'] > 0) { //Only prototyping enemies can be voided, and only they need "Nothing!" appended.
	$nullified = True;
	if (!$tospooky) {
		$sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
		$allyresult = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $sessioname . "'");
		while ($allyrow = mysql_fetch_array($allyresult)) {
	  	if ($allyrow['session_name'] == $userrow['session_name'] && $allyrow['pre_entry_prototypes'] == 0) {
	    	$nullified = False;
	  	}
		}
	}
	if ($nullified == True) { //Session is currently void.
	  $description = "It appears skeletal. You can only barely tell what type of enemy it is supposed to be.";
	  $health = $health * $health * $health; //Tons of health.
	} else {
	  $description = $description . "Nothing!";
	}
      }
      if ($enemyrow['prototypings'] > 0 && $grist != "None") {
	$enemyname = $grist . " " . $enemytype; //Prototype enemy, receives grist prefix.
      } else {
	$enemyname = $enemytype; //Non-prototyping enemy, used as is.
      }
      if ($enemyrow['canwield'] == 1 && $canusespecibus) { //this enemy might be wielding a weapon!
      	//echo "DEBUG: rolling up weapon chance<br />";
      	$xtracardchance = rand(1,100);
      	$luckmod = floor(($userrow['Luck'] + $userrow['Brief_Luck']) / 25);
      	//echo "DEBUG: roll: $xtracardchance - $luckmod<br />";
      	if ($xtracardchance <= 1 + $luckmod) { //1% chance of this happening, up to 5% at max luck
      	//echo "DEBUG: strife card rolled!<br />";
      		$weaponresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`power` < " . strval($power) . " AND `Captchalogue`.`abstratus` NOT LIKE '%notaweapon%';");
      		$weaponcount = 0;
      		while ($row = mysql_fetch_array($weaponresult)) {
      			$weaponcount++;
      		}
      		$randweapon = rand(1,$weaponcount);
      		$weaponresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`power` < " . strval($power) . " AND `Captchalogue`.`abstratus` NOT LIKE '%notaweapon%' LIMIT " . strval($randweapon) . ",1;");
      		$weaponrow = mysql_fetch_array($weaponresult);
      		$wname = str_replace("\\", "", $weaponrow['name']);
      		$description = $description . " It also appears to be wielding " . $wname . "!";
      		$power+=$weaponrow['power'];
      	}
      }
      if ($enemyname == "Blade Cloud") { //special case: this boss adds up the power from every existing bladekind weapon
        $absresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `abstratus` LIKE 'bladekind%' OR `abstratus` LIKE '%, bladekind%' ORDER BY `power` ASC");
        $alltotalpower = 0;
        while ($arow = mysql_fetch_array($absresult)) {
          $alltotalpower += $arow['power'];
        }
        $power = floor($alltotalpower / 3);
        $health = $alltotalpower;
      }
      if ($enemyname == "Animated Blade") { //special case: pick a random bladekind weapon from the database and bulid the stats around it
      	$weaponresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `abstratus` LIKE 'bladekind%' OR `abstratus` LIKE '%, bladekind%'");
      	$weaponcount = 0;
      	while ($row = mysql_fetch_array($weaponresult)) {
      		$weaponcount++;
      	}
      	$randweapon = rand(1,$weaponcount);
      	$weaponresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `abstratus` LIKE 'bladekind%' OR `abstratus` LIKE '%, bladekind%' LIMIT " . strval($randweapon) . ",1;");
      	$weaponrow = mysql_fetch_array($weaponresult);
      	$wname = str_replace("\\", "", $weaponrow['name']);
      	$description = $description . " This one appears to be " . $wname . ".";
      	$power = $weaponrow['power'];
      	$bonusrow = array($weaponrow['abstain'], $weaponrow['abjure'], $weaponrow['accuse'], $weaponrow['abuse'], $weaponrow['aggrieve'], $weaponrow['aggress'], $weaponrow['assail'], $weaponrow['assault']);
				$power += max($bonusrow);
      	$health = $weaponrow['power'] + $weaponrow['aggrieve'] + $weaponrow['aggress'] + $weaponrow['assail'] + $weaponrow['assault'] + $weaponrow['abuse'] + $weaponrow['accuse'] + $weaponrow['abjure'] + $weaponrow['abstain'];
      	if ($health < 0) $health = abs($health);
	if ($health == 0) $health = 1;
      }
      if (!empty($enemyrow['spawnstatus'])) { //enemy spawns with status effect, like the Burning Building
      	$allstati = explode("|", $enemyrow['spawnstatus']);
      	$j = 0;
      	while (!empty($allstati[$j])) {
      		$userrow['strifestatus'] .= "ENEMY" . strval($i) . ":" . $allstati[$j] . "|";
      		$j++;
      	}
      }
      $userrow[$enemystr] = $enemyname; //entire string will be escaped when data is written
      $userrow[$powerstr] = $power;
      $userrow[$maxpowerstr] = $power;
      $userrow[$healthstr] = $health;
      $userrow[$maxhealthstr] = $health;
      $userrow[$descstr] = $description;
      $userrow[$categorystr] = $gristtype;
      writeEnemydata($userrow);
      $i = $max_enemies + 1; //Done!
    } else {
      $i++; //Keep lookin';
    }
  }
  return $slot; //This will be -1 if no slot was found, or the slot the enemy was placed in if one was.
}
?>