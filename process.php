<?php
global $errors;

$store = json_decode(file_get_contents($pluginPath . "/store.json"));
$options = [
    'http' => [
        'method'  => 'GET',
    ]
];
$context = stream_context_create( $options );

if ($_REQUEST['submission']) {
    // the form has been submitted

    // syncPlaylist

    $save = [
        'apiKey' => $_REQUEST['apiKey'],
        'autoplayPlaylist' => $_REQUEST['autoplayPlaylist'],
        'fullPlaylist' => $_REQUEST['fullPlaylist'],
    ];

    $schedules = getSchedules();

    // process schedule
    $selectedSchedule = null;
    foreach ($schedules as $schedule) {
        if ($schedule['enabled'] !== 1) {
            continue;
        }
        if ($store->fullPlaylist === $schedule['playlist']) {
            // we have found the first playlist matching. send off this data and stop
            $selectedSchedule = $schedule;
        }
    }

    file_put_contents($pluginPath . "/store.json", json_encode($save));

    // send autoplayPlaylist
    list($status, $errors) = postAutoplayPlaylist(
        $_REQUEST['apiKey'],
        getPlaylistMeta($_REQUEST['autoplayPlaylist'])
    );
    // send fullPlaylist
    // send schedule
} # if
