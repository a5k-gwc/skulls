<?php
$ENABLED				= 1;

$STATFILE_ENABLED		= 1;
$PING_WEBCACHES			= 1;	// Disable ONLY if the server have FSOCKOPEN disabled
$KICK_START_ENABLED		= 1;	// KickStart should be DISABLED after populating the webcache

$LOG_ENABLED			= 0;	// Enable ONLY for debugging

$MAX_HOSTS				= 20;	// Maximum number of host stored for EACH network
$MAX_HOSTS_OUT			= 20;	// Maximum number of host sent in each request

$MAX_CACHES				= 35;	// Maximum number of cache stored for ALL networks
$MAX_CACHES_OUT			= 20;	// Maximum number of cache sent in each request

$TIMEOUT				= 20;	// Sockets time out
?>