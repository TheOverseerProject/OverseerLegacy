<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to view your messages.</br>";
} else {
 
  $max_inbox = 50; //could maybe afford to increase  

$compugood = true;
  if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
  	if ($userrow['hascomputer'] < 3) {
  		if ($compugood == true) echo "You don't have a hands-free computer equipped, so you can't send or view messages during strife.</br>";
  		$compugood = false;
  	}
  }
  if ($userrow['indungeon'] != 0 && $userrow['hascomputer'] < 2) {
  	if ($compugood == true) echo "You don't have a portable computer in your inventory, so you can't send or view messages while away from home.</br>";
  	$compugood = false;
  }
  if ($userrow['hascomputer'] == 0) {
  	if ($compugood == true) echo "You need a computer in storage or your inventory to send and view messages.</br>";
  	$compugood = false;
  }

  if ($compugood) {
  $msgresult = mysql_query("SELECT * FROM `Messages` WHERE `Messages`.`username` = '" . $username . "' LIMIT 1;");
  $msgrow = mysql_fetch_array($msgresult);
  
  if (!empty($_POST['msgfix'])) {
    $counter = 1;
    $unreads = 0;
    while ($counter <= 50) {
    	$msgstring = $msgrow['msg' . strval($counter)];
    	$boom = explode("|",$msgstring);
    	if (empty($boom[3]) && !empty($msgstring)) $unreads++;
    	$counter++;
    }
    mysql_query("UPDATE `Players` SET `newmessage` = $unreads WHERE `Players`.`username` = '$username' LIMIT 1;");
    echo "Your unread message count has been refreshed. If you could send a bug report detailing the actions you took to make the count reflect your unreads improperly, it would be much appreciated!</br>";
  }

  //first thing's first, view the message if the player clicked on one
  if (!empty($_GET['view'])) {
    $msgstring = $msgrow['msg' . strval($_GET['view'])];
    if ($msgstring != "") {
    	//echo $msgstring;
      $boom = explode("|",$msgstring);
      echo 'FROM: ' . $boom[0] . '</br>';
      $fixsubject = str_replace("THIS IS A LINE", "|", $boom[1]);
      echo 'SUBJECT: ' . $fixsubject . '</br>';
      $fixbody = str_replace("THIS IS A LINE", "|", $boom[2]);
      echo $fixbody;
      echo '</br></br>';
      if (empty($boom[3])) {
	$msgstring = str_replace("\\", "", $msgstring); //god dang these apostrophes
	$msgstring = mysql_real_escape_string($msgstring);
        mysql_query("UPDATE `Messages` SET `msg" . strval($_GET['view']) . "` = '" . $msgstring . "|READ' WHERE `username` = '" . $username . "' LIMIT 1;");
        $userrow['newmessage']--;
        mysql_query("UPDATE `Players` SET `newmessage` = " . strval($userrow['newmessage']) . " WHERE `Players`.`username` = '$username' LIMIT 1;");
	$marked[intval($_GET['view'])] = True;
	}
	if ($boom[0] != "Submissions" && $boom[0] != "Gristwire" && $boom[0] != "Porkhollow") {
		$boom[0] = str_replace("<i>", "", $boom[0]);
		$boom[0] = str_replace("</i>", "", $boom[0]);
	  echo 'Quick reply:</br>';
  	echo '<form action="messages.php" method="post" id="qreply">';
  	echo 'To: ' . $boom[0] . '<input id="to" name="to" type="hidden" value="' . $boom[0] . '" /></br>';
  	if (strpos($boom[1], "Re: ") === false) //to stop the infinite Re: spam on replies
  	echo 'Subject: <input id="subject" name="subject" type="text" value="Re: ' . $boom[1] . '" /></br>';
  	else
  	echo 'Subject: <input id="subject" name="subject" type="text" value="' . $boom[1] . '" /></br>';
  	echo 'Message:</br><textarea name="body" rows="6" cols="40" form="qreply"></textarea></br>';
  	echo '<input type="submit" value="Send it!" /></form>';
		}
      } else echo 'Message not found.</br>';
    }

  //here is where we will do stuff to selected messages
  if (!empty($_POST['action'])) {
    if ($_POST['action'] == "markasread") { //mark the messages as read
      $check = 1;
      while ($check <= $max_inbox) {
        if (!empty($_POST['select' . strval($check)]) || !empty($_POST['selectall'])) {
	  $marked[$check] = True;
	  $sendstring = str_replace("\\", "", $msgrow['msg' . strval($check)]); //god dang these apostrophes
	  $sendstring = mysql_real_escape_string($sendstring);
	  if (!strpos($msgrow['msg' . strval($check)], "|READ") && !empty($msgrow['msg' . strval($check)])) {
	  	mysql_query("UPDATE `Messages` SET `msg" . strval($check) . "` = '" . $sendstring . "|READ' WHERE `username` = '" . $username . "' LIMIT 1;");
	  	$userrow['newmessage']--;
	  	}
	  }
	$check++;
	}
	if (!empty($_POST['selectall'])) $userrow['newmessage'] = 0;
	mysql_query("UPDATE `Players` SET `newmessage` = " . strval($userrow['newmessage']) . " WHERE `Players`.`username` = '$username' LIMIT 1;");
      echo 'Message(s) marked as read.</br>';
      }
    if ($_POST['action'] == "delete") { //delete these messages
      $check = 1;
      while ($check <= $max_inbox) {
        if (!empty($_POST['select' . strval($check)]) || !empty($_POST['selectall'])) {
	  $deleted[$check] = True;
	  mysql_query("UPDATE `Messages` SET `msg" . strval($check) . "` = '' WHERE `username` = '" . $username . "' LIMIT 1;");
	  if (!strpos($msgrow['msg' . strval($check)], "|READ") && !empty($msgrow['msg' . strval($check)])) {
	  $userrow['newmessage']--;
    }
	  }
	$check++;
	}
	if (!empty($_POST['selectall'])) $userrow['newmessage'] = 0;
	mysql_query("UPDATE `Players` SET `newmessage` = " . strval($userrow['newmessage']) . " WHERE `Players`.`username` = '$username' LIMIT 1;");
      echo 'Message(s) deleted.</br>';
      }
    }

  //lets send a message
  if (!empty($_POST['into'])) $_POST['to'] = $_POST['into'];
  
  if (!empty($_POST['to'])) {
    if (strrpos($_POST['to'], "; ")) { //see if the message is sent to multiple people
      $sendto = explode("; ", $_POST['to']);
      $receivers = count($sendto);
      } else {
      if ($_POST['to'] == "SESSION") { //send to everyone in the user's session
        $sessionresult = mysql_query("SELECT `username` FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "' ;");
	$receivers = 0;
	while ($sesrow = mysql_fetch_array($sessionresult)) {
	  if ($sesrow['username'] != $username) { 
	    $sendto[$receivers] = $sesrow['username'];
	    $receivers++;
	    }
	  }
	} else {
        $sendto[0] = $_POST['to'];
        $receivers = 1;
	}
      }
    $rcount = 0;
    while ($rcount < $receivers) {
      $founduser = False;
      $sendresult = mysql_query("SELECT * FROM `Messages` WHERE `Messages`.`username` = '" . $sendto[$rcount] . "' LIMIT 1;");
      while ($sendrow = mysql_fetch_array($sendresult)) {
        if ($sendrow['username'] == $sendto[$rcount]) {
          $check = 1;
	  $founduser = True;
          $foundempty = False;
          while ($check <= $max_inbox && $foundempty == False) { //make sure there's a free spot in recipient's inbox
            if ($sendrow['msg' . strval($check)] == "") $foundempty = True;
            if ($foundempty == False) $check++;
            }
          if ($foundempty) {
          	//echo $_POST['body'] . "</br>";
	    $realbody = $_POST['body']; //start cleaning up body, remove HTML and do the liney thing
	    $realbody = str_replace("<", "", $realbody);
	    $realbody = str_replace(">", "", $realbody);
	    $realbody = str_replace("|", "THIS IS A LINE", $realbody);
	    //echo $realbody . "</br>";
	    if (empty($_POST['subject'])) $_POST['subject'] = "(no subject)";
	    $realsubject = $_POST['subject'];
	    $realsubject = str_replace("<", "", $realsubject);
	    $realsubject = str_replace(">", "", $realsubject);
	    $realsubject = str_replace("|", "THIS IS A LINE", $realsubject);
	    if ($userrow['session_name'] == "Developers") {
	      $youstring = "<i>" . $username . "</i>";
	      } else {
	      $youstring = $username;
	      }
	      if (empty($userrow['colour'])) $userrow['colour'] = "Black";
            $sendstring = $youstring . '|' . $realsubject . '|<font color="' . $userrow['colour'] . '">' . $realbody . '</font>';
	    $sendstring = str_replace("\\", "", $sendstring); //god dang these apostrophes
	    $sendstring = mysql_real_escape_string($sendstring);
	    //echo "UPDATE `Messages` SET `msg" . strval($check) . "` = '" . $sendstring . "' WHERE `username` = '" . $sendrow['username'] . "' LIMIT 1;</br>";
            mysql_query("UPDATE `Messages` SET `msg" . strval($check) . "` = '" . $sendstring . "' WHERE `username` = '" . $sendrow['username'] . "' LIMIT 1;");
	    echo "Message sent to " . $sendto[$rcount] . " successfully.</br>";
	    mysql_query("UPDATE `Players` SET `Players`.`newmessage` = `newmessage` + 1 WHERE `Players`.`username` = '" . $sendrow['username'] . "' LIMIT 1;");
            } else echo $sendto[$rcount] . " did not receive message: inbox full.</br>";
          }
        }
      if ($founduser == False) echo $sendto[$rcount] . " did not receive message: user not found.</br>";
      $rcount++;
      }
    }
    
    //we'll count messages here and tell the user if their inbox is full
  $k = 1;
  $msgs = 0;
  while ($k <= $max_inbox) {
  	if ($msgrow['msg' . strval($k)] != '' && empty($deleted[$k])) {
  		$msgs++;
  	}
	$k++;
  }
  echo 'Messages stored: ' . strval($msgs) . ' / ' . strval($max_inbox) . '<br />';
  if ($msgs == $max_inbox) {
  	echo '<b>Your inbox is full! You will be unable to receive messages until you make some room.</b><br />';
  }

  //let's generate that message table~
  echo '<form action="messages.php" method="post">';
  echo '<table border="1" bordercolor="#CCCCCC" style="background-color:#EEEEEE" width="100%" cellpadding="3" cellspacing="3">';
  echo '<tr><td>Select</td><td>From</td><td>Subject</td></tr>';
  $k = 1;
  while ($k <= $max_inbox) {
    if ($msgrow['msg' . strval($k)] != '' && empty($deleted[$k])) {
      echo '<tr><td><input type="checkbox" name="select' . strval($k) . '" value="select' . strval($k) . '"></td>';
      $msgstring = $msgrow['msg' . strval($k)];
      $boom = explode("|",$msgstring);
      echo '<td>' . $boom[0] . '</td><td>';
      $fixsubject = str_replace("THIS IS A LINE", "|", $boom[1]);
      if (empty($boom[3]) && empty($marked[$k])) echo '<b>';
      echo '<a href="messages.php?view=' . strval($k) . '">' . $fixsubject . '</a>';
      if (empty($boom[3]) && empty($marked[$k])) echo '</b>';
      echo '</td></tr>';
      }
    $k++;
    }
  echo '</table></br>Selected messages: <select name="action"><option value="nothing"></option><option value="markasread">Mark as Read</option><option value="delete">Delete</option></select><input type="submit" value="Do it!" /></form>';
  echo '<form action="messages.php" method="post"><input type="hidden" name="selectall" value="selectall">Perform an action on ALL messages: <select name="action"><option value="nothing"></option><option value="markasread">Mark as Read</option><option value="delete">Delete</option></select><input type="submit" value="Do it!" /></form></br>';

  //here's the send new message form
  echo 'Send a new message:</br>';
  echo '<form action="messages.php" method="post" id="newmsg">Send to someone in your session: <select name="into"><option value=""></option>';
  $yoursessionresult = mysql_query("SELECT `username` FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
  while ($ysessionrow = mysql_fetch_array($yoursessionresult)) {
  	if ($ysessionrow['username'] != $username) echo '<option value="' . $ysessionrow['username'] . '">' . $ysessionrow['username'] . '</option>';
  }
  echo '</select></br>To: <input id="to" name="to" type="text" /> (Separate multiple users with a semicolon followed by a space. Type SESSION to send to everyone in your session.)</br>';
  echo 'Subject: <input id="subject" name="subject" type="text" /></br>';
  echo 'Message:</br><textarea name="body" rows="6" cols="40" form="newmsg"></textarea></br>';
  echo '<input type="submit" value="Send it!" /></form>';
  }
  echo '</br><form action="messages.php" method="post"><input type="hidden" name="msgfix" value="yes"><input type="submit" value="Click here if your message count on the header is wrong"></br>(a temporary solution, hopefully we can find out what the issue is soon)</form>';
}
require_once("footer.php");
?>