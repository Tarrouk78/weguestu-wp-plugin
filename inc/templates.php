<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Affiche la liste des offres
 */
function wegestu_jobs_render_list($response) {

    // 🔍 DEBUG : Affiche données brutes pour admin ou si ?debug=1
    if ( current_user_can('administrator') || isset($_GET['debug']) ) {
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

    // Pagination si existe
    $next_page = $response['data']['next_page_url'] ?? null;
    if($next_page){
        $html .= '<div class="wegestu-jobs-pagination">';
        $html .= '<button class="wegestu-load-more" data-next="'.esc_url($next_page).'">'.esc_html__('Charger plus', 'wegestu-jobs').'</button>';
        $html .= '</div>';
    }

    return $html;
}

/**
 * Affiche les items (jobs)
 */
function wegestu_jobs_render_list_items($items) {
    if (empty($items) || !is_array($items)) {
        return '<div class="wegestu-jobs-empty">'.esc_html__('Aucune offre trouvée.', 'wegestu-jobs').'</div>';
    }

    $html = '';
    foreach ($items as $job) {

        // 🔍 DEBUG job sans ID
        if ( current_user_can('administrator') && !isset($job['id']) ) {
            echo '<div style="background:#330000;color:#fff;padding:5px;font-size:12px;">⚠️ Job sans ID détecté : ';
            print_r($job);
            echo '</div>';
        }

        $id       = intval($job['id'] ?? 0);
        $title    = esc_html($job['name'] ?? $job['title'] ?? __('(Titre non défini)','wegestu-jobs'));
        $company  = esc_html($job['esn_name'] ?? $job['company'] ?? '');
        $location = esc_html($job['city'][0] ?? $job['location'] ?? '');
        $desc     = wp_kses_post($job['description'] ?? '');

        $apply_url = $id ? home_url('/offres/'. $id .'/') : '#';

        $html .= '<article class="wegestu-job-item">';
        $html .= '<h3>'.$title.'</h3>';
        if($company)  $html .= '<div><strong>'.esc_html__('Entreprise: ','wegestu-jobs').'</strong>'.$company.'</div>';
        if($location) $html .= '<div><strong>'.esc_html__('Lieu: ','wegestu-jobs').'</strong>'.$location.'</div>';
        if($desc)     $html .= '<div class="wegestu-job-excerpt">'.wp_trim_words(wp_strip_all_tags($desc),30,'...').'</div>';
        if($id)       $html .= '<a href="'.$apply_url.'">'.esc_html__('Voir le détail','wegestu-jobs').'</a>';
        $html .= '</article>';
    }

    return $html;
}

/**
 * Affiche le détail d’un job
 */
function wegestu_jobs_render_detail($response) {

    // 🔍 DEBUG : données brutes
    if ( current_user_can('administrator') || isset($_GET['debug']) ) {
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

    $id         = intval($job['id'] ?? 0);
    $title      = esc_html($job['name'] ?? $job['title'] ?? '');
    $company    = esc_html($job['esn_name'] ?? $job['company'] ?? '');
    $location   = esc_html($job['city'][0] ?? $job['location'] ?? '');
    $desc       = wp_kses_post($job['description'] ?? '');
    $salary     = esc_html($job['salary'] ?? '');
    $experience = esc_html($job['years_of_experience'] ?? '');
    $skills     = !empty($job['skills']) ? implode(', ', $job['skills']) : '';
    $date       = esc_html($job['created_at'] ?? '');
    $apply_url  = $id ? home_url('/offres/'. $id .'/') : '#';

    $html = '<div class="wegestu-job-detail">';
    $html .= '<h2>'.$title.'</h2>';
    if($company)    $html .= '<div><strong>'.esc_html__('Entreprise: ','wegestu-jobs').'</strong>'.$company.'</div>';
    if($location)   $html .= '<div><strong>'.esc_html__('Lieu: ','wegestu-jobs').'</strong>'.$location.'</div>';
    if($salary)     $html .= '<div><strong>'.esc_html__('Salaire: ','wegestu-jobs').'</strong>'.$salary.'</div>';
    if($experience) $html .= '<div><strong>'.esc_html__('Expérience: ','wegestu-jobs').'</strong>'.$experience.' ans</div>';
    if($skills)     $html .= '<div><strong>'.esc_html__('Compétences: ','wegestu-jobs').'</strong>'.$skills.'</div>';
    if($date)       $html .= '<div><strong>'.esc_html__('Publié le: ','wegestu-jobs').'</strong>'.$date.'</div>';
    if($desc)       $html .= '<div class="wegestu-job-description">'.wpautop($desc).'</div>';
    $html .= '<div class="wegestu-job-apply"><a href="'.$apply_url.'">'.esc_html__('Postuler maintenant','wegestu-jobs').'</a></div>';
    $html .= '</div>';

    return $html;
}
