<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wegestu_Elementor_Loader {

    public static function init() {
        // Enregistrer les widgets Elementor
        add_action('elementor/widgets/register', [__CLASS__, 'register_widgets']);

        // Enqueue styles et scripts
        add_action('elementor/frontend/after_enqueue_styles', [__CLASS__, 'enqueue_styles']);
        add_action('elementor/frontend/after_register_scripts', [__CLASS__, 'enqueue_scripts']);
    }

    public static function register_widgets($widgets_manager) {
        // Charger la classe du widget Job Detail
        require_once WEGESTU_JOBS_DIR . 'inc/elementor/class-wegestu-elementor-job-detail.php';
        if ( class_exists( 'Wegestu_Elementor_Job_Detail' ) ) {
            $widgets_manager->register( new Wegestu_Elementor_Job_Detail() );
        }

        // Si tu as un autre widget liste de jobs, tu peux le charger ici
        // require_once WEGESTU_JOBS_DIR . 'inc/elementor/widget-wegestu-jobs.php';
        // if ( class_exists( 'Wegestu_Widget_Jobs' ) ) {
        //     $widgets_manager->register( new Wegestu_Widget_Jobs() );
        // }
    }

    public static function enqueue_styles() {
        wp_enqueue_style( 'wegestu-jobs-elementor', WEGESTU_JOBS_URL . 'assets/css/wegestu-jobs.css', array(), WEGESTU_JOBS_VERSION );
    }

    public static function enqueue_scripts() {
        wp_enqueue_script( 'wegestu-jobs-js', WEGESTU_JOBS_URL . 'assets/js/wegestu-jobs.js', array('jquery'), WEGESTU_JOBS_VERSION, true );
        wp_localize_script( 'wegestu-jobs-js', 'WegestuJobs', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wegestu_jobs_nonce' ),
        ));
    }
}
