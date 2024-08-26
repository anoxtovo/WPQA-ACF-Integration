<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wpqa_acf_integration_admin_page() {
    // Check if form is submitted
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

    // Get current settings
    $enable_integration = get_option('wpqa_acf_enable_integration', 0);
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">WPQA ACF Integration</h1>
        <hr class="wp-header-end">
        
        <div class="card">
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
        
        <?php if ($enable_integration): ?>
        <div class="card mt-4">
            <h2 class="title">ACF Fields Configuration</h2>
            <div class="card-body">
                <?php
                if (function_exists('acf_add_local_field_group')) {
                    // Display ACF field configuration options
                    echo '<p>ACF is active. You can now configure your custom fields for WPQA integration.</p>';
                    // TODO: Add UI for managing ACF fields
                } else {
                    echo '<div class="notice notice-warning"><p>Advanced Custom Fields plugin is not active. Please install and activate it to use this feature.</p></div>';
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <h2 class="title">Help & Documentation</h2>
            <div class="card-body">
                <p>Need help with WPQA ACF Integration? Check out our documentation or contact support.</p>
                <a href="#" class="button button-secondary">View Documentation</a>
                <a href="#" class="button button-secondary">Contact Support</a>
            </div>
        </div>
    </div>
    
    <style>
    .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        margin-top: 20px;
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
    </style>
    <?php
}