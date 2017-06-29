<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FS_WC_Shipping_Enviopack class.
 *
 * @extends WC_Shipping_Method
 */
class FS_WC_Shipping_Enviopack extends WC_Shipping_Method {
	private $default_boxes;
	private $found_rates;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                               = 'oca';
		$this->method_title                     = __( 'OCA Express Pak', 'fs-wc-shipping-enviopack' );
		$this->default_boxes                    = include( 'data/data-fs-wc-boxsizes.php' );
		$this->init();
	}

	/**
	 * init function.
	 */
	private function init() {
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title           = $this->get_option( 'title', $this->method_title );
		$this->origin          = apply_filters( 'woocommerce_enviopack_origin_postal_code', str_replace( ' ', '', strtoupper( $this->get_option( 'origin' ) ) ) );
		$this->origin_country  = apply_filters( 'woocommerce_enviopack_origin_country_code', WC()->countries->get_base_country() );
		$this->cuit_number    = $this->get_option( 'cuit_number' );
		$this->ajuste_precio = $this->get_option( 'ajuste_precio' );
		$this->tipo_servicio    = $this->get_option( 'tipo_servicio' );
		$this->debug           = ( $bool = $this->get_option( 'debug' ) ) && $bool == 'yes' ? true : false;
 		$this->services           = $this->get_option( 'services', array( ));

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Output a message
	 */
	public function debug( $message, $type = 'notice' ) {
		if ( $this->debug ) {
			wc_add_notice( $message, $type );
		}
	}

	/**
	 * environment_check function.
	 */
	private function environment_check() {
		if ( ! in_array( WC()->countries->get_base_country(), array( 'AR' ) ) ) {
			echo '<div class="error">
				<p>' . __( 'Argentina tiene que ser el pais de Origen.', 'fs-wc-shipping-enviopack' ) . '</p>
			</div>';
		} elseif ( ! $this->origin && $this->enabled == 'yes' ) {
			echo '<div class="error">
				<p>' . __( 'envioPack esta activo, pero no hay Codigo Postal.', 'fs-wc-shipping-enviopack' ) . '</p>
			</div>';
		}
	}

	/**
	 * admin_options function.
	 */
	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();

		// Show settings
		parent::admin_options();
	}

	/**
	 * init_form_fields function.
	 */
	public function init_form_fields() {
		$this->form_fields  = include( 'data/data-fs-wc-settings.php' );
	}

		/**
	 * generate_box_packing_html function.
	 */
	public function generate_service_html() {
		ob_start();
		include( 'data/fs-wc-services.php' );
		return ob_get_clean();
	}

	
	/**
	 * validate_box_packing_field function.
	 *
	 * @param mixed $key
	 */
		public function validate_service_field( $key ) {
 		$service_name     = isset( $_POST['service_name'] ) ? $_POST['service_name'] : array();
		$service_operativa     = isset( $_POST['service_operativa'] ) ? $_POST['service_operativa'] : array();
		$service_enabled    = isset( $_POST['service_enabled'] ) ? $_POST['service_enabled'] : array();
 
		$services = array();

		if ( ! empty( $service_operativa ) && sizeof( $service_operativa ) > 0 ) {
			for ( $i = 0; $i <= max( array_keys( $service_operativa ) ); $i ++ ) {

				if ( ! isset( $service_operativa[ $i ] ) )
					continue;
		
				if ( $service_operativa[ $i ] ) {
  					$services[] = array(
 						'name'     =>  $service_name[ $i ] ,
						'operativa'     => floatval( $service_operativa[ $i ] ),
						'enabled'    => isset( $service_enabled[ $i ] ) ? true : false
					);
				}
			}
 
		}
		return $services;
	}

	/**
	 * Get packages - divide the WC package into packages/parcels suitable for a OCA quote
	 */
	public function get_oca_packages( $package ) {
		switch ( $this->packing_method ) {
			case 'box_packing' :
				return $this->box_shipping( $package );
			break;
			case 'per_item' :
			default :
				return $this->per_item_shipping( $package );
			break;
		}
	}

	/**
	 * per_item_shipping function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return array
	 */
	private function per_item_shipping( $package ) {
		$to_ship  = array();
		$group_id = 1;

		// Get weight of order
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'woocommerce-shipping-oca' ), $item_id ), 'error' );
				continue;
			}

			if ( ! $values['data']->get_weight() ) {
				$this->debug( sprintf( __( 'Product # is missing weight. Aborting.', 'woocommerce-shipping-oca' ), $item_id ), 'error' );
				return;
			}

			$group = array();

			$group = array(
				'GroupNumber'       => $group_id,
				'GroupPackageCount' => $values['quantity'],
				'Weight' => array(
					'Value' => $values['data']->get_weight(),
					'Units' => 'KG'
				),
				'packed_products' => array( $values['data'] )
			);

			if ( $values['data']->length && $values['data']->height && $values['data']->width ) {

				$dimensions = array( $values['data']->length, $values['data']->width, $values['data']->height );

				sort( $dimensions );

				$group['Dimensions'] = array(
					'Length' => $values['data']->length,
					'Width'  => $values['data']->width,
					'Height' => $values['data']->height,
					'Units'  => 'CM'
				);
			}

			$group['InsuredValue'] = array(
				'Amount'   => round( $values['data']->get_price() ),
				'Currency' => get_woocommerce_currency()
			);

			$to_ship[] = $group;

			$group_id++;
		}

		return $to_ship;
	}


	/**
	 * calculate_shipping function.
	 *
	 * @param mixed $package
	 */
	public function calculate_shipping( $package=array() ) {
		// Debugging
		$this->debug( __( 'OCA Express Pak modo de depuración está activado - para ocultar estos mensajes, desactive el modo de depuración en los ajustes.', 'woocommerce-shipping-oca' ) );

		// Get requests
		$oca_packages   = $this->get_oca_packages( $package );
				
		// Ensure rates were found for all packages
		$packages_to_quote_count = sizeof( $oca_requests );
 		
		$oca_package = $oca_packages[0]['GroupPackageCount'];		
		
		foreach ($oca_packages as $key) {
			$oca_package = $key['GroupPackageCount'];
	 		$oca_weight = $key['Weight']['Value'] * $oca_package;
			$oca_lenth = $key['Dimensions']['Length'] * $oca_package;
			$oca_width = $key['Dimensions']['Width'] * $oca_package;		
			$oca_height = $key['Dimensions']['Height'] * $oca_package;	
			$oca_amount = $key['InsuredValue']['Amount'];	
			$oca_weightb += $oca_weight;
 			$oca_volume = $oca_lenth * $oca_width * $oca_height * $oca_package;
 			$oca_volumeb += $oca_volume;
 			$oca_packageb = 1;
		}
					
 		$oca_volumesy = $oca_volumeb / 1000000;
    	$oca_volumesy = number_format($oca_volumesy, 10);


		$seguro = round($package['contents_cost']);

	 	$mercado_pago = $this->mercado_pago;
 		if($mercado_pago =='1'){
			add_filter( 'woocommerce_cart_shipping_method_full_label', 'remove_local_pickup_free_label', 10, 2 );
			function remove_local_pickup_free_label($full_label, $method){
				$full_label = str_replace("(Gratis)","",$full_label);
			return $full_label;
			}
		}			
		foreach($this->services as $services) {
			
			if($services['enabled'] == 1){
  				$oca = new Oca($cuit = $this->cuit_number, $operativa = $services['operativa']);
				$oca_response  = $oca->tarifarEnvioCorporativo($oca_weightb, $oca_volumesy, $this->origin, $package['destination']['postcode'], $oca_packageb, $seguro);
				$porcentaje = $oca_response[0]['Total'] * $this->ajuste_precio / 100;

				$precio = $oca_response[0]['Total'] + $porcentaje;
				
				if($mercado_pago =='1'){
					$titulo = $this->method_title . ' - ' . $services['name'] . ' - $' . $precio ;
					$precio = '0';
				} else {
					$titulo = $this->method_title . ' - ' . $services['name'];
				}

				$rate = array(
					'id' => sprintf("%s-%s", $titulo, $services['name']),
					'label' => sprintf("%s", $titulo),
					'cost' => $precio,
					'calc_tax' => 'per_item'
				);	
				$this->add_rate( $rate );
			}
			
		}	
 	 
 	}

	/**
	 * Run requests and get/parse results
	 * @param  array $requests
	 */
	public function run_package_request( $requests ) {
		try {
			foreach ( $requests as $key => $request ) {
				$this->process_result( $this->get_result( $request ) );
			}
		} catch ( Exception $e ) {
			$this->debug( print_r( $e, true ), 'error' );
			return false;
		}
	}

	/**
	 * get_result function.
	 *
	 * @access private
	 * @param mixed $request
	 * @return array
	 */
	private function get_result( $request ) {
		$this->debug( 'OCA REQUEST: <a href="#" class="debug_reveal">Reveal</a><pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r( $request, true ) . '</pre>' );

		//$result = $client->getRates( $request );

		wc_enqueue_js( "
			jQuery('a.debug_reveal').on('click', function(){
				jQuery(this).closest('div').find('.debug_info').slideDown();
				jQuery(this).remove();
				return false;
			});
			jQuery('pre.debug_info').hide();
		" );

		return $result;
	}

	/**
	 * process_result function.
	 *
	 * @access private
	 * @param mixed $result
	 * @return void
	 */
	private function process_result( $result = '' ) {
		if ( $result && ! empty ( $result->RateReplyDetails ) ) {

			$rate_reply_details = $result->RateReplyDetails;

		}
	}

	/**
	 * sort_rates function.
	 *
	 * @param mixed $a
	 * @param mixed $b
	 * @return int
	 */
	public function sort_rates( $a, $b ) {
		if ( $a['sort'] == $b['sort'] ) return 0;
		return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
	}
}