=== ArrayCodes Order Notifications for WooCommerce ===
Contributors: arraycodes, heitor_tito
Donate link: https://array.codes
Tags: notifications, whatsapp, woocommerce, order, sms
Requires at least: 6.2
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Send WhatsApp notifications to customers and sellers for new WooCommerce orders.

== Description ==

**ArrayCodes Order Notifications for WooCommerce** connects your WooCommerce store to the WhatsApp Business API (Meta) and automatically sends order notifications via WhatsApp.

**Includes:**

* New order notification for the **customer**
* New order notification for the **seller**
* **Receive messages** — view incoming WhatsApp conversations from your admin panel

**Need advanced features?** Array.codes also offers a full-featured PRO plugin on the WooCommerce Marketplace with order status update notifications, unpaid order reminders, abandoned cart recovery, back-in-stock and out-of-stock alerts, review request notifications, order details on demand, auto-reply for received messages, checkout opt-in field, and more.

<a href="https://woocommerce.com/products/notifications-with-whatsapp/" target="_blank">Check out the PRO version on the WooCommerce Marketplace →</a>

== Installation ==

= Requirements =

This plugin requires a **Meta (Facebook) Developer account** and access to the **WhatsApp Business Cloud API**. You will need the following credentials from your Meta App before you can send notifications:

* **Access Token** — a permanent system user token with `whatsapp_business_messaging` and `whatsapp_business_management` permissions
* **Phone Number ID** — the ID of the WhatsApp phone number registered in your Meta App
* **WhatsApp Business Account ID** — your Meta Business Account ID
* **App Secret** — found in your Meta App dashboard under **App Settings → Basic**. Required to verify incoming webhook payloads.

To obtain these credentials, follow the official Meta guide: https://developers.facebook.com/docs/whatsapp/cloud-api/get-started

= Plugin Installation =

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install directly through the WordPress plugin screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **WooCommerce → ArrayCodes Order Notifications** to begin setup.
4. On the **Connection** tab, enter your Meta App credentials, including the **App Secret** (App Settings → Basic in your Meta App dashboard).
5. Copy the **Webhook URL** shown in the plugin and register it in your Meta App under WhatsApp → Configuration → Webhook.
6. Save your settings and use the built-in **debug tools** to verify that the connection is working.

For full setup documentation, visit <a href="https://woocommerce.com/document/notifications-with-whatsapp/" target="_blank">notifications-with-whatsapp documentation</a>.

== External Services ==

This plugin connects to the **Meta (Facebook) Graph API** to deliver WhatsApp Business messages and to manage message templates. No data is sent to any service provided by Array.codes or any other third party beyond Meta.

= Meta (Facebook) Graph API =

**What it is:** The official Meta Graph API (`graph.facebook.com`) is the interface used to communicate with the WhatsApp Business Platform. The plugin uses the store owner's own Meta App credentials (provided during setup) to authenticate all requests.

**When and what data is sent:**

* When a new WooCommerce order is placed — the customer's WhatsApp phone number, the order ID, and the configured message template name are sent to Meta to trigger a notification to the customer and/or seller.
* When the store admin loads or saves message template settings — template names and language codes are fetched from or submitted to the Meta Business account.
* When the store admin uses the debug/connection tools — the configured Access Token is sent to Meta to verify its validity.
* When a WhatsApp message is received — Meta sends a webhook payload to your site containing the sender's phone number and message content; no data is sent outbound at that moment.

**Legal:**

* Meta Platform Terms: https://developers.facebook.com/terms/
* WhatsApp Business Policy: https://www.whatsapp.com/legal/business-policy/
* Meta Privacy Policy: https://www.facebook.com/privacy/policy/

== Development ==

The unminified JavaScript and CSS source files are included in the plugin under the `assets/src/` directory.

= Building from source =

Requirements: Node.js 20+ and npm 10+.

1. Navigate to the plugin directory.
2. Run `npm install` to install dependencies.
3. Run `npm run build` to compile assets into `assets/build/`.

For development with live reloading, use `npm run start` instead of `npm run build`.

== Changelog ==
= 1.0.0 - 2026-04-10 =
* First release
* Support: Support -> WP 6.9.4 WC 10.6.2
