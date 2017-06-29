<?php

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



/**

 * Array of settings

 */

return array(

	'enabled'          => array(

		'title'           => __( 'Activar envioPack', 'fs-wc-shipping-enviopack' ),

		'type'            => 'checkbox',

		'label'           => __( 'Activar este método de envió', 'fs-wc-shipping-enviopack' ),

		'default'         => 'no'

	),

	'debug'      => array(

		'title'           => __( 'Modo Depuración', 'fs-wc-shipping-enviopack' ),

		'label'           => __( 'Activar modo depuración', 'fs-wc-shipping-enviopack' ),

		'type'            => 'checkbox',

		'default'         => 'no',

		'desc_tip'    => true,

		'description'     => __( 'Activar el modo de depuración para mostrar información', 'fs-wc-shipping-enviopack' )

	),

	'title'            => array(

		'title'           => __( 'Título', 'fs-wc-shipping-enviopack' ),

		'type'            => 'text',

		'description'     => __( 'Controla el título que el usuario ve durante el pago.', 'fs-wc-shipping-enviopack' ),

		'default'         => __( 'envioPack', 'fs-wc-shipping-enviopack' ),

		'desc_tip'        => true

	),

	'origin'           => array(

		'title'           => __( 'Código Postal de Origen', 'fs-wc-shipping-enviopack' ),

		'type'            => 'text',

		'description'     => __( 'Ingrese el código postal del <strong> remitente </ strong>.', 'fs-wc-shipping-enviopack' ),

		'default'         => '',

		'desc_tip'        => true

    ),

    'api'              => array(

		'title'           => __( 'Configuración de la API', 'fs-wc-shipping-enviopack' ),

		'type'            => 'title',

		'description'     => __( 'Sus datos de acceso de la API se obtienen de la página web envioPack', 'fs-wc-shipping-enviopack' ),

    ),

    'cuit_number'           => array(

		'title'           => __( 'Número de CUIT', 'fs-wc-shipping-enviopack' ),

		'type'            => 'text',

		'description'     => __( '', 'fs-wc-shipping-enviopack' ),

		'default'         => __( '', 'fs-wc-shipping-enviopack' ),

    	'placeholder' => __( '00-00000000-0', 'meta-box' ),



    ),

    'ajuste_precio'           => array(

		'title'           => __( 'Ajustar Costos %', 'fs-wc-shipping-enviopack' ),

		'type'            => 'text',

		'description'     => __( 'Agregar costo extra al precio.', 'fs-wc-shipping-enviopack' ),

		'default'         => __( '', 'fs-wc-shipping-enviopack' ),

    	'placeholder' => __( '1%', 'meta-box' ),		

    ),

    'packing'           => array(

		'title'           => __( 'Paquetes', 'fs-wc-shipping-enviopack' ),

		'type'            => 'title',

		'description'     => __( 'Los siguientes ajustes determinan cómo los artículos se embalan', 'fs-wc-shipping-enviopack' ),

    ),

	'packing_method'   => array(

		'title'           => __( 'Método Embalaje', 'fs-wc-shipping-enviopack' ),

		'type'            => 'select',

		'default'         => '',

		'class'           => 'packing_method',

		'options'         => array(

			'per_item'       => __( 'Por defecto: artículos individuales', 'fs-wc-shipping-enviopack' ),

		),

	),

 	'services'  => array(

		'type'            => 'service'

	),

);