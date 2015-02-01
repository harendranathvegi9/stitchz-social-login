=== Stitchz Social Login ===
Contributors: stitchzdotnet
Link: http://www.stitchz.net/
Tags: user authentication, social login
Requires at least: 3.9.2
Tested up to: 3.9
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The Stitchz Social Login module adds the option to authenticate with one or
more of the 20+ social identities providers supported by Stitchz.

== Description ==

The Stitchz Social Login plugin extends the standard WordPress user registration 
and login experience by integrating social login features. With Stitchz Social 
Login users can login with one or more supported social networks, including 
Facebook, Twitter, Google, LinkedIn and more (20+).

Stitchz Social Login provides a single, simple interface that maintains all
your social identity provider information safely and securely (and encrypted
while at rest). Using Stitchz saves time and eliminates custom code necessary to 
integrate and manage multiple identity providers.

Any user account can connect one or more social identities to their account
and use them to login in with (before or after their account is created).

For a full description of the module, visit the project page:
  http://www.stitchz.net/Wordpress

To submit bug reports and feature suggestions, or to track changes:
  http://stitchz.uservoice.com/forums/81839?lang=en


== REQUIREMENTS ==

None.


== Installation ==

* Stitchz Social Login plugin can be downloaded and added as a plugin to any
  existing Wordpress installation. The plugin can be downloaded from:
  http://www.stitchz.net/Wordpress

* Detailed installation steps can be found in the INSTALL.txt file included
  with this release. 


==- CONFIGURATION ==

* Configure Stitchz Login API Settings in the Wordpress Admin Counsel » Stitchz 
  Login API Settings:

  - Configure your Wordpress instance to connect to the Stitchz Social Login API

    You must setup your application at https://login.stitchz.net/ before 
    continuing.

    Once your application is created and setup at https://login.stitchz.net, 
    copy your App Url, ApiKey, AppSecret, and Return Url into the appropriate 
    fields in the Stitchz Social Login API Settings form. All fields with 
    "(required)" are required.

    The API Version field determines how users' authentication requests are 
    sent to Stitchz. The "Standard Login" option is a basic authentication 
    request used to only authenticate a user. The "OAuth 2 Login" option sends
    an OAuth 2.0 authenticated request to Stitchz and returns a valid OAuth 2.0 
    token that can be used to request further resources without forcing the end
    users to re-authenticate. "OAuth 2 Login" requires HTTPS.

    Confirm your Stitchz Login API Settings before moving on to the next 
    section.

    Note: Your "Return Url" is your Wordpress website's full web address plus the 
    Stitchz Wordpress end point ('/stitchz_social_login/auth'), i.e. 
    https://www.yourwebsiteaddress.com/stitchz_social_login/auth

  - Stitchz Login Provider List

    After configuring your Stitchz Login API Settings you must pull/request 
    your already configured Social Login Identity Providers from Stitchz. 
    Click the "Sync Providers" button to synchronize your provider list 
    configured in your Stitchz application with your Wordpress instance. 

    If all settings are correct a sample login will display with all your 
    configured and active identity providers. If and error message appears
    or no sample login is displayed double check your Stitchz Login API 
    Settings. If all settings are correct check the FAQs below for additional
    help or support@stitchz.net.

  - Stitchz Login Addin Settings

    The Stitchz Login Addin Settings control where the social login links will
    be displayed. By default all login screens will display the social login
    links until they are disabled here. Simply remove the tick in the checkbox
    to prevent it from displaying.

    The "Theme Version" field currently only has one option to choose. Future 
    releases will include additional options.

    The "Social Login Notes" field can contain up to 255 letters or numbers and
    will be displayed under the social login links. This can be used to present
    a message to the users before log in.

  - Save

    After all settings have been set and confirmed, click Save to save your
    settings to the database. 


== USAGE ==

* To login with any identity provider, click the provider link on the login
  page. The browser will be redirected to the identity provider's login page 
  followed by (typically) a permissions/scope confirmation page. After a 
  successful login the browser will be redirected back to the Wordpress website. 

* Any user account can connect one or more social identities to their account.
  The Connected Identities section on the user profile page lists all social 
  identities associated with the user. Identities can be removed by clicking
  the "X" next to the provider name, or added by click the provider name. 


== TROUBLESHOOTING ==

* If the sample login links do not display on the Stitchz Login API Settings
  page, check the following:

  - Check that the App Url, ApiKey and AppSecret are entered exactly as 
    displayed in your Stitchz application page at: https://login.stitchz.net/

  - Confirm your settings, click Save and try again. 

  - Go to your Stitchz application and confirm the Return Url matches your
    website address plus the required Stitchz end point in Wordpress, i.e. 
    'http://www.YourWebsiteAddress.com/stitchz_social_login/auth'


== Frequently Asked Questions ==

Q: Where are the FAQs?

A: They're coming soon.


== Changelog ==

  = 1.0 =
  * 1st version

== Upgrade Notice ==

  = 1.0 =
  * 1st version

== Screenshots ==

  1. Stitchz Social Login Settings - http://www.stitchz.net/Content/images/stitchz_social_login_wordpress_settings.png

== CONTACT ==

Current maintainer(s):
* Stitchzdotnet (Ethan Peterson) - @stichzdotnet on Twitter
