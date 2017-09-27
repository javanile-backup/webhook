<?php
/**
 * Mount command for producer.
 *
 * PHP version 5
 *
 * @category ProducerCommand
 *
 * @author    Francesco Bianco <bianco@javanile.org>
 * @copyright 2015-2017 Javanile.org
 * @license   https://goo.gl/KPZ2qI  MIT License
 */

namespace Javanile\Webhook;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Endpoint extends Manifest
{
    protected $hook;

    protected $input;

    protected $cronLog;

    public function __construct($args)
    {
        foreach (['manifest', 'request', 'payload'] as $key) {
            if (!isset($args[$key])) {
                throw new \Exception("Argument required '{$key}'.");
            }
        }

        parent::__construct($args['manifest']);

        //
        $this->request = $args['request'];
        $this->payload = $args['payload'];
        $this->client = isset($args['client']) ? $args['client'] : null;
        $this->hook = isset($args['hook']) ? $args['hook'] : null;
        $this->info = isset($args['info']) ? preg_replace('/[^a-z]/i', '', $args['info']) : 'event';

        //
        $accessLogFile = $this->basePath.'/logs/access.log';
        $this->accessLog = new Logger('ACCESS');
        $this->accessLog->pushHandler(new StreamHandler($accessLogFile, Logger::INFO));
    }

    public function run()
    {
        $this->accessLog->info($_SERVER['REQUEST_URI']);

        switch ($this->request) {
            case 'POST':
                return $this->runHook();
            case 'GET':
                return $this->runInfo();
        }

        http_response_code(400);

        return '<h1>Webhook: Bad request.</h1>';
    }

    public function runHook()
    {
        //
        if (!$this->hook) {
            return $this->error('Missing hook.');
        }

        //
        $manifest = $this->loadManifest();
        if (!isset($manifest['hook']) || !$manifest['hook']) {
            return $this->error('Manifest without hooks.');
        } elseif (!isset($manifest['hook'][$this->hook])) {
            return $this->error("Undefined hook '{$this->hook}'.");
        }

        //
        $this->eventLog->info("ackn '{$this->hook}'");

        // Add to ONCE requested task or exec
        foreach ($manifest['hook'][$this->hook] as $key => $value) {
            if ($key == 'task') {
                if (is_array($value)) {
                    foreach ($value as $t) {
                        $manifest['once'][] = $this->getTaskExec($t);
                    }
                } else {
                    $manifest['once'][] = $this->getTaskExec($value);
                }
            } elseif ($key == 'exec') {
                if (is_array($value)) {
                    foreach ($value as $t) {
                        $manifest['once'][] = $value;
                    }
                } else {
                    $manifest['once'][] = $value;
                }
            }
        }

        //
        $manifest['once'] = array_unique($manifest['once']);
        $this->saveManifest($manifest);

        return json_encode($manifest['once']);
    }

    public function runInfo()
    {
        echo '<h1>Webhook: Informations</h1>';

        $manifest = $this->loadManifest();

        //
        if (is_array($manifest['hook'])) {
            foreach ($manifest['hook'] as $hook => $task) {
                $host = $_SERVER['HTTP_HOST'];
                $base = dirname($_SERVER['REQUEST_URI']);
                $webhook = "http://{$host}/{$base}/webhook.php?hook={$hook}";
                echo '<pre>'.$webhook.'</pre>';
            }
        }

        //
        if (@$manifest['once']) {
            echo '<h2>Penging</h2>';
            foreach ($manifest['once'] as $task) {
                echo '<pre>'.$task.'</pre>';
            }
        }

        //
        $log = $this->basePath.'/logs/'.$this->info.'.log';
        if (file_exists($log)) {
            echo '<h2>Log: '.$log.'</h2>';
            echo '<pre>'.file_get_contents($log).'</pre>';
        }

        //
        echo '<style>pre{border:#ccc;background:#eee;padding:5px;margin:0 0 10px 0;}</style>';
        echo '<style>h1{margin:0 0 5px 0;}h2{margin:20px 0 5px 0;}</style>';
        echo '<script>setTimeout("window.location.reload()", 5000);</script>';
    }

    protected function error($message)
    {
        return json_encode(['error' => $message]);
    }
}
