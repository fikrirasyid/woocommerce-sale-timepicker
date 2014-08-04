=== WooCommerce Sale Timepicker - Providing Indonesian Banks as payment method for WooCommerce ===
Contributors: fikrirasyid
Tags: ecommerce, e-commerce, commerce, woothemes, wordpress ecommerce, timepicker
Requires at least: 3.9
Tested up to: 3.9
Stable tag: 0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==

WooCommerce's scheduled sale mechanism uses date based update (even though the time is stored as timestamp). The steps involved in this:

1. User sets sale schedule between dates using jQuery UI datepicker
2. The data is stored in as timestamp inside post_meta with `_sale_price_dates_from` and `_sale_price_dates_from` keys.
3. Upon installation, WooCommerce registered scheduled a daily event on WP-Cron system with `woocommerce_scheduled_sales` hook. When this hook is triggered, WooCommerce finds for products which qualified to be set as __sale__ based on its timestamp on `_sale_price_dates_from` post meta. WooCommerce also finds for productswhich qualified to be set as __normal__ (from __sale__ status) based on timestamp on `_sale_price_dates_to` post meta.

One day, [my employer](http://store.hijapedia.com) asked me "can we set the sale schedule on hour basis"? There's this promotion method on retail business called "Flash Sale" we'd like to implement on our site. "Flash Sale" is basically a very short span of sale schedule, typically in matter of hours. 

This kind of thing cannot be accomplished by WooCommerce's original sale mechanism. Hence this plugin is developed.

What this plugin does are:

1. Since the WooCommerce's product metabox HTML output cannot be modified, this plugin remove the original WooCommerce metabox and registering the slightly modified WooCommerce metabox (sub-class of original metabox class with overwrite method).
2. Dequeueing jQuery UI datepicker on product editor screen and enqueueing [Trent Richardson's Timepicker](http://trentrichardson.com/examples/timepicker/) + applying it.
3. Registering more frequent WordPress event (fired every 5 minutes) called `woocommerce_scheduled_sales_micro` to perform the exact thing happened on `woocommerce_scheduled_sales` hook which happends daily

That's pretty much about it.

## Disclosure: 

This plugin is released as courtesy of [Hijapedia.com](http://hijapedia.com). I originally made this plugin for [Hijapedia Store](http://store.hijapedia.com) and convinced my employer that there is a greater good for everyone by releasing this plugin as an open source plugin.