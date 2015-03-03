<?php

/**
 * Adds the Stitchz Configuration Settings form to the nav menu.
 */
function stitchz_social_login_config_menu() {
	$page = add_menu_page( 'Stitchz Social Login ' . __( 'Setup', 'stitchz_social_login' ), 'Stitchz Login API Settings', 'administrator', 'stitchz_social_login_setup', 'stitchz_social_login_config_settings_page' );

	// hook the admin config settings
	add_action( 'admin_init', 'stitchz_social_login_register_settings' );
}
add_action( 'admin_menu', 'stitchz_social_login_config_menu' );

/**
 * Setup and register the settings options group.
 */
function stitchz_social_login_register_settings() {
	// register the plugin settings options
	register_setting( 'stitchz_social_login_settings_group', 'stitchz_social_login_settings', 'stitchz_social_login_settings_sanitize' );
}

/**
 * Sanitize and validate all Stitchz Configuration Settings form values.
 *
 * @param array $settings
 *   Array of fields from the options form.
 *
 * @return array
 *   A validated and sanitized collection of field values.
 */
function stitchz_social_login_settings_sanitize( $settings ) {

	$config = get_option( 'stitchz_social_login_settings' );

	if ( ! is_array( $config ) ) {
	  $config = array();
	}

	$options = stitchz_social_login_options_settings();

	foreach ( $options as $option ) {
	  $value = trim( $settings[ $option['name'] ] );
	  switch ( $option['type'] ) {
		case 'text_domain':
		  if ( stitchz_social_login_check_is_valid_api_url( $value ) ) {
			$config[ $option['name'] ] = esc_url_raw( $value );
		  } else {
			add_settings_error( $option['name'], esc_attr( 'stitchz_social_login_invalid_domain' ), sprintf( __( 'An invalid App URL was found, the Url must be a Stitchz.net Url. A valid Stitchz.net Url can be obtained at %s', 'stitchz_social_login' ), '<a href="https://login.stitchz.net/">https://login.stitchz.net</a>' ) );
		  }
		break;

		case 'text_small':
		  $value = ( strlen( $value ) > 255 ) ? substr( $value,0,255 ) : $value;
		  $config[ $option['name'] ] = sanitize_text_field( $value );
		break;

		case 'text_url':
		  $value = ( strlen( $value ) > 1000) ? substr( $value,0,1000 ) : $value;
		  $config[ $option['name'] ] = esc_url_raw( $value );
		break;

		case 'select':
		  $config[ $option['name'] ] = sanitize_text_field( $value );
		break;

		case 'text':
		  $config[ $option['name'] ] = sanitize_text_field( $value );
		break;

		case 'checkbox':
		  $config[ $option['name'] ] = sanitize_text_field( $value );
		break;

		default:
		  $config[ $option['name'] ] = sanitize_text_field( $value );
		break;

	  }
    }
	return $config;
}

/**
 * Build and display the Stitchz Configuration Settings form page.
 */
