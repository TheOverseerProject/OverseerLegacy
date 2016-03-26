<?php
session_start();
$supertime_begin = microtime(true);
require('includes/headerin.php');
require('includes/global_functions.php');

?>
<!DOCTYPE html>
<html>
<head>
<title>The Overseer Project</title>
<link href="core.css?1" rel="stylesheet"/>
<link href="coring.css?1" rel="stylesheet" media="screen and (max-width: 1000px)"/>
<link href="mobile.css?1" rel="stylesheet" media="screen and (max-width: 800px)"/>
<?php
$imagestr = "Images/title/corpia.png";
if ($userrow['dreamingstatus'] == "Prospit") { //User on Prospit
  echo '<link href="prospit.css?1" rel="stylesheet"/>';
  $imagestr = "Images/title/corpiaprospit.png";
} elseif ($userrow['dreamingstatus'] == "Derse") {
  echo '<link href="derse.css?1" rel="stylesheet"/>';
}
if (mdetect()) {
  echo '<link href="coring.css?1" rel="stylesheet"/>
  <link href="mobile.css?1" rel="stylesheet"/>';
}
?>
<?php if (!empty($userrow['colour'])) echo "<style>favcolour{color: $userrow[colour];}</style>"; ?>
<meta name="viewport" content="width=device-width">
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<?php if (mdetect()) { ?>
  <script>
    $(document).ready(function() {
      $("ul.drop li ul").hide();
      $("ul.drop li").click(function(e) {
        e.stopPropagation();
        var child = $(this).children("ul");
        vis = $(child).is(":visible");
        $("ul.drop li ul").hide();
        vis ? child.hide() : child.show();
      });
      $("ul.adrop li").click(function(e) {
        e.stopPropagation();
        var child = $(this).children("ul");
        vis = $(child).is(":visible");
        $("ul.adrop li ul").hide();
        vis ? child.hide() : child.show();
      });
      $("#banner, #spanner, body, #mained, html").click(function() {
        $("ul.drop li ul, ul.adrop li ul").hide();
      });
    });
  </script>
  <style>
  .asessions {
    width: 80px;
  }
  .aabout {
    width: 110px;
  }
  .aplayer {
    width: 115px;
  }
  .astrife {
    width: 120px;
  }
  .aexplore {
    width: 100px;
  }
  .ashop {
    width: 100px;
  }
  .rhyme {
    text-align: center;
  }
  </style>
<?php } else { ?>
  <style>
  ul.drop li:hover > ul {
    display: block;
  }
  ul.adrop li:hover > ul {
    display: block;
  }
  </style>
<?php } ?>

<script>
  window.start = new Date().getTime();
  var countdown = setInterval(function () {
      window.minutes = parseInt($("span.c1").html());
      window.seconds = parseInt($("span.c2").html());
      window.encounters = parseInt($("span.c3").html());
  	var current = new Date().getTime();
  	var diff = current - start;
      if (diff >= 1000) {
          window.seconds--;
          if (window.seconds == -1) {
              window.seconds = 59;
              window.minutes--;
          }
          if (window.minutes == -1) {
            window.minutes = 19;
            window.seconds = 59;
            if (window.encounters < 100) {
              window.encounters++;
            }
          }
          $("span.c1, span.d1").html(("0"+window.minutes).slice(-2));
          $("span.c2, span.d2").html(("0"+window.seconds).slice(-2));
          $("span.c3, span.d3").html(window.encounters);
  		window.start = current - (diff-1000);
      }
  }, 10);

  $(document).ready(function() {
    $('li.dream').click(function() {
      $('#dreamsequence').submit();
    });
  });
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-43055694-1', 'theoverseerproject.com');
  ga('send', 'pageview');

