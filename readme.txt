=== Payment Gateway for PayFabric ===
Tags: woocommerce, payfabric
Requires at least: 5.0
Tested up to: 5.9.3
Requires PHP: 7.1
Stable tag: 1.0.13
WC requires at least: 4.0
WC tested up to: 6.4.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds PayFabric as a payment gateway for WooCommerce.

== Description ==

Accept credit card payments easily and directly on your WooCommerce store via [PayFabric](https://www.payfabric.com/).

= Features =

* Charge customer credit cards via PayFabric's wallet and transaction APIs.
* Customers can safely and securely store their credit cards with PayFabric through their WooCommerce account.
* Process refunds directly from the WooCommerce order admin area.

Payment Gateway for PayFabric allows you to test your PayFabric integration in their [sandbox environment](http://sandbox.payfabric.com/).

Upgrade to the premium version of Payment Gateway for PayFabric directly from the WordPress admin area to unlock live payment capabilities.

Visit [tools.cypressnorth.com](https://tools.cypressnorth.com/) for more information.

== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of the Payment Gateway for PayFabric plugin, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “Payment Gateway for PayFabric” and click Search Plugins. Once you’ve found our plugin you can view details about it and install it by simply clicking "Install Now", then "Activate".

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

== Upgrade Notice ==

== Screenshots ==

1. PayFabric listed as a paymethod in the WooCommerce payment methods settings.
2. The PayFabric payment gateway settings screen used to configure the PayFabric gateway.
3. PayFabric credit card pay form displayed in WooCommerce checkout.
4. Upgrade to our paid version to unlock live payment processing.

== Changelog ==

= 1.0.13 =
* Security updates

= 1.0.12 =
* Updates plugin meta information
* Security fixes

= 1.0.11 =
* Security fixes

= 1.0.10 =
* Pass exception to payment and refund error hooks

= 1.0.9 =
* Add payfabric_transaction_data filter
* Add payfabric_process_payment_error hook
* Add payfabric_process_refund_error hook

= 1.0.8 =
* Use order object fields to prepare wallet when not using a saved card

= 1.0.7 =
* Pass CVC field when processing a transaction

= 1.0.6 =
* Pass CVC field when creating a wallet

= 1.0.5 =
* Check customer wallets for existence of credit card

= 1.0.4 =
* Add backwards compatibility support for PHP 7.1

= 1.0.3 =
* Inital release

== Frequently Asked Questions ==

= What is the difference between this version and the paid version? =

The free version, available via the WordPress plugin repository, allows you test credit card payments via PayFabric. You can upgrade to the premium version directly from the WordPress admin area to unlock live payment capabilities.

= Want to report a bug? =

Email us at [wordpress@cypressnorth.com](mailto:wordpress@cypressnorth.com).