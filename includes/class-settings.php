<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Intltel_CF7_Settings {

	const OPTION_KEY = 'intltel_cf7_options';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function get_options() {
		$defaults = array(
			'legacy_selector'      => '.phone',
			'legacy_code_selector' => '.phoneCode',
			'default_country'      => 'ru',
			'auto_detect_country'  => '',
		);

		$options = get_option( self::OPTION_KEY, array() );

		return wp_parse_args( $options, $defaults );
	}

	public static function add_menu() {
		add_submenu_page(
			'wpcf7',
			__( 'Международный Телефон', 'intltel-cf7' ),
			__( 'Международный Телефон', 'intltel-cf7' ),
			'wpcf7_edit_contact_forms',
			'intltel-cf7-settings',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register_settings() {
		register_setting( 'intltel_cf7_settings_group', self::OPTION_KEY, array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize' ),
		) );

		add_settings_section(
			'intltel_cf7_general_section',
			__( 'Страна по умолчанию', 'intltel-cf7' ),
			'__return_false',
			'intltel-cf7-settings'
		);

		add_settings_field(
			'default_country',
			__( 'Страна по умолчанию', 'intltel-cf7' ),
			array( __CLASS__, 'render_default_country_field' ),
			'intltel-cf7-settings',
			'intltel_cf7_general_section'
		);

		add_settings_field(
			'auto_detect_country',
			__( 'Определять страну автоматически', 'intltel-cf7' ),
			array( __CLASS__, 'render_auto_detect_field' ),
			'intltel-cf7-settings',
			'intltel_cf7_general_section'
		);

		add_settings_section(
			'intltel_cf7_legacy_section',
			__( 'Готовые поля на старых формах', 'intltel-cf7' ),
			array( __CLASS__, 'render_legacy_section_intro' ),
			'intltel-cf7-settings'
		);

		add_settings_field(
			'legacy_selector',
			__( 'Классы полей телефона', 'intltel-cf7' ),
			array( __CLASS__, 'render_legacy_selector_field' ),
			'intltel-cf7-settings',
			'intltel_cf7_legacy_section'
		);

		add_settings_field(
			'legacy_code_selector',
			__( 'Классы полей телефонного кода', 'intltel-cf7' ),
			array( __CLASS__, 'render_legacy_code_selector_field' ),
			'intltel-cf7-settings',
			'intltel_cf7_legacy_section'
		);
	}

	public static function sanitize( $input ) {
		$output = array();

		$output['legacy_selector']      = isset( $input['legacy_selector'] ) ? sanitize_text_field( $input['legacy_selector'] ) : '';
		$output['legacy_code_selector'] = isset( $input['legacy_code_selector'] ) ? sanitize_text_field( $input['legacy_code_selector'] ) : '';

		$default_country = isset( $input['default_country'] ) ? strtolower( sanitize_text_field( $input['default_country'] ) ) : '';
		$output['default_country'] = preg_match( '/^[a-z]{2}$/', $default_country ) ? $default_country : 'ru';

		$output['auto_detect_country'] = ! empty( $input['auto_detect_country'] ) ? '1' : '';

		return $output;
	}

	public static function render_default_country_field() {
		$options = self::get_options();
		?>
		<input type="text" class="small-text" maxlength="2" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[default_country]" value="<?php echo esc_attr( $options['default_country'] ); ?>" placeholder="ru" />
		<p class="description"><?php esc_html_e( 'Код страны ISO-2 (ru, ua, kz...), которая будет выбрана по умолчанию, если в теге [intltel] не указана опция initialcountry и автоопределение выключено или не сработало.', 'intltel-cf7' ); ?></p>
		<?php
	}

	public static function render_auto_detect_field() {
		$options = self::get_options();
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[auto_detect_country]" value="1" <?php checked( $options['auto_detect_country'], '1' ); ?> />
			<?php esc_html_e( 'Определять страну по IP-адресу посетителя', 'intltel-cf7' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'Внимание: IP-адрес посетителя будет отправляться стороннему сервису геолокации (ipapi.co). Учтите это в политике конфиденциальности сайта. Работает, если в теге [intltel] не задана опция initialcountry; при ошибке или блокировке запроса используется страна по умолчанию.', 'intltel-cf7' ); ?></p>
		<?php
	}

	public static function render_legacy_section_intro() {
		echo '<p>' . esc_html__( 'Если на форме телефон сделан обычным полем CF7 (например [tel* your-phone class:phone]), а не тегом [intltel], плагин всё равно подключит к нему выбор страны — по CSS-классу поля.', 'intltel-cf7' ) . '</p>';
	}

	public static function render_legacy_selector_field() {
		$options = self::get_options();
		?>
		<input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[legacy_selector]" value="<?php echo esc_attr( $options['legacy_selector'] ); ?>" placeholder=".phone" />
		<p class="description"><?php esc_html_e( 'CSS-классы полей телефона через запятую, например: .phone, .telephone. Оставьте пустым, чтобы отключить.', 'intltel-cf7' ); ?></p>
		<?php
	}

	public static function render_legacy_code_selector_field() {
		$options = self::get_options();
		?>
		<input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[legacy_code_selector]" value="<?php echo esc_attr( $options['legacy_code_selector'] ); ?>" placeholder=".phoneCode" />
		<p class="description"><?php esc_html_e( 'CSS-класс скрытого поля в этой же форме, куда записывается телефонный код (например +7). Необязательно.', 'intltel-cf7' ); ?></p>
		<?php
	}

	public static function render_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Международный Телефон для CF7', 'intltel-cf7' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'intltel_cf7_settings_group' );
				do_settings_sections( 'intltel-cf7-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
