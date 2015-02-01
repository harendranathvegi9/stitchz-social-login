<?php

/**
 * Set up the options fields used to define a complete Stitchz.net profile.
 */
function stitchz_social_login_options_settings() {
  $settings = array();

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_domain',
	'name' => 'domain',
	'desc' => 'The App Url or subdomain of your Stitchz Login application',
	'title' => 'App URL',
	'type' => 'text_domain',
	'maxlength' => 500,
	'default' => 'https://api.stitchz.net/',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_apikey',
	'name' => 'apikey',
	'desc' => 'Your Stitchz Login application apikey',
	'title' => 'ApiKey',
	'type' => 'text_small',
	'maxlength' => 255,
	'default' => '',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_appsecret',
	'name' => 'appsecret',
	'desc' => 'Your Stitchz Login application secret',
	'title' => 'AppSecret',
	'type' => 'text_small',
	'maxlength' => 255,
	'default' => '',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_redirecturl',
	'name' => 'redirecturl',
	'desc' => 'This site&#39;s web address where Stitchz Login will send a response to. The URL is your Wordpress website&#39;s full web address plus the Stitchz Wordpress end point (&#39;/stitchz_social_login/auth&#39;)',
	'title' => 'Return URL',
	'type' => 'text_url',
	'maxlength' => 1000,
	'default' => '',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_version',
	'name' => 'version',
	'desc' => 'The version of api call to authenticate the user',
	'title' => 'API Version',
	'type' => 'select',
	'default' => '2',
	'options' => array (
		'1' => 'Standard Login',
		'2' => 'OAuth 2 Login',
	  ),
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_providers',
	'name' => 'providers',
	'desc' => 'Base64 encoded string of Identity Providers',
	'title' => 'Providers',
	'type' => 'text',
	'default' => '',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_scope',
	'name' => 'scope',
	'desc' => 'OAuth scope as defined in the Stitchz application settings',
	'title' => 'Scope',
	'type' => 'text_small',
	'maxlength' => 255,
	'default' => '',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_theme_version',
	'name' => 'theme_version',
	'desc' => 'The version to display the Social Login icons in',
	'title' => 'Theme Version',
	'type' => 'select',
	'default' => 'Basic',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_enable_user_login_screen',
	'name' => 'enable_user_login_screen',
	'desc' => 'A boolean value indicating whether or not to show social login icons on the user_login form',
	'title' => 'Enable on User_Login Screen?',
	'type' => 'checkbox',
	'default' => '0',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_enable_user_registration_screen',
	'name' => 'enable_user_registration_screen',
	'desc' => 'A boolean value indicating whether or not to show social login icons on the user_registration form',
	'title' => 'Enable on User Registration Screen?',
	'type' => 'checkbox',
	'default' => '0',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_enable_comment_screen',
	'name' => 'enable_comment_screen',
	'desc' => 'A boolean value indicating whether or not to show social login icons on the comments form',
	'title' => 'Enable on Comment Screen?',
	'type' => 'checkbox',
	'default' => '0',
  );

  $settings[] = array(
	'id' => 'stitchz_social_login_settings_notes',
	'name' => 'notes',
	'desc' => 'A short description or note displayed under the social login icons (255 characters or less)',
	'title' => 'Social Login Notes',
	'type' => 'text_small',
	'maxlength' => 255,
	'default' => '',
  );

  return $settings;
}

/**
 * Check if user registration is allowed.
 */
function stitchz_social_login_users_can_register () {
  $opts = get_option( 'users_can_register' );

  if ( $opts === '1' ) { 
	return TRUE;
  }
  else {
	return FALSE;
  }
}