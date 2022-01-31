=== WP Force SSL & HTTPS SSL Redirect ===
Contributors: WebFactory
Tags: ssl, force ssl, add ssl, install ssl, https, ssl certificate, ssl redirect, mixed content, hsts, lets encrypt, generate ssl certificate
Requires at least: 4.6
Tested up to: 5.9
Requires PHP: 5.2
Stable Tag: 1.65
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable SSL & HTTPS redirect with 1 click! Add SSL certificate & WP Force SSL to redirect site from HTTP to HTTPS & fix SSL errors.

== Description ==

<a href="https://wpforcessl.com/?ref=wporg">WP Force SSL</a> helps you redirect insecure HTTP traffic to secure HTTPS and fix SSL errors **without touching any code**. Activate Force SSL and everything will be set and SSL enabled. The entire site will move to HTTPS using your SSL certificate. It works with any SSL certificate. It can be free SSL certificate from Let's Encrypt or a paid SSL certificate.

How to add SSL & enable SSL? Most hosting companies support the free SSL certificate from Let's Encrypt, so login to your hosting panel and add SSL certificate. You'll see a button labeled "Add SSL Certificate" or "Add Let's Encrypt Certificate" and after that it's 1 click to have the SSL enabled on your site with WP Force SSL. If that doesn't work get <a href="https://wpforcessl.com/">WP Force SSL PRO</a> and it'll generate free SSL certificate for your site. And will regenerate SSL certificate every 90 days.

Access WP Force SSL settings via the main Settings menu -> WP Force SSL.

= SSL Tests available in the plugin =
* is site on localhost?
* check SSL certificate
* check SSL certificate expiry date
* is latest version of Force SSL used?
* are known incompatible SSL plugins active?
* is WP address URL set for SSL?
* is WP home URL set for SSL?
* is SSL monitoring enabled (pro feature)
* is HTTPS redirection working?
* is file redirection working (pro feature)
* is HSTS enabled?
* check mixed-content issue (pro feature)
* is htaccess available & writable?
* is 404 redirection enabled (pro feature)

= Settings =
* redirect HTTP to HTTPS
* fix mixed-content (pro)
* enable HSTS
* force secure cookies (pro)
* cross-site scripting protection (pro)
* expect CT header
* X-Frame options
* show WP Force SSL menu in admin bar
* show WP Force SSL widget in admin dashboard

= SSL certificate testing tool =
WP Force SSL comes with an SSL certificate testing tool. It tests if the SSL certificate is valid, properly installed & up-to date.

= Need support? =
We're here for you! Things get frustrating when they don't work so make sure you <a href="https://wordpress.org/support/plugin/wp-force-ssl/">open a support topic</a> in the official Force SSL forum. We answer all questions within a few hours!

= External Assets =
A big thank you to <a href="https://sweetalert2.github.io/">SweetAlert2</a> authors which we use to make alerts nicer. And to <a href="https://depositphotos.com/248496280/stock-illustration-online-payment-protection-system-concept.html">DepositPhotos</a> for the lovely header image.


== Frequently Asked Questions ==

= Who is this plugin for? =
For anyone who has an SSL certificate installed on their site and wants to be sure all content is accessed (and redirected if needed) via a secure connection.

= Do I need an SSL certificate for this to work? =
Yes, you do need an SSL certificate. If you don't have one you can buy <a href="https://wpforcessl.com/">WP Force SSL PRO</a> which will generate an SSL certificate for free.

= After activating WP Force SSL, do I need to do anything else? =
No, nothing. After activating WP Force SSL, the main option "redirect SSL" will already be active. What you can do is use our tests to make sure everything related to SSL is working.

== Screenshots ==

1. Built-in tests verify your SSL configuration
2. SSL settings
3. SSL certificate tester

== Installation ==

1. Open Plugins - Add New in WP admin and search for "WP Force SSL"
2. Install and activate the plugin
3. Open plugin settings via Settings - WP Force SSL
4. Check provided tests and settings


== Changelog ==

= v1.65 =
- 2022/01/12
- added admin Dashboard widget

= v1.60 =
- 2021/12/28
- new GUI & features
- PRO version available

= v1.57 =
- 2021/01/30
- added flyout menu

= v1.56 =
- 2020/09/30
- minor bug fixes
- added promo for WP 301 Redirects PRO
- 602,200 downloads; 100,000 installations

= v1.55 =
- 2020/01/22
- minor bug fixes
- 384,400 downloads; 90,000 installations

= v1.5 =
- 2019/09/23
- complete rewrite of entire WP Force SSL plugin
- added support for HSTS
- 288,290 downloads; 80,000 installations

= v1.4 =
- Changed function naming to avoid conflicts reported by users.

= v1.3 =
- Dropping support for PHP 5.3: Only 15.9% of the people that use WordPress use PHP 5.3, it reached end of life and you should ask your host to upgrade.

= v1.2.1 =
- Fixed an issue where some users were getting a error message for no valid header when activating the plugin.

= v1.2 =
- Dropping support for PHP 5.2: Only 5.7% of the people that use WordPress use PHP 5.2, it's old, buggy, and insecure.

= v0.1 =
- 2015/01/15
- initial release
