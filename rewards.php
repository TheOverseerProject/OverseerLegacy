<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to access this developer tool.</br>";
} else {
  require_once("includes/SQLconnect.php");
  $allowall = true;
  if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods" && !$allowall) {
    echo "Public rewards are now closed.";
  } else {
    if (!empty($_POST['gift'])) {
      if ($userrow['modlevel'] < 10) {
	$_POST['user'] = $username; //get rid of this when public rewards are removed
      }
      $_POST['user'] = mysql_real_escape_string($_POST['user']);
      $_POST['gift'] = mysql_real_escape_string($_POST['gift']);
      $_POST['quantity'] = intval(mysql_real_escape_string($_POST['quantity']));
      $_POST['captcha'] = mysql_real_escape_string($_POST['captcha']);
      if ($_POST['gift'] == "lookup") {
        $targetresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
	$targetrow = mysql_fetch_array($targetresult);
	if ($targetrow['username'] == $_POST['user']) {
	  echo 'Username: ' . $targetrow['username'] . '</br>';	 
	  echo 'Session: ' . $targetrow['session_name']  . '</br>';
	  echo 'Echeladder: ' . $targetrow['Echeladder'] . '</br>';
	  echo 'Equipped Weapon(s): ' . $targetrow[$targetrow['equipped']];
	  if ($targetrow['offhand'] != "2HAND" && $targetrow['offhand'] != "") echo ', ' . $targetrow[$targetrow['offhand']];
	  echo '</br>';
	  echo 'Solo Fraymotif I: ' . $targetrow['solo1'] . '</br>';
	  echo 'Solo Fraymotif II: ' . $targetrow['solo2'] . '</br>';
	  echo 'Solo Fraymotif III: ' . $targetrow['solo3'] . '</br>';
	  echo 'Boondollars: ' . $targetrow['Boondollars'] . '</br>';
	  echo 'Encounters: ' . $targetrow['encounters'] . '</br>';
	  $reachgrist = False;
    	  $result2 = mysql_query("SELECT * FROM Players LIMIT 1;");
    	  while ($col = mysql_fetch_field($result2)) {
      	    $gristtype = $col->name;
      	    if ($gristtype == "Build_Grist") { //Reached the start of the grists.
              $reachgrist = True;
      	      }
      	    if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
              $reachgrist = False;
      	      }
      	    if ($reachgrist == True) {
              echo $gristtype . ': ' . $targetrow[$gristtype] . '</br>';
      	      }
    	    }
	  } else echo 'No player by the username of ' . $_POST['user'] . ' found.</br>';
	} else {
	$targetresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
	$targetrow = mysql_fetch_array($targetresult);
	$quantity = intval($_POST['quantity']);
	if (is_int($quantity) == false || $quantity < 0) {
	  echo "Invalid quantity! Defaulting to 1.";
	  $quantity = 1;	  
	}
	if ($targetrow['username'] == $_POST['user']) {
	  if ($_POST['gift'] == "item") {
	    $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['captcha'] . "' LIMIT 1;");
	    $itemrow = mysql_fetch_array($itemresult);
	    if ($itemrow['captchalogue_code'] == $_POST['captcha']) {
	      $k = 1;
	      $itemsgiven = 0;
	      while ($k <= 50 && $itemsgiven < $quantity) {
		$foundblank = False;
		while ($foundblank == False) {
		  if ($targetrow['inv' . $k] == "") {
		    $foundblank = True;
		  } else {
		    $k++;
		    if ($k > 50) {
		      $foundblank = True;
		    }
		  }
      		}
		if ($k <= 50) {
		  mysql_query("UPDATE Players SET `inv" . strval($k) . "` = '" . $itemrow['name'] . "' WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
		  $itemsgiven++;
		  $k++;
		}
	      }
	      echo $_POST['user'] . " was given " . $itemrow['name'] . " x" . $itemsgiven . "!</br>";
	      $giftstring = $itemrow['name'] . " x" . $itemsgiven;
	    } else echo "That captcha code doesn't appear to belong to any item.</br>";
	  } else {
	    $amount = $quantity;
	    $field = $_POST['gift'];
	    if ($field == "encounters" && $amount + $targetrow['encounters'] > 100) $amount = 100 - $targetrow['encounters'];
	    if ($field == "Echeladder" && $amount + $targetrow['Echeladder'] > 612) $amount = 612 - $targetrow['Echeladder'];
	    if ($field == "Echeladder" && $amount + $targetrow['Echeladder'] < 1) $amount = 1 - $targetrow['Echeladder'];
	    if ($field == "allgrists") {
	      $reachgrist = False;
	      $result2 = mysql_query("SELECT * FROM Players LIMIT 1;");
	      while ($col = mysql_fetch_field($result2)) {
		$gristtype = $col->name;
		if ($gristtype == "Build_Grist") { //Reached the start of the grists.
		  $reachgrist = True;
		}
		if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
		  $reachgrist = False;
		}
		if ($reachgrist == True) {
		  mysql_query("UPDATE Players SET `$gristtype` = " . strval($targetrow[$gristtype]+$amount) . " WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
		}
	      }
	      echo $_POST['user'] . " was given " . strval($amount) . " of all grist types!</br>";
	      $giftstring = strval($amount) . " of all grist types";
	    } else {
	      mysql_query("UPDATE Players SET `$field` = " . strval($targetrow[$field]+$amount) . " WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
	      if ($field == "Echeladder") {
		echo $_POST['user'] . " was given " . strval($amount) . " Echeladder rungs!</br>";
		$giftstring = strval($amount) . " Echeladder rungs";
		$currentrung = $targetrow[$field]+$amount;
		$gelviscosity = 1;
		if ($currentrung == 1) {
		  $gelviscosity = 10;
		} else if ($currentrung == 2) {
		  $gelviscosity = 15;
		} else if ($currentrung > 2 && $currentrung < 6) {
		  $gelviscosity = $currentrung*10-5;
		} else {
		  $gelviscosity = $currentrung*15-30;
		}
		mysql_query("UPDATE Players SET `Gel_Viscosity` = " . strval($gelviscosity) . ", `Health_Vial` = LEAST(" . strval($gelviscosity) . "," . strval($targetrow['Health_Vial']) . "), `Dream_Health_Vial` = LEAST(" . strval($gelviscosity) . "," . strval($targetrow['Dream_Health_Vial']) . "), `Aspect_Vial` = LEAST(" . strval($gelviscosity) . "," . strval($targetrow['Aspect_Vial']) . ") WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
	      } else {
		echo $_POST['user'] . " was given " . strval($amount) . " " . $field . "!</br>";
		$giftstring = strval($amount) . " " . $field;
	      }
	    }
	    }
	  if (!empty($_POST['body'])) {
	    $sendresult = mysql_query("SELECT * FROM `Messages` WHERE `Messages`.`username` = '" . $sendto[$rcount] . "' LIMIT 1;");
	    $sendrow = mysql_fetch_array($sendresult);
	    $check = 1;
	    $max_inbox = 50;	
	    $foundempty = False;
            while ($check <= $max_inbox && $foundempty == False) { //make sure there's a free spot in recipient's inbox
              if ($sendrow['msg' . strval($check)] == "") $foundempty = True;
              if ($foundempty == False) $check++;
              }
	    if ($foundempty) {
              $sendstring = "<i>" . $username . "</i>" . "|The devs have gifted you " . $giftstring . "!|" . $_POST['body'];
	      $sendstring = str_replace("'", "''", $sendstring); //god dang these apostrophes
	      mysql_query("UPDATE `Messages` SET `msg" . strval($check) . "` = '" . $sendstring . "' WHERE `username` = '" . $sendrow['username'] . "' LIMIT 1;");
	      mysql_query("UPDATE `Players` SET `Players`.`newmessage` = 1 WHERE `Players`.`username` = '" . $sendrow['username'] . "' LIMIT 1;");
	      } else echo "Attempted to send a message, but the user's inbox was full.</br>";
	    }
	  } else echo 'No player by the username of ' . $_POST['user'] . ' found.</br>';
	}
      }	
    echo '<form action="rewards.php" method="post" id="reward">';
    if ($userrow['modlevel'] >= 10) {
      echo 'Name of recipient: <input id="user" name="user" type="text" /></br>';
    }
    echo 'What to gift: <select name="gift">';
    echo '<option value="lookup">No reward, just look up info</option>';
    echo '<option value="Boondollars">Boondollars</option>';
    echo '<option value="encounters">Encounters</option>';
    echo '<option value="Echeladder">Echeladder rungs</option>';
    echo '<option value="abstrati">Strife abstrati</option>';
    $reachgrist = False;
    $result2 = mysql_query("SELECT * FROM Players LIMIT 1;");
    while ($col = mysql_fetch_field($result2)) {
      $gristtype = $col->name;
      if ($gristtype == "Build_Grist") { //Reached the start of the grists.
        $reachgrist = True;
      }
      if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
        $reachgrist = False;
      }
      if ($reachgrist == True) {
        echo '<option value="' . $gristtype . '">' . $gristtype . '</option>'; //Produce an option in the dropdown menu for this grist.
      }
    }
    echo '<option value="allgrists">All grists</option>';
    echo '<option value="item">Item</option>';
    echo '</select></br>';
    echo '<form action="rewards.php" method="post">Quantity of reward: <input id="quantity" name="quantity" type="text" /></br>';
    echo '<form action="rewards.php" method="post">Captcha code of item (leave blank if other reward type): <input id="captcha" name="captcha" type="text" /></br>';
    echo 'Attach a message (optional):</br><textarea name="body" rows="6" cols="40" form="reward"></textarea></br>';
    echo '<input type="submit" value="Give reward!" /></form>';
  }
}
require_once("footer.php");
?>