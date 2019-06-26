<?php

/**
 * API Setup Class
 *
 * @package  Laravel
 * @author   Ferdinand
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
						"auth" => "http://149.129.250.199:4008",
						//"auth" => "http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-auth",
						"hectarestatement" => "http://app.tap-agri.com/mobileinspection/ins-msa-hectarestatement",
						//"hectarestatement" => "http://app.tap-agri.com/mobileinspection/ins-msa-hectarestatement",
						"finding" => "http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-finding",
						"inspection" => "http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-inspection",
						// "inspection" => "http://app.tap-agri.com/mobileinspection/ins-msa-inspection",
						// "inspection" => "http://localhost:4010",
						"report" => "http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-reports",
						// "report" => "http://149.129.250.199:4013",
						"ebccvalidation" => "http://149.129.250.199:4014",
					)
				)
			),
			"qa" => array(
				"msa" => array(
					"ins" => array(
						"auth" => "http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-auth",
						"hectarestatement" => "http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-hectarestatement",
						"report" => "http://app.tap-agri.com/mobileinspectiondev/ins-msa-dev-reports",
						"ebccvalidation" => "http://149.129.250.199:3014",
					)
				)
			),
			"prod" => array(
				"msa" => array(
					"ins" => array(
						"auth" => "http://app.tap-agri.com/mobileinspection/ins-msa-auth",
						"hectarestatement" => "http://app.tap-agri.com/mobileinspection/ins-msa-hectarestatement",
						"report" => "http://app.tap-agri.com/mobileinspection/ins-msa-reports",
						"ebccvalidation" => "http://app.tap-agri.com/mobileinspection/ins-msa-ebccval",
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

	/**
	 * Untuk mendefinisikan url-url yang dipakai dalam website dengan manual token
	 *
	 * @var array
	 */
	public static function ins_rest_client_manual( $method, $url, $body = array() ) {
		$client = new \GuzzleHttp\Client();
		$init_headers = array(
			"Authorization" => "Bearer ".Storage::get( 'files/access_token_mobile_inspection.txt' )
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