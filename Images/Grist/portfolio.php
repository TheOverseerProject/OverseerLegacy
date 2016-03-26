<?php
require 'log.php';
require 'additem.php';
session_start();
if (empty($_SESSION['username'])) {
  echo "Log in to view and manipulate your strife portfolio and options.</br>";
} else {
  $con = mysql_connect("localhost","theovers_DC","pi31415926535");
  if (!$con)
    {
      echo "Connection failed.\n";
      die('Could not connect: ' . mysql_error());
    }
  
  mysql_select_db("theovers_HS", $con);
  $username = $_SESSION['username'];
  $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $username . "'");
  while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
    if ($row['username'] == $username) { //Paranoia: Make sure something stupid didn't just happen.
      $userrow = $row;
    }
  }
  
  //--Begin equipping code here.--
  
  if (!empty($_POST['equipmain'])) { //User is equipping an item to their main hand.
    $equipname = str_replace("'", "\\\\''", $userrow[$_POST['equipmain']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $equipname . "'");
    while ($itemrow = mysql_fetch_array($itemresult)) {
      $itemname = $itemrow['name'];
      $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
      if ($itemname == $userrow[$_POST['equipmain']]) {
	$equippedmain = $_POST['equipmain']; //For use later.
	echo "You equip your $itemname as your main weapon.</br>"; //NOTE - Unauthorized equipping prevented by menu options not being there.
	mysql_query("UPDATE `Players` SET `equipped` = '" . $_POST['equipmain'] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	if ($itemrow['size'] == "large") { //Item is two-handed. Note that weapons bigger than "large" are classified as "notaweapon"
	  mysql_query("UPDATE `Players` SET `offhand` = '2HAND' WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Current weapon is two-handed.
	  $equippedoff = "2HAND";
	}
	if ($userrow['offhand'] == $equippedmain) { //Offhand weapon transferred to main hand.
	  mysql_query("UPDATE `Players` SET `offhand` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Move offhand weapon.
	}
      }
    }
  }
  if (!empty($_POST['equipoff'])) { //User is equipping an item to their offhand.
    $offname = str_replace("'", "\\\\''", $userrow[$_POST['equipoff']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $offname . "'");
    while ($itemrow = mysql_fetch_array($itemresult)) {
      $itemname = $itemrow['name'];
      $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
      if ($itemname == $userrow[$_POST['equipoff']]) {
	$equippedoff = $_POST['equipoff']; //For use later.
	echo "You equip your $itemname as your offhand weapon.</br>";
	if ($userrow['offhand'] == "2HAND") {
	  $userrow['equipped'] = ""; //Remove two-handed weapon if we equip to the offhand.
	}
	mysql_query("UPDATE `Players` SET `offhand` = '" . $_POST['equipoff'] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      }
    }
  }
  
  //--End equipping code here.--
  //NOTE - Equipping of unauthorized items is impossible due to them not appearing as options. Begin echeladder naming code here.
  
  if (!empty($_POST['echename'])) {
    $newrung = mysql_real_escape_string($_POST['echename']);
    $rungstr = "rung" . strval($userrow['Echeladder']);
    //mysql_query("UPDATE `Players` SET `Echeladder_Rung` = '" . $newrung . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Used to be for updating Echeladder rung. Now outdated.
    mysql_query("UPDATE `Echeladders` SET `" . $rungstr . "` = '" . $newrung . "' WHERE `Echeladders`.`username` = '$username' LIMIT 1 ;");
  }
  
  //--End echeladder naming code here. That was short! Begin resting code here.--
  
  if (!empty($_POST['rest'])) {
    if ($userrow['encounters'] > 0) { //Player has encounters.
      $newhp = $userrow['Health_Vial'] + ceil($userrow['Gel_Viscosity'] / 4);
      if ($newhp > $userrow['Gel_Viscosity']) $newhp = $userrow['Gel_Viscosity'];
      mysql_query("UPDATE `Players` SET `Health_Vial` = $newhp WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `encounters` = $userrow[encounters]-1 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      echo "You head back to your dwelling and take a nice, long rest.</br>";
      $rested = True;
    } else {
      echo "You do not have any encounters remaining and therefore cannot encounter any sleep.</br>";
    }
  }
  
  //--End resting code here. Begin new abstratus code here.--
  if (!empty($_POST['new_abstratus'])) {
    $i = 1;
    $assignsuccess = False;
    while($i <= $userrow['abstrati']) {
      $abstrastr = ("abstratus" . strval($i));
      if ($userrow[$abstrastr] == "") { //Unassigned abstrati reached, assign the selected abstratus here.
	mysql_query("UPDATE `Players` SET `" . $abstrastr . "` = '" . $_POST['new_abstratus'] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	$newabstratus = $_POST['new_abstratus'];
	$assignsuccess = True;
	echo "A kind abstratus has been instantiated to $newabstratus</br>";
	$i = $userrow['abstrati']; //Only assign one per click through.
      }
      $i++;
    }
    if ($assignsuccess == False) echo "You have no abstrati remaining to assign!</br>";
  }
  //End new abstratus code here.
  
  echo "Strife Portfolio Manager v0.0.1a. Please select a captchalogued weapon.</br>";
  echo "Abstrati available:</br>";
  $i = 1;
  $free = 0;
  while ($i <= $userrow['abstrati']) {
    $abstrastr = ("abstratus" . strval($i));
    if ($userrow[$abstrastr] != "") {
      echo $userrow[$abstrastr];
      echo "</br>";
    } else {
      $free++;
    }
    $i++;
  }
  if (!empty($newabstratus)) {
    echo "$newabstratus </br>";
    if ($free > 0) {
      $free--; //The new abstratus wasn't counted.
    }
  } else {
    $newabstratus = "None.";
  }
  echo "Abstrati unassigned: $free";
  echo '<form action="portfolio.php" method="post"><select name="new_abstratus">';
  $itemresult = mysql_query("SELECT * FROM Captchalogue ORDER BY abstratus");
  $currentabstratus = "";
  while ($itemrow = mysql_fetch_array($itemresult)) {
    $mainabstratus = "";
    $alreadydone = False;
    $foundcomma = False;
    $j = 0;
    if (strrchr($itemrow['abstratus'], ',') == False) {
      $mainabstratus = $itemrow['abstratus'];
    } else {
      while ($foundcomma != True) {
	$char = "";
	$char = substr($itemrow['abstratus'],$j,1);
	if ($char == ",") { //Found a comma. We know there is one because of the if statement above. Break off the string as the main abstratus.
	  $mainabstratus = substr($itemrow['abstratus'],0,$j);
	  $foundcomma = True;
	} else {
	  $j++;
	}
      }
    }
    if ($currentabstratus == $mainabstratus) {
      $alreadydone = True;
    } else {
      $currentabstratus = $mainabstratus;
    }
    if ($alreadydone == False && $mainabstratus != "notaweapon") { //New abstratus to add to the options.
      echo '<option value = "' . $mainabstratus . '">' . $mainabstratus . '</option>';
    }
  }
  echo '</select> <input type="submit" value="Assign it!" /> </form>';
  echo '<form action="portfolio.php" method="post"><select name="equipmain">';
  $reachinv = false;
  $invresult = mysql_query("SELECT * FROM Players");
  while ($col = mysql_fetch_field($invresult)) {
    $invslot = $col->name;
    if ($invslot == "inv1") { //Reached the start of the inventory.
      $reachinv = True;
    }
    if ($invslot == "abstratus1") { //Reached the end of the inventory.
      $reachinv = False;
    }
    if ($reachinv == True && $userrow[$invslot] != "") { //This is a non-empty inventory slot.
      $itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $captchalogue = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($row = mysql_fetch_array($captchalogue)) {
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$invslot] && $row['abstratus'] != "notaweapon") { //Item found in captchalogue database, and it is a weapon.
	  $i = 1;
	  while ($i <= $userrow['abstrati']) {
	    $itemabstrati = $row['abstratus'];
	    $abstrastr = ("abstratus" . strval($i));
	    while (strrchr($itemabstrati, ',') != False) { //Comma means there's still another abstratus in there to check.
	      $foundcomma = False;
	      $j = 0;
	      while ($foundcomma != True) {
		$char = "";
		$char = substr($itemabstrati,$j,1);
		if ($char == ",") { //Found a comma. We know there is one because of the if statement above. Remove the first abstratus for testing, leave the rest.
		  $abstratuscheck = substr($itemabstrati,0,$j); 
		  $itemabstrati = substr($itemabstrati,($j+2)); //Assume a space after the comma
		  $foundcomma = True;
		  if ($abstratuscheck == $userrow[$abstrastr] || $abstratuscheck == $newabstratus) { //User has existing matching abstratus
		    echo '<option value = "' . $invslot . '">' . $userrow[$invslot];
		    if ($row['size'] == "large") echo " (Two-handed)";
		    echo '</option>';
		    $i = $userrow['abstrati']; //Done.
		  }
		} else {
		  $j++;
		}
	      }
	    }
	    if ($itemabstrati == $userrow[$abstrastr] || $itemabstrati == $newabstratus) { //User has existing matching abstratus
	      echo '<option value = "' . $invslot . '">' . $userrow[$invslot] . '</option>';
	      $i = $userrow['abstrati']; //Done.
	    }
	    $i++;
	  }
	}
      }
    }
  }
  echo '</select> <input type="submit" value="Equip to main hand" /> </form>';
  echo '<form action="portfolio.php" method="post"><select name="equipoff">';
  $reachinv = false;
  $invresult = mysql_query("SELECT * FROM Players");
  while ($col = mysql_fetch_field($invresult)) {
    $invslot = $col->name;
    if ($invslot == "inv1") { //Reached the start of the inventory.
      $reachinv = True;
    }
    if ($invslot == "abstratus1") { //Reached the end of the inventory.
      $reachinv = False;
    }
    if ($reachinv == True && $userrow[$invslot] != "" && $invslot != $userrow['equipped'] && $invslot != $equippedmain) { //This is a non-empty inventory slot that isn't equipped to the main hand
      $itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $captchalogue = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($row = mysql_fetch_array($captchalogue)) {
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$invslot] && $row['abstratus'] != "notaweapon" && $row['size'] != "large") { //Item found in captchalogue database, and it is a weapon
	  $i = 1;
	  while ($i <= $userrow['abstrati']) {
	    $itemabstrati = $row['abstratus'];
	    $abstrastr = ("abstratus" . strval($i));
	    while (strrchr($itemabstrati, ',') != False) { //Comma means there's still another abstratus in there to check.
	      $foundcomma = False;
	      $j = 0;
	      while ($foundcomma != True) {
		$char = "";
		$char = substr($itemabstrati,$j,1);
		if ($char == ",") { //Found a comma. We know there is one because of the if statement above. Remove the first abstratus for testing, leave the rest.
		  $abstratuscheck = substr($itemabstrati,0,$j); 
		  $itemabstrati = substr($itemabstrati,($j+2)); //Assume a space after the comma
		  $foundcomma = True;
		  if ($abstratuscheck == $userrow[$abstrastr] || $abstratuscheck == $newabstratus) { //User has existing matching abstratus
		    echo '<option value = "' . $invslot . '">' . $userrow[$invslot] . '</option>';
		    $i = $userrow['abstrati']; //Done.
		  }
		} else {
		  $j++;
		}
	      }
	    }
	    if ($itemabstrati == $userrow[$abstrastr] || $itemabstrati == $newabstratus) { //User has existing matching abstratus
	      echo '<option value = "' . $invslot . '">' . $userrow[$invslot] . '</option>';
	      $i = $userrow['abstrati']; //Done.
	    }
	    $i++;
	  }
	}
      }
    }
  }
  echo '</select> <input type="submit" value="Equip to offhand" /> </form>';
  $echeresult = mysql_query("SELECT * FROM Echeladders WHERE `Echeladders`.`username` = '" . $username . "'");
  $echerow = mysql_fetch_array($echeresult);
  echo "Current Echeladder height: $userrow[Echeladder]";
  if (!empty($newrung)) {
    echo "</br>Current Echeladder rung: $newrung </br>";
  } else {
    $echestr = "rung" . strval($userrow['Echeladder']);
    if ($echerow[$echestr] != "") {
      echo "</br>Current Echeladder rung: $echerow[$echestr]</br>";
    } else {
      echo '<form action="portfolio.php" method="post">';
      echo 'Current Echeladder rung: <input id="echename" name="echename" type="text" /><input type="submit" value="Name it!" /> </form>';
    }
  }
  $mainpower = 0;
  $offpower = 0;
  $powerlevel = 0;
  $spritepower = $userrow['sprite_strength'];
  if ($equippedmain != "") {
    $itemname = str_replace("'", "\\\\''", $userrow[$equippedmain]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
    while ($row = mysql_fetch_array($itemresult)) {
      $itemname = $row['name'];
      $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
      if ($itemname == $userrow[$equippedmain]) {
	$mainpower = $row['power'];
      }
    }
  } else {
    if ($userrow['equipped'] != "") {
      $itemname = str_replace("'", "\\\\''", $userrow[$userrow['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($row = mysql_fetch_array($itemresult)) {
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$userrow['equipped']]) {
	  $mainpower = $row['power'];
	}
      }
    } else {
      $mainpower = 0;
    }
  }
  if ($equippedoff != "") {
    $itemname = str_replace("'", "\\\\''", $userrow[$equippedoff]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
    while ($row = mysql_fetch_array($itemresult)) {
      $itemname = $row['name'];
      $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
      if ($itemname == $userrow[$equippedoff]) {
	$offpower = ($row['power'] / 2);
      }
    }
  } else {
    if ($userrow['offhand'] != "" && $userrow['offhand'] != $equippedmain && $equippedoff != "2HAND") {
    $itemname = str_replace("'", "\\\\''", $userrow[$userrow['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($row = mysql_fetch_array($itemresult)) {
      $itemname = $row['name'];
      $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
      if ($itemname == $userrow[$userrow['offhand']]) {
	  $offpower = ($row['power'] / 2);
	}
      }
    } else {
      $offpower = 0;
    }
  }
  echo "Sprite's power level: $spritepower</br>";
  if ($spritepower < 0) {
    echo "Your sprite is useless in combat! You decide to leave it behind.</br>";
    $spritepower = 0;
  }
  if ($userrow['powerboost'] != 0) echo "Current temporary power modifier: $userrow[powerboost]</br>";
  if ($userrow['offenseboost'] != 0) echo "Current temporary offense modifier: $userrow[offenseboost]</br>";
  if ($userrow['defenseboost'] != 0) echo "Current temporary defense modifier: $userrow[defenseboost]</br>";
  $powerlevel = $userrow['Echeladder'] + $mainpower + $offpower + $spritepower + $userrow['powerboost'];
  echo "Current power level: $powerlevel </br>";
  if ($powerlevel == 9001) echo "(Yes, yes, very funny.)</br>";
  echo "Health Vial: ";
  if ($rested == True) {
    echo strval(floor(($newhp / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining.
    echo "%</br>";
    echo "Encounters remaining:";
    echo strval($userrow['encounters'] - 1);
  } else {
    echo strval(floor(($userrow['Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining.
    echo "%</br>";
    echo "Encounters remaining:";
    echo strval($userrow['encounters']);
  }
  echo '<form action="portfolio.php" method="post"><input type="hidden" name="rest" value="rest" /><input type="submit" value="Rest and recuperate" /></form></br>';
  $invresult = mysql_query("SELECT * FROM Players");
  echo $username;
  echo "'s captchalogued weapons:</br></br>";
  while ($col = mysql_fetch_field($invresult)) {
    $invslot = $col->name;
    if ($invslot == "inv1") { //Reached the start of the inventory.
      $reachinv = True;
    }
    if ($invslot == "abstratus1") { //Reached the end of the inventory.
      $reachinv = False;
    }
    if ($reachinv == True && $userrow[$invslot] != "") { //This is a non-empty inventory slot.
    $itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $captchalogue = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($row = mysql_fetch_array($captchalogue)) {
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$invslot] && $row['abstratus'] != "notaweapon") { //Item found in captchalogue database, and it is a weapon. Print out details.
	  echo "Weapon: $itemname</br>";
	  if ($row['art'] != "") {
	    echo '<img src="Images/Items/' . $row['art'] . '" title="Image by ' . $row['credit'] . '"></br>';
	  }
	  if (($invslot == $userrow['equipped'] || $invslot == $equippedmain) && $invslot != $equippedoff) { //Item is equipped in the main hand.
	    if ($invslot == $equippedmain || $equippedmain == "") { //Most recently equipped item is either this or nothing.
	      if ($row['size'] == "large") {
		echo "Equipped in: both hands.</br>";
	      } else {
		echo "Equipped in: main hand.</br>";
	      }
	    }
	  }
	  if (($invslot == $userrow['offhand'] || $invslot == $equippedoff) && $invslot != $equippedmain) { //Item is equipped in the offhand.
	    if ($invslot == $equippedoff || $equippedoff == "") { //Most recently equipped item is either this or nothing.
	      echo "Equipped in: offhand.</br>";
	    }
	  }
	  echo "Abstratus: $row[abstratus]</br>";
	  echo "Strength: $row[power]</br>";
	  if ($row['aggrieve'] > 0) {
	    echo "Aggrieve bonus: $row[aggrieve] </br>";
	  }
	  if ($row['aggrieve'] < 0) {
	    echo "Aggrieve penalty: $row[aggrieve] </br>";
	  }
	  if ($row['aggress'] > 0) {
	    echo "Aggress bonus: $row[aggress] </br>";
	  }
	  if ($row['aggress'] < 0) {
	    echo "Aggress penalty: $row[aggress] </br>";
	  }
	  if ($row['assail'] > 0) {
	    echo "Assail bonus: $row[assail] </br>";
	  }
	  if ($row['assail'] < 0) {
	    echo "Assail penalty: $row[assail] </br>";
	  }
	  if ($row['assault'] > 0) {
	    echo "Assault bonus: $row[assault] </br>";
	  }
	  if ($row['assault'] < 0) {
	    echo "Assault penalty: $row[assault] </br>";
	  }
	  if ($row['abuse'] > 0) {
	    echo "Abuse bonus: $row[abuse] </br>";
	  }
	  if ($row['abuse'] < 0) {
	    echo "Abuse penalty: $row[abuse] </br>";
	  }
	  if ($row['accuse'] > 0) {
	    echo "Accuse bonus: $row[accuse] </br>";
	  }
	  if ($row['accuse'] < 0) {
	    echo "Accuse penalty: $row[accuse] </br>";
	  }
	  if ($row['abjure'] > 0) {
	    echo "Abjure bonus: $row[abjure] </br>";
	  }
	  if ($row['abjure'] < 0) {
	    echo "Abjure penalty: $row[abjure] </br>";
	  }
	  if ($row['abstain'] > 0) {
	    echo "Abstain bonus: $row[abstain] </br>";
	  }
	  if ($row['abstain'] < 0) {
	    echo "Abstain penalty: $row[abstain] </br>";
	  }
	  echo "Description: $row[description]</br></br>";
	}
      }
    }
  }
}
?>
