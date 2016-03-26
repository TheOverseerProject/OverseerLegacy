<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
	echo "Log in to edit your Pesternote settings.</br>";
} else {
	if (!empty($_POST['username'])) {
		$pesterclash = False;
		$pesterresult = mysql_query("SELECT * FROM Players WHERE `Players`.`pesternoteUsername` = '" . $_POST['username'] . "'");
		while ($row = mysql_fetch_array($pesterresult)) {
			if ($_POST['username'] == $row['username']) { //Name clash: Player name is already taken.
				echo "That Pesternote account is linked to another Overseer account. Sorry!</br>";
				$pesterclash = True;
			}
		}
		if (!$pesterclash) {
			if ($_POST['password'] != $_POST['confirmpw']) {
				echo "Those passwords do not match! Give it another go, you probably just mistyped one slightly.</br>";
			} else { //Success! Add details to database.
				mysql_query("UPDATE `Players` SET `pesternoteUsername` = '" . $_POST['username'] . "', `pesternotePassword` = '" . $_POST['password'] . "' WHERE `Players`.`username` = '$username' LIMIT 1;");
				if (!empty($_POST['confirmation'])) { //Post a confirmation check
					$confirmation = sendPost($_POST['username'], $_POST['password'], "I've just linked this account to my account $username on The Overseer Project");
					if ($confirmation) {
						echo "Test successful!</br>";
					} else {
						echo "Test failed. Make sure your details are correct.</br>";
					}
				}
			}
		}
	}
	if (!empty($_POST['settings'])) {
		if (!empty($_POST['postbosses'])) {
			$userrow['postbosses'] = 1;
		} else {
			$userrow['postbosses'] = 0;
		}
		mysql_query("UPDATE `Players` SET `postbosses` = $userrow[postbosses] WHERE `Players`.`username` = '$username' LIMIT 1;");
		echo "Settings updated!</br>";
	}
	echo "<a href='http://www.pesternote.com'>Pesternote</a> settings. Control how your Project account interacts with your Pesternote account here.</br>";
	echo "Note that you can de-link by entering an empty username and password and submitting the linking form.</br></br>";
	echo "Set your Pesternote account using this form:</br>";
	echo '<form action="pesternote.php" method="post"> Pesternote username: <input id="username" name="username" type="text" /><br />
Pesternote password: <input id="password" name="password" type="password" /><br />
Confirm Pesternote password: <input id="confirmpw" name="confirmpw" type="password" /><br />
<input type="checkbox" name="confirmation" value="confirmation"> Make a post to my Pesternote account immediately to check if the details work<br />
<input type="submit" value="Link this Pesternote account to this Project account" /> </form><br />';
	echo "Or change your Pesternote settings using this one (NOTE - an unchecked box will turn a given setting off):</br>";
	echo '<form action="pesternote.php" method="post">
<input type="hidden" name="settings" value="settings">
<input type="checkbox" name="postbosses" value="postbosses"> Post to my Pesternote account when I defeat a boss<br />
<input type="submit" value="Apply these settings" /> </form><br />';
	echo "Current settings:</br>";
	if ($userrow['postbosses'] == 0) {
		echo "Do not post boss defeats to my Pesternote account</br>";
	} else {
		echo "Do post boss defeats to my Pesternote account</br>";
	}
}
require_once("footer.php");
?>