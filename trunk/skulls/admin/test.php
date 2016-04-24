<?php
//
//  Copyright © 2005-2008, 2015-2016 by ale5000
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

include '../vars.php';
ini_set('display_errors', '1'); error_reporting(~0);

function GetMicrotime()
{
	list($usec, $sec) = explode(' ', microtime(), 2);
	return (float)$usec + (float)$sec;
}

if(function_exists('date_default_timezone_get')) date_default_timezone_set(@date_default_timezone_get());
function FormatDate($timestamp)
{
	return gmdate('Y/m/d H:i:s', $timestamp);
}

function GetTimestamp($date)
{
	return strtotime($date.'UTC');
}

function FsockTest1($hostname, $port)
{
	$fp = @fsockopen('tcp://'.$hostname, $port, $errno, $errstr, 5); if($fp === false) return false;  /* Closed or unreachable port */
	fclose($fp);
	return true;  /* Opened port */
}

function FsockTest2($hostname, $port)
{
	//$start = GetMicrotime();

	$fp = @fsockopen('tcp://'.$hostname, $port, $errno, $errstr, 5);
	if($fp === false)
	{
		/* if(GetMicrotime()-$start > 4.9) */
		if($errno === 111 || $errno === 10061) return true;  /* Closed port but reachable */
		elseif($errno === 110 || $errno === 10060) return false;  /* Unreachable port (connection timed out) */
		else return (string)$errno;
	}

	fclose ($fp);
	return true;  /* Opened port */
}

