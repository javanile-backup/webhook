<?php

namespace Javanile\Webhook\Tests;

use Javanile\Producer;
use Javanile\Webhook\Endpoint as WebhookEndpoint;
use PHPUnit\Framework\TestCase;

Producer::addPsr4(['Javanile\\Webhook\\Tests\\' => __DIR__]);

final class WebhookCronTest extends TestCase
{
    public function testCronProcess()
    {
        $cron = new Cron(__DIR__.'/samples/manifest-cron-1.json');

        $cron = $cron->init();
        $this->assertEquals($output, '{"error":"Manifest without hooks."}');

        $cron = $cron->feed();
        $this->assertEquals($output, '{"error":"Manifest without hooks."}');

        $cron = $cron->feed();
        $this->assertEquals($output, '{"error":"Manifest without hooks."}');

        $cron = $cron->done();
        $this->assertEquals($output, '{"error":"Manifest without hooks."}');
    }

    public function testCronEmpty()
    {
        $cron = new Cron(__DIR__ . '/samples/manifest-cron-2.json');

        $cron = $cron->init();
        $this->assertEquals($output, '{"error":"Manifest without hooks."}');
    }
}
