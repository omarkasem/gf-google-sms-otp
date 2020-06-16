<?php
/**
 * Main class of the plugin
 *
 * @package GF_Free_SMS_Verifications
 */

namespace GF_Free_SMS_Verify;

/**
 * App class
 */
class App {
	/**
	 * Plugin name
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Populate plugin name and version
	 *
	 * @param string $plugin_name name of the plugin.
	 * @param string $version version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->register_hooks();

	}

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'gform_loaded', array( $this, 'load_gravity_forms' ), 5 );
		add_filter( 'gform_field_validation', array( $this, 'validate_form' ), 10, 4 );
	}

	/**
	 * Validate form submission
	 *
	 * @param array  $result form result.
	 * @param string $value value of the field.
	 * @param array  $form form array.
	 * @param array  $field field array.
	 * @return array
	 */
	public function validate_form( $result, $value, $form, $field ) {
		if ( 'gf-free-sms-verification' === $field['type'] ) {
			if ( '' !== $value ) {
				if ( ! isset( $_POST['gf_firebase_user_token'] ) || '' === $_POST['gf_firebase_user_token'] || ! isset( $_POST['gf_firebase_api_key'] ) || '' === $_POST['gf_firebase_api_key'] ) {
					$result['is_valid'] = false;
					$result['message']  = __( 'There were an issue in the mobile verification', 'gf-free-sms-verification' );
				}

				$response = $this->verify_user_token( sanitize_text_field( wp_unslash( $_POST['gf_firebase_user_token'] ) ), sanitize_text_field( wp_unslash( $_POST['gf_firebase_api_key'] ) ) );
				if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
					$result['is_valid'] = false;
					$result['message']  = __( 'There were an issue in the mobile verification', 'gf-free-sms-verification' );
				}
				$value                          = $this->get_user_phone( $response );
				$_POST[ 'input_' . $field->id ] = $value;
			}
		}
		return $result;
	}

	/**
	 * Verify user token
	 *
	 * @param string $token user token.
	 * @param string $api_key api key.
	 * @return array
	 */
	public function verify_user_token( $token, $api_key ) {
		$url      = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . $api_key;
		$args     = array(
			'method'  => 'POST',
			'body'    => wp_json_encode(
				array(
					'idToken' => $token,
				)
			),
			'headers' => array(
				'Content-Type' => 'application/json',
			),
		);
		$response = wp_remote_request( $url, $args );
		return $response;
	}

	/**
	 * Get user phone
	 *
	 * @param array $response user response.
	 * @return string
	 */
	private function get_user_phone( $response ) {
		$body = json_decode( wp_remote_retrieve_body( $response ) );
		if ( $body->users ) {
			$user = $body->users[0];
			return $user->phoneNumber;
		}
	}

	/**
	 * Load gravity forms
	 *
	 * @return void
	 */
	public static function load_gravity_forms() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once plugin_dir_path( __FILE__ ) . 'partials/class-gf-sms-addon.php';

		\GFAddOn::register( 'GF_Free_SMS_Verify\GF_SMS_Addon' );
	}

	/**
	 * Load plugin text domain
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			GF_FREE_SMS_VERIFICATION,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
