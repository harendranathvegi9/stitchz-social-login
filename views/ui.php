<?php

/**
 * Register general population styles for formatting the identities.
 */
function stitchz_social_login_enqueue_stylesheet() {
  wp_register_style( 'stitchz_social_login_wp_admin_css', STITCHZ_SOCIAL_LOGIN__PLUGIN_URL .  'styles/stitchz_social_login.css', false, '1.0.0' );
  wp_register_style( 'stitchz_social_login_fonts_wp_admin_css', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css', false, '4.1.0' );

  wp_enqueue_style( 'stitchz_social_login_wp_admin_css' );
  wp_enqueue_style( 'stitchz_social_login_fonts_wp_admin_css' );
}
add_action( 'login_enqueue_scripts', 'stitchz_social_login_enqueue_stylesheet' );

/**
 * Display the list of identity providers for the login form.
 */
function stitchz_social_login_display_login_form() {
  echo stitchz_social_login_login_view( 'login_form' );
}
add_action( 'login_form', 'stitchz_social_login_display_login_form' );

/**
 * Display the list of identity providers for the registration form.
 */
function stitchz_social_login_display_registration_form() {
  echo stitchz_social_login_login_view( 'registration_form' );
}
add_action( 'register_form', 'stitchz_social_login_display_registration_form' );

/**
 * Display the list of identity providers for the comments form.
 */
function stitchz_social_login_display_comments_form() {
  if ( ! is_user_logged_in() && comments_open() ) {
	echo stitchz_social_login_login_view( 'comments_form' );
  }
}
add_action( 'wp_enqueue_scripts', 'stitchz_social_login_enqueue_stylesheet' );
add_action( 'comment_form_top', 'stitchz_social_login_display_comments_form' );

/**
 * Display the list of identity providers for the registration form.
 *
 * @param string $page 
 *   The name of the page to build and display.
 */
function stitchz_social_login_login_view( $page ) {
  // Make sure users are allowed to register.
  if ( stitchz_social_login_users_can_register() ) {
	$settings = get_option ( 'stitchz_social_login_settings' );

	// If the settings are available then return nothing.
	if ( ! is_array ( $settings ) ) {
	  return;
	}

	$domain = esc_url_raw( isset( $settings['domain'] ) ? $settings['domain'] : __( 'https://api.stitchz.net/', 'stitchz_social_login' ) );
	$apikey = sanitize_text_field( isset( $settings['apikey'] ) ? $settings['apikey'] : '' );
	$appsecret = sanitize_text_field( isset( $settings['appsecret'] ) ? $settings['appsecret'] : '' );
	$redirecturl = esc_url_raw( isset( $settings['redirecturl'] ) ? $settings['redirecturl'] : get_site_url() . '/stitchz_social_login/auth' );
	$version = sanitize_text_field( isset( $settings['version'] ) ? $settings['version'] : '2' );
	$providers = stitchz_social_login_decode_json( sanitize_text_field( isset( $settings['providers'] ) ? $settings['providers'] : '' ) );
	$scope = sanitize_text_field( isset( $settings['scope'] ) ? $settings['scope'] : '');
	$theme_version = sanitize_text_field( isset( $settings['theme_version'] ) ? $settings['theme_version'] : 'Basic' );
	$enable_user_login_screen = sanitize_text_field( isset( $settings['enable_user_login_screen'] ) ? ( $settings['enable_user_login_screen'] == '1' ? TRUE : FALSE ) : FALSE );
	$enable_user_registration_screen = sanitize_text_field( isset( $settings['enable_user_registration_screen'] ) ? ( $settings['enable_user_registration_screen'] == '1' ? TRUE : FALSE ) : FALSE );
	$enable_comment_screen = sanitize_text_field( isset( $settings['enable_comment_screen'] ) ? ( $settings['enable_comment_screen'] == '1' ? TRUE : FALSE ) : FALSE );
	$notes = sanitize_text_field( ( isset( $settings['notes'] ) ? $settings['notes'] : '' ), 'filtered_html' );

	$view_html = '<div id="stitchz_social_login_provider_list_block">';
	if ( 'login_form' === $page && $enable_user_login_screen ) {
	  $providers_list = stitchz_social_login_format_provider_list( $domain, $providers, $version, $apikey, $redirecturl, $scope, $notes );
      $view_html .=  $providers_list;
	} elseif ( 'registration_form' === $page && $enable_user_registration_screen ) {
	  $providers_list = stitchz_social_login_format_provider_list( $domain, $providers, $version, $apikey, $redirecturl, $scope, $notes, __( 'Register with', 'stitchz_social_login' ) . ':' );
      $view_html .= $providers_list;
	} elseif ( 'comments_form' === $page && $enable_comment_screen ) {
	  $providers_list = stitchz_social_login_format_provider_list( $domain, $providers, $version, $apikey, $redirecturl, $scope, $notes, __( 'Comment with', 'stitchz_social_login' ) . ':' );
      $view_html .= $providers_list;
	}
	$view_html .= '</div>';

	return $view_html;
  }
}

/**
 * Create the list of identity providers for display.
 *
 * @param string $dnsalias
 *   App url as provided by Stitchz.
 * @param array $providers
 *   Json string of identity providers.
 * @param string $apiversion
 *   The version of the Stitchz api being used.
 * @param string $apikey
 *   Unique client id provided by Stitchz.
 * @param string $redirect_uri
 *   Callback url where Stitchz will send authenticated response.
 * @param string $scope
 *   Application scope as provided by Stitchz.
 * @param string $notes
 *   Free form text to display under the identities.
 * @param string $title
 *   The section title or header.
 * @param string $list_id
 *   The html ID name to give to the resulting div.
 *
 * @return string
 *   An html string of formatted identity providers.
 */
function stitchz_social_login_format_provider_list( $dnsalias, array $providers, $apiversion, $apikey, $redirect_uri, $scope, $notes, $title = NULL, $list_id = NULL ) {
  if ( ! isset( $dnsalias ) ) {
	stitchz_social_login_log_debug( 'DnsAlias is empty or missing' );
    return __( 'Configuration error. Contact the site administrator.', 'stitchz_social_login' );
  }

  if ( empty( $providers ) || ! is_array( $providers ) || ! isset( $providers ) ) {
    stitchz_social_login_log_debug( 'Provider list is empty or missing' );
    return __( 'Your providers list is currently empty. Click "Sync Providers" to pull your list of configured identity providers from your Stitchz Login application.', 'stitchz_social_login' );
  }

  if ( ! isset( $apiversion ) ) {
    stitchz_social_login_log_debug( 'Api Version is empty or missing' );
    return __( 'Configuration error. Contact the site administrator.', 'stitchz_social_login' );
  }

  if ( ! isset( $apikey ) ) {
    stitchz_social_login_log_debug( 'ApiKey is empty or missing' );
    return __( 'Configuration error. Contact the site administrator.', 'stitchz_social_login' );
  }

  if ( ! isset( $redirect_uri ) ) {
    stitchz_social_login_log_debug( 'Redirect Uri is empty or missing' );
    return __( 'Configuration error. Contact the site administrator.', 'stitchz_social_login' );
  }

  if ( ! isset( $scope ) ) {
    stitchz_social_login_log_debug( 'Scope is empty or missing' );
    return __( 'Configuration error. Contact the site administrator.', 'stitchz_social_login' );
  }

  $title = sanitize_text_field( isset( $title ) ? $title : __( 'Sign In With:', 'stitchz_social_login' ) );

  $restricted_resource_url = rtrim( esc_url_raw( $dnsalias ), '/' ) . '/';

  $provider_html = '<div>' . $title . '</div>';
  $provider_html .= '<ul class="stitchz_social_login_provider_list"' . ( ! empty( $list_id ) ? ' id="' . esc_attr( $list_id ) . '"' : '') . '>';

  foreach ( $providers as $provider ) {
    if ( TRUE === $provider->IsActive ) {
      $provider_name = sanitize_text_field( $provider->Name );

      // Check which api version to call.
      if ( $apiversion == '1' ) {
        $authenticate_url = $restricted_resource_url . str_replace( ' ', '', $provider_name ) . '/v1/Authenticate?ApiKey=' . urlencode( $apikey ) . '&ReturnUrl=' . urlencode( $redirect_uri );
      } else {
        // Generate a random string.
        $state = substr( md5( uniqid( mt_rand(), TRUE ) ), 0, 10 );
        $authenticate_url = $restricted_resource_url . str_replace( ' ', '', $provider_name ) . '/v2/Authenticate?client_id=' . urlencode( $apikey ) . '&redirect_uri=' . urlencode( $redirect_uri ) . '&scope=' . urlencode( $scope ) . '&state=' . $state . '&response_type=code';
      }

      // Check for a valid $authenticate_url (api) and return an error if invalid.
      if ( FALSE === stitchz_social_login_check_is_valid_api_url( $authenticate_url ) ) {
	    return '<div class="messages error"><ul><li>' . __( 'Invalid App Url. Ensure the App Url is correct or contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
      } else {
        $provider_html .= stitchz_social_login_format_provider( $provider_name, $authenticate_url );
      }
    }
  }
  $provider_html .= '</ul>';

  if ( ! empty( $notes ) ) {
    $provider_html .= '<div class="stitchz_social_login_provider_list_notes">' . sanitize_text_field( $notes, 'filtered_html' ) . '</div>';
  }

  $provider_html .= '<div class="icon_footer"></div><div style="clear:both;"></div>';

  return $provider_html;
}

/**
 * Format the individual identity provider for display.
 *
 * @param string $provider_name
 *   Name of identity provider.
 * @param array $authentication_url
 *   Full URL of authentication end point.
 *
 * @return string
 *   An html string of the formatted identity provider.
 */
function stitchz_social_login_format_provider( $provider_name, $authentication_url ) {

  return '<li class="stitchz_social_login_identity">
  <a href="' . $authentication_url . '">
    <div class="stitchz_social_login_identity_provider ' . strtolower( $provider_name ) . '" title="link a new identity provider">
      <span class="fa fa-fw fa-' . strtolower( $provider_name ) . '"></span>
      <span class="stitchz_social_login_identity_provider_name">' . $provider_name . '</span>
    </div>
  </a>
  <br style="clear: left;" />
</li>';

}

/**
 * Format the individual identity provider for display, with delete link.
 *
 * @param string $provider_name
 *   Name of identity provider.
 * @param array $authentication_url
 *   Full URL of authentication end point.
 *
 * @return string
 *   An html string of the formatted identity provider.
 */
function stitchz_social_login_format_used_provider( $provider_name, $authentication_url ) {

  return '<li class="stitchz_social_login_identity">
  <div class="stitchz_social_login_identity_provider ' . strtolower( $provider_name ) . '">
    <span class="fa fa-fw fa-' . strtolower( $provider_name ) . '"></span>
    <span class="stitchz_social_login_identity_provider_name">' . $provider_name . '</span>
  </div>
  <div class="stitchz_social_login_identity_provider_actions">
    <a href="' . $authentication_url . '">
      <span class="fa fa-times" title="remove this identity provider"></span>
    </a>
  </div>
  <br style="clear: left;" />
</li>';

}

/**
 * Formats the currently connected identity providers.
 *
 * Creates an html formatted list of connected identity providers, including
 * links to remove the identity profile from current account.
 *
 * @param object $account
 *   The user account to get unused identities for.
 * @param array $identities
 *   A list of identities connected to the user.
 * @param string $redirect_uri
 *   Callback url where Stitchz will send authenticated response.
 * @param string $list_id
 *   The html ID name to give to the resulting div.
 *
 * @return string
 *   An html list of connected identities.
 */
function stitchz_social_login_format_user_provider_list( $account, array $identities, $redirect_uri, $list_id = NULL ) {

  $provider_html = '<div class="provider_list_title">' . __( 'Connected social login identities:', 'stitchz_social_login' ) . '</div>';

  $provider_html .= '<ul class="stitchz_social_login_profile_identities_list"' . ( ! empty( $list_id ) ? ' id="' . esc_attr( $list_id ) . '"' : '' ) . '>';

  // Check that the account and identities objects exist and are set.
  if ( isset( $account ) && isset( $identities ) && count( $identities ) > 0 ) {
	foreach ( $identities as $identity ) {
	  $provider_name = explode( '|', $identity->meta_value );

	  // Remove the stitchz authentication url.
	  $redirect_uri = str_replace( '/stitchz_social_login/auth', '', esc_url_raw( $redirect_uri ) );
	  // Check if $redirect_uri ends with a slash or not.
	  $redirect_uri = rtrim( $redirect_uri, '/' );
	  $authenticate_url = $redirect_uri . '/stitchz_social_login/' . $account->ID . '/identity/delete/' . $identity->umeta_id;

	  // Format each used identity provider.
	  if ( isset( $provider_name ) && count( $provider_name ) > 0 ) {
	    $provider_html .= stitchz_social_login_format_used_provider( $provider_name[0], $authenticate_url );
	  }
	}
  } else {
	$provider_html .= '<li class="stitchz_social_login_identity">';
	$provider_html .= __( 'No providers connected to your account', 'stitchz_social_login' );
	$provider_html .= '</li>';
  }
  $provider_html .= '</ul>';

  return $provider_html;
}

/**
 * Builds list of identity providers that aren't connect to the current user.
 *
 * @param object $account
 *   The user account to get unused identities for.
 * @param array $providers
 *   A json collection of identity providers.
 * @param array $identities
 *   A list of identities connected to the user.
 * @param string $dnsalias
 *   The app url as provided by Stitchz.
 * @param string $apikey
 *   The unique client id provided by Stitchz.
 * @param string $redirect_uri
 *   Callback url where Stitchz will send authenticated response.
 * @param string $list_id
 *   The html ID name to give to the resulting div.
 *
 * @return string
 *   An html list of providers that are unused by the user.
 */
function stitchz_social_login_addin_unused_identity_list( $account, array $providers, array $identities, $dnsalias, $apikey, $redirect_uri, $list_id = NULL ) {

  // Check if any $providers exist, if not, then don't return the HTML.
  if ( ! empty( $providers ) ) {
    $provider_html = '<div class="provider_list_title">' . __( 'Authenticate with another Social Login Provider:', 'stitchz_social_login' ) . '</div>';
    $provider_html .= '<ul class="stitchz_social_login_provider_list"' . ( ! empty( $list_id ) ? ' id="' . esc_attr( $list_id ) . '"' : '' ) . '>';

    $i = 0;

    // Remove the trailing slash if it exists.
    $dnsalias = rtrim( esc_url_raw( $dnsalias ), '/' );

    // Remove the stitchz authentication url.
    $redirect_uri = str_replace( '/stitchz_social_login/auth', '', esc_url_raw( $redirect_uri ) );

    // Check if $redirect_uri ends with a slash or not.
    $redirect_uri = rtrim( $redirect_uri, '/' );
    $redirect_uri = $redirect_uri . '/stitchz_social_login/identity/add';

    // Loop through providers and build a list of unused providers to display.
    if ( isset( $providers ) ) {
      foreach ( $providers as $provider ) {
        if ( TRUE === $provider->IsActive ) {
          $provider_name = ucfirst( trim( sanitize_text_field( $provider->Name ) ) );
          $existing_provider = FALSE;

          // Check if any identities are currently connected.
          if ( isset( $identities ) ) {
            foreach ( $identities as $identity ) {
              $identity_provider_name = explode( '|', $identity->meta_value );

              if ( isset( $identity_provider_name ) && count( $identity_provider_name ) > 0 ) {
  			    if ( $provider_name === $identity_provider_name[0] ) {
  				  $existing_provider = TRUE;
  				  break;
	  		    }
              }
            }
          }

          // If the provider isn't used, then add it to the list to be displayed.
          if ( FALSE === $existing_provider ) {
            $i += 1;
            $authenticate_url = $dnsalias . "/" . $provider_name . "/v1/Authenticate?ApiKey=" . urlencode( $apikey ) . "&ReturnUrl=" . urlencode( $redirect_uri );

            // Check for a valid $authenticate_url (api) and return an error if invalid.
            if ( FALSE === stitchz_social_login_check_is_valid_api_url( $authenticate_url ) ) {
	          return '<div class="messages error"><ul><li>' . __( 'Invalid App Url. Ensure the App Url is correct or contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
            } else {
              $provider_html .= stitchz_social_login_format_provider( $provider_name, $authenticate_url );
            }
          }
        }
      }
    }

    $provider_html .= '</ul>';

    // If no providers are left to use, then let the end user know.
    if ( $i == 0 ) {
      $provider_html = '<div class="provider_list_title">' . __( 'All available social login providers have been used.', 'stitchz_social_login' ) . '</div>';
    }

    // Footer.
    $provider_html .= '<div class="icon_footer"></div><div style="clear:both;"></div>';
  }

  return $provider_html;
}

/**
 * Register admin page styles for formatting the identities.
 *
 * @param string $hook
 *   The page name the hook was executed on.
 */
function stitchz_social_login_admin_enqueue_styles( $hook ) {
    //if( 'admin.php' != $hook )
    //    return;
    wp_register_style( 'stitchz_social_login_wp_admin_css', STITCHZ_SOCIAL_LOGIN__PLUGIN_URL .  'styles/stitchz_social_login.css', false, '1.0.0' );
    wp_register_style( 'stitchz_social_login_fonts_wp_admin_css', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css', false, '4.1.0' );

    wp_enqueue_style( 'stitchz_social_login_wp_admin_css' );
    wp_enqueue_style( 'stitchz_social_login_fonts_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'stitchz_social_login_admin_enqueue_styles' );
