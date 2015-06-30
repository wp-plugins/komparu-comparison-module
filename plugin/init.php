<?php

ini_set('pcre.backtrack_limit', '10485760');

/** @var \Herbert\Framework\Plugin $plugin */
SessionHelper::getSid($plugin);

if (is_admin()) {
    KomparuClient::getInstance($plugin);
}
