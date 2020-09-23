<?php
require_once "globals.php";

$options = array(
    'http' => array(
        'method'  => 'GET'
    )
);
$context = stream_context_create( $options );

function getFppStatus() {
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

    $save = [
        'fppStatus' => $fppStatus,
        'currentlyPlaying' => $currentlyPlaying,
        'fppd' => $fppd,
        'scheduler' => $scheduler,
        'currentlyPlayingStatus' => $currentlyPlayingStatus,
        'currentStatus' => $fppStatus->status,
    ];
    file_put_contents($pluginPath . "/test.json", json_encode($save));
    file_put_contents($pluginPath . "/test1.json", 'test');
    sleep(10);
}
