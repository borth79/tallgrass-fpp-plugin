<?php
global $pluginPath;
global $scriptPath;
$pluginPath = "/home/fpp/media/plugins/tallgrass-fpp-plugin";
$scriptPath = $pluginPath . "/scripts";


function saveData($step, $data, $reset = false, $file) {
    $fileData = '';
    if ($reset === false) {
        $fileData = file_get_contents($file);
    }
    $fileData .= "\n\nStep: " . $step ."\n";
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
    $url = "http://127.0.0.1/api/sequence/" . str_ireplace(' ', '%20', $sequence) . "/meta";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
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

function postSchedule($apiKey=null, $fullPlaylist=null)
{
    $schedules = getSchedules();
    $activeSchedules = [];
    foreach ($schedules as $schedule) {
        if ($schedule['enabled'] !== 1) {
            continue;
        }
        if ($schedule['playlist'] !== $fullPlaylist) {
            continue;
        }
        $activeSchedules[] = $schedule;
    }
    $url = "http://api.tallgrasslights.com/api/xlights/show-schedule";

    $headers = [
        'Content-Type: application/json',
    ];

    $postData = [
        'apiKey' => $apiKey,
        'schedule' => $activeSchedules,
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
    saveData('Full path to media file', $sequecneData->variableHeaders->mf, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xNextSongResponse.txt");
    $mediaFile = explode("/", $sequecneData->variableHeaders->mf);
    end($mediaFile);
    saveData('Media File: ', $mediaFile, false, "/home/fpp/media/plugins/tallgrass-fpp-plugin/xNextSongResponse.txt");

    # build json playlist file
//    $playlistFile = [
//        'name' => 'Song_Requests',
//        'version' => 3,
//        'repeat' => 0,
//        'loopCount' => 0,
//        'desc' => '',
//        'mainPlaylist' =>
//            [
//                0 =>
//                    [
//                        'type' => 'both',
//                        'enabled' => 1,
//                        'playOnce' => 0,
//                        'sequenceName' => 'The-Walking-Dead.fseq',
//                        'mediaName' => 'The-Walking-Dead-2018.mp4',
//                        'videoOut' => '--Default--',
//                        'duration' => 48.15,
//                    ],
//            ],
//        'playlistInfo' => [
//                'total_duration' => '00m:48s',
//                'total_items' => 1,
//            ],
//    ];

}