</script>
</head>
<body link="#2cff4b" aLink="#2cff4b" vLink="#2cff4b">
<!-- Project Wonderful Ad Box Loader -->
<script type="text/javascript">
   (function(){function pw_load(){
      if(arguments.callee.z)return;else arguments.callee.z=true;
      var d=document;var s=d.createElement('script');
      var x=d.getElementsByTagName('script')[0];
      s.type='text/javascript';s.async=true;
      s.src='//www.projectwonderful.com/pwa.js';
      x.parentNode.insertBefore(s,x);}
   if (window.attachEvent){
    window.attachEvent('DOMContentLoaded',pw_load);
    window.attachEvent('onload',pw_load);}
   else{
    window.addEventListener('DOMContentLoaded',pw_load,false);
    window.addEventListener('load',pw_load,false);}})();
</script>
<!-- End Project Wonderful Ad Box Loader -->
<span class="ban">
<span class="adleft">
<center><img src="Images/title/adsnew.png" style="padding:0px; margin:0px; max-width: 160px;"></center>
<!--<script type="text/javascript"><!--
google_ad_client = "ca-pub-6147873397190735";
/* Overseer Horizontal */
google_ad_slot = "3189632801";
google_ad_width = 160;
google_ad_height = 600;

</script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>-->
<!-- Project Wonderful Ad Box Code -->
<div id="pw_adbox_71403_3_0"></div>
<script type="text/javascript"></script>
<noscript><map name="admap71403" id="admap71403"><area href="http://www.projectwonderful.com/out_nojs.php?r=0&c=0&id=71403&type=3" shape="rect" coords="0,0,160,600" title="" alt="" target="_blank" /></map>
<table cellpadding="0" cellspacing="0" style="width:160px;border-style:none;background-color:#ffffff;"><tr><td><img src="http://www.projectwonderful.com/nojs.php?id=71403&type=3" style="width:160px;height:600px;border-style:none;" usemap="#admap71403" alt="" /></td></tr><tr><td style="background-color:#ffffff;" colspan="1"><center><a style="font-size:10px;color:#0000ff;text-decoration:none;line-height:1.2;font-weight:bold;font-family:Tahoma, verdana,arial,helvetica,sans-serif;text-transform: none;letter-spacing:normal;text-shadow:none;white-space:normal;word-spacing:normal;" href="http://www.projectwonderful.com/advertisehere.php?id=71403&type=3" target="_blank">Ads by Project Wonderful!  Your ad here, right now: $0</a></center></td></tr></table>
</noscript>
<!-- End Project Wonderful Ad Box Code -->
</span>
</span>

<div id="mained">
<div id="banner">
<a href="/"><div id="bannerd"></div><img id="banning" src="/Images/title/banner.png"></a>

