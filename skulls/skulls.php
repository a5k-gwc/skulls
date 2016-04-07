<?php
//
//  Copyright © 2005-2008, 2015-2016 by ale5000
//  This file is part of Skulls! Multi-Network WebCache.
//
//  Skulls is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Skulls is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with Skulls.  If not, see <http://www.gnu.org/licenses/>.
//

$SUPPORTED_NETWORKS = array();
include './vars.php';

define('NAME', 'Skulls');
define('VENDOR', 'SKLL');										/* Vendor code (four uppercase letters) */
define('SHORT_VER', '0.3.2');									/* Numeric version (without letters) */
define('VER', SHORT_VER.'d');									/* Full version (it can contain letters) */
define('GWC_SITE', 'https://sourceforge.net/projects/skulls/');	/* Official site of this GWC */

define('LICENSE_NAME', 'GPL');
define('LICENSE_VER', '3+');
define('LICENSE_URL', 'http://www.gnu.org/licenses/gpl-3.0.html');

define('MAX_HOST_AGE', 259200);									/* 3 days */
define('RESPONSE_LINES_LIMIT', 64);
define('DEBUG', 0);

function GetMainFileRev()
{
	$main_rev = '$Rev$';
	return trim(substr($main_rev, 1, -1));
}

/* Compression will be enabled later only if needed, otherwise it is just a waste of server resources */
function DisableAutomaticCompression()
{
	$auto_compr = ini_get('zlib.output_compression');
	if(!empty($auto_compr))
		ini_set('zlib.output_compression', '0');
	if(function_exists('apache_setenv'))
		apache_setenv('no-gzip', '1');
}

function IsSecureConnection()
{
	if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		return true;
	if(USING_CLOUDFLARE && isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], '"https"') !== false)
		return true;
	if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
		return true;
	return false;
}

function NormalizePort($secure_http, $port)
{
	if(!$port) return null;
	if($secure_http) { if($port === 443) return null; }
	else { if($port === 80) return null; }

	return ':'.$port;
}

function IsWebInterface()
{
	if(isset($_GET['client']))
		return false;
	if(empty($_SERVER['QUERY_STRING']) || isset($_GET['showinfo']) || isset($_GET['showhosts']) || isset($_GET['showurls'])
	  || isset($_GET['showblocklists']) || isset($_GET['stats']))
		return true;

	$param_count = count($_GET);
	if(isset($_GET['compression'])) $param_count--; if(isset($_GET['data'])) $param_count--; if(isset($_GET['ckattempt'])) $param_count--;
	if($param_count === 0)
		return true;

	return false;
}

if(DEBUG) error_reporting(-1);
DisableAutomaticCompression();
if(function_exists('header_remove')) header_remove('X-Powered-By');
$PHP_SELF = $_SERVER['PHP_SELF'];
define('NETWORKS_COUNT', count($SUPPORTED_NETWORKS));

if(!ENABLED || basename($PHP_SELF) === 'index.php' || NETWORKS_COUNT === 0)
{
	header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	die("ERROR: Service disabled\r\n");
}

$SECURE_HTTP = IsSecureConnection();
$UNRELIABLE_HOST = false;

if(empty($_SERVER['HTTP_HOST']))
{
	$UNRELIABLE_HOST = true;
	if(!IsWebInterface())
	{
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		die("ERROR: Missing Host header\r\n");
	}
	$server_port = (isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : null);
	$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'].NormalizePort($SECURE_HTTP, $server_port); unset($server_port);
}

function SanitizeHeaderValue($val)
{
	if(strlen($val) > 1024) return null;
	return str_replace(array(':', '/', '\\', '"', "\r", "\n", "\0"), array('%3A', '%2F'), $val);
}

