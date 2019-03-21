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

	public function __construct() {
		$this->url_api_ins_msa_auth = APISetup::url()['msa']['ins']['auth'];
		$this->url_api_ins_msa_hectarestatement = APISetup::url()['msa']['ins']['hectarestatement'];
		$this->url_api_ins_msa_report = APISetup::url()['msa']['ins']['report'];
	}

	public function index() {
		return view( 'report.index' );
	}

	public function download() {
		$url_region_data = $this->url_api_ins_msa_hectarestatement.'/region/all';
		$data['region_data'] = APISetup::ins_rest_client( 'GET', $url_region_data );

		return view( 'report.download', $data );
	}

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
			self::download_excel_finding( $setup );
		}
		// Report Inspeksi
		else if ( Input::get( 'REPORT_TYPE' ) == 'INSPEKSI' ) {
			self::download_excel_inspeksi( $setup );
		}
		// Report Class Block
		else if ( Input::get( 'REPORT_TYPE' ) == 'CLASS_BLOCK_AFD_ESTATE' ) {
			$date = date( 'Ymd', strtotime( Input::get( 'DATE_MONTH' ) ) );
			$setup['START_DATE'] = date( 'Ym', strtotime( $date ) ).'00';
			$setup['END_DATE'] = date( 'Ymt', strtotime( $date ) );
			self::download_excel_class_block( $setup );
		}
	}
	
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
			#self::download_excel_finding( $setup );
			print 'FINDING';
		}
		// Report Inspeksi
		else if ( Input::get( 'REPORT_TYPE' ) == 'INSPEKSI' ) {
			self::download_excel_inspeksis( $setup );
		}
		// Report Class Block
		else if ( Input::get( 'REPORT_TYPE' ) == 'CLASS_BLOCK_AFD_ESTATE' ) {
			$date = date( 'Ymd', strtotime( Input::get( 'DATE_MONTH' ) ) );
			$setup['START_DATE'] = date( 'Ym', strtotime( $date ) ).'00';
			$setup['END_DATE'] = date( 'Ymt', strtotime( $date ) );
			self::generate_class_block( $setup );
		}
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
				$inspection_class_block[$header_id]['PERIODE'] = date( 'Y.m', strtotime( $baris['SPMON'] ) );
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
					"DATE_TIME" => str_replace( '.', '', $__block['PERIODE'] ),
					"INSERT_TIME" => date( 'YmdHis' ),
					"UPDATE_TIME" => 0,
					"DELETE_TIME" => 0
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
				$inspection_class_block[$header_id]['PERIODE'] = date( 'Y.m', strtotime( $baris['SPMON'] ) );
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

		
		$class = array();

		foreach ( $inspection_class_block as $block ) {
			if ( !isset( $class[$block['BA_CODE']][$block['AFD_CODE']] ) || empty( $class[$block['BA_CODE']][$block['AFD_CODE']] ) ):
				$class[$block['BA_CODE']]['BA_CODE'] = $block['BA_CODE'];
				$class[$block['BA_CODE']]['BA_NAME'] = $block['BA_NAME'];
				$class[$block['BA_CODE']]['HASIL_INSPEKSI'] = 0;
				$class[$block['BA_CODE']]['HASIL_INSPEKSI_AKHIR'] = 0;
				$class[$block['BA_CODE']]['NILAI_INSPEKSI'] = '';
				$class[$block['BA_CODE']]['DATA'] = array();
			endif;
		}

		unset( $block );
		foreach ( $inspection_class_block as $block ) {
			if ( !isset( $class[$block['BA_CODE']][$block['AFD_CODE']] ) || empty( $class[$block['BA_CODE']][$block['AFD_CODE']] ) ):
				$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['BA_CODE'] = $block['BA_CODE'];
				$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['BA_NAME'] = $block['BA_NAME'];
				$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['AFD_CODE'] = $block['AFD_CODE'];
				$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['AFD_NAME'] = $block['AFD_NAME'];
				$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['NILAI_INSPEKSI'] = 0;
				$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['HASIL_INSPEKSI'] = '';
				$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['HASIL_INSPEKSI_AKHIR'] = 0;
				$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'] = array();
			endif;
			
		}

		unset( $block );
		foreach ( $inspection_class_block as $block ) {
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'][$block['BLOCK_CODE']]['BA_CODE'] = $block['BA_CODE'];
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'][$block['BLOCK_CODE']]['BA_NAME'] = $block['BA_NAME'];
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'][$block['BLOCK_CODE']]['AFD_CODE'] = $block['AFD_CODE'];
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'][$block['BLOCK_CODE']]['AFD_NAME'] = $block['AFD_NAME'];
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'][$block['BLOCK_CODE']]['BLOCK_CODE'] = $block['BLOCK_CODE'];
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'][$block['BLOCK_CODE']]['BLOCK_NAME'] = $block['BLOCK_NAME'];
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'][$block['BLOCK_CODE']]['NILAI_INSPEKSI'] = $block['NILAI_INSPEKSI'];
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'][$block['BLOCK_CODE']]['HASIL_INSPEKSI'] = $block['HASIL_INSPEKSI']['GRADE'];
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['DATA'][$block['BLOCK_CODE']]['HASIL_INSPEKSI_AKHIR'] = $block['HASIL_INSPEKSI']['KONVERSI_ANGKA'];
		}

		unset( $block );
		foreach ( $inspection_class_block as $block ) {
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['NILAI_INSPEKSI'] += $block['HASIL_INSPEKSI']['KONVERSI_ANGKA'];
			$class[$block['BA_CODE']]['DATA'][$block['AFD_CODE']]['HASIL_INSPEKSI_AKHIR'] += $block['HASIL_INSPEKSI']['KONVERSI_ANGKA'];
		}

		unset( $block );
		#foreach ( $inspection_class_block as $block ) {
		#	$class[$block['BA_CODE']]['NILAI_INSPEKSI'] += $block['HASIL_INSPEKSI']['KONVERSI_ANGKA'];
		#}
		
		foreach ( $class as $_class ) {
			foreach ( $_class['DATA'] as $_afd ) {
				$_hasil_afd = $_afd['NILAI_INSPEKSI'] / count( $_afd['DATA'] );
				$_hasil_kriteria = Data::web_report_inspection_kriteria_findone( $_hasil_afd );
				$class[$_afd['BA_CODE']]['DATA'][$_afd['AFD_CODE']]['NILAI_INSPEKSI'] = $_hasil_afd;
				$class[$_afd['BA_CODE']]['DATA'][$_afd['AFD_CODE']]['HASIL_INSPEKSI'] = $_hasil_kriteria['GRADE'];
				$class[$_afd['BA_CODE']]['DATA'][$_afd['AFD_CODE']]['HASIL_INSPEKSI_AKHIR'] = $_hasil_kriteria['KONVERSI_ANGKA'];

				$class[$_afd['BA_CODE']]['NILAI_INSPEKSI'] += $_hasil_kriteria['KONVERSI_ANGKA'];
				
			}
		}

		foreach ( $class as $_ba ) {
			$_hasil_ba = $_ba['NILAI_INSPEKSI'] / count( $_ba['DATA'] );
			$_hasil_kriteria = Data::web_report_inspection_kriteria_findone( $_hasil_ba );
			$class[$_ba['BA_CODE']]['NILAI_INSPEKSI'] = $_hasil_ba;
			$class[$_ba['BA_CODE']]['HASIL_INSPEKSI'] = $_hasil_kriteria['GRADE'];
			$class[$_ba['BA_CODE']]['HASIL_INSPEKSI_AKHIR'] = $_hasil_kriteria['KONVERSI_ANGKA'];
		}

		sort( $inspection_class_block );
		$results['inspection_block'] = $inspection_class_block;
		$results['periode'] = date( 'Ym', strtotime( $data['START_DATE'] ) );
		$results['content'] = $content;
		$results['class_data'] = $class;

		#foreach ( $class as $_class ) {
		#	$_jml_inspeksi = count( $_class['DATA'] );
		#	print $_jml_inspeksi.'<br />';
		#	print '<pre>';
		#	#print_r( $_class );
		#	print '</pre>';
		#}
		print '<pre>';
		print_r( $inspection_class_block );
		print '</pre>';
		dd();
		Excel::create( 'Report-Class-Block', function( $excel ) use ( $results ) {
			$excel->sheet( 'Per Block', function( $sheet ) use ( $results ) {
				$sheet->loadView( 'report.excel-class-block', $results );
			} );
		} )->export( 'xls' );
	}

	/** ZONA BONGKAR PASANG -----------------------------------------------------------------END **/


































	public function download_excel_inspeksi( $data, $output = 'excel' ) {
		
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
							if ( isset( $cc[$content_code]['LABEL'][$value] ) ) {
								$inspection_header[$header_id]['DATA_JUMLAH_PANEN'][$content_code] += $value;
							}
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

		#sort( $inspection_header );
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
	}

	public function download_excel_finding( $data ) {

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
			$excel->sheet( 'Per Baris', function( $sheet ) use ( $data ) {
				$sheet->loadView( 'report.excel-finding', $data );
			} );
		} )->export( 'xls' );
	}

	public function download_excel_inspeksis( $data, $output = 'excel' ) {

		$query_finding['REGION_CODE'] = ( isset( $data['REGION_CODE'] ) ? $data['REGION_CODE'] : "" );
		$query_finding['COMP_CODE'] = ( isset( $data['COMP_CODE'] ) ? $data['COMP_CODE'] : "" );
		$query_finding['WERKS'] = ( isset( $data['BA_CODE'] ) ? $data['BA_CODE'] : "" );
		$query_finding['AFD_CODE'] = ( isset( $data['AFD_CODE'] ) ? $data['AFD_CODE'] : "" );
		$query_finding['BLOCK_CODE'] = ( isset( $data['BLOCK_CODE'] ) ? $data['BLOCK_CODE'] : "" );
		$query_finding['START_DATE'] = $data['START_DATE'].'000000';
		$query_finding['END_DATE'] = $data['END_DATE'].'595959';

		$content = Data::web_report_inspection_content_find();
		$content_perawatan = array();
		$content_perawatan_bobot = array();
		$content_pemupukan = array();
		$content_panen = array();
		$inspection_header = array();
		//$inspection_detail = Data::web_report_inspection_find( $query_finding )['items'];
		$inspection_detail = Data::web_report_inspection_find( $query_finding )['items'];
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

			if ( !empty( $ins_detail['DETAIL'] ) ) {
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
			}
			#print '<pre>';
			#print_r( $data['inspection_data'][$i]['CONTENT'] );
			#print '</pre>';

			
			$client = new \GuzzleHttp\Client();
			$res = $client->request( 'POST', 'http://149.129.250.199:3013/api/report/inspection-baris', [
				"headers" => [
					"Authorization" => 'Bearer '.session( 'ACCESS_TOKEN' ),
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

			
			$i++;
		}

	}

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

}