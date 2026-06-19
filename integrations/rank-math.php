<?php
/**
 * Integration: Rank Math SEO (stub)
 *
 * Detection only — Rank Math's frontend output is minimal.
 * Future: breadcrumb styling, schema markup hints in dashboard.
 */
return [
	'slug'   => 'rank-math',
	'label'  => 'Rank Math SEO',
	'detect' => fn() => class_exists( 'RankMath' ),
	'css'    => null,
	'init'   => null,
];
