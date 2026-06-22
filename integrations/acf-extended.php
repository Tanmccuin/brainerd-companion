<?php
/**
 * Integration: ACF Extended (ACFE)
 *
 * Detects ACFE and applies minor CSS overrides to align its admin UI
 * with the Brainerd dashboard styling. Also enables ACFE's enhanced
 * block features (better repeater UI, dynamic previews).
 *
 * Install ACFE: https://wordpress.org/plugins/acf-extended/
 */
return [
	'slug'   => 'acf-extended',
	'label'  => 'ACF Extended',
	'detect' => fn() => class_exists( 'ACFE' ) || class_exists( 'acfe' ),
	'css'    => null,
	'init'   => function() {
		// Enable ACFE's enhanced block UI features when available.
		if ( function_exists( 'acfe_update_setting' ) ) {
			acfe_update_setting( 'modules/block_types', true );
		}
	},
];
