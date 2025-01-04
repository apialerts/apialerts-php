<?php

namespace ApiAlerts;

class Client {

    public function sendAsync(string $apiKey, APIAlertsEvent $event, bool $debug): void {
        if (!$this->validateApiKey($apiKey) || !$this->validateEvent($event)) {
            return;
        }

        $payload = json_encode($event);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Constants::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-type: application/json",
            "Authorization: Bearer " . $apiKey,
            "X-Integration: " . Constants::X_INTEGRATION_NAME,
            "X-Version: " . Constants::X_INTEGRATION_VERSION,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $mh = curl_multi_init();
        curl_multi_add_handle($mh, $ch);

        // Execute the handle in a non-blocking manner
        do {
            $status = curl_multi_exec($mh, $active);
        } while ($status == CURLM_CALL_MULTI_PERFORM);

        // Wait for activity on any curl-connection
        while ($active && $status == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $status = curl_multi_exec($mh, $active);
                } while ($status == CURLM_CALL_MULTI_PERFORM);
            }
        }

        // Log the response asynchronously
        $this->logResponseAsync($ch, $debug);

        // Close the handles
        curl_multi_remove_handle($mh, $ch);
        curl_multi_close($mh);
    }

    private function logResponseAsync($ch, bool $debug): void {
        $result = curl_multi_getcontent($ch);
        if ($result === FALSE) {
            error_log('x (apialerts.com) Error: Error sending alert');
            return;
        }

        echo "! (apialerts.com) executing\n";

        print_r($result); // Print the result
        $response = json_decode($result, true);
        print_r($response); // Print the result

        if ($debug) {
            $workspace = $response['workspace'] ?? '?';
            $project = $response['project'] ?? '?';
            $channel = $response['channel'] ?? '?';
            echo "✓ (apialerts.com) Alert sent to " . $workspace . " " . $project . " (" . $channel . ") successfully.\n";
            $warnings = $response['errors'] ?? [];
            foreach ($warnings as $warning) {
                echo "! (apialerts.com) Warning: " . $warning . "\n";
            }
        }
    }

    private function validateApiKey(string $apiKey): bool {
        if (empty($apiKey)) {
            error_log('x (apialerts.com) Error: API Key not provided. Use configure() to set a default key, or pass the key as a parameter to the sendWithApiKey function.');
            return false;
        }
        return true;
    }

    private function validateEvent(APIAlertsEvent $event): bool {
        if (empty($event->message)) {
            error_log('x (apialerts.com) Error: Message is required');
            return false;
        }
        return true;
    }
}