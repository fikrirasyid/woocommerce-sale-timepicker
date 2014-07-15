<?php
/**
 * Plugin Name: WooCommerce Sale Timepicker
 * Plugin URI: http://www.woothemes.com/woocommerce/
 * Description: Replacing datepicker on product dashboard with more precise timepicker
 * Version: 0.1
 * Author: Fikri Rasyid
 * Author URI: http://fikrirasyid.com
 * Requires at least: 3.9
 * Tested up to: 3.9
 *
 * Text Domain: woocommerce-sale-time-picker
 *
 * @package WooCommerce
 * @category Product
 * @author Fikri Rasyid
 */
class Woocommerce_Sale_Timepicker{

	var $plugin_url;
	var $plugin_dir;

	function __construct(){
		$this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
		$this->plugin_dir = dirname( __FILE__ );

		// Adding external files
		$this->requiring();

		// Enqueueing scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Removing and adding meta box
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 40 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 50 );
	}

	/**
	 * Requiring external files file(s)
	 */
	function requiring(){
		require_once( $this->plugin_dir . '/includes/wc-sl-meta-box-product-data.php' );
	}

	/**
	 * Register and enqueue script
	 */
	function admin_scripts(){
		wp_register_script( 'jquery-ui-timepicker', $this->plugin_url . '/js/jquery-ui-timepicker.js', array( 'jquery', 'jquery-ui-datepicker' ) );
		wp_register_script( 'woocommerce_admin_sale_timepicker', $this->plugin_url . '/js/woocommerce-sale-timepicker.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'accounting', 'round', 'jquery-ui-timepicker', 'woocommerce_admin_meta_boxes' ) );

		// Get current screen estate
		$screen = get_current_screen();

		// Only enqueue the script on add new / edit product dashboard
		if( 'product' == $screen->id ){
	    	wp_enqueue_script( 'woocommerce_admin_sale_timepicker' );
		}
	}

	/**
	 * Removing product data meta box which comes with WooCommerce
	 * 
	 * @return void
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'woocommerce-product-data', 'product', 'normal' );
	}

	/**
	 * Adding custom meta box that is slightly modified for timepicking purpose
	 * It is basically extending the original class with slight modification on the method
	 * 
	 * @return void
	 */
	function add_meta_boxes(){
		add_meta_box( 'woocommerce-product-data', __( 'Product Data', 'woocommerce' ), 'WC_SL_Meta_Box_Product_Data::output', 'product', 'normal', 'high' );		
	}	
}
new Woocommerce_Sale_Timepicker;