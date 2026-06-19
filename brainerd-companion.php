<?php
/**
 * Plugin Name:       Brainerd Companion
 * Description:       Plugin detection, style overrides, and dashboard for the Brainerd theme ecosystem.
 * Version:           0.1.0-alpha
 * Requires PHP:      8.0
 * Requires at least: 6.4
 * Author:            Brainerd Street Picture Co.
 * Author URI:        https://tannermooredesign.com
 * Text Domain:       brainerd-companion
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'BRAINERD_COMPANION_DIR', plugin_dir_path( __FILE__ ) );
define( 'BRAINERD_COMPANION_URL', plugin_dir_url( __FILE__ ) );

require_once BRAINERD_COMPANION_DIR . 'inc/class-integration-registry.php';
require_once BRAINERD_COMPANION_DIR . 'inc/class-dashboard-widget.php';
require_once BRAINERD_COMPANION_DIR . 'inc/class-settings.php';

/**
 * Boot the companion plugin.
 */
add_action( 'plugins_loaded', function (): void {
	$registry = Brainerd\Integration_Registry::instance();

	foreach ( glob( BRAINERD_COMPANION_DIR . 'integrations/*.php' ) as $file ) {
		$integration = require $file;
		if ( is_array( $integration ) && ! empty( $integration['slug'] ) ) {
			$registry->register( $integration );
		}
	}

	$registry->init();
}, 20 );

add_action( 'wp_dashboard_setup', function (): void {
	Brainerd\Dashboard_Widget::register();
} );

add_action( 'admin_menu', function (): void {
	Brainerd\Settings::register();
} );

add_action( 'admin_init', function (): void {
	Brainerd\Settings::init();
} );
