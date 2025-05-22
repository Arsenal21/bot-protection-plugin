=== Bot Protection with Turnstile ===
Contributors: mra13, Tips and Tricks HQ
Tags: turnstile, captcha, cloudflare, spam-protection, security
Donate link: https://www.tipsandtricks-hq.com/development-center
Requires at least: 6.5
Requires PHP: 8.0
Tested up to: 6.8
Stable tag: 1.0.2
License: GPLv2 or later

A lightweight plugin that protects core WordPress forms and selected third‑party plugins from spam and bot attacks using Cloudflare Turnstile CAPTCHA.

== Description ==

Bot Protection with Turnstile lets you drop-in Cloudflare's privacy-focused, no-CAPTCHA challenge on the most common attack surfaces of a WordPress site:

* **Core forms** – login, registration, password reset, and comments.
* **Accept Stripe Payments** – protect checkout and payment pop-ups.
* **Simple Download Monitor** – secure download buttons and squeeze forms.

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
1. **Settings page** – add keys and choose forms.
2. **Login form** secured by Turnstile.
3. **Registration form** secured by Turnstile.
4. **Checkout form** inside Accept Stripe Payments.

== Changelog ==

= WIP =
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
