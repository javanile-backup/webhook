<?php
/**
 * Mount command for producer.
 *
 * PHP version 5
 *
 * @category   ProducerCommand
 *
 * @author     Francesco Bianco <bianco@javanile.org>
 * @license    https://goo.gl/KPZ2qI  MIT License
 * @copyright  2015-2017 Javanile.org
 */

namespace Javanile\Webhook;

class Endpoint extends Manifest
{
    protected $hook;

    protected $input;

    public function __construct($args)
    {
        foreach (['manifest', 'hook', 'input'] as $key) {
            if (!isset($args[$key])) {
                throw new \Exception("Argument required '{$key}'.");
            }
        }

        parent::__construct($args['manifest']);

        $this->hook = $args['hook'];
        $this->input = $args['input'];
    }

    public function run()
    {
        if (!$this->hook) {
            return $this->error('Missing hook.');
        }

        $manifest = $this->loadManifest();
        if (!isset($manifest['hook']) || !$manifest['hook']) {
            return $this->error('Manifest without hooks.');
        } elseif (!isset($manifest['hook'][$this->hook])) {
            return $this->error("Undefined hook '{$this->hook}'.");
        }

        $task = $manifest['hook'][$this->hook]['task'];

        $manifest['once'][] = $task;

        $manifest['once'] = array_unique($manifest['once']);

        $this->saveManifest($manifest);

        $resp = $manifest;
        $resp = $input;

        //echo $input['ref'];

        return json_encode($manifest['once']);
    }

    protected function error($message)
    {
        return json_encode(['error' => $message]);
    }
}
