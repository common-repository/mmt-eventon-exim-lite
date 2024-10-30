<?php
/**
 * Plugin Name: MMT - ExIm - Lite
 * Plugin URI: http://www.momothemes.com/
 * Description: Export / import events to / from Eventbrite. Display Eventbrite events with shortcodes and widget.
 * Text Domain: mmt-eo-exim
 * Domain Path: /languages
 * Author: MoMo Themes
 * Version: 1.1.2
 * Author URI: http://www.momothemes.com/
 * Requires at least: 5.0.0
 * Tested up to: 5.9.1
 */
class MMT_ExIm_Lite {
	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	public $version = '1.1.2';
	/**
	 * Plugin Name
	 *
	 * @var string
	 */
	public $name = 'MMT ExIm Lite';
	/**
	 * Plugin ID
	 *
	 * @var string
	 */
	public $id = 'EXIM';
	/**
	 * Plugin Slug
	 *
	 * @var string
	 */
	public $slug = 'mmt-eo-exim';
	/**
	 * Plugin URL
	 *
	 * @var string
	 */
	public $plugin_url;
	/**
	 * Plugin Slug
	 *
	 * @var string
	 */
	public $plugin_slug = 'mmt-eo-exim';
	/**
	 * Eventon Version
	 *
	 * @var string
	 */
	public $eventon_version = '2.8.3';
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugin_init' ) );
	}
	/**
	 * Plugin Init
	 * Check if EventON main plugin exist
	 */
	public function plugin_init() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		add_action( 'admin_menu', array( $this, 'mmt_eo_exim_set_menu_in_eventon' ) );
		add_action( 'init', array( $this, 'mmt_eo_exim_init' ), 0 );
	}

	/**
	 * Initiate Plugin
	 */
	public function mmt_eo_exim_init() {
		include_once 'includes/eo-ei-script-style.php';
		include_once 'includes/eo-ei-functions.php';
		include_once 'includes/class-currency-list.php';
		include_once 'includes/class-mmt-eventbrite-widget.php';
		include_once 'includes/class-mmt-eventbrite-api.php';

		$this->scripts = new MMT_EO_ExIm_Script_And_Style();
		$this->fn      = new MMT_EO_ExIm_Functions();

		add_action( 'widgets_init', array( $this, 'mmt_register_eventbrite_widget' ) );

		if ( is_admin() ) {
			include_once 'includes/admin/class-admin-ajax.php';
			include_once 'includes/admin/class-export-eventbrite.php';
		}
	}

	/**
	 * Register Eventbrite Widget
	 */
	public function mmt_register_eventbrite_widget() {
		$mmt_eo_exim_options      = get_option( 'mmt_eo_exim_options' );
		$enable_eventbrite_widget = isset( $mmt_eo_exim_options['enable_eventbrite_widget'] ) ? $mmt_eo_exim_options['enable_eventbrite_widget'] : '';
		if ( 'on' === $enable_eventbrite_widget ) {
			register_widget( 'MMT_Eventbrite_Widget' );
		}
	}
	/**
	 * Set submenu in Eventon Settings Menu
	 */
	public function mmt_eo_exim_set_menu_in_eventon() {
		add_menu_page(
			esc_html( 'ExIm Lite', 'mmt-eo-exim' ),
			esc_html( 'ExIm Lite', 'mmt-eo-exim' ),
			'administrator',
			'mmt-eo-exim-lite',
			array( $this, 'mmt_eo_exim_settings_page' ),
			'dashicons-randomize',
			10
		);
	}
	/**
	 * Admin Settings Page
	 */
	public function mmt_eo_exim_settings_page() {
		include_once 'includes/admin/admin-settings-page.php';
	}
	/**
	 * Loggong Function
	 *
	 * @param array $log Log Data.
	 */
	public function write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}
// Initiate this addon within the plugin.
$GLOBALS['mmt_eo_exim'] = new MMT_ExIm_Lite();
