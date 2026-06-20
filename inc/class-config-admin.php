<?php

namespace Brainerd;

/**
 * Config Admin — renders the Brainerd settings page with Core + Extended fields.
 * Auto-generates the form from registered field definitions.
 */
final class Config_Admin {

	public static function register(): void {
		add_menu_page(
			'Brainerd',
			'Br<span style="color:#e84d22">ai</span>nerd',
			'manage_options',
			'brainerd',
			[ self::class, 'render' ],
			'dashicons-layout',
			59
		);

		add_submenu_page(
			'brainerd',
			'Site Config',
			'Site Config',
			'manage_options',
			'brainerd',
			[ self::class, 'render' ]
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$config = Config::instance();

		if ( isset( $_POST['brainerd_config_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['brainerd_config_nonce'] ), 'brainerd_save_config' ) ) {
			self::handle_save( $config );
			echo '<div class="notice notice-success"><p>Config saved.</p></div>';
		}

		$groups = $config->get_fields_by_group();
		$group_labels = [
			'identity'   => 'Site identity',
			'contact'    => 'Contact information',
			'navigation' => 'Navigation & CTA',
			'appearance' => 'Appearance',
			'general'    => 'Extended settings',
		];

		?>
		<div class="wrap">
			<h1>Br<span style="color:#e84d22">ai</span>nerd Site Config</h1>
			<p style="color:#666;">Global site settings. Changes here update the header, footer, and site-wide elements. Design tokens (colors, fonts, spacing) are managed in <code>theme.json</code>.</p>

			<form method="post">
				<?php wp_nonce_field( 'brainerd_save_config', 'brainerd_config_nonce' ); ?>

				<?php foreach ( $groups as $group => $fields ) : ?>
					<h2 style="margin-top:2rem;"><?php echo esc_html( $group_labels[ $group ] ?? ucfirst( $group ) ); ?></h2>
					<table class="form-table">
						<?php foreach ( $fields as $key => $field ) :
							$prov  = $config->get_with_provenance( $key );
							$value = $prov['value'];
							$source = $prov['source'] ?? 'default';
						?>
							<tr>
								<th scope="row">
									<label for="brainerd_<?php echo esc_attr( $key ); ?>">
										<?php echo esc_html( $field['label'] ); ?>
									</label>
								</th>
								<td>
									<?php self::render_field( $key, $field, $value ); ?>
									<?php if ( $field['description'] ) : ?>
										<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
									<?php endif; ?>
									<?php if ( $source !== 'default' ) : ?>
										<span style="font-size:11px;color:<?php echo $source === 'client-stated' ? '#0a7b3e' : '#996633'; ?>;margin-left:4px;">
											<?php echo esc_html( $source ); ?>
										</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				<?php endforeach; ?>

				<?php
				$accent = $config->get( 'accent_color' );
				$surface = $config->get( 'surface_color' );
				if ( $accent && $surface ) {
					$result = Config::check_contrast( $accent, $surface );
					echo '<div style="margin:1rem 0;padding:12px 16px;border-radius:4px;';
					echo $result['aa'] ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;';
					echo '">';
					echo 'Accent/surface contrast ratio: <strong>' . esc_html( $result['ratio'] ) . ':1</strong> — ';
					echo $result['aa'] ? 'Passes WCAG AA' : 'Fails WCAG AA (minimum 4.5:1)';
					if ( $result['aaa'] ) echo ' (AAA)';
					echo '</div>';
				}
				?>

				<?php submit_button( 'Save Config' ); ?>
			</form>

			<hr>
			<h2>For AI assistants</h2>
			<p>Register extended fields via PHP:</p>
			<pre style="background:#f6f7f7;padding:16px;border-radius:4px;max-width:600px;font-size:13px;">Brainerd\Config::instance()->register( 'my_field', [
    'label'   => 'My Custom Field',
    'type'    => 'text',
    'section' => 'extended',
    'group'   => 'general',
    'default' => '',
] );</pre>
			<p>Read in templates: <code>brainerd_config( 'my_field' )</code></p>
		</div>
		<?php
	}

	private static function render_field( string $key, array $field, $value ): void {
		$id   = 'brainerd_' . $key;
		$name = 'brainerd_config[' . $key . ']';

		switch ( $field['type'] ) {
			case 'textarea':
				echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="3" class="large-text">' . esc_textarea( $value ) . '</textarea>';
				break;

			case 'toggle':
				echo '<label><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1" ' . checked( $value, true, false ) . '> Enabled</label>';
				break;

			case 'color':
				echo '<input type="color" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
				break;

			default:
				$type = in_array( $field['type'], [ 'email', 'url', 'tel' ], true ) ? $field['type'] : 'text';
				echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
				break;
		}
	}

	private static function handle_save( Config $config ): void {
		$posted = $_POST['brainerd_config'] ?? [];

		foreach ( $config->get_fields() as $key => $field ) {
			if ( $field['type'] === 'toggle' ) {
				$val = isset( $posted[ $key ] ) ? true : false;
			} else {
				$val = isset( $posted[ $key ] ) ? sanitize_text_field( wp_unslash( $posted[ $key ] ) ) : '';
			}

			$existing = $config->get_with_provenance( $key );
			$source   = $existing['source'] ?? 'default';

			if ( $source === 'default' && $val !== '' && $val !== $field['default'] ) {
				$source = 'client-stated';
			}

			$config->set( $key, $val, $source, $existing['origin'] ?? '', $existing['origin_file'] ?? '' );
		}

		$config->save();
	}
}
