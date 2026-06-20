<?php

namespace Brainerd;

/**
 * Dashboard Widget — shows detected plugins and integration status at a glance.
 */
final class Dashboard_Widget {

	public static function register(): void {
		wp_add_dashboard_widget(
			'brainerd_companion_status',
			'Brainerd Theme Ecosystem',
			[ self::class, 'render' ]
		);

		add_action( 'admin_head', function (): void {
			?>
			<script>
			document.addEventListener('DOMContentLoaded', function() {
				var el = document.querySelector('#brainerd_companion_status .hndle span');
				if (el) el.innerHTML = el.textContent.replace('Brainerd', 'Br<span style="color:#e84d22">ai</span>nerd');
			});
			</script>
			<?php
		} );
	}

	public static function render(): void {
		$registry     = Integration_Registry::instance();
		$integrations = $registry->all();
		$theme        = wp_get_theme();

		$detected  = array_filter( $integrations, fn( $i ) => $i['detected'] );
		$styled    = array_filter( $detected, fn( $i ) => $i['has_css'] && $i['enabled'] );
		$unstyled  = array_filter( $detected, fn( $i ) => ! $i['has_css'] );
		$disabled  = array_filter( $detected, fn( $i ) => ! $i['enabled'] );

		echo '<div class="brainerd-dashboard">';

		$companion_ver = get_plugin_data( BRAINERD_COMPANION_DIR . 'brainerd-companion.php' )['Version'] ?? '0.1.0-alpha';
		echo '<p style="margin-top:0;color:#666;font-size:13px;">';
		echo 'Br<span style="color:#e84d22">ai</span>nerd v' . esc_html( $theme->get( 'Version' ) );
		echo ' &middot; Companion v' . esc_html( $companion_ver );
		echo '</p>';

		echo '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:16px 0;">';

		self::stat_card( count( $detected ), 'Plugins detected' );
		self::stat_card( count( $styled ), 'Styled integrations' );
		self::stat_card( count( $unstyled ) + count( $disabled ), 'Unstyled / disabled' );

		echo '</div>';

		if ( $integrations ) {
			echo '<table class="widefat striped" style="margin-top:12px;">';
			echo '<thead><tr><th>Plugin</th><th>Status</th><th>Override</th></tr></thead><tbody>';

			foreach ( $integrations as $slug => $int ) {
				$status = 'Not detected';
				$color  = '#999';
				$badge  = '';

				if ( $int['detected'] ) {
					if ( ! $int['enabled'] ) {
						$status = 'Disabled';
						$color  = '#996633';
						$badge  = '<span style="background:#fef3cd;color:#856404;padding:2px 8px;border-radius:3px;font-size:11px;">off</span>';
					} elseif ( $int['has_css'] ) {
						$status = 'Active + styled';
						$color  = '#0a7b3e';
						$badge  = '<span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:3px;font-size:11px;">styled</span>';
					} else {
						$status = 'Active';
						$color  = '#0969da';
						$badge  = '<span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:3px;font-size:11px;">no override</span>';
					}
				}

				echo '<tr>';
				echo '<td><strong>' . esc_html( $int['label'] ) . '</strong></td>';
				echo '<td style="color:' . esc_attr( $color ) . '">' . esc_html( $status ) . '</td>';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- controlled badge HTML
				echo '<td>' . $badge . '</td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
		}

		echo '<p style="margin-top:16px;"><a href="' . esc_url( admin_url( 'admin.php?page=brainerd-integrations' ) ) . '">Manage integrations &rarr;</a></p>';

		echo '</div>';
	}

	private static function stat_card( int $count, string $label ): void {
		echo '<div style="background:#f0f0f1;border-radius:6px;padding:14px 16px;text-align:center;">';
		echo '<div style="font-size:24px;font-weight:600;line-height:1;margin-bottom:4px;">' . esc_html( (string) $count ) . '</div>';
		echo '<div style="font-size:11px;color:#666;text-transform:uppercase;letter-spacing:0.05em;">' . esc_html( $label ) . '</div>';
		echo '</div>';
	}
}
