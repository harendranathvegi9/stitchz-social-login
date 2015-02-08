<?php

/**
 * Add the public querystring variables.
 *
 * @param array $vars
 *   The querystring variable passed in with the Url.
 *
 * @return array
 */
function stitchz_social_login_add_query_vars( $vars ) {
  $vars[] = '__auth';
  $vars[] = '__add';
  $vars[] = '__del';
  $vars[] = 'user_id';
  $vars[] = 'meta_id';
  return $vars;
}

/**
 * Read the incoming request for the Stitchz query variable.
 */
function stitchz_social_login_scan_requests() {
  global $wp;

  // This is an authentication request, pass the request into the handler.
  if ( isset( $wp->query_vars['__auth'] ) ) {
	stitchz_social_login_authorization_handler();
	exit;
  }
  // Add an additional identity to the current user.
  else if ( isset( $wp->query_vars['__add'] ) ) {
	stitchz_social_login_add_identity();
	exit;
  }
  // Remove an identity from the current user.
  else if ( isset( $wp->query_vars['__del'] ) && isset( $wp->query_vars['user_id'] ) && isset( $wp->query_vars['meta_id'] ) ) {
	$uid = $wp->query_vars['user_id'];
	$mid = $wp->query_vars['meta_id'];

	stitchz_social_login_remove_identity( $uid, $mid );
	exit;
  }
}

/**
 * Process incoming request for authentication into Wordpress app.
 */
function stitchz_social_login_authorization_handler() {

  // Read database settings.
  $config = get_option( 'stitchz_social_login_settings' );

  // Check api version to call.
  if ( $config['version'] == '1' ) {
    // Read incoming request.
    $profile = stitchz_social_login_authorization_handler_prepare_and_send_v1_request( $config['domain'], $config['appsecret'] );
  } else {
    // Read incoming request.
    $profile = stitchz_social_login_authorization_handler_prepare_and_send_v2_request( $config['domain'], $config['apikey'], $config['appsecret'], $config['redirecturl'] );
  }

  // Check if a valid profile object has been returned.
  if ( is_object( $profile ) ) {
    if ( isset( $profile->error ) ) {
      // Check error code and/or status parameter.
      $error = ( ! empty( $profile->error->error_description ) ? $profile->error->error_description : __( 'Unknown error, please contact your website administrator.', 'stitchz_social_login' ) );
      stitchz_social_login_set_login_message ( sprintf( __( 'There was an error logging in with Stitchz Social Login, %s', 'stitchz_social_login' ), $error ), 'error' );
      add_filter( 'login_message', 'stitchz_social_login_set_login_message' );
      wp_redirect( wp_login_url() );
    } elseif ( isset( $profile->profile ) ) {
      $useridentity = $profile->profile;
      $identity = $useridentity->identifier;

      // The provider are we authenticed with.
      $provider = 'stitchz.net';
      if ( isset( $useridentity->accounts ) ) {
        $socialaccount = $useridentity->accounts;
        $provider = $socialaccount->domain;
      }

      // Get and clean up the authenticating provider name.
      $identity_provider_name = stitchz_social_login_clean_provider_name( $provider );

      // Check if the user account exists based on identity.
      $account = stitchz_social_login_authorization_handler_get_user_by_identity( $identity, $identity_provider_name );

      // Is the user account object empty.
      if ( is_object( $account ) && ! empty( $account->ID ) ) {

        // Update the user's stitchz profile with the oauth credentials.
        if ( $config['version'] == '2' ) {
          stitchz_social_login_auth_save_oauth2_identity_meta_data( $account->ID, $useridentity );
        }

        // TODO: should we check if user's social profile has changed? i.e. email, aboutMe, photo, url, etc.

        // Log the user in and move on.
        stitchz_social_login_login_user( $account );
        exit;
      }
      // New user.
      else {

        // We'll use this later if no valid email is found.
        $fake_email_address = FALSE;

        // Find a valid username.
        if ( isset( $useridentity->preferredUsername ) && ! empty( $useridentity->preferredUsername ) ) {
          $loginname = $useridentity->preferredUsername;
        } elseif ( !empty( $useridentity->displayName ) ) {
          $loginname = $useridentity->displayName;
        } else {
          $loginname = $useridentity->identifier;
        }
        $loginname = sanitize_user( $loginname );

        // Generate a random password.
        $rnd_password = wp_generate_password( 12 );

		// Check if username is already used.
		$loginname = stitchz_social_login_check_and_get_unique_username( $loginname, $provider );

		// Check for valid and unique email address.
		$email_address = ( isset( $useridentity->email ) ? $useridentity->email : '' );
		$email_address = stitchz_social_login_check_and_get_unique_email( $email_address, $loginname, $provider );

		// Assign the default drupal role.
        $account_role = get_option( 'default_role' );

        $display_name = $useridentity->displayName;
        $url = ( isset( $useridentity->url ) ? $useridentity->url : '' );
        $given_name = ( isset( $useridentity->givenName ) ? $useridentity->givenName : '' );
        $family_name = ( isset( $useridentity->familyName ) ? $useridentity->familyName : '' );

		// Create the user.
		$new_user_fields = array(
          'user_login' => $loginname,
          'display_name' => $display_name,
          'user_pass' => $rnd_password,
          'user_email' => $email_address,
          'first_name' => $given_name,
          'last_name' => $family_name,
          'user_url' => $url,
          'role' => $account_role,
        );

        $new_user_id = wp_insert_user( $new_user_fields );

        // Check if the user account was created successfully.
        if ( is_numeric( $new_user_id ) && FALSE !== ( $account = get_userdata( $new_user_id ) ) ) {

		  // Associate identifier to new account.
		  stitchz_social_login_authorization_handler_assoc_identity_to_user( $new_user_id, $identity, $identity_provider_name );

		  // Update user meta database.
		  if ( isset( $useridentity->aboutMe ) && ! empty( $useridentity->aboutMe ) ) {
			stitchz_social_login_auth_save_meta_data( $new_user_id, 'description', $useridentity->aboutMe );
		  }

		  // Add the user's stitchz oauth credentials.
		  if ( $config['version'] == '2' ) {
			stitchz_social_login_auth_save_oauth2_identity_meta_data( $new_user_id, $useridentity );
		  }

		  // TODO: add user photo

		  // Log the user in and move on.
		  stitchz_social_login_login_user( $account );
		  exit;
		}
      }
    }
  }

  // Check if the admin has enabled user registration.
  if ( stitchz_social_login_users_can_register() ) {
	stitchz_social_login_log_debug( 'Redirecting to user registration page...' );
	wp_redirect( wp_registration_url() );
	exit;
  } else {
	stitchz_social_login_log_debug( 'Redirecting to home page...' );
	wp_redirect( home_url() );
	exit;
  }
}

