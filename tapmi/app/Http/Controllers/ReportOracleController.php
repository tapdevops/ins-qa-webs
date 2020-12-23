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

class ReportOracleController extends Controller {
	protected $url_api_ins_msa_hectarestatement;
	protected $url_api_ins_msa_auth;
	protected $active_menu;
	
	public function __construct() {
		$this->active_menu = '_' . str_replace('.', '', '02.03.00.00.00') . '_';
		$this->url_api_ins_msa_hectarestatement = APISetup::url()['msa']['ins']['hectarestatement'];
		$this->url_api_ins_msa_auth = APISetup::url()['msa']['ins']['auth'];
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
							'Accept' => 'application/json',
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

	public function kafka_control() {
		$data['active_menu'] = $this->active_menu;
		$data['table_data'] = $this->db_mobile_ins->select( "
			SELECT
				TD.*,
				KP.EXECUTE_DATE
			FROM
				(
					SELECT 
						COUNT( 1 ) AS ROW_NUM, 
						'TR_FINDING' AS TABLE_NAME, 
						'MSA_FINDING' AS GROUP_MSA,
						'INS_MSA_FINDING_TR_FINDING' AS TOPIC_NAME
					FROM 
						TR_FINDING
					UNION
					SELECT 
						COUNT( 1 ) AS ROW_NUM, 
						'TR_EBCC_VALIDATION_H' AS TABLE_NAME, 
						'EBCC_VALIDATION' AS GROUP_MSA,
						'INS_MSA_EBCCVAL_TR_EBCC_VALIDATION_H' AS TOPIC_NAME
					FROM 
						TR_EBCC_VALIDATION_H
					UNION
					SELECT 
						COUNT( 1 ) AS ROW_NUM, 
						'TR_EBCC_VALIDATION_D' AS TABLE_NAME, 
						'EBCC_VALIDATION' AS GROUP_MSA,
						'INS_MSA_EBCCVAL_TR_EBCC_VALIDATION_D' AS TOPIC_NAME
					FROM 
						TR_EBCC_VALIDATION_D
					UNION
					SELECT 
						COUNT( 1 ) AS ROW_NUM, 
						'TR_INSPECTION_GENBA' AS TABLE_NAME, 
						'MSA_INSPECTION' AS GROUP_MSA,
						'INS_MSA_INS_TR_INSPECTION_GENBA' AS TOPIC_NAME
					FROM 
						 TR_INSPECTION_GENBA
					UNION
					SELECT 
						COUNT( 1 ) AS ROW_NUM, 
						'TR_TRACK_INSPECTION' AS TABLE_NAME, 
						'MSA_INSPECTION' AS GROUP_MSA,
						'INS_MSA_INS_TR_TRACK_INSPECTION' AS TOPIC_NAME
					FROM 
						TR_TRACK_INSPECTION
					UNION
					SELECT 
						COUNT( 1 ) AS ROW_NUM, 
						'TM_USER_AUTH' AS TABLE_NAME, 
						'MSA_AUTH' AS GROUP_MSA,
						'INS_MSA_AUTH_TM_USER_AUTH' AS TOPIC_NAME
					FROM
						TM_USER_AUTH
					UNION
					SELECT 
						COUNT( 1 ) AS ROW_NUM, 
						'TR_BLOCK_INSPECTION_H' AS TABLE_NAME, 
						'MSA_INSPECTION' AS GROUP_MSA,
						'INS_MSA_INS_TR_BLOCK_INSPECTION_H' AS TOPIC_NAME
					FROM 
						TR_BLOCK_INSPECTION_H
					UNION
					SELECT 
						COUNT( 1 ) AS ROW_NUM, 
						'TR_BLOCK_INSPECTION_D' AS TABLE_NAME, 
						'MSA_INSPECTION' AS GROUP_MSA,
						'INS_MSA_INS_TR_BLOCK_INSPECTION_D' AS TOPIC_NAME
					FROM 
						TR_BLOCK_INSPECTION_D
				) TD
				INNER JOIN TM_KAFKA_PAYLOADS KP
					ON KP.TOPIC_NAME = TD.TOPIC_NAME
			ORDER BY
				TD.GROUP_MSA ASC,
				TD.TABLE_NAME ASC
		" );

		return view( 'orareport.kafka_control', $data );
	}

	public function read_nohup() {
		$content = File::get( base_path( 'nohup.out' ) );
		print '<pre>';
		print_r( $content );
		print '</pre>';
	}
	
	public function download() {
		$url_region_data = $this->url_api_ins_msa_hectarestatement . '/region/all';
		$data['region_data'] = APISetup::ins_rest_client('GET', $url_region_data);
		$data['active_menu'] = $this->active_menu;
		return view('orareport.download', $data);
	}
	
	public function download_proses( Request $request ) {
		$RO = new ReportOracle;
		$REPORT_TYPE = $request->REPORT_TYPE != '' ? $request->REPORT_TYPE :  null;
		$START_DATE = $request->START_DATE != '' ? $request->START_DATE : null;
		$END_DATE = $request->END_DATE != '' ? $request->END_DATE : null;
		$REGION_CODE = $request->REGION_CODE != '' ? $request->REGION_CODE : null;
		$COMP_CODE = $request->COMP_CODE != '' ? $request->COMP_CODE : null;
		$BA_CODE = $request->BA_CODE != '' ? $request->BA_CODE : null;
		$BA_NAME = $this->db_mobile_ins->select("select est_name from tap_dw.tm_est@proddw_link where werks = '$BA_CODE'");
		$EST_NAME='';
		foreach ($BA_NAME as $ba) {
			$EST_NAME         = $ba->est_name;
		}
		
		$AFD_CODE = $request->AFD_CODE != '' ? $request->AFD_CODE : null;
		$BLOCK_CODE = $request->BLOCK_CODE != '' ? $request->BLOCK_CODE : null;
		$DATE_MONTH = $request->DATE_MONTH != '' ? $request->DATE_MONTH : null;
		
		$file_name = null;
		$file_name_date = date( 'd F Y', strtotime( $START_DATE ) ).' - '.date( 'd F Y', strtotime( $END_DATE ) );

		// Set Empty Array (Biar gak error)
		$results['head'] = array();
		$results['data'] = array();
		$results['summary'] = array();
		$results['periode'] = date( 'Ym', strtotime( $START_DATE ) );
		$results['ba_name'] = $EST_NAME;
		

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT EBCC VALIDATION ESTATE/MILL
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		if ( $REPORT_TYPE == 'EBCC_VALIDATION_ESTATE' || $REPORT_TYPE == 'EBCC_VALIDATION_MILL' ) {
			$results['head'] = $RO->EBCC_VALIDATION_ESTATE_HEAD();
			$results['data'] = $RO->EBCC_VALIDATION(
									$REPORT_TYPE, 
									$START_DATE, 
									$END_DATE, 
									$REGION_CODE, 
									$COMP_CODE, 
									$BA_CODE, 
									$AFD_CODE, 
									$BLOCK_CODE
								);
			$file_name = 'Report-Sampling-EBCC';
			$results['sheet_name'] = 'Sampling EBCC';
			$results['view'] = 'orareport.excel-ebcc-validation';
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT EBCC COMPARE ESTATE/MILL
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'EBCC_COMPARE_ESTATE' || $REPORT_TYPE == 'EBCC_COMPARE_MILL' ) {	

			$query_ebcc_compare = $RO->EBCC_COMPARE_OLD(
				$REPORT_TYPE, 
				$START_DATE, 
				$END_DATE, 
				$REGION_CODE, 
				$COMP_CODE, 
				$BA_CODE, 
				$AFD_CODE, 
				$BLOCK_CODE
			);
			$results['data'] = $query_ebcc_compare['data'];
			//$results['summary'] = $query_ebcc_compare['summary'];
			$file_name = 'Report-EBCC-Compare';
			$results['sheet_name'] = 'Sampling EBCC vs EBCC';
			
			/*//Untuk tampilakn di view
			$results['view'] = 'orareport.excel-ebcc-compare';
			return view( $results['view'], $results );
			*/
			
			Excel::create( $file_name, function( $excel ) use ( $results ) {
				$excel->sheet( 'Summary', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'orareport.excel-ebcc-compare-summary', $results );
				} );
				$excel->sheet( 'Summary - per Krani Buah', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'orareport.excel-ebcc-compare-summary-krani', $results );
				} );
				$excel->sheet( 'Compare', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'orareport.excel-ebcc-compare', $results );
				} );
			} )->export( 'xlsx' );
			
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT FINDING
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'FINDING' ) {
			$results['data'] = $RO->FINDING(
									$REPORT_TYPE, 
									$START_DATE, 
									$END_DATE, 
									$REGION_CODE, 
									$COMP_CODE, 
									$BA_CODE, 
									$AFD_CODE, 
									$BLOCK_CODE
								);
			$results['data'] = json_decode( json_encode( $results['data'] ), true );
			$file_name = 'Report Finding - '.$file_name_date;
			$results['sheet_name'] = 'Finding';
			$results['view'] = 'orareport.excel-finding';
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT INSPECTION
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'INSPEKSI' ) {
			$data_baris = $RO->INSPECTION_BARIS(
							$REPORT_TYPE, 
							$START_DATE, 
							$END_DATE, 
							$REGION_CODE, 
							$COMP_CODE, 
							$BA_CODE, 
							$AFD_CODE, 
							$BLOCK_CODE
						);
			$results['data_baris'] = json_decode( json_encode( $data_baris ), true );
			$data_header = $RO->INSPECTION_HEADER(
							$REPORT_TYPE, 
							$START_DATE, 
							$END_DATE, 
							$REGION_CODE, 
							$COMP_CODE, 
							$BA_CODE, 
							$AFD_CODE, 
							$BLOCK_CODE
						);
			$results['data_header'] = json_decode( json_encode( $data_header ), true );
			$file_name = 'Report Inspeksi - '.$file_name_date;

			Excel::create( $file_name, function( $excel ) use ( $results ) {
				$excel->sheet( 'Per Baris', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'orareport.excel-inspection-baris', $results );
				} );
				$excel->sheet( 'Per Inspeksi', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'orareport.excel-inspection-header', $results );
				} );
			} )->export( 'xlsx' );
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT INSPECTION GENBA
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'INSPEKSI_GENBA' ) {
			$data_baris = $RO->INSPECTION_GENBA_BARIS(
							$REPORT_TYPE, 
							$START_DATE, 
							$END_DATE, 
							$REGION_CODE, 
							$COMP_CODE, 
							$BA_CODE, 
							$AFD_CODE, 
							$BLOCK_CODE
						);
			$results['data_baris'] = json_decode( json_encode( $data_baris ), true );
			$data_header = $RO->INSPECTION_GENBA_HEADER(
							$REPORT_TYPE, 
							$START_DATE, 
							$END_DATE, 
							$REGION_CODE, 
							$COMP_CODE, 
							$BA_CODE, 
							$AFD_CODE, 
							$BLOCK_CODE
						);
			$results['data_header'] = json_decode( json_encode( $data_header ), true );
			$data_genba = $RO->INSPECTION_GENBA(
							$REPORT_TYPE, 
							$START_DATE, 
							$END_DATE, 
							$REGION_CODE, 
							$COMP_CODE, 
							$BA_CODE, 
							$AFD_CODE, 
							$BLOCK_CODE
						);
			$results['data_genba'] = json_decode( json_encode( $data_genba ), true );
			$file_name = 'Report Genba - '.$file_name_date;

			Excel::create( $file_name, function( $excel ) use ( $results ) {
				$excel->sheet( 'Per Baris', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'orareport.excel-inspection-baris', $results );
				} );
				$excel->sheet( 'Per Inspeksi', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'orareport.excel-inspection-header', $results );
				} );
				$excel->sheet( 'Peserta Genba', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'orareport.excel-inspection-genba', $results );
				} );
			} )->export( 'xlsx' );
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT INSPECTION CLASS BLOCK
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'CLASS_BLOCK_AFD_ESTATE' ) {
			$results['data'] = $RO->INSPECTION_CLASS_BLOCK(
									$REPORT_TYPE, 
									$START_DATE, 
									$END_DATE, 
									$REGION_CODE, 
									$COMP_CODE, 
									$BA_CODE, 
									$AFD_CODE, 
									$BLOCK_CODE,
									$DATE_MONTH
								);					
			$results['data'] = json_decode( json_encode( $results['data'] ), true );
			$results['date_month'] = $DATE_MONTH.'-01';
			$file_name = 'Report Class Block - '.$BA_CODE.' - '.date( 'M Y', strtotime( $request->DATE_MONTH.'-01' ) );
			$results['sheet_name'] = 'Class Block';
			$results['view'] = 'orareport.excel-inspection-class-block';

			// return view( 'orareport.excel-inspection-class-block', $results );
			// dd();
		}
		
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT POINT BULANAN
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'POINT_BULANAN' ) {
			$results['data_point'] = $RO->POINT_BULANAN(
									$REPORT_TYPE, 
									$START_DATE, 
									$END_DATE, 
									$REGION_CODE, 
									$COMP_CODE, 
									$BA_CODE, 
									$AFD_CODE, 
									$BLOCK_CODE,
									$DATE_MONTH
								);
			$results['data_point'] = json_decode( json_encode( $results['data_point'] ),true );					
			// $results['data_history'] = $RO->HISTORY_POINT_BULANAN(
									// $REPORT_TYPE, 
									// $START_DATE, 
									// $END_DATE, 
									// $REGION_CODE, 
									// $COMP_CODE, 
									// $BA_CODE, 
									// $AFD_CODE, 
									// $BLOCK_CODE,
									// $DATE_MONTH
								// );					
			//$results['data_history'] = json_decode( json_encode( $results['data_history'] ),true );
			
			$results['date_month'] = date( 'M Y',strtotime( $DATE_MONTH.'-01' ) );
			$file_name = 'Report Point Bulanan - '.$results['date_month'];
			// $results['sheet_name'] = 'Point Bulanan';
			// $results['view'] = 'orareport.excel-point-bulanan';
			Excel::create( $file_name, function( $excel ) use ( $results ) {
				// $excel->sheet( 'History Point', function( $sheet ) use ( $results ) {
					// $sheet->loadView( 'orareport.excel-point-bulanan-history', $results );
				// } );
				$excel->sheet( 'Point Bulanan', function( $sheet ) use ( $results ) {
					$sheet->loadView( 'orareport.excel-point-bulanan', $results );
				} );
			} )->export( 'xlsx' );
			
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT PENCAPAIAN INSPEKSI
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'PENCAPAIAN_INSPEKSI' ) {
			$results['data'] = $RO->PENCAPAIAN_INSPEKSI(
									$REPORT_TYPE, 
									$START_DATE, 
									$END_DATE, 
									$REGION_CODE, 
									$COMP_CODE, 
									$BA_CODE, 
									$AFD_CODE, 
									$BLOCK_CODE,
									$DATE_MONTH
								);					
			$results['data'] = json_decode( json_encode( $results['data'] ), true );
			$results['periode'] = date('d M Y',(strtotime ( '-7 day' , strtotime ( $START_DATE) ) ))." - ".date('d M Y',(strtotime ( '-1 day' , strtotime ( $START_DATE) ) ));
			$file_name = 'Laporan Pencapaian Inspeksi Lapangan - '.$BA_CODE.' - '.date( 'M Y', strtotime( $request->START_DATE ));
			$results['sheet_name'] = 'Achievement';
			$results['view'] = 'orareport.excel-pencapaian-inspeksi';

			// return view( 'orareport.excel-pencapaian-inspeksi', $results );
			// dd();
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT MONITORING UPLOAD EBCC
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'MONITORING_UPLOAD_EBCC' ) {
			$results['data'] = $RO->MONITORING_UPLOAD_EBCC(
									$START_DATE, 
									$REGION_CODE, 
									$COMP_CODE
								);	
			$region = $this->db_mobile_ins->select("SELECT REGION_NAME FROM tap_dw.tm_region@dwh_link WHERE REGION_CODE = '$REGION_CODE'");
			$region_name='ALL';
			foreach ($region as $reg) {
				$region_name = $reg->region_name;
			}
			$results['region'] = $region_name;
			$company = $this->db_mobile_ins->select("SELECT COMP_NAME FROM tap_dw.tm_comp@dwh_link WHERE COMP_CODE = '$REGION_CODE'");
			$company_name='ALL';
			foreach ($company as $reg) {
				$company_name = $reg->company_name;
			}
			$results['company'] = $company_name;
			$results['data'] = json_decode( json_encode( $results['data'] ), true );
			$results['date'] = date('d M Y',( strtotime ( $START_DATE)));
			$file_name = 'Monitoring Upload EBCC - '.date( 'd M Y', strtotime( $request->START_DATE ));
			$results['sheet_name'] = 'Monitoring Upload EBCC';
			$results['view'] = 'orareport.excel-monitoring-upload-ebcc';

			// return view( 'orareport.excel-pencapaian-inspeksi', $results );
			// dd();
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT MONITORING SYNC MI
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'MONITORING_SYNC_MI' ) {
			$results['data'] = $RO->MONITORING_SYNC_MI(
									$START_DATE, 
									$REGION_CODE, 
									$COMP_CODE
								);	
			$region = $this->db_mobile_ins->select("SELECT REGION_NAME FROM tap_dw.tm_region@dwh_link WHERE REGION_CODE = '$REGION_CODE'");
			$region_name='ALL';
			foreach ($region as $reg) {
				$region_name = $reg->region_name;
			}
			$results['region'] = $region_name;
			$company = $this->db_mobile_ins->select("SELECT COMP_NAME FROM tap_dw.tm_comp@dwh_link WHERE COMP_CODE = '$REGION_CODE'");
			$company_name='ALL';
			foreach ($company as $reg) {
				$company_name = $reg->company_name;
			}
			$results['company'] = $company_name;
			$results['data'] = json_decode( json_encode( $results['data'] ), true );
			$results['date'] = date('d M Y',( strtotime ( $START_DATE)));
			$file_name = 'Monitoring Sync MI - '.date( 'd M Y', strtotime( $request->START_DATE ));
			$results['sheet_name'] = 'Monitoring Sync MI';
			$results['view'] = 'orareport.excel-monitoring-sync-mi';

			// return view( 'orareport.excel-pencapaian-inspeksi', $results );
			// dd();
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT MONITORING CETAK LHM
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'MONITORING_CETAK_LHM' ) {
			$results['data'] = $RO->MONITORING_CETAK_LHM(
									$START_DATE, 
									$REGION_CODE, 
									$COMP_CODE
								);	
			$region = $this->db_mobile_ins->select("SELECT REGION_NAME FROM tap_dw.tm_region@dwh_link WHERE REGION_CODE = '$REGION_CODE'");
			$region_name='ALL';
			foreach ($region as $reg) {
				$region_name = $reg->region_name;
			}
			$results['region'] = $region_name;
			$company = $this->db_mobile_ins->select("SELECT COMP_NAME FROM tap_dw.tm_comp@dwh_link WHERE COMP_CODE = '$REGION_CODE'");
			$company_name='ALL';
			foreach ($company as $reg) {
				$company_name = $reg->company_name;
			}
			$results['company'] = $company_name;
			$results['data'] = json_decode( json_encode( $results['data'] ), true );
			$results['date'] = date('d M Y',( strtotime ( $START_DATE)));
			$file_name = 'Monitoring Cetak LHM - '.date( 'd M Y', strtotime( $request->START_DATE ));
			$results['sheet_name'] = 'Monitoring Cetak LHM';
			$results['view'] = 'orareport.excel-monitoring-cetak-lhm';

			// return view( 'orareport.excel-pencapaian-inspeksi', $results );
			// dd();
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT MONITORING VALIDASI DESKTOP
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'MONITORING_VALIDASI_DESKTOP' ) {
			$results['data'] = $RO->MONITORING_VALIDASI_DESKTOP(
									$START_DATE, 
									$REGION_CODE, 
									$COMP_CODE
								);	
			$region = $this->db_mobile_ins->select("SELECT REGION_NAME FROM tap_dw.tm_region@dwh_link WHERE REGION_CODE = '$REGION_CODE'");
			$region_name='ALL';
			foreach ($region as $reg) {
				$region_name = $reg->region_name;
			}
			$results['region'] = $region_name;
			$company = $this->db_mobile_ins->select("SELECT COMP_NAME FROM tap_dw.tm_comp@dwh_link WHERE COMP_CODE = '$REGION_CODE'");
			$company_name='ALL';
			foreach ($company as $reg) {
				$company_name = $reg->company_name;
			}
			$results['company'] = $company_name;
			$results['data'] = json_decode( json_encode( $results['data'] ), true );
			$results['date'] = date('d M Y',( strtotime ( $START_DATE)));
			$file_name = 'Monitoring Validasi Desktop - '.date( 'd M Y', strtotime( $request->START_DATE ));
			$results['sheet_name'] = 'Monitoring Validasi Desktop';
			$results['view'] = 'orareport.excel-monitoring-validasi-desktop';

			// return view( 'orareport.excel-pencapaian-inspeksi', $results );
			// dd();
		}

		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		# REPORT REGISTRASI THP
		# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		else if ( $REPORT_TYPE == 'REGISTRASI_THP' ) {
			$url_region_data = APISetup::ins_rest_client('GET', $this->url_api_ins_msa_hectarestatement . '/api/v2.2/tph' );
			$results['data'] = $url_region_data['data'];
			$results['date'] = date('d M Y');
			$file_name = 'Registrasi TPH - '.date( 'd M Y');
			$results['sheet_name'] = 'Registrasi TPH';
			$results['view'] = 'orareport.excel-registrasi-tph';

			// return view( 'orareport.excel-pencapaian-inspeksi', $results );
			// dd();
		}

		if( $file_name && $REPORT_TYPE != 'INSPEKSI' AND $REPORT_TYPE != 'EBCC_COMPARE_ESTATE' AND $REPORT_TYPE != 'EBCC_COMPARE_MILL') {
			
			Excel::create( $file_name, function( $excel ) use ( $results ) {
				$excel->sheet( $results['sheet_name'], function( $sheet ) use ( $results ) {
					$sheet->loadView( $results['view'], $results );
				} );
			} )->export( 'xlsx' );
			/*return view( $results['view'], $results );*/
		}
	}

	# View Page Report EBCC Compare
	# Untuk menampilkan view
	public function view_page_report_ebcc_compare( Request $req ) {
		$results = array();
		$results['data'] = ( new ReportOracle() )->EBCC_COMPARE_PREVIEW( $req->id );

		if ( !empty( $results['data'] ) ) {
			return view( 'orareport/preview-ebcc-compare', $results );
		}
		else {
			return 'Data not found.';
		}
	}

	# Download PDF Report EBCC Compare
	public function pdf_report_ebcc_compare( Request $req ) {
		// $results = array();
		// $results['data'] = ( new ReportOracle() )->EBCC_COMPARE_PREVIEW( $req->id );

		// if ( !empty( $results['data'] ) ) {
		// 	$pdf = PDF::loadView( 'orareport.pdf', $results )->setPaper( 'a4', 'landscape' );
		// 	return $pdf->download( 'invoice.pdf' );
		// }
		// else {
		// 	return 'Data not found.';
		// }
	}
	
	# View Page Report Finding
	# Untuk menampilkan view
	public function view_page_report_finding( Request $req ) {
		$results = array();
		$results['data'] = ( new ReportOracle() )->FINDING_PREVIEW( $req->id );

		if ( !empty( $results['data'] ) ) {
			return view( 'orareport/preview-finding', $results );
		}
		else {
			return 'Data not found.';
		}
	}
	
}
