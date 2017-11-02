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
use Yalesov\CronExprParser\Parser;

class Cron extends Manifest
{
    /**
     * Cron moment to match.
     */
    protected $now;

    /**
     * Cron log handler.
     */
    protected $cronLog;

    /**
     * Init as Cron handler.
     */
    public function __construct($manifest, $now = 'now')
    {
        parent::__construct($manifest);

        $this->now = $now;

        //
        $cronLogFile = $this->basePath.'/logs/cron.log';
        $this->cronLog = new Logger('CRON');
        $this->cronLog->pushHandler(new StreamHandler($cronLogFile, Logger::INFO));
    }

    /**
     * Cron session init.
     */
    public function runInit()
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
            if (Parser::matchTime($this->now, $time)) {
                $needsave = true;
                foreach ($cron as $key => $value) {
                    if ($key == 'task') {
                        if (is_array($value)) {
                            foreach ($value as $t) {
                                if (!$t) {
                                    continue;
                                }
                                $manifest['once'][] = $this->getTaskExec($t);
                            }
                        } else {
                            if (!$value) {
                                $manifest['once'][] = $this->getTaskExec($value);
                            }
                        }
                    } elseif ($key == 'exec') {
                        if (is_array($value)) {
                            foreach ($value as $t) {
                                if (!$t) {
                                    continue;
                                }
                                $manifest['once'][] = $t;
                            }
                        } else {
                            if (!$value) {
                                $manifest['once'][] = $value;
                            }
                        }
                    }
                }
            }
        }

        //
        if ($needsave) {
            $manifest['once'] = count($manifest['once']) > 1
                ? array_unique($manifest['once']) : $manifest['once'];
            $this->saveManifest($manifest);
        }
    }

    /**
     *
     */
    public function runFeed()
    {
        $manifest = $this->loadManifest();

        if (!isset($manifest['once'])
            || empty($manifest['once'])
            || !is_array($manifest['once'])) {
            return;
        }

        $task = array_shift($manifest['once']);

        if (isset($manifest['once']) && empty($manifest['once'])) {
            unset($manifest['once']);
        }

        $manifest['skip'][] = $task;

        $this->saveManifest($manifest);

        $this->cronLog->info('FEED '.$task);
        $this->eventLog->info("exec '{$task}'");

        return $task;
    }

    /**
     *
     */
    public function runStop()
    {
        $this->cronLog->info('STOP');
        $manifest = $this->loadManifest();
        unset($manifest['skip']);
        $this->saveManifest($manifest);
    }
}
