<?php

include('pjlink/pjlink.class.php');

$request = $_POST['command'];
$pjlink = new PJLink();
$projectIP = '192.168.86.88';
$port = 4352;
$projectorStatusMessage = '';

switch($request) {
    case 'OFF':
        $pjlink->powerOff($projectIP, '', '60', $port);
        break;
    case 'ON':
        $pjlink->powerOn($projectIP, '', '60', $port);
        break;
    case 'ON':
        $projectorStatusMessage = $pjlink->powerOn($projectIP, '', '60', $port);
        break;
    case 'STATUS':
        $projectorStatusMessage = $pjlink->getPowerState($projectIP, '', '60', $port);
        if ($projectorStatusMessage === 1) {
            $projectorStatusMessage = 'ON';
        }
        if ($projectorStatusMessage === 1) {
            $projectorStatusMessage = 'OFF';
        }
        break;
}

if ($pjlink->error) {
    $projectorStatusMessage = $pjlink->error;
}
