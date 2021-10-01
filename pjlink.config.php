<?php
$store = json_decode(file_get_contents($pluginPath . "/store.json"));
$pjlink = new PJLink();
$projectorIP = $store->projectorIp;
$port = 4352;
$projectorStatusMessage = '';
