<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to access the captchalogue list.</br>";
} else {
	require_once("includes/SQLconnect.php");
  if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
    echo "What are you doing here?";
  } else {
    if (!empty($_POST['ftype'])) {
    	$amount = $_POST['amount'];
    	if ($_POST['ftype'] == "item") {
    		$feedbackresult = mysql_query("SELECT * FROM `Feedback` WHERE `Feedback`.`type` = 'item' AND `Feedback`.`greenlight` = 1 ORDER BY `Feedback`.`ID` ASC LIMIT $amount");
    		while ($feedrow = mysql_fetch_array($feedbackresult)) {
	    $feedback = $feedrow['user'] . " - Item suggestion. " . str_replace(":", "THIS IS A COLON", $feedrow['name']) . ": " . $feedrow['description'] . ". Made from: " . $feedrow['recipe'] . " with code " . $feedrow['code'] . " and suggested power level " . strval($feedrow['power']) . ". Additional comments: " . $feedrow['comments'] . " User comments: " . $feedrow['usercomments'] . "; </br>";
	  echo $feedback . "</br>";
	  $feedbackfound = True;
      }
      if ($feedbackfound == False) echo "There is currently no feedback of type " . $_POST['ftype'] . "</br>";
      if (!empty($_POST['delete'])) mysql_query("DELETE FROM `Feedback` WHERE `Feedback`.`type` = 'item' AND `Feedback`.`greenlight` = 1 ORDER BY `Feedback`.`ID` ASC LIMIT $amount");
    	} elseif ($_POST['ftype'] == "bitem") {
    		$feedbackresult = mysql_query("SELECT * FROM `Feedback` WHERE `Feedback`.`type` = 'item' AND `Feedback`.`greenlight` = 1 AND `Feedback`.`urgent` = 1 ORDER BY `Feedback`.`ID` ASC LIMIT $amount");
    		while ($feedrow = mysql_fetch_array($feedbackresult)) {
	    $feedback = $feedrow['user'] . " - Item suggestion. " . str_replace(":", "THIS IS A COLON", $feedrow['name']) . ": " . $feedrow['description'] . ". Made from: " . $feedrow['recipe'] . " with code " . $feedrow['code'] . " and suggested power level " . strval($feedrow['power']) . ". Additional comments: " . $feedrow['comments'] . " User comments: " . $feedrow['usercomments'] . "; </br>";
	  echo $feedback . "</br>";
	  $feedbackfound = True;
      }
      if ($feedbackfound == False) echo "There is currently no feedback of type " . $_POST['ftype'] . "</br>";
      if (!empty($_POST['delete'])) mysql_query("DELETE FROM `Feedback` WHERE `Feedback`.`type` = 'item' AND `Feedback`.`greenlight` = 1 AND `Feedback`.`urgent` = 1 ORDER BY `Feedback`.`ID` ASC LIMIT $amount");
    	} elseif ($_POST['ftype'] == "ritem") {
    		$feedbackresult = mysql_query("SELECT * FROM `Feedback` WHERE `Feedback`.`type` = 'item' AND `Feedback`.`defunct` = 1 ORDER BY `Feedback`.`ID` ASC LIMIT $amount");
    		while ($feedrow = mysql_fetch_array($feedbackresult)) {
	    $feedback = $feedrow['user'] . " - Item suggestion. " . str_replace(":", "THIS IS A COLON", $feedrow['name']) . ": " . $feedrow['description'] . ". Made from: " . $feedrow['recipe'] . " with code " . $feedrow['code'] . " and suggested power level " . strval($feedrow['power']) . ". Additional comments: " . $feedrow['comments'] . " User comments: " . $feedrow['usercomments'] . "; </br>";
	  echo $feedback . "</br>";
	  $feedbackfound = True;
      }
      if ($feedbackfound == False) echo "There is currently no feedback of type " . $_POST['ftype'] . "</br>";
      if (!empty($_POST['delete'])) mysql_query("DELETE FROM `Feedback` WHERE `Feedback`.`type` = 'item' AND `Feedback`.`defunct` = 1 ORDER BY `Feedback`.`ID` ASC LIMIT $amount");
    	} else {
    	$feedbackfound = False;
      $feedbackresult = mysql_query("SELECT * FROM `Feedback` WHERE `Feedback`.`type` = '" . $_POST['ftype'] . "'  LIMIT $amount");
      while ($feedbackrow = mysql_fetch_array($feedbackresult)) {
		if ($_POST['ftype'] == "art") {
	    $feedback = $feedbackrow['user'] . " - Art submission. " . $feedbackrow['name'] . " Link to art: " . $feedbackrow['description'] . " " . $feedbackrow['comments'];
		} elseif ($_POST['ftype'] == "ques") {
			$feedback = $feedbackrow['user'] . " - Consort quest. Prompt: " . $feedbackrow['description'] . " Requires: " . $feedbackrow['comments'];
			if (!empty($feedbackrow['recipe'])) $feedback .= " Reward: " . $feedbackrow['recipe'];
			else $feedback .= " Reward: Default";
	  } else {
	    $feedback = $feedbackrow['ID'] . ": " . $feedbackrow['user'] . " - " . $feedbackrow['comments'];
	  }
	  echo $feedback . "</br></br>";
	  $feedbackfound = True;
      }
      if ($feedbackfound == False) echo "There is currently no feedback of type " . $_POST['ftype'] . "</br>";
      if (!empty($_POST['delete'])) mysql_query("DELETE FROM `Feedback` WHERE `Feedback`.`type` = '" . $_POST['ftype'] . "'  LIMIT $amount");
    	}
    }
    $usercount = 0;
    $greenlight = 0;
    $gchallenge = 0;
    $yellowlight = 0;
    $redlight = 0;
    $graylight = 0;
    $unmarked = 0;
    $inactive = 0;
    $counterresult = mysql_query("SELECT `ID`,`type`,`greenlight`,`urgent`,`clarify`,`defunct`,`suspended`,`lastupdated` FROM `Feedback` ;");
    while ($tempfeed = mysql_fetch_array($counterresult)) {
      $usercount++;
			$count[$tempfeed['type']]++;
			if ($tempfeed['type'] == "item") {
				if ($tempfeed['greenlight'] == 1) {
					$greenlight++;
					if ($tempfeed['urgent'] == 1) $gchallenge++;
				} elseif ($tempfeed['clarify'] == 1) {
					$yellowlight++;
					if (time() - $tempfeed['lastupdated'] > 604800) $inactive++;
				} elseif ($tempfeed['defunct'] == 1) {
					$redlight++;
				} elseif ($tempfeed['suspended'] == 1) {
					$graylight++;
				} else {
					$unmarked++;
				}
			}
    }
    echo "There are currently " . $usercount . " feedback entries in total.</br>";
    echo "Item suggestions: " . $count['item'] . "</br>";
    echo "- Greenlit items: $greenlight (Challenge: $gchallenge)</br>"; 
    echo "- Yellow items: $yellowlight (Inactive: $inactive)</br>";
    echo "- Red items: $redlight</br>";
    echo "- Suspended items: $graylight</br>";
    echo "- Unmarked items: $unmarked</br>";
    echo "Art submissions: " . $count['art'] . "</br>";
    echo "Bug reports: " . $count['bug'] . "</br>";
    echo "Misc feedback: " . $count['misc'] . "</br>";
    echo "Quest ideas: " . $count['ques'] . "</br></br>";
    echo '<form action="feedbackpull.php" method="post">Type of feedback: <select id="ftype" name="ftype"><option value="item">Greenlit Items</option><option value="bitem">Greenlit Challenge Items</option><option value="ritem">Redlit Items (just delete)</option><option value="art">Art Submissions</option><option value="bug">Bug Reports</option><option value="misc">Misc Feedback</option><option value="ques">Quest Ideas</option></select></br>';
    echo 'Maximum amount of entries to grab/delete: <input type="text" name="amount"></br>';
    echo '<input type="checkbox" name="delete" value="delete">Delete feedback after loading</br><input type="submit" value="Pull feedback" /></form>';
  }
}
require_once("footer.php");
?>