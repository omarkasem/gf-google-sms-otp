<?php
namespace Plugin_Name;
class App_Class{
    private $plugin_name;
    private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->register_hooks();
		
	}

	public function register_hooks(){
        add_action( 'gform_loaded', array( $this, 'load' ), 5 );
        add_filter( 'gform_field_validation', array($this,'validate_form'), 10, 4 );
	}

    public function validate_form($result, $value, $form, $field){
        if($field['type'] === 'gf-google-sms-otp'){
            if($value != ''){
                if(!isset($_POST['gf_firebase_user_token']) || $_POST['gf_firebase_user_token'] == '' || !isset($_POST['gf_firebase_api_key']) || $_POST['gf_firebase_api_key'] == ''){
                    $result['is_valid'] = false;
                    $result['message'] = _('There were an issue in the mobile verification');
                }
                return $this->verify_user_token($_POST['gf_firebase_user_token'],$_POST['gf_firebase_api_key'],$result);
            }
        }
        return $result;
    }

    public function verify_user_token($token,$api_key,$result){
        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key='.$api_key;
        $args = array(
            'method'=>'POST',
            'body'=>json_encode(array(
                'idToken'=>$token,
            )),
            'headers'=>array(
                'Content-Type'=>'application/json',
            )
        );
        $res = wp_remote_request( $url,$args);
        if(wp_remote_retrieve_response_code($res) !== 200){
            $result['is_valid'] = false;
            $result['message'] = _('There were an issue in the mobile verification');
        }
        return $result;
    }




    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( plugin_dir_path( __FILE__ ).'partials/gf_addon_class.php' );

        \GFAddOn::register( 'GFGoogleSMSOTP' );
    }



}