function FsockTest()
{
	$fsock_base = 0; $fsock_full = 0; $warning = ""; $now = time();

	$cache_file = '../'.DATA_DIR.'/detection-cache.dat';
	if(file_exists($cache_file))
	{
		$file = file_get_contents($cache_file);
		if(!empty($file))
		{
			$file_array = explode('|', $file, 5); unset($file);
			if(GetTimestamp($file_array[0]) > $now - 6 * 60 * 60)
				return array($file_array[0], (int)$file_array[1], (int)$file_array[2], $file_array[3]);
		}
	}

	if(function_exists('fsockopen') && FsockTest1('google.com', 80) && FsockTest1('google.com', 443))
	{
		$fsock_base = 1; $fsock_full = -1;
		if(FsockTest1('skulls.sourceforge.net', 80))
		{
			$result = FsockTest2('skulls.sourceforge.net', 60000);

			if($result === true) $fsock_full = 1;
			elseif($result === false) $fsock_full = 0;
			else $warning = 'Unknown result from fsockopen, returned error: '.$result;
		}
		else
			$warning = 'There is a problem on the SourceForge server, retry later.';
	}

	$fp = fopen($cache_file, 'wb');
	if($fp !== false)
	{
		flock($fp, LOCK_EX);
		fwrite($fp, FormatDate($now).'|'.$fsock_base.'|'.$fsock_full.'|'.$warning.'|');
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	return array(FormatDate($now), $fsock_base, $fsock_full, $warning);
}

function DisplayTristate($val)
{
	if($val === -1) return '<span style="color: yellow">Unknown</span>';
	return ($val? '<span style="color: green">true</span>' : '<span style="color: red">false</span>');
}

function CheckFunction($function_name)
{
	if(function_exists($function_name))
		echo "<font color=\"green\"><b>OK</b></font><br>";
	else
		echo "<font color=\"red\"><b>MISSING</b></font><br>";
}

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\r\n";
echo "<html><head><title>Test</title><meta name=\"robots\" content=\"noindex,nofollow,noarchive\"><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>\r\n";
echo "<br><br>";

$php_version = PHP_VERSION;
if((float)$php_version >= 4.3)
	echo "<b>PHP version: <font color=\"green\">OK</font></b>";
else
	echo "<b>PHP version: <font color=\"red\">".$php_version."</font> (This version of PHP is too old, the minimum version is 4.3)</b>";

echo "<br><br>\r\n";

echo "<div><b><big><font color=\"blue\">Detected settings</font></big></b></div>\r\n";
echo '<div><i><small>Here you will see the settings that you should set in vars.php based on some tests on your server.</small></i></div>';
echo '<div><i><small>The server must be connected to Internet otherwise the tests won\'t give the correct results.</small></i></div>';
$fsock_result = FsockTest(); if($fsock_result[3] !== "") $fsock_result[3] = ' <strong style="color: orange; font-weight: bolder; cursor: help;" title="'.$fsock_result[2].'">&sup1;</strong>';
echo '<div><b><small>Last check: ',$fsock_result[0],' UTC</small></b></div>',"\r\n";

echo "<blockquote>\r\n";

echo '<div><b>FSOCK_BASE: ',DisplayTristate($fsock_result[1]),'</b></div>',"\r\n";
echo '<div><b>FSOCK_FULL: ',DisplayTristate($fsock_result[2]),'</b>',$fsock_result[3],'</div>',"\r\n";

echo "<b>CONTENT_TYPE_WORKAROUND:</b> If the box below is empty then it is OK and you must set the value to <b><font color=\"green\">false</font></b> otherwise it means that your server interfere with the script and you must set the value to <b><font color=\"red\">true</font></b> to workaround the problem.<br>\r\n";
echo "<iframe src=\"inc.php\" height=\"60\" width=\"300\"></iframe><br>\r\n";

echo "</blockquote><br>\r\n";


echo "<div><b><big><font color=\"blue\">Needed functions</font></big></b></div>\r\n";
echo "<blockquote>";

echo "<b>Function ctype_digit:</b> ";
CheckFunction("ctype_digit");
echo "<b>Function ip2long:</b> ";
CheckFunction("ip2long");
echo "<br>";

echo "<b>Function gzcompress:</b> ";
CheckFunction("gzcompress");
echo "<b>Function ob_start:</b> ";
CheckFunction("ob_start");
echo "<b>Function ob_end_flush:</b> ";
CheckFunction("ob_end_flush");
echo "<b>Function header:</b> ";
CheckFunction("header");
echo "<br>";

echo "<b>Function explode:</b> ";
CheckFunction("explode");
echo "<b>Function implode:</b> ";
CheckFunction("implode");
echo "<b>Function strpos:</b> ";
CheckFunction("strpos");
echo "<b>Function count:</b> ";
CheckFunction("count");
echo "<b>Function strtolower:</b> ";
CheckFunction("strtolower");
echo "<b>Function strtoupper:</b> ";
CheckFunction("strtoupper");
echo "<b>Function rtrim:</b> ";
CheckFunction("rtrim");
echo "<b>Function substr:</b> ";
CheckFunction("substr");
echo "<b>Function strlen:</b> ";
CheckFunction("strlen");
echo "<b>Function min:</b> ";
CheckFunction("min");
echo "<b>Function max:</b> ";
CheckFunction("max");
echo "<br>";

echo "<b>Function file_get_contents:</b> ";
CheckFunction("file_get_contents");
echo "<b>Function file:</b> ";
CheckFunction("file");
echo "<b>Function fopen:</b> ";
CheckFunction("fopen");
echo "<b>Function flock:</b> ";
CheckFunction("flock");
echo "<b>Function fgets:</b> ";
CheckFunction("fgets");
echo "<b>Function fwrite:</b> ";
CheckFunction("fwrite");
echo "<b>Function feof:</b> ";
CheckFunction("feof");
echo "<b>Function rewind:</b> ";
CheckFunction("rewind");
echo "<b>Function fclose:</b> ";
CheckFunction("fclose");
echo "<br>";

echo "<b>Function time:</b> ";
CheckFunction("time");
echo "<b>Function gmdate:</b> ";
CheckFunction("gmdate");
echo "<b>Function date:</b> ";
CheckFunction("date");
echo "<b>Function strtotime:</b> ";
CheckFunction("strtotime");
echo "<b>Function floor:</b> ";
CheckFunction("floor");
echo "<br>";

echo "</blockquote><br>\r\n";

echo "</body></html>";
?>