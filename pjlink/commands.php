<?php

include('pjlink.class.php');

$request = $_GET['command'];
$pjlink = new PJLink();
$projectIP = '192.168.86.88';
$port = 4352;

switch($request) {
    case 'OFF':
        $pjlink->powerOff($projectIP, '', '60', $port);
        break;
    case 'ON':
        $pjlink->powerOn($projectIP, '', '60', $port);
        break;
    case 'ON':
        $pjlink->powerOn($projectIP, '', '60', $port);
        break;
    case 'STATUS':
        $pjlink->getPowerState($projectIP, '', '60', $port);
        break;
}

echo "Errors: \n";
print_r($pjlink->error);
echo "\n";
