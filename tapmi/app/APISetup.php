<?php

/**
 * API Setup Class
 *
 * @package  Laravel
 * @author   Ferdinand
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use Session;

class APISetup extends Model {

	/**
	 * Untuk mendefinisikan url-url yang dipakai dalam website
	 *
	 * @var array
	 */
	public static function url() {

		$env = 'dev';

		$data = array(
			"dev" => array(
				"msa" => array(
					"ins" => array(
						"auth" => "http://149.129.250.199:3008",
						"hectarestatement" => "149.129.250.199:3009",
						"report" => "http://149.129.250.199:3013",
						"ebccvalidation" => "http://149.129.250.199:3014",
					)
				)
			),
			"qa" => array(
				"msa" => array(
					"ins" => array(
						"auth" => "http://149.129.244.86:3008",
						"hectarestatement" => "http://149.129.244.86:3009",
						"report" => "http://149.129.250.199:3013",
						"ebccvalidation" => "http://149.129.250.199:3014",
					)
				)
			),
			"prod" => array(
				"msa" => array(
					"ins" => array(
						"auth" => "http://app.tap-agri.com/mobileinspection/ins-msa-auth",
						"hectarestatement" => "http://app.tap-agri.com/mobileinspection/ins-msa-hectarestatement",
						"report" => "http://149.129.250.199:3013",
						"ebccvalidation" => "http://149.129.250.199:3014",
					)
				)
			)
		);

		if ( isset( $data[$env] ) ) {
			return $data[$env];
		}
		else {
			return null;
		}
		
	}

	/**
	 * Untuk mendefinisikan url-url yang dipakai dalam website
	 *
	 * @var array
	 */
	public static function ins_rest_client( $method, $url, $body = array() ) {
		$client = new \GuzzleHttp\Client();
		$init_headers = array(
			"Authorization" => 'Bearer '.session( 'ACCESS_TOKEN' )
		);
		$init_body = $body;
		$init = array();

		switch ( $method ) {
			case 'GET':
				$init = array(
					"headers" => $init_headers
				);
			break;
			case 'POST':
				$init = array(
					"headers" => $init_headers,
					"json" => $init_body
				);
			break;
			case 'PUT':
				$init = array(
					"headers" => $init_headers,
					"json" => $init_body
				);
			break;
			case 'DELETE':
				$init = array(
					"headers" => $init_headers
				);
			break;
		}

		$result = $client->request( $method, $url, $init );
		$data = json_decode( $result->getBody(), true );

		return $data;
	}

}