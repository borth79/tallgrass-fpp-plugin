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

function postAutoplayPlaylist($apiKey = null, $playlist = null)
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
        print_r($postData);
        $url = "http://api.tallgrasslights.com/api/xlights/autoplay-list";
        $headers = [
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        echo "<br />Response:<br />";
        print_r($response);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/testResponse.txt", $response);
        file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/postAutoplayError.txt", 'code: ' . $responseCode . "\nresponse:\n" . $response);
        return $responseCode === 200;
    } catch (Exception $exception) {
        echo '<div class="alert alert-danger">';
        print_r($exception->getMessage());
        echo '</div>';
    }

}

function getSequenceData($sequence)
{
    $url = "http://127.0.0.1/api/sequence/" . str_ireplace(' ', '%20', $sequence) . "/meta";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response);
}
