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

/**
 * Configuration
 * -------------
 * move webhook.php file in public folder
 * change $_WEBHOOK_DIR to found it.
 * (es. $_WEBHOOK_DIR = '/srv/deploy-tools/webhook';).
 */
$_WEBHOOK_DIR = __DIR__;

// Php debugging
error_reporting(E_ALL);
ini_set('display_errors', true);

// Require vendor libraries
require_once $_WEBHOOK_DIR.'/vendor/autoload.php';

// Load secret variables
$secret = require_once $_WEBHOOK_DIR.'/secret.php';

// Namespace of endpoint handler
use Javanile\Webhook\Endpoint as WebhookEndpoint;

// Instantiate web endpoint handler
$endpoint = new WebhookEndpoint([
    'secret'   => $secret,
    'manifest' => $_WEBHOOK_DIR.'/manifest.json',
    'request'  => $_SERVER['REQUEST_METHOD'],
    'payload'  => 'php://input',
    'client'   => filter_input(INPUT_GET, 'client'),
    'hook'     => filter_input(INPUT_GET, 'hook'),
    'info'     => filter_input(INPUT_GET, 'info'),
    'log'      => __DIR__.'/log',
]);

// Print output
echo $endpoint->run();
