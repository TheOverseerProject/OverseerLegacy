<?php
require 'time.php'; //This is now necessary so the header can keep track of your timer.
require 'pesternotefunctions.php'; //Loads in the Pesternote functions. Naturally.
// mobile detection code
function mdetect(){
  $useragent=$_SERVER['HTTP_USER_AGENT'];
  if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
    return (bool)true;
  } else {
    return false;
  }
}

require_once("includes/SQLconnect.php");
if (empty($_SESSION['username'])) {

  $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = 'default'");
  $userrow = mysql_fetch_array($result);
  if ($userrow['session_name'] != 'Developers' && $userrow['session_name'] != 'Itemods') {
  	//die('The dev area is only available to developers/moderators!');
  }
  //Should stop the "userrow undefined" spam.
} else {

  $username=$_SESSION['username'];
  $result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $username . "' LIMIT 1;");

  while ($row = mysql_fetch_array($result)) { //Fetch the user's database row. We're going to need it several times.
    if ($row['username'] == $username) { //Paranoia: Double-check.
      $userrow = $row;
    }
  }
  if ($userrow['session_name'] != 'Developers' && $userrow['session_name'] != 'Itemods') {
  	//die('The dev area is only available to developers/moderators!');
  }
  //Begin setting string-based vars here.
  $healthvialcolour = "black"; //These are the defaults.
  $aspectvialcolour = "doom";
  if ($userrow['colour'] == "Purple") $healthvialcolour = "purple";
  if ($userrow['Aspect'] == "Breath") $aspectvialcolour = "breath";
  if ($userrow['Aspect'] == "Light") $aspectvialcolour = "light";
  if ($userrow['dreamingstatus'] == "Awake") {
    $healthvialstr = "Health_Vial";
    $oldenemyprestr = "oldenemy";
    $downstr = "down";
  } else {
    $healthvialstr = "Dream_Health_Vial";
    $oldenemyprestr = "olddreamenemy";
    $downstr = "dreamdown";
  }
  //Variable definitions here.
  $max_enemies = 5;
  $droptype = 0; //Drop type is argument 1.
  $droptier = 1; //Drop tiers (if applicable) are argument 2.
  $dropquantity = 2; //Drop quantities are argument 3.
  $dropchance = 3; //Drop chances are argument 4.
  //NOTE - Time's encounter reducing ability activates here, but performing an ability search when that's the only thing that ever will affect the timer constantly seems wasteful.
  //So we just check directly.
  $up = False;
  $time = time();
  if ($userrow['Aspect'] == "Time") {
    $interval = 1080; //18 minutes
    $questinterval = 1620; //27 minutes
  } else { 
    $interval = 1200; //This is where the interval between encounter ticks is set. (20 minutes)
    $questinterval = 1800; //30 minutes
  }
  $lasttick = $userrow['lasttick'];
  $lastquesttick = $userrow['lastquesttick'];
  $encounters = $userrow['encounters'];
  $quests = $userrow['availablequests'];
  if ($lastquesttick != 0) {
  	while ($time - $lastquesttick > $questinterval) { //Attempt to tick up once per 30 minutes.
      $quests += 1;
      $lastquesttick += $questinterval;
    }
  } else { //Player has not had a tick yet.
    $lastquesttick = $time;
  }
  if ($lasttick != 0) {
    while ($time - $lasttick > $interval) { //Attempt to tick up once per 20 minutes.
      $encounters += 1;
      $lasttick += $interval;
    } //calculates encounters after quests so that lasttick will be more accurate (and encounters > quests anyway)
  } else { //Player has not had a tick yet.
    $lasttick = $time;
  }
  if ($encounters > $userrow['encounters'] && ($userrow['down'] == 1 || $userrow['dreamdown'] == 1)) { //Both downs recover after a single encounter is earned.
    $sessionresult = mysql_query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '$sessioname' LIMIT 1;");
    $sessionrow = mysql_fetch_array($sessionresult);
    if ($sessionrow['sessionbossname'] == '') {
   	 $encounters -= 1;
  	  mysql_query("UPDATE `Players` SET `down` = 0, `dreamdown` = 0 WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;"); //Player recovers.
  	  $up = True;
    }
  }
  if ($encounters > 100) $encounters = 100;
  if ($quests > 50) $quests = 50;
  if ($lasttick != $userrow['lasttick']) {
    mysql_query("UPDATE `Players` SET `encounters` = $encounters, `lasttick` = $lasttick, `availablequests` = $quests, `lastquesttick` = $lastquesttick WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
    $userrow['encounters'] = $encounters;
    $userrow['availablequests'] = $quests;
    $userrow['lasttick'] = $lasttick;
    $userrow['lastquesttick'] = $lastquesttick;
  }
}
