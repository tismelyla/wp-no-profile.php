<?php
/*
Plugin Name: No Profile.php
Version: 0.3.1.1
Description: Removes the profile page for through a custom capability.
Plugin URI: https://github.com/tismelyla/wp-no-profile.php
Author: tismelyla
*/

// Function to add 'tml_npp_view_profile' capability to selected roles
function no_profile_add_capabilities() {
    $roles = ['administrator', 'editor'];

    foreach ($roles as $role_name) {
        if ($role = get_role($role_name)) { // Check if role exists
            if (!$role->has_cap('tml_npp_view_profile')) {
                $role->add_cap('tml_npp_view_profile', true);
            }
        }
    }
}

// Function to remove 'tml_npp_view_profile' capability when plugin is deactivated
function no_profile_remove_capabilities() {
    $roles = ['administrator', 'editor'];

    foreach ($roles as $role_name) {
        if ($role = get_role($role_name)) { // Check if role exists
            $role->remove_cap('tml_npp_view_profile');
        }
    }
}

// Ensure role modifications happen properly
function no_profile_run_on_init() {
    no_profile_add_capabilities();
}
add_action('init', 'no_profile_run_on_init');

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'no_profile_add_capabilities');
register_deactivation_hook(__FILE__, 'no_profile_remove_capabilities');


// Disables rendering of the "edit profile" button in the admin bar
function mytheme_admin_bar_render() {
    if (is_admin_bar_showing()) {
        if ( !current_user_can( 'tml_npp_view_profile' ) ) {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu( 'edit-profile', 'user-actions' );
        }
    }
}
add_action( 'wp_before_admin_bar_render', 'mytheme_admin_bar_render' );

// Stops direct access
function stop_access_profile() {
    if (!current_user_can('tml_npp_view_profile') && is_admin() && 'profile.php' === basename($_SERVER['PHP_SELF'])) {
        wp_redirect(admin_url());
        exit;
    }
}
add_action('admin_init', 'stop_access_profile');

// Removes menu pages
function no_profile_remove_menu_items() {
    if (!current_user_can('tml_npp_view_profile')) {
        remove_menu_page('profile.php');
        remove_submenu_page('users.php', 'profile.php');
    }
}
add_action('admin_menu', 'no_profile_remove_menu_items', 999);