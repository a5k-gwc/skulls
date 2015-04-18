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

function ShowHtmlPage($num, $header, $footer)
{
	global $NET, $SUPPORTED_NETWORKS;
	if(!function_exists("Initialize"))
		include "functions.php";

	Initialize($SUPPORTED_NETWORKS, TRUE, TRUE);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo NAME; ?>! Multi-Network WebCache <?php echo VER; ?></title>

<meta name="robots" content="<?php if($num === 1) echo 'index, follow'; else echo 'noindex, nofollow'; ?>, noarchive, noimageindex">
<meta name="keywords" content="skulls, gwebcache, gwc, p2p, gnutella, bootstrap">
<meta name="author" content="ale5000">
<meta name="generator" content="Notepad++">

<style type="text/css">
<!--
body { margin: 2em; padding: 0; background-color: #ffff00; font-family: Verdana; font-size: 75%; }
div { margin-bottom: 2em; }
table { border: 1px solid #ff3300; border-spacing: 0; border-collapse: collapse; background-color: #ffffff; }
td { padding: 0.4em; }

.center { text-align: center; }
.center table { margin-left: auto; margin-right: auto; text-align: left; }
.title-text { padding-left: 0.2em; font-size: 1.6em; }
.page-list { padding-left: 0.8em; padding-bottom: 1.5em; }
.page-title { background-color: #ccff99; }
.inner-table-informations { border-style: none; }
.inner-table-informations td { padding: 0.1em; }
.inner-table { border-color: #ccccdd; }

.odd  { background-color: #ffffff; }  /* Odd lines */
.even { background-color: #f0f0f0; }  /* Even lines */

table, div { font-size: 10px; }
a.hover-underline:link, a.hover-underline:visited, a.hover-underline:active, .gwc { text-decoration: none; }
a.hover-underline:hover { text-decoration: underline; }
-->
</style>
</head>

<body>
<?php
	if($header !== "") echo '<div class="center">',$header,'</div>',"\n";
?>
	<div class="center">
		<table>
			<tr>
				<td><b class="title-text"><span style="color: #008000"><?php echo NAME; ?>!</span> Multi-Network WebCache <?php echo VER; ?></b></td>
			</tr>
			<tr>
				<td class="page-list">
					<a href="?showinfo=1">General Details</a> /
					<a href="?showhosts=1&amp;net=all">Hosts</a> /
					<a href="?showurls=1">Alternative WebCaches</a> /
					<a href="?stats=1">Statistics</a>
				</td>
			</tr>
<?php
			if($num == 1)	// Info
			{
?>
				<tr class="page-title"> 
					<td style="color: #0044FF;"><b>Cache Info</b></td>
				</tr>
				<tr>
					<td>
						<table class="inner-table-informations" width="100%">
							<tr>
								<td width="150">- Running since:</td>
								<td style="color: #994433;">
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
								<td width="150">- Version:</td>
								<td style="color: #008000;" title="<?php echo GetMainFileRev(); ?>"><b><?php echo VER; ?></b></td>
							</tr>
							<tr>
								<td width="150">- Supported networks:</td>
								<td style="color: #994433;">
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
								<td>&nbsp;</td>
							</tr>
<?php include './geoip/geoip.php'; $geoip = new GeoIPWrapper(); ?>
							<tr>
								<td width="150">- GeoIP type:</td>
								<td style="color: #994433;"><b><?php if($geoip) echo $geoip->GetType(); ?></b></td>
							</tr>
<?php
							if($geoip && $geoip->IsEnabled())
							{
?>
								<tr>
									<td width="150">- GeoIP DB version:</td>
									<td style="color: #008000;"><b><?php echo htmlentities($geoip->GetDBVersion()); ?></b></td>
								</tr>
								<tr>
									<td width="150">- GeoIP DB copyright:</td>
									<td style="color: #994433;"><?php echo htmlentities($geoip->GetDBCopyright()); ?></td>
								</tr>
<?php
							}
							if($geoip) $geoip->Destroy(); $geoip = null;

							$mail = str_replace('@', ' AT ', MAINTAINER_EMAIL);
							if($mail === 'name AT server DOT com')
								$mail = "";
							elseif($mail !== "")
								$mail = ' title="'.htmlentities($mail).'"';
?>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td width="150">- Maintainer:</td>
								<td style="color: #0044FF;"><?php echo '<b',$mail,'>',htmlentities(MAINTAINER_NICK),'</b>'; ?></td>
							</tr>
<?php
							if(MAINTAINER_WEBSITE !== 'http://www.your-site.com/' && MAINTAINER_WEBSITE !== "")
							{
?>
								<tr>
									<td width="150">- Maintainer website:</td>
									<td style="color: #0044FF;">
<?php
										$website = htmlentities(MAINTAINER_WEBSITE);
										echo '<a href="',$website,'" class="hover-underline" rel="external" target="_blank">',$website,'</a>',"\n";
?>
									</td>
								</tr>
<?php
							}
?>
						</table>
					</td>
				</tr>
<?php
			}
			elseif($num == 2)	// Host
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
				<tr class="page-title"> 
					<td style="color: #0044FF"><b><?php echo ucfirst($NET); ?> Hosts (<?php echo $elements." of ".$max_hosts; ?>)</b></td>
				</tr>
				<tr>
					<td>
						<table class="inner-table" width="100%">
							<tr bgcolor="#C6E6E6">
								<td>Host address (Leaves)</td>
								<td>Client</td>
								<td>Network</td>
								<td>Last updated</td>
							</tr>
							<?php
							if( $elements === 0 )
								print("<tr align=\"center\" bgcolor=\"#FFFFFF\"><td colspan=\"4\" height=\"30\">There are no <strong>hosts</strong> listed at this time.</td></tr>\n");
							else
							{
								include './geoip/geoip.php';
								$geoip = new GeoIPWrapper();

								for( $i = $elements - 1; $i >= 0; $i-- )
								{
									list( $h_age, $h_ip, $h_port, $h_leaves, , , $h_vendor, $h_ver, $h_ua, /* $h_suspect */, ) = explode('|', $host_file['host'][$i], 13);
									if(isset($host_file['net'][$i])) $net = $host_file['net'][$i];
									$color = (($elements - $i) % 2 === 0 ? 'even' : 'odd');
									$host = $h_ip.':'.$h_port;
									$url = strtolower($net).':host:';

									echo '<tr class="',$color,'" align="left">';
									echo '<td style="padding-right: 10pt;">';
									if($geoip)
									{
										$country_name = $geoip->GetCountryNameByIP($h_ip);
										$country_code = $geoip->GetCountryCodeByIP($h_ip);
										echo '<img width="16" height="11" src="'.$geoip->GetCountryFlag($country_code).'" alt="'.$country_code.'" title="'.$country_name.'"> ';
									}
									echo '<a href="',$url,$host,'">',$host,'</a>';
									if($h_leaves !== "")
										echo ' (',$h_leaves,')';
									echo '</td>';
									echo '<td style="padding-right: 20pt;"><strong title="',$h_ua,'">',ReplaceVendorCode($h_vendor, $h_ver),'</strong></td>';
									echo '<td style="padding-right: 20pt;"><a href="?showhosts=1&amp;net=',strtolower($net),'">',$net,'</a></td>';
									echo '<td>',$h_age,'</td></tr>',"\n";
								}

								if($geoip) $geoip->Destroy();
								$geoip = null;
							}
							?>
						</table>
					</td>
				</tr>
<?php
			}
			elseif($num == 3)	// WebCache
			{
				$cache_file = file(DATA_DIR."/caches.dat");
				$elements = count($cache_file);
?>
				<tr class="page-title"> 
					<td style="color: #0044FF"><b>Alternative WebCaches (<?php echo count($cache_file)." of ".MAX_CACHES; ?>)</b>&nbsp;&nbsp;&nbsp;&nbsp;<a href="Javascript:sendGWCs();">Add first 20 caches to your P2P application</a></td>
				</tr>
				<tr>
					<td>
						<table class="inner-table" width="100%">
							<tr bgcolor="#C6E6E6">
								<td>URL</td>
								<td>Name</td>
								<td>Networks</td>
								<td>Submitting client</td>
								<td>Last checked</td>
							</tr>
							<?php
							if( $elements === 0 )
								print("<tr align=\"center\" bgcolor=\"#FFFFFF\"><td colspan=\"5\" height=\"30\">There are no <strong>alternative webcaches</strong> listed at this time.</td></tr>\n");
							else
							{
								$udp = "";
								for($i = $elements - 1; $i >= 0; $i--)
								{
									list ($cache_url, $cache_name, $net, $client, $version, $time) = explode("|", $cache_file[$i], 6);
									$cache_name = htmlentities($cache_name);
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

									$output = "<tr class=\"".$color."\" align=\"left\">";
									$output .= "<td style=\"padding-right: 10pt;\">";

									if(strpos($cache_url, "://") > -1)
									{
										$prefix = "gwc:";
										$output .= '<a class="gwc" href="'.$prefix.$cache_url.'?nets='.str_replace(' ', "", strtolower($net)).'" rel="nofollow">+</a> ';
									}
									else
										$output .= "&nbsp;&nbsp;&nbsp;";
									$output .= '<a href="'.$cache_url.'" rel="external" target="_blank">';

									if(strpos($cache_url, "://") > -1)
									{
										$type = "tcp";
										list( , $cache_url) = explode("://", $cache_url);
									}
									else
									{
										$type = "udp";
										$cache_url = substr($cache_url, strpos($cache_url, ":")+1);
									}
									$max_length = 40;

									$pos = strpos($cache_url, "/");
									if($pos > 0)
										$cache_url = substr($cache_url, 0, $pos);

									if(strlen($cache_url) > $max_length)
										$output .= substr($cache_url, 0, $max_length)."...";
									else
										$output .= $cache_url;

									$output .= "</a></td>";
									if(strpos($cache_name, NAME) === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"".GWC_SITE."\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(NAME !== 'Sk'.'ulls' && strpos($cache_name, 'Sk'.'ulls') === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://sourceforge.net/projects/skulls/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									//elseif(strpos($cache_name, "Bazooka") === 0)
										//$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://www.bazookanetworks.com/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(strpos($cache_name, "Beacon Cache") === 0)  /* Beacon Cache and Beacon Cache II */
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://sourceforge.net/projects/beaconcache/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(strpos($cache_name, "Cachechu") === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://github.com/kevogod/cachechu\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(strpos($cache_name, "GhostWhiteCrab") === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://sourceforge.net/projects/frostwire/files/GhostWhiteCrab/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(strpos($cache_name, "PHPGnuCacheII") === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://gwcii.sourceforge.net/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(strpos($cache_name, "jumswebcache") === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://www1.mager.org/GWebCache/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(strpos($cache_name, "MWebCache") === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://mute-net.sourceforge.net/mWebCache.shtml\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(strpos($cache_name, "node.gwc") === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://andrewgilmore.co.uk/project/nodegwc\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(strpos($cache_name, "GWebCache") === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://gnucleus.sourceforge.net/gwebcache/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									elseif(strpos($cache_name, "DKAC/Enticing-Enumon") === 0)
										$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://dkac.trillinux.org/dkac/dkac.php\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
									else
										$output .= "<td style=\"padding-right: 20pt;\">".$cache_name."</td>";
									$output .= "<td style=\"padding-right: 20pt;\">".ucfirst($net)."</td>";
									$output .= "<td style=\"padding-right: 20pt;\"><strong>".ReplaceVendorCode($client, $version)."</strong></td>";
									$output .= "<td>".rtrim($time).'</td></tr>'."\n";

									if($type == "tcp") echo $output;
									else $udp .= $output;
								}
								echo $udp;
							}
							?>
						</table>
					</td>
				</tr>
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
				<tr class="page-title"> 
					<td style="color: #0044FF;"><b>Statistics</b></td>
				</tr>
				<tr>
					<td>
						<table class="inner-table-informations" width="100%">
							<tr>
								<td width="150">- Total requests:</td>
								<td style="color: #994433;">
<?php
									if(STATS_ENABLED)
										echo ReadStatsTotalReqs();
									else
										echo 'Disabled';
?>
								</td>
							</tr>
							<tr>
								<td width="150">- Requests this hour:</td>
								<td style="color: #994433;">
<?php
									if(STATS_ENABLED)
										echo ($other_reqs + $upd_reqs + $blocked_reqs),' (',$blocked_reqs,' blocked)';
									else
										echo 'Disabled';
?>
								</td>
							</tr>
							<tr>
								<td width="150">- Updates this hour:</td>
								<td style="color: #994433;">
<?php
									if(STATS_ENABLED)
										echo $upd_reqs,' (',$upd_bad_reqs,' bad)';
									else
										echo 'Disabled';
?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
<?php
			}
?>
			<tr>
				<td style="padding: 5pt;"><b><?php echo NAME; ?>'s project page: <a href="<?php echo GWC_SITE; ?>" rel="external" target="_blank"><?php echo GWC_SITE; ?></a></b></td>
			</tr>
		</table>
	</div>

<?php
	if($num == 1)	// Info
	{
?>
	<div class="center">
		<table>
			<tr>
				<td>
					<?php ShowUpdateCheck(); ?>
				</td>
			</tr>
		</table>
	</div>
<?php
	}

	if($num == 3)	// WebCache
	{
?>
<script type="text/JavaScript">
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

function sendGWCs()
{
	timer = setInterval("sendLink()", 25);
}
</script>
<?php
	}

	if($footer !== "") echo '<div class="center">',$footer,'</div>',"\n";
	echo '<div><a href="http://www1429309663.blogrover.com/" rel="nofollow"><img width="80" height="15" src="http://pics8.inxhost.com/images/sticker.gif" alt="Sticker"></a></div>',"\n";
?>
</body>
</html>
<?php
}
?>