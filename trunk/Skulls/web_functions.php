<?php
function ReplaceVendorCode($client, $version){
	$cache = 0;
	if( $client == "TEST" && (float)$version == 0 && substr($version, 0, 1) != "0" )
	{
		$client = $version;
		$version = "";
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
			$url = "http://www.shareaza.com/";
			break;
		case "RAZB":
			$client_name = "SBeta (Shareaza beta)";		// Beta version of Shareaza
			$url = "http://www.shareaza.com/beta/";
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
			$client_name = "Jonatkins scan";
			$url = "http://gcachescan.jonatkins.com/";
			break;
		case "KICKSTART":
			$client_name = "KickStart";
			$url = "";
			break;
		case "TEST":
			$client_name = "WebCache";
			$url = "";
			break;

		case "BAZK":
			$client_name = "Bazooka (WebCache)";
			$url = "http://rocketx.port5.com/";
			break;
		case "GCII":
			$client_name = "PHPGnuCacheII (WebCache)";
			$url = "http://gwcii.sourceforge.net/";
			break;
		case "SKLL":
			$client_name = "Skulls (WebCache)";
			$url = "http://sourceforge.net/projects/skulls/";
			break;

		default:
			if($cache)
				$client_name = "WebCache (".$client.")";
			elseif( $client != "" )
				$client_name = $client;
			else
				$client_name = "Unknown client";

			$url = "";
	}

	if( $url != "" )
		return "<a href=\"".$url."\" target=\"_blank\">".$client_name." ".$version."</a>";
	else
		return $client_name." ".$version;
}

function CheckUpdates($url = "http://skulls.sourceforge.net/latest_ver.php", $came_from = NULL){
	$debug = FALSE;
	global $PHP_SELF;
	$SERVER_NAME = $_SERVER["SERVER_NAME"];

	$relayed = FALSE;
	$status = NULL;
	if(!file_exists(DATA_DIR."/update_check.dat"))
	{
		$file = @fopen(DATA_DIR."/update_check.dat", "xb");
		if($file)
		{
			flock($file, 2);
			fwrite($file, "UNCHECKED||".gmdate("Y/m/d H:i", 1));
			flock($file, 3);
			fclose($file);
		}
		else
		{
			echo "<font color=\"red\"><b>Error during writing of ".DATA_DIR."/update_check.dat</b></font><br>";
			echo "<b>You must create the file manually, and give to the file the correct permissions.</b><br><br>";
			die();
		}
	}

	$file = file(DATA_DIR."/update_check.dat");
	if(count($file) > 0)
	{
		list($status, $latest_version, $latest_check) = explode("|", $file[0]);

		$time_diff = time() - ( @strtotime( $latest_check ) + @date("Z") );	// GMT
		if(strpos($status, "Error") > -1)
			$time_diff = floor($time_diff / 3600);	// Hours
		else
			$time_diff = floor($time_diff / 86400);	// Days
	}
	else
		$time_diff = 100;

	if($status == "OK" && $time_diff < 7)
		$cached = TRUE;
	elseif($time_diff < 2)
		$cached = TRUE;
	else
	{
		if($SERVER_NAME == "localhost" || $SERVER_NAME == "127.0.0.1")
		{
			echo "<font color=\"gold\"><b>Update check not allowed from localhost</b></font><br>";
			return NULL;
		}
		$cached = FALSE;
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

		$fp = @fsockopen( $host_name, $port, $errno, $errstr, 20 );
		$latest_version = NULL;
		$status = NULL;

		if(!$fp)
		{
			$status = "Error ".$errno;
		}
		else
		{
			$alternate_url = NULL;
			$query = "update_check=1&client=".VENDOR."&url=http://".$SERVER_NAME.$PHP_SELF."&cache=1";

			fputs( $fp, "GET ".substr( $url, strlen($main_url[0]), (strlen($url) - strlen($main_url[0]) ) )."?".$query." HTTP/1.0\r\nHost: ".$host_name."\r\n\r\n");
			while ( !feof($fp) )
			{
				$line = fgets( $fp, 1024 );
				if($debug) echo $line."<br>";

				if( strtolower( substr( $line, 0, 2 ) ) == "v|" )
				{
					$latest_version = rtrim($line);
					list( , $latest_version) = explode("|", $latest_version);
					break;
				}
				elseif( strtolower( substr( $line, 0, 2 ) ) == "a|" )
				{
					$alternate_url = rtrim($line);
					list( , $alternate_url) = explode("|", $alternate_url);
					if($alternate_url != $url && $alternate_url != $came_from && $alternate_url != NULL)
					{
						$latest_version = CheckUpdates($alternate_url, $url);
						$relayed = TRUE;
						break;
					}
				}
				elseif(strpos($line, "404 Not Found") > -1)
				{
					$status = "404";
					break;
				}
			}

			fclose ($fp);
		}
	}

	if(!$relayed)
	{
		if(strpos($status, "Error") > -1)
			echo "<font color=\"red\"><b>".$status."</b></font><br>\r\n";
		elseif($status == "404")
			echo "<font color=\"red\"><b>Invalid query or file deleted</b></font><br>\r\n";
		elseif($status == "INCORRECT" || empty($latest_version))
		{
			echo "<font color=\"red\"><b>Server response incorrect, maybe there are problems in the update server</b></font><br>\r\n";
			$status = "INCORRECT";
			$latest_version = NULL;
		}
		else
		{
			echo "<font color=\"green\"><b>OK</b></font><br>\r\n";
			$status = "OK";
		}

		if(!$cached)
		{
			$file = fopen(DATA_DIR."/update_check.dat", "wb");
			if($file)
			{
				flock($file, 2);
				fwrite($file, RemoveGarbage($status)."|".RemoveGarbage($latest_version)."|".gmdate("Y/m/d H:i"));
				flock($file, 3);
				fclose($file);
			}
			else
			{
				echo "<font color=\"red\"><b>Error during writing of ".DATA_DIR."/update_check.dat</b></font><br>";
				echo "<b>You must create the file manually, and give to the file the correct permissions.</b><br><br>";
			}
		}
	}

	return $latest_version;
}

function ShowUpdateCheck(){
	$latest_version = CheckUpdates();

	if($latest_version != NULL)
	{
		$need_update = FALSE;

		if((float)SHORT_VER < (float)$latest_version)
			$need_update = TRUE;
		elseif((float)SHORT_VER == (float)$latest_version)
		{
			list( , , $last_digit) = explode(".", SHORT_VER);
			list( , , $last_digit_of_latest_version) = explode(".", $latest_version);
			if($last_digit < $last_digit_of_latest_version)
				$need_update = TRUE;
		}

		if($need_update) $color = "red";
		else $color = "green";
		echo "<b>Latest version: <font color=\"green\">".$latest_version."</font></b><br>";
		echo "<b>This version: <font color=\"".$color."\">".SHORT_VER."</font></b><br>";

		if($need_update)
		{
			echo "<br><font color=\"".$color."\"><b>There is a new version of ".NAME.", ";
			echo "please visit <a href=\"http://sourceforge.net/projects/skulls/\" class=\"hover-underline\" target=\"_blank\">".NAME." project page</a> to obtain the latest version.</b></font><br>";
		}
	}
}
?>