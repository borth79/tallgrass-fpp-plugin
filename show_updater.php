<?php
require_once "globals.php";


function getFppStatus() {
    $url = "http://127.0.0.1/api/fppd/status";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    saveData('Response from /api/fppd/status/', json_encode($response), false);
    return json_decode($response);
}

function saveData($step, $data, $reset = false) {
    $file = "/home/fpp/media/plugins/tallgrass-fpp-plugin/xShowUpdater.txt";
    $fileData = '';
    if ($reset === false) {
        $fileData = file_get_contents($file);
    }
    $fileData .= "\n\nStep: " . $step ."\n";
    $fileData .= $data;
    file_put_contents($file, $fileData);
}

while(true) {
    // get store again in case the the apiKey is updated
    $store = json_decode(file_get_contents($pluginPath . "/store.json"));
    saveData('Start while loop', date('Y-m-d H:i:s'), true);

    $fppStatus = getFppStatus();
    $currentStatus = $fppStatus->status;
    if ($currentStatus !== 1) {
        saveData('Check show status', $currentStatus, false);
        continue;
    }
    $currentlyPlaying = $fppStatus->current_sequence;
    $fppd = $fppStatus->fppd;
    $scheduler = $fppStatus->scheduler;
    $currentlyPlayingStatus = $fppStatus->scheduler->status;


    $sequecneData = getSequenceData($currentlyPlaying);
    $musicMeta = getMusicMeta($currentlyPlaying);

    $postData = [
        'apiKey' => $store->apiKey,
        'song_id' => $sequecneData->ID,
        'start_time' => date('Y-m-d H:i:s', time() - $fppStatus->seconds_elapsed),
        'end_time' => date('Y-m-d H:i:s', time() + $fppStatus->seconds_remaining),
    ];
    saveData('Post data to tallgrasslights', json_encode($postData), false);

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

    saveData('Response from tallgrasslights', json_encode($response), false);

    # change sleep timer to roughly time remaining on the song to reduce requests
    $sleepTime = $fppStatus->seconds_remaining > 0 ? $fppStatus->seconds_remaining + 2 : 20;
    sleep($sleepTime);
}
