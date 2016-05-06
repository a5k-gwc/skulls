<?php
//  web_functions.php
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

function ReplaceVendorCode($vendor, $version, $ua, $is_a_gwc_param = 0)
{
	$IS_GWC = 0; $IS_crawler = false; $url = null; $ua_prefix = null;
	if( $vendor === 'TEST' && !ctype_digit(substr($version, 0, 1)) )  // If $vendor is TEST and $version does NOT start with a number then version contains both name and version
	{
		if(strpos($version, '/') !== false)
			list( $vendor, $version ) = explode('/', $version, 2);
		elseif(strpos($version, ' ') !== false)
			list( $vendor, $version ) = explode(' ', $version, 2);
		elseif(strpos($version, '-') !== false)
			list( $vendor, $version ) = explode('-', $version, 2);
		else
			{$vendor = $version; $version = "";}
		$IS_GWC = 1;
	}
	if($is_a_gwc_param === 1) $IS_GWC = 2;

	/* http://rfc-gnutella.sourceforge.net/ */
	switch($vendor)
	{
		case 'ACQL':
			$client_name = 'Acqlite';
			$url = 'http://acqlite.sourceforge.net/';
			break;
		case 'ACQX':
			$client_name = 'Acquisition';
			$url = 'http://www.acquisitionx.com/';
			break;
		case 'AGIO':
			$client_name = 'Adagio';
			$url = 'http://sourceforge.net/projects/agio/';
			break;
		case 'AGNT':
			$client_name = 'Agentella';
			break;
		case 'ARES':
			$client_name = 'Ares';  /* by SoftGap */
			$url = 'http://aresgalaxy.sourceforge.net/';
			break;
		case 'ATOM':
			$client_name = 'AtomWire';
			break;
		case 'BEAR':
			$client_name = 'BearShare';
			$url = 'https://en.wikipedia.org/wiki/BearShare';
			break;
		case 'COCO':
			$client_name = 'CocoGnut';
			$url = 'http://www.alpha-programming.co.uk/software/cocognut/';
			break;
		case 'CULT':
			$client_name = 'Cultiv8r (Emixode)';
			break;
		case 'DNET':
			$client_name = 'Deepnet Explorer';
			$url = 'http://www.deepnetexplorer.com/';
			break;
		case 'ENVY':
			$client_name = 'Envy';
			$url = 'https://sourceforge.net/projects/getenvy/';
			break;
		case 'FISH':
			$client_name = 'PEERanha';
			break;
		case 'FOXY':  /* Foxy - client of Foxy network */
			$client_name = 'Foxy';
			$url = 'https://en.wikipedia.org/wiki/Foxy_%28P2P%29';
			break;
		case 'FSCP':
			$client_name = 'Filescope';
			$url = 'http://filescope.com/';
			break;
		case 'FUST':
			$client_name = 'Fusteeno';
			break;
		case 'GDNA':
			$client_name = 'GnucDNA';
			$url = 'http://gnucleus.sourceforge.net/GnucDNA/';
			break;
		case 'GEN2':
			$client_name = 'Gentoo giFT-Gnutella';
			break;
		case 'GIFT':
			$client_name = 'giFT-Gnutella';
			$url = 'http://gift.sourceforge.net/';
			break;
		case 'GNEW':
			$client_name = 'Gnewtellium';
			$url = 'http://gnewtellium.sourceforge.net/';
			break;
		case 'GNOT':
			$client_name = 'Gnotella';
			break;
		case 'GNTG':
			$client_name = 'Gnutelligentsia';
			$url = 'http://gnutelligentsia.sourceforge.net/';
			break;
		case 'GNUC':
			$client_name = 'Gnucleus';
			$url = 'http://gnucleus.sourceforge.net/Gnucleus/';
			break;
		case 'GNUT':
			$client_name = 'Gnut';
			break;
		case 'GNZL':
			$client_name = 'Gnoozle';
			break;
		case 'GOLD':
			$client_name = 'Ares Gold';
			break;
		case 'GPUX':
			$client_name = 'GPU';
			$url = 'https://sourceforge.net/projects/gpu/';
			break;
		/* ToDO: Check also vendor GNTD, maybe old version */
		case 'GTKG':
			$client_name = 'Gtk-Gnutella';
			$url = 'http://gtk-gnutella.sourceforge.net/';
			break;
		case 'HSLG':
			$client_name = 'Hagelslag';
			$url = 'http://os4depot.net/index.php?function=showfile&file=network/p2p/hagelslag.lha';
			break;
		case 'HYDR':
			$client_name = 'Hydranode';
			$url = 'http://hydranode.com/';
			break;
		case 'LIME':
			$client_name = 'LimeWire';
			$url = 'https://en.wikipedia.org/wiki/LimeWire';
			break;
		case 'MACT':
			$client_name = 'Mactella';
			break;
		case 'MESH':
			$client_name = 'iMesh';
			$url = 'http://www.imesh.com/';
			break;
		case 'MLDK':
			$client_name = 'MLDonkey';
			$url = 'http://mldonkey.sourceforge.net/';
			break;
		case 'MMMM':  /* Morpheus 2.0+ */
			$client_name = 'Morpheus';
			$url = 'http://en.wikipedia.org/wiki/Morpheus_%28software%29';
			break;
		case 'MNAP':
			$client_name = 'MyNapster';
			break;
		case 'MOOD':
			$client_name = 'MoodAmp';
			break;
		case 'MRPH':  /* Morpheus - old versions */
			$client_name = 'Morpheus (old)';
			$url = 'http://en.wikipedia.org/wiki/Morpheus_%28software%29';
			break;
		case 'MUTE':  /* MUTE - client of MUTE network (Network parameter enforced in the code to prevent leakage on Gnutella) */
			$client_name = 'MUTE';
			$url = 'http://mute-net.sourceforge.net/';
			break;
		case 'MXIE':
			$client_name = 'mxie';
			break;
		case 'NAPS':
			$client_name = 'NapShare';
			$url = 'http://napshare.sourceforge.net/';
			break;
		case 'NOVA':
			$client_name = 'Nova';
			$url = 'http://novap2p.sourceforge.net/';
			break;
		case 'OCFG':
			$client_name = 'OpenCola';
			break;
		case 'OPRA':
			$client_name = 'Opera';
			$url = 'http://www.opera.com/';
			break;
		case 'PEER':
			$client_name = 'PeerProject';
			$url = 'http://peerproject.org/';
			break;
		case 'PHEX':
			$client_name = 'Phex';  /* Phex 3.4.2.116 => User-Agent: Jakarta Commons-HttpClient/3.0.1 */
			$url = 'http://www.phex.org/mambo/';
			break;
		case 'QAZA':
			$client_name = 'Quazaa';
			$url = 'http://quazaa.sourceforge.net/';
			break;
		case 'QAZB':
			$client_name = 'Quazaa Beta';
			$url = 'http://quazaa.sourceforge.net/';
			break;
		case 'QTEL':
			$client_name = 'Qtella';
			$url = 'http://qtella.sourceforge.net/';
			break;
		case 'RAZA':  /* Shareaza */
			$client_name = 'Shareaza';
			$url = 'http://shareaza.sourceforge.net/';
			break;
		case 'RAZB':  /* Shareaza - old beta versions */
			$client_name = 'ShareazaBeta';
			$url = 'http://shareaza.sourceforge.net/?id=debug';
			break;
		case 'RAZL':
			$client_name = 'ShareazaLite';
			$url = 'http://sourceforge.net/projects/flox/';
			break;
		case 'RZCA':
			$client_name = 'ShareazaPlus Alpha';
			$url = 'http://shareazaplus.sourceforge.net/';
			break;
		case 'RZCB':
			$client_name = 'ShareazaPlus Beta';
			$url = 'http://shareazaplus.sourceforge.net/';
			break;
		case 'RZCC':
			$client_name = 'ShareazaPlus';
			$url = 'http://shareazaplus.sourceforge.net/';
			break;
		case 'SALM':
			$client_name = 'Salmonella';
			break;
		case 'SEER':
			$client_name = 'Client by Jim Lee (unknown name)';
			break;
		case 'SHLN':
			$client_name = 'Sharelin';
			$url = 'http://sharelin.sourceforge.net/';
			break;
		case 'SNOW':
			$client_name = 'FrostWire';
			$url = 'http://www.frostwire.com/';
			break;
		case 'SNUT':
			$client_name = 'SwapNut';
			break;
		case 'SWAP':
			$client_name = 'Swapper';
			$url = 'http://www.revolutionarystuff.com/swapper/';
			break;
		case 'SWFT':
			$client_name = 'SwiftPeer';
			break;
		case 'TAMU':  /* Vendor code used for research from Texas A&M University */
			$client_name = 'Texas A&M University';
			$url = 'http://irl.cs.tamu.edu/';
			/* http://irl.cs.tamu.edu/courses/2010-spring2/463-500/1-19-10.pdf */
			break;
		case 'TFLS':
			$client_name = 'TrustyFiles';
			$url = 'http://www.trustyfiles.com/';
			break;
		/*case 'TGWC':
			$client_name = '';
			break;*/
		case 'TOAD':
			$client_name = 'ToadNode';
			break;
		case 'WSHR':
			$client_name = 'WireShare';
			$url = 'https://sourceforge.net/projects/wireshare/';
			break;
		case 'XOLO':
			$client_name = 'XoloX';
			break;
		case 'XTLA':
			$client_name = 'Xtella';
			$url = 'http://xtella.sourceforge.net/';
			break;
		case 'ZIGA':
			$client_name = 'Ziga';
			$url = 'http://sourceforge.net/projects/ziga/';
			break;

		/* Custom vendor codes (they are set inside the code to differentiate multiple clients with the same vendor code) */
		case 'ANTS':  /* ANts P2P - the original vendor code is ANtsP2P */
			$client_name = 'ANts P2P';
			$url = 'http://antsp2p.sourceforge.net/';
			break;
		case 'CABO':  /* Cabos (client of gnutella network) - the original vendor code is LIME */
			$client_name = 'Cabos'; $ua_prefix = 'LimeWire MOD - ';
			$url = 'https://sourceforge.net/projects/cabos/';
			/* http://cabos.osdn.jp/ */
			break;
		case 'KOMM':  /* Kommute (client of MUTE network) - the original vendor code is MUTE */
			$client_name = 'Kommute';
			$url = 'http://calypso.sourceforge.net/';
			break;
		case 'LIMM':  /* Generic vendor (client of gnutella network) - the original vendor code is LIME */
			$client_name = 'LimeWire MOD'; $ua_prefix = 'LimeWire MOD - ';
			break;
		case 'MMFC':  /* MUTE MFC (client of MUTE network) - the original vendor code is MUTE */
			$client_name = 'MUTE MFC';
			$url = 'https://sourceforge.net/projects/mfc-mute-net/';
			break;
		case 'MTLL':  /* Mutella (client of gnutella network) - the original vendor code is MUTE but it is changed to MTLL in the code to avoid confusion with MUTE (client of MUTE network) */
			$client_name = 'Mutella';
			$url = 'http://mutella.sourceforge.net/';
			break;
		case 'MUTG':  /* Generic vendor (client of MUTE network) - the original vendor code is MUTE */
			$client_name = 'Generic MUTE client';
			break;
		case 'RAZM':  /* Generic vendor (client of gnutella/gnutella2 network) - the original vendor code is RAZA */
			$client_name = 'Shareaza MOD'; $ua_prefix = 'Shareaza MOD - ';
			break;

		/* Crawlers */
		case 'DOXU':
			$client_name = 'G2 Crawler';
			$url = 'http://crawler.doxu.org/';
			$IS_crawler = true;
			/* Example query => ?client=DOXU1.2&get=1&ping=1&net=gnutella2 */
			break;
		case 'GWCSCANNER':
			$client_name = 'Multi-Network GWC Scan';
			$url = 'http://gcachescan.grantgalitz.com/';
			$IS_crawler = true;
			break;
		case 'PGDBScan':
			$client_name = 'Jon Atkins GWC scan';
			$url = 'http://gcachescan.jonatkins.com/';
			$IS_crawler = true;
			break;
		case 'WURM':
			$client_name = 'Wurm Scanner';
			$url = 'http://kevogod.trillinux.org/';
			$IS_crawler = true;
			break;

		/* GWCs */
		case 'BAZK':
		case 'Bazooka':
			$client_name = 'Bazooka';
			//$url = 'http://www.bazookanetworks.com/';
			$IS_GWC = 2;
			break;
		case 'BCII':
			$client_name = 'Beacon Cache II';
			$url = 'https://sourceforge.net/projects/beaconcache/';
			$IS_GWC = 2;
			break;
		case 'BCON':
			$client_name = 'Beacon Cache';
			$url = 'https://sourceforge.net/projects/beaconcache/';
			$IS_GWC = 2;
			break;
		case 'Boa':
			$client_name = 'Boa';
			$url = 'https://github.com/kevogod/Boa';
			$IS_GWC = 2;
			break;
		case 'Cachechu':
			$client_name = 'Cachechu';
			$url = 'https://github.com/kevogod/cachechu';
			$IS_GWC = 2;
			break;
		case 'CANN':
			$client_name = 'Cannon';
			$IS_GWC = 2;
			break;
		case 'CHTC':
			$client_name = 'CheaterCache';
			$IS_GWC = 2;
			break;
		case 'Crab':
			$client_name = 'GhostWhiteCrab';
			$url = 'http://github.com/gtk-gnutella/gwc';
			/* http://sourceforge.net/projects/frostwire/files/GhostWhiteCrab/ */
			$IS_GWC = 2;
			break;
		case 'DKAC';
			$client_name = 'DKAC/Enticing-Enumon';
			$url = 'http://dkac.trillinux.org/dkac/dkac.php';
			$IS_GWC = 2;
			break;
		case 'GCII':
			$client_name = 'PHPGnuCacheII';
			$url = 'http://gwcii.sourceforge.net/';
			$IS_GWC = 2;
			/* Example query => ?client=GCII&version=2.1.1&ping=1&net=gnutella2 */
			break;
		case 'GUAR':
			$client_name = 'Guarana';
			$url = 'http://github.com/leite/guarana';
			$IS_GWC = 2;
			/* Example query => ?client=GUAR&version=GUAR+0.3&getnetworks=1&cache=1&net=gnutella2&ping=1&get=1&hostfile=1&urlfile=1 */
			break;
		case 'GWebCache':  /* Original GWebCache */
			$client_name = 'GWebCache';
			$url = 'http://gnucleus.sourceforge.net/gwebcache/';
			$IS_GWC = 2;
			break;
		case 'JGWC':
			$client_name = 'jumswebcache';
			$url = 'http://www1.mager.org/GWebCache/';
			/* http://github.com/jum/GWebCache */
			$IS_GWC = 2;
			break;
		case 'MWCP':
		case 'MWebCache':
			$client_name = 'MWebCache';
			$url = 'http://sourceforge.net/p/mute-net/support-requests/7/';
			/* http://mute-net.sourceforge.net/mWebCache.shtml */
			$IS_GWC = 2;
			break;
		case 'NGWC':
			$client_name = 'node.gwc';
			$url = 'http://andrewgilmore.co.uk/project/nodegwc';
			/* http://github.com/agilmore/node.gwc */
			$IS_GWC = 2;
			/* Example query => ?ping=1&multi=1&client=NGWC&version=0.1&cache=1&net=gnutella2 */
			break;
		case 'SKLL':
			$client_name = 'Skulls';
			$url = 'http://skulls.sourceforge.net/';
			$IS_GWC = 2;
			break;

		/* Special cases */
		case 'Submit':
			if($IS_GWC > 1 || $IS_crawler) $client_name = 'Unknown submission';
			else{ $client_name = 'Manual submission'; $IS_GWC = 0; }
			break;

		default:
			if($IS_crawler)
				$full_name = 'Unknown crawler';
			elseif($IS_GWC)
				$full_name = 'Unknown GWC';
			else
				$full_name = 'Unknown client';

			if($vendor !== "") $full_name .= ' ('.$vendor.' '.$version.')';
			if($ua !== 'KickStart') $ua = 'User-Agent: '.$ua;

			return '<span title="'.$ua.'">'.$full_name.'</span>';
	}

	$full_name = $client_name; if($version !== "") $full_name .= ' '.$version;
	if($IS_GWC) $full_name .= ' (GWC)';
	if($ua !== 'KickStart') $ua = $ua_prefix.'User-Agent: '.$ua;

	if($url !== null) return '<a href="'.$url.'" rel="external nofollow" title="'.$ua.'">'.$full_name.'</a>';
	return '<span title="'.$ua.'">'.$full_name.'</span>';
}

