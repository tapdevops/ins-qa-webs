<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;	
use Redirect;
use App\APISetup;

# API Setup

class MasterCategoryFindingController extends Controller {
	protected $api;
	protected $active_menu;
	protected $url_api_ins_msa_auth;

	#   		 								  				  ▁ ▂ ▄ ▅ ▆ ▇ █ CONSTRUCTOR
	# -------------------------------------------------------------------------------------
	public function __construct() {
		$this->active_menu = '_'.str_replace( '.', '', '02.07.00.00.00' ).'_';
		$this->db_mobile_ins = DB::connection('mobile_ins');
		$this->url_api_ins_msa_auth = APISetup::url()['msa']['ins']['auth'];
	}

	#   		 								  				        ▁ ▂ ▄ ▅ ▆ ▇ █ INDEX
	# -------------------------------------------------------------------------------------
	public function index() {
		$allowed_role = array( "ADMIN" );
		$data['active_menu'] = $this->active_menu;

		if ( in_array( session('USER_ROLE'), $allowed_role ) ) {
			$data['master_type'] = 'master.category-finding';
			$data['category_finding'] = $this->db_mobile_ins->table('tm_category')->select('*')->get();
			$data['auth_url'] = $this->url_api_ins_msa_auth;
			return view( 'master.index', $data );
		}
		
	}

	#   		 								  				  ▁ ▂ ▄ ▅ ▆ ▇ █ CREATE / UPDATE 
	# -------------------------------------------------------------------------------------
	public function store(Request $request) {
		$allowed_role = array( "ADMIN" );
		if ( in_array( session('USER_ROLE'), $allowed_role ) ) {
			if($request->id==''){
				// add 
				$check = $this->db_mobile_ins->table('tm_category')->where(['category_code'=>$request->category_code])->first();

				if($check){
					return Redirect::back()->with('warning', $request->category_code.' already exist');
				}else{

					if ( $request->hasFile( 'icon' ) ) {
						$temp = $request->file( 'icon' );
						$file_name = str_replace(' ','',$temp->getClientOriginalName());
						$temp->move( public_path( 'uploads-category-icon/'.date( 'Y-m-d' ) ), $file_name );
						// try {
							$client = new \GuzzleHttp\Client();
							$url_import_db = $this->url_api_ins_msa_auth . '/api/v2.0/category/upload/icon';
							$response = $client->request( 'POST', $url_import_db, [
									 'headers' => [
										'Accept' => 'application/json',
										'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' ),
									], 
									'multipart' => [
										[
											'name' => 'IMAGES',
											'contents' => fopen( './uploads-category-icon/'.date( 'Y-m-d' ).'/'.$file_name, 'r' ),
											'filename' => $file_name
										]
									]
								]
							);
							$response = json_decode( $response->getBody(), true );
							if ( $response['status'] == true ) {
								$insert = [	'CATEGORY_CODE'=>$request->category_code,
											'CATEGORY_NAME'=>$request->category_name,
											'ICON'=>$file_name];	
								$this->db_mobile_ins->table('tm_category')->insert($insert);
								$insert['INSERT_USER'] = session('USERNAME');
								$insert['INSERT_TIME'] = floatval(date('YmdHis'))+0.0;
								$insert['UPDATE_USER'] = session('USERNAME');
								$insert['UPDATE_TIME'] = floatval(date('YmdHis'))+0.0;
								$insert['DELETE_USER'] = '';
								$insert['DELETE_TIME'] = 0.0;
								DB::connection('mongodb_auth')->collection('TM_CATEGORY')->where(['CATEGORY_CODE'=>$request->category_code])->update($insert, ['upsert' => true]);
								return Redirect::back()->with('success', $request->category_code.' added');
							}
							else{
								return Redirect::back()->with('warning', $request->category_code.' failed to upload icon');
							}

						// }
						// catch( \Exception $e ) {
						// 	$data['message'] = $e->getMessage();
						// 	dd($data);
						// }
					}
				}
			}else{
				// edit 
				if ( $request->hasFile('icon') ) {
					$temp = $request->file( 'icon' );
					$file_name = str_replace(' ','',$temp->getClientOriginalName());
					$temp->move( public_path( 'uploads-category-icon/'.date( 'Y-m-d' ) ), $file_name );
					// try {
						$client = new \GuzzleHttp\Client();
						$url_import_db = $this->url_api_ins_msa_auth . '/api/v2.0/category/upload/icon';
						$response = $client->request( 'POST', $url_import_db, [
								 'headers' => [
									'Accept' => 'application/json',
									'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' ),
								], 
								'multipart' => [
									[
										'name' => 'IMAGES',
										'contents' => fopen( './uploads-category-icon/'.date( 'Y-m-d' ).'/'.$file_name, 'r' ),
										'filename' => $file_name
									]
								]
							]
						);
					$update = [	'CATEGORY_NAME'=>$request->category_name,
								'ICON'=>$file_name];	
				}else{
					$update = [	'CATEGORY_NAME'=>$request->category_name];	
				}
				$this->db_mobile_ins->table('tm_category')->where(['category_code'=>$request->category_code])->update($update);
				$update['UPDATE_USER'] = session('USERNAME');
				$update['UPDATE_TIME'] = floatval(date('YmdHis'));
				DB::connection('mongodb_auth')->collection('TM_CATEGORY')->where(['CATEGORY_CODE'=>$request->category_code])->update($update, ['upsert' => true]);
				return Redirect::back()->with('success', $request->category_code.' updated');
			}
			return Redirect::back();
		}
	}
	
}