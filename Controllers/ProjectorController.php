<?php

use TallGrassProjector;

class ProjectorController
{
    public $pluginPath;
    public $store;

    public function __construct($pluginPath, $storeFile)
    {
        $this->pluginPath = $pluginPath;
        $this->store = json_decode(file_get_contents($pluginPath . '/' . $storeFile));
    }

    public function status()
    {
        $projector = new TallGrassProjector($this->store, 'pjlink');
        exit(
            json_encode([
                'status' => 'success',
                'data' => $projector->getStatus()
            ])
        );
    }
}