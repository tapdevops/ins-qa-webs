<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Session;

class APIDataSource extends Model {

	protected $ins_msa;

	public function __construct() {
		$this->ins_msa = 'ABC';
	}

	

	public static function user_search( $url, $parameter = '' ) {
		$client = new \GuzzleHttp\Client();
		$result = $client->request( 'GET', $url.$parameter, [
			'headers' => [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			]
		]);
		$data = json_decode( $result->getBody(), true );

		return $data;
	}

	public static function data_modules( $parameter = '' ) {
		$client = new \GuzzleHttp\Client();
		$result = $client->request( 'GET', 'http://149.129.245.230:3008/api/modules'.$parameter, [
			'headers' => [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			]
		]);
		$data = json_decode( $result->getBody() );
		return $data;
	}

	public static function data_parameter( $parameter = '' ) {
		$client = new \GuzzleHttp\Client();
		$result = $client->request( 'GET', 'http://149.129.245.230:3008/api/parameter'.$parameter, [
			'headers' => [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			]
		]);
		$data = json_decode( $result->getBody() );
		return $data;
	}

	public static function data_userauthorization( $parameter = '' ) {
		$client = new \GuzzleHttp\Client();
		$result = $client->request( 'GET', 'http://149.129.245.230:3008/api/user-authorization'.$parameter, [
			'headers' => [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			]
		]);
		$data = json_decode( $result->getBody(), true );
		return $data;
	}

	public static function data_user( $parameter = '' ) {
		$client = new \GuzzleHttp\Client();
		$result = $client->request( 'GET', 'http://149.129.245.230:3008/api/user'.$parameter, [
			'headers' => [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			]
		]);
		$data = json_decode( $result->getBody(), true );
		return $data;
	}

	public static function data_employee_hris( $parameter = '' ) {
		$client = new \GuzzleHttp\Client();
		$result = $client->request( 'GET', 'http://149.129.245.230:3008/api/employee-hris'.$parameter, [
			'headers' => [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			]
		]);
		$data = json_decode( $result->getBody(), true );
		return $data;
	}

}