<?php
if (empty($_SESSION['username'])) {
?>
  <div class="intercross">
    <script>
    $(document).ready(function () {
    var username = "";
    var password = "";
      $('#login').submit(function() {
        username = $("#username").val();
        password = $("#password").val();
        $('#password').val('');
        $('#catch').text('Logging you in...').attr('style', 'color: #FFA500;');
        $.post("login.php", { username: username, password: password, mako: "kawaii" })
        .done(function(data) {
        if (data == "true") {
        window.location = 'index.php';
        $('#catch').text('Success!').attr('style', 'color: green;');
        }
        else {
        $('#catch').text('Incorrect login details').attr('style', 'color: red;');
        }
        });
        return false;
      });
    });
    </script>

    <?php
    if (empty($_SESSION['username'])) { ?>
    <style>
      .intercross {
        height: 90px;
      }
    </style>
    <?php
    echo '<form id="login" action="login.php" method="post"> Username: <input id="username" maxlength="50" name="username" type="text" /><br /> Password: <input id="password" maxlength="50" name="password" type="password" />
    <br />
    <input name="Submit" type="submit" value="Submit" /> </form>
    <span style="color: red;" id="catch"></span>
    ';
    } else {
    echo "<script>
    $(document).ready(function () {
        window.location = 'index.php';
    });
    </script>";
    }
    ?>
  </div>
  <div class="intermix">
    <script>
      $(document).ready(function () {
      var username = "";
      var password = "";
        $('#logina').submit(function() {
          usernamea = $("#usernamea").val();
          passworda = $("#passworda").val();
          $('#passworda').val('');
          $('.catch').text('Logging you in...').attr('style', 'color: #FFA500;');
          $.post("login.php", { username: usernamea, password: passworda, mako: "kawaii" })
          .done(function(data) {
          if (data == "true") {
          window.location = 'index.php';
          $('.catch').text(' Success!').attr('style', 'color: green;');
          }
          else {
          $('.catch').text(' Incorrect login details').attr('style', 'color: red;');
          }
          });
          return false;
        });
      });
    </script>
    <?php
    if (empty($_SESSION['username'])) {
    echo '<form id="logina" action="login.php" method="post">
    &nbsp;<nobr>Username: <input id="usernamea" maxlength="50" name="usernamea" type="text" /></nobr> <nobr>Password: <input id="passworda" maxlength="50" name="passworda" type="password" /></nobr> <input name="Submit" type="submit" value="Submit" />
    </form>
    <center><span style="color: red;" class="catch"></span></center>
    ';
    } else {
    echo "<script>
    $(document).ready(function () {
        window.location = 'index.php';
    });
    </script>";
    }
    ?>
  </div>
<?php } else { ?>
  <div class="intercross">
  <?php
  if($userrow['Boondollars']>10000000) { 
    $booned = number_format($userrow['Boondollars']/1000000, 2); 
	$booni = "bucki.png";
	} 
  else {
    $booned = number_format($userrow['Boondollars']); 	
	$booni = "booni.png";
	}
    $gristed =  number_format($userrow['Build_Grist']);
    $ecchi = $userrow['Echeladder'];
    $classy = "Class";
    $classresulta = mysql_query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$userrow[$classy]';");
    $classrowa = mysql_fetch_array($classresulta);
    $unarmedpowera = floor($userrow['Echeladder'] * (pow(($classrowa['godtierfactor'] / 100),$userrow['Godtier'])));
    $equippedmain = $_POST['equipmain'];
    if ($equippedmain != "") {
      $itemname = str_replace("'", "\\\\''", $userrow[$equippedmain]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($row = mysql_fetch_array($itemresult)) {
        $itemname = $row['name'];
        $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
        if ($itemname == $userrow[$equippedmain]) {
  	$mainpowera = $row['power'];
        }
      }
    } else {
      if ($userrow['equipped'] != "") {
        $itemname = str_replace("'", "\\\\''", $userrow[$userrow['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
        $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
        while ($row = mysql_fetch_array($itemresult)) {
  	$itemname = $row['name'];
  	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
  	if ($itemname == $userrow[$userrow['equipped']]) {
  	  $mainpowera = $row['power'];
  	}
        }
      } else {
        $mainpowera = 0;
      }
    }
    if ($equippedoff != "") {
      $itemname = str_replace("'", "\\\\''", $userrow[$equippedoff]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
      while ($row = mysql_fetch_array($itemresult)) {
        $itemname = $row['name'];
        $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
        if ($itemname == $userrow[$equippedoff]) {
  	$offpower = ($row['power'] / 2);
        }
      }
    } else {
      if ($userrow['offhand'] != "" && $userrow['offhand'] != $equippedmain && $equippedoff != "2HAND") {
      $itemname = str_replace("'", "\\\\''", $userrow[$userrow['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
        while ($row = mysql_fetch_array($itemresult)) {
        $itemname = $row['name'];
        $itemname = str_replace("\\", "", $itemname); //Remove escape characters.
        if ($itemname == $userrow[$userrow['offhand']]) {
  	  $offpowera = ($row['power'] / 2);
  	}
        }
      } else {
        $offpowera = 0;
      }
    }
    $spritepowera = $userrow['sprite_strength'];
    if ($spritepowera < 0) {
      $spritepowera = 0;
    }
    if ($userrow['dreamingstatus'] == "Awake") {
      $healthy = strval(floor(($userrow['Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining.
      $powerlevela = $unarmedpowera + $mainpowera + $offpowera + $spritepowera + $userrow['powerboost'];
    } else {
      $healthy = strval(floor(($userrow['Dream_Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining in a Dreaming state.
      $powerlevela = $unarmedpowera + $userrow['powerboost'];
    }
    $minuta = strval(produceMinutes($interval - ($time - $lasttick)));
    $seconda = strval(produceSeconds($interval - ($time - $lasttick)));
  ?>
    <span class="ripe">
      <div class="lined">
        <a href="overview.php"><?php echo $_SESSION['username']; ?></a>
      </div>
      <div class="pined">
        <a href="overview.php"><img src="/Images/title/health.png" align="center" title="Health"></a> <?php echo $healthy; ?>%
      </div>
    </span>

    <div class="lefy">
      <a href="strife.php"><img src="/Images/title/sl.png" align="center" title="Number of Encounters"></a> <span class="c3"><?php echo strval($encounters); ?></span>
      </br>
      <a href="portfolio.php"><img src="/Images/title/power.png" align="center" title="Strife Power"></a> <?php echo $powerlevela; ?>
      </br>
      <a href="grist.php"><img src="/Images/title/gristling.png" align="center" title="Grist Count"></a> <?php echo strval($gristed); ?>
    </div>

    <div class="righy">
      <a href="strife.php"><img src="/Images/title/enc.png" align="center" title="Time Until Next Encounter"></a> <span class="c1"><?php echo $minuta; ?></span>:<span class="c2"><?php echo $seconda; ?></span>
      </br>
      <a href="echeviewer.php"><img src="/Images/title/eche.png" align="center" title="Echeladder"></a> <?php echo strval($ecchi); ?>
      </br>
      <a href="porkhollow.php"><img src="/Images/title/<?php echo strval($booni); ?>" align="center" title="Boondollars"></a> <?php echo strval($booned); ?>
    </div>
  
  </div>

  <div class="intermix">
  &nbsp;<span class="pined"><a href="overview.php"><?php echo $_SESSION['username']; ?></a></span>
  <nobr><a href="overview.php"><img src="/Images/title/health.png" align="center" title="Health"></a><?php echo $healthy; ?>%</nobr>
  <nobr><a href="strife.php"><img src="/Images/title/sl.png" align="center" title="Number of Encounters"></a><span class="d3"><?php echo strval($encounters); ?></span></nobr>
  <nobr><a href="strife.php"><img src="/Images/title/enc.png" align="center" title="Time Until Next Encounter"></a>&nbsp;<span class="d1"><?php echo $minuta; ?></span>:<span class="d2"><?php echo $seconda; ?></span></nobr>
  <nobr><a href="portfolio.php"><img src="/Images/title/power.png" align="center" title="Strife Power"></a><?php echo $powerlevela; ?></nobr>
  <nobr><a href="echeviewer.php"><img src="/Images/title/eche.png" align="center" title="Echeladder"></a><?php echo strval($ecchi); ?></nobr>
  <nobr><a href="grist.php"><img src="/Images/title/gristling.png" align="center" title="Grist Count"></a><?php echo strval($gristed); ?></nobr>
  <nobr><a href="porkhollow.php"><img src="/Images/title/<?php echo strval($booni); ?>" align="center" title="Boondollars"></a><?php echo strval($booned); ?></nobr>
  </div>
<?php } ?>
</div>

<div id="spanner">
<?php
  if (empty($_SESSION['username'])) { ?>
  <ul id="nav" class="drop">
    <li><a href="loginer.php"><span class="rhyme slam alogin">&gt;LOGIN</span></a></li>

    <li><span class="rhyme slam asessions">SESSIONS</span>
      <ul>
        <li><a href="sessionform.php"><span class="rhyme asessions bsessions">&gt;START SESSION</span></a></li>
        <li><a href="playerform.php"><span class="rhyme asessions bsessions">&gt;JOIN SESSION</span></a></li>
        <li><a href="sessioninfo.php"><span class="rhyme asessions bsessions">&gt;VIEW SESSION</span></a></li>
        <li><a href="forgotpassword.php"><span class="rhyme asessions bsessions">&gt;PASSWORD RECOVERY</span></a></li>
      </ul>
    </li>

    <li><span class="rhyme slam aabout">ABOUT</span>
      <ul>
        <li><a href="credits.php"><span class="rhyme aabout babout">&gt;Credits</span></a></li>
        <li><a href="changelog.php"><span class="rhyme aabout babout">&gt;Change Log</span></a></li>
        <li><a href="http://theoverseerproject.tumblr.com/"><span class="rhyme aabout babout">&gt;News</span></a></li>
        <li><a href="news.php"><span class="rhyme aabout babout">&gt;Items/<wbr>Art Updates</span></a></li>
        <li><a href="randomizer.php"><span class="rhyme aabout babout">&gt;Random Combinations</span></a></li>
      </ul>
    </li>

    <li><a href="http://overseerforums.forumotion.com/"><span class="rhyme slam aforums">&gt;FORUMS</span></a></li>

    <li><a href="http://the-overseer.wikia.com/wiki/Main_Page"><span class="rhyme slam awiki">&gt;WIKI</span></a></li>
	
	<li><a href="http://www.alterniafm.com"><span class="rhyme slam alogin">&gt;RADIO</span></a></li>

    <li><a href="about.php"><span class="rhyme slam afaq">&gt;FAQ</span></a></li>
  </ul>

<?php } else { ?>
<form id="dreamsequence" action="dreamtransition.php" method="post"><input type="hidden" name="sleep" value="sleep" /></form>
  <ul id="anav" class="adrop">
    <li><span class="rhyme slam aplayer">PLAYER</span>
      <ul>
        <li><a href="overview.php"><span class="rhyme aplayer bplayer">&gt;Info</span></a></li>
        <li class="dream"><span class="rhyme aplayer bplayer">&gt;Sleep?</span></li>
        <li><a href="echeviewer.php"><span class="rhyme aplayer bplayer">&gt;Echeladder</span></a></li>
        <li><a href="inventory.php"><span class="rhyme aplayer bplayer">&gt;Inventory/<wbr>Alchemy</span></a></li>
        <li><a href="storage.php"><span class="rhyme aplayer bplayer">&gt;Item Storage</span></a></li>
        <li><a href="atheneum.php"><span class="rhyme aplayer bplayer">&gt;Atheneum</span></a></li>
        <li><a href="sburbdevices.php"><span class="rhyme aplayer bplayer">&gt;SBURB Devices</span></a></li>
        <li><a href="sburbserver.php"><span class="rhyme aplayer bplayer">&gt;SBURB Server</span></a></li>
        <li><a href="sessioninfo.php"><span class="rhyme aplayer bplayer">&gt;View Session</span></a></li>
        <li><a href="playersettings.php"><span class="rhyme aplayer bplayer">&gt;Player Settings</span></a></li>
      </ul>
    </li>

    <li><span class="rhyme slam astrife">STRIFE</span>
      <ul>
        <li><a href="strife.php"><span class="rhyme astrife bstrife">&gt;Strife!</span></a></li>
        <li><a href="dungeons.php"><span class="rhyme astrife bstrife">&gt;Dungeon Diving</span></a></li>
        <li><a href="portfolio.php"><span class="rhyme astrife bstrife">&gt;Strife Portfolio</span></a></li>
        <li><a href="echeviewer.php"><span class="rhyme astrife bstrife">&gt;Echeladder</span></a></li>
        <li><a href="consumables.php"><span class="rhyme astrife bstrife">&gt;Consumables</span></a></li>
        <li><a href="wardrobe.php"><span class="rhyme astrife bstrife">&gt;Wardrobifier</span></a></li>
	<?php if (!empty($_SESSION['adjective'])) { ?>
        <li><a href="aspectpowers.php"><span class="rhyme astrife bstrife">&gt;DO THE <?php echo $_SESSION['adjective']; ?> THING</span></a></li>
        <li><a href="roletech.php"><span class="rhyme astrife bstrife">&gt;Roletechs</span></a></li>
        <?php } ?>
      </ul>
    </li>
    
    <li><span class="rhyme slam aexplore">EXPLORE</span>
      <ul>
        <li><a href="dungeons.php"><span class="rhyme aexplore bexplore">&gt;Dungeon Diving</span></a></li>
        <?php if ($userrow['dreamingstatus'] !== "Awake") { ?>
        <li><a href="explore.php"><span class="rhyme aexplore bexplore">&gt;Explore Your Surroundings</span></a></li>
        <?php } else { ?>
        <li><a href="consortquests.php"><span class="rhyme aexplore bexplore">&gt;Go Questing</span></a></li>
        <li><a href="mercenaries.php"><span class="rhyme aexplore bexplore">&gt;Followers</span></a></li>
        <?php } ?>
        <li class="dream"><span class="rhyme aexplore bexplore">&gt;Sleep?</span></li>
      </ul>
    </li>

    <li><span class="rhyme slam ashop">SHOP</span>
      <ul>
        <li><a href="catalogue.php"><span class="rhyme ashop bshop">&gt;Item Catalogue</span></a></li>
        <li><a href="grist.php"><span class="rhyme ashop bshop">&gt;Gristwire</span></a></li>
        <li><a href="porkhollow.php"><span class="rhyme ashop bshop">&gt;Virtual Porkhollow</span></a></li>
        <?php if ($userrow['dreamingstatus'] == "Awake") { ?>
        <li><a href="shop.php"><span class="rhyme ashop bshop">&gt;Consort Shops</span></a></li>
        <li><a href="gristexchange.php"><span class="rhyme ashop bshop">&gt;Stock Exchange</span></a></li>
        <?php } ?>
        <li><a href="fraymotifs.php"><span class="rhyme ashop bshop">&gt;Fraymotifs</span></a></li>
        <li><a href="resets.php"><span class="rhyme ashop bshop">&gt;Resetter</span></a></li>
        <li><a href="rewards.php"><span class="rhyme ashop bshop">&gt;Rewards</span></a></li>								       
      </ul>
    </li>

    <?php if ($userrow['admin'] == 1) { ?>
    <li><a href="admin.php"><span class="rhyme slam aadministration">&gt;ADMIN</span></a></li>
    <?php } ?>
    <li><a href="messages.php"><span class="rhyme slam amessages">&gt;MESSAGES<?php
      $msgcount = $userrow['newmessage'];
      if ($msgcount != 0) {
        echo "($msgcount)";
      }
    ?></span></a></li>

    <li><span class="rhyme slam aabout">ABOUT</span>
      <ul>
        <li><a href="credits.php"><span class="rhyme aabout babout">&gt;Credits</span></a></li>
        <li><a href="changelog.php"><span class="rhyme aabout babout">&gt;Change Log</span></a></li>
        <li><a href="http://theoverseerproject.tumblr.com/"><span class="rhyme aabout babout">&gt;News</span></a></li>
        <li><a href="news.php"><span class="rhyme aabout babout">&gt;Items/<wbr>Art Updates</span></a></li>
        <li><a href="captchalist.php"><span class="rhyme aabout babout">&gt;Item List</span></a></li>
        <li><a href="randomizer.php"><span class="rhyme aabout babout">&gt;Random Combinations</span></a></li>
        <li><a href="feedback.php"><span class="rhyme aabout babout">&gt;Feedback/<wbr>Submit</span></a></li>
        <li><a href="submissions.php"><span class="rhyme aabout babout">&gt;Submissions</span></a></li>
		<li><a href="donate.php"><span class="rhyme aabout babout">&gt;Donate</span></a></li>
      </ul>
    </li>

    <li><a href="http://overseerforums.forumotion.com/"><span class="rhyme slam aforums">&gt;FORUMS</span></a></li>

    <li><a href="http://the-overseer.wikia.com/wiki/Main_Page"><span class="rhyme slam awiki">&gt;WIKI</span></a></li>
	
	<li><a href="http://www.alterniafm.com"><span class="rhyme slam alogin">&gt;RADIO</span></a></li>

    <li><a href="about.php"><span class="rhyme slam afaq">&gt;FAQ</span></a></li>
	

    <li><a href="logout.php"><span class="rhyme slam alogin">&gt;LOGOUT</span></a></li>
  </ul>
<?php } ?>
<div id="canner">