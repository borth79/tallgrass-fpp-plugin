<?php
require_once "globals.php";
require_once "pjlink/pjlink.class.php";
include('pjlink.config.php');
$showInitiated = true;
while(true) {
    // get store again in case the the apiKey is updated
    $store = json_decode(file_get_contents($pluginPath . "/store.json"));
    saveData('Start while loop', date('Y-m-d H:i:s'), true, $pluginPath . "/xShowUpdater.txt");

    $fppStatus = getFppStatus();
    $currentStatus = $fppStatus->status;
    saveData('Check show status', $currentStatus, false, $pluginPath . "/xShowUpdater.txt");
    if ($currentStatus !== 1) {
        $projectorStatus = $pjlink->getPowerState($store->projectorIP, '', '60', $port);
        // show is off check projector status
        saveData('Show is disabled', '', false, $pluginPath . "/xShowUpdater.txt");
        saveData('Check Projector Status', $projectorStatus, false, $pluginPath . "/xShowUpdater.txt");
        if ($projectorStatus !== 2) {
            saveData('Turning off power',
                $pjlink->powerOff($projectorIP, '', '60', $port),
                false,
                $pluginPath . "/xShowUpdater.txt"
            );
        }
        saveData('Check Projector Status', $projectorStatus, false, $pluginPath . "/xShowUpdater.txt");
        saveData('Sleeping for 20 seconds', 'Have a nice nap', false, $pluginPath . "/xShowUpdater.txt");
        sleep(20);
        continue;
    }
    if ($currentStatus === 1 && ($store->persistentProjector || $showInitiated)) {
        $projectorStatus = $pjlink->getPowerState($store->projectorIP, '', '60', $port);
    }
    // turn on projector if not on and show is running
    if ($projectorStatus !== 1) {
        saveData('Projector Settings',
            'IP: ' . $store->projectorIP . ', Port: ' . $port,
            false,
            $pluginPath . "/xShowUpdater.txt"
        );
        saveData('Turning on power',
            $pjlink->powerOn($store->projectorIP, '', '60', $port),
            false,
            $pluginPath . "/xShowUpdater.txt"
        );
    }

    $currentlyPlaying = $fppStatus->current_sequence;
    $fppd = $fppStatus->fppd;
    $scheduler = $fppStatus->scheduler;
    $currentlyPlayingStatus = $fppStatus->scheduler->status;


    $sequecneData = getSequenceData($currentlyPlaying);
    if (!empty($fppStatus->current_song)) {
        $musicMeta = getMusicMeta($fppStatus->current_song);
    }

    $postData = [
        'apiKey' => $store->apiKey,
        'song_id' => $sequecneData->ID,
        'start_time' => date('Y-m-d H:i:s', time() - $fppStatus->seconds_elapsed),
        'end_time' => date('Y-m-d H:i:s', time() + $fppStatus->seconds_remaining),
    ];
    saveData('Post data to tallgrasslights', json_encode($postData), false, $pluginPath . "/xShowUpdater.txt");

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

    saveData('Post data for currently-playing', json_encode($postData), false, $pluginPath . "/xShowUpdater.txt");
    saveData('Response from tallgrasslights', json_encode($response), false, $pluginPath . "/xShowUpdater.txt");

    # change sleep timer to roughly time remaining on the song to reduce requests
    $sleepTime = $fppStatus->seconds_remaining > 0 ? $fppStatus->seconds_remaining + 2 : 20;

    // get next song
    updateSongQueue($store->apiKey);

    $showInitiated = false;
    sleep($sleepTime);
}
