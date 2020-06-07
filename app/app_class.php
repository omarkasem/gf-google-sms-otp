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
	}

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( plugin_dir_path( __FILE__ ).'partials/gf_addon_class.php' );

        \GFAddOn::register( 'GFGoogleSMSOTP' );
    }



}
