<?php
require_once("header.php");
require_once("time.php");
require_once("includes/grist_icon_parser.php");
require_once("includes/chaincheck.php");

$seisopen = false; //set to false to close the stock exchange to the public

function initGrists() {
	$result2 = mysql_query("SELECT * FROM `Captchalogue` LIMIT 1;"); //document grist types now so we don't have to do it later
  $reachgrist = False;
  $terminateloop = False;
  $totalgrists = 0;
  while (($col = mysql_fetch_field($result2)) && $terminateloop == False) {
    $gristcost = $col->name;
    $gristtype = substr($gristcost, 0, -5);
    if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
      $reachgrist = True;
    }
    if ($gristcost == "End_Of_Grists") { //Reached the end of the grists.
      $reachgrist = False;
      $terminateloop = True;
    }
    if ($reachgrist == True) {
      $gristname[$totalgrists] = $gristtype;
      $totalgrists++;
    }
  }
  return $gristname;
}

function getBasecost($gristname) {
	switch ($gristname) {
		case "Build_Grist":
			return 20;
			break;
		
		case "Rust":
		case "Shale":
		case "Amber":
		case "Chalk":
		case "Uranium":
		case "Frosting":
		case "Blood":
		case "Jet":
		case "Cobalt":
		case "Copper":
		case "Amethyst":
		case "Iodine":
			return 40;
			break;
			
		case "Marble":
		case "Malachite":
		case "Tar":
		case "Rock_Candy":
		case "Gold":
		case "Sunstone":
			return 60;
			break;
			
		case "Ruby":
		case "Topaz":
		case "Mercury":
			return 80;
			break;
			
		case "Obsidian":
		case "Rose_Quartz":
		case "Sulfur":
		case "Redstone":
		case "Garnet":
			return 100;
			break;
			
		case "Emerald":
		case "Caulk":
			return 120;
			break;
			
		case "Quartz":
		case "Titanium":
			return 140;
			break;
			
		case "Diamond":
		case "Star_Sapphire":
			return 160;
			break;
			
		case "Polychromite":
		case "Opal":
			return 180;
			break;
			
		case "Rainbow":
			return 200;
			break;
			
		case "Artifact_Grist":
			return 0;
			break;
			
		default:
			return -1;
			break;
	}
}

