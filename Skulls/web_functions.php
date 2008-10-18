<?php
function ReplaceVendorCode($client, $version){
	$cache = 0;
	if( $client == "TEST" && (float)$version == 0 && substr($version, 0, 1) != "0" )	// If $client is TEST and $version isn't numeric...
	{
		list( $client, $version ) = explode(" ", $version, 2);
		$cache = 1;
	}

	switch($client)
	{
		case "ACQL":
			$client_name = "Acqlite";
			$url = "http://acqlite.sourceforge.net/";
			break;
		case "ACQX":
			$client_name = "Acquisition";
			$url = "http://www.acquisitionx.com/";
			break;
		case "AGIO":
			$client_name = "Adagio";
			$url = "http://sourceforge.net/projects/agio/";
			break;
		case "BEAR":
			$client_name = "BearShare";
			$url = "http://www.bearshare.com/";
			break;
		case "COCO":
			$client_name = "CocoGnut";
			$url = "http://www.alpha-programming.co.uk/software/cocognut/";
			break;
		case "DNET":
			$client_name = "Deepnet Explorer";
			$url = "http://www.deepnetexplorer.com/";
			break;
		case "GDNA":
			$client_name = "GnucDNA";
			$url = "http://www.gnucleus.com/GnucDNA/";
			break;
		case "GIFT":
			$client_name = "giFT";
			$url = "http://gift.sourceforge.net/";
			break;
		case "GNUC":
			$client_name = "Gnucleus";
			$url = "http://www.gnucleus.com/Gnucleus/";
			break;
		case "GNZL":
			$client_name = "Gnoozle";
			$url = "";
			break;
		case "GOLD":
			$client_name = "Ares Gold";
			$url = "";
			break;
		case "GPUX":
			$client_name = "GPU";
			$url = "http://sourceforge.net/projects/gpu/";
			break;
		case "GTKG":
			$client_name = "GTK Gnutella";
			$url = "http://gtk-gnutella.sourceforge.net/";
			break;
		case "LIME":
			$client_name = "LimeWire";
			$url = "http://www.limewire.com/";
			break;
		case "MESH":
			$client_name = "iMesh";
			$url = "http://www.imesh.com/";
			break;
		case "MLDK":
			$client_name = "MLDonkey";
			$url = "http://www.mldonkey.net/";
			break;
		case "MMMM":
		case "MRPH":
			$client_name = "Morpheus";
			$url = "http://www.morpheus.com/";
			break;
		case "MNAP":
			$client_name = "MyNapster";
			$url = "http://www.mynapster.com/";
			break;
		case "MTLL":	// Vendor code of Mutella is changed to MTLL in the code to avoid confusion with MUTE network
			$client_name = "Mutella";
			$url = "http://mutella.sourceforge.net/";
			break;
		case "MUTE":	// MUTE (A MUTE net client)
			$client_name = "MUTE";
			$url = "http://mute-net.sourceforge.net/";
			break;
		case "MXIE":
			$client_name = "mxie";
			$url = "http://www.mxie.com/";
			break;
		case "NOVA":
			$client_name = "Nova";
			$url = "http://novap2p.sourceforge.net/";
			break;
		case "PHEX":
			$client_name = "Phex";
			$url = "http://phex.kouk.de/mambo/";
			break;
		case "RAZA":
			$client_name = "Shareaza";
			$url = "http://shareaza.sourceforge.net/";
			break;
		case "RAZB":
			$client_name = "ShareazaBeta";		// Beta version of Shareaza
			$url = "http://shareaza.sourceforge.net/help/?beta";
			break;
		case "RAZL":
			$client_name = "ShareazaLite";
			$url = "http://sourceforge.net/projects/flox/";
			break;
		case "RZCB":
			$client_name = "ShareazaPlus";
			$url = "http://shareazaplus.sourceforge.net/";
			break;
		case "SNOW":
			$client_name = "FrostWire";
			$url = "http://www.frostwire.com/";
			break;
		case "SWAP":
			$client_name = "Swapper";
			$url = "http://www.revolutionarystuff.com/swapper/";
			break;
		case "TFLS":
			$client_name = "TrustyFiles";
			$url = "http://www.trustyfiles.com/";
			break;
		case "XOLO":
			$client_name = "XoloX";
			$url = "http://www.xolox.nl/";
			break;

		case "PGDBScan":
			$client_name = "Jon Atkins GWC scan";
			$url = "http://gcachescan.jonatkins.com/";
			break;
		case "WURM":
			$client_name = "Wurm Scanner";
			$url = "http://kevogod.trillinux.org/";
			break;
		case "KICKSTART":
			$client_name = "KickStart";
			$url = "";
			break;

		case "BAZK":
		case "Bazooka":
			$client_name = "Bazooka";
			$url = "http://www.bazookanetworks.com/";
			$cache = 2;
			break;
		case "BCII":
			$client_name = "Beacon Cache II";
			$url = "http://sourceforge.net/projects/beaconcache/";
			$cache = 2;
			break;
		case "BCON":
			$client_name = "Beacon Cache";
			$url = "http://sourceforge.net/projects/beaconcache/";
			$cache = 2;
			break;
		case "Cachechu":
			$client_name = "Cachechu";
			$url = "http://code.google.com/p/cachechu/";
			$cache = 2;
			break;
		case "CANN":
			$client_name = "Cannon";
			$url = "";
			$cache = 2;
			break;
		case "CHTC":
			$client_name = "CheaterCache";
			$url = "";
			$cache = 2;
			break;
		case "GCII":
			$client_name = "PHPGnuCacheII";
			$url = "http://gwcii.sourceforge.net/";
			$cache = 2;
			break;
		case "JGWC":
			$client_name = "Jums Web Cache";
			$url = "http://www1.mager.org/GWebCache/";
			$cache = 2;
			break;
		case "MWebCache":
			$client_name = "MWebCache";
			$url = "http://sourceforge.net/tracker/index.php?func=detail&aid=1588787&group_id=83030&atid=568086";
			$cache = 2;
			break;
		case "SKLL":
			$client_name = "Skulls";
			$url = "http://sourceforge.net/projects/skulls/";
			$cache = 2;
			break;

		case "TEST":
			$cache = 1;
		default:
			if($cache)
				return "WebCache (".$client." ".$version.")";
			elseif( $client != "" )
				$client_name = $client;
			else
				$client_name = "Unknown client";
			$url = "";
	}

	if($cache == 2) $version .= " (WebCache)";

	if( $url != "" )
		return "<a href=\"".$url."\" target=\"_blank\">".$client_name." ".$version."</a>";
	else
		return $client_name." ".$version;
}

