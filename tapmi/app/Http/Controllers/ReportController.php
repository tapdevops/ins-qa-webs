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
use DateTime;
use Maatwebsite\Excel\Facades\Excel;

# API
use App\APISetup;
use App\APIData as Data;

class ReportController extends Controller {

	protected $url_api_ins_msa_auth;
	protected $url_api_ins_msa_hectarestatement;
	protected $access_token;
	protected $active_menu;
	protected $auth;

	public function __construct() {
		$this->active_menu = '_'.str_replace( '.', '', '02.03.00.00.00' ).'_';
		$this->url_api_ins_msa_auth = APISetup::url()['msa']['ins']['auth'];
		$this->url_api_ins_msa_hectarestatement = APISetup::url()['msa']['ins']['hectarestatement'];
		$this->url_api_ins_msa_report = APISetup::url()['msa']['ins']['report'];
		$this->access_token = Storage::get( 'files/access_token_mobile_inspection.txt' );
		$this->auth = array(
			'username' => 'ferdinand',
			'password' => 'tap12345',
			'imei' => ''
		);
	}

	/*
	 |--------------------------------------------------------------------------
	 | Page - Index
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function index() {
			return view( 'report.index' );
		}

	/*
	 |--------------------------------------------------------------------------
	 | Page - Download
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function download() {
			$url_region_data = $this->url_api_ins_msa_hectarestatement.'/region/all';
			$data['region_data'] = APISetup::ins_rest_client( 'GET', $url_region_data );
			$data['active_menu'] = $this->active_menu;
			return view( 'report.download', $data );
		}

	/*
	 |--------------------------------------------------------------------------
	 | Proses - Download
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function download_proses( Request $req ) {

			$data['status'] = false;
			$data['message'] = 'Terjadi kesalahan dalam download report.';

			$setup = array();
			if ( Input::get( 'REGION_CODE' ) != '' && Input::get( 'COMP_CODE' ) == '' ) {
				$setup['REGION_CODE'] = Input::get( 'REGION_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}
			else if ( Input::get( 'COMP_CODE' ) != '' && Input::get( 'BA_CODE' ) == '' ) {
				$setup['COMP_CODE'] = Input::get( 'COMP_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}
			else if ( Input::get( 'BA_CODE' ) != '' && Input::get( 'AFD_CODE' ) == '' ) {
				$setup['BA_CODE'] = Input::get( 'BA_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}
			else if ( Input::get( 'BA_CODE' ) != '' && Input::get( 'AFD_CODE' ) != '' && Input::get( 'BLOCK_CODE' ) == '' ) {
				$setup['BA_CODE'] = Input::get( 'BA_CODE' );
				$setup['AFD_CODE'] = Input::get( 'AFD_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}

			else if ( Input::get( 'BA_CODE' ) != '' && Input::get( 'AFD_CODE' ) != '' && Input::get( 'BLOCK_CODE' ) != '' ) {
				$setup['BA_CODE'] = Input::get( 'BA_CODE' );
				$setup['AFD_CODE'] = Input::get( 'AFD_CODE' );
				$setup['BLOCK_CODE'] = Input::get( 'BLOCK_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}

			// Report Finding
			if ( Input::get( 'REPORT_TYPE' ) == 'FINDING' ) {
				self::excel_finding( $setup );
			}
			// Report Inspeksi
			else if ( Input::get( 'REPORT_TYPE' ) == 'INSPEKSI' ) {
				self::excel_inspeksi( $setup );
			}
			// Report EBCC Validation
			else if ( Input::get( 'REPORT_TYPE' ) == 'EBCC_VALIDATION' ) {
				if ( Input::get( 'REGION_CODE' ) != '' && Input::get( 'COMP_CODE' ) == '' ) {
					$setup['WERKS_AFD_BLOCK_CODE'] = substr( Input::get( 'REGION_CODE' ), 1, 1 );
				}
				else if ( Input::get( 'COMP_CODE' ) != '' && Input::get( 'BA_CODE' ) == '' ) {
					$setup['WERKS_AFD_BLOCK_CODE'] = Input::get( 'COMP_CODE' );
				}
				else if ( Input::get( 'BA_CODE' ) != '' && Input::get( 'AFD_CODE' ) == '' ) {
					$setup['WERKS_AFD_BLOCK_CODE'] = Input::get( 'BA_CODE' );
				}
				else if ( Input::get( 'BA_CODE' ) != '' && Input::get( 'AFD_CODE' ) != '' &&  Input::get( 'BLOCK_CODE' ) == '' ) {
					$setup['WERKS_AFD_BLOCK_CODE'] = Input::get( 'AFD_CODE' );
				}
				else if ( Input::get( 'AFD_CODE' ) != '' && Input::get( 'BLOCK_CODE' ) != '' ) {
					$setup['WERKS_AFD_BLOCK_CODE'] = Input::get( 'BLOCK_CODE' );
				}
				self::download_excel_ebcc_validation( $setup );
			}
			// Report Class Block
			else if ( Input::get( 'REPORT_TYPE' ) == 'CLASS_BLOCK_AFD_ESTATE' ) {
				$date = date( 'Ymd', strtotime( Input::get( 'DATE_MONTH' ) ) );
				$setup['START_DATE'] = date( 'Ym', strtotime( $date ) ).'00';
				$setup['END_DATE'] = date( 'Ymt', strtotime( $date ) );
				self::download_excel_class_block( $setup );
			}
		}

	/*
	 |--------------------------------------------------------------------------
	 | Proses - Generate
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function generate_proses( Request $req ) {

			$data['status'] = false;
			$data['message'] = 'Terjadi kesalahan dalam download report.';

			$setup = array();
			if ( Input::get( 'REGION_CODE' ) != '' && Input::get( 'COMP_CODE' ) == '' ) {
				$setup['REGION_CODE'] = Input::get( 'REGION_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}
			else if ( Input::get( 'COMP_CODE' ) != '' && Input::get( 'BA_CODE' ) == '' ) {
				$setup['COMP_CODE'] = Input::get( 'COMP_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}
			else if ( Input::get( 'BA_CODE' ) != '' && Input::get( 'AFD_CODE' ) == '' ) {
				$setup['BA_CODE'] = Input::get( 'BA_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}
			else if ( Input::get( 'BA_CODE' ) != '' && Input::get( 'AFD_CODE' ) != '' && Input::get( 'BLOCK_CODE' ) == '' ) {
				$setup['BA_CODE'] = Input::get( 'BA_CODE' );
				$setup['AFD_CODE'] = Input::get( 'AFD_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}
			
			else if ( Input::get( 'BA_CODE' ) != '' && Input::get( 'AFD_CODE' ) != '' && Input::get( 'BLOCK_CODE' ) != '' ) {
				$setup['BA_CODE'] = Input::get( 'BA_CODE' );
				$setup['AFD_CODE'] = Input::get( 'AFD_CODE' );
				$setup['BLOCK_CODE'] = Input::get( 'BLOCK_CODE' );
				$setup['START_DATE'] = date( 'Ymd', strtotime( Input::get( 'START_DATE' ) ) );
				$setup['END_DATE'] = date( 'Ymd', strtotime( Input::get( 'END_DATE' ) ) );
			}
			
			// Report Finding
			if ( Input::get( 'REPORT_TYPE' ) == 'FINDING' ) {
				# ...
			}
			// Report Inspeksi
			else if ( Input::get( 'REPORT_TYPE' ) == 'INSPEKSI' ) {
				self::generate_inspeksi( $setup );
			}
			// Report Class Block
			else if ( Input::get( 'REPORT_TYPE' ) == 'CLASS_BLOCK_AFD_ESTATE' ) {
				$date = date( 'Ymd', strtotime( Input::get( 'DATE_MONTH' ) ) );
				$setup['START_DATE'] = date( 'Ym', strtotime( $date ) ).'00';
				$setup['END_DATE'] = date( 'Ymt', strtotime( $date ) );
				self::generate_class_block( $setup );
			}
		}

	public function download_excel_ebcc_validation( $data ) {

		$kualitas_jjg_hasilpanen = Data::kualitas_find( '?UOM=JJG&GROUP_KUALITAS=HASIL PANEN' );
		$kualitas_jjg_hasilpanen_check = [];
		foreach ( $kualitas_jjg_hasilpanen as $jjg_hasilpanen ) {
			$kualitas_jjg_hasilpanen_check[] = $jjg_hasilpanen['ID_KUALITAS'];
		}

		/*
		print '<pre>';
		print_r( $kualitas_jjg_hasilpanen_check );
		print '<pre>';
		if ( in_array( '19', $kualitas_jjg_hasilpanen_check ) ) {
			print 'Yes';
		}
		else {
			print 'No';
		}
		dd();
		*/

		$kualitas_penalty_tph = Data::kualitas_find( '?UOM=TPH&GROUP_KUALITAS=PENALTY DI TPH' );
		$kualitas_penalty_tph_check = [];
		foreach ( $kualitas_penalty_tph as $penalty_tph ) {
			$kualitas_penalty_tph_check[] = $penalty_tph['ID_KUALITAS'];
		}
		/*
		print '<pre>';
		print_r( $kualitas_penalty_tph_check );
		print '</pre>';
		dd();
		*/

		$kualitas_jjg_kondisibuah = Data::kualitas_find( '?UOM=JJG&GROUP_KUALITAS=KONDISI BUAH' );
		$kualitas_jjg_kondisibuah_check = [];
		foreach ( $kualitas_jjg_kondisibuah as $jjg_kondisibuah ) {
			$kualitas_jjg_kondisibuah_check[] = $jjg_kondisibuah['ID_KUALITAS'];
		}
		/*
		print '<pre>';
		print_r( $kualitas_jjg_kondisibuah_check );
		print '</pre>';
		dd();
		*/

		$results = [];
		$results['data'] = [];
		$results['periode'] = date( 'Ym', strtotime( $data['START_DATE'] ) );
		$results['kualitas_jjg_hasilpanen'] = $kualitas_jjg_hasilpanen;
		$results['kualitas_jjg_kondisibuah'] = $kualitas_jjg_kondisibuah;
		$results['kualitas_penalty_tph'] = $kualitas_penalty_tph;

		$ebcc_validation = Data::web_report_ebcc_validation_find( '/'.$data['WERKS_AFD_BLOCK_CODE'].'/'.$data['START_DATE'].'/'.$data['END_DATE'] );

		// print '<pre>';
		// print_r( '/'.$data['WERKS_AFD_BLOCK_CODE'].'/'.$data['START_DATE'].'/'.$data['END_DATE'] );
		// print '</pre>';
		// dd();

