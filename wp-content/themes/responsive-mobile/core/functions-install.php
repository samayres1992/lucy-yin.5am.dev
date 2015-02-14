<?php
/**
 * Functions Install
 *
 * Functions for installation & activation
 *
 * @package        Responsive
 * @license        license.txt
 * @copyright      2014 CyberChimps
 * @since          1.9.5.0
 *
 * Please do not edit this file. This file is part of the Responsive and all modifications
 * should be made in a child theme.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
 * Customize theme activation message.
 *
 * @since    1.9.5.0
 */
function responsive_mobile_activation_notice() {
	if ( isset( $_GET['activated'] ) ) {
		$return = '<div class="updated activation"><p><strong>';
					$my_theme = wp_get_theme();
		if ( isset( $_GET['previewed'] ) ) {
			$return .= sprintf( __( 'Settings saved and %s activated successfully.' ), $my_theme->get( 'Name' ) );
		} else {
			$return .= sprintf( __( '%s activated successfully.' ), $my_theme->get( 'Name' ) );
		}
		$return .= '</strong> <a href="' . home_url( '/' ) . '">' . __( 'Visit site', 'responsive-mobile' ) . '</a></p>';
		//$return .= '<p><a class="button button-primary customize load-customize" href="' . admin_url( 'customize.php?theme=' . get_stylesheet() ) . '">' . __( 'Customize', 'responsive-mobile' ) . '</a>';
		$return .= ' <a class="button button-primary theme-options" href="' . admin_url( 'themes.php?page=theme_options' ) . '">' . __( 'Theme Options', 'responsive-mobile' ) . '</a>';
		$return .= ' <a class="button button-primary help" href="https://cyberchimps.com/forum/free/responsive/">' . __( 'Help', 'responsive-mobile' ) . '</a>';
		$return .= '</p></div>';
		echo $return;
	}
}
add_action( 'admin_notices', 'responsive_mobile_activation_notice' );

/*
 * Hide core theme activation message.
 *
 * @since    1.9.5.0
 */
function responsive_mobile_admin_css() { ?>
	<style>
	.themes-php #message2 {
		display: none;
	}
	.themes-php div.activation a {
		text-decoration: none;
	}
	</style>
<?php }
add_action( 'admin_head', 'responsive_mobile_admin_css' );

/**
 * Add plugin automation file
 */
if ( ! class_exists( 'Theme_Plugin_Dependency' ) ) {
	require_once( dirname( __FILE__ ) . '/class-theme-plugin-dependency.php' );
}

/**
 * Ignore admin notice
 *
 * @since     2.0.0
 */
function responsive_mobile_ignore_notice() {
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset( $_GET[ 'responsive_mobile_ignore_notice'] ) && 'true' == $_GET[ 'responsive_mobile_ignore_notice'] ) {
		update_user_meta( $user_id, 'responsive_mobile_ignore_notice', 'true' );
	}
	if ( isset( $_GET[ 'responsive_mobile_ignore_notice'] ) && 'false' == $_GET[ 'responsive_mobile_ignore_notice'] ) {
		delete_user_meta( $user_id, 'responsive_mobile_ignore_notice' );
	}
}
add_action( 'admin_init', 'responsive_mobile_ignore_notice' );

/*
 * Add notification to Reading Settings page to notify if Custom Front Page is enabled.
 *
 * @since    1.9.4.0
 */
function responsive_mobile_plugin_notice() {
	global $pagenow;
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	// Check that the user hasn't already clicked to ignore the message
	// Add plugin notification only if the current user ican install plugins and on theme.php
	if ( ! get_user_meta( $user_id, 'responsive_mobile_ignore_notice' ) && current_user_can( 'install_plugins' ) && 'themes.php' == $pagenow ) {

		// Set array of plugins to be suggested.
		$plugins = array(
			array(
				'name' => 'Clef', // Name of the plugin.
				'slug' => 'wpclef', // The plugin slug (typically the folder name)
				'uri'  => 'http://wordpress.org/extend/plugins/wpclef' // plugin url ( http://wordpress.org/plugins/plugin_slug )
			)
		);

		// Initialise plugin suggestion text.
		$msg = '';
		$msg .= '<div class="updated"><p>' . __( 'This theme recommends the following plugins:', 'responsive-mobile' ) . '</br><strong>';

		// Loop through each plugin.
		foreach( $plugins as $plugin ) {

			// Get plugin object by sending plugin slug and uri.
			$plugin_object = new Theme_Plugin_Dependency( $plugin['slug'], $plugin['uri'] );

			// Display plugin name as the suggestion with link to the plugin page in wordpress.org
			$msg .= ' <a target="_blank" href="' . esc_url( $plugin['uri'] ) . '">' . $plugin['name'] . '</a>';

			// Check if the plugin is allready installed then show link to Activate it.
			if ( $plugin_object->check() ) {
				$msg .= ' | <a href="' . $plugin_object->activate_link() . '">' . __( 'Activate', 'responsive-mobile' ) . '</a>,</br>';
			}

			// Otherwise if it is not installed, but the install link is availble then show link to install it.
			elseif ( $install_link = $plugin_object->install_link() ) {
				$msg .= ' | <a href="' . $install_link . '">' .  __( 'Install', 'responsive-mobile' ) . '</a>,</br>';
			}

			// If the install link is not availble then display message to install manually.
			else {
				$msg .= sprintf( __( '%s is not installed. Please install this plugin manually.', 'responsive-mobile' ), $plugin['name'] ) . '</br>';
			}
		} // End of the plugin loop.

		// Show link to Hide the Notice.
		$msg .= '</strong><a href="?responsive_mobile_ignore_notice=true">' . __( 'Hide Notice', 'responsive-mobile' ) . '</a></p></div>';

		echo $msg;
	}

}
add_action( 'admin_notices', 'responsive_mobile_plugin_notice' );
