<?php

/**
 * Send request to Stitchz for access token.
 *
 * @param string $dnsalias
 *   The app url as provided by Stitchz.
 * @param string $apikey
 *   The unique client id provided by Stitchz.
 * @param string $appsecret
 *   The application secret provided by Stitchz.
 *
 * @return string|bool
 *   A valid oauth 2 access token or FALSE if the call fails.
 */
function stitchz_social_login_get_access_token( $dnsalias, $apikey, $appsecret ) {

  // Remove the trailing slash if found, then build the api end point url.
  $dnsalias = rtrim( esc_url_raw( $dnsalias ), '/' ) . '/';
  $url = $dnsalias . 'api/oauth2/Token';

  // Check for a valid $url (api) and return an error if invalid.
  if ( FALSE === stitchz_social_login_check_is_valid_api_url( $url ) ) {
    return '<div class="messages error"><ul><li>' . __( 'Invalid App Url. Ensure the App Url is correct or contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
  }

  // Build a valid request body.
  $data = 'client_id=' . urlencode( $apikey ) . '&client_secret=' . urlencode( $appsecret ) . '&grant_type=client_credentials&format=json';

  $options = array(
    'method' => 'POST',
    'headers' => array(
      'content-type' => 'application/x-www-form-urlencoded',
      'accept' => 'application/json',
    ),
    'body' => $data,
  );

  // TODO: we could use CURL here or give a choice of default Wordpress vs. Curl.

  // Send the POST request to the api end point.
  $response = wp_remote_post( $url, $options );

  // Check for a Wordpress error in the response.
  if ( is_wp_error( $response ) ) {
    if ( class_exists( 'WP_Exception' ) && $response instanceof WP_Exception ) {
      throw $response;
    }
    else {
      throw new Exception( $response->get_error_message(), (int) $response->get_error_code() );
    }
  }

  // Check for a valid response.
  if ( is_array( $response ) && $response['response']['code'] ) {
    switch ( $response['response']['code'] ) {
      case '200':
        if ( ! empty( $response['body'] ) ) {
		  $json_response = json_decode( $response['body'] );

          // Get the Access Token out of the response.
          $access_token = $json_response->access_token;
          $expiry_time = $json_response->expires_in;

          // Check if the token is there.
          if ( ! empty( $access_token ) ) {
            return $access_token;
          }
        }
        break;

      case '401':
        stitchz_social_login_log_debug( sprintf( 'Authentication Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
        return '<div class="messages warning"><ul><li>' . __( 'Unauthorized request. Double check your API Key and App Secret and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

      case '404':
        stitchz_social_login_log_debug( sprintf( 'Authentication Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
        return '<div class="messages warning"><ul><li>' . __( 'Not Found. Double check your relaying party URL and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

      case '500':
        stitchz_social_login_log_debug( sprintf( 'Authentication Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
        return '<div class="messages error"><ul><li>' . __( 'Internal Server Error. Ensure the service provider is available and contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
    }
  }
  return FALSE;
}

/**
 * Sends authorized HTTP request to Stitchz api.
 *
 * @param string $url
 *   The url to send api request.
 * @param string $access_token
 *   A valid oauth access token.
 *
 * @return json|bool
 *   A valid json object or FALSE if the call fails.
 */
function stitchz_social_login_get_api_call( $url, $access_token ) {

  // Check for a valid $url (api) and return an error if invalid.
  if ( FALSE === stitchz_social_login_check_is_valid_api_url( $url ) ) {
	return '<div class="messages error"><ul><li>' . __( 'Invalid App Url. Ensure the App Url is correct or contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
  }

  // Check if the given $access_token is empty and use it to submit the request.
  if ( ! empty( $access_token ) ) {
    $options = array(
      'method' => 'GET',
      'headers' => array(
        'authorization' => 'Bearer ' . $access_token,
        'accept' => 'application/json',
      ),
    );

    // TODO: we could use CURL here or give a choice of default WordPress vs. Curl.

    // Send the GET request to the api end point.
    $response = wp_remote_get( $url, $options );

    // Check for a Wordpress error in the response.
    if ( is_wp_error( $response ) ) {
        if ( class_exists( 'WP_Exception' ) && $response instanceof WP_Exception ) {
            throw $response;
        }
        else {
            throw new Exception( $response->get_error_message(), (int) $response->get_error_code() );
        }
    }

    // Check for a valid response.
    if ( is_array( $response ) && $response['response']['code'] ) {
      switch ($response['response']['code'] ) {
        case '200':
          if ( ! empty( $response['body'] ) ) {
            $json_response = json_decode( $response['body'] );
            return $json_response;
          }
          break;

        case '401':
          stitchz_social_login_log_debug( sprintf( 'API Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
          return '<div class="messages warning"><ul><li>' . __( 'Unauthorized request. Double check your API Key and App Secret and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

        case '404':
          stitchz_social_login_log_debug( sprintf( 'API Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
          return '<div class="messages warning"><ul><li>' . __( 'Not Found. Double check your relaying party URL and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

        case '500':
          stitchz_social_login_log_debug( sprintf( 'API Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
          return '<div class="messages error"><ul><li>' . __( 'Internal Server Error. Ensure the service provider is available and contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
      }
    }
  }
  return FALSE;
}

/**
 * Setup and send an HTTP request to Stitchz to get an authenticated user's social profile.
 *
 * @param string $dnsalias
 *   App url as provided by Stitchz.
 * @param string $appsecret
 *   The application secret provided by Stitchz.
 *
 * @return json|bool
 *   A json encoded response from Stitchz API or FALSE if the call fails.
 */
function stitchz_social_login_authorization_handler_prepare_and_send_v1_request( $dnsalias, $appsecret ) {

  // A POST request is preferred
  $token = ( ! isset( $_POST['token'] ) ? ( ! isset( $_GET['token'] ) ? ( ! isset( $_REQUEST['token'] ) ? 0 : $_REQUEST['token'] ) : $_GET['token'] ) : $_POST['token'] );

  // Check if a valid token response was returned.
  if ( $token !== 0 ) {

	// Remove the trailing slash if found, then build the api end point url.
	$url = rtrim( esc_url_raw( $dnsalias ), '/' ) . '/Authentication/v1/Auth';
	$data = 'Token=' . urlencode( $token ) . '&AppSecret=' . urlencode( $appsecret );

	// Check for a valid $url (api) and return an error if invalid.
    if ( FALSE === stitchz_social_login_check_is_valid_api_url( $url ) ) {
	  return '<div class="messages error"><ul><li>' . __( 'Invalid App Url. Ensure the App Url is correct or contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
    }

	$options = array(
	  'method' => 'POST',
	  'headers' => array(
		'content-type' => 'application/x-www-form-urlencoded',
		'accept' => 'application/json',
	  ),
	  'body' => $data,
	);

	// TODO: we could use CURL here or give a choice of default Wordpress vs. Curl.

	// Send the POST request to the api end point.
	$response = wp_remote_post( $url, $options );

	// Check for a Wordpress error in the response.
	if ( is_wp_error( $response ) ) {
	  if ( class_exists( 'WP_Exception' ) && $response instanceof WP_Exception ) {
		throw $response;
	  }
	  else {
		throw new Exception( $response->get_error_message(), (int) $response->get_error_code() );
	  }
	}

	// Check for a valid response.
	if ( is_array( $response ) && $response['response']['code'] ) {
	  switch ( $response['response']['code'] ) {
		case '200':
		  if ( ! empty( $response['body'] ) ) {
			$json_response = json_decode( $response['body'] );

			return $json_response;
		  }
		  break;

		case '400':
		  stitchz_social_login_log_debug( sprintf( 'V1 Profile Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
		  return '<div class="messages warning"><ul><li>' . __( 'Bad Request. Double check your API Key and App Secret and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

		case '401':
		  stitchz_social_login_log_debug( sprintf( 'V1 Profile Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
		  return '<div class="messages warning"><ul><li>' . __( 'Unauthorized request. Double check your API Key and App Secret and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

		case '404':
		  stitchz_social_login_log_debug( sprintf( 'V1 Profile Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
		  return '<div class="messages warning"><ul><li>' . __( 'Not Found. Double check your relaying party URL and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

		case '500':
		  stitchz_social_login_log_debug( sprintf( 'V1 Profile Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
		  return '<div class="messages error"><ul><li>' . __( 'Internal Server Error. Ensure the service provider is available and contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
	  }
	}
  }
  return FALSE;
}

/**
 * Setup and send an HTTP request to Stitchz to get an authenticated user's social profile.
 *
 * @param string $dnsalias
 *   App url as provided by Stitchz.
 * @param string $apikey
 *   Unique client id provided by Stitchz.
 * @param string $appsecret
 *   The application secret provided by Stitchz.
 * @param string $redirecturi
 *   Callback url where Stitchz will send authenticated response.
 *
 * @return json|bool
 *   A json encoded response from Stitchz API or FALSE if the call fails.
 */
function stitchz_social_login_authorization_handler_prepare_and_send_v2_request( $dnsalias, $apikey, $appsecret, $redirecturi ) {

  // A POST request is preferred
  $token = ( ! isset( $_POST['token'] ) ? ( ! isset( $_GET['token'] ) ? ( ! isset( $_REQUEST['token'] ) ? 0 : $_REQUEST['token'] ) : $_GET['token'] ) : $_POST['token'] );

  // Check if a valid token response was returned.
  if ( $token !== 0 ) {
	// Check that the return url ends with the proper end point url.
	if ( FALSE === strpos( $redirecturi, '/stitchz_social_login/auth' ) ) {
	  $redirecturi .= '/stitchz_social_login/auth';
	}

	// Remove the trailing slash if found, then build the api end point url.
	$url = rtrim( esc_url_raw( $dnsalias ), '/' ) . '/Authentication/v2/Auth';
	$version = 'v2';
	$data = 'client_id=' . urlencode( $apikey ) . '&client_secret=' . urlencode( $appsecret ) . '&grant_type=authorization_code&redirect_uri=' . urlencode( $redirecturi ) . '&code=' . urlencode( $token ) . '&version=' . urlencode( $version ) . '&format=json';

    // Check for a valid $url (api) and return an error if invalid.
    if ( FALSE === stitchz_social_login_check_is_valid_api_url( $url ) ) {
	  return '<div class="messages error"><ul><li>' . __(  'Invalid App Url. Ensure the App Url is correct or contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
    }

	$options = array(
	  'method' => 'POST',
	  'headers' => array(
		'content-type' => 'application/x-www-form-urlencoded',
		'accept' => 'application/json',
	  ),
	  'body' => $data,
	);

	// TODO: we could use CURL here or give a choice of default Wordpress vs. Curl.

	// Send the POST request to the api end point.
	$response = wp_remote_post( $url, $options );

	// Check for a Wordpress error in the response.
	if ( is_wp_error( $response ) ) {
	  if ( class_exists( 'WP_Exception' ) && $response instanceof WP_Exception ) {
		throw $response;
	  }
	  else {
		throw new Exception( $response->get_error_message(), (int) $response->get_error_code() );
	  }
	}

	// Check for a valid response.
	if ( is_array( $response ) && $response['response']['code'] ) {
	  switch ( $response['response']['code'] ) {
		case '200':
		  if ( ! empty( $response['body'] ) ) {
			$json_response = json_decode( $response['body'] );

			return $json_response;
		  }
		  break;

		case '400':
		  stitchz_social_login_log_debug( sprintf( 'V2 Profile Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
		  return '<div class="messages warning"><ul><li>' . __( 'Bad Request. Double check your API Key and App Secret and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

		case '401':
		  stitchz_social_login_log_debug( sprintf( 'V2 Profile Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] )  );
		  return '<div class="messages warning"><ul><li>' . __( 'Unauthorized request. Double check your API Key and App Secret and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

		case '404':
		  stitchz_social_login_log_debug( sprintf( 'V2 Profile Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
		  return '<div class="messages warning"><ul><li>' . __( 'Not Found. Double check your relaying party URL and try again.', 'stitchz_social_login' ) . '</li></ul></div>';

		case '500':
		  stitchz_social_login_log_debug( sprintf( 'V2 Profile Request - %s response code was %s - %s', $url, $response['response']['code'], $response['response']['message'] ) );
        return '<div class="messages error"><ul><li>' . __( 'Internal Server Error. Ensure the service provider is available and contact your service provider.', 'stitchz_social_login' ) . '</li></ul></div>';
	  }
	}
  }
  return FALSE;
}

/**
 * Checks if the given Url is a valid stitchz.net dns alias.
 *
 * @param string $url
 *   The Url to check.
 *
 * @return bool
 *   True if the Url is valid and False if not.
 */
function stitchz_social_login_check_is_valid_api_url( $url ) {
  $arr = parse_url( $url );
  if ( isset( $arr['host'] ) ) {
    $host = $arr['host'];

    // Check that the API end point is valid.
    if ( preg_match( '#^(?:[^.]+\.)*stitchz\.net$#i', $host, $arr ) ) {
      return TRUE;
    }
  }
  return FALSE;
}