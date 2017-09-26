<?php

namespace Javanile\Webhook\Tests;

use Javanile\Producer;
use Javanile\Webhook\Endpoint as WebhookEndpoint;
use PHPUnit\Framework\TestCase;

Producer::addPsr4(['Javanile\\Webhook\\Tests\\' => __DIR__]);

final class WebhookManifestTest extends TestCase
{
    public function testManifest()
    {
        $endpoint = new WebhookManifest(__DIR__.'/samples/manifest1.json');

        $output = $endpoint->run();

        $this->assertEquals($output, '{"error":"Manifest without hooks."}');
    }
}
