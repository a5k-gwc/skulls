<?php
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

/*
This product includes GeoLite data created by MaxMind, available from http://www.maxmind.com/
Database download: http://dev.maxmind.com/geoip/legacy/geolite/
Used GeoLite Country and GeoLite ASN.

This product includes country flag icons created by Mark James.
Flag icons download: http://www.famfamfam.com/lab/icons/flags/
*/

/*
NOTE: If you use a version of the GeoIP PECL extension older than 1.1.0 it may ignore the path of the database configured by ini_set and use the default path;
in this case you have to manually configure the path inside php.ini or copy the database to the correct folder.
*/
class GeoIPWrapper
{
	/* Private */
	var $enabled = false;
	var $is_pecl = false;
	var $curr_dir = null;
	var $api_handle = null;
	var $db_ver = null;
	var $db_cr = "";


	/* Private */
	function _InitPECL()
	{
		/* GeoIP PECL manual: http://php.net/manual/book.geoip.php */
		if(function_exists('geoip_country_code_by_name'))  /* It needs the GeoIP PECL extension >= 0.2.0 installed on the server */
			if(ini_set('geoip.custom_directory', $this->curr_dir.'/../ext') !== false)
			{
				$this->is_pecl = true;
				return true;
			}

		return false;
	}

	function _InitAPI()
	{
		/* MaxMind GeoIP Legacy PHP API: http://github.com/maxmind/geoip-api-php */
		$path = $this->curr_dir.'/api/geoip.inc';
		if(!file_exists($path))
			return false;

		include_once $path;
		$this->api_handle = geoip_open($this->curr_dir.'/../ext/GeoIP.dat', GEOIP_STANDARD);
		if($this->api_handle === null)
			{ trigger_error('GeoIPWrapper - Initialization of API failed', E_USER_ERROR); return false; }

		return true;
	}

	function _GetDBInfoPECL()
	{
		$db_info = @geoip_database_info(GEOIP_COUNTRY_EDITION);
		if($db_info === null) return false;

		return htmlentities($db_info, ENT_QUOTES, 'ISO-8859-1');
	}

	function _GetDBInfoDirectlyFromFile()  /* This code will possibly break in the future but I haven't found a better way to get the DB version */
	{
		$read_data = false; $null = "\0"; $max_length = 200;
		$fp = @fopen($this->curr_dir.'/../ext/GeoIP.dat', 'rb');

		if($fp !== false)
		{
			if(fseek($fp, -$max_length, SEEK_END) !== -1)
				$read_data = fread($fp, $max_length);
			fclose($fp);
		}

		if($read_data === false || strlen($read_data) !== $max_length)
			return false;

		$i = $max_length;
		while($i-- > 0)
			if($read_data[$i] === $null)
				break;

		return htmlentities(substr($read_data, $i+1), ENT_QUOTES, 'ISO-8859-1');
	}

	function _GetDBVersionAndCopyright()
	{
		if($this->db_ver === null)
		{
			if($this->is_pecl)
				$db_info = $this->_GetDBInfoPECL();
			else
				$db_info = $this->_GetDBInfoDirectlyFromFile();

			if($db_info === false) return false;
			$db_info = str_replace('(c)', '&copy;', $db_info);

			$cr_pos = strpos($db_info, 'Copyright');
			if($cr_pos !== false)
			{
				$this->db_ver = rtrim(substr($db_info, 0, $cr_pos));
				$this->db_cr = rtrim(substr($db_info, $cr_pos));
			}
			else
				$this->db_ver = rtrim($db_info);
		}

		return true;
	}


	/* Constructor */
	function __construct()
	{
		$this->curr_dir = dirname(__FILE__);

		if($this->_InitPECL() || $this->_InitAPI())
			$this->enabled = true;
	}
	function GeoIPWrapper() { $this->__construct(); }

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
			$this->db_ver = null;
			$this->db_cr = "";
		}
	}

	/* Public */
	function IsEnabled()
	{
		return $this->enabled;
	}

	function GetType()
	{
		if($this->enabled)
		{
			if($this->is_pecl)
				return 'PECL extension';
			else
				return 'Legacy PHP API';
		}

		return 'Missing';
	}

	function GetDBVersion()
	{
		if(!$this->enabled) return "";

		if($this->_GetDBVersionAndCopyright())
			return $this->db_ver;

		return 'Missing DB';
	}

	function GetDBCopyright()
	{
		if($this->enabled && $this->_GetDBVersionAndCopyright())
			return $this->db_cr;

		return "";
	}

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

	/* Only PECL for now */
	function GetASNByIP($ip)
	{
		if(!$this->enabled) return null;
		if($this->is_pecl)
			if(function_exists('geoip_asnum_by_name'))
				return htmlentities(@geoip_asnum_by_name($ip), ENT_QUOTES, 'ISO-8859-1');

		return null;
	}
}
?>