function BinaryToBase32($input)
{
	static $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';  /* RFC 4648 Base32 alphabet */

	$out = ""; $in_len = strlen($input); $i = -1; $buf = 0; $buf_len = 0;
	while(++$i < $in_len)
	{
		$buf <<= 8; $buf |= ord($input[$i]); $buf_len += 8;
		while($buf_len >= 5)
		{
			$buf_len -= 5;
			$out .= $alphabet[$buf >> $buf_len];
			$buf &= ((1 << $buf_len) - 1);
		}
	}
	if($buf_len) $out .= $alphabet[$buf << (5 - $buf_len)];  /* Pad the remaining bits to a 5-bit chunk */
	return $out;
}

function CalculateSHA1($filename)
{
	$hash = sha1_file($filename); if($hash === false) return false;
	return strtoupper($hash);
}

function CalculateSHA1Base32($filename)
{
	if((int)PHP_VERSION >= 5)
		{ $hash = sha1_file($filename, true); if($hash === false) return false; }
	else
		{ $hash = sha1_file($filename); if($hash === false) return false; $hash = pack('H*', $hash); }
	return BinaryToBase32($hash);
}

function CheckHashAndFilesize($filename, &$BL_file_size)
{
	$BL_file_size = filesize($filename); if($BL_file_size === false) return false;
	$hash = CalculateSHA1($filename); if($hash === false) return false;
	$title = '"SHA1 = '.$hash.'"';

	$BL_stored_info = @file_get_contents(substr($filename, 0, -3).'hash');
	if($BL_stored_info === false) return '<span class="bad" title='.$title.'>Missing hash file</span>';
	$BL_stored_info = explode('|', $BL_stored_info, 3);

	if($hash === $BL_stored_info[0] && $BL_file_size === (int)$BL_stored_info[1])
		return '<span class="good" title='.$title.'>OK</span>';
	else
		return '<span class="bad" title='.$title.'>Corrupted</span>';
}