$MY_URL = ($SECURE_HTTP? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$PHP_SELF;  /* HTTP_HOST already contains port if needed */
if(CACHE_URL !== $MY_URL && CACHE_URL !== "" && !$UNRELIABLE_HOST)
{
	header($_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently');
	header('Location: '.CACHE_URL.(empty($_SERVER['QUERY_STRING'])? "" : '?'.SanitizeHeaderValue($_SERVER['QUERY_STRING'])));
	die;
}

define('STATS_OTHER',   0);
define('STATS_UPD',     1);
define('STATS_UPD_BAD', 2);
define('STATS_BLOCKED', 3);

function GetMicrotime()
{
	list($usec, $sec) = explode(' ', microtime(), 2);
	return (float)$usec + (float)$sec;
}

function LoadLib($name, $file_name = null)
{
	return extension_loaded($name) || (function_exists('dl') && @dl(((PHP_SHLIB_SUFFIX === 'dll')? 'php_' : "").($file_name? $file_name : $name).'.'.PHP_SHLIB_SUFFIX));
}

function IsFakeClient(&$vendor, $ver, $ua)
{
	/* Block empty User-Agent, User-Agent without version and other rip-offs */
	if($vendor === 'RAZA')
	{
		if($ua === "" || $ua === 'Shareaza' || strpos($ua, 'ShareazaPRO') === 0 || strpos($ua, 'Shareaza PRO') === 0 || strpos($ua, 'dianlei') === 0 || strpos($ua, 'Python-urllib/') === 0)
			return true;
		if(($ver === '1.0.0.0' || $ver === '3.0.0.0') && strpos($ua, 'Shareaza ') === 0)
			return true;
	}
	/* Block empty User-Agent and other rip-offs */
	elseif($vendor === 'LIME')
	{
		if($ua === "" || $ver === '1.1.1.6')
			return true;
	}
	elseif($vendor === 'TEST')
	{
		/* Block fake Cabos */
		if(strpos($ua, 'Cabos/') !== false)  /* Cabos usually use LIME vendor code, this seems fake.  UA: LimeWire/4.12.11 (Cabos/0.7.2) */
			return true;
	}
	return false;
}

function RunHttpOptionsMethod($CORS = false)
{
	header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
	if($CORS)
	{
		header('Access-Control-Allow-Methods: GET,OPTIONS');
		header('Access-Control-Max-Age: 43200');
	}
	header('Allow: GET,HEAD,POST,OPTIONS'); header('Content-Length: 0'); die;
}

/* Normalize and validate identity */
function ValidateIdentity($method, &$vendor, &$ver, &$ua, $net, &$detected_net, $CORS)
{
	if(strpos($_SERVER['SERVER_PROTOCOL'], 'HTTP/') !== 0) { header('HTTP/1.0 501 Not Implemented'); header('Content-Length: 0'); die; }
	if($CORS) header('Access-Control-Allow-Origin: *');
	if($method === 'OPTIONS') RunHttpOptionsMethod($CORS);
	if($CORS? $method !== 'GET' : $method !== 'GET' && $method !== 'POST' && $method !== 'HEAD') { header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed'); header('Allow: GET,HEAD,POST,OPTIONS'); header('Content-Length: 0'); die; }

	if($vendor === 'RAZA')
	{
		if(strpos($ua, 'Shareaza ') !== 0) $vendor = 'RAZM';  /* Change vendor code of mod versions */
	}
	elseif($vendor === 'LIME')
	{
		if(strpos($ua, 'Cabos/') !== false) { $vendor = 'CABO'; $ver = substr($ua, strpos($ua, 'Cabos/')+6, -1); }
		elseif(strpos($ua, 'LimeWire/') !== 0 || (float)$ver >= 5.7) $vendor = 'LIMM';  /* Change vendor code of mod versions */
	}
	elseif($vendor === 'MUTE')  /* There are MUTE (MUTE network client) and Mutella (Gnutella network client), both identify themselves as MUTE */
	{
		if(strpos($ua, 'Mutella') === 0)
		{
			if($net === null) $detected_net = 'gnutella';
			$vendor = 'MTLL';
		}
		else
		{
			if($net === null) $detected_net = 'mute';  /* Changed network parameter for MUTE clients to prevent leakage on other networks */
			if(strpos($ver, 'komm_') === 0)
			{
				$vendor = 'KOMM';
				$ver = substr($ver, 5);
				if(empty($ua) && function_exists('getallheaders'))  /* Workaround for incorrectly named header */
				{ $headers = getallheaders(); if($headers !== false && isset($headers['user_agent'])) $ua = $headers['user_agent']; }
			}
			elseif(strpos($ver, 'MFC_') === 0)
			{
				$vendor = 'MMFC';
				$ver = substr($ver, 4);
			}
			elseif(strpos($ver, '_') !== false)
				$vendor = 'MUTG';
		}
	}
	elseif($vendor === 'ANTSP2P')
	{
		$vendor = 'ANTS';  /* Fix the not-standard vendor length */
		if(strpos($ver, 'beta') === 0) $ver = substr($ver, 4).' beta';
	}
	elseif($vendor === 'FOXY')
	{
		if($detected_net !== 'foxy') return false;  /* Foxy clients use Foxy network, block other networks here (it shouldn't be needed but just in case) */
	}

	/* Version missing, vendor missing or wrong length */
	if($ver === "" || strlen($vendor) !== 4) return false;

	return true;
}

function VerifyUserAgent($ua)
{
	/* Block Googlebot and perl from performing queries */
	if(strpos($ua, 'Googlebot/') !== false || strpos($ua, 'libwww-perl') === 0) return false;

	if(strpos($ua, 'Mozilla') !== false)
	{
		/* Foxy clients and some very old gnutella/gnutella2 clients use this User-Agent */
		if($ua === 'Mozilla/4.0') return true;

		/* Block Bingbot and browsers from performing queries */
		if(strpos($ua, 'bingbot/') !== false || strpos($ua, ' MSIE ') !== false || strpos($ua, 'Trident/') !== false || strpos($ua, 'Firefox/') !== false) return false;
		/* Block black hat bots */
		if(strpos($ua, 'Mozilla/') !== 0) return false; $pos_par1 = strpos($ua, '('); $pos_par2 = strpos($ua, ')');
		if($pos_par1 === false || $pos_par2 === false || $pos_par2 < $pos_par1) return false;
	}

	return true;
}

function VerifyUserAgentWeb($ua, $block_search_enine = true)
{
	if(strpos($ua, 'masscan/') === 0 || strpos($ua, 'libwww-perl') === 0) return false;
	if($block_search_enine && (strpos($ua, 'Googlebot/') !== false || strpos($ua, 'bingbot/') !== false)) return false;

	if(strpos($ua, 'Mozilla') !== false)
	{
		/* Block black hat bots */
		if(strpos($ua, 'Mozilla/') !== 0) return false; $pos_par1 = strpos($ua, '('); $pos_par2 = strpos($ua, ')');
		if($pos_par1 === false || $pos_par2 === false || $pos_par2 < $pos_par1) return false;
	}

	return true;
}

function VerifyVersion($client, $version)
{
	/* Block some old versions */
	$float_ver = (float)$version;
	switch($client)
	{
		case 'RAZA':
			if($float_ver < 2.3)
				return false;
			break;
	}

	return true;
}

function ValidateIdentityWeb($method, $ua)
{
	/* Block port scanner and perl */
	if(strpos($ua, 'masscan/') === 0 || strpos($ua, 'libwww-perl') === 0) return false;

	if(strpos($_SERVER['SERVER_PROTOCOL'], 'HTTP/') !== 0) { header('HTTP/1.0 501 Not Implemented'); header('Content-Length: 0'); die; }
	if($method === 'OPTIONS') RunHttpOptionsMethod();
	if($method !== 'GET' && $method !== 'POST' && $method !== 'HEAD') { header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed'); header('Allow: GET,HEAD,POST,OPTIONS'); header('Content-Length: 0'); die; }

	return true;
}

function CanonicalizeURL(&$full_url)
{
	/* $_GET parameters are already "urldecoded" by PHP, so do NOT urldecode again */
	if(DEBUG) echo 'D|update|URL received: ',$full_url,"\r\n";

	if(strpos($full_url, '://') !== false)
	{
		list($scheme, $url) = explode('://', $full_url, 2);
		$scheme = strtolower($scheme);
		$secure_http = ($scheme === 'https');

		/* Drop everything after "?" */
		if(strpos($url, '?') !== false)
			list($url, ) = explode('?', $url, 2);
		/* Drop everything after "#" */
		if(strpos($url, '#') !== false)
			list($url, ) = explode('#', $url, 2);

		/* Separate host from path */
		if(strpos($url, '/') !== false)
			list($host, $path) = explode('/', $url, 2);
		else
			{$host = $url; $path = "";}
		$path = '/'.$path;

		/* Remove dots and slashes at the end of $path */
		$end_slash = false;
		$path_len = strlen($path);
		while( $path_len-- > 0 )
		{
			if($path[$path_len] === '/')
				$end_slash = true;
			elseif($path[$path_len] === '.');
			else
				break;
		}
		$path_len++;
		$path = substr($path, 0, $path_len);

		if($path_len > 4)
		{
			$ext = substr($path, -4);
			if($ext === '.php' || $ext === '.cgi' || $ext === '.asp' || $ext === '.cfm' || $ext === '.jsp')
			{
				$end_slash = false;  /* If we can be sure it is a file then we can safely strip the slash */

				$last_slash = strrpos($path, '/'); if($last_slash === false) $last_slash = 0;
				if( strpos($path, '/index', $last_slash) === $path_len - 10 )
					$path = substr($path, 0, -9);  /* Strip index.php, index.asp, etc. */
			}
			elseif($ext === '.htm' || substr($path, -5) === '.html')
				return false;  /* Block static pages */
			elseif(strpos($path, '.php/') !== false)
				return false;  /* Block some erroneous url rewrites that make it available from infinite urls like this: http://www.domain.com/gwc.php/everything_is_allowed_here */
		}
		if($end_slash)  /* Add slash only if there was before */
			$path .= '/';

		if(strpos($host, ':') !== false)
			{ list($host_name, $host_port) = explode(':', $host, 2); if(!ctype_digit($host_port)) return false; $host_port = (int)$host_port; }
		else
			{ $host_name = $host; $host_port = ($secure_http? 443 : 80); }
		$host_name = strtolower(trim($host_name));

		/* ToDO: Verify port */
		/* ToDO: Remove dot at the end of hostname if present */

		if(strpos($host_name, '.xn--') !== false || strpos($host_name, 'xn--') === 0)
			return false;  /* Block already IDN encoded domains, URLs must be submitted in the original form and IDN encoded only for querying them */
		if(substr($host_name, -9) === '.nyud.net' || substr($host_name, -10) === '.nyucd.net')
			return false;  /* Block Coral Content Distribution Network */

		$full_url = $scheme.'://'.$host_name.NormalizePort($secure_http, $host_port).$path;
	}
	else
	{
		$cache_length = strlen($full_url);
		if(substr($full_url, $cache_length-1) === '/')
			$full_url = substr($full_url, 0, $cache_length-1);
	}

	if(DEBUG) echo 'D|update|URL cleaned: ',$full_url,"\r\n";

	return true;
}

function NetsToString()
{
	global $SUPPORTED_NETWORKS;
	$nets = "";

	for( $i=0; $i < NETWORKS_COUNT; $i++ )
	{
		if($i) $nets .= '-';
		$nets .= $SUPPORTED_NETWORKS[$i];
	}
	return $nets;
}

function RemoveGarbage($value)
{
	$value = str_replace("|", "", $value);
	$value = str_replace("\r", "", $value);
	$value = str_replace("\n", "", $value);
	return str_replace("\0", "", $value);
}

function Pong($detected_pv, $net_list_sent_elsewhere, $multi, $net, $client, $version, $remote_ip)
{
	$send_old_pong = false; $send_pong = false;

	/* v2.x, v4+ */
	if($detected_pv >= 2 && $detected_pv !== 3) $send_pong = true;
	/* v1.x, v3.x */
	elseif($detected_pv >= 1) $send_old_pong = true;
	/* v0 - if the spec version isn't clear we send both pong */
	else { $send_old_pong = true; $send_pong = true; }

	if($send_old_pong)
		echo 'PONG ',NAME,' ',VER,"\r\n";
	if($send_pong)
	{
		echo 'I|pong|',NAME,' ',VER;
		if(!$net_list_sent_elsewhere)
		{
			if($net === 'gnutella2' && !$multi)
			{
				if($client === 'TEST' && strpos($version, 'Bazooka') === 0)
					echo '|gnutella2||COMPAT';	/* Workaround for compatibility with Bazooka (it expect only gnutella2 instead of a supported network list and chokes on the rest) */
				elseif($client === 'GCII')
					echo '|||COMPAT';			/* Workaround for compatibility with PHPGnuCacheII (it expects our url instead of a supported network list, keep it empty is also fine) */
			}

			echo '|',strtolower(NetsToString());
		}
		echo "\r\n";
	}
}

function Support($support, $supported_networks)
{
	if($support > 1)
	{
		echo 'I|networks';
		for($i=0; $i<NETWORKS_COUNT; $i++)
			echo '|',strtolower($supported_networks[$i]);
		echo "\r\n";
	}
	else
		for($i=0; $i<NETWORKS_COUNT; $i++)
			echo 'I|support|',strtolower($supported_networks[$i]),"\r\n";
}

function CheckNetwork($supported_networks, $net)
{
	$net = strtolower($net);
	for($i=0; $i<NETWORKS_COUNT; $i++)
		if(strtolower($supported_networks[$i]) == $net)
			return TRUE;

	return FALSE;
}

function CheckNetworkString($supported_networks, $nets, $multi = TRUE)
{
	if( $multi && strpos($nets, "-") > -1 )
	{
		$nets = explode("-", $nets);
		$nets_count = count($nets);
		for( $i = 0; $i < $nets_count; $i++ )
			if( CheckNetwork($supported_networks, $nets[$i]) )
				return TRUE;
	}
	else
	{
		if( CheckNetwork($supported_networks, $nets) )
			return TRUE;
	}

	if(LOG_MINOR_ERRORS)
		Logging("unsupported-nets");

	return FALSE;
}

function TimeSinceSubmissionInSeconds($now, $time_of_submission, $offset)
{
	$time_of_submission = trim($time_of_submission);
	return $now - ( strtotime($time_of_submission) + $offset );	// GMT
}

function IsValidPrivateIP($ip)
{
	if(ip2long($ip) === false) return false; $ip_array = explode('.', $ip, 4);
	return ($ip_array[0] === '10' || $ip_array[0] === '192' && $ip_array[1] === '168' || $ip_array[0] === '172' && $ip_array[1] >= 16 && $ip_array[1] <= 31);
}

function ValidateRemoteIP($ip, &$is_localhost)
{
	$is_localhost = false;

	if(strpos($ip, ':') !== false) { if($ip === '::1') $is_localhost = true; return true; }  /* IPv6 */

	$ip_array = explode('.', $ip, 4);
	/* Private addresses */
	if($ip_array[0] === '10' || $ip_array[0] === '172' && $ip_array[1] >= 16 && $ip_array[1] <= 31 || $ip_array[0] === '192' && $ip_array[1] === '168') return false;
	/* Loopback */
	if($ip_array[0] === '127') $is_localhost = true;

	return true;
}

function ValidateIP($ip, $reject_lan_IPs = true)
{
	$long_ip = ip2long($ip); if($long_ip === false) return false;

	// http://www.rfc-editor.org/rfc/rfc3330.txt
	$ip_array = explode('.', $ip, 4);
	if($ip_array[0] === '0' || $ip_array[0] === '127') return false;			/* "This" network and Loopback */

	if($reject_lan_IPs)
		if( $ip_array[0] === '10'												/* Private addresses */
		 || $ip_array[0] === '100' && $ip_array[1] >= 64 && $ip_array[1] <= 127	/* Carrier Grade NAT addresses */
		 || $ip_array[0] === '169' && $ip_array[1] === '254'					/* Link-local addresses */
		 || $ip_array[0] === '172' && $ip_array[1] >= 16 && $ip_array[1] <= 31	/* Private addresses */
		 || $ip_array[0] === '192' && $ip_array[1] === '168'					/* Private addresses */
		)
			return false;

	return $ip === long2ip((float)$long_ip);  /* The float cast will prevent getting wrong IPs on some systems */
}

function ValidatePort($port, $net)
{
	if(!ctype_digit($port) || $port < 1 || $port > 65535) return false;
	if($net === 'gnutella') { if($port === '7001' || $port === '27016') return false; }
	elseif($net === 'mute') { if($port === '6346') return false; }
	return true;
}

function ValidateHost($host, $remote_ip, $net, $suppress_log = false)
{
	list($ip, $port) = explode(':', $host, 2);
	if($ip !== $remote_ip || !ValidateIP($ip)) { if(LOG_MINOR_ERRORS && !$suppress_log) Logging('invalid-hosts'); return false; }
	if(!ValidatePort($port, $net)) { if(LOG_MINOR_ERRORS && !$suppress_log) Logging('invalid-host-ports'); return false; }
	return true;
}

function CheckURLValidity($cache)
{
	if(strlen($cache) > 10)
		if(substr($cache, 0, 7) == "http://" || substr($cache, 0, 8) == "https://")
			if( !(strpos($cache, "?") > -1 || strpos($cache, "&") > -1 || strpos($cache, "#") > -1) )
				return true;

	if(LOG_MINOR_ERRORS)
		Logging("invalid-urls");

	return false;
}

function CheckUDPURLValidity($cache)
{
	if(strlen($cache) > 6 && strpos($cache, 'udp:') === 0)
		return true;

	if(LOG_MINOR_ERRORS)
		Logging("invalid-udp-urls");

	return false;
}

function CheckBlockedDomain($domain)
{
	/* Malicious */
	if($domain === 'udp-ho'.'st-cache.com')
		return true;

	/* Duplicates */
	if( /* Duplicates of mcache.mccarragher.org */
		$domain === 'mccarragher.org' || $domain === 'news.mccarragher.org'
		|| $domain === 'gallaxial.com' || $domain === 'www.gallaxial.com' || $domain === 'news.gallaxial.com'
		|| $domain === 'spacesst.com' || $domain === 'www.spacesst.com' || $domain === 'news.spacesst.com'
	)
		return true;

	return false;
}

/* When bugs of GWCs are fixed, ask on http://sourceforge.net/p/skulls/discussion/ and the GWCs will be unlocked */
function CheckBlockedGWC($gwc_url)
{
	$gwc_url = strtolower($gwc_url);
	if(
		$gwc_url === 'http://cache.trillinux.org/g2/bazooka.php'	/* Bugged - Return hosts with negative age */
		|| $gwc_url === 'http://fascination77.free.fr/cachechu/'	/* Bugged - Fatal error, call to undefined function: stream_socket_client() */
		|| $gwc_url === 'http://p2p.findclan.org/'					/* Bugged/bad - Completely empty */
		|| $gwc_url === 'http://cache-doxu.rhcloud.com/'			/* Bugged/bad - No hosts */
		|| $gwc_url === 'http://59.175.238.8:9030/beacon2/gwc.php'	/* Bugged/bad - Partially broken */
		|| $gwc_url === 'http://peerproject.org/webcache/'			/* Duplicate URL */
		|| $gwc_url === 'http://cache.peernix.com/gwc.php'			/* No longer exist */
		|| $gwc_url === 'http://gwc.gofoxy.net:2108/gwc/cgi-bin/fc'	/* No longer exist */
		|| $gwc_url === 'http://gwc.iblinx.com:2108/gwc/cgi-bin/fc'	/* No longer exist */
		|| $gwc_url === 'http://gamagic.com:2108/gwc/cgi-bin/fc'	/* No longer exist */
		|| $gwc_url === 'http://0517play.com:4400/gwc/cgi-bin/fc'	/* No longer exist */
	)
		return true;

	return false;
}

function CleanFailedUrls()
{
	ignore_user_abort(true);
	set_time_limit(120);

	$failed_urls_file = file(DATA_DIR.'/failed_urls.dat');
	$file_count = count($failed_urls_file);
	$file = fopen(DATA_DIR.'/failed_urls.dat', 'wb');
	flock($file, LOCK_EX);

	$now = time();
	$offset = date('Z');
	for($i = 0; $i < $file_count; $i++)
	{
		$failed_urls_file[$i] = rtrim($failed_urls_file[$i]);
		list(, $failed_time) = explode('|', $failed_urls_file[$i]);
		$time_diff = $now - (strtotime($failed_time) + $offset);	// GMT
		$time_diff = floor($time_diff / 86400);	// Days

		if($time_diff < 2) fwrite($file, $failed_urls_file[$i]."\r\n");
	}

	flock($file, LOCK_UN);
	fclose($file);
}

function CheckFailedUrl($url)
{
	$file = file(DATA_DIR.'/failed_urls.dat');
	$file_count = count($file);

	for($i = 0, $now = time(), $offset = date('Z'); $i < $file_count; $i++)
	{
		$read = explode('|', $file[$i]);
		if($url == $read[0])
		{
			$read[1] = trim($read[1]);
			$time_diff = $now - (strtotime($read[1]) + $offset);	// GMT
			$time_diff = floor($time_diff / 86400);	// Days

			if($time_diff < 2) return TRUE;
		}
	}

	return FALSE;
}

function AddFailedUrl($url)
{
	$file = fopen(DATA_DIR.'/failed_urls.dat', 'ab');
	flock($file, LOCK_EX);
	fwrite($file, $url.'|'.gmdate('Y/m/d H:i')."\r\n");
	flock($file, LOCK_UN);
	fclose($file);
}

function ReplaceHost($file_path, $line, $this_host, &$host_file, $recover_limit = false)
{
	$new_host_file = implode( "", array_merge( array_slice($host_file, 0, $line), array_slice($host_file, ($recover_limit? $line + 2 : $line + 1)) ) );

	$file = fopen($file_path, 'wb');
	flock($file, LOCK_EX);
	fwrite($file, $new_host_file.$this_host);
	flock($file, LOCK_UN);
	fclose($file);
}

function ReplaceCache($file_path, $line, &$cache_file, $this_alt_gwc)
{
	$new_cache_file = implode( "", array_merge( array_slice($cache_file, 0, $line), array_slice($cache_file, ($line + 1)) ) );

	$file = fopen($file_path, 'wb');
	flock($file, LOCK_EX);
	if($this_alt_gwc !== null)
		fwrite($file, $new_cache_file.$this_alt_gwc);
	else
		fwrite($file, $new_cache_file);
	flock($file, LOCK_UN);
	fclose($file);
}

function cURL_SetOptions($ch, $idn_host, $port)
{
	$headers = array();
	$headers[] = 'Host: '.$idn_host;
	$headers[] = 'Connection: close';
	$headers[] = 'User-Agent: '.NAME.' '.VER;
	if(CACHE_URL !== "") $headers[] = 'X-GWC-URL: '.CACHE_URL;

	if(DEBUG)
	{
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	}

	if(
		!curl_setopt($ch, CURLOPT_PORT, $port)
		|| !curl_setopt($ch, CURLOPT_RETURNTRANSFER, true)
		|| !curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int)CONNECT_TIMEOUT)
		|| !curl_setopt($ch, CURLOPT_TIMEOUT, (int)TIMEOUT)
		|| !curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false)
		|| !curl_setopt($ch, CURLOPT_FORBID_REUSE, true)
		|| !curl_setopt($ch, CURLOPT_FRESH_CONNECT, true)
		|| !curl_setopt($ch, CURLOPT_HTTPHEADER, $headers)
	)
		return false;

	if(defined('CURLOPT_BINARYTRANSFER'))
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);  /* Used only in PHP 5.1.0-5.1.2 */

	return true;
}

