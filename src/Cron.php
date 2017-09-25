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

        $manifest = $this->loadManifest();
        $needsave = false;

        foreach ($manifest['cron'] as $cron) {
            $time = $cron['time'];
            if (Parser::matchTime('now', $time)) {
                $needsave = true;
                $task = $cron['task'];
                $manifest['once'][] = $task;
            }
        }

        if ($needsave) {
            $this->saveManifest($manifest);
        }
    }

    /**
     *
     */
    public function feed()
    {
        $manifest = $this->loadManifest();

        if (!isset($manifest['once']) || !$manifest['once']) {
            return;
        }

        $task = array_pop($manifest['once']);

        if (!$manifest['once']) {
            unset($manifest['once']);
        }

        $manifest['skip'][] = $task;

        $this->saveManifest($manifest);

        $this->cronLog->info('FEED '.($task ? $task : '-- none --'));

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