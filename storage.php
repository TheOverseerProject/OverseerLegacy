<?php
require 'additem.php';
require_once("header.php");
require_once("includes/effectprinter.php");

if (empty($_SESSION['username'])) {
  echo "Log in to view and manipulate your storage.</br>";
} elseif ($userrow['dreamingstatus'] != "Awake") {
  echo "Your dream self can't access your house's storage!";
} else {
	if (strpos($userrow['storeditems'], "JUMPER.") !== false) $jumper = true;
	if (strpos($userrow['storeditems'], "ALCHEMITER.") !== false) $alchemiter = true;
if (empty($max_items)) $max_items = 50;
$maxstorage = $userrow['house_build_grist'] + 1000;
$s = 1;
while ($s <= $max_items) {
	$storstr = 'inv' . strval($s);
if (!empty($_POST[$storstr])) {
	$itemnom = str_replace("'", "\\\\''", $userrow[$storstr]);
	$stackcode[$s] = "none";
	if (strpos($itemnom, "(CODE:") !== false) {
		$stackcode[$s] = substr($itemnom, -9, 8);
		$oitemnom = substr($itemnom, 0, -16);
	} else $oitemnom = $itemnom;
	$itemresult = mysql_query("SELECT `captchalogue_code`,`name`,`size`,`abstratus`,`effects` FROM `Captchalogue` WHERE `Captchalogue`.`name` = '" . $oitemnom . "' LIMIT 1");
	$storerow = mysql_fetch_array($itemresult);
	if (strpos($itemnom, " (ghost image)") !== false) {
		$itemnom = str_replace(" (ghost image)", "", $itemnom);
  	echo "You go to store your " . $itemnom . ", but as it's a ghost image, it simply disappears upon being removed from your inventory.<br />";
  	mysql_query("UPDATE `Players` SET `" . $_POST['storeitem'] . "` = '' WHERE `Players`.`username` = '$username' LIMIT 1;");
  	$storecode[$s] = "nope";
 	} else {
		$itemnom = str_replace("\\", "", $itemnom);
		$itemnom = str_replace("''", "'", $itemnom);
		$storecode[$s] = $storerow['captchalogue_code'];
		$storename[$s] = $itemnom;
		$storesize[$s] = itemSize($storerow['size']);
		$storecomp[$s] = "";
		if ($stackcode[$s] != "none") {
			$storecomp[$s] .= "CODE=" . $stackcode[$s] . ".";
			$storename[$s] .= " (CODE:" . $stackcode[$s] . ")";
		}
		if (strpos($storerow['abstratus'], "computer") !== false) $storecomp[$s] .= "ISCOMPUTER.";
		$containertag = specialArray($storerow['effects'], "CONTAINER");
		if ($containertag[0] == "CONTAINER") {
			$contain[$s] = intval($containertag[1]);
		} else $contain[$s] = 0;
		$storagetag = specialArray($storerow['effects'], "STORAGE");
		if ($storagetag[0] == "STORAGE") $storecomp[$s] .= $storagetag[1];
		if ($oitemnom == "Punch Card Shunt" && $jumper && $alchemiter) { //this is a shunt, so apply shunt effect, but only if player has both alchemiter and jumper
			$itemresult = mysql_query("SELECT `captchalogue_code`,`name`,`effects` FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '$stackcode' LIMIT 1");
			$shuntrow = mysql_fetch_array($itemresult);
			$shunttag = specialArray($shuntrow['effects'], "SHUNT");
			if ($shunttag[0] != "SHUNT") {
				$shunttag = specialArray($shuntrow['effects'], "STORAGE"); //if no shunt effect exists, check for a storage effect and apply it
				if ($shunttag[0] == "STORAGE") $shunttag[0] = "SHUNT";
			}
			if ($shunttag[0] == "SHUNT") $storecomp[$s] .= $shunttag[1];
		}
 	}
} else $storecode[$s] = "nope";
$s++;
}
$boom = explode("|", $userrow['storeditems']);
$totalitems = count($boom);
$i = 0;
$space = 0;
$itemstored = false;
$itemget = false;
while ($i <= $totalitems) {
	$args = explode(":", $boom[$i]);
	$itemresult = mysql_query("SELECT `captchalogue_code`,`name`,`size`,`effects` FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '" . $args[0] . "' LIMIT 1");
	$irow = mysql_fetch_array($itemresult);
	if ($irow['captchalogue_code'] == $args[0]) { //Item found.
		$getstr = $args[0];
		if (strpos($args[2], "CODE=") !== false) {
			$thiscode = substr($args[2], 5, 8);
			$getstr .= ":" . $thiscode;
		} else $thiscode = "none";
		if (!empty($_POST[$getstr])) { //This is the item that the user wants to retrieve.
			echo "Retrieving " . $irow['name'] . "... ";
			$actualget = 0;
			$getquantity = intval($_POST[$getstr]);
			while ($getquantity > 0) {
				if ($args[1] > 0) {
					$invslot = addItem($irow['name'], $userrow, $thiscode);
					if ($invslot != "inv-1") {
						$args[1]--;
						$getquantity--;
						$actualget++;
						$userrow[$invslot] = $irow['name'];
						if (strpos($args[2], "CODE=") !== false) $userrow[$invslot] .= " (CODE:" . $thiscode . ")";
					} else {
						echo "Inventory is too full to retrieve that many. ";
						$getquantity = 0;
					}
				} else {
					echo "Not enough items in storage. ";
					$getquantity = 0;
				}
			}
			if ($actualget > 0) {
				$itemget = true; //retrieved at least one item successfully
				echo "Amount retrieved: $actualget</br>";
			}
		}
		$space += itemSize($irow['size']) * $args[1];
		$itemcode[$i] = $irow['captchalogue_code'];
		$itemnamunique[$i] = $irow['name'];
		$itemno[$i] = $args[1];
		$containertag = specialArray($irow['effects'], "CONTAINER");
		if ($containertag[0] == "CONTAINER") {
			$maxstorage += intval($containertag[1]) * $itemno[$i];
		}
		$itemcomp[$i] = $args[2];
		if (strpos($itemcomp[$i], "CODE=") !== false) $itemnamunique[$i] .= " (CODE:" . substr($itemcomp[$i], 5, 8) . ")";
	} else {
		echo "ERROR: Items with code " . $args[0] . " stored, but no matching item was found. This is probably a bug; please submit a report!</br>";
		logDebugMessage($username . " - has item with code " . $args[0] . " stored, but code wasn't found in database");
	}
	$i++;
}
$actualstore = 0;
if ($storecode != "nope") {
	$i = 0;
	$nospace = false;
	while ($i <= $totalitems) {
		if (strpos($itemcomp[$i], "CODE=") !== false) {
			$thiscode = substr($itemcomp[$i], 5, 8);
		} else $thiscode = "none";
		$s = 1;
		while ($s <= $max_items) {
			if ($itemcode[$i] == $storecode[$s] && $stackcode[$s] == $thiscode) { //User wants to store more of these items.
				$rslot = "inv" . strval($s);
				if ($space + $storesize[$s] <= $maxstorage + $contain[$s]) {
					$itemno[$i]++;
					$space += $storesize[$s];
					$maxstorage += $contain[$s];
					$actualstore++;
					$storecode[$s] = "nope";
					$itemstored = true;
					autoUnequip($userrow, "none", $rslot);
					mysql_query("UPDATE `Players` SET `$rslot` = '' WHERE `Players`.`username` = '$username' LIMIT 1");
					$userrow[$rslot] = "";
					echo $storename[$s] . " stored.<br />";
				} else {
					echo "Not enough space to store " . $storename[$s] . ".<br />";
					$nospace = true;
				}
			}
			$s++;
		}
		$i++;
	}
	if (!$itemstored && !$nospace) { //item not already found in storage, so a new entry must be created
		$s = 1;
		while ($s <= $max_items) {
			if (!empty($storecode[$s]) && $storecode[$s] != "nope") {
				$nextstore = $storecode[$s];
				$nextstack = $stackcode[$s];
				$t = 1;
				$storedone = false;
				while ($t <= $max_items) {
					$rslot = "inv" . strval($t);
					if ($nextstore == $storecode[$t] && $nextstack == $stackcode[$t]) {
						if ($space + $storesize[$t] <= $maxstorage + $contain[$t]) {
							$itemno[$i]++;
							$space += $storesize[$t];
							$maxstorage += $contain[$t];
							$actualstore++;
							$storecode[$t] = "nope";
							$itemstored = true;
							$storedone = true;
							autoUnequip($userrow, "none", $rslot);
							mysql_query("UPDATE `Players` SET `$rslot` = '' WHERE `Players`.`username` = '$username' LIMIT 1");
							$userrow[$rslot] = "";
							echo $storename[$t] . " stored.<br />";
						} else {
							echo "Not enough space to store " . $storename[$t] . ".<br />";
							$nospace = true;
						}
					}
					$t++;
				}
				if ($storedone) {
					$itemcode[$i] = $nextstore;
					$itemnamunique[$i] = $storename[$s];
					$itemcomp[$i] = $storecomp[$s];
					$i++;
					$totalitems++;
				}
			}
			$s++;
		}
		if ($actualstore > 0) {
			$itemstored = true; //stored at least one item successfully
			echo "Items stored: $actualstore</br>";
		}
	}
}
if ($itemget || $itemstored) { //there was a change to the storage string, so let's rebuild it and update it
	$i = 0; //yet again
	$newstring = "";
	while ($i <= $totalitems) {
		if (!empty($itemcode[$i]) && $itemno[$i] != 0)
		$newstring .= $itemcode[$i] . ":" . $itemno[$i] . ":" . $itemcomp[$i] . "|";
		$i++;
	}
	mysql_query("UPDATE `Players` SET `storeditems` = '$newstring' WHERE `Players`.`username` = '$username' LIMIT 1");
	$userrow['storeditems'] = $newstring;
	compuRefresh($userrow);
}
echo "Item Storage Service v some number.</br>";
echo "Items stored in your house:</br></br>";
echo '<form action="storage.php" method="post">To retrieve items, enter the quantity to retrieve in the box to the left of the desired item(s).<br />';
$i = 0;
while ($i <= $totalitems) {
	if (!empty($itemcode[$i]) && $itemno[$i] != 0) {
		if (strpos($itemnamunique[$i], "(CODE:") !== false) {
			echo '<input type="text" name="' . $itemcode[$i] . ':' . substr($itemnamunique[$i], -9, 8) . '" />' . $itemnamunique[$i] . " x " . strval($itemno[$i]) . "</br>";
		} else {
			echo '<input type="text" name="' . $itemcode[$i] . '" />' . $itemnamunique[$i] . " x " . strval($itemno[$i]) . "</br>";
		}
	}
	$i++;
}
echo '<input type="submit" value="Quickly retrieve items."></form></br>';
echo "</br>Storage space used: " . strval($space) . "/" . strval($maxstorage);
echo "</br>You can increase your maximum storage space by building up your house.</br></br>";
echo "You have the following items that provide an effect from storage:<br />";
$nothing = true;
$i = 0;
while ($i <= $totalitems) {
	$alleffects = explode(".", $itemcomp[$i]);
	$j = 0;
	while (!empty($alleffects[$j])) {
		$currentarray = explode("/", $alleffects[$j]);
		$ntohing = printStorageEffects($currentarray, false);
		if ($nothing == true)
		$nothing = false;
		$j++;
	}
	$i++;
}
if ($nothing == true) echo "Nothing! If you find an item that you think might help you from storage, try storing it.<br />";
echo "<br />";
echo '<form action="storage.php" method="post">Offload items from inventory:</br>';
$i = 1;
while ($i <= $max_items) {
	$inv = 'inv' . strval($i);
	if (!empty($userrow[$inv])) echo '<input type="checkbox" name="' . $inv . '" value="' . $inv . '" />' . $userrow[$inv] . '<br />';
	$i++;
}
echo '<input type="submit" value="Quickly relieve items."></form>';
}
require_once("footer.php");
?>