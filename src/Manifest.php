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

class Manifest
{
    /**
     *
     */
    protected $manifest;

    /**
     *
     */
    protected $errorLog;

    /**
     *
     */
    public function __construct($manifest = null)
    {
        $this->manifest = realpath($manifest);
        $this->basePath = dirname($this->manifest);

        $errorLogFile = $this->basePath.'/logs/error.log';
        $this->errorLog = new Logger('CRON');
        $this->errorLog->pushHandler(new StreamHandler($errorLogFile, Logger::INFO));
    }

    /**
     *
     */
    public function loadManifest()
    {
        $manifest = json_decode(file_get_contents($this->manifest), true);

        if (!$manifest) {
            $this->errorLog->error('Manifest error: '.$this->getManifestError());
        }

        return $manifest;
    }

    /**
     *
     */
    public function saveManifest($manifest)
    {
        if (!$manifest) {
            $this->errorLog->error("Try to save empty manifeset.", debug_backtrace());

            return;
        }

        return file_put_contents(
            $this->manifest,
            json_encode(
                $manifest,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }

    /**
     *
     */
    public function hasManifestError()
    {
        return json_last_error() !== JSON_ERROR_NONE;
    }

    /**
     *
     */
    public function getManifestError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'Empty manifest';

            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';

            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';

            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';

            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';

            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';

            default:
                return 'Unknown error';
        }
    }
}
