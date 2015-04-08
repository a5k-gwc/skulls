<?php
//
//  Copyright (C) 2005-2008, 2015 by ale5000
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

$SUPPORTED_NETWORKS = null;
include 'vars.php';
$UDP['ukhl'] = 0;	// The support isn't complete

define( 'NAME', 'Skulls' );
define( 'VENDOR', 'SKLL' );											// Four uppercase letters vendor code
define( 'SHORT_VER', '0.3.0' );										// Numeric version (without letters)
define( 'VER', SHORT_VER.'e' );										// Full version (it can contain letters)
define( 'GWC_SITE', 'http://sourceforge.net/projects/skulls/' );	// Official site of this GWebCache
define( 'OPEN_SOURCE', '1' );
define( 'DEBUG', 0 );

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
	if( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' )
		return true;

	if( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' )
		return true;

	return false;
}

DisableAutomaticCompression();

if(function_exists('header_remove'))
	header_remove('X-Powered-By');

$PHP_SELF = $_SERVER['PHP_SELF'];
$REMOTE_IP = $_SERVER['REMOTE_ADDR'];

if(!ENABLED || basename($PHP_SELF) === 'index.php' || $SUPPORTED_NETWORKS === null)
{
	header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	die("ERROR: Service disabled\r\n");
}

if(empty($_SERVER['HTTP_HOST']))
{
	header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	include 'log.php';
	if(LOG_MAJOR_ERRORS) Logging("missing_host_header", null, null, null);
	die("ERROR: Missing Host header\r\n");
}

$MY_URL = (IsSecureConnection() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$PHP_SELF;  /* HTTP_HOST already contains port if needed */
if(CACHE_URL !== $MY_URL && CACHE_URL !== "")
{
	header($_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently');
	header('Location: '.CACHE_URL);
	die();
}

define( 'NETWORKS_COUNT', count($SUPPORTED_NETWORKS) );

function GetMicrotime()
{ 
	list($usec, $sec) = explode(' ', microtime(), 2);
	return (float)$usec + (float)$sec; 
}

function NormalizeIdentity(&$vendor, &$ver, $user_agent)
{
	/* Check if vendor and version are mixed inside vendor */
	if($ver === "" && strlen($vendor) > 4)
	{
		$ver = substr($vendor, 4);
		$vendor = substr($vendor, 0, 4);
	}
	$vendor = strtoupper($vendor);

	/* Change vendor code of mod versions of Shareaza */
	if($vendor === 'RAZA')
	{
		if(strpos($user_agent, 'Shareaza ') !== 0 || strpos($user_agent, 'Shareaza PRO') === 0)
			$vendor = 'RAZM';
	}
	elseif($vendor === 'LIME')
	{
		if(strpos($user_agent, 'Cabos') !== false)
			$vendor = 'CABO';
		elseif(strpos($user_agent, 'LimeWire') !== 0 || (float)$ver >= 5.7)
			$vendor = 'LIMM';
	}
}

function ValidateIdentity($vendor, $ver)
{
	if($ver === "")
		return false;  /* Version missing */
	if(strlen($vendor) < 4)
		return false;  /* Vendor missing or too short */

	return true;
}

function VerifyUserAgent($vendor, $user_agent)
{
	/* Block Google and MSIE from making queries */
	if(strpos($user_agent, 'Googlebot') !== false || strpos($user_agent, '; MSIE ') !== false)
		return false;

	if($vendor === 'RAZM')
	{  /* Block empty User-Agent, User-Agent without version and rip-offs; bad clients */
		if($user_agent === "" || $user_agent === 'Shareaza' || strpos($user_agent, 'Shareaza PRO') === 0)
			return false;
	}
	elseif($vendor === 'LIMM')
	{  /* Block empty User-Agent; bad clients */
		if($user_agent === "")
			return false;
	}

	return true;
}

function VerifyVersion($client, $version)
{
	/* Block some old versions and some bad versions */
	$float_ver = (float)$version;
	switch($client)
	{
		case 'RAZA':
		case 'RAZM':  /* 3.0 and 3.3.1.0 are fakes */
			if($float_ver < 2.3 || $version === '3.0' || $version === '3.3.1.0')
				return false;
			break;
		case 'LIME':  /* Invalid new versions are switched to LIMM, so no need to check here */
			if($float_ver < 3)
				return false;
			break;
		case 'LIMM':
			if($float_ver < 2 || $float_ver >= 8)
				return false;
			break;
		case 'BEAR':
			if($float_ver < 5)
				return false;
			break;
	}

	return true;
}

function ValidatePort($port)
{
	$int_port = (int)$port;
	if($int_port === 7001 || $int_port === 27016)
		return false;

	return true;
}

function CanonicalizeURL(&$full_url)
{
	/* $_GET parameters are already "urldecoded" by PHP, so do NOT urldecode again */
	if(DEBUG) echo 'D|update|URL sent: ',$full_url,"\r\n";

	if(strpos($full_url, '://') !== false)
	{
		list($scheme, $url) = explode('://', $full_url, 2);

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
		}
		if($end_slash)  /* Add slash only if there was before */
			$path .= '/';

		if(strpos($host, ':') !== false)
			{ list($host_name, $host_port) = explode(':', $host, 2); if(!ctype_digit($host_port)) return false; $host_port = (int)$host_port; }
		else
			{ $host_name = $host; $host_port = 80; }
		/* ToDO: Verify port */
		/* ToDO: Remove dot at the end of hostname if present */

		if(substr($host_name, -9) === '.nyud.net' || substr($host_name, -10) === '.nyucd.net')
			return false;  /* Block Coral Content Distribution Network */

		$full_url = strtolower($scheme).'://'.strtolower($host_name).($host_port === 80 ? "" : ':'.$host_port).$path;
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

function Pong($support, $multi, $net, $client, $version, $supported_net, $remote_ip)
{
	if($remote_ip === '127.0.0.1')  /* Prevent caches that point to 127.0.0.1 to being added to cache list, in this case we actually ping ourselves so the cache may look working while it isn't */
		return;

	$pong = 'I|pong|'.NAME.' '.VER;

	if($support)
	{
		echo $pong."\r\n";
	}
	elseif($multi)
	{
		$nets = strtolower(NetsToString());
		echo $pong.'|'.$nets."\r\n";
	}
	elseif($supported_net)
	{
		if($net === 'gnutella' || $net === 'mute')
			echo 'PONG '.NAME.' '.VER."\r\n";

		global $SUPPORTED_NETWORKS;
		if(NETWORKS_COUNT > 1 || strtolower($SUPPORTED_NETWORKS[0]) !== 'gnutella')
		{
			$nets = strtolower(NetsToString());
			if($client === 'TEST' && $net === 'gnutella2' && strpos($version, 'Bazooka') === 0)
				echo $pong.'|gnutella2||COMPAT|'.$nets."\r\n";	/* Workaround for compatibility with Bazooka (it expect only gnutella2 instead of a supported network list and chokes on the rest) */
			elseif($client === 'GCII' && $net === 'gnutella2')
				echo $pong.'|||COMPAT|'.$nets."\r\n";			/* Workaround for compatibility with PHPGnuCacheII (it expects our url instead of a supported network list, keep it empty is also fine) */
			else
				echo $pong.'|'.$nets."\r\n";
		}
	}
}

function Support($support, $supported_networks, $udp)
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
	{
		global $CLIENT, $VERSION, $NET;
		Logging("unsupported_nets", $CLIENT, $VERSION, $NET);
	}

	return FALSE;
}

function TimeSinceSubmissionInSeconds($now, $time_of_submission, $offset)
{
	$time_of_submission = trim($time_of_submission);
	return $now - ( @strtotime($time_of_submission) + $offset );	// GMT
}

function CheckIPValidity($remote_ip, $ip)
{
	$ip_port = explode(":", $ip);	// $ip_port[0] = IP	$ip_port[1] = Port

	$ip_array = explode(".", $ip_port[0]);
	// http://www.rfc-editor.org/rfc/rfc3330.txt

	if(
		!(	// Check if it isn't a reserved address
			$ip_array[0] == 0		// "This" Network
			|| $ip_array[0] == 10	// Private Network
			|| $ip_array[0] == 127	// Loopback
			|| $ip_array[0] == 172 && ( $ip_array[1] >= 16 && $ip_array[1] <= 31 )	// Private Network
			|| $ip_array[0] == 192 && $ip_array[1] == 168	// Private Network
		)
	)
	{
		if( count($ip_port) == 2 &&
			ctype_digit($ip_port[1]) &&
			$ip_port[1] > 0 &&
			$ip_port[1] < 65536 &&
			$ip_port[0] == $remote_ip &&
			ip2long($ip_port[0]) == ip2long($remote_ip)
		)
			return TRUE;
	}

	if(LOG_MINOR_ERRORS)
	{
		global $CLIENT, $VERSION, $NET;
		Logging("invalid_ips", $CLIENT, $VERSION, $NET);
	}

	return FALSE;
}

function CheckURLValidity($cache)
{
	global $UDP;
	$uhc = $UDP["uhc"] == 1 && substr($cache, 0, 4) == "uhc:";
	$ukhl = $UDP["ukhl"] == 1 && substr($cache, 0, 5) == "ukhl:";

	if(strlen($cache) > 10)
		if( substr($cache, 0, 7) == "http://" || substr($cache, 0, 8) == "https://" || $uhc || $ukhl )
			if( !(strpos($cache, "?") > -1 || strpos($cache, "&") > -1 || strpos($cache, "#") > -1) )
				return TRUE;

	if(LOG_MINOR_ERRORS)
	{
		global $CLIENT, $VERSION, $NET;
		Logging("invalid_urls", $CLIENT, $VERSION, $NET);
	}

	return FALSE;
}

/* When bugs of GWCs are fixed, ask on http://sourceforge.net/p/skulls/discussion/ and the GWCs will be unlocked */
function CheckBlockedGWC($gwc_url)
{
	$gwc_url = strtolower($gwc_url);
	if(
		$gwc_url === 'http://cache.trillinux.org/g2/bazooka.php'  /* Bugged - return hosts with negative age */
		|| $gwc_url === 'http://fascination77.free.fr/cachechu/'  /* Bugged - Call to undefined function: stream_socket_client() */
	)
		return true;

	return false;
}

function CleanFailedUrls()
{
	$failed_urls_file = file(DATA_DIR."/failed_urls.dat");
	$file_count = count($failed_urls_file);
	$file = fopen(DATA_DIR."/failed_urls.dat", "wb");
	flock($file, LOCK_EX);

	$now = time();
	$offset = @date("Z");
	for($i = 0; $i < $file_count; $i++)
	{
		$failed_urls_file[$i] = rtrim($failed_urls_file[$i]);
		list( , $failed_time) = explode("|", $failed_urls_file[$i]);
		$time_diff = $now - ( @strtotime( $failed_time ) + $offset );	// GMT
		$time_diff = floor($time_diff / 86400);	// Days

		if( $time_diff < 2 ) fwrite($file, $failed_urls_file[$i]."\r\n");
	}

	flock($file, LOCK_UN);
	fclose($file);
}

function CheckFailedUrl($url)
{
	$file = file(DATA_DIR."/failed_urls.dat");
	$file_count = count($file);

	for($i = 0, $now = time(), $offset = @date("Z"); $i < $file_count; $i++)
	{
		$read = explode("|", $file[$i]);
		if($url == $read[0])
		{
			$read[1] = trim($read[1]);
			$time_diff = $now - ( @strtotime( $read[1] ) + $offset );	// GMT
			$time_diff = floor($time_diff / 86400);	// Days

			if( $time_diff < 2 ) return TRUE;
		}
	}

	return FALSE;
}

function AddFailedUrl($url)
{
	$file = fopen(DATA_DIR."/failed_urls.dat", "ab");
	flock($file, LOCK_EX);
	fwrite($file, $url."|".gmdate("Y/m/d H:i")."\r\n");
	flock($file, LOCK_UN);
	fclose($file);
}

function ReplaceHost($file_path, $line, $this_host, &$host_file, $recover_limit = false)
{
	$new_host_file = implode("", array_merge( array_slice($host_file, 0, $line), array_slice( $host_file, ($recover_limit ? $line + 2 : $line + 1) ) ) );

	$file = fopen($file_path, "wb");
	flock($file, LOCK_EX);
	fwrite($file, $new_host_file.$this_host);
	flock($file, LOCK_UN);
	fclose($file);
}

function ReplaceCache($cache_file, $line, $cache, $cache_data, $client, $version)
{
	$new_cache_file = implode("", array_merge( array_slice($cache_file, 0, $line), array_slice( $cache_file, ($line + 1) ) ) );

	$file = fopen(DATA_DIR."/caches.dat", "wb");
	flock($file, LOCK_EX);
	if($cache != NULL)
		fwrite($file, $new_cache_file.$cache."|".$cache_data[0]."|".$cache_data[1]."|".$client."|".$version."|".gmdate("Y/m/d h:i:s A")."\r\n");
	else
		fwrite($file, $new_cache_file);
	flock($file, LOCK_UN);
	fclose($file);
}

function PingGWC($gwc_url, $query)
{
	$errno = -1; $errstr = "";
	list( , $cache ) = explode("://", $gwc_url);	// It remove "http://" from $cache - $cache = www.test.com:80/page.php
	$main_url = explode("/", $cache);				// $main_url[0] = www.test.com:80		$main_url[1] = page.php

	/* Separate hostname from port */
	if(strpos($main_url[0], ':') !== false)
		{ list($host_name, $host_port) = explode(':', $main_url[0], 2); $host_port = (int)$host_port; }
	else
		{ $host_name = $main_url[0]; $host_port = 80; }

	$fp = @fsockopen($host_name, $host_port, $errno, $errstr, (float)TIMEOUT);
	if(!$fp)
	{
		$cache_data = "ERR|".$errno;				// ERR|Error name
		if(DEBUG) echo "\r\n".'D|update|ERROR|'.$errno.'|'.rtrim($errstr)."\r\n";
	}
	else
	{
		$pong = "";
		$oldpong = "";
		$nets_list1 = null;
		$error = "";

		$our_url = "";
		if(CACHE_URL !== "") $our_url = 'X-GWC-URL: '.CACHE_URL."\r\n";
		$host = $host_name.($host_port === 80 ? "" : ':'.$host_port);
		$common_headers = "Connection: close\r\nUser-Agent: ".NAME.' '.VER."\r\n".$our_url."\r\n";
		$out = "GET ".substr( $cache, strlen($main_url[0]), (strlen($cache) - strlen($main_url[0]) ) ).'?'.$query.' '.$_SERVER['SERVER_PROTOCOL']."\r\nHost: ".$host."\r\n".$common_headers;
		if(DEBUG) echo "\r\n".$out;

		if( !fwrite($fp, $out) )
		{
			$cache_data = "ERR|Request error";		// ERR|Error name
			fclose($fp);
		}
		else
		{
			while( !feof($fp) )
			{
				$line = rtrim(fgets($fp, 1024));
				if(DEBUG) echo $line."\r\n";

				if( strtolower( substr( $line, 0, 7 ) ) === "i|pong|" )
					$pong = rtrim($line);
				elseif(substr($line, 0, 4) === "PONG")
					$oldpong = rtrim($line);
				elseif(strtolower( substr($line, 0, 11) ) === "i|networks|")
					$nets_list1 = strtolower( substr($line, 11) );
				elseif(substr($line, 0, 5) == "ERROR" || strpos($line, "404 Not Found") > -1 || strpos($line, "403 Forbidden") > -1)
					$error .= rtrim($line)." - ";
				elseif( strtolower(substr($line, 0, 2)) == "i|" && strpos($line, "not") > -1 && strpos($line, "supported") > -1 )
					$error .= rtrim($line)." - ";
			}
			fclose($fp);

			if(!empty($pong))
			{
				$received_data = explode("|", $pong);
				$gwc_name = RemoveGarbage(rawurldecode($received_data[2]));
				$cache_data = "P|".$gwc_name;

				if($nets_list1 !== null)
					$nets = RemoveGarbage(str_replace( array('-', '|'), array('%2D', '-'), $nets_list1 ));
				elseif(count($received_data) > 3 && $received_data[3] != "")
				{
					if(substr($received_data[3], 0, 4) === "http")  /* Workaround for compatibility with PHPGnuCacheII */
						$nets = "gnutella-gnutella2";
					else
						$nets = RemoveGarbage(strtolower($received_data[3]));
				}
				elseif(strpos($gwc_name, 'GhostWhiteCrab') === 0)  /* On GhostWhiteCrab if the network is gnutella then the networks list is missing :( */
					$nets = "gnutella";
				elseif( !empty($oldpong) )
					$nets = "gnutella-gnutella2";
				else
					$nets = "gnutella2";

				$cache_data .= "|".$nets;		// P|Name of the GWC|Networks list
			}
			elseif(!empty($oldpong))
			{
				$oldpong = RemoveGarbage(rawurldecode(substr($oldpong, 5)));
				$cache_data = "P|".$oldpong;

				/* Needed to force specs v2 since it ignore all other ways, well it also break code by inserting the network list inside pong with the wrong separator */
				if(strpos($oldpong, 'Cachechu') === 0)
					return PingGWC($gwc_url, $query.'&update=1');

				if( substr($oldpong, 0, 13) == "PHPGnuCacheII" ||	// Workaround for compatibility
					//substr($oldpong, 0, 10) == "perlgcache" ||		// ToDO: Re-verify
					substr($oldpong, 0, 12) == "jumswebcache" ||
					substr($oldpong, 0, 11) == "GWebCache 2" )
					$nets = "gnutella-gnutella2";
				elseif(substr($oldpong, 0, 9) == "MWebCache")
					$nets = "mute";
				else
					$nets = "gnutella";

				$cache_data .= "|".$nets;		// P|Name of the GWC|Networks list
			}
			else
			{
				$error = RemoveGarbage(strtolower($error));
				$cache_data = "ERR|".$error;	// ERR|Error name
			}
		}
	}

	if(DEBUG) echo "\r\nD|update|Result: ".$cache_data."\r\n\r\n";
	return $cache_data;
}

function CheckGWC($cache, $cache_network)
{
	global $SUPPORTED_NETWORKS;

	$nets = NULL;
	if(strpos($cache, "://") > -1)
	{
		$udp = FALSE;
		$query = "ping=1&multi=1&pv=2&client=".VENDOR."&version=".SHORT_VER."&cache=1";
		$result = PingGWC($cache, $query);		// $result =>	P|Name of the GWC|Networks list	or	ERR|Error name
	}
	else
	{
		$udp = TRUE;
		include "udp.php";
		$result = PingUDP($cache);
	}
	$received_data = explode("|", $result);

	if($received_data[0] == "ERR" && !$udp)
	{
		if( strpos($received_data[1], "not supported") > -1
			|| strpos($received_data[1], "unsupported network") > -1
			|| strpos($received_data[1], "no network") > -1
			|| strpos($received_data[1], "net-not-supported") > -1
		)	// Workaround for compatibility with GWCv2 specs
		{																// FOR WEBCACHES DEVELOPERS: If you want avoid necessity to make double ping, make your cache pingable without network parameter when there are ping=1 and multi=1
			$query .= "&net=gnutella2";
			$result = PingGWC($cache, $query);
			$nets = "gnutella2";
		}
		elseif( strpos($received_data[1], "access denied by acl") > -1 )
		{
			$query = "ping=1&multi=1&pv=2&client=TEST&version=".VENDOR."%20".SHORT_VER."&cache=1";
			$result = PingGWC($cache, $query);
		}
		unset($received_data);
		$received_data = explode("|", $result);
	}

	if($received_data[0] == "ERR" || $received_data[1] == "")
		$cache_data[0] = "FAIL";
	else
	{
		if($nets == NULL) $nets = $received_data[2];
		if(CheckNetworkString($SUPPORTED_NETWORKS, $nets))
		{
			$cache_data[0] = $received_data[1];
			$cache_data[1] = $nets;
		}
		else
			$cache_data[0] = "UNSUPPORTED";
	}

	return $cache_data;
}

function WriteHostFile($net, $h_ip, $h_port, $h_leaves, $h_max_leaves, $h_uptime, $h_vendor, $h_ver, $h_ua, $h_suspect = '0')
{
	global $SUPPORTED_NETWORKS;

	// return 4; Unused
	$file_path = DATA_DIR.'/hosts_'.$net.'.dat';
	$host_file = file($file_path);
	$file_count = count($host_file);
	$host_exists = FALSE;

	for($i = 0; $i < $file_count; $i++)
	{
		list($time, $read_ip, ) = explode('|', $host_file[$i], 3);
		if($h_ip === $read_ip)
		{
			$host_exists = TRUE;
			break;
		}
	}
	$this_host = gmdate('Y/m/d h:i:s A').'|'.$h_ip.'|'.$h_port.'|'.$h_leaves.'|'.$h_max_leaves.'|'.$h_uptime.'|'.RemoveGarbage($h_vendor).'|'.RemoveGarbage($h_ver).'|'.RemoveGarbage($h_ua).'|'.$h_suspect."|||\n";

	if($host_exists)
	{
		$time_diff = time() - ( @strtotime( $time ) + @date("Z") );	// GMT
		$time_diff = floor($time_diff / 3600);	// Hours

		if( $time_diff < 24 )
			return 0; // Exists
		else
		{
			ReplaceHost($file_path, $i, $this_host, $host_file);
			return 1; // Updated timestamp
		}
	}
	else
	{
		if($file_count > MAX_HOSTS || $file_count > 100)
		{
			ReplaceHost($file_path, 0, $this_host, $host_file, true);
			return 3; // OK, pushed old data
		}
		elseif($file_count == MAX_HOSTS)
		{
			ReplaceHost($file_path, 0, $this_host, $host_file);
			return 3; // OK, pushed old data
		}
		else
		{
			$file = fopen($file_path, "ab");
			flock($file, LOCK_EX);
			fwrite($file, $this_host);
			flock($file, LOCK_UN);
			fclose($file);
			return 2; // OK
		}
	}
}

function WriteCacheFile($cache, $net, $client, $version)
{
	global $MY_URL;

	if($cache === $MY_URL)  /* It doesn't allow to insert itself in the GWC list */
		return 0; // Exists

	$cache = RemoveGarbage($cache);
	if(CheckFailedUrl($cache))
		return 4; // Failed URL

	$client = RemoveGarbage($client);
	$version = RemoveGarbage($version);
	$cache_file = file(DATA_DIR."/caches.dat");
	$file_count = count($cache_file);
	$cache_exists = FALSE;

	for($i = 0; $i < $file_count; $i++)
	{
		list( $read, ) = explode("|", $cache_file[$i]);

		if( strtolower($cache) == strtolower($read) )
		{
			list( , , , , , $time ) = explode("|", rtrim($cache_file[$i]));
			$cache_exists = TRUE;
			break;
		}
	}

	if($cache_exists)
	{
		$time_diff = time() - ( @strtotime( $time ) + @date("Z") );	// GMT
		$time_diff = floor($time_diff / 3600);	// Hours
		if(RECHECK_CACHES < 12) $recheck_caches = 12; else $recheck_caches = RECHECK_CACHES;

		if( $time_diff < $recheck_caches )
			return 0; // Exists
		else
		{
			$cache_data = CheckGWC($cache, $net);

			if($cache_data[0] == "FAIL")
			{
				AddFailedUrl($cache);
				ReplaceCache( $cache_file, $i, NULL, NULL, NULL, NULL );
				return 5; // Ping failed
			}
			elseif($cache_data[0] == "UNSUPPORTED")
			{
				AddFailedUrl($cache);
				ReplaceCache( $cache_file, $i, NULL, NULL, NULL, NULL );
				return 6; // Unsupported network
			}
			else
			{
				ReplaceCache( $cache_file, $i, $cache, $cache_data, $client, $version );
				return 1; // Updated timestamp
			}
		}
	}
	else
	{
		if(CheckBlockedGWC($cache))
			return 4; // Blocked URL
		else
		{
			$cache_data = CheckGWC($cache, $net);

			if($cache_data[0] == "FAIL")
			{
				AddFailedUrl($cache);
				return 5; // Ping failed
			}
			elseif($cache_data[0] == "UNSUPPORTED")
			{
				AddFailedUrl($cache);
				return 6; // Unsupported network
			}
			else
			{
				if($file_count >= MAX_CACHES || $file_count > 100)
				{
					ReplaceCache( $cache_file, 0, $cache, $cache_data, $client, $version );
					return 3; // OK, pushed old data
				}
				else
				{
					$file = fopen(DATA_DIR."/caches.dat", "ab");
					flock($file, LOCK_EX);
					fwrite($file, $cache."|".$cache_data[0]."|".$cache_data[1]."|".$client."|".$version."|".gmdate("Y/m/d h:i:s A")."\r\n");
					flock($file, LOCK_UN);
					fclose($file);
					return 2; // OK
				}
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

function HostFile($net)
{
	$host_file = file(DATA_DIR."/hosts_".$net.".dat");
	$count_host = count($host_file);

	if($count_host <= MAX_HOSTS_OUT)
		$max_hosts = $count_host;
	else
		$max_hosts = MAX_HOSTS_OUT;

	for( $i = 0; $i < $max_hosts; $i++ )
	{
		list( , $h_ip, $h_port, ) = explode('|', $host_file[$count_host - 1 - $i], 4);
		echo $h_ip,':',$h_port,"\r\n";
	}
}

function UrlFile($net)
{
	$cache_file = file(DATA_DIR."/caches.dat");
	$count_cache = count($cache_file);

	for( $n = 0, $i = $count_cache - 1; $n < MAX_CACHES_OUT && $i >= 0; $i-- )
	{
		list( $cache, , $cache_net, ) = explode("|", $cache_file[$i]);

		$show = FALSE;
		if(strpos($cache_net, "-") > -1)
		{
			$cache_networks = explode("-", $cache_net);
			$cache_nets_count = count($cache_networks);
			for( $x=0; $x < $cache_nets_count; $x++ )
			{
				if( $cache_networks[$x] == $net)
				{
					$show = TRUE;
					break;
				}
			}
		}
		elseif($cache_net == $net)
			$show = TRUE;

		if($show && strpos($cache, "://") > -1)
		{
			echo $cache."\r\n";
			$n++;
		}
	}
}

function Get($net, $get, $getleaves, $getvendors, $uhc, $ukhl, $add_dummy_host)
{
	$output = "";
	$now = time();
	$offset = @date("Z");
	$separators = 0;
	if($getvendors) $separators = 3;
	elseif($getleaves) $separators = 2;

	if($get)
	{
		$host_file = file(DATA_DIR."/hosts_".$net.".dat");
		$count_host = count($host_file);

		if($count_host <= MAX_HOSTS_OUT)
			$max_hosts = $count_host;
		else
			$max_hosts = MAX_HOSTS_OUT;

		for( $i=0; $i<$max_hosts; $i++ )
		{
			list( $h_age, $h_ip, $h_port, $h_leaves, , , $h_vendor, /* $h_ver */, /* $h_ua */, /* $h_suspect */, ) = explode('|', $host_file[$count_host - 1 - $i], 13);
			$host = 'H|'.$h_ip.':'.$h_port.'|'.TimeSinceSubmissionInSeconds( $now, $h_age, $offset );
			if($separators > 1) $host .= '||';
			if($getleaves) $host .= $h_leaves;
			if($separators > 2) $host .= '|';
			if($getvendors && $h_vendor !== 'KICKSTART') $host .= $h_vendor;
			$output .= $host."\r\n";
		}
		/* Workaround for a bug, some old Shareaza versions doesn't send updates if we don't have any host */
		if($count_host === 0 && $add_dummy_host)
		{
			$output .= "H|1.1.1.1:7331|100000\r\n";
			$count_host = 1;
		}
	}
	else
		$count_host = 0;

	if(FSOCKOPEN)
	{
		$cache_file = file(DATA_DIR."/caches.dat");
		$count_cache = count($cache_file);

		if($get)
		{
			for( $n=0, $i=$count_cache-1; $n<MAX_CACHES_OUT && $i>=0; $i-- )
			{
				list( $cache, , $cache_net, , , $time ) = explode("|", $cache_file[$i]);

				$show = FALSE;
				if(strpos($cache_net, "-") > -1)
				{
					$cache_networks = explode("-", $cache_net);
					$cache_nets_count = count($cache_networks);
					for( $x=0; $x < $cache_nets_count; $x++ )
					{
						if( $cache_networks[$x] == $net)
						{
							$show = TRUE;
							break;
						}
					}
				}
				elseif($cache_net == $net)
					$show = TRUE;

				if($show && strpos($cache, "://") > -1)
				{
					$cache = "U|".$cache."|".TimeSinceSubmissionInSeconds( $now, rtrim($time), $offset );
					$output .= $cache."\r\n";
					$n++;
				}
			}
		}

		if($uhc)
		{
			for( $n=0, $i=$count_cache-1; $n<MAX_UHC_CACHES_OUT && $i>=0; $i-- )
			{
				list( $cache, , $cache_net, , , $time ) = explode("|", $cache_file[$i]);

				$show = FALSE;
				if( $cache_net == "gnutella" && !(strpos($cache, "://") > -1) )
					$show = TRUE;

				if($show)
				{
					$cache = "U|".$cache."|".TimeSinceSubmissionInSeconds( $now, rtrim($time), $offset );
					$output .= $cache."\r\n";
					$n++;
				}
			}
		}
	}
	else
		$count_cache = 0;

	if( $count_host == 0 && $count_cache == 0 )
		$output .= "I|NO-URL-NO-HOSTS\r\n";
	elseif( $count_cache == 0 )
		$output .= "I|NO-URL\r\n";
	elseif( $count_host == 0 )
		$output .= "I|NO-HOSTS\r\n";

	echo $output;
}

function StartCompression($COMPRESSION)
{
	if($COMPRESSION == "deflate")
		{ $compressed = TRUE; ob_start("gzcompress"); }
	else
		{ $compressed = FALSE; }
	if($compressed) header("Content-Encoding: deflate");

	return $compressed;
}

function CleanStats($request)
{
	$now = time();
	$offset = @date("Z");
	$file_count = 0;
	$line_length = 17;
	$file = fopen( "stats/".$request."_requests_hour.dat", "rb" );

	if(OPTIMIZED_STATS)
	{
		while(!feof($file))
		{
			$current_stat = fgets($file, 20);
			$time_diff = $now - ( @strtotime($current_stat) + $offset );	// GMT
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
		$time_diff = $now - ( @strtotime($current_stat) + $offset );	// GMT
		$time_diff = floor($time_diff / 3600);	// Hours

		if($time_diff < 1)
		{
			$stat_file[$file_count] = rtrim($current_stat);
			$file_count++;
		}
	}
	fclose($file);


	set_time_limit("20");
	$file = fopen("stats/".$request."_requests_hour.dat", "wb");
	flock($file, LOCK_EX);
	for($i = 0; $i < $file_count; $i++)
		fwrite($file, $stat_file[$i]."\n");
	flock($file, LOCK_UN);
	fclose($file);
}

function ReadStats($request)
{
	$requests = 0;
	$now = time();
	$offset = @date("Z");
	$line_length = 17;
	$file = fopen( "stats/".$request."_requests_hour.dat", "rb" );

	if(OPTIMIZED_STATS)
	{
		while(!feof($file))
		{
			$current_stat = fgets($file, 20);
			$time_diff = $now - ( @strtotime($current_stat) + $offset );	// GMT
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
		$time_diff = $now - ( @strtotime($current_stat) + $offset );	// GMT
		$time_diff = floor($time_diff / 3600);	// Hours

		if($time_diff < 1)
			$requests++;
	}
	fclose($file);

	return $requests;
}

function UpdateStats($request)
{
	if(!STATS_ENABLED) return;

	$file = fopen("stats/".$request."_requests_hour.dat", "ab");
	flock($file, LOCK_EX);
	fwrite($file, gmdate("Y/m/d H:i")."\n");
	flock($file, LOCK_UN);
	fclose($file);
}


$PHP_VERSION = (float)PHP_VERSION;

$PING = !empty($_GET["ping"]) ? $_GET["ping"] : 0;

$NET = !empty($_GET["net"]) ? strtolower($_GET["net"]) : NULL;
$IS_A_CACHE = !empty($_GET["cache"]) ? $_GET["cache"] : 0;		// This must be added to every request made by a cache, to let it know that we are a cache and not a client
$MULTI = !empty($_GET["multi"]) ? $_GET["multi"] : 0;			// It is added to every ping request (it has no effect on other things), it tell to the pinged cache to ignore the "net" parameter and outputting the pong using this format, if possible, "I|pong|[cache name] [cache version]|[supported networks list]" - example: I|pong|Skulls 0.3.0|gnutella-gnutella2
$PV = !empty($_GET["pv"]) ? $_GET["pv"] : 0;
$UHC = !empty($_GET["uhc"]) && $PHP_VERSION >= 4.3 ? $_GET["uhc"] : 0;
$UKHL = !empty($_GET["ukhl"]) && $PHP_VERSION >= 4.3 ? $_GET["ukhl"] : 0;

$INFO = !empty($_GET["info"]) ? $_GET["info"] : 0;				// This tell to the cache to show info like the name, the version, the vendor code, the home page of the cache, the nick and the website of the maintainer (the one that has put the cache on a webserver)

$UA_ORIGINAL = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
$USER_AGENT = str_replace('/', ' ', $UA_ORIGINAL);

$COMPRESSION = !empty($_GET["compression"]) ? strtolower($_GET["compression"]) : NULL;	// It tell to the cache what compression to use (it override HTTP_ACCEPT_ENCODING), currently values are: deflate, none
$ACCEPT_ENCODING = !empty($_SERVER["HTTP_ACCEPT_ENCODING"]) ? $_SERVER["HTTP_ACCEPT_ENCODING"] : NULL;
/* The deflate compression in HTTP 1.1 is the format specified by RFC 1950 instead Internet Explorer incorrectly interpret it as RFC 1951 (buggy IE, what surprise!!!) */
if($COMPRESSION === null && strpos($ACCEPT_ENCODING, "deflate") !== false && strpos($UA_ORIGINAL, '; MSIE ') === false && !DEBUG) $COMPRESSION = "deflate";

$HOST = !empty($_GET["ip"]) ? $_GET["ip"] : ( !empty($_GET["ip1"]) ? $_GET["ip1"] : NULL );
$IP = null; $PORT = null; $GOOD_PORT = true;
$CACHE = !empty($_GET["url"]) ? $_GET["url"] : ( !empty($_GET["url1"]) ? $_GET["url1"] : NULL );
$LEAVES = isset($_GET['x_leaves']) ? $_GET['x_leaves'] : null;
$MAX_LEAVES = isset($_GET['x_max']) ? $_GET['x_max'] : null;
$CLUSTER = !empty($_GET['cluster']) ? $_GET['cluster'] : null;

$HOSTFILE = !empty($_GET["hostfile"]) ? $_GET["hostfile"] : 0;
$URLFILE = !empty($_GET["urlfile"]) ? $_GET["urlfile"] : 0;
$STATFILE = !empty($_GET["statfile"]) ? $_GET["statfile"] : 0;

$BFILE = !empty($_GET["bfile"]) ? $_GET["bfile"] : 0;
if($BFILE) { $HOSTFILE = 1; $URLFILE = 1; }

$GET = !empty($_GET["get"]) ? $_GET["get"] : 0;
$UPDATE = !empty($_GET["update"]) ? $_GET["update"] : 0;

$CLIENT = !empty($_GET['client']) ? $_GET['client'] : "";
$VERSION = !empty($_GET['version']) ? $_GET['version'] : "";

$SUPPORT = empty($_GET['support']) ? 0 : $_GET['support'];
$GETNETWORKS = empty($_GET['getnetworks']) ? 0 : $_GET['getnetworks'];

$GETLEAVES = empty($_GET['getleaves']) ? 0 : $_GET['getleaves'];
$GETVENDORS = empty($_GET['getvendors']) ? 0 : $_GET['getvendors'];

$NO_IP_HEADER = empty($_GET['noipheader']) ? 0 : $_GET['noipheader'];


$SHOWINFO = !empty($_GET['showinfo']) ? $_GET['showinfo'] : 0;
$SHOWHOSTS = !empty($_GET['showhosts']) ? $_GET['showhosts'] : 0;
$SHOWCACHES = !empty($_GET['showurls']) ? $_GET['showurls'] : 0;
$SHOWSTATS = !empty($_GET['stats']) ? $_GET['stats'] : 0;
$SHOWDATA = !empty($_GET['data']) ? $_GET['data'] : 0;

$KICK_START = !empty($_GET['kickstart']) ? $_GET['kickstart'] : 0;	// It request hosts from a caches specified in the "url" parameter for a network specified in "net" parameter (it is used the first time to populate the cache, it MUST be disabled after that).

if( empty($_SERVER['QUERY_STRING']) )
	$SHOWINFO = 1;

if( isset($noload) ) die();

if(MAINTAINER_NICK === 'your nickname here' || MAINTAINER_NICK === "")
{
	echo "You must read readme.txt in the admin directory first.\r\n";
	die();
}

if($NET === 'gnutella1')
	$NET = 'gnutella';
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


if( $SHOWINFO )
	$web = 1;
elseif( $SHOWHOSTS )
	$web = 2;
elseif( $SHOWCACHES )
	$web = 3;
elseif( $SHOWSTATS || $SHOWDATA )
	$web = 4;
else
	$web = 0;

if($web)
{
	include "web_interface.php";

	$compressed = StartCompression($COMPRESSION);
	ShowHtmlPage($web);
	if($compressed) ob_end_flush();
}
elseif( $KICK_START )
{
	if( !KICK_START_ENABLED )
		die("ERROR: Kickstart is disabled\r\n");

	if( $NET == NULL )
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
	if(!CONTENT_TYPE_WORKAROUND)
		header('Content-Type: text/plain; charset=UTF-8');
	else
		header('Content-Type: application/octet-stream');

	if(STATS_ENABLED)
	{
		$file = fopen("stats/requests.dat", "r+b");
		flock($file, LOCK_EX);
		$requests = fgets($file, 50);
		if($requests == "") $requests = 1; else $requests++;
		rewind($file);
		fwrite($file, $requests);
		flock($file, LOCK_UN);
		fclose($file);
	}

	NormalizeIdentity($CLIENT, $VERSION, $UA_ORIGINAL);
	if( !ValidateIdentity($CLIENT, $VERSION) )
	{
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		echo "ERROR: Invalid client identification\r\n";
		if(LOG_MINOR_ERRORS) Logging('unidentified_clients', $CLIENT, $VERSION, $NET);
		UpdateStats("other");
		die();
	}

	/* Separate ip from port for the submitted host, it will be used later */
	if($HOST !== null)
	{
		list($IP, $PORT) = explode(':', $HOST, 2);
		$GOOD_PORT = ValidatePort($PORT);
	}

	if($CLIENT === 'MUTE')  /* There are MUTE (MUTE network client) and Mutella (Gnutella network client), both identify themselves as MUTE */
	{
		if(strpos($UA_ORIGINAL, 'Mutella') === 0)
			$CLIENT = 'MTLL';
		else
			$NET = 'mute';  /* Changed network parameter for MUTE clients to prevent leakage on G1/G2 */
	}
	elseif($CLIENT === 'FOXY')
		$NET = 'foxy';      /* Enforced network parameter for Foxy clients to prevent leakage on G1/G2 */

	if($NET === null) $NET = 'gnutella';  /* This should NOT absolutely be changed (also if your GWC doesn't support the gnutella network) otherwise you will mix hosts of different networks and it is bad */

	/* Block also missing REMOTE_ADDR, although it is unlikely, apparently it could happen in some configurations */
	if( !VerifyUserAgent($CLIENT, $UA_ORIGINAL) || !VerifyVersion($CLIENT, $VERSION) || !$GOOD_PORT || $REMOTE_IP === 'unknown' || $REMOTE_IP == "" )
	{
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		if(LOG_MINOR_ERRORS) Logging('bad_old_clients', $CLIENT, $VERSION, $NET);
		UpdateStats("other");
		die();
	}

	/* getnetworks=1 is the same of support=2, in case it is specified then the old support=1 is ignored */
	if($GETNETWORKS)
		$SUPPORT = 2;

	if($IS_A_CACHE || $CLIENT === 'TEST')
	{
		$HOST = null;         /* Block host submission by caches, they don't do it */
		$NO_IP_HEADER = 1;  /* Do NOT send X-Remote-IP header to caches, they don't need it */
	}

	if(!$PING && !$GET && !$UHC && !$UKHL && !$SUPPORT && !$HOSTFILE && !$URLFILE && !$STATFILE && $CACHE == NULL && $HOST == NULL && !$INFO)
	{
		print "ERROR: Invalid command - Request rejected\r\n";
		if(LOG_MAJOR_ERRORS) Logging("invalid_queries", $CLIENT, $VERSION, $NET);
		UpdateStats("other");
		die();
	}

	if($LEAVES !== null && ( !ctype_digit($LEAVES) || $LEAVES > 2047 ))
	{
		$LEAVES = null;
		if(LOG_MAJOR_ERRORS) Logging("invalid_leaves", $CLIENT, $VERSION, $NET);
	}
	if($MAX_LEAVES !== null && ( !ctype_digit($MAX_LEAVES) || $MAX_LEAVES > 2047 ))
	{
		$MAX_LEAVES = null;
		if(LOG_MAJOR_ERRORS) Logging("invalid_max_leaves", $CLIENT, $VERSION, $NET);
	}
	$CLUSTER = null;

	$compressed = StartCompression($COMPRESSION);

	//$CACHE_IS_VALID = true;
	if($CACHE !== null)
		if(!CanonicalizeURL($CACHE))
			$CACHE = 'BLOCKED';

	if(!$NO_IP_HEADER)
	{
		if(!empty($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] !== 'unknown')
			header('X-Remote-IP: '.$_SERVER['HTTP_CLIENT_IP']);  /* Check for shared internet/ISP IP */
		elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== 'unknown')
			header('X-Remote-IP: '.$_SERVER['HTTP_X_FORWARDED_FOR']);  /* Check for IPs passing through proxies */
		else
			header('X-Remote-IP: '.$REMOTE_IP);
	}

	if( CheckNetworkString($SUPPORTED_NETWORKS, $NET, FALSE) )
		$supported_net = TRUE;
	else
	{
		$supported_net = FALSE;
		if(($PING && !$MULTI && !$SUPPORT) || $GET || $HOSTFILE || $URLFILE || $CACHE != NULL || $HOST != NULL) echo "ERROR: Network not supported\r\n";
	}

	if($PING)
		Pong($SUPPORT, $MULTI, $NET, $CLIENT, $VERSION, $supported_net, $REMOTE_IP);
	if($SUPPORT)
		Support($SUPPORT, $SUPPORTED_NETWORKS, $UDP);

	if($UPDATE)
	{
		if( $HOST != NULL && $supported_net )
		{
			if( CheckIPValidity($REMOTE_IP, $HOST) )
			{
				$result = WriteHostFile($NET, $IP, $PORT, $LEAVES, $MAX_LEAVES, "", $CLIENT, $VERSION, $UA_ORIGINAL);

				if( $result == 0 ) // Exists
					print "I|update|OK|Host already updated\r\n";
				elseif( $result == 1 ) // Updated timestamp
					print "I|update|OK|Updated host timestamp\r\n";
				elseif( $result == 2 ) // OK
					print "I|update|OK|Host added\r\n";
				elseif( $result == 3 ) // OK, pushed old data
					print "I|update|OK|Host added (pushed old data)\r\n";
				else
					print "I|update|ERROR|Unknown error 1, return value = ".$result."\r\n";
			}
			else // Invalid IP
				print "I|update|WARNING|Invalid host"."\r\n";
		}

		if( $CACHE != NULL && $supported_net )
		{
			if(!FSOCKOPEN) // Cache adding disabled
				print "I|update|WARNING|URL adding is disabled\r\n";
			elseif( CheckURLValidity($CACHE) )
			{
				$result = WriteCacheFile($CACHE, $NET, $CLIENT, $VERSION);

				if( $result == 0 ) // Exists
					print "I|update|OK|URL already updated\r\n";
				elseif( $result == 1 ) // Updated timestamp
					print "I|update|OK|Updated URL timestamp\r\n";
				elseif( $result == 2 ) // OK
					print "I|update|OK|URL added\r\n";
				elseif( $result == 3 ) // OK, pushed old data
					print "I|update|OK|URL added (pushed old data)\r\n";
				elseif( $result == 4 ) // Blocked or failed URL
					print "I|update|OK|Blocked URL\r\n";
				elseif( $result == 5 ) // Ping failed
					print "I|update|WARNING|Ping of ".$CACHE." failed\r\n";
				elseif( $result == 6 ) // Unsupported network
					print "I|update|WARNING|Network of URL not supported\r\n";
				else
					print "I|update|ERROR|Unknown error 2, return value = ".$result."\r\n";
			}
			else // Invalid URL
				print("I|update|WARNING|Invalid URL"."\r\n");
		}
	}
	else
	{
		if( $supported_net && ( $HOST != NULL || $CACHE != NULL ) )
			print "OK\r\n";

		if( $HOST != NULL && $supported_net )
		{
			if( CheckIPValidity($REMOTE_IP, $HOST) )
				$result = WriteHostFile($NET, $IP, $PORT, $LEAVES, $MAX_LEAVES, "", $CLIENT, $VERSION, $UA_ORIGINAL);
			else // Invalid IP
				print "WARNING: Invalid host"."\r\n";
		}

		if( $CACHE != NULL && $supported_net )
		{
			if(!FSOCKOPEN) // Cache adding disabled
				print "WARNING: URL adding is disabled\r\n";
			elseif( CheckURLValidity($CACHE) )
			{
				$result = WriteCacheFile($CACHE, $NET, $CLIENT, $VERSION);

				if( $result == 5 ) // Ping failed
					print "WARNING: Ping of ".$CACHE." failed\r\n";
				elseif( $result == 6 ) // Unsupported network
					print "WARNING: Network of URL not supported\r\n";
			}
			else // Invalid URL
				print "WARNING: Invalid URL"."\r\n";
		}
	}

	if(!$supported_net) $GET = 0;

	if($GET || $UHC || $UKHL)
	{
		$dummy_host_needed = CheckIfDummyHostIsNeeded($CLIENT, $VERSION);

		Get($NET, $GET, $GETLEAVES, $GETVENDORS, $UHC, $UKHL, $dummy_host_needed);
		if($UHC || $UKHL)
		{
			echo "I|uhc|".$UDP["uhk"]."\r\n";
			echo "I|ukhl|".$UDP["ukhl"]."\r\n";
		}
	}
	elseif($supported_net)
	{
		if($HOSTFILE)
			HostFile($NET);

		if($URLFILE && FSOCKOPEN)
			UrlFile($NET);

		if($PV >= 3 && ($HOSTFILE || $URLFILE))
			echo "nets: ".strtolower(NetsToString())."\r\n";
	}

	if($CACHE != NULL || $HOST != NULL)
		UpdateStats("update");
	else
		UpdateStats("other");

	if($INFO)
	{
		echo "I|name|".NAME."\r\n";
		echo "I|ver|".VER."\r\n";
		echo "I|vendor|".VENDOR."\r\n";
		echo "I|gwc-site|".GWC_SITE."\r\n";
		echo "I|open-source|".OPEN_SOURCE."\r\n";

		echo "I|maintainer|".MAINTAINER_NICK."\r\n";
		if(MAINTAINER_WEBSITE != "http://www.your-site.com/" && MAINTAINER_WEBSITE != "")
			echo "I|maintainer-site|".MAINTAINER_WEBSITE."\r\n";
	}

	if($STATFILE)
	{
		if(STATS_ENABLED)
		{
			$requests = file("stats/requests.dat");
			echo $requests[0]."\r\n";

			$other_requests = ReadStats("other");
			$update_requests = ReadStats("update");

			echo ($other_requests + $update_requests)."\r\n";
			echo $update_requests."\r\n";
		}
		else
			echo "WARNING: Statfile disabled\r\n";
	}


	$clean_file = NULL;
	$changed = FALSE;
	$file = fopen( DATA_DIR."/last_action.dat", "r+b" );
	flock($file, LOCK_EX);
	$last_action_string = fgets($file, 50);

	if($last_action_string != "")
	{
		list($last_ver, $last_stats_status, $last_action, $last_action_date) = explode("|", $last_action_string);
		$time_diff = time() - ( @strtotime( $last_action_date ) + @date("Z") );	// GMT
		$time_diff = floor($time_diff / 3600);	// Hours
		if($time_diff >= 1 && $CACHE == NULL)
		{
			$last_action++;
			switch($last_action)
			{
				default:
					$last_action = 0;
				case 0:
					$clean_file = "stats";
					$clean_type = "other";
					break;
				case 1:
					$clean_file = "stats";
					$clean_type = "update";
					break;
				case 2:
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

	if($compressed) ob_end_flush();

	if($clean_file == "stats")
		CleanStats($clean_type);
	elseif($clean_file == "failed_urls")
		CleanFailedUrls();
}
?>