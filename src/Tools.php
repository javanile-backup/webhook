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

class Tools extends Manifest
{
    /**
     * Get remote url for call webhook.
     *
     * @param mixed $url
     */
    public function getRemoteUrl($url = null)
    {
        if (!$url || $url == 'manifest') {
            $manifest = $this->loadManifest();
            $url = isset($manifest['url']) ? $manifest['url'] : 'http://localhost/';
        }

        return rtrim($url, '/').'/webhook.php';
    }

    /**
     * Get remote data for call webhook.
     */
    public function getRemoteData()
    {
        $data = [
            'ref' => 'refs/heads/master',
        ];

        return json_encode($data);
    }
}
