<?php
/**
 * Integration: Brainerd Mobile Nav
 *
 * The Brainerd theme ships with a built-in full-screen mobile nav overlay.
 * Disabling this integration sets the `brainerd_companion_disabled_mobile_nav`
 * option, which tells the theme to skip loading its mobile nav JS.
 *
 * This allows users to cleanly replace the built-in nav with a third-party
 * plugin (e.g., Responsive Menu, UberMenu) or a custom implementation.
 */
return [
	'slug'   => 'mobile-nav',
	'label'  => 'Built-in Mobile Nav',
	'detect' => fn() => wp_get_theme()->get( 'TextDomain' ) === 'brainerd',
	'css'    => null,
	'init'   => function() {
		$disabled = (array) get_option( 'brainerd_companion_disabled', [] );
		update_option( 'brainerd_companion_disabled_mobile_nav', in_array( 'mobile-nav', $disabled, true ) );
	},
];
