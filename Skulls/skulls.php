<?php
//
//   Skulls! Multi-Network WebCache (PHP)
//
//   Copyright (C) 2005-2006 by ale5000
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

if( file_exists("vars.php") )
	include "vars.php";
else
	die("ERROR: The file vars.php is missing.");

if( !$ENABLED )
{
	header("Status: 404 Not Found");
	header("Content-Type: text/plain");
	die("ERROR: Service disabled\r\n");
}

$NAME		= "Skulls";
$VENDOR		= "SKLL";
$SHORT_VER	= "0.1.3";
$VER		= $SHORT_VER." Beta";

$SUPPORTED_NETWORKS[0] = "Gnutella";
$SUPPORTED_NETWORKS[1] = "Gnutella2";



function InizializeNetworkFiles($net){
	$net = strtolower($net);

	if ( !file_exists(DATA_DIR."/hosts_".$net.".dat") )
		fclose( fopen(DATA_DIR."/hosts_".$net.".dat", "x") );
}

function Inizialize($supported_networks){
	if ( !file_exists(DATA_DIR."/") )
		mkdir(DATA_DIR."/", 0777);

	if ( !file_exists(DATA_DIR."/runnig_since.dat") )
	{
		if ( !$file = fopen( DATA_DIR."/runnig_since.dat", "w" ) )
			die("ERROR: Writing file failed");
		else
		{
			flock($file, 2);
			fwrite($file, date("Y/m/d h:i:s A"));
			flock($file, 3);
			fclose($file);
		}
	}

	if ( !file_exists(DATA_DIR."/caches.dat") )
		fclose( fopen(DATA_DIR."/caches.dat", "x") );

	$networks_count=count($supported_networks);

	for( $i=0; $i<$networks_count; $i++ )
		InizializeNetworkFiles( $supported_networks[$i] );

	if ( !file_exists(DATA_DIR."/blocked_caches.dat") )
	{
		$file = fopen(DATA_DIR."/blocked_caches.dat", "x");
		flock($file, 2);
		fwrite($file, "http://gwc.wodi.org/g2/bazooka"."\r\n");
		flock($file, 3);
		fclose($file);
	}

	global $STATFILE_ENABLED;

	if($STATFILE_ENABLED)
	{
		if( !file_exists("stats/") )
			mkdir("stats/", 0775);

		if( !file_exists("stats/requests.dat") )
		{
			$file = fopen( "stats/requests.dat", "x" );
			flock($file, 2);
			fwrite($file, "0");
			flock($file, 3);
			fclose($file);
		}

		if( !file_exists("stats/update_requests_hour.dat") )
			fclose( fopen("stats/update_requests_hour.dat", "x") );

		if( !file_exists("stats/other_requests_hour.dat") )
			fclose( fopen("stats/other_requests_hour.dat", "x") );
	}
}

function Pong($ver){
	global $NAME;

	print "PONG ".$NAME." ".$ver."\r\n";
	print "I|pong|".$NAME." ".$ver."|multi|TCP\r\n";
}

function Support($supported_networks)
{
	$networks_count=count($supported_networks);

	for( $i=0; $i<$networks_count; $i++ )
		print "I|support|".strtolower($supported_networks[$i])."\r\n";
}

function CheckNetwork($supported_networks, $net){
	$networks_count=count($supported_networks);

	for( $i=0; $i<$networks_count; $i++ )
		if ( strtolower($supported_networks[$i]) == strtolower($net) )
			return 1;

	return 0;
}

function CheckNetParameter($supported_networks, $net, $request){
	if ( !CheckNetwork($supported_networks, $net) )
	{
		print "I|".$request."|WARNING|Network not supported\r\n";
		return 0;
	}

	return 1;
}

function TimeSinceSubmissionInSeconds($time_of_submission){
	$time_of_submission = trim($time_of_submission);

	return time() - strtotime($time_of_submission);
}

function CheckIPValidity($remote_ip, $ip){
	$ip_port = explode(":", $ip, 3);	// $ip_port[0] = IP	$ip_port[1] = Port

	if ( count($ip_port) == 2 )
		if (
			strlen($ip) > 0 &&
			ip2long($ip_port[0]) == ip2long($remote_ip) &&
			$ip_port[0] == $remote_ip &&
			is_numeric($ip_port[1]) &&
			$ip_port[1] > 0 &&
			$ip_port[1] < 65536
		)
			return 1;

	return 0;
}

function CheckURLValidity($cache){
	if ( strlen($cache) > 10 )
		if( substr($cache, 0, 7) == "http://" || substr($cache, 0, 8) == "https://" )
			return 1;

	return 0;
}

function CheckBlockedCache($cache){
	$blocked_cache_file = file(DATA_DIR."/blocked_caches.dat");

	$blocked = FALSE;

	for( $i = 0; $i < count($blocked_cache_file); $i++ )
		if( strtolower($cache) == trim( strtolower($blocked_cache_file[$i]) ) )
		{
			$blocked = TRUE;
			break;
		}

	return $blocked;
}

function IsClientTooOld( $client, $version ){
	if( $version == "" )
		return 0;

	$version = (float)$version;

    switch($client)
	{
		case "RAZA":
			if( $version < 2 )
				return 1;
			break;
    }

	return 0;
}

