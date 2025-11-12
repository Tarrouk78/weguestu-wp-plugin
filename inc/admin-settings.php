<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'wegestu_jobs_add_menu' );
add_action( 'admin_init', 'wegestu_jobs_register_settings' );

/**
 * Enregistre les options
 */
function wegestu_jobs_register_settings() {
    register_setting( 'wegestu_jobs_options', 'wegestu_jobs_base_url', array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ) );
    register_setting( 'wegestu_jobs_options', 'wegestu_jobs_token', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'wegestu_jobs_options', 'wegestu_jobs_per_page_default', array( 'type' => 'integer', 'sanitize_callback' => 'intval' ) );
}

/**
 * Ajoute la page de menu
 */
function wegestu_jobs_add_menu() {
    add_options_page(
        'Wegestu Jobs API',
        'Wegestu Jobs',
        'manage_options',
        'wegestu-jobs',
        'wegestu_jobs_render_admin_page'
    );
}

/**
 * Rend la page d'admin
 */
function wegestu_jobs_render_admin_page() {
    ?>
    <div class="wrap">
        <h1>⚙️ Paramètres Wegestu Jobs</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'wegestu_jobs_options' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="wegestu_jobs_base_url">Base API URL</label></th>
                    <td><input type="text" name="wegestu_jobs_base_url" id="wegestu_jobs_base_url" value="<?php echo esc_attr( get_option('wegestu_jobs_base_url', 'https://api.test.wegestu.com/') ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="wegestu_jobs_token">Token Bearer</label></th>
                    <td>
                        <input type="password" name="wegestu_jobs_token" id="wegestu_jobs_token" value="<?php echo esc_attr( get_option('wegestu_jobs_token', '') ); ?>" class="regular-text" autocomplete="off">
                        <p class="description">Copiez ici votre token d’authentification API Wegestu.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="wegestu_jobs_per_page_default">Per page par défaut</label></th>
                    <td><input type="number" name="wegestu_jobs_per_page_default" id="wegestu_jobs_per_page_default" value="<?php echo intval( get_option('wegestu_jobs_per_page_default', 5) ); ?>" class="small-text"></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <h2>Tester la connexion API</h2>
        <button id="wegestu_test_api" class="button button-secondary">Tester la connexion</button>
        <span id="wegestu_test_result" style="margin-left:15px;"></span>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($){
        $('#wegestu_test_api').on('click', function(e){
            e.preventDefault();
            $('#wegestu_test_result').text('Test en cours...');
            $.post(ajaxurl, {
                action: 'wegestu_test_api',
                base_url: $('#wegestu_jobs_base_url').val(),
                token: $('#wegestu_jobs_token').val(),
                _wpnonce: '<?php echo wp_create_nonce("wegestu_test_api_nonce"); ?>'
            }, function(response){
                if(response.success){
                    $('#wegestu_test_result').text('✅ Connexion OK !');
                } else {
                    $('#wegestu_test_result').text('❌ Erreur : ' + response.data);
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * AJAX pour tester la connexion
 */
add_action('wp_ajax_wegestu_test_api', 'wegestu_test_api_callback');

function wegestu_test_api_callback() {
    check_ajax_referer('wegestu_test_api_nonce', '_wpnonce');

    $base_url = isset($_POST['base_url']) ? esc_url_raw($_POST['base_url']) : '';
    $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';

    if(empty($base_url) || empty($token)){
        wp_send_json_error('URL ou token manquant.');
    }

    $response = wp_remote_get( trailingslashit($base_url) . 'api/User/getListJob?per_page=1', [
        'headers' => ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'],
        'timeout' => 10
    ]);

    if(is_wp_error($response)){
        wp_send_json_error($response->get_error_message());
    }

    $code = wp_remote_retrieve_response_code($response);
    if($code === 200){
        wp_send_json_success();
    } else {
        wp_send_json_error('Code HTTP ' . $code);
    }
}
