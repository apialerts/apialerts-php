<?php

namespace ApiAlerts;

require 'Constants.php';
require 'Client.php';

class ApiAlerts {
    private string $apiKey;
    private bool $debug;
    private Client $client;

    public function __construct(string $apiKey, ?bool $debug = null) {
        $this->apiKey = $apiKey;
        $this->debug = $debug ?: false;
        $this->client = new Client();
    }

    public function configure(string $apiKey, ?bool $debug = null): void {
        $this->apiKey = $apiKey;
        $this->debug = $debug ?: false;
    }

    public function send(ApiAlertsEvent $event): void {
        $this->client->sendAsync($this->apiKey, $event, $this->debug);
    }

    public function sendWithApiKey(string $apiKey, ApiAlertsEvent $event): void {
        $this->client->sendAsync($apiKey, $event, $this->debug);
    }
}