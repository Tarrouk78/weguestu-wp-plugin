<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Wegestu_Elementor_Job_Detail extends Widget_Base {

    public function get_name() {
        return 'wegestu_job_detail';
    }

    public function get_title() {
        return __( 'Wegestu Job Detail', 'wegestu-jobs' );
    }

    public function get_icon() {
        return 'eicon-post-content';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Contenu', 'wegestu-jobs' ),
            ]
        );

        $this->add_control(
            'job_id',
            [
                'label' => __( 'ID du Job (optionnel)', 'wegestu-jobs' ),
                'type' => Controls_Manager::NUMBER,
                'description' => __( 'Si laissé vide, prendra l\'ID de l\'URL (query_var job_id)', 'wegestu-jobs' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $job_id = intval( $settings['job_id'] ) ?: get_query_var('job_id');

        if ( ! $job_id ) {
            echo '<p>' . esc_html__( 'Aucun job sélectionné', 'wegestu-jobs' ) . '</p>';
            return;
        }

        $api = new Wegestu_API();
        $job = $api->get_job_by_id( $job_id );

        if ( isset( $job['error'] ) ) {
            echo '<div class="wegestu-jobs-error">'.esc_html($job['error']).'</div>';
            return;
        }

        echo wegestu_jobs_render_detail( $job );
    }

    protected function _content_template() {
        ?>
        <#
        var job_id = settings.job_id || '';
        #>
        <div class="wegestu-job-detail-elementor">
            {{{ job_id }}}
        </div>
        <?php
    }
}
