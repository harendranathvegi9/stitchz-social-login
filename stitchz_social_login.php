<?php
/**
* Plugin Name: Stitchz Social Login
* Plugin URI: http://www.stitchz.net/Wordpress
* Description: Stitchz Social Login adds the option to authenticate users with one or more of the 20+ social identities providers supported by Stitchz with a simple interface that maintains all your social identity provider information safely and securely. Using Stitchz saves you from having to integrate and manage each identity provider individually.
* Version: 1.0
* Author: Ethan Peterson
* Author URI: https://plus.google.com/+EthanPeterson
* License: GPLv3
*/

define( 'STITCHZ_SOCIAL_LOGIN_VERSION', '1.0' );
define( 'STITCHZ_SOCIAL_LOGIN__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'STITCHZ_SOCIAL_LOGIN__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( STITCHZ_SOCIAL_LOGIN__PLUGIN_DIR . '/tools/logger.php' );
require_once( STITCHZ_SOCIAL_LOGIN__PLUGIN_DIR . '/tools/http-utilities.php' );
require_once( STITCHZ_SOCIAL_LOGIN__PLUGIN_DIR . '/tools/options.php' );
require_once( STITCHZ_SOCIAL_LOGIN__PLUGIN_DIR . '/tools/auth.php' );
require_once( STITCHZ_SOCIAL_LOGIN__PLUGIN_DIR . '/views/admin.php' );
require_once( STITCHZ_SOCIAL_LOGIN__PLUGIN_DIR . '/views/ui.php' );
require_once( STITCHZ_SOCIAL_LOGIN__PLUGIN_DIR . '/views/user.php' );

/**
 * Flush any rewrite rules upon activation.
 *
 * @return void
 */
function stitchz_social_login_activate() {
  _log_debug( 'Flushing the rewrite rules...' );
  flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'stitchz_social_login_activate' );

/**
 * Define the Stitchz Configuration link.
 *
 * @return array
 *   A new collection of admin links with the Stitchz Setup page link.
 */
function stitchz_social_login_add_config_link( $links, $file ) {
  $mylinks = array( '<a href="' . admin_url( 'admin.php?page=stitchz_social_login_setup' ) . '">' . __( 'Configure', 'stitchz_social_login' ) . '</a>',
  );
  return array_merge( $links, $mylinks );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'stitchz_social_login_add_config_link', 10, 2 );

/**
 * Gets list of identity providers and returns html.
 *
 * @return string
 *   An html string of identity providers.
 */
function stitchz_social_login_provider_list() {

  // Send the HTTP request to get the list of providers.
  stitchz_social_login_admin_provider_list();

  // Kill any further page processing.
  die();
}
add_action( 'wp_ajax_stitchz_social_login_provider_list', 'stitchz_social_login_provider_list' );

/**
 * Initializes authorization handler and associated end points
 *
 * @return void
 */
function stitchz_social_login_initialize() {
  add_filter( 'query_vars', 'stitchz_social_login_add_query_vars', 0 );
  add_action( 'parse_request', 'stitchz_social_login_scan_requests', 100 );
  add_rewrite_rule( '^stitchz_social_login/auth?','index.php?__auth=1','top' );
  add_rewrite_rule( '^stitchz_social_login/identity/add', 'index.php?__add=1', 'top' );
  add_rewrite_rule( '^stitchz_social_login/?([0-9]+)?/identity/delete/?([0-9]+)?', 'index.php?user_id=$matches[1]&meta_id=$matches[2]&__del=1', 'top' );
}
add_action( 'init', 'stitchz_social_login_initialize', 101 );