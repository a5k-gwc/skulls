<?php
//  vars.php - Settings
//
//  Copyright © 2005-2008, 2015-2016  ale5000
//  You must read readme.txt in the admin directory to configure this properly.

define( 'ENABLED',					1 );

// It may happen that a GWC is reachable from more then one address (this uselessly increase the GWC usage), to avoid this you must put the address that you want use for the cache below.
// IMPORTANT: Check carefully that it is the correct url
define( 'CACHE_URL',				'' );	// Optional but recommended - The full url of your GWC, example: http://gwc.your-site.com/skulls.php

define( 'MAINTAINER_NICK', 'your nickname here' );			// Your nickname
define( 'MAINTAINER_EMAIL', 'name AT server DOT com' );		// Optional - Your e-mail in the format 'name AT server DOT com' to avoid spam. Example: ernest@gmail.com => ernest AT gmail DOT com
define( 'MAINTAINER_WEBSITE', 'http://' );					// Optional - The url of your website (it isn't the url of your GWC), example: http://www.your-site.com/

define( 'STATS_ENABLED',			1 );	// Enable collecting statistics
define( 'STATS_FOR_BAD_CLIENTS',	0 );	// Collect statistics also for requests from bad clients (NOTE: This is only for debugging, usually it should be kept disabled since it increase server load)
define( 'OPTIMIZED_STATS',			1 );
define( 'KICK_START_ENABLED',		0 );	// KickStart should be DISABLED after populating the webcache

define( 'LOG_MAJOR_ERRORS',			1 );	// Enable logging of major errors
define( 'LOG_MINOR_ERRORS',			0 );	// Enable logging of minor errors (NOTE: This is only for debugging, usually it should be kept disabled since it increase server load)

define( 'MAX_HOSTS',				100 );	// Maximum number of host stored for EACH network (If there are 3 networks enabled and this value is 100 then 100 x 3 = 300 - By setting this value too high you DECREASE the SPEED of the gwc)
define( 'MAX_HOSTS_OUT',			50 );	// Maximum number of host sent in each request (By setting this value too high you WASTE MORE BANDWIDTH, by setting it too low you could increase the number of requests needed)

define( 'MAX_CACHES',				50 );	// Maximum number of cache stored for ALL networks (This value is used mainly for normal GWCs but also for UDP ones, so if the value is 50 then 50 x 2 = 100 - By setting this value too high you DECREASE the SPEED of the gwc)
define( 'MAX_CACHES_OUT',			15 );	// Maximum number of cache sent in each request (By setting this value too high you WASTE MORE BANDWIDTH, by setting it too low you could increase the number of requests needed)
define( 'MAX_UDP_CACHES_OUT',		5 );

define( 'RECHECK_CACHES',			24 );	// Hours to recheck a good cache
define( 'CONNECT_TIMEOUT',			10 );	// Socket connection timeout
define( 'TIMEOUT',					8 );	// Socket timeout

define('VERIFY_HOSTS', true);
define('ENABLE_URL_SUBMIT', true);  // Enable the submission of alternative GWCs, if it is disabled in addition of rejecting url submissions it will not return any previous url in queries although they will be still visible in the web interface

define('DATA_DIR_PATH', 'data');  // The directory where data files will be stored, it is a relative path. You can also leave it as is by default
define('DIR_FLAGS', 0751);        // Permission flags to set when creating new folders. You can also leave it as is by default


/***  SECURITY  ***/
define('USE_GWC_BLOCKLIST', true);


/***  SERVER RELATED SETTINGS  ***/
define('FSOCK_BASE', true);  // Disable ONLY if the server have FSOCKOPEN disabled, use admin/test.php to check
define('FSOCK_FULL', true);  // Use admin/test.php to check
define('CONTENT_TYPE_WORKAROUND', false);  // Use admin/test.php to know the right value

define('USING_CLOUDFLARE_CDN', false);  // Enable this option only if CloudFlare is configured and enabled on the DNS of your site
define('USING_OPENSHIFT_HOSTING', false);
define('TRUST_X_REMOTE_ADDR_FROM_LOCALHOST', false);  // Do NOT enable this option if it isn't strictly needed on your server. This may be needed on some servers like SourceForge
define('TRUST_X_CLIENT_IP_FROM_LOCALHOST', false);    // Do NOT enable this option if it isn't strictly needed on your server. This may be needed on some servers like OpenShift


/***  BLOCKLIST DISTRIBUTION  ***/
// To enable the blocklist download you need to install the Add-on
define('BL_SPEED_LIMIT', 128);  // Rate limit for EACH blocklist download, expressed in KB/s. Range: 16-2048 (must be multiple of 8)


/***  MISC  ***/
// Here you can set what you want to display at the top and/or at the bottom of the page, it can be text or html
$header = '';
$footer = '';


/***  NETWORKS LIST  ***/
/*
	You can add or remove any network you want. To disable a network just add the // at the start of the line, to enable it just do the opposite.
	The name of the network can't contains these two characters: | -

	Note: Enabling the Gnutella and/or Foxy network can increase a lot the number of requests, so do NOT enable them if your server can't handle the load.
	IMPORTANT: Additionally the Foxy servents tend to over-query the GWebCache, so think carefully before enabling this.
*/

$SUPPORTED_NETWORKS[] = 'Gnutella2';
//$SUPPORTED_NETWORKS[] = 'Gnutella';
$SUPPORTED_NETWORKS[] = 'MUTE';
$SUPPORTED_NETWORKS[] = 'ANtsNet';
$SUPPORTED_NETWORKS[] = 'Pastella';
//$SUPPORTED_NETWORKS[] = 'Kad';
//$SUPPORTED_NETWORKS[] = 'Foxy';
?>