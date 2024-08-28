<?php
/*
Plugin Name: WPQA ACF Integration
Description: Integrates WPQA plugin with ACF to add custom fields to the question form.
Version: 1.4.6
Author: Thumula Basura Suraweera
Author URI: https://www.thumulabasura.com/wpqa-acf-integration
License: GPLv2
*/

if (!defined('ABSPATH')) {
    exit;
}

define('WPQA_ACF_INTEGRATION_VERSION', '1.4.6');
define('WPQA_ACF_INTEGRATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPQA_ACF_INTEGRATION_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WPQA_ACF_INTEGRATION_PLUGIN_DIR . 'includes/admin-page.php';
require_once WPQA_ACF_INTEGRATION_PLUGIN_DIR . 'includes/error-handling.php';

/**
 * Activates the plugin and sets default options.
 */
function wpqa_acf_integration_activate() {
    add_option('wpqa_acf_enable_integration', 0);
}
register_activation_hook(__FILE__, 'wpqa_acf_integration_activate');

/**
 * Adds the plugin menu to the WordPress admin.
 */
function wpqa_acf_integration_add_admin_menu() {
    add_menu_page(
        __('WPQA ACF Integration', 'wpqa-acf-integration'),
        __('WPQA ACF', 'wpqa-acf-integration'),
        'manage_options',
        'wpqa-acf-integration',
        'wpqa_acf_integration_admin_page',
        'dashicons-format-aside',
        30
    );
}
add_action('admin_menu', 'wpqa_acf_integration_add_admin_menu');

/**
 * Enqueues admin scripts and styles.
 *
 * @param string $hook The current admin page.
 */
function wpqa_acf_integration_enqueue_admin_scripts($hook) {
    if ('toplevel_page_wpqa-acf-integration' !== $hook) {
        return;
    }

    wp_enqueue_style('bootstrap', WPQA_ACF_INTEGRATION_PLUGIN_URL . 'lib/css/bootstrap.min.css', array(), '5.3.0');
    wp_enqueue_script('bootstrap', WPQA_ACF_INTEGRATION_PLUGIN_URL . 'lib/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
    wp_enqueue_style('wpqa_acf_integration_admin', WPQA_ACF_INTEGRATION_PLUGIN_URL . 'lib/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'wpqa_acf_integration_enqueue_admin_scripts');

/**
 * Injects ACF fields into the WPQA question form.
 *
 * @param string $out The existing output.
 * @param string $type The type of form (add/edit).
 * @param bool $question_add Whether this is an add question form.
 * @param bool $question_edit Whether this is an edit question form.
 * @param int|null $get_question The question ID if editing.
 * @return string The modified output with ACF fields injected.
 */
function wpqa_acf_integration_inject_fields($out, $type, $question_add, $question_edit, $get_question) {
    if (get_option('wpqa_acf_enable_integration', 0)) {
        $selected_field_group = get_option('wpqa_acf_selected_field_group', '');
        if (function_exists('acf_form') && !empty($selected_field_group)) {
            ob_start();
            acf_form(array(
                'post_id'       => 'new_post',
                'field_groups'  => array($selected_field_group),
                'form'          => false,
                'return'        => false,
            ));
            $acf_fields_output = ob_get_clean();
            return $out . $acf_fields_output;
        }
    }
    return $out;
}
add_filter('wpqa_add_edit_question_after_title', 'wpqa_acf_integration_inject_fields', 10, 5);

/**
 * Saves ACF fields when a WPQA question is added.
 *
 * @param int $question_id The ID of the question being saved.
 * @param array|null $values The values being saved (if any).
 */
function wpqa_acf_integration_save_fields($question_id, $values = null) {
    if (!is_numeric($question_id) || $question_id <= 0 || !get_option('wpqa_acf_enable_integration', 0)) {
        return;
    }

    if (!isset($_POST['acf_nonce']) || !wp_verify_nonce($_POST['acf_nonce'], 'acf_nonce')) {
        error_log(__('ACF nonce verification failed in WPQA ACF Integration', 'wpqa-acf-integration'));
        return;
    }

    if (function_exists('acf_save_post')) {
        acf_save_post($question_id);
    }
}
add_action('wpqa_add_question', 'wpqa_acf_integration_save_fields', 10, 2);

/**
 * Displays ACF fields for a given question.
 *
 * @param int $question_id The ID of the question to display fields for.
 */
function wpqa_acf_integration_display_fields($question_id) {
    if (get_option('wpqa_acf_enable_integration', 0) && function_exists('get_fields')) {
        $fields = get_fields($question_id);
        if ($fields) {
            echo '<div class="acf-fields">';
            foreach ($fields as $key => $value) {
                echo '<p><strong>' . esc_html($key) . ':</strong> ' . wp_kses_post($value) . '</p>';
            }
            echo '</div>';
        }
    }
}
add_action('wpqa_display_question_fields', 'wpqa_acf_integration_display_fields', 10, 1);

/**
 * Adds custom ACF fields to WPQA fields array.
 *
 * @param array $fields Existing fields array.
 * @param string $context The context in which fields are being added.
 * @return array Modified fields array.
 */
function custom_wpqa_add_acf_fields($fields, $context) {
    if (!is_array($fields)) {
        $fields = array();
    }

    $acf_fields = array('linkurl', 'start_offer', 'expiry_offer');
    $fields = array_merge($fields, $acf_fields);

    return $fields;
}
add_filter('wpqa_add_user_question_fields', 'custom_wpqa_add_acf_fields', 10, 2);
add_filter('wpqa_add_question_fields', 'custom_wpqa_add_acf_fields', 10, 2);

/**
 * Handles the title field for WPQA questions.
 *
 * @param array $posted Posted data.
 * @return array Modified posted data.
 */
function wpqa_acf_integration_handle_title($posted) {
    $title_from_post = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $title_from_posted = isset($posted['title']) ? sanitize_text_field($posted['title']) : '';

    if (empty($title_from_posted) && !empty($title_from_post)) {
        $posted['title'] = $title_from_post;
        error_log('WPQA ACF Integration: Fallback title used from $_POST.');
    }

    return $posted;
}
add_filter('wpqa_before_process_question', 'wpqa_acf_integration_handle_title');