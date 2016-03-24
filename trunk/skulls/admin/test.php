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

function GetMicrotime()
{
	list($usec, $sec) = explode(' ', microtime(), 2);
	return (float)$usec + (float)$sec;
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
		else return (int)$errno;
	}

	fclose ($fp);
	return true;  /* Opened port */
}

function FsockTest()
{
	$fsock_base = false; $fsock_full = false; $warning = null;

	if(function_exists('fsockopen') && FsockTest1('google.com', 80) && FsockTest1('google.com', 443))
	{
		$fsock_base = true;
		$result = FsockTest2('sourceforge.net', 60000);

		if($result === true) $fsock_full = true;
		elseif($result === false);
		else $warning = 'Unknown result from fsockopen, returned error: '.$result;
	}

	return array($fsock_base, $fsock_full, $warning);
}

function DisplayBool($bool)
{
	return $bool? '<span style="color: green">true</span>' : '<span style="color: red">false</span>';
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

echo "<blockquote>\r\n";

$fsock_result = FsockTest(); if($fsock_result[2] !== null) $fsock_result[2] = ' <strong style="color: orange; font-weight: bolder; cursor: help;" title="'.$fsock_result[2].'">&sup1;</strong>';

echo '<div><b>FSOCK_BASE: ',DisplayBool($fsock_result[0]),'</b></div>',"\r\n";
echo '<div><b>FSOCK_FULL: ',DisplayBool($fsock_result[1]),'</b>',$fsock_result[2],'</div>',"\r\n";

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
echo "<br>";

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