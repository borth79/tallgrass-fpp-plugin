<?php

if ($_REQUEST['submission']) {
    // the form has been submitted

    // syncPlaylist

    $save = [
        'apiKey' => $_REQUEST['apiKey'],
        'autoplayPlaylist' => $_REQUEST['autoplayPlaylist'],
        'fullPlaylist' => $_REQUEST['fullPlaylist'],
        'schedule' => $_REQUEST['fullPlaylist'],
    ];

    $res = file_put_contents($pluginPath . "/store.json", json_encode($save));
} # if
