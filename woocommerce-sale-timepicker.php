<?php
/**
 * Plugin Name: WooCommerce Sale Timepicker
 * Description: Replacing datepicker on WooCommerce product dashboard with more timepicker (So the sale can be scheduled in minute basis)
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

		// Register five minutes interval cron
		add_filter( 'cron_schedules', array( $this, 'cron_five_minutes' ) );

		// Register activation task
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		// Register deactivation task
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		// Enqueueing scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Removing and adding meta box
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 40 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 50 );

		// Refresh scheduled sales
		add_action( 'woocommerce_scheduled_sales_micro', 'wc_scheduled_sales' );
	}

	/**
	 * Requiring external files file(s)
	 */
	function requiring(){
		require_once( $this->plugin_dir . '/includes/wc-st-meta-box-product-data.php' );
	}

	/**
	 * Register new interval
	 * 
	 * @return array of modified schedule
	 */
	function cron_five_minutes( $schedule ){
		$schedules['every5minutes'] = array(
			'interval' => 300,
			'display' => __( 'Every 5 minutes', 'woocommerce-sale-timepicker' )
		);

		return $schedules;
	}

	/**
	 * Activation task
	 * 
	 * @return void
	 */
	function activation(){
		if( !wp_next_scheduled( 'woocommerce_scheduled_sales_micro' ) ){
			wp_schedule_event( current_time( 'timestamp', wp_timezone_override_offset() ), 'every5minutes', 'woocommerce_scheduled_sales_micro' );
		}

	}

	/**
	 * Deactivation task
	 * 
	 * @return void
	 */
	function deactivation(){
		wp_clear_scheduled_hook( 'woocommerce_scheduled_sales_micro' );
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
		add_meta_box( 'woocommerce-product-data', __( 'Product Data', 'woocommerce' ), 'WC_ST_Meta_Box_Product_Data::output', 'product', 'normal', 'high' );		
	}	
}
new Woocommerce_Sale_Timepicker;