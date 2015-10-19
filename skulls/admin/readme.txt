Skulls! Multi-Network WebCache
-------------------------------

skulls.php is the main file.

admin/test.php can be used to verify settings that must be changed in vars.php.
admin/update.php can be used to update data files if you have installed an old version of this GWC (it doesn't check if Skulls is updated).


WARNING
I STRONGLY SUGGEST you use a separate sub-domain for the GWC such as http://gwc.your-site.com/skulls.php instead of http://your-site.com/skulls.php
So if you ever want to take it down all you will have to do is delete the sub-domain.

P2P clients have long memories, it could take a long time (maybe forever) until the url of your GWC has vanished.
If you don't follow this warning you will still be hit by GWC requests even after you have stopped running the GWC.


INSTALLATION PROCEDURE
1. Send all files to your web server
2. Go on http://gwc.your-site.com/admin/test.php in your browser
3. Then open vars.php with notepad and change the settings that you see in step 2
4. In vars.php change also MAINTAINER_EMAIL (optional), MAINTAINER_WEBSITE (optional) and MAINTAINER_NICK
5. Put the full url of your GWC in CACHE_URL inside vars.php, example: http://gwc.your-site.com/skulls.php (optional but recommended)
6. Send updated vars.php to your web server
7. Go on http://gwc.your-site.com/skulls.php in your browser, if you don't see any error you are OK
Note: You must be sure that there aren't any problems before go to the step 8
8. Submit the url of skulls.php here: http://skulls.sourceforge.net/submit.php

If you need more help ask here: http://sourceforge.net/p/skulls/discussion/


UPDATE PROCEDURE
1. Delete old skulls.php in your web server
2. Send updated files inside admin directory to your web server
3. Go on http://gwc.your-site.com/admin/update.php in your browser
4. Send all updated files to your web server (Copy in the new vars.php the things that you have changed in the old vars.php)
5. Go on http://gwc.your-site.com/skulls.php in your browser, if you don't see any error you are OK

If you need more help ask here: http://sourceforge.net/p/skulls/discussion/
