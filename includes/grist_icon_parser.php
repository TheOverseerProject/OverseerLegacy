<?php
function gristNameToImagePath($gristtype)
{

	if ($gristtype == "Opal" || $gristtype == "Polychromite" || $gristtype == "Rainbow") { //Special cases for animated grists.
		return "$gristtype.gif";}
	else
	{	return "$gristtype.png";}
		
}

?>