function cURL_OnError($ch, $function_name, $initialized = true)
{
	if($initialized)
	{
		if(DEBUG) echo 'D|update|GWC|cURL|Error ',curl_errno($ch),'|',rtrim(curl_error($ch)),"\r\n";
		curl_close($ch);
	}
	return 'ERR|cURL-'.$function_name.'-FAILED';
}

function ConnectionTest()
{
	if(!FSOCK_BASE) return true;

	$fp = @fsockopen('google.com', 80, $errno, $errstr, 5); if($fp === false) return false;
	fclose($fp);

	return true;
}

function PingPort($ip, $port)
{
	if(!FSOCK_FULL) return true;  //ToDO: Add cURL support

	$fp = @fsockopen($ip, $port, $errno, $errstr, 8); if($fp === false) { if(LOG_MINOR_ERRORS) Logging('broken-hosts'); return false; }
	fclose($fp);

	return true;
}

function PingGWC($gwc_url, $query, $net_param = null)
{
	if(!ENABLE_URL_SUBMIT) return 'ERR|DISABLED';
	$our_url = null; $gwc_idn_hostname = false;

	list($gwc_scheme, $gwc_base_url) = explode('://', $gwc_url, 2);
	list($gwc_host, $gwc_path) = explode('/', $gwc_base_url, 2);
	$secure_http = ($gwc_scheme === 'https');
	unset($gwc_scheme, $gwc_base_url);

	if(strpos($gwc_host, ':') !== false)
		{ list($gwc_hostname, $gwc_port) = explode(':', $gwc_host, 2); $gwc_port = (int)$gwc_port; }
	else
		{ $gwc_hostname = $gwc_host; $gwc_port = ($secure_http? 443 : 80); }
	unset($gwc_host);

	/* It needs the PHP Intl extension (bundled version with --enable-intl or PECL) enabled on the server */
	if(function_exists('idn_to_ascii')) $gwc_idn_hostname = idn_to_ascii($gwc_hostname);
	if($gwc_idn_hostname === false) $gwc_idn_hostname = $gwc_hostname;
	$gwc_idn_host = $gwc_idn_hostname.NormalizePort($secure_http, $gwc_port);
	if(DEBUG) echo "\r\nD|update|GWC|HOSTNAME|",$gwc_hostname,"\r\nD|update|GWC|IDN-HOSTNAME|",$gwc_idn_hostname,"\r\nD|update|GWC|SECURE-HTTP|",(int)$secure_http,"\r\n";

	$final_query = $query; if($net_param !== null) $final_query .= '&net='.$net_param;
	$cache_data = null; $pong = ""; $oldpong = ""; $error = ""; $nets_list1 = null;
	if(FSOCK_FULL || (FSOCK_BASE && ($gwc_port === 80 || $gwc_port === 443)))
	{
		$errno = -1; $errstr = "";
		$fp = @fsockopen(($secure_http? 'tls://' : "").$gwc_idn_hostname, $gwc_port, $errno, $errstr, (float)CONNECT_TIMEOUT);
		if($fp === false)
		{
			if(DEBUG) echo 'D|update|GWC|CONN-ERR|',$errno,'|',rtrim($errstr),"\r\n";
			return 'CONN-ERR|'.$errno;
		}
		else
		{
			if(CACHE_URL !== "") $our_url = 'X-GWC-URL: '.CACHE_URL."\r\n";
			$common_headers = "Connection: close\r\nUser-Agent: ".NAME.' '.VER."\r\n".$our_url;
			$out = 'GET /'.$gwc_path.'?'.$final_query.' '.$_SERVER['SERVER_PROTOCOL']."\r\n";
			$out .= 'Host: '.$gwc_idn_host."\r\n".$common_headers."\r\n";
			if(DEBUG) echo "\r\n",rtrim($out),"\r\n";

			if(fwrite($fp, $out) !== strlen($out))
			{
				fclose($fp); if(DEBUG) echo 'D|update|GWC|REQ-ERR',"\r\n";
				return 'ERR|REQ-ERR';
			}
			else
			{
				stream_set_timeout($fp, TIMEOUT);
				$i = 0;
				while($i++ < RESPONSE_LINES_LIMIT)
				{
					$line = fgets($fp, 256);
					if($line === false) break;
					$line = rtrim($line);
					$line_lc = strtolower($line);
					if(DEBUG) echo "\r\n",$i,' ',$line;

					if(substr($line_lc, 0, 7) === 'i|pong|')
						$pong = $line;
					elseif(substr($line_lc, 0, 4) === 'pong')
						$oldpong = $line;
					elseif(substr($line_lc, 0, 11) === 'i|networks|')
						$nets_list1 = substr($line_lc, 11);
					elseif(substr($line_lc, 0, 5) === 'error' || strpos($line, '404 Not Found') !== false || strpos($line, '403 Forbidden') !== false)
						$error .= $line.'-';
					elseif(substr($line_lc, 0, 2) === 'i|' && strpos($line_lc, 'not') !== false && strpos($line_lc, 'supported') !== false)
						$error .= $line.'-';
				}
				fclose($fp);
			}
		}
	}
	elseif(LoadLib('curl'))  /* cURL */
	{
		$gwc_url = ($secure_http? 'https' : 'http').'://'.$gwc_idn_host.'/'.$gwc_path; /* Rewrite url with idn host */

		$ch = curl_init($gwc_url.'?'.$final_query);
		if($ch === false) return cURL_OnError(null, 'init', false);

		if(!cURL_SetOptions($ch, $gwc_idn_host, $gwc_port)) return cURL_OnError($ch, 'setopt');

		$response = curl_exec($ch);
		if($response === false) return cURL_OnError($ch, 'exec');

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($http_code === false) return cURL_OnError($ch, 'getinfo');

		curl_close($ch);

		if(DEBUG) echo 'D|update|GWC|HTTP-CODE|',$http_code,"\r\n";
		if($http_code > 299 || $http_code < 200)
			if($http_code !== 404 && $http_code !== 403)  /* A GWC may return 404 if it is queried with a missing net parameter, we cope with this case later */
				return 'ERR|HTTP-CODE-'.$http_code;

		$i = -1; $lines = explode("\n", $response, RESPONSE_LINES_LIMIT+1); $response = null; $tot_lines = count($lines);
		if($tot_lines === RESPONSE_LINES_LIMIT+1 || rtrim($lines[$tot_lines-1]) === "") { $lines[$tot_lines-1] = null; $tot_lines--; }
		while(++$i < $tot_lines)
		{
			$line = rtrim($lines[$i]);
			$line_lc = strtolower($line);
			if(DEBUG) echo "\r\n",$i+1,' ',$line;

			if(substr($line_lc, 0, 7) === 'i|pong|')
				$pong = $line;
			elseif(substr($line_lc, 0, 4) === 'pong')
				$oldpong = $line;
			elseif(substr($line_lc, 0, 11) === 'i|networks|')
				$nets_list1 = substr($line_lc, 11);
			elseif(substr($line_lc, 0, 5) === 'error')
				$error .= $line.'-';
			elseif(substr($line_lc, 0, 2) === 'i|' && strpos($line_lc, 'not') !== false && strpos($line_lc, 'supported') !== false)
				$error .= $line.'-';
		}
		if($http_code === 404 || $http_code === 403) { $pong = ""; $oldpong = ""; }
	}
	else
		return 'ERR|DISABLED';

	if(!empty($pong))
	{
		$received_data = explode("|", $pong);
		$gwc_name = RemoveGarbage(trim(rawurldecode($received_data[2])));
		$cache_data = 'P|'.$gwc_name;

		if($nets_list1 !== null)
			$nets = RemoveGarbage(str_replace( array('-', '|'), array('%2D', '-'), $nets_list1 ));
		elseif(!empty($received_data[3]) && strpos($received_data[3], 'http') !== 0)
			$nets = RemoveGarbage(strtolower($received_data[3]));
		elseif(strpos($gwc_name, 'GhostWhiteCrab') === 0)  /* On GhostWhiteCrab if the network is gnutella then the networks list is missing :( */
			$nets = "gnutella";
		elseif(strpos($gwc_name, 'PHPGnuCacheII') === 0)   /* Workaround for compatibility with PHPGnuCacheII, it send its own url instead of the networks list */
			$nets = "gnutella2-gnutella";
		elseif($net_param !== null)
			$nets = $net_param;
		elseif(!empty($oldpong))
			$nets = "gnutella2-gnutella";  /* Guessed */
		else
			$nets = "gnutella2";           /* Guessed */

		$cache_data .= '|'.$nets.'|'.$net_param;		// P|Name of the GWC|Networks list|Net parameter needed for query
	}
	elseif(!empty($oldpong))
	{
		$oldpong = RemoveGarbage(trim(rawurldecode(substr($oldpong, 4))));
		$cache_data = 'P|'.$oldpong;

		/* Needed to force v2 spec since they ignore the other ways */
		if(strpos($oldpong, 'Cachechu') === 0 || strpos($oldpong, 'PHPGnuCacheII') === 0)
			if(strpos($query, 'update=1') === false)
				return PingGWC($gwc_url, $query.'&update=1', $net_param);

		if(substr($oldpong, 0, 9) == "MWebCache")
			$nets = "mute";
		elseif($net_param !== null)
			$nets = $net_param;
		elseif( //substr($oldpong, 0, 10) == "perlgcache" ||		// ToDO: Re-verify
			substr($oldpong, 0, 12) == "jumswebcache" ||
			substr($oldpong, 0, 11) == "GWebCache 2" )
			$nets = "gnutella2-gnutella";
		else
			$nets = "gnutella";  /* Guessed */

		$cache_data .= '|'.$nets.'|'.$net_param;		// P|Name of the GWC|Networks list|Net parameter needed for query
	}
	else
	{
		$error = RemoveGarbage($error);
		$cache_data = 'ERR|'.$error;	// ERR|Error name
	}

	return $cache_data;
}

