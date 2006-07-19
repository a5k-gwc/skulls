<?php
function ReplaceVendorCode($client, $version){
	if( $client == "TEST" && (float)$version == 0 && substr($version, 0, 1) != "0" )
	{
		$client = $version;
		$version = "";
		$test = 1;
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
			if(isset($test))
				$client_name = "WebCache (".$client.")";
			elseif( $client != "" )
				$client_name = "Unknown client (".$client.")";
			else
				$client_name = "Unknown client";

			$url = "";
    }

	if( $url != "" )
		return "<a href=\"".$url."\" target=\"_blank\">".$client_name." ".$version."</a>";
	else
		return $client_name." ".$version;
}
?>