<?php
/*
Plugin Name: No Profile.php
Version: 0.3.0.0
Description: Removes the profile page for all users other than those who can moderate comments.
Plugin URI: https://github.com/tismelyla/wp-no-profile.php
Author: tismelyla
*/

/**
 * Remove the Edit Profile link from the admin bar for non-moderators.
 */
function no_profile_add_custom_capability() {
    // Add 'view_profile' capability to roles that need it (e.g., 'editor', 'author', etc.)
    $roles = ['editor', 'author', 'contributor']; // Adjust this list based on which roles should have access

    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            $role->add_cap('view_profile', true);  // Add 'view_profile' capability to the role
        }
    }
}
register_activation_hook(__FILE__, 'no_profile_add_custom_capability');

function mytheme_admin_bar_render() {
    if ( !current_user_can( 'view_profile' ) ) { // Use view_profile to check for users who can moderate comments
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu( 'edit-profile', 'user-actions' );
    }
}
add_action( 'wp_before_admin_bar_render', 'mytheme_admin_bar_render' );

/**
 * Stop non-moderator users from accessing the profile page and remove the profile page from the admin menu.
 */
function stop_access_profile() {
    // Check if the user does not have the 'view_profile' capability
    if ( !current_user_can( 'view_profile' ) ) { 
        // Redirect non-moderator users from the profile page if they try to access it directly
        if ( is_admin() && 'profile.php' === basename( $_SERVER['PHP_SELF'] ) ) {
            wp_redirect( admin_url() ); // Redirect to the dashboard
            exit;
        }

        // Redirect non-moderator users from the profile page using the page query parameter
        if ( is_admin() && isset( $_GET['page'] ) && 'profile.php' === $_GET['page'] ) {
            wp_redirect( admin_url() ); // Redirect to the dashboard
            exit;
        }

        // Remove profile-related menu items from the admin sidebar
        remove_menu_page( 'profile.php' );
        remove_submenu_page( 'users.php', 'profile.php' );
    }
}
add_action( 'admin_init', 'stop_access_profile' );
