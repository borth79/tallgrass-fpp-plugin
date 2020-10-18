<?php
require_once "globals.php";

// get the current data store
require_once "process.php";

$store = json_decode(file_get_contents($pluginPath . "/store.json"));

$options = [
    'http' => [
        'method'  => 'GET',
    ]
];
$context = stream_context_create($options);

 // get the playlists
$playlists = getAllPlaylists();

// get the playlist details
foreach ($playlists as $playlist) {
    $url = "http://127.0.0.1/api/playlist/" . $playlist;
    $result = file_get_contents( $url, false, $context );
    $responseMeta = json_decode( $result, true );
}

$sequences = getAllSequences();

?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<script>
    function projectorOff() {
        $.ajax({
            type: "POST",
            url: '/plugin.php?plugin=tallgrass-fpp-plugin&page=pjlink.php',
            data: 'command=OFF',
            success: function () {

            },
            dataType: 'html'
        });
    }

    function projectorOn() {
        $.ajax({
            type: "POST",
            url: '/plugin.php?plugin=tallgrass-fpp-plugin&page=pjlink.php',
            data: 'command=ON',
            success: function () {

            },
            dataType: 'html'
        });
    }

    function projectorStatus() {
        $.ajax({
            type: "POST",
            url: '/plugin.php?plugin=tallgrass-fpp-plugin&page=pjlink.php',
            data: 'command=STATUS',
            success: function () {

            },
            dataType: 'html'
        });
    }
</script>


<div class="container">
    <?php if (isset($errors) && count($errors)) { ?>
    <div class="alert alert-danger">
        <div class="font-weight-bold">Errors:</div>
        <?=implode('<br />', $errors);?>
    </div>
    <?php } ?>

    <?php if ($_REQUEST['submission']) { ?>
        <div class="alert alert-success">
            <p>Updates have been submitted.</p>
        </div>
    <?php } ?>

    <div class="alert alert-secondary">
        <h2>TallGrass Lights Plugin</h2>
    </div>

    <form method="post" action="/plugin.php?plugin=tallgrass-fpp-plugin&page=content.php">
        <input type="hidden" name="submission" value="1">
        <div class="form-group">
            <label for="apiKey">API Key</label>
            <input type="password" class="form-control" name="apiKey" id="apiKey" aria-describedby="apiKeyHelp" value="<?=$store->apiKey?>">
            <small id="apiKeyHelp" class="form-text text-muted">Enter your TallGrass API key</small>
        </div>

        <div class="form-group">
            <label for="autoplayPlaylist">Autoplay Playlist</label>
            <select class="form-control" name="autoplayPlaylist" id="autoplayPlaylist" aria-describedby="autoplayPlaylistHelp">
                <?php
                    foreach($playlists as $playlist) {
                        echo '<option value="'.$playlist.'" '. (($store->autoplayPlaylist === $playlist) ? 'selected' : '') .'>'. $playlist .'</option>';
                    }
                ?>
            </select>
            <small id="autoplayPlaylistHelp" class="form-text text-muted">Sync your autoplay playlist with TallGrassLights.com</small>
        </div>

        <div class="form-group">
            <label for="fullPlaylist">Full Playlist</label>
            <select class="form-control" name="fullPlaylist" id="fullPlaylist" aria-describedby="fullPlaylistHelp">
                <?php
                foreach($playlists as $playlist) {
                    echo '<option value="'.$playlist.'" '. (($store->fullPlaylist === $playlist) ? 'selected' : '') .'>'. $playlist .'</option>';
                }
                ?>
            </select>
            <small id="fullPlaylistHelp" class="form-text text-muted">Sync your full playlist with TallGrassLights.com</small>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <h4>Projector</h4>
    <hr />

    <div class="row">
        <div class="col-4">
            <div class="btn btn-success" onclick="projectorOn()">Turn On</div>
        </div>
        <div class="col-4">
            <div class="btn btn-danger" onclick="projectorOff()">Turn Off</div>
        </div>
        <div class="col-4">
            <div class="btn btn-info" onclick="projectorOff()">Status</div>
        </div>
        </div>
</div>
