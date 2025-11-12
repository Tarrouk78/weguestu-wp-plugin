<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Fonction principale pour afficher la liste des jobs
function wegestu_jobs_render_list($response) {
    // 🔍 MODE DEBUG : si l’admin est connecté, afficher la réponse brute
    if ( current_user_can('administrator') ) {
        echo '<div style="background:#222;color:#0f0;padding:10px;font-size:12px;margin-bottom:15px;overflow:auto;max-height:300px;">';
        echo '<strong>🪲 DEBUG API RESPONSE (wegestu_jobs_render_list)</strong><br><pre>';
        print_r($response);
        echo '</pre></div>';
    }

    if (isset($response['error'])) {
        return '<div class="wegestu-jobs-error">'.esc_html($response['error']).'</div>';
    }

    // Structure attendue : data > data ou data
    $items = $response['data']['data'] ?? $response['data'] ?? $response;

    if (empty($items) || !is_array($items)) {
        return '<div class="wegestu-jobs-empty">'.esc_html__('Aucune offre trouvée.', 'wegestu-jobs').'</div>';
    }

    $html = '<div class="wegestu-jobs-list">';
    $html .= wegestu_jobs_render_list_items($items);
    $html .= '</div>';

    // Pagination
    if (isset($response['data']['next_page_url']) && $response['data']['next_page_url']) {
        $html .= '<div class="wegestu-jobs-pagination"><a href="#" class="wegestu-jobs-load-more">'.esc_html__('Charger plus', 'wegestu-jobs').'</a></div>';
    }

    return $html;
}

// Fonction pour afficher les items
function wegestu_jobs_render_list_items($items) {
    $html = '';
    foreach ($items as $job) {
        // 🔍 MODE DEBUG : vérifier structure d’un job
        if ( current_user_can('administrator') && !isset($job['id']) ) {
            echo '<div style="background:#330000;color:#fff;padding:5px;font-size:12px;">⚠️ Élément sans ID détecté : ';
            print_r($job);
            echo '</div>';
        }

        $title = esc_html($job['name'] ?? $job['title'] ?? __('(Titre non défini)', 'wegestu-jobs'));
        $company = esc_html($job['esn_name'] ?? $job['company'] ?? __('Entreprise inconnue', 'wegestu-jobs'));
        $location = esc_html($job['location'] ?? __('Localisation non précisée', 'wegestu-jobs'));
        $id = intval($job['id'] ?? 0);

        $html .= '<div class="wegestu-job-item">';
        $html .= '<h3 class="wegestu-job-title">'.$title.'</h3>';
        $html .= '<p class="wegestu-job-company">'.$company.'</p>';
        $html .= '<p class="wegestu-job-location">'.$location.'</p>';

        if ($id > 0) {
            $html .= '<a href="?job_id='.$id.'" class="wegestu-job-detail-link">'.esc_html__('Voir le détail', 'wegestu-jobs').'</a>';
        }

        $html .= '</div>';
    }
    return $html;
}

// Fonction pour afficher le détail d’un job
function wegestu_jobs_render_detail($response) {
    // 🔍 DEBUG
    if ( current_user_can('administrator') ) {
        echo '<div style="background:#112244;color:#0ff;padding:10px;font-size:12px;margin-bottom:15px;overflow:auto;max-height:300px;">';
        echo '<strong>🪲 DEBUG API RESPONSE (wegestu_jobs_render_detail)</strong><br><pre>';
        print_r($response);
        echo '</pre></div>';
    }

    if (isset($response['error'])) {
        return '<div class="wegestu-jobs-error">'.esc_html($response['error']).'</div>';
    }

    $job = $response['data'] ?? $response;

    if (empty($job) || !is_array($job)) {
        return '<div class="wegestu-jobs-empty">'.esc_html__('Offre introuvable.', 'wegestu-jobs').'</div>';
    }

    $title = esc_html($job['name'] ?? $job['title'] ?? __('(Titre non défini)', 'wegestu-jobs'));
    $company = esc_html($job['esn_name'] ?? $job['company'] ?? __('Entreprise inconnue', 'wegestu-jobs'));
    $location = esc_html($job['location'] ?? __('Localisation non précisée', 'wegestu-jobs'));
    $description = wp_kses_post($job['description'] ?? __('Pas de description disponible.', 'wegestu-jobs'));

    $html = '<div class="wegestu-job-detail">';
    $html .= '<h2>'.$title.'</h2>';
    $html .= '<p><strong>'.esc_html__('Entreprise :', 'wegestu-jobs').'</strong> '.$company.'</p>';
    $html .= '<p><strong>'.esc_html__('Localisation :', 'wegestu-jobs').'</strong> '.$location.'</p>';
    $html .= '<div class="wegestu-job-description">'.$description.'</div>';
    $html .= '</div>';

    return $html;
}
