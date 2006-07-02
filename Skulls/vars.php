<?php
$ENABLED =						1;

define( "PING_WEBCACHES",		1 );	// Disable ONLY if the server have FSOCKOPEN disabled
define( "STATFILE_ENABLED",		1 );
define( "KICK_START_ENABLED",	0 );	// KickStart should be DISABLED after populating the webcache

define( "LOG_ENABLED",			0 );	// Enable ONLY for debugging

define( "MAX_HOSTS",			30 );	// Maximum number of host stored for EACH network (If there are 2 networks and this value is 30 -> 30 x 2 = 60)
define( "MAX_HOSTS_OUT",		20 );	// Maximum number of host sent in each request

define( "MAX_CACHES",			40 );	// Maximum number of cache stored for ALL networks
define( "MAX_CACHES_OUT",		20 );	// Maximum number of cache sent in each request

$TIMEOUT				= 20;	// Sockets time out

define( "DATA_DIR", "data" );
?>