function ReplaceHost($host_file, $line, $ip, $leaves, $net, $cluster, $client, $version){
	$new_host_file = implode("", array_merge( array_slice($host_file, 0, $line), array_slice( $host_file, ($line + 1) ) ) );

	$file = fopen(DATA_DIR."/hosts_".$net.".dat", "w");

	flock($file, 2);
	fwrite($file, $new_host_file.$ip."|".$leaves."|".$cluster."|".$client."|".$version."|".date("Y/m/d h:i:s A")."\r\n");
	flock($file, 3);
	fclose($file);
}

function ReplaceCache($cache_file, $line, $cache, $cache_data, $client, $version){
	$new_cache_file = implode("", array_merge( array_slice($cache_file, 0, $line), array_slice( $cache_file, ($line + 1) ) ) );

	$file = fopen(DATA_DIR."/caches.dat", "w");

	flock($file, 2);
	if ($cache != NULL)
		fwrite($file, $new_cache_file.$cache."|".$cache_data[0]."|".$cache_data[1]."|".$client."|".$version."|".date("Y/m/d h:i:s A")."\r\n");
	else
		fwrite($file, $new_cache_file);
	flock($file, 3);
	fclose($file);
}

function PingWebCache($cache){
	global $SUPPORTED_NETWORKS, $VENDOR, $SHORT_VER, $TIMEOUT;

	list( , $cache ) = explode("://", $cache, 2);		// It remove "http://" from "cache" - $cache = www.test.com:80/page.php
	$main_url = explode("/", $cache);					// $main_url[0] = www.test.com:80		$main_url[1] = page.php
	$splitted_url = explode(":", $main_url[0], 2);		// $splitted_url[0] = www.test.com		$splitted_url[1] = 80

	if (count($splitted_url) > 1)
		list($host_name, $port) = $splitted_url;
	else
	{
		$host_name = $main_url[0];
		$port = 80;
	}

	$fp = @fsockopen( $host_name, $port, $errno, $errstr, $TIMEOUT );

	if (!$fp)
	{
		echo "Error ".$errno;
		$cache_data[0] = "FAILED";
	}
	else
	{
		$pong = "";
		$oldpong = "";
		$error = "";

		$query = "ping=1&multi=1&client=".$VENDOR."&version=".$SHORT_VER;
		if ( $main_url[count($main_url)-1] == "bazooka.php" )	// Workaround for Bazooka WebCache
			$query .= "&net=gnutella2";

		fputs( $fp, "GET ".substr( $cache, strlen($main_url[0]), (strlen($cache) - strlen($main_url[0]) ) )."?".$query." HTTP/1.0\r\nHost: ".$host_name."\r\n\r\n");

		while ( !feof($fp) )
		{
			$line = fgets( $fp, 1024 );

			if( strtolower( substr( $line, 0, 7 ) ) == "i|pong|" )
				$pong = $line;
			elseif( substr($line, 0, 4) == "PONG" )
				$oldpong = $line;
			elseif( substr($line, 0, 5) == "ERROR" )
				$error = $line;
		}

		fclose ($fp);

		if( !empty($pong) )
		{
			$received_data = explode( "|", $pong );

			$cache_data[0] = trim( $received_data[2] );

			if( count($received_data) >= 4 && ( substr( $received_data[3], 0, 4 ) != "http" ) )
			{
				$cache_data[1] = trim( strtolower( $received_data[3] ) );

				if( !CheckNetwork($SUPPORTED_NETWORKS, $cache_data[1]) && $cache_data[1] != "multi" )
					$cache_data[0] = "UNSUPPORTED";
			}
			elseif( !empty($oldpong) )
				$cache_data[1] = "multi";
			else
				$cache_data[1] = "gnutella2";

		}
		elseif( !empty($oldpong) )
		{
			$cache_data[0] = trim( substr( $oldpong, 5, strlen($oldpong) - 5 ) );
			$cache_data[1]= "gnutella";
		}
		elseif( strtolower( trim( substr($error, 7) ) ) == "network not supported" )	// Workaround for WebCaches that follow Bazooka extensions of specifications
		{																				// FOR WEBCACHES DEVELOPERS: If you want avoid necessity to make double request, make your cache pingable without network parameter when there are ping=1 and multi=1
			$fp = @fsockopen( $host_name, $port, $errno, $errstr, $TIMEOUT );

			if (!$fp)
			{
				echo "Error ".$errno;
				$cache_data[0] = "FAILED";
			}
			else
			{
				fputs( $fp, "GET ".substr( $cache, strlen($main_url[0]), (strlen($cache) - strlen($main_url[0]) ) )."?ping=1&multi=1&client=".$VENDOR."&version=".$SHORT_VER."&net=gnutella2 HTTP/1.0\r\nHost: ".$host_name."\r\n\r\n");

				$pong = "";

				while ( !feof($fp) )
				{
					$line = fgets( $fp, 1024 );

					if( strtolower( substr( $line, 0, 7 ) ) == "i|pong|" )
					{
						$pong = $line;
						break;
					}
				}

				fclose ($fp);

				if( !empty($pong) )
				{
					$received_data = explode( "|", $pong );
					$cache_data[0] = trim( $received_data[2] );
					$cache_data[1] = "gnutella2";
				}
				else
					$cache_data[0] = "FAILED";
			}
		}
		else
			$cache_data[0] = "FAILED";
	}

	return $cache_data;
}

