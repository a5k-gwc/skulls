<?php
//
//  Copyright (C) 2005-2008, 2015-2016 by ale5000
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

define('REVISION', '4.9.1');

header($_SERVER['SERVER_PROTOCOL'].' 200 OK'); list(,$prot_ver) = explode('/', $_SERVER['SERVER_PROTOCOL'], 2);
if($prot_ver >= 1.1) header('Cache-Control: no-cache'); else header('Pragma: no-cache');

if(file_exists('./revision.dat'))
	$file_content = file('./revision.dat');

if( !isset($file_content[0]) )
	$file_content[0] = 0;

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'."\r\n";
$html_header = '<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>Data update</title><meta name="robots" content="noindex, follow, noarchive, noimageindex"></head><body>'."\r\n";
$html_footer = '</body></html>'."\r\n";

if(rtrim($file_content[0]) === REVISION)
{
	echo $html_header;
	echo '<div>There is no need to update it.<br>This file checks only if data files are updated, it doesn\'t check if the GWC is updated.<br>To check if this GWC is updated you must go on the main page.</div>'."\r\n";
	echo $html_footer;
	die;
}

ini_set('display_errors', '1');
error_reporting(-1);

$log = "";
$errors = 0;
$updated = FALSE;
include '../vars.php';

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
			if($item !== '.' && $item !== '..')
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

if( !file_exists('../'.DATA_DIR.'/') )
{
	$result = mkdir('../'.DATA_DIR.'/', 0777);
	$log .= '<div>Creating <b>'.DATA_DIR.'/</b> directory: '.check($result).'</div>'."\r\n";
}

$gwc_name = DATA_DIR.'/alt-gwcs.dat';
$gwc_full_name = '../'.$gwc_name;
if( file_exists($gwc_full_name) )
{
	$MY_URL = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];  /* HTTP_HOST already contains port if needed */
	$MY_URL = strtolower(str_replace('/admin/update.php', '/skulls.php', $MY_URL));
	$cache_file = file($gwc_full_name);
	$count_cache = count($cache_file);

	$changed = FALSE;
	$urls_array = array();
	for($i = 0; $i < $count_cache; $i++)
	{
		$delete = FALSE;
		$line = explode('|', rtrim($cache_file[$i]));

		if(!isset($line[14]))
			$delete = TRUE;
		else
		{
			$gwc_url = $line[3]; $gwc_url_LC = strtolower($gwc_url);
			if(isset($urls_array[$gwc_url_LC]) && $urls_array[$gwc_url_LC] === true)  /* Duplicate URL */
				$delete = TRUE;
			elseif(strpos($gwc_url, '?') !== false || strpos($gwc_url, '&') !== false || strpos($gwc_url, '#') !== false || strpos($gwc_url, '/index.php') === strlen($gwc_url) - 10)
				$delete = TRUE;
			else
			{
				if(strpos($gwc_url, '://') !== false)
				{
					list(, $cache) = explode('://', $gwc_url, 2);

					if(strpos($cache, '/') !== false)
						list($host, $path) = explode('/', $cache, 2);
					else
						$host = $cache;

					if(strtolower($host) !== $host || strtolower($cache) === $MY_URL || strpos($path, '.php/') !== false)
						$delete = TRUE;
				}
				else
				{
					$delete = TRUE;
					$log .= '<div><strong style="color: red;">Strange url</strong> in <b>'.$gwc_name.'</b> file removed: '.$gwc_url.'</div>'."\r\n";
				}
			}
		}

		if($delete)
		{
			$changed = true;
			$data[$i] = "";
		}
		else
		{
			$urls_array[$gwc_url_LC] = true;
			$data[$i] = implode('|', $line);
		}
		unset($line);
	}

	$file = fopen($gwc_full_name, 'wb');
	if($file !== false)
	{
		flock($file, LOCK_EX);
		for($i = 0; $i < $count_cache; $i++)
		{
			$data[$i] = rtrim($data[$i]);
			if($data[$i] !== "")
				fwrite($file, $data[$i]."\r\n");
		}
		flock($file, LOCK_UN);
		fclose($file);
	}
	else Error('');

	if($changed)
	{
		$log .= '<div>Internal structure updated in <b>'.$gwc_name.'</b> file.</div>'."\r\n";
		$updated = TRUE;
	}
}

function DeleteFile($name)
{
	$full_name = '../'.$name;
	if(file_exists($full_name)) return '<div>Deleting <b>'.$name.'</b> file: '.check(unlink($full_name)).'</div>'."\r\n";
}

function ValidateSize($name)
{
	$full_name = '../'.$name;
	if(file_exists($full_name))
	{
		if(filesize($full_name) === false) return '<div>'.Error('Unable to check the size').' of <b>'.$name.'</b> file.</div>'."\r\n";
		if(filesize($full_name) > 1024 * 1024) return '<div>Truncating <b>'.$name.'</b> file because it is too big: '.truncate($full_name).'</div>'."\r\n";
	}
}

$log .= DeleteFile(DATA_DIR.'/caches.dat');
$log .= DeleteFile('log/unsupported_nets.log');
$log .= DeleteFile('log/invalid_ips.log');
$log .= DeleteFile('log/invalid_urls.log');
$log .= DeleteFile('log/unidentified_clients.log');
$log .= DeleteFile('log/old_clients.log');
$log .= DeleteFile('log/invalid_queries.log');
$log .= DeleteFile('log/invalid_leaves.log');
$log .= DeleteFile('admin/index.html');
$log .= DeleteFile('admin/index.htm');
if(file_exists('../index.html')) $log .= DeleteFile('index.htm');

$log .= ValidateSize('data/failed_urls.dat');
$log .= ValidateSize('stats/upd-reqs.dat');
$log .= ValidateSize('stats/upd-bad-reqs.dat');
$log .= ValidateSize('stats/other-reqs.dat');
$log .= ValidateSize('stats/blocked-reqs.dat');

echo $html_header.$log;

if($errors)
{
	echo '<div><br><strong style="color: red;">'.$errors.' ';
	if($errors === 1)
		echo 'ERROR';
	else
		echo 'ERRORS';
	echo '.</strong></div>',"\r\n";
	echo '<div><strong>You must execute the failed actions manually.</strong></div>';
}
else
{
	$file = fopen('./revision.dat', 'wb');
	if($file !== FALSE)
	{
		flock($file, 2);
		fwrite($file, REVISION);
		flock($file, 3);
		fclose($file);

		if($updated)
			echo '<div><br><strong style="color: green;">Updated correctly.</strong></div>',"\r\n";
		else
		{
			echo '<div><strong style="color: green;">Already updated.</strong></div>',"\r\n";
			echo '<div><b>This file checks only if data files are updated, it doesn\'t check if the GWC is updated.<br>To check if this GWC is updated you must go on the main page.</b></div>'."\r\n";
		}
	}
	else
		echo '<div style="color: red;">Error during writing of <b>admin/revision.dat</b> file.</div>';
}

if(!empty($footer)) echo '<div><br>'.$footer.'</div>';
echo $html_footer;
?>