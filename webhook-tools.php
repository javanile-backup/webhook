<?php

require_once __DIR__.'/vendor/autoload.php';

use Javanile\Webhook\Tools as WebhookTools;

$method = $argv[1];

$webhookTools = new WebhookTools(__DIR__.'/manifest.json');

$methodsMap = [
    //
    'cron-init' => 'runCronInit',
    'cron-feed' => 'runCronFeed',
    'cron-done' => 'runCronDone',

    //
    'remote-url'  => 'getRemoteUrl',
    'remote-data' => 'getRemoteData',
];

if (!isset($methodsMap[$method])) {
    die();
}

echo call_user_func_array([$webhookTools, $methodsMap[$method]], array_slice($argv, 2));
