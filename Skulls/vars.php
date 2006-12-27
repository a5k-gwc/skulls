<?php
$ENABLED =							1;

define( "FSOCKOPEN",				1 );	// Disable ONLY if the server have FSOCKOPEN disabled, use admin/test.php to check
define( "CONTENT_TYPE_WORKAROUND",	0 );	// Use admin/test.php to know the right value

define( "STATS_ENABLED",			1 );	// Enable collecting statistics
define( "KICK_START_ENABLED",		0 );	// KickStart should be DISABLED after populating the webcache

define( "LOG_MAJOR_ERRORS",			1 );	// Enable logging of major errors
define( "LOG_MINOR_ERRORS",			0 );	// Enable logging of minor errors (ONLY for debugging)

define( "MAX_HOSTS",				30 );	// Maximum number of host stored for EACH network (If there are 2 networks and this value is 30 then 30 x 2 = 60 - Setting this value too high DECREASE SPEED of the webcache)
define( "MAX_HOSTS_OUT",			30 );	// Maximum number of host sent in each request (By setting this value too high you can WASTE BANDWIDTH, by setting too low you can increase the number of requests)

define( "MAX_CACHES",				50 );	// Maximum number of cache stored for ALL networks (Setting this value too high DECREASE SPEED of the webcache)
define( "MAX_CACHES_OUT",			10 );	// Maximum number of cache sent in each request (By setting this value too high you can WASTE BANDWIDTH, by setting too low you can increase the number of requests)

define( "RECHECK_CACHES",			24 );	// Hours to recheck a good cache
define( "TIMEOUT",					20 );	// Socket time out for fsockopen

define( "DATA_DIR", "data" );				// Directory where data files are stored (you should use relative path, you can leave it as is by default)

define( "MAINTAINER_NICK", "your nickname here" );	// Your nick
// The following field is optional (you can leave it as is by default to doesn't show it)
define( "MAINTAINER_EMAIL", "name AT server DOT com" );	// Your e-mail in that format "name AT server DOT com". Example: pippo@excite.it => pippo AT excite DOT it


// You can add or remove any network, but after you changed them you must delete last_action.dat in data directory to initialize the changes.
// By default gnutella is disabled and gnutella2 is enabled.

//$SUPPORTED_NETWORKS[] = "Gnutella";
$SUPPORTED_NETWORKS[] = "Gnutella2";

// Note -> The name of the network can't contains these characters:  | -
?>