<?php
global $wpdb;
$table_name_texts        = $wpdb->prefix . 'mwb_english_texts';
$table_name_languages    = $wpdb->prefix . 'mwb_translation_languages';
$table_name_translations = $wpdb->prefix . 'mwb_translations';

// Get the number of English texts
$english_texts_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name_texts" );

// Accessible API URLs and text counts
$api_urls = [
	'English' => [
		'url'   => rest_url( 'mwb/v1/translations/en' ),
		'count' => $english_texts_count
	]
];

// Get available languages from the Languages table
$languages = $wpdb->get_results( "SELECT language_code, language_name FROM $table_name_languages" );

foreach ( $languages as $language ) {
	$translation_count                    = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM $table_name_translations WHERE language_code = %s",
		$language->language_code
	) );
	$api_urls[ $language->language_name ] = [
		'url'   => rest_url( 'mwb/v1/translations/' . $language->language_code ),
		'count' => $translation_count
	];
}

// Get Cron tasks
$cron_jobs = _get_cron_array();

?>
<div class="wrap">
    <h1><?php _e( 'MWB Translate Manager Dashboard', 'mwb-translate-manager' ); ?></h1>
    <div class="mwb-dashboard-widgets-wrap">
		<?php foreach ( $api_urls as $language => $info ): ?>
            <div class="mwb-dashboard-widget">
                <h2><?php echo esc_html( $info['count'] ); ?> <?php _e( 'Total Texts', 'mwb-translate-manager' ); ?> (<?php echo esc_html( $language ); ?>)</h2>
                <p><?php _e( 'API URL:', 'mwb-translate-manager' ); ?> <a href="<?php echo esc_url( $info['url'] ); ?>"
                               target="_blank"><?php echo esc_html( $info['url'] ); ?></a></p>
            </div>
		<?php endforeach; ?>
    </div>

    <h2><?php _e( 'Cron Jobs', 'mwb-translate-manager' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th><?php _e( 'Next Run (GMT/UTC)', 'mwb-translate-manager' ); ?></th>
            <th><?php _e( 'Schedule', 'mwb-translate-manager' ); ?></th>
            <th><?php _e( 'Hook', 'mwb-translate-manager' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ( $cron_jobs as $timestamp => $cron ) : ?>
			<?php foreach ( $cron as $hook => $dings ) : ?>
				<?php foreach ( $dings as $sig => $data ) : ?>
                    <tr>
                        <td><?php echo esc_html( date( 'Y-m-d H:i:s', $timestamp ) ); ?></td>
                        <td><?php echo isset( $data['schedule'] ) ? esc_html( $data['schedule'] ) : 'One-time'; ?></td>
                        <td><?php echo esc_html( $hook ); ?></td>
                    </tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<?php endforeach; ?>
        </tbody>
    </table>
</div>