function stitchz_social_login_config_settings_page() {
	if ( ! current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'stitchz_social_login' ) );
	}

	// hook the ajax javascript into the page
	add_action( 'admin_footer', 'stitchz_social_login_admin_ajax' );

	?>
	<div class="wrap">
	  <h2><?php _e( 'Stitchz Social Login API Settings', 'stitchz_social_login' ); ?></h2>
	  <form method="post" action="options.php">
	  <?php
	   settings_errors();
	   settings_fields( 'stitchz_social_login_settings_group' );
	   $settings = get_option( 'stitchz_social_login_settings' );

	   $domain = esc_url_raw( isset( $settings['domain'] ) ? $settings['domain'] : __( 'https://api.stitchz.net/', 'stitchz_social_login' ) );
	   $apikey = sanitize_text_field( isset( $settings['apikey'] ) ? $settings['apikey'] : '' );
	   $appsecret = sanitize_text_field( isset ( $settings['appsecret'] ) ? $settings['appsecret'] : '' );
	   $redirecturl = esc_url_raw( isset( $settings['redirecturl'] ) ? $settings['redirecturl'] : get_site_url() . '/stitchz_social_login/auth' );
	   $version = sanitize_text_field( isset( $settings['version'] ) ? $settings['version'] : '2' );
	   $providers = sanitize_text_field( isset( $settings['providers'] ) ? $settings['providers'] : '' );
	   $scope = sanitize_text_field( isset( $settings['scope'] ) ? $settings['scope'] : '');
	   $theme_version = sanitize_text_field( isset( $settings['theme_version'] ) ? $settings['theme_version'] : 'Basic' );
	   $enable_user_login_screen = sanitize_text_field( isset( $settings['enable_user_login_screen'] ) ? $settings['enable_user_login_screen'] : '0' );
	   $enable_user_registration_screen = sanitize_text_field( isset( $settings['enable_user_registration_screen'] ) ? $settings['enable_user_registration_screen'] : '0' );
	   $enable_comment_screen = sanitize_text_field( isset( $settings['enable_comment_screen'] ) ? $settings['enable_comment_screen'] : '0' );
	   $notes = sanitize_text_field( ( isset( $settings['notes'] ) ? $settings['notes'] : '' ), 'filtered_html' );
	   ?>
	    <div><?php printf( __( 'Connect your Wordpress site with Stitchz Login by completing the fields below, sync your providers list, then click Save. The below information should be copied directly from your %s', 'stitchz_social_login' ), '<a href="https://login.stitchz.net/">Stitczh Login Application Settings</a>' ); ?>
	    </div>
	    <table class="form-table">
	      <tr class="form-field form-required">
	        <th scope="row">
	          <label for="stitchz_social_login_settings[domain]"><?php  _e( 'App URL', 'stitchz_social_login' ); ?> <span class="description">(<?php  _e( 'required', 'stitchz_social_login' ); ?>)</span></label>
	        </th>
	        <td>
	          <input type="text" name="stitchz_social_login_settings[domain]" id="stitchz_social_login_settings_domain" size="90" value="<?php echo $domain ?>" />
	          <div class="stitchz_social_login_description"><?php  _e( 'The App Url or subdomain of your Stitchz Login application', 'stitchz_social_login' ); ?>
	          </div>
	        </td>
	      </tr>
	      <tr class="form-field form-required">
	        <th scope="row">
	          <label for="stitchz_social_login_settings[apikey]"><?php  _e( 'ApiKey', 'stitchz_social_login' ); ?> <span class="description">(<?php  _e( 'required', 'stitchz_social_login' ); ?>)</span></label>
	        </th>
	        <td>
	          <input type="text" name="stitchz_social_login_settings[apikey]" id="stitchz_social_login_settings_apikey" size="90" value="<?php echo $apikey ?>" placeholder="000000000000" />
	          <div class="stitchz_social_login_description"><?php _e( 'Your Stitchz Login application apikey', 'stitchz_social_login' ); ?>
	          </div>
	        </td>
	      </tr>
	      <tr class="form-field form-required">
	        <th scope="row">
	          <label for="stitchz_social_login_settings[appsecret]"><?php _e( 'AppSecret', 'stitchz_social_login' ); ?> <span class="description">(<?php _e( 'required', 'stitchz_social_login' ); ?>)</span></label>
	        </th>
	        <td>
	          <input type="text" name="stitchz_social_login_settings[appsecret]" id="stitchz_social_login_settings_appsecret" size="90" value="<?php echo $appsecret ?>" placeholder="000000000000" />
	          <div class="stitchz_social_login_description"><?php _e( 'Your Stitchz Login application secret', 'stitchz_social_login' ); ?>
	          </div>
	        </td>
	      </tr>
	      <tr class="form-field form-required">
	        <th scope="row">
	          <label for="stitchz_social_login_settings[redirecturl]"><?php _e( 'Return URL', 'stitchz_social_login' ); ?> <span class="description">(<?php _e( 'required', 'stitchz_social_login' ); ?>)</span></label>
	        </th>
	        <td>
	          <input type="text" name="stitchz_social_login_settings[redirecturl]" id="stitchz_social_login_settings_redirecturl" size="90" value="<?php echo $redirecturl ?>" placeholder="&lt;your wordpress url&gt;" />
	          <div class="stitchz_social_login_description"><?php printf( __( 'This site&#39;s web address where Stitchz Login will send a response to. The URL is your Wordpress website&#39;s full web address plus the Stitchz Wordpress end point (&#39;/stitchz_social_login/auth&#39;), i.e. %s', 'stitchz_social_login' ), get_site_url() . '/stitchz_social_login/auth' ); ?>
	          </div>
	        </td>
	      </tr>
	      <tr class="form-field">
	        <th scope="row">
	          <label for="stitchz_social_login_settings[version]"><?php _e( 'API Version', 'stitchz_social_login' ); ?></label>
	        </th>
	        <td>
	          <select name="stitchz_social_login_settings[version]" id="stitchz_social_login_settings_version">
	            <option value="1"<?php echo ( $version === '1' ? ' selected="selected"' : '' ); ?>><?php _e( 'Standard Login', 'stitchz_social_login' ); ?></option>
	            <option value="2"<?php echo ( $version === '2' ? ' selected="selected"' : '' ); ?>><?php _e( 'OAuth 2 Login', 'stitchz_social_login' ); ?></option>
	          </select>
	          <div class="stitchz_social_login_description"><?php _e( 'The version of api call to authenticate the user', 'stitchz_social_login' ); ?>
	          </div>
	        </td>
	      </tr>
	    </table>

	    <h2><?php _e( 'Stitchz Login Provider List', 'stitchz_social_login' ); ?></h2>
	    <div><?php _e( 'After your configuration settings have been set, click the button below to test your settings and sync your identity providers from your Stitchz Login application here.', 'stitchz_social_login' ); ?></div>
	    <table class="form-table">
	      <tr class="form-field">
	        <td>
	          <input type="button" class="button" value="<?php _e( 'Sync Providers', 'stitchz_social_login' ); ?>" id="stitchz_social_login_sync_providers" />
	        </td>
	        <td>
	          <input type="button" class="button" value="<?php _e( 'Clear Providers', 'stitchz_social_login' ); ?>" id="stitchz_social_login_clear_providers" />
              <input type="hidden" name="stitchz_social_login_settings[providers]" id="stitchz_social_login_settings_providers" value="<?php echo $providers ?>" />
              <input type="hidden" name="stitchz_social_login_settings[scope]" id="stitchz_social_login_settings_scope" value="<?php echo $scope ?>" />
            </td>
          </tr>
	      <tr>
	        <td scope="row" colspan="2">
	          <div id="stitchz_social_login_provider_list_block">
                <?php $providers_list = stitchz_social_login_format_provider_list( $domain, stitchz_social_login_decode_json( $providers ), $version, $apikey, $redirecturl, $scope, $notes ); echo $providers_list ?>
              </div>
            </td>
          </tr>
	    </table>

	    <h2><?php _e( 'Stitchz Login Addin Settings' , 'stitchz_social_login' ); ?></h2>
	    <div></div>
	    <table class="form-table">
	      <tr class="form-field">
	        <th scope="row">
	          <label for="stitchz_social_login_settings[theme_version]"><?php _e( 'Theme Version' , 'stitchz_social_login' ); ?></label>
	        </th>
	        <td>
	          <select name="stitchz_social_login_settings[theme_version]" id="stitchz_social_login_settings_theme_version">
	            <option value="<?php echo $theme_version ?>"<?php echo ( $theme_version === 'Basic' ? ' selected="selected"' : '' ); ?>><?php _e( 'Basic' , 'stitchz_social_login' ); ?></option>
	          </select>
	          <div class="stitchz_social_login_description"><?php _e( 'The version to display the Social Login icons in', 'stitchz_social_login' ); ?>
	          </div>
	        </td>
	      </tr>
	      <tr class="form-field">
	        <th scope="row">
	          <label for="stitchz_social_login_settings[enable_user_login_screen]"><?php _e( 'Enable on User_Login Screen', 'stitchz_social_login' ); ?>?</label>
	        </th>
	        <td>
	          <input type="checkbox" value="1"<?php echo ( $enable_user_login_screen === '1' ? ' checked="checked"' : '' ); ?> name="stitchz_social_login_settings[enable_user_login_screen]" id="stitchz_social_login_settings_enable_user_login_screen" />
	          <div class="stitchz_social_login_description"><?php _e( 'A boolean value indicating whether or not to show social login icons on the user_login form', 'stitchz_social_login' ); ?>
	          </div>
	        </td>
	      </tr>
          <tr class="form-field">
            <th scope="row">
              <label for="stitchz_social_login_settings[enable_user_registration_screen]"><?php _e( 'Enable on User Registration Screen', 'stitchz_social_login' ); ?>?</label>
            </th>
            <td>
              <input type="checkbox" value="1"<?php echo ( $enable_user_registration_screen === '1' ? '  checked="checked"' : ' ' ); ?> name="stitchz_social_login_settings[enable_user_registration_screen]" id="stitchz_social_login_settings_enable_user_registration_screen" />
              <div class="stitchz_social_login_description"><?php _e( 'A boolean value indicating whether or not to show social login icons on the user registration form', 'stitchz_social_login' ); ?>
              </div>
            </td>
          </tr>
          <tr class="form-field">
            <th scope="row">
              <label for="stitchz_social_login_settings[enable_comment_screen]"><?php _e( 'Enable on Comments Screen', 'stitchz_social_login' ); ?>?</label>
            </th>
            <td>
              <input type="checkbox" value="1"<?php echo ( $enable_comment_screen === '1' ? ' checked="checked"' : '' ); ?> name="stitchz_social_login_settings[enable_comment_screen]" id="stitchz_social_login_settings_enable_comment_screen" />
              <div class="stitchz_social_login_description"><?php _e( 'A boolean value indicating whether or not to show social login icons on the comments form', 'stitchz_social_login' ); ?>
              </div>
            </td>
          </tr>
	      <tr class="form-field">
	        <th scope="row">
	          <label for="stitchz_social_login_settings[notes]"><?php _e( 'Social Login Notes', 'stitchz_social_login' ); ?></label>
	        </th>
	        <td>
	          <input type="text" size="90" value="<?php echo $notes ?>" name="stitchz_social_login_settings[notes]" />
	          <div class="stitchz_social_login_description"><?php _e( 'A short description or note displayed under the social login icons (255 characters or less)', 'stitchz_social_login' ); ?>
	          </div>
	        </td>
	      </tr>
	    </table>

	    <p class="submit">
	      <input type="submit" value="<?php _e( 'Save', 'stitchz_social_login' ); ?>" class="button button-primary" />
	      <input type="hidden" name="page" value="settings" />
	    </p>
	  </form>
	  <div>Stitchz Social Login Version: <?php echo ( defined( 'STITCHZ_SOCIAL_LOGIN_VERSION' ) ? STITCHZ_SOCIAL_LOGIN_VERSION : '' ); ?>
      </div>
	</div>

	<?php
}

