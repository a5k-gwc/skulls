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

if( !defined("DATA_DIR") )
	die();
if( !file_exists("log/") )
	mkdir("log/", 0777);

function Logging($filename, $CLIENT, $VERSION, $NET)
{
	global $UA_ORIGINAL;
	$HTTP_X_FORWARDED_FOR = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : "";
	$HTTP_CLIENT_IP = isset($_SERVER["HTTP_CLIENT_IP"]) ? $_SERVER["HTTP_CLIENT_IP"] : "";

	$file = fopen("log/".$filename.".log", "ab");
	flock($file, 2);
	fwrite($file, gmdate("Y/m/d H:i:s")." | ".$CLIENT." ".$VERSION." | ".$UA_ORIGINAL." | ".$NET." | ".$_SERVER["QUERY_STRING"]." | ".$_SERVER["REMOTE_ADDR"]." | ".$HTTP_X_FORWARDED_FOR." | ".$HTTP_CLIENT_IP."\r\n");
	flock($file, 3);
	fclose($file);
}
?>