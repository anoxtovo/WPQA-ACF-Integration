<?php
/**
 * Plugin Name: Custom Question Fields
 * Description: Adds custom fields (start_time, expire_time, offer_url) to questions.
 * Version: 1.0
 * Author: Your Name
 */

// Hook to add custom fields in the form
function add_custom_question_fields($post_id) {
    // Ensure post type is 'question'
    if (get_post_type($post_id) !== 'question') {
        return;
    }

    $start_time = get_post_meta($post_id, 'start_time', true);
    $expire_time = get_post_meta($post_id, 'expire_time', true);
    $offer_url = get_post_meta($post_id, 'offer_url', true);

    echo '<div class="wpqa_start_time">
        <label for="start_time">'.esc_html__("Start Time","wpqa").'</label>
        <input type="datetime-local" name="start_time" id="start_time" value="'.esc_attr($start_time).'">
    </div>';

    echo '<div class="wpqa_expire_time">
        <label for="expire_time">'.esc_html__("Expire Time","wpqa").'</label>
        <input type="datetime-local" name="expire_time" id="expire_time" value="'.esc_attr($expire_time).'">
    </div>';

    echo '<div class="wpqa_offer_url">
        <label for="offer_url">'.esc_html__("Offer URL","wpqa").'</label>
        <input type="url" name="offer_url" id="offer_url" value="'.esc_attr($offer_url).'">
    </div>';

    // Include nonce for security
    wp_nonce_field('save_custom_question_fields', 'custom_question_nonce');
}
add_action('add_meta_boxes', 'add_custom_question_fields');

// Hook to save custom fields
function save_custom_question_fields($post_id) {
    // Verify nonce
    if (!isset($_POST['custom_question_nonce']) || !wp_verify_nonce($_POST['custom_question_nonce'], 'save_custom_question_fields')) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save start_time
    if (isset($_POST['start_time'])) {
        update_post_meta($post_id, 'start_time', sanitize_text_field($_POST['start_time']));
    }

    // Save expire_time
    if (isset($_POST['expire_time'])) {
        update_post_meta($post_id, 'expire_time', sanitize_text_field($_POST['expire_time']));
    }

    // Save offer_url
    if (isset($_POST['offer_url'])) {
        update_post_meta($post_id, 'offer_url', esc_url_raw($_POST['offer_url']));
    }
}
add_action('save_post', 'save_custom_question_fields');

?>