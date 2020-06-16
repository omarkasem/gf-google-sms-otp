<?php
/**
 * Plugin Name: Free SMS Verification for Gravity Forms
 * Plugin URI: https://www.wisersteps.com
 * Description: The best free SMS verification plugin for Gravity Forms, Verify users numbers before submitting the forms, Use Google firebase to edit the sent message to phone numbers by adding your website name.
 * Version: 1.0.0
 * Author: WiserSteps
 * Author URI: https://www.wisersteps.com
 * Developer: Omar Kasem
 * Developer URI: https://www.wisersteps.com
 * Text Domain: gf-free-sms-verification
 * Domain Path: /languages
 *
 * @package GF_Free_SMS_Verifications
 */

namespace GF_Free_SMS_Verify;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define Name & Version.
define( 'GF_FREE_SMS_VERIFICATION', 'gf-free-sms-verification' );
define( 'GF_FREE_SMS_VERIFICATION_VERSION', '1.0.0' );


// Require Main Files.
require plugin_dir_path( __FILE__ ) . 'app/class-app.php';
new App( GF_FREE_SMS_VERIFICATION, GF_FREE_SMS_VERIFICATION_VERSION );
