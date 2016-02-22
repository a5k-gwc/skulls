<?php
header($_SERVER['SERVER_PROTOCOL'].' 200 OK'); list(,$prot_ver) = explode('/', $_SERVER['SERVER_PROTOCOL'], 2);
header('Content-Type: text/plain');
if($prot_ver >= 1.1) header('Cache-Control: no-cache'); else header('Pragma: no-cache');

echo "\r\n";
?>