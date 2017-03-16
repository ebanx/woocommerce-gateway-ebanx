# CHANGELOG

## 1.7.0
* Improvement - We removed some unnecessaries folders and files from plugin [#353](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/353)
* Performance - All JS assets are loading on footer [#357](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/357)
* Fix - Fixed the low resolution of the EBANX badge on non-retina displays [#354](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/354)
* Fix - Prevent fatal error when the plugin is activated without WooCommerce plugin [#360](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/360)
* Feature - The HTML select fields are now using the `select2` jQuery plugin to improve the user experience [#356](https://github.com/ebanx/woocommerce-gateway-ebanx/pull/356)

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
