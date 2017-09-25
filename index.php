<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

#require_once __DIR__.'/vendor/autoload.php';

$manifest = json_decode(file_get_contents(__DIR__.'/manifest.json'), true);

echo '<pre>once: '.@implode(', ', @$manifest['once']).'</pre>';

if (is_array($manifest['hook'])) {
    foreach ($manifest['hook'] as $hook => $task) {
        $webhook = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?hook=$hook";
        echo '<pre>'.$webhook.'</pre>';
    }
}

if (isset($_GET['watch'])) {
    $logFile = __DIR__.'/logs/'.$_GET['watch'].'.log';
    echo '<pre>'.file_get_contents($logFile).'</pre>';
    echo '<script>setTimeout("window.location.reload()", 15000);</script>';
}

echo '<style>pre{border:#ccc;background:#eee;padding:5px}</style>';

