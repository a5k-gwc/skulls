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

function DetectServer()
{
	if(function_exists('apache_get_version') && apache_get_version() !== false)
		return 'Apache';

	if(!empty($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'apache') === 0)
		return 'Apache';

	return false;
}

function InitializeNetworkFile($net, $show_errors = FALSE)
{
	$net = strtolower($net);
	if(!file_exists(DATA_DIR."/hosts_".$net.".dat"))
	{
		$file = @fopen(DATA_DIR."/hosts_".$net.".dat", "wb");
		if($file !== FALSE) fclose($file);
		elseif($show_errors)
			echo "<font color=\"red\">Error during writing of ".DATA_DIR."/hosts_".$net.".dat</font><br>";
	}
	else
	{
		$fp = @fopen(DATA_DIR."/hosts_".$net.".dat", "r+b"); if($fp === false) return;
		flock($fp, LOCK_EX);
		$line = fgets($fp, 200);
		if($line !== false && strpos($line, '|') !== false)
		{
			list($field1, $field2, ) = explode('|', $line, 3);
			/* Old format => ip:port|leaves|... */
			/* New format => date|ip|port|... */
			if(strpos($field1, '.') !== false && strpos($field2, '.') === false)
				ftruncate($fp, 0);  /* If the file is in the old format truncate it */
		}
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}

function Initialize($supported_networks, $show_errors = FALSE, $forced = FALSE)
{
	$errors = "";
	$running_since = gmdate("Y/m/d h:i:s A");
	if(!file_exists(DATA_DIR."/running_since.dat"))
	{
		if(file_exists(DATA_DIR."/runnig_since.dat"))
		{
			if(!rename(DATA_DIR."/runnig_since.dat", DATA_DIR."/running_since.dat"))
				$errors .= "<font color=\"red\">Error during renaming of ".DATA_DIR."/runnig_since.dat to ".DATA_DIR."/running_since.dat</font><br>";
		}
		else
		{
			$file = @fopen( DATA_DIR."/running_since.dat", "wb" );
			if($file !== FALSE)
			{
				flock($file, 2); fwrite($file, $running_since); flock($file, 3); fclose($file);
			}
			else $errors .= "<font color=\"red\">Error during writing of ".DATA_DIR."/running_since.dat</font><br>";
		}
	}
	elseif(!$forced)
	{
		$file = @fopen(DATA_DIR."/running_since.dat", "r+b");
		if($file !== FALSE)
		{
			flock($file, 2);
			$line = fgets($file);
			if(rtrim($line) == "") { rewind($file); fwrite($file, $running_since); }
			flock($file, 3);
			fclose($file);
		}
		else $errors .= "<font color=\"red\">Error during reading of ".DATA_DIR."/running_since.dat</font><br>";
	}
	if(!file_exists(DATA_DIR."/alt-gwcs.dat"))
	{
		$file = @fopen(DATA_DIR."/alt-gwcs.dat", "wb");
		if($file !== FALSE) fclose($file);
		else $errors .= "<font color=\"red\">Error during writing of ".DATA_DIR."/alt-gwcs.dat</font><br>";
	}
	if(!file_exists(DATA_DIR."/alt-udps.dat"))
	{
		$file = @fopen(DATA_DIR."/alt-udps.dat", "wb");
		if($file !== FALSE) fclose($file);
		else $errors .= "<font color=\"red\">Error during writing of ".DATA_DIR."/alt-udps.dat</font><br>";
	}
	if(!file_exists(DATA_DIR."/failed_urls.dat"))
	{
		$file = @fopen(DATA_DIR."/failed_urls.dat", "wb");
		if($file !== FALSE) fclose($file);
		else $errors .= "<font color=\"red\">Error during writing of ".DATA_DIR."/failed_urls.dat</font><br>";
	}

	for( $i = 0; $i < NETWORKS_COUNT; $i++ )
		InitializeNetworkFile($supported_networks[$i], $show_errors);

	if(STATS_ENABLED)
	{
		if(!file_exists("stats/")) mkdir("stats/", 0777);
		if(!file_exists("stats/requests.dat"))
		{
			$file = @fopen("stats/requests.dat", "wb");
			if($file !== FALSE) { flock($file, 2); fwrite($file, "0"); flock($file, 3); fclose($file); }
			else $errors .= "<font color=\"red\">Error during writing of stats/requests.dat</font><br>";
		}
		if(!file_exists("stats/upd-reqs.dat"))
		{
			$file = @fopen("stats/upd-reqs.dat", "wb");
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/upd-reqs.dat</font><br>";
		}
		if(!file_exists("stats/upd-bad-reqs.dat"))
		{
			$file = @fopen("stats/upd-bad-reqs.dat", "wb");
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/upd-bad-reqs.dat</font><br>";
		}
		if(!file_exists("stats/blocked-reqs.dat"))
		{
			$file = @fopen("stats/blocked-reqs.dat", "wb");
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/blocked-reqs.dat</font><br>";
		}
		if(!file_exists("stats/other-reqs.dat"))
		{
			$file = @fopen("stats/other-reqs.dat", "wb");
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/other-reqs.dat</font><br>";
		}
	}

	if(!file_exists("admin/revision.dat"))
	{
		$file = @fopen("admin/revision.dat", "wb");
		if($file !== FALSE)
		{
			flock($file, 2);
			fwrite($file, "0");
			flock($file, 3);
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

	if(!$fp)
	{
		echo "<font color=\"red\"><b>Error ".$errno.".</b></font><br>\r\n";
		return;
	}
	else
	{
		include './update.php';

		fwrite( $fp, "GET ".substr( $cache, strlen($main_url[0]), (strlen($cache) - strlen($main_url[0]) ) )."?get=1&hostfile=1&getvendors=1&client=".VENDOR."&version=".SHORT_VER."&cache=1&net=".$net." ".$_SERVER['SERVER_PROTOCOL']."\r\nHost: ".$host_name."\r\nUser-Agent: ".NAME." ".VER."\r\nConnection: close\r\n\r\n" );
		while( !feof($fp) )
		{
			$is_host = FALSE;
			$line = rtrim(fgets($fp, 1024));
			echo $line."<br>";

			if(strtolower(substr($line, 0, 2)) == "h|")		// Host
			{
				unset($host);
				$host = explode("|", $line);
				$ip_port = explode(":", $host[1]);	// $ip_port[0] = IP	$ip_port[1] = Port
				if(ValidateHost($host[1], $ip_port[0]))
					$is_host = TRUE;
			}
			elseif(strtolower(substr($line, 0, 2)) == "u|");	// GWC
			elseif(strtolower(substr($line, 0, 2)) == "i|");	// Info
			elseif(strtolower(substr($line, 0, 2)) == "d|");	// Debug
			elseif(strpos($line, '://') !== false);				// GWC (old method)
			elseif(strpos($line, ':') !== false)				// Host (old method)
			{
				unset($host);
				$host[1] = $line;
				$ip_port = explode(":", $host[1]);	// $ip_port[0] = IP	$ip_port[1] = Port
				if(ValidateHost($host[1], $ip_port[0]))
					$is_host = TRUE;
			}

			if($is_host)
			{
				$h_vendor = null;
				if(!IsIPInBlockList($ip_port[0]))
				{
					if(!empty($host[5])) $h_vendor = RemoveGarbage($host[5]);
					$result = WriteHostFile($net, $ip_port[0], rtrim($ip_port[1]), "", "", "", $h_vendor, "", 'KickStart');
				}
				else
					$result = 9;

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
				elseif( $result == 9 ) // Blocked host
					echo '<b class="bad">I|update|WARNING|Invalid host</b><br>',"\r\n";
				else
					echo "<font color=\"red\"><b>I|update|ERROR|Unknown error 3, return value = ".$result."</b></font><br>\r\n";
			}
		}
		fclose ($fp);
	}
}
?>