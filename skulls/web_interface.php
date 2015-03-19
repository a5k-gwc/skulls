<?php
include "web_functions.php";

function ShowHtmlPage($num){
	global $NET, $SUPPORTED_NETWORKS;
	if(!function_exists("Initialize"))
		include "functions.php";

	Initialize($SUPPORTED_NETWORKS, TRUE, TRUE);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo NAME; ?>! Multi-Network WebCache <?php echo VER; ?></title>
	<meta name="robots" content="noindex,nofollow,noarchive">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<style type="text/css">
		body { font-family: Verdana; }
		table, div { font-size: 10px; }
		a.hover-underline:link, a.hover-underline:visited, a.hover-underline:active, .gwc { text-decoration: none; }
		a.hover-underline:hover { text-decoration: underline; }
	</style>
</head>

<body bgcolor="#FFFF00"><br>

<table align="center">
	<tr>
		<td bgcolor="#FF3300">
			<table width="100%" cellspacing="0" cellpadding="5">
				<tr>
					<td bgcolor="#FFFFFF" style="font-size: 16px;"><b><span style="color: #008000"><?php echo NAME; ?>!</span> Multi-Network WebCache <?php echo VER; ?></b></td>
				</tr>
				<tr>
					<td height="30" valign="top" bgcolor="#FFFFFF">
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
				<tr bgcolor="#CCFF99"> 
					<td style="color: #0044FF;"><b>Cache Info</b></td>
				</tr>
				<tr>
					<td bgcolor="#FFFFFF">
						<table width="100%" cellspacing="0">
							<tr>
								<td width="150">- Running since:</td>
								<td style="color: #994433;">
								<?php
									if(file_exists(DATA_DIR."/running_since.dat"))
									{
										$running_since = file(DATA_DIR."/running_since.dat");
										echo $running_since[0];
									}
								?>
								</td>
							</tr>
							<tr>
								<td width="150">- Version:</td>
								<td style="color: #008000;"><b><?php echo VER; ?></b></td>
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
								?>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td width="150">- Maintainer:</td>
								<td style="color: #0044FF;">
									<?php
										$mail = str_replace("@", " AT ", MAINTAINER_EMAIL);
										if($mail == "name AT server DOT com") $mail = "";
										echo "<b title=\"".$mail."\">".MAINTAINER_NICK."</b>";
									?>
								</td>
							</tr>
							<?php
								if(MAINTAINER_WEBSITE != "http://www.your-site.com/")
								{
							?>
									<tr>
										<td width="150">- Maintainer website:</td>
										<td style="color: #0044FF;">
											<?php
												$website = MAINTAINER_WEBSITE;
												echo "<a href=\"".$website."\" class=\"hover-underline\" target=\"_blank\">".$website."</a>";
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

					for($x = 0; $x < NETWORKS_COUNT; $x++)
					{
						$temp = file(DATA_DIR."/hosts_".strtolower($SUPPORTED_NETWORKS[$x]).".dat");
						$n_temp = count($temp);
						for($y = 0; $y < $n_temp; $y++)
						{
							$host_file["host"][$elements] = $temp[$y];
							$host_file["net"][$elements] = $SUPPORTED_NETWORKS[$x];
							$elements++;
							unset($temp[$y]);
						}
					}
				}
				elseif( file_exists(DATA_DIR."/hosts_".$NET.".dat") )
				{
					$host_file["host"] = file(DATA_DIR."/hosts_".$NET.".dat");
					$net = ucfirst($NET);
					$elements = count($host_file["host"]);
				}
				?>
				<tr bgcolor="#CCFF99"> 
					<td style="color: #0044FF">
					<b><?php echo ucfirst($NET); ?> Hosts (<?php echo $elements." of ".$max_hosts; ?>)</b>
					</td>
				</tr>
				<tr>
					<td bgcolor="#FFFFFF">
						<table width="100%" cellspacing="0">
							<tr>
								<td bgcolor="#CCCCDD">
									<table width="100%" cellspacing="0" cellpadding="4">
										<tr bgcolor="#C6E6E6"> 
											<td>Host address (Leaves)</td>
											<td>Client</td>
											<td>Network</td>
											<td>Last updated</td>
										</tr>
										<?php
											if( $elements == 0 )
												print("<tr align=\"center\" bgcolor=\"#FFFFFF\"><td colspan=\"4\" height=\"30\">There are no <strong>hosts</strong> listed at this time.</td></tr>\r\n");
											else
											{
												for( $i = $elements - 1; $i >= 0; $i-- )
												{
													list ($ip, $leaves, , $client, $version, $time) = explode("|", $host_file["host"][$i]);
													if(isset($host_file["net"][$i])) $net = $host_file["net"][$i];
													$color = $i % 2 == 0 ? "#F0F0F0" : "#FFFFFF";

													echo "<tr align=\"left\" bgcolor=\"".$color."\">";
													echo "<td style=\"padding-right: 10pt;\">";
													if(strpos(strtolower($net), "gnutella") > -1)
														$url = "gnutella:host:";
													else
														$url = $net.":host:";
													echo "<a href=\"".$url.$ip."\">".$ip."</a>";
													if( !empty($leaves) )
														echo " (".$leaves.")";
													echo "</td>";
													echo "<td style=\"padding-right: 20pt;\"><strong>".ReplaceVendorCode($client, $version)."</strong></td>";
													echo "<td style=\"padding-right: 20pt;\"><a href=\"?showhosts=1&amp;net=".strtolower($net)."\">".$net."</a></td>";
													echo "<td>".$time."</td></tr>";
												}
											}
										?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			elseif($num == 3)	// WebCache
			{
				$cache_file = file(DATA_DIR."/caches.dat");
				?>
				<tr bgcolor="#CCFF99"> 
					<td style="color: #0044FF"><b>Alternative WebCaches (<?php echo count($cache_file)." of ".MAX_CACHES; ?>)</b>&nbsp;&nbsp;&nbsp;&nbsp;<a href="Javascript:sendGWCs();">Add first 20 caches to your P2P application</a></td>
				</tr>
				<tr>
					<td bgcolor="#FFFFFF">
						<table width="100%" cellspacing="0">
							<tr>
								<td bgcolor="#CCCCDD">
									<table width="100%" cellspacing="0" cellpadding="4">
										<tr bgcolor="#C6E6E6"> 
											<td>WebCache URL</td>
											<td>Name</td>
											<td>Network</td>
											<td>Submitting client</td>
											<td>Last updated</td>
										</tr>
										<?php
											if( count($cache_file) == 0 )
												print("<tr align=\"center\" bgcolor=\"#FFFFFF\"><td colspan=\"5\" height=\"30\">There are no <strong>alternative webcaches</strong> listed at this time.</td></tr>\r\n");
											else
											{
												$udp = "";
												for($i = count($cache_file) - 1; $i >= 0; $i--)
												{
													list ($cache_url, $cache_name, $net, $client, $version, $time) = explode("|", $cache_file[$i], 6);
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
													$color = $i % 2 == 0 ? "#F0F0F0" : "#FFFFFF";

													$output = "<tr align=\"left\" bgcolor=\"".$color."\">";
													$output .= "<td style=\"padding-right: 10pt;\">";

													if(strpos($cache_url, "://") > -1)
													{
														$prefix = "gwc:";
														$output .= "<a class=\"gwc\" href=\"".$prefix.$cache_url."?nets=".str_replace(" ", "", strtolower($net))."\">+</a> ";
													}
													else
														$output .= "&nbsp;&nbsp;&nbsp;";
													$output .= "<a href=\"".$cache_url."\" target=\"_blank\">";

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
													$max_length = 35;

													$pos = strpos($cache_url, "/");
													if($pos > 0)
														$cache_url = substr($cache_url, 0, $pos);

													if(strlen($cache_url) > $max_length)
														$output .= substr($cache_url, 0, $max_length)."...";
													else
														$output .= $cache_url;

													$output .= "</a></td>";
													if(strpos($cache_name, NAME) > -1)
														$output .= "<td style=\"padding-right: 20pt;\"><a href=\"".GWC_SITE."\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
													elseif(NAME != "Skulls" && strpos($cache_name, "Skulls") > -1)
														$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://sourceforge.net/projects/skulls/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
													elseif(strpos($cache_name, "Bazooka") > -1)
														$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://www.bazookanetworks.com/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
													elseif(strpos($cache_name, "Beacon Cache") > -1)  // Beacon Cache and Beacon Cache II
														$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://sourceforge.net/projects/beaconcache/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
													elseif(strpos($cache_name, "Cachechu") > -1)
														$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://code.google.com/p/cachechu/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
													elseif(strpos($cache_name, "PHPGnuCacheII") > -1)
														$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://gwcii.sourceforge.net/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
													elseif(strpos($cache_name, "GWebCache") > -1)
														$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://www.gnucleus.com/gwebcache/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
													elseif(strpos($cache_name, "jumswebcache") > -1)
														$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://www1.mager.org/GWebCache/\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
													elseif(strpos($cache_name, "MWebCache") > -1)
														$output .= "<td style=\"padding-right: 20pt;\"><a href=\"http://sourceforge.net/tracker/index.php?func=detail&amp;aid=1588787&amp;group_id=83030&amp;atid=568086\" class=\"hover-underline\" style=\"color: black;\" target=\"_blank\">".$cache_name."</a></td>";
													else
														$output .= "<td style=\"padding-right: 20pt;\">".$cache_name."</td>";
													$output .= "<td style=\"padding-right: 20pt;\">".ucfirst($net)."</td>";
													$output .= "<td style=\"padding-right: 20pt;\"><strong>".ReplaceVendorCode($client, $version)."</strong></td>";
													$output .= "<td>".$time."</td></tr>";

													if($type == "tcp") echo $output;
													else $udp .= $output;
												}
												echo $udp;
											}
										?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			elseif($num == 4)	// Statistics
			{
				if(STATS_ENABLED)
				{
					$other_requests = ReadStats("other");
					$update_requests = ReadStats("update");
				}
				?>
				<tr bgcolor="#CCFF99"> 
					<td style="color: #0044FF;"><b>Statistics</b></td>
				</tr>
				<tr>
					<td bgcolor="#FFFFFF">
						<table width="100%" cellspacing="0">
							<tr>
								<td width="150">- Total requests:</td>
								<td style="color: #994433;">
								<?php
									if(STATS_ENABLED)
									{
										$requests = file("stats/requests.dat");
										echo $requests[0];
									}
									else
										echo "Disabled";
								?>
								</td>
							</tr>
							<tr>
								<td width="150">- Requests this hour:</td>
								<td style="color: #994433;">
								<?php
									if(STATS_ENABLED)
										echo $other_requests + $update_requests;
									else
										echo "Disabled";
								?>
								</td>
							</tr>
							<tr>
								<td width="150">- Updates this hour:</td>
								<td style="color: #994433;">
								<?php
									if(STATS_ENABLED)
										echo $update_requests;
									else
										echo "Disabled";
								?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
		        <?php
			}
				?>
				<tr bgcolor="#FFFFFF">
					<td style="padding: 5pt;"><b><?php echo NAME; ?>'s project page: <a href="<?php echo GWC_SITE; ?>" target="_blank"><?php echo GWC_SITE; ?></a></b></td>
				</tr>
			</table>
		</td>
	</tr>
</table><br>

<?php
	if($num == 1)	// Info
	{
?>
<table align="center">
	<tr>
		<td bgcolor="#FF3300">
			<table width="100%" cellspacing="0" cellpadding="5">
				<tr>
					<td bgcolor="#FFFFFF">
						<?php ShowUpdateCheck(); ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
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

	global $footer;
	if( isset($footer) && $footer != "" ) echo "<br><div>".$footer."</div>";
?>

</body>
</html>
<?php
}
?>