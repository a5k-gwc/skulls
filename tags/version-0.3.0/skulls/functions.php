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

function InitializeNetworkFile($net, $show_errors = FALSE){
	$net = strtolower($net);
	if(!file_exists(DATA_DIR."/hosts_".$net.".dat"))
	{
		$file = @fopen(DATA_DIR."/hosts_".$net.".dat", "wb");
		if($file !== FALSE) fclose($file);
		elseif($show_errors)
			echo "<font color=\"red\">Error during writing of ".DATA_DIR."/hosts_".$net.".dat</font><br>";
	}
}

function Initialize($supported_networks, $show_errors = FALSE, $forced = FALSE){
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
	if(!file_exists(DATA_DIR."/caches.dat"))
	{
		$file = @fopen(DATA_DIR."/caches.dat", "wb");
		if($file !== FALSE) fclose($file);
		else $errors .= "<font color=\"red\">Error during writing of ".DATA_DIR."/caches.dat</font><br>";
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
		if(!file_exists("stats/update_requests_hour.dat"))
		{
			$file = @fopen("stats/update_requests_hour.dat", "wb");
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/update_requests_hour.dat</font><br>";
		}
		if(!file_exists("stats/other_requests_hour.dat"))
		{
			$file = @fopen("stats/other_requests_hour.dat", "wb");
			if($file !== FALSE) fclose($file);
			else $errors .= "<font color=\"red\">Error during writing of stats/other_requests_hour.dat</font><br>";
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

	if(!file_exists(".htaccess"))
	{
		$file = @fopen(".htaccess", "wb");
		if($file !== FALSE)
		{
			flock($file, 2);
			fwrite($file, "RewriteEngine On\r\n\r\n# This should block the extension strip for the cache that is enabled on some servers\r\nRewriteRule ^skulls$ skulls.php$1 [R=permanent,L]\r\n");
			flock($file, 3);
			fclose($file);
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

	$fp = @fsockopen($host_name, $port, $errno, $errstr, (float)TIMEOUT);

	if(!$fp)
	{
		echo "<font color=\"red\"><b>Error ".$errno.".</b></font><br>\r\n";
		return;
	}
	else
	{
		fwrite( $fp, "GET ".substr( $cache, strlen($main_url[0]), (strlen($cache) - strlen($main_url[0]) ) )."?get=1&hostfile=1&client=".VENDOR."&version=".SHORT_VER."&cache=1&net=".$net." HTTP/1.0\r\nHost: ".$host_name."\r\nUser-Agent: ".NAME." ".VER."\r\nConnection: Close\r\n\r\n" );
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
				if(CheckIPValidity($ip_port[0], $host[1]))
					$is_host = TRUE;
			}
			elseif(strtolower(substr($line, 0, 2)) == "u|")	// Cache
			{
			}
			elseif(strtolower(substr($line, 0, 2)) == "i|")	// Info
			{
			}
			elseif(strtolower(substr($line, 0, 2)) == "d|")	// Debug
			{
			}
			elseif(strpos($line, ":") > -1)					// Host (old method)
			{
				unset($host);
				$host[1] = $line;
				$ip_port = explode(":", $host[1]);	// $ip_port[0] = IP	$ip_port[1] = Port
				if(CheckIPValidity($ip_port[0], $host[1]))
					$is_host = TRUE;
			}

			if($is_host)
			{
				if(isset($host[3]) && strlen($host[3]) <= 256) // Cluster
					$cluster = RemoveGarbage($host[3]);
				else
					$cluster = NULL;
				$result = WriteHostFile( $ip_port[0], rtrim($host[1]), NULL, $net, $cluster, "KICKSTART", "1.0" );

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
				else
					echo "<font color=\"red\"><b>I|update|ERROR|Unknown error 3, return value = ".$result."</b></font><br>\r\n";
			}
		}
		fclose ($fp);
	}
}
?>