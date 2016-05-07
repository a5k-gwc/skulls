<?php
//  dl.php - Download proxy with resume support used to serve blocklists
//
//  Copyright © 2016  ale5000
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

function Configure()
{
	ini_set('display_errors', '0');
	while(ob_get_level() && ob_end_clean());  /* Disable output buffering if it is active on the server */
	ini_set('default_charset', 'utf-8');  /* Set default charset to UTF-8 */
	$zlib_compr = ini_get('zlib.output_compression'); if(!empty($zlib_compr)) ini_set('zlib.output_compression', '0');
	if(function_exists('apache_setenv')) { apache_setenv('no-gzip', '1'); apache_setenv('dont-vary', '1'); }
	if(function_exists('date_default_timezone_get')) date_default_timezone_set(@date_default_timezone_get());  /* Suppress warnings if the timezone isn't set */
	if(function_exists('header_remove')) header_remove('X-Powered-By');
}

function Initialize()
{
	Configure();

	$SUPPORTED_NETWORKS = array();
	include './vars.php';	
	if(USING_OPENSHIFT_HOSTING && isset($_SERVER['OPENSHIFT_DATA_DIR'])) define('DATA_DIR', $_SERVER['OPENSHIFT_DATA_DIR']); else define('DATA_DIR', './'.DATA_DIR_PATH.'/');
}

function SetStatus($status)
{
	header($_SERVER['SERVER_PROTOCOL'].' '.$status);
}

function GetRange($filesize, &$start_byte, &$end_byte, &$length)
{
	list($range_type, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
	if($range_type !== 'bytes') return false;
	if(strpos($range, ',') !== false) return false;  /* Only single range supported */

	list($start_byte, $end_byte) = explode('-', $range, 2);

	if($end_byte === "") $end_byte = $filesize - 1; elseif(!ctype_digit($end_byte)) return false;
	if($start_byte === "") { $start_byte = $filesize - $end_byte; $end_byte = $filesize - 1; } elseif(!ctype_digit($start_byte)) return false;
	$length = $end_byte - $start_byte + 1;
	if($end_byte >= $filesize || $length < 1) return false;

	return true;
}

function ServeFile2($fp, $filesize)
{
	$bytes_sent = 0;
	if(empty($_SERVER['HTTP_RANGE']))
	{
		SetStatus('200 OK');
		header('Content-Length: '.$filesize);

		flush();
		while(!feof($fp))
		{
			$buf = fread($fp, 8*1024); if($buf === false) break;
			echo $buf; flush(); if(connection_status() !== CONNECTION_NORMAL) break;
			$bytes_sent += strlen($buf);
		}
		return ($bytes_sent === $filesize);
	}
	else
	{
		if(!GetRange($filesize, $start_byte, $end_byte, $length))
		{
			SetStatus('416 Requested Range Not Satisfiable');
			header('Content-Range: bytes */' . $filesize);
			return false;
		}

		SetStatus('206 Partial Content');
		header('Content-Length: '.$length);
		header('Content-Range: bytes '.$start_byte.'-'.$end_byte.'/'.$filesize);

		if(fseek($fp, $start_byte) !== -1)
		{
			flush();
			while(!feof($fp) && $bytes_sent < $length)
			{
				$buf = fread($fp, 1024*8); if($buf === false) break;
				echo $buf; flush(); if(connection_status() !== CONNECTION_NORMAL) break;
				$bytes_sent += strlen($buf);
			}
			return ($bytes_sent === $length);
		}
	}

	return false;
}

function ServeFile($b_id, $in_filename, $req_hash, $req_size, $out_filename)
{
	ignore_user_abort(true); set_time_limit(0);

	if(!file_exists($in_filename) || $req_size !== filesize($in_filename) || ($fp = fopen($in_filename, 'rb')) === false)
	{
		SetStatus('404 Not Found');
		return false;
	}

	header('X-BlockList-UID: "'.$b_id.'"');
	header('Content-Description: P2P BlockList');
	header('Content-Disposition: attachment; filename="'.$out_filename.'"');
	header('Content-Type: application/octet-stream');
	$ts = @filemtime($in_filename); if($ts !== false) header('Last-Modified: '.gmdate('D, d M Y H:i:s', $ts).' GMT');
	header('ETag: "'.sprintf('%x', $req_size).'-'.$req_hash.'"');
	header('Accept-Ranges: bytes');
	header('Cache-Control: max-age=604800');  /* 7 days */

	$success = ServeFile2($fp, $req_size);
	fclose($fp);

	return $success;
}

function Main()
{
	Initialize();

	$ua = empty($_SERVER['HTTP_USER_AGENT'])? "" : $_SERVER['HTTP_USER_AGENT'];
	$b_req_format = empty($_GET['format'])? null : $_GET['format'];
	$b_req_hash = empty($_GET['hash'])? null : $_GET['hash'];
	$b_req_size = empty($_GET['size'])? null : $_GET['size'];

	if(strpos($ua, 'Mozilla') === false && $b_req_hash !== null && $b_req_size !== null && $b_req_format === 'cidr')
	{
		$b_info = file_get_contents(DATA_DIR.'dl/blocklist-'.$b_req_format.'.info');
		list($b_id, $b_rev, $b_author, $b_sha1, /*$b_tiger_tree*/, /* Reserved */, $b_size) = explode('|', $b_info, 8); if($b_author === 'Lord of the Rings') $b_author = 'LOTR';

		if($b_req_hash === $b_sha1 && $b_req_size === $b_size)
		{
			$out_filename = 'P2P-BlockList-'.$b_req_format.'-'.$b_author.'-'.$b_rev.'.dat';
			$result = ServeFile($b_id, DATA_DIR.'dl/blocklist-'.$b_req_format.'.dat', $b_sha1, (int)$b_size, $out_filename);
			return true;
		}
	}

	SetStatus('404 Not Found');
	return false;
}
Main();
?>