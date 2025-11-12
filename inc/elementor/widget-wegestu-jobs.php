<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! class_exists( 'Wegestu_Widget_Jobs' ) ) {

    class Wegestu_Widget_Jobs extends Widget_Base {

        public function get_name() {
            return 'wegestu_jobs';
        }

        public function get_title() {
            return __( 'Wegestu Jobs', 'wegestu-jobs' );
        }

        public function get_icon() {
            return 'eicon-post-list';
        }

        public function get_categories() {
            return array( 'general' );
        }

        public function get_keywords() {
            return array( 'jobs', 'offres', 'wegestu' );
        }

        protected function register_controls() {

            $this->start_controls_section(
                'section_content',
                [
                    'label' => __( 'Contenu', 'wegestu-jobs' ),
                ]
            );

            $this->add_control(
                'title',
                [
                    'label' => __( 'Titre (optionnel)', 'wegestu-jobs' ),
                    'type' => Controls_Manager::TEXT,
                    'default' => __( 'Offres d\'emploi', 'wegestu-jobs' ),
                ]
            );

            $this->add_control(
                'per_page',
                [
                    'label' => __( 'Nombre par page', 'wegestu-jobs' ),
                    'type' => Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 50,
                    'step' => 1,
                    'default' => 5,
                ]
            );

            $this->add_control(
    'job_id',
    [
        'label' => __( 'Afficher une offre spécifique (ID)', 'wegestu-jobs' ),
        'type' => Controls_Manager::NUMBER,
        'min' => 1,
        'default' => '',
        'description' => __( 'Si défini, affiche uniquement cette offre.', 'wegestu-jobs' ),
    ]
);


            $this->add_control(
                'show_company',
                [
                    'label' => __( 'Afficher le nom de l\'entreprise', 'wegestu-jobs' ),
                    'type' => Controls_Manager::SWITCHER,
                    'label_on' => __( 'Oui', 'wegestu-jobs' ),
                    'label_off' => __( 'Non', 'wegestu-jobs' ),
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );

            $this->add_control(
                'show_location',
                [
                    'label' => __( 'Afficher la localisation', 'wegestu-jobs' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );

            $this->add_control(
                'show_apply_button',
                [
                    'label' => __( 'Afficher le bouton Postuler', 'wegestu-jobs' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );

            $this->add_control(
                'show_load_more',
                [
                    'label' => __( 'Afficher "Charger plus"', 'wegestu-jobs' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );

            $this->end_controls_section();

            // Section styles (optionnel)
            $this->start_controls_section(
                'section_style',
                [
                    'label' => __( 'Styles', 'wegestu-jobs' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'title_color',
                [
                    'label' => __( 'Couleur du titre', 'wegestu-jobs' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wegestu-job-title' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->end_controls_section();
        }

        protected function render() {
            $settings = $this->get_settings_for_display();

            // Titre optionnel
            if ( ! empty( $settings['title'] ) ) {
                echo '<h2 class="wegestu-widget-title">' . esc_html( $settings['title'] ) . '</h2>';
            }

            // Récupérer les jobs via la classe API existante
          $api = new Wegestu_API();

if ( ! empty( $settings['job_id'] ) ) {
    $response = $api->get_job_by_id( intval($settings['job_id']) );
    if ( isset($response['data']) ) {
        $job = is_array($response['data']) ? $response['data'] : $response;
    } else {
        $job = $response;
    }
    echo wegestu_jobs_render_detail( $job ); // réutilise ton template
    return;
}

$per_page = intval( $settings['per_page'] ?? 5 );
$response = $api->get_jobs( $per_page, 1 );


            // Si erreur -> message simple
            if ( isset( $response['error'] ) ) {
                echo '<div class="wegestu-jobs-error">' . esc_html( $response['error'] ) . '</div>';
                return;
            }

            // Extraire la liste (selon structure API)
            $items = isset($response['data']['data']) ? $response['data']['data'] : ( isset($response['data']) ? $response['data'] : $response );

            // Construire le HTML en tenant compte des contrôles
            echo '<div class="wegestu-jobs-widget">';
            if ( empty( $items ) || ! is_array( $items ) ) {
                echo '<div class="wegestu-jobs-empty">' . esc_html__( 'Aucune offre trouvée.', 'wegestu-jobs' ) . '</div>';
            } else {
                echo '<div class="wegestu-jobs-list">';
                foreach ( $items as $job ) {
                    $id = $job['id'] ?? '';
                    $title = $job['name'] ?? '';
                    $company = $job['esn_name'] ?? '';
                    $location = isset($job['city'][0]) ? $job['city'][0] : '';
                    $desc = $job['description'] ?? '';

                    echo '<article class="wegestu-job-item" data-job-id="' . esc_attr($id) . '">';
                    echo '<h3 class="wegestu-job-title">' . esc_html( $title ) . '</h3>';

                    if ( $settings['show_company'] === 'yes' && $company ) {
                        echo '<div class="wegestu-job-company">' . esc_html( $company ) . '</div>';
                    }

                    if ( $settings['show_location'] === 'yes' && $location ) {
                        echo '<div class="wegestu-job-location">' . esc_html( $location ) . '</div>';
                    }

                    if ( $desc ) {
                        echo '<div class="wegestu-job-excerpt">' . wp_kses_post( wp_trim_words( wp_strip_all_tags($desc), 20, '...' ) ) . '</div>';
                    }

                    if ( $settings['show_apply_button'] === 'yes' ) {
                        $apply_url = esc_url( add_query_arg( array( 'job_id' => $id ), home_url() ) );
                        echo '<div class="wegestu-job-apply"><a class="wegestu-apply-btn" href="' . $apply_url . '">' . esc_html__( 'Postuler', 'wegestu-jobs' ) . '</a></div>';
                    }

                    echo '</article>';
                }
                echo '</div>'; // .wegestu-jobs-list

                // Load more (si activé)
                if ( $settings['show_load_more'] === 'yes' && isset( $response['data']['next_page_url'] ) && ! empty( $response['data']['next_page_url'] ) ) {
                    // stocker next_page_url dans un data attr pour JS
                    $next = esc_url( $response['data']['next_page_url'] );
                    echo '<div class="wegestu-jobs-pagination"><button class="wegestu-load-more" data-next="' . $next . '" data-per_page="' . esc_attr( $per_page ) . '">' . esc_html__( 'Charger plus', 'wegestu-jobs' ) . '</button></div>';
                }
            }
            echo '</div>'; // .wegestu-jobs-widget
        }

    }
}
