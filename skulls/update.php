<?php
//  update.php
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

function IPToNumber($ip)
{
	if($ip === '255.255.255.255') return (int)4294967295;  /* The IP 255.255.255.255 has different return values based on PHP version so we need to uniform this */
	return ip2long($ip);
}

function ToUnsigned($number)
{
	return (float)sprintf('%u', $number);
}

function CIDRPrefixLength2long($cidr)
{
	if($cidr < 1 || $cidr > 32) return false;
	return (pow(2, 32 - $cidr) - 1);
}

function CIDRCalculateStartOfRange($ip, $cidr)
{
	$bits_to_zero_fill = 32 - $cidr;
	return ($ip >> $bits_to_zero_fill << $bits_to_zero_fill);
}

function IsIPInRange($ip, $cidr_range)
{
	if(strpos($cidr_range, '/') === false) { if(DEBUG > 2) echo 'CIDR Range: ',$cidr_range,"\r\n\r\n"; return $ip === IPToNumber($cidr_range); }

	$cidr = explode('/', $cidr_range, 2); if(!ctype_digit($cidr[1])) { if(DEBUG) echo 'Invalid CIDR range: ',$cidr_range,"\r\n\r\n"; return false; }
	$cidr[1] = (int)$cidr[1];

	$cidr_prefix = CIDRPrefixLength2long($cidr[1]);
	if($cidr_prefix === false) { if(DEBUG) echo 'Invalid CIDR range: ',$cidr_range,"\r\n\r\n"; return false; }

	$ip1 = CIDRCalculateStartOfRange(IPToNumber($cidr[0]), $cidr[1]);
	$ip2 = ($ip1 | $cidr_prefix);
	$ip = ToUnsigned($ip); $ip1 = ToUnsigned($ip1); $ip2 = ToUnsigned($ip2);

	if(DEBUG)
	{
		$valid = (long2ip($ip1) === $cidr[0]);
		if(DEBUG > 2 || !$valid)
		{
			if(!$valid) echo 'Start IP do not match.',"\r\n";
			echo 'CIDR Range: ',$cidr_range,"\r\n";
			echo 'Wildcard Bits: ',long2ip($cidr_prefix),"\r\n";
			echo 'Start IP: ',long2ip($ip1),' (',$ip1,')',"\r\n";
			echo 'End IP: ',long2ip($ip2),' (',$ip2,')',"\r\n\r\n";
		}
	}
	return ($ip1 <= $ip) && ($ip <= $ip2);
}

function IsIPInBlockList($ip)
{
	if(!USE_GWC_BLOCKLIST) return false;
	$ip = IPToNumber($ip);
	$fp = fopen('./ext/gwc-blocklist.dat', 'rb'); if($fp === false) return true;
	if(fgets($fp, 512) !== false)  /* Skip first line, it contains only informations */
	{
		while(true)
		{
			$line = fgets($fp, 32); if($line === false) break;
			$line = rtrim($line);
			if($line === "") continue;

			if(IsIPInRange($ip, $line))
			{
				fclose($fp); if(DEBUG) echo 'IP "',long2ip($ip),'" is blocked by: ',$line,"\r\n\r\n";
				return true;
			}
		}
	}
	fclose($fp);
	return false;
}

function IsCloudFlareIP($ip)
{
	$ip = IPToNumber($ip);
	$fp = fopen('./ext/cloudflare-ips.dat', 'rb'); if($fp === false) return false;
	while(true)
	{
		$line = fgets($fp, 32); if($line === false) break;
		$line = rtrim($line);
		if($line === "") continue;

		if(IsIPInRange($ip, $line))
		{
			fclose($fp); if(DEBUG) echo 'IP "',long2ip($ip),'" is CloudFlare IP by line : ',$line,"\r\n\r\n";
			return true;
		}
	}
	fclose($fp);
	return false;
}
?>