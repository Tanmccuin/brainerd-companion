<?php
/**
 * Integration: WooCommerce (stub)
 *
 * Detection only — CSS override not yet built.
 * When built, add css/woocommerce.css targeting cart, checkout, product cards,
 * and account pages using --tmd-* tokens.
 */
return [
	'slug'   => 'woocommerce',
	'label'  => 'WooCommerce',
	'detect' => fn() => class_exists( 'WooCommerce' ),
	'css'    => null,
	'init'   => null,
];
