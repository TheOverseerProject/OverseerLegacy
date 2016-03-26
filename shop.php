<?php
require_once("header.php");
require 'additem.php';
require 'includes/chaincheck.php';
require 'includes/pricesandvaules.php';
require 'includes/fieldparser.php';

$currentresultad = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_GET['land'] . "';");
$currentrowad = mysql_fetch_array($currentresultad);
$sonsort = $currentrowad['consort_name'];
$sona = explode(" ", $sonsort);
$bona = $sona[0];

//if (!empty($_GET['devcolor'])) $bona = $_GET['devcolor'];

if ($bona == "Aqua") { $conco = "#00DDDD"; }
if ($bona == "Black") { $conco = "#000000"; }
if ($bona == "Blue") { $conco = "#0000FF"; }
if ($bona == "Fuchsia") { $conco = "#FF00FF"; }
if ($bona == "Gray") { $conco = "#808080"; }
if ($bona == "Grey") { $conco = "#D3D3D3"; }
if ($bona == "Green") { $conco = "#008000"; }
if ($bona == "Lime") { $conco = "#00EE00"; }
if ($bona == "Maroon") { $conco = "#800000"; }
if ($bona == "Navy") { $conco = "#000080"; }
if ($bona == "Olive") { $conco = "#808000"; }
if ($bona == "Purple") { $conco = "#800080"; }
if ($bona == "Red") { $conco = "#FF0000"; }
if ($bona == "Silver") { $conco = "#C0C0C0"; }
if ($bona == "Teal") { $conco = "#008080"; }
if ($bona == "White") { $conco = "#FFFFFF"; }
if ($bona == "Yellow") { $conco = "#FFFF00"; }
if ($bona == "Orange") { $conco = "#FFA500"; }
if ($bona == "Pink") { $conco = "#FFC0CB"; }
if ($bona == "Gold") { $conco = "#FFD700"; }

if ($bona == "White" || $bona == "Yellow" || $bona == "Grey" || $bona == "Pink" || $bona == "Gold") {
	$altbgcol = "#666666";
	$altbdcol = "#444444";
} else {
	$altbgcol = "#EEEEEE";
	$altbdcol = "#E0E0E0";
}

$aonsort = $userrow['colour'];
$aona = explode(" ", $aonsort);
$dona = $aona[0];

if ($dona == "Aqua") { $concoa = "#00DDDD"; }
if ($dona == "Black") { $concoa = "#000000"; }
if ($dona == "Blue") { $concoa = "#0000FF"; }
if ($dona == "Fuchsia") { $concoa = "#FF00FF"; }
if ($dona == "Gray") { $concoa = "#808080"; }
if ($dona == "Grey") { $concoa = "#D3D3D3"; }
if ($dona == "Green") { $concoa = "#008000"; }
if ($dona == "Lime") { $concoa = "#00EE00"; }
if ($dona == "Maroon") { $concoa = "#800000"; }
if ($dona == "Navy") { $concoa = "#000080"; }
if ($dona == "Olive") { $concoa = "#808000"; }
if ($dona == "Purple") { $concoa = "#800080"; }
if ($dona == "Red") { $concoa = "#FF0000"; }
if ($dona == "Silver") { $concoa = "#C0C0C0"; }
if ($dona == "Teal") { $concoa = "#008080"; }
if ($dona == "White") { $concoa = "#FFFFFF"; }
if ($dona == "Yellow") { $concoa = "#FFFF00"; }
if ($dona == "Orange") { $concoa = "#FFA500"; }
if ($dona == "Pink") { $concoa = "#FFC0CB"; }
if ($dona == "Gold") { $concoa = "#FFD700"; }

?>

<style>
.shopketxt {
  background-color: <?php echo $altbgcol; ?>;
  border-color: <?php echo $altbdcol; ?>;
  color: <?php echo $conco; ?>;
}

.ratcha {
  box-shadow: 0px 0px 30px 5px <?php echo $conco; ?>;
}

input[type="radio"]:checked+label{
  box-shadow: 0px 0px 30px 5px <?php echo $concoa; ?>;
}

<?php
if (mdetect()) { ?>
.gatcha {
  max-width: 50%;
  margin: 0px;
}

.conshop {
  margin: 0px;
}
<?php } ?>

</style>

