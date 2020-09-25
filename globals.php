<?php
global $pluginPath;
global $scriptPath;
$pluginPath = "/home/fpp/media/plugins/tallgrass-fpp-plugin";
$scriptPath = $pluginPath . "/scripts";


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

function getSequenceData($sequence)
{
    $url = "http://127.0.0.1/api/sequence/" . str_ireplace(' ', '%20', $sequence) . "/meta";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/sequenceDataResponse.txt", "Response:\n" . json_encode($response));
    return json_decode($response);
}


function postSchedule($apiKey=null, $fullPlaylist=null)
{
    $schedules = getSchedules();
    foreach ($schedules as $schedule) {
        print_r($schedule);
    }
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
            $sequenceData[] = array_merge(
                (array) getSequenceData($sequence['sequenceName']),
                [ 'length' => $sequence['duration'] ]
            );
        }

        $postData = [
            'apiKey' => $apiKey,
            'list' => $sequenceData,
        ];
        switch ($type) {
            case 'auto': $url = "http://api.tallgrasslights.com/api/xlights/autoplay-list";
                break;
            case 'full': $url = "http://api.tallgrasslights.com/api/xlights/song-list";
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
                file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/postAutoplayResponse.txt", 'code: ' . $responseCode . "\nresponse:\n" . json_encode($response));
                break;
            case 'full':
                file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/postSongListResponse.txt", 'code: ' . $responseCode . "\nresponse:\n" . json_encode($response));
                break;
        }

        return ['code' => $responseCode, 'errors' => $errors];
    } catch (Exception $exception) {
        echo '<div class="alert alert-danger">';
        print_r($exception->getMessage());
        echo '</div>';
    }

}

