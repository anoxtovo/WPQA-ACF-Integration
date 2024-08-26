<?php
/*
Plugin Name: WPQA ACF Integration
Description: Integrates WPQA plugin with ACF to add custom fields to the question form.
Version: 1.4.6
Author: Thumula Basura Suraweera
Author URI: https://www.thumulabasura.com/wpqa-acf-integration
License: GPLv2
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPQA_ACF_INTEGRATION_VERSION', '1.4.6');
define('WPQA_ACF_INTEGRATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPQA_ACF_INTEGRATION_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once WPQA_ACF_INTEGRATION_PLUGIN_DIR . 'includes/admin-page.php';

// Hook for plugin activation
register_activation_hook(__FILE__, 'wpqa_acf_integration_activate');

function wpqa_acf_integration_activate() {
    // Set default options on activation
    add_option('wpqa_acf_enable_integration', 0);
}

// Hook for adding admin menu
add_action('admin_menu', 'wpqa_acf_integration_add_admin_menu');

function wpqa_acf_integration_add_admin_menu() {
    add_menu_page(
        'WPQA ACF Integration',
        'WPQA ACF',
        'manage_options',
        'wpqa-acf-integration',
        'wpqa_acf_integration_admin_page',
        'dashicons-format-aside',
        30
    );
}

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'wpqa_acf_integration_enqueue_admin_scripts');

function wpqa_acf_integration_enqueue_admin_scripts($hook) {
    // Load styles and scripts only on your plugin's pages
    if ('toplevel_page_wpqa-acf-integration' !== $hook && 'wpqa-acf-integration_page_wpqa-acf-dynamic-fields' !== $hook) {
        return;
    }

    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap', WPQA_ACF_INTEGRATION_PLUGIN_URL . 'lib/css/bootstrap.min.css', array(), '5.3.0');

    // Enqueue Bootstrap JS
    wp_enqueue_script('bootstrap', WPQA_ACF_INTEGRATION_PLUGIN_URL . 'lib/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);

    // Enqueue custom CSS
    wp_enqueue_style('wpqa_acf_integration_admin', WPQA_ACF_INTEGRATION_PLUGIN_URL . 'lib/css/admin-style.css');
}

// Hook for integrating ACF fields with WPQA
add_action('wpqa_add_question_fields', 'wpqa_acf_integration_add_fields');

function wpqa_acf_integration_add_fields() {
    // Check if integration is enabled
    if (get_option('wpqa_acf_enable_integration', 0)) {
        // Retrieve the selected ACF field group key
        $selected_field_group = get_option('wpqa_acf_selected_field_group', '');

        // Check if ACF is active and a field group is selected
        if (function_exists('acf_form') && !empty($selected_field_group)) {
            acf_form(array(
                'post_id'       => 'new_post', // Use 'new_post' for new submissions or pass the post ID for edits
                'field_groups'  => array($selected_field_group), // Use the selected ACF field group
                'form'          => false, // Disable the form wrapper
                'return'        => false, // Prevent redirect after submission
            ));
        }
    }
}

// Inject ACF fields after the question title in WPQA form
add_filter('wpqa_add_edit_question_after_title', 'wpqa_acf_integration_inject_fields', 10, 5);

function wpqa_acf_integration_inject_fields($out, $type, $question_add, $question_edit, $get_question) {
    ob_start();
    wpqa_acf_integration_add_fields(); // Call the function that outputs the ACF fields
    $acf_fields_output = ob_get_clean();

    return $out . $acf_fields_output; // Append ACF fields to the existing form output
}

// Hook for saving ACF fields with WPQA question
add_action('wpqa_add_question', 'wpqa_acf_integration_save_fields', 10, 2);

function wpqa_acf_integration_save_fields($question_id, $values) {
    // Check if integration is enabled
    if (get_option('wpqa_acf_enable_integration', 0)) {
        // Save the custom ACF fields
        if (function_exists('acf_save_post')) {
            acf_save_post($question_id);
        }
    }
}

// Hook for displaying ACF fields with WPQA question
add_action('wpqa_display_question_fields', 'wpqa_acf_integration_display_fields', 10, 1);

function wpqa_acf_integration_display_fields($question_id) {
    if (get_option('wpqa_acf_enable_integration', 0)) {
        $offer_url = get_field('offer_url', $question_id);
        $offer_start_date = get_field('offer_start_date', $question_id);
        $offer_end_date = get_field('offer_end_date', $question_id);

        if ($offer_url || $offer_start_date || $offer_end_date) {
            echo '<div class="acf-fields">';
            if ($offer_url) {
                echo '<p><strong>Offer URL:</strong> <a href="' . esc_url($offer_url) . '">' . esc_html($offer_url) . '</a></p>';
            }
            if ($offer_start_date) {
                echo '<p><strong>Offer Start Date:</strong> ' . esc_html($offer_start_date) . '</p>';
            }
            if ($offer_end_date) {
                echo '<p><strong>Offer End Date:</strong> ' . esc_html($offer_end_date) . '</p>';
            }
            echo '</div>';
        }
    }
}

// Dynamic Fields Management
add_action('admin_menu', 'wpqa_acf_integration_dynamic_fields_menu');
function wpqa_acf_integration_dynamic_fields_menu() {
    add_submenu_page(
        'wpqa-acf-integration',
        'Custom Fields',
        'Custom Fields',
        'manage_options',
        'wpqa-acf-dynamic-fields',
        'wpqa_acf_integration_dynamic_fields_page'
    );
}
?>
