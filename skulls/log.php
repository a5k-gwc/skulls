<?php
//
//  Copyright © 2005-2008, 2015 by ale5000
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

function Logging($filename, $detected_pv = null)
{
	global $CLIENT, $VERSION, $NET, $UA;
	$REMOTE_IP = empty($_SERVER['REMOTE_ADDR'])? null : $_SERVER['REMOTE_ADDR'];
	$ACCEPT_ENCODING = empty($_SERVER['HTTP_ACCEPT_ENCODING'])? null : $_SERVER['HTTP_ACCEPT_ENCODING'];
	$FORWARDED_FOR = empty($_SERVER['HTTP_X_FORWARDED_FOR'])? null : $_SERVER['HTTP_X_FORWARDED_FOR'];
	$CLIENT_IP = empty($_SERVER['HTTP_CLIENT_IP'])? null : $_SERVER['HTTP_CLIENT_IP'];

	$line = gmdate('Y/m/d H:i:s').'|'.$detected_pv.'|'.$NET.'|'.$CLIENT.' '.$VERSION.'|'.$ACCEPT_ENCODING.'|'.$UA.'|?'.$_SERVER['QUERY_STRING'].'|'.$REMOTE_IP.'|'.$FORWARDED_FOR.'|'.$CLIENT_IP."\r\n";

	$file = fopen('log/'.$filename.'.log', 'ab');
	if($file === false) return;
	flock($file, LOCK_EX);
	fwrite($file, $line);
	flock($file, LOCK_UN);
	fclose($file);
}
?>