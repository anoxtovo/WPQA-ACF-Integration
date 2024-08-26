<?php
/*
Plugin Name: WPQA ACF Integration
Description: Integrates WPQA plugin with ACF to add custom fields to the question form.
Version: 1.0
Author: Thumula Basura Suraweera
Author URI: https://www.thumulabasura.com/wpqa-acf-integration
License: GPLv2
*/

// Hook into the WPQA form display to inject ACF fields
function wpqa_acf_inject_custom_fields($out, $type, $question_add, $question_edit, $get_question) {
    if (function_exists('get_field')) {
        $offer_url = get_field('offer_url', $get_question);
        $start_time = get_field('start_time', $get_question);
        $expire_time = get_field('expire_time', $get_question);

        // Display Offer URL field
        $out .= '<div class="wpqa_offer_url">
            <label for="acf_offer_url">'.esc_html__("Offer URL", "wpqa").'<span class="required">*</span></label>
            <input type="url" name="acf_offer_url" id="acf_offer_url" class="form-control" value="'.esc_attr($offer_url).'">
            <span class="form-description">'.esc_html__("Please enter an offer URL for the question.", "wpqa").'</span>
        </div>';

        // Display Start Time field
        $out .= '<div class="wpqa_start_time">
            <label for="acf_start_time">'.esc_html__("Start Time", "wpqa").'<span class="required">*</span></label>
            <input type="datetime-local" name="acf_start_time" id="acf_start_time" class="form-control" value="'.esc_attr($start_time).'">
            <span class="form-description">'.esc_html__("Please select a start time for the question.", "wpqa").'</span>
        </div>';

        // Display Expire Time field
        $out .= '<div class="wpqa_expire_time">
            <label for="acf_expire_time">'.esc_html__("Expire Time", "wpqa").'<span class="required">*</span></label>
            <input type="datetime-local" name="acf_expire_time" id="acf_expire_time" class="form-control" value="'.esc_attr($expire_time).'">
            <span class="form-description">'.esc_html__("Please select an expire time for the question.", "wpqa").'</span>
        </div>';
    }

    return $out;
}
add_filter('wpqa_question_sort', 'wpqa_acf_inject_custom_fields', 10, 5);

// Save ACF fields when the question is saved
function wpqa_acf_save_custom_fields($post_id) {
    if (!isset($_POST['acf_offer_url'], $_POST['acf_start_time'], $_POST['acf_expire_time'])) {
        return;
    }

    // Save the Offer URL
    update_field('offer_url', esc_url_raw($_POST['acf_offer_url']), $post_id);

    // Save the Start Time
    update_field('start_time', sanitize_text_field($_POST['acf_start_time']), $post_id);

    // Save the Expire Time
    update_field('expire_time', sanitize_text_field($_POST['acf_expire_time']), $post_id);
}
add_action('save_post_question', 'wpqa_acf_save_custom_fields');
