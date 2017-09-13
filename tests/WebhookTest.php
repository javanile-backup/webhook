<?php

namespace Javanile\Webhook\Tests;

use Javanile\Producer;
use PHPUnit\Framework\TestCase;
use Javanile\Webhook\Endpoint as WebhookEndpoint;

Producer::addPsr4(['Javanile\\Webhook\\Tests\\' => __DIR__]);

final class WebhookTest extends TestCase
{
    public function testEndpoint()
    {
        $endpoint = new WebhookEndpoint(__DIR__.'/samples/manifest1.json');

        $this->assertEquals(0, 0);
    }
}
