<?php
header("Pragma: no-cache");

define( "REVISION", 4.2 );
if( !file_exists("revision.dat") )
{
	$file = fopen("revision.dat", "xb");
	flock($file, 2);
	fwrite($file, "1");
	flock($file, 3);
	fclose($file);
}
$file_content = file("revision.dat");
if(rtrim($file_content[0]) >= REVISION)
{
	header("Content-Type: text/plain");
	die("There is no need to update it.\r\nThis file checks only if data files are updated, it doesn't check if Skulls is updated.\r\n");
}


$log = "";
$errors = 0;
$updated = FALSE;
include "../vars.php";

function check($result)
{
	global $updated, $errors;
	$updated = TRUE;

	if($result)
		return "<font color=\"green\"><b>OK</b></font><br/>\r\n";
	else
	{
		$errors++;
		return "<font color=\"red\"><b>ERROR</b></font><br/>\r\n";
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
	$cache_file = file("../".DATA_DIR."/caches.dat");
	$count_cache = count($cache_file);

	$changed = FALSE;
	for($i = 0; $i < $count_cache; $i++)
	{
		$line = explode("|", trim($cache_file[$i]));
		if($line[2] == "multi")
		{
			$line[2] = "gnutella-gnutella2";
			$changed = TRUE;
		}
		$data[$i] = implode("|", $line);
	}

	$file = fopen("../".DATA_DIR."/caches.dat", "wb");
	flock($file, 2);
	for($i = 0; $i < $count_cache; $i++)
		fwrite($file, $data[$i]."\r\n");
	flock($file, 3);
	fclose($file);

	if($changed)
	{
		$log .= "Internal structure updated in ".DATA_DIR."/caches.dat.<br/>\r\n";
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

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\r\n";
echo "<html><head><title>Update</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>\r\n";

echo $log;

if($errors)
{
	echo "<br/><font color=\"red\"><b>".$errors." ";
	if($errors == 1)
		echo "ERROR";
	else
		echo "ERRORS";
	echo ".</b></font><br/>";
	echo "<b>You must execute the failed actions manually.</b>";
}
else
{
	$file = fopen("revision.dat", "wb");
	flock($file, 2);
	fwrite($file, REVISION);
	flock($file, 3);
	fclose($file);

	if($updated)
		echo "<br/><font color=\"green\"><b>Updated correctly.</b></font>";
	else
		echo "<font color=\"green\"><b>Already updated.</b></font>";
}

echo "</body></html>";
?>