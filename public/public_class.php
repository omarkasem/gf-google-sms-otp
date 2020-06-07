<?php
namespace Plugin_Name;
class Public_Class{
    private $plugin_name;
    private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->register_hooks();
	}
	public function register_hooks(){
		// add_action( 'wp_enqueue_scripts', array($this,'enqueue_styles'));
		// add_action( 'wp_enqueue_scripts', array($this,'enqueue_scripts') );
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name.'firebase-ui-auth', plugin_dir_url( __FILE__ ) . 'assets/css/firebase-ui-auth.css', array(), $this->version, 'all' );

		// wp_enqueue_style( $this->plugin_name.'firebase-ui-auth-rtl', plugin_dir_url( __FILE__ ) . 'assets/css/firebase-ui-auth-rtl.css', array(), $this->version, 'all' );
	
	}

	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name.'firebase-app', plugin_dir_url( __FILE__ ) . 'assets/js/firebase-app.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->plugin_name.'firebase-auth', plugin_dir_url( __FILE__ ) . 'assets/js/firebase-auth.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->plugin_name.'firebase-ui-auth__ar', plugin_dir_url( __FILE__ ) . 'assets/js/firebase-ui-auth__ar.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/public-script.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'plugin_object',
        array( 
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			// 'firebase_config'=>
		));
	}



	
}
