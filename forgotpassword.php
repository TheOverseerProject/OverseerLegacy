<?php
require_once("header.php");

function genRecoveryCode() {
	$i = 0;
	$reccode = "";
	while ($i < 32) {
		$newchar = rand(1,16);
		$newchar--;
		switch ($newchar) {
			case 10:
				$reccode .= "a";
				break;
			case 11:
				$reccode .= "b";
				break;
			case 12:
				$reccode .= "c";
				break;
			case 13:
				$reccode .= "d";
				break;
			case 14:
				$reccode .= "e";
				break;
			case 15:
				$reccode .= "f";
				break;
			default:
				$reccode .= strval($newchar);
				break;
		}
		$i++;
	}
	return $reccode;
}
if (!empty($_SESSION['username'])) {
	echo "If you're logged in already, why do you need to reset your password?</br>";
} else {
if (!empty($_POST['accountname'])) {
	if (!empty($_POST['accountemail'])) {
		$recoverresult = mysql_query("SELECT `username`,`email` FROM `Players` WHERE `Players`.`username` = '" . $_POST['accountname'] . "' LIMIT 1;");
		$recrow = mysql_fetch_array($recoverresult);
		//echo $recrow['username'] . "/" . $_POST['accountname'];
		if ($recrow['username'] == $_POST['accountname']) {
			if ($recrow['email'] == $_POST['accountemail']) {
				$newcode = genRecoveryCode();
				$url = "http://www.theoverseerproject.com/forgotpassword.php?user=" . $recrow['username'] . "&code=" . $newcode;
				$message = $_POST['accountname'] . ",\r\n\r\nYou have received this email because a request was made to change your account's password. If you initiated this action, please click on or copy the following URL to continue the process:\r\n\r\n $url \r\n\r\nYou will then be prompted to input a new password.\r\n\r\nIf you did not initiate this request, disregard this message. Your account password cannot be changed without visiting that link, and it will expire the next time you log into your account.\r\n\r\nRegards,\r\nThe Overseer Team";
				$message = wordwrap($message, 70, "\r\n");
				$to = $_POST['accountemail'];
				$subject = "Password Recovery Request";
				$from = "info@overseerdev.ctri.co.uk";
				$headers = "From:" . $from;
				$sentsuccess = mail($to,$subject,$message,$headers);
				if ($sentsuccess) {
					echo "An email was sent with instructions on how to change your password. You should hopefully receive it sometime within the next 15 minutes. Be sure to check your spam folder if it doesn't appear in your inbox.</br>";
					mysql_query("UPDATE `Players` SET `recovery_confirm` = '$newcode' WHERE `Players`.`username` = '" . $_POST['accountname'] . "' LIMIT 1;");
				}
				else echo 'Something went wrong while sending the email. Feel free to try again, but please contact <a href="http://babbyoverseer.tumblr.com">BabbyOverseer</a> if the problem persists.</br>';
			} else echo "The email that you gave doesn't match the email in the database for that account. Please make sure you typed it correctly.</br>";
		} else echo "No account by that name exists. Please make sure you typed it correctly.</br>";
	} else echo "Please input an email address.</br>";
}

if (empty($_GET['user'])) {
	echo "You can use this form if you forgot your password. As long as you set your email address, you can have a link sent to it that will prompt you for a new password.</br></br>";
	echo '<form method="post" action="forgotpassword.php">Account username: <input type="text" name="accountname"></br>
	Email address: <input type="text" name="accountemail"></br>
	<input type="submit" value="Send recovery email"></form>';
	echo 'If your account doesn\'t have an email address assigned, or you have any other questions, please contact <a href="http://babbyoverseer.tumblr.com">BabbyOverseer</a>.';
} else {
	if (!empty($_GET['code'])) {
		$recoverresult = mysql_query("SELECT `username`,`email`,`recovery_confirm` FROM `Players` WHERE `Players`.`username` = '" . $_GET['user'] . "' LIMIT 1;");
		$recrow = mysql_fetch_array($recoverresult);
		if ($recrow['username'] == $_GET['user']) {
			if ($recrow['recovery_confirm'] == $_GET['code'] && !empty($recrow['recovery_confirm'])) {
				if (!empty($_POST['newpass'])) {
					if ($_POST['newpass'] == $_POST['cnewpass'] && !empty($_POST['newpass'])) {
						$newpass = crypt(mysql_real_escape_string($_POST['newpass']));
						mysql_query("UPDATE Players SET `password` = '$newpass' WHERE `Players`.`username` = '" . $_GET['user'] . "' LIMIT 1;");
						echo "Password changed successfully!</br>";
						mysql_query("UPDATE Players SET `recovery_confirm` = '' WHERE `Players`.`username` = '" . $_GET['user'] . "' LIMIT 1;"); //blank the recovery ID so that it can't be used again
					} else echo "Error changing password: Confirmation does not match new password, or the new password was left blank.</br>";
				} else {
					echo '<form method="post" action="forgotpassword.php?user=' . $_GET['user'] . '&code=' . $_GET['code'] . '">Recovery confirmation code successfully validated. Input your new password below.</br>
					New Password: <input type="password" name="newpass"></br>
					Confirm New Password: <input type="password" name="cnewpass"></br>
					<input type="submit" value="Change it!"></form></br>';
				}
			} else echo "Error: The recovery confirmation code is incorrect, or the account is not requesting password recovery.</br>";
		} else echo "Error: Username not found.</br>";
	} else echo "Error: Please use the URL provided with the recovery email.</br>";
}
}
require_once("footer.php");
?>