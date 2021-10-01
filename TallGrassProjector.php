<?php

use PJLink;

class TallGrassProjector
{
    public $store;
    public $protocol;

    public function __construct($store, $protocol) {
        $this->store = $store;
        $this->protocol = $this->loadProtocol($protocol);

    }

    private function loadProtocol($protocol)
    {
        switch ($protocol) {
            case 'pjlink':
                return new PJLink();
            default:
                throw new Exception('Could not find a matching protocol');
        }
    }

    public function getStatus()
    {
        return $this->protocol->getPowerState(
            $this->store->projectorIp,
            isset($this->store->projectorIp) ?: '',
            isset($this->store->projectorTimeout) ?: '60',
            isset($this->store->projectorPort) ?: '4352'
        );
    }
}