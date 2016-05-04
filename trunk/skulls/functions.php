<?php
//  functions.php
//
//  Copyright © 2005-2008, 2015-2016  ale5000
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

function DetectServer()
{
	if(function_exists('apache_get_version') && apache_get_version() !== false)
		return 'Apache';

	if(!empty($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'apache') === 0)
		return 'Apache';

	return false;
}

function InitializeNetworkFile($net, $show_errors = false)
{
	$net = strtolower($net);
	if(!file_exists(DATA_DIR.'hosts-'.$net.'.dat'))
	{
		$file = @fopen(DATA_DIR.'hosts-'.$net.'.dat', 'wb');
		if($file !== false) fclose($file);
		elseif($show_errors)
			echo "<font color=\"red\">Error during writing of ".DATA_DIR."hosts-".$net.".dat</font><br>";
	}
}

function Initialize($supported_networks, $show_errors = false, $forced = false)
{
	$errors = "";
	if(!file_exists(DATA_DIR.'running-since.dat'))
	{
		$running_since = gmdate('Y/m/d H:i:s');
		$fp = @fopen(DATA_DIR.'running-since.dat', 'wb');
		if($fp !== false)
		{
			flock($fp, LOCK_EX); fwrite($fp, $running_since); fflush($fp); flock($fp, LOCK_UN); fclose($fp);
		}
		else $errors .= "<font color=\"red\">Error during writing of ".DATA_DIR."running-since.dat</font><br>";
	}
	if(!file_exists(DATA_DIR.'alt-gwcs.dat'))
	{
		$file = @fopen(DATA_DIR.'alt-gwcs.dat', 'wb');
		if($file !== FALSE) fclose($file);
		else $errors .= "<font color=\"red\">Error during writing of ".DATA_DIR."alt-gwcs.dat</font><br>";
	}
	if(!file_exists(DATA_DIR.'alt-udps.dat'))
	{
		$file = @fopen(DATA_DIR.'alt-udps.dat', 'wb');
		if($file !== FALSE) fclose($file);
		else $errors .= "<font color=\"red\">Error during writing of ".DATA_DIR."alt-udps.dat</font><br>";
	}
	if(!file_exists(DATA_DIR.'failed_urls.dat'))
	{
		$file = @fopen(DATA_DIR.'failed_urls.dat', 'wb');
		if($file !== FALSE) fclose($file);
		else $errors .= "<font color=\"red\">Error during writing of ".DATA_DIR."failed_urls.dat</font><br>";
	}

	for($i = 0; $i < NETWORKS_COUNT; $i++)
		InitializeNetworkFile($supported_networks[$i], $show_errors);

	if(STATS_ENABLED)
	{
		if(!file_exists('stats/')) mkdir('stats/', 0777);
		if(!file_exists('stats/requests.dat'))
		{
			$file = @fopen('stats/requests.dat', 'wb');
			if($file !== FALSE) { flock($file, LOCK_EX); fwrite($file, "0"); flock($file, LOCK_UN); fclose($file); }
			else $errors .= "<font color=\"red\">Error during writing of stats/requests.dat</font><br>";
		}
		if(!file_exists('stats/upd-reqs.dat'))
		{
			$file = @fopen('stats/upd-reqs.dat', 'wb');
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/upd-reqs.dat</font><br>";
		}
		if(!file_exists('stats/upd-bad-reqs.dat'))
		{
			$file = @fopen('stats/upd-bad-reqs.dat', 'wb');
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/upd-bad-reqs.dat</font><br>";
		}
		if(!file_exists('stats/blocked-reqs.dat'))
		{
			$file = @fopen('stats/blocked-reqs.dat', 'wb');
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/blocked-reqs.dat</font><br>";
		}
		if(!file_exists('stats/other-reqs.dat'))
		{
			$file = @fopen('stats/other-reqs.dat', 'wb');
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/other-reqs.dat</font><br>";
		}
	}

	if(!file_exists('admin/revision.dat'))
	{
		$file = @fopen('admin/revision.dat', 'wb');
		if($file !== FALSE)
		{
			flock($file, LOCK_EX);
			fwrite($file, "0");
			flock($file, LOCK_UN);
			fclose($file);
		}
		else $errors .= "<font color=\"red\">Error during writing of admin/revision.dat</font><br>";
	}

	if(!file_exists('./.htaccess') && DetectServer() === 'Apache')
	{
		$fp1 = @fopen('./includes/base-htaccess.php', 'rb');
		$fp2 = @fopen('./.htaccess', 'wb');
		if($fp1 !== false && $fp2 !== false)
		{
			flock($fp1, LOCK_EX);
			flock($fp2, LOCK_EX);

			fgets($fp1, 512);  /* Skip first line */
			while(($data = fgets($fp1, 512)) !== false)
				fwrite($fp2, $data);

			flock($fp1, LOCK_UN);
			flock($fp2, LOCK_UN);
			fclose($fp1);
			fclose($fp2);
		}
		else $errors .= "<font color=\"red\">Error during writing of .htaccess</font><br>";
	}

	if($show_errors && $errors != "")
	{
		echo $errors;
		echo "<br><b>You must create files manually, and give to them the correct permissions.</b><br>";
		die();
	}
}