<div class="classyshop">

<?php if (mdetect()) { } else { ?>
<center>
<?php } ?>


<?php
if (empty($_SESSION['username'])) {
  echo "Log in to purchase items from your consorts.</br>";
} else {
	$gateresult = mysql_query("SELECT * FROM Gates"); //we'll need this to determine the level of the shops
  $gaterow = mysql_fetch_array($gateresult); //Gates only has one row.
  $result2 = mysql_query("SELECT * FROM `Players` LIMIT 1;"); //document grist types now so we don't have to do it later
  $reachgrist = False;
  $terminateloop = False;
  $totalgrists = 0;
  while (($col = mysql_fetch_field($result2)) && $terminateloop == False) {
    $gristtype = $col->name;
    if ($gristtype == "Build_Grist") { //Reached the start of the grists.
      $reachgrist = True;
    }
    if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
      $reachgrist = False;
      $terminateloop = True;
    }
    if ($reachgrist == True) {
      $gristname[$totalgrists] = $gristtype;
      $totalgrists++;
    }
  }
if ($userrow['dreamingstatus'] != "Awake") echo "The shops on your dream moon are useless to you as you'd have nowhere to put the purchased items - your sylladex is with your waking self!</br>";
elseif ($userrow['house_build_grist'] < $gaterow['gate1']) echo "You won't be able to find any shops without having access to at least one Gate.</br>";
else {
	$chain = chainArray($userrow);
	$totalchain = count($chain);
	if (empty($_GET['land'])) {
		echo '<form action="shop.php" method="get">Select a Land on which to go shopping:<select name="land"> ';
		$locationstr = "Land of " . $userrow['land1'] . " and " . $userrow['land2'];
	  echo '<option value="' . $userrow['username'] . '">' . $locationstr . '</option>';
    $landcount = 1; //0 should be the user's land which we already printed
    while ($landcount < $totalchain) {
    	$currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $chain[$landcount] . "';");
	    $currentrow = mysql_fetch_array($currentresult);
	  	$locationstr = "Land of " . $currentrow['land1'] . " and " . $currentrow['land2'];
	  	echo '<option value="' . $currentrow['username'] . '">' . $locationstr . '</option>';
	  	$landcount++;
    }
    echo '</select><input type="submit" value="Shop here"></form>';
    /*$debugitem = randomItem('Amber_Cost', 1000, $gristname, $totalgrists);
    $landresult = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '" . $currentrow['grist_type'] . "'");
  	$landrow = mysql_fetch_array($landresult);
    $debugprice = totalBooncost($debugitem, $landrow, $gristname, $totalgrists);
    echo strval($debugprice);*/
    echo '</br></br><a href="shtop.php">If you are having troubles with the shop for some reason, click here to use the simplified shop page.</a>';
	} else {
		$aok = false;
		if ($_GET['land'] == $username) $aok = true;
		else {
			$landcount = 1;
			while ($landcount < $totalchain && !$aok) {
				if ($chain[$landcount] == $_GET['land']) $aok = true; //verify that the chosen land is accessible by the user
				$landcount++;
			}
		}
		if (!$aok) echo "You can't reach that player's land with your current gate setup!</br>";
		else { //good to go!
			$currentresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_GET['land'] . "';");
	    $currentrow = mysql_fetch_array($currentresult);
	  	$locationstr = "Land of " . $currentrow['land1'] . " and " . $currentrow['land2'];
			echo "Shopping on: $locationstr</br>";
			if (!empty($currentrow['shopstock'])) { //shop is current and exists, so let's parse it.
			//We have to parse first in case the user is purchasing something; wouldn't want the shop to refresh right before the purchase goes through!
				$boom = explode("|", $currentrow['shopstock']);
				$maxshopitems = (count($boom) - 1); //the last one is an empty string so ignore
				$tsi = 0;
				while ($tsi < $maxshopitems) {
					$itemstuff = explode("==", $boom[$tsi]);
					$shopitem[$tsi] = $itemstuff[0];
					$shopprice[$tsi] = $itemstuff[1];
					$shoppower[$tsi] = $itemstuff[2];
					$shopkind[$tsi] = $itemstuff[3];
					$tsi++;
				}
			}

			if (!empty($_POST['buyitem'])) {
				$pid = intval(str_replace("buy", "", $_POST['buyitem']));
				if (empty($shopitem[$pid])) {}
				elseif ($userrow['Boondollars'] < $shopprice[$pid]) {}
				else { //player can afford the item and it exists
					$newitem = addItem($shopitem[$pid],$userrow);
					if ($newitem != "inv-1") { //player has room in their inventory
						$newboons = $userrow['Boondollars'] - $shopprice[$pid]; //make them pay.
						mysql_query("UPDATE `Players` SET `Boondollars` = $newboons WHERE `Players`.`username` = '$username' LIMIT 1;");
						echo "You purchase " . $shopitem[$pid] . " x1 for " . $shopprice[$pid] . " Boondollars.</br>";
						mysql_query("UPDATE `Players` SET `econony` = " . strval($currentrow['econony']+$shopprice[$pid]) . " WHERE `Players`.`username` = '" . $currentrow['username'] . "'");
					} else echo "You don't have room in your inventory for this item! You'll have to clear some space before you can buy it.</br>";
				}
			}
			
			$forcerefresh = false;
			if ($_GET['forcerefresh'] == "yes" && $userrow['session_name'] == "Developers") $forcerefresh = true;

			if (empty($currentrow['shopstock']) || (time() - $currentrow['lastshoptick'] > 86400) || $forcerefresh) { //the shop is empty or a day has passed since the shop was last refreshed
  			$shopgate = highestGate($gaterow, $currentrow['house_build_grist']);
  			if ($shopgate < 1) $shopgate = 1; //even on a land with no gates available, shops will stock as if there is at least one, in case the land is reached by a flying player for instance
  			$shopinflation = 1 + ((rand(90,110) - econonyLevel($currentrow['econony'])) / 100); //shop prices deviate +/- 10% from the norm
  			if ($shopinflation < 0.5) $shopinflation = 0.5;
  			$tsi = 0; //total shop items
  			$shopstring = "";
  			$maxshopitems = 3 + ($shopgate * 2) + rand(0,$shopgate); //the amount of items this shop will have when we're done
  			$landresult = mysql_query("SELECT * FROM `Grist_Types` WHERE `Grist_Types`.`name` = '" . $currentrow['grist_type'] . "'");
  			$landrow = mysql_fetch_array($landresult);
  			while ($tsi < $maxshopitems) {
  				$thisgrist = $landrow['grist' . strval(rand(1,9))]; //pick a random grist type from that land
  				$shopitemrow = randomItem($thisgrist . '_Cost', $gaterow['gate' . strval($shopgate)], $gristname, $totalgrists, "");
  				$shopitem[$tsi] = $shopitemrow['name'];
  				$shopprice[$tsi] = round(totalBooncost($shopitemrow, $landrow, $gristname, $totalgrists, $currentrow['session_name']) * $shopinflation);
  				$shoppower[$tsi] = $shopitemrow['power'];
  				$shopkind[$tsi] = $shopitemrow['abstratus'];
  				$shopstring = $shopstring . $shopitem[$tsi] . "==" . strval($shopprice[$tsi]) . "==" . strval($shoppower[$tsi]) . "==" . strval($shopkind[$tsi]) . "|";
  				$tsi++;
  			}
  			mysql_query("UPDATE `Players` SET `shopstock` = '$shopstring', `lastshoptick` = " . strval(time()) . " WHERE `Players`.`username` = '" . $currentrow['username'] . "'");
  			$currentrow['lastshoptick'] = time();
			}
			$time = 86400 - (time() - $currentrow['lastshoptick']);
			$seconds = $time % 60;
  		$minutes = floor($time/60) % 60;
  		$hours = floor($time/3600);
  		$hourstr = strval($hours);
  		while (strlen($hourstr) < 2) $hourstr = "0" . $hourstr;
  		$minutestr = strval($minutes);
  		while (strlen($minutestr) < 2) $minutestr = "0" . $minutestr;
  		$secondstr = strval($seconds);
  		while (strlen($secondstr) < 2) $secondstr = "0" . $secondstr;
  		$timestr = $hourstr . ":" . $minutestr . ":" . $secondstr;
			echo "This shop's stock will refresh in <span class='shopthr'>$hourstr</span>:<span class='shoptmin'>$minutestr</span>:<span class='shoptsec'>$secondstr</span>.</br></br>";
			//whether the shop was just created or parsed, we now have shop data so let's display it
			$drow = getDialogue("shop", $userrow, $currentrow['land1'], $currentrow['land2']);
			$shoptext = $drow['dialogue'];
			if (!empty($_POST['buyitem'])) {
                            if (empty($shopitem[$pid])) { $shoptext = "You're trying to buy a non-existant item!"; }
                            elseif ($userrow['Boondollars'] < $shopprice[$pid]) { $shoptext = "Sorry $username, I can't give credit! Come back when you're a little... mmm... RICHER!"; }
    			    else { $shoptext = "Thank you for your purchase!"; }
    			}
			echo "<div class='shopketxt'>SHOPKEEP: $shoptext</div>";
			echo '<form action="shop.php?land=' . $currentrow['username'] . '" method="post">';
			$csi = 0; //current shop item. no, not crime scene investigation.
			while ($csi < $tsi) {
				$canwield = matchesAbstratus($userrow['abstratus1'], $shopkind[$csi]);
				if ($canwield) {
					echo "This weapon is compatible with your strife specibus! ";
					$yourwpnpower = $mainpowera + $offpowera; //use power calculated from the header because we're efficient like that BL
					$powerdiff = $shoppower[$csi] - $yourwpnpower;
					if ($powerdiff < -9999) { $ramtext = "It looks like an utter piece of shit, though."; }
					elseif ($powerdiff < -5000) { $ramtext = "However, you outgrew the need for such flimsy weaponry long ago."; }
					elseif ($powerdiff < -1000) { $ramtext = "You doubt you could see any use out of it, though."; }
					elseif ($powerdiff < -100) { $ramtext = "It looks a bit weak for your needs."; }
					elseif ($powerdiff < 0) { $ramtext = "You think your current equipment might be better, but you never know..."; }
					elseif ($powerdiff == 0) { $ramtext = "It looks exactly as strong as your current equipment."; }
					elseif ($powerdiff > 9999) { $ramtext = "Whoa, where has this been all your life?!"; }
					elseif ($powerdiff > 9000) { $ramtext = "You'd be lucky to ever get your hands on one of these!"; }
					elseif ($powerdiff > 5000) { $ramtext = "It looks ridiculously stronger than your current weapon!"; }
					elseif ($powerdiff > 1000) { $ramtext = "You could definitely use something as strong as this!"; }
					elseif ($powerdiff > 100) { $ramtext = "It looks pretty strong. It'd probably be a decent upgrade."; }
					elseif ($powerdiff > 0) { $ramtext = "You think it might be a little stronger than what you're currently using, but you can't say for sure."; }
				} elseif (strrpos($shopkind[$csi], "headgear") || strrpos($shopkind[$csi], "facegear") || strrpos($shopkind[$csi], "bodygear") || strrpos($shopkind[$csi], "accessory")) {
					$ramtext = "This looks like something you can wear.";
				} elseif (strrpos($shopkind[$csi], "computer")) {
					$ramtext = "You think you can use this to communicate with your friends.";
				} elseif (!(strrpos($shopkind[$csi], "notaweapon") === false)) {
					$ramtext = "This item doesn't look like it can be equipped.";
				} else { $ramtext = "You can't wield this weapon."; }
				?>
                                <label class="conshop">
                                    <input type="radio" name="buyitem" id="buy<?php echo strval($csi); ?>" class="shpo" value="buy<?php echo strval($csi); ?>">
                                    <label class="gatcha">
                                        <span class="ripin"><?php echo $shopitem[$csi]; ?></span>
                                        <div class="descripa"><?php echo $ramtext; ?></div>
                                        <div class="raster"></div>
                                        <span class="ripinit"><?php echo strval($shopprice[$csi]); ?></span>
                                    </label>
                                </label>
                                <br/>
				<?php
				$csi++;
			}
			echo '<input type="submit" class="ratcha" value="Buy it!"></form>';
		}
		echo '</br></br><a href="shtop.php?land=' . $_GET['land'] . '">If you are having troubles with the shop for some reason, click here to use the simplified shop page.</a>';
	}
}
}

?>
<?php if (mdetect()) { } else { ?>
</center>
<?php } ?>
</div>
<?php
require_once("footer.php");
?>