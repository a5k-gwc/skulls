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
			$client_name = "Morpheus";
			$url = "http://www.morpheus.com/";
			break;
		case "MRPH":
			$client_name = "Morpheus";
			$url = "http://www.morpheus.com/";
			break;
		case "MNAP":
			$client_name = "MyNapster";
			$url = "http://www.mynapster.com/";
			break;
		case "MUTE":
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
			$url = "http://ale5000.altervista.org/software.htm";
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
?>