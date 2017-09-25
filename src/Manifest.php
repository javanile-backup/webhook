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

class Manifest
{
    protected $manifest;

    public function __construct($manifest = null)
    {
        $this->manifest = realpath($manifest);
        $this->basePath = dirname($this->manifest);
    }

    public function loadManifest()
    {
        return json_decode(file_get_contents($this->manifest), true);
    }

    public function saveManifest($manifest)
    {
        return file_put_contents(
            $this->manifest,
            json_encode(
                $manifest,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }

    public function hasManifestError()
    {
        return json_last_error() !== JSON_ERROR_NONE;
    }

    public function getManifestError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return [
                    'status' => 0,
                    'value'  => '',
                ];

            case JSON_ERROR_DEPTH:
                return [
                    'status' => 1,
                    'value'  => 'Maximum stack depth exceeded',
                ];

            case JSON_ERROR_STATE_MISMATCH:
                return [
                    'status' => 1,
                    'value'  => 'Underflow or the modes mismatch',
                ];

            case JSON_ERROR_CTRL_CHAR:
                return [
                    'status' => 1,
                    'value'  => 'Unexpected control character found',
                ];

            case JSON_ERROR_SYNTAX:
                return [
                    'status' => 1,
                    'value'  => 'Syntax error, malformed JSON',
                ];

            case JSON_ERROR_UTF8:
                return [
                    'status' => 1,
                    'value'  => 'Malformed UTF-8 characters, possibly incorrectly encoded',
                ];

            default:
                return [
                    'status' => 1,
                    'value'  => 'Unknown error',
                ];
        }
    }
}
