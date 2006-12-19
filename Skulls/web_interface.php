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
		list( , $url ) = explode("://", $url, 2);		// It remove "http://" from "cache" - $url = www.test.com:80/page.php
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

function ShowHtmlPage($num){
	global $NET;
	if($NET == NULL) $NET = "all";

	if(!function_exists("Initialize"))
	{
		global $SUPPORTED_NETWORKS;
		include "functions.php";
		Initialize($SUPPORTED_NETWORKS, TRUE);
	}
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
						<b>Update check process:</b> <?php $latest_version = CheckUpdates(); ?>
						<?php
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

								if($need_update) echo "<font color=\"".$color."\"><b>There is a new version of Skulls, you should update it.</b></font><br>";
							}
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php
	}
?>

</body>
</html>
<?php
}
?>