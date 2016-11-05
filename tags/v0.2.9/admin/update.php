<?php
header("Pragma: no-cache");
define( "REVISION", 4.9 );
if(file_exists("revision.dat"))
	$file_content = file("revision.dat");

if( !isset($file_content[0]) )
	$file_content[0] = 0;

$doctype = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\r\n";
$html_header = "<html><head><title>Update</title><meta name=\"robots\" content=\"noindex,nofollow,noarchive\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>\r\n";
$html_footer = "</body></html>\r\n";

if(rtrim($file_content[0]) == REVISION)
{
	echo $doctype.$html_header;
	echo "There is no need to update it.<br>\r\nThis file checks only if data files are updated, it doesn't check if Skulls is updated.<br>\r\nTo check if Skulls is updated you must go on skulls.php<br>\r\n";
	echo $html_footer;
	die();
}

ini_set("display_errors", 1);
if(defined("E_STRICT"))
	error_reporting(E_ALL | E_STRICT);
else
	error_reporting(E_ALL);

$log = "";
$errors = 0;
$updated = FALSE;
include "../vars.php";

function check($result)
{
	global $updated, $errors;
	$updated = TRUE;

	if($result)
		return "<font color=\"green\"><b>OK</b></font><br>\r\n";
	else
	{
		$errors++;
		return "<font color=\"red\"><b>ERROR</b></font><br>\r\n";
	}
}

function remove_dir($dir)
{
	if ($handle = opendir($dir))
	{
		while( $item = readdir($handle) )
		{
			if($item != "." && $item != "..")
			{
				if( is_dir($dir.$item))
					remove_dir($dir.$item);
				else
					unlink($dir.$item);
			}
		}

		closedir($handle);
		rmdir($dir);
	}
}

function truncate($file_name)
{
	global $updated, $errors;
	$updated = TRUE;

	$file = fopen($file_name, "wb");
	if($file !== FALSE)
	{
		fclose($file);
		return "<font color=\"green\"><b>OK</b></font><br>\r\n";
	}

	$errors++;
	return "<font color=\"red\"><b>ERROR</b></font><br>\r\n";
}

if( file_exists("../webcachedata/") )
{
	if( !file_exists("../".DATA_DIR."/") )
	{
		$result = rename("../webcachedata/", "../".DATA_DIR."/");
		$log .= "Renaming webcachedata folder to ".DATA_DIR.": ";
		$log .= check($result);
	}
	else
	{
		remove_dir("../webcachedata/");
		$result = !file_exists("../webcachedata/");
		$log .= "Deleting webcachedata folder: ";
		$log .= check($result);
	}
}

if( !file_exists("../".DATA_DIR."/") )
{
	$result = mkdir("../".DATA_DIR."/", 0777);
	$log .= "Creating ".DATA_DIR."/: ";
	$log .= check($result);
}

if( file_exists("../".DATA_DIR."/hosts_gnutella1.dat") )
{
	if( !file_exists("../".DATA_DIR."/hosts_gnutella.dat") )
	{
		$result = rename("../".DATA_DIR."/hosts_gnutella1.dat", "../".DATA_DIR."/hosts_gnutella.dat");
		$log .= "Renaming ".DATA_DIR."/hosts_gnutella1.dat to ".DATA_DIR."/hosts_gnutella.dat: ";
		$log .= check($result);
	}
	else
	{
		$result = unlink("../".DATA_DIR."/hosts_gnutella1.dat");
		$log .= "Deleting ".DATA_DIR."/hosts_gnutella1.dat: ";
		$log .= check($result);
	}
}

