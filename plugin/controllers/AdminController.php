<?php

class AdminController extends BaseController
{
    public function index()
    {
        $websites = array_filter(
            KomparuClient::getInstance($this->plugin)->resource('website')->get(['order' => 'product_type.id']),
            function ($website) {
                return $website['product_type']['implementation'] == 'komparu';
            }
        );

        return $this->view->render('admin/index', [
            'websites' => $websites
        ]);
    }

    public function settings()
    {
        ob_start();
        $this->plugin->settings->show_forms();

        return $this->view->render('admin/configure', [
            'forms' => ob_get_clean()
        ]);
    }

    public function clear($token)
    {
        $results = $GLOBALS['wpdb']->get_results("delete from `wp_options` where `option_name` like '%cmpmd%{$token}%'");
        $url     = sprintf(
            'http://code.komparu.%s/%s?__reset=&format=plugin&kmp-subid=demo',
            $this->plugin->config['target'],
            $token
        );
        $page = (new GuzzleHttp\Client())->get($url);

        exit();
    }
}