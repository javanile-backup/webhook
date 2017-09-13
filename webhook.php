<?php

$manifest = json_decode(file_get_contents(__DIR__.'/manifest.json'));


$resp = $manifest;


echo json_encode($resp);

