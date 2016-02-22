<?php
//
//  Copyright (C) 2005-2008, 2015 by ale5000
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

header("Pragma: no-cache");
define( "REVISION", 4.9 );
if(file_exists("revision.dat"))
	$file_content = file("revision.dat");

if( !isset($file_content[0]) )
	$file_content[0] = 0;

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'."\r\n";
$html_header = "<html><head><title>Update</title><meta name=\"robots\" content=\"noindex,nofollow,noarchive\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>\r\n";
$html_footer = "</body></html>\r\n";

if(rtrim($file_content[0]) == REVISION)
{
	echo $html_header;
	echo "There is no need to update it.<br>\r\nThis file checks only if data files are updated, it doesn't check if the GWC is updated.<br>\r\nTo check if this GWC is updated you must go on the main page.<br>\r\n";
	echo $html_footer;
	die;
}

ini_set('display_errors', 1);
error_reporting(-1);

$log = "";
$errors = 0;
$updated = FALSE;
include "../vars.php";

function Error($text)
{
	global $errors; $errors++; return '<strong style="color: red;">'.$text.'</strong>';
}

function check($result)
{
	global $updated; $updated = true;
	if($result) return '<strong style="color: green;">OK</strong>'; else return Error('ERROR').'<br>';
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

function truncate($name)
{
	global $updated; $updated = true;
	$file = fopen($name, 'wb'); if($file !== false) { fclose($file); return '<strong style="color: green;">OK</strong>'; }

	return Error('ERROR');
}

clearstatcache();

if( !file_exists("../".DATA_DIR."/") )
{
	$result = mkdir("../".DATA_DIR."/", 0777);
	$log .= '<div>Creating '.DATA_DIR.'/: '.check($result).'</div>'."\r\n";
}

if( file_exists("../".DATA_DIR."/caches.dat") )
{
	$MY_URL = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];  /* HTTP_HOST already contains port if needed */
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
			  || strpos($line[0], "index.php") == strlen($line[0]) - 9
		)
			$delete = TRUE;
		else
		{

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

function DeleteFile($name)
{
	$full_name = '../'.$name;
	if(file_exists($full_name)) return '<div>Deleting <b>"'.$name.'"</b>: '.check(unlink($full_name)).'</div>'."\r\n";
}

function ValidateSize($name)
{
	$full_name = '../'.$name;
	if(file_exists($full_name))
	{
		if(filesize($full_name) === false) return '<div>'.Error('Unable to check the size: ').'<b>'.$name.'</b></div>'."\r\n";
		if(filesize($full_name) > 1024 * 1024) return '<div>Truncating <b>"'.$name.'"</b> because it is too big: '.truncate($full_name).'</div>'."\r\n";
	}
}



if(file_exists('../index.html')) $log .= DeleteFile('index.htm');
$log .= DeleteFile('admin/index.htm');
$log .= DeleteFile('admin/index.html');
$log .= DeleteFile('log/unsupported_nets.log');
$log .= DeleteFile('log/invalid_ips.log');
$log .= DeleteFile('log/invalid_urls.log');
$log .= DeleteFile('log/unidentified_clients.log');
$log .= DeleteFile('log/old_clients.log');
$log .= DeleteFile('log/invalid_queries.log');
$log .= DeleteFile('log/invalid_leaves.log');

$log .= ValidateSize('data/failed_urls.dat');
$log .= ValidateSize('stats/upd-reqs.dat');
$log .= ValidateSize('stats/upd-bad-reqs.dat');
$log .= ValidateSize('stats/other-reqs.dat');
$log .= ValidateSize('stats/blocked-reqs.dat');

echo $html_header.$log;

if($errors)
{
	echo '<div><br><strong style="color: red;">'.$errors.' ';
	if($errors == 1)
		echo "ERROR";
	else
		echo "ERRORS";
	echo '.</strong></div>',"\r\n";
	echo '<div><strong>You must execute the failed actions manually.</strong></div>';
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
			echo "<b>This file checks only if data files are updated, it doesn't check if the GWC is updated.<br>\r\nTo check if this GWC is updated you must go on the main page.</b><br>\r\n";
		}
	}
	else
		echo "<font color=\"red\">Error during writing of admin/revision.dat</font><br>";
}

if( isset($footer) && $footer != "" ) echo "<br><div>".$footer."</div>";
echo $html_footer;
?>