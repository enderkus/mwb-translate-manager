<?php

/**
 * Fired during plugin activation
 *
 * @link       https://enderkus.com.tr
 * @since      1.0.0
 *
 * @package    Mwb_Translate_Manager
 * @subpackage Mwb_Translate_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Mwb_Translate_Manager
 * @subpackage Mwb_Translate_Manager/includes
 * @author     Ender KUS <ender@enderkus.com.tr>
 */
class Mwb_Translate_Manager_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		// Create the languages table
		$table_name_languages = $wpdb->prefix . 'mwb_translation_languages';
		$sql_languages = "CREATE TABLE IF NOT EXISTS $table_name_languages (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            language_name varchar(255) NOT NULL,
            language_code varchar(10) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		// Create a table of English texts
		$table_name_texts = $wpdb->prefix . 'mwb_english_texts';
		$sql_texts = "CREATE TABLE IF NOT EXISTS $table_name_texts (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            text varchar(255) NOT NULL,
            UNIQUE (text),
            PRIMARY KEY  (id)
        ) $charset_collate;";

		// Create a table to save translations
		$table_name_translations = $wpdb->prefix . 'mwb_translations';
		$sql_translations = "CREATE TABLE IF NOT EXISTS $table_name_translations (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            english_text_id mediumint(9) NOT NULL,
            language_code varchar(10) NOT NULL,
            translated_text varchar(255) NOT NULL,
            PRIMARY KEY  (id),
            FOREIGN KEY (english_text_id) REFERENCES $table_name_texts(id) ON DELETE CASCADE
        ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_languages);
		dbDelta($sql_texts);
		dbDelta($sql_translations);

		$default_languages = [
			['French', 'fr'],
			['Spanish', 'es'],
			['German', 'de'],
			['Italian', 'it'],
			['Portuguese', 'pt'],
			['Dutch', 'nl'],
			['Polish', 'pl'],
			['Japanese', 'ja'],
			['Russian', 'ru']
		];

		// Check available languages and add only the missing ones
		foreach ($default_languages as $lang) {
			$language_exists = $wpdb->get_var($wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name_languages WHERE language_code = %s",
				$lang[1]
			));

			if ($language_exists == 0) {
				$wpdb->insert($table_name_languages, [
					'language_name' => $lang[0],
					'language_code' => $lang[1]
				]);
			}
		}

		// Add cron job
		if (!wp_next_scheduled('mwb_daily_translation_check')) {
			wp_schedule_event(time(), 'daily', 'mwb_daily_translation_check');
		}
		// Set offset to 0 for the first time
		update_option('mwb_translation_offset', 0);
	}
}
