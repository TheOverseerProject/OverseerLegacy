This is The Overseer Project, Version 1 code, as it sits on the live build. The current state 
(26/03/2016) of the code mirrors the "final" official version of the project before deprecation
in favour of version 2. This repository will be mirrored to a non-changing repository for posterity.

By releasing this code, we hope that you can gain an appreciation for complexity of the project, and 
learn from our mistakes.

NOTE: V1 was written by a bunch of amateurs, learning from scratch. Code documentation may be sparse,
and the code itself may be terrible. You've been warned. 

INSTALLATION
------------

First up, you're gonna need a webserver. On Linux, this can be Nginx or Apache. You'll also need PHP, 
and MySQL.

NOTE: v1 was written using PHP's mysql extension. This is now considered deprecated, and mysqli is
recommended instead - be aware when installing that this extension must be enabled. 

For Windows, you can use WAMP to set it all up easily.

NOTE: By default, WAMP's MySQL instance enforces lowercase for table and column names. Disable this 
if you might ever want to move it to a Linux server. 

Once that's all installed, it's time to set up the database. Import install/Overseerv1.sql - this
contains everything you need to get started, including items. Set up users etc. 

Next, head to /includes and edit SQLConnect.php.dist with your database info, then rename it to 
SQLConnect.php, removing the .dist. Do the same with DEV SQLConnect - this is likely not needed
but if you run into problems, worth checking.

That should be about it. 

Have fun - we're looking forward to seeing what you come up with. 

- Thellere