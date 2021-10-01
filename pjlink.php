<?php

include('pjlink/pjlink.class.php');
$store = json_decode(file_get_contents($pluginPath . "/store.json"));
$request = $_POST['command'];
$pjlink = new PJLink();
$projectorIp = $store->projectorIp;
$persistentProjector = $store->persistentProjector;
$projectorStatusMessage = '';

$projectorStatusMessage = $pjlink->getPowerState($projectorIp, '', '5', $store->projectorPort);
switch($request) {
    case 'OFF':
        $pjlink->powerOff($projectorIp, '', '60', $store->projectorPort);
        break;
    case 'ON':
        $pjlink->powerOn($projectorIp, '', '60', $store->projectorPort);
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
if ($projectorStatusMessage === '3') {
    $projectorStatusMessage = '<span class="badge badge-pill badge-warning">WARMING UP</span>';
}

if ($pjlink->error) {
    $projectorStatusMessage = $pjlink->error;
}
