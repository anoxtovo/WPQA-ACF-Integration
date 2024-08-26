<?php
/*
Plugin Name: WPQA ACF Integration
Description: Integrates WPQA plugin with ACF to add custom fields to the question form.
Version: 1.4.1
Author: Thumula Basura Suraweera
Author URI: https://www.thumulabasura.com/wpqa-acf-integration
License: GPLv2
*/

// Hook into the WPQA form display to inject ACF fields dynamically
function wpqa_custom_inject_acf_fields($out, $question_sort_option, $question_sort, $sort_key, $sort_value, $type, $question_add, $question_edit, $get_question) {
    // Specify the ACF field group key
    $field_group_key = 'group_1234567890abcdef'; // Replace with your actual field group key

    if ($sort_key == "title_question") {
        if (function_exists('acf_get_fields')) {
            $fields = acf_get_fields($field_group_key);

            if ($fields) {
                foreach ($fields as $field) {
                    $field_name = $field['name'];
                    $field_value = ($type == "edit" && isset($question_edit[$field_name])) ? esc_attr($question_edit[$field_name]) : esc_attr(get_field($field_name, $get_question));
                    
                    $out .= '<div class="wpqa_custom_field">
                        <label for="'.esc_attr($field['name']).'">'.esc_html($field['label']).'<span class="required">*</span></label>';
                    
                    // Handle different field types (e.g., text, date, url)
                    switch ($field['type']) {
                        case 'text':
                        case 'url':
                            $out .= '<input type="'.esc_attr($field['type']).'" name="'.esc_attr($field['name']).'" id="'.esc_attr($field['name']).'" class="form-control" value="'.esc_attr($field_value).'">';
                            break;
                        case 'datetime_picker':
                            $out .= '<input type="datetime-local" name="'.esc_attr($field['name']).'" id="'.esc_attr($field['name']).'" class="form-control" value="'.esc_attr($field_value).'">';
                            break;
                        // Add more cases as needed for other field types
                        default:
                            $out .= '<input type="text" name="'.esc_attr($field['name']).'" id="'.esc_attr($field['name']).'" class="form-control" value="'.esc_attr($field_value).'">';
                            break;
                    }

                    $out .= '<span class="form-description">'.esc_html($field['instructions']).'</span></div>';
                }
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

    // Specify the ACF field group key
    $field_group_key = 'group_1234567890abcdef'; // Replace with your actual field group key

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
