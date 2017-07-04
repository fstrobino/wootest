<?php
/**
* envioPack API Class
* fstrobino Campos Verdes - 2017
*/

class EnvioPack {
	const VERSION				= '1';
	protected $webservice_url	= 'https://api.enviopack.com';
		
	public function __construct() {
	}
	
	
	public function setUserAgent() {
		return 'ENVIOPACK-PHP-API ' . self::VERSION . ' - FSTROBINO/ENVIOPACK-PHP-API';
	}

	public function getAccessToken($api_key = '', $api_secret = '') {

		$data = array("api-key" => $api_key, "secret-key" => $api_secret);
		$data_string = json_encode($data);
		
		// Vamos a hacer CURL POST
		$ch = curl_init("{$this->webservice_url}/auth");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->setUserAgent());
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    			'Content-Type: application/json',
    			'Content-Length: ' . strlen($data_string))
		);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		
		//execute post
		$result = curl_exec($ch);
		//decode json to array
		$response_array = json_decode($result);
		//close connection
		curl_close($ch);
		
		return $response_array;				
	}	


	public function calcularEnvioPorDestino($accessToken, $provincia, $codigoPostal, $peso, $servicio) {
		
		$query_string = array(	'access_token'	=> $accessToken,
								'provincia'		=> $provincia,
								'codigo_postal'	=> $codigoPostal,
								'peso'			=> $peso,
								'servicio'		=> $servicio);

		$url = "{$this->webservice_url}/cotizar/precio/por-provincia";

		// Vamos a hacer CURL GET

		// create curl resource
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);

		// set url
		$query = http_build_query($query_string);
		curl_setopt($ch, CURLOPT_URL, "$url?$query");
		
		//execute call
		$result = curl_exec($ch);
		//decode json to array
		$response_array = json_decode($result);
		//close connection
		curl_close($ch);
		
		return $response_array;
	}
}