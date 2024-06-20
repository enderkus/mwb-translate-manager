<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://enderkus.com.tr
 * @since      1.0.0
 *
 * @package    Mwb_Translate_Manager
 * @subpackage Mwb_Translate_Manager/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Mwb_Translate_Manager
 * @subpackage Mwb_Translate_Manager/includes
 * @author     Ender KUS <ender@enderkus.com.tr>
 */
class Mwb_Translate_Manager_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;

		// Get the table names
		$table_name_languages = $wpdb->prefix . 'mwb_translation_languages';
		$table_name_texts = $wpdb->prefix . 'mwb_english_texts';
		$table_name_translations = $wpdb->prefix . 'mwb_translations';

		// Delete the tables
		$wpdb->query("DROP TABLE IF EXISTS $table_name_translations;");
		$wpdb->query("DROP TABLE IF EXISTS $table_name_texts;");
		$wpdb->query("DROP TABLE IF EXISTS $table_name_languages;");

		// Cancel the daily cron job
		wp_clear_scheduled_hook('mwb_daily_translation_check');
		// Cancel the helper cron job too
		wp_clear_scheduled_hook('mwb_process_translation_batch');
		// Reset the number of processed translations
		delete_option('mwb_translation_offset');
	}

}
