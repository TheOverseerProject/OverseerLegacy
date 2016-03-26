<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to access your control panel.</br>";
  echo '<form id="login" action="login.php" method="post"> Username: <input id="username" maxlength="50" name="username" type="text" /><br /> Password: <input id="password" maxlength="50" name="password" type="password" /><br /> <input name="Submit" type="submit" value="Submit" /> </form>';
} else {
 
  echo '<a href="grist.php">Gristwire</a></br>';
  echo '<a href="porkhollow.php">Virtual Porkhollow</a></br>';
  echo '<a href="inventory.php">Inventory and Alchemical Operations</a></br>';
  echo '<a href="consumables.php">Consume Consumables</a></br>';
  echo '<a href="catalogue.php">Captchalogue Catalogue</a></br>';
  echo '<a href="portfolio.php">Strife Portfolio and options</a></br>';
  echo '<a href="echeviewer.php">View your Echeladder</a></br>';
  echo '<a href="strife.php">Strife!</a></br>';
  
  echo '<a href="overview.php">Player and Sprite overview</a></br>';
  echo '<a href="fraymotifs.php">Fraymotifs</a></br>';
  echo '<a href="resets.php">Resetter</a></br>';
  echo '<a href="feedback.php">Suggest an item, submit artwork, report a bug, or comment on the game</a></br>';
  echo '<a href="news.php">Latest News</a></br>';
  echo '<a href="index.php">Site Index</a></br>';
  echo '<a href="sessioninfo.php">Look up session information</a></br>';
  //echo '<a href="events.php">Event Log</a></br>'; -- Will work on event log later.
  echo '<a href="logout.php">Log Out</a></br>';
  
  $sessionmates = mysql_query("SELECT * FROM Players");
  while ($row = mysql_fetch_array($sessionmates)) {
    if ($row['session_name'] == $userrow['session_name']) {
      if (!empty($row['enemy1name']) || !empty($row['enemy2name']) || !empty($row['enemy3name']) || !empty($row['enemy4name']) || !empty($row['enemy5name'])) { //Ally is strifing
	if ($row['username'] != $username) {
	  echo "$row[username] is strifing right now!</br>";
	} else {
	  echo "You are strifing right now!</br>";
	}
      }
    }
  }
}

?>