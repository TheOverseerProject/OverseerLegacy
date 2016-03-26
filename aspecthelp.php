<?php
require_once("header.php");
echo '<a href="aspectpowers.php">Back to aspect power usage</a></br>';
echo 'Each option for a pattern (damage, power boost, heal, etc) gets better or worse depending on your class and aspect. Experiment with different aspect manipulations and see what works well for your combination.</br></br>';
echo 'Aspect Patterns consume your entire aspect vial by default. This vial regenerates by 10% per encounter consumed when you go to sleep or wake up, due to some weird shit with one of your selves being rested and recovering the vial but both selves sharing it because it is so intrinsic to your self. To consume less, you need to put points into reducing the cost: each 1% reduces the cost by 1% of your vial by default, this is raised and lowered by certain classes and aspects like the other effects.</br></br>';
echo 'Each class is either active or passive, offering a percentage bonus to the correct type of action and a corresponding penalty to the incorrect type. Buffing yourself or attacking enemies as the main strifer are considered active actions, while buffing an ally or attacking enemies while aiding an ally are considered passive. These bonuses and penalties even have a small effect on your regular power!</br></br>';
echo 'You can store up to four aspect patterns. They can be changed at any time, so if you need something specific during a strife you can set the pattern during the fight.</br></br>';
echo 'Remember that some powerful enemies are resistant to damage and power reduction. If your ability is hitting the cap those enemies have, consider mixing in some cost reduction or self-buffs.</br></br>';
echo 'It is possible to use one consumable item OR aspect pattern or ability per round. Choose wisely! Fraymotifs are much simpler to use, and so you can throw down as many tunes as you like each turn.';
require_once("footer.php");
?>