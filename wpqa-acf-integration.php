<?php
/*
Plugin Name: WPQA ACF Integration
Description: Integrates WPQA plugin with ACF to add custom fields to the question form.
Version: 1.4
Author: Thumula Basura Suraweera
Author URI: https://www.thumulabasura.com/wpqa-acf-integration
License: GPLv2
*/

// Check if ACF is installed and active
function wpqa_acf_check_and_create_group() {
    // Ensure ACF is active
    if (function_exists('acf_add_local_field_group')) {
        // Define the field group
        $field_group_key = 'group_custom_question_box_fields';
        
        // Check if the field group already exists
        if (!acf_get_field_group($field_group_key)) {
            // Add the field group
            acf_add_local_field_group(array(
                'key' => $field_group_key,
                'title' => 'Custom Question Box Fields',
                'fields' => array(
                    array(
                        'key' => 'field_start_time',
                        'label' => 'Start Time',
                        'name' => 'start_time',
                        'type' => 'datetime_picker',
                        'instructions' => 'Select the start time for the question.',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_expire_time',
                        'label' => 'Expire Time',
                        'name' => 'expire_time',
                        'type' => 'datetime_picker',
                        'instructions' => 'Select the expire time for the question.',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_offer_url',
                        'label' => 'Offer URL',
                        'name' => 'offer_url',
                        'type' => 'url',
                        'instructions' => 'Enter the offer URL for the question.',
                        'required' => 1,
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'question',
                        ),
                    ),
                ),
            ));
        }
    } else {
        add_action('admin_notices', 'wpqa_acf_missing_notice');
    }
}
add_action('init', 'wpqa_acf_check_and_create_group');

// Display an admin notice if ACF is not installed or activated
function wpqa_acf_missing_notice() {
    echo '<div class="notice notice-error"><p><strong>WPQA ACF Integration:</strong> Advanced Custom Fields (ACF) plugin is required for this integration to work. Please install and activate ACF.</p></div>';
}

// Hook into the WPQA form display to inject ACF fields dynamically
function wpqa_custom_inject_acf_fields($out, $question_sort_option, $question_sort, $sort_key, $sort_value, $type, $question_add, $question_edit, $get_question) {
    if ($sort_key == "title_question" && function_exists('acf_get_fields')) {
        $field_group_key = 'group_custom_question_box_fields';
        $fields = acf_get_fields($field_group_key);

        if ($fields) {
            foreach ($fields as $field) {
                $field_name = $field['name'];
                $field_value = ($type == "edit" && isset($question_edit[$field_name])) ? esc_attr($question_edit[$field_name]) : esc_attr(get_field($field_name, $get_question));
                
                $out .= '<div class="wpqa_custom_field">
                    <label for="'.esc_attr($field['name']).'">'.esc_html($field['label']).'<span class="required">*</span></label>';
                
                switch ($field['type']) {
                    case 'text':
                    case 'url':
                        $out .= '<input type="'.esc_attr($field['type']).'" name="'.esc_attr($field['name']).'" id="'.esc_attr($field['name']).'" class="form-control" value="'.esc_attr($field_value).'">';
                        break;
                    case 'datetime_picker':
                        $out .= '<input type="datetime-local" name="'.esc_attr($field['name']).'" id="'.esc_attr($field['name']).'" class="form-control" value="'.esc_attr($field_value).'">';
                        break;
                    default:
                        $out .= '<input type="text" name="'.esc_attr($field['name']).'" id="'.esc_attr($field['name']).'" class="form-control" value="'.esc_attr($field_value).'">';
                        break;
                }

                $out .= '<span class="form-description">'.esc_html($field['instructions']).'</span></div>';
            }
        }
    }

    return $out;
}
add_filter('wpqa_question_sort', 'wpqa_custom_inject_acf_fields', 10, 8);

// Save ACF fields when the question is saved
function wpqa_custom_save_acf_fields($post_id) {
    if (get_post_type($post_id) != 'question') {
        return;
    }

    $field_group_key = 'group_custom_question_box_fields';

    if (function_exists('acf_get_fields')) {
        $fields = acf_get_fields($field_group_key);

        if ($fields) {
            foreach ($fields as $field) {
                $field_name = $field['name'];

                if (isset($_POST[$field_name])) {
                    update_field($field_name, sanitize_text_field($_POST[$field_name]), $post_id);
                }
            }
        }
    }
}
add_action('save_post_question', 'wpqa_custom_save_acf_fields');
