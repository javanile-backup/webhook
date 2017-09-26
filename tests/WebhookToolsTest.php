<?php

namespace Javanile\Webhook\Tests;

use Javanile\Producer;
use Javanile\Webhook\Endpoint as WebhookTools;
use PHPUnit\Framework\TestCase;

Producer::addPsr4(['Javanile\\Webhook\\Tests\\' => __DIR__]);

final class WebhookToolsTest extends TestCase
{
    public function testCronTools()
    {
        $tools = new WebhookTools(__DIR__.'/samples/manifest1.json');

        $this->assertEquals($output, '{"error":"Manifest without hooks."}');
    }

    public function testRemoteTools()
    {
        $tools = new WebhookTools(__DIR__.'/samples/manifest1.json');

        $this->assertEquals($output, '{"error":"Manifest without hooks."}');
    }
}
