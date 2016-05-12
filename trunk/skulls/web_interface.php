<?php
//  web_interface.php
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

include "web_functions.php";

function ShowHtmlPage($compression, $header, $footer)
{
	global $NET, $SUPPORTED_NETWORKS; $script_name = $_SERVER['SCRIPT_NAME'];
	header('Content-Type: text/html; charset=utf-8');

	$page_number = 1; $suffix = null;
	if($NET === null) $NET = 'all';

	if(!empty($_GET['showinfo']));
	elseif(!empty($_GET['showhosts'])) { $page_number = 2; $suffix = ' - Hosts'; }
	elseif(!empty($_GET['showurls'])) { $page_number = 3; $suffix = ' - GWCs'; }
	elseif(!empty($_GET['showblocklists'])) { $page_number = 4; if($_GET['showblocklists'] > 1) $page_number = 5; $suffix = ' - BlockLists'; }
	elseif(!empty($_GET['stats']) || !empty($_GET['data'])) { $page_number = 6; $suffix = ' - Stats'; }

	if($page_number === 1 || $page_number === 5)
		header('Cache-Control: max-age=43200');  /* 12 hours */
	else
		header('Cache-Control: max-age=60');     /* 1 minute */

	$maintainer = htmlentities(MAINTAINER_NICK, ENT_QUOTES, 'UTF-8');
	$title = NAME.'! Multi-Network WebCache '.VER.$suffix.' (by '.$maintainer.')';
	$base_link = basename($script_name).'?'; if($compression !== null) $base_link .= 'compression='.$compression.'&amp;';

	if(!function_exists("Initialize"))
		include "functions.php";

	Initialize($SUPPORTED_NETWORKS, TRUE, TRUE);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $title; ?></title>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="includes/style.css">
<!--[if lte IE 9]><link rel="stylesheet" type="text/css" href="includes/style-ie.css"><![endif]-->
<?php if($page_number === 1 && !empty($_SERVER['QUERY_STRING'])) echo '<link rel="canonical" href="',$script_name,'">',"\n"; ?>
<meta name="robots" content="<?php if($page_number === 1) echo 'index'; else echo 'noindex'; ?>, follow, noarchive, noimageindex">
<meta name="description" content="<?php echo NAME; ?> is a Multi-Network WebCache used from P2P clients to bootstrap. It support all versions of the GWC specification.">
<meta name="keywords" content="<?php echo strtolower(NAME); ?>, gwebcache, gwc, p2p, bootstrap">
</head>

<body>
	<div class="header">
		<div id="accessible-links"><a href="#content">[Skip to content]</a></div>
<?php
		if($header !== "") echo "\t\t",'<div class="center">',EncodeAmpersand($header),'</div> <div class="spacer"></div>',"\n";
?>
	</div>

	<div class="center">
		<div class="container">
			<div class="header">
				<h1 class="title-spacing"><span class="main-title"><?php echo NAME; ?>!</span> Multi-Network WebCache <?php echo VER; ?></h1>
				<div id="page-list"><a href="<?php echo $base_link; ?>showinfo=1">Home</a> / <a href="<?php echo $base_link; ?>showhosts=1">Hosts</a> / <a href="<?php echo $base_link; ?>showurls=1">Alternative GWCs</a> / <a href="<?php echo $base_link; ?>showblocklists=1">BlockLists</a> / <a href="<?php echo $base_link; ?>stats=1">Statistics</a></div>
			</div>
			<div id="content">
<?php
			if($page_number == 1)	// Info
			{
				$idna_support = (function_exists('idn_to_ascii'));
?>
				<div class="page-title" title="Informations about this GWebCache"><strong>Cache Info</strong></div>
				<div class="padding">
					<table class="inner-table-infos">
						<tr>
							<th>- Running since:</th>
							<td>
<?php
								if(file_exists(DATA_DIR.'running-since.dat'))
								{
									$running_since = file(DATA_DIR.'running-since.dat');
									echo $running_since[0],' UTC',"\n";
								}
?>
							</td>
						</tr>
						<tr>
							<th>- Version:</th>
							<td><span class="green" title="<?php echo GetMainFileRev(); ?>"><span class="bold"><?php echo VER; ?></span></span></td>
						</tr>
						<tr>
							<th>- Networks:</th>
							<td>
<?php
								global $SUPPORTED_NETWORKS;
								for( $i = 0; $i < NETWORKS_COUNT; $i++ )
								{
									echo $SUPPORTED_NETWORKS[$i];
									if( $i < NETWORKS_COUNT - 1 )
										echo ", ";
								}
								echo "\n";
?>
							</td>
						</tr>
						<tr>
							<th>- IDNA support:</th>
							<td>
								<span class="<?php echo ($idna_support? 'good' : 'bad'); ?>">
									<span class="bold"><?php echo ($idna_support? 'Yes' : 'No'); ?></span>
								</span>
							</td>
						</tr>
						<tr>
							<th>- License:</th>
							<td>
								<span class="bold"><a href="<?php echo LICENSE_URL; ?>" rel="external"><?php echo LICENSE_NAME.' v'.LICENSE_VER; ?></a></span>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>&nbsp;</td>
						</tr>
<?php include './geoip/geoip.php'; $geoip = new GeoIPWrapper; ?>
						<tr>
							<th>- GeoIP type:</th>
							<td><span class="bold"><?php if($geoip) echo $geoip->GetType(); ?></span></td>
						</tr>
<?php
						if($geoip && $geoip->IsEnabled())
						{
?>
							<tr>
								<th>- GeoIP DB version:</th>
								<td><span class="green"><span class="bold"><?php echo $geoip->GetDBVersion(); ?></span></span></td>
							</tr>
							<tr>
								<th>- GeoIP DB (c)opy:</th>
								<td><?php echo $geoip->GetDBCopyright(); ?></td>
							</tr>
<?php
						}
						if($geoip) $geoip->Destroy(); $geoip = null;

						$mail = null;
						if(MAINTAINER_EMAIL !== 'name AT server DOT com' && MAINTAINER_EMAIL !== "")
							$mail = ' title="'.htmlentities(str_replace(array('@', '.'), array(' AT ', ' DOT '), MAINTAINER_EMAIL), ENT_QUOTES, 'UTF-8').'"';
?>
						<tr>
							<td></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th>- Maintainer:</th>
							<td><span class="blue"<?php echo $mail; ?>><span class="bold"><?php echo $maintainer; ?></span></span></td>
						</tr>
<?php
						if(MAINTAINER_WEBSITE !== 'http://' && MAINTAINER_WEBSITE !== "")
						{
?>
							<tr>
								<th>- Maintainer site:</th>
								<td>
									<span class="blue">
<?php
									$website = htmlentities(MAINTAINER_WEBSITE, ENT_QUOTES, 'UTF-8');
									echo '<a href="',$website,'" class="hover-underline" rel="external">',$website,'</a>',"\n";
?>
									</span>
								</td>
							</tr>
<?php
						}
?>
					</table>
				</div>
<?php
			}
			elseif($page_number == 2)	// Hosts
			{
				$max_hosts = MAX_HOSTS;
				$elements = 0;

				if( $NET == "all" )
				{
					global $SUPPORTED_NETWORKS;
					$max_hosts *= NETWORKS_COUNT;

					for($x = NETWORKS_COUNT - 1; $x >= 0; $x--)
					{
						$temp = file(DATA_DIR.'hosts-'.strtolower($SUPPORTED_NETWORKS[$x]).'.dat');
						$n_temp = count($temp);
						for($y = 0; $y < $n_temp; $y++)
						{
							$host_file["host"][$elements] = $temp[$y];
							$host_file["net"][$elements] = $SUPPORTED_NETWORKS[$x];
							$elements++;
						}
						$temp = null;
					}
				}
				elseif(file_exists(DATA_DIR.'hosts-'.$NET.'.dat'))
				{
					$host_file["host"] = file(DATA_DIR.'hosts-'.$NET.'.dat');
					$net = ucfirst($NET);
					$elements = count($host_file["host"]);
				}
?>
				<div class="page-title"><strong><?php echo htmlentities(ucfirst($NET), ENT_QUOTES, 'UTF-8'); ?> Hosts (<?php echo $elements." of ".$max_hosts; ?>)</strong></div>
				<div class="padding">
					<table class="inner-table" summary="Current hosts in cache">
						<tr class="header-column">
							<th>Host address (Leaves)</th>
							<th>Client</th>
							<th>Network</th>
							<th>Last updated</th>
						</tr>
<?php
						if( $elements === 0 )
							echo '<tr><td class="empty-list" colspan="4">There are no <strong>hosts</strong> listed at this time.</td></tr>',"\n";
						else
						{
							include './geoip/geoip.php'; $geoip = new GeoIPWrapper;

							for( $i = $elements - 1; $i >= 0; $i-- )
							{
								list( $h_age, $h_ip, $h_port, $h_leaves, $h_max_leaves, , $h_vendor, $h_ver, $h_ua, /* $h_suspect */, ) = explode('|', $host_file['host'][$i], 13);
								if(isset($host_file['net'][$i])) $net = $host_file['net'][$i];
								$color = (($elements - $i) % 2 === 0 ? 'even' : 'odd');
								$host = $h_ip.':'.$h_port;
								if(strtolower($net) === 'gnutella2')
									$url = 'g2:host:';
								else
									$url = strtolower($net).':host:';

								echo '<tr class="',$color,'">';
								echo '<td>';
								$asn = null;
								if($geoip)
								{
									$country_name = $geoip->GetCountryNameByIP($h_ip);
									$country_code = $geoip->GetCountryCodeByIP($h_ip);
									echo '<img width="16" height="11" src="'.$geoip->GetCountryFlag($country_code).'" alt="'.$country_code.'" title="'.$country_name.'"> ';
									$asn = $geoip->GetASNByIP($h_ip);
								}
								echo '<a href="',$url,$host,'" rel="nofollow" title="'.$asn.'">',$host,'</a>';
								if($h_leaves !== "")
									echo ' (',$h_leaves,(empty($h_max_leaves)? null : '/'.$h_max_leaves),')';
								echo ' &nbsp;</td>';
								echo '<td><strong>',ReplaceVendorCode($h_vendor, $h_ver, $h_ua),'</strong> &nbsp;</td>';
								echo '<td><a href="',$base_link,'showhosts=1&amp;net=',strtolower($net),'">',$net,'</a> &nbsp;</td>';
								echo '<td>',$h_age,'</td></tr>',"\n";
							}

							if($geoip) $geoip->Destroy(); $geoip = null;
						}
?>
					</table>
				</div>
<?php
			}
			elseif($page_number == 3)	// GWCs
			{
				include './geoip/geoip.php'; $geoip = new GeoIPWrapper;

				$cache_file = file(DATA_DIR.'alt-gwcs.dat');
				$elements = count($cache_file);
?>
				<div class="page-title"><strong>Alternative GWCs (<?php echo $elements." of ".MAX_CACHES; ?>)</strong> &nbsp;&nbsp; <a id="Send-GWCs" href="#Send-GWCs" onclick="sendGWCs(event);" rel="nofollow">Add first 20 GWCs to your P2P application</a></div>
				<div class="padding">
					<table class="inner-table" summary="Current GWCs in cache">
						<tr class="header-column">
							<th>URL</th>
							<th>Name</th>
							<th>Networks</th>
							<th>Submitting client</th>
							<th>Last checked</th>
						</tr>
<?php
						if( $elements === 0 )
							echo '<tr><td class="empty-list" colspan="5">There are no <strong>alternative GWCs</strong> listed at this time.</td></tr>',"\n";
						else
						{
							for($i = $elements - 1; $i >= 0; $i--)
							{
								list($time, /* New specs only */, $gwc_ip, $cache_url, $net, /* Net parameter needed */, /*$gwc_vendor.*/, /* $gwc_version */, $cache_name, $gwc_server, $client, $version, $is_a_gwc_param, $user_agent,) = explode("|", $cache_file[$i], 15);
								$cache_name = htmlentities($cache_name, ENT_QUOTES, 'UTF-8'); $net_readable = null;
								if(strpos($net, '-') !== false)
								{
									$networks = explode('-', $net);
									$cache_nets_count = count($networks);
									for( $x=0; $x < $cache_nets_count; $x++ )
									{
										if($x) $net_readable .= ', ';
										$net_readable .= ucfirst($networks[$x]);
									}
								}
								else
									$net_readable = ucfirst($net);
								$color = (($elements - $i) % 2 === 0 ? 'even' : 'odd');

								$output = '<tr class="'.$color.'">';
								$output .= '<td>';

								$prefix = 'gwc:';
								$output .= '<a class="gwc" href="'.$prefix.$cache_url.'?nets='.$net.'" rel="nofollow">+</a> ';

								if($geoip)
								{
									$country_name = $geoip->GetCountryNameByIP($gwc_ip);
									$country_code = $geoip->GetCountryCodeByIP($gwc_ip);
									$output .= '<img width="16" height="11" src="'.$geoip->GetCountryFlag($country_code).'" alt="'.$country_code.'" title="'.$country_name.'"> ';
								}
								$output .= '<span'.(strpos($cache_url, 'https:') === 0? ' class="https"' : "").'><a class="url" href="'.$cache_url.'" rel="external" title="'.$gwc_ip.'">';

								list(,$cache_url) = explode("://", $cache_url);
								$max_length = 40;

								$pos = strpos($cache_url, "/");
								if($pos > 0)
									$cache_url = substr($cache_url, 0, $pos);

								if(strlen($cache_url) > $max_length)
									$output .= substr($cache_url, 0, $max_length)."...";
								else
									$output .= $cache_url;

								$output .= '</a></span> &nbsp;</td><td><span title="'.$gwc_server.'">';
								if(strpos($cache_name, 'Sk'.'ulls') === 0)
									$output .= '<a class="gwc-home-link" href="http://sk'.'ulls.sourceforge.net/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, NAME) === 0)
									$output .= '<a class="gwc-home-link" href="'.GWC_SITE.'" rel="external nofollow">'.$cache_name.'</a>';
								//elseif(strpos($cache_name, 'Bazooka') === 0)
									//$output .= '<a class="gwc-home-link" href="" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'Beacon Cache') === 0)  /* Beacon Cache and Beacon Cache II */
									$output .= '<a class="gwc-home-link" href="https://sourceforge.net/projects/beaconcache/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'Cachechu') === 0)
									$output .= '<a class="gwc-home-link" href="https://github.com/kevogod/cachechu" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'GhostWhiteCrab') === 0)
									$output .= '<a class="gwc-home-link" href="https://github.com/gtk-gnutella/gwc" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'PHPGnuCacheII') === 0)
									$output .= '<a class="gwc-home-link" href="http://gwcii.sourceforge.net/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'jumswebcache') === 0)
									$output .= '<a class="gwc-home-link" href="http://www1.mager.org/GWebCache/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'MWebCache') === 0)
									$output .= '<a class="gwc-home-link" href="https://sourceforge.net/p/mute-net/support-requests/7/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'node.gwc') === 0)
									$output .= '<a class="gwc-home-link" href="http://andrewgilmore.co.uk/project/nodegwc" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'GWebCache') === 0)
									$output .= '<a class="gwc-home-link" href="http://gnucleus.sourceforge.net/gwebcache/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'DKAC/Enticing-Enumon') === 0)
									$output .= '<a class="gwc-home-link" href="http://dkac.trillinux.org/dkac/dkac.php" rel="external nofollow">'.$cache_name.'</a>';
								else
									$output .= $cache_name;
								$output .= '</span> &nbsp;</td><td>'.$net_readable.' &nbsp;</td>';
								$output .= '<td><span class="bold">'.ReplaceVendorCode($client, $version, $user_agent, (int)$is_a_gwc_param).'</span> &nbsp;</td>';
								$output .= '<td>'.rtrim($time).'</td></tr>'."\n";

								echo $output;
							}
						}
?>
					</table>
				</div><div>&nbsp;</div>
<?php
				$cache_file = file(DATA_DIR.'alt-udps.dat');
				$elements = count($cache_file);
?>
				<div class="page-title"><strong>Alternative UDP host caches (<?php echo $elements." of ".MAX_CACHES; ?>)</strong></div>
				<div class="padding">
					<table class="inner-table" summary="Current UDP host caches in cache">
						<tr class="header-column">
							<th>URL</th>
							<th>Name</th>
							<th>Network</th>
							<th>Submitting client</th>
							<th>Last checked</th>
						</tr>
<?php
						if( $elements === 0 )
							echo '<tr><td class="empty-list" colspan="5">There are no <strong>alternative UDP host caches</strong> listed at this time.</td></tr>',"\n";
						else
						{
							for($i = $elements - 1; $i >= 0; $i--)
							{
								list($time, /* New specs only */, $gwc_ip, $cache_url, $net, /* Net parameter needed */, /*$gwc_vendor.*/, /* $gwc_version */, $cache_name, $gwc_server, $client, $version, $is_a_gwc_param, $user_agent,) = explode("|", $cache_file[$i], 15);
								$cache_name = htmlentities($cache_name, ENT_QUOTES, 'UTF-8');
								$color = (($elements - $i) % 2 === 0 ? 'even' : 'odd');

								$output = '<tr class="'.$color.'">';
								$output .= '<td>';

								if($net === 'gnutella')
									$prefix = 'uhc:';
								elseif($net === 'gnutella2')
									$prefix = 'ukhl:';
								else
									$prefix = $net.':'.'udphc:';
								$output .= '<a class="gwc" href="'.$prefix.$cache_url.'" rel="nofollow">+</a> ';

								if($geoip)
								{
									$country_name = $geoip->GetCountryNameByIP($gwc_ip);
									$country_code = $geoip->GetCountryCodeByIP($gwc_ip);
									$output .= '<img width="16" height="11" src="'.$geoip->GetCountryFlag($country_code).'" alt="'.$country_code.'" title="'.$country_name.'"> ';
								}
								$output .= '<span class="udp"><a class="url" href="http://'.$cache_url.'" rel="external" title="'.$gwc_ip.'">';

								$max_length = 40;
								$pos = strpos($cache_url, '/'); if($pos !== false) $cache_url = substr($cache_url, 0, $pos);
								if(strlen($cache_url) > $max_length)
									$output .= substr($cache_url, 0, $max_length).'...';
								else
									$output .= $cache_url;

								$output .= '</a></span> &nbsp;</td><td><span title="'.$gwc_server.'">'.$cache_name;
								$output .= '</span> &nbsp;</td><td>'.ucfirst($net).' &nbsp;</td>';
								$output .= '<td><span class="bold">'.ReplaceVendorCode($client, $version, $user_agent, (int)$is_a_gwc_param).'</span> &nbsp;</td>';
								$output .= '<td>'.rtrim($time).'</td></tr>'."\n";

								echo $output;
							}
						}
?>
					</table>
				</div>
<?php
				if($geoip) $geoip->Destroy(); $geoip = null;
			}
			elseif($page_number == 4)	// BlockList downloads
			{
				$b_formats = array('cidr' => 'CIDR version (LimeWire)');

				if(!empty($_GET['format']) && isset($b_formats[$_GET['format']]))
				{
					$b_format = $_GET['format']; $b_name = $b_formats[$b_format]; $b_magnet = null;
					if(!BLRevCheck($b_format) || ($b_magnet = htmlspecialchars(BLGenerateMagnet($b_format), ENT_QUOTES, 'UTF-8')) === "") $b_name = 'BlockList conversion error';
?>
					<div class="page-title"><strong>P2P BlockList</strong></div>
					<div class="padding">
						<div class="padding"><strong>Magnet link</strong></div>
						<div class="padding"><a class="magnet" href="<?php echo $b_magnet; ?>"><img width="14" height="14" src="images/magnet-icon.png" alt="Magnet"> <?php echo $b_name; ?></a></div>
					</div>
<?php
				}
				else
				{
?>
					<div class="page-title"><strong>P2P BlockList</strong> &nbsp;&nbsp; <a href="<?php echo $base_link; ?>showblocklists=2">Show informations</a></div>
					<div class="padding">
<?php
						if(file_exists('./ext/blocklist.dat'))
						{
?>
						<table><caption class="table-caption"><strong>Downloads list</strong></caption>
							<tr class="header-column">
								<th>Format</th>
							</tr>
							<tr>
								<td><div><a href="<?php echo $base_link; ?>showblocklists=1&amp;format=cidr"><?php echo $b_formats['cidr']; ?></a></div></td>
							</tr>
							<tr>
								<td><div>New formats will come in the future</div></td>
							</tr>
						</table>
<?php
						}
						else
							echo '<div class="padding">You must install the Add-on for this.</div>',"\n";
?>
					</div>
<?php
				}
			}
			elseif($page_number == 5)	// BlockList informations
			{
				GetBlockListInfo('./ext/gwc-blocklist.dat', '58FC4518D9', $BL_type, $BL_hash_check, $BL_file_size, $BL_author, $BL_rev, $BL_license);
?>
				<div class="page-title"><strong>GWC BlockList</strong></div>
				<div class="padding">
					<div class="padding"><strong>Lite BlockList (to be used by this GWC)</strong></div>
					<table class="inner-table-infos" summary="GWC BlockList">
						<tr>
							<th>- Type:</th>
							<td><strong><?php echo $BL_type; ?></strong></td>
						</tr>
						<tr>
							<th>- Hash check:</th>
							<td><strong><?php echo $BL_hash_check; ?></strong></td>
						</tr>
						<tr>
							<th>- Size:</th>
							<td><?php echo round($BL_file_size / 1024 / 1024, 2),' MB (',$BL_file_size,' bytes)'; ?></td>
						</tr>
						<tr>
							<th>- Author:</th>
							<td><?php echo $BL_author; ?></td>
						</tr>
						<tr>
							<th>- Revision:</th>
							<td><?php echo $BL_rev; ?></td>
						</tr>
						<tr>
							<th>- License:</th>
							<td><?php echo $BL_license; ?></td>
						</tr>
					</table>
				</div>
				<div>&nbsp;</div>
<?php
				GetBlockListInfo('./ext/blocklist.dat', '8C76B2A8FB', $BL_type, $BL_hash_check, $BL_file_size, $BL_author, $BL_rev, $BL_license);
?>
				<div class="page-title"><strong>P2P BlockList</strong></div>
				<div class="padding">
					<div class="padding"><strong>Full BlockList (to be used by P2P applications)</strong></div>
					<table class="inner-table-infos" summary="P2P BlockList">
						<tr>
							<th>- Type:</th>
							<td><strong><?php echo $BL_type; ?></strong></td>
						</tr>
						<tr>
							<th>- Hash check:</th>
							<td><strong><?php echo $BL_hash_check; ?></strong></td>
						</tr>
						<tr>
							<th>- Size:</th>
							<td><?php echo round($BL_file_size / 1024 / 1024, 2),' MB (',$BL_file_size,' bytes)'; ?></td>
						</tr>
						<tr>
							<th>- Author:</th>
							<td><?php echo $BL_author; ?></td>
						</tr>
						<tr>
							<th>- Revision:</th>
							<td><?php echo $BL_rev; ?></td>
						</tr>
						<tr>
							<th>- License:</th>
							<td><?php echo $BL_license; ?></td>
						</tr>
					</table>
				</div>
<?php
			}
			elseif($page_number == 6)	// Statistics
			{
				if(STATS_ENABLED)
				{
					/* Bad update requests of last hour */
					$upd_bad_reqs = ReadStats(STATS_UPD_BAD);
					/* Good + bad update requests of last hour */
					$upd_reqs = ReadStats(STATS_UPD) + $upd_bad_reqs;
					/* Blocked requests of last hour */
					$blocked_reqs = ReadStats(STATS_BLOCKED);
					/* Other requests of last hour */
					$other_reqs = ReadStats(STATS_OTHER);
				}
?>
				<div class="page-title"><strong>Statistics</strong></div>
				<div class="padding">
					<table class="inner-table-infos" summary="Statistics about this GWC">
						<tr>
							<th>- Total requests:</th>
							<td>
<?php
								if(STATS_ENABLED)
									echo ReadStatsTotalReqs();
								else
									echo 'Disabled';
								echo "\n";
?>
							</td>
						</tr>
						<tr>
							<th>- Requests this hour:</th>
							<td>
<?php
								if(STATS_ENABLED)
									echo ($other_reqs + $upd_reqs + $blocked_reqs),' (',$blocked_reqs,' blocked)';
								else
									echo 'Disabled';
								echo "\n";
?>
							</td>
						</tr>
						<tr>
							<th>- Updates this hour:</th>
							<td>
<?php
								if(STATS_ENABLED)
									echo $upd_reqs,' (',$upd_bad_reqs,' bad)';
								else
									echo 'Disabled';
								echo "\n";
?>
							</td>
						</tr>
					</table>
				</div>
<?php
			}
?>
			</div>
			<div id="project-link"><strong><?php echo NAME; ?>'s project page: <a href="<?php echo GWC_SITE; ?>" rel="external"><?php echo GWC_SITE; ?></a></strong></div>
		</div>
	</div>
	<div class="spacer"></div>
<?php
	if($page_number == 1)	// Info
	{
?>
	<div class="center"><div class="container"><div class="padding">
		<?php ShowUpdateCheck(); ?>
	</div></div></div>
	<div class="spacer"></div>
<?php
	}
?>

	<div class="footer">
<?php
		if($footer !== "") echo "\t\t",'<div class="center">',EncodeAmpersand($footer),'</div> <div class="spacer"></div>',"\n";
?>
		<div><a href="http://www1429309663.blogrover.com/" onclick="this.blur();" rel="nofollow"><img width="80" height="15" src="images/sticker.png" alt="Sticker"></a></div>
	</div>

	<script type="text/javascript">
	<!--
	function UseRelExternal()
	{
		var links; if(document.links) links = document.links; else if(document.getElementsByTagName) links = document.getElementsByTagName("a"); if(!links) return false;

		var links_count = links.length, ext_links_event = function(e){ var e = e || window.event; if(e.preventDefault) e.preventDefault(); else e.returnValue = false; this.blur(); window.open(this.href, '_' + "blank"); };
		for(var i=0; i<links_count; i++)
			if((' '+links[i].rel+' ').indexOf(" external ") != -1 && !links[i].onclick)
				links[i].onclick = ext_links_event;

		return true;
	}
	UseRelExternal();
	//-->
	</script>
<?php

	if($page_number == 3)	// WebCache
	{
?>

	<script type="text/javascript">
	<!--
	var links = document.getElementsByTagName("a");
	var links_count = links.length;
	var timer, i, c;

	function resetVars()
	{
		timer = null;
		i = 0;
		c = 0;
	}
	resetVars();

	function sendLink()
	{
		if( c < 20 && i < links_count )
		{
			if( links[i].className == "gwc" )
			{
				try{ document.location.href = links[i].href; }
				catch(e){ alert("Error, gwc: isn't associated to a p2p application."); clearInterval(timer); resetVars(); }
				c++;
			}
			i++;
		}
		else
		{
			clearInterval(timer); resetVars();
		}
	}

	function sendGWCs(e)
	{
		if(e.preventDefault) e.preventDefault(); else e.returnValue = false;
		var target = e.target || e.srcElement; if(target.nodeType == 3) target = target.parentNode;  /* Defeat Safari bug */
		target.blur();
		timer = setInterval("sendLink()", 25);
	}
	//-->
	</script>
<?php
	}
?>
</body>
</html><?php
}
?>