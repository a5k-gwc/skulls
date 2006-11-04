<?php
$ENABLED =							1;

define( "FSOCKOPEN",				1 );	// Disable ONLY if the server have FSOCKOPEN disabled, use admin/test.php to check
define( "CONTENT_TYPE_WORKAROUND",	0 );	// Use admin/test.php to know the right value

define( "STATS_ENABLED",			1 );
define( "KICK_START_ENABLED",		0 );	// KickStart should be DISABLED after populating the webcache

define( "LOG_MAJOR_ERRORS",			1 );	// Enable logging of major errors
define( "LOG_MINOR_ERRORS",			0 );	// Enable logging of minor errors (ONLY for debugging)

define( "MAX_HOSTS",				25 );	// Maximum number of host stored for EACH network (If there are 2 networks and this value is 25 then 25 x 2 = 50 - Setting this value too high DECREASE SPEED of the webcache)
define( "MAX_HOSTS_OUT",			20 );	// Maximum number of host sent in each request (By setting this value too high you can WASTE BANDWIDTH, by setting too low you can increase the number of requests)

define( "MAX_CACHES",				50 );	// Maximum number of cache stored for ALL networks (Setting this value too high DECREASE SPEED of the webcache)
define( "MAX_CACHES_OUT",			15 );	// Maximum number of cache sent in each request (By setting this value too high you can WASTE BANDWIDTH, by setting too low you can increase the number of requests)

define( "RECHECK_CACHES",			10 );	// Days to recheck a cache
define( "TIMEOUT",					20 );	// Sockets time out

define( "DATA_DIR", "data" );

// The following field is optional
define( "EMAIL", "pippo AT excite DOT it" );	// Your e-mail in that format "name AT server DOT com". Example: pippo@excite.it => pippo AT excite DOT it
?>