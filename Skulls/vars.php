<?php
		/* You must read readme.txt in the admin directory to configure properly */

define( "ENABLED",					1 );

// It may happen that the cache is reachable from more then one address (this uselessly increase the cache usage), to avoid this you must put the address that you want use for the cache below.
// IMPORTANT: Check carefully that it is the correct url
define( "CACHE_URL",				"" );	// Optional but recommended - The full url of the cache, example: http://gwc.your-site.com/skulls.php

define( "FSOCKOPEN",				1 );	// Disable ONLY if the server have FSOCKOPEN disabled, use admin/test.php to check
define( "CONTENT_TYPE_WORKAROUND",	0 );	// Use admin/test.php to know the right value

define( "MAINTAINER_NICK", "your nickname here" );				// Your nick
define( "MAINTAINER_EMAIL", "name AT server DOT com" );			// Optional - Your e-mail in that format "name AT server DOT com". Example: pippo@excite.it => pippo AT excite DOT it
define( "MAINTAINER_WEBSITE", "http://www.your-site.com/" );	// Optional - The address of your website (It isn't the url of the cache)

define( "STATS_ENABLED",			1 );	// Enable collecting statistics
define( "OPTIMIZED_STATS",			1 );	// It works only if the the version of PHP is higher than 4.0.0
define( "KICK_START_ENABLED",		0 );	// KickStart should be DISABLED after populating the webcache

define( "LOG_MAJOR_ERRORS",			1 );	// Enable logging of major errors
define( "LOG_MINOR_ERRORS",			0 );	// Enable logging of minor errors (ONLY for debugging)

define( "MAX_HOSTS",				30 );	// Maximum number of host stored for EACH network (If there are 2 networks and this value is 30 then 30 x 2 = 60 - Setting this value too high DECREASE SPEED of the webcache)
define( "MAX_HOSTS_OUT",			30 );	// Maximum number of host sent in each request (By setting this value too high you can WASTE BANDWIDTH, by setting too low you can increase the number of requests)

define( "MAX_CACHES",				50 );	// Maximum number of cache stored for ALL networks (Setting this value too high DECREASE SPEED of the webcache)
define( "MAX_CACHES_OUT",			10 );	// Maximum number of cache sent in each request (By setting this value too high you can WASTE BANDWIDTH, by setting too low you can increase the number of requests)
define( "MAX_UDP_CACHES_OUT",		5 );

define( "RECHECK_CACHES",			24 );	// Hours to recheck a good cache
define( "TIMEOUT",					20 );	// Socket time out for fsockopen

define( "DATA_DIR", "data" );				// Directory where data files are stored (you should use relative path, you can leave it as is by default)

$footer = "";

// You can add or remove any network, but after you changed them you must delete last_action.dat in data directory to initialize the changes.
// By default gnutella is disabled and gnutella2 is enabled.
// Note -> The name of the network can't contains these characters:  | -

//$SUPPORTED_NETWORKS[] = "Gnutella";
$SUPPORTED_NETWORKS[] = "Gnutella2";
$SUPPORTED_NETWORKS[] = "MUTE";

// UDP (Currently it is incomplete)
// 0: Disabled, 1: Enabled
$UDP["uhc"] = 0;
$UDP["ukhl"] = 0;

?>