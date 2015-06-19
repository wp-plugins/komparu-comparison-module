<?php

class Api extends BaseController
{
    public function compmodule($token)
    {

        try {
            if (!($page = $this->getPageObject($token))) {
                return false;
            }

            add_action('wp_footer', function () use ($token) {
                echo '
                <script type="text/javascript" src="' . $this->plugin->siteUrl . '/compmodule/js/' . $token . '.js"></script>
                <script type="">jQuery(document).ready(function(){
                Kmp.sessionId="'.$sid = SessionHelper::getSid($this->plugin).'";
                });</script>';
            });

            echo $this->view->render('shortcode/index', [
                'css'  => $page->css,
                'html' => $page->html
            ]);
        } catch(Exception $e) {
            echo $this->view->render('shortcode/index', []);
        }

        return false;
    }
}

$apiName = $plugin->config['api'];
global $$apiName;
$$apiName    = new Api($plugin);
$plugin->api = $$apiName;

