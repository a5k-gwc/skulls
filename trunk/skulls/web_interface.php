<?php
//
//  Copyright © 2005-2008, 2015 by ale5000
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

include "web_functions.php";

function ShowHtmlPage($num, $php_self, $compression, $header, $footer)
{
	global $NET, $SUPPORTED_NETWORKS;
	if($NET === null) $NET = 'all';

	if(!function_exists("Initialize"))
		include "functions.php";

	Initialize($SUPPORTED_NETWORKS, TRUE, TRUE);

	$base_link = basename($php_self).'?';
	if($compression !== null) $base_link .= 'compression='.$compression.'&amp;';

	$title = NAME.'! Multi-Network WebCache '.VER;
	$maintainer = htmlentities(MAINTAINER_NICK, ENT_QUOTES, 'UTF-8');
	$idn_support = (function_exists('idn_to_ascii'));
	if($num === 2) $title .= ' - Hosts'; elseif($num === 3) $title .= ' - GWCs'; elseif($num === 4) $title .= ' - Stats';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?php echo $title,' (by ',$maintainer,')'; ?></title>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="includes/style.css">
<!--[if lte IE 9]><link rel="stylesheet" type="text/css" href="includes/style-ie.css"><![endif]-->
<?php if($num === 1) echo '<link rel="canonical" href="',$php_self,'">',"\n"; ?>
<meta name="robots" content="<?php if($num === 1) echo 'index'; else echo 'noindex'; ?>, follow, noarchive, noimageindex">
<meta name="description" content="<?php echo NAME; ?> is a Multi-Network WebCache used from p2p clients to bootstrap.">
<meta name="keywords" content="<?php echo strtolower(NAME); ?>, gwebcache, gwc, p2p, bootstrap, gnutella, gnutella2">
</head>

<body>
	<div class="header">
		<div id="accessible-links"><a href="#content">[Skip to content]</a></div>
<?php
		if($header !== "") echo "\t\t",'<div class="center">',$header,'</div> <div class="spacer"></div>',"\n";
?>
	</div>

	<div class="center">
		<div class="container">
			<div class="header">
				<h1 class="title-spacing"><span class="main-title"><?php echo NAME; ?>!</span> Multi-Network WebCache <?php echo VER; ?></h1>
				<div id="page-list"><a href="<?php echo $base_link; ?>showinfo=1">Home</a> / <a href="<?php echo $base_link; ?>showhosts=1">Hosts</a> / <a href="<?php echo $base_link; ?>showurls=1">Alternative GWCs</a> / <a href="<?php echo $base_link; ?>stats=1">Statistics</a></div>
			</div>
			<div id="content">
<?php
			if($num == 1)	// Info
			{
?>
				<div id="page-title"><strong>Cache Info</strong></div>
				<div class="padding">
					<table class="inner-table-infos" summary="Informations about this GWC">
						<tr>
							<th>- Running since:</th>
							<td class="brown">
<?php
								if(file_exists(DATA_DIR."/running_since.dat"))
								{
									$running_since = file(DATA_DIR."/running_since.dat");
									echo $running_since[0],"\n";
								}
?>
							</td>
						</tr>
						<tr>
							<th>- Version:</th>
							<td class="green" title="<?php echo GetMainFileRev(); ?>"><span class="bold"><?php echo VER; ?></span></td>
						</tr>
						<tr>
							<th>- Networks:</th>
							<td class="brown">
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
							<th>- IDN support:</th>
							<td class="<?php echo ($idn_support? 'good' : 'bad'); ?>"><span class="bold"><?php echo ($idn_support? 'Yes' : 'No'); ?></span></td>
						</tr>
						<tr>
							<td></td>
							<td>&nbsp;</td>
						</tr>
<?php include './geoip/geoip.php'; $geoip = new GeoIPWrapper(); ?>
						<tr>
							<th>- GeoIP type:</th>
							<td class="brown"><span class="bold"><?php if($geoip) echo $geoip->GetType(); ?></span></td>
						</tr>
<?php
						if($geoip && $geoip->IsEnabled())
						{
?>
							<tr>
								<th>- GeoIP DB version:</th>
								<td class="green"><span class="bold"><?php echo htmlentities($geoip->GetDBVersion(), ENT_QUOTES, 'UTF-8'); ?></span></td>
							</tr>
							<tr>
								<th>- GeoIP DB (c)opy:</th>
								<td class="brown"><?php echo htmlentities($geoip->GetDBCopyright(), ENT_QUOTES, 'UTF-8'); ?></td>
							</tr>
<?php
						}
						if($geoip) $geoip->Destroy(); $geoip = null;

						$mail = str_replace('@', ' AT ', MAINTAINER_EMAIL);
						if($mail === 'name AT server DOT com')
							$mail = "";
						elseif($mail !== "")
							$mail = ' title="'.htmlentities($mail, ENT_QUOTES, 'UTF-8').'"';
?>
						<tr>
							<td></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th>- Maintainer:</th>
							<?php echo '<td class="blue"',$mail,'><span class="bold">',$maintainer,'</span></td>',"\n"; ?>
						</tr>
<?php
						if(MAINTAINER_WEBSITE !== 'http://www.your-site.com/' && MAINTAINER_WEBSITE !== "")
						{
?>
							<tr>
								<th>- Maintainer site:</th>
								<td class="blue">
<?php
									$website = htmlentities(MAINTAINER_WEBSITE, ENT_QUOTES, 'UTF-8');
									echo '<a href="',$website,'" class="hover-underline" rel="external">',$website,'</a>',"\n";
?>
								</td>
							</tr>
<?php
						}
?>
					</table>
				</div>
<?php
			}
			elseif($num == 2)	// Hosts
			{
				$max_hosts = MAX_HOSTS;
				$elements = 0;

				if( $NET == "all" )
				{
					global $SUPPORTED_NETWORKS;
					$max_hosts *= NETWORKS_COUNT;

					for($x = NETWORKS_COUNT - 1; $x >= 0; $x--)
					{
						$temp = file(DATA_DIR."/hosts_".strtolower($SUPPORTED_NETWORKS[$x]).".dat");
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
				elseif( file_exists(DATA_DIR."/hosts_".$NET.".dat") )
				{
					$host_file["host"] = file(DATA_DIR."/hosts_".$NET.".dat");
					$net = ucfirst($NET);
					$elements = count($host_file["host"]);
				}
?>
				<div id="page-title"><strong><?php echo htmlentities(ucfirst($NET), ENT_QUOTES, 'UTF-8'); ?> Hosts (<?php echo $elements." of ".$max_hosts; ?>)</strong></div>
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
							include './geoip/geoip.php';
							$geoip = new GeoIPWrapper();

							for( $i = $elements - 1; $i >= 0; $i-- )
							{
								list( $h_age, $h_ip, $h_port, $h_leaves, $h_max_leaves, , $h_vendor, $h_ver, $h_ua, /* $h_suspect */, ) = explode('|', $host_file['host'][$i], 13);
								if(isset($host_file['net'][$i])) $net = $host_file['net'][$i];
								$color = (($elements - $i) % 2 === 0 ? 'even' : 'odd');
								$host = $h_ip.':'.$h_port;
								$url = strtolower($net).':host:';

								echo '<tr class="',$color,'">';
								echo '<td>';
								if($geoip)
								{
									$country_name = $geoip->GetCountryNameByIP($h_ip);
									$country_code = $geoip->GetCountryCodeByIP($h_ip);
									echo '<img width="16" height="11" src="'.$geoip->GetCountryFlag($country_code).'" alt="'.$country_code.'" title="'.$country_name.'"> ';
								}
								echo '<a href="',$url,$host,'" rel="nofollow">',$host,'</a>';
								if($h_leaves !== "")
									echo ' (',$h_leaves,(empty($h_max_leaves)? null : '/'.$h_max_leaves),')';
								echo ' &nbsp;</td>';
								echo '<td><strong title="',$h_ua,'">',ReplaceVendorCode($h_vendor, $h_ver),'</strong> &nbsp;</td>';
								echo '<td><a href="',$base_link,'showhosts=1&amp;net=',strtolower($net),'">',$net,'</a> &nbsp;</td>';
								echo '<td>',$h_age,'</td></tr>',"\n";
							}

							if($geoip) $geoip->Destroy();
							$geoip = null;
						}
?>
					</table>
				</div>
<?php
			}
			elseif($num == 3)	// GWCs
			{
				include './geoip/geoip.php';
				$cache_file = file(DATA_DIR.'/alt-gwcs.dat');
				$elements = count($cache_file);
?>
				<div id="page-title"><strong>Alternative GWCs (<?php echo $elements." of ".MAX_CACHES; ?>)</strong> &nbsp;&nbsp; <a id="Send-GWCs" href="#Send-GWCs" onclick="sendGWCs(event);" rel="nofollow">Add first 20 GWCs to your P2P application</a></div>
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
							$geoip = new GeoIPWrapper();

							for($i = $elements - 1; $i >= 0; $i--)
							{
								list($time, $gwc_ip, $cache_url, $cache_name, $net, /**/, $gwc_server, $client, $version, /* UA */,) = explode("|", $cache_file[$i], 11);
								$cache_name = htmlentities($cache_name, ENT_QUOTES, 'UTF-8');
								if( strpos($net, "-") > -1 )
								{
									$networks = explode( "-", $net );
									$cache_nets_count = count($networks);
									$net = "";
									for( $x=0; $x < $cache_nets_count; $x++ )
									{
										if($x) $net .= " - ";
										$net .= ucfirst($networks[$x]);
									}
								}
								$color = (($elements - $i) % 2 === 0 ? 'even' : 'odd');

								$output = '<tr class="'.$color.'">';
								$output .= '<td>';

								$prefix = 'gwc:';
								$output .= '<a class="gwc" href="'.$prefix.$cache_url.'?nets='.str_replace(' ', "", strtolower($net)).'" rel="nofollow">+</a> ';

								if($geoip)
								{
									$country_name = $geoip->GetCountryNameByIP($gwc_ip);
									$country_code = $geoip->GetCountryCodeByIP($gwc_ip);
									$output .= '<img width="16" height="11" src="'.$geoip->GetCountryFlag($country_code).'" alt="'.$country_code.'" title="'.$country_name.'"> ';
								}
								$output .= '<a'.(strpos($cache_url, 'https:') === 0 ? ' class="https"' : "").' href="'.$cache_url.'" rel="external">';

								list(,$cache_url) = explode("://", $cache_url);
								$max_length = 40;

								$pos = strpos($cache_url, "/");
								if($pos > 0)
									$cache_url = substr($cache_url, 0, $pos);

								if(strlen($cache_url) > $max_length)
									$output .= substr($cache_url, 0, $max_length)."...";
								else
									$output .= $cache_url;

								$output .= '</a> &nbsp;</td><td><span title="'.$gwc_server.'">';
								if(strpos($cache_name, NAME) === 0)
									$output .= '<a class="gwc-home-link" href="'.GWC_SITE.'" rel="external nofollow">'.$cache_name.'</a>';
								elseif(NAME !== 'Sk'.'ulls' && strpos($cache_name, 'Sk'.'ulls') === 0)
									$output .= '<a class="gwc-home-link" href="http://sourceforge.net/projects/sk'.'ulls/" rel="external nofollow">'.$cache_name.'</a>';
								//elseif(strpos($cache_name, 'Bazooka') === 0)
									//$output .= '<a class="gwc-home-link" href="http://www.bazookanetworks.com/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'Beacon Cache') === 0)  /* Beacon Cache and Beacon Cache II */
									$output .= '<a class="gwc-home-link" href="http://sourceforge.net/projects/beaconcache/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'Cachechu') === 0)
									$output .= '<a class="gwc-home-link" href="http://github.com/kevogod/cachechu" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'GhostWhiteCrab') === 0)
									$output .= '<a class="gwc-home-link" href="http://sourceforge.net/projects/frostwire/files/GhostWhiteCrab/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'PHPGnuCacheII') === 0)
									$output .= '<a class="gwc-home-link" href="http://gwcii.sourceforge.net/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'jumswebcache') === 0)
									$output .= '<a class="gwc-home-link" href="http://www1.mager.org/GWebCache/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'MWebCache') === 0)
									$output .= '<a class="gwc-home-link" href="http://mute-net.sourceforge.net/mWebCache.shtml" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'node.gwc') === 0)
									$output .= '<a class="gwc-home-link" href="http://andrewgilmore.co.uk/project/nodegwc" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'GWebCache') === 0)
									$output .= '<a class="gwc-home-link" href="http://gnucleus.sourceforge.net/gwebcache/" rel="external nofollow">'.$cache_name.'</a>';
								elseif(strpos($cache_name, 'DKAC/Enticing-Enumon') === 0)
									$output .= '<a class="gwc-home-link" href="http://dkac.trillinux.org/dkac/dkac.php" rel="external nofollow">'.$cache_name.'</a>';
								else
									$output .= $cache_name;
								$output .= '</span> &nbsp;</td><td>'.ucfirst($net).' &nbsp;</td>';
								$output .= '<td><span class="bold">'.ReplaceVendorCode($client, $version).'</span> &nbsp;</td>';
								$output .= '<td>'.rtrim($time).'</td></tr>'."\n";

								echo $output;
							}

							if($geoip) $geoip->Destroy();
							$geoip = null;
						}
?>
					</table>
				</div>
				<div>&nbsp;</div>
<?php
				$cache_file = file(DATA_DIR.'/alt-udps.dat');
				$elements = count($cache_file);
?>
				<div id="page-title"><strong>Alternative UDP host caches (<?php echo $elements." of ".MAX_CACHES; ?>)</strong></div>
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
							$geoip = new GeoIPWrapper();

							for($i = $elements - 1; $i >= 0; $i--)
							{
								list($time, $gwc_ip, $cache_url, $cache_name, $net, /**/, $gwc_server, $client, $version, /* UA */,) = explode("|", $cache_file[$i], 11);
								$cache_name = htmlentities($cache_name, ENT_QUOTES, 'UTF-8');
								$color = (($elements - $i) % 2 === 0 ? 'even' : 'odd');

								$output = '<tr class="'.$color.'">';
								$output .= '<td>';

								if($net === 'gnutella')
									$prefix = 'uhc:';
								elseif($net === 'gnutella2')
									$prefix = 'ukhl:';
								else
									$prefix = $net.':'.'udpurl:';
								$output .= '<a class="gwc" href="'.$prefix.$cache_url.'" rel="nofollow">+</a> ';

								if($geoip)
								{
									$country_name = $geoip->GetCountryNameByIP($gwc_ip);
									$country_code = $geoip->GetCountryCodeByIP($gwc_ip);
									$output .= '<img width="16" height="11" src="'.$geoip->GetCountryFlag($country_code).'" alt="'.$country_code.'" title="'.$country_name.'"> ';
								}

								$output .= '<a class="udp" href="http://'.$cache_url.'" rel="external">';
								$max_length = 40;
								$pos = strpos($cache_url, '/'); if($pos !== false) $cache_url = substr($cache_url, 0, $pos);
								if(strlen($cache_url) > $max_length)
									$output .= substr($cache_url, 0, $max_length).'...';
								else
									$output .= $cache_url;
								$output .= '</a> &nbsp;</td><td><span title="'.$gwc_server.'">'.$cache_name;
								$output .= '</span> &nbsp;</td><td>'.ucfirst($net).' &nbsp;</td>';
								$output .= '<td><span class="bold">'.ReplaceVendorCode($client, $version).'</span> &nbsp;</td>';
								$output .= '<td>'.rtrim($time).'</td></tr>'."\n";

								echo $output;
							}

							if($geoip) $geoip->Destroy();
							$geoip = null;
						}
?>
					</table>
				</div>
<?php
			}
			elseif($num == 4)	// Statistics
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
				<div id="page-title"><strong>Statistics</strong></div>
				<div class="padding">
					<table class="inner-table-infos" summary="Statistics about this GWC">
						<tr>
							<th>- Total requests:</th>
							<td class="brown">
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
							<td class="brown">
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
							<td class="brown">
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
	if($num == 1)	// Info
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
		if($footer !== "") echo "\t\t",'<div class="center">',$footer,'</div> <div class="spacer"></div>',"\n";
?>
		<div><a href="http://www1429309663.blogrover.com/" rel="nofollow"><img width="80" height="15" src="images/sticker.png" alt="Sticker"></a></div>
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

	if($num == 3)	// WebCache
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