=== Stitchz Social Login ===
Contributors: stitchzdotnet
Link: http://www.stitchz.net/
Tags: user authentication, social login, facebook, google, twitter, single sign-on, linkedin, social authentication, sso, vk, plugin, widget, yahoo, microsoft live, paypal, google plus, open id
Requires at least: 3.9.2
Tested up to: 3.9
Stable tag: 1.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The Stitchz Social Login plugin adds the option to authenticate with one or
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

= Supported Providers =
- Facebook
- Twitter
- Google
- Google Plus
- LinkedIn
- Tumblr
- Box
- Paypal
- Yahoo
- Dropbox
- OpenID
- Instagram
- VK (Vkontakte)
- Foursquare
- Windows Live
- SoundCloud
- Discogs
- Flickr
- SalesForce.com
- more...

For additional details of the plugin, visit the project page:
  http://www.stitchz.net/Wordpress

To submit bug reports and feature suggestions, or to track changes:
  http://stitchz.uservoice.com/forums/81839?lang=en


== Installation ==
= General Installation =
Stitchz Social Login plugin can be downloaded and added as a plugin to any existing Wordpress installation. The plugin can be downloaded from: http://www.stitchz.net/Wordpress

1. Login to Wordpress as an Administrator.

2. Go to the Wordpress Admin counsel, then click "Plugins".

3. Click "Add New".

4. On the Add New page, click "Upload".

5. Click "Browse" and navigate to where the stitchz_social_login.zip file was saved to and click open.

6. Click "Install Now" to install the plugin.


= Configuration =
Before using Stitchz Social Login, you must setup an application at: <a href="https://login.stitchz.net/">https://login.stitchz.net/</a>. Once you application has been setup, continue with
the steps below.

1.  Login to your Wordpress site as an Administrator, and go to the Wordpress Admin counsel.

2.  Click "Settings > Permalinks"

3.  Change the default way WordPress handles web URLs. Under "Common Settings", select any option except "Default", and click "Save Changes". This will modify the .htaccess rewrite rules.

4.  Next, click "Plugins".

5.  Navigate to the Stitchz Social Login plugin in the plugins list and click "Activate".

6.  Then, click "Configure".

7.  Copy your App Url, ApiKey, AppSecret, and Return Url from your Stitch application into the appropriate fields in the Stitchz Social Login API Settings form. All fields with "(required)" are, you guessed it, required.

    The "Return Url" is your Wordpress website's full web address plus the Stitchz Wordpress end point ('/stitchz_social_login/auth'), i.e. https://www.yourwebsiteaddress.com/stitchz_social_login/auth

8.  Next, select the API Version to run. The "Standard Login" option is a basic authentication request used to only authenticate a user. The "OAuth 2 Login" option sends an OAuth 2.0 authenticated request to the Stitchz API and returns a valid OAuth 2.0 token. The token can be used later to make authorized requests to Stitchz.

9.  Confirm your Stitchz Login API Settings before moving on to the next section.

10. Click the "Sync Providers" button to synchronize your provider list configured in your Stitchz application with your Wordpress instance. If all settings are correct a sample login will display with all your configured and active identity providers. Check the README.txt file if any errors occur.

11. Enable/disable any login forms where the Stitchz Social Login links are displayed. By default the links are visible on all available forms.

12. The "Social Login Notes" field can contain up to 255 letters or numbers in plain text and will be displayed under the social login links.

13. Click Save.

== USAGE ==

* To login with any identity provider, click the provider link on the login page. The browser will be redirected to the identity provider's login page followed by (typically) a permissions/scope confirmation page. After a successful login the browser will be redirected back to the Wordpress website. 

* Any user account can connect one or more social identities to their account. The Connected Identities section on the user profile page lists all social identities associated with the user. Identities can be removed by clicking the "X" next to the provider name, or added by click the provider name. 

* Stitchz Login Shortcode can be used on any page/post by using the following: [stitchz_social_login_shortcode]

== REQUIREMENTS ==

None.


== Frequently Asked Questions ==
= If the sample login links do not display on the Stitchz Login API Settings page, check the following: =
- Check that the App Url, ApiKey and AppSecret are entered exactly as displayed in your Stitchz application page at: <a href="https://login.stitchz.net/">https://login.stitchz.net/</a>

- Confirm your settings, click Save and try again. 

- Go to your Stitchz application and confirm the Return Url matches your website address plus the required Stitchz end point in Wordpress, i.e. 'http://www.YourWebsiteAddress.com/stitchz_social_login/auth'


== Changelog ==

  = 1.0.1 =
  * Added shortcode code

  = 1.0 =
  * 1st version

== Upgrade Notice ==

  = 1.0 =
  * 1st version

  = 1.0.1 =
  * Added shortcode code

== Screenshots ==
1. **Stitchz Social Login Settings**: The Stitchz Social Login Settings Screen

== CONTACT ==

Current maintainer(s):
* Stitchzdotnet (Ethan Peterson) - <a href="http://www.twitter.com/stitchzdotnet">@stichzdotnet</a> on Twitter
