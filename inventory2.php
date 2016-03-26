<?php
require 'log.php';
require 'designix.php';
require 'additem.php';
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to view and manipulate your inventory.</br>";
} elseif ($userrow['dreamingstatus'] != "Awake") {
  echo "Your dream self can't access your sylladex!";
} else {
  echo "<!DOCTYPE html><html><head><style>gristvalue{color: #FF0000; font-size: 60px;}</style><style>gristvalue2{color: #0FAFF1; font-size: 60px;}</style></head><body>";
  
  
  $sessionname = $userrow['session_name'];
	$sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$sessionname'");
	$sessionrow = mysql_fetch_array($sessionresult);
	$challenge = $sessionrow['challenge'];

  //--Begin designix code here.--
    
  if (!empty($_POST['code1']) && !empty($_POST['code2'])) { //User is performing designix operations.
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
      echo "After a brief delay, the designix finishes and sends you the code $code</br>";
      mysql_query("UPDATE `Players` SET `Build_Grist` = " . strval($userrow['Build_Grist']-4) . " WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      if ($challenge == 1) $_GET['holocode'] = $code; //go straight into the holopad operation if challenge mode
			$combined = true; //so that you can't cheat to preview any code
      } else echo "You can't combine codes that aren't in your atheneum in Challenge Mode. If the code(s) you tried to combine is in your inventory, then it is bugged somehow. Use the populate atheneum page to fix it.</br>";
    } else {
      echo "You need four Build Grist to produce the cards required to use the designix!</br>";
    }
  }
    
  //--End designix code here. Begin holopad code here.--
    
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
	if ($itemrow['art'] != "") echo '<img src="Images/Items/' . $itemrow['art'] . '" title="Image by ' . $itemrow['credit'] . '"></br>';
	echo "The holopad displays the $itemname. It also prints out a short description:</br>";
	echo $itemrow['description'];
	echo "</br>";
	echo "It costs ";
	$reachgrist = False;
	$terminateloop = False; //time-saver
	$colresult = mysql_query("SELECT * FROM Captchalogue LIMIT 1;");
	while (($col = mysql_fetch_field($colresult)) && $terminateloop == False) {
	  $gristcost = $col->name;
	  $gristtype = substr($gristcost, 0, -5);
	  if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
	    $reachgrist = True;
	  }
	  if ($gristcost == "End_of_Grists") { //Reached the end of the grists.
	    $reachgrist = False;
	    $terminateloop = True;
	  }
	  if ($reachgrist == True && $itemrow[$gristcost] != 0) { //Item requires some of this grist. Or produces some. Either way.
	    $nothing = False; //Item costs something.
	    if ($gristtype == "Opal" || $gristtype == "Polychromite" || $gristtype == "Rainbow") { //Special cases for animated grists.
	      echo '<img src="Images/Grist/' . $gristtype . '.gif " height="50" width="50" title="' . $gristtype . '"></img>';
	      if ($userrow[$gristtype] >= $itemrow[$gristcost]) {
	        echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	      } else {
	      	echo " <gristvalue>$itemrow[$gristcost] </gristvalue>";
	      }
	    } else {
	      echo '<img src="Images/Grist/' . $gristtype . '.png " height="50" width="50" title="' . $gristtype . '"></img>';
	      if ($userrow[$gristtype] >= $itemrow[$gristcost]) {
	        echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	      } else {
	      	echo " <gristvalue>$itemrow[$gristcost] </gristvalue>";
	      }
	    }
	  }
	}
	if ($nothing) { //Item costs nothing! SORD.....
	  echo '<img src="Images/Grist/Build_Grist.png" height="50" width="50" title="Build_Grist"></img>';
	  echo " <gristvalue2>0 </gristvalue2>";
	}
	if ($userrow['session_name'] == "Itemods" || $userrow['session_name'] == "Developers") {
		echo "</br>";
		echo "Abstratus: $itemrow[abstratus]</br>";
	  echo "Strength: $itemrow[power]</br>";
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
    if ($itemfound == False) echo 'The holopad informs you that the code you have inputted refers to an item that does not exist yet. <a href="feedback.php">Suggest this item!</a></br>';
    if ($itemfound == True) echo "</br>";
    if ($challenge == 1 && $combined) { //go ahead and add to the atheneum anyway so that the player can suggest the item if they want
      if (!strrpos($sessionrow['atheneum'], $_GET['holocode'])) {
      	$newatheneum = $sessionrow['atheneum'] . $_GET['holocode'] . "|";
      	mysql_query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '$sessionname' LIMIT 1 ;");
      }
    }
  }
    
  //--End holopad code here. Begin alchemiter code here.--
    
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
    $n = 1;
    $freespots = 0;
    $notenoughspots = False;
    while ($n <= 50) { //find out how many free spots are available in the user's inventory
      $invstr = "inv" . $n;
      if ($userrow[$invstr] == '') $freespots++;
      $n++;
      }
    if ($freespots < $numberalched) { //if the user tried to make more items than they could fit, set the actual number of items made to the amount of slots they have
       $numberalched = $freespots;
       $notenoughspots = True;
       }
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['alchcode'] . "'");
    $itemfound = False;
    $canafford = True;
    $nothing = True;
    $reachgrist = False;
    $terminateloop = False;
    while ($itemrow = mysql_fetch_array($itemresult)) {
      if ($itemrow['captchalogue_code'] == $_POST['alchcode']) {
	$itemfound = True;
	echo "The item costs ";
	$colresult = mysql_query("SELECT * FROM Captchalogue");
	while (($col = mysql_fetch_field($colresult)) && $terminateloop == False) {
	  $gristcost = $col->name;
	  $gristtype = substr($gristcost, 0, -5);
	  if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
	    $reachgrist = True;
	  }
	  if ($gristcost == "End_of_Grists") { //Reached the end of the grists.
	    $reachgrist = False;
	    $terminateloop = True;
	  }
	  if ($reachgrist == True && $itemrow[$gristcost] != 0) { //Item requires some of this grist. Or produces some. Either way.
	    $nothing = False; //Item costs something.
	    if ($userrow[$gristtype] < $itemrow[$gristcost] * $numberalched) { //Player cannot afford to alchemize this item.
	      $canafford = False;
	    }
	    if ($gristtype == "Opal" || $gristtype == "Polychromite" || $gristtype == "Rainbow") { //Special cases for animated grists.
	      echo '<img src="Images/Grist/' . $gristtype . '.gif" height="50" width="50" title="' . $gristtype . '"></img>';
	      if ($userrow[$gristtype] >= $itemrow[$gristcost]) {
	        echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	      } else {
	      	echo " <gristvalue>$itemrow[$gristcost] </gristvalue>";
	      }
	    } else {
	      echo '<img src="Images/Grist/' . $gristtype . '.png" height="50" width="50" title="' . $gristtype . '"></img>';
	      if ($userrow[$gristtype] >= $itemrow[$gristcost]) {
	        echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	      } else {
	      	echo " <gristvalue>$itemrow[$gristcost] </gristvalue>";
	      }
	    }
	  }
	}
	if ($nothing) { //Item costs nothing! SORD.....
	  echo '<img src="Images/Grist/Build_Grist.png" height="50" width="50" title="Build_Grist"></img>';
	  echo " <gristvalue2>0 </gristvalue2>";
	}
      }
    }
    if ($itemfound == True && $canafford == True) { //Player successfully creates item.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['alchcode'] . "'");
      while ($itemrow = mysql_fetch_array($itemresult)) {
	if ($itemrow['captchalogue_code'] == $_POST['alchcode']) {
	  $n = 0; //0 instead of 1 so that it'll give a failure return and print the standard "no room" code if there's no room for even 1 of them
	  while ($n < $numberalched) { //earlier code should prevent making the item if not enough space for it
	    $itemslot = addItem($itemrow['name'],$username); //We need to use this result later.
	    $n++;
	  }
	  if ($itemslot != "inv-1") { //Give them the item and check to see if they got it. inv-1 is the failure return.
	    $itemname = $itemrow['name'];
	    $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	    $alchitem = $itemname;
	    require_once("includes/SQLconnect.php"); //Reconnection appears necessary due to addItem making its own little connection.
	    $reachgrist = False;
	    $terminateloop = False;
	    $colresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = 'Perfectly Generic Object' LIMIT 1;");
	    $costquery = "UPDATE `Players` SET ";
	    while (($col = mysql_fetch_field($colresult)) && $terminateloop == False) {
	      $gristcost = $col->name;
	      $gristtype = substr($gristcost, 0, -5); //Remove "_cost"
	      if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
		$reachgrist = True;
	      }
	      if ($gristcost == "End_of_Grists") { //Reached the end of the grists.
		$reachgrist = False;
		$terminateloop = True;
	      }
	      $actualcost = 0;
	      if ($reachgrist == True) {
	      	$actualcost = $itemrow[$gristcost] * $numberalched;
		if ($actualcost != 0) $costquery = $costquery . "`$gristtype` = $userrow[$gristtype]-$actualcost, ";
	      }
	    }
	    $costquery = substr($costquery, 0, -2); //Dispose of last comma and space.
	    $costquery = $costquery . " WHERE `Players`.`username` = '$username' LIMIT 1 ;";
	    mysql_query($costquery); //Pay.
	    $itemname = $itemrow['name'];
	    $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	    if ($numberalched == 1) {
	      echo "</br>You successfully create the $itemname!</br>";
	      } else {
	      if ($notenoughspots) {
	        echo "</br>You create $itemname x $numberalched before your Sylladex fills up completely.</br>";
		} else {
		echo "</br>You successfully create $itemname x $numberalched!</br>";
		}
	      }
	  } else {
	    $con = mysql_connect("localhost","theovers_DC","pi31415926535"); //Reconnection appears necessary due to addItem making its own little connection.
	    if (!$con)
	      {
		echo "Connection failed.\n";
		die('Could not connect: ' . mysql_error());
	      }
	    mysql_select_db("theovers_HS", $con);
	    echo "</br>You have no room in your Sylladex for this item!</br>"; //May change this to do weird things. But probably not.
	  }
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
    if ($itemfound == False) echo 'The alchemiter informs you that the code you have inputted refers to an item that does not exist yet. <a href="feedback.php">Suggest this item!</a></br>';
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
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
    $nothing = True;
    $success = False; //this is needed in case the item is a ghost item
    while ($itemrow = mysql_fetch_array($itemresult)) {
      $itemname = $itemrow['name'];
      $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
      if ($itemname == $userrow[$_POST[$invstring]]) {
	$recycled[$invstring] = true; //For use later.
	if (strrpos($itemrow['abstratus'], "computer") && $userrow['hascomputer'] == 1) { //Check to see if this was the last computer that the player had
	  $check = 1;
	  $nocomputer = True;
	  while ($check <= 50 && $nocomputer) {
	    if ($userrow['inv' . strval($check)] != "") {
	      $icheckname = str_replace("'", "\\\\''", $userrow['inv' . strval($check)]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded. <-- this message will never get old
	      $icheckresult = mysql_query("SELECT `name`,`abstratus` FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $icheckname . "'");
	      while ($icheckrow = mysql_fetch_array($icheckresult)) {
	        if (strrpos($icheckrow['abstratus'], "computer")) $nocomputer = False;
		}
	      }
	    $check++;
	    }
	  if ($nocomputer) mysql_query("UPDATE `Players` SET `hascomputer` = 0 WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //mark the player as not having computer access
	  }
	$success = True;
	echo "You recycle your $itemname into ";
	$colresult = mysql_query("SELECT * FROM Captchalogue LIMIT 1;");
	mysql_query("UPDATE `Players` SET `" . $_POST[$invstring] . "` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	if ($userrow['equipped'] == $_POST[$invstring]) { //Item is equipped in the main hand.
	  mysql_query("UPDATE `Players` SET `equipped` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	}
	if ($userrow['offhand'] == $_POST[$invstring]) { //Item is equipped in the offhand.
	  mysql_query("UPDATE `Players` SET `offhand` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	}
	if ($userrow['headgear'] == $_POST[$invstring]) { //Item is worn on the head.
	  mysql_query("UPDATE `Players` SET `headgear` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	}
	if ($userrow['facegear'] == $_POST[$invstring]) { //Item is worn on the face.
	  mysql_query("UPDATE `Players` SET `facegear` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	}
	if ($userrow['bodygear'] == $_POST[$invstring]) { //Item is worn on the body.
	  mysql_query("UPDATE `Players` SET `bodygear` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	}
	if ($userrow['accessory'] == $_POST[$invstring]) { //Item is equipped in the accessory slot.
	  mysql_query("UPDATE `Players` SET `accesory` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	}
	$reachgrist = False;
	$terminateloop = False;
	$refundquery = "UPDATE `Players` SET ";
	while (($col = mysql_fetch_field($colresult)) && $terminateloop == False) {
	  $gristcost = $col->name;
	  $gristtype = substr($gristcost, 0, -5);
	  if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
	    $reachgrist = True;
	  }
	  if ($gristcost == "End_of_Grists") { //Reached the end of the grists.
	    $reachgrist = False;
	    $terminateloop = True;
	  }
	  if ($reachgrist == True && $itemrow[$gristcost] != 0) { //Item requires some of this grist. Or produces some. Either way.
	    $nothing = False; //Item costs something.
	    $refundquery = $refundquery . "`$gristtype` = $userrow[$gristtype]+$itemrow[$gristcost], ";
	    $userrow[$gristtype] += $itemrow[$gristcost];
	    if ($gristtype == "Opal" || $gristtype == "Polychromite" || $gristtype == "Rainbow") { //Special cases for animated grists.
	      echo '<img src="Images/Grist/' . $gristtype . '.gif" height="50" width="50" title="' . $gristtype . '"></img>';
	      echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	    } else {
	      echo '<img src="Images/Grist/' . $gristtype . '.png" height="50" width="50" title="' . $gristtype . '"></img>';
	      echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	    }
	  }
	}
	if ($nothing) { //Item costs nothing! SORD.....
	  echo '<img src="Images/Grist/Build_Grist.png" height="50" width="50" title="Build_Grist"></img>';
	  echo " <gristvalue2>0 </gristvalue2>";
	} else { //Item costed something, use the refund query to restore grist.
	  $refundquery = substr($refundquery, 0, -2); //Dispose of last comma and space.
	  $refundquery = $refundquery . " WHERE `Players`.`username` = '$username' LIMIT 1 ;";
	  mysql_query($refundquery); //Un-pay.
	}
      }
    }
    if ($success == False) {
      mysql_query("UPDATE `Players` SET `" . $_POST[$invstring] . "` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
      echo "It seems that the item you tried to recycle no longer exists, or never existed to begin with. You get no grist, but the item has been removed from your inventory, freeing the slot. If you alchemized that item legitimately, please submit a bug report and we'll return your grist ASAP!";
      }
    echo "</br>";
  		}
    $currentrecycle++;
  	}
  }

  //--End Grist Recycler code here.--

  if (empty($recycled)) $recycled = "";
  if (empty($alchitem)) $alchitem = "";
  if (empty($itemslot)) $itemslot = "inv-1";
  echo "Remote Punch Designix access v0.0.1a. Insert two codes and four Build Grist to continue.";
  echo '<form action="inventory.php" method="post">First code: <input id="code1" name="code1" type="text" /><br />';
  echo 'Second code: <input id="code2" name="code2" type="text" /><br />';
  echo 'Combination to use: <select name="combine"><option value="or">||</option><option value="and">&&</option></select></br>';
  echo '<input type="submit" value="Design it!" /></form></br>';
  echo "Remote Punch Designix access with Captchalogue Scanner v0.0.1a. Insert two captchalogue cards and four Build Grist to continue.";
  echo '<form action="inventory.php" method="post">First item:<select name="code1">';
  $reachinv = false;
  $terminateloop = False;
  $invresult = mysql_query("SELECT * FROM Players LIMIT 1;");
  while (($col = mysql_fetch_field($invresult)) && $terminateloop == False) {
    $invslot = $col->name;
    if ($invslot == "inv1") { //Reached the start of the inventory.
      $reachinv = True;
    }
    if ($invslot =="abstratus1") { //Reached the end of the inventory.
      $reachinv = False;
      $terminateloop = True;
    }
    if ($reachinv == True && $userrow[$invslot] != "" && $invslot != $recycled) { //This is a non-empty inventory slot that wasn't just recycled away.
      $itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($itemrow = mysql_fetch_array($itemresult)) {
	$itemname = $itemrow['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$invslot]) $captchaloguecode = $itemrow['captchalogue_code'];
      }
      echo '<option value = "' . $captchaloguecode . '">' . $userrow[$invslot] . '</option>'; //Add option to use this slot for alchemy.
    }
  }
  if ($itemslot != "inv-1" && !empty($itemslot)) { //Player alchemized an item.
    $itemname = str_replace("'", "\\\\''", $alchitem); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
    while ($itemrow = mysql_fetch_array($itemresult)) {
      if ($itemrow['name'] == $alchitem) $captchaloguecode = $itemrow['captchalogue_code'];
    }
    echo '<option value = "' . $captchaloguecode . '">' . $alchitem . '</option>';
  }
  echo '</select><br />Second item:<select name="code2">';
  $reachinv = false;
  $terminateloop = False;
  $invresult = mysql_query("SELECT * FROM Players LIMIT 1;");
  while (($col = mysql_fetch_field($invresult)) && $terminateloop == False) {
    $invslot = $col->name;
    if ($invslot == "inv1") { //Reached the start of the inventory.
      $reachinv = True;
    }
    if ($invslot == "abstratus1") { //Reached the end of the inventory.
      $reachinv = False;
      $terminateloop = True;
    }
    if ($reachinv == True && $userrow[$invslot] != "" && $invslot != $recycled) { //This is a non-empty inventory slot that wasn't just recycled away.
      $itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($itemrow = mysql_fetch_array($itemresult)) {
	$itemname = $itemrow['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$invslot]) $captchaloguecode = $itemrow['captchalogue_code'];
      }
      echo '<option value = "' . $captchaloguecode . '">' . $userrow[$invslot] . '</option>'; //Add option to use this slot for alchemy.
    }
  }
  if ($itemslot != "inv-1" && !empty($itemslot)) { //Player alchemized an item.
    $itemname = str_replace("'", "\\\\''", $alchitem); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
    while ($itemrow = mysql_fetch_array($itemresult)) {
      if ($itemrow['name'] == $alchitem) $captchaloguecode = $itemrow['captchalogue_code'];
    }
    echo '<option value = "' . $captchaloguecode . '">' . $alchitem . '</option>';
  }
  echo '</select><br />';
  echo 'Combination to use: <select name="combine"><option value="or">||</option><option value="and">&&</option></select></br>';
  echo '<input type="submit" value="Design it!" /></form></br>';
  echo '</br>';
  echo "Remote holopad access v0.0.1a. Insert code to preview.";
  echo '<form action="inventory.php" method="get">Captchalogue code: <input id="holocode" name="holocode" type="text" /><br />';
  echo '<input type="submit" value="Observe it!" /></form></br>';
  echo '</br>';
  echo "Alchemiter v0.0.1a. Insert code to synthesize.";
  echo '<form action="inventory.php" method="post">Captchalogue code: <input id="alchcode" name="alchcode" type="text" /><br />Make this many (blank for 1): <input id="alchnum" name="alchnum" type="text" />';
  echo '<input type="submit" value="Create it!" /></form></br>';
  echo '</br>';
  echo "Grist Recycler v0.0.1a. Please select a captchalogued item.</br>";
  echo "Please refresh the interface before attempting to recycle newly alchemized items.";
  echo '<form action="inventory.php" method="post"><select name="recycle">';
  $reachinv = false;
  $terminateloop = False;
  $invresult = mysql_query("SELECT * FROM Players LIMIT 1;");
  while (($col = mysql_fetch_field($invresult)) && $terminateloop == False) {
    $invslot = $col->name;
    if ($invslot == "inv1") { //Reached the start of the inventory.
      $reachinv = True;
    }
    if ($invslot == "abstratus1") { //Reached the end of the inventory.
      $reachinv = False;
      $terminateloop = True;
    }
    if ($reachinv == True && $userrow[$invslot] != "" && !$recycled[$invslot]) { //This is a non-empty inventory slot that wasn't just recycled away.
      echo '<option value = "' . $invslot . '">' . $userrow[$invslot] . '</option>'; //Add option to recycle this slot.
    }
  }
  if ($itemslot != "inv-1" && !empty($itemslot)) { //Player alchemized an item.
    echo '<option value = "' . $itemslot . '">' . $alchitem . '</option>';
  }
  echo '</select> <input type="submit" value="Recycle it!" /> </form>';
  echo '</br>Use the checkboxes below to choose items to mass-recycle.</br>';

  //--Begin displaying user inventory here.--

  echo $username;
  echo "'s inventory:</br></br>";
  echo '<form action="inventory.php" method="post">';
  $reachinv = false;
  $terminateloop = False;
  $invresult = mysql_query("SELECT * FROM Players LIMIT 1;");
  while (($col = mysql_fetch_field($invresult)) && $terminateloop == False) {
    $invslot = $col->name;
    if ($invslot == "inv1") { //Reached the start of the inventory.
      $reachinv = True;
    }
    if ($invslot == "abstratus1") { //Reached the end of the inventory.
      $reachinv = False;
      $terminateloop = True;
    }
    if ($reachinv == True && $userrow[$invslot] != "" && !$recycled[$invslot]) { //This is a non-empty inventory slot that wasn't just recycled away.
      echo "Item: $userrow[$invslot]</br>";
      $itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $captchalogue = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($row = mysql_fetch_array($captchalogue)) {
	$itemname = $row['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemname == $userrow[$invslot]) { //Item found in captchalogue database. Print out details.
	  if ($row['art'] != "") {
	    echo '<img src="Images/Items/' . $row['art'] . '" title="Image by ' . $row['credit'] . '"></br>';
	  }
	  echo "Code: $row[captchalogue_code]</br>";
	  echo "Description: $row[description]</br>";
	  echo '<input type="checkbox" name="' . $invslot . '" value="' . $invslot . '"> Recycle this</br></br>';
	}
      }
    }
  }
  if ($itemslot != "inv-1" && !empty($itemslot)) { //Player alchemized an item.
    echo "Item: $alchitem</br>";
    $itemname = str_replace("'", "\\\\''", $alchitem); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
    $captchalogue = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
    while ($row = mysql_fetch_array($captchalogue)) {
      $n = 1;
      while ($n <= $numberalched) {
      if ($row['name'] == $alchitem) { //Item found in captchalogue database. Print out details.
	if ($row['art'] != "") {
	  echo '<img src="images/Items/' . $row['art'] . '" title="Image by ' . $row['credit'] . '"></br>';
	}
	echo "Code: $row[captchalogue_code]</br>";
	echo "Description: $row[description]</br>";
	echo '<input type="checkbox" name="' . $itemslot . '" value="' . $itemslot . '"> Recycle this</br></br>';
      }
      $n++;
      }
    }
  }
  echo '</br><input type="hidden" name="recycle" value="multi"><input type="submit" value="Recycle selected items" /></form>';
}
require_once("footer.php");
?>