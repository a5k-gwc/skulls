<?php
header("Pragma: no-cache");

define( "REVISION", 4 );
if( !file_exists("revision.dat") )
{
	$file = fopen("revision.dat", "x");
	flock($file, 2);
	fwrite($file, "1");
	flock($file, 3);
	fclose($file);
}
$file_content = file("revision.dat");
if($file_content[0] >= REVISION)
	die("There is no need to update it.<br/>\r\nThis file checks only if data files are updated, it doesn't check if Skulls is updated.<br/>\r\n");


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

if( file_exists("../".DATA_DIR."/hosts_gnutella1.dat") )
{
	if( !file_exists("../".DATA_DIR."/hosts_gnutella.dat") )
	{
		$result = rename("../".DATA_DIR."/hosts_gnutella1.dat", "../".DATA_DIR."/hosts_gnutella.dat");
		$log .= "Renaming hosts_gnutella1.dat to hosts_gnutella.dat: ";
		$log .= check($result);
	}
	else
	{
		$result = unlink("../".DATA_DIR."/hosts_gnutella1.dat");
		$log .= "Deleting hosts_gnutella1.dat: ";
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

	$file = fopen("../".DATA_DIR."/caches.dat", "w");
	flock($file, 2);
	for($i = 0; $i < $count_cache; $i++)
		fwrite($file, $data[$i]."\r\n");
	flock($file, 3);
	fclose($file);

	if($changed)
	{
		$log .= "Internal structure updated in caches.dat.<br/>\r\n";
		$updated = TRUE;
	}
}

if( file_exists("../".DATA_DIR."/blocked_caches.dat") )
{
	$data = file("../".DATA_DIR."/blocked_caches.dat");
	$count_blocked_caches = count($data);

	$new_blocked_caches[] = array("http://www.exactmobile.co.za/cache.asp", 0);
	$new_blocked_caches[] = array("http://www.exactmobile.co.za/cache.asp/", 0);
	$new_blocked_caches[] = array("http://www.sexymobile.co.za/cache.asp", 0);
	$count_new_blocked_caches = count($new_blocked_caches);

	$changed = FALSE;
	for($i = 0; $i < $count_blocked_caches; $i++)
	{
		$data[$i] = trim($data[$i]);
		for($x = 0; $x < $count_new_blocked_caches; $x++)
		{
			if($data[$i] == $new_blocked_caches[$x][0])
				$new_blocked_caches[$x][1] = 1;
		}
	}

	for($x = 0; $x < $count_new_blocked_caches; $x++)
		if($new_blocked_caches[$x][1] == 0)
			$changed = TRUE;

	$file = fopen("../".DATA_DIR."/blocked_caches.dat", "w");
	flock($file, 2);
	for($i = 0; $i < $count_blocked_caches; $i++)
	{
		if( $data[$i] != "http://gwc.wodi.org/g2/bazooka" )	// Old blocked cache
			fwrite($file, $data[$i]."\r\n");
		else
			$changed = TRUE;
	}
	for($x = 0; $x < $count_new_blocked_caches; $x++)
		if($new_blocked_caches[$x][1] == 0)
			fwrite($file, $new_blocked_caches[$x][0]."\r\n");
	flock($file, 3);
	fclose($file);

	if($changed)
	{
		$log .= "blocked_caches.dat updated.<br/>\r\n";
		$updated = TRUE;
	}
}

if( !file_exists("../".DATA_DIR."/failed_urls.dat") )
{
	$log .= "Creating ".DATA_DIR."/failed_urls.dat: ";

	$file = fopen( "../".DATA_DIR."/failed_urls.dat", "w" );
	if( !$file )
		$result = FALSE;
	else
	{
		fclose($file);
		$result = TRUE;
	}

	$log .= check($result);
}

if( file_exists("../log/skulls.log") )
{
	$result = unlink("../log/skulls.log");
	$log .= "Deleting skulls.log: ";
	$log .= check($result);
}

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\r\n";
echo "<html><head><title>Update</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>\r\n";

echo $log;

if($errors)
	echo "<br/><font color=\"red\"><b>".$errors." ERRORS.</b></font>";
else
{
	$file = fopen("revision.dat", "w");
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