<?php
function location ($userrow,$locationrow) {
  switch ($userrow[location]) {
  case "Prospit":
    $locationstr = "Prospit";
    break;
  case "Derse":
    $locationstr = "Derse";
    break;
  case "Battlefield":
    $locationstr = "The Battlefield";
    break;
  case "Lair":
    $locationstr = "Your Denizen's Lair";
    break;
  case "home":
    $locationstr = "Land of $userrow[land1] and $userrow[land2]";
    break;
  default:
    $locationstr = "Land of $locationrow[land1] and $locationrow[land2]";
    break;
  }
  return $locationstr;
}
?>