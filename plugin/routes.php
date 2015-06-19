<?php

$plugin->route->get([
    'as'    => 'compmoduleCode',
    'route' => $plugin->config['rewrite'] . 'code/:route'
], 'CodeController@get');

$plugin->route->get([
    'as'    => 'compmoduleJavascript',
    'route' => $plugin->config['rewrite'] . 'js/:token.js'
], 'JavaScriptController@get');

$plugin->route->get([
    'as'    => 'compmoduleMedia',
    'route' => str_replace(get_site_url(), '', wp_upload_dir()['baseurl']) . '/compmodule/:image'
], 'MediaController@get');