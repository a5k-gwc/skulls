<?php
function Ping($host_name)
{
	$port = 80;

	$fp = @fsockopen( $host_name, $port );
	if($fp)
	{
		fclose ($fp);
		return TRUE;
	}
	else
		return FALSE;
}

echo "<b><br/><br/><br/>PING: ";

if( Ping("www.google.it") || Ping("www.libero.it") || Ping("sourceforge.net") )
	echo "<font color=\"green\">OK</font><br/>Set FSOCKOPEN to 1.";
else
	echo "<font color=\"red\">FAILED</font><br/>Set FSOCKOPEN to 0.";

echo "<br/><br/><br/>\r\n\r\n";

echo "If you see the box below empty you are OK otherwise you must set CONTENT_TYPE_WORKAROUND to 1.<br/>\r\n";
echo "<iframe src=\"inc.php\" />";
echo "</b>";
?>