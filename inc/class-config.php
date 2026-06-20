<?php

namespace Brainerd;

/**
 * Config — two-tier settings system for site chrome.
 *
 * Core fields ship with the theme. Extended fields are registered by AI or
 * plugins during the build. All stored in wp_options as a single serialized
 * array. Template helper: brainerd_config('key') or brainerd_config('key', 'fallback').
 *
 * Every value carries provenance metadata:
 *   - source: client-stated | inferred | default
 *   - origin: (optional) figma:path, screenshot:file, conversation:, default:
 *   - origin_file: (optional) source file reference
 *
 * @see project_config_system.md for full spec.
 */
final class Config {

	private static ?self $instance = null;

	private const OPTION_KEY   = 'brainerd_config';
	private const SPEC_VERSION = '0.1.0';

	/** @var array<string, array> Field definitions (schema). */
	private array $fields = [];

	/** @var array<string, mixed> Loaded config values. */
	private array $values = [];

	private bool $loaded = false;

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	private function __construct() {
		$this->register_core_fields();
	}

	/**
	 * Register a config field.
	 *
	 * @param string $key    Unique field key.
	 * @param array  $args   {
	 *     @type string $label       Display label.
	 *     @type string $type        Field type: text, url, email, tel, textarea, toggle, color, nav, social.
	 *     @type string $section     'core' or 'extended'.
	 *     @type string $group       Display group for admin UI (e.g., 'identity', 'contact', 'navigation').
	 *     @type mixed  $default     Default value.
	 *     @type string $description Help text.
	 * }
	 */
	public function register( string $key, array $args ): void {
		$this->fields[ $key ] = wp_parse_args( $args, [
			'label'       => $key,
			'type'        => 'text',
			'section'     => 'extended',
			'group'       => 'general',
			'default'     => '',
			'description' => '',
		] );
	}

	/**
	 * Get a config value.
	 *
	 * @param string $key      Field key.
	 * @param mixed  $fallback Fallback if not set.
	 * @return mixed The value (unwrapped from provenance envelope).
	 */
	public function get( string $key, $fallback = '' ) {
		$this->load();
		$entry = $this->values[ $key ] ?? null;

		if ( is_array( $entry ) && isset( $entry['value'] ) ) {
			return $entry['value'];
		}

		if ( $entry !== null ) {
			return $entry;
		}

		return $this->fields[ $key ]['default'] ?? $fallback;
	}

	/**
	 * Get the full provenance envelope for a value.
	 *
	 * @return array{value: mixed, source: string, origin?: string, origin_file?: string}
	 */
	public function get_with_provenance( string $key ): array {
		$this->load();
		$entry = $this->values[ $key ] ?? null;

		if ( is_array( $entry ) && isset( $entry['value'] ) ) {
			return $entry;
		}

		return [
			'value'  => $entry ?? ( $this->fields[ $key ]['default'] ?? '' ),
			'source' => 'default',
		];
	}

	/**
	 * Set a config value with provenance.
	 */
	public function set( string $key, $value, string $source = 'client-stated', string $origin = '', string $origin_file = '' ): void {
		$this->load();

		$entry = [ 'value' => $value, 'source' => $source ];
		if ( $origin )      $entry['origin']      = $origin;
		if ( $origin_file ) $entry['origin_file'] = $origin_file;

		$this->values[ $key ] = $entry;
	}

	/**
	 * Save current values to the database.
	 */
	public function save(): void {
		$data = [
			'specVersion' => self::SPEC_VERSION,
			'values'      => $this->values,
		];
		update_option( self::OPTION_KEY, $data, false );
	}

	/**
	 * Get all registered field definitions.
	 *
	 * @return array<string, array>
	 */
	public function get_fields(): array {
		return $this->fields;
	}

	/**
	 * Get fields grouped by their display group.
	 *
	 * @return array<string, array<string, array>>
	 */
	public function get_fields_by_group(): array {
		$grouped = [];
		foreach ( $this->fields as $key => $field ) {
			$grouped[ $field['group'] ][ $key ] = $field;
		}
		return $grouped;
	}

	/**
	 * Validate WCAG AA contrast between two hex colors.
	 *
	 * @return array{ratio: float, aa: bool, aaa: bool}
	 */
	public static function check_contrast( string $hex1, string $hex2 ): array {
		$l1 = self::relative_luminance( $hex1 );
		$l2 = self::relative_luminance( $hex2 );
		$ratio = ( max( $l1, $l2 ) + 0.05 ) / ( min( $l1, $l2 ) + 0.05 );

		return [
			'ratio' => round( $ratio, 2 ),
			'aa'    => $ratio >= 4.5,
			'aaa'   => $ratio >= 7.0,
		];
	}

	// ── Private ──────────────────────────────────────────────────────────────

	private function load(): void {
		if ( $this->loaded ) return;
		$this->loaded = true;

		$data = get_option( self::OPTION_KEY, [] );

		if ( isset( $data['values'] ) ) {
			$this->values = $data['values'];
		}
	}

	private function register_core_fields(): void {
		$core = [
			'site_name' => [
				'label' => 'Site name', 'type' => 'text', 'group' => 'identity',
				'default' => get_bloginfo( 'name' ),
				'description' => 'Used in header, footer, and meta tags.',
			],
			'tagline' => [
				'label' => 'Tagline', 'type' => 'text', 'group' => 'identity',
				'default' => get_bloginfo( 'description' ),
			],
			'copyright' => [
				'label' => 'Copyright line', 'type' => 'text', 'group' => 'identity',
				'default' => '&copy; ' . gmdate( 'Y' ) . ' Your Company',
			],
			'phone' => [
				'label' => 'Phone', 'type' => 'tel', 'group' => 'contact',
				'default' => '',
			],
			'email' => [
				'label' => 'Email', 'type' => 'email', 'group' => 'contact',
				'default' => '',
			],
			'address' => [
				'label' => 'Address', 'type' => 'textarea', 'group' => 'contact',
				'default' => '',
				'description' => 'Physical address (optional).',
			],
			'cta_text' => [
				'label' => 'Header CTA text', 'type' => 'text', 'group' => 'navigation',
				'default' => 'GET IN TOUCH',
			],
			'cta_url' => [
				'label' => 'Header CTA link', 'type' => 'url', 'group' => 'navigation',
				'default' => '/contact/',
			],
			'dark_mode' => [
				'label' => 'Enable dark mode toggle', 'type' => 'toggle', 'group' => 'appearance',
				'default' => true,
			],
		];

		foreach ( $core as $key => $args ) {
			$args['section'] = 'core';
			$this->register( $key, $args );
		}
	}

	private static function relative_luminance( string $hex ): float {
		$hex = ltrim( $hex, '#' );
		$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
		$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
		$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

		$r = $r <= 0.03928 ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = $g <= 0.03928 ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = $b <= 0.03928 ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}
}
