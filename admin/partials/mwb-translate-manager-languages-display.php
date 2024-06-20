<?php
global $wpdb;
$table_name = $wpdb->prefix . 'mwb_translation_languages';

// Handle form submissions for adding/deleting languages
if (isset($_POST['action'])) {
	// Check CSRF protection
	if (!check_admin_referer('mwb_manage_languages_action', 'mwb_manage_languages_nonce')) {
		wp_die('The security check failed.');
	}

	if ($_POST['action'] == 'add_language' && !empty($_POST['language_name']) && !empty($_POST['language_code'])) {
		$wpdb->insert($table_name, [
			'language_name' => sanitize_text_field($_POST['language_name']),
			'language_code' => sanitize_text_field($_POST['language_code'])
		]);
		echo '<div class="notice notice-success is-dismissible"><p>'.__('Language added successfully!', 'mwb-translate-manager').'</p></div>';
	} elseif ($_POST['action'] == 'delete_language' && !empty($_POST['language_id'])) {
		$wpdb->delete($table_name, ['id' => intval($_POST['language_id'])]);
		echo '<div class="notice notice-success is-dismissible"><p>'.__('Language deleted successfully!', 'mwb-translate-manager').'</p></div>';
	}
}

// Retrieve languages from the database
$languages = $wpdb->get_results("SELECT * FROM $table_name");
?>
<div class="wrap">
    <h1><?php _e('MWB Translate Manager Languages', 'mwb-translate-manager'); ?></h1>
    <form method="post" action="">
		<?php wp_nonce_field('mwb_manage_languages_action', 'mwb_manage_languages_nonce'); ?>
        <input type="hidden" name="action" value="add_language">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('New Language NAme', 'mwb-translate-manager'); ?></th>
                <td><input type="text" name="language_name" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Language Code', 'mwb-translate-manager'); ?></th>
                <td><input type="text" name="language_code" required /></td>
            </tr>
        </table>
	    <?php submit_button( __( 'Add Language', 'mwb-translate-manager' ) ); ?>
    </form>
    <hr />
    <h2><?php _e('Languages', 'mwb-translate-manager'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th><?php _e('ID', 'mwb-translate-manager'); ?></th>
            <th><?php _e('Language Name', 'mwb-translate-manager'); ?></th>
            <th><?php _e('Language Code', 'mwb-translate-manager'); ?></th>
            <th><?php _e('Actions', 'mwb-translate-manager'); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($languages as $language): ?>
            <tr>
                <td><?php echo esc_html($language->id); ?></td>
                <td><?php echo esc_html($language->language_name); ?></td>
                <td><?php echo esc_html($language->language_code); ?></td>
                <td>
                    <form method="post" action="" style="display:inline;">
						<?php wp_nonce_field('mwb_manage_languages_action', 'mwb_manage_languages_nonce'); ?>
                        <input type="hidden" name="action" value="delete_language">
                        <input type="hidden" name="language_id" value="<?php echo esc_attr($language->id); ?>">
	                    <?php submit_button(esc_html__('Delete', 'mwb-translate-manager'), 'delete', '', false); ?>
                    </form>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
</div>
