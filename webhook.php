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

error_reporting(E_ALL);
ini_set('display_errors', true);

require_once __DIR__.'/vendor/autoload.php';

use Javanile\Webhook\Endpoint as WebhookEndpoint;

$manifest = __DIR__.'/manifest.json';

$endpoint = new WebhookEndpoint([
    'logs'     => __DIR__.'/logs',
    'manifest' => $manifest,
    'input'    => 'php://input',
    'hook'     => filter_input(INPUT_GET, 'hook'),
]);

echo $endpoint->run();
