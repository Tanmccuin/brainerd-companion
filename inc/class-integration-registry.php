<?php

namespace Brainerd;

/**
 * Integration Registry — discovers, registers, and loads third-party plugin integrations.
 *
 * Each integration is a PHP file in integrations/ that returns an array:
 *
 *   return [
 *       'slug'   => 'gravity-forms',           // Unique key
 *       'label'  => 'Gravity Forms',            // Display name
 *       'detect' => fn() => class_exists('GFForms'), // Is the plugin active?
 *       'css'    => 'css/gravity-forms.css',    // Style override (relative to integrations/)
 *       'init'   => null,                       // Optional callable for PHP hooks
 *   ];
 *
 * Adding an integration = dropping a file. No edits to this class needed.
 */
final class Integration_Registry {

	private static ?self $instance = null;

	/** @var array<string, array> Registered integrations keyed by slug. */
	private array $integrations = [];

	/** @var array<string, bool> Runtime detection cache. */
	private array $detected = [];

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	private function __construct() {}

	public function register( array $integration ): void {
		$slug = $integration['slug'] ?? '';
		if ( ! $slug ) {
			return;
		}

		$this->integrations[ $slug ] = wp_parse_args( $integration, [
			'slug'   => '',
			'label'  => $slug,
			'detect' => '__return_false',
			'css'    => null,
			'init'   => null,
		] );
	}

	public function init(): void {
		$disabled = (array) get_option( 'brainerd_companion_disabled', [] );

		foreach ( $this->integrations as $slug => $int ) {
			$this->detected[ $slug ] = is_callable( $int['detect'] ) && call_user_func( $int['detect'] );

			if ( ! $this->detected[ $slug ] || in_array( $slug, $disabled, true ) ) {
				continue;
			}

			if ( $int['css'] ) {
				$css_url = BRAINERD_COMPANION_URL . 'integrations/' . $int['css'];
				add_action( 'wp_enqueue_scripts', function () use ( $slug, $css_url ) {
					wp_enqueue_style(
						'brainerd-int-' . $slug,
						$css_url,
						[ 'gform_theme' ],
						'0.1.0-alpha'
					);
				}, 50 );
			}

			if ( is_callable( $int['init'] ) ) {
				call_user_func( $int['init'] );
			}
		}
	}

	/** @return array<string, array> All registered integrations with detection status. */
	public function all(): array {
		$disabled = (array) get_option( 'brainerd_companion_disabled', [] );
		$result   = [];

		foreach ( $this->integrations as $slug => $int ) {
			$result[ $slug ] = [
				'label'    => $int['label'],
				'detected' => $this->detected[ $slug ] ?? false,
				'has_css'  => ! empty( $int['css'] ),
				'has_init' => is_callable( $int['init'] ),
				'enabled'  => ! in_array( $slug, $disabled, true ),
			];
		}

		return $result;
	}
}
