<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://enderkus.com.tr
 * @since      1.0.0
 *
 * @package    Mwb_Translate_Manager
 * @subpackage Mwb_Translate_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mwb_Translate_Manager
 * @subpackage Mwb_Translate_Manager/admin
 * @author     Ender KUS <ender@enderkus.com.tr>
 */
class Mwb_Translate_Manager_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mwb-translate-manager-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mwb-translate-manager-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the admin menu for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			'MWB Translate Manager',
			'MWB Translate Manager',
			'manage_options',
			'mwb-translate-manager',
			array( $this, 'display_main_page' ),
			'dashicons-translation',
			6
		);

		add_submenu_page(
			'mwb-translate-manager',
			'API Key Settings',
			'API Key Settings',
			'manage_options',
			'mwb-translate-manager-api-key-settings',
			array( $this, 'display_api_key_settings_page' )
		);

		add_submenu_page(
			'mwb-translate-manager',
			'Languages',
			'Languages',
			'manage_options',
			'mwb-translate-manager-languages',
			array( $this, 'display_languages_page' )
		);

		add_submenu_page(
			'mwb-translate-manager',
			'Translations',
			'Translations',
			'manage_options',
			'mwb-translate-manager-translations',
			array( $this, 'display_translations_page' )
		);
	}

	public function display_main_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/mwb-translate-manager-admin-display.php';
	}

	public function display_api_key_settings_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/mwb-translate-manager-api-key-settings-display.php';
	}

	public function display_languages_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/mwb-translate-manager-languages-display.php';
	}

	public function display_translations_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/mwb-translate-manager-translations-display.php';
	}

}