/**
 * Log the user in to this Wordpress app.
 *
 * @param object $user
 *   The user account to log in to the app.
 */
function stitchz_social_login_login_user( $user ) {
  //Refresh the cache
  wp_cache_delete( $user->ID, 'users' );
  wp_cache_delete( $user->user_login, 'userlogins' );

  //Set the cookie and login
  wp_clear_auth_cookie();
  wp_set_current_user( $user->ID , $user->user_login );
  wp_set_auth_cookie( $user->ID, TRUE );
  do_action( 'wp_login', $user->user_login, $user );

  wp_redirect( home_url() . '/wp-admin/profile.php' );

  exit;
}

/**
 * Grab the OAuth2 accessToken info for future use and store in database.
 *
 * @param int $uid
 *   The user id for the user account to add the token to.
 * @param string $meta_key
 *   A security identifier for the authenticated user.
 * @param string $meta_value
 *   A security identifier used to exchange for an unexpired access token.
 */
function stitchz_social_login_auth_save_meta_data( $uid, $meta_key, $meta_value ) {
  return update_user_meta( $uid, $meta_key, $meta_value );
}

/**
 * Grab the OAuth2 accessToken info for future use and store in database.
 *
 * @param int $uid
 *   The user id for the user account to add the token to.
 * @param string $meta_key
 *   A security identifier for the authenticated user.
 * @param string $meta_value
 *   A security identifier used to exchange for an unexpired access token.
 */
