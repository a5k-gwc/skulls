<?php
function ShowHtmlPage($num){
	global $NET;
	include "vendor_code.php";

	if($NET == NULL)
		$NET = "all";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo NAME; ?>! Multi-Network WebCache <?php echo VER; ?></title>
	<meta name="robots" content="noindex,nofollow,nocache">
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
													$color = $i % 2 == 0 ? "#F0F0F0" : "#FFFFFF";

													echo "<tr align=\"left\" bgcolor=\"".$color."\">";
													echo "<td style=\"padding-right: 10pt;\"><a href=\"gnutella:host:".$ip."\">".$ip."</a>";
													if( !empty($leaves) )
														echo " (".$leaves.")";
													echo "</td>";
													echo "<td style=\"padding-right: 20pt;\"><strong>".ReplaceVendorCode($client, $version)."</strong></td>";
													echo "<td style=\"padding-right: 20pt;\">".(isset($net) ? $net : $host_file["net"][$i])."</td>";
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

													list( , $cache_url ) = explode("://", $cache_url, 2);

													if( strlen($cache_url) > 27 )
													{
														echo strpos($cache_url, "/") > 0 ? substr( $cache_url, 0, strpos($cache_url, "/") ) : substr( $cache_url, 0, 22 )." ... ";
														echo "/";
													}
													else
														echo $cache_url;

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
					UpdateStats("update", FALSE);
					UpdateStats("other", FALSE);
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
									{
										$requests = count( file("stats/other_requests_hour.dat") ) + count( file("stats/update_requests_hour.dat") );
										echo $requests;
									}
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
									{
										$requests = count( file("stats/update_requests_hour.dat") );
										echo $requests;
									}
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