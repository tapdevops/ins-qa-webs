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











































	






























	/** ZONA BONGKAR PASANG -------------------------------------------------------------------- **/

	public function download_excel_class_block( $data, $output = 'excel' ) {

		$query_finding['REGION_CODE'] = ( isset( $data['REGION_CODE'] ) ? $data['REGION_CODE'] : "" );
		$query_finding['COMP_CODE'] = ( isset( $data['COMP_CODE'] ) ? $data['COMP_CODE'] : "" );
		$query_finding['WERKS'] = ( isset( $data['BA_CODE'] ) ? $data['BA_CODE'] : "" );
		$query_finding['START_DATE'] = $data['START_DATE'].'000000';
		$query_finding['END_DATE'] = $data['END_DATE'].'595959';
		$content = Data::web_report_inspection_content_find();
		$content_perawatan = array();
		$content_perawatan_bobot = array();
		$content_pemupukan = array();
		$content_panen = array();
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

		$ins_block_data = array();
		$ins_afd_data = array();
		$ins_werks_data = array();
		$insdata = array();
		$results = array();
		$n = 0;
		foreach ( $inspection_detail as $ins_detail ) {

			// Membuat konten
			foreach ( $content as $ct ) {
				$insdata['inspection_data'][$n]['CONTENT'][$ct['CONTENT_CODE']] = null;
				$insdata['inspection_data'][$n]['CONTENT_PANEN'][$ct['CONTENT_CODE']] = null;
				$insdata['inspection_data'][$n]['CONTENT_PERAWATAN_BOBOT_TEXT'][$ct['CONTENT_CODE']] = null;
				$insdata['inspection_data'][$n]['CONTENT_PERAWATAN'][$ct['CONTENT_CODE']] = null;
				$insdata['inspection_data'][$n]['CONTENT_PEMUPUKAN'][$ct['CONTENT_CODE']] = null;
			}

			$block_id = (String) $ins_detail['WERKS'].(String) $ins_detail['AFD_CODE'].(String) $ins_detail['BLOCK_CODE'];
			if ( !isset( $ins_block_data[$block_id] ) ) {
				$hectarestatement =  Data::web_report_land_use_findone( $ins_detail['WERKS'].$ins_detail['AFD_CODE'].$ins_detail['BLOCK_CODE'] );
				$ms = str_replace( ' ', '', $hectarestatement['MATURITY_STATUS'] );
				$ins_block_data[$block_id]['WERKS'] = $ins_detail['WERKS'];
				$ins_block_data[$block_id]['EST_NAME'] = $hectarestatement['EST_NAME'];
				$ins_block_data[$block_id]['AFD_CODE'] = $ins_detail['AFD_CODE'];
				$ins_block_data[$block_id]['AFD_NAME'] = $hectarestatement['AFD_NAME'];
				$ins_block_data[$block_id]['BLOCK_CODE'] = $ins_detail['BLOCK_CODE'];
				$ins_block_data[$block_id]['BLOCK_NAME'] = $hectarestatement['BLOCK_NAME'];
				$ins_block_data[$block_id]['MATURITY_STATUS'] = $ms;
				$ins_block_data[$block_id]['DATA_JUMLAH'] = array();
				$ins_block_data[$block_id]['DATA_RATA2'] = array();
				$ins_block_data[$block_id]['NILAI_INSPEKSI'] = 0; 
				$ins_block_data[$block_id]['HASIL_INSPEKSI'] = '';
				$ins_block_data[$block_id]['JUMLAH_DETAIL'] = 0;
			}

			// Count Inspeksi
			$count_inspection[$block_id][] = $ins_detail['BLOCK_INSPECTION_CODE'];
			
			if ( !empty( $ins_detail['DETAIL'] ) ) {

				foreach ( $ins_detail['DETAIL'] as $detail ) {
					// Content Code
					$content_code = $detail['CONTENT_INSPECTION_CODE'];

					// Isi ke konten
					$insdata['inspection_data'][$n]['CONTENT'][$content_code] = $detail['VALUE'];
					// Convert Konten Perawatan
					if ( $cc[$content_code]['CATEGORY'] == 'PERAWATAN' ) {
						$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
						$insdata['inspection_data'][$n]['CONTENT_PERAWATAN'][$content_code] = $perawatan_value;
					}
					// Convert Konten Panen
					else if ( $cc[$content_code]['CATEGORY'] == 'PANEN' ) {
						$insdata['inspection_data'][$n]['CONTENT_PANEN'][$content_code] = $detail['VALUE'];
					}
					// Convert Konten Pemupukan
					else if ( $cc[$content_code]['CATEGORY'] == 'PEMUPUKAN' ) {
						if ( isset( $cc[$content_code]['LABEL'][$detail['VALUE']] ) ) {
							$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
							$insdata['inspection_data'][$n]['CONTENT_PEMUPUKAN'][$content_code] = $perawatan_value;
						}
					}

					if ( isset( $ins_block_data[$block_id] ) ) {
						if ( !isset( $ins_block_data[$block_id]['DATA_JUMLAH_PANEN'][$content_code] ) ) {
							$ins_block_data[$block_id]['DATA_JUMLAH_PANEN'][$content_code] = null;
						}
						if ( !isset( $ins_block_data[$block_id]['DATA_JUMLAH_RAWAT'][$content_code] ) ) {
							$ins_block_data[$block_id]['DATA_JUMLAH_RAWAT'][$content_code] = null;
						}
						if ( !isset( $ins_block_data[$block_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] ) ) {
							$ins_block_data[$block_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] = null;
						}
						if ( !isset( $ins_block_data[$block_id]['DATA_JUMLAH_PERAWATAN'][$content_code] ) ) {
							$ins_block_data[$block_id]['DATA_JUMLAH_PERAWATAN'][$content_code] = null;
						}

						if ( $cc[$content_code]['CATEGORY'] == 'PERAWATAN' ) {
							$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
							$ins_block_data[$block_id]['DATA_JUMLAH_RAWAT'][$content_code] += $perawatan_value;
						}
						
						else if ( $cc[$content_code]['CATEGORY'] == 'PANEN' ) {
							$ins_block_data[$block_id]['DATA_JUMLAH_PANEN'][$content_code] += $detail['VALUE'];
						}

						else if ( $cc[$content_code]['CATEGORY'] == 'PEMUPUKAN' ) {
							if ( isset( $cc[$content_code]['LABEL'][$detail['VALUE']] ) ) {
								$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
								$ins_block_data[$block_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] += $perawatan_value;
							}
							else {
								$ins_block_data[$block_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] += 0;
							}
						}
					}

				}
			}
			$n++;
		}

		#print '<pre>';
		#print '<h1>COUNT</h1>';
		#print_r( $count_inspection );
		#print '</pre>';

		# Rata-rata pemupukan
		foreach( $ins_block_data as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_PEMUPUKAN'] as $x => $y ) {
				#print '<pre>';
				#print_r( $k.' -> '.$y );
				#print '</pre>';
				$ins_block_data[$k]['DATA_RATA2_PEMUPUKAN'][$x] = $y / count( $count_inspection[ ( String ) $k ] );
				#print $k.' ~ '.$x.' = '.(   $y / count( $count_inspection[ ( String ) $k ] )   ).'<br />';
			}
		}

		# Rata-rata
		foreach( $ins_block_data as $k => $v ) {
			print '<pre>';
			print '<h1>'.$k.'</h1>';
			
			foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
				#print '<pre>';
				#print_r( $y.' / '.count( $count_inspection[ ( String ) $k ] ) );
				#print '</pre>';
				if ( $y != null ) {
					$ins_block_data[$k]['DATA_RATA2'][$x] = $y / count( $count_inspection[ ( String ) $k ] );
					print 'OK<br />';
				}
				else {
					print 'Gak OK<br />';
				}
				//print $k.' ~ '.$x.' = '.(   $y / count( $count_inspection[ ( String ) $k ] )   ).'<br />';
			}
			print '</pre>';
		}

		# Data Bobot Rawat
		foreach( $ins_block_data as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
				if ( isset( $content_perawatan_bobot[$x] ) ) {
					$ins_block_data[$k]['DATA_BOBOT_RAWAT'][$x] = $content_perawatan_bobot[$x]['BOBOT'];
				}
			}
		}

		# RATA2 X BOBOT / JUMLAH_BOBOT
		foreach( $ins_block_data as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
				if ( $ins_block_data[$k]['MATURITY_STATUS'] == 'TBM0' ) {
					$ins_block_data[$k]['DATA_RATAXBOBOT'][$x] = ( $ins_block_data[$k]['DATA_RATA2'][$x] * $ins_block_data[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm0 ;
				}
				else if ( $ins_block_data[$k]['MATURITY_STATUS'] == 'TBM1' ) {
					$ins_block_data[$k]['DATA_RATAXBOBOT'][$x] = ( $ins_block_data[$k]['DATA_RATA2'][$x] * $ins_block_data[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm1 ;
				}
				else if ( $ins_block_data[$k]['MATURITY_STATUS'] == 'TBM2' ) {
					$ins_block_data[$k]['DATA_RATAXBOBOT'][$x] = ( $ins_block_data[$k]['DATA_RATA2'][$x] * $ins_block_data[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm2 ;
				}
				else if ( $ins_block_data[$k]['MATURITY_STATUS'] == 'TBM3' ) {
					$ins_block_data[$k]['DATA_RATAXBOBOT'][$x] = ( $ins_block_data[$k]['DATA_RATA2'][$x] * $ins_block_data[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_tbm3 ;
				}
				else {
					$ins_block_data[$k]['DATA_RATAXBOBOT'][$x] = ( $ins_block_data[$k]['DATA_RATA2'][$x] * $ins_block_data[$k]['DATA_BOBOT_RAWAT'][$x] ) / $_bobot_all ;
				}
			}
		}

		# NILAI INSPEKSI
		foreach( $ins_block_data as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
				$ins_block_data[$k]['NILAI_INSPEKSI'] += $ins_block_data[$k]['DATA_RATAXBOBOT'][$x];
			}
		}

		# HASIL INSPEKSI
		foreach( $ins_block_data as $k => $v ) {
			$hasil = Data::web_report_inspection_kriteria_findone( $ins_block_data[$k]['NILAI_INSPEKSI'] );
			$ins_block_data[$k]['HASIL_INSPEKSI'] = $hasil;
		}

		#print '<pre>';
		#print '<h1>BLOCK DATA</h1>';
		#print_r( $ins_block_data );
		#print '</pre>';
		dd();

		$results['inspeksi_block'] = $ins_block_data;
		$results['periode'] = date( 'F Y', strtotime( $data['START_DATE'] ) );

		
		Excel::create( 'Report-Class-Block', function( $excel ) use ( $results ) {
			$excel->sheet( 'Per Block', function( $sheet ) use ( $results ) {
				$sheet->loadView( 'report.excel-class-block', $results );
			} );
		} )->export( 'xls' );
	}

	/** ZONA BONGKAR PASANG -----------------------------------------------------------------END **/














#FTAC001005190224124856
#FTAC001005190224124616


























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
		$query_finding['END_DATE'] = $data['END_DATE'].'595959';
		
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

	public function download_excel_inspeksi( $data, $output = 'excel' ) {

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

		$i = 0;
		foreach ( $inspection_detail as $ins_detail ) {
			$date_inspeksi = substr( $ins_detail['INSPECTION_DATE'], 0, 8 );
			$hectarestatement =  Data::web_report_land_use_findone( $ins_detail['WERKS'].$ins_detail['AFD_CODE'].$ins_detail['BLOCK_CODE'] );
			$inspektor_data = Data::user_find_one( ( String ) $ins_detail['INSERT_USER'] )['items'];
			$baris_start_ins = date( 'Y-m-d H:i:s', strtotime( $ins_detail['START_INSPECTION'] ) );
			$baris_end_ins = date( 'Y-m-d H:i:s', strtotime( $ins_detail['END_INSPECTION'] ) );
			$baris_diff = ( new DateTime( $baris_start_ins ) )->diff( new DateTime( $baris_end_ins ) );

			// Data Inspeksi Detail
			$data['inspection_data'][$i]['BLOCK_INSPECTION_CODE'] = $ins_detail['BLOCK_INSPECTION_CODE'];
			$data['inspection_data'][$i]['WERKS'] = $ins_detail['WERKS'];
			$data['inspection_data'][$i]['AFD_CODE'] = $ins_detail['AFD_CODE'];
			$data['inspection_data'][$i]['BLOCK_CODE'] = $ins_detail['BLOCK_CODE'];
			$data['inspection_data'][$i]['INSPECTION_DATE'] = $date_inspeksi;
			$data['inspection_data'][$i]['AREAL'] = $ins_detail['AREAL'];
			$data['inspection_data'][$i]['LAT_START_INSPECTION'] = $ins_detail['LAT_START_INSPECTION'];
			$data['inspection_data'][$i]['LONG_START_INSPECTION'] = $ins_detail['LONG_START_INSPECTION'];
			$data['inspection_data'][$i]['CONTENT'] = array();
			$data['inspection_data'][$i]['LAMA_INSPEKSI'] = ( strlen( $baris_diff->i ) == 1 ? '0'.$baris_diff->i : $baris_diff->i ).':'.( strlen( $baris_diff->s ) == 1 ? '0'.$baris_diff->s : $baris_diff->s );

			// Hectare Statement
			$data['inspection_data'][$i]['AFD_NAME'] = '';
			$data['inspection_data'][$i]['EST_NAME'] = '';
			$data['inspection_data'][$i]['SPMON'] = '';
			$data['inspection_data'][$i]['MATURITY_STATUS'] = '';
			$data['inspection_data'][$i]['BLOCK_NAME'] = '';
			
			if ( !empty( $hectarestatement ) ) {
				$data['inspection_data'][$i]['AFD_NAME'] = $hectarestatement['AFD_NAME'];
				$data['inspection_data'][$i]['EST_NAME'] = $hectarestatement['EST_NAME'];
				$data['inspection_data'][$i]['SPMON'] = $hectarestatement['SPMON'];
				$data['inspection_data'][$i]['MATURITY_STATUS'] = $hectarestatement['MATURITY_STATUS'];
				$data['inspection_data'][$i]['BLOCK_NAME'] = $hectarestatement['BLOCK_NAME'];
			}

			// Data Inspektor
			$data['inspection_data'][$i]['INSPEKTOR']['FULLNAME'] = $inspektor_data['FULLNAME'];
			$data['inspection_data'][$i]['INSPEKTOR']['JOB'] = $inspektor_data['JOB'];
			$data['inspection_data'][$i]['INSPEKTOR']['REF_ROLE'] = $inspektor_data['REF_ROLE'];
			$data['inspection_data'][$i]['INSPEKTOR']['USER_ROLE'] = $inspektor_data['USER_ROLE'];
			$data['inspection_data'][$i]['INSPEKTOR']['USER_AUTH_CODE'] = $inspektor_data['USER_AUTH_CODE'];
			$data['inspection_data'][$i]['INSPEKTOR']['EMPLOYEE_NIK'] = $inspektor_data['EMPLOYEE_NIK'];

			// Membuat konten
			foreach ( $content as $ct ) {
				$data['inspection_data'][$i]['CONTENT'][$ct['CONTENT_CODE']] = 0;
				$data['inspection_data'][$i]['CONTENT_PANEN'][$ct['CONTENT_CODE']] = 0;
				$data['inspection_data'][$i]['CONTENT_PERAWATAN_BOBOT_TEXT'][$ct['CONTENT_CODE']] = 0;
				$data['inspection_data'][$i]['CONTENT_PERAWATAN'][$ct['CONTENT_CODE']] = 0;
				$data['inspection_data'][$i]['CONTENT_PEMUPUKAN'][$ct['CONTENT_CODE']] = 0;
			}

			// Header : Membuat array
			$header_id = $inspektor_data['EMPLOYEE_NIK'].$ins_detail['WERKS'].$ins_detail['AFD_CODE'].$ins_detail['BLOCK_CODE'].$ins_detail['INSPECTION_DATE'];
			$ms = str_replace( ' ', '', $hectarestatement['MATURITY_STATUS'] );
			$count_inspection[$header_id][] = $ins_detail['BLOCK_INSPECTION_CODE'];

			if ( !isset( $inspection_header[$header_id] ) ) {
				$inspection_header[$header_id] = array();
				$inspection_header[$header_id]['NIK_REPORTER'] = $inspektor_data['EMPLOYEE_NIK'];
				$inspection_header[$header_id]['NAMA_REPORTER'] = $inspektor_data['FULLNAME'];
				$inspection_header[$header_id]['JABATAN'] = $inspektor_data['JOB'];
				$inspection_header[$header_id]['BA_CODE'] = $ins_detail['WERKS'];
				$inspection_header[$header_id]['BA_NAME'] = $hectarestatement['EST_NAME'];
				$inspection_header[$header_id]['AFD_CODE'] = $ins_detail['AFD_CODE'];
				$inspection_header[$header_id]['AFD_NAME'] = $hectarestatement['AFD_NAME'];
				$inspection_header[$header_id]['BLOCK_CODE'] = $ins_detail['BLOCK_CODE'];
				$inspection_header[$header_id]['BLOCK_NAME'] = $hectarestatement['BLOCK_NAME'];
				$inspection_header[$header_id]['INSPECTION_DATE'] = $ins_detail['INSPECTION_DATE'];
				$inspection_header[$header_id]['MATURITY_STATUS'] =  str_replace( ' ', '', $hectarestatement['MATURITY_STATUS'] );
				$inspection_header[$header_id]['PERIODE'] = date( 'Y.m', strtotime( $hectarestatement['SPMON'] ) );
				$inspection_header[$header_id]['LAMA_INSPEKSI'] = 0;
				$inspection_header[$header_id]['DATA_JUMLAH'] = array();
				$inspection_header[$header_id]['DATA_RATA2'] = array();
				$inspection_header[$header_id]['NILAI_INSPEKSI'] = 0; 
				$inspection_header[$header_id]['HASIL_INSPEKSI'] = '';
				$inspection_header[$header_id]['JUMLAH_DETAIL'] = 0;
			}

			$inspection_header[$header_id]['LAMA_INSPEKSI'] += ( $baris_diff->i * 60 ) + $baris_diff->s;

			if ( !empty( $ins_detail['DETAIL'] ) ) {
				foreach ( $ins_detail['DETAIL'] as $detail ) {

					// Content Code
					$content_code = $detail['CONTENT_INSPECTION_CODE'];

					// Isi ke konten
					$data['inspection_data'][$i]['CONTENT'][$content_code] = $detail['VALUE'];
					// Convert Konten Perawatan
					if ( $cc[$content_code]['CATEGORY'] == 'PERAWATAN' ) {
						$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
						$data['inspection_data'][$i]['CONTENT_PERAWATAN'][$content_code] = $perawatan_value;
					}
					// Convert Konten Panen
					else if ( $cc[$content_code]['CATEGORY'] == 'PANEN' ) {
						$data['inspection_data'][$i]['CONTENT_PANEN'][$content_code] = $detail['VALUE'];
					}
					// Convert Konten Pemupukan
					else if ( $cc[$content_code]['CATEGORY'] == 'PEMUPUKAN' ) {
						if ( isset( $cc[$content_code]['LABEL'][$detail['VALUE']] ) ) {
							$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
							$data['inspection_data'][$i]['CONTENT_PEMUPUKAN'][$content_code] = $perawatan_value;
						}
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
							$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
							$inspection_header[$header_id]['DATA_JUMLAH_RAWAT'][$content_code] += $perawatan_value;

							# Perawatan dengan bobot > 0
							if ( $cc[$content_code]['BOBOT'] > 0 ) {
								#$nilai_text_bobot = 
								#$data['inspection_data'][$i]['CONTENT_PERAWATAN_BOBOT_TEXT'][$content_code] = ;
							}
						}
						else if ( $cc[$content_code]['CATEGORY'] == 'PANEN' ) {
							$inspection_header[$header_id]['DATA_JUMLAH_PANEN'][$content_code] += $detail['VALUE'];
						}
						else if ( $cc[$content_code]['CATEGORY'] == 'PEMUPUKAN' ) {
							if ( isset( $cc[$content_code]['LABEL'][$detail['VALUE']] ) ) {
								$perawatan_value = $cc[$content_code]['LABEL'][$detail['VALUE']];
								$inspection_header[$header_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] += $perawatan_value;
							}
							else {
								$inspection_header[$header_id]['DATA_JUMLAH_PEMUPUKAN'][$content_code] += 0;
							}
							
						}
					}
				}
			}

			$inspection_header[$header_id]['JUMLAH_DETAIL'] ++;

			$i++;
		}

		# Rata-rata pemupukan
		foreach( $inspection_header as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_PEMUPUKAN'] as $x => $y ) {
				$inspection_header[$k]['DATA_RATA2_PEMUPUKAN'][$x] = $y / count( $count_inspection[ ( String ) $k ] );
			}
		}

		# Rata-rata
		foreach( $inspection_header as $k => $v ) {
			foreach ( $v['DATA_JUMLAH_RAWAT'] as $x => $y ) {
				$inspection_header[$k]['DATA_RATA2'][$x] = $y / count( $count_inspection[ ( String ) $k ] );
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

		# RATA2 X BOBOT / JUMLAH_BOBOT
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

		$data['inspection_header'] = $inspection_header;
		$data['content'] = $content;
		$data['content_perawatan'] = $content_perawatan;
		$data['content_perawatan_bobot'] = $content_perawatan_bobot;
		$data['content_pemupukan'] = $content_pemupukan;
		$data['content_panen'] = $content_panen;
		$data['periode'] = date( 'F Y', strtotime( $data['START_DATE'] ) );

		if ( $output == 'excel' ) {
			Excel::create( 'Report-Inspeksi', function( $excel ) use ( $data ) {
				$excel->sheet( 'Per Baris', function( $sheet ) use ( $data ) {
					$sheet->loadView( 'report.excel-inspection-baris', $data );
				} );
				$excel->sheet( 'Per Inspeksi', function( $sheet ) use ( $data ) {
					$sheet->loadView( 'report.excel-inspection-header', $data );
				} );
			} )->export( 'xls' );
		} else if ( $output == 'inspection_header' ) {
			return $inspection_header;
		} else if ( $output == 'inspection_data' ) {
			return array(
				"periode" => $data['periode'],
				"inspection" => $data['inspection_data']
			);
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