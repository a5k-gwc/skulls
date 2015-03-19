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

function Ping($host_name)
{
	if( !function_exists("fsockopen") )
		return FALSE;

	$port = 80;
	$fp = @fsockopen($host_name, $port, $errno, $errstr, 20);
	if($fp)
	{
		fclose ($fp);
		return TRUE;
	}

	return FALSE;
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
if((float)$php_version >= 4.1)
	echo "<b>PHP version: <font color=\"green\">OK</font></b>";
else
	echo "<b>PHP version: <font color=\"red\">".$php_version."</font> (This version of PHP is too old, the minimum version is 4.1)</b>";

echo "<br><br>\r\n";

if( Ping("www.google.com") || Ping("www.libero.it") )
	$ping = TRUE;
else
	$ping = FALSE;

echo "<b><font color=\"blue\">Settings of vars.php</font></b>";
echo "<blockquote>";

echo "<b>FSOCKOPEN:</b> ";
if($ping) echo "<b><font color=\"green\">1</font></b>";
else echo "<b><font color=\"red\">0</font></b>";
echo "<br>\r\n";
echo "<b>CONTENT_TYPE_WORKAROUND:</b> If the box below is empty set the value to <b><font color=\"green\">0</font></b> otherwise you must set the value to <b><font color=\"red\">1</font></b><br>\r\n";
echo "<iframe src=\"inc.php\" height=\"50\" width=\"90\"></iframe><br>\r\n";

echo "</blockquote><br>\r\n";

echo "<b><font color=\"blue\">Needed functions</font></b>";
echo "<blockquote>";

echo "<b>Function ctype_digit:</b> ";
CheckFunction("ctype_digit");
echo "<b>Function ip2long:</b> ";
CheckFunction("ip2long");
echo "<br>";

echo "<b>Function gzdeflate:</b> ";
CheckFunction("gzdeflate");
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