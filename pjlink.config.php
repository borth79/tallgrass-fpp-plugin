<?php
$store = json_decode(file_get_contents($pluginPath . "/store.json"));
$pjlink = new PJLink();
$projectorIp = $store->projectorIp;
$projectorStatusMessage = '';
