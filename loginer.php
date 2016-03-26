<?php
session_start();
require_once("header.php"); ?>

<script>
$(document).ready(function () {
var username = "";
var password = "";
  $('#loginb').submit(function() {
    username = $("#usernameb").val();
    password = $("#passwordb").val();
    $('#passwordb').val('');
    $('#catchb').text('Logging you in...').attr('style', 'color: #FFA500;');
    $.post("login.php", { username: username, password: password, mako: "kawaii" })
    .done(function(data) {
    if (data == "true") {
    window.location = 'index.php';
    $('#catchb').text('Success!').attr('style', 'color: green;');
    }
    else {
    $('#catchb').text('Incorrect login details').attr('style', 'color: red;');
    }
    });
    return false;
  });
});
</script>

<?php
if ($_SESSION['username'] == "") {
echo '<form id="loginb" action="login.php" method="post"> Username: <input id="usernameb" maxlength="50" name="usernameb" type="text" /><br /> Password: <input id="passwordb" maxlength="50" name="passwordb" type="password" /><br />
<a href="playerform.php">Enter a Session</a> |
<a href="sessionform.php"> Create a Session</a>
<br />
<a href="forgotpassword.php">Forget your password?</a>
<br />
<input name="Submit" type="submit" value="Submit" /> </form>
<span style="color: red;" id="catchb"></span>

';
} else {
echo "<script>
$(document).ready(function () {
    window.location = 'index.php';
});
</script>";
}
require_once("footer.php");
?>	