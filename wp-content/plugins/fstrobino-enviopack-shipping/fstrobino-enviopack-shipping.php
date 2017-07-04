<?php
/*
	Plugin Name: WooCommerce envioPack Shipping
	Plugin URI: https://github.com/fstrobino/woocommerce-envioPack-plugin
	Description: Obtain shipping rates dynamically via envioPack API for your orders.
	Version: 0.1.0
	Author: Federico Strobino
	Author URI: https://github.com/fstrobino
	Copyright: 2017 fstrobino

*/

/**
 * Required functions
 */
require_once( 'includes/fstrobino-woocommerce-functions.php' );

/**
 * Plugin page links
 */
function wc_enviopack_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="https://github.com/fstrobino">' . __( 'Consultas', 'woocommerce-shipping-enviopack' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_enviopack_plugin_links' );

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function fstrobino_enviopack_shipping_method() {
        if ( ! class_exists( 'Fstrobino_Enviopack_Shipping_Method' ) ) {
            class Fstrobino_Enviopack_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'fstrobino_enviopack'; 
                    $this->method_title       = __( 'envioPack Shipping', 'fstrobino_enviopack' );  
                    $this->method_description = __( 'Calcular costos de envios por envioPack', 'fstrobino_enviopack' ); 
                    
                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array(
                        'AR' // Croatia
                    );

                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Enviopack Shipping', 'fstrobino_enviopack' );
                }
 
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

				/**
				* environment_check function.
				*/
				private function environment_check() {

                    $message_var = "environment_check";
                    if ( class_exists( 'PC' ) ) { 
                        PC::debug( $message_var, 'environment_check' ); 
                    }

					if ( ! in_array( WC()->countries->get_base_country(), array( 'AR' ) ) ) {
						echo '<div class="error">
							<p>' . __( 'Argentina tiene que ser el pais de Origen.', 'fstrobino_enviopack' ) . '</p>
						</div>';
					} elseif ( ! $this->origin && $this->enabled == 'yes' ) {
						echo '<div class="error">
							<p>' . __( 'envioPack esta activo, pero no hay Codigo Postal.', 'fstrobino_enviopack' ) . '</p>
						</div>';
					}
				}
 
                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    $this->form_fields = array(
            
                    'enabled' => array(
                        'title' => __( 'Enable', 'fstrobino_enviopack' ),
                        'type' => 'checkbox',
                        'description' => __( 'Enable this shipping.', 'fstrobino_enviopack' ),
                        'default' => 'yes'
                        ),
            
                    'title' => array(
                        'title' => __( 'Title', 'fstrobino_enviopack' ),
                        'type' => 'text',
                        'description' => __( 'Title to be display on site', 'fstrobino_enviopack' ),
                        'default' => __( 'envioPack Shipping', 'fstrobino_enviopack' )
                        ),
					'weight' => array(
						'title' => __( 'Peso (kg)', 'fstrobino_enviopack' ),
						'type' => 'number',
						'description' => __( 'Peso maximo aceptado', 'fstrobino_enviopack' ),
						'default' => 100
						),
            
                    );
            
                }
            
                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package ) {
                   
					$weight = 0;
					$cost = 0;
					$country = $package["destination"]["country"];
                    $state = $package["destination"]["state"];
					$postal_code = $package[ 'destination' ][ 'postcode' ];
					
					foreach ( $package['contents'] as $item_id => $values ) 
					{ 
						$_product = $values['data']; 
						$weight = $weight + $_product->get_weight() * $values['quantity']; 
					}
					
					$weight = wc_get_weight( $weight, 'kg' );

                    //FIXME quitar del plugin este logger
                    $message_var = "calculate_shipping: valores de la compra: pais {$country} provincia {$state} postal {$postal_code} peso {$weight} ";
                    if ( class_exists( 'PC' ) ) { 
                        PC::debug( $message_var, 'calculate_shipping log' ); 
                    }


                    //@FIXME mover a los settings del shipping method 
                    $api_key='';
                    $api_secret='';

                    $envioPack = new EnvioPack();
                    $token_array = $envioPack->getAccessToken($api_key,$api_secret);
                    $token = $token_array->token;//"eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJleHAiOjE0OTkxOTI5NTMsInVzZXJuYW1lIjoiOGJkZjA3ZTRhZmNkMDEwNGM3YWU5MTMwNjg4MDRjZTM1YmNmNTEwNiIsImlhdCI6IjE0OTkxNzg1NTMifQ.fRxgixs3BaZSW4H6r2J3m_hxxK-a-XOfGKYOGIPmCGKBHOLdNm9N_J0GLAe6FqxXIvoSrKKT6iU8wnOheUGGidVFYOXnHjkjxCICD-5I9vvbtNt4IHBQzL4DBdITNwVJ6oH_EoE4FJF5UsWBFerGEYD4MjG1iEHmFEb1paaXG4PEUMxs9rmMH8hMa3cfHJHvt5JGWrJBJ1kUvEsUB7yAwKGEp3a-dnK9w9L8D8kC8bPqRedwMXuR8x7y7sLzmcUhgtE_BZUz5tfgr8JkbEjL8IqPGA3iUJ9yFcNyagb9IIC4owOxXK5Ep60VNss5t32CF-_hW7_wNgs5BKZlKJAydlAe9Rq5qoxvitXdVL_GVx_-VPtH1DcC-2LSIUrIhaUPIR0NDoQxR_j1Z_b_bEE9YIooHRLf0vSeXu4hLoEPXqYrlE_bSZ7s-Wz1mLRyituJWjj_9JEQIXrZxBrgaknTBdT-2tuSITRzx-RNlITC1lkabX04CqbFqZj_rUBKQMsgOh4_Qgs7GqPllRB2PzA916nHxI0dlzOUjt5ZVTZhPeb-2ZmFt7pvm_9ibbgVV2c8PAxNzHSego8nOtNU80_oR6GQac8kfv-AyD48eccSoMiI_ayQZs9tIB_yWFxcc54m1ECtBWmwAMCrlQVw_MmkWEFKleC1CZLRm4aNWVpWFj4";
                    $tarifas = $envioPack->calcularEnvioPorDestino($token,$state,$postal_code,$weight,'N');
                    
                    foreach ($tarifas as $tarifa) {

                        if("D" === $tarifa->modalidad && "N" === $tarifa->servicio){
                            $rateLabel = "Servicio estandard a domicilio en {$tarifa->horas_entrega} hs aprox.";
                            $rateId = "envioPack-{$tarifa->modalidad}-{$tarifa->servicio}--{$tarifa->valor}";
                            $rate = array(
                                'id' => $rateId,
                                'label' => $rateLabel,
                                'cost' => $tarifa->valor,
                            );
                            $this->add_rate( $rate);
                        }
                    }
					
            	}
            }    
        }
    }

    add_action( 'init', 'php_console_example' ); // Run the function on init
    function php_console_example() {
        $message_var = "Here's an example of logging the contents of a variable with PHP Console!"; // A simple string var.
        if ( class_exists( 'PC' ) ) { // Prevent your site from breaking if the 'PC' class has not been registered.
           // PC::debug( $message_var, 'Example' ); // Here's where the magic happens: The contents of $message_var will be logged with the title 'Example'.
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'fstrobino_enviopack_shipping_method' );
 
    function add_fstrobino_enviopack_shipping_method( $methods ) {
        $methods[] = 'Fstrobino_Enviopack_Shipping_Method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_fstrobino_enviopack_shipping_method' );
}