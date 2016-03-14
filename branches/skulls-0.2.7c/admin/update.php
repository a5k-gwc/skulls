<?php
header("Pragma: no-cache");

define( "REVISION", 4.4 );
if( !file_exists("revision.dat") )
{
	$file = fopen("revision.dat", "xb");
	flock($file, 2);
	fwrite($file, "1");
	flock($file, 3);
	fclose($file);
}
$file_content = file("revision.dat");

$doctype = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\r\n";
$header = "<html><head><title>Update</title><meta name=\"robots\" content=\"noindex,nofollow,noarchive\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>\r\n";
$html_footer = "</body></html>\r\n";

if(rtrim($file_content[0]) >= REVISION)
{
	echo $doctype.$header;
	echo "There is no need to update it.<br>\r\nThis file checks only if data files are updated, it doesn't check if Skulls is updated.<br>\r\nTo check if Skulls is updated you must go on skulls.php<br>\r\n";
	echo $html_footer;
	die();
}

ini_set(display_errors, 1);
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
		if($line[2] == "multi")
		{
			$line[2] = "gnutella-gnutella2";
			$changed = TRUE;
		}
		if(strpos($line[0], "?") > -1 || strpos($line[0], "&") > -1 || strpos($line[0], "#") > -1 || $line[0] == "http://gwc.nickstallman.net/gcache" || $line[0] == "http://gwc.nickstallman.net/gcache2.asp")
			$delete = TRUE;

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
		else
		{
			$errors++;
			echo "<font color=\"red\"><b>ERROR: Strange url</b></font><br>\r\n";
		}

		if( isset($urls_array[$line[0]]) && $urls_array[$line[0]] == 1 )
			$delete = TRUE;

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

if( !file_exists("../".DATA_DIR."/failed_urls.dat") )
{
	$log .= "Creating ".DATA_DIR."/failed_urls.dat: ";

	$file = fopen( "../".DATA_DIR."/failed_urls.dat", "wb" );
	if( !$file )
		$result = FALSE;
	else
	{
		fclose($file);
		$result = TRUE;
	}

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

echo $doctype.$header.$log;

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
	$file = fopen("revision.dat", "wb");
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

if( isset($footer) && $footer != "" ) echo "<br><div>".$footer."</div>";
echo $html_footer;
?>