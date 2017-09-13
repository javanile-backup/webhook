<?php

require_once __DIR__.'/vendor/autoload.php';

use Javanile\Webhook\Endpoint as WebhookEndpoint;

$manifest = __DIR__.'/manifest.json';

$endpoint = new WebhookEndpoint($manifest);

echo $endpoint->run();
