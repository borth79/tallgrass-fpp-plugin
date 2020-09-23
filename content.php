<?php
$pluginPath = "/home/fpp/media/plugins/tallgrass-lights";
$scriptPath = "/home/fpp/media/plugins/tallgrass-lights";

$options = [
    'http' => [
        'method'  => 'GET',
    ]
];

 // get the playlists
$url = "http://127.0.0.1/api/playlists";
$context = stream_context_create( $options );
$result = file_get_contents( $url, false, $context );
$playlists = json_decode( $result, true );

// get the playlist details
foreach ($playlists as $playlist) {
    echo "Playlist: " . $playlist . "\n";
    $url = "http://127.0.0.1/api/playlists/" . $playlist;
    $result = file_get_contents( $url, false, $context );
    $responseMeta = json_decode( $result, true );
    print_r($responseMeta);
}

$url = "http://127.0.0.1/api/sequence";
$result = file_get_contents( $url, false, $context );
$response = json_decode( $result, true );
print_r($response);

foreach ($response as $name) {
    $url = "http://127.0.0.1/api/sequence/" . $name . "/meta";
    $result = file_get_contents( $url, false, $context );
    $responseMeta = json_decode( $result, true );
    print_r($responseMeta);
}
?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

<div class="container">
    <div class="alert alert-info">
        <h2>TallGrass Lights Plugin</h2>
    </div>

    <form method="post" action="#">
        <div class="form-group">
            <label for="apiKey">API Key</label>
            <input type="text" class="form-control" id="apiKey" aria-describedby="apiKeyHelp">
            <small id="apiKeyHelp" class="form-text text-muted">Enter your TallGrass API key</small>
        </div>

        <div class="form-group">
            <label for="syncPlaylist">Sync Playlist</label>
            <select class="form-control" id="syncPlaylist" aria-describedby="syncPlaylistHelp">
                <?php
                    foreach($playlists as $playlist) {
                        echo '<option value="'.$playlist.'">'. $playlist .'</option>';
                    }
                ?>
            </select>
            <small id="syncPlaylistHelp" class="form-text text-muted">Sync your playlist with TallGrassLights.com</small>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
