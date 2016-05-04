<?php
//  admin/common.php
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

function Configure()
{
	error_reporting(~0); ini_set('display_errors', '1');
	ini_set('default_charset', 'utf-8');  /* Set default charset to UTF-8 */
	if(function_exists('date_default_timezone_get')) date_default_timezone_set(@date_default_timezone_get());  /* Suppress warnings if the timezone isn't set */
	if(function_exists('header_remove')) header_remove('X-Powered-By');
}

function InitializeVars()
{
	Configure();

	$SUPPORTED_NETWORKS = array();
	include '../vars.php';
	define('DATA_DIR', '../'.DATA_DIR_PATH.'/');
}

function GetMicrotime()
{
	list($usec, $sec) = explode(' ', microtime(), 2);
	return (float)$usec + (float)$sec;
}

function FormatDate($ts)
{
	return gmdate('Y/m/d H:i:s', $ts);
}

function GetTimestamp($date)
{
	if($date === "") return false;
	$ts = strtotime($date.' UTC'); if($ts === -1) return false;
	return $ts;
}
?>