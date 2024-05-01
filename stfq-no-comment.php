<?php
/*
Plugin Name: STFQ No Comment
Description: This plugin disables comments across the entire site.
Version: 1.0
Author: Strangefrequency LLC
Author URI: https://strangefrequency.com
License: GPL v3
License URI: https://www.gnu.org/licenses/old-licenses/gpl-3.0.html
*/

// Add settings page
function stfq_disable_comments_settings_page() {
    add_options_page(
        'STFQ No Comment',
        'STFQ No Comment',
        'manage_options',
        'stfq-disable-comments-settings',
        'stfq_disable_comments_render_settings_page'
    );
}
add_action('admin_menu', 'stfq_disable_comments_settings_page');

// Render settings page
function stfq_disable_comments_render_settings_page() {
    ?>
    <div class="wrap">
        <h2>STFQ No Comment Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('stfq-disable-comments-settings-group'); ?>
            <?php do_settings_sections('stfq-disable-comments-settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings and fields
function stfq_disable_comments_register_settings() {
    register_setting('stfq-disable-comments-settings-group', 'stfq_disable_comments_disable_xmlrpc');
    register_setting('stfq-disable-comments-settings-group', 'stfq_disable_comments_disable_rest_api');
    add_settings_section(
        'stfq-disable-comments-settings-section',
        'Additional Comment Disabling Options',
        'stfq_disable_comments_section_callback',
        'stfq-disable-comments-settings'
    );
    add_settings_field(
        'stfq_disable_comments_disable_xmlrpc',
        'Disable Comments via XML-RPC',
        'stfq_disable_comments_disable_xmlrpc_callback',
        'stfq-disable-comments-settings',
        'stfq-disable-comments-settings-section'
    );
    add_settings_field(
        'stfq_disable_comments_disable_rest_api',
        'Disable Comments via REST API',
        'stfq_disable_comments_disable_rest_api_callback',
        'stfq-disable-comments-settings',
        'stfq-disable-comments-settings-section'
    );
}
add_action('admin_init', 'stfq_disable_comments_register_settings');

// Section callback
function stfq_disable_comments_section_callback() {
    echo '<p>Check the options below to disable comments via XML-RPC and/or REST API.</p>';
}

// XML-RPC callback
function stfq_disable_comments_disable_xmlrpc_callback() {
    $disable_xmlrpc = get_option('stfq_disable_comments_disable_xmlrpc');
    echo '<input type="checkbox" id="stfq_disable_comments_disable_xmlrpc" name="stfq_disable_comments_disable_xmlrpc" value="1" ' . checked(1, $disable_xmlrpc, false) . ' />';
}

// REST API callback
function stfq_disable_comments_disable_rest_api_callback() {
    $disable_rest_api = get_option('stfq_disable_comments_disable_rest_api');
    echo '<input type="checkbox" id="stfq_disable_comments_disable_rest_api" name="stfq_disable_comments_disable_rest_api" value="1" ' . checked(1, $disable_rest_api, false) . ' />';
}

// Disable comments via XML-RPC
function stfq_disable_comments_disable_xmlrpc() {
    $disable_xmlrpc = get_option('stfq_disable_comments_disable_xmlrpc');
    if ($disable_xmlrpc) {
        add_filter('xmlrpc_enabled', '__return_false');
    }
}
add_action('init', 'stfq_disable_comments_disable_xmlrpc');

// Disable comments via REST API
function stfq_disable_comments_disable_rest_api() {
    $disable_rest_api = get_option('stfq_disable_comments_disable_rest_api');
    if ($disable_rest_api) {
        add_filter('rest_allow_anonymous_comments', '__return_false');
        add_filter('rest_allow_comments_collection', '__return_false');
    }
}
add_action('init', 'stfq_disable_comments_disable_rest_api');

// Disable support for comments and trackbacks in post types
function stfq_disable_comments_post_types_support() {
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}
add_action('admin_init', 'stfq_disable_comments_post_types_support');

// Close comments on the front-end
function stfq_disable_comments_status() {
    return false;
}
add_filter('comments_open', 'stfq_disable_comments_status', 20, 2);
add_filter('pings_open', 'stfq_disable_comments_status', 20, 2);

// Hide existing comments
function stfq_disable_comments_hide_existing_comments($comments) {
    $comments = array();
    return $comments;
}
add_filter('comments_array', 'stfq_disable_comments_hide_existing_comments', 10, 2);

// Remove comments page in admin menu
function stfq_disable_comments_admin_menu() {
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'stfq_disable_comments_admin_menu');

// Redirect any user trying to access comments page
function stfq_disable_comments_admin_menu_redirect() {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }
}
add_action('admin_init', 'stfq_disable_comments_admin_menu_redirect');

// Remove comments metabox from dashboard
function stfq_disable_comments_dashboard() {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}
add_action('admin_init', 'stfq_disable_comments_dashboard');

// Remove comments links from admin bar
function stfq_disable_comments_admin_bar() {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
}
add_action('init', 'stfq_disable_comments_admin_bar');
