<?php

/** @var \Herbert\Framework\Plugin $plugin */
SessionHelper::getSid($plugin);

if (is_admin()) {
    KomparuClient::getInstance($plugin);
}
