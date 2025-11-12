<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'wegestu_jobs_base_url' );
delete_option( 'wegestu_jobs_token' );
delete_option( 'wegestu_jobs_per_page_default' );

// Optionnel : supprimer transients
global $wpdb;
$like = '_transient_wegestu_%';
$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $like ) );