function GetBlockListInfo($filename, $unique_id, &$BL_type, &$BL_hash_check, &$BL_file_size, &$BL_author, &$BL_rev, &$BL_license)
{
	$BL_type = null; $BL_hash_check = null; $BL_file_size = 0; $BL_author = null; $BL_rev = null; $BL_license = null;
	if(!file_exists($filename)) { $BL_type = '<span class="bad">Missing file</span>'; return false; }

	$fp = fopen($filename, 'rb'); if($fp === false) return false;
	$line = fgets($fp, 512); if($line !== false) $BL_info = explode('|', $line, 6);
	fclose($fp);
	if(!isset($BL_info)) return false;

	$BL_rev = $BL_info[1].' ('.$BL_info[2].')'; $BL_author = $BL_info[3]; $BL_license = rtrim($BL_info[4]);

	if($BL_info[0] === '0')				/* Custom BlockList withOUT hash check */
	{
		$BL_type = '<span class="unknown">Custom - No hash check</span>';
		$BL_hash_check = '<span class="unknown">Disabled</span>';
	}
	else
	{
		if($BL_info[0] === $unique_id)	/* Original BlockList with hash check */
			$BL_type = '<span class="good">Original</span>';
		else							/* Custom BlockList with hash check */
			$BL_type = '<span class="unknown">Custom</span>';
		$result = CheckHashAndFilesize($filename, $BL_file_size); if($result !== false) $BL_hash_check = $result;
	}
	return true;
}

