<?php

/** @var \Herbert\Framework\Plugin $plugin */
SessionHelper::getSid($plugin);

if (is_admin()) {
    KomparuClient::getInstance($plugin);
}

add_action('upgrader_process_complete', function ($upgrader_object, $options) use ($plugin) {
    if (!($options['type'] == 'plugin' and in_array('compmodule/plugin.php', $options['plugins']))) {
        return $upgrader_object;
    }
    $admin = new AdminController($plugin);
    $admin->clear();
    $admin->delete();

    return $upgrader_object;
}, 10, 2);