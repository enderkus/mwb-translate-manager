<?php
global $wpdb;
$table_name_texts        = $wpdb->prefix . 'mwb_english_texts';
$table_name_translations = $wpdb->prefix . 'mwb_translations';
$table_name_languages    = $wpdb->prefix . 'mwb_translation_languages';

// Handle form submissions for adding new translations
if ( isset( $_POST['action'] ) && $_POST['action'] == 'add_translation' && ! empty( $_POST['new_text'] ) ) {
	// Check CSRF protection
	if ( ! check_admin_referer( 'mwb_add_translation_action', 'mwb_add_translation_nonce' ) ) {
		wp_die( 'The security check failed.' );
	}

	$new_text      = sanitize_text_field( $_POST['new_text'] );
	$existing_text = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name_texts WHERE text = %s", $new_text ) );

	if ( $existing_text ) {
		echo '<div class="notice notice-warning is-dismissible"><p>' . __( 'The text already exists in the database.', 'mwb-translate-manager' ) . '</p></div>';
	} else {
		$api_key                  = get_option( 'mwb_weglot_api_key', '' );
		$languages                = $wpdb->get_results( "SELECT language_code FROM $table_name_languages" );
		$errors                   = [];
		$translations             = [];
		$all_translations_success = true;

		foreach ( $languages as $language ) {
			$url  = "https://api.weglot.com/translate?api_key=$api_key";
			$body = json_encode( [
				'l_from'      => 'en',
				'l_to'        => $language->language_code,
				'request_url' => home_url(),
				'words'       => [
					[ 'w' => $new_text, 't' => 1 ]
				]
			] );

			$response = wp_remote_post( $url, [
				'headers' => [ 'Content-Type' => 'application/json' ],
				'body'    => $body
			] );

			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
				$errors[]                 = $language->language_code;
				$all_translations_success = false;
			} else {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				if ( ! isset( $data['to_words'][0] ) ) {
					$errors[]                 = $language->language_code;
					$all_translations_success = false;
				} else {
					$translated_text = $data['to_words'][0];
					$translations[]  = [
						'language_code'   => $language->language_code,
						'translated_text' => $translated_text
					];
				}
			}
		}

		if ( $all_translations_success ) {
			// Add the new text to the english_texts table
			$wpdb->insert( $table_name_texts, [
				'text' => $new_text
			] );
			$english_text_id = $wpdb->insert_id;

			// Add translations to translations table
			foreach ( $translations as $translation ) {
				$wpdb->insert( $table_name_translations, [
					'english_text_id' => $english_text_id,
					'language_code'   => $translation['language_code'],
					'translated_text' => $translation['translated_text']
				] );
			}

			echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Translation added successfully!', 'mwb-translate-manager' ) . '</p></div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p> ' . __( 'Translation failed for languages:', 'mwb-translate-manager' ) . ' ' . implode( ', ', $errors ) . '</p></div>';
		}
	}
}

// Retrieve texts and their translations from the database
$texts        = $wpdb->get_results( "SELECT * FROM $table_name_texts" );
$languages    = $wpdb->get_results( "SELECT * FROM $table_name_languages" );
$translations = $wpdb->get_results( "SELECT * FROM $table_name_translations" );

$translations_by_text = [];
foreach ( $translations as $translation ) {
	$translations_by_text[ $translation->english_text_id ][ $translation->language_code ] = $translation->translated_text;
}
?>
<div class="wrap">
    <h1><?php _e( 'MWB Translate Manager Translations', 'mwb-translate-manager' ); ?></h1>
    <form method="post" action="">
		<?php wp_nonce_field( 'mwb_add_translation_action', 'mwb_add_translation_nonce' ); ?>
        <input type="hidden" name="action" value="add_translation">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'New Text', 'mwb-translate-manager' ); ?></th>
                <td><input type="text" name="new_text" required/></td>
            </tr>
        </table>
		<?php submit_button( __( 'Add Translation', 'mwb-translate-manager' ) ); ?>
    </form>
    <hr/>
    <ul class="text-list">
		<?php foreach ( $texts as $text ): ?>
            <li>
                <strong><?php echo esc_html( $text->text ); ?></strong>
                <button class="button view-translations"
                        data-text-id="<?php echo esc_attr( $text->id ); ?>"><?php _e( 'View Translations', 'mwb-translate-manager' ); ?></button>
                <div class="translations-modal" id="modal-<?php echo esc_attr( $text->id ); ?>" style="display:none;">
                    <div class="modal-content">
                        <span class="close" data-text-id="<?php echo esc_attr( $text->id ); ?>">&times;</span>
                        <h2><?php _e( 'Translations for', 'mwb-translate-manager' ); ?>
                            "<?php echo esc_html( $text->text ); ?>"</h2>
                        <ul>
							<?php foreach ( $languages as $language ): ?>
                                <li>
                                    <strong><?php echo esc_html( $language->language_name ); ?>
                                        (<?php echo esc_html( $language->language_code ); ?>):</strong>
									<?php echo esc_html( $translations_by_text[ $text->id ][ $language->language_code ] ?? 'No translation available' ); ?>
                                </li>
							<?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </li>
		<?php endforeach; ?>
    </ul>
</div>
