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
require_once __DIR__.'/../vendor/autoload.php';

use Javanile\Webhook\Trigger as WebhookTrigger;

$manifest = isset($argv[1]) && $argv[1] ? $argv[1] : die("Error: missing manifest.\n");
$method   = isset($argv[2]) && $argv[2] ? $argv[2] : die("Error: missing method.\n");

$webhookTrigger = new WebhookTrigger($manifest);

$methodsMap = [
    // Remote tools
    'remote-url'  => 'getRemoteUrl',
    'remote-data' => 'getRemoteData',
];

if (!isset($methodsMap[$method])) {
    die("Error: method not valid.\n");
}

echo call_user_func_array(
    [$webhookTrigger, $methodsMap[$method]],
    array_slice($argv, 2)
);
