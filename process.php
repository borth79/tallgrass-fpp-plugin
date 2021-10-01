<?php
global $errors;
$errors = [];

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
        'projectorIp' => $_REQUEST['projectorIp'],
        'persistentProjector' => $_REQUEST['persistentProjector'],
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

    # process the schedule
    $fullPlaylistResponse = postSchedule(
        $_REQUEST['apiKey'],
        $_REQUEST['fullPlaylist']
    );

    # PROCESS THE SONG LIST BEFORE THE AUTOPLAY LIST - AUTOPLAY IS DEPENDENT ON SONG LIST
    $fullPlaylistResponse = postPlaylist(
        $_REQUEST['apiKey'],
        getPlaylistMeta($_REQUEST['fullPlaylist']),
        'full'
    );
    $errors = array_merge($errors, $fullPlaylistResponse['errors']);
    // send autoplayPlaylist
    $autoplayResponse = postPlaylist(
        $_REQUEST['apiKey'],
        getPlaylistMeta($_REQUEST['autoplayPlaylist']),
        'auto'
    );
    $errors = array_merge($errors, $autoplayResponse['errors']);

    // send fullPlaylist
    // send schedule
} # if
