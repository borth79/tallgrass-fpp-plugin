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
    file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/test4.txt", $result);
    return json_decode( $result );
}

while(true) {
    $fppStatus = getFppStatus();
    $currentlyPlaying = $fppStatus->current_sequence;
    file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/test4.txt", $currentlyPlaying);
    $fppd = $fppStatus->fppd;
    $scheduler = $fppStatus->scheduler;
    $currentlyPlayingStatus = $fppStatus->scheduler->status;
    $currentStatus = $fppStatus->status;

    $sequecneData = getSequenceData($currentlyPlaying);

    $postData = [
        'apiKey' => $apiKey,
        'song_id' => $sequecneData->ID,
        'start_time' => date('Y-m-d H:i:s', time() - $fppStatus->seconds_elapsed),
        'end_time' => date('Y-m-d H:i:s', time() + $fppStatus->seconds_remaining),
    ];
    file_put_contents($pluginPath . "/currentlyPlayingPostData.json", json_encode($postData));

    $url = "http://api.tallgrasslights.com/api/xlights/currently-playing";
    $headers = [
        'Content-Type: application/json',
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = json_decode(curl_exec($ch));
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    file_put_contents($pluginPath . "/currentlyPlayingResponseData.json", $response);

    sleep(10);
}
