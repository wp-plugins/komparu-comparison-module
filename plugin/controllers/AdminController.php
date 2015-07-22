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

    public function clear($token = '')
    {
        $GLOBALS['wpdb']->get_results(sprintf(
            'delete from `%soptions` where `option_name` like "%%cmpmd%%%s%%"',
            $GLOBALS['wpdb']->prefix,
            $token
        ));

        if ($token != '') {
            (new GuzzleHttp\Client())->get(sprintf(
                'http://code.komparu.%s/%s?__reset=&format=plugin',
                $this->plugin->config['target'],
                $token
            ));
        }
    }

    public function delete()
    {
        add_filter('posts_where', function ($where) {
            return $where . ' AND (' . $GLOBALS['wpdb']->posts . '.guid LIKE "%uploads/compmodule%") ';
        }, 10, 2);

        $q = new WP_Query([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 9999,
        ]);

        while ($attachment = $q->next_post()) {
            wp_delete_attachment($attachment->ID);
            try {
                unlink(str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $attachment->guid));
            } catch(Exception $e) {
            }
        }

    }
}