function stitchz_social_login_auth_remove_meta_data( $uid, $meta_key, $meta_value = NULL ) {
  return delete_user_meta( $uid, $meta_key, $meta_value );
}

/**
 * Save the OAuth2 encrypted tokens and expiration datetimes to the database.
 *
 * @param int $user_id
 *   The user ID to save the credentials for.
 * @param object $useridentity
 *   The authenticated user's identity object.
 */
function stitchz_social_login_auth_save_oauth2_identity_meta_data( $user_id, $useridentity ) {
  if ( ! empty( $useridentity->accessToken ) AND
	! empty( $useridentity->refreshToken ) AND
	! empty( $useridentity->accessTokenExpirationUtc ) AND
	! empty( $useridentity->accessTokenIssueDateUtc ) AND
	! empty( $useridentity->callback ) ) {
	stitchz_social_login_log_debug( 'Save OAuth2 response to database.' );
	stitchz_social_login_auth_save_meta_data( $user_id, 'access_token',  $useridentity->accessToken );
	stitchz_social_login_auth_save_meta_data( $user_id, 'refresh_token',  $useridentity->refreshToken );
	stitchz_social_login_auth_save_meta_data( $user_id, 'access_token_expiration_utc', $useridentity->accessTokenExpirationUtc );
	stitchz_social_login_auth_save_meta_data( $user_id, 'access_token_issue_date_utc', $useridentity->accessTokenIssueDateUtc );
	stitchz_social_login_auth_save_meta_data( $user_id, 'callback', $useridentity->callback );
  }
}

/**
 * Get the global $user from the given identity.
 *
 * @param string $identity
 *   The unique identifier mapped to a user account.
 * @param string $provider
 *   The authenticating provider's name.
 *
 * @return object|bool
 *   A valid user account or FALSE is nothing found.
 */
function stitchz_social_login_authorization_handler_get_user_by_identity( $identity, $provider ) {
  global $wpdb;

  // The token is required.
  if ( strlen( trim( strval( $identity) ) ) == 0 ) {
	return FALSE;
  }

  // Read user for this token.
  $sql = "SELECT u.ID FROM " . $wpdb->users . " AS u INNER JOIN " . $wpdb->usermeta . " AS um ON (u.ID = um.user_id) WHERE um.meta_key = 'stitchz_social_login_identity' AND um.meta_value=%s";

  $userid = $wpdb->get_var( $wpdb->prepare( $sql, ( $provider . '|' . $identity ) ) );

  // Check if the $user_id is a number, and if so, try to get the associated user account.
  if ( is_numeric( $userid ) ) {
    if ( FALSE !== ( $user = get_userdata( $userid ) ) ) {
      return $user;
    }
  }
  return FALSE;
}

/**
 * Check if the given username has been used previously or not.
 *
 * @param string $username
 *   The username to check against.
 * @param string $appender
 *   If the username has been used, the appender is added to the name to try
 *   and create a new unique username.
 *
 * @return string $username
 *   A confirmed unique username.
 */
function stitchz_social_login_check_and_get_unique_username( $username, $appender ) {

  // Check if username is already used.
  if ( username_exists( $username ) ) {
	$temp_loginname = trim( $username ) . '-' . $appender;
	$x = 1;

	// Ensure a unique name, otherwise, create a unique one.
	while ( FALSE !== username_exists( $temp_loginname ) ) {
	  $temp_loginname = trim( $username ) . '-' . $appender . '-' . $x;
	  $x++;
	}
	$username = $temp_loginname;
  }
  return $username;
}

/**
 * Check if the given email has been used previously or not.
 *
 * @param string $email
 *   The email address to check against.
 * @param string $username
 *   The username can sometimes be formatted as an email address, so check it.
 * @param string $appender
 *   If a fake email address has been used, the appender is added to the username
 *   to try and create a new unique email.
 *
 * @return string
 *   A confirmed unique email address.
 */
