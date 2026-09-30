<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Intltel_CF7_Form_Tag {

	const TYPE = 'intltel';

	public static function init() {
		add_action( 'wpcf7_init', array( __CLASS__, 'register_tag' ) );

		add_filter( 'wpcf7_validate_' . self::TYPE, array( __CLASS__, 'validate' ), 10, 2 );
		add_filter( 'wpcf7_validate_' . self::TYPE . '*', array( __CLASS__, 'validate' ), 10, 2 );

		add_filter( 'wpcf7_posted_data', array( __CLASS__, 'merge_dial_code_into_posted_data' ) );

		add_filter( 'wpcf7_editor_panels', array( __CLASS__, 'add_editor_panel' ) );
	}

	public static function register_tag() {
		wpcf7_add_form_tag(
			array( self::TYPE, self::TYPE . '*' ),
			array( __CLASS__, 'render' ),
			array( 'name-attr' => true )
		);
	}

	// Список стран задаётся одним значением через дефис (ru-ua-by) —
	// запятая внутри значения опции ломает разбор тега в самом CF7.
	private static function split_country_list( $value ) {
		return $value ? array_filter( explode( '-', strtolower( $value ) ) ) : array();
	}

	public static function render( $tag ) {
		$tag = new WPCF7_FormTag( $tag );

		if ( empty( $tag->name ) ) {
			return '';
		}

		$validation_error = wpcf7_get_validation_error( $tag->name );

		$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-intltel' );

		if ( $validation_error ) {
			$class .= ' wpcf7-not-valid';
		}

		$atts = array();
		$atts['class']       = $tag->get_class_option( $class );
		$atts['id']          = $tag->get_id_option();
		$atts['tabindex']    = $tag->get_option( 'tabindex', 'signed_int', true );
		$atts['autocomplete'] = $tag->get_option( 'autocomplete', '[-0-9a-zA-Z]+', true );

		if ( $tag->is_required() ) {
			$atts['aria-required'] = 'true';
		}

		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

		$value = (string) reset( $tag->values );

		if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
			$atts['placeholder'] = $value;
			$value               = '';
		}

		$value = $tag->get_default_option( $value );
		$value = wpcf7_get_hangover( $tag->name, $value );

		$atts['value'] = $value;
		$atts['type']  = 'tel';
		$atts['name']  = $tag->name;

		$initial_country   = $tag->get_option( 'initialcountry', '[a-zA-Z]{2}', true );
		$preferred         = self::split_country_list( $tag->get_option( 'preferred', '[a-zA-Z-]+', true ) );
		$only_countries    = self::split_country_list( $tag->get_option( 'onlycountries', '[a-zA-Z-]+', true ) );
		$exclude_countries = self::split_country_list( $tag->get_option( 'excludecountries', '[a-zA-Z-]+', true ) );

		$atts['data-initial-country']      = $initial_country ? strtolower( $initial_country ) : null;
		$atts['data-preferred-countries']  = $preferred ? implode( ',', $preferred ) : null;
		$atts['data-only-countries']       = $only_countries ? implode( ',', $only_countries ) : null;
		$atts['data-exclude-countries']    = $exclude_countries ? implode( ',', $exclude_countries ) : null;

		$atts = wpcf7_format_atts( $atts );

		$code_name  = $tag->name . '-code';
		$code_value = wpcf7_get_hangover( $code_name, '' );

		$code_atts = wpcf7_format_atts( array(
			'type'  => 'hidden',
			'name'  => $code_name,
			'class' => 'wpcf7-intltel-code',
			'value' => $code_value,
		) );

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap wpcf7-intltel-wrap" data-name="%1$s"><input %2$s /><input %3$s />%4$s</span>',
			esc_attr( $tag->name ),
			$atts,
			$code_atts,
			$validation_error
		);

		return $html;
	}

	// Ожидаемая длина национального номера (без кода страны) для
	// наиболее частых стран. Для остальных стран используется запасной
	// диапазон — этого не хватит для 100% точности (как настоящий
	// libphonenumber), но однозначно отсекает недописанные номера.
	private static function get_national_length_ranges() {
		return array(
			'7'   => array( 10, 10 ), // RU, KZ
			'375' => array( 9, 9 ),   // BY
			'380' => array( 9, 9 ),   // UA
			'998' => array( 9, 9 ),   // UZ
			'995' => array( 9, 9 ),   // GE
			'374' => array( 8, 8 ),   // AM
			'994' => array( 9, 9 ),   // AZ
			'996' => array( 9, 9 ),   // KG
			'992' => array( 9, 9 ),   // TJ
			'993' => array( 8, 8 ),   // TM
			'370' => array( 8, 8 ),   // LT
			'371' => array( 8, 8 ),   // LV
			'372' => array( 7, 8 ),   // EE
			'48'  => array( 9, 9 ),   // PL
			'49'  => array( 10, 11 ), // DE
			'44'  => array( 10, 10 ), // GB
			'33'  => array( 9, 9 ),   // FR
			'39'  => array( 9, 10 ),  // IT
			'34'  => array( 9, 9 ),   // ES
			'1'   => array( 10, 10 ), // US, CA
			'86'  => array( 11, 11 ), // CN
			'91'  => array( 10, 10 ), // IN
			'972' => array( 9, 9 ),   // IL
			'90'  => array( 10, 10 ), // TR
			'971' => array( 9, 9 ),   // AE
			'52'  => array( 10, 10 ), // MX
			'55'  => array( 10, 11 ), // BR
			'351' => array( 9, 9 ),   // PT
			'358' => array( 9, 9 ),   // FI
			'46'  => array( 9, 9 ),   // SE
			'47'  => array( 8, 8 ),   // NO
			'45'  => array( 8, 8 ),   // DK
			'31'  => array( 9, 9 ),   // NL
			'32'  => array( 9, 9 ),   // BE
			'41'  => array( 9, 9 ),   // CH
		);
	}

	// Формат национального номера для стран, где длины мало: например, в США/Канаде
	// код региона не начинается с 0 или 1, поэтому номер с приклеенным кодом страны
	// («1 201 555 012» — 10 цифр) не должен проходить как 10-значный национальный.
	private static function get_national_patterns() {
		return array(
			'1' => '/^[2-9][0-9]{2}[2-9][0-9]{6}$/', // US, CA (NANP)
			'7' => '/^[3489][0-9]{9}$/',            // RU, KZ
		);
	}

	public static function validate( $result, $tag ) {
		$tag = new WPCF7_FormTag( $tag );

		$name = $tag->name;
		$raw_value = isset( $_POST[ $name ] ) ? $_POST[ $name ] : '';
		// Значение должно быть строкой; если прислали your-phone[]=...,
		// $_POST[$name] окажется массивом, и trim()/preg_replace() на
		// PHP 8+ упадут с фатальной ошибкой — считаем это пустым полем.
		$value = is_string( $raw_value ) ? trim( wp_unslash( $raw_value ) ) : '';

		if ( $tag->is_required() && '' === $value ) {
			$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
			return $result;
		}

		if ( '' === $value ) {
			return $result;
		}

		$digits = preg_replace( '/[^0-9]/', '', $value );

		$code_name = $name . '-code';
		$raw_code = isset( $_POST[ $code_name ] ) ? $_POST[ $code_name ] : '';
		$dial_code = is_string( $raw_code ) ? preg_replace( '/[^0-9]/', '', wp_unslash( $raw_code ) ) : '';

		$ranges = self::get_national_length_ranges();

		if ( $dial_code && isset( $ranges[ $dial_code ] ) ) {
			list( $min, $max ) = $ranges[ $dial_code ];
		} else {
			// Запасной диапазон для стран, которых нет в таблице выше.
			$min = 6;
			$max = 14;
		}

		$patterns = self::get_national_patterns();

		if ( strlen( $digits ) < $min || strlen( $digits ) > $max ) {
			$result->invalidate( $tag, __( 'Введите корректный номер телефона.', 'intltel-cf7' ) );
		} elseif ( $dial_code && isset( $patterns[ $dial_code ] ) && ! preg_match( $patterns[ $dial_code ], $digits ) ) {
			$result->invalidate( $tag, __( 'Введите корректный номер телефона.', 'intltel-cf7' ) );
		}

		return $result;
	}

	public static function merge_dial_code_into_posted_data( $data ) {
		$contact_form = WPCF7_ContactForm::get_current();

		if ( ! $contact_form ) {
			return $data;
		}

		$tags = $contact_form->scan_form_tags( array(
			'type' => array( self::TYPE, self::TYPE . '*' ),
		) );

		foreach ( $tags as $tag ) {
			$code_name = $tag->name . '-code';

			if ( isset( $_POST[ $code_name ] ) && is_string( $_POST[ $code_name ] ) ) {
				$data[ $code_name ] = sanitize_text_field( wp_unslash( $_POST[ $code_name ] ) );
			}
		}

		return $data;
	}

	public static function add_editor_panel( $panels ) {
		$panels['intltel-panel'] = array(
			'title'    => __( 'Международный телефон', 'intltel-cf7' ),
			'callback' => array( __CLASS__, 'render_editor_panel' ),
		);

		return $panels;
	}

	public static function render_editor_panel() {
		?>
		<h2><?php esc_html_e( 'Поле телефона с выбором страны', 'intltel-cf7' ); ?></h2>

		<p>
			<?php esc_html_e( 'Вставьте в форму один из тегов ниже. Скопируйте нужный вариант и добавьте на нужное место в шаблоне формы.', 'intltel-cf7' ); ?>
		</p>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Базовый вариант', 'intltel-cf7' ); ?></th>
				<td><code>[intltel* your-phone initialcountry:ru]</code></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'С приоритетными странами', 'intltel-cf7' ); ?></th>
				<td><code>[intltel* your-phone initialcountry:ru preferred:ru-ua-by]</code></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Ограничить список стран', 'intltel-cf7' ); ?></th>
				<td><code>[intltel* your-phone onlycountries:ru-ua-by]</code></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Необязательное поле', 'intltel-cf7' ); ?></th>
				<td><code>[intltel your-phone initialcountry:ru]</code></td>
			</tr>
		</table>

		<h3><?php esc_html_e( 'Доступные опции', 'intltel-cf7' ); ?></h3>
		<ul style="list-style: disc; margin-left: 1.5em;">
			<li><code>initialcountry:ru</code> — <?php esc_html_e( 'страна по умолчанию (код ISO-2)', 'intltel-cf7' ); ?></li>
			<li><code>preferred:ru-ua-by</code> — <?php esc_html_e( 'страны в начале списка (через дефис, без пробелов и запятых)', 'intltel-cf7' ); ?></li>
			<li><code>onlycountries:ru-ua-by</code> — <?php esc_html_e( 'показывать только перечисленные страны', 'intltel-cf7' ); ?></li>
			<li><code>excludecountries:ru</code> — <?php esc_html_e( 'скрыть перечисленные страны', 'intltel-cf7' ); ?></li>
			<li><code>id:my-id class:my-class placeholder "Телефон"</code> — <?php esc_html_e( 'стандартные опции CF7', 'intltel-cf7' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'Тег для письма', 'intltel-cf7' ); ?></h3>
		<p>
			<?php
			printf(
				/* translators: %1$s and %2$s are mail-tag examples */
				esc_html__( 'Номер без кода страны доступен как %1$s, а телефонный код отдельно — как %2$s. Например: +[your-phone-code] [your-phone].', 'intltel-cf7' ),
				'<code>[your-phone]</code>',
				'<code>[your-phone-code]</code>'
			);
			?>
		</p>
		<?php
	}
}
