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
        $GLOBALS['wpdb']->get_results(sprintf(
            'delete from `%soptions` where `option_name` like "%%cmpmd%%%s%%"',
            $GLOBALS['wpdb']->prefix,
            $token
        ));

        (new GuzzleHttp\Client())->get(sprintf(
            'http://code.komparu.%s/%s?__reset=&format=plugin',
            $this->plugin->config['target'],
            $token
        ));

        exit();
    }
}