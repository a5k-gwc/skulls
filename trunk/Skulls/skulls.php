<?php
//
//   Skulls! Multi-Network WebCache (PHP)
//
//   Copyright (C) 2005-2007 by ale5000
//   Sources of this script can be downloaded here: http://sourceforge.net/projects/skulls/
//
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of the GNU General Public License
//   as published by the Free Software Foundation; either version 2
//   of the License, or (at your option) any later version.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
//

$SUPPORTED_NETWORKS = NULL;
include "vars.php";

if( !isset($_GET) )
{
	$_SERVER = &$HTTP_SERVER_VARS;
	$_GET = &$HTTP_GET_VARS;
}

$PHP_SELF = $_SERVER["PHP_SELF"];
$REMOTE_IP = $_SERVER["REMOTE_ADDR"];

if(!ENABLED || basename($PHP_SELF) == "index.php")
{
	header("HTTP/1.0 404 Not Found");
	die("ERROR: Service disabled\r\n");
}

/*if($REMOTE_IP == "...") { header("HTTP/1.0 404 Not Found"); die(); }*/

$SERVER_NAME = !empty($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : $_SERVER["HTTP_HOST"];
$SERVER_PORT = !empty($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : 80;
$MY_URL = $SERVER_PORT != 80 ? $SERVER_NAME.":".$SERVER_PORT.$PHP_SELF : $SERVER_NAME.$PHP_SELF;
if(CACHE_URL != "")
{
	list( , $CACHE_URL ) = explode("://", CACHE_URL);
	if($MY_URL != $CACHE_URL)
	{
		header("HTTP/1.0 301 Moved Permanently");
		header("Location: ".CACHE_URL);
		die();
	}
}

define( "NAME", "Skulls" );
define( "VENDOR", "SKLL" );
define( "SHORT_VER", "0.2.8" );
define( "VER", SHORT_VER."c" );

if($SUPPORTED_NETWORKS == NULL)
	die("ERROR: No network is supported.");

$networks_count = count($SUPPORTED_NETWORKS);
define( "NETWORKS_COUNT", $networks_count );

function GetMicrotime(){ 
    list($usec, $sec) = explode(" ",microtime()); 
    return (float)$usec + (float)$sec; 
}

function NetsToString(){
	global $SUPPORTED_NETWORKS;
	$nets = "";

	for( $i=0; $i < NETWORKS_COUNT; $i++ )
	{
		if($i) $nets .= "-";
		$nets .= $SUPPORTED_NETWORKS[$i];
	}
	return $nets;
}

function RemoveGarbage($value){
	$value = str_replace("|", "", $value);
	$value = str_replace("\r", "", $value);
	$value = str_replace("\n", "", $value);
	return str_replace("\0", "", $value);
}

function Pong($multi, $net, $client, $supported_net, $remote_ip){
	if($remote_ip == "127.0.0.1")	// Prevent caches that incorrectly point to 127.0.0.1 to being added to cache list
		return;

	$pong = "I|pong|".NAME." ".VER;

	if($multi)
	{
		$nets = strtolower(NetsToString());
		echo $pong."|".$nets."|".FSOCKOPEN."|TCP\r\n";
	}
	elseif($supported_net)
	{
		if($net == "gnutella" || $net == "mute")
			echo "PONG ".NAME." ".VER."\r\n";

		global $SUPPORTED_NETWORKS;
		if(NETWORKS_COUNT > 1 || strtolower($SUPPORTED_NETWORKS[0]) != "gnutella")
		{
			$nets = strtolower(NetsToString());
			if($client == "TEST" && $net == "gnutella2" && $nets != "gnutella2")
				echo $pong."|gnutella2|".FSOCKOPEN."|COMPAT|".$nets."|TCP\r\n";	// Workaround for compatibility with Bazooka
			elseif($client == "GCII" && $net == "gnutella2")
				echo $pong."||".FSOCKOPEN."|COMPAT|".$nets."|TCP\r\n";			// Workaround for compatibility with PHPGnuCacheII
			else
				echo $pong."|".$nets."|".FSOCKOPEN."|TCP\r\n";
		}
	}
}

function Support($supported_networks, $udp)
{
	for( $i = 0; $i < NETWORKS_COUNT; $i++ )
		echo "I|support|".strtolower($supported_networks[$i])."\r\n";
	echo "I|compression|none\r\n";
	echo "I|compression|deflate\r\n";
	echo "I|url|".FSOCKOPEN."\r\n";
	echo "I|uhc|".$udp["uhc"]."\r\n";
	echo "I|ukhl|".$udp["ukhl"]."\r\n";
}

function CheckNetwork($supported_networks, $net){
	for( $i = 0; $i < NETWORKS_COUNT; $i++ )
		if( strtolower($supported_networks[$i]) == strtolower($net) )
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

function TimeSinceSubmissionInSeconds($now, $time_of_submission, $offset){
	$time_of_submission = trim($time_of_submission);
	return $now - ( @strtotime($time_of_submission) + $offset );	// GMT
}

function CheckIPValidity($remote_ip, $ip){
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

function CheckURLValidity($cache){
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

// When bugs of caches are fixed, ask here http://sourceforge.net/tracker/?atid=797138&group_id=155771&func=browse and the caches will be unlocked
function CheckBlockedCache($cache){
	$cache = strtolower($cache);
	if(
		// Bad
		$cache == "http://www.xolox.nl/gwebcache/"
		|| $cache == "http://www.xolox.nl/gwebcache/default.asp"
		|| $cache == "http://reukiodo.dyndns.org/gwebcache/gwcii.php"
	)
		return TRUE;

	return FALSE;
}

function IsClientTooOld($client, $version){
    switch($client)
	{
		case "RAZA":
		case "RAZB":
			if((float)$version < 2.2)	// This also block some ripp-offs that are based on old versions of Shareaza
				return TRUE;
			break;
		case "BEAR":
			if((float)$version < 6.1)
			{
				$short_ver = substr($version, 0, 5);
				if($short_ver != "5.1.0" && $short_ver != "5.2.1" )
					return TRUE;
			}
			break;
		case "SKLL":
			if((int)$version == 0)
				if((float)substr($version, 2) < 2.4)
					return TRUE;
			break;
    }

	return FALSE;
}

function CleanFailedUrls(){
	$failed_urls_file = file("data/failed_urls.dat");
	$file_count = count($failed_urls_file);
	$file = fopen("data/failed_urls.dat", "wb");
	flock($file, 2);

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

	flock($file, 3);
	fclose($file);
}

function CheckFailedUrl($url){
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

function AddFailedUrl($url){
	$file = fopen(DATA_DIR."/failed_urls.dat", "ab");
	flock($file, 2);
	fwrite($file, $url."|".gmdate("Y/m/d H:i")."\r\n");
	flock($file, 3);
	fclose($file);
}

function ReplaceHost($host_file, $line, $ip, $leaves, $net, $cluster, $client, $version, $recover_limit = FALSE){
	$new_host_file = implode("", array_merge( array_slice($host_file, 0, $line), array_slice( $host_file, ($recover_limit ? $line + 2 : $line + 1) ) ) );

	$file = fopen(DATA_DIR."/hosts_".$net.".dat", "wb");
	flock($file, 2);
	fwrite($file, $new_host_file.$ip."|".$leaves."|".$cluster."|".$client."|".$version."|".gmdate("Y/m/d h:i:s A")."\r\n");
	flock($file, 3);
	fclose($file);
}

function ReplaceCache($cache_file, $line, $cache, $cache_data, $client, $version){
	$new_cache_file = implode("", array_merge( array_slice($cache_file, 0, $line), array_slice( $cache_file, ($line + 1) ) ) );

	$file = fopen(DATA_DIR."/caches.dat", "wb");
	flock($file, 2);
	if($cache != NULL)
		fwrite($file, $new_cache_file.$cache."|".$cache_data[0]."|".$cache_data[1]."|".$client."|".$version."|".gmdate("Y/m/d h:i:s A")."\r\n");
	else
		fwrite($file, $new_cache_file);
	flock($file, 3);
	fclose($file);
}

function PingGWC($cache, $query){
	$debug = FALSE;

	list( , $cache ) = explode("://", $cache);		// It remove "http://" from $cache - $cache = www.test.com:80/page.php
	$main_url = explode("/", $cache);				// $main_url[0] = www.test.com:80		$main_url[1] = page.php
	$splitted_url = explode(":", $main_url[0]);		// $splitted_url[0] = www.test.com		$splitted_url[1] = 80

	if( count($splitted_url) > 1 )
		list($host_name, $port) = $splitted_url;
	else
	{
		$host_name = $main_url[0];
		$port = 80;
	}

	$fp = @fsockopen( $host_name, $port, $errno, $errstr, TIMEOUT );
	if(!$fp)
	{
		$cache_data = "ERR|".$errno;		// ERR|Error name
		if($debug) echo "Error ".$errno.": ".$errstr."\r\n";
	}
	else
	{
		$pong = "";
		$oldpong = "";
		$error = "";

		fwrite( $fp, "GET ".substr( $cache, strlen($main_url[0]), (strlen($cache) - strlen($main_url[0]) ) )."?".$query." HTTP/1.0\r\nHost: ".$host_name."\r\n\r\n");
		while( !feof($fp) )
		{
			$line = fgets( $fp, 1024 );
			if($debug) echo $line."\r\n";

			if( strtolower( substr( $line, 0, 7 ) ) == "i|pong|" )
				$pong = rtrim($line);
			elseif( substr($line, 0, 4) == "PONG" )
				$oldpong = rtrim($line);
			elseif( substr($line, 0, 5) == "ERROR" )
				$error = rtrim($line);
		}
		fclose($fp);

		if(!empty($pong))
		{
			$received_data = explode("|", $pong);
			$cache_data = "P|".RemoveGarbage($received_data[2]);

			if(count($received_data) > 3 && $received_data[3] != "")
			{
				if(substr($received_data[3], 0, 4) == "http")		// Workaround for compatibility with PHPGnuCacheII
					$nets = "gnutella-gnutella2";
				else
					$nets = RemoveGarbage(strtolower($received_data[3]));
			}
			elseif( !empty($oldpong) )
				$nets = "gnutella-gnutella2";
			else
				$nets = "gnutella2";

			$cache_data .= "|".$nets;		// P|Name of the GWC|Networks list
		}
		elseif(!empty($oldpong))
		{
			$oldpong = RemoveGarbage(substr($oldpong, 5));
			$cache_data = "P|".$oldpong;

			if( substr($oldpong, 0, 13) == "PHPGnuCacheII" ||	// Workaround for compatibility
				substr($oldpong, 0, 10) == "perlgcache" ||
				substr($oldpong, 0, 9) == "GWebCache" ||
				substr($oldpong, 0, 12) == "jumswebcache" )
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

	if($debug) echo "\r\nResult: ".$cache_data."\r\n\r\n";
	return $cache_data;
}

function CheckGWC($cache, $cache_network){
	global $SUPPORTED_NETWORKS;

	$nets = NULL;
	if(strpos($cache, "://") > -1)
	{
		$udp = FALSE;
		$query = "ping=1&multi=1&client=".VENDOR."&version=".SHORT_VER."&cache=1";
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
		if( strpos($received_data[1], "network not supported") > -1 )	// Workaround for compatibility with GWCv2 specs
		{																// FOR WEBCACHES DEVELOPERS: If you want avoid necessity to make double ping, make your cache pingable without network parameter when there are ping=1 and multi=1
			$query .= "&net=gnutella2";
			$result = PingGWC($cache, $query);
			$nets = "gnutella2";
		}
		elseif( strpos($received_data[1], "access denied by acl") > -1 )
		{
			$query = "ping=1&multi=1&client=TEST&version=".VENDOR."%20".SHORT_VER."&cache=1";
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

function WriteHostFile($remote_ip, $ip, $leaves, $net, $cluster, $client, $version){
	global $SUPPORTED_NETWORKS;

	// return 4; Unused
	$client = RemoveGarbage($client);
	$version = RemoveGarbage($version);
	$host_file = file(DATA_DIR."/hosts_".$net.".dat");
	$file_count = count($host_file);
	$host_exists = FALSE;

	for($i = 0; $i < $file_count; $i++)
	{
		list( $read, ) = explode("|", $host_file[$i]);
		list( $read_ip, ) = explode(":", $read);

		if( $remote_ip == $read_ip )
		{
			list( , , , , , $time ) = explode("|", rtrim($host_file[$i]));
			$host_exists = TRUE;
			break;
		}
	}

	if($host_exists)
	{
		$time_diff = time() - ( @strtotime( $time ) + @date("Z") );	// GMT
		$time_diff = floor($time_diff / 3600);	// Hours

		if( $time_diff < 24 )
			return 0; // Exists
		else
		{
			ReplaceHost($host_file, $i, $ip, $leaves, $net, $cluster, $client, $version);
			return 1; // Updated timestamp
		}
	}
	else
	{
		if($file_count > MAX_HOSTS || $file_count >= 100)
		{
			ReplaceHost($host_file, 0, $ip, $leaves, $net, $cluster, $client, $version, TRUE);
			return 3; // OK, pushed old data
		}
		elseif($file_count == MAX_HOSTS)
		{
			ReplaceHost($host_file, 0, $ip, $leaves, $net, $cluster, $client, $version);
			return 3; // OK, pushed old data
		}
		else
		{
			$file = fopen(DATA_DIR."/hosts_".$net.".dat", "ab");
			flock($file, 2);
			fwrite($file, $ip."|".$leaves."|".$cluster."|".$client."|".$version."|".gmdate("Y/m/d h:i:s A")."\r\n");
			flock($file, 3);
			fclose($file);
			return 2; // OK
		}
	}
}

function WriteCacheFile($cache, $net, $client, $version){
	global $MY_URL;

	if(strpos($cache, "://") > -1)
	{
		list( , $url ) = explode("://", $cache);
		if($url == $MY_URL)	// It doesn't allow to insert itself in cache list
			return 0; // Exists
	}

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
		if(CheckBlockedCache($cache))
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
				if($file_count >= MAX_CACHES || $file_count >= 100)
				{
					ReplaceCache( $cache_file, 0, $cache, $cache_data, $client, $version );
					return 3; // OK, pushed old data
				}
				else
				{
					$file = fopen(DATA_DIR."/caches.dat", "ab");
					flock($file, 2);
					fwrite($file, $cache."|".$cache_data[0]."|".$cache_data[1]."|".$client."|".$version."|".gmdate("Y/m/d h:i:s A")."\r\n");
					flock($file, 3);
					fclose($file);
					return 2; // OK
				}
			}
		}
	}
}

function HostFile($net){
	$host_file = file(DATA_DIR."/hosts_".$net.".dat");
	$count_host = count($host_file);

	if($count_host <= MAX_HOSTS_OUT)
		$max_hosts = $count_host;
	else
		$max_hosts = MAX_HOSTS_OUT;

	for( $i = 0; $i < $max_hosts; $i++ )
	{
		list( $host, ) = explode("|", $host_file[$count_host - 1 - $i]);
		$host = trim($host);
		print($host."\r\n");
	}
}

function UrlFile($net){
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

function Get($net, $pv, $get, $uhc, $ukhl){
	$output = "";
	$now = time();
	$offset = @date("Z");

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
			list( $host, $leaves, $cluster, , , $time ) = explode("|", $host_file[$count_host - 1 - $i]);
			$host = "H|".$host."|".TimeSinceSubmissionInSeconds( $now, rtrim($time), $offset )."|".$cluster;
			if( $pv >= 4 ) $host .= "|".$leaves;
			$output .= $host."\r\n";
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
					if( $pv >= 4 ) $cache .= "|".( $cache_net != $net ? $cache_net : "" );
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
					if( $pv >= 4 ) $cache .= "|".( $cache_net != $net ? $cache_net : "" );
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

	if($pv >= 3)
		$output .= "I|nets|".strtolower(NetsToString())."\r\n";

	echo $output;
}

function StartCompression($COMPRESSION){
	if($COMPRESSION == "deflate")
		{ $compressed = TRUE; ob_start("gzdeflate"); }
	else
		{ $compressed = FALSE; }
	if($compressed) header("Content-Encoding: deflate");

	return $compressed;
}

function CleanStats($request){
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
	flock($file, 2);
	for($i = 0; $i < $file_count; $i++)
		fwrite($file, $stat_file[$i]."\n");
	flock($file, 3);
	fclose($file);
}

function ReadStats($request){
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

function UpdateStats($request){
	if(!STATS_ENABLED) return;

	$file = fopen("stats/".$request."_requests_hour.dat", "ab");
	flock($file, 2);
	fwrite($file, gmdate("Y/m/d H:i")."\n");
	flock($file, 3);
	fclose($file);
}


$PHP_VERSION = (float)PHP_VERSION;

$PING = !empty($_GET["ping"]) ? $_GET["ping"] : 0;

$PV = !empty($_GET["pv"]) ? $_GET["pv"] : 0;
$NET = !empty($_GET["net"]) ? strtolower($_GET["net"]) : NULL;
$NETS = !empty($_GET["nets"]) ? strtolower($_GET["nets"]) : NULL;	// Currently unsupported
$MULTI = !empty($_GET["multi"]) ? $_GET["multi"] : 0;
$UHC = !empty($_GET["uhc"]) && $PHP_VERSION >= 4.3 ? $_GET["uhc"] : 0;
$UKHL = !empty($_GET["ukhl"]) && $PHP_VERSION >= 4.3 ? $_GET["ukhl"] : 0;

$INFO = !empty($_GET["info"]) ? $_GET["info"] : 0;

$USER_AGENT = !empty($_SERVER["HTTP_USER_AGENT"]) ? str_replace("/", " ", $_SERVER["HTTP_USER_AGENT"]) : NULL;

$COMPRESSION = !empty($_GET["compression"]) ? strtolower($_GET["compression"]) : NULL;
$ACCEPT_ENCODING = !empty($_SERVER["HTTP_ACCEPT_ENCODING"]) ? $_SERVER["HTTP_ACCEPT_ENCODING"] : NULL;
if($COMPRESSION == NULL && strpos($ACCEPT_ENCODING, "deflate") > -1) $COMPRESSION = "deflate";

$IP = !empty($_GET["ip"]) ? $_GET["ip"] : ( !empty($_GET["ip1"]) ? $_GET["ip1"] : NULL );
$CACHE = !empty($_GET["url"]) ? $_GET["url"] : ( !empty($_GET["url1"]) ? $_GET["url1"] : NULL );
$LEAVES = !empty($_GET["x_leaves"]) ? $_GET["x_leaves"] : NULL;
$CLUSTER = !empty($_GET["cluster"]) ? $_GET["cluster"] : NULL;

$HOSTFILE = !empty($_GET["hostfile"]) ? $_GET["hostfile"] : 0;
$URLFILE = !empty($_GET["urlfile"]) ? $_GET["urlfile"] : 0;
$STATFILE = !empty($_GET["statfile"]) ? $_GET["statfile"] : 0;

$BFILE = !empty($_GET["bfile"]) ? $_GET["bfile"] : 0;
if($BFILE) { $HOSTFILE = 1; $URLFILE = 1; }

$GET = !empty($_GET["get"]) ? $_GET["get"] : 0;
$UPDATE = !empty($_GET["update"]) ? $_GET["update"] : 0;

$CLIENT = !empty($_GET["client"]) ? strtoupper($_GET["client"]) : NULL;
// There is MUTE (MUTE network client) and Mutella (Gnutella network client).
// Both identifying itself as MUTE.
if($CLIENT == "MUTE")
{
	list($name, ) = explode(" ", $USER_AGENT);
	if($name == "Mutella")
		$CLIENT = "MTLL";
	elseif($NET == NULL)
		$NET = "mute";
	unset($name);
}
$VERSION = !empty($_GET["version"]) ? $_GET["version"] : NULL;

$SUPPORT = !empty($_GET["support"]) ? $_GET["support"] : 0;

$SHOWINFO = !empty($_GET["showinfo"]) ? $_GET["showinfo"] : 0;
$SHOWHOSTS = !empty($_GET["showhosts"]) ? $_GET["showhosts"] : 0;
$SHOWCACHES = !empty($_GET["showurls"]) ? $_GET["showurls"] : 0;
$SHOWSTATS = !empty($_GET["stats"]) ? $_GET["stats"] : 0;
$SHOWDATA = !empty($_GET["data"]) ? $_GET["data"] : 0;

$KICK_START = !empty($_GET["kickstart"]) ? $_GET["kickstart"] : 0;	// It request hosts from a caches specified in the "url" parameter for a network specified in "net" parameter.

if( empty($_SERVER["QUERY_STRING"]) )
	$SHOWINFO = 1;

if( isset($noload) ) die();

if(MAINTAINER_NICK == "your nickname here")
{
	echo "You must read readme.txt in the admin directory first.\r\n";
	die();
}

if($NET == "gnutella1")
	$NET = "gnutella";
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
		flock($file, 2);
		fwrite($file, VER."|".STATS_ENABLED."|-1|".gmdate("Y/m/d H:i")."|");
		flock($file, 3);
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
	if(ini_get("zlib.output_compression") == 1)
		ini_set("zlib.output_compression", "0");
	include "web_interface.php";

	$compressed = StartCompression($COMPRESSION);
	ShowHtmlPage($web);
	if($compressed) ob_end_flush();
}
elseif( $KICK_START )
{
	if( !KICK_START_ENABLED )
		die("ERROR: Kickstart is disabled\r\n");

	if( !CheckNetworkString($SUPPORTED_NETWORKS, $NET, FALSE) )
		die("ERROR: Network not supported\r\n");

	if( !function_exists("KickStart") )
	{
		include "functions.php";
	}
	KickStart($NET, $CACHE);
}
else
{
	header("Connection: close");
	if(!CONTENT_TYPE_WORKAROUND)
		header("Content-Type: text/plain");
	else
		header("Content-Type: application/octet-stream");

	if(STATS_ENABLED)
	{
		$file = fopen("stats/requests.dat", "r+b");
		flock($file, 2);
		$requests = fgets($file, 50);
		if($requests == "") $requests = 1; else $requests++;
		rewind($file);
		fwrite($file, $requests);
		flock($file, 3);
		fclose($file);
	}

	if($VERSION == NULL && strlen($CLIENT) > 4)
	{
		$VERSION = substr( $CLIENT, 4 );
		$CLIENT = substr( $CLIENT, 0, 4 );
	}

	if($CLIENT == NULL || $VERSION == NULL)
	{
		header("HTTP/1.0 404 Not Found");
		print "ERROR: Client or version unknown - Request rejected\r\n";
		if(LOG_MINOR_ERRORS) Logging("unidentified_clients", $CLIENT, $VERSION, $NET);

		if($CACHE != NULL || $IP != NULL)
			UpdateStats("update");
		else
			UpdateStats("other");
		die();
	}

	$blocked = FALSE;
	$name = explode(" ", $USER_AGENT);

	if($name[0] == "MP3Rocket")
		$CLIENT = $name[0];
	elseif($CLIENT == "LIME")
	{
		if($name[0] == "eTomi" || $name[0] == "360Share")
			$CLIENT = $name[0];
	}
	elseif($CLIENT == "RAZA" && isset($name[1]))
	{
		if( ($name[0] == "Shareaza" && $name[1] == "PRO") || ($name[0] == "Morpheus" && $name[1] == "Music") || ($name[0] == "Bearshare" && $name[1] == "MP3") || ($name[0] == "WinMX" && $name[1] == "MP3") )	// They are ripp-off of Shareaza
			$blocked = TRUE;
	}
	unset($name);

	if( $blocked || IsClientTooOld($CLIENT, $VERSION) )
	{
		header("HTTP/1.0 404 Not Found");

		//if(LOG_MINOR_ERRORS) Logging("old_clients", $CLIENT, $VERSION, $NET);

		if($CACHE != NULL || $IP != NULL)
			UpdateStats("update");
		else
			UpdateStats("other");
		die();
	}

	if(ini_get("zlib.output_compression") == 1)
		ini_set("zlib.output_compression", "0");

	if(!$PING && !$GET && !$UHC && !$UKHL && !$SUPPORT && !$HOSTFILE && !$URLFILE && !$STATFILE && $CACHE == NULL && $IP == NULL && !$INFO)
	{
		print "ERROR: Invalid command - Request rejected\r\n";
		if(LOG_MAJOR_ERRORS) Logging("invalid_queries", $CLIENT, $VERSION, $NET);
		UpdateStats("other");
		die();
	}

	if($CLIENT == "TEST")
		$IP = NULL;

	if($LEAVES != NULL && !ctype_digit($LEAVES))
	{
		$LEAVES = NULL;
		if(LOG_MAJOR_ERRORS) Logging("invalid_leaves", $CLIENT, $VERSION, $NET);
	}

	if($CLUSTER != NULL)
	{
		if( strlen($CLUSTER) > 256 )
			$CLUSTER = NULL;
		else
			$CLUSTER = RemoveGarbage($value);
	}

	if($CACHE != NULL)
	{	// Cleaning url
		if(strpos($CACHE, "://") > -1)
		{
			list( $protocol, $url ) = explode("://", $CACHE);

			if( strpos($url, "/") > -1 )
				list( $host, $path ) = explode("/", $url, 2);
			elseif( strpos($url, "?") > -1 )
			{
				list( $host, $path ) = explode("?", $url);
				$path = "?".$path;
			}
			else
			{
				$host = $url;
				$path = "";
			}

			$path = str_replace( "./", "", $path );		// Remove "./" from $path if present

			$slash = FALSE;
			while( substr( $path, strlen($path) - 1, 1 ) == "/" )
			{
				$path = substr( $path, 0, strlen($path) - 1 );
				$slash = TRUE;
			}

			if( substr( $path, strlen($path) - 1, 1 ) == "." )
				$path = substr( $path, 0, strlen($path) - 1 );	// Remove dot at the end of $path if present

			if( strlen($path) && $slash )
				$path .= "/";

			if( strpos($host, ":") > -1 )
			{
				$splitted_host = explode(":", $host);
				$host_name = $splitted_host[0];
				$host_port = (int)$splitted_host[1];
			}
			else
			{
				$host_name = $host;
				$host_port = 80;
			}

			if( substr( $host_name, strlen($host_name) - 1, 1 ) == "." )
				$host_name = substr( $host_name, 0, strlen($host_name) - 1 );	// Remove dot at the end of $host_name if present

			if( $host_port == 80 )
				$host_port = "";
			else
				$host_port = ":".$host_port;

			if( substr($host_name, -20) == "gwc.nickstallman.net" )
				$CACHE = "http://gwc.nickstallman.net/gcache.asp";
			elseif( substr($host_name, -9) == ".nyud.net" )
				$CACHE = "BLOCKED";
			else
				$CACHE = $protocol."://".strtolower($host_name).$host_port."/".$path;
		}
		else
		{
			$cache_length = strlen($CACHE);
			if(substr($CACHE, $cache_length-1) == "/")
				$CACHE = substr($CACHE, 0, $cache_length-1);
		}
	}

	if($NET == NULL) $NET = "gnutella";
	$compressed = StartCompression($COMPRESSION);

	if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
		header("X-Remote-IP: ".$_SERVER["HTTP_X_FORWARDED_FOR"]);
	else
		header("X-Remote-IP: ".$REMOTE_IP);

	if( CheckNetworkString($SUPPORTED_NETWORKS, $NET, FALSE) )
		$supported_net = TRUE;
	else
	{
		$supported_net = FALSE;
		if(($PING && !$MULTI) || $GET || $HOSTFILE || $URLFILE || $CACHE != NULL || $IP != NULL) echo "ERROR: Network not supported\r\n";
	}

	if($PING)
		Pong($MULTI, $NET, $CLIENT, $supported_net, $REMOTE_IP);
	if($SUPPORT)
		Support($SUPPORTED_NETWORKS, $UDP);

	if($UPDATE)
	{
		if( $IP != NULL && $supported_net )
		{
			if( CheckIPValidity($REMOTE_IP, $IP) )
			{
				$result = WriteHostFile($REMOTE_IP, $IP, $LEAVES, $NET, $CLUSTER, $CLIENT, $VERSION);

				if( $result == 0 ) // Exists
					print "I|update|OK|Host already updated\r\n";
				elseif( $result == 1 ) // Updated timestamp
					print "I|update|OK|Updated host timestamp\r\n";
				elseif( $result == 2 ) // OK
					print "I|update|OK|Host added\r\n";
				elseif( $result == 3 ) // OK, pushed old data
					print "I|update|OK|Host added (pushed old data)\r\n";
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
			}
			else // Invalid URL
				print("I|update|WARNING|Invalid URL"."\r\n");
		}
	}
	else
	{
		if( $supported_net && ( $IP != NULL || $CACHE != NULL ) )
			print "OK\r\n";

		if( $IP != NULL && $supported_net )
		{
			if( CheckIPValidity($REMOTE_IP, $IP) )
				$result = WriteHostFile($REMOTE_IP, $IP, $LEAVES, $NET, $CLUSTER, $CLIENT, $VERSION);
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

	if($GET || $UHC)
	{
		Get($NET, $PV, $GET, $UHC, $UKHL);
		if($UHC || $UKHL)
		{
			echo "I|uhc|".$UDP["uhk"]."\r\n";
			echo "I|ukhl|".$UDP["ukhl"]."\r\n";
		}
	}
	else
	{
		if($HOSTFILE && $supported_net)
			HostFile($NET);

		if($URLFILE && $supported_net && FSOCKOPEN)
			UrlFile($NET);

		if($PV >= 3 && ($HOSTFILE || $URLFILE) && $supported_net)
			echo "nets: ".strtolower(NetsToString())."\r\n";
	}

	if($CACHE != NULL || $IP != NULL)
		UpdateStats("update");
	else
		UpdateStats("other");

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

	if($INFO)
	{
		echo "I|name|".NAME."\r\n";
		echo "I|ver|".VER."\r\n";
		echo "I|gwc-site|http://sourceforge.net/projects/skulls/\r\n";
		echo "I|open-source|1\r\n";

		echo "I|maintainer|".MAINTAINER_NICK."\r\n";
		if(MAINTAINER_WEBSITE != "http://www.your-site.com/")
			echo "I|maintainer-site|".MAINTAINER_WEBSITE."\r\n";
	}


	$clean_file = NULL;
	$changed = FALSE;
	$file = fopen( DATA_DIR."/last_action.dat", "r+b" );
	flock($file, 2);
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
	flock($file, 3);
	fclose($file);

	if($compressed) ob_end_flush();

	if($clean_file == "stats")
		CleanStats($clean_type);
	elseif($clean_file == "failed_urls")
		CleanFailedUrls();
}
?>