/**
 * Sends an HTTP request to get a list of associated providers.
 */
function stitchz_social_login_admin_provider_list() {
  if ( ! wp_verify_nonce( $_REQUEST['nonce'], "stitchz_social_login_provider_list_nonce" ) ) {
    exit( __('Invalid token received, log out then back in and try again.', 'stitchz_social_login' ) );
  }

  try {
	$dnsalias = ( ! empty( $_POST['domain'] ) ? $_POST['domain'] : 'https://api.stitchz.net/' );
	$apikey = ( ! empty( $_POST['apikey'] ) ? $_POST['apikey'] : '0000000000' );
	$appsecret = ( ! empty( $_POST['appsecret'] ) ? $_POST['appsecret'] : '0000000000' );
	$version = ( ! empty( $_POST['version'] ) ? $_POST['version'] : '' );
	$redirecturl = ( ! empty( $_POST['redirecturl'] ) ? $_POST['redirecturl'] : '' );
	$dnsalias = rtrim( $dnsalias, '/' ) . '/';
	$url = $dnsalias . 'api/v2/providers';
	$no_format = ( ! empty( $_REQUEST['noformat'] ) ? TRUE : FALSE );

	// Get a valid access token.
	$access_token = stitchz_social_login_get_access_token( $dnsalias, $apikey, $appsecret );

	if ( ! empty( $access_token ) ) {
	  // Check for error response.
	  if ( FALSE !== strpos( $access_token, '<div' ) ) {
		echo $access_token;
	  }

	  // Send a generic API request.
	  $json_providers = stitchz_social_login_get_api_call( $url, $access_token );

	  if ( ! is_object( $json_providers ) ) {
		// Check for error response.
		if ( FALSE !== strpos( $json_providers, '<div' ) ) {
		  echo $json_providers;
		}
	  } else {
		$providers = $json_providers->Providers;
		$scope = $json_providers->Scope;
		$notes = '';

		if ( FALSE === $no_format ) {
		  $provider_html = stitchz_social_login_format_provider_list( $dnsalias, $providers, $version, $apikey, $redirecturl, $scope, $notes );

		  stitchz_social_login_save_provider_list( $providers, $scope );

		  echo $provider_html;
	    } else {
		  echo stitchz_social_login_encode_json( $providers );
		}
	  }
	}
   }
   catch ( WP_Exception $exception ) {
		stitchz_social_login_log_debug( $exception->get_error_message() );
        echo $exception->get_error_message();
    }
}

