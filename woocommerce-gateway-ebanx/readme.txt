=== EBANX Payment Gateway for WooCommerce ===
Contributors: ebanxwp
Tags: credit card, boleto, ebanx, woocommerce, approval rate, conversion rate, brazil, mexico, peru, colombia, chile, oxxo, baloto, cash payment, local payment one-click payment, installments, alternative payments, accept more payments
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 1.13.2
License: MIT
License URI: https://opensource.org/licenses/MIT

Offer Latin American local payment methods & increase your conversion rates with the solution used by AliExpress, AirBnB and Spotify in Brazil.

== Description ==

[youtube https://www.youtube.com/watch?v=J8vFkDwHMWA]

Accept with ease and security, the most popular local payment methods in Latin America and receive your money anywhere in the world. The WooCommerce EBANX Payment Gateway plugin will enable you to significantly increase your cross-border sales & conversion rates by allowing you to sell like a local in the world’s fastest growing ecommerce markets.

Ready to accept local payments but don’t have an account yet? Finish our [simple sign-up](https://www.ebanx.com/business/en/dashboard) and we can start helping you sell like a local in Latin America or schedule a [call with a Business Development Executive](https://app.hubspot.com/meetings/ebanx-ronaldo/schedule-call).

**Plugin Description**

The WooCommerce EBANX Payment Gateway plugin allows you to accept local & international payment methods directly on your ecommerce store. Using the EBANX plugin you can process relevant cash, online debit, and credit card payments in Brazil, Mexico, Chile, Colombia & Peru, and access over 20M EBANX Wallet users. **No technical knowledge is needed for installation. Installation is simple, the way it should be.**

**EBANX Advantages**

* Security is already taken care of, the customer’s sensitive data doesn’t go to your server but is saved in EBANX environment using PCI standards
* One-click purchases which allow your client to skip the checkout process
* Checkout payment form is responsive and adapts nicely to all mobile screen sizes and themes
* Everything you need in one plugin, you don’t have to install any external plugins or extensions
* Sell to over 20M EBANX Wallet users
* Join ecommerce merchants such as AliExpress, Airbnb and Spotify

**Customize and Manage Your Payments**

With the EBANX plugin, you can:

* Choose which payment methods are displayed at checkout
* Set a maximum number of installments
* Select an expiration date for cash payments
* Allow customers to save their credit card information
* Set individual interest rates for each credit card instalment plan
* Create orders & request refunds directly in WooCommerce
* Accept Local Currencies, USD and EUR based on your WooCommerce Currency Options, to be processed by EBANX

The plugin also includes:

* Sandbox mode for testing
* Capture mode that when activated allows you to collect payments after a manual review
* Extra fields that are added automatically for payments made in Brazil or Chile where customers must provide more information to local regulatory authorities
* Support for checkout managers

**Want to do a Test Drive?**

Our demonstrations allow you to create a payment as customer would and to explore all the plugin features **without having to install**. Access the [Demo Store](https://www.ebanxdemo.com/) for your own first-hand experience or [request a personal demonstration via phone](https://app.hubspot.com/meetings/ebanx-ronaldo/schedule-call) with a Business Development Executive.

Looking for more detailed information? Visit our [Developer’s Academy](https://www.ebanx.com/business/en/developers/) for step-by-step guides, API references, and integration options or [call a Business Development Executive](https://app.hubspot.com/meetings/ebanx-ronaldo/schedule-call).

**Requirements**

All pages that incorporate the EBANX plugin must be served over HTTPS.

**About EBANX**

[EBANX is a local payments expert](https://www.ebanx.com/business/en) and we offer complete solutions for international businesses wanting to sell more in Latin America. Whether you are an enterprise or running your own startup, EBANX can help you sell internationally with ease and efficiency.

== Installation ==

**Automatic**

Automatic installation is the easiest option and can be done without leaving your web browser. To do an automatic install of the EBANX plugin, login to the WordPress Dashboard, go to the Plugins menu, and select “Add New.” Then, search for the “EBANX Payment Gateway for WooCommerce” and click “Install Now.”

**Manual**

To install the plugin manually, download our plugin and upload it to your web server via an FTP application. Visit the [WordPress codex](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation "WordPress codex") for further instructions.

**Via GitHub**

The EBANX Gateway Plugin can also be installed using GitHub. You can find our repository [here](https://github.com/ebanx/checkout-woocommerce/ "EBANX GitHub repository"). To download the plugin from our repository, please view [our latest release](https://github.com/ebanx/woocommerce-gateway-ebanx/releases/latest "Latest Release from GitHub repository") and download the `woocommerce-gateway-ebanx.zip` package.

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
  * Baloto

= Which currencies does EBANX accept? =

* USD - U.S. Dollar
* EUR - Euro
* BRL - Real
* MXN - Peso Mexicano
* COP - Peso Colombiano
* CLP - Peso Chileno
* PEN - Novo Sol

= Can I use my own Checkout Manager plugin? =

Yes, you can.

1. Set up your own billing fields in the checkout manager plugin page;
2. Go to the `EBANX Settings` page and open the `Advanced Options` section;
3. Enable the `Use my checkout manager fields` checkbox and fill in the field names as in step 1;
4. There you go, you’re all set!

== Changelog ==

= 1.13.2 =
* Fix - Replaced wp_die to exit to avoid error 500 [#515](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/515)
* Fix - Updating order when it receives a payment status notification [#516](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/516) 

= 1.13.1 =
* Fix - Avoid duplication payment notifications [#509](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/509)
* Fix - Changed PSE thank you page HTML [#512](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/512)
* Fix - Changed Boleto thank you page HTML [#513](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/513)

= 1.13.0 =
* Fix - Fix for debug log when is enabled before record a log [#507](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/507)
* Fix - Fix issue to avoid some issues on refund transactions [#506](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/506)
* Fix - Changed label to Minimum Instalment (title-cased labels) [#500](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/500)
* Fix - Fixed compliance fields when country is empty [#498](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/498)
* Feature - Docker implementation and end-to-end tests for Brazil payments done [#504](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/504)

= 1.12.1 =
* Fix - Credit-card saving for new customers [#496](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/496)
* Fix - One-click payments button in product details [#496](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/496)

= 1.12.0 =
* Feature - Using interest rate on minimum instalment value [#490](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/490)
* Feature - Refactor EBANX query router [#487](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/487)
* Feature - Added a minimal value setting on settings [#477](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/477)
* Feature - Changed cookie to localStorage to save flags [#476](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/476)
* Feature - Plugin docs using phpDocumentator [#488](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/488)
* Fix - Thank you page values and instalments fixed [#473](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/473)
* Fix - Hide saved cards when option is disabled [#475](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/475)
* Fix - DNI field is not mandatory for colombia any more [#486](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/486)
* Fix - Changed the assets path to system path instead of host path [#489](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/489)
* Fix - Using absolute path to spinner gif [#485](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/485)
* Improvement - Updated notification notices and notes [#468](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/468)

= 1.11.4 =
* Fix - Fixed float values not being accepted in interest rates [#480](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/480)
* Fix - Added '/' to Notification URL to prevent Response Code 301 [#480](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/480)

= 1.11.3 =
* Fix - Fixed a problem that it was incrementing a value by instalment [#463](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/463)

= 1.11.2 =
* Fix - Fixed translation paths [#462](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/462)
* Fix - Fixed converted value message when instalments is changed [#462](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/462)
* Fix - Fixed problems with newer version of WooCommerce [#462](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/462)

= 1.11.0 =
* Feature - Showing the prices with IOF for Brazil before on gateways [#441](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/441)
* Feature - Alert the merchants when HTTPS isn't present [#427](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/427)
* Feature - Show a message to fill the integration keys when empty [#426](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/426)
* Feature - Hooks implemented to facilitate the future integrations [#423](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/423)
* Feature - Capture payment manually clicking on "Processing" button [#421](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/421)
* Feature - Show a message when credit card is invalid on sandbox mode [#420](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/420)
* Feature - Created a flash message management helper class [#414](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/414)
* Improvement - Assets optimization by 62% faster [#429](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/429)
* Fix - Refactored and fixed bugs of one click feature [#457](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/457)
* Fix - Reverts the WC3 update keeping backward compatibility [#455](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/455)
* Fix - SafetyPay Notices [#450](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/450)

These are the most importante fixes and features, but another fixes and quality issues were resolved too.

= 1.10.1 =
* Fix - Removed methods to prevent fatal error [#412](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/412)

= 1.10.0 =
* Feature - Removed restriction on guest users for sandbox mode [#406](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/406)
* Feature - Showing some EBANX order details on admin order details page [#404](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/404)
* Improvement - Removed unecessary properties and variables [#407](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/407)
* Improvement - Improved texts and options on OXXO thank you page [#409](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/409)
* Fix - Updated deprecated function [#403](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/403)

= 1.9.1 =
* Fix - Fixed translations string keys in instalment template [#402](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/402)

= 1.9.0 =
* Feature - Advanced options hide when not applicable [#391](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/391)
* Feature - Translated my-account credit card section [#398](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/398)
* Feature - Added tooltips with nice descriptions to gateway settings page [#400](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/400)
* Improvement - Cached last key check response to speed up admin panel [#396](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/396)
* Improvement - Cached exchange rates in short intervals to improve checkout page performance [#399](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/399)
* Fix - Fixed translations for instalments with interests [#395](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/395)

= 1.8.1 =
* Fix - Fixed instalment reading on checkout [#393](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/393)

= 1.8.0 =
* Feature - Hide irrelevant fields and group fields by country on EBANX Settings page [#373](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/373)
* Feature - Added new payment gateway Baloto (Colombia) [#371](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/371)
* Feature - Hide the payment gateways on checkout page when sandbox mode is enabled for non admin users and not logged users [#380](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/380)
* Feature - A warning was added when sandbox mode is enabled [#378](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/378)
* Feature - Added asterisk to required compliance fields on checkout page [#370](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/370)

= 1.7.1 =
* Fix - Fixed Oxxo and Pagoefectivo iframe not showing [#382](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/382)

= 1.7.0 =
* Feature - The HTML select fields are now using the `select2` jQuery plugin to improve the user experience [#356](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/356)
* Improvement - We removed some unnecessaries folders and files from plugin [#353](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/353)
* Improvement - All JS assets are loading on footer [#357](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/357)
* Fix - Fixed the low resolution of the EBANX badge on non-retina displays [#354](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/354)
* Fix - Prevent fatal error when the plugin is activated without WooCommerce plugin [#360](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/360)
* Fix - Avoid SSL warning from EBANX PHP libray when the plugin make a request to URLs with a bad SSL certificate [#362](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/362)
* Fix - Resolves fatal error when the plugin can't get some informations [#365](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/365)

= 1.6.1 =
* Fix - Address splitting function to avoid mistakes during checkout [#352](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/352)

= 1.6.0 =
* Feature - Integrates with EBANX Dashboard plugin presence check [#348](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/348)
* Improvement - Gets the banking ticket HTML by cUrl with url fopen fallback [#345](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/345)
* Improvement - Changed iframe boleto URL fetching to avoid xss injections [#345](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/345)
* Fix - Max instalment limits are now adjusted for local currency instead of assuming USD for prices [#349](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/349)

= 1.5.3 =
* Fix - In case user country was not set one-click payments was crashing [#343](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/343)

= 1.5.2 =
* Fix - Checking for new feature's settings presence to avoid notices [#342](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/342)

= 1.5.1 =
* Fix - Notification URL in payment payload [#341](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/341)

= 1.5.0 =
* Feature - Instalment interest rates are now configurable [#336](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/336)
* Improvement - Payment Options section in admin is now togglable [#336](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/336)

= 1.4.1 =
* Fix - Fixed API Lead URL to the correct URL, because it was causing a redirect without www

= 1.4.0 =
* Fix - Fixed max instalments limit according to acquirer in one-click payments [#334](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/334)
* Improvement - Sending analytics information for plugin activations [#332](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/332)

= 1.3.0 =
* Feature - Allowed local currency, USD and EUR to be processed by EBANX based on WooCommerce Currency Options [#325](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/325)
* Improvement - Updated to new EBANX logo [#326](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/326)
* Fix - Removed the pipe character from the last WooCommerce Checkout Settings tab menu [#329](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/329)

= 1.2.3 =
* Fix - Checkout manager field for person type selecting in Brasil value is now respected [#323](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/323)

= 1.2.2 =
* Fix - Chceckout manager fields are no longer mandatory when activated [#320](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/320)

= 1.2.1 =
* Fix - Chile payments when using checkout manager [#306](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/306)

= 1.2.0 =
* Feature - Instalments limit based on minimun amount accepted by credit card acquirer [#298](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/298)
* Feature - API requests now using cUrl as main method of http communication [#302](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/302)
* Feature - Checkout manager option for entity type field in brazil checkout in cases where cnpj and cpf are both enabled [#304](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/304)
* Fix - Undisplayed thank-you-page messages [#299](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/299)
* Fix - Checkout manager settings being respected even when disabled [#304](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/304)

= 1.1.2 =
* Fix - Integration keys validation messages now update properly [#297](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/297)

= 1.1.1 =
* Fix - Brazil compliance fields showing for other countries [#294](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/294)

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

== Arbitrary section ==

When you use our plug in, you trust us with your information and agree that we may keep it and use it for the purposes of our commercial relationship. As we are a PCI compliant company, we will keep all your data safe, and will not use it for any other purposes.

