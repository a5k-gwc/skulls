<?php
header("Pragma: no-cache");

define( "REVISION", 2 );
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
	die("There is no need to update it.<br>\r\nThis file checks only if data files are updated, it doesn't check if Skulls is updated.<br>\r\n");


$log = "";
$errors = 0;
$updated = FALSE;
include "../vars.php";

function check($result)
{
	global $updated, $errors;
	$updated = TRUE;

	if($result)
		return "<b>OK</b><br>\r\n";
	else
	{
		$errors++;
		return "<b>ERROR</b><br>\r\n";
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
	for($i=0; $i<$count_cache; $i++)
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
	for($i=0; $i<$count_cache; $i++)
		fwrite($file, $data[$i]."\r\n");
	flock($file, 3);
	fclose($file);

	if($changed)
	{
		$log .= "Internal structure updated in caches.dat.<br>\r\n";
		$updated = TRUE;
	}
}

if( file_exists("../log/") && !LOG_ENABLED && !LOG_ERRORS )
{
	remove_dir("../log/");
	$result = !file_exists("../log/");
	$log .= "Deleting log folder: ";
	$log .= check($result);
}

$file = fopen("revision.dat", "w");
flock($file, 2);
fwrite($file, REVISION);
flock($file, 3);
fclose($file);

echo $log;

if($errors)
	echo "<br>".$errors." errors.";
elseif($updated)
	echo "<br>Updated correctly.";
else
	echo "Already updated.";
?>