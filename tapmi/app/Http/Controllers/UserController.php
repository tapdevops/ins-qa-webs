<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use View;
use Validator;
use Redirect;
use Session;
use Config;
use URL;

# API Setup
use App\APISetup;
use App\APIData as Data;

class UserController extends Controller {

	protected $api;
	protected $active_menu;

	#   		 								  				  ▁ ▂ ▄ ▅ ▆ ▇ █ CONSTRUCTOR
	# -------------------------------------------------------------------------------------
	public function __construct() {
		$this->url_api_ins_msa_auth = APISetup::url()['msa']['ins']['auth'];
		$this->active_menu = '_'.str_replace( '.', '', '02.05.00.00.00' ).'_';
	}

	#   		 								  				        ▁ ▂ ▄ ▅ ▆ ▇ █ INDEX
	# -------------------------------------------------------------------------------------
	public function index() {

		$allowed_role = array( "ADMIN" );
		$data['active_menu'] = $this->active_menu;

		if ( in_array( session('USER_ROLE'), $allowed_role ) ) {
			$data['master_user'] = array();
			if ( !empty( Data::user_find() ) ) {
				$i = 0;
				foreach ( Data::user_find() as $q ) {
					if ( isset( $q['JOB'] ) && isset( $q['FULLNAME'] ) ) {
						$data['master_user'][$i]['USER_AUTH_CODE'] = $q['USER_AUTH_CODE'];
						$data['master_user'][$i]['EMPLOYEE_NIK'] = $q['EMPLOYEE_NIK'];
						$data['master_user'][$i]['USER_ROLE'] = $q['USER_ROLE'];
						$data['master_user'][$i]['LOCATION_CODE'] = $q['LOCATION_CODE'];
						$data['master_user'][$i]['REF_ROLE'] = $q['REF_ROLE'];
						$data['master_user'][$i]['JOB'] = $q['JOB'];
						$data['master_user'][$i]['FULLNAME'] = $q['FULLNAME'];

						$i++;
					}
					
				}
			}

			return view( 'user.index', $data );
		}
		
	}

	#   		 								  				  ▁ ▂ ▄ ▅ ▆ ▇ █ CREATE USER
	# -------------------------------------------------------------------------------------
	public function create() {
		$allowed_role = array( "ADMIN" );

		if ( in_array( session('USER_ROLE'), $allowed_role ) ) {
			$data['parameter'] = Data::parameter_find( '?PARAMETER_GROUP=USER_ROLE' );
			return view( 'user.create', $data );
		}
	}

	#   		 								  		   ▁ ▂ ▄ ▅ ▆ ▇ █ CREATE USER PROSES
	# -------------------------------------------------------------------------------------
	public function create_proses() {

		$client = new \GuzzleHttp\Client();
		$res = $client->request( 'POST', $this->url_api_ins_msa_auth.'/api/user', [
			'json' => [
				'EMPLOYEE_NIK' => Input::get( 'EMPLOYEE_NIK'),
				'USER_ROLE' => Input::get( 'ROLES'),
				'USERNAME' => Input::get( 'USERNAME'),
				'REF_ROLE' => Input::get( 'REFFERENCE_ROLE'),
				'LOCATION_CODE' => Input::get( 'LOCATION')
			],
			'headers' => [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			]
		]);
		$data = json_decode( $res->getBody(), true );

		return response()->json( $data );
	}

	#   		 								  				  ▁ ▂ ▄ ▅ ▆ ▇ █ SEARCH USER
	# -------------------------------------------------------------------------------------
	#
	# Mencari user dari tabel HRIS dan SAP sesuai dengan data yang diinput pada parameter "q".
	# Max result HRIS dan SAP yang ditampilkan 20.
	#
	public function search_user() {
		
		$data['total_count'] = 0;
		$data['items'] = array();
		$data['incomplete_results'] = false;
		$url = $this->url_api_ins_msa_auth.'/api/user-search?q='.$_GET['q'];

		if ( isset( $_GET['q'] ) ) {
			$client = APISetup::ins_rest_client( 'GET', $url );
			$i = 0;

			if ( $client['status'] == true ) {
				if ( count( $client['data'] ) > 0 ) {
					$data['total_count'] = count( $client['data'] );
					foreach ( $client['data'] as $c ) {
						$data['items'][$i]['id'] = $c['NIK'];
						$data['items'][$i]['text'] = $c['NAMA_LENGKAP'];
						$data['items'][$i]['description'] = $c['JOB_CODE'];
						$i++;
					}
				}
			}
		}

		return response()->json( $data );
	}

	#   		 								  				         ▁ ▂ ▄ ▅ ▆ ▇ █ EDIT
	# -------------------------------------------------------------------------------------
	public function edit( Request $req ) {
		$userdata = Data::user_find_one( $req->id );
		if ( !empty( $userdata['items'] ) ) {
			$data['id'] = $req->id;
			$data['refrole'] = Data::refrole_find();
			$data['parameter'] = Data::parameter_find( '?PARAMETER_GROUP=USER_ROLE' );
			$data['user'] = $userdata['items'];

			return view( 'user.edit', $data );
		}
	}

	#   		 								  				  ▁ ▂ ▄ ▅ ▆ ▇ █ EDIT PROSES
	# -------------------------------------------------------------------------------------
	public function edit_proses( Request $req ) {
		$client = new \GuzzleHttp\Client();
		$res = $client->request( 'PUT', $this->url_api_ins_msa_auth.'/api/user/'.$req->id, [
			'json' => [
				'USER_ROLE' => Input::get( 'USER_ROLE'),
				'REF_ROLE' => Input::get( 'REFFERENCE_ROLE'),
				'LOCATION_CODE' => Input::get( 'LOCATION_CODE')
			],
			'headers' => [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			]
		]);
		$data = json_decode( $res->getBody(), true );

		return response()->json( $data );
	}

}