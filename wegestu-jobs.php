<?php
/**
 * Plugin Name: Wegestu Jobs Connector
 * Description: Connecte WordPress à l'API Wegestu pour afficher les offres d'emploi et les entreprises. Shortcodes: [wegestu_jobs], [wegestu_job id="..."]
 * Version: 1.0.2
 * Author: Wegestu
 * Text Domain: wegestu-jobs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WEGESTU_JOBS_DIR', plugin_dir_path( __FILE__ ) );
define( 'WEGESTU_JOBS_URL', plugin_dir_url( __FILE__ ) );
define( 'WEGESTU_JOBS_VERSION', '1.0.2' );

// Includes
require_once WEGESTU_JOBS_DIR . 'inc/class-wegestu-api.php';
require_once WEGESTU_JOBS_DIR . 'inc/admin-settings.php';
require_once WEGESTU_JOBS_DIR . 'inc/templates.php';

// Elementor integration
if ( did_action( 'elementor/loaded' ) ) {
    require_once WEGESTU_JOBS_DIR . 'inc/elementor/class-wegestu-elementor-loader.php';
    Wegestu_Elementor_Loader::init();
}

/**
 * Enqueue assets
 */
function wegestu_jobs_enqueue_assets() {
    wp_enqueue_style( 'wegestu-jobs-css', WEGESTU_JOBS_URL . 'assets/css/wegestu-jobs.css', [], WEGESTU_JOBS_VERSION );
    wp_enqueue_script( 'wegestu-jobs-js', WEGESTU_JOBS_URL . 'assets/js/wegestu-jobs.js', ['jquery'], WEGESTU_JOBS_VERSION, true );

    wp_localize_script( 'wegestu-jobs-js', 'WegestuJobs', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'wegestu_jobs_nonce' ),
    ]);
}
add_action( 'wp_enqueue_scripts', 'wegestu_jobs_enqueue_assets' );

/**
 * Shortcodes
 */
add_shortcode( 'wegestu_jobs', 'wegestu_jobs_list_shortcode' );
add_shortcode( 'wegestu_job', 'wegestu_job_detail_shortcode' );

function wegestu_jobs_list_shortcode( $atts ) {
    $atts = shortcode_atts([
        'per_page' => 5,
        'page'     => 1,
    ], $atts, 'wegestu_jobs');

    $api = new Wegestu_API();
    $response = $api->get_jobs( intval($atts['per_page']), intval($atts['page']) );

    return wegestu_jobs_render_list( $response );
}

function wegestu_job_detail_shortcode( $atts ) {
    $atts = shortcode_atts([ 'id' => 0 ], $atts, 'wegestu_job');

    $id = intval($atts['id']) ?: get_query_var('job_id');

    if ( ! $id ) {
        return '<p>' . esc_html__('Identifiant d\'offre invalide', 'wegestu-jobs') . '</p>';
    }

    $api = new Wegestu_API();
    $job = $api->get_job_by_id( $id );

    // Debug API
    // echo '<pre>'; print_r($job); echo '</pre>'; exit;

    return wegestu_jobs_render_detail( $job );
}

/**
 * AJAX "Load More"
 */
add_action( 'wp_ajax_wegestu_load_more', 'wegestu_jobs_ajax_load_more' );
add_action( 'wp_ajax_nopriv_wegestu_load_more', 'wegestu_jobs_ajax_load_more' );

function wegestu_jobs_ajax_load_more() {
    check_ajax_referer( 'wegestu_jobs_nonce', 'nonce' );

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 5;

    $api = new Wegestu_API();
    $response = $api->get_jobs( $per_page, $page );

    $html = wegestu_jobs_render_list_items( $response );

    wp_send_json_success([ 'html' => $html ]);
}

/**
 * Activation hook
 */
register_activation_hook( __FILE__, 'wegestu_jobs_activate' );

function wegestu_jobs_activate() {
    $defaults = [
        'base_url' => 'https://talents.test.wegestu.com/',
        'token'    => '',
        'per_page_default' => 5,
    ];

    foreach( $defaults as $k => $v ) {
        if ( get_option('wegestu_jobs_' . $k) === false ) {
            update_option('wegestu_jobs_' . $k, $v);
        }
    }

    wegestu_add_rewrite_rules();
    flush_rewrite_rules();
}

/**
 * Rewrite rules pour les détails
 */
function wegestu_add_rewrite_rules() {
    add_rewrite_rule(
        '^offres/([0-9]+)/?$',
        'index.php?pagename=offres&job_id=$matches[1]',
        'top'
    );
}
add_action('init', 'wegestu_add_rewrite_rules');

/**
 * Query var
 */
add_filter('query_vars', 'wegestu_add_query_vars');
function wegestu_add_query_vars($vars) {
    $vars[] = 'job_id';
    return $vars;
}
