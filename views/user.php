<?php

/**
 * Show custom user profile fields
 *
 * @param object $user
 *   The user object.
 *
 * @return void
 */
function stitchz_social_login_user_profile_fields( $user ) {
  $settings = get_option( 'stitchz_social_login_settings' );

  // If the settings are not available then return nothing.
  if ( ! is_array( $settings ) ) {
    return;
  }

  $domain = ( isset( $settings['domain'] ) ? $settings['domain'] : __( 'https://api.stitchz.net/', 'stitchz_social_login' ) );
  $apikey = ( isset( $settings['apikey'] ) ? $settings['apikey'] : '' );
  $appsecret = ( isset( $settings['appsecret'] ) ? $settings['appsecret'] : '' );
  $redirecturl = ( isset( $settings['redirecturl'] ) ? $settings['redirecturl'] : get_site_url() . '/stitchz_social_login/auth' );
  $version = ( isset( $settings['version'] ) ? $settings['version'] : '2' );
  $providers = ( isset( $settings['providers'] ) ? stitchz_social_login_decode_json( $settings['providers'] ) : array() );
  $scope = ( isset( $settings['scope'] ) ? $settings['scope'] : '' );
  $theme_version = ( isset( $settings['theme_version'] ) ? $settings['theme_version'] : 'Basic' );

  // Get an array of the user's identities.
  $identities = stitchz_social_login_addin_get_user_identities( $user );
  ?>
  <h3><?php _e( 'Connected Identities', 'stitchz_social_login' ) ?></h3>
  <div id="stitchz_social_login_provider_list_block">
	<div id="stitchz_social_login_addin_form_fieldset_identities">
	  <?php echo stitchz_social_login_format_user_provider_list( $user, $identities, $redirecturl ); ?>
	</div>
	<div id="stitchz_social_login_addin_form_fieldset_providers">
	  <?php echo stitchz_social_login_addin_unused_identity_list( $user, $providers, $identities, $domain, $apikey, $redirecturl ); ?>
	</div>
	<div class="clearfix"></div>
  </div>
  <?php
}
add_action( 'show_user_profile', 'stitchz_social_login_user_profile_fields' );

/**
 * Gets an array of the user's identities.
 *
 * @param object $account
 *   User account to get identities for.
 *
 * @return array
 *   An associative array of identities.
 */
function stitchz_social_login_addin_get_user_identities( $account ) {
  global $wpdb;

  // Grab the user ID, if empty then get the current user's ID.
  if ( empty( $account ) ) {
	global $current_user;
    $uid = $current_user->ID;
  }
  else {
    $uid = $account->ID;
  }

  // Read the user's identity from the database.
  $sql = "SELECT um.meta_value, um.umeta_id FROM " . $wpdb->users . " AS u INNER JOIN " . $wpdb->usermeta . " AS um ON (u.ID = um.user_id) WHERE um.meta_key = 'stitchz_social_login_identity' AND u.ID=%d";

  $result = $wpdb->get_results( $wpdb->prepare( $sql, $uid ) );

  // Load the resultset into an array so we can iterate over it multiple times.
  $results = array();
  foreach ( $result as $identity ) {
    $results[] = $identity;
  }

  // Return an array of identities.
  return $results;
}
