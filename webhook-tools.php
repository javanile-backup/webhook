<?php

require_once __DIR__.'/vendor/autoload.php';

use Javanile\Webhook\Tools as WebhookTools;

var_Dump($argv);

$method = $argv[1];

$webhookTools = new WebhookTools();

$methodsMap = [
    'remote-url' => 'getRemoteUrl'
];

if (!isset($methodsMap[$method])) {
    die();
}

echo call_user_func_array([$webhookTools, $methodsMap[$method]], array_slice($argv, 2));
