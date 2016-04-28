<?php
//  log.php
//
//  Copyright © 2005-2008, 2015-2016  ale5000
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

if( !defined("DATA_DIR") )
	die();
if( !file_exists("log/") )
	mkdir("log/", 0777);

function Logging($filename, $detected_pv = null)
{
	global $CLIENT, $VERSION, $DETECTED_NET, $UA, $ORIGIN;
	$REMOTE_IP = empty($_SERVER['REMOTE_ADDR'])? null : $_SERVER['REMOTE_ADDR'];
	$ACCEPT_ENCODING = empty($_SERVER['HTTP_ACCEPT_ENCODING'])? null : $_SERVER['HTTP_ACCEPT_ENCODING'];
	$X_FORWARDED_FOR = empty($_SERVER['HTTP_X_FORWARDED_FOR'])? null : $_SERVER['HTTP_X_FORWARDED_FOR'];
	$X_CLIENT_IP = empty($_SERVER['HTTP_X_CLIENT_IP'])? null : $_SERVER['HTTP_X_CLIENT_IP'];
	$REFERER = empty($_SERVER['HTTP_REFERER'])? null: $_SERVER['HTTP_REFERER'];

	$line = gmdate("Y/m/d H:i:s").'|'.$detected_pv.'|'.RemoveGarbage($DETECTED_NET).'|'.RemoveGarbage($CLIENT.' '.$VERSION).'|'.RemoveGarbage($ACCEPT_ENCODING).'|'.RemoveGarbage($UA).'|?'.RemoveGarbage($_SERVER['QUERY_STRING']).'|'.RemoveGarbage($REMOTE_IP).'|'.RemoveGarbage($X_FORWARDED_FOR).'|'.RemoveGarbage($X_CLIENT_IP).'|'.RemoveGarbage($ORIGIN).'|'.RemoveGarbage($REFERER).'|'."\r\n";

	$file = fopen('log/'.$filename.'.log', 'ab');
	if($file === false) return;
	flock($file, LOCK_EX);
	fwrite($file, $line);
	flock($file, LOCK_UN);
	fclose($file);
}
?>