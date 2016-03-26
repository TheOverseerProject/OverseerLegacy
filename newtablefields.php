<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to use consumable items.</br>";
  echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} else {
require_once("includes/SQLconnect.php");
  if ($userrow['session_name'] != "Developers") {
    echo "Hey! This tool is for the developers only. Nice try, pal.";
  } else {
		echo "Now adding new fields</br>";
		mysql_query("ALTER TABLE  `Consort_Dialogue` ADD  `gate` SMALLINT NOT NULL DEFAULT  '1' COMMENT  'Minimum house gate required for this quest to appear' AFTER  `context`");
		}
}
?>