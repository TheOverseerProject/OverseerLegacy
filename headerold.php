<?php
session_start();
require 'time.php'; //This is now necessary so the header can keep track of your timer.
if (empty($_SESSION['username'])) {
  //This is empty for lulz.
  //Okay DC. Whatever you want
  //Actually it's empty because the original code had the if statement around this way. Reversing it would have been more trouble.
} else {
  $con = mysql_connect("localhost","theovers_DC","pi31415926535");
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("theovers_HS", $con);
  $username=$_SESSION['username'];
  $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $username . "'");
  while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
    if ($row['username'] == $username) { //Paranoia: Double-check.
      $userrow = $row;
    }
  }
  $up = False;
  $time = time();
  $interval = 1200; //This is where the interval between encounter ticks is set.
  $lasttick = $userrow['lasttick'];
  $encounters = $userrow['encounters'];
  if ($lasttick != 0) {
    while ($time - $lasttick > $interval) { //Attempt to tick up once per 20 minutes.
      if ($encounters < 100) { //Cap encounters at 100
	$encounters += 1;
      }
      $lasttick += $interval;
    }
  } else { //Player has not had a tick yet.
    $lasttick = $time;
  }
  if ($encounters > $userrow['encounters'] && $userrow['down'] == 1) { //Player is down and attempting to gain an encounter. Negate one gain and recover the player.
    $encounters -= 1;
    mysql_query("UPDATE `Players` SET `down` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Player recovers.
    $up = True;
  }
  mysql_query("UPDATE `Players` SET `encounters` = $encounters WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
  mysql_query("UPDATE `Players` SET `lasttick` = $lasttick WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>The Overseer Project</title>
<link href="/core.css?<?php echo date('l_jS_\of_F_Y_h:i:s_A'); ?>" rel="stylesheet"/>

<meta name="viewport" content="width=device-width">
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
	<script type="text/javascript">
$(document).ready(function () {
        $('#dan, #lan, #pan').css("text-decoration", "underline");
	$('#can').hover(
		function () {
			//show its submenu
			$('#sham, #ran, #kan').hide();
			$('#man').show();
			$('#can').css("text-decoration", "none");
			$('#dan, #lan, #pan').css("text-decoration", "underline");
		},
		function () {
		}

	);
	$('#dan').hover(
		function () {
			//show its submenu
			$('#man, #ran, #kan').hide();
			$('#sham').show();
                        $('#dan').css("text-decoration", "none");
			$('#can, #lan, #pan').css("text-decoration", "underline");
		},
		function () {
		}

	);
	$('#lan').hover(
		function () {
			//show its submenu
			$('#man, #sham, #kan').hide();
			$('#ran').show();
                        $('#lan').css("text-decoration", "none");
			$('#can, #dan, #pan').css("text-decoration", "underline");
		},
		function () {
		}

	);
	$('#pan').hover(
		function () {
			//show its submenu
			$('#man, #sham, #ran').hide();
			$('#kan').show();
                        $('#pan').css("text-decoration", "none");
			$('#can, #dan, #lan').css("text-decoration", "underline");
		},
		function () {
		}

	);
});
	</script>
<style>
#sham, #ran, #kan {
  display: none;
}
</style>

</head>
<body link="#2cff4b" aLink="#2cff4b" vLink="#2cff4b">

<p id="banner" align="center">
<a href="/">The Overseer Project</a> <img src="/Images/title/corpia.png" />
<span id="can" style="cursor: pointer;">Sessions</span> <img src="/Images/title/corpia.png" />

<?php
if (empty($_SESSION['username'])) {
} else {
echo '<span id="pan" style="cursor: pointer;">Player</span> <img src="/Images/title/corpia.png" />';
echo '<span id="lan" style="cursor: pointer;">Strife</span> <img src="/Images/title/corpia.png" />';
echo '<a href="resets.php">Resetter</a> <img src="/Images/title/corpia.png" />';
}
?>

<span id="dan" style="cursor: pointer;">About</span> <img src="/Images/title/corpia.png" />
<a href="feedback.php">Feedback/Submit</a>

<br />

<span id="man">
<?php
if (empty($_SESSION['username'])) {
echo '<a href="loginer.php">Log In</a> <img src="/Images/title/corpia.png" />
<a href="playerform.php">Enter a Session</a> <img src="/Images/title/corpia.png" />
<a href="sessionform.php">Create a Session</a> <img src="/Images/title/corpia.png" />
';
} else {
echo '<a href="logout.php">Log Out</a> <img src="/Images/title/corpia.png" />';
}
?>
<a href="sessioninfo.php">Examine a Session</a>
</span>

<span id="sham">
<a href="tumblr.php">Latest News</a> <img src="/Images/title/corpia.png" />
<a href="news.php">Item and art updates</a> <img src="/Images/title/corpia.png" />
<a href="captchalist.php">List of existing items</a> <img src="/Images/title/corpia.png" />
<a href="about.php">About the project</a>
</span>

<span id="ran">
<a href="strife.php">Strife</a> <img src="/Images/title/corpia.png" />
<a href="portfolio.php">Strife Portfolio and options</a> <img src="/Images/title/corpia.png" />
<a href="echeviewer.php">View your Echeladder</a> <img src="/Images/title/corpia.png" />
<a href="fraymotifs.php">Fraymotifs</a>
</span>

<span id="kan">
<a href="overview.php">Player/Sprite Info</a> <img src="/Images/title/corpia.png" />
<a href="grist.php">Gristwire</a> <img src="/Images/title/corpia.png" />
<a href="porkhollow.php">Virtual Porkhollow</a> <img src="/Images/title/corpia.png" />
<a href="consumables.php">Consume Consumables</a> <img src="/Images/title/corpia.png" />
<a href="inventory.php">Inventory and Alchemical Operations</a> <img src="/Images/title/corpia.png" />
<a href="catalogue.php">Captchalogue Catalogue</a>
</span>

</p>

<div id="spanner">

<?php
$booned = $userrow['Boondollars'];
$gristed = $userrow['Build_Grist'];

if (empty($_SESSION['username'])) {
} else {
  echo "Greetings, " . $_SESSION['username'] . ". You currently have " . strval($encounters) . " encounters with your next one at " . strval(produceTimeString($interval - ($time - $lasttick))) . ", " . strval($booned) . "<img src='/Images/title/boon.png' width='16' /> & " . strval($gristed) . "<img src='/Images/title/grist.png' width='16' />";
}
?>
<div id="canner">