<?php
require_once("header.php");

function updateSubmission($subid) {
	$currenttime = time();
	mysql_query("UPDATE `Feedback` SET `lastupdated` = $currenttime WHERE `Feedback`.`ID` = $subid ;");
	$feedrow['lastupdated'] = $currenttime;
}

if (empty($_SESSION['username'])) {
  echo "Log in to view item submissions.</br>";
} else {
 require_once("includes/SQLconnect.php");
echo "<!DOCTYPE html><html><head><style>itemcode{font-family:'Courier New'}</style><style>normal{color: #111111;}</style><style>urgent{color: #0000CC;}</style><style>defunct{color: #CC0000;}</style><style>clarify{color: #CCCC00;}</style><style>greenlit{color: #00AA00;}</style><style>suspended{color: #999999;}</style><style>randomized{color: #EE6606;}</style><style>halp{color: #FFFFFF;}</style></head><body>";
  if (empty($_GET['page'])) {
    $page = 1;
    } else {
    $page = intval($_GET['page']);
    }
    
  if (!empty($_POST['delete'])) {
    $feedresult = mysql_query("SELECT * FROM `Feedback` WHERE `Feedback`.`ID` = '" . strval($_POST['delete']) . "' ;");
    $feedrow = mysql_fetch_array($feedresult);
    if ($feedrow['user'] == $username || $userrow['session_name'] == "Developers") {
      if ($feedrow['type'] == "bug" || $feedrow['type'] == "misc") {
        mysql_query("DELETE FROM `Feedback` WHERE `Feedback`.`ID` = '" . strval($_POST['delete']) . "' ;");
	echo 'Submission deleted.</br>';
      } else echo "You can't delete non-bug/misc submissions from here.</br>";
    } else echo "You don't have permission to delete that submission. (lol rhyme)</br>";
  }

  //first thing's first, view the submission if the player clicked on one
  if (!empty($_GET['view'])) {
    $feedresult = mysql_query("SELECT * FROM `Feedback` WHERE `Feedback`.`ID` = '" . strval($_GET['view']) . "' ;");
    $feedrow = mysql_fetch_array($feedresult);
    if ($feedrow['ID'] == $_GET['view']) {
      if ($feedrow['type'] == "bug" || $feedrow['type'] == "misc") {
	if (!empty($_POST['body'])) {
	  $realbody = $_POST['body']; //start cleaning up body, remove HTML and do the liney thing
	  $realbody = str_replace("|", "THIS IS A LINE", $realbody);
	  $exstring = ": ";
	  if ($userrow['session_name'] == "Developers") $exstring = " (Developer): ";
	  if ($userrow['session_name'] == "Itemods") $exstring = " (Moderator): ";
	  if ($feedrow['user'] == $username) $exstring = " (Submitter): ";
	  	$msgresult = mysql_query("SELECT * FROM `Messages` WHERE `Messages`.`username` = '" . $feedrow['user'] . "' LIMIT 1;");
  		$msgrow = mysql_fetch_array($msgresult);
  		if ($msgrow['feedbacknotice'] == 1) {
  			$check = 0;
  			$foundempty = false;
  			while ($check < 50 && !$foundempty) {
	  			if (empty($msgrow['msg' . strval($check + 1)])) $foundempty = true;
	  			$check++;
  			}
  			if ($foundempty) {
  				$msgfield = "msg" . strval($check);
  				$newmsgstring = $username . "|";
  				if ($feedrow['type'] == "bug") {
	  				$newmsgstring = $newmsgstring . "Bug Report Response (ID " . strval($feedrow['ID']) . ")|";
	  			} elseif ($feedrow['type'] == "misc") {
	  				$newmsgstring = $newmsgstring . "Feedback Response (ID " . strval($feedrow['ID']) . ")|";
	  			}
	  			$newmsgstring = mysql_real_escape_string($newmsgstring . $realbody . "<br /><br />Original:<br />" . $feedrow['comments']);
	  			mysql_query("UPDATE Messages SET `$msgfield` = '$newmsgstring' WHERE `Messages`.`username` = '" . $feedrow['user'] . "'");
	  			mysql_query("UPDATE Players SET `newmessage` = `newmessage` + 1 WHERE `Players`.`username` = '" . $feedrow['user'] . "'");
	  			echo "Message sent!<br />";
  			} else echo "ERROR: Response could not be sent because this user's inbox is full.<br />";
  		} else echo "ERROR: Response could not be sent because this user has opted out of receiving feedback notices.<br />";
	  //echo $_POST['body'] . "</br>";
	  $newcomments = $feedrow['usercomments'] . $username . $exstring . $realbody . "|";
	  $newncomments = mysql_real_escape_string($newcomments);
	  //echo $newcomments . "</br>";
	  mysql_query("UPDATE `Feedback` SET `usercomments` = '" . $newncomments . "' WHERE `Feedback`.`ID` = '" . strval($_GET['view']) . "' ;");
	  $feedrow['usercomments'] = $newcomments;
	  echo "Your comment has been posted.</br>";
	  updateSubmission($_GET['view']);
	  }
	  $stylestring = "normal";
  	if ($feedrow['type'] == "bug") {
  		$stylestring = "defunct";
  	} elseif ($feedrow['type'] == "misc") {
  		$stylestring = "clarify";
  	}
	$likestring = "+" . strval($feedrow['likes']);
        echo '<' . $stylestring . '>Submission ID: ' . strval($feedrow['ID']) . '</' . $stylestring . '></br>';
        if ($userrow['session_name'] == "Developers" || $userrow['session_name'] == "Itemods") {
        	echo 'Submitted by: ' . $feedrow['user'] . '</br>';
        }
	if ($feedrow['comments'] != "") echo "Submitter's comments: " . $feedrow['comments'] . "</br>";
	echo "</br>";
	if ($feedrow['usercomments'] != "") {
	  echo "Reviewers' comments:</br>";
	  $count = 0;
	  $boom = explode("|", $feedrow['usercomments']);
	  $allmessages = count($boom);
	  while ($count <= $allmessages) {
	    $boom[$count] = str_replace("THIS IS A LINE", "|", $boom[$count]);
	    echo $boom[$count] . "</br>";
	    $count++;
	    }
	  }
	echo produceTimeSinceUpdate($feedrow['lastupdated']);
	echo "</br>";
	echo '<form action="bugsubmissions.php?view=' . strval($feedrow['ID']) . '&page=' . strval($page) . '" method="post" id="usercomment">';
	echo 'Respond to this feedback:</br><textarea name="body" rows="6" cols="40" form="usercomment"></textarea></br>';
	echo '<input type="submit" value="Share your opinion" /></form>';
	if ($feedrow['user'] == $username || $userrow['session_name'] == "Developers") {
	  echo '</br><form action="bugsubmissions.php?page=' . strval($page) . '" method="post"><input type="hidden" name="delete" value="' . strval($feedrow['ID']) . '"><input type="submit" value="Delete this submission"></form></br>';
	}
        } else echo 'The submission with that ID is not a bug/feedback report.</br>';
      } else echo 'No item submission with that ID exists.</br>';
    }
  echo 'Bug/Feedback viewer v0.0.1a. Click on a submission to view/review it.</br>';
  //let's generate that message table~

    $startpoint = strval(($page - 1) * 20);
    $feedresult = mysql_query("SELECT `ID`,`type`,`user`,`comments`,`usercomments` FROM `Feedback` WHERE `Feedback`.`type` = 'bug' OR `Feedback`.`type` = 'misc' ORDER BY `Feedback`.`ID` ASC LIMIT " . $startpoint . ",20 ;");
  echo '<table border="1" bordercolor="#CCCCCC" style="background-color:#EEEEEE" width="100%" cellpadding="3" cellspacing="3">';
  echo '<tr><td>ID</td><td>Issue</td><td>Username</td></tr>';
  $results = false;
  while ($showrow = mysql_fetch_array($feedresult)) {
  	$results = true;
  	$stylestring = "normal";
  	if (!empty($showrow['usercomments'])) {
  		$stylestring = "greenlit";
  	} elseif ($showrow['type'] == "bug") {
  		$stylestring = "defunct";
  	} elseif ($showrow['type'] == "misc") {
  		$stylestring = "clarify";
  	}
  	if (strlen($showrow['comments']) > 100) {
    	  $showrow['comments'] = substr($showrow['comments'], 0, 100) . "..."; //first 100 characters of the item
    	}
    echo '<tr><td><' . $stylestring . '>' . strval($showrow['ID']) . '</' . $stylestring . '></td><td><a href="bugsubmissions.php?view=' . strval($showrow['ID']) . '&page=' . strval($page) . '">' . $showrow['comments'] . '</a></td><td>' . $showrow['user'] . '</td></tr>';
  }
  if (!$results) echo '<tr><td colspan="3">No submissions found. Either this is an invalid page number, or nothing matches those parameters.</td></tr>';
  echo '</table></br>';
  $countresult = mysql_query("SELECT `ID` FROM `Feedback` WHERE `Feedback`.`type` = 'bug' OR `Feedback`.`type` = 'misc'");
  $pcount = 20;
  $ptotal = 0;
  $alltotal = 0;
  echo '<center>Pages:</br>';
  if ($page > 1) {
  	echo '<a href="bugsubmissions.php?page=' . strval($page - 1) . '">Previous page</a> | ';
  } else {
  	echo 'Previous page | ';
  }
  while ($row = mysql_fetch_array($countresult)) {
  	$alltotal++;
  	if ($pcount == 20) {
  		$ptotal++;
  		if ($ptotal == $page) {
  			echo strval($ptotal) . ' | ';
  		} else {
  			echo '<a href="bugsubmissions.php?page=' . strval($ptotal) . '">' . strval($ptotal) . '</a> | ';
  		}
  		$pcount = 0;
  	}
  	$pcount++;
  }
  if ($page < $ptotal) {
    echo '<a href="bugsubmissions.php?page=' . strval($page + 1) . '">Next page</a>';
  } else {
  	echo 'Next page';
  }
  echo "<br />Total results: $alltotal</center>";
}
require_once("footer.php");
?>