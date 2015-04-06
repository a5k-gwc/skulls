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

/* This product includes GeoLite data created by MaxMind, available from http://www.maxmind.com */
/* Database: http://dev.maxmind.com/geoip/legacy/geolite/ */
/* Manual: http://php.net/manual/book.geoip.php */

function InitializeGeoIP()
{
	if(function_exists('geoip_country_code_by_name'))  /* It needs the PECL geoip extension >= 0.2.0 installed on the server */
		if(ini_set('geoip.custom_directory', dirname(__FILE__)) !== false)
			return true;

	return false;
}

function GeoIPGetCountryName($ip_or_hostname)
{
	$country_name = @geoip_country_name_by_name($ip_or_hostname);
	if($country_name !== false)
		return $country_name;

	return 'Unknown';
}

function GeoIPGetCountryCode($ip_or_hostname)
{
	$country_code = @geoip_country_code_by_name($ip_or_hostname);
	if($country_code !== false)
		return $country_code;

	return false;
}

function GeoIPGetCountryFlag($country_code)  /* Flag icons: http://www.famfamfam.com/lab/icons/flags/ */
{
	if($country_code !== false)
	{
		$path = 'geoip/flags/'.strtolower($country_code).'.png';
		if(file_exists($path))
			return $path;
	}

	return 'geoip/flags/unknown.png';
}
?>