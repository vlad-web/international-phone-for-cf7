<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Intltel_CF7_Tag_Generator {

	public static function init() {
		add_action( 'wpcf7_admin_init', array( __CLASS__, 'add' ), 15, 0 );
	}

	public static function add() {
		if ( ! class_exists( 'WPCF7_TagGenerator' ) ) {
			return;
		}

		$tag_generator = WPCF7_TagGenerator::get_instance();

		$tag_generator->add(
			Intltel_CF7_Form_Tag::TYPE,
			__( 'phone (international)', 'intltel-cf7' ),
			array( __CLASS__, 'render' ),
			array( 'version' => '2' )
		);
	}

	public static function render( $contact_form, $args ) {
		$args = wp_parse_args( $args, array( 'content' => '' ) );
		$tgg  = new WPCF7_TagGeneratorGenerator( $args['content'] );

		$formatter = new WPCF7_HTMLFormatter();

		$formatter->append_start_tag( 'header', array(
			'class' => 'description-box',
		) );

		$formatter->append_start_tag( 'h3' );

		$formatter->append_preformatted( esc_html__(
			'Form-tag generator: phone field with country selector', 'intltel-cf7'
		) );

		$formatter->end_tag( 'h3' );

		$formatter->append_start_tag( 'p' );

		$formatter->append_preformatted( esc_html__(
			'Generates a form-tag for a phone number field with a country flag selector, based on International Telephone Input.', 'intltel-cf7'
		) );

		$formatter->end_tag( 'header' );

		$formatter->append_start_tag( 'div', array(
			'class' => 'control-box',
		) );

		$formatter->call_user_func( static function () use ( $tgg ) {
			$tgg->print( 'field_type', array(
				'with_required'  => true,
				'select_options' => array(
					Intltel_CF7_Form_Tag::TYPE => __( 'phone (international)', 'intltel-cf7' ),
				),
			) );

			$tgg->print( 'field_name' );

			self::print_option_field( $tgg, 'initialcountry', __( 'Country by default', 'intltel-cf7' ), 'ru', __( 'ISO-2 country code, e.g. ru', 'intltel-cf7' ) );
			self::print_option_field( $tgg, 'preferred', __( 'Preferred countries', 'intltel-cf7' ), 'ru-ua-by', __( 'ISO-2 codes separated by a hyphen, e.g. ru-ua-by', 'intltel-cf7' ) );
			self::print_option_field( $tgg, 'onlycountries', __( 'Show only these countries', 'intltel-cf7' ), 'ru-ua-by', __( 'ISO-2 codes separated by a hyphen', 'intltel-cf7' ) );
			self::print_option_field( $tgg, 'excludecountries', __( 'Hide these countries', 'intltel-cf7' ), 'ru', __( 'ISO-2 codes separated by a hyphen', 'intltel-cf7' ) );

			$tgg->print( 'id_attr' );
			$tgg->print( 'class_attr' );
		} );

		$formatter->end_tag( 'div' );

		$formatter->append_start_tag( 'footer', array(
			'class' => 'insert-box',
		) );

		$formatter->call_user_func( static function () use ( $tgg ) {
			$tgg->print( 'insert_box_content' );
			$tgg->print( 'mail_tag_tip' );
		} );

		$formatter->print();
	}

	private static function print_option_field( $tgg, $option_prefix, $label, $placeholder, $description ) {
		$legend_id = $tgg->ref( $option_prefix . '-legend' );
		?>
		<fieldset>
			<legend id="<?php echo esc_attr( $legend_id ); ?>"><?php echo esc_html( $label ); ?></legend>
			<input
				type="text"
				data-tag-part="option"
				data-tag-option="<?php echo esc_attr( $option_prefix ); ?>:"
				aria-labelledby="<?php echo esc_attr( $legend_id ); ?>"
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				pattern="[a-zA-Z-]*"
			/>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		</fieldset>
		<?php
	}
}
