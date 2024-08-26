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
    if ('toplevel_page_wpqa-acf-integration' !== $hook) {
        return;
    }

    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap', WPQA_ACF_INTEGRATION_PLUGIN_URL . 'lib/css/bootstrap.min.css', array(), '5.3.0');

    // Enqueue Bootstrap JS
    wp_enqueue_script('bootstrap', WPQA_ACF_INTEGRATION_PLUGIN_URL . 'lib/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
}

// Hook for integrating ACF fields with WPQA
add_action('wpqa_add_question_fields', 'wpqa_acf_integration_add_fields');

function wpqa_acf_integration_add_fields() {
    // Check if integration is enabled
    if (get_option('wpqa_acf_enable_integration', 0)) {
        // Add ACF fields to WPQA question form
        // This function will be implemented later
    }
}

// Hook for saving ACF fields with WPQA question
add_action('wpqa_add_question', 'wpqa_acf_integration_save_fields', 10, 2);

function wpqa_acf_integration_save_fields($question_id, $values) {
    // Check if integration is enabled
    if (get_option('wpqa_acf_enable_integration', 0)) {
        // Save ACF fields data
        // This function will be implemented later
    }
}