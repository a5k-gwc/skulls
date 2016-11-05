Skulls! Multi-Network WebCache
------------------------------

skulls.php is the main file.

admin/test.php can be used to verify settings that must be changed in vars.php.
admin/update.php can be used to update data files if you have installed an old version of this cache (it doesn't check if Skulls is updated).


WARNING
I STRONGLY SUGGEST you use a CNAME for the cache such as http://gwc.your-site.com/skulls.php
So if you ever want to take it down all you will have to do is delete the CNAME.

P2P clients have long memories, it takes a long time (maybe forever) until the name of your cache has vanished.
If you don't follow this warning you will still be hit by cache requests after you have stopped running the cache.


INSTALLATION PROCEDURE
1. Send all files to your web server
2. Go on http://name_of_your_web_server/admin/test.php in your browser
3. Then open vars.php with notepad and change the settings that you see in step 2
4. In vars.php change also MAINTAINER_EMAIL (optional), MAINTAINER_WEBSITE (optional) and MAINTAINER_NICK
5. Send updated vars.php to your web server
6. Go on http://name_of_your_web_server/skulls.php in your browser, if you don't see any error you are OK
7. Put the full url of the cache in CACHE_URL inside vars.php (with notepad), example: http://gwc.your-site.com/skulls.php (optional but recommended)
Note: You must be sure that there aren't any problems before go to the step 8
8. Submit the url of skulls.php here: http://skulls.sourceforge.net/submit.php

If you need more help ask here: http://sourceforge.net/forum/forum.php?forum_id=522656


UPDATE PROCEDURE
1. Delete old skulls.php in your web server
2. Send updated files inside admin directory to your web server
3. Go on http://name_of_your_web_server/admin/update.php in your browser
4. Send all updated files to your web server (Copy the things that you have changed in the new vars.php)
5. Go on http://name_of_your_web_server/skulls.php in your browser, if you don't see any error you are OK

If you need more help ask here: http://sourceforge.net/forum/forum.php?forum_id=522656