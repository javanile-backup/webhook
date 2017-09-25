<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/vendor/autoload.php';

$manifest = __DIR__.'/manifest.json';

echo '<pre>';

echo $manifest;

echo '</pre>';

