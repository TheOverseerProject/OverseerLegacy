<?php
require 'designix.php';
require 'additem.php';
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to access the catalogue.</br>";
} else {
  if ($userrow['dreamingstatus'] != "Awake") {
    echo "Your dream self can't access your sylladex!";
  } else {
    echo "<!DOCTYPE html><html><head><style>gristvalue{color: #FF0000; font-size: 60px;}</style></head><body>";
    require_once("includes/SQLconnect.php");
    
    $captchalogue = 0;
    $sessionname = $userrow['session_name'];
		$sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$sessionname'");
		$sessionrow = mysql_fetch_array($sessionresult);
		$challenge = $sessionrow['challenge'];
		$maxstorage = $userrow['house_build_grist'] + 1000;
    
    //Begin captchaloguing code here.

    if (!empty($_POST['catalogue'])) { //User captchalogues item.
    	if ($challenge == 1) $captchas = 50;
    	else $captchas = 500;
      if ($userrow['captchalogues'] < $captchas) {
      	if ($challenge == 1) {
      		$totalitems = 0;
      		if ($userrow['captchalogues'] == 0) $itemsresult = mysql_query("SELECT `name` FROM `Captchalogue` WHERE `Captchalogue`.`catalogue` = 1 AND `Captchalogue`.`abstratus` NOT LIKE '%notaweapon%' ;"); //user is guaranteed to get a weapon on their first try
      		elseif ($userrow['captchalogues'] == 1) $itemsresult = mysql_query("SELECT `name` FROM `Captchalogue` WHERE `Captchalogue`.`catalogue` = 1 AND `Captchalogue`.`abstratus` LIKE '%computer%' ;"); //user is guaranteed to get a computer on their second try
      		else $itemsresult = mysql_query("SELECT `name` FROM `Captchalogue` WHERE `Captchalogue`.`catalogue` = 1");
      		while ($row = mysql_fetch_array($itemsresult)) $totalitems++;
      		$item1 = rand(1,$totalitems);
      		$item1--;
      		if ($userrow['captchalogues'] == 0) $result = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`catalogue` = 1 AND `Captchalogue`.`abstratus` NOT LIKE '%notaweapon%' LIMIT $item1 , 1 ;");
      		elseif ($userrow['captchalogues'] == 1) $result = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`catalogue` = 1 AND `Captchalogue`.`abstratus` LIKE '%computer%' LIMIT $item1 , 1 ;");
      		else $result = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`catalogue` = 1 LIMIT $item1 , 1 ;");
      	} else {
	$result = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['catalogue'] . "'");
      	}
	while ($row = mysql_fetch_array($result)) {
	  if ($row['captchalogue_code'] == $_POST['catalogue'] || $_POST['catalogue'] == "random") {
	    if ($row['catalogue'] == 1) {
	      $itemadd = addItem($row['name'],$userrow);
	      //require_once("includes/SQLconnect.php");
	      if ($itemadd != "inv-1") {
					$captchalogue = 1;
					mysql_query("UPDATE `Players` SET `captchalogues` = '" . strval($userrow['captchalogues']+1) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
					echo $row['name'] . " captchalogued!</br>";
	      } else { //no space in inventory, time to see if we can store it
					$actualstore = storeItem($row['name'], 1, $userrow);
					if ($actualstore >= 1) { //if we stored more than one for some strange reason, better to take away a captchalogue as normal
						$captchalogue = 1;
						mysql_query("UPDATE `Players` SET `captchalogues` = '" . strval($userrow['captchalogues']+1) . "' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
						echo "Your inventory was full, so " . $row['name'] . " was sent to storage.</br>";
					} else {
						echo "Captchalogue failed: Your inventory is full, and there is insufficient space in storage.</br>";
					}
	      }
	    } else {
	      echo "Captchalogue failed: THAT ITEM IS NOT IN THE CATALOGUE, FUCKASS.</br>";
	    }
	  }
	}
      } else {
	echo "Captchalogue failed: You have no captchalogues remaining.</br>";
      }
    }
    
    //End captchaloguing code here.
    
    echo "Captchalogue Catalogue v0.0.1a. Select an item to captchalogue.</br>";
    if ($challenge == 1) echo 'WARNING - You only get 50 basic item captchalogues in Challenge Mode, so use them wisely!</br>';
    else echo 'WARNING - You "only" get 500 basic item captchalogues per game, so use them wisely!</br>';
    $captchalogue = $userrow['captchalogues'] + $captchalogue;
    echo "Captchalogues used: " . strval($captchalogue) . "</br>";
    if ($challenge == 1) {
    	echo 'In Challenge Mode, you receive a random base item every time you captchalogue something. Make the most out of what you have, and if all else fails, remember that you can always submit items. Good luck!</br>';
    	echo '<form action="catalogue.php" method="post"><input type="hidden" name="catalogue" value="random"><input type="submit" value="Find me an item!"></form>';
    } else {
    echo "Ordered alphabetically:";
    echo '<form action="catalogue.php" method="post"><select name="catalogue">';
    $result = mysql_query ("SELECT * FROM Captchalogue WHERE catalogue = 1 AND refrance = 0 ORDER BY name");
    while ($row = mysql_fetch_array($result)) {
      if ($row['catalogue'] != 0) { //Item is in the catalogue.
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($row['abstratus'] != "notaweapon") $itemname = $itemname . " - " . $row['abstratus'];
	echo '<option value="' . $row['captchalogue_code'] . '">' . $itemname . '</option>'; //Send off the captchalogue code. Seems like the way to do things.
      }
    }
    echo '</select> <input type="submit" value="Captchalogue it!" /> </form></br>';
    echo "Ordered by abstratus:";
    echo '<form action="catalogue.php" method="post"><select name="catalogue">';
    $result = mysql_query("SELECT * FROM `Captchalogue` WHERE catalogue = 1 AND refrance = 0 ORDER BY `Captchalogue`.`abstratus` ASC");
    while ($row = mysql_fetch_array($result)) {
      if ($row['catalogue'] != 0) { //Item is in the catalogue.
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($row['abstratus'] != "notaweapon") $itemname = $itemname . " - " . $row['abstratus'];
	echo '<option value="' . $row['captchalogue_code'] . '">' . $itemname . '</option>'; //Send off the captchalogue code. Seems like the way to do things.
      }
    }
    echo '</select> <input type="submit" value="Captchalogue it!" /> </form></br>';
    echo "Reference items:";
    echo '<form action="catalogue.php" method="post"><select name="catalogue">';
    $result = mysql_query ("SELECT * FROM Captchalogue WHERE catalogue = 1 AND refrance = 1 ORDER BY name");
    while ($row = mysql_fetch_array($result)) {
      if ($row['catalogue'] != 0) { //Item is in the catalogue.
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($row['abstratus'] != "notaweapon") $itemname = $itemname . " - " . $row['abstratus'];
	echo '<option value="' . $row['captchalogue_code'] . '">' . $itemname . '</option>'; //Send off the captchalogue code. Seems like the way to do things.
      }
    }
    echo '</select> <input type="submit" value="Captchalogue it!" /> </form></br>';
    }
  }
}
require_once("footer.php");
?>