function KickStart($net, $cache){
	if( !CheckURLValidity($cache) )
		die("ERROR: The KickStart URL isn't valid\r\n");

	list( , $cache ) = explode("://", $cache, 2);		// It remove "http://" from $cache - $cache = www.test.com:80/page.php
	$main_url = explode("/", $cache);					// $main_url[0] = www.test.com:80		$main_url[1] = page.php
	$splitted_url = explode(":", $main_url[0], 2);		// $splitted_url[0] = www.test.com		$splitted_url[1] = 80

	if(count($splitted_url) == 2)
		list($host_name, $port) = $splitted_url;
	else
	{
		$host_name = $main_url[0];
		$port = 80;
	}

	$fp = @fsockopen($host_name, $port, $errno, $errstr, (float)CONNECT_TIMEOUT);

	if($fp === false)
	{
		echo "<font color=\"red\"><b>Error ".$errno.".</b></font><br>\r\n";
		return;
	}
	else
	{
		include './update.php';

		fwrite($fp, 'GET '.substr( $cache, strlen($main_url[0]), (strlen($cache) - strlen($main_url[0]) ) ).'?get=1&hostfile=1&getvendors=1&getnetworks=1&pv=2&net='.$net.'&client='.VENDOR.'&version='.SHORT_VER.'&cache=1'." HTTP/1.0\r\nHost: ".$host_name."\r\nUser-Agent: ".NAME." ".VER."\r\nConnection: close\r\n\r\n");
		while(!feof($fp))
		{
			$is_host = FALSE;
			$line = rtrim(fgets($fp, 1024));
			echo $line,"<br>";

			if(strpos($line, '|') !== false)
			{
				if(strtolower(substr($line, 0, 2)) == "h|")		// Host
				{
					$host = explode('|', $line, 7);
					$is_host = true;
				}
				elseif(strtolower(substr($line, 0, 2)) == "u|");	// GWC
				elseif(strtolower(substr($line, 0, 2)) == "i|");	// Info
				elseif(strtolower(substr($line, 0, 2)) == "d|");	// Debug
			}
			elseif(strpos($line, '://') !== false);		// GWC (old specs)
			elseif(strpos($line, ':') !== false)		// Host (old specs)
			{
				list($ip) = explode(':', $line, 2);
				if(ValidateIP($ip))
				{
					$host = array(); $host[1] = $line;
					$is_host = true;
				}
				unset($ip);
			}

			if($is_host)
			{
				$h_vendor = null; if(!empty($host[5])) $h_vendor = strtoupper(RemoveGarbage($host[5]));
				list($h_ip, $h_port) = explode(':', $host[1], 2);

				if(!ValidateHostKickStart($host[1], $net) || IsIPInBlockList($h_ip))
					$result = 9;
				elseif($h_vendor !== null && (strlen($h_vendor) !== 4 || $h_vendor === 'LIME'))  /* Skip LimeWire (often fake) and invalid vendors from KickStart */
					$result = 10;
				else
					$result = WriteHostFile($net, $h_ip, $h_port, "", "", "", $h_vendor, "", 'KickStart', 0, false);

				if( $result == 0 ) // Exists
					echo "<b>I|update|OK|Host already updated</b><br>\r\n";
				elseif( $result == 1 ) // Updated timestamp
					echo "<b>I|update|OK|Updated host timestamp</b><br>\r\n";
				elseif( $result == 2 ) // OK
					echo "<b>I|update|OK|Host added successfully</b><br>\r\n";
				elseif( $result == 3 ) // OK, pushed old data
				{
					echo "<b>I|update|OK|Host added successfully (pushed old data)</b><br>\r\n";
					break;
				}
				elseif( $result == 4 ) // Error, failed verification (do NOT happens here)
				{
					echo "<b>Invalid return code</b><br>\r\n";
					break;
				}
				elseif( $result == 9 ) // Blocked host
					echo '<b class="bad">I|update|WARNING|Invalid host</b><br>',"\r\n";
				elseif( $result == 10 ) // Skipped host
					echo '<b class="bad">I|update|WARNING|Skipped host</b><br>',"\r\n";
				else
					echo "<font color=\"red\"><b>I|update|ERROR|Unknown error 3, return value = ".$result."</b></font><br>\r\n";
			}
		}
		fclose ($fp);
	}
}
?>