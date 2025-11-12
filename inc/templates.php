<?php
if ( ! defined('ABSPATH') ) exit;

function wegestu_jobs_render_list($response) {
    if(isset($response['error'])) return '<div class="wegestu-jobs-error">'.esc_html($response['error']).'</div>';

    $items = $response['data']['data'] ?? $response['data'] ?? $response;

    $html = '<div class="wegestu-jobs-list">';
    $html .= wegestu_jobs_render_list_items($items);
    $html .= '</div>';

    $next_page = $response['data']['next_page_url'] ?? null;
    if($next_page){
        $html .= '<div class="wegestu-jobs-pagination">';
        $html .= '<button class="wegestu-load-more" data-next="'.esc_url($next_page).'">'.esc_html__('Charger plus', 'wegestu-jobs').'</button>';
        $html .= '</div>';
    }

    return $html;
}

function wegestu_jobs_render_list_items($items){
    if(empty($items) || !is_array($items)) return '<div class="wegestu-jobs-empty">'.esc_html__('Aucune offres trouvée.', 'wegestu-jobs').'</div>';

    $html = '';
    foreach($items as $job){
        $id = $job['id'] ?? '';
        $title = $job['name'] ?? __('(Titre non défini)','wegestu-jobs');
        $company = $job['esn_name'] ?? '';
        $location = $job['city'][0] ?? '';
        $desc = $job['description'] ?? '';
        $apply_url = home_url('/offres/'.intval($id).'/');

        $html .= '<article class="wegestu-job-item">';
        $html .= '<h3>'.$title.'</h3>';
        if($company) $html .= '<div><strong>'.esc_html__('Entreprise: ','wegestu-jobs').'</strong>'.$company.'</div>';
        if($location) $html .= '<div><strong>'.esc_html__('Lieu: ','wegestu-jobs').'</strong>'.$location.'</div>';
        if($desc) $html .= '<div class="wegestu-job-excerpt">'.wp_kses_post(wp_trim_words(wp_strip_all_tags($desc),30,'...')).'</div>';
        $html .= '<a href="'.$apply_url.'">'.esc_html__('Voir le détail','wegestu-jobs').'</a>';
        $html .= '</article>';
    }
    return $html;
}

function wegestu_jobs_render_detail($job){
    if(isset($job['error'])) return '<div class="wegestu-jobs-error">'.esc_html($job['error']).'</div>';

    $j = $job['data'] ?? $job;

    $id = $j['id'] ?? '';
    $title = $j['name'] ?? '';
    $company = $j['esn_name'] ?? '';
    $location = $j['city'][0] ?? '';
    $desc = $j['description'] ?? '';
    $salary = $j['salary'] ?? '';
    $experience = $j['years_of_experience'] ?? '';
    $skills = !empty($j['skills']) ? implode(', ', $j['skills']) : '';
    $date = $j['created_at'] ?? '';
    $apply_url = home_url('/offres/'.intval($id).'/');

    $html = '<div class="wegestu-job-detail">';
    $html .= '<h2>'.$title.'</h2>';
    if($company) $html .= '<div><strong>'.esc_html__('Entreprise: ','wegestu-jobs').'</strong>'.$company.'</div>';
    if($location) $html .= '<div><strong>'.esc_html__('Lieu: ','wegestu-jobs').'</strong>'.$location.'</div>';
    if($salary) $html .= '<div><strong>'.esc_html__('Salaire: ','wegestu-jobs').'</strong>'.$salary.'</div>';
    if($experience) $html .= '<div><strong>'.esc_html__('Expérience: ','wegestu-jobs').'</strong>'.$experience.' ans</div>';
    if($skills) $html .= '<div><strong>'.esc_html__('Compétences: ','wegestu-jobs').'</strong>'.$skills.'</div>';
    if($date) $html .= '<div><strong>'.esc_html__('Publié le: ','wegestu-jobs').'</strong>'.$date.'</div>';
    if($desc) $html .= '<div class="wegestu-job-description">'.wp_kses_post(wpautop($desc)).'</div>';
    $html .= '<div class="wegestu-job-apply"><a href="'.$apply_url.'">'.esc_html__('Postuler maintenant','wegestu-jobs').'</a></div>';
    $html .= '</div>';

    return $html;
}
