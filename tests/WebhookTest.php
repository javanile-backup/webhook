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
        $endpoint = new WebhookEndpoint([
            'manifest' => __DIR__.'/samples/manifest1.json',
            'input'    => __DIR__.'/samples/input1.json',
            'hook'     => 'test',
        ]);

        $output = $endpoint->run();

        $this->assertEquals($output, '{"error":"Manifest without hooks."}');
    }
}
