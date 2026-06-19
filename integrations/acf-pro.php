<?php
/**
 * Integration: Advanced Custom Fields Pro
 *
 * Detection + init: ensures ACF JSON save/load paths are correct
 * when brainerd-blocks plugin is active.
 */
return [
	'slug'   => 'acf-pro',
	'label'  => 'ACF Pro',
	'detect' => fn() => class_exists( 'ACF' ) && defined( 'ACF_PRO' ),
	'css'    => null,
	'init'   => null,
];
