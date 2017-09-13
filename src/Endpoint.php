<?php
/**
 * Class MainPrivateApplication
 */
namespace Javanile\Webhook;

class Endpoint extends Manifest
{
    public function run()
    {
        $hook = filter_input(INPUT_GET, 'hook');
        if (!$hook) {
            return $this->error('Missing hook.');
        }

        $manifest = $this->loadManifest();
        if (!isset($manifest['hook']) || !$manifest['hook']) {
            return $this->error('Manifest without hooks.');
        } elseif (!isset($manifest['hook'][$hook])) {
            return $this->error("Undefined hook '{$hook}'.");
        }

        $task = $manifest['hook'][$hook]['task'];

        $manifest['once'][] = $task;

        $manifest['once'] = array_unique($manifest['once']);

        $this->saveManifest($manifest);

        $input = json_decode(file_get_contents('php://input'), true);

        $resp = $manifest;
        $resp = $input;

        #echo $input['ref'];

        return json_encode($manifest['once']);
    }

    protected function error($message)
    {
        return json_encode(['error' => $message]);
    }
}