if( file_exists("../".DATA_DIR."/caches.dat") )
{
	$PHP_SELF = $_SERVER["PHP_SELF"];
	$SERVER_NAME = !empty($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : $_SERVER["HTTP_HOST"];
	$SERVER_PORT = !empty($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : 80;
	$MY_URL = $SERVER_PORT != 80 ? $SERVER_NAME.":".$SERVER_PORT.$PHP_SELF : $SERVER_NAME.$PHP_SELF;
	$MY_URL = strtolower(str_replace("/admin/update.php", "/skulls.php", $MY_URL));
	$cache_file = file("../".DATA_DIR."/caches.dat");
	$count_cache = count($cache_file);

	$changed = FALSE;
	$urls_array = array();
	for($i = 0; $i < $count_cache; $i++)
	{
		$delete = FALSE;
		$line = explode("|", rtrim($cache_file[$i]));

		if( !isset($line[5]) || ( isset($urls_array[$line[0]]) && $urls_array[$line[0]] == 1 ) )
			$delete = TRUE;
		elseif(strpos($line[0], "?") > -1 || strpos($line[0], "&") > -1 || strpos($line[0], "#") > -1
			// Bad
			|| $line[0] == "http://www.xolox.nl/gwebcache/"
			|| $line[0] == "http://www.xolox.nl/gwebcache/default.asp"
			|| $line[0] == "http://fischaleck.net/cache/mcache.php"
			|| $line[0] == "http://mcache.naskel.cx/mcache.php"
			|| $line[0] == "http://silence.forcedefrappe.com/mcache.php"
			// It take an eternity to load, it can't help network
			|| $line[0] == "http://reukiodo.dyndns.org/beacon/gwc.php"
			|| $line[0] == "http://reukiodo.dyndns.org/gwebcache/gwcii.php"
			// Double - They are accessible also from another url
			|| $line[0] == "http://gwc.frodoslair.net/skulls/skulls"
			|| $line[0] == "http://gwc.nickstallman.net/beta.php"
			|| $line[0] == "http://gwebcache.spearforensics.com/"
			// Other
			|| $line[0] == "http://bbs.robertwoolley.co.uk/GWebCache/gcache.php"
			|| strpos($line[0], ".nyud.net/") > -1
			|| strpos($line[0], ".nyucd.net/") > -1
			|| strpos($line[0], "index.php") == strlen($line[0]) - 9
		)
			$delete = TRUE;
		else
		{
			if($line[2] == "multi")
			{
				$line[2] = "gnutella-gnutella2";
				$changed = TRUE;
			}

			if(strpos($line[0], "://") > -1)
			{
				list( , $cache ) = explode("://", $line[0]);

				if( strpos($cache, "/") > -1 )
					list( $host, ) = explode("/", $cache);
				else
					$host = $cache;

				if(strtolower($host) != $host || strtolower($cache) == $MY_URL)
					$delete = TRUE;
			}
			elseif(substr($line[0], 0, 4) == "uhc:" || substr($line[0], 0, 5) == "ukhl:")
				;
			else
			{
				$delete = TRUE;
				echo "<font color=\"red\"><b>caches.dat -> strange url removed: ".$line[0]."</b></font><br>\r\n";
			}
		}

		if($delete)
		{
			$changed = TRUE;
			$data[$i] = "";
		}
		else
		{
			$urls_array[$line[0]] = 1;
			$data[$i] = implode("|", $line);
		}
		unset($line);
	}

	$file = fopen("../".DATA_DIR."/caches.dat", "wb");
	flock($file, 2);
	for($i = 0; $i < $count_cache; $i++)
	{
		$data[$i] = rtrim($data[$i]);
		if($data[$i] != "")
			fwrite($file, $data[$i]."\r\n");
	}
	flock($file, 3);
	fclose($file);

	if($changed)
	{
		$log .= "Internal structure updated in ".DATA_DIR."/caches.dat.<br>\r\n";
		$updated = TRUE;
	}
}

if( file_exists("../".DATA_DIR."/blocked_caches.dat") )
{
	$result = unlink("../".DATA_DIR."/blocked_caches.dat");
	$log .= "Deleting ".DATA_DIR."/blocked_caches.dat: ";
	$log .= check($result);
}

if( file_exists("../vendor_code.php") )
{
	$result = unlink("../vendor_code.php");
	$log .= "Deleting vendor_code.php: ";
	$log .= check($result);
}

if( file_exists("../log/skulls.log") )
{
	$result = unlink("../log/skulls.log");
	$log .= "Deleting log/skulls.log: ";
	$log .= check($result);
}

if( file_exists("../log/old_clients.log") )
{
	$result = unlink("../log/old_clients.log");
	$log .= "Deleting log/old_clients.log: ";
	$log .= check($result);
}

if( file_exists("../index.htm") && file_exists("../index.html") )
{
	$result = unlink("../index.htm");
	$log .= "Deleting index.htm (there is already index.html): ";
	$log .= check($result);
}

if( file_exists("../admin/index.htm") && file_exists("../admin/index.html") )
{
	$result = unlink("../admin/index.htm");
	$log .= "Deleting admin/index.htm (there is already admin/index.html): ";
	$log .= check($result);
}

if( file_exists("../data/failed_urls.dat") )
{
	if( filesize("../data/failed_urls.dat") > 1 * 1024 *1024 )
	{
		$log .= "Truncating data/failed_urls.dat because it is too big: ";
		$log .= truncate("../data/failed_urls.dat");
	}
}

if( file_exists("../stats/update_requests_hour.dat") )
{
	$bad = FALSE;

	$file = @fopen("../stats/update_requests_hour.dat", "r+b");
	if($file !== FALSE)
	{
		for($i = 0; $i < 5; $i++)
		{
			$line = fgets($file);
			if( strpos($line, "\r") > -1 )
			{
				$bad = TRUE;
				break;
			}
		}
		fclose($file);
	}

	if($bad)
	{
		$log .= "Truncating stats/update_requests_hour.dat because the format is changed: ";
		$log .= truncate("../stats/update_requests_hour.dat");
	}
	elseif( filesize("../stats/update_requests_hour.dat") > 1 * 1024 *1024 )
	{
		$log .= "Truncating stats/update_requests_hour.dat because it is too big: ";
		$log .= truncate("../stats/update_requests_hour.dat");
	}
}

if( file_exists("../stats/other_requests_hour.dat") )
{
	$bad = FALSE;

	$file = @fopen("../stats/other_requests_hour.dat", "r+b");
	if($file !== FALSE)
	{
		for($i = 0; $i < 5; $i++)
		{
			$line = fgets($file);
			if( strpos($line, "\r") > -1 )
			{
				$bad = TRUE;
				break;
			}
		}
		fclose($file);
	}

	if($bad)
	{
		$log .= "Truncating stats/other_requests_hour.dat because the format is changed: ";
		$log .= truncate("../stats/other_requests_hour.dat");
	}
	elseif( filesize("../stats/other_requests_hour.dat") > 1 * 1024 *1024 )
	{
		$log .= "Truncating stats/other_requests_hour.dat because it is too big: ";
		$log .= truncate("../stats/other_requests_hour.dat");
	}
}

echo $doctype.$html_header.$log;

if($errors)
{
	echo "<br><font color=\"red\"><b>".$errors." ";
	if($errors == 1)
		echo "ERROR";
	else
		echo "ERRORS";
	echo ".</b></font><br>";
	echo "<b>You must execute the failed actions manually.</b><br>";
}
else
{
	$file = @fopen("revision.dat", "wb");
	if($file !== FALSE)
	{
		flock($file, 2);
		fwrite($file, REVISION);
		flock($file, 3);
		fclose($file);

		if($updated)
			echo "<br><font color=\"green\"><b>Updated correctly.</b></font><br>";
		else
		{
			echo "<font color=\"green\"><b>Already updated.</b></font><br>";
			echo "<b>This file checks only if data files are updated, it doesn't check if Skulls is updated.<br>To check if Skulls is updated you must go on skulls.php</b><br>";
		}
	}
	else
		echo "<font color=\"red\">Error during writing of admin/revision.dat</font><br>";
}

if( isset($footer) && $footer != "" ) echo "<br><div>".$footer."</div>";
echo $html_footer;
?>