


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Put your plugin code here
}


<?php
/*
	Plugin Name: WooCommerce envioPack Shipping
	Plugin URI: https://github.com/fstrobino/woocommerce-envioPack-plugin
	Description: Obtain shipping rates dynamically via the oca API for your orders.
	Version: 0.1.0
	Author: Federico Strobino
	Author URI: https://github.com/fstrobino
	Copyright: 2017 fstrobino

*/

/**
 * Required functions
 * require_once( 'includes/wanderlust-functions.php' );
 */


/**
 * Plugin page links
 * podemos meter links a nuestro repo
 */

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Put your plugin code here
}

/**
 * WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
    /**
	 * woocommerce_init_shipping_table_rate function.
	 *
	 * @access public
	 * @return void
	 */
	function wc_oca_init() {
		include_once( 'includes/class-wc-shipping-oca.php' );
	}

	add_action( 'woocommerce_shipping_init', 'wc_oca_init' );

	/**
	 * wc_oca_add_method function.
	 *
	 * @access public
	 * @param mixed $methods
	 * @return void
	 */
	function wc_oca_add_method( $methods ) {
		$methods[] = 'WC_Shipping_Oca';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'wc_oca_add_method' );

	/**
	 * wc_oca_scripts function.
	 */
	function wc_oca_scripts() {
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	add_action( 'admin_enqueue_scripts', 'wc_oca_scripts' );

	//Only Numbers
		add_action( 'wp_footer', 'only_numbers_oca', 100 );
		function only_numbers_oca(){  ?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery('#calc_shipping_postcode').attr({ maxLength : 4 });
					jQuery('#billing_postcode').attr({ maxLength : 4 });
					jQuery('#shipping_postcode').attr({ maxLength : 4 });

			         jQuery("#calc_shipping_postcode").keypress(function (e) {
			          if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
			          	return false;
			          }
			        });
			        jQuery("#billing_postcode").keypress(function (e) { 
			          if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) { 
			          return false;
			          }
			        });
			        jQuery("#shipping_postcode").keypress(function (e) {  
			          if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
			          return false;
			          }
			        });
				});
			</script>
		 
 		<?php }

	$oca_settings = get_option( 'woocommerce_oca_settings', array() );
	
}