<?php
require 'designix.php';
require 'additem.php';
require 'monstermaker.php'; //lol blade cloud
require_once 'includes/effectprinter.php'; //for printing effects, consolidated into an include for simplicity (also includes glitches)
require_once("header.php");
require_once("includes/grist_icon_parser.php");

$max_items = 50;

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

if (empty($_SESSION['username'])) {
  echo "Log in to view and manipulate your inventory.</br>";
} elseif ($userrow['dreamingstatus'] != "Awake") {
  echo "Your dream self can't access your sylladex!";
} else {
  echo "<!DOCTYPE html><html><head><style>gristvalue{color: #FF0000; font-size: 60px;}</style><style>gristvalue2{color: #0FAFF1; font-size: 60px;}</style><style>itemcode{font-family:'Courier New'}</style></head><body>";
  echo '<div style = "float: right;"><a href="alchemybasics.php">The basics of alchemy</a></div>';
  $maxstorage = $userrow['house_build_grist'] + 1000;
  $gristed = false; //will be set to true when grist types are initialized
  $sessionname = $userrow['session_name'];
	$sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$sessionname'");
	$sessionrow = mysql_fetch_array($sessionresult);
	$challenge = $sessionrow['challenge'];
	$canon = $sessionrow['canon'];
	if (strpos($userrow['storeditems'], "USELESS.") !== false) $useless = true;
	else $useless = false;
	if (strpos($userrow['storeditems'], "RECYCLER.") !== false) $recycler = true;
	else $recycler = false;

  //--Begin designix code here.--
  $camefromscanner = false;
  if (!empty($_POST['invcode1']) && !empty($_POST['invcode2'])) { //shortcuts!
  	if (!$canon || strpos($userrow['storeditems'], "CAPTCHASCAN.") !== false) {
  	$invslot = $_POST['invcode1'];
  	$itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
  	$ghostmode = false;
  	if (strpos($itemname, " (ghost image)") !== false) {
  		$itemname = str_replace(" (ghost image)", "", $itemname);
  		$ghostmode = true;
  	}
  	if (strpos($itemname, " (CODE:" !== false)) {
  		$itemname = substr($itemname, 0, -16);
  	}
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($itemrow = mysql_fetch_array($itemresult)) {
	$itemname = $itemrow['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$invslot] || $ghostmode) {
		if (strpos($userrow['storeditems'], "LASERSTATION.") === false && strpos($itemrow['effects'], "OBSCURED|") !== false) echo "Not even your fancy-pants remote captchalogue scanner can read the code on the back of that first item!</br>";
		else $_POST['code1'] = $itemrow['captchalogue_code'];
	}
      }
    $invslot = $_POST['invcode2'];
  	$itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
  	$ghostmode = false;
  	if (strpos($itemname, " (ghost image)") !== false) {
  		$itemname = str_replace(" (ghost image)", "", $itemname);
  		$ghostmode = true;
  	}
  	if (strpos($itemname, " (CODE:" !== false)) {
  		$itemname = substr($itemname, 0, -16);
  	}
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($itemrow = mysql_fetch_array($itemresult)) {
	$itemname = $itemrow['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$invslot] || $ghostmode) {
		if (strpos($userrow['storeditems'], "LASERSTATION.") === false && strpos($itemrow['effects'], "OBSCURED|") !== false) echo "Not even your fancy-pants remote captchalogue scanner can read the code on the back of that second item!</br>";
		else $_POST['code2'] = $itemrow['captchalogue_code'];
	}
      }
      if (!empty($_POST['code1']) && !empty($_POST['code2'])) $camefromscanner = true;
  	} else echo "You don't have a machine that can auto-scan and combine codes!<br />";
  }
    
  if (!empty($_POST['code1']) && !empty($_POST['code2'])) { //User is performing designix operations.
  if (!$canon || $camefromscanner || strpos($userrow['storeditems'], "CAPTCHACOMBINE.") !== false) {
  	$letthrough = false;
		if ($challenge == 1) {
			$skip1 = false;
			$skip2 = false;
			$itemresult1 = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['code1'] . "' ;");
			while ($itemrow1 = mysql_fetch_array($itemresult1)) {
				if (!(strrpos($sessionrow['atheneum'], $itemrow1['captchalogue_code']) === false)) $skip1 = true;
			}
			$itemresult2 = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['code2'] . "' ;");
			while ($itemrow2 = mysql_fetch_array($itemresult2)) {
				if (!(strrpos($sessionrow['atheneum'], $itemrow2['captchalogue_code']) === false)) $skip2 = true;
			}
			if ($skip1 && $skip2) $letthrough = true;
		} else $letthrough = true;
    if ($userrow['Build_Grist'] >= 4) {
      if ($_POST['combine'] == "or") {
	$code = orcombine($_POST['code1'], $_POST['code2']);
      } else {
	$code = andcombine($_POST['code1'], $_POST['code2']);
      }
      if ($letthrough == true) {
      echo "You expend four Build Grist creating two captchalogue cards which the designix punches holes into corresponding to the codes.</br>";
      echo "After a brief delay, the designix finishes and sends you the code <itemcode>$code</itemcode></br>";
      mysql_query("UPDATE `Players` SET `Build_Grist` = " . strval($userrow['Build_Grist']-4) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      if ($challenge == 1) $_GET['holocode'] = $code; //go straight into the holopad operation if challenge mode
			$combined = true; //so that you can't cheat to preview any code
      } else echo "You can't combine codes that aren't in your atheneum in Challenge Mode. If the code(s) you tried to combine is/are in your inventory, then it is bugged somehow. Use the populate atheneum page to fix it.</br>";
    } else {
      echo "You need four Build Grist to produce the cards required to use the designix!</br>";
    }
  } else echo "You don't have a machine that can auto-combine codes!<br />";
  }
    
  //--End designix code here. Begin holopad code here.--
  
  if (!empty($_POST['holoitem'])) {
  	if (strpos($userrow['storeditems'], "HOLOPAD.") !== false) {
  		if (strpos($userrow[$_POST['holoitem']], "Cruxite Dowel (CODE:") !== false || strpos($userrow[$_POST['holoitem']], "Captchalogue Card (CODE:") !== false) {
				$_GET['holocode'] = substr($userrow[$_POST['holoitem']], -9, 8);
				echo "The Holopad processes the code...</br>";
  		} else echo "That object is not compatible with the Holopad!</br>";
  	} else echo "You don't have a device that can preview items!</br>";
  }
  
  if (!empty($_GET['holocode']) && $canon) { //stop CANON, not challenge, players from using links to preview without holopad
  	if (strpos($userrow['storeditems'], "HOLOPAD.") === false && strpos($userrow['storeditems'], "REMOTEHOLO.") === false) {
  		echo "You don't have a device that can preview items!</br>";
  		$_GET['holocode'] = "";
  	}
  }
    
  if (!empty($_GET['holocode'])) { //User is using the holopad.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_GET['holocode'] . "'");
    $itemfound = False;
    while ($itemrow = mysql_fetch_array($itemresult)) {
      if ($itemrow['captchalogue_code'] == $_GET['holocode']) {
      if (!strrpos($sessionrow['atheneum'], $_GET['holocode'])) {
      	if ($challenge == 0 || $combined) {
      	$newatheneum = $sessionrow['atheneum'] . $_GET['holocode'] . "|";
      	mysql_query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '$sessionname' LIMIT 1 ;");
      	} else {
      		echo "The code you are previewing has not yet been discovered by your session. You can view it, but you have to either combine the items to make it or physically acquire the item another way to add it to your atheneum.</br>";
      	}
      }
	$itemfound = True;
	$nothing = True;
	$itemname = $itemrow['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemrow['art'] != "") echo '<img src="/Images/Items/' . $itemrow['art'] . '" title="Image by ' . $itemrow['credit'] . '"></br>';
	if (substr($itemname, 0, 2) == "A " || substr($itemname, 0, 3) == "An " || substr($itemname, 0, 4) == "The ")
	echo "The holopad displays $itemname. It also prints out a short description:</br>";
	else
	echo "The holopad displays the $itemname. It also prints out a short description:</br>";
	$desc = descvarConvert($userrow, $itemrow['description'], $itemrow['effects']);
	echo $desc . "</br>" . "The inputted holocode is repeated: <span style='color:#F3C; font-size:30px; font-weight:bold;'>" . $_GET['holocode'] . "</span><br />" . "It costs ";
	$reachgrist = False;
	$terminateloop = False; //time-saver
	if ($gristed == false) {
		$gristname = initGrists();
		$totalgrists = count($gristname);
		$gristed = true;
	}
	$gristcount = 0;
	while ($gristcount <= $totalgrists) {
		$gristtype = $gristname[$gristcount];
	  $gristcost = $gristtype . "_Cost";
	  if ($itemrow[$gristcost] != 0) { //Item requires some of this grist. Or produces some. Either way.
	    $nothing = False; //Item costs something.
	      echo '<img src="Images/Grist/' . gristNameToImagePath($gristtype) . '" height="50" width="50" title="' . $gristtype . '"></img>';
	      if ($userrow[$gristtype] >= $itemrow[$gristcost]) {
	        echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	      } else {
	      	echo " <gristvalue>$itemrow[$gristcost] </gristvalue>";
	      }
	  }
	  $gristcount++;
	}
	if (strpos($itemrow['effects'], "FLAVORCOST") !== false) {
		$i = 0;
		$effectarray = explode("|", $itemrow['effects']);
		while (!empty($effectarray[$i])) {
			$flavarray = explode(":", $effectarray[$i]);
			if ($flavarray[0] == "FLAVORCOST") {
				echo '<img src="Images/Grist/' . gristNameToImagePath($flavarray[1]) . '" height="50" width="50" title="' . $flavarray[1] . '"></img>';
				echo " <gristvalue2>" . $flavarray[2] . " </gristvalue2>";
				$nothing = false;
			}
			$i++;
		}
	}
	if ($nothing) { //Item costs nothing! SORD.....
	  echo '<img src="Images/Grist/Build_Grist.png" height="50" width="50" title="Build_Grist"></img>';
	  echo " <gristvalue2>0 </gristvalue2>";
	}
	if ($userrow['session_name'] == "Itemods" || $userrow['session_name'] == "Developers" || strpos($userrow['storeditems'], "HOLOPLUS.") !== false) {
		echo "</br>";
		echo "Abstratus: $itemrow[abstratus]</br>";
	  echo "Strength: $itemrow[power]</br>";
	  echo "Size: $itemrow[size]</br>";
	  if ($itemrow['aggrieve'] != 0) echo "Aggrieve: $itemrow[aggrieve]</br>";
	  if ($itemrow['aggress'] != 0) echo "Aggress: $itemrow[aggress]</br>";
	  if ($itemrow['assail'] != 0) echo "Assail: $itemrow[assail]</br>";
	  if ($itemrow['assault'] != 0) echo "Assault: $itemrow[assault]</br>";
	  if ($itemrow['abuse'] != 0) echo "Abuse: $itemrow[abuse]</br>";
	  if ($itemrow['accuse'] != 0) echo "Accuse: $itemrow[accuse]</br>";
	  if ($itemrow['abjure'] != 0) echo "Abjure: $itemrow[abjure]</br>";
	  if ($itemrow['abstain'] != 0) echo "Abstain: $itemrow[abstain]</br>";
	}
      }
    }
    if ($combined) {
    	if ($_POST['combine'] == "or") $recipetext = "&newrecipe=" . $itemrow1['name'] . "%20||%20" . $itemrow2['name'];
    	else $recipetext = "&newrecipe=" . $itemrow1['name'] . "%20%26%26%20" . $itemrow2['name'];
    } else $recipetext = "";
    if ($itemfound == False) echo 'The holopad informs you that the code you have inputted refers to an item that does not exist yet. <a href="feedback.php?type=item&newcode=' . $_GET['holocode'] . $recipetext . '">Suggest this item!</a></br>';
    if ($itemfound == True) echo "</br>";
    if ($challenge == 1 && $combined) { //go ahead and add to the atheneum anyway so that the player can suggest the item if they want
      if (!strrpos($sessionrow['atheneum'], $_GET['holocode'])) {
      	$newatheneum = $sessionrow['atheneum'] . $_GET['holocode'] . "|";
      	mysql_query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '$sessionname' LIMIT 1 ;");
      }
    }
  }
    
  //--End holopad code here. Begin alchemiter code here.--
  
  if (!empty($_POST['alchcrux'])) {
  	if (strpos($userrow['storeditems'], "ALCHEMITER.") !== false) {
  		if (strpos($userrow[$_POST['alchcrux']], "Cruxite Dowel (CODE:") !== false) {
  			if (!$useless) {
					$_POST['alchcode'] = substr($userrow[$_POST['alchcrux']], -9, 8);
					echo "The laser arm on the Alchemiter scans the cruxite dowel and processes its code...</br>";
  			} else echo "One of your jumper block \"upgrades\" has made the Alchemiter completely useless!</br>";
  		} else echo "That object is not compatible with the alchemiter!</br>";
  	} else echo "You don't have a device that can alchemize items!</br>";
  }
    
  if (!empty($_POST['alchcode'])) { //User is using the alchemiter.
  	$letthrough = false;
  	if ($challenge == 1) {
  		if (strrpos($sessionrow['atheneum'], $_POST['alchcode']) === false) $letthrough = false;
  		else $letthrough = true;
  	} else $letthrough = true;
  	if ($letthrough) {
    if (empty($_POST['alchnum'])) { //User didn't specify amount of items to make, so assume 1
       $numberalched = 1;
       } else {
       $numberalched = $_POST['alchnum'];
       if ($numberalched < 0) $numberalched = $numberalched * -1;
       }
       $tostorage = 0;
    $n = 1;
    if (empty($_POST['autostore']) && $userrow['sessionbossengaged'] == 0) {
    	$freespots = 0;
    	$notenoughspots = False;
    	while ($n <= 50) { //find out how many free spots are available in the user's inventory
      	$invstr = "inv" . $n;
      	if ($userrow[$invstr] == '') $freespots++;
      	$n++;
      }
    	if ($freespots < $numberalched) { //if the user tried to make more items than they could fit, set the actual number of items made to the amount of slots they have
    		$tostorage = $numberalched - $freespots; //send the rest to storage
      	$numberalched = $freespots;
      	$notenoughspots = True;
     	}
    } else {
        if ($userrow['sessionbossengaged'] != 0) echo "Unfortunately you're kind of too busy to pick up any items right now, so they just sit around on the Alchemiter pad until some helpful consorts cart them into your house.</br>";
    	$tostorage = $numberalched;
    	$numberalched = 0;
    }
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['alchcode'] . "'");
    $itemfound = False;
    $canafford = True;
    $nothing = True;
    $reachgrist = False;
    $terminateloop = False;
    $itemrow = mysql_fetch_array($itemresult);
      if ($itemrow['captchalogue_code'] == $_POST['alchcode']) {
      	if (itemSize($itemrow['size']) > itemSize($userrow['moduspower'])) { //item is too big for the player's fetch modus
      		$tostorage += $numberalched; //send all to storage instead of trying to captchalogue them, because it bugs out otherwise
      		$numberalched = 0;
      		$freespots = 0;
      	}
	$itemfound = True;
	$alchrow = $itemrow; //for use later
	echo "The item costs ";
	if (!$gristed) {
		$gristname = initGrists();
		$totalgrists = count($gristname);
		$gristed = true;
	}
	$gristcount = 0;
	while ($gristcount <= $totalgrists) {
		$gristtype = $gristname[$gristcount];
	  $gristcost = $gristtype . "_Cost";
	  //echo $gristtype . " " . $gristcost;
	  //echo strval($itemrow[$gristcost]);
	  if ($itemrow[$gristcost] != 0) { //Item requires some of this grist. Or produces some. Either way.
	    $nothing = False; //Item costs something.
	    if ($userrow[$gristtype] < $itemrow[$gristcost] * ($numberalched + $tostorage)) { //Player cannot afford to alchemize this item.
	      $canafford = False;
	    }
	    echo '<img src="Images/Grist/' . gristNameToImagePath($gristtype) . '" height="50" width="50" title="' . $gristtype . '"></img>';
	    if ($userrow[$gristtype] >= $itemrow[$gristcost]) {
	      echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	    } else {
	      echo " <gristvalue>$itemrow[$gristcost] </gristvalue>";
	    }
	  }
	  $gristcount++;
	}
	if (strpos($itemrow['effects'], "FLAVORCOST") !== false) {
		$i = 0;
		$effectarray = explode("|", $itemrow['effects']);
		while (!empty($effectarray[$i])) {
			$flavarray = explode(":", $effectarray[$i]);
			if ($flavarray[0] == "FLAVORCOST") {
				echo '<img src="Images/Grist/' . gristNameToImagePath($flavarray[1]) . '" height="50" width="50" title="' . $flavarray[1] . '"></img>';
				echo " <gristvalue2>" . $flavarray[2] . " </gristvalue2>";
				$nothing = false;
			}
			$i++;
		}
	}
	if ($nothing) { //Item costs nothing! SORD.....
	  echo '<img src="Images/Grist/Build_Grist.png" height="50" width="50" title="Build_Grist"></img>';
	  echo " <gristvalue2>0 </gristvalue2>";
	}
      }
    if ($itemfound == True && $canafford == True) { //Player successfully creates item.
	  $n = 0; //0 instead of 1 so that it'll give a failure return and print the standard "no room" code if there's no room for even 1 of them
	  while ($n < $numberalched) { //earlier code should prevent making the item if not enough space for it
	    $itemslot = addItem($itemrow['name'],$userrow); //We need to use this result later.
	    if ($itemslot != "inv-1") $userrow[$itemslot] = $itemrow['name']; //update this so using the userrow again will not overwrite the item we just added
	    else $tostorage++; //if no room, add one to the amount we need to send to storage
	    $n++;
	  }
	  if ($tostorage > 0) { //we're making items and sending them to storage, either because there's no room in the player's inventory or the player wants them to go there anyway
			$actualstore = storeItem($itemrow['name'], $tostorage, $userrow);
			if ($actualstore < $tostorage) {
				$nospace = true;
			}
	  }
	  echo "</br>";
	  $itemname = $itemrow['name'];
	  $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	  if ($itemslot != "inv-1" || $actualstore > 0) { //Give them the item and check to see if they got it. inv-1 is the failure return.
	    $itemname = $itemrow['name'];
	    $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	    $alchitem = $itemname;
	    //require_once("includes/SQLconnect.php"); //Reconnection appears necessary due to addItem making its own little connection.
	if (!$gristed) {
		$gristname = initGrists();
		$totalgrists = count($gristname);
		$gristed = true;
	}
	$gristcount = 0;
	$costquery = "UPDATE Players SET ";
	while ($gristcount <= $totalgrists) {
		$gristtype = $gristname[$gristcount];
	  $gristcost = $gristtype . "_Cost";
	      $actualcost = 0;
	      	$actualcost = $itemrow[$gristcost] * ($numberalched + $actualstore);
		if ($actualcost != 0) $costquery = $costquery . "`$gristtype` = $userrow[$gristtype]-$actualcost, ";
		$gristcount++;
	    }
	    $costquery = substr($costquery, 0, -2); //Dispose of last comma and space.
	    $costquery = $costquery . " WHERE `Players`.`username` = '$username' LIMIT 1 ;";
	    //echo "Costquery is " . $costquery;
	    mysql_query($costquery); //Pay.
	    if ($numberalched == 1) {
	    	if (substr($itemname, 0, 2) == "A " || substr($itemname, 0, 3) == "An " || substr($itemname, 0, 4) == "The ")
	      echo "You successfully create $itemname!</br>";
	      else
	      echo "You successfully create the $itemname!</br>";
	      } else {
	      if ($notenoughspots) {
	      	if ($numberalched > 0)
	        echo "You create $itemname x $numberalched before your Sylladex fills up completely.</br>";
	        else
	        echo "You have no room in your Sylladex for this item!</br>";
		} else {
			if ($numberalched > 0)
		echo "You successfully create $itemname x $numberalched!</br>";
		}
	      }
	  } else {
	    //require_once("includes/SQLconnect.php");
	    echo "You have no room in your Sylladex for this item!</br>"; //May change this to do weird things. But probably not.
	  }
	  if ($actualstore > 0) {
	  	if (!$nospace) echo "$itemname x $actualstore created and sent to storage.</br>";
	  	else echo "$itemname x $actualstore created and sent to storage before running out of storage space.</br>";
	  } elseif ($nospace) echo "You have no room in storage for this item!</br>";
	  if (strpos($itemrow['abstratus'], "bladekind") !== false && $userrow['down'] == 0 && $userrow['enemydata'] == "" && $userrow['aiding'] == "") { //random chance of summoning the blade cloud, gasp!
	    $bcchance = floor($itemrow['power'] / 100); //max chance of 99% for endgame weapons
	    $bcchance = $bcchance / 2; //except not actually
	    if ($bcchance < 1) $bcchance = 1; //min chance of 1% for weak weapons
	    $bctries = $numberalched + $actualstore;
	    $bc = 0;
	    while ($bc < $bctries) {
	      $bcroll = rand(1,100);
	      if ($bcroll <= $bcchance) {
	        $slot = generateEnemy($userrow,"None",0,"Blade Cloud",false);
	        if ($slot != -1) {
	          echo "Suddenly, ";
	          if (substr($itemname, 0, 2) == "A " || substr($itemname, 0, 3) == "An " || substr($itemname, 0, 4) == "The ")
	            echo $itemname;
	          else
	            echo "the " . $itemname;
	          echo " floats off of the alchemiter and summons a bunch of other swords from seemingly nowhere, which spiral into a whirlwind of blades hurtling straight at you!<br />";
	          echo '<a href="strife.php">==&gt;</a><br />';
	        }
	        $bc = $bctries;
	      }
	      $bc++;
	    }
	  }
    }
    if ($itemfound == True && $canafford == False) {
       if ($numberalched == 1) {
       	  echo "</br>You cannot afford to make this item, whatever it is.</br>";
	  } else {
	  echo "</br>You cannot afford to make that many copies of this item, whatever it is.</br>";
	  }
	}
    if ($itemfound == False) echo 'The alchemiter informs you that the code you have inputted refers to an item that does not exist yet. <a href="feedback.php?type=item&newcode=' . $_POST['alchcode'] . '">Suggest this item!</a></br>';
  } else echo "The code you have inputted is not in your Atheneum yet. In Challenge Mode, you must acquire a code before an item can be made with it.</br>";
  }

  //--End Alchemiter code here. Begin Grist Recycler code here.--

  if (!empty($_POST['recycle'])) { //User is recycling an inventory item. recycle gives an inventory slot to recycle or multi for multiple
  	if ($_POST['recycle'] != "multi") {
  		$_POST[$_POST['recycle']] = $_POST['recycle']; //a little hacky/convoluted but it should work
  	} else {
  		//echo "attempting to multicycle</br>";
  	}
  	$currentrecycle = 1;
  	while ($currentrecycle <= $invslots) {
  		$invstring = 'inv' . strval($currentrecycle);
  		//echo $invstring . " (" . $_POST[$invstring] . ") = " . $userrow[$invstring];
  		if (!empty($_POST[$invstring])) {
    $itemname = str_replace("'", "\\\\''", $userrow[$_POST[$invstring]]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    if (strpos($itemname, " (ghost image)") !== false) {
  		$itemname = str_replace(" (ghost image)", "", $itemname);
  		$isghost = true;
  	} else {
  		$isghost = false;
  	}
  	if (strpos($itemname, "(CODE:") !== false) {
  		$itemname = substr($itemname, 0, -16);
  		$userrow[$_POST[$invstring]] = $itemname;
  		//echo $itemname;
  	}
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
    $nothing = True;
    $success = False; //this is needed in case the item is a ghost item
    while ($itemrow = mysql_fetch_array($itemresult)) {
      $itemname = $itemrow['name'];
      $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
      if ($itemname == $userrow[$_POST[$invstring]] || $isghost) {
	$recycled[$invstring] = true; //For use later.
	$success = True;
	$deploytag = specialArray($itemrow['effects'], "DEPLOYABLE");
	if ($deploytag[0] != "DEPLOYABLE") {
	echo "You recycle your $itemname into ";
	mysql_query("UPDATE `Players` SET `" . $_POST[$invstring] . "` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	autoUnequip($userrow,"none",$_POST[$invstring]);
	if (!$gristed) {
		$gristname = initGrists();
		$totalgrists = count($gristname);
		$gristed = true;
	}
	$gristcount = 0;
	$refundquery = "UPDATE Players SET ";
	if ($isghost == false) {
		while ($gristcount <= $totalgrists) {
			$gristtype = $gristname[$gristcount];
	  	$gristcost = $gristtype . "_Cost";
	  	if ($itemrow[$gristcost] != 0) { //Item requires some of this grist. Or produces some. Either way.
	    	$nothing = False; //Item costs something.
	    	$refundquery = $refundquery . "`$gristtype` = $userrow[$gristtype]+$itemrow[$gristcost], ";
	    	$userrow[$gristtype] += $itemrow[$gristcost];
	    	echo '<img src="Images/Grist/' . gristNameToImagePath($gristtype) . '" height="50" width="50" title="' . $gristtype . '"></img>';
	    	echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	  	}
	  	$gristcount++;
		}
		if (strpos($itemrow['effects'], "FLAVORCOST") !== false) {
			$i = 0;
			$effectarray = explode("|", $itemrow['effects']);
			while (!empty($effectarray[$i])) {
				$flavarray = explode(":", $effectarray[$i]);
				if ($flavarray[0] == "FLAVORCOST") {
					echo '<img src="Images/Grist/' . gristNameToImagePath($flavarray[1]) . '" height="50" width="50" title="' . $flavarray[1] . '"></img>';
					echo " <gristvalue2>" . $flavarray[2] . " </gristvalue2>";
					$nothing = false;
				}
				$i++;
			}
		}
	} else {
		$nothing = true; //treat ghost items as having no cost whatsoever
	}
	if ($nothing) { //Item costs nothing! SORD.....
	  echo '<img src="Images/Grist/Build_Grist.png" height="50" width="50" title="Build_Grist"></img>';
	  echo " <gristvalue2>0 </gristvalue2>";
	} else { //Item costed something, use the refund query to restore grist.
	  $refundquery = substr($refundquery, 0, -2); //Dispose of last comma and space.
	  $refundquery = $refundquery . " WHERE `Players`.`username` = '$username' LIMIT 1 ;";
	  mysql_query($refundquery); //Un-pay.
	}
	} else {
		mysql_query("UPDATE `Players` SET `" . $_POST[$invstring] . "` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		echo "The item was recycled, but the recycler produced no grist! It seems that the recycler can't handle SBURB machinery properly...</br>";
	}
      }
    }
    if ($success == False) {
    	if ($userrow[$_POST[$invstring]] != "") {
	      mysql_query("UPDATE `Players` SET `" . $_POST[$invstring] . "` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      	echo "It seems that the item you tried to recycle (" . $userrow[$_POST[$invstring]] . ") no longer exists, or never existed to begin with. You get no grist, but the item has been removed from your inventory, freeing the slot. If you alchemized that item legitimately, please submit a bug report and we'll return your grist ASAP!";
      	logDebugMessage($username . " - tried to recycle " . $userrow[$_POST[$invstring]] . ", was told it didn't exist");
      	$userrow[$_POST[$invstring]] = "";
    	} else {
    		echo "You don't have an item in that inventory slot. Maybe it was already recycled?";
    	}
    }
    echo "</br>";
  		}
    $currentrecycle++;
  	}
  	compuRefresh($userrow);
  }

  //--End Grist Recycler code here.--

  if (empty($recycled)) $recycled = array();
  if (empty($alchitem)) $alchitem = "";
  if (empty($itemslot)) $itemslot = "inv-1";
  if (!$canon || strpos($userrow['storeditems'], "CAPTCHACOMBINE.") !== false) {
  echo "Remote Punch Designix access v0.0.1a. Insert two codes and four Build Grist to continue.";
  echo '<form action="inventory.php" method="post">First code: <input id="code1" name="code1" type="text" /><br />';
  echo 'Second code: <input id="code2" name="code2" type="text" /><br />';
  echo 'Combination to use: <select name="combine"><option value="or">||</option><option value="and">&&</option></select></br>';
  echo '<input type="submit" value="Design it!" /></form></br>';
  }
  if (!$canon || strpos($userrow['storeditems'], "CAPTCHASCAN.") !== false) {
  echo "Remote Punch Designix access with Captchalogue Scanner v0.0.1a. Insert two captchalogue cards and four Build Grist to continue.";
  echo '<form action="inventory.php" method="post">First item:<select name="invcode1">';
  $invcount = 1;
  while ($invcount <= $max_items) {
    $invslot = 'inv' . strval($invcount);
    if ($userrow[$invslot] != "" && !$recycled[$invslot]) { //This is a non-empty inventory slot that wasn't just recycled away.
      echo '<option value = "' . $invslot . '">' . $userrow[$invslot] . '</option>'; //Add option to use this slot for alchemy.
    }
    $invcount++;
  }
  if ($itemslot != "inv-1" && !empty($itemslot)) { //Player alchemized an item.
    echo '<option value = "' . $itemslot . '">' . $alchitem . '</option>';
  }
  echo '</select><br />Second item:<select name="invcode2">';
  $invcount = 1;
  while ($invcount <= $max_items) {
    $invslot = 'inv' . strval($invcount);
    if ($userrow[$invslot] != "" && !$recycled[$invslot]) { //This is a non-empty inventory slot that wasn't just recycled away.
      echo '<option value = "' . $invslot . '">' . $userrow[$invslot] . '</option>'; //Add option to use this slot for alchemy.
    }
    $invcount++;
  }
  if ($itemslot != "inv-1" && !empty($itemslot)) { //Player alchemized an item.
    echo '<option value = "' . $itemslot . '">' . $alchitem . '</option>';
  }
  echo '</select><br />';
  echo 'Combination to use: <select name="combine"><option value="or">||</option><option value="and">&&</option></select></br>';
  echo '<input type="submit" value="Design it!" /></form></br>';
  echo '</br>';
  }
  if (!$canon || strpos($userrow['storeditems'], "REMOTEHOLO.") !== false) {
  echo "Remote holopad access v0.0.1a. Insert code to preview.";
  echo '<form action="inventory.php" method="get">Captchalogue code: <input id="holocode" name="holocode" type="text" /><br />';
  echo '<input type="submit" value="Observe it!" /></form></br>';
  echo '</br>';
  }
  if (!$canon || strpos($userrow['storeditems'], "MANUALCHEMITER.") !== false) {
  echo "Alchemiter v0.0.1a. Insert code to synthesize.";
  echo '<form action="inventory.php" method="post">Captchalogue code: <input id="alchcode" name="alchcode" type="text" /><br />Make this many (blank for 1): <input id="alchnum" name="alchnum" type="text" />';
  echo '<br /><input name="autostore" type="checkbox"> Send all created items to storage</br>';
  echo '<input type="submit" value="Create it!" /></form></br>';
  echo '</br>';
  }
  if (!$canon || $recycler) {
  echo "Grist Recycler v0.0.1a. Please select a captchalogued item.</br>";
  echo "Please refresh the interface before attempting to recycle newly alchemized items.";
  echo '<form action="inventory.php" method="post"><select name="recycle">';
  $invcount = 1;
  while ($invcount <= $max_items) {
    $invslot = 'inv' . strval($invcount);
    if ($userrow[$invslot] != "" && !$recycled[$invslot]) { //This is a non-empty inventory slot that wasn't just recycled away.
      echo '<option value = "' . $invslot . '">' . $userrow[$invslot] . '</option>'; //Add option to recycle this slot.
    }
    $invcount++;
  }
  if ($itemslot != "inv-1" && !empty($itemslot)) { //Player alchemized an item.
    echo '<option value = "' . $itemslot . '">' . $alchitem . '</option>';
  }
  echo '</select> <input type="submit" value="Recycle it!" /> </form>';
  echo '</br>Use the checkboxes below to choose items to mass-recycle.</br>';
  }

  //--Begin displaying user inventory here.--

  echo $username;
  echo "'s inventory:</br></br>";
  echo '<form action="inventory.php" method="post">';
    $invcount = 1;
	$captchalogue = "SELECT * FROM Captchalogue WHERE";
	$captchaloguequantities = array();
	$firstinvslot = array();
	$invspace = 0;
  while ($invcount <= $max_items) {
    $invslot = 'inv' . strval($invcount);
    if ($userrow[$invslot] != "" && !$recycled[$invslot]) { //This is a non-empty inventory slot that wasn't just recycled away.
	  $pureitemname = str_replace("\\", "", $userrow[$invslot]);
	  $pureitemname = str_replace("'", "", $pureitemname);
	  if (strpos($pureitemname, " (ghost image)") !== false) {
	  	$pureitemname = str_replace(" (ghost image)", "", $pureitemname);
	  	if (empty($captchaloguequantities[$pureitemname])) {
	  		$ghostquantities[$pureitemname] = 1;
	  	} else {
	  		$ghostquantities[$pureitemname] += 1;
	  	}
	  }
	  if (strpos($pureitemname, "(CODE:") !== false) { //this item is holding a code, like a captchalogue card or a cruxite dowel
	  	$pureitemname = substr($pureitemname, 0, -16);
	  	$itemname = str_replace("'", "\\\\''", substr($userrow[$invslot], 0, -16));
	  } else {
    	$itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	  }
	  $itemname = str_replace(" (ghost image)", "", $itemname);
      //$captchalogue = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
	  if (empty($captchaloguequantities[$pureitemname])) {
		$captchalogue = $captchalogue . "`Captchalogue`.`name` = '" . $itemname . "' OR ";
		$captchaloguequantities[$pureitemname] = 1;
		$firstinvslot[$pureitemname] = $invslot;
	  } else {
		$captchaloguequantities[$pureitemname] += 1;
	  }
	  $invspace++;
	}
	$invcount++;
  }
  if ($invspace > 0) {
  echo "Inventory space used: $invspace / $max_items cards</br>Fetch modus strength: " . $userrow['moduspower'] . "</br>(The maximum size of item you can captchalogue. You may be able to increase this with certain consumables.)</br></br>";
  $captchalogue = substr($captchalogue, 0, -4);
  $captchalogueresult = mysql_query($captchalogue);  
  
  echo "<table class='inventory'>";
  $count = 1;
  while ($row = mysql_fetch_array($captchalogueresult)) {
  //table code
  if ($count == 4) {
  $count = 1;
  echo "</tr><tr>";
  } else {
  $count++;
  }
  echo "<td class='" . $row['name'] . "'>";
  //end table code
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	$pureitemname = str_replace("'", "", $itemname);
	$invslot = $firstinvslot[$pureitemname];
	$quantity = strval($captchaloguequantities[$pureitemname]);
	if (!empty($ghostquantities[$pureitemname])) {
		$quantity .= " (" . strval($ghostquantities[$pureitemname]) . " are ghost images)";
	}
	echo "Item: $itemname</br>";
	echo "Quantity: $quantity</br>";
	if ($row['art'] != "") {
	  echo '<img src="/Images/Items/' . $row['art'] . '" title="Image by ' . $row['credit'] . '"></br>';
	}
	if (strpos($row['effects'], "OBSCURED|") !== false)
	echo "Code: <itemcode>--------</itemcode></br>"; //can't read this code without a laserstation
	else
	echo "Code: <itemcode>$row[captchalogue_code]</itemcode></br>";
	$desc = descvarConvert($userrow, $row['description'], $row['effects']);
	echo "Description: $desc</br>";
	echo "Type/abstratus: $row[abstratus]</br>";
	echo "Size: $row[size] (" . strval(itemSize($row['size'])) . ")";
	if ($row['consumable'] == 1) echo ", consumable";
	echo "</br>";
	if ($row['power'] != 0) echo "Base power: $row[power]</br>";
	$actives[0] = $row['aggrieve'];
	$actives[1] = $row['aggress'];
	$actives[2] = $row['assail'];
	$actives[3] = $row['assault'];
	$highestactive = max($actives);
	if ($highestactive > 0) echo "Highest active bonus: $highestactive</br>";
	$passives[0] = $row['abuse'];
	$passives[1] = $row['accuse'];
	$passives[2] = $row['abjure'];
	$passives[3] = $row['abstain'];
	$highestpassive = max($passives);
	if ($highestpassive > 0) echo "Highest passive bonus: $highestpassive</br>";
	if (!empty($row['effects'])) { //Item has effects. Print those here.
		$effectarray = explode('|', $row['effects']);
		$effectnumber = 0;
		while (!empty($effectarray[$effectnumber])) {
			$currenteffect = $effectarray[$effectnumber];
			$currentarray = explode(':', $currenteffect);
			$efound = printEffects($currentarray);
			if (!$efound) logDebugMessage($username . " - unrecognized item property $currentarray[0] from $row[name]");
			$effectnumber++;
		}
	}
	echo '<input type="checkbox" name="' . $invslot . '" value="' . $invslot . '"> Recycle one of these</br></br>';
	  echo "</td>";

  }  echo "</table>";

  echo '</br><input type="hidden" name="recycle" value="multi"><input type="submit" value="Recycle selected items" /></form>';
  }
  else echo "Your inventory is empty.</br>";
}
require_once("footer.php");
?>