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
	protected $active_menu;

	public function __construct() {
		$this->active_menu = '_'.str_replace( '.', '', '02.03.00.00.00' ).'_';
		$this->url_api_ins_msa_auth = APISetup::url()['msa']['ins']['auth'];
		$this->url_api_ins_msa_hectarestatement = APISetup::url()['msa']['ins']['hectarestatement'];
		$this->url_api_ins_msa_report = APISetup::url()['msa']['ins']['report'];
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

		$i = 0;
		foreach ( $ebcc_validation['data'] as $ebcc ) {
			$results['data'][$i]['EBCC_VALIDATION_CODE'] = $ebcc['EBCC_VALIDATION_CODE'];
			$results['data'][$i]['WERKS_AFD_CODE'] = $ebcc['WERKS_AFD_CODE'];
			$results['data'][$i]['WERKS_AFD_BLOCK_CODE'] = $ebcc['WERKS_AFD_BLOCK_CODE'];
			$results['data'][$i]['WERKS'] = $ebcc['WERKS'];
			$results['data'][$i]['EST_NAME'] = '';
			$results['data'][$i]['AFD_CODE'] = $ebcc['AFD_CODE'];
			$results['data'][$i]['BLOCK_CODE'] = $ebcc['BLOCK_CODE'];
			$results['data'][$i]['BLOCK_NAME'] = '';
			$results['data'][$i]['MATURITY_STATUS'] = '';
			$results['data'][$i]['NO_TPH'] = $ebcc['NO_TPH'];
			$results['data'][$i]['STATUS_TPH_SCAN'] = $ebcc['STATUS_TPH_SCAN'];
			$results['data'][$i]['ALASAN_MANUAL'] = $ebcc['ALASAN_MANUAL'];
			$results['data'][$i]['TANGGAL_VALIDASI'] = date( 'd M Y', strtotime( $ebcc['INSERT_TIME'] ) );
			$results['data'][$i]['LAT_TPH'] = $ebcc['LAT_TPH'];
			$results['data'][$i]['LON_TPH'] = $ebcc['LON_TPH'];
			$results['data'][$i]['DELIVERY_CODE'] = $ebcc['DELIVERY_CODE'];
			$results['data'][$i]['STATUS_DELIVERY_CODE'] = $ebcc['STATUS_DELIVERY_CODE'];
			$results['data'][$i]['NIK_VALIDATOR'] = '';
			$results['data'][$i]['NAMA_VALIDATOR'] = '';
			$results['data'][$i]['JABATAN_VALIDATOR'] = '';

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

			$hectarestatement =  Data::web_report_land_use_findone( $ebcc['WERKS_AFD_BLOCK_CODE'] );
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

		print '<pre>';
		print_r( $results['data'] );
		print '</pre>';
		dd();
		
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

		$inspection_baris = Data::web_report_inspection_baris_find( '/'.$parameter.'/'.$data['START_DATE'].'/'.$data['END_DATE'] )['items'];

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
			$hasil = Data::web_report_inspection_kriteria_findone( $inspection_class_block[$k]['NILAI_INSPEKSI'] );
			$inspection_class_block[$k]['HASIL_INSPEKSI'] = $hasil;
		}

		sort( $inspection_class_block );

		$client = new \GuzzleHttp\Client();
		foreach ( $inspection_class_block as $__block ) {
			$res = $client->request( 'POST', $this->url_api_ins_msa_report.'/api/report/class-block', [
				"headers" => [
					"Authorization" => 'Bearer '.session( 'ACCESS_TOKEN' ),
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
		$periode_min_1 = date( 'Ym', strtotime( $periode." - 1 month" ) );
		$periode_min_2 = date( 'Ym', strtotime( $periode." - 2 month" ) );
		$periode_min_3 = date( 'Ym', strtotime( $periode." - 3 month" ) );
		$periode_min_4 = date( 'Ym', strtotime( $periode." - 4 month" ) );
		$periode_min_5 = date( 'Ym', strtotime( $periode." - 5 month" ) );
		$periode_min_6 = date( 'Ym', strtotime( $periode." - 6 month" ) );

		if ( isset( $data['BA_CODE'] ) ) {
			$data_class_block = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode )['items'];
			$data_class_block_min_1 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_1 )['items'];
			$data_class_block_min_2 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_2 )['items'];
			$data_class_block_min_3 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_3 )['items'];
			$data_class_block_min_4 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_4 )['items'];
			$data_class_block_min_5 = Data::web_report_class_block_find( '/'.$data['BA_CODE'].'/'.$periode_min_4 )['items'];
			$data_all_block = Data::hectarestatement_block_find( $data['BA_CODE'] );
			$kriteria_find = Data::web_report_inspection_kriteria_find();
			$kriteria = [];



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

			print '<pre>';
			print_r( $class_block_01 );
			print_r( $class_block_02 );
			print_r( $class_block_03 );
			print_r( $class_block_04 );
			print '</pre>';
			dd();

			foreach ( $data_all_block as $ablock ) {

				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['WERKS'] = $ablock['WERKS'];
				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['AFD_CODE'] = $ablock['AFD_CODE'];
				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['BLOCK_CODE'] = $ablock['BLOCK_CODE'];
				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['BLOCK_NAME'] = $ablock['BLOCK_NAME'];
				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['WERKS_AFD_BLOCK_CODE'] = $ablock['WERKS_AFD_BLOCK_CODE'];

				$class_01 = '';
				$kriteria_angka_01 = '';
				if ( isset( $class_block_01[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_01 = $class_block_01[$ablock['WERKS_AFD_BLOCK_CODE']];
					$kriteria_angka_01 = $kriteria[$class_01];
				}

				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_01'] = $class_01;
				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['ANGKA_01'] = $kriteria_angka_01;

				$class_02 = '';
				if ( isset( $class_block_02[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_02 = $class_block_02[$ablock['WERKS_AFD_BLOCK_CODE']];
				}

				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_02'] = $class_02;

				$class_03 = '';
				if ( isset( $class_block_03[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_03 = $class_block_03[$ablock['WERKS_AFD_BLOCK_CODE']];
				}

				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_03'] = $class_03;

				$class_04 = '';
				if ( isset( $class_block_04[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_04 = $class_block_04[$ablock['WERKS_AFD_BLOCK_CODE']];
				}

				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_04'] = $class_04;

				$class_05 = '';
				if ( isset( $class_block_05[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_05 = $class_block_05[$ablock['WERKS_AFD_BLOCK_CODE']];
				}

				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_05'] = $class_05;

				$class_06 = '';
				if ( isset( $class_block_06[$ablock['WERKS_AFD_BLOCK_CODE']] ) ) {
					$class_06 = $class_block_06[$ablock['WERKS_AFD_BLOCK_CODE']];
				}

				$report_data[$ablock['WERKS_AFD_BLOCK_CODE']]['NILAI_06'] = $class_06;
			}

			$results['report_data'] = $report_data;
			$results['periode'] = date( 'Ym', strtotime( $periode ) );

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
			$region_data = APISetup::ins_rest_client( 'GET', $url );

			foreach ( $region_data['data'] as $data ) {
				$parameter['REGION_CODE'] = ( String ) $data['REGION_CODE'];
				$parameter['START_DATE'] = date( 'Ym01' );
				$parameter['END_DATE'] = date( 'Ymt' );
				self::generate_inspeksi( $parameter );
			}
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
			
			$inspection_baris = Data::web_report_inspection_baris_find( '/'.$parameter.'/'.$data['START_DATE'].'/'.$data['END_DATE'] )['items'];
			$inspection_header = array();
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
					$hasil = Data::web_report_inspection_kriteria_findone( $inspection_header[$k]['NILAI_INSPEKSI'] );
					$inspection_header[$k]['HASIL_INSPEKSI'] = $hasil;
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

			$query_inspeksi['REGION_CODE'] = ( isset( $data['REGION_CODE'] ) ? $data['REGION_CODE'] : "" );
			$query_inspeksi['COMP_CODE'] = ( isset( $data['COMP_CODE'] ) ? $data['COMP_CODE'] : "" );
			$query_inspeksi['WERKS'] = ( isset( $data['BA_CODE'] ) ? $data['BA_CODE'] : "" );
			$query_inspeksi['AFD_CODE'] = ( isset( $data['AFD_CODE'] ) ? $data['AFD_CODE'] : "" );
			$query_inspeksi['BLOCK_CODE'] = ( isset( $data['BLOCK_CODE'] ) ? $data['BLOCK_CODE'] : "" );
			$query_inspeksi['START_DATE'] = $data['START_DATE'].'000000';
			$query_inspeksi['END_DATE'] = $data['END_DATE'].'235959';

			$content = Data::web_report_inspection_content_find();
			$content_perawatan = array();
			$content_perawatan_bobot = array();
			$content_pemupukan = array();
			$content_panen = array();
			$inspection_header = array();
			$inspection_detail = Data::web_report_inspection_find( $query_inspeksi )['items'];
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
					$hectarestatement =  Data::web_report_land_use_findone( $ins_detail['WERKS'].$ins_detail['AFD_CODE'].$ins_detail['BLOCK_CODE'] );
					
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

					$client = new \GuzzleHttp\Client();
					$res = $client->request( 'POST', $this->url_api_ins_msa_report.'/api/report/inspection-baris', [
						"headers" => [
							#"Authorization" => 'Bearer '.session( 'ACCESS_TOKEN' ),
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
					]);
					
					if ( json_decode( $res->getBody(), true )['status'] == false ) {
						$status = false;
						print $ins_detail['BLOCK_INSPECTION_CODE'].' - Gagal.<br />';
					}
					else {
						print $ins_detail['BLOCK_INSPECTION_CODE'].' - OK.<br />';
					}
				}
				
				$i++;
			}
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

}