function CheckGWC($cache, $net_param = null, $congestion_check = false)
{
	global $SUPPORTED_NETWORKS;

	if(strpos($cache, '://') > -1)
	{
		$udp = FALSE;
		$query = 'ping=1&multi=1&getnetworks=1&pv=2&client='.VENDOR.'&version='.SHORT_VER.'&cache=1';
		$result = PingGWC($cache, $query, $net_param);  /* $result => P|Name of the GWC|Networks list|Net parameter needed for query   or   ERR|Error name   or   CONN-ERR|Error number */
	}
	else
	{
		$udp = TRUE;
		include './udp.php';
		$result = PingUDP($cache);
	}
	$received_data = explode('|', $result, 4);

	if($received_data[0] === 'ERR' && !$udp)
	{
		$error = strtolower($received_data[1]);
		if(
			strpos($error, "network not supported") !== false
			|| strpos($error, "unsupported network") !== false
			|| strpos($error, "no network") !== false
			|| strpos($error, "net-not-supported") !== false
		)	// Workaround for compatibility with some GWCs using v2 spec
		{	// FOR GWCs DEVELOPERS: If you want to avoid the necessity to make double ping, make your GWC pingable without the network parameter or with the wrong network parameter when there are ping=1 and multi=1
			$result = PingGWC($cache, $query, 'gnutella2');
		}
		elseif( strpos($received_data[1], "access denied by acl") > -1 )
		{
			$query = 'ping=1&multi=1&getnetworks=1&pv=2&client=TEST&version='.VENDOR.'%20'.SHORT_VER.'&cache=1';
			$result = PingGWC($cache, $query, $net_param);
		}
		unset($received_data);
		$received_data = explode('|', $result, 4);
	}
	if(DEBUG) echo "\r\nD|update|GWC|Result|",$result,"\r\n\r\n";

	$cache_data = null;
	if($congestion_check && $received_data[0] === 'CONN-ERR' && !ConnectionTest())
		$cache_data[0] = 'CONGESTION';
	elseif($received_data[0] === 'CONN-ERR' || $received_data[0] === 'ERR' || $received_data[1] === "")
		$cache_data[0] = 'FAIL';
	else
	{
		if(CheckNetworkString($SUPPORTED_NETWORKS, $received_data[2]))
		{
			$cache_data[0] = $received_data[1];
			$cache_data[1] = $received_data[2];
			$cache_data[2] = $received_data[3];
		}
		else
			$cache_data[0] = 'UNSUPPORTED';
	}

	return $cache_data;
}

function ResolveDNS($host_name)
{
	$ip = gethostbyname($host_name.'.');
	if($ip === $host_name || $ip === $host_name.'.') return false;  /* When the function fails on some servers the dot is kept but in others it is removed so check both */
	return $ip;
}

function WriteHostFile($net, $h_ip, $h_port, $h_leaves, $h_max_leaves, $h_uptime, $h_vendor, $h_ver, $h_ua, $h_suspect = 0, $verify_host = true)
{
	$file_path = DATA_DIR.'/hosts-'.$net.'.dat';
	$host_file = file($file_path);
	$file_count = count($host_file);
	$host_exists = FALSE;

	for($i = 0; $i < $file_count; $i++)
	{
		list($time, $read_ip, $h_curr_port) = explode('|', $host_file[$i], 4);
		if($h_ip === $read_ip)
		{
			$host_exists = TRUE;
			break;
		}
	}
	if($h_ver === '5.3.6' && $net === 'gnutella') $h_suspect = $h_suspect + 1;
	$this_host = gmdate('Y/m/d h:i:s A').'|'.$h_ip.'|'.$h_port.'|'.$h_leaves.'|'.$h_max_leaves.'|'.$h_uptime.'|'.RemoveGarbage($h_vendor).'|'.RemoveGarbage($h_ver).'|'.RemoveGarbage($h_ua).'|'.$h_suspect."|||\n";

	if($host_exists)
	{
		$time_diff = time() - (strtotime($time) + date('Z'));	// GMT
		$time_diff = round($time_diff / 3600, 1);	// Hours

		if($time_diff < 6)
			return 0; // Exists
		elseif($h_port !== $h_curr_port && VERIFY_HOSTS && $verify_host && !PingPort($h_ip, $h_port))  /* ToDO: Remove host in this case */
			return 4; // Error, failed verification
		else
		{
			ReplaceHost($file_path, $i, $this_host, $host_file);
			return 1; // Updated timestamp
		}
	}
	else
	{
		if(VERIFY_HOSTS && $verify_host && !PingPort($h_ip, $h_port))
			return 4; // Error, failed verification
		elseif($file_count > MAX_HOSTS || $file_count > 300)
		{
			ReplaceHost($file_path, 0, $this_host, $host_file, true);
			return 3; // OK, pushed old data
		}
		elseif($file_count === MAX_HOSTS)
		{
			ReplaceHost($file_path, 0, $this_host, $host_file);
			return 3; // OK, pushed old data
		}
		else
		{
			$file = fopen($file_path, 'ab');
			flock($file, LOCK_EX);
			fwrite($file, $this_host);
			flock($file, LOCK_UN);
			fclose($file);
			return 2; // OK
		}
	}
}

