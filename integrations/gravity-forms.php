<?php
/**
 * Integration: Gravity Forms
 *
 * Loads a CSS override that maps GF's custom properties to Brainerd's --tmd-* tokens.
 * Covers: labels, inputs, buttons, validation, confirmation messages.
 * Dark mode support: inherits from --tmd-* variables automatically.
 */
return [
	'slug'   => 'gravity-forms',
	'label'  => 'Gravity Forms',
	'detect' => fn() => class_exists( 'GFForms' ),
	'css'    => 'css/gravity-forms.css',
	'init'   => null,
];
