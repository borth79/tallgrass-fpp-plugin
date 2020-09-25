<?php
require_once "globals.php";

function getFppStatus() {
    $options = [
        'http' => [
            'method'  => 'GET',
        ]
    ];
    $context = stream_context_create( $options );

    $url = "http://127.0.0.1/api/fppd/status";
    $result = file_get_contents( $url, false, $context );
    return json_decode( $result );
}

while(true) {
    $fppStatus = getFppStatus();
    $currentlyPlaying = $fppStatus->current_sequence;
    $fppd = $fppStatus->fppd;
    $scheduler = $fppStatus->scheduler;
    $currentlyPlayingStatus = $fppStatus->scheduler->status;
    $currentStatus = $fppStatus->status;

    $sequecneData = getSequenceData($currentlyPlaying);
    #file_put_contents($pluginPath . "/responseTest1.json", json_encode($sequecneData));

    $save = [
        'currentlyPlaying' => $currentlyPlaying,
        'fppd' => $fppd,
        'scheduler' => $scheduler,
        'currentStatus' => $fppStatus->status,
    ];
    file_put_contents($pluginPath . "/responseTest.json", json_encode($save));
    sleep(10);
}