		$i = 0;
		foreach ( $ebcc_validation['data'] as $ebcc ) {
			$results['data'][$i]['EBCC_VALIDATION_CODE'] = $ebcc['EBCC_VALIDATION_CODE'];
			$results['data'][$i]['WERKS_AFD_CODE'] = $ebcc['WERKS'].$ebcc['AFD_CODE'].$ebcc['BLOCK_CODE'];
			$results['data'][$i]['WERKS_AFD_BLOCK_CODE'] = $ebcc['WERKS'].$ebcc['AFD_CODE'].$ebcc['BLOCK_CODE'];
			$results['data'][$i]['WERKS'] = $ebcc['WERKS'];
			$results['data'][$i]['EST_NAME'] = '';
			$results['data'][$i]['AFD_CODE'] = $ebcc['AFD_CODE'];
			$results['data'][$i]['BLOCK_CODE'] = $ebcc['BLOCK_CODE'];
			$results['data'][$i]['BLOCK_NAME'] = '';
			$results['data'][$i]['MATURITY_STATUS'] = '';
			$results['data'][$i]['NO_TPH'] = $ebcc['NO_TPH'];
			$results['data'][$i]['STATUS_TPH_SCAN'] = $ebcc['STATUS_TPH_SCAN'];
			$results['data'][$i]['ALASAN_MANUAL'] = $ebcc['ALASAN_MANUAL'];
			$results['data'][$i]['TANGGAL_VALIDASI'] = date( 'Y-m-d H:i:s', strtotime( $ebcc['INSERT_TIME'] ) );
			$results['data'][$i]['LAT_TPH'] = $ebcc['LAT_TPH'];
			$results['data'][$i]['LON_TPH'] = $ebcc['LON_TPH'];
			$results['data'][$i]['DELIVERY_CODE'] = $ebcc['DELIVERY_CODE'];
			$results['data'][$i]['STATUS_DELIVERY_CODE'] = $ebcc['STATUS_DELIVERY_CODE'];

			$inspektor_data = Data::user_find_one( ( String ) $ebcc['INSERT_USER'] )['items'];
			$results['data'][$i]['NIK_VALIDATOR'] = $inspektor_data['EMPLOYEE_NIK'];
			$results['data'][$i]['NAMA_VALIDATOR'] = $inspektor_data['FULLNAME'];
			$results['data'][$i]['JABATAN_VALIDATOR'] = $inspektor_data['JOB'];

			# Kualitas: { UOM: "JJG", GROUP_KUALITAS: "HASIL PANEN" }
			$results['data'][$i]['HASIL_JJG_HASILPANEN'] = [];
			foreach ( $kualitas_jjg_hasilpanen_check as $jjg_hasilpanen_check ) {
				$results['data'][$i]['HASIL_JJG_HASILPANEN']['_'.$jjg_hasilpanen_check] = 0;
			}

			# Kualitas: { UOM: "JJG", GROUP_KUALITAS: "KONDISI BUAH" }
			$results['data'][$i]['HASIL_JJG_KONDISIBUAH'] = [];
			foreach ( $kualitas_jjg_kondisibuah_check as $jjg_kondisibuah_check ) {
				$results['data'][$i]['HASIL_JJG_KONDISIBUAH']['_'.$jjg_kondisibuah_check] = 0;
			}

			# Kualitas: { UOM: "TPH", GROUP_KUALITAS: "PENALTY DI TPH" }
			$results['data'][$i]['PENALTY_DI_TPH'] = [];
			foreach ( $kualitas_penalty_tph_check as $penalty_tph_check ) {
				$results['data'][$i]['PENALTY_DI_TPH']['_'.$penalty_tph_check] = 0;
			}

			foreach ( $ebcc['DETAIL'] as $detail ) {
				# Kualitas: { UOM: "JJG", GROUP_KUALITAS: "HASIL PANEN" }
				if ( in_array( $detail['ID_KUALITAS'], $kualitas_jjg_hasilpanen_check ) ) {
					$results['data'][$i]['HASIL_JJG_HASILPANEN']['_'.$detail['ID_KUALITAS']] = $detail['JUMLAH'];
				}

				# Kualitas: { UOM: "JJG", GROUP_KUALITAS: "KONDISI BUAH" }
				if ( in_array( $detail['ID_KUALITAS'], $kualitas_jjg_kondisibuah_check ) ) {
					$results['data'][$i]['HASIL_JJG_KONDISIBUAH']['_'.$detail['ID_KUALITAS']] = $detail['JUMLAH'];
				}

				# Kualitas: { UOM: "TPH", GROUP_KUALITAS: "PENALTY DI TPH" }
				if ( in_array( $detail['ID_KUALITAS'], $kualitas_penalty_tph_check ) ) {
					$results['data'][$i]['PENALTY_DI_TPH']['_'.$detail['ID_KUALITAS']] = $detail['JUMLAH'];
				}
			}

			$hectarestatement =  Data::web_report_land_use_findone( $ebcc['WERKS'].$ebcc['AFD_CODE'].$ebcc['BLOCK_CODE'] );
			if ( !empty( $hectarestatement ) ) {
				$results['data'][$i]['EST_NAME'] = $hectarestatement['EST_NAME'];
				$results['data'][$i]['BLOCK_NAME'] = $hectarestatement['BLOCK_NAME'];
				$results['data'][$i]['MATURITY_STATUS'] = $hectarestatement['MATURITY_STATUS'];
			}

			$validator = Data::user_find_one( ( String ) $ebcc['INSERT_USER'] )['items'];
			if ( !empty( $validator ) ) {
				$results['data'][$i]['NIK_VALIDATOR'] = $validator['EMPLOYEE_NIK'];
				$results['data'][$i]['NAMA_VALIDATOR'] = $validator['FULLNAME'];
				$results['data'][$i]['JABATAN_VALIDATOR'] = str_replace( '_', ' ', $validator['JOB'] );
			}

			$i++;
		}

