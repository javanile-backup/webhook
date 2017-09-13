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


        var_dump($parts);

        return $url.'/webhook.php';
    }

}