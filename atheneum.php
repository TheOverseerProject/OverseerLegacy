<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to view the Atheneum.</br>";
} else {
  require_once("includes/SQLconnect.php");
 	echo "<!DOCTYPE html><html><head><style>itemcode{font-family:'Courier New'}</style></head><body>";
 	if ($_GET['show'] == "weapons") $showstring = "WHERE `Captchalogue`.`abstratus` NOT LIKE '%notaweapon%'";
 	elseif ($_GET['show'] == "wearable") $showstring = "WHERE `Captchalogue`.`abstratus` LIKE '%headgear%' OR `Captchalogue`.`abstratus` LIKE '%accessory%' OR `Captchalogue`.`abstratus` LIKE '%facegear%' OR `Captchalogue`.`abstratus` LIKE '%bodygear%'";
 	elseif ($_GET['show'] == "consume") $showstring = "WHERE `Captchalogue`.`consumable` = 1";
 	elseif ($_GET['show'] == "base") $showstring = "WHERE `Captchalogue`.`catalogue` = 1";
 	elseif ($_GET['show'] == "nonbase") $showstring = "WHERE `Captchalogue`.`catalogue` = 0";
 	elseif ($_GET['show'] == "all" || empty($_GET['show'])) $showstring = "";
 	else $showstring = "WHERE `Captchalogue`.`abstratus` LIKE '" . $_GET['show'] . "%' OR `Captchalogue`.`abstratus` LIKE '%, " . $_GET['show'] . "%'";
 	
  echo 'Session Atheneum</br>';
  echo 'All items acquired or previewed by players in your session will be shown here.</br>';
  echo '<a href="populateatheneum.php">Use this page to add all current inventory items acquired before the update</a></br></br>';
  echo 'View options:</br><a href="atheneum.php?show=all">All items</a> | <a href="atheneum.php?show=weapons">Weapons</a> | <a href="atheneum.php?show=notaweapon">Non-weapons</a> | <a href="atheneum.php?show=wearable">Wearables</a> | <a href="atheneum.php?show=consume">Consumables</a> | <a href="atheneum.php?show=base">Base items</a> | <a href="atheneum.php?show=nonbase">Non-base items</a></br>';
  echo '<form method="get" action="atheneum.php">Or search for an abstratus: <input type="text" name="show"><input type="submit" value="Search"></form></br></br>';
  if (!empty($_GET['testsession']) && $userrow['session_name'] == "Developers") {
  	$lookups = $_GET['testsession'];
  } else {
  	$lookups = $userrow['session_name'];
  }
  $sessionresult = mysql_query("SELECT `atheneum` FROM Sessions WHERE `Sessions`.`name` = '$lookups' LIMIT 1;");
  $sesrow = mysql_fetch_array($sessionresult);
  //echo $sesrow['atheneum'];
	$captcharesult = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue $showstring ORDER BY `name` ASC");
	$founditems = 0;
	$totalitems = 0;
	while ($crow = mysql_fetch_array($captcharesult)) {
		$totalitems++;
		if (strrpos($sesrow['atheneum'], $crow['captchalogue_code'])) {
			$founditems++;
			echo '<a href="inventory.php?holocode=' . $crow['captchalogue_code'] . '">' . $crow['name'] . ' - <itemcode>' . $crow['captchalogue_code'] . '</itemcode></a></br>';
		}
	}
	$percents = ($founditems / $totalitems) * 100;
	echo "</br>ITEMS FOUND: " . strval($founditems) . " / " . strval($totalitems) . " (" . strval($percents) . "%)</br>";
	if ($showstring == "") {
	echo "Prof. Blah's Analysis:</br>";
	if ($percents <= 1) echo "Try getting some base items from the catalogue!";
	elseif ($percents <= 2) echo "You've got a good starting assortment of items - time to mash them together randomly!";
	elseif ($percents <= 5) echo "You can find a lot of exotic items in the dungeons!";
	elseif ($percents <= 10) echo "Don't forget, you can find different kinds of loot from different dungeons. Explore as many lands as you can!";
	elseif ($percents <= 15) echo "Your session has discovered over one tenth of all the items this multiverse has to offer. That's nothing to sneeze at!";
	elseif ($percents <= 25) echo "Your collection is coming along quite nicely. Now's the perfect time to scour the Item List and find items within your reach!";
	elseif ($percents <= 35) echo "Now THAT is a collection! You and your friends are doing very well, but can you go ALL THE WAY...?";
	elseif ($percents <= 45) echo "AHA, the item pile doesnt stop from getting TALLER.";
	elseif ($percents <= 55) echo "How HIGH do you even have to BE just to DO something like that.........";
	elseif ($percents <= 65) echo "This selection has too many PRICES and VAULES!";
	elseif ($percents <= 75) echo "Ok jesus christ that is a lot of items. Seriously that's like way more than enough to play this game how do you even have the time???";
	elseif ($percents <= 85) echo "This is starting to get out of hand... I was only joking when I said you could go ALL THE WAY...";
	elseif ($percents <= 95) echo "MAYDAY! MAYDAY! ITEM COLLECTION REACHING CRITICAL MASS! BAIL, FOR THE LOVE OF HUSSIE, BAIL!!!";
	elseif ($percents < 100) echo "well at this point the only thing left to do is to get those last few items... we're all going to die anyway";
	else {
		echo "You've finally done it. You've acquired all of the items that exist in this game. If there was ever a session that deserved to beat the game... it's " . $userrow['session_name'] . ".</br>";
		echo "I guess there's only one thing to do now...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>...</br>";
		echo "SUBMIT MORE ITEMS??????????? :L";
	}
	}
}
require_once("footer.php");
?>