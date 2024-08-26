<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wpqa_acf_integration_admin_page() {
    // Check if form is submitted for general settings
    if (isset($_POST['wpqa_acf_save_settings'])) {
        // Verify nonce for security
        if (check_admin_referer('wpqa_acf_settings_nonce')) {
            // Process form submission
            $enable_integration = isset($_POST['enable_integration']) ? 1 : 0;
            update_option('wpqa_acf_enable_integration', $enable_integration);

            // Show success message
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
        }
    }

    // Check if form is submitted for selected ACF field group
    if (isset($_POST['wpqa_acf_save_field_group'])) {
        // Verify nonce for security
        if (check_admin_referer('wpqa_acf_field_group_nonce')) {
            // Process form submission
            $selected_field_group = isset($_POST['acf_field_group']) ? sanitize_text_field($_POST['acf_field_group']) : '';
            update_option('wpqa_acf_selected_field_group', $selected_field_group);

            // Show success message
            echo '<div class="notice notice-success is-dismissible"><p>Field group selection saved successfully!</p></div>';
        }
    }

    // Get current settings
    $enable_integration = get_option('wpqa_acf_enable_integration', 0);
    $selected_field_group = get_option('wpqa_acf_selected_field_group', '');

    // Get available ACF field groups
    $acf_field_groups = function_exists('acf_get_field_groups') ? acf_get_field_groups() : [];
?>
    <div class="wrap wpqa-acf-integration-admin">
        <h1 class="wp-heading-inline">WPQA ACF Integration</h1>
        <hr class="wp-header-end">

        <div class="card-container">
            <div class="card card-half">
                <h2 class="title">Settings</h2>
                <div class="card-body">
                    <form method="post" action="">
                        <?php wp_nonce_field('wpqa_acf_settings_nonce'); ?>

                        <div class="form-group">
                            <label for="enable_integration" class="form-check-label">
                                <input type="checkbox" id="enable_integration" name="enable_integration" class="form-check-input" value="1" <?php checked(1, $enable_integration); ?>>
                                Enable WPQA ACF Integration
                            </label>
                            <p class="description">Check this box to enable the integration between WPQA and Advanced Custom Fields.</p>
                        </div>

                        <button type="submit" name="wpqa_acf_save_settings" class="button button-primary">Save Settings</button>
                    </form>
                </div>
            </div>

            <div class="card card-half">
                <h2 class="title">Help & Documentation</h2>
                <div class="card-body">
                    <p>Need help with WPQA ACF Integration? Check out our documentation or contact support.</p>
                    <a href="#" class="button button-secondary">View Documentation</a>
                    <a href="#" class="button button-secondary">Contact Support</a>
                </div>
            </div>
        </div>

        <?php if ($enable_integration): ?>
            <div class="card mt-4 full-width">
                <h2 class="title">Select ACF Field Group</h2>
                <div class="card-body">
                    <form method="post" action="">
                        <?php wp_nonce_field('wpqa_acf_field_group_nonce'); ?>

                        <div class="form-group">
                            <label for="acf_field_group">Select ACF Field Group:</label>
                            <select id="acf_field_group" name="acf_field_group" class="widefat">
                                <option value="">-- Select a Field Group --</option>
                                <?php foreach ($acf_field_groups as $field_group): ?>
                                    <option value="<?php echo esc_attr($field_group['key']); ?>" <?php selected($selected_field_group, $field_group['key']); ?>>
                                        <?php echo esc_html($field_group['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" name="wpqa_acf_save_field_group" class="button button-primary">Save Field Group</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .card-container {
            display: block; /* Stack the boxes vertically */
        }

        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            margin-bottom: 20px; /* Space between the rows */
        }

        .card.card-half {
            width: 100%; /* Full width for each card in the first row */
        }

        .card.full-width {
            width: 100%; /* Ensure full width for the table container */
        }

        .card .title {
            border-bottom: 1px solid #ccd0d4;
            margin: 0;
            padding: 8px 12px;
        }

        .card-body {
            padding: 12px;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .description {
            font-style: italic;
            color: #666;
        }

        .widefat {
            width: 100%; /* Ensuring the dropdown is full width */
        }
    </style>
<?php
}
?>