function WriteCacheFile($file_path, $is_udp, $gwc_url, $client, $version, $is_a_gwc_param, $user_agent)
{
	global $MY_URL;
	$gwc_url = RemoveGarbage($gwc_url);

	if($gwc_url === $MY_URL)  /* It doesn't allow to insert itself in the GWC list */
		return 0;  // Exists
	if(CheckBlockedGWC($gwc_url) || CheckFailedUrl($gwc_url))
		return 4;  // Blocked URL

	$client = RemoveGarbage($client);
	$version = RemoveGarbage($version);
	$cache_file = file($file_path);
	$file_count = count($cache_file);
	$cache_exists = FALSE;

	for($i = 0; $i < $file_count; $i++)
	{
		list($time, /* New specs only */, $gwc_ip, $read, /* Networks */, $net_param,) = explode('|', $cache_file[$i], 7);

		if(strtolower($gwc_url) == strtolower($read))
		{
			$cache_exists = TRUE;
			if($net_param === "") $net_param = null;
			break;
		}
	}
	$this_alt_gwc = null;
	$new_specs_only = '0';
	$temp = $gwc_url; if(!$is_udp) list(,$temp) = explode('://', $temp, 2);
	list($temp,) = explode('/', $temp, 2); list($temp,) = explode(':', $temp, 2);

	if(CheckBlockedDomain($temp)) return 4;  // Blocked URL

	if($cache_exists)
	{
		$time_diff = time() - (strtotime($time) + date('Z'));	// GMT
		$time_diff = floor($time_diff / 3600);	// Hours
		if(RECHECK_CACHES < 12) $recheck_caches = 12; else $recheck_caches = RECHECK_CACHES;

		if($time_diff < $recheck_caches)
			return 0; // Exists
		else
		{
			/* If the DNS resolution fails we assume it is already an IP address */
			$gwc_ip = ResolveDNS($temp); if($gwc_ip === false) { $gwc_ip = $temp; $new_specs_only = '1'; } elseif(strpos($gwc_url, 'https://') === 0) $new_specs_only = '1';
			if(DEBUG) echo 'D|update|GWC|IP|'.RemoveGarbage($gwc_ip)."\r\n";
			if(!ValidateIP($gwc_ip))  return 4;  // Blocked URL

			$cache_data = CheckGWC(($is_udp? 'uhc:' : "").$gwc_url, $net_param, true);

			if($cache_data[0] === 'FAIL')
			{
				AddFailedUrl($gwc_url);
				ReplaceCache($file_path, $i, $cache_file, null);
				return 5; // Ping failed
			}
			elseif($cache_data[0] === 'UNSUPPORTED')
			{
				AddFailedUrl($gwc_url);
				ReplaceCache($file_path, $i, $cache_file, null);
				return 6; // Unsupported network
			}
			elseif($cache_data[0] === 'CONGESTION')
			{
				return 7; // Possible network congestion
			}
			else
			{
				$this_alt_gwc = gmdate('Y/m/d h:i:s A').'|'.$new_specs_only.'|'.$gwc_ip.'|'.$gwc_url.'|'.$cache_data[1].'|'.$cache_data[2].'|'./* $gwc_vendor .*/'|'./* $gwc_version .*/'|'.$cache_data[0].'|'./*gwc_server.*/'|'.$client.'|'.$version.'|'.((int)$is_a_gwc_param).'|'.RemoveGarbage($user_agent)."|\n";
				ReplaceCache($file_path, $i, $cache_file, $this_alt_gwc);
				return 1; // Updated timestamp
			}
		}
	}
	else
	{
		/* If the DNS resolution fails we assume it is already an IP address */
		$gwc_ip = ResolveDNS($temp); if($gwc_ip === false) { $gwc_ip = $temp; $new_specs_only = '1'; } elseif(strpos($gwc_url, 'https://') === 0) $new_specs_only = '1';
		if(DEBUG) echo 'D|update|GWC|IP|'.RemoveGarbage($gwc_ip)."\r\n";
		if(!ValidateIP($gwc_ip))  return 4;  // Blocked URL

		$cache_data = CheckGWC(($is_udp? 'uhc:' : "").$gwc_url);

		if($cache_data[0] === 'FAIL')
		{
			AddFailedUrl($gwc_url);
			return 5; // Ping failed
		}
		elseif($cache_data[0] === 'UNSUPPORTED')
		{
			AddFailedUrl($gwc_url);
			return 6; // Unsupported network
		}
		else
		{
			$this_alt_gwc = gmdate('Y/m/d h:i:s A').'|'.$new_specs_only.'|'.$gwc_ip.'|'.$gwc_url.'|'.$cache_data[1].'|'.$cache_data[2].'|'./* $gwc_vendor .*/'|'./* $gwc_version .*/'|'.$cache_data[0].'|'./*gwc_server.*/'|'.$client.'|'.$version.'|'.((int)$is_a_gwc_param).'|'.RemoveGarbage($user_agent)."|\n";

			if($file_count >= MAX_CACHES || $file_count >= 200)
			{
				ReplaceCache($file_path, 0, $cache_file, $this_alt_gwc);
				return 3; // OK, pushed old data
			}
			else
			{
				$file = fopen($file_path, 'ab');
				flock($file, LOCK_EX);
				fwrite($file, $this_alt_gwc);
				flock($file, LOCK_UN);
				fclose($file);
				return 2; // OK
			}
		}
	}
}


/* Workaround for a bug, some old Shareaza versions doesn't send updates if we don't have any host */
function CheckIfDummyHostIsNeeded($vendor, $ver)
{
	if($vendor === 'RAZA')
	{
		$ver_array = explode('.', $ver, 3);
		if( count($ver_array) === 3 )
			if($ver_array[0] < 2 || ($ver_array[0] === '2' && $ver_array[1] < 6))
				return true;
	}

	return false;
}

function HostFile($net, $age)
{
	$now = time(); $offset = date('Z');
	$host_file = file(DATA_DIR.'/hosts-'.$net.'.dat');
	$count_host = count($host_file);

	if($count_host <= MAX_HOSTS_OUT)
		$max_hosts = $count_host;
	else
		$max_hosts = MAX_HOSTS_OUT;

	for($i = 0; $i < $max_hosts; $i++)
	{
		list($h_age, $h_ip, $h_port,) = explode('|', $host_file[$count_host - 1 - $i], 4);
		$h_age = TimeSinceSubmissionInSeconds($now, $h_age, $offset);
		if($h_age > MAX_HOST_AGE) break;
		echo $h_ip,':',$h_port; if($age) echo '|',$h_age; echo "\r\n";
	}
}

function UrlFile($detected_pv, $net, $age, $client)
{
	$now = time(); $offset = date('Z');
	$cache_file = file(DATA_DIR.'/alt-gwcs.dat');
	$count_cache = count($cache_file);

	for($n = 0, $i = $count_cache - 1; $n < MAX_CACHES_OUT && $i >= 0; $i--)
	{
		list($gwc_age, $new_specs_only,, $cache, $cache_net,) = explode('|', $cache_file[$i], 6);

		$show = FALSE;
		if(strpos($cache_net, '-') > -1)
		{
			$cache_networks = explode('-', $cache_net);
			$cache_nets_count = count($cache_networks);
			for($x=0; $x < $cache_nets_count; $x++)
			{
				if($cache_networks[$x] === $net)
				{
					$show = TRUE;
					break;
				}
			}
		}
		elseif($cache_net === $net)
			$show = TRUE;

		if($show && strpos($cache, '://') > -1)
		{
			if((bool)$new_specs_only && $detected_pv < 2.1) continue;
			echo $cache; if($age) echo '|',TimeSinceSubmissionInSeconds($now, $gwc_age, $offset); echo "\r\n";
			$n++;
		}
	}
}

function Get($detected_pv, $net, $get, $getleaves, $getvendors, $getuptime, $getmaxleaves, $getudp, $client, $add_dummy_host)
{
	$output = "";
	$now = time(); $offset = date('Z');
	$separators = 0;
	if($getmaxleaves) $separators = 5;
	elseif($getuptime) $separators = 4;
	elseif($getvendors) $separators = 3;
	elseif($getleaves) $separators = 2;

	$hosts_sent = 0;
	if($get)
	{
		$host_file = file(DATA_DIR.'/hosts-'.$net.'.dat');
		$count_host = count($host_file);

		if($count_host <= MAX_HOSTS_OUT)
			$max_hosts = $count_host;
		else
			$max_hosts = MAX_HOSTS_OUT;

		for($i=0; $i<$max_hosts; $i++)
		{
			list($h_age, $h_ip, $h_port, $h_leaves, $h_max_leaves, $h_uptime, $h_vendor, /* $h_ver */, /* $h_ua */, /* $h_suspect */,) = explode('|', $host_file[$count_host - 1 - $i], 13);
			$h_age = TimeSinceSubmissionInSeconds($now, $h_age, $offset);
			if($h_age > MAX_HOST_AGE) break;
			$host = 'H|'.$h_ip.':'.$h_port.'|'.$h_age;
			if($separators > 1) $host .= '||';
			if($getleaves) $host .= $h_leaves;
			if($separators > 2) $host .= '|';
			if($getvendors) $host .= $h_vendor;
			if($separators > 3) $host .= '|';
			if($getuptime) $host .= $h_uptime;
			if($separators > 4) $host .= '|';
			if($getmaxleaves) $host .= $h_max_leaves;
			$output .= $host."\r\n";
			$hosts_sent++;
		}
		/* Workaround for a bug, some old Shareaza versions doesn't send updates if we don't have any host */
		if($hosts_sent === 0 && $add_dummy_host)
		{
			$output .= "H|1.1.1.1:7331|100000\r\n";
			$hosts_sent = 1;
		}
	}

	$gwcs_sent = 0;
	$udps_sent = 0;
	if(ENABLE_URL_SUBMIT)
	{
		$cache_file = file(DATA_DIR.'/alt-gwcs.dat');
		$count_cache = count($cache_file);

		if($get)
		{
			for($n=0, $i=$count_cache-1; $n<MAX_CACHES_OUT && $i>=0; $i--)
			{
				list($time, $new_specs_only,, $cache, $cache_net,) = explode('|', $cache_file[$i], 6);

				$show = FALSE;
				if(strpos($cache_net, '-') > -1)
				{
					$cache_networks = explode('-', $cache_net);
					$cache_nets_count = count($cache_networks);
					for($x=0; $x < $cache_nets_count; $x++)
					{
						if($cache_networks[$x] === $net)
						{
							$show = TRUE;
							break;
						}
					}
				}
				elseif($cache_net === $net)
					$show = TRUE;

				if($show && strpos($cache, '://') > -1)
				{
					if((bool)$new_specs_only && $detected_pv < 2.1) continue;
					$cache = 'U|'.$cache.'|'.TimeSinceSubmissionInSeconds($now, rtrim($time), $offset);
					$output .= $cache."\r\n";
					$n++;
				}
			}
			$gwcs_sent = $n;
		}

		if($getudp && $net === 'gnutella')
		{
			$cache_file = file(DATA_DIR.'/alt-udps.dat');
			$count_cache = count($cache_file);

			for($n=0, $i=$count_cache-1; $n<MAX_UDP_CACHES_OUT && $i>=0; $i--)
			{
				list($time, /* New specs only */,, $cache, $cache_net,) = explode('|', $cache_file[$i], 6);
				$cache = 'C|'.$cache.'|'.TimeSinceSubmissionInSeconds($now, rtrim($time), $offset);
				$output .= $cache."\r\n";
				$n++;
			}
			$udps_sent = $n;
		}
	}

	if($hosts_sent === 0)
		$output .= "I|NO-HOSTS\r\n";
	if($gwcs_sent === 0)
		$output .= "I|NO-URL\r\n";
	if($getudp && $udps_sent === 0)
		$output .= "I|NO-UDP-URL\r\n";
	/* I|NO-URL-NO-HOSTS combined reply is no longer used */

	echo $output;
}

function DetectEncoding($user_agent)
{
	$ACCEPT_ENCODING = empty($_SERVER['HTTP_ACCEPT_ENCODING']) ? "" : $_SERVER['HTTP_ACCEPT_ENCODING'];

	/* The deflate compression in HTTP 1.1 is the format specified by RFC 1950 instead Internet Explorer incorrectly interpret it as RFC 1951 (buggy IE, what surprise!!!) */
	if(strpos($ACCEPT_ENCODING, 'deflate') !== false && strpos($user_agent, ' MSIE ') === false)
		return 'deflate';
	if(strpos($ACCEPT_ENCODING, 'gzip') !== false)
		return 'gzip';

	return 'none';
}

function StartCompression($compression, $user_agent, $web_interface = false)
{
	if($web_interface) header('Vary: Accept-Encoding');

	/* If the compression parameter has an unsupported value then just use Accept-Encoding */
	if( $compression === null || ($compression !== 'none' && $compression !== 'deflate' && $compression !== 'gzip') )
		$compression = DetectEncoding($user_agent);

	if($compression === 'deflate')
	{
		header('Content-Encoding: deflate');
		ob_start('gzcompress');
		return true;
	}
	if($web_interface && $compression === 'gzip')
	{
		header('Content-Encoding: gzip');
		ob_start('gzencode');
		return true;
	}

	return false;
}

