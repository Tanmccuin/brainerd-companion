<?php

namespace Brainerd;

/**
 * Settings page — toggle integrations on/off.
 * Uses native WP Settings API (no ACF dependency).
 */
final class Settings {

	public static function register(): void {
		self::register_submenu();
	}

	public static function register_submenu(): void {
		add_submenu_page(
			'brainerd',
			'Integrations',
			'Integrations',
			'manage_options',
			'brainerd-integrations',
			[ self::class, 'render' ]
		);
	}

	public static function init(): void {
		register_setting( 'brainerd_companion', 'brainerd_companion_disabled', [
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => function ( $val ) {
				return is_array( $val ) ? array_map( 'sanitize_key', $val ) : [];
			},
		] );
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$registry     = Integration_Registry::instance();
		$integrations = $registry->all();
		$disabled     = (array) get_option( 'brainerd_companion_disabled', [] );

		?>
		<div class="wrap">
			<h1>Br<span style="color:#e84d22;">ai</span>nerd Companion</h1>
			<p>Manage which plugin integrations are active. Detected plugins automatically get style overrides loaded. Disable an integration to use the plugin's default styling.</p>

			<form method="post" action="options.php">
				<?php settings_fields( 'brainerd_companion' ); ?>

				<table class="widefat striped" style="max-width:700px;margin-top:20px;">
					<thead>
						<tr>
							<th style="width:40px;">On</th>
							<th>Integration</th>
							<th>Plugin</th>
							<th>Overrides</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $integrations as $slug => $int ) : ?>
							<tr>
								<td>
									<?php if ( $int['detected'] ) : ?>
										<input
											type="checkbox"
											name="brainerd_integration_enabled[<?php echo esc_attr( $slug ); ?>]"
											value="1"
											<?php checked( $int['enabled'] ); ?>
											onchange="document.getElementById('brainerd-disabled-<?php echo esc_attr( $slug ); ?>').disabled = this.checked;"
										>
										<input
											type="hidden"
											name="brainerd_companion_disabled[]"
											id="brainerd-disabled-<?php echo esc_attr( $slug ); ?>"
											value="<?php echo esc_attr( $slug ); ?>"
											<?php disabled( $int['enabled'] ); ?>
										>
									<?php else : ?>
										<span style="color:#999;" title="Plugin not detected">&#8212;</span>
									<?php endif; ?>
								</td>
								<td><strong><?php echo esc_html( $int['label'] ); ?></strong></td>
								<td>
									<?php if ( $int['detected'] ) : ?>
										<span style="color:#0a7b3e;">Active</span>
									<?php else : ?>
										<span style="color:#999;">Not installed</span>
									<?php endif; ?>
								</td>
								<td>
									<?php
									$parts = [];
									if ( $int['has_css'] )  $parts[] = 'CSS';
									if ( $int['has_init'] ) $parts[] = 'PHP';
									echo $parts ? esc_html( implode( ' + ', $parts ) ) : '<span style="color:#999;">none</span>';
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php submit_button( 'Save Changes' ); ?>
			</form>

			<hr>
			<h2>Adding an integration</h2>
			<p>Drop a PHP file in <code>brainerd-companion/integrations/</code> that returns an array:</p>
			<pre style="background:#f6f7f7;padding:16px;border-radius:4px;max-width:700px;overflow-x:auto;font-size:13px;line-height:1.6;">&lt;?php
return [
    'slug'   =&gt; 'my-plugin',
    'label'  =&gt; 'My Plugin',
    'detect' =&gt; fn() =&gt; class_exists( 'My_Plugin' ),
    'css'    =&gt; 'css/my-plugin.css',   // relative to integrations/
    'init'   =&gt; null,                   // optional PHP callable
];</pre>
			<p>CSS files go in <code>integrations/css/</code>. Use <code>--tmd-*</code> tokens for colors so overrides respect the active theme palette and dark mode.</p>
		</div>
		<?php
	}
}
