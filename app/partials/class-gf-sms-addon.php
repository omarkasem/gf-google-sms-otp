<?php
namespace GF_Free_SMS_Verify;

\GFForms::include_addon_framework();

class GF_SMS_Addon extends \GFAddOn {

	protected $_version                  = 'gf-free-sms-verification'_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug                     = 'gf-free-sms-verification';
	protected $_path                     = 'gf_addon_class.php';
	protected $_full_path                = __FILE__;
	protected $_title                    = 'Gravity Forms Google SMS OTP';
	protected $_short_title              = 'Google SMS OTP';


	private static $_instance = null;


	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'class-gf-sms-field.php';
		}
	}

	public function init_admin() {
		parent::init_admin();
		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_field_standard_settings', array( $this, 'my_standard_settings' ), 10, 2 );
	}

	public function localize_scripts() {

		$translation_array = array(
			'a_value' => $text,
		);
		wp_localize_script( 'gf_google_admin_script', 'object_name', $translation_array );
	}

	public function init_frontend() {
		add_action( 'gform_enqueue_scripts', array( $this, 'enq_styles_scripts' ), 10, 2 );
	}

	public function enq_styles_scripts( $form, $is_ajax ) {
		if ( ! $this->check_plugin_options( $form ) ) {
			return;}

		$exist = false;
		foreach ( $form['fields'] as $key => $field ) {
			if ( 'gf-google-sms-otp' === $field['type'] ) {
				$firebase_countries = $field['firebase_countries'];
				$exist              = true;
			}
		}
		if ( false === $exist ) {
			return;}

		// Enqueue Styles
		$rtl = intval( $this->get_plugin_setting( 'gf_sms_firebase_rtl' ) );
		wp_enqueue_style( 'gf-free-sms-verification' . 'firebase-ui-auth', plugin_dir_url( __DIR__ ) . 'assets/css/firebase-ui-auth.css', array(), $this->version, 'all' );

		if ( 1 === $rtl ) {
			wp_enqueue_style( 'gf-free-sms-verification' . 'firebase-ui-auth_rtl', plugin_dir_url( __DIR__ ) . 'assets/css/firebase-ui-auth-rtl.css', array(), $this->version, 'all' );
		}

		// Enqueue Scripts
		$firebase_config = $this->get_plugin_setting( 'gf_sms_firebase_config' );
		$firebase_lang   = $this->get_plugin_setting( 'gf_sms_firebase_language' );

		wp_enqueue_script( 'gf-free-sms-verification' . 'firebase_app', plugin_dir_url( __DIR__ ) . 'assets/js/firebase-app.min.js', array( 'jquery' ), $this->_version, false );

		wp_enqueue_script( 'gf-free-sms-verification' . 'firebase_auth', plugin_dir_url( __DIR__ ) . 'assets/js/firebase-auth.min.js', array( 'jquery' ), $this->_version, false );

		wp_enqueue_script( 'gf-free-sms-verification' . 'firebase_ui_auth__' . $firebase_lang, 'https://www.gstatic.com/firebasejs/ui/4.5.1/firebase-ui-auth__' . $firebase_lang . '.js', array( 'jquery' ), $this->_version, false );

		wp_enqueue_script( 'gf-free-sms-verification' . 'public-script', plugin_dir_url( __DIR__ ) . 'assets/js/public-script.js', array( 'jquery' ), $this->_version, false );

		$translation_array = array(
			'firebaseConfig'     => $firebase_config,
			'firebase_countries' => $firebase_countries,
		);
		wp_localize_script( 'gf-free-sms-verification' . 'public-script', 'firebase_data', $translation_array );

	}


	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gf-free-sms-verification' . 'select2',
				'src'     => plugin_dir_url( __DIR__ ) . 'assets/css/select2.min.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'gf-free-sms-verification' . 'select2',
				'src'     => plugin_dir_url( __DIR__ ) . 'assets/js/select2.min.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				),
			),

			array(
				'handle'  => 'gf-free-sms-verification' . 'admin_script',
				'src'     => plugin_dir_url( __DIR__ ) . 'assets/js/admin-script.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) ),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}


	public function check_plugin_options() {
		$firebase_config = $this->get_plugin_setting( 'gf_sms_firebase_config' );
		$firebase_lang   = $this->get_plugin_setting( 'gf_sms_firebase_language' );
		if ( '' === $firebase_config || '' === $firebase_lang ) {
			return false;}
		return true;
	}



	public function tooltips( $tooltips ) {
		$simple_tooltips = array(
			'firebase_countries' => sprintf( '<h6>%s</h6>%s', esc_html__( 'Firebase Whitelisted Countries', 'gf-free-sms-verification' ), esc_html__( '<ul><li>Select the countries that will show up in the phone validation.</li><li>The first one will be the default.</li><li>Leave empty to show all countries.</li></ul>', 'gf-free-sms-verification' ) ),
		);

		return array_merge( $tooltips, $simple_tooltips );
	}

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
						echo '<option value="' . $key . '">' . $val . '</option>';
					}
					?>
				</select>
			</li>

			<?php
		}
	}



	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Google SMS OTP', 'gf-free-sms-verification' ),
				'fields' => array(
					array(
						'label'    => esc_html__( 'Firebase Config Information', 'gf-free-sms-verification' ),
						'type'     => 'textarea',
						'name'     => 'gf_sms_firebase_config',
						'required' => true,
						'tooltip'  => esc_html__( 'This is the tooltip', 'gf-free-sms-verification' ),
						'class'    => 'medium merge-tag-support mt-position-right',
					),

					array(
						'label'    => esc_html__( 'Verification Field Language', 'gf-free-sms-verification' ),
						'type'     => 'select',
						'name'     => 'gf_sms_firebase_language',
						'required' => true,
						'tooltip'  => esc_html__( 'This is the tooltip', 'gf-free-sms-verification' ),
						'choices'  => $this->get_supported_languages(),
					),
					array(
						'label'    => esc_html__( 'Enable RTL (Right to left)', 'gf-free-sms-verification' ),
						'type'     => 'radio',
						'name'     => 'gf_sms_firebase_rtl',
						'required' => true,
						'tooltip'  => esc_html__( 'This is the tooltip', 'gf-free-sms-verification' ),
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



	public function get_whitelisted_countries() {
		$string    = "| AF | Afghanistan |
		| AX | Åland Islands |
		| AL | Albania |
		| DZ | Algeria |
		| AS | American Samoa |
		| AD | Andorra |
		| AO | Angola |
		| AI | Anguilla |
		| AG | Antigua and Barbuda |
		| AR | Argentina |
		| AM | Armenia |
		| AW | Aruba |
		| AC | Ascension Island |
		| AU | Australia |
		| AT | Austria |
		| AZ | Azerbaijan |
		| BS | Bahamas |
		| BH | Bahrain |
		| BD | Bangladesh |
		| BB | Barbados |
		| BY | Belarus |
		| BE | Belgium |
		| BZ | Belize |
		| BJ | Benin |
		| BM | Bermuda |
		| BT | Bhutan |
		| BO | Bolivia |
		| BA | Bosnia and Herzegovina |
		| BW | Botswana |
		| BR | Brazil |
		| IO | British Indian Ocean Territory |
		| VG | British Virgin Islands |
		| BN | Brunei |
		| BG | Bulgaria |
		| BF | Burkina Faso |
		| BI | Burundi |
		| KH | Cambodia |
		| CM | Cameroon |
		| CA | Canada |
		| CV | Cape Verde |
		| BQ | Caribbean Netherlands |
		| KY | Cayman Islands |
		| CF | Central African Republic |
		| TD | Chad |
		| CL | Chile |
		| CN | China |
		| CX | Christmas Island |
		| CC | Cocos (Keeling) Islands |
		| CO | Colombia |
		| KM | Comoros |
		| CD | Democratic Republic Congo |
		| CG | Republic of Congo |
		| CK | Cook Islands |
		| CR | Costa Rica |
		| CI | Côte d'Ivoire |
		| HR | Croatia |
		| CU | Cuba |
		| CW | Curaçao |
		| CY | Cyprus |
		| CZ | Czech Republic |
		| DK | Denmark |
		| DJ | Djibouti |
		| DM | Dominica |
		| DO | Dominican Republic |
		| TL | East Timor |
		| EC | Ecuador |
		| EG | Egypt |
		| SV | El Salvador |
		| GQ | Equatorial Guinea |
		| ER | Eritrea |
		| EE | Estonia |
		| ET | Ethiopia |
		| FK | Falkland Islands (Islas Malvinas) |
		| FO | Faroe Islands |
		| FJ | Fiji |
		| FI | Finland |
		| FR | France |
		| GF | French Guiana |
		| PF | French Polynesia |
		| GA | Gabon |
		| GM | Gambia |
		| GE | Georgia |
		| DE | Germany |
		| GH | Ghana |
		| GI | Gibraltar |
		| GR | Greece |
		| GL | Greenland |
		| GD | Grenada |
		| GP | Guadeloupe |
		| GU | Guam |
		| GT | Guatemala |
		| GG | Guernsey |
		| GN | Guinea Conakry |
		| GW | Guinea-Bissau |
		| GY | Guyana |
		| HT | Haiti |
		| HM | Heard Island and McDonald Islands |
		| HN | Honduras |
		| HK | Hong Kong |
		| HU | Hungary |
		| IS | Iceland |
		| IN | India |
		| ID | Indonesia |
		| IR | Iran |
		| IQ | Iraq |
		| IE | Ireland |
		| IM | Isle of Man |
		| IL | Israel |
		| IT | Italy |
		| JM | Jamaica |
		| JP | Japan |
		| JE | Jersey |
		| JO | Jordan |
		| KZ | Kazakhstan |
		| KE | Kenya |
		| KI | Kiribati |
		| XK | Kosovo |
		| KW | Kuwait |
		| KG | Kyrgyzstan |
		| LA | Laos |
		| LV | Latvia |
		| LB | Lebanon |
		| LS | Lesotho |
		| LR | Liberia |
		| LY | Libya |
		| LI | Liechtenstein |
		| LT | Lithuania |
		| LU | Luxembourg |
		| MO | Macau |
		| MK | Macedonia |
		| MG | Madagascar |
		| MW | Malawi |
		| MY | Malaysia |
		| MV | Maldives |
		| ML | Mali |
		| MT | Malta |
		| MH | Marshall Islands |
		| MQ | Martinique |
		| MR | Mauritania |
		| MU | Mauritius |
		| YT | Mayotte |
		| MX | Mexico |
		| FM | Micronesia |
		| MD | Moldova |
		| MC | Monaco |
		| MN | Mongolia |
		| ME | Montenegro |
		| MS | Montserrat |
		| MA | Morocco |
		| MZ | Mozambique |
		| MM | Myanmar (Burma) |
		| NA | Namibia |
		| NR | Nauru |
		| NP | Nepal |
		| NL | Netherlands |
		| NC | New Caledonia |
		| NZ | New Zealand |
		| NI | Nicaragua |
		| NE | Niger |
		| NG | Nigeria |
		| NU | Niue |
		| NF | Norfolk Island |
		| KP | North Korea |
		| MP | Northern Mariana Islands |
		| NO | Norway |
		| OM | Oman |
		| PK | Pakistan |
		| PW | Palau |
		| PS | Palestinian Territories |
		| PA | Panama |
		| PG | Papua New Guinea |
		| PY | Paraguay |
		| PE | Peru |
		| PH | Philippines |
		| PL | Poland |
		| PT | Portugal |
		| PR | Puerto Rico |
		| QA | Qatar |
		| RE | Réunion |
		| RO | Romania |
		| RU | Russia |
		| RW | Rwanda |
		| BL | Saint Barthélemy |
		| SH | Saint Helena |
		| KN | St. Kitts |
		| LC | St. Lucia |
		| MF | Saint Martin |
		| PM | Saint Pierre and Miquelon |
		| VC | St. Vincent |
		| WS | Samoa |
		| SM | San Marino |
		| ST | São Tomé and Príncipe |
		| SA | Saudi Arabia |
		| SN | Senegal |
		| RS | Serbia |
		| SC | Seychelles |
		| SL | Sierra Leone |
		| SG | Singapore |
		| SX | Sint Maarten |
		| SK | Slovakia |
		| SI | Slovenia |
		| SB | Solomon Islands |
		| SO | Somalia |
		| ZA | South Africa |
		| GS | South Georgia and the South Sandwich Islands |
		| KR | South Korea |
		| SS | South Sudan |
		| ES | Spain |
		| LK | Sri Lanka |
		| SD | Sudan |
		| SR | Suriname |
		| SJ | Svalbard and Jan Mayen |
		| SZ | Swaziland |
		| SE | Sweden |
		| CH | Switzerland |
		| SY | Syria |
		| TW | Taiwan |
		| TJ | Tajikistan |
		| TZ | Tanzania |
		| TH | Thailand |
		| TG | Togo |
		| TK | Tokelau |
		| TO | Tonga |
		| TT | Trinidad/Tobago |
		| TN | Tunisia |
		| TR | Turkey |
		| TM | Turkmenistan |
		| TC | Turks and Caicos Islands |
		| TV | Tuvalu |
		| VI | U.S. Virgin Islands |
		| UG | Uganda |
		| UA | Ukraine |
		| AE | United Arab Emirates |
		| GB | United Kingdom |
		| US | United States |
		| UY | Uruguay |
		| UZ | Uzbekistan |
		| VU | Vanuatu |
		| VA | Vatican City |
		| VE | Venezuela |
		| VN | Vietnam |
		| WF | Wallis and Futuna |
		| EH | Western Sahara |
		| YE | Yemen |
		| ZM | Zambia |
		| ZW | Zimbabwe |
		";
		$new       = explode( '|', $string );
		$new       = array_map( 'trim', $new );
		$new       = array_values( array_filter( $new ) );
		$new_array = array();
		for ( $i = 0; $i < count( $new ); $i += 2 ) {
			$new_array[ $new[ $i ] ] = $new[ $i + 1 ];
		}
		return $new_array;
	}


	public function get_supported_languages() {
		$string    = '| ar | Arabic |
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
		$new       = explode( '|', $string );
		$new       = array_map( 'trim', $new );
		$new       = array_values( array_filter( $new ) );
		$new_array = array();
		for ( $i = 0; $i < count( $new ); $i += 2 ) {
			$new_array[] = array(
				'label' => $new[ $i + 1 ],
				'value' => $new[ $i ],
			);
		}
		return $new_array;
	}



}
