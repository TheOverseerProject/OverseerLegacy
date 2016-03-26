<?php
require_once("header.php");

$newsresult = mysql_query("SELECT * FROM News ORDER BY `ID` DESC LIMIT 1"); //the first row we pull here should be the latest news
  while ($row = mysql_fetch_array($newsresult)) {
    if ($row['ID'] > 0) {
      $newestrow = $row; //Fetch latest result.
    }
  }
  
if(!empty($_GET['startpoint'])) {
  $endpoint = $newestrow['ID'] - 9 - $_GET['startpoint'];
  $startpoint = $newestrow['ID'] - $_GET['startpoint'];
} else $_GET['startpoint'] = 0;

if (!empty($_GET['view'])) {
	$viewresult = mysql_query("SELECT * FROM News WHERE `News`.`ID` = " . strval($_GET['view']));
	$vrow = mysql_fetch_array($viewresult);
	if ($vrow['ID'] == $_GET['view']) {
		if (!empty($_POST['body']) && !empty($_SESSION['username'])) {
	  	$realbody = $_POST['body']; //start cleaning up body, remove HTML and do the liney thing
	  	$realbody = str_replace("<", "", $realbody);
	  	$realbody = str_replace(">", "", $realbody);
	  	$realbody = str_replace("|", "THIS IS A LINE", $realbody);
	  	$exstring = ": ";
	  	if ($userrow['session_name'] == "Developers") $exstring = " (Developer): ";
	  	if ($userrow['session_name'] == "Itemods") $exstring = " (Moderator): ";
	  	//echo $_POST['body'] . "</br>";
	  	$newcomments = $vrow['comments'] . $username . $exstring . $realbody . "|";
		  $newncomments = mysql_real_escape_string($newcomments);
	  	//echo $newcomments . "</br>";
	  	mysql_query("UPDATE `News` SET `comments` = '" . $newncomments . "' WHERE `News`.`ID` = '" . strval($_GET['view']) . "' ;");
	  	$vrow['comments'] = $newcomments;
	  	echo "Your comment has been posted.</br>";
	  }
		echo "<b>$vrow[title]</b></br></br>";
    echo "Posted by:<b> $vrow[postedby]</b> at $vrow[date]. (Times should be at GMT +10)</br></br>";
    echo "$vrow[news]</br></br>";
		echo "Comments:</br>";
		if ($vrow['comments'] != "") {
	  	$count = 0;
	  	$boom = explode("|", $vrow['comments']);
	  	$allmessages = count($boom);
	  	while ($count <= $allmessages) {
	    	$boom[$count] = str_replace("THIS IS A LINE", "|", $boom[$count]);
	    	echo $boom[$count] . "</br>";
	    	$count++;
	    }
	  } else echo "No comments have been posted yet.</br>";
	  echo "</br>";
	  if (!empty($_SESSION['username'])) {
	  	echo '<form action="news.php?view=' . strval($vrow['ID']) . '" method="post" id="usercomment">Leave a comment on this post:</br><textarea name="body" rows="6" cols="40" form="usercomment"></textarea></br>';
	  	echo '<input type="submit" value="Post it!"></form></br>';
	  } else {
	  	echo 'Log in to post a comment.</br></br>';
	  }
	  if ($_GET['view'] < $newestrow['ID']) echo '<div style = "float: right;"><a href="news.php?view=' . strval($_GET['view'] + 1) . '">Next ==&gt;</a></div>';
	  if ($_GET['view'] > 1) echo '<a href="news.php?view=' . strval($_GET['view'] - 1) . '">&lt;== Previous</a>';
	}
} else {

echo 'NOTE - News is displayed in order from newest to oldest.</br>Click on "Read more" to view a news post in its entirety and/or post a comment on it.</br><hr></br>';

$newsresult = mysql_query("SELECT * FROM News ORDER BY `ID` DESC");
while ($row = mysql_fetch_array($newsresult)) {
  if (empty($endpoint)) $endpoint = $newestrow['ID'] - 9; //
  if (empty($startpoint)) $startpoint = $newestrow['ID']; //Start from the latest news by default
  if (($startpoint >= $row['ID']) && ($endpoint <= $row['ID'])) {
    echo "<b>$row[title]</b></br></br>";
    echo "Posted by:<b> $row[postedby]</b> at $row[date]. (Times should be at GMT +10)</br></br>";
    $breakingpoint = strpos($row['news'], "<"); //find the first line break (searching for just the < because not every dev uses the same br syntax)
    if ($breakingpoint != 0) $shortnews = substr($row['news'], 0, $breakingpoint) . " [...]"; //truncate the post to the breaking point...
    else $shortnews = $row['news'];
    echo "$shortnews</br></br>"; //echo the shortened news...
    echo '<a href="news.php?view=' . strval($row['ID']) . '">Read more</a></br><hr></br>'; //...and add a "read more" link to show the whole post :L
  }
}
//echo '<form action="news.php" method="post">Look at news stories<input id="build" name="startpoint" type="text" />entries back:<br />';
//echo '<input type="submit" value="Read it!" /></form>';
	  if ($startpoint < $newestrow['ID']) echo '<div style = "float: right;"><a href="news.php?startpoint=' . strval($_GET['startpoint'] - 10) . '">Next 10 posts ==&gt;</a></div>';
	  if ($endpoint > 1) echo '<a href="news.php?startpoint=' . strval($_GET['startpoint'] + 10) . '">&lt;== Previous 10 posts</a>';
}
require_once("footer.php");
?>