<?php
global $pluginPath;
global $scriptPath;
$pluginPath = "/home/fpp/media/plugins/tallgrass-fpp-plugin";
$scriptPath = $pluginPath . "/scripts";


function saveData($step=null, $data, $reset = false, $file) {
    $fileData = '';
    if ($reset === false) {
        $fileData = file_get_contents($file);
    }
    if ($step) {
        $fileData .= "\n\nStep: " . $step ."\n";
    }
    $fileData .= $data;
    file_put_contents($file, $fileData);
}

function getFppStatus() {
    $url = "http://127.0.0.1/api/fppd/status";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    saveData('Response from /api/fppd/status/', json_encode($response), false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xShowUpdater.txt");
    return json_decode($response);
}

function getSchedules() {
    $url = "http://127.0.0.1/api/schedule";
    $options = [
        'http' => [
            'method'  => 'GET',
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents( $url, false, $context );
    return json_decode( $result, true );
}

function getAllPlaylists()
{
    // get the playlists
    $url = "http://127.0.0.1/api/playlists";
    $options = [
        'http' => [
            'method'  => 'GET',
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $playlists = json_decode($result, true);
    return $playlists;
}

function getPlaylistMeta($playlist)
{
    $url = "http://127.0.0.1/api/playlist/" . str_ireplace(' ', '%20', $playlist);
    $options = [
        'http' => [
            'method'  => 'GET',
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents( $url, false, $context );
    return json_decode( $result, true );
}

function getAllSequences()
{
    $options = [
        'http' => [
            'method'  => 'GET',
        ]
    ];
    $context = stream_context_create($options);
    $url = "http://127.0.0.1/api/sequence";
    $result = file_get_contents( $url, false, $context );
    return json_decode( $result, true );
}

function getSequenceData($sequence=null)
{
    if ($sequence === null) {
        return json_decode([]);
    }
    $sequence = str_ireplace('.fseq', '', $sequence);
    $sequence = str_ireplace(' ', '%20', $sequence);
    saveData('Sequence to Fetch', $sequence, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xShowUpdater.txt");
    $url = "http://127.0.0.1/api/sequence/" . $sequence . "/meta";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    saveData('Response from ' . $url, $response, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xShowUpdater.txt");
    file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/responseSequenceDataResponse.txt", $response);
    return json_decode($response);
}

function secondsToTime($seconds)
{
    $hours = floor($seconds / 3600);
    $mins = floor($seconds / 60 % 60);
    $secs = floor($seconds % 60);
    return $hours.':'.$mins.':'.$secs;
}

function getMusicMeta($fileName=null)
{
    if ($fileName === null) {
        return json_decode([]);
    }
    $url = "http://127.0.0.1/api/media/" . str_ireplace(' ', '%20', $fileName) . "/meta";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    saveData('Request Url', $url, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/" . "/xShowUpdater.txt");
    saveData('/api/media/<file>/meta response', $response, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/" . "/xShowUpdater.txt");
    return json_decode($response);
}

function getMusicDuration($fileName=null)
{
    if ($fileName === null) {
        return json_decode([]);
    }
    $url = "http://127.0.0.1/api/media/" . str_ireplace(' ', '%20', $fileName) . "/duration";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response)->{$fileName}->duration;
}

function postSchedule($apiKey=null, $fullPlaylist=null)
{
    $schedules = getSchedules();
    $activeSchedules = [];
    saveData('postSchedule()', json_encode($schedules), true, "/home/fpp/media/plugins/tallgrass-fpp-plugin/debug.txt");
//    foreach ($schedules as $schedule) {
//        file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/debug.txt", json_encode($schedule));
//        if ($schedule['enabled'] !== 1) {
//            continue;
//        }
//        if ($schedule['playlist'] !== $fullPlaylist) {
//            continue;
//        }
//        $activeSchedules[] = $schedule;
//    }
    $url = "http://api.tallgrasslights.com/api/xlights/show-schedule";

    $headers = [
        'Content-Type: application/json',
    ];

    $postData = [
        'apiKey' => $apiKey,
        'schedule' => json_encode($schedules),
    ];
    file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/responsePostScheduleData.txt", json_encode($postData));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = json_decode(curl_exec($ch));
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $errors = (isset($response->errors)) ? $response->errors : [];
    file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/responsePostScheduleResponse.txt", 'code: ' . $responseCode . "\nresponse:\n" . json_encode($response));

    return ['code' => $responseCode, 'errors' => $errors];
}


function postPlaylist($apiKey = null, $playlist = null, $type = 'full')
{
    try {
        # MUST PULL META DATA FROM SEQUENCE
        $sequenceData = [];
        foreach ($playlist['mainPlaylist'] as $sequence) {
            if (!$sequence['enabled']) {
                continue;
            }
            saveData('$playlist', json_encode($playlist), true, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xTest.txt");
            saveData('Media Meta Response', json_encode(getMusicMeta($playlist['mediaName'])), false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xTest.txt");
            $sequenceData[] = array_merge(
                (array) getSequenceData($sequence['sequenceName']),
                (array) getMusicMeta($playlist['mediaName']),
                [ 'length' => secondsToTime(round($sequence['duration'])) ]
            );
        }

        $postData = [
            'apiKey' => $apiKey,
            'list' => $sequenceData,
        ];
        switch ($type) {
            case 'auto':
                saveData('Auto Playlist Post Data', json_encode($postData), false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xPostAutoPlaylistData.txt");
                $url = "http://api.tallgrasslights.com/api/xlights/autoplay-list";
                break;
            case 'full':
                saveData('Full Playlist Post Data', json_encode($postData), false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xPostFullPlaylistData.txt");
                $url = "http://api.tallgrasslights.com/api/xlights/song-list";
                break;
        }
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

        $errors = (isset($response->errors)) ? $response->errors : [];
        switch ($type) {
            case 'auto':
                file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/responsePostAutoplayResponse.txt", 'code: ' . $responseCode . "\nresponse:\n" . json_encode($response));
                break;
            case 'full':
                file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/responsePostSongListResponse.txt", 'code: ' . $responseCode . "\nresponse:\n" . json_encode($response));
                break;
        }

        return ['code' => $responseCode, 'errors' => $errors];
    } catch (Exception $exception) {
        echo '<div class="alert alert-danger">';
        print_r($exception->getMessage());
        echo '</div>';
    }

}



function updateSongQueue($apiKey) {
    // get next song from tallgrasslights
    $url = "http://api.tallgrasslights.com/api/xlights/next-song";

    $headers = [
        'Content-Type: application/json',
    ];

    $postData = [
        'apiKey' => $apiKey,
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
    saveData('http://api.tallgrasslights.com/api/xlights/next-song', json_encode($response), true, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xNextSongResponse.txt");

    # get sequence info
    $sequecneData = getSequenceData($response->file);
    saveData('getSequenceData('.$response->file.')', json_encode($sequecneData), false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xNextSongResponse.txt");
    $mediaPath = explode("/", $sequecneData->variableHeaders->mf);
    $mediaFile = end($mediaPath);
    saveData('Media File: ', $mediaFile, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xNextSongResponse.txt");

    # TODO: This will fail if it has no media
    $mediaDuration = getMusicDuration($mediaFile);
    $mediaDurationX = explode(':', secondsToTime(round($mediaDuration)));
    $mediaDurationString = $mediaDurationX[1].'m:'.$mediaDurationX[2].'s';
    saveData('Media Duration: ', json_encode($mediaDuration), false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xNextSongResponse.txt");

    # Song List
    $songList[] = [
        'type' => 'both',
        'enabled' => 1,
        'playOnce' => 0,
        'sequenceName' => $response->file,
        'mediaName' => $mediaFile,
        'videoOut' => '--Default--',
        'duration' => getMusicDuration($mediaFile),
    ];
    $playlistDuration = $mediaDuration;

    # Get in between sequence
    $inBetweenFile = 'Pick-Next-Song.fseq';
    $inBetweenSequenceData = getSequenceData($inBetweenFile);
    if (!empty($inBetweenSequenceData)) {
        saveData('getSequenceData(' . $inBetweenFile . ')', json_encode($inBetweenSequenceData), false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xNextSongResponse.txt");
        $inBetweenMediaPath = explode("/", $inBetweenSequenceData->variableHeaders->mf);
        $inBetweenMediaFile = end($inBetweenMediaPath);
        $inBetweenMediaDuration = getMusicDuration($inBetweenMediaFile);
        $songList[] = [
            'type' => 'both',
            'enabled' => 1,
            'playOnce' => 0,
            'sequenceName' => $inBetweenFile,
            'mediaName' => $inBetweenMediaFile,
            'videoOut' => '--Default--',
            'duration' => getMusicDuration($inBetweenMediaFile),
        ];
        $playlistDuration += $inBetweenMediaDuration;
    }

    # build json playlist file
    $playlistFile = [
        'name' => 'Song_Queue',
        'version' => 3,
        'repeat' => 0,
        'loopCount' => 0,
        'desc' => '',
        'mainPlaylist' => $songList,
        'playlistInfo' => [
                'total_duration' => $playlistDuration,
                'total_items' => count($songList),
            ],
    ];
    saveData('$playlistFile', json_encode($playlistFile), false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xNextSongResponse.txt");

    # write to the playlist file
    saveData(null, json_encode($playlistFile), true, "/home/fpp/media/playlists/Song_Queue.json");
}

function getRunningEffects()
{
    $url = "http://127.0.0.1/fppxml.php?command=getRunningEffects";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    saveData('Response from /fppxml.php?command=getRunningEffects', $response, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xShowUpdater.txt");
    $effects = simplexml_load_string($response);
    foreach (get_object_vars($effects->RunningEffect) as $key => $val) {
        saveData('temp', $key, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xShowUpdater.txt");
    }
    foreach ($effects->RunningEffect as $effect) {
        saveData('temp', $effect->name, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xShowUpdater.txt");
        $runningEffects[] = $effect->name;
    }
    return $runningEffects;
}
