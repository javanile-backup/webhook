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

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Endpoint extends Manifest
{
    /**
     *
     */
    protected $hook;

    /**
     *
     */
    protected $input;

    /**
     *
     */
    protected $cronLog;

    /**
     *
     */
    public function __construct($args)
    {
        foreach (['manifest', 'hook', 'input'] as $key) {
            if (!isset($args[$key])) {
                throw new \Exception("Argument required '{$key}'.");
            }
        }

        parent::__construct($args['manifest']);

        $this->hook = $args['hook'];
        $this->input = $args['input'];

        $accessLogFile = $this->basePath.'/logs/access.log';
        $this->accessLog = new Logger('ACCESS');
        $this->accessLog->pushHandler(new StreamHandler($accessLogFile, Logger::INFO));
    }

    /**
     *
     */
    public function run()
    {
        $this->accessLog->info($_SERVER['REQUEST_URI']);

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

        // Add to ONCE requested task or exec
        foreach ($manifest['hook'][$this->hook] as $key => $value) {
            if ($key == 'task') {
                $task = './tasks/'.$value;
                $manifest['once'][] = $task;
            } elseif ($key == 'exec') {
                $task = $value;
                $manifest['once'][] = $task;
            }
        }

        //
        $manifest['once'] = array_unique($manifest['once']);
        $this->saveManifest($manifest);

        return json_encode($manifest['once']);
    }

    /**
     *
     */
    protected function error($message)
    {
        return json_encode(['error' => $message]);
    }
}
