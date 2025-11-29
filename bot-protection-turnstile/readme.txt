=== Bot Protection with Turnstile ===
Contributors: mra13, Tips and Tricks HQ
Tags: turnstile, captcha, cloudflare, spam-protection, security
Donate link: https://www.tipsandtricks-hq.com/development-center
Requires at least: 6.5
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 1.1.1
License: GPLv2 or later

A lightweight plugin that protects core WordPress forms and selected third‑party plugins from spam and bot attacks using Cloudflare Turnstile CAPTCHA.

== Description ==

Bot Protection with Turnstile lets you drop-in Cloudflare's privacy-focused, no-CAPTCHA challenge on the most common attack surfaces of a WordPress site:

* **Core WordPress forms** – Login, registration, password reset, and comment forms.
* **WooCommerce** – Protect checkout, login, registration, and password reset forms.
* **bbPress** – Secure forum registration, login, and topic creation forms.
* **Contact Form 7** – Add Turnstile to your CF7 forms to block spam submissions.
* **Accept Stripe Payments** – Protect checkout and payment pop-up forms.
* **Simple Download Monitor** – Secure download buttons and squeeze forms.
* **Simple Shopping Cart** – Add Turnstile to your shopping cart plugin's manual checkout forms.
* **WP Express Checkout** – Protect checkout 100% discount checkout forms with Turnstile.
* **WP eMember** – Secure registration, login and password reset forms with Turnstile.

Just add your Turnstile *Site Key* and *Secret Key*, choose the forms you want to protect, and you’re done. No more subjecting your users to image puzzles or accessibility headaches.

Turnstile can generate multiple types of non-intrusive challenges to verify users are human, all without showing visitors a puzzle.

### Highlights
* Zero-friction, user-friendly bot protection.
* A free reCAPTCHA alternative for WordPress.
* Works even when visitors are behind ad-blockers or privacy extensions.
* Granular toggles to enable/disable on individual forms.
* Debug logging feature.
* Fully translatable and developer-friendly with action/filter hooks.
* Road-map for upcoming integrations with other popular plugins.

== Getting Started ==
It's quick and easy to get started with the Bot Protection plugin.

* Generate a Site Key and Secret Key in your Cloudflare account, then enter them in the plugin's settings page.
* Choose which forms you want to protect with Turnstile and click Save.
* Turnstile challenges will automatically appear on the selected forms to to block bots and spam.

For more detailed instructions, please see our [setup guide](https://www.tipsandtricks-hq.com/bot-protection-with-turnstile-plugin).

== External Services ==
This plugin integrates with the Cloudflare Turnstile CAPTCHA service to help protect WordPress forms from spam and automated abuse.

When a protected form (such as login, registration, or comment) is displayed, the plugin connects to Cloudflare Turnstile to generate a CAPTCHA challenge. When the form is submitted, Turnstile receives the user's IP address and browser metadata to verify whether the submission is from a human or bot.

This service is provided by Cloudflare, Inc.:
- Terms of Service: https://www.cloudflare.com/terms/
- Privacy Policy: https://www.cloudflare.com/privacypolicy/

== Installation ==
1. Upload the plugin ZIP via **Plugins → Add New → Upload Plugin**, or install it directly from the WordPress.org repository.
2. Activate **Bot Protection with Turnstile** via the **Plugins** menu.
3. Navigate to **Settings → Turnstile**.
4. Enter your **Site Key** and **Secret Key** from the Cloudflare dashboard.
5. Check the boxes for the forms and integrations you wish to protect.
6. Save changes and test a form to confirm the Turnstile widget appears.

== Frequently Asked Questions ==

= Is it free to use? =
Yes, Turnstile CAPTCHA is free to use. You just need a free Cloudflare account to get started. This plugin is also completely free.

= Where do I get a Site Key and Secret Key? =
Sign in to your Cloudflare account, add a Turnstile widget, and copy the credentials provided.

= Is there a setup guide for this plugin? =
Yes, you can view the plugin's setup guide [here](https://www.tipsandtricks-hq.com/bot-protection-with-turnstile-plugin)

= Does this slow down my site? =
No. The Turnstile script is tiny and loaded from Cloudflare's global edge network. It adds a negligible footprint.

= Can I style or reposition the widget? =
Yes – you can choose a theme and widget size in the settings menu.

= I only need it on comments – is that possible? =
Absolutely. Toggle off any forms you don't wish to protect.

== Screenshots ==
1. WordPress login form example.
2. WordPress registration form example.
3. WordPress password reset form example.
4. WordPress comment form example.
5. Checkout form of the Accept Stripe Payments plugin.

== Changelog ==

= 1.1.1 =
* Added support added for WPEC free and manual checkout.

= 1.1.0 =
* Added an appropriate error message that will be shown in the front end when api keys are not set in the settings.

= 1.0.9 =
* Manual Checkout Form support added for WP Express Checkout plugin.
* Turnstile CAPTCHA support added for the WP eMember plugin.

= 1.0.8 =
* Fixed a JS warning.

= 1.0.7 =
* Improved the Simple Download Monitor plugin's integration.

= 1.0.6 =
* Compatibility with the Simple Download Monitor plugin's download via link feature.

= 1.0.5 =
* Contact Form 7 plugin integration added.
* Simple Shopping Cart plugin integration added.
* WP Express Checkout plugin integration added.

= 1.0.4 =
* Added integration for WooCommerce.
* Displays a notice in the settings menu if a supported plugin is inactive.
* Added integration for the bbPress forum plugin.
* Suppresses the Stripe plugin's CAPTCHA-disabled warning when Turnstile is enabled.

= 1.0.3 =
* Added a note for when the captcha is enabled in the settings page of the ASP or SDM plugins.
* Added new CSS code to the admin CSS file.

= 1.0.2 =
* Removed the use of inline <script> tags.
* Added 'External services' section to the readme.txt file.
* Removed the debug logging to a local file inside the plugin folder.

= 1.0.1 =
* Output escaping improvement as per the plugin check report.

= 1.0.0 =
* Initial release version.

== Upgrade Notice ==
None.
