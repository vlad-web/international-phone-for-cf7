<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Intltel_CF7_Assets {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function enqueue() {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'intltel-cf7-iti',
			INTLTEL_CF7_URL . 'assets/vendor/intl-tel-input/css/intlTelInput.min.css',
			array(),
			INTLTEL_CF7_VERSION
		);

		wp_enqueue_script(
			'intltel-cf7-iti',
			INTLTEL_CF7_URL . 'assets/vendor/intl-tel-input/js/intlTelInput.min.js',
			array(),
			INTLTEL_CF7_VERSION,
			true
		);

		wp_enqueue_script(
			'intltel-cf7',
			INTLTEL_CF7_URL . 'assets/js/international-phone-cf7.min.js',
			array( 'intltel-cf7-iti' ),
			INTLTEL_CF7_VERSION,
			true
		);

		$options = Intltel_CF7_Settings::get_options();

		wp_localize_script( 'intltel-cf7', 'IntltelCf7', array(
			'utilsUrl'           => INTLTEL_CF7_URL . 'assets/vendor/intl-tel-input/js/utils.js',
			// Строка "Search" переведена в стандартных языковых пакетах WP,
			// поэтому плейсхолдер автоматически подстраивается под язык
			// текущей страницы (в т.ч. переключение языка через Polylang).
			'searchPlaceholder'  => translate( 'Search' ),
			'legacySelector'     => $options['legacy_selector'],
			'legacyCodeSelector' => $options['legacy_code_selector'],
			'defaultCountry'     => Intltel_CF7_Settings::get_default_country(),
			'autoDetectCountry'  => (bool) $options['auto_detect_country'],
		) );

		wp_enqueue_style(
			'intltel-cf7',
			INTLTEL_CF7_URL . 'assets/css/international-phone-cf7.min.css',
			array( 'intltel-cf7-iti' ),
			INTLTEL_CF7_VERSION
		);
	}
}