if ($seisopen == false) {
	echo "The Stock Exchange is currently closed due to balance issues. This is non-negotiable and it will re-open when these issues have been resolved. Please be patient.<br />";
} elseif (empty($_SESSION['username'])) {
	echo "Log in to use the Grist Stock Exchange.</br>";
} else {
	echo "<!DOCTYPE html><html><head><style>defunct{color: #CC0000;}</style><style>clarify{color: #CCCC00;}</style><style>greenlit{color: #00AA00;}</style></head><body>";
	
	$sessionresult = mysql_query("SELECT `exchangeland` FROM `Sessions` WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "'");
	$sessionrow = mysql_fetch_array($sessionresult);
	if (empty($sessionrow['exchangeland'])) {
		if ($userrow['econony'] >= 4000000) {
			if (!empty($_POST['setupland'])) {
				mysql_query("UPDATE `Sessions` SET `exchangeland` = '$username' WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "'");
				echo "The Stock Exchange has been established on the Land of $userrow[land1] and $userrow[land2]! You and your sessionmates will now be able to trade with the multiverse and, if you play your cards right, strike it rich!<br />";
				echo "<a href='gristexchange.php'>==&gt;</a>";
			} else {
				echo "Your session has yet to establish its Stock Exchange facilities.<br />";
				echo "Your land's economy is strong enough to support them!<br />";
				echo "Note that only players who can reach the land the Stock Exchange is on can use it.<br />";
				echo "You can only set up the Stock Exchange on one land in your session, so if you're sure you want it on your land, click the following button to get started.<br />";
				echo "<form action='gristexchange.php' method='post'><input type='hidden' name='setupland' value='yes' /><input type='submit' value='Set up the Stock Exchange on The Land of $userrow[land1] and $userrow[land2]' /></form>";
			}
		} else {
			echo "Your session has yet to establish its Stock Exchange facilities.<br />";
			echo "Your land's economy is not yet strong enough to support them.<br />";
			echo "If you want to set them up on your land, you will need to raise its economy value first.<br />";
			echo "You can raise a land's economy by buying from its shops or doing quests on it.<br />";
		}
	} else {
		$canreach = false;
		if ($username == $sessionrow['exchangeland']) $canreach = true;
		else {
			$chain = chainArray($userrow);
			$i = 0;
			while (!empty($chain[$i]) && !$canreach) {
				if ($chain[$i] == $sessionrow['exchangeland']) $canreach = true;
				$i++;
			}
		}
		if (!$canreach) {
			echo "You cannot reach the stock exchange on $sessionrow[exchangeland]'s land at the moment.<br />";
		} else {
	
	//This will register which abilities the player has in $abilities. The standard check is if (!empty($abilities[ID of ability to be checked for>]))
    $abilityresult = mysql_query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') 
	AND `Abilities`.`Rungreq` BETWEEN 0 AND $userrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $userrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
    $abilities = array(0 => "Null ability. No, not void.");
    while ($temp = mysql_fetch_array($abilityresult)) {
		$abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
		//as pulling the correct element out of the abilities array via the ID. Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
    }
    
  if (!empty($abilities[27])) { //player has Make a Killing
  	$timeenabled = true;
  } else $timeenabled = false;
	
	$gristname = initGrists();
	$totalgrists = count($gristname);
	$updatetime = 21600; //holds the amount of time between stock updates (in seconds), currently 6 hours
	
	$forceupdate = false;
	$forcereset = false;
	if ($userrow['session_name'] == "Developers" || $userrow['session_name'] == "Itemods") {
		if (empty($_GET['xchange'])) $xchangename = "Dev";
		else $xchangename = $_GET['xchange'];
	if (!empty($_GET['devaction'])) {
		if ($_GET['devaction'] == "reset")
		$forcereset = true;
		elseif ($_GET['devaction'] == "update")
		$forceupdate = true;
		elseif ($_GET['devaction'] == "scramble") {
			$stockresult = mysql_query("SELECT * FROM `Grist_Exchange` WHERE `name` = '$xchangename'");
			$stockrow = mysql_fetch_array($stockresult);
			$i = 0;
			while ($i < $totalgrists) {
				$buystr = $gristname[$i] . "_bought";
				$sellstr = $gristname[$i] . "_sold";
				$supplystr = $gristname[$i] . "_supply";
				$newbuy = rand(1,1000000);
				$newsell = rand(1,1000000);
				$newsupply = $stockrow[$supplystr] + $newsell - $newbuy;
				mysql_query("UPDATE `Grist_Exchange` SET `$buystr` = $newbuy, `$sellstr` = $newsell, `$supplystr` = $newsupply WHERE `Grist_Exchange`.`name` = '$xchangename'");
				$stockrow[$buystr] = $newbuy;
				$stockrow[$sellstr] = $newsell;
				$stockrow[$supplystr] = $newsupply;
				$i++;
			}
		}
	}
	} else {
	$xchangename = "Default";
	}
	
	$stockresult = mysql_query("SELECT * FROM `Grist_Exchange` WHERE `name` = '$xchangename'");
	$stockrow = mysql_fetch_array($stockresult);
	
	if ($forcereset) {
		$i = 0;
		while ($i < $totalgrists) { //second loop is for actual price calculation
			$griststr = $gristname[$i] . "_price";
			$buystr = $gristname[$i] . "_bought";
			$sellstr = $gristname[$i] . "_sold";
			$changestr = $gristname[$i] . "_change";
			$supplystr = $gristname[$i] . "_supply";
			$newprice = getBasecost($gristname[$i]);
			mysql_query("UPDATE `Grist_Exchange` SET `$griststr` = $newprice, `$buystr` = 0, `$sellstr` = 0, `$changestr` = 0, `$supplystr` = 10000000 WHERE `Grist_Exchange`.`name` = '$xchangename'");
			$stockrow[$griststr] = $newprice;
			$stockrow[$buystr] = 0;
			$stockrow[$sellstr] = 0;
			$stockrow[$changestr] = 0;
			$stockrow[$supplystr] = 10000000;
			$i++;
		}
	}
	
	$currenttime = time();
	if ($stockrow['tick'] + $updatetime < $currenttime || $forceupdate) { //time to update the stocks!
		$i = 0;
		$ratiocap = 0;
		$newprice = 0;
		$pricehike = 0;
		while ($i < $totalgrists) { //second loop is for actual price calculation
			$thechange = 1;
			$pricehike = 0;
			//echo "<br />" . $gristname[$i];
			$griststr = $gristname[$i] . "_price";
			$buystr = $gristname[$i] . "_bought";
			$sellstr = $gristname[$i] . "_sold";
			$changestr = $gristname[$i] . "_change";
			$supplystr = $gristname[$i] . "_supply";
			if ($stockrow[$sellstr] == 0) $stockrow[$sellstr] = 1; //divide by zero protection
			if ($stockrow[$buystr] == 0) $stockrow[$buystr] = 1; //anti-price zeroing
			$ratio = $stockrow[$buystr] / $stockrow[$sellstr]; //Ratio of demand to supply
			$activity = $stockrow[$buystr] + $stockrow[$sellstr]; //Grist moved this tick.
			$netactivity = abs($stockrow[$buystr] - $stockrow[$sellstr]); //Net grist change from buying and selling
			$shortage = $activity - $stockrow[$supplystr]; //Comparison of grist moved to grist stored. If positive, implies a shortage.
			if ($shortage > 0) $pricehike = 2; //If there's a shortage, the price increases.
			if ($shortage < 0 && $ratio < 1) { //No shortage and supply outstrips demand: Price drops
				$pricehike = -2;
			}
			if ($stockrow[$supplystr] == 0) $stockrow[$supplystr] = 1; //divide by zero protection
			$sensitivity = $netactivity / $stockrow[$supplystr]; //Ratio of net activity to remaining grist supplies
			if ($sensitivity > 1) $sensitivity = 1; //Prices cannot become more sensitive: If they would, price-hiking kicks in instead.
			$sensitivity = pow($ratio, ($sensitivity - 1)); //(1 / $ratio) if sensitivity is 0. 1 if sensitivity is 1.
			$thechange = $ratio * $sensitivity;
			if ($thechange > 2) $thechange = 2;
			if ($thechange < 0.5) $thechange = 0.5;
			$newprice = ($stockrow[$griststr] * $thechange) + $pricehike;
			if ($newprice < 1) $newprice = 1;
			if ($gristname[$i] == "Artifact_Grist") $newprice = 0; //Artifact will always be worth absolutely nothing but it's on the exchange because of reasons
			$difference = $newprice - $stockrow[$griststr];
			mysql_query("UPDATE `Grist_Exchange` SET `$griststr` = $newprice, `$buystr` = 0, `$sellstr` = 0, `$changestr` = $difference WHERE `Grist_Exchange`.`name` = '$xchangename'");
			$stockrow[$griststr] = intval($newprice * 100) / 100;
			$stockrow[$buystr] = 0;
			$stockrow[$sellstr] = 0;
			$stockrow[$changestr] = intval($difference * 100) / 100;
			$i++;
		}
		while ($stockrow['tick'] + $updatetime < $currenttime) {
			$stockrow['tick'] += $updatetime;
		}
		mysql_query("UPDATE `Grist_Exchange` SET `tick` = $stockrow[tick] WHERE `Grist_Exchange`.`name` = '$xchangename'");
	}
	
	if (!empty($_POST['buygrist'])) {
		$quantity = $_POST['buyamount'];
		if ($quantity > 0) {
			$griststr = $_POST['buygrist'] . "_price";
			$supplystr = $_POST['buygrist'] . "_supply";
			$xchangerate = $stockrow[$griststr];
			$changestr = $_POST['buygrist'] . "_change";
			if ($xchangerate != -1) {
				$stop = false;
				if (!empty($_POST['oldprices']) && $timeenabled) {
					$xchangerate -= $stockrow[$changestr];
					if ($userrow['encounters'] < 1) {
						echo "You don't have a spare encounter to send yourself back in time.<br />";
						$stop = true;
					} else {
						echo $abilities[27] . "<br />";
						$userrow['encounters'] -= 1;
						mysql_query("UPDATE `Players` SET `encounters` = $userrow[encounters] WHERE `Players`.`username` = '$username'");
					}
				}
				$totalcost = ceil($xchangerate * $quantity);
				if (!$stop) {
				if ($userrow['Boondollars'] >= $totalcost) {
					if ($stockrow[$supplystr] > 0) {
						if ($stockrow[$supplystr] - $quantity < 0) {
							$quantity = $stockrow[$supplystr];
							echo "The Exchange seems to only have $quantity $_POST[buygrist] in stock.<br />";
							$totalcost = ceil($xchangerate * $quantity);
						}
						$leftoverboon = $userrow['Boondollars'] - $totalcost;
						$newgrist = $userrow[$_POST['buygrist']] + $quantity;
						mysql_query("UPDATE Players SET `Boondollars` = $leftoverboon, `" . $_POST['buygrist'] . "` = $newgrist WHERE `Players`.`username` = '$username' LIMIT 1;");
						$buystr = $_POST['buygrist'] . "_bought";
						$stockrow[$buystr] += $quantity;
						$stockrow[$supplystr] -= $quantity;
						mysql_query("UPDATE `Grist_Exchange` SET `$buystr` = $stockrow[$buystr], `$supplystr` = $stockrow[$supplystr] WHERE `Grist_Exchange`.`name` = '$xchangename'");
						echo "You have purchased $quantity " . $_POST['buygrist'] . " for $totalcost.</br>";
					} else echo "The Exchange is out of " . $_POST['buygrist'] . "! You'll have to wait until the stock replenishes.<br />";
				} else echo "It costs $totalcost boondollars to buy $quantity " . $_POST['buygrist'] . ". You don't have enough!</br>";
				} else echo "The transaction could not be performed.<br />";
			} else echo "You don't seem to have given a grist type that exists.</br>";
		} else echo "You can't buy zero or a negative amount of grist!</br>";
	}
	if (!empty($_POST['sellgrist'])) {
		$quantity = $_POST['sellamount'];
		if ($quantity > 0) {
			$griststr = $_POST['sellgrist'] . "_price";
			$xchangerate = $stockrow[$griststr];
			$changestr = $_POST['buygrist'] . "_change";
			if ($xchangerate != -1) {
				$stop = false;
				if (!empty($_POST['oldprices']) && $timeenabled) {
					$xchangerate -= $stockrow[$changestr];
					if ($userrow['encounters'] < 1) {
						echo "You don't have a spare encounter to send yourself back in time.<br />";
						$stop = true;
					} else {
						echo $abilities[27] . "<br />";
						$userrow['encounters'] -= 1;
						mysql_query("UPDATE `Players` SET `encounters` = $userrow[encounters] WHERE `Players`.`username` = '$username'");
					}
				}
				$totalcost = ceil($xchangerate * $quantity);
				if (!$stop) {
				if ($userrow[$_POST['sellgrist']] >= $quantity) {
					$leftoverboon = $userrow['Boondollars'] + $totalcost;
					$newgrist = $userrow[$_POST['sellgrist']] - $quantity;
					mysql_query("UPDATE Players SET `Boondollars` = $leftoverboon, `" . $_POST['sellgrist'] . "` = $newgrist WHERE `Players`.`username` = '$username' LIMIT 1;");
					$sellstr = $_POST['sellgrist'] . "_sold";
					$supplystr = $_POST['sellgrist'] . "_supply";
					$stockrow[$sellstr] += $quantity;
					$stockrow[$supplystr] += $quantity;
					mysql_query("UPDATE `Grist_Exchange` SET `$sellstr` = $stockrow[$sellstr], `$supplystr` = $stockrow[$supplystr] WHERE `Grist_Exchange`.`name` = '$xchangename'");
					echo "You have sold $quantity " . $_POST['sellgrist'] . " for $totalcost.</br>";
				} else echo "You don't have that much " . $_POST['sellgrist'] . ".</br>";
				} else echo "The transaction could not be performed.<br />";
			} else echo "You don't seem to have given a grist type that exists.</br>";
		} else echo "You can't sell zero or a negative amount of grist!</br>";
	}
	
	if ($timeenabled) {
		$i = 0;
		$ratiocap = 0;
		$newprice = 0;
		while ($i < $totalgrists) { //second loop is for actual price calculation
			$thechange = 1;
			$pricehike = 0;
			//echo "<br />" . $gristname[$i];
			$griststr = $gristname[$i] . "_price";
			$buystr = $gristname[$i] . "_bought";
			$sellstr = $gristname[$i] . "_sold";
			$changestr = $gristname[$i] . "_change";
			$supplystr = $gristname[$i] . "_supply";
			if ($stockrow[$sellstr] == 0) $stockrow[$sellstr] = 1; //divide by zero protection
			if ($stockrow[$buystr] == 0) $stockrow[$buystr] = 1; //insanity protection
			$ratio = $stockrow[$buystr] / $stockrow[$sellstr]; //Ratio of demand to supply
			$activity = $stockrow[$buystr] + $stockrow[$sellstr]; //Grist moved this tick.
			$netactivity = abs($stockrow[$buystr] - $stockrow[$sellstr]); //Net grist change from buying and selling
			$shortage = $activity - $stockrow[$supplystr]; //Comparison of grist moved to grist stored. If positive, implies a shortage.
			if ($shortage > 0) $pricehike = 2; //If there's a shortage, the price increases.
			if ($shortage < 0 && $ratio < 1) { //No shortage and supply outstrips demand: Price drops
				$pricehike = -2;
			}
			if ($stockrow[$supplystr] == 0) $stockrow[$supplystr] = 1; //divide by zero protection
			$sensitivity = $netactivity / $stockrow[$supplystr]; //Ratio of net activity to remaining grist supplies
			if ($sensitivity > 1) $sensitivity = 1; //Prices cannot become more sensitive: If they would, price-hiking kicks in instead.
			$sensitivity = pow($ratio, ($sensitivity - 1)); //(1 / $ratio) if sensitivity is 0. 1 if sensitivity is 1.
			$thechange = $ratio * $sensitivity;
			$newprice = ($stockrow[$griststr] * $thechange) + $pricehike;
			if ($newprice < 1) $newprice = 1;
			if ($gristname[$i] == "Artifact_Grist") $newprice = 0; //Artifact will always be worth absolutely nothing but it's on the exchange because of reasons
			$difference = $newprice - $stockrow[$griststr];
			$projectedchange[$i] = intval($difference * 100) / 100;
			$i++;
		}
	}
	$lresult = mysql_query("SELECT `land1`,`land2` FROM `Players` WHERE `Players`.`username` = '$sessionrow[exchangeland]'");
	$lrow = mysql_fetch_array($lresult);
	$landshort = "LO";
	$boom = explode(" ", $lrow['land1']);
	$bcount = 0;
	while ($bcount <= count($boom)) {
		$landshort .= strtoupper(substr($boom[$bcount], 0, 1));
		$bcount++;
	}
	$landshort .= "A";
	$boom = explode(" ", $lrow['land2']);
	$bcount = 0;
	while ($bcount <= count($boom)) {
		$landshort .= strtoupper(substr($boom[$bcount], 0, 1));
		$bcount++;
	}
	echo "The $landshort Stock Exchange<br />The Stock Exchange is a place where you can buy or sell grist of any sort for Boondollars. Each grist has a given price, which will increase or decrease every update based on how much of it is bought or sold across the multiverse, and how much of it is left in stock.<br />";
	$updatestr = produceTimeString($updatetime - ($currenttime - $stockrow['tick']));
	echo "Prices will update in $updatestr.<br />";
	if ($timeenabled) echo "You have the ability to see how the prices will change next update, if everything stays the way it is now.<br />";
	echo '<table border="1" bordercolor="#CCCCCC" style="background-color:#EEEEEE" width="100%" cellpadding="3" cellspacing="3">';
  echo '<tr><td>Grist</td><td>Boons/Unit</td><td>Change</td>';
  if ($timeenabled) {
  	echo '<td>Projection</td>';
  }
  if ($userrow['session_name'] == "Developers") {
  	echo '<td>Bought</td><td>Sold</td><td>Supply</td>';
  }
  echo '</tr>';
	$i = 0;
	while ($i < $totalgrists) {
		$griststr = $gristname[$i] . "_price";
		$buystr = $gristname[$i] . "_bought";
		$sellstr = $gristname[$i] . "_sold";
		$changestr = $gristname[$i] . "_change";
		$supplystr = $gristname[$i] . "_supply";
		if ($stockrow[$changestr] > 0) $change = "<greenlit>+$stockrow[$changestr]</greenlit>";
		elseif ($stockrow[$changestr] < 0) $change = "<defunct>$stockrow[$changestr]</defunct>";
		else $change = "<clarify>$stockrow[$changestr]</clarify>";
		echo "<tr><td><img src='/Images/Grist/".gristNameToImagePath($gristname[$i])."' height='15' width='15' alt = 'xcx'/>$gristname[$i]</td><td>$stockrow[$griststr]</td><td>$change</td>";
		if ($timeenabled) {
			if ($projectedchange[$i] > 0) $pchange = "<greenlit>+$projectedchange[$i]</greenlit>";
			elseif ($projectedchange[$i] < 0) $pchange = "<defunct>$projectedchange[$i]</defunct>";
			else $pchange = "<clarify>$projectedchange[$i]</clarify>";
			echo "<td>$pchange</td>";
		}
		if ($userrow['session_name'] == "Developers") {
			echo "<td>$stockrow[$buystr]</td><td>$stockrow[$sellstr]</td><td>$stockrow[$supplystr]</td>";
		}
		echo "</tr>";
		$i++;
	}
	echo '</table><br /><br />';
	
	echo '<form method="post" action="gristexchange.php">Buy Grist:</br>Type: <select name="buygrist">';
	$i = 0;
	while ($i < $totalgrists) {
		$griststr = $gristname[$i] . "_price";
		echo '<option value="' . $gristname[$i] . '">' . $gristname[$i] . ' (Cost: ' . strval($stockrow[$griststr]) . ' boondollars/unit)</option>';
		$i++;
	}
	echo '</select></br>';
	if ($timeenabled) echo '<input type="checkbox" name="oldprices" value="yes" /> Use prices from last update (cost: 1 encounter)<br />';
	echo 'Amount to buy: <input type="text" name="buyamount"><input type="submit" value="Buy!"></form></br></br>';
	
	echo '<form method="post" action="gristexchange.php">Sell Grist:</br>Type: <select name="sellgrist">';
	$i = 0;
	while ($i < $totalgrists) {
		$griststr = $gristname[$i] . "_price";
		echo '<option value="' . $gristname[$i] . '">' . $gristname[$i] . ' (Value: ' . strval($stockrow[$griststr]) . ' boondollars/unit)</option>';
		$i++;
	}
	echo '</select></br>';
	if ($timeenabled) echo '<input type="checkbox" name="oldprices" value="yes" /> Use prices from last update (cost: 1 encounter)<br />';
	echo 'Amount to sell: <input type="text" name="sellamount"><input type="submit" value="Sell!"></form></br>';
		}
}
}
require_once("footer.php");
?>