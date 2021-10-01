<?php

use ProjectorController;

// $pluginPath - provided by FPP
$storeFile = 'store.json';

$request = (object) [
    'controller' => $_REQUEST['controller'],
    'method' => $_REQUEST['status'],
];

if ($request->controller === 'projector') {
    try {
        $controller = new ProjectorController($pluginPath, $storeFile);
        $controller->{$request->method}();
    } catch (Exception $exception) {
        exit(json_encode(['status' => 'error', 'message' => $exception->getMessage()]));
    }

}