function stitchz_social_login_check_and_get_unique_email( $email, $username, $provider ) {

  if ( isset( $email ) && ! empty( $email ) && is_email( $email ) ) {
    $email_address = $email;
  } else {
    // Check if $username is in an email format.
    if ( is_email( $username ) ) {
      $email_address = $username;
    }
    // Create a fake email address based on the identity providers domain.
    else {
      $fake_email_address = TRUE;
      $email_address = trim( $username ) . '@' . $provider;
    }
  }

  // Ensure a unique email, otherwise, create a unique one.
  if ( email_exists( $email_address ) ) {
    $temp_email = $email_address;
    while ( FALSE !== email_exists( $temp_email ) ) {
      $temp_email = trim( $username ) . '-' . $x . '@' . $provider;
      $x++;
    }
    $email_address = $temp_email;
  }
  return $email_address;
}

/**
 * Adds an additional social login identity to the current user.
 */
function stitchz_social_login_add_identity() {

  global $current_user;

  // The user must be logged in.
  if ( is_object( $current_user ) && ! empty( $current_user->ID ) ) {

    // Read settings from database.
    $settings = get_option( 'stitchz_social_login_settings' );

	// If the settings are available then return nothing.
	if ( ! is_array( $settings ) ) {
	  return;
	}

    // Send request and read incoming response.
    $profile = stitchz_social_login_authorization_handler_prepare_and_send_v1_request( $settings['domain'], $settings['appsecret'] );

    // If we have a valid response.
    if ( is_object( $profile ) ) {
      // Check if the response contains any errors.
      if ( isset( $profile->error ) ) {
        // Check error code and/or status parameter.
        $error = ( ! empty( $profile->error->error_description ) ? $profile->error->error_description : __( 'Unknown error, please contact your website administrator.' ) );
		stitchz_social_login_set_login_message ( sprintf( __( 'There was an error logging in with Stitchz Social Login, %s', 'stitchz_social_login' ), $error ), 'error' );
		add_filter( 'login_message', 'stitchz_social_login_set_login_message' );
        wp_redirect( home_url() . '/wp-admin/profile.php' );
      }
      // No errors, so lets process the profile.
      elseif ( isset( $profile->profile ) ) {
        $useridentity = $profile->profile;
        $identity = $useridentity->identifier;

        // The provider we are authenticed with.
        $provider = 'stitchz.net';
        if ( isset( $useridentity->accounts ) ) {
          $socialaccount = $useridentity->accounts;
          $provider = $socialaccount->domain;
        }

        // Get and clean up the authenticating provider name.
        $identity_provider_name = stitchz_social_login_clean_provider_name( $provider );

        // Add identity to current user account.
        stitchz_social_login_authorization_handler_assoc_identity_to_user( $current_user->ID, $identity, $identity_provider_name );

        // Send the user to their profile page.
        wp_redirect( home_url() . '/wp-admin/profile.php' );
        exit;
      }
    }
  } else {
    stitchz_social_login_log_debug( __( 'Stitchz Social Login Add Identity', 'Unable to add identity, the user is not logged in or their session has expired.' , 'stitchz_social_login' ) );

    stitchz_social_login_set_login_message( __( 'There was an error adding the social identity. Login and try again.' , 'stitchz_social_login' ) );
  }
  wp_redirect( home_url() );
  exit;
}

/**
 * Removes the given identity (authmap id) from the current user.
 *
 * @param object $account
 *   The user account to remove the identity from.
 * @param int $aid
 *   The authmap id to be removed.
 *
 * @return null|bool
 *   Redirects to user page if successful, otherwise, returns FALSE.
 */
function stitchz_social_login_remove_identity( $uid, $mid ) {
  if ( is_numeric( $uid ) && is_numeric( $mid ) ) {

	global $wpdb;
    global $current_user;

    // Check if give UID matches current user ID, or if the user privileges to perform the action.
    if ( $current_user->ID != $uid && ! current_user_can( 'delete_users' ) ) {
      stitchz_social_login_log_debug( 'User (' . $current_user->ID . ') is not the same as (' . $uid . '), and does not have permissions to delete users.');
      wp_redirect( home_url() . '/wp-admin/profile.php' );
      exit;
    }

    // Read user for this token.
	$sql = "SELECT um.meta_value FROM " . $wpdb->users . " AS u INNER JOIN " . $wpdb->usermeta . " AS um ON (u.ID = um.user_id) WHERE um.meta_key = 'stitchz_social_login_identity' AND um.umeta_id=%s";

    // Run the query and get the result
    $identity = $wpdb->get_var( $wpdb->prepare( $sql, $mid ) );

    // Check that an identity was returned.
    if ( ! empty( $identity ) ) {
      stitchz_social_login_authorization_handler_remove_identity_from_user( $uid, $identity );

      // Check if any identities remain, if not, then remove any Stitchz leftovers for this account, i.e. callback, refresh_token, etc.
      stitchz_social_login_authorization_handler_clean_left_overs( $uid );
    }
  }

  // Send the user to their profile page.
  wp_redirect( home_url() . '/wp-admin/profile.php' );
  exit;
}

