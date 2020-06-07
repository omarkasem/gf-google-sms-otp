<?php

GFForms::include_addon_framework();

class GFGoogleSMSOTP extends GFAddOn {

	protected $_version = GF_GOOGLE_SMS_OTP_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = GF_GOOGLE_SMS_OTP_DOMAIN;
	protected $_path = 'gf_addon_class.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Google SMS OTP';
	protected $_short_title = 'Google SMS OTP';

	/**
	 * @var object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Include the field early so it is available when entry exports are being performed.
	 */
	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( plugin_dir_path( __FILE__ ) .'gf_sms_field.php' );
		}
	}

	public function init_admin() {
		parent::init_admin();
		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_field_standard_settings', array($this,'my_standard_settings'), 10, 2 );
	}

	public function localize_scripts(){
		
		$translation_array = array(
			'a_value' => $text,
		);
		wp_localize_script( 'gf_google_admin_script', 'object_name', $translation_array );
	}

	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	public function scripts() {
		$firebase_config = $this->get_plugin_setting( 'gf_sms_firebase_config');
		$firebase_lang = $this->get_plugin_setting( 'gf_sms_firebase_language');
		

		$scripts = array(
			array(
				'handle'  => GF_GOOGLE_SMS_OTP_DOMAIN.'firebase_app',
				'src'     =>plugin_dir_url( __DIR__ ) . 'assets/js/firebase-app.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 
						'field_types' => array( GF_GOOGLE_SMS_OTP_DOMAIN ),
						'admin_page' => array( 'entry_detail' )
					),
				),
			),
			array(
				'handle'  => GF_GOOGLE_SMS_OTP_DOMAIN.'firebase_auth',
				'src'     =>plugin_dir_url( __DIR__ ) . 'assets/js/firebase-auth.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'field_types' => array( GF_GOOGLE_SMS_OTP_DOMAIN ) ),
				),
			),
			array(
				'handle'  => GF_GOOGLE_SMS_OTP_DOMAIN.'firebase_ui_auth__'.$firebase_lang,
				'src'     =>'https://www.gstatic.com/firebasejs/ui/4.5.1/firebase-ui-auth__'.$firebase_lang.'.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'field_types' => array( GF_GOOGLE_SMS_OTP_DOMAIN ) ),
				),
			),

			array(
				'handle'  => 'gf_google_admin_script',
				'src'     =>plugin_dir_url( __DIR__ ) . 'assets/js/admin-script.js',
				'version' => $this->_version,
				'strings'=>array(
					'firebaseConfig'=>$firebase_config,
				),
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'field_types' => array( GF_GOOGLE_SMS_OTP_DOMAIN ) ),
				),
			),

		);

		return array_merge( parent::scripts(), $scripts );
	}

	public function styles() {
		$rtl = intval($this->get_plugin_setting( 'gf_sms_firebase_rtl'));
	
		$styles = array(
			array(
				'handle'  => GF_GOOGLE_SMS_OTP_DOMAIN.'firebase-ui-auth',
				'src'     => plugin_dir_url( __DIR__ ) . 'assets/css/firebase-ui-auth.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( GF_GOOGLE_SMS_OTP_DOMAIN ) )
				)
			),
		);

		if($rtl === 1){
			$styles[] = 
			array(
				'handle'  => GF_GOOGLE_SMS_OTP_DOMAIN.'firebase-ui-auth-rtl',
				'src'     => plugin_dir_url( __DIR__ ) . 'assets/css/firebase-ui-auth-rtl.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( GF_GOOGLE_SMS_OTP_DOMAIN ) )
				)
			);
		}

		return array_merge( parent::styles(), $styles );
	}

	public function tooltips( $tooltips ) {
		$simple_tooltips = array(
			'firebase_config_info' => sprintf( '<h6>%s</h6>%s', esc_html__( 'Firebase Config Info', GF_GOOGLE_SMS_OTP_DOMAIN ), esc_html__( 'Follow These Steps.', GF_GOOGLE_SMS_OTP_DOMAIN ) ),
		);

		return array_merge( $tooltips, $simple_tooltips );
	}


	// # FIELD SETTINGS -------------------------------------------------------------------------------------------------

	
	function my_standard_settings( $position, $form_id ) {
		if ( $position == 250 ) {
			?>
			<li class="firebase_config_info field_setting">
				<label for="firebase_config_info" class="section_label">
					<?php esc_html_e( 'Firebase Config Information', GF_GOOGLE_SMS_OTP_DOMAIN ); ?>
					<?php gform_tooltip( 'firebase_config_info' ) ?>
				</label>
				<textarea class="fieldwidth-3" id="firebase_config_info" rows="13" onkeyup="setInputFirebaseConfig(jQuery(this).val());" onchange="setInputFirebaseConfig(jQuery(this).val());"></textarea>
			
			</li>

			<?php
		}
	}



	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Google SMS OTP', GF_GOOGLE_SMS_OTP_DOMAIN ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Firebase Config Information', GF_GOOGLE_SMS_OTP_DOMAIN ),
						'type'    => 'textarea',
						'name'    => 'gf_sms_firebase_config',
						'required'=>true,
						'tooltip' => esc_html__( 'This is the tooltip', GF_GOOGLE_SMS_OTP_DOMAIN ),
						'class'   => 'medium merge-tag-support mt-position-right',
					),

					array(
						'label'   => esc_html__( 'Verification Field Language', GF_GOOGLE_SMS_OTP_DOMAIN ),
						'type'    => 'select',
						'name'    => 'gf_sms_firebase_language',
						'required'=>true,
						'tooltip' => esc_html__( 'This is the tooltip', GF_GOOGLE_SMS_OTP_DOMAIN ),
						'choices'=>$this->get_supported_languages(),
					),
					array(
						'label'   => esc_html__( 'Enable RTL (Right to left)', GF_GOOGLE_SMS_OTP_DOMAIN ),
						'type'    => 'radio',
						'name'    => 'gf_sms_firebase_rtl',
						'required'=>true,
						'tooltip' => esc_html__( 'This is the tooltip', GF_GOOGLE_SMS_OTP_DOMAIN ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Yes', GF_GOOGLE_SMS_OTP_DOMAIN ),
								'value'=>1,
							),
							array(
								'label' => esc_html__( 'No', GF_GOOGLE_SMS_OTP_DOMAIN ),
								'value'=>0,
								'default_value' => true,
							),
						),
					),
				),
			),
	
		);

	}

	public function get_supported_languages(){
		$string = '| ar | Arabic |
		| bg | Bulgarian |
		| ca | Catalan |
		| zh_cn | Chinese (Simplified) |
		| zh_tw | Chinese (Traditional) |
		| hr | Croatian |
		| cs | Czech |
		| da | Danish |
		| nl | Dutch |
		| en | English |
		| en_gb | English (UK) |
		| fa | Farsi |
		| fil | Filipino |
		| fi | Finnish |
		| fr | French |
		| de | German |
		| el | Greek |
		| iw | Hebrew |
		| hi | Hindi |
		| hu | Hungarian |
		| id | Indonesian |
		| it | Italian |
		| ja | Japanese |
		| ko | Korean |
		| lv | Latvian |
		| lt | Lithuanian |
		| no | Norwegian (Bokmal) |
		| pl | Polish |
		| pt_br | Portuguese (Brazil) |
		| pt_pt | Portuguese (Portugal) |
		| ro | Romanian |
		| ru | Russian |
		| sr | Serbian |
		| sk | Slovak |
		| sl | Slovenian |
		| es | Spanish |
		| es_419 | Spanish (Latin America) |
		| sv | Swedish |
		| th | Thai |
		| tr | Turkish |
		| uk | Ukrainian |
		| vi | Vietnamese |';
		$new = explode('|',$string);
		$new = array_map('trim', $new);
		$new = array_values(array_filter($new));
		$newArray = [];
		for ($i = 0; $i < count($new); $i += 2){
			$newArray[] = array('label'=>$new[$i+1],'value'=>$new[$i]);
		}
		return $newArray;
	}

	
}