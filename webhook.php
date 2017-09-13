<?php

require_once __DIR__.'/vendor/autoload.php';

use Javanile\Webhook\Endpoint;

$manifest = __DIR__.'/manifest.json';

$endpoint = new Endpoint($manifest);

echo $endpoint->run();
