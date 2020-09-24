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

function postAutoplayPlaylist($playlist)
{
    $url = "http://api.borthlights.com/api/show/show-status";
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response =curl_exec($ch);
        print_r($response);
        $info = curl_getinfo($ch);
        print_r($info);
        curl_close($ch);
        echo "NO ERROR";
    } catch (Exception $exception) {
        print_r($exception->getMessage());
    }

    $options = [
        'http' => [
            'method'  => 'GET',
        ]
    ];
    $context = stream_context_create($options);
    $url = "http://api.borthlights.com/api/show/show-status";
    $result = file_get_contents( $url, false, $context );
    print_r(json_decode( $result, true ));

    echo "<br />Post Data:<br />";
    print_r($playlist);
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $playlist
        ]
    ];
    $context = stream_context_create($options);
    $url = "http://api.borthlights.com/api/xlights/autoplay-list";
    $result = file_get_contents( $url, false, $context );
    echo "<br />Post Data Response:<br />";
    print_r($result);
    file_put_contents("/home/fpp/media/plugins/tallgrass-fpp-plugin/testResponse.txt", $result);
    return json_decode( $result, true );
}
