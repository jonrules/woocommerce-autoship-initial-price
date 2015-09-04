<?php
/*
Plugin Name: WC Auto-Ship Intital Price
Plugin URI: http://patternsinthecloud.com
Description: Maintain the initial purchase price for autoship items.
Version: 1.0
Author: Patterns in the Cloud
Author URI: http://patternsinthecloud.com
License: Single-site
*/

define( 'WC_AUTOSHIP_INITIAL_PRICE_VERSION', '1.0.0' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	
	function wc_autoship_initial_price_install() {

	}
	register_activation_hook( __FILE__, 'wc_autoship_initial_price_install' );
	
	function wc_autoship_initial_price_deactivate() {
	
	}
	register_deactivation_hook( __FILE__, 'wc_autoship_initial_price_deactivate' );
	
	function wc_autoship_initial_price_uninstall() {

	}
	register_uninstall_hook( __FILE__, 'wc_autoship_initial_price_uninstall' );
	
	function wc_autoship_initial_price_admin_enqueue_scripts() {
		$url_path = plugin_dir_url( __FILE__ );
		// Admin scripts
		wp_register_script( 'wc-autoship-initial-price-admin-scripts', $url_path . 'js/admin-scripts.js', array( 'jquery' ), WC_AUTOSHIP_INITIAL_PRICE_VERSION );
		wp_localize_script( 'wc-autoship-initial-price-admin-scripts', 'wc_autoship_initial_price', array(
			'export_url' => admin_url( '/admin-ajax.php?action=wc_autoship_initial_price' )
		));
		wp_enqueue_script( 'wc-autoship-initial-price-admin-scripts' );
	}
	add_action( 'admin_enqueue_scripts', 'wc_autoship_initial_price_admin_enqueue_scripts' );
	
	function wc_autoship_initial_price_admin_menu() {
		add_menu_page( 'WC Auto-Ship Initial Price', 'Export', 'manage_woocommerce', 'wc_autoship_initial_price', 'wc_autoship_initial_price_render_admin_page', 'dashicons-media-spreadsheet' );
	}
	add_action( 'admin_menu', 'wc_autoship_initial_price_admin_menu' );
	
	function wc_autoship_initial_price_render_admin_page() {
		echo '<div class="wrap">';

		require_once( 'templates/admin-page.php' );
			
		echo '</div>';
	}
	
	function wc_autoship_initial_price_filter( $autoship_price, $product_id, $schedule_frequency, $customer_id ) {
		
	}
	add_filter( 'wc_autoship_price', 'wc_autoship_initial_price_filter' );
}