function QueryUpdateServer($url = "http://skulls.sourceforge.net/latest_ver.php", $came_from = NULL){
	global $MY_URL;
	$debug = FALSE;

	list( , $url ) = explode("://", $url, 2);		// It remove "http://" from $url - $url = www.test.com:80/page.php
	$main_url = explode("/", $url);					// $main_url[0] = www.test.com:80		$main_url[1] = page.php
	$splitted_url = explode(":", $main_url[0], 2);	// $splitted_url[0] = www.test.com		$splitted_url[1] = 80

	if( count($splitted_url) > 1 )
		list($host_name, $port) = $splitted_url;
	else
	{
		$host_name = $main_url[0];
		$port = 80;
	}

	$fp = @fsockopen( $host_name, $port, $errno, $errstr, TIMEOUT );
	$status = NULL;
	$msg = NULL;
	$msg_error = NULL;
	$msg_info = NULL;
	if($debug) echo "---------------<br>";

	if(!$fp)
	{
		$status = "SOCK_ERROR";
		$msg = "Error ".$errno;
	}
	else
	{
		$query = "update_check=1&client=".VENDOR."&url=http://".$MY_URL."&cache=1";

		fwrite( $fp, "GET ".substr( $url, strlen($main_url[0]), (strlen($url) - strlen($main_url[0]) ) )."?".$query." HTTP/1.0\r\nHost: ".$host_name."\r\n\r\n");
		while ( !feof($fp) )
		{
			$line = rtrim( fgets( $fp, 1024 ) );
			if($debug) echo $line."<br>";

			if( strtolower( substr( $line, 0, 2 ) ) == "v|" )
			{
				$received_data = explode("|", $line);
				$msg = $received_data[1];
				if( !empty($msg) )
					$status = "OK";
			}
			elseif( strtolower( substr( $line, 0, 2 ) ) == "a|" && $status != "OK" )
			{
				$received_data = explode("|", $line);
				$alternate_url = $received_data[1];

				if($alternate_url != $url && $alternate_url != $came_from && $alternate_url != NULL)
				{
					$returned_data = QueryUpdateServer($alternate_url, $url);
					$returned_data = explode("|", $returned_data);
					$status = $returned_data[0];
					$msg = $returned_data[1];
					$msg_info = $returned_data[3];
					$msg_error = $returned_data[4];
				}
				elseif($debug) echo "<font color=\"red\"><b>Loop</b></font><br>";
			}
			elseif( strtolower( substr( $line, 0, 7 ) ) == "i|info|" )
			{
				$received_data = explode("|", $line);
				$msg_info .= $received_data[2]."<br>";
			}
			elseif( strtolower( substr( $line, 0, 8 ) ) == "i|error|" )
			{
				$received_data = explode("|", $line);
				$msg_error .= $received_data[2]."<br>";
			}
			elseif(strpos($line, "404 Not Found") > -1)
			{
				$status = "404";
				$msg = $line;
			}
			elseif(strpos($line, "403 Forbidden") > -1)
			{
				$status = "403";
				$msg = $line;
			}
			
		}

		fclose ($fp);
	}

	if($debug) echo "Status: ".RemoveGarbage($status)."<br>---------------<br>";
	return RemoveGarbage($status)."|".RemoveGarbage($msg)."||".RemoveGarbage($msg_info)."|".RemoveGarbage($msg_error);
}