/**
 * Adds the given identity to the wordpress user account.
 *
 * @param int $user_id
 *   The user account id to associate an identity to.
 * @param string $identity
 *   The identity to connect to the user account.
 * @param string $provider
 *   The provider name or domain.
 *
 * @return bool
 *   TRUE if successful; FALSE if not.
 */
function stitchz_social_login_authorization_handler_assoc_identity_to_user( $user_id, $identity, $provider ) {
  global $wpdb;

  // Add a new key to the usermeta table for the given user_id.
  $rows_affected = $wpdb->insert( $wpdb->usermeta, array(
    'meta_key' => 'stitchz_social_login_identity',
    'meta_value' => $provider . '|' . $identity,
    'user_id' => $user_id,
  ) );
}

/**
 * Removes the given identity from the given user_id.
 *
 * @param int $user_id
 *   The user account id to remove the identity from.
 * @param string $identity_value
 *   The identity and provider concatenated string/key.
 */
function stitchz_social_login_authorization_handler_remove_identity_from_user( $user_id, $identity_value ) {
  stitchz_social_login_auth_remove_meta_data( $user_id, 'stitchz_social_login_identity', $identity_value );
}

/**
 * Removes any OAuth2 encrypted tokens and expiration datetimes from the database.
 *
 * @param int $uid
 *   The user account id to remove the OAuth2 tokens from.
 */
function stitchz_social_login_authorization_handler_clean_left_overs( $uid ) {
  global $wpdb;

  // Read user_meta for given user_id.
  $sql = "SELECT um.meta_value, um.umeta_id FROM " . $wpdb->users . " AS u INNER JOIN " . $wpdb->usermeta . " AS um ON (u.ID = um.user_id) WHERE um.meta_key = 'stitchz_social_login_identity' AND u.ID=%d";

  $results = $wpdb->get_results( $wpdb->prepare( $sql, $uid ) );

  if ( empty( $results ) || count( $results ) == 0 ) {
	stitchz_social_login_auth_remove_meta_data( $uid, 'access_token' );
	stitchz_social_login_auth_remove_meta_data( $uid, 'refresh_token' );
	stitchz_social_login_auth_remove_meta_data( $uid, 'access_token_expiration_utc' );
	stitchz_social_login_auth_remove_meta_data( $uid, 'access_token_issue_date_utc' );
	stitchz_social_login_auth_remove_meta_data( $uid, 'callback' );
  }
}

/**
 * Sanitizes and removes any domain from the provider name.
 *
 * @param string $provider
 *   The provider name to clean.
 *
 * @return string
 *   A clean and sanitized string free of domain name or dot com ending.
 */
function stitchz_social_login_clean_provider_name( $provider ) {
  return ucfirst( trim( preg_replace( array( '/.com$/', '/.net$/', '/.co$/' ), '', sanitize_text_field( $provider ) ) ) );
}

/**
 * Builds a warning/error block used to display a message to the end user.
 *
 * @param string $message
 *   The message to display to the end user.
 * @param string $error_type
 *   The type of message block to display; error or warning.
 *
 * @return string
 *   The html block to display.
 */
function stitchz_social_login_set_login_message( $message, $error_type ) {
  if ( ! empty( $message ) ) {
	$class_name = esc_attr( $error_type !== 'error' ? 'update-nag' : 'error login-error' );
	$id_name = esc_attr( $error_type !== 'error' ? 'login_message' : 'login_error' );

    return '<div id="setting-error-stitchz_social_login_' . $id_name . '" class="' . $class_name . '">' . sanitize_text_field( $message ) . '</div>';
  }
}
