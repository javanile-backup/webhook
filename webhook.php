<?php

$manifest = json_decode(file_get_contents(__DIR__.'/manifest.json'));

$input = file_get_contents('php://input');

$resp = $manifest;
$resp = $input;


echo json_encode($resp);

