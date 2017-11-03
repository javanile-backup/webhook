<?php
/**
 * Command line tool for vendor code.
 *
 * PHP version 5
 *
 * @category CommandLine
 *
 * @author    Francesco Bianco <bianco@javanile.org>
 * @copyright 2015-2017 Javanile.org
 * @license   https://goo.gl/KPZ2qI  MIT License
 */
require_once __DIR__.'/vendor/autoload.php';

use Javanile\Webhook\Tools as WebhookTools;

$method = $argv[1];

$webhookTools = new WebhookTools(__DIR__.'/manifest.json');

$methodsMap = [
    // Cron tools
    'cron-init' => 'runCronInit',
    'cron-feed' => 'runCronFeed',
    'cron-done' => 'runCronDone',
    // Remote tools
    'remote-url'  => 'getRemoteUrl',
    'remote-data' => 'getRemoteData',
];

if (!isset($methodsMap[$method])) {
    exit();
}

echo call_user_func_array(
    [$webhookTools, $methodsMap[$method]],
    array_slice($argv, 2)
);
