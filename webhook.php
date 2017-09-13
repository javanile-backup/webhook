<?php

require_once __DIR__.'/vendor/autoload.php';

use Javanile\Webhook\Endpoint as WebhookEndpoint;

$manifest = __DIR__.'/manifest.json';

$endpoint = new WebhookEndpoint([
    'manifest' => $manifest,
    'input'    => 'php://input',
    'hook'     => filter_input(INPUT_GET, 'hook'),
]);

echo $endpoint->run();
