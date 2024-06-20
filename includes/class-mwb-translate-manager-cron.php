<?php

/**
 * The cron-specific functionality of the plugin.
 *
 * @link       https://enderkus.com.tr
 * @since      1.0.0
 *
 * @package    Mwb_Translate_Manager
 * @subpackage Mwb_Translate_Manager/includes
 */

class Mwb_Translate_Manager_Cron {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action('mwb_daily_translation_check', [$this, 'start_translation_process']);
		add_action('mwb_process_translation_batch', [$this, 'process_translation_batch']);
	}

	/**
	 * Start the translation process by scheduling the first batch.
	 *
	 * @since    1.0.0
	 */
	public function start_translation_process() {
		// Reset the offset and schedule the first batch
		update_option('mwb_translation_offset', 0);
		$this->schedule_next_batch();
	}

	/**
	 * Schedule the next batch of translations to be processed.
	 *
	 * @since    1.0.0
	 */
	public function schedule_next_batch() {
		if (!wp_next_scheduled('mwb_process_translation_batch')) {
			wp_schedule_single_event(time() + 180, 'mwb_process_translation_batch'); // 3 dakika sonra çalıştır
		}
	}

	/**
	 * Process a batch of translations.
	 *
	 * @since    1.0.0
	 */
	public function process_translation_batch() {
		global $wpdb;
		$table_name_translations = $wpdb->prefix . 'mwb_translations';
		$table_name_texts = $wpdb->prefix . 'mwb_english_texts';
		$table_name_languages = $wpdb->prefix . 'mwb_translation_languages';

		$api_key = get_option('mwb_weglot_api_key', '');
		$languages = $wpdb->get_results("SELECT language_code FROM $table_name_languages");
		// Number of translations to process each time
		$batch_size = 10;
		$offset = get_option('mwb_translation_offset', 0);

		$texts = $wpdb->get_results($wpdb->prepare(
			"SELECT id, text FROM $table_name_texts LIMIT %d OFFSET %d",
			$batch_size, $offset
		));

		if (empty($texts)) {
			// Reset offset if there are no more translations to process
			update_option('mwb_translation_offset', 0);
			return;
		}

		foreach ($texts as $text) {
			foreach ($languages as $language) {
				$url = "https://api.weglot.com/translate?api_key=$api_key";
				$body = json_encode([
					'l_from' => 'en',
					'l_to' => $language->language_code,
					'request_url' => home_url(),
					'words' => [
						['w' => $text->text, 't' => 1]
					]
				]);

				$response = wp_remote_post($url, [
					'headers' => ['Content-Type' => 'application/json'],
					'body' => $body
				]);

				if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
					$body = wp_remote_retrieve_body($response);
					$data = json_decode($body, true);

					if (isset($data['to_words'][0])) {
						$translated_text = $data['to_words'][0];

						$translation = $wpdb->get_row($wpdb->prepare(
							"SELECT id FROM $table_name_translations 
                            WHERE english_text_id = %d AND language_code = %s",
							$text->id, $language->language_code
						));

						if ($translation) {
							$wpdb->update($table_name_translations, [
								'translated_text' => $translated_text
							], ['id' => $translation->id]);
						} else {
							$wpdb->insert($table_name_translations, [
								'english_text_id' => $text->id,
								'language_code' => $language->language_code,
								'translated_text' => $translated_text
							]);
						}
					}
				}
			}
		}

		// Increase the number of processed translations
		update_option('mwb_translation_offset', $offset + $batch_size);

		// Plan the next batch
		$this->schedule_next_batch();
	}
}

new Mwb_Translate_Manager_Cron();
