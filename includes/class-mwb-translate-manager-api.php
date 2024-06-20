<?php

class Mwb_Translate_Manager_API {

	public function __construct() {
		add_action('rest_api_init', [$this, 'register_routes']);
	}

	public function register_routes() {
		register_rest_route('mwb/v1', '/translations/(?P<language>[a-zA-Z0-9-]+)', [
			'methods' => 'GET',
			'callback' => [$this, 'get_translations'],
			'permission_callback' => '__return_true',
		]);
	}

	public function get_translations($request) {
		global $wpdb;

		$language = $request['language'];
		$table_name_texts = $wpdb->prefix . 'mwb_english_texts';
		$table_name_translations = $wpdb->prefix . 'mwb_translations';
		$table_name_languages = $wpdb->prefix . 'mwb_translation_languages';

		// Check if the requested language exists in the database
		$language_exists = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name_languages WHERE language_code = %s",
			$language
		));

		if ($language_exists == 0 && $language !== 'en') {
			return new WP_REST_Response(['message' => 'Invalid language'], 404);
		}

		$translations = [];

		if ($language === 'en') {
			$results = $wpdb->get_results("SELECT id, text FROM $table_name_texts");
			foreach ($results as $row) {
				$translations[] = [
					'id' => $row->id,
					'text' => $row->text,
					'translation' => $row->text,
				];
			}
		} else {
			$results = $wpdb->get_results($wpdb->prepare(
				"SELECT t1.id, t1.text, t2.translated_text 
				FROM $table_name_texts t1
				LEFT JOIN $table_name_translations t2 ON t1.id = t2.english_text_id
				WHERE t2.language_code = %s",
				$language
			));
			foreach ($results as $row) {
				$translations[] = [
					'id' => $row->id,
					'text' => $row->text,
					'translation' => $row->translated_text,
				];
			}
		}

		return new WP_REST_Response($translations, 200);
	}
}

new Mwb_Translate_Manager_API();
?>
