<?php
require_once("header.php");
require 'additem.php';
require_once("includes/grist_icon_parser.php");

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

if (strpos($userrow['storeditems'], "DREAMBOT.") !== false && $userrow['dreamingstatus'] != "Awake") { 
	//items in storage with the DREAMBOT tag will grant access to the server program as one's dreamself
	$dreambot == true;
} else {
	$dreambot == false;
}

if (empty($_SESSION['username'])) {
  echo "Log in to mess with the server program.</br>";
} elseif ($userrow['dreamingstatus'] != "Awake" && !$dreambot) {
  echo "Your dream self can't access your computer!";
} else {
	echo "<!DOCTYPE html><html><head><style>gristvalue{color: #FF0000; font-size: 60px;}</style><style>gristvalue2{color: #0FAFF1; font-size: 60px;}</style><style>itemcode{font-family:'Courier New'}</style></head><body>";
	$compugood = true;
	if ($dreambot) {
		if (strpos($userrow['storeditems'], "ISCOMPUTER.") == 0) { //dreambot checks for a computer in storage, regardless of player computability
			echo "Your dreambot can't use the SBURB server program without access to a computer in storage!<br />";
			$compugood = false;
		}
	} else {
  if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
  	if ($userrow['hascomputer'] < 3) {
  		if ($compugood == true) echo "You don't have a hands-free computer equipped, so you can't use the SBURB server program during strife.</br>";
  		$compugood = false;
  	}
  }
  if ($userrow['indungeon'] != 0 && $userrow['hascomputer'] < 2) {
  	if ($compugood == true) echo "You don't have a portable computer in your inventory, so you can't use the SBURB server program while away from home.</br>";
  	$compugood = false;
  }
  if ($userrow['hascomputer'] == 0) {
  	if ($compugood == true) echo "You need a computer in storage or your inventory to use the SBURB server program.</br>";
  	$compugood = false;
  }
	}
  
  if ($compugood) {
  	
  	if (!empty($_POST['client'])) {
    	$playerfound = False;
    	$registered = "";
    	$sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . mysql_real_escape_string($_POST['client']) . "'");
    	while ($row = mysql_fetch_array($sessionmates)) {
      	if ($row['session_name'] == $userrow['session_name']) {
					if ($row['username'] == mysql_real_escape_string($_POST['client']) && ($row['server_player'] == "" || $row['server_player'] == $username)) {
	  				$playerfound = True;
	  				$client = mysql_real_escape_string($_POST['client']);
	  				mysql_query("UPDATE `Players` SET `server_player` = '$username' WHERE `Players`.`username` = '$client' LIMIT 1 ;");
	  				mysql_query("UPDATE `Players` SET `client_player` = '$client' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	  				echo "Client registered.</br>";
	  				$userrow['client_player'] = $client;
					} else {
	  				if ($row['server_player'] != "" && $playerfound != True) {
	    				$playerfound = True;
	    				echo "Client already possesses a server player: " . $row['server_player'] . "</br>";
	  				}
					}
      	}
    	}
    	if ($playerfound == False) {
      	echo "Target player was not found in your session.</br>";
    	}
  	}
  
 		if (empty($userrow['client_player'])) {
			echo "You haven't registered a player as your client yet.</br>";
  		echo '<form action="sburbserver.php" method="post">Register client player: <input id="client" name="client" type="text" /><br />';
  		echo '<input type="submit" value="Connect it!" /></form></br>';
		} else {
			$clientresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $userrow['client_player'] . "'");
			$clientrow = mysql_fetch_array($clientresult);
			$landresult = mysql_query("SELECT * FROM Grist_Types WHERE `Grist_Types`.`name` = '" . $clientrow['grist_type'] . "'");
			$landrow = mysql_fetch_array($landresult);
			$tier1grist = $landrow['grist1'];
			if ($clientrow['server_player'] == "") {
				echo "Something went amiss, and your client player doesn't have you set as their server! We've just attempted to fix this, but if you see this message multiple times, please submit a bug report.<br />";
				mysql_query("UPDATE `Players` SET `server_player` = '$username' WHERE `Players`.`username` = '" . $clientrow['username'] . "' LIMIT 1;");
			}
			
		  if (!empty($_POST['build'])) {
   			if (intval($_POST['build']) > 0) {
     			if (intval($_POST['build'] <= $clientrow['Build_Grist'])) {
						$build = $_POST['build'];
						$newtotal = $build + $clientrow['house_build_grist'];
						$newgrist = $clientrow['Build_Grist'] - $build;
						mysql_query("UPDATE `Players` SET `house_build_grist` = '$newtotal', `Build_Grist` = '$newgrist' WHERE `Players`.`username` = '" . $clientrow['username'] . "' LIMIT 1 ;");
						echo "Build successful!</br>";
						$clientrow['house_build_grist'] = $newtotal;
						$clientrow['Build_Grist'] = $newgrist;
     			} else {
						echo "Build failed: Client lacks required Build Grist.</br>";
     			}
   			}
 			}
 			
 			if (!empty($_POST['deployitem'])) {
 				$deployresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['deployitem'] . "'");
				while ($drow = mysql_fetch_array($deployresult)) {
					$deploytag = specialArray($drow['effects'], "DEPLOYABLE"); 
					if ($deploytag[0] == "DEPLOYABLE") {
						$existtag = specialArray($clientrow['storeditems'], $_POST['deployitem']); //this also works for storage items, fancy that
						if ($existtag[0] == $_POST['deployitem']) $currentstack = $existtag[1];
						else $currentstack = 0;
						if ($deploytag[1] == "MAXSTORE") $fullstack = $deploytag[2];
						else $fullstack = 1;
						if ($currentstack < $fullstack || $drow['captchalogue_code'] == "11111111") { //the user doesn't have this in their storage yet
							$canafford = false;
							if ($deploytag[1] == "FREE") {
								$canafford = true;
								$coststring = "Build_Grist";
								$newgrist = $clientrow['Build_Grist'];
							} elseif ($deploytag[1] == "TIER1") {
								$coststring = $tier1grist;
								if ($clientrow[$tier1grist] > $deploytag[2]) {
									$canafford = true;
									$newgrist = $clientrow[$tier1grist] - $deploytag[2];
								}
							} else {
								$coststring = "Build_Grist";
								if ($clientrow['Build_Grist'] > $drow['Build_Grist_Cost']) {
									$canafford = true;
									$newgrist = $clientrow['Build_Grist'] - $drow['Build_Grist_Cost'];
								}
							}
							if ($canafford) {
								$space = storageSpace($clientrow['storeditems']);
								$maxspace = $clientrow['house_build_grist'] + 1000;
								if ($space + itemSize($drow['size']) <= $maxspace) { //let's finally deploy this thing
									storeItem($drow['name'], 1, $clientrow);
									mysql_query("UPDATE `Players` SET `$coststring` = $newgrist WHERE `Players`.`username` = '" . $clientrow['username'] . "' LIMIT 1;");
									echo $drow['name'] . " successfully deployed!</br>";
								} else echo "Deploy failed: you can't find enough room in the client's house to put down the item! You'll have to make some room first.</br>";
							} else echo "Deploy failed: client lacks the required $coststring.</br>";
						} else echo "Deploy failed: Your client already has as many of those items as they'll need.</br>";
					} else echo "Deploy failed: you can't deploy that item!</br>";
				}
 			}
 			
 			if (!empty($_POST['recycling'])) {
 				$gristed = false;
 				if (!empty($clientrow['storeditems'])) {
 					$updatestore = "";
 					$boom = explode("|", $clientrow['storeditems']);
					$totalitems = count($boom);
					$i = 1;
					while ($i < $totalitems) {
						$args = explode(":", $boom[$i - 1]);
						if (!empty($_POST['r-' . $args[0]])) {
							$iresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '" . $args[0] . "' LIMIT 1;");
							$irow = mysql_fetch_array($iresult);
							if ($irow['captchalogue_code'] == $args[0]) {
								if ($_POST['q-' . $args[0]] < 1 || empty($_POST['q-' . $args[0]])) $_POST['q-' . $args[0]] = 1; //set to 1 if blank or less than 0
								if (intval($args[1]) >= $_POST['q-' . $args[0]]) {
									$nothing = true;
									echo "You recycle your client's " . $irow['name'] .  " x " . strval($_POST['q-' . $args[0]]) . " into ";
									if (!$gristed) {
										$gristname = initGrists();
										$totalgrists = count($gristname);
										$gristed = true;
									}
									$deploytag = specialArray($irow['effects'], "DEPLOYABLE"); //should always return an array because of the search query above
									if ($deploytag[1] == "FREE") $irow['Build_Grist_Cost'] = 0;
									elseif ($deploytag[1] == "TIER1") {
										$irow[$tier1grist . '_Cost'] = 0;
										$irow['Build_Grist_Cost'] = 0;
									}
									$gristcount = 0;
									$refundquery = "UPDATE Players SET ";
									while ($gristcount <= $totalgrists) {
										$gristtype = $gristname[$gristcount];
	  								$gristcost = $gristtype . "_Cost";
	  								if ($irow[$gristcost] != 0) { //Item requires some of this grist. Or produces some. Either way.
	    								$nothing = False; //Item costs something.
	    								$totalthiscost = $irow[$gristcost] * $_POST['q-' . $args[0]];
	    								$refundquery = $refundquery . "`$gristtype` = " . strval($clientrow[$gristtype]) . "+" . strval($totalthiscost) . ", ";
	    								$clientrow[$gristtype] += $irow[$gristcost];
	    								echo '<img src="Images/Grist/' . gristNameToImagePath($gristtype) . '" height="50" width="50" title="' . $gristtype . '"></img>';
	      							echo " <gristvalue2>" . strval($totalthiscost) . "</gristvalue2>";
	  								}
	  								$gristcount++;
									}
									if ($nothing) { //Item costs nothing! SORD.....
	  								echo '<img src="Images/Grist/Build_Grist.png" height="50" width="50" title="Build_Grist"></img>';
	  								echo " <gristvalue2>0 </gristvalue2>";
									} else { //Item costed something, use the refund query to restore grist.
	  								$refundquery = substr($refundquery, 0, -2); //Dispose of last comma and space.
	  								$refundquery = $refundquery . " WHERE `Players`.`username` = '" . $clientrow['username'] . "' LIMIT 1 ;";
	  								mysql_query($refundquery); //Un-pay.
									}
									$args[1]-=$_POST['q-' . $args[0]];
									echo '<br />';
								} else "Error: Client does not have " . $args[1] . " of " . $irow['name'] . "<br />";
							} else {
								echo 'Error: unknown item<br />';
							}
						}
						if ($args[1] > 0) {
							$updatestore .= $args[0] . ":" . $args[1] . ":" . $args[2] . "|";
						}
						$i++;
					}
					if ($updatestore != $clientrow['storeditems']) {
						mysql_query("UPDATE `Players` SET `storeditems` = '$updatestore' WHERE `Players`.`username` = '" . $clientrow['username'] . "' LIMIT 1;");
					}
 				} else echo "Your client has nothing to recycle!<br />";
 				compuRefresh($clientrow);
 			}
 			
 			$clientresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $userrow['client_player'] . "'");
			$clientrow = mysql_fetch_array($clientresult);
			//refresh clientrow so that things like grist and storage are up-to-date. yeah it's inefficient but I'm lazy so
	
			echo "SBURB Server Menu</br>";
			echo "Client player: " . $clientrow['username'] . "</br>";
			echo "Client's build grist: " . strval($clientrow['Build_Grist']) . "</br></br>";
			
			echo "&gt;Revise</br>";
			echo "Client's house investment: " . strval($clientrow['house_build_grist']) . "</br>";
			echo "House gates accessible on your client's Land: ";
  		$gates = 0;
  		$i = 1;
  		$gateresult = mysql_query("SELECT * FROM Gates");
  		$gaterow = mysql_fetch_array($gateresult); //Gates only has one row.
  		while ($i <= 7) {
    		$gatestr = "gate" . strval($i);
    		if ($gaterow[$gatestr] <= $clientrow['house_build_grist']) {
      		$gates++;
    		} else {
      		$i = 7; //We are done.
    		}
    		$i++;
  		}
  		echo strval($gates) . "</br>";
			echo "Building up your client's house will increase their item storage space, as well as help them reach higher gates.</br>";
			echo '<form action="sburbserver.php" method="post">Amount of build grist to spend on client\'s housing: <input id="build" name="build" type="text" /><br />';
      echo '<input type="submit" value="Build it!" /></form></br>';
	
			echo "&gt;Deploy</br>";
			echo '<form method="post" action="sburbserver.php">Select a machine to deploy:</br><select name="deployitem">';
			$deployresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `Captchalogue`.`effects` LIKE '%DEPLOYABLE%' ORDER BY `Build_Grist_Cost` ASC");
			while ($drow = mysql_fetch_array($deployresult)) {
				$deploytag = specialArray($drow['effects'], "DEPLOYABLE"); //should always return an array because of the search query above
				if ($deploytag[1] == "FREE") $coststring = "--";
				elseif ($deploytag[1] == "TIER1") $coststring = strval($deploytag[2]) . " " . $tier1grist;
				else $coststring = strval($drow['Build_Grist_Cost']) . " Build Grist";
				echo '<option value="' . $drow['captchalogue_code'] . '">' . $drow['name'] . ' (Cost: ' . $coststring . ')</option>';
			}
			echo '</select></br><input type="submit" value="Deploy it!"></form><br />';
			
			echo "&gt;Recycle<br />";
			echo "Your client may be unable to recycle items directly from their storage, but you sure can!<br />";
			if (!empty($clientrow['storeditems'])) {
				echo '<form method="post" action="sburbserver.php"><input type="hidden" name="recycling" value="yes">';
				$boom = explode("|", $clientrow['storeditems']);
				$totalitems = count($boom);
				$i = 1;
				while ($i < $totalitems) {
					$args = explode(":", $boom[$i - 1]);
					$iresult = mysql_query("SELECT `captchalogue_code`,`name` FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '" . $args[0] . "' LIMIT 1;");
					$irow = mysql_fetch_array($iresult);
					if ($irow['captchalogue_code'] == $args[0]) {
						echo '<input type="checkbox" name="r-' . $args[0] . '" value="yes">';
						echo $irow['name'] . ' x ' . $args[1];
						if ($args[1] > 1) {
							echo ' - Amount to recycle: <input type="text" name="q-' . $args[0] . '">';
						} else {
							echo '<input type="hidden" name="q-' . $args[0] . '" value="1">';
						}
					} else {
						echo 'Error: unknown item';
					}
					echo '<br />';
					$i++;
				}
				echo '<input type="submit" value="Recycle it!"></form>';
			} else {
				echo "...if your client HAD anything in storage, that is.<br />";
			}
		}
	}
}

require_once("footer.php");
?>