		Excel::create( 'Report-EBCC-Validation', function( $excel ) use ( $results ) {
			$excel->sheet( 'Per Baris', function( $sheet ) use ( $results ) {
				$sheet->loadView( 'report.excel-ebcc-validation', $results );
			} );
		} )->export( 'xls' );
	}




































	/** ZONA BONGKAR PASANG -------------------------------------------------------------------- **/

	public function generate_class_block( $data, $output = 'excel' ) {

		$parameter = '';
		if ( isset( $data['BLOCK_CODE'] ) ) {
			$parameter = $data['BLOCK_CODE'];
		}
		else if ( !isset( $data['BLOCK_CODE'] ) && isset( $data['AFD_CODE'] ) ) {
			$parameter = $data['AFD_CODE'];
		}
		else if ( !isset( $data['AFD_CODE'] ) && isset( $data['BA_CODE'] ) ) {
			$parameter = $data['BA_CODE'];
		}
		else if ( !isset( $data['BA_CODE'] ) && isset( $data['COMP_CODE'] ) ) {
			$parameter = $data['COMP_CODE'];
		}
		else if ( !isset( $data['COMP_CODE'] ) && isset( $data['REGION_CODE'] ) ) {
			$parameter = $data['REGION_CODE'];
		}

		$kriteria_find = Data::web_report_inspection_kriteria_find(); // Update 2019-06-18 14:51
		$data_block = Data::hectarestatement_block_find( $parameter );
		$inspection_baris = Data::web_report_inspection_baris_valid_find( '/'.$parameter.'/'.substr( $data['START_DATE'], 0, 6 ) )['items'];

		$inspection_class_block = array();
		$content = Data::web_report_inspection_content_find();
		$content_perawatan = array();
		$content_perawatan_bobot = array();
		$content_pemupukan = array();
		$content_panen = array();
		$count_inspection = array();
		$_bobot_all = 0;
		$_bobot_tbm0 = 0;
		$_bobot_tbm1 = 0;
		$_bobot_tbm2 = 0;
		$_bobot_tbm3 = 0;
		$count_bobot = 0;
		
		foreach ( $content as $d ) {
			if ( $d['TBM3'] == 'YES' ) {
				$_bobot_tbm3 += $d['BOBOT'];
			}
			if ( $d['TBM2'] == 'YES' ) {
				$_bobot_tbm2 += $d['BOBOT'];
			}
			if ( $d['TBM1'] == 'YES' ) {
				$_bobot_tbm1 += $d['BOBOT'];
			}
			if ( $d['TBM0'] == 'YES' ) {
				$_bobot_tbm0 += $d['BOBOT'];
			}
			$_bobot_all += $d['BOBOT'];
			$count_bobot = $count_bobot + $d['BOBOT'];
		}

		foreach( $content as $content_key ) {
			$cc[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
			$cc[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
			$cc[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
			$cc[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
			$cc[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
			$cc[$content_key['CONTENT_CODE']]['LABEL'] = array();
			$cc[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
			$cc[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
			$cc[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
			$cc[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
			$cc[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];

			if ( !empty( $content_key['LABEL'] ) ) {
				$a = 0;
				foreach  ( $content_key['LABEL'] as $label ) {
					$cc[$content_key['CONTENT_CODE']]['LABEL'][$label['LABEL_NAME']] = $label['LABEL_SCORE'];
					$a++;
				}
			}

			if ( $content_key['CATEGORY'] == 'PANEN' ) {
				$content_panen[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
				$content_panen[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
				$content_panen[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
				$content_panen[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
				$content_panen[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
				$content_panen[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
				$content_panen[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
				$content_panen[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
				$content_panen[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
				$content_panen[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];
			}

			if ( $content_key['CATEGORY'] == 'PERAWATAN' ) {
				if ( $content_key['BOBOT'] > 0 ) {
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
					$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];
				}
				else {
					$content_perawatan[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
					$content_perawatan[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
					$content_perawatan[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
					$content_perawatan[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
					$content_perawatan[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
					$content_perawatan[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
					$content_perawatan[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
					$content_perawatan[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
					$content_perawatan[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
					$content_perawatan[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];
				}
			}

			if ( $content_key['CATEGORY'] == 'PEMUPUKAN' ) {
				$content_pemupukan[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
				$content_pemupukan[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
				$content_pemupukan[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
				$content_pemupukan[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
				$content_pemupukan[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
				$content_pemupukan[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
				$content_pemupukan[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
				$content_pemupukan[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
				$content_pemupukan[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
				$content_pemupukan[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];
			}
		}

		foreach ( $inspection_baris as $baris ) {

			$header_id = $baris['WERKS'].$baris['AFD_CODE'].$baris['BLOCK_CODE'];
			if ( !isset( $inspection_class_block[$header_id] ) ) {
				$inspection_class_block[$header_id] = array();
				$inspection_class_block[$header_id]['BA_CODE'] = $baris['WERKS'];
				$inspection_class_block[$header_id]['BA_NAME'] = $baris['EST_NAME'];
				$inspection_class_block[$header_id]['AFD_CODE'] = $baris['AFD_CODE'];
				$inspection_class_block[$header_id]['AFD_NAME'] = $baris['AFD_NAME'];
				$inspection_class_block[$header_id]['BLOCK_CODE'] = $baris['BLOCK_CODE'];
				$inspection_class_block[$header_id]['BLOCK_NAME'] = $baris['BLOCK_NAME'];
				$inspection_class_block[$header_id]['MATURITY_STATUS'] =  $baris['MATURITY_STATUS'];
				$inspection_class_block[$header_id]['PERIODE'] = date( 'Y.m', strtotime( $baris['INSPECTION_DATE'] ) );
				$inspection_class_block[$header_id]['LAMA_INSPEKSI'] = 0;
				$inspection_class_block[$header_id]['DATA_JUMLAH'] = array();
				$inspection_class_block[$header_id]['DATA_RATA2'] = array();
				$inspection_class_block[$header_id]['NILAI_INSPEKSI'] = 0; 
				$inspection_class_block[$header_id]['HASIL_INSPEKSI'] = '';
				$inspection_class_block[$header_id]['JUMLAH_INSPEKSI'] = 0;

				foreach( $content as $ck => $cv ) {
					$inspection_class_block[$header_id]['COUNT_CONTENT'][$cv['CONTENT_CODE']] = 0;
				}
			}
			$inspection_class_block[$header_id]['JUMLAH_INSPEKSI']++;

			if ( !empty( $baris['CONTENT'][0] ) ) {
				foreach ( $baris['CONTENT'][0] as $key_baris => $baris_content ) {
					// Content Code
					$content_code = $key_baris;
					$value = $baris_content;
					if ( isset( $inspection_class_block[$header_id] ) ) {
						if ( $value >= 0 ):
							$inspection_class_block[$header_id]['COUNT_CONTENT'][$content_code]++;
						endif;
					}

					if ( isset( $inspection_class_block[$header_id] ) ) {
						if ( !isset( $inspection_class_block[$header_id]['DATA_JUMLAH_PANEN'][$content_code] ) ) {
							$inspection_class_block[$header_id]['DATA_JUMLAH_PANEN'][$content_code] = 0;
						}
						if ( !isset( $inspection_class_block[$header_id]['DATA_JUMLAH_RAWAT'][$content_code] ) ) {
							$inspection_class_block[$header_id]['DATA_JUMLAH_RAWAT'][$content_code] = 0;
						}
						if ( !isset( $inspection_class_block[$header_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] ) ) {
							$inspection_class_block[$header_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] = 0;
						}
						if ( !isset( $inspection_class_block[$header_id]['DATA_JUMLAH_PERAWATAN'][$content_code] ) ) {
							$inspection_class_block[$header_id]['DATA_JUMLAH_PERAWATAN'][$content_code] = 0;
						}

						if ( $cc[$content_code]['CATEGORY'] == 'PERAWATAN' ) {
							$perawatan_value = $cc[$content_code]['LABEL'][$value];
							$inspection_class_block[$header_id]['DATA_JUMLAH_RAWAT'][$content_code] += $perawatan_value;
						}
						else if ( $cc[$content_code]['CATEGORY'] == 'PANEN' ) {
							if ( isset( $cc[$content_code]['LABEL'][$value] ) ) {
								$inspection_class_block[$header_id]['DATA_JUMLAH_PANEN'][$content_code] += $value;
							}
						}
						else if ( $cc[$content_code]['CATEGORY'] == 'PEMUPUKAN' ) {
							if ( isset( $cc[$content_code]['LABEL'][$value] ) ) {
								$perawatan_value = $cc[$content_code]['LABEL'][$value];
								$inspection_class_block[$header_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] += $perawatan_value;
							}
						}
					}
				}
			}
		}

		# Rata-rata pemupukan
		foreach( $inspection_class_block as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_PEMUPUKAN'] as $x => $y ) {
				$inspection_class_block[$k]['DATA_RATA2_PEMUPUKAN'][$x] = $y / $inspection_class_block[$k]['COUNT_CONTENT'][$x];
			}
		}

		# Rata-rata
		foreach( $inspection_class_block as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
				$inspection_class_block[$k]['DATA_RATA2'][$x] = $y / $inspection_class_block[$k]['COUNT_CONTENT'][$x];
			}
		}

		# Data Bobot Rawat
		foreach( $inspection_class_block as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
				$inspection_class_block[$k]['DATA_BOBOT_RAWAT'][$x] = 0;
				if ( isset( $content_perawatan_bobot[$x] ) ) {
					$inspection_class_block[$k]['DATA_BOBOT_RAWAT'][$x] = $content_perawatan_bobot[$x]['BOBOT'];
				}
			}
		}

		# RATA2 X BOBOT / JUMLAH_BOBOT
		foreach( $inspection_class_block as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
				if ( $inspection_class_block[$k]['MATURITY_STATUS'] == 'TBM0' ) {
					$inspection_class_block[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_class_block[$k]['DATA_RATA2'][$x] * $inspection_class_block[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm0 ;
				}
				else if ( $inspection_class_block[$k]['MATURITY_STATUS'] == 'TBM1' ) {
					$inspection_class_block[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_class_block[$k]['DATA_RATA2'][$x] * $inspection_class_block[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm1 ;
				}
				else if ( $inspection_class_block[$k]['MATURITY_STATUS'] == 'TBM2' ) {
					$inspection_class_block[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_class_block[$k]['DATA_RATA2'][$x] * $inspection_class_block[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm2 ;
				}
				else if ( $inspection_class_block[$k]['MATURITY_STATUS'] == 'TBM3' ) {
					$inspection_class_block[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_class_block[$k]['DATA_RATA2'][$x] * $inspection_class_block[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm3 ;
				}
				else {
					$inspection_class_block[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_class_block[$k]['DATA_RATA2'][$x] * $inspection_class_block[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_all ;
				}
			}
		}

		# NILAI INSPEKSI
		foreach( $inspection_class_block as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
				$inspection_class_block[$k]['NILAI_INSPEKSI'] += $inspection_class_block[$k]['DATA_RATAXBOBOT'][$x];
			}
		}

		# HASIL INSPEKSI
		foreach( $inspection_class_block as $k => $v ) {
			#$hasil = Data::web_report_inspection_kriteria_findone( $inspection_class_block[$k]['NILAI_INSPEKSI'] );
			# Update 2019-06-18 14:51
			$hasil = self::get_kriteria( $kriteria_find, floatval( $inspection_class_block[$k]['NILAI_INSPEKSI'] ) );
			$inspection_class_block[$k]['HASIL_INSPEKSI'] = $hasil['raw'];
		}

		sort( $inspection_class_block );

		// print '<pre>';
		// print_r( $inspection_class_block );
		// print '</pre>';dd();

		$client = new \GuzzleHttp\Client();
		foreach ( $inspection_class_block as $__block ) {
			$res = $client->request( 'POST', $this->url_api_ins_msa_report.'/api/report/class-block', [
				"headers" => [
					"Authorization" => 'Bearer '.$this->access_token,
					"Content-Type" => 'application/json'
				],
				'json' => [
					"WERKS" => $__block['BA_CODE'],
					"AFD_CODE" => $__block['AFD_CODE'],
					"BLOCK_CODE" => $__block['BLOCK_CODE'],
					"CLASS_BLOCK" => $__block['HASIL_INSPEKSI']['GRADE'],
					"DATE_TIME" => str_replace( '.', '', $__block['PERIODE'] )
				],
			]);
		}
	}

	public function get_kriteria( $data_kriteria, $angka ) {
		$data['nilai'] = '';
		$data['angka'] = 0;
		$data['raw'] = array(
			"KRITERIA_CODE" => "",
			"COLOR" => "",
			"GRADE" => "",
			"BATAS_ATAS" => "",
			"BATAS_BAWAH" => "",
			"KONVERSI_ANGKA" => ""
		);

		if ( !empty( $data_kriteria ) ) {
			foreach ( $data_kriteria as $kriteria ) {
				if ( intval( $angka ) == 3 ) {
					$data['nilai'] = $data_kriteria[0]['GRADE'];
					$data['angka'] = $data_kriteria[0]['KONVERSI_ANGKA'];
					$data['raw'] = $data_kriteria[0];
				}

				if ( $angka <= 1 ) {
					if ( $angka >= $kriteria['BATAS_BAWAH'] && $angka <= $kriteria['BATAS_ATAS'] ) {
						$data['nilai'] = $kriteria['GRADE'];
						$data['angka'] = $kriteria['KONVERSI_ANGKA'];
						$data['raw'] = $kriteria;
					}
				}
				else {
					if ( $angka > $kriteria['BATAS_BAWAH'] && $angka <= $kriteria['BATAS_ATAS'] ) {
						$data['nilai'] = $kriteria['GRADE'];
						$data['angka'] = $kriteria['KONVERSI_ANGKA'];
						$data['raw'] = $kriteria;
					}
				}
			}
		}

		return $data;
	}

	public function testing_hasil_kriteria() {
		$kriteria_find = Data::web_report_inspection_kriteria_find();
		$result = self::get_kriteria( $kriteria_find, 3 );
		print '<pre>';
		print_r( $result );
		print '</pre>';
		print 'ABCDZ';
	}

	public function download_excel_class_block( $data, $output = 'excel' ) {

		$parameter = '';
		if ( isset( $data['BLOCK_CODE'] ) ) {
			$parameter = $data['BLOCK_CODE'];
		}
		else if ( !isset( $data['BLOCK_CODE'] ) && isset( $data['AFD_CODE'] ) ) {
			$parameter = $data['AFD_CODE'];
		}
		else if ( !isset( $data['AFD_CODE'] ) && isset( $data['BA_CODE'] ) ) {
			$parameter = $data['BA_CODE'];
		}
		else if ( !isset( $data['BA_CODE'] ) && isset( $data['COMP_CODE'] ) ) {
			$parameter = $data['COMP_CODE'];
		}
		else if ( !isset( $data['COMP_CODE'] ) && isset( $data['REGION_CODE'] ) ) {
			$parameter = $data['REGION_CODE'];
		}

		$periode = substr( $data['START_DATE'], 0, 6 );
		$periode_min_1 = date( 'Ym', strtotime( $periode." - 2 month" ) );
		$periode_min_2 = date( 'Ym', strtotime( $periode." - 3 month" ) );
		$periode_min_3 = date( 'Ym', strtotime( $periode." - 4 month" ) );
		$periode_min_4 = date( 'Ym', strtotime( $periode." - 5 month" ) );
		$periode_min_5 = date( 'Ym', strtotime( $periode." - 6 month" ) );
		$periode_min_6 = date( 'Ym', strtotime( $periode." - 7 month" ) );

		// print '/'.$data['BA_CODE'].'/'.$periode_min_1.'<br />';
		// print '/'.$data['BA_CODE'].'/'.$periode_min_2.'<br />';
		// print '/'.$data['BA_CODE'].'/'.$periode_min_3.'<br />';
		// print '/'.$data['BA_CODE'].'/'.$periode_min_4.'<br />';
		// print '/'.$data['BA_CODE'].'/'.$periode_min_5.'<br />';
		// print '/'.$data['BA_CODE'].'/'.$periode_min_6.'<br />';
		// dd();
		if ( isset( $data['BA_CODE'] ) ) {
			$data_class_block = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode )['items'];
			$data_class_block_min_1 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_1 )['items'];
			$data_class_block_min_2 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_2 )['items'];
			$data_class_block_min_3 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_3 )['items'];
			$data_class_block_min_4 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_4 )['items'];
			$data_class_block_min_5 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_5 )['items'];
			$data_class_block_min_6 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_6 )['items'];

			// print '<pre>';
			// print_r( $data_class_block_min_5 );
			// print '</pre>';
			// dd();

			$data_all_block = Data::hectarestatement_block_find( $data['BA_CODE'] );
			$kriteria_find = Data::web_report_inspection_kriteria_find();
			$kriteria = [];
			$report_data_block = [];
			$report_data_afd = [];
			$report_data_afd_temp = [];
			$report_data_est = [];
			$report_data_est_temp = [];

			$class_block_01 = array();
			$class_block_02 = array();
			$class_block_03 = array();
			$class_block_04 = array();
			$class_block_05 = array();
			$class_block_06 = array();
			$class_block_06 = array();
			$class_block_07 = array();
			$report_data = array();

			foreach ( $kriteria_find as $kt ) {
				$kriteria[$kt['GRADE']] = $kt['KONVERSI_ANGKA'];
			}

			// Set Data Primary
			foreach ( $data_class_block as $cb01 ) {
				$class_block_01[$cb01['WERKS_AFD_BLOCK_CODE']] = $cb01['CLASS_BLOCK'];
			}

			// Set Data Min 1 Month
			foreach ( $data_class_block_min_1 as $cb02 ) {
				$class_block_02[$cb02['WERKS_AFD_BLOCK_CODE']] = $cb02['CLASS_BLOCK'];
			}

			// Set Data Min 2 Month
			foreach ( $data_class_block_min_2 as $cb03 ) {
				$class_block_03[$cb03['WERKS_AFD_BLOCK_CODE']] = $cb03['CLASS_BLOCK'];
			}

			// Set Data Min 3 Month
			foreach ( $data_class_block_min_3 as $cb04 ) {
				$class_block_04[$cb04['WERKS_AFD_BLOCK_CODE']] = $cb04['CLASS_BLOCK'];
			}

			// Set Data Min 4 Month
			foreach ( $data_class_block_min_4 as $cb05 ) {
				$class_block_05[$cb05['WERKS_AFD_BLOCK_CODE']] = $cb05['CLASS_BLOCK'];
			}

			// Set Data Min 5 Month
			foreach ( $data_class_block_min_5 as $cb06 ) {
				$class_block_06[$cb06['WERKS_AFD_BLOCK_CODE']] = $cb06['CLASS_BLOCK'];
			}

			// Set Data Min 6 Month
			foreach ( $data_class_block_min_6 as $cb07 ) {
				$class_block_07[$cb07['WERKS_AFD_BLOCK_CODE']] = $cb07['CLASS_BLOCK'];
			}

			// print '<pre>';
			// print_r($class_block_07);
			// print '</pre>';
			// dd();

			foreach ( $data_all_block as $ablock ) {

				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['WERKS'] = $ablock['WERKS'];
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['AFD_CODE'] = $ablock['AFD_CODE'];
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['BLOCK_CODE'] = $ablock['BLOCK_CODE'];
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['BLOCK_NAME'] = $ablock['BLOCK_NAME'];
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['WERKS_AFD_BLOCK_CODE'] = $ablock['WERKS_AFD_BLOCK_CODE'];

				$class_01 = '';
				$kriteria_angka_01 = 0;
				if ( isset( $class_block_01[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_01 = $class_block_01[$ablock['WERKS_AFD_BLOCK_CODE']];
					$kriteria_angka_01 = $kriteria[$class_01];
				}

				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_01'] = $class_01;
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['ANGKA_01'] = $kriteria_angka_01;

				$class_02 = '';
				$kriteria_angka_02 = 0;
				if ( isset( $class_block_02[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_02 = $class_block_02[$ablock['WERKS_AFD_BLOCK_CODE']];
					$kriteria_angka_02 = $kriteria[$class_02];
				}

				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_02'] = $class_02;
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['ANGKA_02'] = $kriteria_angka_02;

				$class_03 = '';
				$kriteria_angka_03 = 0;
				if ( isset( $class_block_03[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_03 = $class_block_03[$ablock['WERKS_AFD_BLOCK_CODE']];
					$kriteria_angka_03 = $kriteria[$class_03];
				}

				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_03'] = $class_03;
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['ANGKA_03'] = $kriteria_angka_03;

				$class_04 = '';
				$kriteria_angka_04 = 0;
				if ( isset( $class_block_04[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_04 = $class_block_04[$ablock['WERKS_AFD_BLOCK_CODE']];
					$kriteria_angka_04 = $kriteria[$class_04];
				}

				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_04'] = $class_04;
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['ANGKA_04'] = $kriteria_angka_04;

				$class_05 = '';
				$kriteria_angka_05 = 0;
				if ( isset( $class_block_05[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_05 = $class_block_05[$ablock['WERKS_AFD_BLOCK_CODE']];
					$kriteria_angka_05 = $kriteria[$class_05];
				}

				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_05'] = $class_05;
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['ANGKA_05'] = $kriteria_angka_05;

				$class_06 = '';
				$kriteria_angka_06 = 0;
				if ( isset( $class_block_06[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_06 = $class_block_06[$ablock['WERKS_AFD_BLOCK_CODE']];
					$kriteria_angka_06 = $kriteria[$class_06];
				}

				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_06'] = $class_06;
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['ANGKA_06'] = $kriteria_angka_06;

				$class_07 = '';
				$kriteria_angka_07 = 0;
				if ( isset( $class_block_07[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_07 = $class_block_07[$ablock['WERKS_AFD_BLOCK_CODE']];
					$kriteria_angka_07 = $kriteria[$class_07];
				}

				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_07'] = $class_07;
				$report_data_block[$ablock['WERKS_AFD_BLOCK_CODE']]['ANGKA_07'] = $kriteria_angka_07;
			}

			// print '<pre>';
			// print_r( $report_data_block );
			// print '</pre>';
			// dd();

			if ( !empty( $report_data_block ) ) {
				$z = 0;
				foreach ( $report_data_block as $rd_block ) {
					if ( !isset( $report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']] ) ) {
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']] = array(
							"WERKS" => $rd_block['WERKS'],
							"AFD_CODE" => $rd_block['AFD_CODE'],
							"NILAI_01" => "",
							"ANGKA_01" => 0,
							"TEMP_NILAI_01" => "",
							"TEMP_ANGKA_01" => 0,
							"TEMP_JUMLAH_DATA_01" => 0,
							"NILAI_02" => "",
							"ANGKA_02" => 0,
							"TEMP_NILAI_02" => "",
							"TEMP_ANGKA_02" => 0,
							"TEMP_JUMLAH_DATA_02" => 0,
							"NILAI_03" => "",
							"ANGKA_03" => 0,
							"TEMP_NILAI_03" => "",
							"TEMP_ANGKA_03" => 0,
							"TEMP_JUMLAH_DATA_03" => 0,
							"NILAI_04" => "",
							"ANGKA_04" => 0,
							"TEMP_NILAI_04" => "",
							"TEMP_ANGKA_04" => 0,
							"TEMP_JUMLAH_DATA_04" => 0,
							"NILAI_05" => "",
							"ANGKA_05" => 0,
							"TEMP_NILAI_05" => "",
							"TEMP_ANGKA_05" => 0,
							"TEMP_JUMLAH_DATA_05" => 0,
							"NILAI_06" => "",
							"ANGKA_06" => 0,
							"TEMP_NILAI_06" => "",
							"TEMP_ANGKA_06" => 0,
							"TEMP_JUMLAH_DATA_06" => 0,
							"NILAI_07" => "",
							"ANGKA_07" => 0,
							"TEMP_NILAI_07" => "",
							"TEMP_ANGKA_07" => 0,
							"TEMP_JUMLAH_DATA_07" => 0,
							"DATA_BLOCK" => array()
						);
					}

					$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['DATA_BLOCK'][$z] = $rd_block;

					if ( $rd_block['NILAI_01'] != "" ) {
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_JUMLAH_DATA_01']++;
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_ANGKA_01'] += $rd_block['ANGKA_01'];
					}

					if ( $rd_block['NILAI_02'] != "" ) {
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_JUMLAH_DATA_02']++;
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_ANGKA_02'] += $rd_block['ANGKA_02'];
					}

					if ( $rd_block['NILAI_03'] != "" ) {
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_JUMLAH_DATA_03']++;
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_ANGKA_03'] += $rd_block['ANGKA_03'];
					}

					if ( $rd_block['NILAI_04'] != "" ) {
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_JUMLAH_DATA_04']++;
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_ANGKA_04'] += $rd_block['ANGKA_04'];
					}

					if ( $rd_block['NILAI_05'] != "" ) {
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_JUMLAH_DATA_05']++;
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_ANGKA_05'] += $rd_block['ANGKA_05'];
					}

					if ( $rd_block['NILAI_06'] != "" ) {
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_JUMLAH_DATA_06']++;
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_ANGKA_06'] += $rd_block['ANGKA_06'];
					}

					if ( $rd_block['NILAI_07'] != "" ) {
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_JUMLAH_DATA_07']++;
						$report_data_afd_temp[$rd_block['WERKS'].$rd_block['AFD_CODE']]['TEMP_ANGKA_07'] += $rd_block['ANGKA_07'];
					}

					$z++;
				}

				if ( !empty( $report_data_afd_temp ) ) {
					foreach ( $report_data_afd_temp as $key_rd_afd => $rd_afd_temp ) {
						if ( $rd_afd_temp['TEMP_JUMLAH_DATA_01'] == 0 ) {
							$report_data_afd_temp[$key_rd_afd]['ANGKA_01'] = 0;
							$report_data_afd_temp[$key_rd_afd]['NILAI_01'] = '';
						}
						else {
							$rd_afd_temp_angka_01 = $rd_afd_temp['TEMP_ANGKA_01'] / $rd_afd_temp['TEMP_JUMLAH_DATA_01'];
							$kriteria_01 = self::get_kriteria( $kriteria_find, $rd_afd_temp_angka_01 );
							$report_data_afd_temp[$key_rd_afd]['ANGKA_01'] = $kriteria_01['angka'];
							$report_data_afd_temp[$key_rd_afd]['NILAI_01'] = $kriteria_01['nilai'];
						}

						if ( $rd_afd_temp['TEMP_JUMLAH_DATA_02'] == 0 ) {
							$report_data_afd_temp[$key_rd_afd]['ANGKA_02'] = 0;
							$report_data_afd_temp[$key_rd_afd]['NILAI_02'] = '';
						}
						else {
							$rd_afd_temp_angka_02 = $rd_afd_temp['TEMP_ANGKA_02'] / $rd_afd_temp['TEMP_JUMLAH_DATA_02'];
							$kriteria_02 = self::get_kriteria( $kriteria_find, $rd_afd_temp_angka_02 );
							$report_data_afd_temp[$key_rd_afd]['ANGKA_02'] = $kriteria_02['angka'];
							$report_data_afd_temp[$key_rd_afd]['NILAI_02'] = $kriteria_02['nilai'];
						}

						if ( $rd_afd_temp['TEMP_JUMLAH_DATA_03'] == 0 ) {
							$report_data_afd_temp[$key_rd_afd]['ANGKA_03'] = 0;
							$report_data_afd_temp[$key_rd_afd]['NILAI_03'] = '';
						}
						else {
							$rd_afd_temp_angka_03 = $rd_afd_temp['TEMP_ANGKA_03'] / $rd_afd_temp['TEMP_JUMLAH_DATA_03'];
							$kriteria_03 = self::get_kriteria( $kriteria_find, $rd_afd_temp_angka_03 );
							$report_data_afd_temp[$key_rd_afd]['ANGKA_03'] = $kriteria_03['angka'];
							$report_data_afd_temp[$key_rd_afd]['NILAI_03'] = $kriteria_03['nilai'];
						}

						if ( $rd_afd_temp['TEMP_JUMLAH_DATA_04'] == 0 ) {
							$report_data_afd_temp[$key_rd_afd]['ANGKA_04'] = 0;
							$report_data_afd_temp[$key_rd_afd]['NILAI_04'] = '';
						}
						else {
							$rd_afd_temp_angka_04 = $rd_afd_temp['TEMP_ANGKA_04'] / $rd_afd_temp['TEMP_JUMLAH_DATA_04'];
							$kriteria_04 = self::get_kriteria( $kriteria_find, $rd_afd_temp_angka_04 );
							$report_data_afd_temp[$key_rd_afd]['ANGKA_04'] = $kriteria_04['angka'];
							$report_data_afd_temp[$key_rd_afd]['NILAI_04'] = $kriteria_04['nilai'];
						}

						if ( $rd_afd_temp['TEMP_JUMLAH_DATA_05'] == 0 ) {
							$report_data_afd_temp[$key_rd_afd]['ANGKA_05'] = 0;
							$report_data_afd_temp[$key_rd_afd]['NILAI_05'] = '';
						}
						else {
							$rd_afd_temp_angka_05 = $rd_afd_temp['TEMP_ANGKA_05'] / $rd_afd_temp['TEMP_JUMLAH_DATA_05'];
							$kriteria_05 = self::get_kriteria( $kriteria_find, $rd_afd_temp_angka_05 );
							$report_data_afd_temp[$key_rd_afd]['ANGKA_05'] = $kriteria_05['angka'];
							$report_data_afd_temp[$key_rd_afd]['NILAI_05'] = $kriteria_05['nilai'];
						}

						if ( $rd_afd_temp['TEMP_JUMLAH_DATA_06'] == 0 ) {
							$report_data_afd_temp[$key_rd_afd]['ANGKA_06'] = 0;
							$report_data_afd_temp[$key_rd_afd]['NILAI_06'] = '';
						}
						else {
							$rd_afd_temp_angka_06 = $rd_afd_temp['TEMP_ANGKA_06'] / $rd_afd_temp['TEMP_JUMLAH_DATA_06'];
							$kriteria_06 = self::get_kriteria( $kriteria_find, $rd_afd_temp_angka_06 );
							$report_data_afd_temp[$key_rd_afd]['ANGKA_06'] = $kriteria_06['angka'];
							$report_data_afd_temp[$key_rd_afd]['NILAI_06'] = $kriteria_06['nilai'];
						}

						if ( $rd_afd_temp['TEMP_JUMLAH_DATA_07'] == 0 ) {
							$report_data_afd_temp[$key_rd_afd]['ANGKA_07'] = 0;
							$report_data_afd_temp[$key_rd_afd]['NILAI_07'] = '';
						}
						else {
							$rd_afd_temp_angka_07 = $rd_afd_temp['TEMP_ANGKA_07'] / $rd_afd_temp['TEMP_JUMLAH_DATA_07'];
							$kriteria_07 = self::get_kriteria( $kriteria_find, $rd_afd_temp_angka_07 );
							$report_data_afd_temp[$key_rd_afd]['ANGKA_07'] = $kriteria_07['angka'];
							$report_data_afd_temp[$key_rd_afd]['NILAI_07'] = $kriteria_07['nilai'];
						}
					}
				}
			}
			
			if ( !empty( $report_data_afd_temp ) ) {
				$y = 0;
				foreach ( $report_data_afd_temp as $rd_afd_temp_2 ) {
					if ( !isset( $report_data_est_temp[$rd_afd_temp_2['WERKS']] ) ) {
						$report_data_est_temp[$rd_afd_temp_2['WERKS']] = array(
							"WERKS" => $rd_afd_temp_2['WERKS'],
							"NILAI_01" => "",
							"ANGKA_01" => 0,
							"TEMP_NILAI_01" => "",
							"TEMP_ANGKA_01" => 0,
							"TEMP_JUMLAH_DATA_01" => 0,
							"NILAI_02" => "",
							"ANGKA_02" => 0,
							"TEMP_NILAI_02" => "",
							"TEMP_ANGKA_02" => 0,
							"TEMP_JUMLAH_DATA_02" => 0,
							"NILAI_03" => "",
							"ANGKA_03" => 0,
							"TEMP_NILAI_03" => "",
							"TEMP_ANGKA_03" => 0,
							"TEMP_JUMLAH_DATA_03" => 0,
							"NILAI_04" => "",
							"ANGKA_04" => 0,
							"TEMP_NILAI_04" => "",
							"TEMP_ANGKA_04" => 0,
							"TEMP_JUMLAH_DATA_04" => 0,
							"NILAI_05" => "",
							"ANGKA_05" => 0,
							"TEMP_NILAI_05" => "",
							"TEMP_ANGKA_05" => 0,
							"TEMP_JUMLAH_DATA_05" => 0,
							"NILAI_06" => "",
							"ANGKA_06" => 0,
							"TEMP_NILAI_06" => "",
							"TEMP_ANGKA_06" => 0,
							"TEMP_JUMLAH_DATA_06" => 0,
							"NILAI_07" => "",
							"ANGKA_07" => 0,
							"TEMP_NILAI_07" => "",
							"TEMP_ANGKA_07" => 0,
							"TEMP_JUMLAH_DATA_07" => 0,
							"DATA_AFD" => array()
						);
					}

					$report_data_est_temp[$rd_afd_temp_2['WERKS']]['DATA_AFD'][$y] = $rd_afd_temp_2;
					
					if ( $rd_afd_temp_2['NILAI_01'] != '' ) {
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['ANGKA_01'] += $rd_afd_temp_2['ANGKA_01'];
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['TEMP_JUMLAH_DATA_01']++;
					}

					if ( $rd_afd_temp_2['NILAI_02'] != '' ) {
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['ANGKA_02'] += $rd_afd_temp_2['ANGKA_02'];
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['TEMP_JUMLAH_DATA_02']++;
					}

					if ( $rd_afd_temp_2['NILAI_03'] != '' ) {
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['ANGKA_03'] += $rd_afd_temp_2['ANGKA_03'];
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['TEMP_JUMLAH_DATA_03']++;
					}

					if ( $rd_afd_temp_2['NILAI_04'] != '' ) {
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['ANGKA_04'] += $rd_afd_temp_2['ANGKA_04'];
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['TEMP_JUMLAH_DATA_04']++;
					}

					if ( $rd_afd_temp_2['NILAI_05'] != '' ) {
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['ANGKA_05'] += $rd_afd_temp_2['ANGKA_05'];
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['TEMP_JUMLAH_DATA_05']++;
					}

					if ( $rd_afd_temp_2['NILAI_06'] != '' ) {
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['ANGKA_06'] += $rd_afd_temp_2['ANGKA_06'];
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['TEMP_JUMLAH_DATA_06']++;
					}
					
					if ( $rd_afd_temp_2['NILAI_07'] != '' ) {
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['ANGKA_07'] += $rd_afd_temp_2['ANGKA_07'];
						$report_data_est_temp[$rd_afd_temp_2['WERKS']]['TEMP_JUMLAH_DATA_07']++;
					}
					
					$y++;
				}
			}

			// print '<pre>';
			// print_r($report_data_afd_temp);
			// print '</pre>';
			// dd();

			if ( !empty( $report_data_est_temp ) ) {
				foreach ( $report_data_est_temp as $key_est_tmp => $est_tmp ) {
					if ( $est_tmp['TEMP_JUMLAH_DATA_01'] > 0 ) {
						$total_est_01 = $est_tmp['ANGKA_01'] / $est_tmp['TEMP_JUMLAH_DATA_01'];
						$est_kriteria_01 = self::get_kriteria( $kriteria_find, $total_est_01 );
						$report_data_est_temp[$key_est_tmp]['NILAI_01'] = $est_kriteria_01['nilai'];
					}

					if ( $est_tmp['TEMP_JUMLAH_DATA_02'] > 0 ) {
						$total_est_02 = $est_tmp['ANGKA_02'] / $est_tmp['TEMP_JUMLAH_DATA_02'];
						$est_kriteria_02 = self::get_kriteria( $kriteria_find, $total_est_02 );
						$report_data_est_temp[$key_est_tmp]['NILAI_02'] = $est_kriteria_02['nilai'];
					}

					if ( $est_tmp['TEMP_JUMLAH_DATA_03'] > 0 ) {
						$total_est_03 = $est_tmp['ANGKA_03'] / $est_tmp['TEMP_JUMLAH_DATA_03'];
						$est_kriteria_03 = self::get_kriteria( $kriteria_find, $total_est_03 );
						$report_data_est_temp[$key_est_tmp]['NILAI_03'] = $est_kriteria_03['nilai'];
					}

					if ( $est_tmp['TEMP_JUMLAH_DATA_04'] > 0 ) {
						$total_est_04 = $est_tmp['ANGKA_04'] / $est_tmp['TEMP_JUMLAH_DATA_04'];
						$est_kriteria_04 = self::get_kriteria( $kriteria_find, $total_est_04 );
						$report_data_est_temp[$key_est_tmp]['NILAI_04'] = $est_kriteria_04['nilai'];
					}

					if ( $est_tmp['TEMP_JUMLAH_DATA_05'] > 0 ) {
						$total_est_05 = $est_tmp['ANGKA_05'] / $est_tmp['TEMP_JUMLAH_DATA_05'];
						$est_kriteria_05 = self::get_kriteria( $kriteria_find, $total_est_05 );
						$report_data_est_temp[$key_est_tmp]['NILAI_05'] = $est_kriteria_05['nilai'];
					}

					if ( $est_tmp['TEMP_JUMLAH_DATA_06'] > 0 ) {
						$total_est_06 = $est_tmp['ANGKA_06'] / $est_tmp['TEMP_JUMLAH_DATA_06'];
						$est_kriteria_06 = self::get_kriteria( $kriteria_find, $total_est_06 );
						$report_data_est_temp[$key_est_tmp]['NILAI_06'] = $est_kriteria_06['nilai'];
					}

					if ( $est_tmp['TEMP_JUMLAH_DATA_07'] > 0 ) {
						$total_est_07 = $est_tmp['ANGKA_07'] / $est_tmp['TEMP_JUMLAH_DATA_07'];
						$est_kriteria_07 = self::get_kriteria( $kriteria_find, $total_est_07 );
						$report_data_est_temp[$key_est_tmp]['NILAI_07'] = $est_kriteria_07['nilai'];
					}
				}
			}

			$results['report_data'] = $report_data_est_temp;
			$results['periode'] = date( 'Ym', strtotime( $periode.'01' ) );

			#print '<pre>';
			#print_r( $report_data_est_temp );
			#print '</pre>';
			#dd();

			Excel::create( 'Report-Class-Block', function( $excel ) use ( $results ) {
				$excel->sheet( 'Per Block', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'report.excel-class-block-2', $results );
				} );
			} )->export( 'xls' );
		}
	}

	/** ZONA BONGKAR PASANG -----------------------------------------------------------------END **/






























	/*
	 |--------------------------------------------------------------------------
	 | Cron - Generate - Inspeksi
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function cron_generate_inspeksi() {
			$url = $this->url_api_ins_msa_hectarestatement.'/region/all';
			$region_data = APISetup::ins_rest_client_manual( 'GET', $url );
			$parameter = array();
			$parameter['START_DATE'] = date( 'Ym01' );
			$parameter['END_DATE'] = date( 'Ymt' );
			$response = array();
			$response['message'] = 'Cron - Generate Report Inspeksi';
			$response['from'] = date( 'Y-m-01' );
			$response['to'] = date( 'Y-m-t' );
			$response['results'] = array();
			
			$i = 0;
			foreach ( $region_data['data'] as $data ) {
				$parameter['REGION_CODE'] = ( String ) $data['REGION_CODE'];
				$res = self::generate_inspeksi( $parameter );
				$response['results'][$i]['region_code'] = ( String ) $data['REGION_CODE'];
				$response['results'][$i]['start_time'] = $res['start_time'];
				$response['results'][$i]['end_time'] = $res['end_time'];
				$response['results'][$i]['results'] = array(
					"success" => $res['results']['success'],
					"failed" => $res['results']['failed'],
					"data" => $res['results']['data'],
				);
				
				$i++;
			}

			return response()->json( $response );
		}

	/*
	 |--------------------------------------------------------------------------
	 | Excel - Finding
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function excel_finding( $data ) {

			$category = Data::category_find();
			$categories = array();

			if ( !empty( $category ) ) {
				foreach( $category as $cat ) {
					$categories[$cat['CATEGORY_CODE']]['CATEGORY_NAME'] = $cat['CATEGORY_NAME'];
				}
			}

			$query_finding['REGION_CODE'] = ( isset( $data['REGION_CODE'] ) ? $data['REGION_CODE'] : "");
			$query_finding['COMP_CODE'] = ( isset( $data['COMP_CODE'] ) ? $data['COMP_CODE'] : "");
			$query_finding['WERKS'] = ( isset( $data['BA_CODE'] ) ? $data['BA_CODE'] : "");
			$query_finding['AFD_CODE'] = ( isset( $data['AFD_CODE'] ) ? $data['AFD_CODE'] : "");
			$query_finding['BLOCK_CODE'] = ( isset( $data['BLOCK_CODE'] ) ? $data['BLOCK_CODE'] : "");
			$query_finding['START_DATE'] = $data['START_DATE'].'000000';
			$query_finding['END_DATE'] = $data['END_DATE'].'235959';
			
			$data['finding_data'] = array();
			$finding_data = Data::web_report_finding_find( $query_finding )['items'];
			$i = 0;

			foreach ( $finding_data as $finding ) {

				$hectarestatement =  Data::web_report_land_use_findone( $finding['WERKS'].$finding['AFD_CODE'].$finding['BLOCK_CODE'] );
				$finding['BLOCK_NAME'] = '';
				$finding['EST_NAME'] = '';
				$finding['MATURITY_STATUS'] = '';
				$finding['SPMON'] = '';
				if ( !empty( $hectarestatement ) ) {
					$finding['BLOCK_NAME'] = $hectarestatement['BLOCK_NAME'];
					$finding['EST_NAME'] = $hectarestatement['EST_NAME'];
					$finding['MATURITY_STATUS'] = $hectarestatement['MATURITY_STATUS'];
					$finding['SPMON'] = $hectarestatement['SPMON'];
				}

				$data['finding_data'][$i]['FINDING_CATEGORY'] = '';
				if ( isset( $categories[$finding['FINDING_CATEGORY']] ) ) {
					$data['finding_data'][$i]['FINDING_CATEGORY'] = $categories[$finding['FINDING_CATEGORY']]['CATEGORY_NAME'];
				}

				// Data Finding
				$data['finding_data'][$i]['FINDING_CODE'] = $finding['FINDING_CODE'];
				$data['finding_data'][$i]['WERKS'] = $finding['WERKS'];
				$data['finding_data'][$i]['EST_NAME'] = $finding['EST_NAME'];
				$data['finding_data'][$i]['AFD_CODE'] = $finding['AFD_CODE'];
				$data['finding_data'][$i]['BLOCK_CODE'] = $finding['BLOCK_CODE'];
				$data['finding_data'][$i]['BLOCK_NAME'] = $finding['BLOCK_NAME'];
				$data['finding_data'][$i]['SPMON'] = $finding['SPMON'];
				$data['finding_data'][$i]['MATURITY_STATUS'] = $finding['MATURITY_STATUS'];
				#$data['finding_data'][$i]['FINDING_CATEGORY'] = $finding['FINDING_CATEGORY'];
				$data['finding_data'][$i]['FINDING_DESC'] = $finding['FINDING_DESC'];
				$data['finding_data'][$i]['FINDING_PRIORITY'] = $finding['FINDING_PRIORITY'];
				$data['finding_data'][$i]['DUE_DATE'] = $finding['DUE_DATE'];
				$data['finding_data'][$i]['STATUS'] = ( isset( $finding['STATUS'] ) ? $finding['STATUS'] : "" );
				$data['finding_data'][$i]['ASSIGN_TO'] = $finding['ASSIGN_TO'];
				$data['finding_data'][$i]['PROGRESS'] = $finding['PROGRESS'];
				$data['finding_data'][$i]['LAT_FINDING'] = $finding['LAT_FINDING'];
				$data['finding_data'][$i]['LONG_FINDING'] = $finding['LONG_FINDING'];
				$data['finding_data'][$i]['REFFERENCE_INS_CODE'] = $finding['REFFERENCE_INS_CODE'];
				$data['finding_data'][$i]['INSERT_USER'] = $finding['INSERT_USER'];
				$data['finding_data'][$i]['INSERT_TIME'] = $finding['INSERT_TIME'];
				$data['finding_data'][$i]['UPDATE_USER'] = $finding['UPDATE_USER'];
				$data['finding_data'][$i]['UPDATE_TIME'] = $finding['UPDATE_TIME'];

				// Data Inspektor
				$inspektor_data = Data::user_find_one( ( String ) $finding['INSERT_USER'] )['items'];
				$data['finding_data'][$i]['INSPEKTOR']['FULLNAME'] = $inspektor_data['FULLNAME'];
				$data['finding_data'][$i]['INSPEKTOR']['JOB'] = $inspektor_data['JOB'];
				$data['finding_data'][$i]['INSPEKTOR']['REF_ROLE'] = $inspektor_data['REF_ROLE'];
				$data['finding_data'][$i]['INSPEKTOR']['USER_ROLE'] = $inspektor_data['USER_ROLE'];
				$data['finding_data'][$i]['INSPEKTOR']['USER_AUTH_CODE'] = $inspektor_data['USER_AUTH_CODE'];
				$data['finding_data'][$i]['INSPEKTOR']['EMPLOYEE_NIK'] = $inspektor_data['EMPLOYEE_NIK'];

				// Data Inspektor
				$pic_data = Data::user_find_one( ( String ) $finding['ASSIGN_TO'] )['items'];
				$data['finding_data'][$i]['PIC']['FULLNAME'] = $pic_data['FULLNAME'];
				$data['finding_data'][$i]['PIC']['EMPLOYEE_NIK'] = $pic_data['EMPLOYEE_NIK'];

				// Data Land Use
				$i++;
			}

			# Generate to excel file
			Excel::create( 'Report-Finding', function( $excel ) use ( $data ) {
				$excel->sheet( 'Temuan', function( $sheet ) use ( $data ) {
					$sheet->loadView( 'report.excel-finding', $data );
				} );
			} )->export( 'xls' );
		}

	/*
	 |--------------------------------------------------------------------------
	 | Excel Inspeksi
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function excel_inspeksi( $data, $output = 'excel' ) {
			
			$parameter = '';
			if ( isset( $data['BLOCK_CODE'] ) ) {
				$parameter = $data['BLOCK_CODE'];
			}
			else if ( !isset( $data['BLOCK_CODE'] ) && isset( $data['AFD_CODE'] ) ) {
				$parameter = $data['AFD_CODE'];
			}
			else if ( !isset( $data['AFD_CODE'] ) && isset( $data['BA_CODE'] ) ) {
				$parameter = $data['BA_CODE'];
			}
			else if ( !isset( $data['BA_CODE'] ) && isset( $data['COMP_CODE'] ) ) {
				$parameter = $data['COMP_CODE'];
			}
			else if ( !isset( $data['COMP_CODE'] ) && isset( $data['REGION_CODE'] ) ) {
				$parameter = $data['REGION_CODE'];
			}
			$kriteria_find = Data::web_report_inspection_kriteria_find(); // Update 2019-06-18 14:51
			$inspection_baris = Data::web_report_inspection_baris_find( '/'.$parameter.'/'.$data['START_DATE'].'/'.$data['END_DATE'] )['items'];

			// print '<pre>';
			// print_r( $inspection_baris );
			// print '</pre>';
			// dd();
			$inspection_header = array();
			$content = Data::web_report_inspection_content_find();
			$kriteria_find = Data::web_report_inspection_kriteria_find();
			$content_perawatan = array();
			$content_perawatan_bobot = array();
			$content_pemupukan = array();
			$content_panen = array();
			$count_inspection = array();
			$_bobot_all = 0;
			$_bobot_tbm0 = 0;
			$_bobot_tbm1 = 0;
			$_bobot_tbm2 = 0;
			$_bobot_tbm3 = 0;
			$count_bobot = 0;
			
			foreach ( $content as $d ) {
				if ( $d['TBM3'] == 'YES' ) {
					$_bobot_tbm3 += $d['BOBOT'];
				}
				if ( $d['TBM2'] == 'YES' ) {
					$_bobot_tbm2 += $d['BOBOT'];
				}
				if ( $d['TBM1'] == 'YES' ) {
					$_bobot_tbm1 += $d['BOBOT'];
				}
				if ( $d['TBM0'] == 'YES' ) {
					$_bobot_tbm0 += $d['BOBOT'];
				}
				$_bobot_all += $d['BOBOT'];
				$count_bobot = $count_bobot + $d['BOBOT'];
			}

			foreach( $content as $content_key ) {
				$cc[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
				$cc[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
				$cc[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
				$cc[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
				$cc[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
				$cc[$content_key['CONTENT_CODE']]['LABEL'] = array();
				$cc[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
				$cc[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
				$cc[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
				$cc[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
				$cc[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];

				if ( !empty( $content_key['LABEL'] ) ) {
					$a = 0;
					foreach  ( $content_key['LABEL'] as $label ) {
						$cc[$content_key['CONTENT_CODE']]['LABEL'][$label['LABEL_NAME']] = $label['LABEL_SCORE'];
						$a++;
					}
				}

				if ( $content_key['CATEGORY'] == 'PANEN' ) {
					$content_panen[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
					$content_panen[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
					$content_panen[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
					$content_panen[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
					$content_panen[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
					$content_panen[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
					$content_panen[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
					$content_panen[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
					$content_panen[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
					$content_panen[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];
				}

				if ( $content_key['CATEGORY'] == 'PERAWATAN' ) {
					if ( $content_key['BOBOT'] > 0 ) {
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
						$content_perawatan_bobot[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];
					}
					else {
						$content_perawatan[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
						$content_perawatan[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
						$content_perawatan[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
						$content_perawatan[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
						$content_perawatan[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
						$content_perawatan[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
						$content_perawatan[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
						$content_perawatan[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
						$content_perawatan[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
						$content_perawatan[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];
					}
				}

				if ( $content_key['CATEGORY'] == 'PEMUPUKAN' ) {
					$content_pemupukan[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
					$content_pemupukan[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
					$content_pemupukan[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
					$content_pemupukan[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
					$content_pemupukan[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
					$content_pemupukan[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
					$content_pemupukan[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
					$content_pemupukan[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
					$content_pemupukan[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
					$content_pemupukan[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];
				}
			}

			foreach ( $inspection_baris as $baris ) {

				if ( count( $baris['CONTENT'] ) > 0 ) {
					$header_id = $baris['REPORTER_NIK'].$baris['WERKS'].$baris['AFD_CODE'].$baris['BLOCK_CODE'].$baris['INSPECTION_DATE'];
					if ( !isset( $inspection_header[$header_id] ) ) {
						$inspection_header[$header_id] = array();
						$inspection_header[$header_id]['NIK_REPORTER'] = $baris['REPORTER_NIK'];
						$inspection_header[$header_id]['NAMA_REPORTER'] = $baris['REPORTER_FULLNAME'];
						$inspection_header[$header_id]['JABATAN'] = $baris['REPORTER_JOB'];
						$inspection_header[$header_id]['BA_CODE'] = $baris['WERKS'];
						$inspection_header[$header_id]['BA_NAME'] = $baris['EST_NAME'];
						$inspection_header[$header_id]['AFD_CODE'] = $baris['AFD_CODE'];
						$inspection_header[$header_id]['AFD_NAME'] = $baris['AFD_NAME'];
						$inspection_header[$header_id]['BLOCK_CODE'] = $baris['BLOCK_CODE'];
						$inspection_header[$header_id]['BLOCK_NAME'] = $baris['BLOCK_NAME'];
						$inspection_header[$header_id]['INSPECTION_DATE'] = $baris['INSPECTION_DATE'];
						$inspection_header[$header_id]['INSPECTION_TIME'] = $baris['INSPECTION_TIME'];
						$inspection_header[$header_id]['MATURITY_STATUS'] =  $baris['MATURITY_STATUS'];
						$inspection_header[$header_id]['PERIODE'] = date( 'Y.m', strtotime( $baris['SPMON'] ) );
						$inspection_header[$header_id]['LAMA_INSPEKSI'] = 0;
						$inspection_header[$header_id]['DATA_JUMLAH'] = array();
						$inspection_header[$header_id]['DATA_RATA2'] = array();
						$inspection_header[$header_id]['NILAI_INSPEKSI'] = 0; 
						$inspection_header[$header_id]['HASIL_INSPEKSI'] = '';
						$inspection_header[$header_id]['JUMLAH_INSPEKSI'] = 0;

						foreach( $content as $ck => $cv ) {
							$inspection_header[$header_id]['COUNT_CONTENT'][$cv['CONTENT_CODE']] = 0;
						}
					}
					$inspection_header[$header_id]['JUMLAH_INSPEKSI']++;
					$inspection_header[$header_id]['LAMA_INSPEKSI'] += $baris['LAMA_INSPEKSI'];

					if ( !empty( $baris['CONTENT'][0] ) ) {
						foreach ( $baris['CONTENT'][0] as $key_baris => $baris_content ) {
							// Content Code
							$content_code = $key_baris;
							$value = $baris_content;
							if ( isset( $inspection_header[$header_id] ) ) {
								if ( $value >= 0 ):
									$inspection_header[$header_id]['COUNT_CONTENT'][$content_code]++;
								endif;
							}

							if ( isset( $inspection_header[$header_id] ) ) {
								if ( !isset( $inspection_header[$header_id]['DATA_JUMLAH_PANEN'][$content_code] ) ) {
									$inspection_header[$header_id]['DATA_JUMLAH_PANEN'][$content_code] = 0;
								}
								if ( !isset( $inspection_header[$header_id]['DATA_JUMLAH_RAWAT'][$content_code] ) ) {
									$inspection_header[$header_id]['DATA_JUMLAH_RAWAT'][$content_code] = 0;
								}
								if ( !isset( $inspection_header[$header_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] ) ) {
									$inspection_header[$header_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] = 0;
								}
								if ( !isset( $inspection_header[$header_id]['DATA_JUMLAH_PERAWATAN'][$content_code] ) ) {
									$inspection_header[$header_id]['DATA_JUMLAH_PERAWATAN'][$content_code] = 0;
								}

								if ( $cc[$content_code]['CATEGORY'] == 'PERAWATAN' ) {
									$perawatan_value = $cc[$content_code]['LABEL'][$value];
									$inspection_header[$header_id]['DATA_JUMLAH_RAWAT'][$content_code] += $perawatan_value;
								}
								else if ( $cc[$content_code]['CATEGORY'] == 'PANEN' ) {
									$inspection_header[$header_id]['DATA_JUMLAH_PANEN'][$content_code] += $value;
								}
								else if ( $cc[$content_code]['CATEGORY'] == 'PEMUPUKAN' ) {
									if ( isset( $cc[$content_code]['LABEL'][$value] ) ) {
										$perawatan_value = $cc[$content_code]['LABEL'][$value];
										$inspection_header[$header_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] += $perawatan_value;
									}
								}
							}
						}
					}
				}
			}


			if ( isset( $inspection_header ) ):
				# Rata-rata pemupukan
				foreach( $inspection_header as $k => $v ) {
					foreach ( $v['DATA_JUMLAH_PEMUPUKAN'] as $x => $y ) {
						$inspection_header[$k]['DATA_RATA2_PEMUPUKAN'][$x] = $y / $inspection_header[$k]['COUNT_CONTENT'][$x];
					}
				}

				# Rata-rata
				foreach( $inspection_header as $k => $v ) {
					foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
						$inspection_header[$k]['DATA_RATA2'][$x] = $y / $inspection_header[$k]['COUNT_CONTENT'][$x];
					}
				}

				# Data Bobot Rawat
				foreach( $inspection_header as $k => $v ) {
					foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
						$inspection_header[$k]['DATA_BOBOT_RAWAT'][$x] = 0;
						if ( isset( $content_perawatan_bobot[$x] ) ) {
							$inspection_header[$k]['DATA_BOBOT_RAWAT'][$x] = $content_perawatan_bobot[$x]['BOBOT'];
						}
					}
				}

				# RATA2 X BOBOT / JUMLAH_BOBOT
				foreach( $inspection_header as $k => $v ) {
					foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
						if ( $inspection_header[$k]['MATURITY_STATUS'] == 'TBM0' ) {
							$inspection_header[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_header[$k]['DATA_RATA2'][$x] * $inspection_header[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm0 ;
						}
						else if ( $inspection_header[$k]['MATURITY_STATUS'] == 'TBM1' ) {
							$inspection_header[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_header[$k]['DATA_RATA2'][$x] * $inspection_header[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm1 ;
						}
						else if ( $inspection_header[$k]['MATURITY_STATUS'] == 'TBM2' ) {
							$inspection_header[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_header[$k]['DATA_RATA2'][$x] * $inspection_header[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm2 ;
						}
						else if ( $inspection_header[$k]['MATURITY_STATUS'] == 'TBM3' ) {
							$inspection_header[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_header[$k]['DATA_RATA2'][$x] * $inspection_header[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm3 ;
						}
						else {
							$inspection_header[$k]['DATA_RATAXBOBOT'][$x] = ( $inspection_header[$k]['DATA_RATA2'][$x] * $inspection_header[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_all ;
						}
					}
				}

				# NILAI INSPEKSI
				foreach( $inspection_header as $k => $v ) {
					foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
						$inspection_header[$k]['NILAI_INSPEKSI'] += $inspection_header[$k]['DATA_RATAXBOBOT'][$x];
					}
				}

				# HASIL INSPEKSI
				foreach( $inspection_header as $k => $v ) {
					#$hasil = Data::web_report_inspection_kriteria_findone( $inspection_header[$k]['NILAI_INSPEKSI'] );
					$hasil = self::get_kriteria( $kriteria_find, $inspection_header[$k]['NILAI_INSPEKSI'] ); # Update 2019-06-18 14:51
					$inspection_header[$k]['HASIL_INSPEKSI'] = $hasil['raw'];

					// print '<pre>';
					// print_r( $hasil );
					// print '</pre><hr /><br />';
				}

				array_multisort( 
					array_column( $inspection_header, 'BA_CODE' ), SORT_ASC,
					array_column( $inspection_header, 'AFD_CODE' ), SORT_ASC,
					array_column( $inspection_header, 'BLOCK_NAME' ), SORT_ASC,
					array_column( $inspection_header, 'INSPECTION_DATE' ), SORT_ASC,
					$inspection_header
				);

				$data['inspection_baris'] = $inspection_baris;
				$data['inspection_header'] = $inspection_header;
				$data['periode'] = date( 'Ym', strtotime( $data['START_DATE'] ) );
				$data['content'] = $content;
				$data['content_perawatan'] = $content_perawatan;
				$data['content_perawatan_bobot'] = $content_perawatan_bobot;
				$data['content_pemupukan'] = $content_pemupukan;
				$data['content_panen'] = $content_panen;

				// print '<pre>';
				// print_r( $data );
				// print '</pre>';
				// dd();

				Excel::create( 'Report-Inspeksi', function( $excel ) use ( $data ) {
					$excel->sheet( 'Per Baris', function( $sheet ) use ( $data ) {
						$sheet->loadView( 'report.excel-inspection-baris', $data );
					} );
					$excel->sheet( 'Per Inspeksi', function( $sheet ) use ( $data ) {
						$sheet->loadView( 'report.excel-inspection-header', $data );
					} );
				} )->export( 'xls' );
			endif;
		}

	/*
	 |--------------------------------------------------------------------------
	 | Generate - Inspeksi
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function generate_inspeksi( $data ) {

			// $data['START_DATE'] = '20190501';
			// $data['END_DATE'] = '20190531';
			// print '<pre>';
			// print_r( $data );
			// print '</pre>';
			// dd();

			$query_inspeksi['REGION_CODE'] = ( isset( $data['REGION_CODE'] ) ? $data['REGION_CODE'] : "" );
			$query_inspeksi['COMP_CODE'] = ( isset( $data['COMP_CODE'] ) ? $data['COMP_CODE'] : "" );
			$query_inspeksi['WERKS'] = ( isset( $data['BA_CODE'] ) ? $data['BA_CODE'] : "" );
			$query_inspeksi['AFD_CODE'] = ( isset( $data['AFD_CODE'] ) ? $data['AFD_CODE'] : "" );
			$query_inspeksi['BLOCK_CODE'] = ( isset( $data['BLOCK_CODE'] ) ? $data['BLOCK_CODE'] : "" );
			$query_inspeksi['START_DATE'] = $data['START_DATE'].'000000';
			$query_inspeksi['END_DATE'] = $data['END_DATE'].'235959';

			$response = array();
			$response['message'] = 'Generate Report Inspeksi';
			$response['start_time'] = date( 'Y-m-d H:i:s' );
			$response['results'] = array(
				"success" => 0,
				"failed" => 0,
				"data" => array()
			);

			$content = Data::web_report_inspection_content_find( 'manual' );
			$content_perawatan = array();
			$content_perawatan_bobot = array();
			$content_pemupukan = array();
			$content_panen = array();
			$inspection_header = array();
			$inspection_detail = Data::web_report_inspection_find( $query_inspeksi, 'manual' )['items'];
			$count_inspection = array();
			
			$_bobot_all = 0;
			$_bobot_tbm0 = 0;
			$_bobot_tbm1 = 0;
			$_bobot_tbm2 = 0;
			$_bobot_tbm3 = 0;
			$count_bobot = 0;

			foreach ( $content as $d ) {
				if ( $d['TBM3'] == 'YES' ) {
					$_bobot_tbm3 += $d['BOBOT'];
				}
				if ( $d['TBM2'] == 'YES' ) {
					$_bobot_tbm2 += $d['BOBOT'];
				}
				if ( $d['TBM1'] == 'YES' ) {
					$_bobot_tbm1 += $d['BOBOT'];
				}
				if ( $d['TBM0'] == 'YES' ) {
					$_bobot_tbm0 += $d['BOBOT'];
				}
				$_bobot_all += $d['BOBOT'];
				$count_bobot = $count_bobot + $d['BOBOT'];
			}

			foreach( $content as $content_key ) {
				$cc[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_CODE'];
				$cc[$content_key['CONTENT_CODE']]['CONTENT_NAME'] = $content_key['CONTENT_NAME'];
				$cc[$content_key['CONTENT_CODE']]['BOBOT'] = $content_key['BOBOT'];
				$cc[$content_key['CONTENT_CODE']]['CATEGORY'] = $content_key['CATEGORY'];
				$cc[$content_key['CONTENT_CODE']]['URUTAN'] = $content_key['URUTAN'];
				$cc[$content_key['CONTENT_CODE']]['LABEL'] = array();
				$cc[$content_key['CONTENT_CODE']]['TBM0'] = $content_key['TBM0'];
				$cc[$content_key['CONTENT_CODE']]['TBM1'] = $content_key['TBM1'];
				$cc[$content_key['CONTENT_CODE']]['TBM2'] = $content_key['TBM2'];
				$cc[$content_key['CONTENT_CODE']]['TBM3'] = $content_key['TBM3'];
				$cc[$content_key['CONTENT_CODE']]['TM'] = $content_key['TM'];

				if ( !empty( $content_key['LABEL'] ) ) {
					$a = 0;
					foreach  ( $content_key['LABEL'] as $label ) {
						$cc[$content_key['CONTENT_CODE']]['LABEL'][$label['LABEL_NAME']] = $label['LABEL_SCORE'];
						$a++;
					}
				}
			}

			$status = false;
			$i = 0;

			foreach ( $inspection_detail as $ins_detail ) {
				if ( !empty( $ins_detail['DETAIL'] ) ) {
					$date_inspeksi = substr( $ins_detail['INSPECTION_DATE'], 0, 8 );
					$time_inspeksi = $ins_detail['INSPECTION_DATE'];
					$hectarestatement =  Data::web_report_land_use_findone( $ins_detail['WERKS'].$ins_detail['AFD_CODE'].$ins_detail['BLOCK_CODE'], 'manual' );
					
					$inspektor_data = Data::user_find_one( ( String ) $ins_detail['INSERT_USER'] )['items'];
					$baris_start_ins = date( 'Y-m-d H:i:s', strtotime( $ins_detail['START_INSPECTION'] ) );
					$baris_end_ins = date( 'Y-m-d H:i:s', strtotime( $ins_detail['END_INSPECTION'] ) );
					$baris_diff = ( new DateTime( $baris_start_ins ) )->diff( new DateTime( $baris_end_ins ) );

					$data['inspection_data'][$i]['CONTENT'] = array();
					$data['inspection_data'][$i]['CONTENT_PANEN'] = array();
					$data['inspection_data'][$i]['CONTENT_PERAWATAN'] = array();
					$data['inspection_data'][$i]['CONTENT_PEMUPUKAN'] = array();

					foreach ( $ins_detail['DETAIL'] as $detail ) {
						// Content Code
						$content_code = $detail['CONTENT_INSPECTION_CODE'];
						// Isi ke konten
						$data['inspection_data'][$i]['CONTENT'][$content_code] =  $detail['VALUE'];
						// Convert Konten Perawatan
						if ( $cc[$content_code]['CATEGORY'] == 'PERAWATAN' ) {
							$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
							$data['inspection_data'][$i]['CONTENT_PERAWATAN'][$content_code] =  intval( $perawatan_value );
						}
						// Convert Konten Panen
						else if ( $cc[$content_code]['CATEGORY'] == 'PANEN' ) {
							$data['inspection_data'][$i]['CONTENT_PANEN'][$content_code] = intval( $detail['VALUE'] );
						}
						// Convert Konten Pemupukan
						else if ( $cc[$content_code]['CATEGORY'] == 'PEMUPUKAN' ) {
							if ( isset( $cc[$content_code]['LABEL'][$detail['VALUE']] ) ) {
								$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
								$data['inspection_data'][$i]['CONTENT_PEMUPUKAN'][$content_code] =  intval( $perawatan_value );
							}
						}
					}

					// print '<pre>';
					// print_r( 
					// 	array(
					// 		"BLOCK_INSPECTION_CODE" => $ins_detail['BLOCK_INSPECTION_CODE'],
					// 		"PERIODE" => date( 'Ym', strtotime( $data['START_DATE'] ) ),
					// 		"WERKS_AFD_CODE" => $ins_detail['WERKS'].$ins_detail['AFD_CODE'],
					// 		"WERKS_AFD_BLOCK_CODE" => $ins_detail['WERKS'].$ins_detail['AFD_CODE'].$ins_detail['BLOCK_CODE'],
					// 		"WERKS" => $ins_detail['WERKS'],
					// 		"EST_NAME" => $hectarestatement['EST_NAME'],
					// 		"AFD_CODE" => $ins_detail['AFD_CODE'],
					// 		"AFD_NAME" => $hectarestatement['AFD_NAME'],
					// 		"BLOCK_CODE" => $ins_detail['BLOCK_CODE'],
					// 		"BLOCK_NAME" => $hectarestatement['BLOCK_NAME'],
					// 		"LAT_START_INSPECTION" => $ins_detail['LAT_START_INSPECTION'],
					// 		"LONG_START_INSPECTION" => $ins_detail['LAT_START_INSPECTION'],
					// 		"INSPECTION_DATE" => $date_inspeksi,
					// 		"INSPECTION_TIME" => $time_inspeksi,
					// 		"AREAL" => $ins_detail['AREAL'],
					// 		"LAMA_INSPEKSI" => ( $baris_diff->i * 60 ) + $baris_diff->s,
					// 		"SPMON" => $hectarestatement['SPMON'],
					// 		"MATURITY_STATUS" =>  str_replace( ' ', '', $hectarestatement['MATURITY_STATUS'] ),
					// 		"REPORTER_FULLNAME" => $inspektor_data['FULLNAME'],
					// 		"REPORTER_JOB" => $inspektor_data['JOB'],
					// 		"REPORTER_REF_ROLE" => $inspektor_data['REF_ROLE'],
					// 		"REPORTER_USER_ROLE" => $inspektor_data['USER_ROLE'],
					// 		"REPORTER_USER_AUTH_CODE" => $inspektor_data['USER_AUTH_CODE'],
					// 		"REPORTER_NIK" => $inspektor_data['EMPLOYEE_NIK'],
					// 		"CONTENT" => $data['inspection_data'][$i]['CONTENT'],
					// 		"CONTENT_PANEN" => $data['inspection_data'][$i]['CONTENT_PANEN'],
					// 		"CONTENT_PERAWATAN" => $data['inspection_data'][$i]['CONTENT_PERAWATAN'],
					// 		"CONTENT_PEMUPUKAN" => $data['inspection_data'][$i]['CONTENT_PEMUPUKAN'],
					// 	) 
					// );
					// print '</pre><hr />';

					$client = new \GuzzleHttp\Client();
					$res = $client->request( 'POST', $this->url_api_ins_msa_report.'/api/report/inspection-baris', [
						"headers" => [
							"Authorization" => 'Bearer '.$this->access_token,
							"Content-Type" => 'application/json'
						],
						'json' => [
							"BLOCK_INSPECTION_CODE" => $ins_detail['BLOCK_INSPECTION_CODE'],
							"PERIODE" => date( 'Ym', strtotime( $data['START_DATE'] ) ),
							"WERKS_AFD_CODE" => $ins_detail['WERKS'].$ins_detail['AFD_CODE'],
							"WERKS_AFD_BLOCK_CODE" => $ins_detail['WERKS'].$ins_detail['AFD_CODE'].$ins_detail['BLOCK_CODE'],
							"WERKS" => $ins_detail['WERKS'],
							"EST_NAME" => $hectarestatement['EST_NAME'],
							"AFD_CODE" => $ins_detail['AFD_CODE'],
							"AFD_NAME" => $hectarestatement['AFD_NAME'],
							"BLOCK_CODE" => $ins_detail['BLOCK_CODE'],
							"BLOCK_NAME" => $hectarestatement['BLOCK_NAME'],
							"LAT_START_INSPECTION" => $ins_detail['LAT_START_INSPECTION'],
							"LONG_START_INSPECTION" => $ins_detail['LAT_START_INSPECTION'],
							"INSPECTION_DATE" => $date_inspeksi,
							"INSPECTION_TIME" => $time_inspeksi,
							"AREAL" => $ins_detail['AREAL'],
							"LAMA_INSPEKSI" => ( $baris_diff->i * 60 ) + $baris_diff->s,
							"SPMON" => $hectarestatement['SPMON'],
							"MATURITY_STATUS" =>  str_replace( ' ', '', $hectarestatement['MATURITY_STATUS'] ),
							"REPORTER_FULLNAME" => $inspektor_data['FULLNAME'],
							"REPORTER_JOB" => $inspektor_data['JOB'],
							"REPORTER_REF_ROLE" => $inspektor_data['REF_ROLE'],
							"REPORTER_USER_ROLE" => $inspektor_data['USER_ROLE'],
							"REPORTER_USER_AUTH_CODE" => $inspektor_data['USER_AUTH_CODE'],
							"REPORTER_NIK" => $inspektor_data['EMPLOYEE_NIK'],
							"CONTENT" => $data['inspection_data'][$i]['CONTENT'],
							"CONTENT_PANEN" => $data['inspection_data'][$i]['CONTENT_PANEN'],
							"CONTENT_PERAWATAN" => $data['inspection_data'][$i]['CONTENT_PERAWATAN'],
							"CONTENT_PEMUPUKAN" => $data['inspection_data'][$i]['CONTENT_PEMUPUKAN'],
						],
					] );

					array_push( $response['results']['data'], $ins_detail['BLOCK_INSPECTION_CODE'] );
					
					if ( json_decode( $res->getBody(), true )['status'] == false ) {
						$response['results']['failed']++;
					}
					else {
						$response['results']['success']++;
					}
				}
				$i++;
			}

			$response['end_time'] = date( 'Y-m-d H:i:s' );

			return $response;
		}

	/*
	 |--------------------------------------------------------------------------
	 | Generate - Token
	 |--------------------------------------------------------------------------
	 | Untuk generate token setiap 6 hari.
	 */
		public function generate_token() {
			// Looping sampai berhasil login (tidak timeout)
			for ( $i = 1; $i <= 1000; $i++ ) {
				$login = self::login();
				if ( $login['status'] == true ) {
					Storage::disk( 'local' )->put( 'files/access_token_mobile_inspection.txt', $login['data']['ACCESS_TOKEN'] );
					break;
				}
			}
		}

	/*
	 |--------------------------------------------------------------------------
	 | Login
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function login() {
			$client = new \GuzzleHttp\Client();
			$login = $client->request( 'POST', $this->url_api_ins_msa_auth.'/api/login', [
				'json' => [
					'username' => $this->auth['username'],
					'password' => $this->auth['password'],
					'imei' => $this->auth['imei'],
				]
			]);
			$login = json_decode( $login->getBody(), true );

			return $login;
		}

	/*
	 |--------------------------------------------------------------------------
	 | Search - Afdeling
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function search_afd( Request $req ) {

			$data['total_count'] = 0;
			$data['items'] = array();
			$data['incomplete_results'] = false;

			if ( isset( $_GET['q'] ) ) :
				$url = $this->url_api_ins_msa_hectarestatement.'/afdeling/q?WERKS='.$_GET['q'];
				$client = APISetup::ins_rest_client( 'GET', $url );
				$i = 0;
				if ( $client['status'] == true ) {
					$data['total_count'] = count( $client['data'] );
					if ( count( $client['data'] ) > 0 ) {
						$data['total_count'] = count( $client['data'] );
						foreach ( $client['data'] as $c ) {
							$data['items'][$i]['id'] = $c['WERKS_AFD_CODE'];
							$data['items'][$i]['text'] = $c['AFD_NAME'];
							$data['items'][$i]['description'] = $c['AFD_NAME'];
							$i++;
						}
					}
				}
			endif;

			return response()->json( $data );
		}

	/*
	 |--------------------------------------------------------------------------
	 | Search - Block
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function search_block( Request $req ) {

			$data['total_count'] = 0;
			$data['items'] = array();
			$data['incomplete_results'] = false;

			if ( isset( $_GET['q'] ) ) :
				$url = $this->url_api_ins_msa_hectarestatement.'/block/q?WERKS_AFD_CODE='.$_GET['q'];
				$client = APISetup::ins_rest_client( 'GET', $url );
				$i = 0;
				if ( $client['status'] == true ) {
					$data['total_count'] = count( $client['data'] );
					if ( count( $client['data'] ) > 0 ) {
						$data['total_count'] = count( $client['data'] );
						foreach ( $client['data'] as $c ) {
							$data['items'][$i]['id'] = $c['WERKS_AFD_BLOCK_CODE'];
							$data['items'][$i]['text'] = $c['BLOCK_NAME'];
							$data['items'][$i]['description'] = $c['BLOCK_NAME'];
							$i++;
						}
					}
				}
			endif;

			return response()->json( $data );
		}

	/*
	 |--------------------------------------------------------------------------
	 | Search - Estate
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function search_est( Request $req ) {

			$data['total_count'] = 0;
			$data['items'] = array();
			$data['incomplete_results'] = false;

			if ( isset( $_GET['q'] ) ) :
				$url = $this->url_api_ins_msa_hectarestatement.'/est/q?COMP_CODE='.$_GET['q'];
				$client = APISetup::ins_rest_client( 'GET', $url );
				$i = 0;
				if ( $client['status'] == true ) {
					$data['total_count'] = count( $client['data'] );
					if ( count( $client['data'] ) > 0 ) {
						$data['total_count'] = count( $client['data'] );
						foreach ( $client['data'] as $c ) {
							$data['items'][$i]['id'] = $c['WERKS'];
							$data['items'][$i]['text'] = $c['EST_NAME'];
							$data['items'][$i]['description'] = $c['EST_NAME'];
							$i++;
						}
					}
				}
			endif;

			return response()->json( $data );
		}

	/*
	 |--------------------------------------------------------------------------
	 | Search - Company
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function search_comp( Request $req ) {
			$data['total_count'] = 0;
			$data['items'] = array();
			$data['incomplete_results'] = false;
			if ( isset( $_GET['q'] ) ) :
				$url = $this->url_api_ins_msa_hectarestatement.'/comp/q?REGION_CODE='.$_GET['q'];
				$client = APISetup::ins_rest_client( 'GET', $url );
				$i = 0;
				if ( $client['status'] == true ) {
					$data['total_count'] = count( $client['data'] );
					if ( count( $client['data'] ) > 0 ) {
						$data['total_count'] = count( $client['data'] );
						foreach ( $client['data'] as $c ) {
							$data['items'][$i]['id'] = $c['COMP_CODE'];
							$data['items'][$i]['text'] = $c['COMP_NAME'];
							$data['items'][$i]['description'] = $c['COMP_NAME'];
							$i++;
						}
					}
				}
			endif;

			return response()->json( $data );
		}

	/*
	 |--------------------------------------------------------------------------
	 | Search - Region
	 |--------------------------------------------------------------------------
	 | ...
	 */
		public function search_region( Request $req ) {
			$data['total_count'] = 0;
			$data['items'] = array();
			$data['incomplete_results'] = false;
			$url = $this->url_api_ins_msa_hectarestatement.'/region/all';

			$client = APISetup::ins_rest_client( 'GET', $url );
			$i = 0;

			if ( $client['status'] == true ) {
				if ( count( $client['data'] ) > 0 ) {
					$data['total_count'] = count( $client['data'] );
					foreach ( $client['data'] as $c ) {
						$data['items'][$i]['id'] = $c['REGION_CODE'];
						$data['items'][$i]['text'] = $c['REGION_NAME'];
						$data['items'][$i]['description'] = $c['REGION_NAME'];
						$i++;
					}
				}
			}

			return response()->json( $data );
		}





	public function xxx() {
	/*
	{
	    "_id" : ObjectId("5d036d4dcaaabf7aae210bcf"),
	    "WERKS" : "4421",
	    "AFD_CODE" : "C",
	    "BLOCK_CODE" : "073",
	    "CLASS_BLOCK" : "C",
	    "WERKS_AFD_BLOCK_CODE" : "4421C073",
	    "WERKS_AFD_CODE" : "4421C",
	    "DATE_TIME" : 201906,
	    "INSERT_TIME" : 20190614164757.0,
	    "__v" : 0
	}
	*/
		$data_block = Data::hectarestatement_block_find( '4421' );
		$new_data = array();
		$month = '06';
		$i = 0;
		$random_cb = array(
			"A",
			"B",
			"C",
			"F"
		);
		foreach ( $data_block as $block ) {
			$rndm = rand( 0, 3 );
			$class_block = $random_cb[$rndm];
			$new_data[$i]['WERKS'] = $block['WERKS'];
			$new_data[$i]['AFD_CODE'] = $block['AFD_CODE'];
			$new_data[$i]['BLOCK_CODE'] = $block['BLOCK_CODE'];
			$new_data[$i]['CLASS_BLOCK'] = $class_block;
			$new_data[$i]['WERKS_AFD_BLOCK_CODE'] = $block['WERKS'].$block['AFD_CODE'].$block['BLOCK_CODE'];
			$new_data[$i]['WERKS_AFD_CODE'] = $block['WERKS'].$block['AFD_CODE'];
			$new_data[$i]['DATE_TIME'] = intval( '2019'.$month );
			$new_data[$i]['INSERT_TIME'] = 20190601000000;
			$i++;
		}

		print json_encode($new_data);

	}





}