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

/*
This product includes GeoLite data created by MaxMind, available at http://www.maxmind.com/
Database download: http://dev.maxmind.com/geoip/legacy/geolite/ 

This product includes country flag icons created by Mark James, available at http://www.famfamfam.com/
Flag icons download: http://www.famfamfam.com/lab/icons/flags/
*/

class GeoIPWrapper
{
	/* Private */
	var $enabled = false;
	var $is_pecl = false;
	var $curr_dir = null;
	var $api_handle = null;


	/* Private */
	function _InitPECL()
	{
		/* GeoIP PECL manual: http://php.net/manual/book.geoip.php */
		if(function_exists('geoip_country_code_by_name'))  /* It needs the GeoIP PECL extension >= 0.2.0 installed on the server */
			if(ini_set('geoip.custom_directory', $this->curr_dir) !== false)
			{
				$this->is_pecl = true;
				return true;
			}

		return false;
	}

	function _InitAPI()
	{
		/* MaxMind GeoIP Legacy PHP API: http://github.com/maxmind/geoip-api-php */
		include_once $this->curr_dir.'/api/geoip.inc';

		$this->api_handle = geoip_open($this->curr_dir.'/GeoIP.dat', GEOIP_STANDARD);
		if($this->api_handle !== null)
			return true;

		trigger_error('GeoIPWrapper - Initialization of API failed', E_USER_ERROR);
		return false;
	}


	/* Constructor */
	function GeoIPWrapper()
	{
		$this->curr_dir = dirname(__FILE__);

		if($this->_InitPECL() || $this->_InitAPI())
			$this->enabled = true;
	}

	/* Destructor (manually executed) */
	function Destroy()
	{
		if($this->enabled)
		{
			if(!$this->is_pecl)
			{
				if($this->api_handle === null)
					trigger_error('GeoIPWrapper - The handle to destroy is already null', E_USER_WARNING);
				elseif(!geoip_close($this->api_handle))
					trigger_error('GeoIPWrapper - Destroy failed', E_USER_ERROR);
			}
			$this->enabled = false;
			$this->is_pecl = false;
			$this->api_handle = null;
		}
		else
			trigger_error('GeoIPWrapper - Duplicate destroy call', E_USER_WARNING);
	}

	/* Public */
	function GetCountryNameByIP($ip)
	{
		if(!$this->enabled) return null;
		if($this->is_pecl)
			$name = @geoip_country_name_by_name($ip);
		else
			$name = geoip_country_name_by_addr($this->api_handle, $ip);

		if($name !== false) return $name;
		return 'Unknown';
	}

	function GetCountryCodeByIP($ip)
	{
		if(!$this->enabled) return null;
		if($this->is_pecl)
			$code = @geoip_country_code_by_name($ip);
		else
			$code = geoip_country_code_by_addr($this->api_handle, $ip);

		if($code !== false) return $code;
		return "";
	}

	function GetCountryFlag($code)
	{
		if(!$this->enabled) return null;
		if($code !== "")
		{
			$path = '/flags/'.strtolower($code).'.png';
			if(file_exists($this->curr_dir.$path))
				return 'geoip'.$path;
		}

		return 'geoip/flags/unknown.png';
	}
}
?>