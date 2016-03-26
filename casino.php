<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to explore your dreams.</br>";
} elseif ($userrow['dreamingstatus'] == "Awake" && $userrow['Godtier'] == 0 && $userrow['exploration'] != "7thgateout") { //Allow waking players exploring the Denizen palace in.
  echo "You cannot explore dream locations with your waking self until you have ascended to the god tiers.";
} else {
  require_once("includes/SQLconnect.php");
  
	
function store($tokens) {	
	global $userrow;
	mysql_query("UPDATE `Players` SET `tokens` = '" . mysql_real_escape_string($tokens) . "' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
	return $userrow['tokens'];
}

function tokens() { 
	return $userrow['tokens'];
}
	$rand = rand(1,99)*10;
	
	$tokens = $userrow['tokens'];
  	echo "Stored: " . $tokens . "<br />";
	store($rand);
	echo "Storing: " . $rand;
	

}
require_once("footer.php");
?>