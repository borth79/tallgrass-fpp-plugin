<?php

include('pjlink/pjlink.class.php');

$request = $_POST['command'];
$pjlink = new PJLink();
$projectIP = '192.168.86.88';
$port = 4352;
$projectorStatusMessage = '';

$projectorStatusMessage = $pjlink->getPowerState($projectIP, '', '60', $port);
switch($request) {
    case 'OFF':
        $pjlink->powerOff($projectIP, '', '60', $port);
        break;
    case 'ON':
        $pjlink->powerOn($projectIP, '', '60', $port);
        break;
}

if ($projectorStatusMessage === '1') {
    $projectorStatusMessage = '<span class="badge badge-pill badge-success">ON</span>';
}
if ($projectorStatusMessage === '2') {
    $projectorStatusMessage = '<span class="badge badge-pill badge-danger">OFF</span>';
}

if ($pjlink->error) {
    $projectorStatusMessage = $pjlink->error;
}
