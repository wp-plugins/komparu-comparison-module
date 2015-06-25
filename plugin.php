<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Komparu
 * Plugin URI:        http://komparu.com/
 * Description:       A plugin.
 * Version:           1.0.5
 * Author:            Komparu B.V.
 * Author URI:        http://komparu.com/wordpress-plugin
 * License:           MIT
 */

require_once __DIR__ . '/vendor/autoload.php';

// Initialise framework
$plugin = new Herbert\Framework\Plugin();

if ( $plugin->config['eloquent'] ) {
	$plugin->database->eloquent();
}

if ( ! get_option( 'permalink_structure' ) ) {
	$plugin->message->error( $plugin->name . ': Please ensure you have permalinks enabled.' );
}