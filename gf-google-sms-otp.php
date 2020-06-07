<?php
/**
 * Plugin Name: Gravity Forms Google SMS OTP
 * Plugin URI: https://www.wisersteps.com
 * Description: Desc
 * Version: 1.0.0
 * Author: WiserSteps
 * Author URI: https://www.wisersteps.com
 * Developer: Omar Kasem
 * Developer URI: https://www.wisersteps.com
 * Text Domain: gf-google-sms-otp
 * Domain Path: /languages
 */
namespace Plugin_Name;
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define Name & Version
define('GF_GOOGLE_SMS_OTP_DOMAIN','gf-google-sms-otp');
define( 'GF_GOOGLE_SMS_OTP_VERSION', '1.0.0' );


// Require Main Files
require plugin_dir_path( __FILE__ ) . 'app/app_class.php';
new App_Class(GF_GOOGLE_SMS_OTP_DOMAIN,GF_GOOGLE_SMS_OTP_VERSION);


