<?php
/**
 * Create the addon for gravity forms
 *
 * @package GF_Free_SMS_Verifications
 */

namespace GF_Free_SMS_Verify;

\GFForms::include_addon_framework();

/**
 * Extend the GFAddon class to create an addon
 */
class GF_SMS_Addon extends \GFAddOn {

	/**
	 * Addon Version
	 *
	 * @var string
	 */
	protected $_version = GF_FREE_SMS_VERIFICATION_VERSION;

	/**
	 * Minimum version
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '1.9';

	/**
	 * Addon slug
	 *
	 * @var string
	 */
	protected $_slug = 'gf-free-sms-verification';

	/**
	 * Addon Path
	 *
	 * @var string
	 */
	protected $_path = 'gf_addon_class.php';

	/**
	 * Addon Full path
	 *
	 * @var string
	 */
	protected $_full_path = __FILE__;

	/**
	 * Addon Title
	 *
	 * @var string
	 */
	protected $_title = 'Gravity Forms Free SMS Verification';

	/**
	 * Addon short title
	 *
	 * @var string
	 */
	protected $_short_title = 'Free SMS Verification';

	/**
	 * Class instance
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Get class instance
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Pre init addon
	 *
	 * @return void
	 */
	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'class-gf-sms-field.php';
		}
	}

	/**
	 * Init admin hook
	 *
	 * @return void
	 */
	public function init_admin() {
		parent::init_admin();
		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_field_standard_settings', array( $this, 'my_standard_settings' ), 10, 2 );
	}

	/**
	 * Frontend init
	 *
	 * @return void
	 */
	public function init_frontend() {
		add_action( 'gform_enqueue_scripts', array( $this, 'enq_styles_scripts' ), 10, 2 );
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @param array   $form form array.
	 * @param boolean $is_ajax plugin ajax.
	 * @return void
	 */
	public function enq_styles_scripts( $form, $is_ajax ) {
		if ( ! $this->check_plugin_options( $form ) ) {
			return;}

		$exist = false;
		foreach ( $form['fields'] as $key => $field ) {
			if ( 'gf-free-sms-verification' === $field['type'] ) {
				$firebase_countries = $field['firebase_countries'];
				$exist              = true;
			}
		}

		if ( false === $exist ) {
			return;}

		// Enqueue Styles.
		$rtl = intval( $this->get_plugin_setting( 'gf_sms_firebase_rtl' ) );
		wp_enqueue_style( GF_FREE_SMS_VERIFICATION . 'firebase-ui-auth', plugin_dir_url( __DIR__ ) . 'assets/css/firebase-ui-auth.css', array(), GF_FREE_SMS_VERIFICATION_VERSION, 'all' );

		if ( 1 === $rtl ) {
			wp_enqueue_style( GF_FREE_SMS_VERIFICATION . 'firebase-ui-auth_rtl', plugin_dir_url( __DIR__ ) . 'assets/css/firebase-ui-auth-rtl.css', array(), GF_FREE_SMS_VERIFICATION_VERSION, 'all' );
		}

		// Enqueue Scripts.
		$firebase_config = $this->get_plugin_setting( 'gf_sms_firebase_config' );
		$firebase_lang   = $this->get_plugin_setting( 'gf_sms_firebase_language' );

		wp_enqueue_script( GF_FREE_SMS_VERIFICATION . 'firebase_app', plugin_dir_url( __DIR__ ) . 'assets/js/firebase-app.min.js', array( 'jquery' ), GF_FREE_SMS_VERIFICATION_VERSION, false );

		wp_enqueue_script( GF_FREE_SMS_VERIFICATION . 'firebase_auth', plugin_dir_url( __DIR__ ) . 'assets/js/firebase-auth.min.js', array( 'jquery' ), GF_FREE_SMS_VERIFICATION_VERSION, false );

		wp_enqueue_script( GF_FREE_SMS_VERIFICATION . 'firebase_ui_auth__' . $firebase_lang, 'https://www.gstatic.com/firebasejs/ui/4.5.1/firebase-ui-auth__' . $firebase_lang . '.js', array( 'jquery' ), GF_FREE_SMS_VERIFICATION_VERSION, false );

		wp_enqueue_script( GF_FREE_SMS_VERIFICATION . 'public-script', plugin_dir_url( __DIR__ ) . 'assets/js/public-script.js', array( 'jquery' ), GF_FREE_SMS_VERIFICATION_VERSION, false );

		$translation_array = array(
			'firebaseConfig'     => $firebase_config,
			'firebase_countries' => $firebase_countries,
		);
		wp_localize_script( GF_FREE_SMS_VERIFICATION . 'public-script', 'firebase_data', $translation_array );

	}

	/**
	 * Include styles in gravity forms
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gf-free-sms-verification_select2',
				'src'     => plugin_dir_url( __DIR__ ) . 'assets/css/select2.min.css',
				'version' => GF_FREE_SMS_VERIFICATION_VERSION,
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	/**
	 * Include scripts in gravity forms
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'gf-free-sms-verification_select2',
				'src'     => plugin_dir_url( __DIR__ ) . 'assets/js/select2.min.js',
				'version' => GF_FREE_SMS_VERIFICATION_VERSION,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				),
			),

			array(
				'handle'  => 'gf-free-sms-verification_admin_script',
				'src'     => plugin_dir_url( __DIR__ ) . 'assets/js/admin-script.js',
				'version' => GF_FREE_SMS_VERIFICATION_VERSION,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Check if plugin options exist
	 *
	 * @return boolean
	 */
	public function check_plugin_options() {
		$firebase_config = $this->get_plugin_setting( 'gf_sms_firebase_config' );
		$firebase_lang   = $this->get_plugin_setting( 'gf_sms_firebase_language' );

		if ( '' === $firebase_config || '' === $firebase_lang ) {
			return false;}
		return true;
	}


	/**
	 * Tooltip callback
	 *
	 * @param array $tooltips array of tooltips.
	 * @return array
	 */
	public function tooltips( $tooltips ) {
		$simple_tooltips = array(
			'firebase_countries' => esc_html__( 'Choose the countries that will show (Leave empty to show all) <ul><li>Select the countries that will show up in the phone validation.</li><li>The first one will be the default.</li><li>Leave empty to show all countries.</li></ul>', 'gf-free-sms-verification' ),
		);

		return array_merge( $tooltips, $simple_tooltips );
	}

	/**
	 * Register options
	 *
	 * @param integer $position position of the field.
	 * @param integer $form_id id of the form.
	 * @return void
	 */
	public function my_standard_settings( $position, $form_id ) {
		if ( 250 === $position ) {
			?>
			<style>
				#gform_fields li #field_settings li.select2-selection__choice{
					padding:0 5px;
				}
			</style>
			<li class="firebase_countries field_setting">
				<label for="firebase_countries" class="section_label">
					<?php esc_html_e( 'Firebase Whitelisted Countries', 'gf-free-sms-verification' ); ?>
					<?php gform_tooltip( 'firebase_countries' ); ?>
				</label>
				<select class="gf_sms_select2" multiple="multiple" style="width:100%;" id="firebase_countries" onkeyup="setWhitelistedCountries(jQuery(this).val());" onchange="setWhitelistedCountries(jQuery(this).val());">
					<?php
					foreach ( $this->get_whitelisted_countries() as $key => $val ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_attr( $val ) . '</option>';
					}
					?>
				</select>
			</li>

			<?php
		}
	}



	/**
	 * Register addon settings
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Free SMS Verification', 'gf-free-sms-verification' ),
				'fields' => array(
					array(
						'label'    => esc_html__( 'Firebase SDK Config', 'gf-free-sms-verification' ),
						'type'     => 'textarea',
						'name'     => 'gf_sms_firebase_config',
						'required' => true,
						'tooltip'  => esc_html__(
							'Follow the steps <a href="https://wisersteps.com/docs/gravity-forms-free-sms-verification/get-firebase-config/">Here</a>
						<br> OR <br>
						The video <a href="https://youtu.be/GwHVKauTSuU">Here</a>',
							'gf-free-sms-verification'
						),
						'class'    => 'medium merge-tag-support mt-position-right',
					),

					array(
						'label'    => esc_html__( 'SDK Language Interface', 'gf-free-sms-verification' ),
						'type'     => 'select',
						'name'     => 'gf_sms_firebase_language',
						'required' => true,
						'tooltip'  => esc_html__( 'Language of the verification process', 'gf-free-sms-verification' ),
						'choices'  => $this->get_supported_languages(),
					),
					array(
						'label'    => esc_html__( 'Enable RTL (Right to left)', 'gf-free-sms-verification' ),
						'type'     => 'radio',
						'name'     => 'gf_sms_firebase_rtl',
						'required' => true,
						'tooltip'  => esc_html__( 'Style of the interface', 'gf-free-sms-verification' ),
						'choices'  => array(
							array(
								'label' => esc_html__( 'Yes', 'gf-free-sms-verification' ),
								'value' => 1,
							),
							array(
								'label'         => esc_html__( 'No', 'gf-free-sms-verification' ),
								'value'         => 0,
								'default_value' => true,
							),
						),
					),
				),
			),

		);

	}


	/**
	 * Whitelisted countires
	 *
	 * @return array
	 */
	public function get_whitelisted_countries() {
		$string    = file_get_contents( __DIR__ . '/whitelisted-countries.json' );
		$new       = explode( '|', $string );
		$new       = array_map( 'trim', $new );
		$new       = array_values( array_filter( $new ) );
		$new_array = array();
		$count     = count( $new );
		for ( $i = 0; $i < $count; $i += 2 ) {
			$new_array[ $new[ $i ] ] = $new[ $i + 1 ];
		}
		return $new_array;
	}

	/**
	 * Supported countries
	 *
	 * @return array
	 */
	public function get_supported_languages() {
		$string    = file_get_contents( __DIR__ . '/supported-countries.json' );
		$new       = explode( '|', $string );
		$new       = array_map( 'trim', $new );
		$new       = array_values( array_filter( $new ) );
		$new_array = array();
		$count     = count( $new );
		for ( $i = 0; $i < $count; $i += 2 ) {
			$new_array[] = array(
				'label' => $new[ $i + 1 ],
				'value' => $new[ $i ],
			);
		}
		return $new_array;
	}



}