/**
 * Transforms a json array into a base64 encoded string.
 *
 * @param array $providers
 *   A json array of identity providers.
 *
 * @return string|bool
 *   A base64 encoded string or FALSE if array is empty.
 */
function stitchz_social_login_encode_json( array $providers ) {
  if ( ! empty( $providers ) ) {
    return base64_encode( json_encode( $providers ) );
  }
  return FALSE;
}

/**
 * Transforms a base64 encoded string into a json array.
 *
 * @param string $providers
 *   A base64 encoded string.
 *
 * @return array
 *   A json array of identity providers.
 */
function stitchz_social_login_decode_json( $providers ) {
  if ( ! empty( $providers ) ) {
    // TODO: check for valid base64 encoded string
    return json_decode( base64_decode( $providers ) );
  } else {
    return array();
  }
}

/**
 * Saves the list of providers and scope to the options.
 *
 * @param array $providers
 *   A json array of Identity Providers from Stitchz.net.
 *
 * @param string $scope
 *   The Stitchz.net assigned scope.
 *
 * @return void
 */
function stitchz_social_login_save_provider_list( array $providers, $scope ) {
  $config = get_option( 'stitchz_social_login_settings' );

  if ( ! is_array ( $config ) ) {
    $config = array();
  }

  $config['providers'] = stitchz_social_login_encode_json( $providers );
  $config['scope'] = $scope;

  update_option( 'stitchz_social_login_settings', $config );
}

