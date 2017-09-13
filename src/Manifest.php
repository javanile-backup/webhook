<?php
/**
 * Class MainPrivateApplication
 */
namespace Javanile\Webhook;

class Manifest
{
    protected $manifest;

    public function __construct($manifest = null)
    {
        $this->manifest = realpath($manifest);
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
}