<?php

# Namespace
	namespace App\Http\Controllers;

# Default Laravel Vendor Setup
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
	use App\APIDataSource;
	use App\APISetup;
	use App\APIData as Data;

class ModulesController extends Controller {

	protected $url_api_ins_msa_auth;
	protected $url_api_ins_msa_hectarestatement;

	public function __construct() {
		$this->url_api_ins_msa_auth = APISetup::url()['msa']['ins']['auth'];
		$this->url_api_ins_msa_hectarestatement = APISetup::url()['msa']['ins']['hectarestatement'];
	}

	#   		 									  			        ▁ ▂ ▄ ▅ ▆ ▇ █ Index
	# -------------------------------------------------------------------------------------
	#
	# Fungsi untuk menampilkan data-data yang ada di dalam database. Data ditampilkan semua
	#
	public function index() {
		$data['modules'] = Data::modules_find();
		return view( 'modules.index', $data );
	}

	#   		 									  			       ▁ ▂ ▄ ▅ ▆ ▇ █ Create
	# -------------------------------------------------------------------------------------
	# 
	# Fungsi untuk membuat module baru.
	#
	public function create() {
		return view( 'modules.create' );
	}

	#   		 									  	        ▁ ▂ ▄ ▅ ▆ ▇ █ Generate Menu
	# -------------------------------------------------------------------------------------
	public function generate_menu( $id = '' ) {
		if ( $id != '' ) {
			/*═════════════════════════════════════════════════════════════════╗
			║ Set Variabel           										   ║
			╚═════════════════════════════════════════════════════════════════*/
			$user_role = '/'.$id;
			$modules_data = Data::modules_find_by_job( $user_role );
			$menu_data = array();
			$menu_data_1 = array();
			$menu_data_2 = array();
			$menu_data_3 = array();
			$menu_data_4 = array();
			$menu_data_5 = array();
			$html_menu_01 = '';
			$html_menu_02 = '';

			/*═════════════════════════════════════════════════════════════════╗
			║ Set Array Menu           										   ║
			╚═════════════════════════════════════════════════════════════════*/
			if ( count( $modules_data ) > 0 ) {
				$i = 0;
				foreach( $modules_data as $menu ) {
					$menu_code = explode( '.', $menu['MODULE_CODE'] );
					$array_key_1 = (String) $menu_code[0];
					$array_key_2 = (String) $menu_code[0].$menu_code[1];
					$array_key_3 = (String) $menu_code[0].$menu_code[1].$menu_code[2];
					$array_key_4 = (String) $menu_code[0].$menu_code[1].$menu_code[2].$menu_code[3];
					$array_key_5 = (String) $menu_code[0].$menu_code[1].$menu_code[2].$menu_code[3].$menu_code[4];

					if ( $menu_code[1] == '00' ) { // Menu 1
						$menu_data['DATA'][$array_key_1]['MODULE_CODE'] = $menu['MODULE_CODE'];
						$menu_data['DATA'][$array_key_1]['PARAMETER_NAME'] = $menu['PARAMETER_NAME'];
						$menu_data['DATA'][$array_key_1]['STATUS'] = $menu['STATUS'];
						$menu_data['DATA'][$array_key_1]['MODULE_NAME'] = $menu['MODULE_NAME'];
						$menu_data['DATA'][$array_key_1]['ITEM_NAME'] = $menu['ITEM_NAME'];
						$menu_data['DATA'][$array_key_1]['ICON'] = $menu['ICON'];
					}
					else if ( $menu_code[2] == '00' ) { // Menu 2
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['MODULE_CODE'] = $menu['MODULE_CODE'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['PARAMETER_NAME'] = $menu['PARAMETER_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['STATUS'] = $menu['STATUS'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['MODULE_NAME'] = $menu['MODULE_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['ITEM_NAME'] = $menu['ITEM_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['ICON'] = $menu['ICON'];
					}
					else if ( $menu_code[3] == '00' ) { // Menu 3
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['MODULE_CODE'] = $menu['MODULE_CODE'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['PARAMETER_NAME'] = $menu['PARAMETER_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['STATUS'] = $menu['STATUS'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['MODULE_NAME'] = $menu['MODULE_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['ITEM_NAME'] = $menu['ITEM_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['ICON'] = $menu['ICON'];
					}
					else if ( $menu_code[4] == '00' ) { // Menu 4
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['MODULE_CODE'] = $menu['MODULE_CODE'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['PARAMETER_NAME'] = $menu['PARAMETER_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['STATUS'] = $menu['STATUS'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['MODULE_NAME'] = $menu['MODULE_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['ITEM_NAME'] = $menu['ITEM_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['ICON'] = $menu['ICON'];
					}
					else if ( $menu_code[4] != '00' ) { // Menu 5
						$array_key = $array_key_5;
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['DATA'][$array_key_5]['MODULE_CODE'] = $menu['MODULE_CODE'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['DATA'][$array_key_5]['PARAMETER_NAME'] = $menu['PARAMETER_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['DATA'][$array_key_5]['STATUS'] = $menu['STATUS'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['DATA'][$array_key_5]['MODULE_NAME'] = $menu['MODULE_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['DATA'][$array_key_5]['ITEM_NAME'] = $menu['ITEM_NAME'];
						$menu_data['DATA'][$array_key_1]['DATA'][$array_key_2]['DATA'][$array_key_3]['DATA'][$array_key_4]['DATA'][$array_key_5]['ICON'] = $menu['ICON'];
					}
				}
			}

			/*═════════════════════════════════════════════════════════════════╗
			║ Set HTML Menu           										   ║
			║ Untuk menyusun HTML berdasarkan User Role sesuai dengan input.   ║
			║ parameter.           									  		   ║
			╚═════════════════════════════════════════════════════════════════*/
			if ( !empty( $menu_data ) ) {
				$html_menu_02 .= '<ul class="m-menu__nav  m-menu__nav--submenu-arrow ">';
				$_01 = 1;
				$_02 = 1;
				foreach ( $menu_data as $kmd => $md ) {
					if ( isset( $md['02'] ) ) {
						// Menu 1
						foreach ( $md['02']['DATA'] as $md1 ) {
							
							$active = '';
							$html_menu_02 .= '<li class="m-menu__item '.$active.'  m-menu__item--submenu m-menu__item--tabs"  m-menu-submenu-toggle="tab" aria-haspopup="true">';
							$html_menu_02 .= '<a  href="javascript:;" class="m-menu__link m-menu__toggle">';
							$html_menu_02 .= '<span class="m-menu__link-text">'.( isset( $md1['MODULE_NAME'] ) ? $md1['MODULE_NAME'] : 'Unknown' ).'</span>';
							$html_menu_02 .= '<i class="m-menu__hor-arrow la la-angle-down"></i>';
							$html_menu_02 .= '<i class="m-menu__ver-arrow la la-angle-right"></i>';
							$html_menu_02 .= '</a>';

							if ( isset( $md1['DATA'] ) ) {

								$html_menu_02 .= '<div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left m-menu__submenu--tabs">';
								$html_menu_02 .= '<span class="m-menu__arrow m-menu__arrow--adjust"></span>';
								$html_menu_02 .= '<ul class="m-menu__subnav">';
								
								foreach ( $md1['DATA'] as $md2 ) {

									if ( isset( $md2['DATA'] ) ) {
										$html_menu_02 .= '<li class="m-menu__item  m-menu__item--submenu m-menu__item--rel m-menu__item--submenu-tabs"  m-menu-submenu-toggle="click" aria-haspopup="true">';
										$html_menu_02 .= '<a  href="javascript:;" class="m-menu__link m-menu__toggle">';
										$html_menu_02 .= '<i class="m-menu__link-icon '.$md2['ICON'].'"></i>';
										$html_menu_02 .= '<span class="m-menu__link-text">'.$md2['MODULE_NAME'].'</span>';
										$html_menu_02 .= '<i class="m-menu__hor-arrow la la-angle-down"></i>';
										$html_menu_02 .= '<i class="m-menu__ver-arrow la la-angle-right"></i>';
										$html_menu_02 .= '</a>';
										$html_menu_02 .= '<div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left">';
										$html_menu_02 .= '<span class="m-menu__arrow m-menu__arrow--adjust"></span>';
										$html_menu_02 .= '<ul class="m-menu__subnav">';

										foreach ( $md2['DATA'] as $md3 ) {

											if ( isset( $md3['DATA'] ) ) {
												$html_menu_02 .= '<li class="m-menu__item  m-menu__item--submenu"  m-menu-submenu-toggle="hover" m-menu-link-redirect="1" aria-haspopup="true">';
												$html_menu_02 .= '<a  href="javascript:;" class="m-menu__link m-menu__toggle">';
												$html_menu_02 .= '<i class="m-menu__link-icon '.$md2['ICON'].'"></i>';
												$html_menu_02 .= '<span class="m-menu__link-text">'.$md2['MODULE_NAME'].'</span>';
												$html_menu_02 .= '<i class="m-menu__hor-arrow la la-angle-right"></i>';
												$html_menu_02 .= '<i class="m-menu__ver-arrow la la-angle-right"></i>';
												$html_menu_02 .= '</a>';
												$html_menu_02 .= '<div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--right">';
												$html_menu_02 .= '<span class="m-menu__arrow "></span>';
												$html_menu_02 .= '<ul class="m-menu__subnav">';

												foreach ( $md3['DATA'] as $md4 ) {
													$html_menu_02 .= '<li class="m-menu__item "  m-menu-link-redirect="1" aria-haspopup="true">';
													$html_menu_02 .= '<a  href="'.url( $md2['ITEM_NAME'] ).'" class="m-menu__link ">';
													$html_menu_02 .= '<span class="m-menu__link-text">'.$md2['MODULE_NAME'].'</span>';
													$html_menu_02 .= '</a>';
													$html_menu_02 .= '</li>';
												}

												$html_menu_02 .= '</ul>';
												$html_menu_02 .= '</div>';
												$html_menu_02 .= '</li>';
											}
											else {
												$html_menu_02 .= '<li class="m-menu__item "  aria-haspopup="true">';
												$html_menu_02 .= '<a  href="'.url( $md3['ITEM_NAME'] ).'" class="m-menu__link ">';
												$html_menu_02 .= '<i class="m-menu__link-icon '.$md2['ICON'].'"></i>';
												$html_menu_02 .= '<span class="m-menu__link-title">';
												$html_menu_02 .= '<span class="m-menu__link-wrap">';
												$html_menu_02 .= '<span class="m-menu__link-text">'.$md3['MODULE_NAME'].'</span>';
												$html_menu_02 .= '</span>';
												$html_menu_02 .= '</span>';
												$html_menu_02 .= '</a>';
												$html_menu_02 .= '</li>';
											}
										}

										$html_menu_02 .= '</ul>';
										$html_menu_02 .= '</div>';
										$html_menu_02 .= '</li>';
									}
									else {
										$html_menu_02 .= '<li class="m-menu__item "  m-menu-link-redirect="1" aria-haspopup="true">';
										$html_menu_02 .= '<a  href="'.url( $md2['ITEM_NAME'] ).'" class="m-menu__link ">';
										$html_menu_02 .= '<i class="m-menu__link-icon '.$md2['ICON'].'"></i>';
										$html_menu_02 .= '<span class="m-menu__link-text">'.$md2['MODULE_NAME'].'</span>';
										$html_menu_02 .= '</a>';
										$html_menu_02 .= '</li>';
									}
								}

								$html_menu_02 .= '</ul>';
								$html_menu_02 .= '</div>';
							}

							$html_menu_02 .= '</li>';
						}
						$_02++;
					}
				}

				$html_menu_02 .= '</ul>';

				/*═════════════════════════════════════════════════════════════════╗
				║ Save to disk            										   ║
				╚═════════════════════════════════════════════════════════════════*/
				Storage::disk( 'resources' )->put( 'layouts/default/header-menu-02-'.$id.'.blade.php', $html_menu_02 );
				
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	#   		 									  			   ▁ ▂ ▄ ▅ ▆ ▇ █ Setup Menu
	# -------------------------------------------------------------------------------------
	public function setup_menu( Request $req ) {

		if ( $req->id == '' ) {
			$data_parameter = Data::parameter_find( '?PARAMETER_GROUP=USER_ROLE' );
			$response['status'] = true;
			$response['message'] = '';
			foreach( $data_parameter as $parameter ) {
				print '<pre>';
				print_r( $parameter );
				print '</pre>';
				
				//if ( isset( (string) $parameter['PARAMETER_NAME'] ) && (string) $parameter['PARAMETER_NAME'] != '' ) {
					if ( self::generate_menu( (string) $parameter['PARAMETER_NAME'] ) == true ) {
						$response['message'][ (string) $parameter['PARAMETER_NAME']] = 'Success! Menu berhasil digenerate.';
					}
					else {
						$response['message'][ (string) $parameter['PARAMETER_NAME']] = 'Error! Menu gagal digenerate.';
					}
				//}
				
			}
		}
		else {
			if ( self::generate_menu( $req->id ) == true ) {
				$response['status'] = true;
				$response['message'] = 'Success! Menu berhasil digenerate.';
			}
			else {
				$response['status'] = false;
				$response['message'] = 'Error! Menu gagal digenerate.';
			}
		}

		return response()->json( $response );
	}



	#   		 								           ▁ ▂ ▄ ▅ ▆ ▇ █ User Authorization
	# -------------------------------------------------------------------------------------
	public function user_authorization() {

		$data['modules'] = Data::modules_find();
		$data['parameter'] = Data::parameter_find( '?PARAMETER_GROUP=USER_ROLE' );
		$data['user_authorization'] = array();
		$i = 0;

		foreach ( Data::user_authorization_find() as $ua ) {
			$data['user_authorization'][$ua['MODULE_CODE']][$ua['PARAMETER_NAME']]['STATUS'] = $ua['STATUS'];
			$i++;
		}

		return view( 'modules.user-authorization', $data );
	}

	#   		 							 ▁ ▂ ▄ ▅ ▆ ▇ █ User Authorization Detail Proses
	# -------------------------------------------------------------------------------------
	public function user_authorization_proses() {
		$client = new \GuzzleHttp\Client();
		$result = $client->request( 'POST', $this->url_api_ins_msa_auth.'/api/user-authorization', [
			'json' => [
				'MODULE_CODE' => Input::get( 'MODULE_CODE' ),
				'PARAMETER_NAME' => Input::get( 'PARAMETER_NAME' )
			],
			'headers' => [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			]
		]);
		$result = json_decode( $result->getBody(), true );
		
		return response()->json( $result );
	}

	#   		 									  			       ▁ ▂ ▄ ▅ ▆ ▇ █ Create
	# -------------------------------------------------------------------------------------
	public function create_proses( Request $req ) {
		$data['status'] = false;
		$data['message'] = '';
		$data['data'] = array();
		$rules = array(
			'MODULE_CODE' => 'required|min:14|max:14',
			'MODULE_NAME' => 'required',
			'ICON' => 'max:64',
			'STATUS' => 'max:64'
		);
		$validator = Validator::make( Input::all(), $rules );

		if ( $validator->fails() ) {
			$data['message'] = 'Error! Periksa kembali inputan anda.';
			
		} else {
			$x = array(
				'MODULE_CODE' => Input::get( 'MODULE_CODE' ),
				'MODULE_NAME' => Input::get( 'MODULE_NAME' ),
				'PARENT_MODULE' => '',
				'ITEM_NAME' => Input::get( 'ITEM_NAME' ),
				'ICON' => Input::get( 'ICON' ),
				'STATUS' => Input::get( 'STATUS' )
			);
			$client = new \GuzzleHttp\Client();
			$headers = [
				'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' )
			];
			$result = $client->request( 'POST', $this->url_api_ins_msa_auth.'/api/modules', [
				'json' => [
					'MODULE_CODE' => Input::get( 'MODULE_CODE' ),
					'MODULE_NAME' => Input::get( 'MODULE_NAME' ),
					'PARENT_MODULE' => '',
					'ITEM_NAME' => Input::get( 'ITEM_NAME' ),
					'ICON' => Input::get( 'ICON' ),
					'STATUS' => Input::get( 'STATUS' )
				],
				'headers' => $headers
			]);

			$result = json_decode( $result->getBody() );
			
			if ( $result->status == true ) {
				$this->source_data();
				$data['status'] = true;
				$data['message'] = 'Data berhasil di insert.';
			}
			else {
				$data['message'] = 'Data gagal di insert.';
			}
		}

		return response()->json( $data );
	}

}