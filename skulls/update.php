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

function IsIPInBlockRange($ip, $cidr_range)
{
	if($cidr_range === "") { if(DEBUG) echo 'Empty line in blocklist.',"\r\n\r\n"; return false; }
	if(strpos($cidr_range, '/') === false) $cidr_range .= '/32';
	$cidr = explode('/', $cidr_range, 2); if(!ctype_digit($cidr[1])) { if(DEBUG) echo 'Invalid CIDR range: ',$cidr_range,"\r\n\r\n"; return false; }
	$cidr[1] = (int)$cidr[1];

	/* Optimization of the single IP blocking */
	if($cidr[1] === 32)
	{
		if(DEBUG > 3)
		{
			echo 'CIDR Range: ',$cidr[0],"\r\n";
			echo 'Wildcard Bits: 0.0.0.0',"\r\n";
			echo 'Start IP: ',$cidr[0],"\r\n";
			echo 'End IP: ',$cidr[0],"\r\n\r\n";
		}
		return $ip === ip2long($cidr[0]);
	}

	$cidr_prefix = CIDRPrefixLength2long($cidr[1]);
	if($cidr_prefix === false) { if(DEBUG) echo 'Invalid CIDR range: ',$cidr_range,"\r\n\r\n"; return false; }

	$ip1 = CIDRCalculateStartOfRange(ip2long($cidr[0]), $cidr[1]);
	$ip2 = ($ip1 | $cidr_prefix);

	if(DEBUG)
	{
		$ok = ($ip1 === ip2long($cidr[0]));
		if(DEBUG > 2 || !$ok)
		{
			if(!$ok) echo 'Start IP do not match.',"\r\n";
			echo 'CIDR Range: ',$cidr_range,"\r\n";
			echo 'Wildcard Bits: ',long2ip($cidr_prefix),"\r\n";
			echo 'Start IP: ',long2ip($ip1),"\r\n";
			echo 'End IP: ',long2ip($ip2),"\r\n\r\n";
		}
	}
	return ($ip1 <= $ip) && ($ip <= $ip2);
}

function IsIPInBlockList($ip)
{
	$ip = ip2long($ip);
	$fp = fopen('./ext/blocklist.dat', 'rb'); if($fp === false) return false;

	while(true)
	{
		$line = fgets($fp, 64); if($line === false) break;

		if(IsIPInBlockRange($ip, rtrim($line)))
		{
			fclose($fp); if(DEBUG) echo 'IP "',long2ip($ip),'" is blocked by: ',rtrim($line),"\r\n\r\n";
			return true;
		}
	}
	fclose($fp);
	return false;
}
?>