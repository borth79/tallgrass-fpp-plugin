<?php
require_once "globals.php";

if ($_REQUEST['submission']) {
    // the form has been submitted

    // syncPlaylist

    $save = [
        'apiKey' => $_REQUEST['apiKey']
    ];
    $res = file_get_contents($pluginPath . "/store.json", json_encode($save));
} # if
