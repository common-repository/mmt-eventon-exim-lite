<?php
/**
 * EventON ExIm - Script and Style
 *
 * @package mmt-eo-exim
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_ExIm_Script_And_Style {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_styles_scripts' ), 15 );
	}

	/**
	 * Register Script and Styles
	 */
	public function register_styles_scripts() {
		global $mmt_eo_exim;
		wp_register_style( 'mmt_eo_exim_style', $mmt_eo_exim->plugin_url . 'assets/css/eo_ei_style.css', array(), $mmt_eo_exim->version );

		wp_register_script( 'mmt_eo_exim_scripts_admin', $mmt_eo_exim->plugin_url . 'assets/js/eo_ei_script_admin.js', array( 'jquery', 'jquery-effects-shake' ), $mmt_eo_exim->version, true );

		add_action( 'admin_enqueue_scripts', array( $this, 'print_admin_scripts' ) );
	}

	/**
	 * Enqueue Admin Script and Styles
	 *
	 * @param string $hook Plugin Name.
	 */
	public function print_admin_scripts() {
		global $mmt_eo_exim;
		wp_enqueue_style( 'mmt_eo_exim_admin_style', $mmt_eo_exim->plugin_url . 'assets/css/eo_ei_style_admin.css', array(), $mmt_eo_exim->version );
		wp_enqueue_script( 'mmt_eo_exim_scripts_admin' );
		$ajaxurl = array(
			'ajaxurl'                => admin_url( 'admin-ajax.php' ),
			'mmt_eo_exim_ajax_nonce' => wp_create_nonce( 'mmt_eo_exim_security_key' ),
		);
		wp_localize_script( 'mmt_eo_exim_scripts_admin', 'mmt_eo_exim_admin', $ajaxurl );
	}
}
