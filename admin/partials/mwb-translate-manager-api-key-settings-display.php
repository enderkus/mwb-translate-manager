<?php
// Weglot API Key settings page

if ( isset( $_POST['mwb_weglot_api_key'] ) ) {
	// Check CSRF protection
	if ( ! check_admin_referer( 'mwb_weglot_api_key_action', 'mwb_weglot_api_key_nonce' ) ) {
		wp_die( 'The security check failed.' );
	}

	update_option( 'mwb_weglot_api_key', sanitize_text_field( $_POST['mwb_weglot_api_key'] ) );
	echo '<div class="notice notice-success is-dismissible"><p>'.__('API Key updated successfully!', 'mwb-translate-manager').'</p></div>';

	// Translate Hello World! text after updating API Key
	mwb_weglot_api_key_updated( sanitize_text_field( $_POST['mwb_weglot_api_key'] ) );
}

$api_key = get_option( 'mwb_weglot_api_key', '' );
?>
<div class="wrap">
    <h1>API Key Settings</h1>
    <form method="post" action="">
		<?php wp_nonce_field( 'mwb_weglot_api_key_action', 'mwb_weglot_api_key_nonce' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'Weglot API Key', 'mwb-translate-manager' ); ?></th>
                <td><input type="text" name="mwb_weglot_api_key" value="<?php echo esc_attr( $api_key ); ?>"/></td>
            </tr>
        </table>
	    <?php submit_button( __( 'Save API Key', 'mwb-translate-manager' ) ); ?>
    </form>
</div>

<?php
function mwb_weglot_api_key_updated( $api_key ) {
	global $wpdb;
	$table_name_languages    = $wpdb->prefix . 'mwb_translation_languages';
	$table_name_texts        = $wpdb->prefix . 'mwb_english_texts';
	$table_name_translations = $wpdb->prefix . 'mwb_translations';

	// Delete the text "Hello World!" and related translations
	$hello_world_text = $wpdb->get_row( "SELECT * FROM $table_name_texts WHERE text = 'Hello World!'" );
	if ( $hello_world_text ) {
		$wpdb->delete( $table_name_texts, [ 'id' => $hello_world_text->id ] );
		$wpdb->delete( $table_name_translations, [ 'english_text_id' => $hello_world_text->id ] );
	}

	$languages = $wpdb->get_results( "SELECT language_code FROM $table_name_languages" );

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
				[ 'w' => 'Hello World!', 't' => 1 ]
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
		// Add the text "Hello World!" to the english_texts table
		$wpdb->insert( $table_name_texts, [
			'text' => 'Hello World!'
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

		echo '<div class="notice notice-success is-dismissible"><p>'.__('API Key test successful! Translations:', 'mwb-translate-manager').'</p><ul>';
		foreach ( $translations as $translation ) {
			echo '<li>' . esc_html( $translation['language_code'] ) . ': ' . esc_html( $translation['translated_text'] ) . '</li>';
		}
		echo '</ul></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p> ' . __( 'Translation failed for languages:', 'mwb-translate-manager' ) . ' ' . implode( ', ', $errors ) . '</p></div>';
	}
}

?>
