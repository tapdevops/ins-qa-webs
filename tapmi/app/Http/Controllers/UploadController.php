<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

use App\APISetup;
use App\ReportOracle;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use File;
// use PDF;

class UploadController extends Controller {
	protected $url_api_ins_msa_hectarestatement;
	protected $url_api_ins_msa_auth;
	protected $active_menu;
	
	public function __construct() {
		$this->active_menu = '_' . str_replace('.', '', '02.03.00.00.00') . '_';
		$this->url_api_ins_msa_hectarestatement = APISetup::url()['msa']['ins']['hectarestatement'];
		$this->url_api_ins_msa_auth = APISetup::url()['msa']['ins']['auth'];
		$this->url_api_ins_msa_image = APISetup::url()['msa']['ins']['image'];
		$this->db_mobile_ins = DB::connection('mobile_ins');
	}

	public function phpinfo() {
		phpinfo();
	}

	# Untuk mengimport Database Realm maupun berformat JSON
	public function import_data() {
		$data['active_menu'] = $this->active_menu;

		return view( 'orareport.import_data', $data );
	}
	
	# Untuk mengimport Photo dalam Zip
	public function import_photo() {
		$data['active_menu'] = $this->active_menu;

		return view( 'orareport.import_photo', $data );
	}

	public function import_data_log() {
		$USER_AUTH_CODE = session( 'USER_AUTH_CODE' );
		$query_result = json_encode( $this->db_mobile_ins->select( "
			SELECT 
				* 
			FROM 
				T_LOG_IMPORT_DB 
			WHERE 
				TRUNC( INSERT_DATE ) = TO_DATE( TO_CHAR( SYSDATE, 'RRRR-MM-DD' ), 'RRRR-MM-DD' ) 
				AND USER_AUTH_CODE = '{$USER_AUTH_CODE}'
		" ), true );
		$datatable = array(
			"data" => json_decode( $query_result )
		);
		return response()->json( $datatable );
	}

	# Untuk proses import Database Realm maupun berformat JSON
	public function import_data_process( Request $req ) {

		# Default Response
		$data['status'] = false;
		$data['message'] = 'File not valid.';
		$data['data'] = array();
		$http_status_code = 404;

		if ( $req->hasFile( 'file' ) ) {
			$temp = $req->file( 'file' );
			$temp->move( public_path( 'uploads/'.date( 'Y-m-d' ) ), $temp->getClientOriginalName() );
			$file_name = $temp->getClientOriginalName();

			try {
				$client = new \GuzzleHttp\Client();
				$url_import_db = $this->url_api_ins_msa_auth . '/api/v2.1/import/database';
				$response = $client->request( 'POST', $url_import_db, [
						 'headers' => [
							'Accept' => 'application/zip',
							'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' ),
						], 
						'multipart' => [
							[
								'name' => 'JSON',
								'contents' => fopen( './uploads/'.date( 'Y-m-d' ).'/'.$file_name, 'r' ),
								'filename' => $file_name
							]
						]
					]
				);
				$response = json_decode( $response->getBody(), true );

				if ( $response['status'] == true ) {
					$http_status_code = 200;
					$data['status'] = true;
					$data['message'] = $response['message'];
				}
				$QV_STATUS = ( $response['status'] == true ? 1 : 0 );
				$QV_USER_AUTH_CODE = session( 'USER_AUTH_CODE' );
				/* $insert_db = $this->db_mobile_ins->insert( "
					INSERT INTO 
						T_LOG_IMPORT_DB( 
							USER_AUTH_CODE, 
							FILENAME, 
							INSERT_DATE,
							STATUS
						)
					VALUES( 
						'{$QV_USER_AUTH_CODE}', 
						'{$file_name}', 
						SYSDATE,
						$QV_STATUS
					)
				" ); */
			}
			catch( \Exception $e ) {
				$data['message'] = $e->getMessage();
			}
		}

		//return response()->json( $data, $http_status_code );
		return redirect()->back() ->with('alert', $data['message']);
	}
	
	# Untuk proses import photo maupun berformat ZIP
	public function import_photo_process( Request $req ) {

		# Default Response
		$data['status'] = false;
		$data['message'] = 'File not valid.';
		$data['data'] = array();
		$http_status_code = 404;

		if ( $req->hasFile( 'file' ) ) {
			$temp = $req->file( 'file' );
			$temp->move( public_path( 'uploads/photos/'.date( 'Y-m-d' ) ), $temp->getClientOriginalName() );
			$file_name = $temp->getClientOriginalName();

			try {
				$client = new \GuzzleHttp\Client();
				$url_import_photo = $this->url_api_ins_msa_image . '/api/v2.0/web/upload/foto-transaksi';
				$response = $client->request( 'POST', $url_import_photo, [
						 'headers' => [
							'Accept' => 'application/zip',
							'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' ),
						], 
						'multipart' => [
							[
								'name' => 'IMAGES_ZIP',
								'contents' => fopen( './uploads/photos/'.date( 'Y-m-d' ).'/'.$file_name, 'r' ),
								'filename' => $file_name
							]
						]
					]
				);
				$response = json_decode( $response->getBody(), true );

				if ( $response['status'] == true ) {
					$http_status_code = 200;
					$data['status'] = true;
					$data['message'] = $response['message'];
				}
				$QV_STATUS = ( $response['status'] == true ? 1 : 0 );
				$QV_USER_AUTH_CODE = session( 'USER_AUTH_CODE' );
				/* $insert_db = $this->db_mobile_ins->insert( "
					INSERT INTO 
						T_LOG_IMPORT_DB( 
							USER_AUTH_CODE, 
							FILENAME, 
							INSERT_DATE,
							STATUS
						)
					VALUES( 
						'{$QV_USER_AUTH_CODE}', 
						'{$file_name}', 
						SYSDATE,
						$QV_STATUS
					)
				" ); */
			}
			catch( \Exception $e ) {
				$data['message'] = $e->getMessage();
			}
		}

		//return response()->json( $data, $http_status_code );
		return redirect()->back() ->with('alert', $data['message']);
	}
	
}