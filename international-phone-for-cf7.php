<?php
/**
 * Plugin Name: Международный Телефон для CF7
 * Description: Добавляет для Contact Form 7 поле телефона с выбором страны на основе International Telephone Input — тег [intltel].
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires Plugins: contact-form-7
 * Author: Buzway
 * Text Domain: intltel-cf7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'INTLTEL_CF7_VERSION', '1.0.0' );
define( 'INTLTEL_CF7_FILE', __FILE__ );
define( 'INTLTEL_CF7_DIR', plugin_dir_path( __FILE__ ) );
define( 'INTLTEL_CF7_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'intltel_cf7_bootstrap' );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'intltel_cf7_settings_link' );

function intltel_cf7_settings_link( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=intltel-cf7-settings' ) ),
		esc_html__( 'Настройки', 'intltel-cf7' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}

function intltel_cf7_bootstrap() {
	if ( ! defined( 'WPCF7_VERSION' ) ) {
		add_action( 'admin_notices', 'intltel_cf7_missing_cf7_notice' );
		return;
	}

	require_once INTLTEL_CF7_DIR . 'includes/class-settings.php';
	require_once INTLTEL_CF7_DIR . 'includes/class-form-tag.php';
	require_once INTLTEL_CF7_DIR . 'includes/class-assets.php';

	Intltel_CF7_Settings::init();
	Intltel_CF7_Form_Tag::init();
	Intltel_CF7_Assets::init();

	if ( is_admin() ) {
		require_once INTLTEL_CF7_DIR . 'includes/class-tag-generator.php';
		Intltel_CF7_Tag_Generator::init();
	}
}

function intltel_cf7_missing_cf7_notice() {
	?>
	<div class="notice notice-warning">
		<p>
			<?php esc_html_e( 'Плагину «Международный Телефон для CF7» требуется активный плагин Contact Form 7.', 'intltel-cf7' ); ?>
		</p>
	</div>
	<?php
}
