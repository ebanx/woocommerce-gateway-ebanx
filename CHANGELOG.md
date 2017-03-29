# CHANGELOG

# 1.10.0
* Feature - Removed restriction on guest users for sandbox mode [#406](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/406)
* Feature - Showing some EBANX order details on admin order details page [#404](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/404)
* Improvement - Removed unecessary properties and variables [#407](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/407)
* Improvement - Improved texts and options on OXXO thank you page [#409](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/409)
* Fix - Updated deprecated function [#403](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/403)


# 1.9.1
* Fix - Fixed translations string keys in instalment template [#402](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/402)

# 1.9.0
* Feature - Advanced options hide when not applicable [#391](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/391)
* Feature - Translated my-account credit card section [#398](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/398)
* Feature - Added tooltips with nice descriptions to gateway settings page [#400](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/400)
* Improvement - Cached last key check response to speed up admin panel [#396](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/396)
* Improvement - Cached exchange rates in short intervals to improve checkout page performance [#399](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/399)
* Fix - Fixed translations for instalments with interests [#395](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/395)

# 1.8.1
* Fix - Fixed instalment reading on checkout [#393](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/393)

# 1.8.0
* Feature - Hide irrelevant fields and group fields by country on EBANX Settings page [#373](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/373)
* Feature - Added new payment gateway Baloto (Colombia) [#371](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/371)
* Feature - Hide the payment gateways on checkout page when sandbox mode is enabled for non admin users and not logged users [#380](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/380)
* Feature - A warning was added when sandbox mode is enabled [#378](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/378)
* Feature - Added asterisk to required compliance fields on checkout page [#370](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/370)

## 1.7.1
* Fix - Fixed Oxxo and Pagoefectivo iframe not showing [#382](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/382)

## 1.7.0
* Feature - The HTML select fields are now using the `select2` jQuery plugin to improve the user experience [#356](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/356)
* Improvement - We removed some unnecessaries folders and files from plugin [#353](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/353)
* Improvement - All JS assets are loading on footer [#357](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/357)
* Fix - Fixed the low resolution of the EBANX badge on non-retina displays [#354](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/354)
* Fix - Prevent fatal error when the plugin is activated without WooCommerce plugin [#360](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/360)
* Fix - Avoid SSL warning from EBANX PHP libray when the plugin make a request to URLs with a bad SSL certificate [#362](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/362)
* Fix - Resolves fatal error when the plugin can't get some informations [#365](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/365)

## 1.6.1
* Fix - Address splitting function to avoid mistakes during checkout [#352](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/352)

## 1.6.0
* Feature - Integrates with EBANX Dashboard plugin presence check [#348](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/348)
* Improvement - Gets the banking ticket HTML by cUrl with url fopen fallback [#345](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/345)
* Improvement - Changed iframe boleto URL fetching to avoid xss injections [#345](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/345)
* Fix - Max instalment limits are now adjusted for local currency instead of assuming USD for prices [#349](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/349)

## 1.5.3
* Fix - In case user country was not set one-click payments was crashing [#343](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/343)

## 1.5.2
* Fix - Checking for new feature's settings presence to avoid notices [#342](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/342)

## 1.5.1
* Fix - Notification URL in payment payload [#341](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/341)

## 1.5.0
* Feature - Instalment interest rates are now configurable [#336](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/336)
* Improvement - Payment Options section in admin is now togglable [#336](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/336)

## 1.4.1
* Fix - Fixed API Lead URL to the correct URL, because it was causing a redirect without www

## 1.4.0
* Improvement - Sending analytics information for plugin activations [#332](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/332)
* Fix - Fixed max instalments limit according to acquirer in one-click payments [#334](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/334)

## 1.3.0
* Feature - Allowed local currency, USD and EUR to be processed by EBANX based on WooCommerce Currency Options [#325](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/325)
* Improvement - Updated to new EBANX logo [#326](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/326)
* Fix - Removed the pipe character from the last WooCommerce Checkout Settings tab menu [#329](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/329)

## 1.2.3
* Fix - Checkout manager field for person type selecting in Brasil value is now respected [#323](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/323)

## 1.2.2
* Fix - Chceckout manager fields are no longer mandatory when activated [#320](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/320)

## 1.2.1
* Fix - Chile payments when using checkout manager [#306](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/306)

## 1.2.0
* Feature - Instalments limit based on minimun amount accepted by credit card acquirer [#298](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/298)
* Feature - API requests now using cUrl as main method of http communication [#302](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/302)
* Feature - Checkout manager option for entity type field in brazil checkout in cases where cnpj and cpf are both enabled [#304](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/304)
* Fix - Undisplayed thank-you-page messages [#299](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/299)
* Fix - Checkout manager settings being respected even when disabled [#304](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/304)

## 1.1.2
* Fix - Integration keys validation messages now update properly [#297](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/297)

## 1.1.1
* Fix - Brazil compliance fields showing for other countries [#294](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/294)

## 1.1.0
* Feature - Instalments field now gets hidden when max instalments is set to one [#275](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/275)
* Feature - Send store notification and return links to payment api [#268](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/268)
* Feature - Support for third-party checkout manager plugins [#279](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/279)
* Feature - CPF/CNPJ Brazilian person types support [#279](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/279)
* Feature - New debit card flags for mexico [#290](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/290)
* Change - Added the new tags: `alternative payments` and `accept more payments`
* Fix - Thank you pages for each payment gateway are now called by order status [#277](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/277)
* Fix - The credit cards gateways were separated by countries [#277](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/277)

## 1.0.2
* Bug - Fixed bug that was breaking the media uploader [#267](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/267)
* Enhancement - All methods are commented now [#266](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/266)

## 1.0.1
* 2016-01-17 - Texts - Chaging the namings and texts from plugin.

## 1.0.0
* 2016-12-30 - First Release.
