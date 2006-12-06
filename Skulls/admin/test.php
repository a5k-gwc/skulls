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

function CheckFunction($function_name)
{
	if(function_exists($function_name))
		echo "<font color=\"green\"><b>OK</b></font><br>";
	else
		echo "<font color=\"red\"><b>MISSING</b></font><br>";
}

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\r\n";
echo "<html><head><title>Test</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>\r\n";
echo "<br><br>";

$php_version = PHP_VERSION;
if((float)$php_version >= 4)
	echo "<b>PHP version: <font color=\"green\">".$php_version."</font></b>";
//elseif((float)$php_version >= 3)
//	echo "<b>PHP version: <font color=\"gold\">".$php_version."</font> (Version of PHP is old, this script may work but it isn't guaranteed)</b>";
else
	echo "<b>PHP version: <font color=\"red\">".$php_version."</font> (Version of PHP is too old)</b>";

echo "<br>\r\n";

echo "<b>PING</b>: ";
if( Ping("www.google.it") || Ping("www.libero.it") || Ping("sourceforge.net") )
	echo "<b><font color=\"green\">OK</font> (Set FSOCKOPEN to 1 in vars.php)</b>";
else
	echo "<b><font color=\"red\">FAILED</font> (Set FSOCKOPEN to 0 in vars.php)</b>";

echo "<br><br>\r\n";

echo "<b>If you see the box below empty you are OK otherwise you must set CONTENT_TYPE_WORKAROUND to 1 in vars.php.</b><br>\r\n";
echo "<iframe src=\"inc.php\"></iframe><br><br>";

echo "<b>Function is_numeric:</b> ";
CheckFunction("is_numeric");
echo "<b>Function ip2long:</b> ";
CheckFunction("ip2long");
echo "<br>";

echo "<b>Function header:</b> ";
CheckFunction("header");
echo "<br>";

echo "<b>Function explode:</b> ";
CheckFunction("explode");
echo "<b>Function implode:</b> ";
CheckFunction("implode");
echo "<b>Function strpos:</b> ";
CheckFunction("strpos");
echo "<b>Function count:</b> ";
CheckFunction("count");
echo "<b>Function strtolower:</b> ";
CheckFunction("strtolower");
echo "<b>Function strtoupper:</b> ";
CheckFunction("strtoupper");
echo "<b>Function rtrim:</b> ";
CheckFunction("rtrim");
echo "<b>Function substr:</b> ";
CheckFunction("substr");
echo "<b>Function strlen:</b> ";
CheckFunction("strlen");
echo "<br>";

echo "<b>Function file:</b> ";
CheckFunction("file");
echo "<b>Function fopen:</b> ";
CheckFunction("fopen");
echo "<b>Function fsockopen:</b> ";
CheckFunction("fsockopen");
echo "<b>Function flock:</b> ";
CheckFunction("flock");
echo "<b>Function fwrite:</b> ";
CheckFunction("fwrite");
echo "<b>Function fgets:</b> ";
CheckFunction("fgets");
echo "<b>Function fputs:</b> ";
CheckFunction("fputs");
echo "<b>Function feof:</b> ";
CheckFunction("feof");
echo "<b>Function rewind:</b> ";
CheckFunction("rewind");
echo "<b>Function fclose:</b> ";
CheckFunction("fclose");
echo "<br>";

echo "<b>Function time:</b> ";
CheckFunction("time");
echo "<b>Function gmdate:</b> ";
CheckFunction("gmdate");
echo "<b>Function date:</b> ";
CheckFunction("date");
echo "<b>Function strtotime:</b> ";
CheckFunction("strtotime");
echo "<b>Function floor:</b> ";
CheckFunction("floor");
echo "<br>";

echo "</body></html>";
?>