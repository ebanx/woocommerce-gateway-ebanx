=== EBANX Payment Gateway for WooCommerce ===
Contributors: ebanxwp
Tags: credit card, boleto, ebanx, woocommerce, approval rate, conversion rate, brazil, mexico, peru, colombia, chile, oxxo, cash payment, local payment one-click payment, installments, alternative payments, accept more payments
Requires at least: 3.7
Tested up to: 4.7
Stable tag: 1.1.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Offer Latin American local payment methods & increase your conversion rates with the solution used by AliExpress, AirBnB and Spotify in Brazil.

== Description ==

Accept with ease and security, the most popular local payment methods in Latin America and receive your money anywhere in the world. The WooCommerce EBANX Payment Gateway plugin will enable you to significantly increase your cross-border sales & conversion rates by allowing you to sell like a local in the world’s fastest growing ecommerce markets.

Ready to accept local payments but don’t have an account yet? Finish our [simple sign-up](https://www.ebanx.com/business/en/dashboard) and we can start helping you sell like a local in Latin America or schedule a [call with a Business Development Executive](https://app.hubspot.com/meetings/ebanx-ronaldo/schedule-call).

**Plugin Description**

The WooCommerce EBANX Payment Gateway plugin allows you to accept local & international payment methods directly on your ecommerce store. Using the EBANX plugin you can process relevant cash, online debit, and credit card payments in Brazil, Mexico, Chile, Colombia & Peru, and access over 17M EBANX Wallet users. **No technical knowledge is needed for installation. Installation is simple, the way it should be.**

**EBANX Advantages**

* Security is already taken care of, the customer’s sensitive data doesn’t go to your server but is saved in EBANX environment using PCI standards
* One-click purchases which allow your client to skip the checkout process
* Checkout payment form is responsive and adapts nicely to all mobile screen sizes and themes
* Everything you need in one plugin, you don’t have to install any external plugins or extensions
* Sell to over 17M EBANX Wallet users
* Join ecommerce merchants such as AliExpress, Airbnb and Spotify

**Customize and Manage Your Payments**

With the EBANX plugin, you can:

* Choose which payment methods are displayed at checkout
* Set a maximum number of installments
* Select an expiration date for cash payments
* Allow customers to save their credit card information
* Create orders & request refunds directly in WooCommerce

The plugin also includes:

* Sandbox mode for testing
* Capture mode that when activated allows you to collect payments after a manual review
* Extra fields that are added automatically for payments made in Brazil or Chile where customers must provide more information to local regulatory authorities

**Want to do a Test Drive?**

Our demonstrations allow you to create a payment as customer would and to explore all the plugin features **without having to install**. Access the [Demo Store](https://www.ebanxdemo.com/) for your own first-hand experience or [request a personal demonstration via phone](https://app.hubspot.com/meetings/ebanx-ronaldo/schedule-call) with a Business Development Executive.

Looking for more detailed information? Visit our [Developer’s Academy](https://www.ebanx.com/business/en/developers/) for step-by-step guides, API references, and integration options or [call a Business Development Executive](https://app.hubspot.com/meetings/ebanx-ronaldo/schedule-call).

**Requirements**

All pages that incorporate the EBANX plugin must be served over HTTPS.

**About EBANX**

[EBANX is a local payments expert](https://www.ebanx.com/business/en) and we offer complete solutions for international businesses wanting to sell more in Latin America. Whether you are an enterprise or running your own startup, EBANX can help you sell internationally with ease and efficiency.

== Installation ==

**Automatic**

Automatic installation is the easiest option and can be done without leaving your web browser. To do an automatic install of the EBANX plugin, login to the WordPress Dashboard, go to the Plugins menu, and select “Add New.” Then, search for the “EBANX Gateway Plugin” and click “Install Now.”

**Manual**

To install the plugin manually, download our plugin and upload it to your web server via an FTP application. Visit the [WordPress codex](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation "WordPress codex") for further instructions.

**Via GitHub**

The EBANX Gateway Plugin can also be installed using GitHub. You can find our repository [here](https://github.com/ebanx/checkout-woocommerce/ "EBANX GitHub repository"). To view step-by-step installation via GitHub instructions click [here](https://github.com/ebanx/checkout-woocommerce/tree/master/woocommerce-gateway-ebanx "Install Via GitHub").

Still need your API Keys? You can pick those up [here](https://www.ebanx.com/business/en/dashboard "EBANX API keys").

== Frequently Asked Questions ==

= Does this plugin support subscriptions or recurring charges? =

Not yet. However, customers are able to save credit card information and make one-click purchases. We are working on having the next version of the plugin support subscriptions.

= Who can I contact if I still have questions? =

Reach out to one of our integration specialists at integration@ebanx.com or speak with your merchant services or business manager. Don’t have a business manager yet? [Sign-up here](https://www.ebanx.com/business/en/dashboard "EBANX Dashboard") and we will assign one to your company account or [schedule a call](https://app.hubspot.com/meetings/ebanx-ronaldo/schedule-call) with a Business Development Executive.

= Where can I find more documentation or instructions? =

The [Developer’s Academy](https://www.ebanx.com/business/en/developers/integrations/extensions-and-plugins/woocommerce-plugin "EBANX Developer's Academy") has step-by-step instructions and detailed information about all our plugins.

= Which payment types does EBANX process? =

* Visa, Mastercard, American Express, Diner’s Club, Discover - all countries
* Brazil
  * EBANX Boleto, Cash Payment
  * Hipercard, Elo, and Aura Domestic Credit Cards
  * Online Debit Transfer
  * EBANX Wallet, Prepaid Card / Debit Transfer
* Mexico
  * OXXO, Cash Payment
  * Debit & Credit Cards
  * EBANX Wallet, Debit Transfer
* Chile
  * Sencillito, Cash Payment
  * Servipag, Online Debit Transfer
* Peru
  * PagoEfectivo, Cash Payment / Debit Transfer
  * SafetyPay, Cash Payment / Debit Transfer
* Colombia
  * Pagos Seguros en Línea (PSE), Online Debit Transfer


== Changelog ==

= 1.1.0 =
* Feature - Instalments field now gets hidden when max instalments is set to one [#275](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/275)
* Feature - Send store notification and return links to payment api [#268](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/268)
* Feature - Support for third-party checkout manager plugins [#279](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/279)
* Feature - CPF/CNPJ Brazilian person types support [#279](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/279)
* Feature - New debit card flags for mexico [#290](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/290)
* Change - Added the new tags: `alternative payments` and `accept more payments`
* Fix - Thank you pages for each payment gateway are now called by order status [#277](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/277)
* Fix - The credit cards gateways were separated by countries [#277](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/277)

= 1.0.2 =
* Bug - Fixed bug that was breaking the media uploader [#267](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/267)
* Enhancement - All methods are commented now [#266](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/266)

= 1.0.1 =
* 2016-01-17 - Texts - Chaging the namings and texts from plugin.

= 1.0.0 =
* 2016-12-30 - First Release.

== Screenshots ==

1. EBANX Features - Be able to accept local credit cards.
2. EBANX Features - One of the features that the plugin brings, it's the One Click Payment. A functionality that allows your customer to save their card data and buy the product with only one click.
3. EBANX Features - Once you choose the EBANX Plugin, your Latin American customers will be able to pay in local methods, such as: boleto, oxxo payments and national credit cards.
4. Plugin Configuration - To start your integration, go to your [EBANX Dashboard](https://www.ebanx.com/business/en/dashboard/users/sign_in) settings to find your test and live keys. Insert them and choose to enable the sandbox mode for testing.
5. Plugin Configuration - You can choose the countries and gateways to work with just by inserting them on the right field to enable payments methods.
6. Plugin Configuration - Set more advanced options such as: Save Card Data, One-click payment, enable auto-capture and maximum number of installments.
