<?php

require 'ApiAlerts.php';
require 'ApiAlertsEvent.php';

use APIAlerts\ApiAlerts;
use APIAlerts\ApiAlertsEvent;

$apiKey = getenv('APIALERTS_API_KEY');
if (!$apiKey) {
    die('Error: APIALERTS_API_KEY environment variable is not set');
}

$build = false;
$release = false;
$publish = false;

if (in_array('--build', $argv)) {
    $build = true;
}
if (in_array('--release', $argv)) {
    $release = true;
}
if (in_array('--publish', $argv)) {
    $publish = true;
}

$eventChannel = 'developer';
$eventMessage = 'apialerts-php';
$eventTags = [];
$eventLink = 'https://github.com/apialerts/apialerts-php/actions';

if ($build) {
    $eventMessage = 'PHP - PR build success';
    $eventTags = ['CI/CD', 'PHP', 'Build'];
} elseif ($release) {
    $eventMessage = 'PHP - Build for publish success';
    $eventTags = ['CI/CD', 'PHP', 'Deploy'];
} elseif ($publish) {
    $eventMessage = 'PHP - Packagist publish success';
    $eventTags = ['CI/CD', 'PHP', 'Deploy'];
}

$event = new ApiAlertsEvent(
    $eventChannel,
    $eventMessage,
    $eventTags,
    $eventLink
);

$client = new ApiAlerts($apiKey, true);

echo "Created client.\n";
echo $eventMessage . "\n";

$client->send($event);

echo "Finish.\n";