function WriteHostFile($ip, $leaves, $net, $cluster, $client, $version){
	global $MAX_HOSTS;

	if($leaves < 15 && $leaves != NULL)
	{
		print "I|update|WARNING|Rejected: Leaf count too low\r\n";
		return 4;
	}
	else
	{
		$host_file = file(DATA_DIR."/hosts_".$net.".dat");

		$host_exists = FALSE;

		for ($i = 0; $i < count($host_file); $i++)
		{
			list( $read, ) = explode("|", $host_file[$i], 2);

			if( $ip == $read )
			{
				$host_exists = TRUE;
				break;
			}
		}

		if( $host_exists == TRUE )
		{
			ReplaceHost($host_file, $i, $ip, $leaves, $net, $cluster, $client, $version);
			return 1; // Updated timestamp
		}
		elseif( $host_exists == FALSE )
		{
			if( count($host_file) >= $MAX_HOSTS )
			{
				ReplaceHost($host_file, 0, $ip, $leaves, $net, $cluster, $client, $version);
				return 3; // OK, pushed old data
			}
			else
			{
				$file = fopen(DATA_DIR."/hosts_".$net.".dat", "a");

				flock($file, 2);
				fwrite($file, $ip."|".$leaves."|".$cluster."|".$client."|".$version."|".date("Y/m/d h:i:s A")."\r\n");
				flock($file, 3);
				fclose($file);
				return 2; // OK
			}
		}
	}
}

