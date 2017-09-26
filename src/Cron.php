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
use Yalesov\CronExprParser\Parser;

class Cron extends Manifest
{
    /**
     *
     */
    protected $cronLog;

    /**
     *
     */
    public function __construct($manifest)
    {
        parent::__construct($manifest);

        $cronLogFile = $this->basePath.'/logs/cron.log';
        $this->cronLog = new Logger('CRON');
        $this->cronLog->pushHandler(new StreamHandler($cronLogFile, Logger::INFO));
    }

    /**
     *
     */
    public function init()
    {
        $this->cronLog->info('INIT');

        //
        $manifest = $this->loadManifest();
        $needsave = false;

        //
        if (!is_array($manifest['cron']) || empty($manifest['cron'])) {
            return;
        }

        //
        foreach ($manifest['cron'] as $cron) {
            if (!isset($cron['time'])) {
                continue;
            }
            $time = $cron['time'];
            if (Parser::matchTime('now', $time)) {
                $needsave = true;
                foreach ($cron as $key => $value) {
                    if ($key == 'task') {
                        $task = './tasks/'.$value;
                        $manifest['once'][] = $task;
                    } elseif ($key == 'exec') {
                        $task = $value;
                        $manifest['once'][] = $task;
                    }
                }
            }
        }

        //
        if ($needsave) {
            $manifest['once'] = array_unique($manifest['once']);
            $this->saveManifest($manifest);
        }
    }

    /**
     *
     */
    public function feed()
    {
        $manifest = $this->loadManifest();

        if (!isset($manifest['once']) || empty($manifest['once'])) {
            return;
        }

        $task = array_shift($manifest['once']);

        if (isset($manifest['once']) && empty($manifest['once'])) {
            unset($manifest['once']);
        }

        $manifest['skip'][] = $task;

        $this->saveManifest($manifest);

        $this->cronLog->info('FEED '.$task);

        return $task;
    }

    /**
     *
     */
    public function done()
    {
        $this->cronLog->info('DONE');
        $manifest = $this->loadManifest();
        unset($manifest['skip']);
        $this->saveManifest($manifest);
    }
}