/**
 * Writes the ajax javascript to the html output.
 */
function stitchz_social_login_admin_ajax() {
 $nonce = wp_create_nonce( "stitchz_social_login_provider_list_nonce" );
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {
  $('td').on('click', 'input#stitchz_social_login_sync_providers', function() {
	var data = {
	  'action': 'stitchz_social_login_provider_list',
	  'nonce' : '<?php echo $nonce ?>',
	  'domain': $('input#stitchz_social_login_settings_domain').val(),
	  'apikey' : $('input#stitchz_social_login_settings_apikey').val(),
	  'appsecret' : $('input#stitchz_social_login_settings_appsecret').val(),
	  'version' : $('input#stitchz_social_login_settings_version').val(),
	  'redirecturl' : $('input#stitchz_social_login_settings_redirecturl').val(),
	};

	$.post(ajaxurl, data, function(response) {
	  $('div#stitchz_social_login_provider_list_block').empty();
	  $('div#stitchz_social_login_provider_list_block').html(response);
	});

	$.post(ajaxurl + '?noformat=true', data, function(response) {
	  $('input:hidden#stitchz_social_login_settings_providers').val(response);
	});
  });

  $('td').on('click', 'input#stitchz_social_login_clear_providers', function() {
	$('input:hidden#stitchz_social_login_settings_providers').val('');
	$('div#stitchz_social_login_provider_list_block').empty();
  });
});
</script>
<?php
}