function WriteCacheFile($cache, $net, $client, $version){
	global $MAX_CACHES, $PING_WEBCACHES;

	list( , $temp ) = explode("://", $cache, 2);
	if( $temp == $_SERVER["SERVER_NAME"].$_SERVER["PHP_SELF"] )	// It don't allow to insert this webcache url in caches list of this webcache
		return 0;

	$cache_file = file(DATA_DIR."/caches.dat");

	$cache_exists = FALSE;

	for ($i = 0; $i < count($cache_file); $i++)
	{
		list ($read, ) = explode("|", $cache_file[$i], 2);

		if ( strtolower($cache) == strtolower($read) )
		{
			list ( , , , , , $time ) = explode("|", $cache_file[$i], 6);
			$cache_exists = TRUE;
			break;
		}
	}

	if ($cache_exists == TRUE)
	{
		$time_diff = bcdiv( time() - strtotime( trim($time) ), 86400 );

		if ( $time_diff < 10 )
			return 0; // Exists
		else
		{
			if( $PING_WEBCACHES )
				$cache_data = PingWebCache($cache);
			else
			{
				global $SUPPORTED_NETWORKS;

				if( CheckNetwork($SUPPORTED_NETWORKS, $net) )
				{
					$cache_data[0] = NULL;
					$cache_data[1] = $net;
				}
				else
					$cache_data[0] = "UNSUPPORTED";
			}

			if( $cache_data[0] == "FAILED" )
			{
				ReplaceCache( $cache_file, $i, NULL, NULL, NULL, NULL );
				return 5; // Ping failed
			}
			elseif( $cache_data[0] == "UNSUPPORTED" )
			{
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
		$blocked = CheckBlockedCache($cache);

		if ($blocked)
			return 4; // Blocked URL
		else
		{
			if( $PING_WEBCACHES )
				$cache_data = PingWebCache($cache);
			else
			{
				global $SUPPORTED_NETWORKS;

				if( CheckNetwork($SUPPORTED_NETWORKS, $net) )
				{
					$cache_data[0] = NULL;
					$cache_data[1] = $net;
				}
				else
					$cache_data[0] = "UNSUPPORTED";
			}

			if( $cache_data[0] == "FAILED" )
				return 5; // Ping failed
			elseif( $cache_data[0] == "UNSUPPORTED" )
				return 6; // Unsupported network
			else
			{
				$file = fopen(DATA_DIR."/caches.dat", "a");

				if ( count($cache_file) >= $MAX_CACHES )
				{
					ReplaceCache( $cache_file, 0, $cache, $cache_data, $client, $version );
					return 3; // OK, pushed old data
				}
				else
				{
					flock($file, 2);
					fwrite($file, $cache."|".$cache_data[0]."|".$cache_data[1]."|".$client."|".$version."|".date("Y/m/d h:i:s A")."\r\n");
					flock($file, 3);
					fclose($file);
					return 2; // OK
				}
			}
		}
	}
}

function HostFile($net){
	global $MAX_HOSTS_OUT;

	$host_file = file(DATA_DIR."/hosts_".$net.".dat");
	$count_host = count($host_file);

	if($count_host <= $MAX_HOSTS_OUT)
		$max_hosts = $count_host;
	else
		$max_hosts = $MAX_HOSTS_OUT;

	for( $i=0; $i<$max_hosts; $i++ )
	{
		list ( $host, ) = explode("|", $host_file[($count_host - 1) - $i], 2);
		print($host."\r\n");
	}
}

function UrlFile($net){
	global $MAX_CACHES_OUT;

	$cache_file = file(DATA_DIR."/caches.dat");
	$count_cache = count($cache_file);

	if ($count_cache <= $MAX_CACHES_OUT)
		$max_caches = $count_cache;
	else
		$max_caches = $MAX_CACHES_OUT;

	for( $i=0; $i<$max_caches; $i++ )
	{
		list ( $cache, , $cache_net, ) = explode("|", $cache_file[$i], 4);
		if( $cache_net == $net || $cache_net == "multi" )
			print($cache."\r\n");
	}
}

function Get($net){
	global $MAX_HOSTS_OUT, $MAX_CACHES_OUT, $PV;

	$host_file = file(DATA_DIR."/hosts_".$net.".dat");
	$count_host = count($host_file);

	if($count_host <= $MAX_HOSTS_OUT)
		$max_hosts = $count_host;
	else
		$max_hosts = $MAX_HOSTS_OUT;

	for( $i=0; $i<$max_hosts; $i++ )
	{
		list ( $host, , $cluster, , , $time ) = explode("|", $host_file[($count_host - 1) - $i], 6);
		$out = "H|".$host."|".TimeSinceSubmissionInSeconds( $time )."|".$cluster;
		if( $PV >= 4 )
			$out .= "|".$net;

		print($out."\r\n");
	}

	$cache_file = file(DATA_DIR."/caches.dat");
	$count_cache = count($cache_file);

	if ($count_cache <= $MAX_CACHES_OUT)
		$max_caches = $count_cache;
	else
		$max_caches = $MAX_CACHES_OUT;

	for( $i=0; $i<$max_caches; $i++ )
	{
		list ( $cache, , $cache_net, , , $time ) = explode("|", $cache_file[$i], 6);
		if( $cache_net == $net || $cache_net == "multi" )
			print("U|".$cache."|".TimeSinceSubmissionInSeconds( $time )."|".$cache_net."\r\n");
	}

	if( $count_host == 0 && $count_cache == 0 )
		print("I|NO-URL-NO-HOSTS\r\n");
	elseif ( $count_cache == 0 )
		print("I|NO-URL\r\n");
	elseif ( $count_host == 0 )
		print("I|NO-HOSTS\r\n");
}

function UpdateStats($request, $add = TRUE ){
	$stat_file = file("stats/".$request."_requests_hour.dat");

	$file = fopen("stats/".$request."_requests_hour.dat", "w");

	flock($file, 2);

	for($i = 0; $i < count($stat_file); $i++)
	{
		$stat_file[$i] = trim($stat_file[$i]);
		$time_diff = bcdiv( time() - strtotime( $stat_file[$i] ), 3600 );

		if( $time_diff < 1 )
			fwrite($file, $stat_file[$i]."\r\n");
	}

	if( $add == TRUE )
		fwrite($file, date("Y/m/d h:i A")."\r\n");

	flock($file, 3);
	fclose($file);
}

function KickStart($net, $cache){
	if( !CheckURLValidity($cache) )
		die("ERROR: The KickStart URL isn't valid\r\n");

	global $VENDOR, $SHORT_VER, $TIMEOUT;

	list( , $cache ) = explode("://", $cache, 2);		// It remove "http://" from "cache" - $cache = www.test.com:80/page.php
	$main_url = explode("/", $cache);					// $main_url[0] = www.test.com:80		$main_url[1] = page.php
	$splitted_url = explode(":", $main_url[0], 2);		// $splitted_url[0] = www.test.com		$splitted_url[1] = 80

	if (count($splitted_url) == 2)
		list($host_name, $port) = $splitted_url;
	else
	{
		$host_name = $main_url[0];
		$port = 80;
	}

	$fp = @fsockopen( $host_name, $port, $errno, $errstr, $TIMEOUT );

	if(!$fp)
	{
		echo "Error ".$errno;
		return;
	}
	else
	{
		fputs( $fp, "GET ".substr( $cache, strlen($main_url[0]), (strlen($cache) - strlen($main_url[0]) ) )."?get=1&net=".$net."&client=".$VENDOR."&version=".$SHORT_VER." HTTP/1.0\r\nHost: ".$host_name."\r\n\r\n" );
		while( !feof($fp) )
		{
			$line = fgets( $fp, 1024 );
			if( strtolower( substr($line, 1, 1) ) == "|" )
				echo("<br>");
			echo($line."<br>");

			if( strtolower( substr($line, 0, 2) ) == "h|" )
			{
				$host = explode( "|", $line, 5 );
				if( !isset($host[3]) ) // Cluster
					$host[3] = NULL;
				WriteHostFile( trim($host[1]), NULL, $net, $host[3], "KICKSTART", NULL );
			}
		}
		fclose ($fp);
	}
}

function ShowHtmlPage($num){
	global $NAME, $VER, $NET, $MAX_HOSTS, $MAX_CACHES, $STATFILE_ENABLED;

	include "vendor_code.php";

	if( $NET == NULL )
		$NET = "all";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo($NAME); ?>! Multi-Network WebCache <?php echo($VER); ?></title>
	<meta name="robots" content="noindex,nofollow,nocache">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<style type="text/css">
		body { font-family: Verdana; }
		table { font-size: 10px; }
	</style>
</head>

<body bgcolor="#FFFF00"><br>

<table align="center">
	<tr> 
		<td bgcolor="#FF3300">
			<table width="100%" cellspacing="0" cellpadding="5">
				<tr> 
					<td bgcolor="#FFFFFF" style="font-size: 16px;"><b><span style="color: #008000"><?php echo($NAME); ?>!</span> Multi-Network WebCache <?php echo($VER); ?></b></td>
				</tr>
				<tr> 
					<td height="30" valign="top" bgcolor="#FFFFFF">
						<a href="?showinfo=1">General Details</a> /
						<a href="?showhosts=1&amp;net=<?php echo($NET); ?>">Hosts</a> /
						<a href="?showurls=1">Alternative WebCaches</a> /
						<a href="?stats=1">Statistics</a>
					</td>
				</tr>
				<?php
			if ($num == 1)	// Info
			{
				?>
				<tr bgcolor="#CCFF99"> 
					<td style="color: #0044FF;"><b>Cache Info</b></td>
				</tr>
				<tr> 
					<td bgcolor="#FFFFFF">
						<table width="100%" cellspacing="0">
							<tr> 
								<td width="150">- Running since:</td>
								<td style="color: #994433;">
								<?php
									if (file_exists(DATA_DIR."/runnig_since.dat"))
									{
										$runnig_since = file(DATA_DIR."/runnig_since.dat");
										echo($runnig_since[0]);
									}
								?>
								</td>
							</tr>
							<tr> 
								<td width="150">- Version:</td>
								<td style="color: #008000;"><b><?php echo($VER); ?></b></td>
							</tr>
							<tr> 
								<td width="150">- Supported networks:</td>
								<td style="color: #994433;">
								<?php
									global $SUPPORTED_NETWORKS;

									$networks_count=count($SUPPORTED_NETWORKS);

									for( $i=0; $i<$networks_count; $i++ )
									{
										echo $SUPPORTED_NETWORKS[$i];
										if( $i<$networks_count-1 )
											echo ", ";
									}
								?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
		        <?php
			}
			elseif ($num == 2)	// Host
			{
				$max_of_hosts = $MAX_HOSTS;
				$network_names = 0;

				if( $NET == "all" )
				{
					global $SUPPORTED_NETWORKS;
					$networks_count = count($SUPPORTED_NETWORKS);
					$max_of_hosts *= $networks_count;

					$host_file = array();
					for( $i=0; $i<$networks_count; $i++ )
					{
						$host_file = array_merge( $host_file, file(DATA_DIR."/hosts_".$SUPPORTED_NETWORKS[$i].".dat"), array($SUPPORTED_NETWORKS[$i]) );
						$network_names++;
					}
				}
				elseif( file_exists(DATA_DIR."/hosts_".$NET.".dat") )
				{
					$host_file = file(DATA_DIR."/hosts_".$NET.".dat");
					$net = ucfirst($NET);
				}
				else
					$host_file = array();

				$current_host = count($host_file) - $network_names;
				?>
				<tr bgcolor="#CCFF99"> 
					<td style="color: #0044FF">
					<b><?php echo( ucfirst($NET) ); ?> Hosts (<?php echo $current_host." of ".$max_of_hosts; ?>)</b>
					</td>
				</tr>
				<tr>
					<td bgcolor="#FFFFFF">
						<table width="100%" cellspacing="0">
							<tr> 
								<td bgcolor="#CCCCDD">
									<table width="100%" cellspacing="0" cellpadding="4">
										<tr bgcolor="#C6E6E6"> 
											<td>Host address (Leaves)</td>
											<td>Client</td>
											<td>Network</td>
											<td>Last updated</td>
										</tr>
										<?php
											if( count($host_file) == 0 )
												print("<tr align=\"center\" bgcolor=\"#FFFFFF\"><td colspan=\"4\" height=\"30\">There are no <strong>hosts</strong> listed at this time.</td></tr>\r\n");
											else
											{
												for( $i = count($host_file) - 1; $i >= 0; $i-- )
												{
													if( strstr($host_file[$i], "|") )
													{
														list ($ip, $leaves, , $client, $version, $time) = explode("|", $host_file[$i], 6);
														$color = $i % 2 == 0 ? "#F0F0F0" : "#FFFFFF";

														echo("<tr align=\"left\" bgcolor=\"".$color."\">");
														echo("<td style=\"padding-right: 10pt;\"><a href=\"gnutella:host:".$ip."\">".$ip."</a>");
														if( !empty($leaves) )
															echo(" (".$leaves.")");
														echo("</td>");
														echo("<td style=\"padding-right: 20pt;\"><strong>".ReplaceVendorCode($client, $version)."</strong></td>");
														echo("<td style=\"padding-right: 20pt;\">".$net."</td>");
														echo("<td>".$time."</td></tr>");
													}
													else
														$net = $host_file[$i];
												}
											}
										?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			elseif ($num == 3)	// WebCache
			{
				$cache_file = file(DATA_DIR."/caches.dat");
				?>
				<tr bgcolor="#CCFF99"> 
					<td style="color: #0044FF"><b>Alternative WebCaches (<?php echo(count($cache_file)." of ".$MAX_CACHES); ?>)</b></td>
				</tr>
				<tr> 
					<td bgcolor="#FFFFFF">
						<table width="100%" cellspacing="0">
							<tr> 
								<td bgcolor="#CCCCDD">
									<table width="100%" cellspacing="0" cellpadding="4">
										<tr bgcolor="#C6E6E6"> 
											<td>WebCache URL</td>
											<td>Name</td>
											<td>Network</td>
											<td>Submitting client</td>
											<td>Date submitted</td>
										</tr>
										<?php
											if ( count($cache_file) == 0 )
												print("<tr align=\"center\" bgcolor=\"#FFFFFF\"><td colspan=\"5\" height=\"30\">There are no <strong>alternative webcaches</strong> listed at this time.</td></tr>\r\n");
											else
												for($i = count($cache_file) - 1; $i >= 0; $i--)
												{
													list ($cache_url, $cache_name, $net, $client, $version, $time) = explode("|", $cache_file[$i], 6);
													if( $net == "multi" )
														$net = "Multi-Network";

													$color = $i % 2 == 0 ? "#F0F0F0" : "#FFFFFF";

													echo("<tr align=\"left\" bgcolor=\"".$color."\">");
													echo("<td style=\"padding-right: 10pt;\">");
													echo("<a href=\"".$cache_url."\" target=\"_blank\">");

													list( , $cache_url ) = explode("://", $cache_url, 2);

													if ( strlen($cache_url) > 27 )
													{
														echo( strpos($cache_url, "/") > 0 ? substr( $cache_url, 0, strpos($cache_url, "/") ) : substr( $cache_url, 0, 22 )." ... " );
														echo("/");
													}
													else
														echo( $cache_url );

													echo("</a></td>");
													echo("<td style=\"padding-right: 20pt;\">".$cache_name."</td>");
													echo("<td style=\"padding-right: 20pt;\">".ucfirst($net)."</td>");
													echo("<td style=\"padding-right: 20pt;\"><strong>".ReplaceVendorCode($client, $version)."</strong></td>");
													echo("<td>".$time."</td></tr>");
												}
										?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			elseif ($num == 4)	// Statistics
			{
				if($STATFILE_ENABLED)
				{
					UpdateStats("update", FALSE);
					UpdateStats("other", FALSE);
				}
				?>
				<tr bgcolor="#CCFF99"> 
					<td style="color: #0044FF;"><b>Statistics</b></td>
				</tr>
				<tr> 
					<td bgcolor="#FFFFFF">
						<table width="100%" cellspacing="0">
							<tr> 
								<td width="150">- Total requests:</td>
								<td style="color: #994433;">
								<?php
									if($STATFILE_ENABLED)
									{
										$requests = file("stats/requests.dat");
										echo($requests[0]);
									}
									else
										echo("Disabled");
								?>
								</td>
							</tr>
							<tr> 
								<td width="150">- Requests this hour:</td>
								<td style="color: #994433;">
								<?php
									if($STATFILE_ENABLED)
									{
										$requests = count( file("stats/other_requests_hour.dat") ) + count( file("stats/update_requests_hour.dat") );
										echo($requests);
									}
									else
										echo("Disabled");
								?>
								</td>
							</tr>
							<tr> 
								<td width="150">- Updates this hour:</td>
								<td style="color: #994433;">
								<?php
									if($STATFILE_ENABLED)
									{
										$requests = count( file("stats/update_requests_hour.dat") );
										echo($requests);
									}
									else
										echo("Disabled");
								?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
		        <?php
			}
				?>
				<tr>
					<td bgcolor="#FFFFFF" style="padding: 5pt;"><b><?php echo $NAME; ?>'s project page: <a href="http://sourceforge.net/projects/skulls/" target="_blank">http://sourceforge.net/projects/skulls/</a></b></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

</body>
</html>
<?php
}

if( (float)PHP_VERSION >= 5.1 )
	date_default_timezone_set("UTC");
else
	@putenv("TZ=UTC");

Inizialize($SUPPORTED_NETWORKS);

$PV = !empty($_GET['pv']) ? $_GET['pv'] : 0;
$PING = !empty($_GET['ping']) ? $_GET['ping'] : 0;

$NET = !empty($_GET['net']) ? strtolower($_GET['net']) : NULL;

$IP = !empty($_GET['ip']) ? $_GET['ip'] : ( !empty($_GET['ip1']) ? $_GET['ip1'] : NULL );
$CACHE = !empty($_GET['url']) ? $_GET['url'] : ( !empty($_GET['url1']) ? $_GET['url1'] : NULL );
$LEAVES = !empty($_GET['x_leaves']) ? $_GET['x_leaves'] : NULL;
$CLUSTER = !empty($_GET['cluster']) ? $_GET['cluster'] : NULL;
if( strlen($CLUSTER) > 256 )
	$CLUSTER = NULL;
elseif( $CLUSTER != NULL )
	$CLUSTER = str_replace( "|", "", $CLUSTER );

$HOSTFILE = !empty($_GET['hostfile']) ? $_GET['hostfile'] : 0;
$URLFILE = !empty($_GET['urlfile']) ? $_GET['urlfile'] : 0;
$STATFILE = !empty($_GET['statfile']) ? $_GET['statfile'] : 0;

$GET = !empty($_GET['get']) ? $_GET['get'] : 0;
$UPDATE = !empty($_GET['update']) ? $_GET['update'] : 0;

$CLIENT = !empty($_GET['client']) ? $_GET['client'] : NULL;
$VERSION = !empty($_GET['version']) ? $_GET['version'] : NULL;

$SUPPORT = !empty($_GET['support']) ? $_GET['support'] : 0;

$SHOWINFO = !empty($_GET['showinfo']) ? $_GET['showinfo'] : 0;
$SHOWHOSTS = !empty($_GET['showhosts']) ? $_GET['showhosts'] : 0;
$SHOWCACHES = !empty($_GET['showurls']) ? $_GET['showurls'] : 0;
$SHOWSTATS = !empty($_GET['stats']) ? $_GET['stats'] : 0;

if( empty($_SERVER['QUERY_STRING']) )
	$SHOWINFO = 1;

$KICK_START = !empty($_GET['kickstart']) ? $_GET['kickstart'] : 0;	// It request hosts from a caches specified in the "url" parameter for a network specified in "net" parameter.

if( !isset($_SERVER) )
	$_SERVER = $HTTP_SERVER_VARS;

$REMOTE_IP = $_SERVER['REMOTE_ADDR'];


if($LOG_ENABLED)
{
	if ( !file_exists("log/") )
		mkdir("log/", 0775);

	$HTTP_X_FORWARDED_FOR = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : "";
	$HTTP_CLIENT_IP = isset($_SERVER["HTTP_CLIENT_IP"]) ? $_SERVER["HTTP_CLIENT_IP"] : "";
	$HTTP_FROM = isset($_SERVER["HTTP_FROM"]) ? $_SERVER["HTTP_FROM"] : "";

	$file = fopen("log/".strtolower($NAME).".log", "a");

	flock($file, 2);
	fwrite($file, $_SERVER['QUERY_STRING']." | ".$_SERVER["HTTP_USER_AGENT"]." | ".$_SERVER["REMOTE_ADDR"]." | ".$HTTP_X_FORWARDED_FOR." | ".$HTTP_CLIENT_IP." | ".$HTTP_FROM."\r\n");
	flock($file, 3);
	fclose($file);
}


if( $SHOWINFO )
	ShowHtmlPage(1);
elseif( $SHOWHOSTS )
	ShowHtmlPage(2);
elseif( $SHOWCACHES )
	ShowHtmlPage(3);
elseif( $SHOWSTATS )
	ShowHtmlPage(4);
elseif( $KICK_START )
{
	if( !$KICK_START_ENABLED )
		die("ERROR: KickStart is disabled\r\n");

	if ( !CheckNetwork($SUPPORTED_NETWORKS, $NET) )
		die("ERROR: Network not supported\r\n");

	KickStart($NET, $CACHE);
}
else
{
	header("Content-Type: text/plain");
	header("X-Remote-IP: ".$REMOTE_IP);
	header("Connection: close");

	if( $CACHE != NULL )
	{	// Clean url
		list( $protocol, $url ) = explode("://", $CACHE, 2);

		if( strstr( $url, "/" ) )
			list( $url, $other_part_url ) = explode("/", $url, 2);
		else
			$other_part_url = "";

		if( substr( $other_part_url, 0, 1 ) == "." )
			$other_part_url = substr( $other_part_url, 1 );					// Remove dot at the start of $other_part_url if present

		while( substr($other_part_url, 0, 1) == "/" )
			$other_part_url = substr($other_part_url, 1);

		if( strstr( $url, ":" ) )
		{
			list( $host_name, $host_port ) = explode(":", $url, 2);
			$host_port = (int)$host_port;
		}
		else
		{
			$host_name = $url;
			$host_port = 80;
		}

		if( substr( $host_name, strlen($host_name) - 1, 1 ) == "." )
			$host_name = substr( $host_name, 0, strlen($host_name) - 1 );	// Remove dot at the end of $host_name if present

		if( $host_port == 80 )
			$host_port = "";
		else
			$host_port = ":".$host_port;

		$CACHE = $protocol."://".$host_name.$host_port."/".$other_part_url;
	}

	if($STATFILE_ENABLED)
	{
		$request = file("stats/requests.dat");

		$file = fopen("stats/requests.dat", "w");

		flock($file, 2);
		fwrite($file, $request[0] + 1);
		flock($file, 3);
		fclose($file);
	}

	if( $CLIENT == NULL )
		die("ERROR: Client unknown - Request rejected\r\n");

	if( $VERSION == NULL )
	{
		if ( strlen($CLIENT) > 4 )
			$VERSION = substr( $CLIENT, 4 );

		$CLIENT = substr( $CLIENT, 0, 4 );
	}

	if( IsClientTooOld( $CLIENT, $VERSION ) )
		die("ERROR: Client too old - Request rejected\r\n");

	if( !$PING && !$GET && !$SUPPORT && !$HOSTFILE && !$URLFILE && !$STATFILE && !$UPDATE && ($CACHE == NULL) && ($IP == NULL) )
	{
		UpdateStats("other");
		die("ERROR: Invalid command - Request rejected\r\n");
	}


	if ( $NET == NULL || $NET == "gnutella1" )
		$NET = "gnutella";


	if ($PING)
		Pong($VER);

	if ($SUPPORT)
		Support($SUPPORTED_NETWORKS);

	if ($UPDATE)
	{
		if ( CheckNetParameter($SUPPORTED_NETWORKS, $NET, "update") )
			if( $IP != NULL )
			{
				if( CheckIPValidity($REMOTE_IP, $IP) )
				{
					$result = WriteHostFile($IP, $LEAVES, $NET, $CLUSTER, $CLIENT, $VERSION);

					if( $result == 1 ) // Updated timestamp
						print "I|update|OK|Updated host timestamp\r\n";
					elseif( $result == 2 ) // OK
						print "I|update|OK|Host added successfully\r\n";
					elseif( $result == 3 ) // OK, pushed old data
						print "I|update|OK|Host added successfully - pushed old data\r\n";
					elseif( $result == 6 ) // Unsupported network
						;//print "I|update|WARNING|Rejected: Network of ".$CACHE." not supported\r\n";
				}
				else // Invalid IP
					print "I|update|WARNING|Rejected: Invalid IP ".$IP."\r\n";
			}

		if( $CACHE != NULL )
		{
			if( CheckURLValidity($CACHE) )
			{
				$result = WriteCacheFile($CACHE, $NET, $CLIENT, $VERSION);

				if( $result == 0 ) // Exists
					print "I|update|OK|Cache already exists and it is already updated\r\n";
				elseif( $result == 1 ) // Updated timestamp
					print "I|update|OK|Updated cache timestamp\r\n";
				elseif( $result == 2 ) // OK
					print "I|update|OK|Cache added successfully\r\n";
				elseif( $result == 3 ) // OK, pushed old data
					print "I|update|OK|Cache added successfully - pushed old data\r\n";
				elseif( $result == 4 ) // Blocked URL
					print "I|update|OK|Blocked URL\r\n";
				elseif( $result == 5 ) // Ping failed
					print "I|update|WARNING|Rejected: Ping of ".$CACHE." failed\r\n";
				elseif( $result == 6 ) // Unsupported network
					print "I|update|WARNING|Rejected: Network of ".$CACHE." not supported\r\n";
			}
			else // Invalid URL
				print("I|update|WARNING|Rejected: Invalid URL ".$CACHE."\r\n");
		}
	}
	else
	{
		if( $IP != NULL )
			if ( CheckNetwork($SUPPORTED_NETWORKS, $NET) )
			{
				if( CheckIPValidity($REMOTE_IP, $IP) )
				{
					$result = WriteHostFile($IP, $LEAVES, $NET, $CLUSTER, $CLIENT, $VERSION);

					if( $result <= 3 )
						print "OK\r\n";
					elseif( $result == 6 ) // Unsupported network
						;//print "WARNING: Network of ".$CACHE." not supported\r\n";
				}
				else // Invalid IP
					print "WARNING: Invalid IP ".$IP."\r\n";
			}

		if( $CACHE != NULL )
		{
			if( CheckURLValidity($CACHE) )
			{
				$result = WriteCacheFile($CACHE, $NET, $CLIENT, $VERSION);

				if( $result <= 4 )
					print "OK\r\n";
				elseif( $result == 5 ) // Ping failed
					print "WARNING: Ping of ".$CACHE." failed\r\n";
				elseif( $result == 6 ) // Unsupported network
					print "WARNING: Network of ".$CACHE." not supported\r\n";
			}
			else // Invalid URL
				print("WARNING: Invalid URL ".$CACHE."\r\n");
		}
	}

	if ($GET)
	{
		if( CheckNetParameter($SUPPORTED_NETWORKS, $NET, "get") )
			Get($NET);

		if( $PV >= 4 )
		{
			$networks_count=count($SUPPORTED_NETWORKS);
			$networks = $SUPPORTED_NETWORKS[0];

			for( $i=1; $i<$networks_count; $i++ )
				$networks .= "!".$SUPPORTED_NETWORKS[$i];
			print("I|networks|".strtolower($networks)."\r\n");
		}
	}
	else
	{
		if($HOSTFILE)
			if ( CheckNetwork($SUPPORTED_NETWORKS, $NET) )
				HostFile($NET);

		if($URLFILE)
			if ( CheckNetwork($SUPPORTED_NETWORKS, $NET) )
				UrlFile($NET);

		if( $PV >= 3 && ($HOSTFILE || $URLFILE) )
			print("net: ".$NET."\r\n");
	}

	if( $UPDATE || ($CACHE != NULL) || ($IP != NULL) )
		UpdateStats("update");
	else
		UpdateStats("other");

	if( $STATFILE && !$PING && !$GET && !$SUPPORT && !$HOSTFILE && !$URLFILE )
	{
		if($STATFILE_ENABLED)
		{
			$requests = file("stats/requests.dat");
			print $requests[0]."\r\n";
			$requests = count( file("stats/other_requests_hour.dat") ) + count( file("stats/update_requests_hour.dat") );
			print $requests."\r\n";
			$requests = count( file("stats/update_requests_hour.dat") );
			print $requests."\r\n";
		}
		else
			print "WARNING: Statfile disabled\r\n";
	}
}
?>