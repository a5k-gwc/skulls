<?php
function InitializeNetworkFile($net){
	$net = strtolower($net);
	if( !file_exists(DATA_DIR."/hosts_".$net.".dat") ) fclose( fopen(DATA_DIR."/hosts_".$net.".dat", "xb") );
}

function Initialize($supported_networks){
	if( !file_exists(DATA_DIR."/runnig_since.dat") )
	{
		$file = fopen( DATA_DIR."/runnig_since.dat", "wb" );
		if( !$file )
			die("ERROR: Writing file failed.\r\n");
		else
		{
			flock($file, 2);
			fwrite($file, gmdate("Y/m/d h:i:s A"));
			flock($file, 3);
			fclose($file);
		}
	}
	if( !file_exists(DATA_DIR."/caches.dat") ) fclose( fopen(DATA_DIR."/caches.dat", "xb") );
	if( !file_exists(DATA_DIR."/failed_urls.dat") ) fclose( fopen(DATA_DIR."/failed_urls.dat", "xb") );

	for( $i = 0; $i < NETWORKS_COUNT; $i++ )
		InitializeNetworkFile( $supported_networks[$i] );

	if( STATS_ENABLED )
	{
		if( !file_exists("stats/") ) mkdir("stats/", 0777);
		if( !file_exists("stats/requests.dat") )
		{
			$file = fopen( "stats/requests.dat", "xb" );
			flock($file, 2);
			fwrite($file, "0");
			flock($file, 3);
			fclose($file);
		}
		if( !file_exists("stats/update_requests_hour.dat") ) fclose( fopen("stats/update_requests_hour.dat", "xb") );
		if( !file_exists("stats/other_requests_hour.dat") ) fclose( fopen("stats/other_requests_hour.dat", "xb") );
	}
}

function KickStart($net, $cache){
	if( !CheckURLValidity($cache) )
		die("ERROR: The KickStart URL isn't valid\r\n");

	list( , $cache ) = explode("://", $cache, 2);		// It remove "http://" from "cache" - $cache = www.test.com:80/page.php
	$main_url = explode("/", $cache);					// $main_url[0] = www.test.com:80		$main_url[1] = page.php
	$splitted_url = explode(":", $main_url[0], 2);		// $splitted_url[0] = www.test.com		$splitted_url[1] = 80

	if(count($splitted_url) == 2)
		list($host_name, $port) = $splitted_url;
	else
	{
		$host_name = $main_url[0];
		$port = 80;
	}

	$fp = @fsockopen( $host_name, $port, $errno, $errstr, TIMEOUT );

	if(!$fp)
	{
		echo "<font color=\"red\"><b>Error ".$errno."</b></font><br>\r\n";
		return;
	}
	else
	{
		fputs( $fp, "GET ".substr( $cache, strlen($main_url[0]), (strlen($cache) - strlen($main_url[0]) ) )."?get=1&hostfile=1&client=".VENDOR."&version=".SHORT_VER."&cache=1&net=".$net." HTTP/1.0\r\nHost: ".$host_name."\r\n\r\n" );
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
				$result = WriteHostFile( rtrim($host[1]), NULL, $net, $cluster, "KICKSTART", "1.0" );

				if( $result == 1 ) // Updated timestamp
					echo "<b>I|update|OK|Updated host timestamp</b><br>\r\n";
				elseif( $result == 2 ) // OK
					echo "<b>I|update|OK|Host added successfully</b><br>\r\n";
				elseif( $result == 3 ) // OK, pushed old data
				{
					echo "<b>I|update|OK|Host added successfully - pushed old data</b><br>\r\n";
					break;
				}
				else
					echo "<font color=\"red\"><b>I|error</b></font><br>\r\n";
			}
		}
		fclose ($fp);
	}
}
?>