function CleanStats($request)
{
	ignore_user_abort(true);
	set_time_limit(120);

	$now = time();
	$offset = date("Z");
	$file_count = 0;
	$line_length = 17;
	$file = fopen('stats/'.$request.'-reqs.dat', 'rb');
	if($file === false) return;

	if(OPTIMIZED_STATS)
	{
		while(!feof($file))
		{
			$current_stat = fgets($file, 20);
			$time_diff = $now - ( strtotime($current_stat) + $offset );	// GMT
			$time_diff = floor($time_diff / 3600);	// Hours

			if($current_stat != "" && $time_diff >= 1)
				fseek( $file, $line_length * 100, SEEK_CUR );
			else
				{ fseek( $file, -$line_length, SEEK_CUR ); break; }
		}
		fseek( $file, -$line_length * 100, SEEK_CUR );
	}

	while(!feof($file))
	{
		$current_stat = fgets($file, 20);
		$time_diff = $now - ( strtotime($current_stat) + $offset );	// GMT
		$time_diff = floor($time_diff / 3600);	// Hours

		if($time_diff < 1)
		{
			$stat_file[$file_count] = rtrim($current_stat);
			$file_count++;
		}
	}
	fclose($file);

	$file = fopen('stats/'.$request.'-reqs.dat', 'wb');
	if($file === false) return;
	flock($file, LOCK_EX);
	for($i = 0; $i < $file_count; $i++)
		fwrite($file, $stat_file[$i]."\n");
	flock($file, LOCK_UN);
	fclose($file);
}

function ReadStats($type)
{
	$name = null;
	if($type === STATS_OTHER) $name = 'other'; elseif($type === STATS_UPD) $name = 'upd'; elseif($type === STATS_UPD_BAD) $name = 'upd-bad';
	elseif($type === STATS_BLOCKED) $name = 'blocked'; else { trigger_error('ReadStats - Invalid type', E_USER_ERROR); return 0; }

	$file = fopen('stats/'.$name.'-reqs.dat', 'rb'); if($file === false) return 0;
	$requests = 0;
	$now = time();
	$offset = date("Z");
	$line_length = 17;

	if(OPTIMIZED_STATS)
	{
		while(!feof($file))
		{
			$current_stat = fgets($file, 20);
			$time_diff = $now - ( strtotime($current_stat) + $offset );	// GMT
			$time_diff = floor($time_diff / 3600);	// Hours

			if($current_stat != "" && $time_diff >= 1)
				fseek( $file, $line_length * 100, SEEK_CUR );
			else
				{ fseek( $file, -$line_length, SEEK_CUR ); break; }
		}
		fseek( $file, -$line_length * 100, SEEK_CUR );
	}

	while(!feof($file))
	{
		$current_stat = fgets($file, 20);
		$time_diff = $now - ( strtotime($current_stat) + $offset );	// GMT
		$time_diff = floor($time_diff / 3600);	// Hours

		if($time_diff < 1)
			$requests++;
	}
	fclose($file);

	return $requests;
}

function UpdateStats($type)
{
	if(!STATS_ENABLED) return;

	$name = null;
	if($type === STATS_OTHER) $name = 'other'; elseif($type === STATS_UPD) $name = 'upd'; elseif($type === STATS_UPD_BAD) $name = 'upd-bad';
	elseif($type === STATS_BLOCKED) $name = 'blocked'; else { trigger_error('UpdateStats - Invalid type', E_USER_ERROR); return; }

	$line = gmdate('Y/m/d H:i')."\n";
	$file = fopen('stats/'.$name.'-reqs.dat', 'ab'); if($file === false) return;
	flock($file, LOCK_EX);
	fwrite($file, $line);
	flock($file, LOCK_UN);
	fclose($file);
}

function ReadStatsTotalReqs()
{
	$requests = file('stats/requests.dat');
	return $requests[0];
}

function WriteStatsTotalReqs()
{
	if(!STATS_ENABLED) return;

	$file = fopen('stats/requests.dat', 'r+b'); if($file === false) return;
	flock($file, LOCK_EX);
	$requests = fgets($file, 50);
	if($requests === "") $requests = 1; else $requests++;
	rewind($file);
	fwrite($file, $requests);
	flock($file, LOCK_UN);
	fclose($file);
}

