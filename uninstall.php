<?php
/**
 * Removes the settings. Submissions are kept unless the
 * HEADLESS_FORM_DELETE_DATA constant is true, so uninstalling never loses messages by accident.
 *
 * @package Headless_Form
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'headless_form_settings' );

if ( defined( 'HEADLESS_FORM_DELETE_DATA' ) && HEADLESS_FORM_DELETE_DATA ) {
	$headless_form_ids = get_posts(
		array(
			'post_type'      => 'hdform_submission',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $headless_form_ids as $headless_form_id ) {
		wp_delete_post( $headless_form_id, true );
	}
}
