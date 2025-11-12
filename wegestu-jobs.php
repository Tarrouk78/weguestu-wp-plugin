<?php
/**
 * Plugin Name: Wegestu Jobs Connector
 * Description: Connecte WordPress à l'API Wegestu pour afficher les offres d'emploi et les entreprises. Shortcodes: [wegestu_jobs], [wegestu_job id="..."]
 * Version: 1.0.4
 * Author: Wegestu
 * Text Domain: wegestu-jobs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WEGESTU_JOBS_DIR', plugin_dir_path( __FILE__ ) );
define( 'WEGESTU_JOBS_URL', plugin_dir_url( __FILE__ ) );
define( 'WEGESTU_JOBS_VERSION', '1.0.4' );

// Includes
require_once WEGESTU_JOBS_DIR . 'inc/class-wegestu-api.php';
require_once WEGESTU_JOBS_DIR . 'inc/admin-settings.php';
require_once WEGESTU_JOBS_DIR . 'inc/templates.php';

// Elementor integration
if ( did_action( 'elementor/loaded' ) ) {
    require_once WEGESTU_JOBS_DIR . 'inc/elementor/class-wegestu-elementor-loader.php';
    Wegestu_Elementor_Loader::init();
}

// Enqueue assets
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'wegestu-jobs-css', WEGESTU_JOBS_URL . 'assets/css/wegestu-jobs.css', [], WEGESTU_JOBS_VERSION );
    wp_enqueue_script( 'wegestu-jobs-js', WEGESTU_JOBS_URL . 'assets/js/wegestu-jobs.js', ['jquery'], WEGESTU_JOBS_VERSION, true );
    wp_localize_script( 'wegestu-jobs-js', 'WegestuJobs', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'wegestu_jobs_nonce' ),
    ]);
});

// Shortcodes
add_shortcode( 'wegestu_jobs', 'wegestu_jobs_list_shortcode' );
add_shortcode( 'wegestu_job', 'wegestu_job_detail_shortcode' );

function wegestu_jobs_list_shortcode( $atts ) {
    $atts = shortcode_atts([ 'per_page'=>5, 'page'=>1 ], $atts, 'wegestu_jobs');
    $api = new Wegestu_API();
    $response = $api->get_jobs(intval($atts['per_page']), intval($atts['page']));
    return wegestu_jobs_render_list($response);
}

function wegestu_job_detail_shortcode( $atts ) {
    $atts = shortcode_atts([ 'id'=>0 ], $atts, 'wegestu_job');
    $id = intval($atts['id']) ?: get_query_var('job_id');
    if (!$id) return '<p>'.esc_html__('Identifiant d\'offre invalide','wegestu-jobs').'</p>';
    $api = new Wegestu_API();
    $job = $api->get_job_by_id($id);
    return wegestu_jobs_render_detail($job);
}

// Activation hook : options par défaut + flush rewrite
register_activation_hook( __FILE__, function() {

    $defaults = [
        'wegestu_api_url'   => 'https://talents.test.wegestu.com/',
        'wegestu_api_token' => '',
        'wegestu_per_page'  => 5,
    ];

    foreach( $defaults as $key => $value ) {
        if ( get_option($key) === false ) {
            update_option($key, $value);
        }
    }

    // Rewrite rules pour /offres/ID
    add_rewrite_rule('^offres/([0-9]+)/?$', 'index.php?pagename=offres&job_id=$matches[1]', 'top');
    flush_rewrite_rules();
});

// Init rewrite rules
add_action('init', function() {
    add_rewrite_rule('^offres/([0-9]+)/?$', 'index.php?pagename=offres&job_id=$matches[1]', 'top');
});

// Query var pour job_id
add_filter('query_vars', function($vars){ 
    $vars[] = 'job_id'; 
    return $vars; 
});
