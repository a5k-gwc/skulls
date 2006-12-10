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

function ShowHtmlPage($num){
	global $NET;
	if($NET == NULL) $NET = "all";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo NAME; ?>! Multi-Network WebCache <?php echo VER; ?></title>
	<meta name="robots" content="noindex,nofollow,noarchive">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<style type="text/css">
		body { font-family: Verdana; }
		table { font-size: 10px; }
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
						<a href="?showhosts=1&amp;net=<?php echo $NET; ?>">Hosts</a> /
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
									if(file_exists(DATA_DIR."/runnig_since.dat"))
									{
										$runnig_since = file(DATA_DIR."/runnig_since.dat");
										echo $runnig_since[0];
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
														$url = "http://";
													echo "<a href=\"".$url.$ip."\">".$ip."</a>";
													if( !empty($leaves) )
														echo " (".$leaves.")";
													echo "</td>";
													echo "<td style=\"padding-right: 20pt;\"><strong>".ReplaceVendorCode($client, $version)."</strong></td>";
													echo "<td style=\"padding-right: 20pt;\">".$net."</td>";
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
					<td style="color: #0044FF"><b>Alternative WebCaches (<?php echo count($cache_file)." of ".MAX_CACHES; ?>)</b></td>
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

													echo "<tr align=\"left\" bgcolor=\"".$color."\">";
													echo "<td style=\"padding-right: 10pt;\">";
													echo "<a href=\"".$cache_url."\" target=\"_blank\">";

													list($protocol, $cache_url) = explode("://", $cache_url, 2);
													$max_length = 35;

													if( strlen($cache_url) > $max_length )
													{
														$pos = strpos($cache_url, "/");
														echo $protocol."://";
														if($pos > 0 && $pos <= $max_length)
															echo substr($cache_url, 0, $pos)."/...";
														else
															echo substr($cache_url, 0, $max_length)."...";
													}
													else
														echo $protocol."://".$cache_url;

													echo "</a></td>";
													echo "<td style=\"padding-right: 20pt;\">".$cache_name."</td>";
													echo "<td style=\"padding-right: 20pt;\">".ucfirst($net)."</td>";
													echo "<td style=\"padding-right: 20pt;\"><strong>".ReplaceVendorCode($client, $version)."</strong></td>";
													echo "<td>".$time."</td></tr>";
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
				<tr>
					<td bgcolor="#FFFFFF" style="padding: 5pt;"><b><?php echo NAME; ?>'s project page: <a href="http://sourceforge.net/projects/skulls/" target="_blank">http://sourceforge.net/projects/skulls/</a></b></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

</body>
</html>
<?php
}
?>