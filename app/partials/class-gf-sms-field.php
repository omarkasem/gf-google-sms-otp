<?php
namespace GF_Free_SMS_Verify;

if ( ! class_exists( '\GFForms' ) ) {
	die();
}

class GF_SMS_Field extends \GF_Field {

	public $type = GF_FREE_SMS_VERIFICATION;


	public function get_form_editor_field_title() {
		return esc_attr__( 'Google SMS OTP', 'gf-free-sms-verification' );
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}


	public function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'description_setting',
			'rules_setting',
			'placeholder_setting',
			'firebase_countries',
			'css_class_setting',
			'size_setting',
			'admin_label_setting',
			'default_value_setting',
			'visibility_setting',
			'conditional_logic_field_setting',
		);
	}


	public function is_conditional_logic_supported() {
		return true;
	}


	public function get_form_editor_inline_script_on_page_render() {

		// set the default field label for the simple type field.
		$script = sprintf( "function SetDefaultValues_simple(field) {field.label = '%s';}", $this->get_form_editor_field_title() ) . PHP_EOL;

		// initialize the fields custom settings.
		$script .= "jQuery(document).bind('gform_load_field_settings', function (event, field, form) {" .
				   "var firebase_countries = field.firebase_countries == undefined ? '' : field.firebase_countries;" .
				   "jQuery('#firebase_countries').val(firebase_countries).trigger('change');" .
				   '});' . PHP_EOL;

		// saving the simple setting.
		$script .= "function setWhitelistedCountries(value) {SetFieldProperty('firebase_countries', value);}" . PHP_EOL;
		return $script;
	}


	public function get_field_input( $form, $value = '', $entry = null ) {
		$id              = absint( $this->id );
		$form_id         = absint( $form['id'] );
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		// Prepare the value of the input ID attribute.
		$field_id = $is_entry_detail || $is_form_editor || 0 == $form_id ? "input_$id" : 'input_' . $form_id . "_$id";

		$value = esc_attr( $value );

		// Prepare the input classes.
		$size         = $this->size;
		$class_suffix = $is_entry_detail ? '_admin' : '';
		$class        = $size . $class_suffix;

		// Prepare the other input attributes.
		$tabindex              = $this->get_tabindex();
		$logic_event           = ! $is_form_editor && ! $is_entry_detail ? $this->get_conditional_logic_event( 'keyup' ) : '';
		$placeholder_attribute = $this->get_field_placeholder_attribute();
		$required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		$disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';

		// Prepare the input tag for this field.
		$input = "<input name='input_{$id}' id='{$field_id}' type='hidden' value='{$value}' class='{$class} gf_google_sms_otp_field' {$tabindex} {$logic_event} {$placeholder_attribute} {$required_attribute} {$invalid_attribute} {$disabled_text}/>";

		return sprintf( "<div class='gf_google_sms_otp'></div><div class='ginput_container ginput_container_%s'>%s</div>", $this->type, $input );
	}
}

\GF_Fields::register( new GF_SMS_Field() );