function BLHandleConversionFailure($fp_out, $fp_info)
{
	if($fp_out !== false) { flock($fp_out, LOCK_UN); fclose($fp_out); }
	if($fp_info !== false) { flock($fp_info, LOCK_UN); fclose($fp_info); }
	ignore_user_abort(false);
	return false;
}

function BLConvertToCidr($fp_in, $b_id, $b_rev, $b_author)
{
	ignore_user_abort(true); $b_out_fn = DATA_DIR.'dl/blocklist-cidr';

	$fp_info = fopen($b_out_fn.'.info', 'wb'); $fp_out = fopen($b_out_fn.'.dat', 'wb');
	set_time_limit(60);  /* Get an exclusive lock may take awhile */
	if($fp_info === false || !flock($fp_info, LOCK_EX) || $fp_out === false || !flock($fp_out, LOCK_EX))
		return BLHandleConversionFailure($fp_out, $fp_info);

	$success = true; $bytes_read = 0; set_time_limit(30);
	while(!feof($fp_in))
	{
		$buf = fread($fp_in, 16384); if($buf === false) break;
		$buf_len = strlen($buf); $bytes_read += $buf_len;
		if(fwrite($fp_out, $buf, $buf_len) !== $buf_len) { $success = false; break; }
	}
	fflush($fp_out); flock($fp_out, LOCK_UN); fclose($fp_out); $fp_out = false; touch($b_out_fn.'.dat');

	clearstatcache();
	if(!$success || filesize($b_out_fn.'.dat') !== $bytes_read)
		return BLHandleConversionFailure($fp_out, $fp_info);

	fwrite($fp_info, $b_id.'|'.$b_rev.'|'.$b_author.'|'.CalculateSHA1Base32($b_out_fn.'.dat').'|'./* Tiger tree */'|'./* Reserved */'|'.$bytes_read.'|');
	fflush($fp_info); flock($fp_info, LOCK_UN); fclose($fp_info);

	ignore_user_abort(false);
	return true;
}

