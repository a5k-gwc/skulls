<?php
//
//  Copyright © 2005-2008, 2015-2016 by ale5000
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

function PingUDP($cache){
	$splitted_url = explode(':', $cache);

	$GUID = VENDOR.'BETA'."\347\271\061\151\240\205\096\191";

	if($splitted_url[0] == "uhc")
	{
		$payload_type = "\000";	// 0x00 PING
		$TTL = "\001";
		$hops = "\000";
		$payload_length = "\007\000\000\000";
		$GGEP_magic_byte = "\303"; // 0xC3
		$GGEP = "SCP";
		$ping = $GUID.$payload_type.$TTL.$hops.$payload_length.$GGEP_magic_byte."\203".$GGEP."\101\000";
	}
	elseif($splitted_url[0] == "ukhl")
	{
		$ping = $GUID."GND"."\000\044\004\001\001\130\000"."KHLR";
	}
	else
		return "ERR|Unsupported UDP prefix";				// ERR|Error name

	if(count($splitted_url) != 3)
		return "ERR|No port in URL";						// ERR|Error name

	list(, $hostname, $port) = $splitted_url;

	$fp = @fsockopen('udp://'.IDN_Encode($hostname), $port, $errno, $errstr, (float)CONNECT_TIMEOUT);
	if(!$fp)
	{
		$cache_data = "ERR|".$errno;						// ERR|Error name
		if(DEBUG) echo "D|update|ERROR|".$errno." (".$errstr.")\r\n";
	}
	else
	{
		if(fwrite($fp, $ping) === false)
		{
			$cache_data = "ERR|Request error";				// ERR|Error name
			fclose($fp);
		}
		else
		{
			stream_set_timeout($fp, TIMEOUT);
			$type = null;
			$data = fread($fp, 8192);
			if(DEBUG) echo 'D|',bin2hex($data),"\r\n";

			if(strpos($data, 'UDPHC') !== false) $type = "uhc";
			elseif(strpos($data, 'IPP') !== false) $type = "ukhl";

			$info = stream_get_meta_data($fp);
			fclose($fp);

			if($info['timed_out'])
				$cache_data = "ERR|Timeout exceeded";		// ERR|Error name
			elseif($type == "uhc")
				$cache_data = "P|".$hostname."|gnutella";	// P|Name of the GWC|Networks list
			elseif($type == "ukhl")
				$cache_data = "P|".$hostname."|gnutella2";	// P|Name of the GWC|Networks list
			else
				$cache_data = "ERR|No UHC or UKHL";				// ERR|Error name
		}
	}

	if(DEBUG) echo "\r\nD|update|Result: ".$cache_data."\r\n\r\n";
	return $cache_data;
}
?>