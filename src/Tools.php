<?php
/**
 * Class MainPrivateApplication
 */
namespace Javanile\Webhook;

class Tools
{


    public function getRemoteUrl($url)
    {

        $parts = parse_url($url);

        return $url.'/webhook.php';
    }

}