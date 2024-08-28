<?php
// Unhook the original WPQA error handling functions
remove_action("wpqa_add_question_errors", "wpqa_add_edit_question_errors", 1);
remove_action("wpqa_edit_question_errors", "wpqa_add_edit_question_errors", 1);
remove_action("wpqa_add_user_question_errors", "wpqa_add_edit_question_errors", 1);
remove_action("wpqa_edit_user_question_errors", "wpqa_add_edit_question_errors", 1);

// Hook in your custom error handling function
add_action("wpqa_add_question_errors", "custom_wpqa_add_edit_question_errors", 1, 8);
add_action("wpqa_edit_question_errors", "custom_wpqa_add_edit_question_errors", 1, 8);
add_action("wpqa_add_user_question_errors", "custom_wpqa_add_edit_question_errors", 1, 8);
add_action("wpqa_edit_user_question_errors", "custom_wpqa_add_edit_question_errors", 1, 8);

function custom_wpqa_add_edit_question_errors($errors, $posted, $type, $question_sort, $get_question_user_id, $comment_question, $title_question) {
    $question_title_min_limit = wpqa_options("question_title_min_limit");
    $question_title_limit = wpqa_options("question_title_limit");
    $question_category_required = wpqa_options("question_category_required");

    // Safely check if 'title' key exists before accessing it
    $original_title = isset($posted['title']) ? trim($posted['title']) : '';
    $question_title = strip_tags($original_title);
    $question_title = str_replace(['<p>', '</p>', '<br>', '<br data-mce-bogus="1">'], '', $question_title);

    // Log for debugging
    error_log('Original Title: ' . $original_title);
    error_log('Processed Title: ' . $question_title);

    // Fallback check
    if (empty($question_title)) {
        if (isset($_POST['title']) && !empty($_POST['title'])) {
            $question_title = $_POST['title'];
            error_log('Fallback: Title found in $_POST directly.');
        } else {
            error_log('Fallback: Title still missing.');
        }
    }

    if ($title_question === "on") {
        if (empty($question_title)) {
            error_log('Title is empty after processing. Adding error.');
            $errors->add('required-field', '<strong>' . esc_html__("Error", "wpqa") . ':&nbsp;</strong> ' . esc_html__("There are required fields (title).", "wpqa"));
        }

        if ($question_title_min_limit > 0 && strlen($question_title) < $question_title_min_limit) {
            error_log('Title is below minimum limit. Adding error.');
            $errors->add('required-field', '<strong>' . esc_html__("Error", "wpqa") . ':&nbsp;</strong> ' . sprintf(esc_html__("Sorry, The minimum characters for question title is %s.", "wpqa"), $question_title_min_limit));
        }

        if ($question_title_limit > 0 && strlen($question_title) > $question_title_limit) {
            error_log('Title exceeds maximum limit. Adding error.');
            $errors->add('required-field', '<strong>' . esc_html__("Error", "wpqa") . ':&nbsp;</strong> ' . sprintf(esc_html__("Sorry, The maximum characters for question title is %s.", "wpqa"), $question_title_limit));
        }
    }

    // Category validation
    if ($type == "add" && $question_category_required == "on") {
        // Debugging category handling
        error_log('Posted Category: ' . print_r($posted['category'], true));

        if (
            (empty($posted['user_id']) || (isset($posted['user_id']) && $posted['user_id'] == "")) &&
            isset($question_sort["categories_question"]["value"]) && $question_sort["categories_question"]["value"] == "categories_question" &&
            (empty($posted['category']) || $posted['category'] == '-1' || (is_array($posted['category']) && count($posted['category']) < 1 && (end($posted['category']) == "" || end($posted['category']) == "-1")))
        ) {
            error_log('Category validation failed. Adding error.');
            $errors->add('required-field', '<strong>' . esc_html__("Error", "wpqa") . ':&nbsp;</strong> ' . esc_html__("There are required fields (category).", "wpqa"));
        }
    }

    return $errors;
}
