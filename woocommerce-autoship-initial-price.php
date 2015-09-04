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
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$prefix = $wpdb->prefix;
		
		$wpdb->hide_errors();
		
		// Customers
		$create_sql =
			"CREATE TABLE {$prefix}wc_autoship_initial_prices (
			schedule_item_id BIGINT(20) UNSIGNED NOT NULL,
			price DECIMAL(10,2) NOT NULL,
			created_time DATETIME NOT NULL,
			modified_time DATETIME NOT NULL,
			PRIMARY KEY  (schedule_item_id)
			);";
		dbDelta( $create_sql );
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
	
	function wc_autoship_initial_price_insert( $table_name, $data, $result, $id ) {
		global $wpdb;
		
		if ( $result === false || empty( $id ) ) {
			return;
		}
		if ( $table_name != "{$wpdb->prefix}wc_autoship_schedule_items" ) {
			return;
		}
		
		// Get item
		if ( ! WC_Autoship_Schedule_Item::id_exists( $id ) ) {
			return;
		}
		$item = new WC_Autoship_Schedule_Item( $id );
		// Get price
		$product_id = $item->get_product_id();
		$variation_id = $item->get_variation_id();
		$price_product_id = empty( $variation_id ) ? $product_id : $variation_id;
		$autoship_price = get_post_meta( $price_product_id, '_wc_autoship_price', true );
		if ( empty( $autoship_price ) ) {
			return;
		}
		
		// Insert price
		$data = array(
			'schedule_item_id' => $id,
			'price' => $autoship_price
		);
		$wpdb->insert( "{$wpdb->prefix}wc_autoship_initial_prices", $data );
	}
	
	function wc_autoship_initial_price_filter( $autoship_price, $product_id, $autoship_frequency, $customer_id, $schedule_item_id ) {
		global $wpdb;
		
		if ( empty( $schedule_item_id ) ) {
			// Return default
			return $autoship_price;
		}
		
		$initial_price = $wpdb->get_var( $wpdb->prepare(
			"SELECT `initial_prices`.price
			FROM {$wpdb->prefix}wc_autoship_initial_prices AS `initial_prices`
			LEFT JOIN {$wpdb->prefix}wc_autoship_schedule_items AS `items` ON(`initial_prices`.schedule_item_id = `items`.id)
			WHERE `items`.id = %d",
			$schedule_item_id
		) );
		if ( empty( $initial_price ) ) {
			// Return default
			return $autoship_price;
		}
		// Return initial price value
		return $initial_price;
	}
	add_filter( 'wc_autoship_price', 'wc_autoship_initial_price_filter', 10, 5 );
}