function BLGetInfo($b_format)
{
	$b_fn = DATA_DIR.'dl/blocklist-'.$b_format;
	if(!file_exists($b_fn.'.info') || ($b_info = file_get_contents($b_fn.'.info')) === false || empty($b_info)) return false;
	if(!file_exists($b_fn.'.dat')) return false;
	return $b_info;
}

function BLRevCheck($b_format)
{
	$b_in_fn = './ext/blocklist.dat';
	if(!file_exists($b_in_fn) || ($fp_in = fopen($b_in_fn, 'rb')) === false) return false;  /* Base blocklist missing or inaccessible */
	if(!flock($fp_in, LOCK_SH)) { fclose($fp_in); return false; }
	$b_in_info = fgets($fp_in, 512); if($b_in_info === false) { flock($fp_in, LOCK_UN); fclose($fp_in); return false; }
	$b_out_info = BLGetInfo($b_format);

	$success = true; $conversion_needed = true;
	list($b_id, $b_rev, /*$b_date*/, $b_author, /*$b_license*/) = explode('|', $b_in_info, 5);
	if($b_out_info !== false)
	{
		list($b_out_id, $b_out_rev, $b_out_author) = explode('|', $b_out_info, 4);
		if($b_id === $b_out_id && $b_rev === $b_out_rev && $b_author === $b_out_author) $conversion_needed = false;
	}

	if($conversion_needed)
	{
		if($b_format === 'cidr')
			$success = BLConvertToCidr($fp_in, $b_id, $b_rev, $b_author);
		else
			$success = false;
	}
	flock($fp_in, LOCK_UN); fclose($fp_in);

	return $success;
}

