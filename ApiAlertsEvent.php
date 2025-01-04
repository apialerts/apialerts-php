<?php

namespace ApiAlerts;

class ApiAlertsEvent {
    public $channel;
    public $message;
    public $tags;
    public $link;

    public function __construct($channel, $message, $tags = [], $link = '') {
        $this->channel = $channel;
        $this->message = $message;
        $this->tags = $tags;
        $this->link = $link;
    }
}