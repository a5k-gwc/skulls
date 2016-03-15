<?php
function PingUDP($cache){
	$splitted_url = explode(":", $cache);

	$GUID = VENDOR."ALPA"."\347\271\061\151\240\205\096\191";

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

	list( , $host_name, $port) = $splitted_url;

	$fp = @fsockopen("udp://".$host_name, $port, $errno, $errstr, (float)TIMEOUT);
	if(!$fp)
	{
		$cache_data = "ERR|".$errno;						// ERR|Error name
		if(DEBUG) echo "D|update|ERROR|".$errno." (".$errstr.")\r\n";
	}
	else
	{
		if( !fwrite($fp, $ping) )
		{
			$cache_data = "ERR|Request error";				// ERR|Error name
			fclose($fp);
		}
		else
		{
			stream_set_timeout($fp, (int)TIMEOUT - 2);
			$line = fread($fp, 2048);
			//if(DEBUG) echo $line."\r\n";

			$info = stream_get_meta_data($fp);
			if(strpos($line, "UDPHC") > -1)
				$type = "uhc";
			elseif(strpos($line, "IPP") > -1)
				$type = "ukhl";
			else
				$type = NULL;
			fclose($fp);

			if($info["timed_out"])
				$cache_data = "ERR|Timeout exceeded";		// ERR|Error name
			elseif($type == "uhc")
				$cache_data = "P|".$host_name."|gnutella";	// P|Name of the GWC|Networks list
			elseif($type == "ukhl")
				$cache_data = "P|".$host_name."|gnutella2";	// P|Name of the GWC|Networks list
			else
				$cache_data = "ERR|No UHC or UKHL";				// ERR|Error name
		}
	}

	if(DEBUG) echo "\r\nD|update|Result: ".$cache_data."\r\n\r\n";
	return $cache_data;
}
?>