/* It use headers that can be easily spoofed. This function is used only for helping clients to detect their IP address; it mustn't be used for security checks. */
function DetectRemoteIP($remote_ip)
{
	/* Shared internet/ISP IP */
	if(!empty($_SERVER['HTTP_CLIENT_IP']) && ValidateIP($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
	/* IPs passing through proxies */
	if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		if(strpos($ip, ',') === false) { if(ValidateIP($ip)) return $ip; }
		else { $ip_array = explode(',', $ip, 10); foreach($ip_array as $val) { $ip = trim($val); if(ValidateIP($ip)) return $ip; } }
	}
	/* Varnish Cache on SourceForge and maybe others */
	if(!empty($_SERVER['HTTP_X_REMOTE_ADDR']) && ValidateIP($_SERVER['HTTP_X_REMOTE_ADDR'])) return $_SERVER['HTTP_X_REMOTE_ADDR'];
	/* Cloud Sites */
	if(!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && ValidateIP($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	/* X-Real-IP header set by some CDN */
	if(!empty($_SERVER['HTTP_X_REAL_IP']) && ValidateIP($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];

	return $remote_ip;
}


/* Set default charset to UTF-8 */
ini_set('default_charset', 'utf-8');
/* Suppress warnings if the timezone isn't set */
if(function_exists('date_default_timezone_get'))
	date_default_timezone_set(@date_default_timezone_get());

$REMOTE_IP = $_SERVER['REMOTE_ADDR'];

$PING = !empty($_GET["ping"]) ? $_GET["ping"] : 0;

$NET = !empty($_GET["net"]) ? strtolower($_GET["net"]) : NULL;
$IS_CRAWLER = empty($_GET["crawler"])? false : true;	// This must be added to every request made by a crawler, to clarify that the request is made from a crawler
$IS_A_CACHE = empty($_GET["cache"])? 0 : 1;				// This must be added to every request made by a GWC, to clarify that the request is made from a GWC
$MULTI = empty($_GET["multi"])? 0 : $_GET["multi"];		// It is added to every ping request (it has no effect on other things), it tell to the pinged cache to ignore the "net" parameter and outputting the pong using this format, if possible, "I|pong|[cache name] [cache version]|[supported networks list]" - example: I|pong|Skulls 0.3.0|gnutella-gnutella2

$INFO = !empty($_GET["info"]) ? $_GET["info"] : 0;		// This tell to the cache to show info like the name, the version, the vendor code, the home page of the cache, the nick and the website of the maintainer (the one that has put the cache on a webserver)

$UA = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";

$COMPRESSION = !empty($_GET["compression"]) ? strtolower($_GET["compression"]) : NULL;	// It tell to the cache what compression to use (it override HTTP_ACCEPT_ENCODING), currently values are: deflate, none

$IP = null; $PORT = null;
$HOST = !empty($_POST['ip'])? $_POST['ip'] : (!empty($_GET['ip'])? $_GET['ip'] : null);
$CACHE = !empty($_POST['url'])? $_POST['url'] : (!empty($_GET['url'])? $_GET['url'] : null);
$UDP_CACHE = (!empty($_GET["udpurl"]))? $_GET["udpurl"] : null;
$LEAVES = isset($_GET['x_leaves']) ? $_GET['x_leaves'] : null;
$MAX_LEAVES = isset($_GET['x_max']) ? $_GET['x_max'] : null;
$UPTIME = isset($_GET['uptime']) ? $_GET['uptime'] : null;

$HOSTFILE = !empty($_GET["hostfile"]) ? $_GET["hostfile"] : 0;
$URLFILE = !empty($_GET["urlfile"]) ? $_GET["urlfile"] : 0;
$STATFILE = !empty($_GET["statfile"]) ? $_GET["statfile"] : 0;

$AGE = empty($_GET['age']) ? 0 : $_GET['age'];

/*
The "gwcs" parameter is almost identical to "urlfile" but can be combined with the hostfile request.
This is necessary to keep backwards-compatibility with some implementations which are supposed
to ignore unknown requests but are not required to handle combined "urlfile" and "hostfile" requests
(Note: this GWC unlike some others also support combined requests of every possible type).
*/
$GWCS = empty($_GET['gwcs']) ? 0 : $_GET['gwcs'];

//$ALLFILE = !empty($_GET["allfile"]) ? $_GET["allfile"] : 0;
$BFILE = !empty($_GET["bfile"]) ? $_GET["bfile"] : 0;

$GET = !empty($_GET["get"]) ? $_GET["get"] : 0;
$GETUDP = (!empty($_GET["getudp"]))? $_GET["getudp"] : 0; /* Currently it is tied to the normal 'get' but in the future will be able to get queried alone */
$UPDATE = (empty($_POST['update']) && empty($_GET['update']))? 0 : 1;

$CLIENT = !empty($_GET['client']) ? $_GET['client'] : "";
$VERSION = !empty($_GET['version']) ? $_GET['version'] : "";

$SUPPORT = empty($_GET['support']) ? 0 : $_GET['support'];
$GETNETWORKS = empty($_GET['getnetworks']) ? 0 : $_GET['getnetworks'];

$GETLEAVES = empty($_GET['getleaves']) ? 0 : $_GET['getleaves'];
$GETVENDORS = empty($_GET['getvendors']) ? 0 : $_GET['getvendors'];
$GETUPTIME = empty($_GET['getuptime']) ? 0 : $_GET['getuptime'];
$GETMAXLEAVES = empty($_GET['getmaxleaves']) ? 0 : $_GET['getmaxleaves'];

$NO_IP_HEADER = empty($_GET['noipheader']) ? 0 : $_GET['noipheader'];


$KICK_START = !empty($_GET['kickstart']) ? $_GET['kickstart'] : 0;  // It request hosts from the GWC specified in the "url" parameter for the network specified in "net" parameter (it is used the first time to populate the hosts list, it MUST be disabled after that).

if( isset($noload) ) die();

if(MAINTAINER_NICK === 'your nickname here' || MAINTAINER_NICK === "")
{
	echo "You must read readme.txt in the admin directory first.\r\n";
	die();
}

if(LOG_MAJOR_ERRORS || LOG_MINOR_ERRORS)
{
	include "log.php";
}

if( !file_exists(DATA_DIR."/last_action.dat") )
{
	if( !file_exists(DATA_DIR."/") ) mkdir(DATA_DIR."/", 0777);

	$file = @fopen( DATA_DIR."/last_action.dat", "xb" );
	if($file !== FALSE)
	{
		flock($file, LOCK_EX);
		fwrite($file, VER."|".STATS_ENABLED."|-1|".gmdate("Y/m/d H:i")."|");
		flock($file, LOCK_UN);
		fclose($file);
	}
	else
	{
		echo "<font color=\"red\"><b>Error during writing of ".DATA_DIR."/last_action.dat</b></font><br>";
		echo "<b>You must create the file manually, and give to the file the correct permissions.</b><br><br>";
	}

	include "functions.php";
	Initialize($SUPPORTED_NETWORKS, TRUE);
}


if(IsWebInterface())
{
	if(!ValidateIdentityWeb($_SERVER['REQUEST_METHOD'], $UA)) { header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); header('Content-Length: 0'); header('Connection: close'); die; }

	include './web_interface.php';
	header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
	$compressed = StartCompression($COMPRESSION, $UA, true);
	ShowHtmlPage($PHP_SELF, $COMPRESSION, $header, $footer);
	if($compressed) ob_end_flush();
}
elseif( $KICK_START )
{
	header('Connection: close');

	if(!KICK_START_ENABLED)
		die("ERROR: KickStart is disabled\r\n");

	if( $NET === NULL )
		die("ERROR: Network not specified\r\n");
	elseif( !CheckNetworkString($SUPPORTED_NETWORKS, $NET, FALSE) )
		die("ERROR: Network not supported\r\n");

	if( !function_exists("KickStart") )
	{
		include "functions.php";
	}
	KickStart($NET, $CACHE);
}
else
{
	header('Connection: close');

	/* Check if vendor and version are mixed inside vendor */
	if($VERSION === "" && strlen($CLIENT) > 4)
	{
		$VERSION = substr($CLIENT, 4);
		$CLIENT = substr($CLIENT, 0, 4);
	}
	$CLIENT = strtoupper($CLIENT);

	$ORIGIN = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : null;

	if(IsFakeClient($CLIENT, $VERSION, $UA))
	{
		header('HTTP/1.0 404 Not Found'); echo "ERROR\r\n";  /* Keep the ERROR text for fake/faulty clients that just ignore status code */
		if(STATS_FOR_BAD_CLIENTS) { UpdateStats(STATS_BLOCKED); WriteStatsTotalReqs(); }
		if(LOG_MINOR_ERRORS) Logging('fake-clients-'.$CLIENT);
		die;
	}

	/* Content-Type */
	if(CONTENT_TYPE_WORKAROUND) { header('Content-Type: application/octet-stream'); header('X-CT: text/plain; utf-8'); }
	else header('Content-Type: text/plain; charset=utf-8');

	$DETECTED_NET = $NET; $DETECTED_REMOTE_IP = null; $CLOUDFLARE_IP = null; $FAKE_CF = false;
	if($DETECTED_NET === null) $DETECTED_NET = 'gnutella';  /* This should NOT absolutely be changed (also if your GWC doesn't support the gnutella network) otherwise you will mix hosts of different networks and it is bad */

	if(TRUST_X_REMOTE_ADDR_FROM_LOCALHOST && isset($_SERVER['HTTP_X_REMOTE_ADDR']) && ($REMOTE_IP === '127.0.0.1' || $REMOTE_IP === '::1'))
		$REMOTE_IP = $_SERVER['HTTP_X_REMOTE_ADDR'];

	/* CloudFlare */
	if(isset($_SERVER['HTTP_CF_CONNECTING_IP']))
	{
		include_once './update.php';
		if(USING_CLOUDFLARE && ValidateIP($REMOTE_IP) && IsCloudFlareIP($REMOTE_IP))
		{
			$CLOUDFLARE_IP = $REMOTE_IP;
			$REMOTE_IP = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}
		else
			$FAKE_CF = true;
	}

	if(!ValidateRemoteIP($REMOTE_IP, $IS_LOCALHOST)) $REMOTE_IP = 'unknown';
	if(!ValidateIdentity($_SERVER['REQUEST_METHOD'], $CLIENT, $VERSION, $UA, $NET, $DETECTED_NET, $ORIGIN !== null) || $FAKE_CF || $REMOTE_IP === 'unknown')
	{
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		echo "ERROR: Invalid identification\r\n";
		UpdateStats(STATS_BLOCKED); WriteStatsTotalReqs();
		if(LOG_MINOR_ERRORS)
		{
			if($FAKE_CF)
				Logging('fake-cloudflare-headers');
			else
				Logging('invalid-identifications');
		}
		die;
	}

	/* Validate CORS requests */
	if($ORIGIN !== null)
	{
		include_once './update.php';
		if(empty($ORIGIN) || $ORIGIN === 'null' || empty($_SERVER['HTTP_REFERER']) || empty($UA) || IsIPInBlockList($REMOTE_IP))
		{
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); die;
		}
	}

	/* Separate ip from port for the submitted host, it will be used later */
	if($HOST !== null)
	{
		if(strpos($HOST, ':') === false)
			{$IP = $HOST; $PORT = 0;}
		else
			list($IP, $PORT) = explode(':', $HOST, 2);
	}

	/* Disallow some network names to avoid confusion with already existing networks */
	if($NET === 'gnutella1' || $NET === 'g2' || $NET === 'shareaza')
	{
		UpdateStats(STATS_BLOCKED); WriteStatsTotalReqs();
		if(LOG_MAJOR_ERRORS) Logging('invalid-network-names');
		die("ERROR: Invalid network name\r\n");
	}

	$IS_WEB_TOOL = false;  /* Is considered a web tool every thing that isn't a P2P servent, like GWebCaches, crawlers, manual submissions, etc. */
	$MANUAL_SUBMIT = false; $FORCE_PV2 = false;
	if($CLIENT === 'TEST')
	{
		$IS_WEB_TOOL = true;
		if($VERSION === 'Submit') $MANUAL_SUBMIT = true;
		elseif(strpos($VERSION, 'Bazooka') === 0) $FORCE_PV2 = true;
	}
	elseif($CLIENT === 'GCII') { $IS_WEB_TOOL = true; if($NET === 'gnutella2') $FORCE_PV2 = true; }
	elseif($IS_CRAWLER || $IS_A_CACHE || $ORIGIN !== null) $IS_WEB_TOOL = true;

	if(($MANUAL_SUBMIT || $ORIGIN !== null)? !VerifyUserAgentWeb($UA) : !VerifyUserAgent($UA))
	{
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		UpdateStats(STATS_BLOCKED); WriteStatsTotalReqs();
		if(LOG_MINOR_ERRORS) Logging('blocked-clients');
		die;
	}

	WriteStatsTotalReqs();

	/*
		Existing GWC specs: v1, v1.1, v2, v2.1, v3, v4  ( GWC v3 is an extension of GWC v1  /  GWC v4 is an extension of GWC v2.1 )

		Priority order (in case there are parameters of different specs mixed togheter): v4 and higher, v2.1, v2, v3, v1.1, v1

		The following parameters can be used in every version of the spec: client, version, ping, pv, getspec, net, x.leaves, x.max, uptime
		The following parameters alone imply spec v1 but together with others are used also in other spec versions: url, ip
	*/

	/*** Smart spec detection - START ***/
	$PV = (empty($_GET['pv'])? 0 : (float)$_GET['pv']);
	$DETECTED_PV = 0;

	if($PV >= 4 || $MULTI)
		$DETECTED_PV = 4;
	elseif($PV < 3)
	{
		if($PV >= 2.1 || $GETNETWORKS || $GETLEAVES || $GETVENDORS || $GETUPTIME || $GETMAXLEAVES || $GETUDP || $INFO || $UDP_CACHE !== null)
			$DETECTED_PV = 2.1;
		elseif($PV >= 2 || $GET || $UPDATE || $SUPPORT || $FORCE_PV2)
			$DETECTED_PV = 2;
	}

	if($DETECTED_PV === 0)  /* Only if not yet detected */
	{
		if($PV >= 3 || $GWCS)
			$DETECTED_PV = 3;
		elseif($PV >= 1.1 || $AGE)
			$DETECTED_PV = 1.1;
		elseif($PV >= 1 || $HOSTFILE || $URLFILE || $BFILE || $STATFILE || $HOST !== null || $CACHE !== null)
			$DETECTED_PV = 1;
	}
	/*** Smart spec detection - END ***/

	if($BFILE) { $HOSTFILE = 1; $URLFILE = 1; }
	elseif($GWCS) $URLFILE = 1;
	/* getnetworks=1 is the same of support=2, in case it is specified then the old support=1 is ignored */
	if($GETNETWORKS) $SUPPORT = 2;

	if($IS_WEB_TOOL)
	{
		$HOST = null;       /* Block host submissions, they aren't hosts */
		$NO_IP_HEADER = 1;  /* Do NOT send X-Remote-IP header, they don't need it */
		if($MANUAL_SUBMIT) { $PING = 0; $GET = 0; $GETUDP = 0; $HOSTFILE = 0; $URLFILE = 0; $STATFILE = 0; $INFO = 0; }
	}

	if(!VerifyVersion($CLIENT, $VERSION))
	{
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		UpdateStats(STATS_BLOCKED);
		if(LOG_MINOR_ERRORS) Logging('old-clients', $DETECTED_PV);
		die("ERROR: Update your client\r\n");
	}

	if(!$GET && !$GETUDP && !$PING && !$SUPPORT && !$HOSTFILE && !$URLFILE && !$STATFILE && $CACHE === null && $UDP_CACHE === null && $HOST === null && !$INFO)
	{
		echo "ERROR: Invalid query\r\n";
		UpdateStats(STATS_BLOCKED);
		if(LOG_MAJOR_ERRORS) Logging('invalid-queries', $DETECTED_PV);
		die;
	}

	if($LEAVES !== null && (!ctype_digit($LEAVES) || $LEAVES > 2047))
	{
		$LEAVES = null; $HOST = null; if(LOG_MAJOR_ERRORS) Logging('invalid-leaves', $DETECTED_PV);
	}
	if($MAX_LEAVES !== null && (!ctype_digit($MAX_LEAVES) || $MAX_LEAVES < 1 || $MAX_LEAVES > 2047))
	{
		$MAX_LEAVES = null; $HOST = null; if(LOG_MAJOR_ERRORS) Logging('invalid-max-leaves', $DETECTED_PV);
	}
	if($UPTIME !== null)
	{
		if(!ctype_digit($UPTIME) || $UPTIME > 31536000)
		{
			$UPTIME = null; if(LOG_MAJOR_ERRORS) Logging('invalid-uptimes', $DETECTED_PV);
		}
		elseif($UPTIME < 60)
		{
			$UPTIME = null; $HOST = null; if(LOG_MAJOR_ERRORS) Logging('short-uptimes', $DETECTED_PV);
		}
	}

	if(!$NO_IP_HEADER) { $DETECTED_REMOTE_IP = DetectRemoteIP($REMOTE_IP); header('X-Remote-IP: '.SanitizeHeaderValue($DETECTED_REMOTE_IP)); }
	if($PING && $MULTI) header('X-Vendor: '.VENDOR);

	$compressed = StartCompression($COMPRESSION, $UA);

	//$CACHE_IS_VALID = true;
	if($CACHE !== null)
		if(!CanonicalizeURL($CACHE))
			$CACHE = 'BLOCKED';

	if( CheckNetworkString($SUPPORTED_NETWORKS, $DETECTED_NET, FALSE) )
		$supported_net = TRUE;
	else
	{
		$supported_net = FALSE;
		if(($PING && !$MULTI && !$SUPPORT) || $GET || $GETUDP || $HOSTFILE || $URLFILE || $CACHE != NULL || $UDP_CACHE != NULL || $HOST != NULL) echo "ERROR: Network not supported\r\n";
	}

	if($PING)
		if($supported_net || $MULTI)
			Pong($DETECTED_PV, $SUPPORT, $MULTI, $NET, $CLIENT, $VERSION, $REMOTE_IP);

	if($SUPPORT)
		Support($SUPPORT, $SUPPORTED_NETWORKS);

	if($HOST !== null && ($CLIENT === 'MUTE' || $CLIENT === 'MMFC' || $CLIENT === 'ANTS' || $CLIENT === 'GPUX'))
		if(IsValidPrivateIP($IP)) { $IP = $REMOTE_IP; $HOST = $IP.':'.$PORT; }  /* Workaround for a bug in own IP detection of some clients */

	$is_good_update = null;
	if($DETECTED_PV >= 2 && $DETECTED_PV !== 3)
	{
		if( $HOST !== NULL && $supported_net )
		{
			$result = -1;
			include_once './update.php';
			if(ValidateHost($HOST, $REMOTE_IP, $DETECTED_NET) && !IsIPInBlockList($REMOTE_IP))
			{
				$result = WriteHostFile($DETECTED_NET, $IP, $PORT, $LEAVES, $MAX_LEAVES, $UPTIME, $CLIENT, $VERSION, $UA);

				if( $result == 0 ) // Exists
					print "I|update|OK|Host already updated\r\n";
				elseif( $result == 1 ) // Updated timestamp
					print "I|update|OK|Updated host timestamp\r\n";
				elseif( $result == 2 ) // OK
					print "I|update|OK|Host added\r\n";
				elseif( $result == 3 ) // OK, pushed old data
					print "I|update|OK|Host added (pushed old data)\r\n";
				elseif( $result == 4 ) // Error, failed verification
					print "I|update|WARNING|Unreachable host\r\n";
				else
					print "I|update|ERROR|Unknown error 1, return value = ".$result."\r\n";
			}
			else // Invalid IP
				print "I|update|WARNING|Invalid host"."\r\n";

			if($result >= 0 && $result <= 3)
				$is_good_update = true;
			else
				$is_good_update = false;
		}

		if( ($CACHE !== null || $UDP_CACHE !== null) && $supported_net )
		{
			$result = -1; $is_udp = false;
			if(!ENABLE_URL_SUBMIT) // Cache adding disabled
				print "I|update|OK|WARNING|URL adding is disabled\r\n";
			elseif( ($UDP_CACHE !== null && $DETECTED_NET === 'gnutella' && !CheckUDPURLValidity($UDP_CACHE)))  // Invalid URL
				print("I|update|WARNING|Invalid UDP URL"."\r\n");
			elseif( ($CACHE !== null && !CheckURLValidity($CACHE)))  // Invalid URL
				print("I|update|WARNING|Invalid URL"."\r\n");
			else
			{
				if($UDP_CACHE !== null && $DETECTED_NET === 'gnutella')
				{
					$is_udp = true;
					$result = WriteCacheFile(DATA_DIR.'/alt-udps.dat', true, substr($UDP_CACHE, 4), $CLIENT, $VERSION, $IS_A_CACHE, $UA);
				}
				elseif($CACHE !== null)
					$result = WriteCacheFile(DATA_DIR.'/alt-gwcs.dat', false, $CACHE, $CLIENT, $VERSION, $IS_A_CACHE, $UA);

				if( $result == 0 ) // Exists
					print "I|update|OK|URL already updated\r\n";
				elseif( $result == 1 ) // Updated timestamp
					print "I|update|OK|Updated URL timestamp\r\n";
				elseif( $result == 2 ) // OK
					print "I|update|OK|URL added\r\n";
				elseif( $result == 3 ) // OK, pushed old data
					print "I|update|OK|URL added (pushed old data)\r\n";
				elseif( $result == 4 ) // Blocked or failed URL
					print "I|update|OK|WARNING|Blocked URL\r\n";
				elseif( $result == 5 ) // Ping failed
				{
					if($is_udp)
						print "I|update|OK|WARNING|Ping of ".$UDP_CACHE." failed\r\n";
					else
						print "I|update|OK|WARNING|Ping of ".$CACHE." failed\r\n";
				}
				elseif( $result == 6 ) // Unsupported network
					print "I|update|WARNING|Network of URL not supported\r\n";
				elseif( $result == 7 ) // Possible network congestion
					print "I|update|OK|CONGESTION|Possible network congestion on this server, cannot check GWC\r\n";
				else
					print "I|update|ERROR|Unknown error 2, return value = ".$result."\r\n";
			}

			if($is_good_update === null)
			{
				if(($result >= 0 && $result <= 3) || $result == 7)
					$is_good_update = true;
				else
					$is_good_update = false;
			}
		}
	}
	else
	{
		if( $supported_net && ( $HOST != NULL || $CACHE != NULL ) )
			print "OK\r\n";

		if( $HOST != NULL && $supported_net )
		{
			$result = -1;
			include_once './update.php';
			if(ValidateHost($HOST, $REMOTE_IP, $DETECTED_NET) && !IsIPInBlockList($REMOTE_IP))
				$result = WriteHostFile($DETECTED_NET, $IP, $PORT, $LEAVES, $MAX_LEAVES, $UPTIME, $CLIENT, $VERSION, $UA);
			else // Invalid IP
				print "WARNING: Invalid host"."\r\n";

			if($result >= 0 && $result <= 3)
				$is_good_update = true;
			else
				$is_good_update = false;
		}

		if( $CACHE != NULL && $supported_net )
		{
			$result = -1;
			if(!ENABLE_URL_SUBMIT) // Cache adding disabled
				print "WARNING: URL adding is disabled\r\n";
			elseif( CheckURLValidity($CACHE) )
			{
				$result = WriteCacheFile(DATA_DIR.'/alt-gwcs.dat', false, $CACHE, $CLIENT, $VERSION, $IS_A_CACHE, $UA);

				if( $result == 5 ) // Ping failed
					print "WARNING: Ping of ".$CACHE." failed\r\n";
				elseif( $result == 6 ) // Unsupported network
					print "WARNING: Network of URL not supported\r\n";
			}
			else // Invalid URL
				print "WARNING: Invalid URL"."\r\n";

			if($is_good_update === null)
			{
				if(($result >= 0 && $result <= 3) || $result == 7)
					$is_good_update = true;
				else
					$is_good_update = false;
			}
		}
	}

	if(!$supported_net) $GET = 0;

	if($GET /*|| $GETUDP*/)
	{
		$dummy_host_needed = CheckIfDummyHostIsNeeded($CLIENT, $VERSION);

		Get($DETECTED_PV, $DETECTED_NET, $GET, $GETLEAVES, $GETVENDORS, $GETUPTIME, $GETMAXLEAVES, $GETUDP, $CLIENT, $dummy_host_needed);
	}
	elseif($supported_net)
	{
		if($HOSTFILE)
			HostFile($DETECTED_NET, $AGE);

		if($URLFILE && ENABLE_URL_SUBMIT)
			UrlFile($DETECTED_PV, $DETECTED_NET, $AGE, $CLIENT);

		if($DETECTED_PV === 3 && ($HOSTFILE || $URLFILE))
			echo "nets: ".strtolower(NetsToString())."\r\n";
	}

	if($INFO)
	{
		echo "I|name|".NAME."\r\n";
		echo "I|ver|".VER."\r\n";
		echo "I|vendor|".VENDOR."\r\n";
		echo "I|software-url|".GWC_SITE."\r\n";
		echo "I|license|".LICENSE_NAME.' v'.LICENSE_VER."\r\n";
		echo "I|license-url|".LICENSE_URL."\r\n";

		echo "I|maintainer|".MAINTAINER_NICK."\r\n";
		if(MAINTAINER_WEBSITE !== 'http://www.your-site.com/' && MAINTAINER_WEBSITE !== "")
			echo "I|maintainer-url|".MAINTAINER_WEBSITE."\r\n";
	}

	if($CACHE != NULL || $HOST != NULL)
		UpdateStats($is_good_update? STATS_UPD : STATS_UPD_BAD);
	else
		UpdateStats(STATS_OTHER);

	if($STATFILE)
	{
		if(STATS_ENABLED)
		{
			/* Good + bad update requests of last hour */
			$upd_reqs = ReadStats(STATS_UPD) + ReadStats(STATS_UPD_BAD);
			/* Other requests of last hour */
			$other_reqs = ReadStats(STATS_OTHER);
			/* Blocked requests of last hour */
			$blocked_reqs = ReadStats(STATS_BLOCKED);

			echo ReadStatsTotalReqs(),"\r\n";
			echo ($other_reqs + $upd_reqs + $blocked_reqs),"\r\n";
			echo $upd_reqs,"\r\n";
		}
		else
			echo "WARNING: Statfile disabled\r\n";
	}

	if($IS_LOCALHOST)
	{
		if($DETECTED_PV >= 2 && $DETECTED_PV !== 3)
			echo "I|general|WARNING|Accessed from localhost\r\n";
		else
			echo "WARNING: Accessed from localhost\r\n";
	}

	if(!empty($_GET['getspec']))
	{
		if($DETECTED_PV >=2 && $DETECTED_PV !== 3)
			echo 'I|pv|',$DETECTED_PV,"\r\n";  /* v2.x, v4+ */
		else
			echo 'pv: ',$DETECTED_PV,"\r\n";   /* v0, v1.x, v3.x */
	}


	$clean_file = NULL;
	$changed = FALSE;
	$file = fopen( DATA_DIR."/last_action.dat", "r+b" );
	if($file !== false)
	{
		flock($file, LOCK_EX);
		$last_action_string = fgets($file, 50);

		/* ToDO: clean this */
		if($last_action_string != "")
		{
			list($last_ver, $last_stats_status, $last_action, $last_action_date) = explode("|", $last_action_string);
			$time_diff = time() - ( strtotime( $last_action_date ) + date("Z") );	// GMT
			$time_diff = floor($time_diff / 3600);	// Hours
			if($time_diff >= 1 && $CACHE == NULL)
			{
				define('CLEAN_STATS_OTHER',   0);
				define('CLEAN_STATS_BLOCKED', 1);
				define('CLEAN_STATS_UPD',     2);
				define('CLEAN_STATS_UPD_BAD', 3);
				define('CLEAN_FAILED_URLS',   4);

				$last_action++;
				switch($last_action)
				{
					default:
						$last_action = 0;
					case CLEAN_STATS_OTHER:
						$clean_file = "stats";
						$clean_type = "other";
						break;
					case CLEAN_STATS_BLOCKED:
						$clean_file = "stats";
						$clean_type = "blocked";
						break;
					case CLEAN_STATS_UPD:
						$clean_file = "stats";
						$clean_type = "upd";
						break;
					case CLEAN_STATS_UPD_BAD:
						$clean_file = "stats";
						$clean_type = "upd-bad";
						break;
					case CLEAN_FAILED_URLS:
						$clean_file = "failed_urls";
						break;
				}
				if(!STATS_ENABLED && $clean_file == "stats") $clean_file = NULL;
				$changed = TRUE;
			}
		}
		else { $last_ver = 0; $last_action = -1; }

		if($last_ver != VER || $last_stats_status != STATS_ENABLED)
		{
			if( !function_exists("Initialize") )
			{
				include "functions.php";
			}
			Initialize($SUPPORTED_NETWORKS);
			$changed = TRUE;
		}
		if($changed)
		{
			$last_action_date = gmdate("Y/m/d H:i");
			rewind($file);
			fwrite($file, VER."|".STATS_ENABLED."|".$last_action."|".$last_action_date."|");
		}
		flock($file, LOCK_UN);
		fclose($file);
	}

	if($compressed) ob_end_flush();
	flush();

	if($clean_file == "stats")
		CleanStats($clean_type);
	elseif($clean_file == "failed_urls")
		CleanFailedUrls();
}
?>