function CheckUpdates(){
	if(!file_exists(DATA_DIR."/update_check.dat"))
	{
		$file = @fopen(DATA_DIR."/update_check.dat", "xb");
		if($file !== FALSE) fclose($file);
		else
		{
			echo "<font color=\"red\"><b>Error during writing of ".DATA_DIR."/update_check.dat</b></font><br>";
			echo "<b>You must create the file manually, and give to the file the correct permissions.</b><br><br>";
			die();
		}
	}

	$file = @file(DATA_DIR."/update_check.dat");
	if($file === FALSE)
	{
		echo "<font color=\"red\"><b>Error during reading of ".DATA_DIR."/update_check.dat</b></font><br>";
		echo "<b>You must give to the file the correct permissions.</b><br><br>";
		die();
	}

	$cached = FALSE;
	if(count($file))
	{
		list($status, $msg, $latest_check, $msg_info, $msg_error) = explode("|", $file[0]);

		$time_diff = time() - ( @strtotime( $latest_check ) + @date("Z") );	// GMT
		if($status == "SOCK_ERROR") $time_diff = floor($time_diff / 3600);	// Hours
		else $time_diff = floor($time_diff / 86400);						// Days

		if($time_diff < 2)
			$cached = TRUE;
		elseif($status == "OK" && $time_diff < 7)
			$cached = TRUE;
	}

	if(!$cached)
	{
		global $SERVER_NAME;
		$ip = gethostbyname($SERVER_NAME);

		if($ip == "127.0.0.1")
		{
			echo "<font color=\"gold\"><b>Update check not allowed from localhost</b></font><br>";
			return NULL;
		}

		$returned_data = QueryUpdateServer();
		$returned_data = explode("|", $returned_data);
		$status = $returned_data[0];
		$msg = $returned_data[1];
		$msg_info = $returned_data[3];
		$msg_error = $returned_data[4];
	}

	if($status == "SOCK_ERROR" || $status == "403")
	{
		echo "<b>Update check process:</b> ";
		if(FSOCKOPEN)
			echo "<font color=\"red\"><b>".$msg."</b></font><br>\r\n";
		else
			echo "<font color=\"gold\"><b>Unable to check without fsockopen</b></font><br>\r\n";
	}
	elseif($status == "OK")
	{
		//echo "<b>Update check process:</b> <font color=\"green\"><b>OK</b></font><br>\r\n";	// Debug
		if($msg_error != "") echo "<b><font style=\"color: #FF0000;\">ERRORS: ".$msg_error."</font></b><br>\r\n";
	}
	elseif($status == "404")
		echo "<b>Update check process:</b> <font color=\"red\"><b>Invalid query or file deleted</b></font><br>\r\n";
	else
	{
		echo "<b>Update check process:</b> <font color=\"red\"><b>Server response incorrect, maybe there are problems in the update server</b></font><br>\r\n";
		$status = "INCORRECT";
	}

	if(!$cached)
	{
		$file = @fopen(DATA_DIR."/update_check.dat", "wb");
		if($file !== FALSE)
		{
			flock($file, 2);
			fwrite($file, $status."|".$msg."|".gmdate("Y/m/d H:i")."|".$msg_info."|".$msg_error);
			flock($file, 3);
			fclose($file);
		}
		else
		{
			echo "<font color=\"red\"><b>Error during writing of ".DATA_DIR."/update_check.dat</b></font><br>";
			echo "<b>You must create the file manually, and give to the file the correct permissions.</b><br><br>";
		}
	}

	$result["status"] = $status;
	$result["latest_ver"] = $msg;
	$result["update_info"] = $msg_info;
	return $result;
}

function ShowUpdateCheck(){
	$result = CheckUpdates();

	if($result["status"] == "OK")
	{
		$need_update = FALSE;

		if((float)SHORT_VER < (float)$result["latest_ver"])
			$need_update = TRUE;
		elseif((float)SHORT_VER == (float)$result["latest_ver"])
		{
			list( , , $last_digit) = explode(".", SHORT_VER);
			list( , , $last_digit_of_latest_version) = explode(".", $result["latest_ver"]);
			if($last_digit < $last_digit_of_latest_version)
				$need_update = TRUE;
		}

		if($need_update) $color = "red";
		else $color = "green";
		echo "<b>Latest version: <font color=\"green\">".$result["latest_ver"]."</font></b><br>";
		echo "<b>This version: <font color=\"".$color."\">".SHORT_VER."</font></b><br>";

		if($need_update)
		{
			if($result["update_info"] != "") echo $result["update_info"]."\r\n";
			echo "<br><font color=\"".$color."\"><b>There is a new version of ".NAME.", ";
			echo "please visit the download page of <a href=\"".GWC_SITE."\" class=\"hover-underline\" target=\"_blank\">".NAME."</a> to obtain the latest version.</b></font><br>";
		}
	}
}
?>