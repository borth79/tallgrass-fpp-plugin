<?php

include('pjlink/pjlink.class.php');
$store = json_decode(file_get_contents($pluginPath . "/store.json"));
$request = $_POST['command'];
$pjlink = new PJLink();
$projectorIP = $store->projectorIp;
$persistentProjector = $store->persistentProjector;
$port = 4352;
$projectorStatusMessage = '';

$projectorStatusMessage = $pjlink->getPowerState($projectorIP, '', '60', $port);
switch($request) {
    case 'OFF':
        $pjlink->powerOff($projectorIP, '', '60', $port);
        break;
    case 'ON':
        $pjlink->powerOn($projectorIP, '', '60', $port);
        break;
}

if ($projectorStatusMessage === '0') {
    $projectorStatusMessage = '<span class="badge badge-pill badge-danger">OFF</span>';
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
