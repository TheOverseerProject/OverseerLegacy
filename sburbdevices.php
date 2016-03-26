<?php
require_once("header.php");
require 'additem.php';
require 'designix.php';
$max_items = 50;
if (empty($_SESSION['username'])) {
  echo "Log in to mess around with equipment.</br>";
} elseif ($userrow['dreamingstatus'] != "Awake") {
  echo "Your dream self doesn't have any equipment to use!";
} else {
	echo "<style>itemcode{font-family:'Courier New'}</style>";
	$dcount = 0;
	$sessionname = $userrow['session_name'];
	$sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$sessionname'");
	$sessionrow = mysql_fetch_array($sessionresult);
	$canon = $sessionrow['canon'];
	$challenge = $sessionrow['challenge'];
	
	if (strpos($userrow['storeditems'], "PUNCHDESIGNIX.") !== false) $designix = true;
	if (strpos($userrow['storeditems'], "CRUXTRUDER.") !== false) $cruxtruder = true;
	if (strpos($userrow['storeditems'], "TOTEMLATHE.") !== false) $lathe = true;
	if (strpos($userrow['storeditems'], "ALCHEMITER.") !== false) $alchemiter = true;
	if (strpos($userrow['storeditems'], "HOLOPAD.") !== false) $holopad = true;
	if (strpos($userrow['storeditems'], "LASERSTATION.") !== false) $intlaser = true;
	if (strpos($userrow['storeditems'], "JUMPER.") !== false) $jumper = true;
	if (strpos($userrow['storeditems'], "USELESS.") !== false) $useless = true;
	if (strpos($userrow['storeditems'], "CRUXBLEND.") !== false) $cruxblend = true;
	if (strpos($userrow['storeditems'], "CARDSHRED.") !== false) $cardshred = true;
	if (strpos($userrow['storeditems'], "COMBOFINDER") !== false) {
		$combofinder = true;
		$breakdowna = explode("|", $userrow['storeditems']);
		$i = 0;
		$cf = 0;
		while (!empty($breakdowna[$i])) {
			$sendpos = strpos($breakdowna[$i], "COMBOFINDER");
			if ($sendpos !== false) {
				$breakdownb = explode(":", $breakdowna[$i]);
				$fresult = mysql_query("SELECT `captchalogue_code`,`name` FROM `Captchalogue` WHERE `captchalogue_code` = '" . $breakdownb[0] . "'");
				while ($frow = mysql_fetch_array($fresult)) {
					$cf++;
					$combofind['code'][$cf] = $breakdownb[0];
					$combofind['name'][$cf] = str_replace("\\", "", $frow['name']);
					$restofstring = substr($breakdowna[$i], $sendpos+11);
					$lolboom = explode(".", $restofstring); //the most convenient way to get a string that stops at a certain point, and we only need the first string that this gives us anyway
					$roflboom = explode("/", $lolboom[0]);
					if (!empty($roflboom[1])) $combofind['area'][$cf] = $roflboom[1];
					else $combofind['area'][$cf] = "inventory";
					if ($roflboom[2] != "") $combofind['return'][$cf] = $roflboom[2];
					else $combofind['return'][$cf] = 1;
					if (!empty($roflboom[3])) $combofind['cost'][$cf] = $roflboom[3];
					else $combofind['cost'][$cf] = 1;
				}
			}
			$i++;
		}
	}
	if (strpos($userrow['storeditems'], "SENDIFICATOR") !== false) {
		$sendify = true;
		$maxsendsize = "miniature";
		$breakdowna = explode("|", $userrow['storeditems']);
		$i = 0;
		while (!empty($breakdowna[$i])) {
			$sendpos = strpos($breakdowna[$i], "SENDIFICATOR/");
			if ($sendpos !== false) {
				$restofstring = substr($breakdowna[$i], $sendpos+13);
				$lolboom = explode(".", $restofstring); //the most convenient way to get a string that stops at a certain point, and we only need the first string that this gives us anyway
				if (itemSize($lolboom[0]) > itemSize($maxsendsize))
				$maxsendsize = $lolboom[0];
			}
			$i++;
		}
	}
	$j = 0;
      	while ($j < $invslots) { //check the inventory for any items with effects that work when held, rather than in storage (such as ghosters)
      		$jnvstr = "inv" . strval($j);
      		$compuname = str_replace("'", "\\\\''", $userrow[$jnvstr]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      		$compuname = str_replace("\\\\\\", "\\\\", $compuname); //really hope this works
      		$compuresult = mysql_query("SELECT `name`, `effects` FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $compuname . "' LIMIT 1;");
      		while ($compurow = mysql_fetch_array($compuresult)) {
      			$ghosters = specialArray($compurow['effects'], "GHOSTER");
      			if ($ghosters[0] == "GHOSTER") {
      				$canghost = true;
      				$ghostname = $compurow['name'];
      			}
      		}
      		$j++;
      	}

	if (!empty($_POST['punchcard'])) {
		if ($designix) {
			if (strpos($userrow[$_POST['punchcard']], "Captchalogue Card (CODE:") !== false) {
				$oldcode = substr($userrow[$_POST['punchcard']], -9, 8);
				$newcode = $_POST['punchcode'];
				if (strpos($sessionrow['atheneum'], $newcode) === false && $challenge) {
					echo "In Challenge Mode, you can't input a code that your session hasn't yet discovered in its atheneum.<br />";
				} else {
					$combinecode = orcombine($oldcode, $newcode);
					echo "You punch the card with the code <itemcode>$newcode</itemcode>. The card now contains the code <itemcode>$combinecode</itemcode>.</br>";
					$nucardname = "Captchalogue Card (CODE:$combinecode)";
					mysql_query("UPDATE `Players` SET `" . $_POST['punchcard'] . "` = '$nucardname' WHERE `Players`.`username` = '$username'");
					$userrow[$_POST['punchcard']] = $nucardname;
					if (!strrpos($sessionrow['atheneum'], $combinecode)) {
      			$newatheneum = $sessionrow['atheneum'] . $combinecode . "|";
      			mysql_query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1 ;");
      		}
				}
			} else echo "You can't punch holes in that for the purposes of alchemy!</br>";
		} else echo "You don't have a machine that can punch cards!</br>";
	}
	
	if (!empty($_POST['cruxtrude'])) {
		if ($cruxtruder) {
			$quantity = $_POST['cruxamount'];
			$dowelscaptchad = 0;
			$actualstore = 0;
			if ($_POST['cruxtrude'] == "inv") {
				while ($quantity > 0 && $_POST['cruxtrude'] == "inv") {
					$newdowel = addItem("Cruxite Dowel",$userrow);
					if ($newdowel != "inv-1") {
						$quantity--;
						$dowelscaptchad++;
						$userrow[$newdowel] = "Cruxite Dowel";
						$dcount++;
					} else {
						$_POST['cruxtrude'] = "store";
					}
				}
			}
			if ($_POST['cruxtrude'] == "store") {
				$actualstore = storeItem("Cruxite Dowel", $quantity, $userrow);
			}
			if ($_POST['cruxamount'] == 1) $tdowels = "dowel";
			else $tdowels = "dowels";
			if ($dowelscaptchad == 1) $cdowels = "dowel";
			else $cdowels = "dowels";
			if ($actualstore == 1) $sdowels = "dowel";
			else $sdowels = "dowels";
			if ($dowelscaptchad + $actualstore > 0) {
				if ($dowelscaptchad + $actualstore == $_POST['cruxamount']) {
					echo "You turn the wheel of the Cruxtruder, producing " . strval($_POST['cruxamount']) . " $tdowels.</br>";
				} else {
					echo "You turn the wheel of the Cruxtruder, but are only able to produce " . strval($_POST['cruxamount']) . " $tdowels. You can't possibly fit any more in your inventory or storage.</br>";
				}
				if ($dowelscaptchad > 0) {
					if ($actualstore > 0) {
						echo "You captchalogue $dowelscaptchad $cdowels before running out of inventory space; $actualstore $sdowels must be sent to storage.";
					} else {
						echo "You captchalogue ";
						if ($cdowels == "dowels") echo "all of them.</br>";
						else echo "it.</br>";
					}
				} elseif ($actualstore > 0) {
					echo "You put ";
					if ($sdowels == "dowels") echo "all of them into storage.</br>";
					else echo "it into storage.</br>";
				}
			} else {
				echo "You don't have room for any cruxite dowels in your inventory or storage!</br>";
			}
		} else echo "You don't have a machine that produces cruxite dowels!</br>";
	}
	
	if (!empty($_POST['lathecard1'])) {
		if ($lathe) {
			if (strpos($userrow[$_POST['lathecard1']], "Captchalogue Card (CODE:") !== false && ($_POST['lathecard2'] == "inv0" || strpos($userrow[$_POST['lathecard2']], "Captchalogue Card (CODE:") !== false)) {
				$i = 1;
				$cruxinv = "inv-1";
				while ($i <= $max_items) {
					$invstr = 'inv' . strval($i);
					if ($userrow[$invstr] == "Cruxite Dowel (CODE:00000000)") { //only let the user carve blank dowels
						$cruxinv = $invstr;
						$i = $max_items;
					}
					$i++;
				}
				if ($cruxinv != "inv-1") {
					if ($_POST['lathecard2'] != "inv0") {
						$code1 = substr($userrow[$_POST['lathecard1']], -9, 8);
						$code2 = substr($userrow[$_POST['lathecard2']], -9, 8);
						$combinecode = andcombine($code1, $code2);
						echo "You slip both of the cards into the Totem Lathe, then feed it an uncarved dowel. The device whirs to life, carving a unique pattern into it.</br>";
						echo "The dowel now corresponds to the code <itemcode>$combinecode</itemcode>.</br>";
						if (!strrpos($sessionrow['atheneum'], $combinecode)) {
      				$newatheneum = $sessionrow['atheneum'] . $combinecode . "|";
      				mysql_query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1 ;");
      			}
						$newname = "Cruxite Dowel (CODE:" . $combinecode . ")";
					} else {
						$code1 = substr($userrow[$_POST['lathecard1']], -9, 8);
						echo "You slip the card into the Totem Lathe, then feed it an uncarved dowel. The device whirs to life, carving a unique pattern into it.</br>";
						echo "The dowel now corresponds to the code <itemcode>$code1</itemcode>.</br>";
						$newname = "Cruxite Dowel (CODE:" . $code1 . ")";
					}
					mysql_query("UPDATE `Players` SET `$cruxinv` = '$newname' WHERE `Players`.`username` = '$username'");
					$userrow[$cruxinv] = $newname;
				} else echo "You don't have a spare cruxite dowel to carve that isn't already carved. You'll have to pick one up from the Cruxtruder or from storage.</br>";
			} else echo "One or both of the items you selected aren't captchalogue cards.</br>";
		} else echo "You don't have a machine that can carve cruxite dowels!</br>";
	}
	
	if (!empty($_POST['disposalaction'])) {
		$aok = false;
		if ($_POST['disposalaction'] == "cruxinv" || $_POST['disposalaction'] == "cruxstore") {
			$breakname = "Cruxite Dowel (CODE:";
			$breakcode = "????????";
			if ($cruxblend) $aok = true;
			else echo "You don't have a device that can destroy cruxite dowels, other than the recycler.<br />";
		} elseif ($_POST['disposalaction'] == "cardinv" || $_POST['disposalaction'] == "cardstore") {
			$breakname = "Captchalogue Card (CODE:";
			$breakcode = "11111111";
			if ($cardshred) $aok = true;
			else echo "You don't have a device that can destroy captchalogue cards, other than the recycler.<br />";
		} else echo "not a statement<br />";
		if ($aok) {
			if (strpos($_POST['disposalaction'], "inv") !== false) {
				$allquery = "UPDATE `Players` SET ";
				$i = 1;
				while ($i <= $max_items) {
					$invstr = "inv" . strval($i);
					if (strpos($userrow[$invstr], $breakname) !== false) {
						$allquery .= "`$invstr` = '', ";
						$userrow[$invstr] = "";
					}
					$i++;
				}
				if ($allquery != "UPDATE `Players` SET ") { //make sure there are changes to make
					$allquery = substr($allquery, 0, -2);
					$allquery .= " WHERE `Players`.`username` = '$username';";
					mysql_query($allquery);
					echo "Items destroyed successfully.<br />";
				} else echo "You don't have any of those in your inventory.<br />";
			} elseif (strpos($_POST['disposalaction'], "store") !== false) {
				$storeitems = explode("|", $userrow['storeditems']);
				$i = 0;
				$updatestorage = "";
				while (!empty($storeitems[$i])) {
					$storeargs = explode(":", $storeitems[$i]);
					if ($storeargs[0] != $breakcode) {
						$updatestorage .= $storeitems[$i] . "|";
					}
					$i++;
				}
				if ($updatestorage != $userrow['storeditems']) {
					$userrow['storeditems'] = $updatestorage;
					mysql_query("UPDATE `Players` SET `storeditems` = '$updatestorage' WHERE `Players`.`username` = '$username';");
					echo "Items destroyed successfully.<br />";
				} else echo "You don't have any of those in storage.<br />";
			}
		}
	}
	
	if (!empty($_POST['ilitem'])) {
		if ($intlaser) {
			if (strpos($_POST['ilitem'], "inv") === false) { //player is trying to consume from outside their inventory!
    		echo "Look at you, trying to be clever! Unfortunately, you can only scan items from your inventory.<br />";
    		$fail = true;
    	} else {
				$compuname = str_replace("'", "\\\\''", $userrow[$_POST['ilitem']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      	$compuname = str_replace("\\\\\\", "\\\\", $compuname); //really hope this works
      	$compuresult = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $compuname . "' LIMIT 1;");
      	$compurow = mysql_fetch_array($compuresult);
      	$ilcode = $compurow['captchalogue_code'];
      	echo "The Intellibeam Laserstation scans the card using its ultra-fine laser and eventually prints out the code: <itemcode>$ilcode</itemcode></br>";
      	if (!strrpos($sessionrow['atheneum'], $ilcode)) {
      		$newatheneum = $sessionrow['atheneum'] . $ilcode . "|";
      		mysql_query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1 ;");
      	}
    	}
		} else echo "You don't have a machine that can read captchalogue codes, and you're quite certain that such a thing could not possibly exist.</br>";
	}
	
	if (!empty($_POST['senditem'])) {
		if ($sendify) {
			if (strpos($_POST['senditem'], "inv") === false) { //player is trying to consume from outside their inventory!
				echo "Look at you, trying to be clever! Unfortunately, you can only send items from your inventory.<br />";
			} else {
			  if (strpos($userrow[$_POST['senditem']], "(CODE:") !== false) {
			    $compuname = substr($userrow[$_POST['senditem']], 0, -16);
			  } else {
			    $compuname = $userrow[$_POST['senditem']];
			  }
				$compuname = str_replace("'", "\\\\''", $userrow[$_POST['senditem']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      	$compuname = str_replace("\\\\\\", "\\\\", $compuname); //really hope this works
      	$compuresult = mysql_query("SELECT `captchalogue_code`,`name`,`size` FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $compuname . "' LIMIT 1;");
      	$compurow = mysql_fetch_array($compuresult);
      	if (itemSize($compurow['size']) <= itemSize($maxsendsize)) {
      		$materesult = mysql_query("SELECT * FROM `Players` WHERE `Players`.`username` = '" . $_POST['sendplayer'] . "'");
      		$materow = mysql_fetch_array($materesult);
      		if ($materow['session_name'] == $userrow['session_name']) {
      			$actualstore = storeItem($userrow[$_POST['senditem']], 1, $materow);
      			if ($actualstore != 0) {
      				echo "You put your " . $userrow[$_POST['senditem']] . " in the Sendificator, and it is promptly transported to " . $materow['username'] . "'s house.<br />";
      				autoUnequip($userrow, "none", $_POST['senditem']);
      				mysql_query("UPDATE `Players` SET `" . $_POST['senditem'] . "` = '' WHERE `Players`.`username` = '$username'");
      				$userrow[$_POST['senditem']] = "";
      			} else echo "Target player does not have enough storage space for that item.<br />";
      		} else echo "You can't send items to players outside of your own session.<br />";
      	} else echo "That item is too big to fit into the sendificator.<br />";
			}
		} else echo "You don't have a device that can sendify items.<br />";
	}
	
	if (!empty($_POST['ghostitem'])) {
		if ($canghost) {
			if (strpos($userrow['storeditems'], $_POST['ghostitem'] . ":") !== false) {
				$compuresult = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['ghostitem'] . "' LIMIT 1;");
      	$compurow = mysql_fetch_array($compuresult);
      	$ghostadd = addItem($compurow['name'] . " (ghost image)",$userrow);
      	if ($ghostadd == "inv-1") {
      		echo "You don't have room in your inventory for a ghost item.<br />";
      	} else {
      		echo $compurow['name'] . " successfully ghosted!<br />";
      		$athenresult = mysql_query("SELECT `atheneum` FROM Sessions WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1;");
      		$athenrow = mysql_fetch_array($athenresult);
      		if (!strrpos($athenrow['atheneum'], $compurow['captchalogue_code']) && strpos($compurow['effects'], "OBSCURED|") === false) {
	      		$newatheneum = $athenrow['atheneum'] . $compurow['captchalogue_code'] . "|";
  	    		mysql_query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1 ;");
    	  	}
      	}
			} else echo "You don't have that item in storage!<br />";
  	} else echo "You don't have an item that can create ghost images!<br />";
	}
	
	if (!empty($_POST['shuntcard'])) {
		if (strpos($userrow[$_POST['shuntcard']], "Captchalogue Card (CODE:") !== false) {
			$i = 1;
			$cruxinv = "inv-1";
			while ($i <= $max_items) {
				$invstr = 'inv' . strval($i);
				if ($userrow[$invstr] == "Punch Card Shunt") { //only let the user insert into empty shunts
					$cruxinv = $invstr;
					$i = $max_items;
				}
				$i++;
			}
			if ($cruxinv != "inv-1") {
				$code1 = substr($userrow[$_POST['shuntcard']], -9, 8);
				$userrow[$cruxinv] = "Punch Card Shunt (CODE:" . $code1 . ")";
				$userrow[$_POST['shuntcard']] = "";
				mysql_query("UPDATE `Players` SET `$cruxinv` = '" . $userrow[$cruxinv] . "', `" . $_POST['shuntcard'] . "` = '' WHERE `Players`.`username` = '$username'");
				echo "You insert the card containing the code $code1 into one of your empty Punch Card Shunts. You can now put it in storage to see what effect it has on your Alchemiter.<br />";
			} else echo "You don't have any empty shunts into which to put that!<br />";
		} else echo "You can't insert that into a shunt!<br />";
	}
	
	if (!empty($_POST['clearshunt'])) {
		if (strpos($userrow[$_POST['clearshunt']], "Punch Card Shunt (CODE:") !== false) {
			$code1 = substr($userrow[$_POST['clearshunt']], -9, 8);
			$retrieve = addItem("Captchalogue Card", $userrow, $code1);
			if ($retrieve != "inv-1") {
				$userrow[$_POST['clearshunt']] = "Punch Card Shunt";
				$userrow[$retrieve] = "Captchalogue Card (CODE:" . $code1 . ")";
				mysql_query("UPDATE `Players` SET `" . $_POST['clearshunt'] . "` = '" . $userrow[$_POST['clearshunt']] . "' WHERE `Players`.`username` = '$username'");	
				echo "You retrieve the card containing the code $code1 from the Punch Card Shunt.<br />";
			} else echo "You don't have room in your Sylladex to get the card out!<br />";
		} else echo "You can't insert that into a shunt!<br />";
	}
	
	if (!empty($_POST['combodevice'])) {
		if ($combofinder) {
			$device = intval($_POST['combodevice']);
			$enc = chargeEncounters($userrow, $combofind['cost'][$device], 0);
			if ($enc) {
				echo "You boot up the " . $combofind['name'][$device] . " and spend some time waiting for the codes to process...<br />";
				$captchalogue = "SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE";
				if ($combofind['area'][$device] == "inventory") {
					$invcount = 1;
					$captchaloguequantities = array();
  				while ($invcount <= $max_items) {
    				$invslot = 'inv' . strval($invcount);
    				if ($userrow[$invslot] != "") { //This is a non-empty inventory slot that wasn't just recycled away.
		  				$pureitemname = str_replace("\\", "", $userrow[$invslot]);
		  				$pureitemname = str_replace("'", "", $pureitemname);
	  					if (strpos($pureitemname, "(CODE:") !== false) { //this item is holding a code, like a captchalogue card or a cruxite dowel
	  						$pureitemname = substr($pureitemname, 0, -16);
	  						$itemname = str_replace("'", "\\\\''", substr($userrow[$invslot], 0, -16));
		  				} else {
	    					$itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		  				}
		  				$itemname = str_replace(" (ghost image)", "", $itemname);
	  					if (empty($captchaloguequantities[$pureitemname])) {
								$captchalogue = $captchalogue . "`Captchalogue`.`name` = '" . $itemname . "' OR ";
								$captchaloguequantities[$pureitemname] = 1;
	  					} else {
								$captchaloguequantities[$pureitemname] += 1;
		  				}
						}
						$invcount++;
  				}
				} elseif ($combofind['area'][$device] == "storage") {
					$stored = explode("|", $userrow['storeditems']);
					$i = 0;
					while (!empty($stored[$i])) {
						$storearray = explode(":", $stored[$i]);
						$captchalogue = $captchalogue . "`Captchalogue`.`captchalogue_code` = '" . $storearray[0] . "' OR ";
						$i++;
					}
				} elseif ($combofind['area'][$device] == "atheneum") {
					$athen = preg_replace("/\\|{2,}/","|",$sessionrow['atheneum']); //eliminate all blanks
					$stored = explode("|", $athen);
					$i = 0;
					while (!empty($stored[$i])) {
						$captchalogue = $captchalogue . "`Captchalogue`.`captchalogue_code` = '" . $stored[$i] . "' OR ";
						$i++;
					}
				}
				$captchalogue = substr($captchalogue, 0, -4);
  			$captchalogueresult = mysql_query($captchalogue);
 				$rcount = 0;
 				while ($row = mysql_fetch_array($captchalogueresult)) {
 					$rcount++;
 					$irow[$rcount] = $row;
 				}
				$i = 1;
				$j = 1;
				$foundcombos = 0;
				$foundcombo = Array(0 => "nothing");
				while ($i <= $rcount) {
					$j = $i + 1;
					while ($j <= $rcount) {
						$testcode = orcombine($irow[$i]['captchalogue_code'], $irow[$j]['captchalogue_code']);
						if ($testcode != $irow[$i]['captchalogue_code'] && $testcode != $irow[$j]['captchalogue_code'] && $testcode != "00000000" && $testcode != "!!!!!!!!") {
							$testresult = mysql_query("SELECT `captchalogue_code` FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '$testcode'");
							while ($testrow = mysql_fetch_array($testresult)) {
								$foundcombos++;
								$foundcombo[$foundcombos] = $irow[$i]['name'] . " || " . $irow[$j]['name'];
							}
						}
						$testcode = andcombine($irow[$i]['captchalogue_code'], $irow[$j]['captchalogue_code']);
						if ($testcode != $irow[$i]['captchalogue_code'] && $testcode != $irow[$j]['captchalogue_code'] && $testcode != "00000000" && $testcode != "!!!!!!!!") {
							$testresult = mysql_query("SELECT `captchalogue_code` FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '$testcode'");
							while ($testrow = mysql_fetch_array($testresult)) {
								$foundcombos++;
								$foundcombo[$foundcombos] = $irow[$i]['name'] . " && " . $irow[$j]['name'];
							}
						}
						$j++;
					}
					$i++;
				}
				if ($foundcombos > 0) {
					echo "The device returns the following:<br /><b>";
					if ($combofind['return'][$device] >= $foundcombos) $returns = 0; //less recipes found than the device can return, so print all of them
					else $returns = $combofind['return'][$device];
					if ($returns == 0) {
						$i = 1;
						while ($i <= $foundcombos) {
							echo $foundcombo[$i] . "<br />";
							$i++;
						}
					} else {
						$report = 0;
						$reported[$report] = true;
						while ($returns > 0) {
							while ($reported[$report] == true) {
								$report = rand(1,$foundcombos);
							}
							$reported[$report] = true;
							echo $foundcombo[$report] . "<br />";
							$returns -= 1;
						}
					}
					echo "</b>";
				} else echo "The device reports that none of the items in your " . $combofind['area'][$device] . " can combine to make anything that has been defined yet. Well, THAT was a waste of time!<br />";
			} else echo "You don't have enough encounters to wait for a full code calculation cycle.<br />";
		} else echo "You don't have a device that can look up combinations!<br />";
	}
	
	if (!$canon) echo "Your session is a non-canon one. You still may use the canon machinery, but you might find your other options (on the inventory page) more convenient.<br />";
	
	echo "Your usable machinery:</br>";
	$hasanything = false;
	
	if ($designix) {
		echo "</br><b>Punch Designix</b></br>";
		echo "You can use this to punch a captchalogue card with a given 8-digit code.</br>";
		echo "Punching a card that has already been punched will combine the two items with the operation ||.</br>";
		echo '<form action="sburbdevices.php" method="post">Card to punch: <select name="punchcard">';
		$i = 1;
		while ($i <= $max_items) {
			$invstr = 'inv' . strval($i);
			if (strpos($userrow[$invstr], "Captchalogue Card (CODE:") !== false) {
				echo '<option value="' . $invstr . '">' . $userrow[$invstr] . '</option>';
			}
			$i++;
		}
		echo '</select></br>';
		echo 'Code to input: <input type="text" name="punchcode"></br>';
		echo '<input type="submit" value="Punch it!"></form>';
		$hasanything = true;
	}
	
	if ($cruxtruder) {
		echo "</br><b>Cruxtruder</b></br>";
		echo "Turning the wheel on this device will produce a fresh Cruxite Dowel.</br>";
		echo '<form action="sburbdevices.php" method="post">Number of dowels to extract: <input type="text" name="cruxamount" value="1"></br>Put dowel(s) into:</br>';
		echo '<input type="radio" name="cruxtrude" value="inv"> Sylladex</br>';
		echo '<input type="radio" name="cruxtrude" value="store"> Storage</br><input type="submit" value="Turn it!"></form>';
		$hasanything = true;
	}
	
	if ($lathe) {
		$i = 1;
				while ($i <= $max_items) {
					$invstr = 'inv' . strval($i);
					if ($userrow[$invstr] == "Cruxite Dowel (CODE:00000000)") { //only let the user carve blank dowels
						$dcount++;
					}
					$i++;
				}
		echo "</br><b>Totem Lathe</b></br>";
		echo "You can insert up to two cards in the device's slot, carving a blank cruxite dowel with a code.</br>";
		echo "Inserting one card will carve the dowel with the code on the card; inserting two cards will combine the two codes with the operation &&.</br>";
		echo "Uncarved dowels in inventory: <b>$dcount</b><br />";
		echo '<form action="sburbdevices.php" method="post">First card: <select name="lathecard1">';
		$i = 1;
		while ($i <= $max_items) {
			$invstr = 'inv' . strval($i);
			if (strpos($userrow[$invstr], "Captchalogue Card (CODE:") !== false) {
				echo '<option value="' . $invstr . '">' . $userrow[$invstr] . '</option>';
			}
			$i++;
		}
		echo '</select></br>Second card: <select name="lathecard2"><option value="inv0">Don\'t insert a second card</option>';
		$i = 1;
		while ($i <= $max_items) {
			$invstr = 'inv' . strval($i);
			if (strpos($userrow[$invstr], "Captchalogue Card (CODE:") !== false) {
				echo '<option value="' . $invstr . '">' . $userrow[$invstr] . '</option>';
			}
			$i++;
		}
		echo '</select></br><input type="submit" value="Carve it!"></form>';
		$hasanything = true;
	}
	
	if ($alchemiter) {
		echo "</br><b>Alchemiter</b></br>";
		if ($useless) echo "One of your jumper block \"upgrades\" has made the Alchemiter completely useless!</br>";
		else {
			echo "You can place a cruxite totem on the smaller platform, and the device will scan its surface and produce the item with that code. If you have the grist, that is.</br>";
			echo "If the code doesn't match an existing item, you will be given the opportunity to design one and submit your idea to the dev team for consideration.</br>";
  		echo '<form action="inventory.php" method="post">Cruxite dowel to use: <select name="alchcrux">';
  		$i = 1;
			while ($i <= $max_items) {
				$invstr = 'inv' . strval($i);
				if (strpos($userrow[$invstr], "Cruxite Dowel (CODE:") !== false) {
					echo '<option value="' . $invstr . '">' . $userrow[$invstr] . '</option>';
				}
				$i++;
			}
  		echo '</select><br />Make this many (blank for 1): <input id="alchnum" name="alchnum" type="text" />';
  		echo '<br /><input name="autostore" type="checkbox"> Send all created items to storage</br>';
  		echo '<input type="submit" value="Create it!" /></form>';
  		echo "(You will be taken to the inventory page.)</br>";
		}
  	$hasanything = true;
	}
	
	if ($jumper) {
		echo "</br><b>Jumper Block Extension</b></br>";
		if ($alchemiter) {
		echo "This device attaches to your Alchemiter and can hold up to eight Punch Card Shunts. Each of those can hold a captchalogue card which you can use to upgrade your Alchemiter.</br>";
		echo "The extension does nothing on its own, but you can manipulate the shunts from here.</br>";
		echo "Current Alchemiter upgrades:</br>";
		$boom = explode("|", $userrow['storeditems']);
		$totalitems = count($boom);
		$i = 0;
		$totalshunts = 0;
		$hasanupgrade = false;
		$newstorage = "";
		while ($i <= $totalitems) {
			$args = explode(":", $boom[$i]);
			if ($args[0] == "HVdF95!Z") { //Found a shunt. Hardcoding the code so that we don't have to dip into the database for every stored item.
				$totalshunts++;
				if ($totalshunts <= 8) {
					if (strpos($args[2], "CODE=") !== false) {
						$thiscode = substr($args[2], 5, 8);
					} else $thiscode = "00000000"; //assume no card inserted which will default to PGO
					$itemresult = mysql_query("SELECT `captchalogue_code`,`name`,`effects` FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '$thiscode' LIMIT 1");
					$irow = mysql_fetch_array($itemresult);
					$shunttag = specialArray($irow['effects'], "SHUNT");
					if ($shunttag[0] != "SHUNT") {
						$shunttag = specialArray($irow['effects'], "STORAGE"); //if no shunt effect exists, check for a storage effect and apply it
						if ($shunttag[0] == "STORAGE") $shunttag[0] = "SHUNT";
					}
					if ($shunttag[0] == "SHUNT") {
						$hasanupgrade = true;
						if (!empty($shunttag[2])) echo "- " . $shunttag[2] . "</br>";
						else {
							echo "- Your Alchemiter has the " . $irow['name'] . " upgrade! ";
							switch ($shunttag[1]) {
								case "USELESS.":
									echo "It has been reduced to an utter pile of shit.";
									break;
								case "PUNCHDESIGNIX.":
									echo "It carries the functionality of a Punch Designix.";
									break;
								case "CRUXTRUDER.":
									echo "It carries the functionality of a Cruxtruder.";
									break;
								case "TOTEMLATHE.":
									echo "It carries the functionality of a Totem Lathe.";
									break;
								case "HOLOPAD.":
									echo "It carries the functionality of a Holopad.";
									break;
								case "HOLOPLUS.":
									echo "The Holopad will inform you of many more of the item's qualities than normal!";
									break;
								case "LASERSTATION.":
									echo "It carries the functionality of an Intellibeam Laserstation.";
									break;
								case "JUMPERCOMPACT.":
									echo "The jumper block has disappeared, and the Alchemiter itself now carries 8 slots which work like punch card shunts.";
									break;
								case "ALCHEMITER.":
									echo "Yo dawg.";
									break;
								case "CAPTCHACOMBINE.":
									echo "You can punch any two codes into the device to have it calculate the combined result.";
									break;
								case "CAPTCHASCAN.":
									echo "The device can scan your sylladex and combine the codes of two of your items.";
									break;
								case "MANUALCHEMITER.":
									echo "You can punch any code into the device to alchemize, alleviating the need for cards and totems!";
									break;
								case "REMOTEHOLO.":
									echo "You can punch any code into the holopad directly to preview it.";
									break;
								default:
									echo "You're... not sure that it does anything. (If you think it should, send the devs some feedback!)";
									break;
							}
							echo "<br />";
						}
						$addthis = $args[0] . ":" . $args[1] . ":CODE=" . $thiscode . "." . $shunttag[1]; //refresh the storage thing so that the effect actually applies
						$newstorage .= $addthis . "|";
					} else {
						echo "- Your Alchemiter has the " . $irow['name'] . " upgrade! You're... not sure that it does anything. (If you think it should, send the devs some feedback!)<br />";
						if (!empty($boom[$i])) $newstorage .= $boom[$i] . "|";
					}
				}
			} else {
				if (!empty($boom[$i])) $newstorage .= $boom[$i] . "|";
			}
			$i++;
		}
		if (!$hasanupgrade) echo "None yet!<br />";
		else {
			if ($userrow['storeditems'] != $newstorage && $newstorage != "") {
				//echo "DEBUG: Storage refresh imminent.<br />OLD: " . $userrow['storeditems'] . "<br />NEW: $newstorage<br />";
				mysql_query("UPDATE `Players` SET `storeditems` = '$newstorage' WHERE `Players`.`username` = '$username'");
			}
		}
		echo "To get an upgrade, insert a punched card into an empty shunt from your inventory and then store it. It will automatically be placed on your Alchemiter and its upgrade will be applied to it.<br />";
		} else echo "This device would attach to your Alchemiter, if you had one!<br />You can still manipulate and attach Punch Card Shunts, but you need to store an Alchemiter to make use of them.<br />";
		echo "Insert a card into an empty shunt:<br />";
		echo '<form action="sburbdevices.php" method="post">Card to insert: <select name="shuntcard">';
		$i = 1;
		while ($i <= $max_items) {
			$invstr = 'inv' . strval($i);
			if (strpos($userrow[$invstr], "Captchalogue Card (CODE:") !== false) {
				echo '<option value="' . $invstr . '">' . $userrow[$invstr] . '</option>';
			}
			$i++;
		}
		echo '</select></br><input type="submit" value="Insert it!"></form>';
		echo "Remove a card from an occupied shunt:<br />";
		echo '<form action="sburbdevices.php" method="post">Shunt to empty out: <select name="clearshunt">';
		$i = 1;
		while ($i <= $max_items) {
			$invstr = 'inv' . strval($i);
			if (strpos($userrow[$invstr], "Punch Card Shunt (CODE:") !== false) {
				echo '<option value="' . $invstr . '">' . $userrow[$invstr] . '</option>';
			}
			$i++;
		}
		echo '</select></br><input type="submit" value="Empty it!"></form>';
	}
	
	if ($cruxblend || $cardshred) {
		echo "</br><b>Alchemy Material Disposal Unit</b></br>";
		echo "You have the ability to quickly dispose of Captchalogue Cards and/or Cruxite Dowels. Select an action below to execute it.<br />";
		echo '<form action="sburbdevices.php" method="post"><select name="disposalaction">';
		if ($cruxblend) {
			echo '<option value="cruxinv">Destroy all Cruxite Dowels in inventory</option><option value="cruxstore">Destroy all Cruxite Dowels in storage</option>';
		}
		if ($cardshred) {
			echo '<option value="cardinv">Destroy all Captchalogue Cards in inventory</option><option value="cardstore">Destroy all Captchalogue Cards in storage</option>';
		}
		echo '</select><input type="submit" value="Eradicate" /></form>';
	}
	
	if ($holopad) {
		echo "</br><b>Holopad</b></br>";
		echo "Simply insert a card or place a dowel on the pad and it will show you a holographic image of the item to which that code corresponds. It will also inform you of the grist cost.</br>";
		if (strpos($userrow['storeditems'], "HOLOPLUS.") !== false) echo "<i>Thanks to your holopad upgrade, you will also learn the power level, abstratus, and size of the item!</i></br>";
		echo '<form action="inventory.php" method="post">Code-containing item: <select name="holoitem">';
		$i = 1;
		while ($i <= $max_items) {
			$invstr = 'inv' . strval($i);
			if (strpos($userrow[$invstr], "Cruxite Dowel (CODE:") !== false || strpos($userrow[$invstr], "Captchalogue Card (CODE:") !== false) {
				echo '<option value="' . $invstr . '">' . $userrow[$invstr] . '</option>';
			}
			$i++;
		}
  	echo '</select><input type="submit" value="Observe it!" /></form>';
		echo "(You will be taken to the inventory page.)</br>";
		$hasanything = true;
	}
	
	if ($combofinder) {
		echo "</br><b>Recipe Finder</b></br>";
		echo "This device scans codes within a given area, randomly mashes them together, and spits out at least one recipe if it finds something that creates an existing item.<br />";
		echo "The process takes forever, though, and you must spend at least one encounter waiting for it to finish scanning and registering all of your cards.<br />";
		echo "Select a recipe-finding device from storage to use. Each device has a unique work area, encounter cost, and recipe return.<br />";
		echo "<form action='sburbdevices.php' method='post'><select name='combodevice'>";
		$i = 1;
		while ($i <= $cf) {
			if ($combofind['return'][$i] == 0) $amountstr = "all";
			else $amountstr = strval($combofind['return'][$i]);
			echo "<option value='$i'>" . $combofind['name'][$i] . " - " . strval($combofind['cost'][$i]) . " encounter(s) for $amountstr " . $combofind['area'][$i] . " recipes</option>";
			$i++;
		}
		echo "<input type='submit' value='Boot it up!' /></form>";
	}
	
	if ($intlaser) {
		echo "</br><b>Intellibeam Laserstation</b></br>";
		echo "Simply put, this device reads codes on the back of captchalogue cards with perfect accuracy. Useful for when you are unable to read them yourself.</br>";
		echo '<form action="sburbdevices.php" method="post">Item to decipher: <select name="ilitem">';
		$i = 1;
		while ($i <= $max_items) {
			$invstr = 'inv' . strval($i);
			if (!empty($userrow[$invstr]))
			echo '<option value="' . $invstr . '">' . $userrow[$invstr] . '</option>';
			$i++;
		}
  	echo '</select><input type="submit" value="Read it!" /></form>';
  	$hasanything = true;
	}
	
	if ($sendify) {
		echo "</br><b>Sendificator</b></br>";
		echo "Insert an item to sendify it directly to the storage of any of your sessionmates.</br>Your sendificator allows you to send items of size \"$maxsendsize\" or smaller.</br>";
		echo '<form action="sburbdevices.php" method="post">Item to send: <select name="senditem">';
		$i = 1;
		while ($i <= $max_items) {
			$invstr = 'inv' . strval($i);
			if (!empty($userrow[$invstr]))
			echo '<option value="' . $invstr . '">' . $userrow[$invstr] . '</option>';
			$i++;
		}
  	echo '</select><br />Target player: <select name="sendplayer">';
  	$materesult = mysql_query("SELECT `username` FROM `Players` WHERE `Players`.`session_name` = '" . mysql_real_escape_string($userrow['session_name']) . "'");
  	while ($materow = mysql_fetch_array($materesult)) {
  		if ($materow['username'] != $username) //don't send items to yourself because that's silly
  		echo '<option value="' . $materow['username'] . '">' . $materow['username'] . '</option>';
  	}
  	echo '</select><br /><input type="submit" value="Send it!" /></form>';
  	$hasanything = true;
	}
	
	if ($canghost) {
		echo "</br><b>" . $ghostname . "</b></br>";
		echo "You can use this to create ghost images from items in storage that you can't captchalogue otherwise.<br />";
		echo "Ghost images can't be used physically and are worth no grist, but you will be able to view the item's code and information, as well as use it in alchemy.<br />";
		echo "To get rid of a ghost image, either recycle it or attempt to store it. It will disappear either way.<br />";
		echo '<form action="sburbdevices.php" method="post">Item to ghostify: <select name="ghostitem">';
		$storedstuff = explode("|", $userrow['storeditems']);
		$totalstored = count($storedstuff);
		$i = 0;
		while ($i <= $totalstored) {
			$thisarray = explode(":", $storedstuff[$i]);
			$storesult = mysql_query("SELECT `captchalogue_code`,`name` FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '" . $thisarray[0] . "' LIMIT 1;");
			while ($storow = mysql_fetch_array($storesult)) {
				echo '<option value="' . $storow['captchalogue_code'] . '">' . $storow['name'] . '</option>';
			}
			$i++;
		}
  	echo '</select><input type="submit" value="Ghost it!" /></form>';
  	$hasanything = true;
	}
	
	if (!$hasanything) echo "None! Ask your server player to deploy something!</br>";
	
}

require_once("footer.php");
?>