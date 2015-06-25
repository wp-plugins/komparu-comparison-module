<?php

class SessionHelper
{
    public static function getSid($plugin)
    {
        if (!session_id()) {
            session_start();
        }
        if (!isset($_SESSION['kmpsid'])) {
            $json               = (new GuzzleHttp\Client())->post('http://code.komparu.'.$plugin->config['target'].'/kmpsid')->json();
            $_SESSION['kmpsid'] = $json['session_id'];
        }

        return $_SESSION['kmpsid'];
    }

}