function BLGenerateMagnet($b_format)
{
	$b_info = BLGetInfo($b_format); if($b_info === false) return "";
	list($b_id, $b_rev, $b_author, $b_sha1, $b_tiger_tree, /* Reserved */, $b_size) = explode('|', $b_info, 8); if($b_author === 'Lord of the Rings') $b_author = 'LOTR';
	if(!ctype_digit($b_rev) || !ctype_digit($b_size)) return "";

	global $MY_URL;
	$magnet = 'magnet:?xt=urn:sha1:'.$b_sha1;
	if($b_tiger_tree !== "") $magnet .= '&xt=urn:tree:tiger:'.$b_tiger_tree;
	$magnet .= '&dn='.rawurlencode('P2P-BlockList-'.$b_format.'-'.$b_author.'-'.$b_rev.'.dat');
	$magnet .= '&xl='.$b_size;
	$magnet .= '&xs='.rawurlencode('http://cache.freebase.be/'.$b_sha1);
	$magnet .= '&as='.rawurlencode(dirname($MY_URL).'/dl.php?format='.$b_format.'&hash='.$b_sha1.'&size='.$b_size);
	$magnet .= '&blocklist-uid='.rawurlencode($b_id);

	return $magnet;
}

function QueryUpdateServer($url = 'http://skulls.sourceforge.net/latest_ver.php', $came_from = null)
{
	global $MY_URL;

	list( , $url ) = explode("://", $url, 2);		// It remove "http://" from $url - $url = www.test.com:80/page.php
	$main_url = explode("/", $url);					// $main_url[0] = www.test.com:80		$main_url[1] = page.php
	$splitted_url = explode(":", $main_url[0], 2);	// $splitted_url[0] = www.test.com		$splitted_url[1] = 80

	if( count($splitted_url) > 1 )
		list($hostname, $port) = $splitted_url;
	else
	{
		$hostname = $main_url[0];
		$port = 80;
	}

	$errno = -1; $errstr = "";
	$fp = @fsockopen($hostname, $port, $errno, $errstr, (FSOCK_BASE? 10 : 5));
	$status = NULL;
	$msg = NULL;
	$msg_error = NULL;
	$msg_info = NULL;
	if(DEBUG) echo "---------------<br>";

	if($fp === false)
	{
		$status = 'CONN-ERR';
		$msg = 'Connection error '.$errno; if(!empty($errstr)) $msg .= ' ('.rtrim($errstr).')';
	}
	else
	{
		$query = 'update_check=1&url='.rawurlencode($MY_URL).'&client='.VENDOR.'&version='.SHORT_VER.'&cache=1';

		if(fwrite($fp, "GET ".substr( $url, strlen($main_url[0]), (strlen($url) - strlen($main_url[0]) ) )."?".$query." ".$_SERVER['SERVER_PROTOCOL']."\r\nHost: ".$hostname."\r\nUser-Agent: ".NAME." ".VER."\r\nConnection: close\r\n\r\n") === false)
		{
			$status = 'REQUEST_ERROR';
			$msg = 'Request failed';
		}
		else
		{
			while ( !feof($fp) )
			{
				$line = rtrim( fgets( $fp, 1024 ) );
				if(DEBUG) echo $line.'<br>';

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
					elseif(DEBUG) echo '<div class="bad"><strong>Loop</strong></div>';
				}
				elseif( strtolower( substr( $line, 0, 7 ) ) == "i|info|" )
				{
					$received_data = explode("|", $line);
					$msg_info .= '<div>'.$received_data[2].'</div>';
				}
				elseif( strtolower( substr( $line, 0, 8 ) ) == "i|error|" )
				{
					$received_data = explode("|", $line);
					$msg_error .= '<div>'.$received_data[2].'</div>';
				}
				elseif(strpos($line, "404 Not Found") !== false)
				{
					$status = "404";
					$msg = $line;
				}
				elseif(strpos($line, "403 Forbidden") !== false)
				{
					$status = "403";
					$msg = $line;
				}
				
			}
		}

		fclose ($fp);
	}

	if(DEBUG) echo '<div>Status: '.RemoveGarbage($status).'</div><div>---------------</div>';
	return RemoveGarbage($status)."|".RemoveGarbage($msg)."||".RemoveGarbage($msg_info)."|".RemoveGarbage($msg_error);
}

