<?php
if ( ! defined('ABSPATH') ) exit;

class Wegestu_API {

    private $base_url;
    private $token;
    private $transient_ttl = 300; // 5 min

    public function __construct() {
        $this->base_url = trailingslashit(get_option('wegestu_jobs_base_url', 'https://api.test.wegestu.com/'));
        $this->token = get_option('wegestu_jobs_token', '');
    }

    public function get_jobs($per_page=5, $page=1) {
        $cache_key = "wegestu_jobs_list_{$per_page}_{$page}";
        $cached = get_transient($cache_key);
        if ($cached !== false) return $cached;

        $url = $this->base_url . "api/User/getListJob?per_page=".intval($per_page)."&page=".intval($page);
        $resp = wp_remote_get($url, ['headers' => $this->default_headers(), 'timeout'=>20]);

        if (is_wp_error($resp)) return ['error' => $resp->get_error_message()];

        $code = wp_remote_retrieve_response_code($resp);
        $data = json_decode(wp_remote_retrieve_body($resp), true);

        if ($code !== 200) return ['error' => "HTTP $code", 'raw' => $data];

        set_transient($cache_key, $data, $this->transient_ttl);
        return $data;
    }

    public function get_job_by_id($id) {
        $cache_key = "wegestu_job_{$id}";
        $cached = get_transient($cache_key);
        if ($cached !== false) return $cached;

        // try dedicated endpoint
        $url = $this->base_url . "api/User/getJob?id=" . intval($id);
        $resp = wp_remote_get($url, ['headers' => $this->default_headers(), 'timeout'=>20]);
        if (is_wp_error($resp)) return ['error' => $resp->get_error_message()];

        $code = wp_remote_retrieve_response_code($resp);
        $data = json_decode(wp_remote_retrieve_body($resp), true);

        if ($code === 200 && !isset($data['error'])) {
            set_transient($cache_key, $data, $this->transient_ttl);
            return $data;
        }

        // fallback search
        $page = 1;
        while($page <= 10){
            $list = $this->get_jobs(50, $page);
            if(isset($list['data']) && is_array($list['data'])){
                foreach($list['data'] as $job){
                    if((isset($job['id']) && intval($job['id']) === $id) || (isset($job['ID_job']) && intval($job['ID_job']) === $id)){
                        set_transient($cache_key, $job, $this->transient_ttl);
                        return $job;
                    }
                }
            } else break;
            $page++;
        }

        return ['error'=>'Job not found'];
    }

    private function default_headers() {
        $h = [
            'Accept' => 'application/json',
            'User-Agent' => 'WegestuJobsWPPlugin/1.0',
        ];
        if(!empty($this->token)) $h['Authorization'] = 'Bearer '.$this->token;
        return $h;
    }
}
