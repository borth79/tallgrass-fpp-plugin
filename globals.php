<?php
global $pluginPath;
global $scriptPath;
$pluginPath = "/home/fpp/media/plugins/tallgrass-fpp-plugin";
$scriptPath = $pluginPath . "/scripts";


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
    $options = [
        'http' => [
            'method'  => 'GET',
        ]
    ];
    $context = stream_context_create($options);
    $url = "http://127.0.0.1/api/playlist/" . $playlist;
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