function CheckUpdates()
{
	if(!file_exists(DATA_DIR.'update_check.dat'))
	{
		$file = @fopen(DATA_DIR.'update_check.dat', 'xb');
		if($file !== FALSE) fclose($file);
		else
		{
			echo "<font color=\"red\"><b>Error during writing of ".DATA_DIR."update_check.dat</b></font><br>";
			echo "<b>You must create the file manually, and give to the file the correct permissions.</b><br><br>";
			die();
		}
	}

	$file = @file(DATA_DIR.'update_check.dat');
	if($file === FALSE)
	{
		echo "<font color=\"red\"><b>Error during reading of ".DATA_DIR."update_check.dat</b></font><br>";
		echo "<b>You must give to the file the correct permissions.</b><br><br>";
		die();
	}

	$cached = FALSE;
	if(count($file))
	{
		$USER_AGENT = empty($_SERVER['HTTP_USER_AGENT']) ? "" : $_SERVER['HTTP_USER_AGENT'];
		list($status, $msg, $latest_check, $msg_info, $msg_error) = explode("|", $file[0]);

		/* Use only cached update check for not-human requests */
		if(strpos($USER_AGENT, 'RED/') === 0 || strpos($USER_AGENT, 'Googlebot/') !== false
		  || strpos($USER_AGENT, 'Google Page Speed') !== false || strpos($USER_AGENT, 'GTmetrix') !== false)
			$cached = true;
		else
		{
			$time_diff = time() - ( strtotime( $latest_check ) + date("Z") );	// GMT
			$time_diff = floor($time_diff / 86400);								// Days

			if($time_diff < 1)
				$cached = TRUE;
			elseif($status === 'OK' && $time_diff < 3)
				$cached = TRUE;
		}
	}

	if(!$cached)
	{
		if($_SERVER['SERVER_ADDR'] === '127.0.0.1' || $_SERVER['SERVER_ADDR'] === '::1')
		{
			echo '<div class="unknown"><strong>Update check not allowed from localhost</strong></div>';
			return null;
		}

		$returned_data = QueryUpdateServer();
		$returned_data = explode("|", $returned_data);
		$status = $returned_data[0];
		$msg = $returned_data[1];
		$msg_info = $returned_data[3];
		$msg_error = $returned_data[4];
	}

	if($status === 'CONN-ERR' || $status === '403')
	{
		echo '<div class="bold">Update check process: ';
		if(FSOCK_BASE)
			echo '<span class="bad"><strong>',$msg,'</strong></span>';
		else
			echo '<span class="unknown">Unable to check</span>';
		echo '</div>',"\n";
	}
	elseif($status === 'REQUEST_ERROR')
	{
		echo '<div class="bold">Update check process: <span class="bad"><strong>'.$msg.'</strong></span></div>',"\n";
	}
	elseif($status === 'OK')
	{
		if(DEBUG) echo '<div class="bold">Update check process: <span class="good">OK</span></div>',"\n";
		if($msg_error !== "") echo '<div class="bold"><div class="bad">ERRORS: <strong>'.$msg_error.'</strong></div></div>',"\n";
	}
	elseif($status === '404')
		echo '<div class="bold">Update check process: <span class="bad"><strong>Invalid query or file deleted</strong></span></div>',"\n";
	else
	{
		echo '<div class="bold">Update check process: <span class="bad"><strong>Server response incorrect, maybe there are problems in the update server</strong></span></div>',"\n";
		$status = 'INCORRECT';
	}

	if(!$cached)
	{
		$file = @fopen(DATA_DIR.'update_check.dat', 'wb');
		if($file !== FALSE)
		{
			flock($file, LOCK_EX);
			fwrite($file, $status."|".$msg."|".gmdate("Y/m/d H:i")."|".$msg_info."|".$msg_error);
			flock($file, LOCK_UN);
			fclose($file);
		}
		else
		{
			echo "<font color=\"red\"><b>Error during writing of ".DATA_DIR."update_check.dat</b></font><br>";
			echo "<b>You must create the file manually, and give to the file the correct permissions.</b><br><br>";
		}
	}

	$result["status"] = $status;
	$result["latest_ver"] = $msg;
	$result["update_info"] = $msg_info;
	return $result;
}

function ShowUpdateCheck()
{
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

		if($need_update) $class = "bad"; else $class = "good";
		echo '<div class="bold">Latest version: <span class="good">',$result['latest_ver'],'</span></div>';
		echo '<div class="bold">This version: <span class="',$class,'">',SHORT_VER,'</span></div>',"\n";

		if($need_update)
		{
			if($result['update_info'] != "") echo '<div>',$result["update_info"],'</div>',"\n";
			echo '<br><div><span class="',$class,'"><b>There is a new version of ',NAME,', ';
			echo 'please visit the official site of <a href="',GWC_SITE,'" class="hover-underline" rel="external">',NAME,'</a> to obtain the latest version.</b></span></div>',"\n";
		}
	}
}
?>