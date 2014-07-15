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

		$this->requiring();

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 40 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 50 );
	}

	/**
	 * Requiring file(s)
	 */
	function requiring(){
		require_once( $this->plugin_dir . '/includes/wc-sl-meta-box-product-data.php' );
	}

	/**
	 * Register script
	 */
	function admin_scripts(){
		wp_register_script( 'jquery-ui-timepicker', $this->plugin_url . '/js/jquery-ui-timepicker.js', array( 'jquery', 'jquery-ui-datepicker' ) );
		wp_register_script( 'woocommerce_admin_sale_timepicker', $this->plugin_url . '/js/woocommerce-sale-timepicker.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'accounting', 'round', 'jquery-ui-timepicker', 'woocommerce_admin_meta_boxes' ) );

		$screen = get_current_screen();

		if( 'product' == $screen->id ){
	    	wp_enqueue_script( 'woocommerce_admin_sale_timepicker' );
		}
	}

	/**
	 * Remove meta box
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'woocommerce-product-data', 'product', 'normal' );
	}

	/**
	 * Add WC Meta boxes
	 */
	function add_meta_boxes(){
		add_meta_box( 'woocommerce-product-data', __( 'Product Data M', 'woocommerce' ), 'WC_SL_Meta_Box_Product_Data::output', 'product', 'normal', 'high' );		
	}	
}
new Woocommerce_Sale_Timepicker;