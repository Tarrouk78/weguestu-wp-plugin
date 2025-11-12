<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Rendu de la liste des offres
 */
function wegestu_jobs_render_list($response) {
    if (isset($response['error'])) {
        return '<div class="wegestu-jobs-error">' . esc_html($response['error']) . '</div>';
    }

    // Détection du bon format de données
    if (isset($response['data']['data'])) {
        $items = $response['data']['data']; // format Laravel pagination
    } elseif (isset($response['data']) && is_array($response['data'])) {
        $items = $response['data']; // format direct
    } elseif (is_array($response)) {
        $items = $response;
    } else {
        $items = [];
    }

    $html  = '<div class="wegestu-jobs-list">';
    $html .= wegestu_jobs_render_list_items($items);
    $html .= '</div>';

    // Gestion pagination si "next_page_url" existe
    $next_page = $response['data']['next_page_url'] ?? null;
    if ($next_page) {
        $html .= '<div class="wegestu-jobs-pagination">';
        $html .= '<button class="wegestu-load-more" data-next="' . esc_url($next_page) . '">'
              . esc_html__('Charger plus', 'wegestu-jobs') . '</button>';
        $html .= '</div>';
    }

    return $html;
}

/**
 * Rendu des éléments individuels d'une liste d'offres
 */
function wegestu_jobs_render_list_items($items) {
    if (empty($items) || !is_array($items)) {
        return '<div class="wegestu-jobs-empty">' . esc_html__('Aucune offre trouvée.', 'wegestu-jobs') . '</div>';
    }

    $html = '';

    foreach ($items as $job) {
        $id       = $job['id'] ?? $job['ID_job'] ?? '';
        $title    = $job['name'] ?? $job['title'] ?? __('(Titre non défini)', 'wegestu-jobs');
        $company  = $job['esn_name'] ?? $job['company_name'] ?? '';
        $location = '';
        if (isset($job['city'])) {
            if (is_array($job['city'])) {
                $location = $job['city'][0] ?? '';
            } else {
                $location = $job['city'];
            }
        }
        $desc = $job['description'] ?? $job['desc'] ?? '';
        $apply_url = home_url('/offres/' . intval($id) . '/');

        $html .= '<article class="wegestu-job-item">';
        $html .= '<h3>' . esc_html($title) . '</h3>';

        if ($company) {
            $html .= '<div><strong>' . esc_html__('Entreprise : ', 'wegestu-jobs') . '</strong>' . esc_html($company) . '</div>';
        }
        if ($location) {
            $html .= '<div><strong>' . esc_html__('Lieu : ', 'wegestu-jobs') . '</strong>' . esc_html($location) . '</div>';
        }
        if ($desc) {
            $html .= '<div class="wegestu-job-excerpt">'
                  . wp_kses_post(wp_trim_words(wp_strip_all_tags($desc), 30, '...'))
                  . '</div>';
        }

        $html .= '<a href="' . esc_url($apply_url) . '">' . esc_html__('Voir le détail', 'wegestu-jobs') . '</a>';
        $html .= '</article>';
    }

    return $html;
}

/**
 * Rendu du détail d'une offre
 */
function wegestu_jobs_render_detail($job) {
    if (isset($job['error'])) {
        return '<div class="wegestu-jobs-error">' . esc_html($job['error']) . '</div>';
    }

    $j = $job['data'] ?? $job;

    $id         = $j['id'] ?? $j['ID_job'] ?? '';
    $title      = $j['name'] ?? $j['title'] ?? '';
    $company    = $j['esn_name'] ?? $j['company_name'] ?? '';
    $location   = '';
    if (isset($j['city'])) {
        $location = is_array($j['city']) ? ($j['city'][0] ?? '') : $j['city'];
    }
    $desc       = $j['description'] ?? '';
    $salary     = $j['salary'] ?? '';
    $experience = $j['years_of_experience'] ?? '';
    $skills     = !empty($j['skills']) ? implode(', ', (array)$j['skills']) : '';
    $date       = $j['created_at'] ?? '';
    $apply_url  = home_url('/offres/' . intval($id) . '/');

    $html  = '<div class="wegestu-job-detail">';
    $html .= '<h2>' . esc_html($title) . '</h2>';

    if ($company) {
        $html .= '<div><strong>' . esc_html__('Entreprise : ', 'wegestu-jobs') . '</strong>' . esc_html($company) . '</div>';
    }
    if ($location) {
        $html .= '<div><strong>' . esc_html__('Lieu : ', 'wegestu-jobs') . '</strong>' . esc_html($location) . '</div>';
    }
    if ($salary) {
        $html .= '<div><strong>' . esc_html__('Salaire : ', 'wegestu-jobs') . '</strong>' . esc_html($salary) . '</div>';
    }
    if ($experience) {
        $html .= '<div><strong>' . esc_html__('Expérience : ', 'wegestu-jobs') . '</strong>' . esc_html($experience) . ' ans</div>';
    }
    if ($skills) {
        $html .= '<div><strong>' . esc_html__('Compétences : ', 'wegestu-jobs') . '</strong>' . esc_html($skills) . '</div>';
    }
    if ($date) {
        $html .= '<div><strong>' . esc_html__('Publié le : ', 'wegestu-jobs') . '</strong>' . esc_html($date) . '</div>';
    }
    if ($desc) {
        $html .= '<div class="wegestu-job-description">' . wp_kses_post(wpautop($desc)) . '</div>';
    }

    $html .= '<div class="wegestu-job-apply"><a href="' . esc_url($apply_url) . '">' . esc_html__('Postuler maintenant', 'wegestu-jobs') . '</a></div>';
    $html .= '</div>';

    return $html;
}
