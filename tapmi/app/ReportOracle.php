<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

set_time_limit(0);

class ReportOracle extends Model{
	
	protected $env;
	protected $db_mobile_ins;
	
	public function __construct() {
		$this->db_mobile_ins = DB::connection( 'mobile_ins' );
		$this->url_api_ins_msa_image = APISetup::url()['msa']['ins']['image'];
		$this->url_api_ins_msa_point = APISetup::url()['msa']['ins']['point'];
	}
	
	public function EBCC_VALIDATION_ESTATE_HEAD() {
		$get = $this->db_mobile_ins->select("
			SELECT
				KUALITAS.ID_KUALITAS,
				KUALITAS.NAMA_KUALITAS
			FROM
				TAP_DW.T_KUALITAS_PANEN@DWH_LINK KUALITAS
			WHERE
				KUALITAS.ACTIVE_STATUS = 'YES' and KUALITAS.ID_KUALITAS not in ( '14','11','12','13' )
			ORDER BY 
				KUALITAS.GROUP_KUALITAS ASC,
				KUALITAS.UOM ASC,
				KUALITAS.NAMA_KUALITAS ASC
		");
		return $get;
	}
	
	public function EBCC_VALIDATION( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE ) {

		if ( $REPORT_TYPE ) {
			if ( $REPORT_TYPE == 'EBCC_VALIDATION_ESTATE' ) {
				$REPORT_TYPE = 'V';
			}
			else {
				$REPORT_TYPE = 'M';
			}
		}	

		$where = "";
		$where .= ( $REGION_CODE != "" && $COMP_CODE == "" ) ? " and EST.REGION_CODE = '$REGION_CODE'  ": "";
		$where .= ( $COMP_CODE != "" && $BA_CODE == "" ) ? " and EST.COMP_CODE = '$COMP_CODE'  ": "";
		$where .= ( $BA_CODE != "" && $AFD_CODE == "" ) ? " and EBCC_HEADER.WERKS = '$BA_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE == "" ) ? " and EBCC_HEADER.WERKS||EBCC_HEADER.AFD_CODE = '$AFD_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE != "" ) ? " and EBCC_HEADER.WERKS||EBCC_HEADER.AFD_CODE||EBCC_HEADER.BLOCK_CODE = '$BLOCK_CODE'  ": "";
		
		$START_DATE = date( 'Y-m-d', strtotime( $START_DATE ) );
		$END_DATE = date( 'Y-m-d', strtotime( $END_DATE ) );

		# Note:
		# Untuk mengambil SPMON dari TAP_DW.TR_HS_LAND_USE, berdasarkan End Date minus 1 (satu) bulan, 
		# kecuali jika bulan Januari, maka akan diambil bulan Januarinya, bukan Desember.
		$sql = "
			  SELECT header.*,
					 NVL (detail.jml_1, 0) AS val_jml_1,
					 NVL (detail.jml_2, 0) AS val_jml_2,
					 NVL (detail.jml_3, 0) AS val_jml_3,
					 NVL (detail.jml_4, 0) AS val_jml_4,
					 NVL (detail.jml_5, 0) AS val_jml_5,
					 NVL (detail.jml_6, 0) AS val_jml_6,
					 NVL (detail.jml_7, 0) AS val_jml_7,
					 NVL (detail.jml_8, 0) AS val_jml_8,
					 NVL (detail.jml_9, 0) AS val_jml_9,
					 NVL (detail.jml_10, 0) AS val_jml_10,
					 NVL (detail.jml_11, 0) AS val_jml_11,
					 NVL (detail.jml_12, 0) AS val_jml_12,
					 NVL (detail.jml_13, 0) AS val_jml_13,
					 NVL (detail.jml_14, 0) AS val_jml_14,
					 NVL (detail.jml_15, 0) AS val_jml_15,
					 NVL (detail.jml_16, 0) AS val_jml_16,
					 (NVL (detail.jml_1, 0) + NVL (detail.jml_2, 0) + NVL (detail.jml_3, 0) + NVL (detail.jml_4, 0) + NVL (detail.jml_6, 0) + NVL (detail.jml_15, 0) + NVL (detail.jml_16, 0)) AS val_total_jjg,
					 CASE WHEN tph.werks IS NOT NULL THEN 'INACTIVE' ELSE 'ACTIVE' END status_tph
				FROM (  SELECT ebcc_val.val_ebcc_code,
							   ebcc_val.val_werks,
							   ebcc_val.val_est_name,
							   ebcc_val.val_nik_validator,
							   ebcc_val.val_nama_validator,
							   REPLACE (ebcc_val.val_jabatan_validator, '_', ' ') AS val_jabatan_validator,
							   ebcc_val.val_status_tph_scan,
							   ebcc_val.val_lat_tph,
							   ebcc_val.val_lon_tph,
							   ebcc_val.val_maturity_status,
							   CASE
								  WHEN ebcc_val.val_alasan_manual IS NULL
								  THEN
									 ''
								  ELSE
									 CASE
										WHEN ebcc_val.val_alasan_manual = '1' THEN 'QR Codenya Hilang'
										WHEN ebcc_val.val_alasan_manual = '2' THEN 'QR Codenya Rusak'
									 END
							   END
								  AS val_alasan_manual,
							   ebcc_val.val_afd_code,
							   ebcc_val.val_block_code,
							   ebcc_val.val_date_time AS val_date_time,
							   ebcc_val.val_block_name,
							   ebcc_val.val_tph_code,
							   ebcc_val.val_delivery_ticket
						  FROM (SELECT ebcc_header.ebcc_validation_code AS val_ebcc_code,
									   est.werks AS val_werks,
									   est.est_name AS val_est_name,
									   ebcc_header.afd_code AS val_afd_code,
									   ebcc_header.insert_time AS val_date_time,
									   ebcc_header.block_code AS val_block_code,
									   ebcc_header.status_tph_scan AS val_status_tph_scan,
									   ebcc_header.alasan_manual AS val_alasan_manual,
									   ebcc_header.no_tph AS val_tph_code,
									   ebcc_header.delivery_code AS val_delivery_ticket,
									   user_auth.employee_nik AS val_nik_validator,
									   emp.employee_fullname AS val_nama_validator,
									   user_auth.user_role AS val_jabatan_validator,
									   land_use.maturity_status AS val_maturity_status,
									   land_use.spmon AS val_spmon,
									   subblock.block_name AS val_block_name,
									   ebcc_header.lat_tph AS val_lat_tph,
									   ebcc_header.lat_tph AS val_lon_tph
								  FROM mobile_inspection.tr_ebcc_validation_h ebcc_header
									   LEFT JOIN mobile_inspection.tm_user_auth user_auth
										  ON user_auth.user_auth_code = (CASE WHEN LENGTH (ebcc_header.insert_user) = 3 THEN '0' || ebcc_header.insert_user ELSE ebcc_header.insert_user END)
									   LEFT JOIN (SELECT employee_nik,
														 employee_fullname,
														 employee_position,
														 employee_joindate AS start_date,
														 CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD') ELSE employee_resigndate END AS end_date
													FROM tap_dw.tm_employee_hris@dwh_link
												  UNION ALL
												  SELECT nik,
														 employee_name,
														 job_code,
														 start_valid,
														 CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid
													FROM tap_dw.tm_employee_sap@dwh_link) emp
										  ON emp.employee_nik = user_auth.employee_nik AND TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (emp.start_date) AND TRUNC (emp.end_date)
									   LEFT JOIN tap_dw.tm_est@dwh_link est
										  ON est.werks = ebcc_header.werks AND SYSDATE BETWEEN est.start_valid AND est.end_valid
									   LEFT JOIN (SELECT werks,
														 afd_code,
														 block_code,
														 block_name,
														 maturity_status,
														 spmon
													FROM tap_dw.tr_hs_land_use@dwh_link
												   WHERE 1 = 1 AND ROWNUM < 2 AND maturity_status IS NOT NULL
														 AND spmon BETWEEN (SELECT CASE
																					  WHEN TO_CHAR (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), 'MM') = '01' THEN TRUNC (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), 'MM')
																					  ELSE TRUNC (ADD_MONTHS (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), -1), 'MM')
																				   END
																			  FROM DUAL)
																	   AND  ( (SELECT CASE
																						 WHEN TO_CHAR (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), 'MM') = '01' THEN LAST_DAY (TO_DATE ('$END_DATE', 'RRRR-MM-DD'))
																						 ELSE LAST_DAY (ADD_MONTHS (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), -1))
																					  END
																				 FROM DUAL))) land_use
										  ON land_use.werks = ebcc_header.werks AND land_use.afd_code = ebcc_header.afd_code AND land_use.block_code = ebcc_header.block_code
									   LEFT JOIN tap_dw.tm_sub_block@dwh_link subblock
										  ON subblock.werks = ebcc_header.werks AND subblock.sub_block_code = ebcc_header.block_code
								 WHERE     1 = 1
									   AND SUBSTR (ebcc_header.ebcc_validation_code, 0, 1) = '$REPORT_TYPE'
									   AND TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (TO_DATE ('$START_DATE', 'RRRR-MM-DD')) AND TRUNC (TO_DATE ('$END_DATE', 'RRRR-MM-DD')) 
									   $where
																																												) ebcc_val
					  GROUP BY ebcc_val.val_ebcc_code,
							   ebcc_val.val_werks,
							   ebcc_val.val_est_name,
							   ebcc_val.val_nik_validator,
							   ebcc_val.val_nama_validator,
							   ebcc_val.val_jabatan_validator,
							   ebcc_val.val_status_tph_scan,
							   ebcc_val.val_alasan_manual,
							   ebcc_val.val_date_time,
							   ebcc_val.val_afd_code,
							   ebcc_val.val_block_code,
							   ebcc_val.val_block_name,
							   ebcc_val.val_tph_code,
							   ebcc_val.val_delivery_ticket,
							   ebcc_val.val_lat_tph,
							   ebcc_val.val_lon_tph,
							   ebcc_val.val_maturity_status,
							   ebcc_val.val_spmon) header
					 LEFT JOIN (SELECT *
								  FROM (  SELECT kualitas.id_kualitas AS idk, ebcc_detail.ebcc_validation_code, ebcc_detail.jumlah
											FROM tap_dw.t_kualitas_panen@dwh_link kualitas LEFT JOIN mobile_inspection.tr_ebcc_validation_d ebcc_detail
													ON ebcc_detail.id_kualitas = kualitas.id_kualitas
										   WHERE kualitas.active_status = 'YES'
										ORDER BY kualitas.group_kualitas ASC, kualitas.uom ASC, kualitas.nama_kualitas ASC) PIVOT (SUM (jumlah)
																															FOR idk
																															IN ('1' AS jml_1,
																															'2' AS jml_2,
																															'3' AS jml_3,
																															'4' AS jml_4,
																															'5' AS jml_5,
																															'6' AS jml_6,
																															'7' AS jml_7,
																															'8' AS jml_8,
																															'9' AS jml_9,
																															'10' AS jml_10,
																															'11' AS jml_11,
																															'12' AS jml_12,
																															'13' AS jml_13,
																															'14' AS jml_14,
																															'15' AS jml_15,
																															'16' AS jml_16))
								 WHERE ebcc_validation_code IS NOT NULL) detail
						ON header.val_ebcc_code = detail.ebcc_validation_code
					 LEFT JOIN (SELECT *
								  FROM ebcc.tm_status_tph
								 WHERE status = 'INACTIVE') tph
						ON     header.val_werks = tph.werks
						   AND header.val_afd_code = tph.afd_code
						   AND header.val_block_code = tph.block_code
						   AND header.val_tph_code = tph.no_tph
						   AND TRUNC (header.val_date_time) BETWEEN tph.start_valid AND nvl(tph.end_valid,sysdate)
			ORDER BY header.val_date_time DESC
		";
		$get = $this->db_mobile_ins->select( $sql );
		return $get;
	}

	public function EBCC_COMPARE_PREVIEW( $id ) {
		$sql = "
			SELECT
			HEADER.*,
			NVL( DETAIL.JML_1, 0 ) AS VAL_JML_1,
			NVL( DETAIL.JML_2, 0 ) AS VAL_JML_2,
			NVL( DETAIL.JML_3, 0 ) AS VAL_JML_3,
			NVL( DETAIL.JML_4, 0 ) AS VAL_JML_4,
			NVL( DETAIL.JML_5, 0 ) AS VAL_JML_5,
			NVL( DETAIL.JML_6, 0 ) AS VAL_JML_6,
			NVL( DETAIL.JML_7, 0 ) AS VAL_JML_7,
			NVL( DETAIL.JML_8, 0 ) AS VAL_JML_8,
			NVL( DETAIL.JML_9, 0 ) AS VAL_JML_9,
			NVL( DETAIL.JML_10, 0 ) AS VAL_JML_10,
			NVL( DETAIL.JML_11, 0 ) AS VAL_JML_11,
			NVL( DETAIL.JML_12, 0 ) AS VAL_JML_12,
			NVL( DETAIL.JML_13, 0 ) AS VAL_JML_13,
			NVL( DETAIL.JML_14, 0 ) AS VAL_JML_14,
			NVL( DETAIL.JML_15, 0 ) AS VAL_JML_15,
			NVL( DETAIL.JML_16, 0 ) AS VAL_JML_16,
			(
				NVL( DETAIL.JML_1, 0 ) + 
				NVL( DETAIL.JML_2, 0 ) + 
				NVL( DETAIL.JML_3, 0 ) + 
				NVL( DETAIL.JML_4, 0 ) + 
				NVL( DETAIL.JML_6, 0 ) +
				NVL( DETAIL.JML_15, 0 ) +
				NVL( DETAIL.JML_16, 0 )
			) AS VAL_TOTAL_JJG,
			F_GET_EBCC_COMPARE ( 
				HEADER.VAL_WERKS, 
				HEADER.VAL_AFD_CODE, 
				HEADER.VAL_BLOCK_CODE,
				TO_CHAR( HEADER.VAL_DATE_TIME, 'DD-MM-YYYY' ), 
				HEADER.VAL_TPH_CODE, 
				(
					NVL( DETAIL.JML_1, 0 ) + 
					NVL( DETAIL.JML_2, 0 ) + 
					NVL( DETAIL.JML_3, 0 ) + 
					NVL( DETAIL.JML_4, 0 ) + 
					NVL( DETAIL.JML_6, 0 ) +
					NVL( DETAIL.JML_15, 0 ) +
					NVL( DETAIL.JML_16, 0 )
				)
			) AS EBCC_NO_BCC
		FROM
			(
				SELECT 
					EBCC_VAL.VAL_EBCC_CODE,
					EBCC_VAL.VAL_WERKS,
					EBCC_VAL.VAL_EST_NAME,
					EBCC_VAL.VAL_NIK_VALIDATOR,
					EBCC_VAL.VAL_NAMA_VALIDATOR,
					EBCC_VAL.VAL_JABATAN_VALIDATOR,
					EBCC_VAL.VAL_STATUS_TPH_SCAN,
					EBCC_VAL.VAL_LAT_TPH,
					EBCC_VAL.VAL_LON_TPH,
					CASE
						WHEN EBCC_VAL.VAL_ALASAN_MANUAL IS NULL THEN ''
						ELSE
							CASE
								WHEN EBCC_VAL.VAL_ALASAN_MANUAL = '1' THEN 'QR Codenya Hilang'
								WHEN EBCC_VAL.VAL_ALASAN_MANUAL = '2' THEN 'QR Codenya Rusak'
						END
					END AS VAL_ALASAN_MANUAL,
					EBCC_VAL.VAL_AFD_CODE,
					EBCC_VAL.VAL_BLOCK_CODE,
					EBCC_VAL.VAL_DATE_TIME AS VAL_DATE_TIME,
					EBCC_VAL.VAL_BLOCK_NAME,
					EBCC_VAL.VAL_TPH_CODE,
					EBCC_VAL.VAL_DELIVERY_TICKET
				FROM
					(
						SELECT
							EBCC_HEADER.EBCC_VALIDATION_CODE AS VAL_EBCC_CODE,
							EST.WERKS AS VAL_WERKS,
							EST.EST_NAME AS VAL_EST_NAME,
							EBCC_HEADER.AFD_CODE AS VAL_AFD_CODE,
							EBCC_HEADER.INSERT_TIME AS VAL_DATE_TIME,
							EBCC_HEADER.BLOCK_CODE AS VAL_BLOCK_CODE,
							EBCC_HEADER.STATUS_TPH_SCAN AS VAL_STATUS_TPH_SCAN,
							EBCC_HEADER.ALASAN_MANUAL AS VAL_ALASAN_MANUAL,
							EBCC_HEADER.NO_TPH AS VAL_TPH_CODE,
							EBCC_HEADER.DELIVERY_CODE AS VAL_DELIVERY_TICKET,
							USER_AUTH.EMPLOYEE_NIK AS VAL_NIK_VALIDATOR,
							EMP.EMPLOYEE_FULLNAME AS VAL_NAMA_VALIDATOR,
							EMP.EMPLOYEE_POSITION AS VAL_JABATAN_VALIDATOR,
							SUBBLOCK.BLOCK_NAME AS VAL_BLOCK_NAME,
							EBCC_HEADER.LAT_TPH AS VAL_LAT_TPH,
							EBCC_HEADER.LAT_TPH AS VAL_LON_TPH
						FROM
							MOBILE_INSPECTION.TR_EBCC_VALIDATION_H EBCC_HEADER
							LEFT JOIN MOBILE_INSPECTION.TM_USER_AUTH USER_AUTH ON 
								USER_AUTH.USER_AUTH_CODE = (
									CASE
										WHEN LENGTH( EBCC_HEADER.INSERT_USER ) = 3 THEN '0' || EBCC_HEADER.INSERT_USER
										ELSE EBCC_HEADER.INSERT_USER
									END
								)
							LEFT JOIN (
								SELECT 
									EMPLOYEE_NIK,
									EMPLOYEE_FULLNAME,
									EMPLOYEE_POSITION,
									EMPLOYEE_JOINDATE as START_DATE,
									CASE 
										WHEN EMPLOYEE_RESIGNDATE IS NULL
										THEN TO_DATE( '99991231', 'RRRRMMDD' )
										ELSE EMPLOYEE_RESIGNDATE
									END as END_DATE
								FROM 
									TAP_DW.TM_EMPLOYEE_HRIS@DWH_LINK 
								UNION ALL
								SELECT 
									NIK, 
									EMPLOYEE_NAME,
									JOB_CODE,
									START_VALID,
									CASE
										WHEN RES_DATE IS NOT NULL 
										THEN RES_DATE
										ELSE END_VALID
									END END_VALID
								FROM 
									TAP_DW.TM_EMPLOYEE_SAP@DWH_LINK
							) EMP ON
								EMP.EMPLOYEE_NIK = USER_AUTH.EMPLOYEE_NIK
								AND TRUNC( EBCC_HEADER.INSERT_TIME ) BETWEEN TRUNC( EMP.START_DATE ) AND TRUNC( EMP.END_DATE )
							LEFT JOIN TAP_DW.TM_EST@DWH_LINK EST ON 
								EST.WERKS = EBCC_HEADER.WERKS 
								AND SYSDATE BETWEEN EST.START_VALID AND EST.END_VALID
							LEFT JOIN TAP_DW.TM_SUB_BLOCK@DWH_LINK SUBBLOCK
								ON SUBBLOCK.WERKS = EBCC_HEADER.WERKS
								AND SUBBLOCK.SUB_BLOCK_CODE = EBCC_HEADER.BLOCK_CODE
						WHERE
							1 = 1
							AND EBCC_HEADER.EBCC_VALIDATION_CODE = '{$id}'
							AND ROWNUM < 2
					) EBCC_VAL
				GROUP BY
					EBCC_VAL.VAL_EBCC_CODE,
					EBCC_VAL.VAL_WERKS,
					EBCC_VAL.VAL_EST_NAME,
					EBCC_VAL.VAL_NIK_VALIDATOR,
					EBCC_VAL.VAL_NAMA_VALIDATOR,
					EBCC_VAL.VAL_JABATAN_VALIDATOR,
					EBCC_VAL.VAL_STATUS_TPH_SCAN,
					EBCC_VAL.VAL_ALASAN_MANUAL,
					EBCC_VAL.VAL_DATE_TIME,
					EBCC_VAL.VAL_AFD_CODE,
					EBCC_VAL.VAL_BLOCK_CODE,
					EBCC_VAL.VAL_BLOCK_NAME,
					EBCC_VAL.VAL_TPH_CODE,
					EBCC_VAL.VAL_DELIVERY_TICKET,
					EBCC_VAL.VAL_LAT_TPH,
					EBCC_VAL.VAL_LON_TPH
			) HEADER
			LEFT JOIN (
				SELECT 
					* 
				FROM (
					SELECT
						KUALITAS.ID_KUALITAS AS IDK,
						EBCC_DETAIL.EBCC_VALIDATION_CODE,
						EBCC_DETAIL.JUMLAH
					FROM
						TAP_DW.T_KUALITAS_PANEN@DWH_LINK KUALITAS
						LEFT JOIN MOBILE_INSPECTION.TR_EBCC_VALIDATION_D EBCC_DETAIL ON EBCC_DETAIL.ID_KUALITAS = KUALITAS.ID_KUALITAS
					WHERE
						KUALITAS.ACTIVE_STATUS = 'YES'
					ORDER BY 
						KUALITAS.GROUP_KUALITAS ASC,
						KUALITAS.UOM ASC,
						KUALITAS.NAMA_KUALITAS ASC
				)
				PIVOT (
					SUM( JUMLAH )
					FOR IDK IN ( 
						'1' AS JML_1,
						'2' AS JML_2,
						'3' AS JML_3,
						'4' AS JML_4,
						'5' AS JML_5,
						'6' AS JML_6,
						'7' AS JML_7,
						'8' AS JML_8,
						'9' AS JML_9,
						'10' AS JML_10,
						'11' AS JML_11,
						'12' AS JML_12,
						'13' AS JML_13,
						'14' AS JML_14,
						'15' AS JML_15,
						'16' AS JML_16
					)
				)
				WHERE EBCC_VALIDATION_CODE IS NOT NULL
			) DETAIL
				ON HEADER.VAL_EBCC_CODE = DETAIL.EBCC_VALIDATION_CODE
		";
		$get = collect( $this->db_mobile_ins->select( $sql ) )->first();
		$joindata = array();
	
		if ( !empty( $get ) ) {
			$client = new \GuzzleHttp\Client();
			$image_selfie = $client->request( 'GET', $this->url_api_ins_msa_image.'/api/v2.0/foto-transaksi/'.$get->val_ebcc_code.'?status_image=SELFIE_V' );
			$image_selfie = json_decode( $image_selfie->getBody(), true );
			
			$image_janjang = $client->request( 'GET', $this->url_api_ins_msa_image.'/api/v2.0/foto-transaksi/'.$get->val_ebcc_code.'?status_image=JANJANG' );
			$image_janjang = json_decode( $image_janjang->getBody(), true );

			$joindata['val_image_selfie'] = ( isset( $image_selfie['data']['http'][0] ) ? $image_selfie['data']['http'][0] : url( 'assets/user.jpg' ) );
			$joindata['val_image_janjang'] = ( isset( $image_janjang['data']['http'][0] ) ? $image_janjang['data']['http'][0] : url( 'assets/dummy-janjang.jpg' ) );
			$joindata['ebcc_image_selfie'] = url( 'assets/user.jpg' );
			$joindata['ebcc_image_janjang'] = url( 'assets/dummy-janjang.jpg' );
			$joindata['val_ebcc_code'] = $get->val_ebcc_code;
			$joindata['val_werks'] = $get->val_werks;
			$joindata['val_est_name'] = $get->val_est_name;
			$joindata['val_nik_validator'] = $get->val_nik_validator;
			$joindata['val_nama_validator'] = $get->val_nama_validator;
			$joindata['val_jabatan_validator'] = $get->val_jabatan_validator;
			$joindata['val_status_tph_scan'] = $get->val_status_tph_scan;
			$joindata['val_alasan_manual'] = $get->val_alasan_manual;
			$joindata['val_afd_code'] = $get->val_afd_code;
			$joindata['val_block_code'] = $get->val_block_code;
			$joindata['val_date_time'] = $get->val_date_time;
			$joindata['val_block_name'] = $get->val_block_name;
			$joindata['val_tph_code'] = $get->val_tph_code;
			$joindata['val_delivery_ticket'] = $get->val_delivery_ticket;
			$joindata['val_jml_bm'] = $get->val_jml_1;
			$joindata['val_jml_bk'] = $get->val_jml_2;
			$joindata['val_jml_ms'] = $get->val_jml_3;
			$joindata['val_jml_or'] = $get->val_jml_4;
			$joindata['val_jml_bb'] = $get->val_jml_6;
			$joindata['val_jml_jk'] = $get->val_jml_15;
			$joindata['val_jml_ba'] = $get->val_jml_16;
			$joindata['val_jml_brd'] = $get->val_jml_5;
			$joindata['val_jjg_panen'] = $get->val_total_jjg;
			$joindata['ebcc_jml_bm'] = '';
			$joindata['ebcc_jml_bk'] = '';
			$joindata['ebcc_jml_ms'] = '';
			$joindata['ebcc_jml_or'] = '';
			$joindata['ebcc_jml_bb'] = '';
			$joindata['ebcc_jml_jk'] = '';
			$joindata['ebcc_jml_ba'] = '';
			$joindata['ebcc_jml_brd'] = '';
			$joindata['ebcc_jjg_panen'] = '';
			$joindata['ebcc_nik_kerani_buah'] = '';
			$joindata['ebcc_nama_kerani_buah'] = '';
			$joindata['ebcc_status_tph'] = '';
			$joindata['ebcc_keterangan_qrcode'] = '';
			$joindata['ebcc_no_bcc'] = $get->ebcc_no_bcc;
			$joindata['ebcc_picture_name'] = '';
			$joindata['akurasi_kualitas_ms'] = '';
			$joindata['match_status'] = 'NOT MATCH';
			$date = date( 'd-m-Y', strtotime( $get->val_date_time ) );
			
			if ( $get->ebcc_no_bcc != null ) {
				$sql_ebcc = "SELECT
						HDP.ID_RENCANA,
						HDP.TANGGAL_RENCANA,
						HDP.NIK_KERANI_BUAH,
						EMP_EBCC.EMP_NAME,
						HDP.ID_BA_AFD_BLOK,
						HDP.NO_REKAP_BCC,
						HP.NO_TPH,
						HP.NO_BCC,
						HP.STATUS_TPH,
						HP.PICTURE_NAME,
						CASE
							WHEN HP.KETERANGAN_QRCODE IS NULL THEN ''
							ELSE
								CASE
									WHEN HP.KETERANGAN_QRCODE = '1' THEN ' - QR Codenya Hilang'
									WHEN HP.KETERANGAN_QRCODE = '2' THEN ' - QR Codenya Rusak'
									ELSE ''
								END
						END AS KETERANGAN_QRCODE,
						NVL( EBCC.F_GET_HASIL_PANEN_BUNCH ( TBA.ID_BA, HP.NO_REKAP_BCC, HP.NO_BCC, 'BUNCH_HARVEST' ), 0 ) as JJG_PANEN,
						NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 1 ), 0 ) AS EBCC_JML_BM,
						NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 2 ), 0 ) AS EBCC_JML_BK,
						NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 3 ), 0 ) AS EBCC_JML_MS,
						NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 4 ), 0 ) AS EBCC_JML_OR,
						NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 6 ), 0 ) AS EBCC_JML_BB,
						NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 15 ), 0 ) AS EBCC_JML_JK,
						NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 16 ), 0 ) AS EBCC_JML_BA,   
						NVL( EBCC.F_GET_HASIL_PANEN_BRDX ( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC ), 0 ) AS EBCC_JML_BRD
					FROM (
							SELECT
								HRP.ID_RENCANA AS ID_RENCANA,
								HRP.TANGGAL_RENCANA AS TANGGAL_RENCANA,
								HRP.NIK_KERANI_BUAH AS NIK_KERANI_BUAH,
								DRP.ID_BA_AFD_BLOK AS ID_BA_AFD_BLOK,
								DRP.NO_REKAP_BCC AS NO_REKAP_BCC
							FROM
								EBCC.T_HEADER_RENCANA_PANEN HRP 
								LEFT JOIN EBCC.T_DETAIL_RENCANA_PANEN DRP ON HRP.ID_RENCANA = DRP.ID_RENCANA
						) HDP
						LEFT JOIN EBCC.T_HASIL_PANEN HP ON HP.ID_RENCANA = HDP.ID_RENCANA AND HP.NO_REKAP_BCC = HDP.NO_REKAP_BCC
						LEFT JOIN EBCC.T_BLOK TB ON TB.ID_BA_AFD_BLOK = HDP.ID_BA_AFD_BLOK
						LEFT JOIN EBCC.T_AFDELING TA ON TA.ID_BA_AFD = TB.ID_BA_AFD
						LEFT JOIN EBCC.T_BUSSINESSAREA TBA ON TBA.ID_BA = TA.ID_BA
						LEFT JOIN EBCC.T_EMPLOYEE EMP_EBCC ON EMP_EBCC.NIK = HDP.NIK_KERANI_BUAH 
					WHERE
						HP.NO_BCC = '{$get->ebcc_no_bcc}'";
						
				$query_ebcc = collect( $this->db_mobile_ins->select( $sql_ebcc ) )->first();
				$joindata['ebcc_nik_kerani_buah'] = $query_ebcc->nik_kerani_buah;
				$joindata['ebcc_nama_kerani_buah'] = $query_ebcc->emp_name;
				$joindata['ebcc_no_bcc'] = $query_ebcc->no_bcc;
				$joindata['ebcc_jml_bm'] = $query_ebcc->ebcc_jml_bm;
				$joindata['ebcc_jml_bk'] = $query_ebcc->ebcc_jml_bk;
				$joindata['ebcc_jml_ms'] = $query_ebcc->ebcc_jml_ms;
				$joindata['ebcc_jml_or'] = $query_ebcc->ebcc_jml_or;
				$joindata['ebcc_jml_bb'] = $query_ebcc->ebcc_jml_bb;
				$joindata['ebcc_jml_jk'] = $query_ebcc->ebcc_jml_jk;
				$joindata['ebcc_jml_ba'] = $query_ebcc->ebcc_jml_ba;
				$joindata['ebcc_jml_brd'] = $query_ebcc->ebcc_jml_brd;
				$joindata['ebcc_jjg_panen'] = $query_ebcc->jjg_panen;
				$joindata['ebcc_status_tph'] = $query_ebcc->status_tph;
				$joindata['ebcc_keterangan_qrcode'] = $query_ebcc->keterangan_qrcode;
				$joindata['ebcc_picture_name'] = ( $query_ebcc->picture_name != null ? 'http://tap-motion.tap-agri.com/ebcc/array/uploads'.$query_ebcc->picture_name : url( 'assets/dummy-janjang.jpg' ) );
				$joindata['match_status'] = ( intval( $query_ebcc->jjg_panen ) == intval( $get->val_total_jjg ) ? 'MATCH' : 'NOT MATCH' );
				$akurasi_kualitas_ms = intval( $query_ebcc->ebcc_jml_ms ) - intval( $get->val_jml_3 );
				$joindata['akurasi_kualitas_ms'] = ( $akurasi_kualitas_ms > 0 ? $akurasi_kualitas_ms : 0 );
				

			}
		}

		return $joindata;
	}
	
	public function EBCC_COMPARE_OLD( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE ) {
		if ( $REPORT_TYPE == 'EBCC_COMPARE_ESTATE' ) {
			$REPORT_TYPE = 'V';
		}
		else if ( $REPORT_TYPE == 'EBCC_COMPARE_MILL' ) {
			$REPORT_TYPE = 'M';
		}
		$where = "";
		$where_ebcc = "";

		$START_DATE = date( 'Y-m-d', strtotime( $START_DATE ) );
		$END_DATE = date( 'Y-m-d', strtotime( $END_DATE ) );
		$where = "";
		$where .= ( $REGION_CODE != "" && $COMP_CODE == "" ) ? " AND EST.REGION_CODE = '$REGION_CODE'  ": "";
		$where .= ( $COMP_CODE != "" && $BA_CODE == "" ) ? " AND EST.COMP_CODE = '$COMP_CODE'  ": "";
		$where .= ( $BA_CODE != "" && $AFD_CODE == "" ) ? " AND EST.WERKS = '$BA_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE == "" ) ? " AND EBCC_HEADER.WERKS||EBCC_HEADER.AFD_CODE = '$AFD_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE != "" ) ? " AND EBCC_HEADER.WERKS||EBCC_HEADER.AFD_CODE||EBCC_HEADER.BLOCK_CODE = '$BLOCK_CODE'  ": "";
		
		$where2 = "";
		$where2 .= ( $REGION_CODE != "" && $COMP_CODE == "" ) ? " AND SUBSTR (id_ba_afd_blok, 1, 2) in 
																	(SELECT comp_code
                                                                       FROM tap_dw.tm_comp@dwh_link
                                                                      WHERE region_code = '$REGION_CODE')  ": "";
		$where2 .= ( $COMP_CODE != "" && $BA_CODE == "" ) ? " AND SUBSTR (id_ba_afd_blok, 1, 2) = '$COMP_CODE'  ": "";
		$where2 .= ( $BA_CODE != "" && $AFD_CODE == "" ) ? " AND SUBSTR (id_ba_afd_blok, 1, 4) = '$BA_CODE'  ": "";
		$where2 .= ( $AFD_CODE != "" && $BLOCK_CODE == "" ) ? " AND SUBSTR (id_ba_afd_blok, 1, 5) = '$AFD_CODE'  ": "";
		$where2 .= ( $AFD_CODE != "" && $BLOCK_CODE != "" ) ? " AND id_ba_afd_blok = '$BLOCK_CODE'  ": "";
		
		$sql_lama_405 = "
				SELECT header.*,
                       NVL (detail.jml_1, 0) AS val_jml_1,
                       NVL (detail.jml_2, 0) AS val_jml_2,
                       NVL (detail.jml_3, 0) AS val_jml_3,
                       NVL (detail.jml_4, 0) AS val_jml_4,
                       NVL (detail.jml_5, 0) AS val_jml_5,
                       NVL (detail.jml_6, 0) AS val_jml_6,
                       NVL (detail.jml_7, 0) AS val_jml_7,
                       NVL (detail.jml_8, 0) AS val_jml_8,
                       NVL (detail.jml_9, 0) AS val_jml_9,
                       NVL (detail.jml_10, 0) AS val_jml_10,
                       NVL (detail.jml_11, 0) AS val_jml_11,
                       NVL (detail.jml_12, 0) AS val_jml_12,
                       NVL (detail.jml_13, 0) AS val_jml_13,
                       NVL (detail.jml_14, 0) AS val_jml_14,
                       NVL (detail.jml_15, 0) AS val_jml_15,
                       NVL (detail.jml_16, 0) AS val_jml_16,
                       (  NVL (detail.jml_1, 0)
                        + NVL (detail.jml_2, 0)
                        + NVL (detail.jml_3, 0)
                        + NVL (detail.jml_4, 0)
                        + NVL (detail.jml_6, 0)
                        + NVL (detail.jml_15, 0)
                        + NVL (detail.jml_16, 0))
                          AS val_total_jjg,
                       ebcc.jlh_ebcc ebcc_count,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.nik_kerani_buah END ebcc_nik_kerani_buah,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.nama_kerani_buah END ebcc_nama_kerani_buah,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.no_bcc END ebcc_no_bcc,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.status_tph END ebcc_status_tph,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.ebcc_jml_bm END ebcc_jml_bm,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.ebcc_jml_bk END ebcc_jml_bk,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.ebcc_jml_ms END ebcc_jml_ms,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.ebcc_jml_or END ebcc_jml_or,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.ebcc_jml_bb END ebcc_jml_bb,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.ebcc_jml_jk END ebcc_jml_jk,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.ebcc_jml_ba END ebcc_jml_ba,
                       CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.jjg_panen END ebcc_jjg_panen,
                       DECODE (ebcc.jlh_ebcc, 1, 'MATCH', 'NOT MATCH') akurasi_sampling_ebcc,
                       CASE
                          WHEN ebcc.jlh_ebcc = 1
                               AND (  NVL (detail.jml_1, 0)
                                    + NVL (detail.jml_2, 0)
                                    + NVL (detail.jml_3, 0)
                                    + NVL (detail.jml_4, 0)
                                    + NVL (detail.jml_6, 0)
                                    + NVL (detail.jml_15, 0)
                                    + NVL (detail.jml_16, 0)) = ebcc.jjg_panen
                          THEN
                             'MATCH'
                          ELSE
                             'NOT_MATCH'
                       END
                          akurasi_kuantitas,
                       CASE
                          WHEN ebcc.jlh_ebcc = 1
                               AND (  NVL (detail.jml_1, 0)
                                    + NVL (detail.jml_2, 0)
                                    + NVL (detail.jml_3, 0)
                                    + NVL (detail.jml_4, 0)
                                    + NVL (detail.jml_6, 0)
                                    + NVL (detail.jml_15, 0)
                                    + NVL (detail.jml_16, 0)) = ebcc.jjg_panen
                          THEN
                             CASE
                                WHEN NVL (detail.jml_3, 0) = NVL (ebcc.ebcc_jml_ms, 0) THEN 0
                                ELSE ABS (NVL (detail.jml_3, 0) - NVL (ebcc.ebcc_jml_ms, 0))
                             END
                       END
                          akurasi_kualitas_ms
                  FROM (SELECT ebcc_val.val_ebcc_code,
                               ebcc_val.val_werks,
                               ebcc_val.val_est_name,
                               ebcc_val.val_nik_validator,
                               ebcc_val.val_nama_validator,
                               ebcc_val.val_jabatan_validator,
                               ebcc_val.val_status_tph_scan,
                               ebcc_val.val_lat_tph,
                               ebcc_val.val_lon_tph,
                               ebcc_val.val_maturity_status,
                               CASE
                                  WHEN ebcc_val.val_alasan_manual IS NULL
                                  THEN
                                     ''
                                  ELSE
                                     CASE
                                        WHEN ebcc_val.val_alasan_manual = '1' THEN 'QR Codenya Hilang'
                                        WHEN ebcc_val.val_alasan_manual = '2' THEN 'QR Codenya Rusak'
                                     END
                               END
                                  AS val_alasan_manual,
                               ebcc_val.val_afd_code,
                               ebcc_val.val_block_code,
                               ebcc_val.val_date_time AS val_date_time,
                               ebcc_val.val_block_name,
                               ebcc_val.val_tph_code,
                               ebcc_val.val_delivery_ticket
                          FROM (SELECT ebcc_header.ebcc_validation_code AS val_ebcc_code,
                                       est.werks AS val_werks,
                                       est.est_name AS val_est_name,
                                       ebcc_header.afd_code AS val_afd_code,
                                       ebcc_header.insert_time AS val_date_time,
                                       ebcc_header.block_code AS val_block_code,
                                       ebcc_header.status_tph_scan AS val_status_tph_scan,
                                       ebcc_header.alasan_manual AS val_alasan_manual,
                                       ebcc_header.no_tph AS val_tph_code,
                                       ebcc_header.delivery_code AS val_delivery_ticket,
                                       user_auth.employee_nik AS val_nik_validator,
                                       emp.employee_fullname AS val_nama_validator,
                                       REPLACE (user_auth.user_role, '_', ' ') AS val_jabatan_validator,
                                       land_use.maturity_status AS val_maturity_status,
                                       land_use.spmon AS val_spmon,
                                       subblock.block_name AS val_block_name,
                                       ebcc_header.lat_tph AS val_lat_tph,
                                       ebcc_header.lat_tph AS val_lon_tph
                                  FROM mobile_inspection.tr_ebcc_validation_h ebcc_header
                                       LEFT JOIN mobile_inspection.tm_user_auth user_auth
                                          ON user_auth.user_auth_code =
                                                (CASE
                                                    WHEN LENGTH (ebcc_header.insert_user) = 3 THEN '0' || ebcc_header.insert_user
                                                    ELSE ebcc_header.insert_user
                                                 END)
                                       LEFT JOIN (SELECT employee_nik,
                                                         employee_fullname,
                                                         employee_position,
                                                         employee_joindate AS start_date,
                                                         CASE
                                                            WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD')
                                                            ELSE employee_resigndate
                                                         END
                                                            AS end_date
                                                    FROM tap_dw.tm_employee_hris@dwh_link
                                                  UNION ALL
                                                  SELECT nik,
                                                         employee_name,
                                                         job_code,
                                                         start_valid,
                                                         CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid
                                                    FROM tap_dw.tm_employee_sap@dwh_link) emp
                                          ON emp.employee_nik = user_auth.employee_nik
                                             AND TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (emp.start_date) AND TRUNC (emp.end_date)
                                       LEFT JOIN tap_dw.tm_est@dwh_link est
                                          ON est.werks = ebcc_header.werks AND SYSDATE BETWEEN est.start_valid AND est.end_valid
                                       LEFT JOIN (SELECT werks,
                                                         afd_code,
                                                         block_code,
                                                         block_name,
                                                         maturity_status,
                                                         spmon
                                                    FROM tap_dw.tr_hs_land_use@dwh_link
                                                   WHERE 1 = 1 AND ROWNUM < 2 AND maturity_status IS NOT NULL
                                                         AND spmon BETWEEN (SELECT CASE
                                                                                      WHEN TO_CHAR (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), 'MM') = '01'
                                                                                      THEN
                                                                                         TRUNC (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), 'MM')
                                                                                      ELSE
                                                                                         TRUNC (ADD_MONTHS (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), -1), 'MM')
                                                                                   END
                                                                              FROM DUAL)
                                                                       AND  ( (SELECT CASE
                                                                                         WHEN TO_CHAR (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), 'MM') = '01'
                                                                                         THEN
                                                                                            LAST_DAY (TO_DATE ('$END_DATE', 'RRRR-MM-DD'))
                                                                                         ELSE
                                                                                            LAST_DAY (ADD_MONTHS (TO_DATE ('$END_DATE', 'RRRR-MM-DD'), -1))
                                                                                      END
                                                                                 FROM DUAL))) land_use
                                          ON     land_use.werks = ebcc_header.werks
                                             AND land_use.afd_code = ebcc_header.afd_code
                                             AND land_use.block_code = ebcc_header.block_code
                                       LEFT JOIN tap_dw.tm_sub_block@dwh_link subblock
                                          ON subblock.werks = ebcc_header.werks AND subblock.sub_block_code = ebcc_header.block_code
                                 WHERE 1 = 1 AND SUBSTR (ebcc_header.ebcc_validation_code, 0, 1) = '$REPORT_TYPE'
                                       AND TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (TO_DATE ('$START_DATE', 'RRRR-MM-DD'))
                                                                               AND  TRUNC (TO_DATE ('$END_DATE', 'RRRR-MM-DD'))
                                                                                 $where
                                                                                                                                 ) ebcc_val
                        group by ebcc_val.val_ebcc_code,
                                 ebcc_val.val_werks,
                                 ebcc_val.val_est_name,
                                 ebcc_val.val_nik_validator,
                                 ebcc_val.val_nama_validator,
                                 ebcc_val.val_jabatan_validator,
                                 ebcc_val.val_status_tph_scan,
                                 ebcc_val.val_alasan_manual,
                                 ebcc_val.val_date_time,
                                 ebcc_val.val_afd_code,
                                 ebcc_val.val_block_code,
                                 ebcc_val.val_block_name,
                                 ebcc_val.val_tph_code,
                                 ebcc_val.val_delivery_ticket,
                                 ebcc_val.val_lat_tph,
                                 ebcc_val.val_lon_tph,
                                 ebcc_val.val_maturity_status,
                                 ebcc_val.val_spmon) header
                       left join (select *
                                    from (select kualitas.id_kualitas as idk, ebcc_detail.ebcc_validation_code, ebcc_detail.jumlah
                                            from    tap_dw.t_kualitas_panen@dwh_link kualitas
                                                 left join
                                                    mobile_inspection.tr_ebcc_validation_d ebcc_detail
                                                 on ebcc_detail.id_kualitas = kualitas.id_kualitas
                                           where kualitas.active_status = 'YES'
										     and ebcc_detail.ebcc_validation_code is NOT NULL) pivot (SUM (jumlah)
                                                                                 for idk
                                                                                 in ('1' as jml_1,
                                                                                 '2' as jml_2,
                                                                                 '3' as jml_3,
                                                                                 '4' as jml_4,
                                                                                 '5' as jml_5,
                                                                                 '6' as jml_6,
                                                                                 '7' as jml_7,
                                                                                 '8' as jml_8,
                                                                                 '9' as jml_9,
                                                                                 '10' as jml_10,
                                                                                 '11' as jml_11,
                                                                                 '12' as jml_12,
                                                                                 '13' as jml_13,
                                                                                 '14' as jml_14,
                                                                                 '15' as jml_15,
                                                                                 '16' as jml_16))) detail
                          on header.val_ebcc_code = detail.ebcc_validation_code
                       left join (select tanggal_rencana,
                                         id_ba,
                                         id_afd,
                                         id_blok,
                                         no_tph,
                                         jlh_ebcc,
                                         nik_kerani_buah,
                                         nama_kerani_buah,
                                         no_bcc,
                                         status_tph,
                                         NVL (ebcc.f_get_hasil_panen_bunch (id_ba,
                                                                                        no_rekap_bcc,
                                                                                        no_bcc,
                                                                                        'BUNCH_HARVEST'), 0)
                                            as jjg_panen,
                                         ebcc_jml_bm,
                                         ebcc_jml_bk,
                                         ebcc_jml_ms,
                                         ebcc_jml_or,
                                         ebcc_jml_bb,
                                         ebcc_jml_jk,
                                         ebcc_jml_ba
                                    from (  select hrp.tanggal_rencana,
                                                   substr(ID_BA_AFD_BLOK,1,4) id_ba,
                                                   substr(ID_BA_AFD_BLOK,5,1) id_afd,
                                                   substr(ID_BA_AFD_BLOK,6,3)id_blok,
                                                   /*SUBSTR (no_bcc, 12, 3) no_tph,*/
												   hp.no_tph no_tph,
                                                   COUNT (distinct hp.no_bcc) jlh_ebcc,
                                                   MAX (hrp.nik_kerani_buah) nik_kerani_buah,
                                                   MAX (emp_ebcc.emp_name) nama_kerani_buah,
                                                   MAX (no_bcc) no_bcc,
                                                   MAX (hp.status_tph) status_tph,
                                                   MAX (hp.no_rekap_bcc) no_rekap_bcc,
                                                   SUM (CASE when thk.id_kualitas = 1 then thk.qty end) ebcc_jml_bm,
                                                   SUM (CASE when thk.id_kualitas = 2 then thk.qty end) ebcc_jml_bk,
                                                   SUM (CASE when thk.id_kualitas = 3 then thk.qty end) ebcc_jml_ms,
                                                   SUM (CASE when thk.id_kualitas = 4 then thk.qty end) ebcc_jml_or,
                                                   SUM (CASE when thk.id_kualitas = 6 then thk.qty end) ebcc_jml_bb,
                                                   SUM (CASE when thk.id_kualitas = 15 then thk.qty end) ebcc_jml_jk,
                                                   SUM (CASE when thk.id_kualitas = 16 then thk.qty end) ebcc_jml_ba
                                              from ebcc.t_header_rencana_panen hrp
                                                   left join ebcc.t_detail_rencana_panen drp
                                                      on hrp.id_rencana = drp.id_rencana
                                                   left join ebcc.t_hasil_panen hp
                                                      on hp.id_rencana = drp.id_rencana and hp.no_rekap_bcc = drp.no_rekap_bcc
                                                   left join ebcc.t_employee emp_ebcc
                                                      on emp_ebcc.nik = hrp.nik_kerani_buah
                                                   left join ebcc.t_hasilpanen_kualtas thk
                                                      on hp.no_bcc = thk.id_bcc and hp.id_rencana = thk.id_rencana
                                             where hrp.tanggal_rencana between TO_DATE ('$START_DATE', 'YYYY-MM-DD')
                                                                               and  TO_DATE ('$END_DATE', 'YYYY-MM-DD')
												   $where2
                                          group by hrp.tanggal_rencana, id_ba_afd_blok,
												   hp.no_tph)) ebcc
                          on     TRUNC (ebcc.tanggal_rencana) = TRUNC (val_date_time)
                             and ebcc.id_ba = val_werks
                             and ebcc.id_afd = val_afd_code
                             and ebcc.id_blok = val_block_code
                             and ebcc.no_tph = val_tph_code
                             order by trunc(header.val_date_time) ASC, header.val_nik_validator ASC, header.val_afd_code ASC
		";
		$sql = "WITH tbl
                AS (        
                SELECT header.*,
                           NVL (detail.jml_1, 0) AS val_jml_1,
                           NVL (detail.jml_2, 0) AS val_jml_2,
                           NVL (detail.jml_3, 0) AS val_jml_3,
                           NVL (detail.jml_4, 0) AS val_jml_4,
                           NVL (detail.jml_5, 0) AS val_jml_5,
                           NVL (detail.jml_6, 0) AS val_jml_6,
                           NVL (detail.jml_7, 0) AS val_jml_7,
                           NVL (detail.jml_8, 0) AS val_jml_8,
                           NVL (detail.jml_9, 0) AS val_jml_9,
                           NVL (detail.jml_10, 0) AS val_jml_10,
                           NVL (detail.jml_11, 0) AS val_jml_11,
                           NVL (detail.jml_12, 0) AS val_jml_12,
                           NVL (detail.jml_13, 0) AS val_jml_13,
                           NVL (detail.jml_14, 0) AS val_jml_14,
                           NVL (detail.jml_15, 0) AS val_jml_15,
                           NVL (detail.jml_16, 0) AS val_jml_16,
                           (NVL (detail.jml_1, 0) + NVL (detail.jml_2, 0) + NVL (detail.jml_3, 0) + NVL (detail.jml_4, 0) + NVL (detail.jml_6, 0) + NVL (detail.jml_15, 0) + NVL (detail.jml_16, 0))
                              AS val_total_jjg,
                           ebcc_me.jlh_ebcc ebcc_count,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.nik_kerani_buah END ebcc_nik_kerani_buah,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.nama_kerani_buah END ebcc_nama_kerani_buah,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.nik_mandor END ebcc_nik_mandor,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.nama_mandor END ebcc_nama_mandor,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.no_bcc END ebcc_no_bcc,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.status_tph END ebcc_status_tph,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.ebcc_jml_bm END ebcc_jml_bm,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.ebcc_jml_bk END ebcc_jml_bk,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.ebcc_jml_ms END ebcc_jml_ms,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.ebcc_jml_or END ebcc_jml_or,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.ebcc_jml_bb END ebcc_jml_bb,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.ebcc_jml_jk END ebcc_jml_jk,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.ebcc_jml_ba END ebcc_jml_ba,
                           CASE WHEN ebcc_me.jlh_ebcc = 1 THEN ebcc_me.jjg_panen END ebcc_jjg_panen,
                           DECODE (ebcc_me.jlh_ebcc, 1, 'MATCH', 'NOT MATCH') akurasi_sampling_ebcc,
                           CASE
                              WHEN ebcc_me.jlh_ebcc = 1
                                   AND (NVL (detail.jml_1, 0) + NVL (detail.jml_2, 0) + NVL (detail.jml_3, 0) + NVL (detail.jml_4, 0) + NVL (detail.jml_6, 0) + NVL (detail.jml_15, 0) + NVL (detail.jml_16, 0)) =
                                         ebcc_me.jjg_panen
                              THEN
                                 'MATCH'
                              ELSE
                                 'NOT MATCH'
                           END
                              akurasi_kuantitas,
                           CASE
                              WHEN ebcc_me.jlh_ebcc = 1
                                   AND (NVL (detail.jml_1, 0) + NVL (detail.jml_2, 0) + NVL (detail.jml_3, 0) + NVL (detail.jml_4, 0) + NVL (detail.jml_6, 0) + NVL (detail.jml_15, 0) + NVL (detail.jml_16, 0)) =
                                         ebcc_me.jjg_panen
                              THEN
                                 CASE WHEN NVL (detail.jml_3, 0) = NVL (ebcc_me.ebcc_jml_ms, 0) THEN 0 ELSE ABS (NVL (detail.jml_3, 0) - NVL (ebcc_me.ebcc_jml_ms, 0)) END
                           END
                              akurasi_kualitas_ms
                      FROM (  SELECT ebcc_val.val_ebcc_code,
                                     ebcc_val.val_werks,
                                     ebcc_val.val_est_name,
                                     ebcc_val.val_nik_validator,
                                     ebcc_val.val_nama_validator,
                                     ebcc_val.val_jabatan_validator,
                                     ebcc_val.val_status_tph_scan,
                                     ebcc_val.val_lat_tph,
                                     ebcc_val.val_lon_tph,
                                     CASE
                                        WHEN ebcc_val.val_alasan_manual IS NULL
                                        THEN
                                           ''
                                        ELSE
                                           CASE
                                              WHEN ebcc_val.val_alasan_manual = '1' THEN 'QR Codenya Hilang'
                                              WHEN ebcc_val.val_alasan_manual = '2' THEN 'QR Codenya Rusak'
                                           END
                                     END
                                        AS val_alasan_manual,
                                     ebcc_val.val_afd_code,
                                     ebcc_val.val_block_code,
                                     ebcc_val.val_date_time AS val_date_time,
                                     ebcc_val.val_block_name,
                                     ebcc_val.val_tph_code,
                                     ebcc_val.val_delivery_ticket,
                                     ebcc_val.val_user_role,
                                     ebcc_val.val_sumber
                                FROM (SELECT ebcc_header.ebcc_validation_code AS val_ebcc_code,
                                             est.werks AS val_werks,
                                             est.est_name AS val_est_name,
                                             ebcc_header.afd_code AS val_afd_code,
                                             ebcc_header.insert_time AS val_date_time,
                                             ebcc_header.block_code AS val_block_code,
                                             ebcc_header.status_tph_scan AS val_status_tph_scan,
                                             ebcc_header.alasan_manual AS val_alasan_manual,
                                             ebcc_header.no_tph AS val_tph_code,
                                             ebcc_header.delivery_code AS val_delivery_ticket,
                                             ebcc_header.insert_user AS val_nik_validator,
                                             emp.employee_fullname AS val_nama_validator,
                                             ebcc_header.user_role AS val_user_role,
                                             REPLACE (NVL (ebcc_header.user_role, employee_position), '_', ' ') AS val_jabatan_validator,
                                             subblock.block_name AS val_block_name,
                                             ebcc_header.lat_tph AS val_lat_tph,
                                             ebcc_header.lat_tph AS val_lon_tph,
                                             ebcc_header.sumber AS val_sumber
                                        FROM (SELECT ebcc_header.ebcc_validation_code,
                                                     ebcc_header.werks,
                                                     ebcc_header.afd_code,
                                                     ebcc_header.block_code,
                                                     ebcc_header.no_tph,
                                                     ebcc_header.status_tph_scan,
                                                     TO_NUMBER (ebcc_header.lat_tph) lat_tph,
                                                     TO_NUMBER (ebcc_header.lon_tph) lon_tph,
                                                     user_auth.employee_nik insert_user,
                                                     ebcc_header.insert_time,
                                                     ebcc_header.status_sync,
                                                     ebcc_header.sync_time,
                                                     ebcc_header.update_time,
                                                     ebcc_header.delivery_code,
                                                     TO_NUMBER (ebcc_header.alasan_manual) alasan_manual,
                                                     user_auth.user_role,
                                                     'MI' sumber
                                                FROM mobile_inspection.tr_ebcc_validation_h/*@proddb_link*/ ebcc_header LEFT JOIN mobile_inspection.tm_user_auth/*@proddb_link*/ user_auth
                                                        ON user_auth.user_auth_code = (CASE WHEN LENGTH (ebcc_header.insert_user) = 3 THEN '0' || ebcc_header.insert_user ELSE ebcc_header.insert_user END)
                                               WHERE TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (TO_DATE ('$START_DATE', 'RRRR-MM-DD')) AND TRUNC (TO_DATE ('$END_DATE', 'RRRR-MM-DD'))
                                                     AND SUBSTR (ebcc_header.ebcc_validation_code, 0, 1) = '$REPORT_TYPE'                                                    
                                                     AND ebcc_header.werks = nvl('$BA_CODE',ebcc_header.werks)
                                             ) ebcc_header
                                             LEFT JOIN (SELECT employee_nik,
                                                               employee_fullname,
                                                               employee_position,
                                                               employee_joindate AS start_date,
                                                               CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD') ELSE employee_resigndate END AS end_date
                                                          FROM tap_dw.tm_employee_hris@dwh_link
                                                        UNION ALL
                                                        SELECT nik,
                                                               employee_name,
                                                               job_code,
                                                               start_valid,
                                                               CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid
                                                          FROM tap_dw.tm_employee_sap@dwh_link) emp
                                                ON emp.employee_nik = ebcc_header.insert_user AND TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (emp.start_date) AND TRUNC (emp.end_date)
                                             LEFT JOIN tap_dw.tm_est@dwh_link est
                                                ON est.werks = ebcc_header.werks AND TRUNC (ebcc_header.insert_time) BETWEEN est.start_valid AND est.end_valid                                    
                                             LEFT JOIN tap_dw.tm_sub_block@dwh_link subblock
                                                ON     subblock.werks = ebcc_header.werks
                                                   AND subblock.sub_block_code = ebcc_header.block_code
                                                   AND ebcc_header.insert_time BETWEEN subblock.start_valid AND subblock.end_valid
                                       WHERE TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (TO_DATE ('$START_DATE', 'RRRR-MM-DD')) AND TRUNC (TO_DATE ('$END_DATE', 'RRRR-MM-DD'))                      
                                       $where)
                                     ebcc_val
                            GROUP BY ebcc_val.val_ebcc_code,
                                     ebcc_val.val_werks,
                                     ebcc_val.val_est_name,
                                     ebcc_val.val_nik_validator,
                                     ebcc_val.val_nama_validator,
                                     ebcc_val.val_jabatan_validator,
                                     ebcc_val.val_status_tph_scan,
                                     ebcc_val.val_alasan_manual,
                                     ebcc_val.val_date_time,
                                     ebcc_val.val_afd_code,
                                     ebcc_val.val_block_code,
                                     ebcc_val.val_block_name,
                                     ebcc_val.val_tph_code,
                                     ebcc_val.val_delivery_ticket,
                                     ebcc_val.val_lat_tph,
                                     ebcc_val.val_lon_tph,
                                     ebcc_val.val_user_role,
                                     ebcc_val.val_sumber) header
                           LEFT JOIN (SELECT *
                                        FROM (SELECT kualitas.id_kualitas AS idk, ebcc_detail.ebcc_validation_code, ebcc_detail.jumlah
                                                FROM tap_dw.t_kualitas_panen@dwh_link kualitas LEFT JOIN mobile_inspection.tr_ebcc_validation_d/*@proddb_link*/ ebcc_detail
                                                        ON ebcc_detail.id_kualitas = kualitas.id_kualitas
                                               WHERE kualitas.active_status = 'YES' and ebcc_detail.ebcc_validation_code IS NOT NULL
                                             ) PIVOT (SUM (jumlah)
                                               FOR idk
                                               IN ('1' AS jml_1,
                                               '2' AS jml_2,
                                               '3' AS jml_3,
                                               '4' AS jml_4,
                                               '5' AS jml_5,
                                               '6' AS jml_6,
                                               '7' AS jml_7,
                                               '8' AS jml_8,
                                               '9' AS jml_9,
                                               '10' AS jml_10,
                                               '11' AS jml_11,
                                               '12' AS jml_12,
                                               '13' AS jml_13,
                                               '14' AS jml_14,
                                               '15' AS jml_15,
                                               '16' AS jml_16))) detail
                              ON header.val_ebcc_code = detail.ebcc_validation_code
                           LEFT JOIN (SELECT tanggal_rencana,
                                             id_ba,
                                             id_afd,
                                             id_blok,
                                             no_tph,
                                             kode_delivery_ticket,
                                             jlh_ebcc,
                                             nik_kerani_buah,
                                             nama_kerani_buah,
                                             nik_mandor,
                                             nama_mandor,
                                             no_bcc,
                                             status_tph,
                                             NVL (ebcc.f_get_hasil_panen_bunch/*@proddb_link*/ (id_ba,
                                                                                            no_rekap_bcc,
                                                                                            no_bcc,
                                                                                            'BUNCH_HARVEST'), 0)
                                                AS jjg_panen,
                                             ebcc_jml_bm,
                                             ebcc_jml_bk,
                                             ebcc_jml_ms,
                                             ebcc_jml_or,
                                             ebcc_jml_bb,
                                             ebcc_jml_jk,
                                             ebcc_jml_ba,
                                             cut_off_date
                                        FROM (  SELECT hrp.tanggal_rencana,
                                                       SUBSTR (id_ba_afd_blok, 1, 4) id_ba,
                                                       SUBSTR (id_ba_afd_blok, 5, 1) id_afd,
                                                       SUBSTR (id_ba_afd_blok, 6, 3) id_blok,
                                                       /*SUBSTR (no_bcc, 12, 3) no_tph,*/
                                                       hp.no_tph no_tph,
                                                       NVL (hp.kode_delivery_ticket, '-') kode_delivery_ticket,
                                                       COUNT (DISTINCT hp.no_bcc) jlh_ebcc,
                                                       MAX (hrp.nik_kerani_buah) nik_kerani_buah,
                                                       MAX (emp_ebcc.emp_name) nama_kerani_buah,
                                                       MAX (hrp.nik_mandor) nik_mandor,
                                                       MAX (emp_ebcc1.emp_name) nama_mandor,
                                                       MAX (no_bcc) no_bcc,
                                                       MAX (hp.status_tph) status_tph,
                                                       MAX (hp.no_rekap_bcc) no_rekap_bcc,
                                                       SUM (CASE WHEN thk.id_kualitas = 1 THEN thk.qty END) ebcc_jml_bm,
                                                       SUM (CASE WHEN thk.id_kualitas = 2 THEN thk.qty END) ebcc_jml_bk,
                                                       SUM (CASE WHEN thk.id_kualitas = 3 THEN thk.qty END) ebcc_jml_ms,
                                                       SUM (CASE WHEN thk.id_kualitas = 4 THEN thk.qty END) ebcc_jml_or,
                                                       SUM (CASE WHEN thk.id_kualitas = 6 THEN thk.qty END) ebcc_jml_bb,
                                                       SUM (CASE WHEN thk.id_kualitas = 15 THEN thk.qty END) ebcc_jml_jk,
                                                       SUM (CASE WHEN thk.id_kualitas = 16 THEN thk.qty END) ebcc_jml_ba,
                                                       MAX (param.cut_off_date) cut_off_date
                                                  FROM ebcc.t_header_rencana_panen/*@proddb_link*/ hrp
                                                       LEFT JOIN ebcc.t_detail_rencana_panen/*@proddb_link*/ drp
                                                          ON hrp.id_rencana = drp.id_rencana
                                                       LEFT JOIN ebcc.t_hasil_panen/*@proddb_link*/ hp
                                                          ON hp.id_rencana = drp.id_rencana AND hp.no_rekap_bcc = drp.no_rekap_bcc
                                                       LEFT JOIN ebcc.t_employee/*@proddb_link*/ emp_ebcc
                                                          ON emp_ebcc.nik = hrp.nik_kerani_buah
                                                       LEFT JOIN ebcc.t_employee/*@proddb_link*/ emp_ebcc1
                                                          ON emp_ebcc1.nik = hrp.nik_mandor
                                                       LEFT JOIN ebcc.t_hasilpanen_kualtas/*@proddb_link*/ thk
                                                          ON hp.no_bcc = thk.id_bcc AND hp.id_rencana = thk.id_rencana
                                                       LEFT JOIN (SELECT TO_DATE (parameter_desc, 'dd-mon-yyyy') cut_off_date
                                                                    FROM tm_parameter
                                                                   WHERE parameter_name = 'CUT_OFF_DELIVERY_TICKET') param
                                                          ON 1 = 1
                                                 WHERE hrp.tanggal_rencana BETWEEN TO_DATE ('$START_DATE', 'YYYY-MM-DD') AND TO_DATE ('$END_DATE', 'YYYY-MM-DD')
                                                 AND SUBSTR (id_ba_afd_blok, 1, 4) = nvl('$BA_CODE',SUBSTR (id_ba_afd_blok, 1, 4))
                                                 $where2
                                              GROUP BY hrp.tanggal_rencana,
                                                       id_ba_afd_blok,
                                                       hp.no_tph,
                                                       NVL (hp.kode_delivery_ticket, '-'))) ebcc_me
                              ON     TRUNC (ebcc_me.tanggal_rencana) = TRUNC (val_date_time)
                                 AND ebcc_me.id_ba = val_werks                                                                                     
                                 AND ebcc_me.id_afd = val_afd_code
                                 AND ebcc_me.id_blok = val_block_code
                                 AND ebcc_me.no_tph = val_tph_code
                                 AND CASE WHEN TRUNC (ebcc_me.tanggal_rencana) <= TRUNC (cut_off_date) THEN NVL (ebcc_me.kode_delivery_ticket, '-') ELSE ebcc_me.kode_delivery_ticket END =
                                       NVL (val_delivery_ticket, '-')
                                       )
        SELECT val_ebcc_code,
               val_werks,
               val_est_name,
               val_nik_validator,
               val_nama_validator,
               val_jabatan_validator,
               val_status_tph_scan,
               TRIM (TO_CHAR (val_lat_tph)) val_lat_tph,
               TRIM (TO_CHAR (val_lon_tph)) val_lon_tph,
               NULL val_maturity_status,
               val_alasan_manual,
               val_afd_code,
               val_block_code,
               val_date_time,
               val_block_name,
               val_tph_code,
               val_delivery_ticket,
               val_jml_1,
               val_jml_2,
               val_jml_3,
               val_jml_4,
               val_jml_5,
               val_jml_6,
               val_jml_7,
               val_jml_8,
               val_jml_9,
               val_jml_10,
               val_jml_11,
               val_jml_12,
               val_jml_13,
               val_jml_14,
               val_jml_15,
               val_jml_16,
               val_total_jjg,
               ebcc_count,
               ebcc_nik_kerani_buah,
               ebcc_nama_kerani_buah,
               ebcc_no_bcc,
               ebcc_status_tph,
               ebcc_jml_bm,
               ebcc_jml_bk,
               ebcc_jml_ms,
               ebcc_jml_or,
               ebcc_jml_bb,
               ebcc_jml_jk,
               ebcc_jml_ba,
               ebcc_jjg_panen,
               akurasi_sampling_ebcc,
               akurasi_kuantitas,
               akurasi_kualitas_ms, 
               CASE WHEN tph.werks is not null then 'INACTIVE' ELSE 'ACTIVE' END status_tph
          FROM tbl hd    LEFT JOIN
                (SELECT *
                   FROM mobile_inspection.tm_status_tph
                  WHERE status = 'INACTIVE'
                  and werks = nvl('$BA_CODE',werks)) tph
             ON     hd.val_werks = tph.werks
                AND hd.val_afd_code = tph.afd_code
                AND hd.val_block_code = tph.block_code
                AND hd.val_tph_code = tph.no_tph
                AND hd.val_date_time BETWEEN tph.start_valid AND nvl(tph.end_valid,sysdate)
                

                
";
		$sql_helpdesk = " SELECT val_ebcc_code,
       val_werks,
       val_est_name,
       val_nik_validator,
       val_nama_validator,
       val_jabatan_validator,
       val_status_tph_scan,
       TRIM (TO_CHAR (val_lat_tph)) val_lat_tph,
       TRIM (TO_CHAR (val_lon_tph)) val_lon_tph,
       NULL val_maturity_status,
       val_alasan_manual,
       val_afd_code,
       val_block_code,
       val_date_time,
       val_block_name,
       val_tph_code,
       val_delivery_ticket,
       val_jml_1,
       val_jml_2,
       val_jml_3,
       val_jml_4,
       val_jml_5,
       val_jml_6,
       val_jml_7,
       val_jml_8,
       val_jml_9,
       val_jml_10,
       val_jml_11,
       val_jml_12,
       val_jml_13,
       val_jml_14,
       val_jml_15,
       val_jml_16,
       val_total_jjg,
       ebcc_count,
       ebcc_nik_kerani_buah,
       ebcc_nama_kerani_buah,
       ebcc_no_bcc,
       ebcc_status_tph,
       ebcc_jml_bm,
       ebcc_jml_bk,
       ebcc_jml_ms,
       ebcc_jml_or,
       ebcc_jml_bb,
       ebcc_jml_jk,
       ebcc_jml_ba,
       ebcc_jjg_panen,
       akurasi_sampling_ebcc,
       akurasi_kuantitas,
       akurasi_kualitas_ms,
       'ACTIVE' status_tph
  FROM mobile_inspection.tr_ebcc_compare@proddb_link hd
 WHERE TO_CHAR (hd.val_date_time, 'RRRRMMDD') BETWEEN '20200921' AND '20200930'
 and val_sumber = 'MI'";				
				 //echo $sql;
				 //die;
		$get = $this->db_mobile_ins->select( $sql );
		$joindata = array();
		$summary_data = array();

		if ( !empty( $get ) ) {
			$i = 0;
			foreach ( $get as $ec ) {
				$summary_krani = date( 'Ymd', strtotime( $ec->val_date_time ) ).'-'.$ec->val_nik_validator.'-'.$ec->ebcc_nik_kerani_buah;
				$joindata[$i]['summary_krani'] = $summary_krani;
				$summary_code = date( 'Ymd', strtotime( $ec->val_date_time ) ).$ec->val_nik_validator.'_'.$ec->val_werks.$ec->val_afd_code;
				$joindata[$i]['summary_code'] = $summary_code;
				$joindata[$i]['val_ebcc_code'] = $ec->val_ebcc_code;
				$joindata[$i]['val_werks'] = $ec->val_werks;
				$joindata[$i]['val_est_name'] = $ec->val_est_name;
				$joindata[$i]['val_nik_validator'] = $ec->val_nik_validator;
				$joindata[$i]['val_nama_validator'] = $ec->val_nama_validator;
				$joindata[$i]['val_jabatan_validator'] = $ec->val_jabatan_validator;
				$joindata[$i]['val_status_tph_scan'] = $ec->val_status_tph_scan;
				$joindata[$i]['val_alasan_manual'] = $ec->val_alasan_manual;
				$joindata[$i]['val_afd_code'] = $ec->val_afd_code;
				$joindata[$i]['val_block_code'] = $ec->val_block_code;
				$joindata[$i]['val_date_time'] = date( 'Y-m-d', strtotime( $ec->val_date_time ) );
				$joindata[$i]['val_block_name'] = $ec->val_block_name;
				$joindata[$i]['val_tph_code'] = $ec->val_tph_code;
				$joindata[$i]['status_tph'] = $ec->status_tph;
				$joindata[$i]['val_delivery_ticket'] = $ec->val_delivery_ticket;
				$joindata[$i]['val_jml_bm'] = $ec->val_jml_1;
				$joindata[$i]['val_jml_bk'] = $ec->val_jml_2;
				$joindata[$i]['val_jml_ms'] = $ec->val_jml_3;
				$joindata[$i]['val_jml_or'] = $ec->val_jml_4;
				$joindata[$i]['val_jml_bb'] = $ec->val_jml_6;
				$joindata[$i]['val_jml_jk'] = $ec->val_jml_15;
				$joindata[$i]['val_jml_ba'] = $ec->val_jml_16;
				$joindata[$i]['val_jml_brd'] = $ec->val_jml_5;
				$joindata[$i]['val_jjg_panen'] = $ec->val_total_jjg;
				$joindata[$i]['ebcc_count'] = $ec->ebcc_count;
				$joindata[$i]['ebcc_jml_bm'] = $ec->ebcc_jml_bm;
				$joindata[$i]['ebcc_jml_bk'] = $ec->ebcc_jml_bk;
				$joindata[$i]['ebcc_jml_ms'] = $ec->ebcc_jml_ms;
				$joindata[$i]['ebcc_jml_or'] = $ec->ebcc_jml_or;
				$joindata[$i]['ebcc_jml_bb'] = $ec->ebcc_jml_bb;
				$joindata[$i]['ebcc_jml_jk'] = $ec->ebcc_jml_jk;
				$joindata[$i]['ebcc_jml_ba'] = $ec->ebcc_jml_ba;
				$joindata[$i]['ebcc_jml_brd'] = '';
				$joindata[$i]['ebcc_jjg_panen'] = $ec->ebcc_jjg_panen;
				$joindata[$i]['ebcc_nik_kerani_buah'] = $ec->ebcc_nik_kerani_buah;
				$joindata[$i]['ebcc_nama_kerani_buah'] = $ec->ebcc_nama_kerani_buah;
				$joindata[$i]['ebcc_status_tph'] = $ec->ebcc_status_tph;
				$joindata[$i]['ebcc_keterangan_qrcode'] = '';
				$joindata[$i]['ebcc_no_bcc'] = $ec->ebcc_no_bcc;
				$joindata[$i]['akurasi_kualitas_ms'] = $ec->akurasi_kualitas_ms;
				$joindata[$i]['akurasi_sampling_ebcc'] = $ec->akurasi_sampling_ebcc;
				$joindata[$i]['akurasi_kuantitas'] = $ec->akurasi_kuantitas;
				$joindata[$i]['link_foto'] = url( 'preview/compare-ebcc/'.$ec->val_ebcc_code );

				/* // Data Summary
				if ( !isset( $summary_data[$summary_code] ) ) {
					$summary_data[$summary_code] = array();
					$summary_data[$summary_code]['nama'] = $ec->val_nama_validator;
					$summary_data[$summary_code]['match'] = 0;
					$summary_data[$summary_code]['akurasi'] = 0;
					$summary_data[$summary_code]['tanggal'] = date( 'd M Y', strtotime( $ec->val_date_time ) );
					$summary_data[$summary_code]['jumlah_data'] = 0;
					$summary_data[$summary_code]['val_jml_bm'] = 0;
					$summary_data[$summary_code]['val_jml_bk'] = 0;
					$summary_data[$summary_code]['val_jml_ms'] = 0;
					$summary_data[$summary_code]['val_jml_or'] = 0;
					$summary_data[$summary_code]['val_jml_bb'] = 0;
					$summary_data[$summary_code]['val_jml_jk'] = 0;
					$summary_data[$summary_code]['val_jml_ba'] = 0;
					$summary_data[$summary_code]['val_jml_brd'] = 0;
					$summary_data[$summary_code]['val_jjg_panen'] = 0;
					$summary_data[$summary_code]['ebcc_jml_bm'] = 0;
					$summary_data[$summary_code]['ebcc_jml_bk'] = 0;
					$summary_data[$summary_code]['ebcc_jml_ms'] = 0;
					$summary_data[$summary_code]['ebcc_jml_or'] = 0;
					$summary_data[$summary_code]['ebcc_jml_bb'] = 0;
					$summary_data[$summary_code]['ebcc_jml_jk'] = 0;
					$summary_data[$summary_code]['ebcc_jml_ba'] = 0;
					$summary_data[$summary_code]['ebcc_jml_brd'] = 0;
					$summary_data[$summary_code]['ebcc_jjg_panen'] = 0;
				}

				$summary_data[$summary_code]['jumlah_data'] += 1;
				$summary_data[$summary_code]['val_jml_bm'] = $summary_data[$summary_code]['val_jml_bm'] + intval( $ec->val_jml_1 );
				$summary_data[$summary_code]['val_jml_bk'] = $summary_data[$summary_code]['val_jml_bk'] + intval( $ec->val_jml_2 );
				$summary_data[$summary_code]['val_jml_ms'] = $summary_data[$summary_code]['val_jml_ms'] + intval( $ec->val_jml_3 );
				$summary_data[$summary_code]['val_jml_or'] = $summary_data[$summary_code]['val_jml_or'] + intval( $ec->val_jml_4 );
				$summary_data[$summary_code]['val_jml_bb'] = $summary_data[$summary_code]['val_jml_bb'] + intval( $ec->val_jml_6 );
				$summary_data[$summary_code]['val_jml_jk'] = $summary_data[$summary_code]['val_jml_jk'] + intval( $ec->val_jml_15 );
				$summary_data[$summary_code]['val_jml_ba'] = $summary_data[$summary_code]['val_jml_ba'] + intval( $ec->val_jml_16 );
				$summary_data[$summary_code]['val_jml_brd'] = $summary_data[$summary_code]['val_jml_brd'] + intval( $ec->val_jml_5 );
				$summary_data[$summary_code]['val_jjg_panen'] = $summary_data[$summary_code]['val_jjg_panen'] + intval( $ec->val_total_jjg );

				$date = date( 'd-m-Y', strtotime( $ec->val_date_time ) );
				
				if ( $ec->ebcc_no_bcc != null ) {
					$sql_ebcc = "SELECT
							HDP.ID_RENCANA,
							HDP.TANGGAL_RENCANA,
							HDP.NIK_KERANI_BUAH,
							EMP_EBCC.EMP_NAME,
							HDP.ID_BA_AFD_BLOK,
							HDP.NO_REKAP_BCC,
							HP.NO_TPH,
							HP.NO_BCC,
							HP.STATUS_TPH,
							CASE
								WHEN HP.KETERANGAN_QRCODE IS NULL THEN ''
								ELSE
									CASE
										WHEN HP.KETERANGAN_QRCODE = '1' THEN ' - QR Codenya Hilang'
										WHEN HP.KETERANGAN_QRCODE = '2' THEN ' - QR Codenya Rusak'
										ELSE ''
									END
							END AS KETERANGAN_QRCODE,
							NVL( EBCC.F_GET_HASIL_PANEN_BUNCH ( TBA.ID_BA, HP.NO_REKAP_BCC, HP.NO_BCC, 'BUNCH_HARVEST' ), 0 ) as JJG_PANEN,
							NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 1 ), 0 ) AS EBCC_JML_BM,
							NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 2 ), 0 ) AS EBCC_JML_BK,
							NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 3 ), 0 ) AS EBCC_JML_MS,
							NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 4 ), 0 ) AS EBCC_JML_OR,
							NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 6 ), 0 ) AS EBCC_JML_BB,
							NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 15 ), 0 ) AS EBCC_JML_JK,
							NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 16 ), 0 ) AS EBCC_JML_BA,   
							NVL( EBCC.F_GET_HASIL_PANEN_BRDX ( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC ), 0 ) AS EBCC_JML_BRD
						FROM (
								SELECT
									HRP.ID_RENCANA AS ID_RENCANA,
									HRP.TANGGAL_RENCANA AS TANGGAL_RENCANA,
									HRP.NIK_KERANI_BUAH AS NIK_KERANI_BUAH,
									DRP.ID_BA_AFD_BLOK AS ID_BA_AFD_BLOK,
									DRP.NO_REKAP_BCC AS NO_REKAP_BCC
								FROM
									EBCC.T_HEADER_RENCANA_PANEN HRP 
									LEFT JOIN EBCC.T_DETAIL_RENCANA_PANEN DRP ON HRP.ID_RENCANA = DRP.ID_RENCANA
							) HDP
							LEFT JOIN EBCC.T_HASIL_PANEN HP ON HP.ID_RENCANA = HDP.ID_RENCANA AND HP.NO_REKAP_BCC = HDP.NO_REKAP_BCC
							LEFT JOIN EBCC.T_BLOK TB ON TB.ID_BA_AFD_BLOK = HDP.ID_BA_AFD_BLOK
							LEFT JOIN EBCC.T_AFDELING TA ON TA.ID_BA_AFD = TB.ID_BA_AFD
							LEFT JOIN EBCC.T_BUSSINESSAREA TBA ON TBA.ID_BA = TA.ID_BA
							LEFT JOIN EBCC.T_EMPLOYEE EMP_EBCC ON EMP_EBCC.NIK = HDP.NIK_KERANI_BUAH 
						WHERE
							HP.NO_BCC = '{$ec->ebcc_no_bcc}'";
							
					$query_ebcc = collect( $this->db_mobile_ins->select( $sql_ebcc ) )->first();

					$joindata[$i]['ebcc_nik_kerani_buah'] = $query_ebcc->nik_kerani_buah;
					$joindata[$i]['ebcc_nama_kerani_buah'] = $query_ebcc->emp_name;
					$joindata[$i]['ebcc_no_bcc'] = $query_ebcc->no_bcc;
					$joindata[$i]['ebcc_jml_bm'] = $query_ebcc->ebcc_jml_bm;
					$joindata[$i]['ebcc_jml_bk'] = $query_ebcc->ebcc_jml_bk;
					$joindata[$i]['ebcc_jml_ms'] = $query_ebcc->ebcc_jml_ms;
					$joindata[$i]['ebcc_jml_or'] = $query_ebcc->ebcc_jml_or;
					$joindata[$i]['ebcc_jml_bb'] = $query_ebcc->ebcc_jml_bb;
					$joindata[$i]['ebcc_jml_jk'] = $query_ebcc->ebcc_jml_jk;
					$joindata[$i]['ebcc_jml_ba'] = $query_ebcc->ebcc_jml_ba;
					$joindata[$i]['ebcc_jml_brd'] = $query_ebcc->ebcc_jml_brd;
					$joindata[$i]['ebcc_jjg_panen'] = $query_ebcc->jjg_panen;
					$joindata[$i]['ebcc_status_tph'] = $query_ebcc->status_tph;
					$joindata[$i]['ebcc_keterangan_qrcode'] = $query_ebcc->keterangan_qrcode;

					$summary_data[$summary_code]['ebcc_jml_bm'] = $summary_data[$summary_code]['ebcc_jml_bm'] + $query_ebcc->ebcc_jml_bm;
					$summary_data[$summary_code]['ebcc_jml_bk'] = $summary_data[$summary_code]['ebcc_jml_bk'] + $query_ebcc->ebcc_jml_bk;
					$summary_data[$summary_code]['ebcc_jml_ms'] = $summary_data[$summary_code]['ebcc_jml_ms'] + $query_ebcc->ebcc_jml_ms;
					$summary_data[$summary_code]['ebcc_jml_or'] = $summary_data[$summary_code]['ebcc_jml_or'] + $query_ebcc->ebcc_jml_or;
					$summary_data[$summary_code]['ebcc_jml_bb'] = $summary_data[$summary_code]['ebcc_jml_bb'] + $query_ebcc->ebcc_jml_bb;
					$summary_data[$summary_code]['ebcc_jml_jk'] = $summary_data[$summary_code]['ebcc_jml_jk'] + $query_ebcc->ebcc_jml_jk;
					$summary_data[$summary_code]['ebcc_jml_ba'] = $summary_data[$summary_code]['ebcc_jml_ba'] + $query_ebcc->ebcc_jml_ba;
					$summary_data[$summary_code]['ebcc_jml_brd'] = $summary_data[$summary_code]['ebcc_jml_brd'] + $query_ebcc->ebcc_jml_brd;
					$summary_data[$summary_code]['ebcc_jjg_panen'] = $summary_data[$summary_code]['ebcc_jjg_panen'] + $query_ebcc->jjg_panen;
					$summary_data[$summary_code]['match'] = $summary_data[$summary_code]['match'] + ( intval( $query_ebcc->jjg_panen ) == intval( $ec->val_total_jjg ) ? 1 : 0 );

					if ( intval( $query_ebcc->jjg_panen ) == intval( $ec->val_total_jjg ) ) {
						$summary_data[$summary_code]['akurasi'] = $summary_data[$summary_code]['akurasi'] + abs( intval( $query_ebcc->ebcc_jml_ms ) - intval( $ec->val_jml_3 ) );
					}

					$joindata[$i]['match_status'] = ( intval( $query_ebcc->jjg_panen ) == intval( $ec->val_total_jjg ) ? 'MATCH' : 'NOT MATCH' );
					$akurasi_kualitas_ms =  abs( $ec->val_jml_3 - $query_ebcc->ebcc_jml_ms );
					$joindata[$i]['akurasi_kualitas_ms'] = ( $akurasi_kualitas_ms > 0 ? $akurasi_kualitas_ms : 0 );
				} */
					
				$i++;
			}
		}

		return array(
			"data" => $joindata,
			//"summary" => $summary_data
		);
	}
	
	public function FINDING( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE ) {
		$START_DATE = date( 'Y-m-d', strtotime( $START_DATE ) );
		$END_DATE = $END_DATE ? date( 'Y-m-d', strtotime( $END_DATE ) ) : date( 'Y-m-d', strtotime( $START_DATE ) );
		$where = "";
		$where .= ( $REGION_CODE != "" && $COMP_CODE == "" ) ? " AND FINDING.REGION_CODE = '$REGION_CODE'  ": "";
		$where .= ( $COMP_CODE != "" && $BA_CODE == "" ) ? " AND FINDING.COMP_CODE = '$COMP_CODE'  ": "";
		$where .= ( $BA_CODE != "" && $AFD_CODE == "" ) ? " AND FINDING.WERKS = '$BA_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE == "" ) ? " AND FINDING.WERKS||FINDING.AFD_CODE = '$AFD_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE != "" ) ? " AND FINDING.WERKS||FINDING.AFD_CODE||FINDING.BLOCK_CODE = '$BLOCK_CODE'  ": "";
		$sql = "
			SELECT 
				FINDING_CODE,
				REGION_CODE,
				COMP_CODE,
				WERKS,
				EST_NAME,
				BLOCK_NAME,
				AFD_CODE,
				BLOCK_CODE,
				BLOCK_NAME,
				TANGGAL_TEMUAN,
				CREATOR_EMPLOYEE_NIK,
				CREATOR_EMPLOYEE_FULLNAME,
				CREATOR_EMPLOYEE_POSITION,
				MATURITY_STATUS,
				LAT_FINDING,
				LONG_FINDING,
				CATEGORY_CODE,
				CATEGORY_NAME,
				CATEGORY_ICON,
				FINDING_PRIORITY,
				DUE_DATE,
				PIC_EMPLOYEE_NIK,
				PIC_EMPLOYEE_FULLNAME,
				PIC_EMPLOYEE_POSITION,
				END_TIME,
				RATING_VALUE,
				RATING_MESSAGE,
				UPDATE_TIME,
				PROGRESS,
				FINDING_DESC,
				STATUS
			FROM 
				(
					SELECT 
						FINDING.FINDING_CODE,
						EST.REGION_CODE,
						EST.COMP_CODE,
						EST.EST_CODE,
						FINDING.WERKS,
						EST.EST_NAME,
						FINDING.AFD_CODE,
						BLOCK.BLOCK_CODE,
						BLOCK.BLOCK_NAME,
						FINDING.INSERT_TIME AS TANGGAL_TEMUAN,
						EMP_CREATOR.NIK AS CREATOR_EMPLOYEE_NIK,
						EMP_CREATOR.EMPLOYEE_NAME AS CREATOR_EMPLOYEE_FULLNAME,
						REPLACE( USER_AUTH_CREATOR.USER_ROLE, '_', ' ' ) AS CREATOR_EMPLOYEE_POSITION,
						LAND_USE.MATURITY_STATUS AS MATURITY_STATUS,
						FINDING.LAT_FINDING,
						FINDING.LONG_FINDING,
						CATEGORY.CATEGORY_CODE,
						CATEGORY.CATEGORY_NAME,
						CATEGORY.ICON AS CATEGORY_ICON,
						FINDING.FINDING_PRIORITY,
						FINDING.DUE_DATE,
						EMP_PIC.NIK AS PIC_EMPLOYEE_NIK,
						EMP_PIC.EMPLOYEE_NAME AS PIC_EMPLOYEE_FULLNAME,
						REPLACE( USER_AUTH_PIC.USER_ROLE, '_', ' ' ) AS PIC_EMPLOYEE_POSITION,
						FINDING.END_TIME,
						FINDING.RATING_VALUE,
						FINDING.RATING_MESSAGE,
						FINDING.UPDATE_TIME,
						FINDING.PROGRESS,
						FINDING.FINDING_DESC,
						CASE
							WHEN FINDING.PROGRESS = 100
							THEN 'SELESAI'
							ELSE 'BELUM SELESAI'
						END AS STATUS
					FROM 
						TR_FINDING FINDING
						LEFT JOIN TM_CATEGORY CATEGORY 
							ON CATEGORY.CATEGORY_CODE = FINDING.FINDING_CATEGORY
						LEFT JOIN TAP_DW.TM_BLOCK@DWH_LINK BLOCK
							ON BLOCK.WERKS = FINDING.WERKS
							AND BLOCK.BLOCK_CODE = FINDING.BLOCK_CODE
							AND TRUNC( FINDING.INSERT_TIME ) BETWEEN BLOCK.START_VALID AND BLOCK.END_VALID
						LEFT JOIN TAP_DW.TM_EST@DWH_LINK EST
							ON EST.WERKS = FINDING.WERKS
							AND TRUNC( FINDING.INSERT_TIME ) BETWEEN EST.START_VALID AND EST.END_VALID
						LEFT JOIN MOBILE_INSPECTION.TM_USER_AUTH USER_AUTH_CREATOR 
							ON USER_AUTH_CREATOR.USER_AUTH_CODE = (
								CASE WHEN LENGTH( FINDING.INSERT_USER ) = 3 
									THEN '0' || FINDING.INSERT_USER
									ELSE FINDING.INSERT_USER
								END
							)
						LEFT JOIN (
							SELECT 
								EMPLOYEE_NIK NIK,
								EMPLOYEE_FULLNAME EMPLOYEE_NAME,
								EMPLOYEE_POSITION JOB_CODE,
								EMPLOYEE_JOINDATE as START_DATE,
								CASE 
									WHEN EMPLOYEE_RESIGNDATE IS NULL
									THEN TO_DATE( '99991231', 'RRRRMMDD' )
									ELSE EMPLOYEE_RESIGNDATE
								END as END_DATE
							FROM 
								TAP_DW.TM_EMPLOYEE_HRIS@DWH_LINK 
							UNION ALL
							SELECT 
								NIK, 
								EMPLOYEE_NAME,
								JOB_CODE,
								START_VALID START_DATE,
								CASE
									WHEN RES_DATE IS NOT NULL 
									THEN RES_DATE
									ELSE END_VALID
								END END_DATE
							FROM 
								TAP_DW.TM_EMPLOYEE_SAP@DWH_LINK 
						) EMP_CREATOR 
							ON EMP_CREATOR.NIK = USER_AUTH_CREATOR.EMPLOYEE_NIK
							AND TRUNC( FINDING.INSERT_TIME ) BETWEEN EMP_CREATOR.START_DATE AND EMP_CREATOR.END_DATE
						LEFT JOIN MOBILE_INSPECTION.TM_USER_AUTH USER_AUTH_PIC 
							ON USER_AUTH_PIC.USER_AUTH_CODE = ( 
								CASE WHEN LENGTH( FINDING.ASSIGN_TO ) = 3 
									THEN '0' || FINDING.ASSIGN_TO
									ELSE FINDING.ASSIGN_TO
								END 
							)
						LEFT JOIN MOBILE_INSPECTION.TM_USER_AUTH USER_AUTH_PIC 
							ON USER_AUTH_PIC.USER_AUTH_CODE = (
								CASE WHEN LENGTH( FINDING.ASSIGN_TO ) = 3 
									THEN '0' || FINDING.ASSIGN_TO
									ELSE FINDING.ASSIGN_TO
								END
							)
						LEFT JOIN (
							SELECT 
								EMPLOYEE_NIK NIK,
								EMPLOYEE_FULLNAME EMPLOYEE_NAME,
								EMPLOYEE_POSITION JOB_CODE,
								EMPLOYEE_JOINDATE as START_DATE,
								CASE 
									WHEN EMPLOYEE_RESIGNDATE IS NULL
									THEN TO_DATE( '99991231', 'RRRRMMDD' )
									ELSE EMPLOYEE_RESIGNDATE
								END as END_DATE
							FROM TAP_DW.TM_EMPLOYEE_HRIS@DWH_LINK 
							UNION ALL
							SELECT 
								NIK, 
								EMPLOYEE_NAME,
								JOB_CODE,
								START_VALID START_DATE,
								CASE
									WHEN RES_DATE IS NOT NULL 
									THEN RES_DATE
									ELSE END_VALID
								END END_DATE
							FROM TAP_DW.TM_EMPLOYEE_SAP@DWH_LINK 
						) EMP_PIC 
							ON EMP_PIC.NIK = USER_AUTH_PIC.EMPLOYEE_NIK
							AND TRUNC( FINDING.INSERT_TIME ) BETWEEN EMP_PIC.START_DATE AND EMP_PIC.END_DATE
						LEFT JOIN (
							SELECT 
								WERKS,
								AFD_CODE,
								LAND_USE_CODE BLOCK_CODE,
								BLOCK_NAME,
								MATURITY_STATUS,
								SPMON 
							FROM 
								TAP_DW.TR_HS_LAND_USE@DWH_LINK
							WHERE 
								SPMON BETWEEN TRUNC ( ADD_MONTHS ( SYSDATE, -1 ), 'YEAR' ) AND LAST_DAY (ADD_MONTHS ( SYSDATE, -1 ) )
							GROUP BY 
								WERKS, 
								AFD_CODE, 
								LAND_USE_CODE, 
								BLOCK_NAME, 
								MATURITY_STATUS, 
								SPMON 
							UNION
							SELECT 
								WERKS,
								AFD_CODE,
								LAND_USE_CODE BLOCK_CODE,
								BLOCK_NAME,
								MATURITY_STATUS,
								ADD_MONTHS(SPMON,1) SPMON 
							FROM 
								TAP_DW.TR_HS_LAND_USE@DWH_LINK
							WHERE
								SPMON = ( SELECT MAX( SPMON ) FROM TAP_DW.TR_HS_LAND_USE@DWH_LINK )
							GROUP BY 
								WERKS, 
								AFD_CODE, 
								LAND_USE_CODE, 
								BLOCK_NAME, 
								MATURITY_STATUS, 
								SPMON 
						) LAND_USE
							ON FINDING.WERKS = LAND_USE.WERKS
							AND LAND_USE.AFD_CODE = FINDING.AFD_CODE
							AND LAND_USE.BLOCK_CODE = FINDING.BLOCK_CODE
							AND SPMON = TRUNC( LAST_DAY( FINDING.INSERT_TIME ) ) 
				) FINDING
			WHERE
				1 = 1
				AND TRUNC( FINDING.TANGGAL_TEMUAN ) BETWEEN TRUNC( TO_DATE( '$START_DATE', 'RRRR-MM-DD' ) ) AND TRUNC( TO_DATE( '$END_DATE', 'RRRR-MM-DD' ) )
				$where
			ORDER BY
				FINDING.STATUS ASC,
				FINDING.TANGGAL_TEMUAN ASC
		";

		$get = $this->db_mobile_ins->select( $sql );
		return $get;
	}

	public function INSPECTION_BARIS( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE ) {
		$START_DATE = date( 'Y-m-d', strtotime( $START_DATE ) );
		$END_DATE = date( 'Y-m-d', strtotime( $END_DATE ) );
		$where = "";
		$where .= ( $REGION_CODE != "" && $COMP_CODE == "" ) ? " AND est.REGION_CODE = '$REGION_CODE'  ": "";
		$where .= ( $COMP_CODE != "" && $BA_CODE == "" ) ? " AND est.COMP_CODE = '$COMP_CODE'  ": "";
		$where .= ( $BA_CODE != "" && $AFD_CODE == "" ) ? " AND est.WERKS = '$BA_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE == "" ) ? " AND hd.WERKS||hd.AFD_CODE = '$AFD_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE != "" ) ? " AND hd.WERKS||hd.AFD_CODE||hd.BLOCK_CODE = '$BLOCK_CODE'  ": "";
		$sql = ( "
			SELECT 
				hd.block_inspection_code \"Kode Inspeksi\", 
				hd.werks \"Kode BA\", 
				est.est_name \"Business Area\", 
				hd.afd_code \"AFD\", 
				hd.block_code \"Kode Block\", 
				sub_block.block_name \"Block Deskripsi\", 
				TO_CHAR (
					hd.inspection_date, 'YYYY-MM-DD'
				) \"Tanggal Inspeksi\", 
				TO_CHAR (
					TRUNC (
						MOD (
							(
								hd.end_inspection - hd.start_inspection
							) * 24 * 60 * 60, 
							3600
						) / 60
					), 
					'FM00'
				) || ':' || TO_CHAR (
					MOD (
						(
							hd.end_inspection - hd.start_inspection
						) * 24 * 60 * 60, 
						60
					), 
					'FM00'
				) \"Lama Inspeksi\", 
				hd.areal \"Baris\", 
				user_auth.employee_nik \"NIK Reporter\", 
				emp.employee_name \"Nama Reporter\", 
				REPLACE (user_auth.user_role, '_', ' ') \"Jabatan Reporter\", 
				TO_CHAR (hd.inspection_date, 'yyyy.mm') \"Periode\", 
				lu.maturity_status \"Maturity Status\", 
				hd.lat_start_inspection \"Lat Start\", 
				hd.long_start_inspection \"Long Start\", 
				\"Pokok Panen\", 
				\"Buah Tinggal\", 
				\"Brondolan di Piringan\", 
				\"Brondolan di TPH\", 
				\"Pokok Tidak di Pupuk\", 
				\"Piringan\", 
				\"Pasar Pikul\", 
				\"TPH\", 
				\"Gawangan\", 
				\"Prunning\", 
				\"Titi Panen\", 
				\"Sistem Penaburan\", 
				\"Kondisi Pupuk\", 
				\"Kastrasi\", 
				\"Sanitasi\", 
				\"Nilai Piringan\", 
				\"Nilai Pasar Pikul\", 
				\"Nilai TPH\", 
				\"Nilai Gawangan\", 
				\"Nilai Prunning\" 
			FROM 
				mobile_inspection.tr_block_inspection_h hd 
				LEFT JOIN tap_dw.tm_est@dwh_link est ON hd.werks = est.werks 
				LEFT JOIN tap_dw.tm_sub_block@dwh_link sub_block ON hd.werks = sub_block.werks 
				AND hd.afd_code = sub_block.afd_code 
				AND hd.block_code = sub_block.sub_block_code 
				AND hd.inspection_date BETWEEN sub_block.start_valid 
				AND sub_block.end_valid 
				LEFT JOIN mobile_inspection.tm_user_auth user_auth ON hd.insert_user = user_auth.user_auth_code 
				LEFT JOIN (
					SELECT 
						nik, 
						employee_name, 
						job_code, 
						start_valid, 
						CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid 
					FROM 
						tap_dw.tm_employee_sap@dwh_link 
					UNION 
					SELECT 
						employee_nik, 
						employee_fullname, 
						employee_position, 
						employee_joindate, 
						CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD') ELSE employee_resigndate END employee_regisndate 
					FROM 
						tap_dw.tm_employee_hris@dwh_link
				) emp ON user_auth.employee_nik = emp.nik 
				AND inspection_date BETWEEN emp.start_valid 
				AND emp.end_valid 
				LEFT JOIN (
					SELECT 
						werks, 
						afd_code, 
						land_use_code, 
						land_use_name, 
						spmon, 
						maturity_status 
					FROM 
						tap_dw.tr_hs_land_use@dwh_link 
					WHERE 
						spmon >= TRUNC (SYSDATE - 1, 'YYYY') 
					UNION ALL 
					SELECT 
						werks, 
						afd_code, 
						land_use_code, 
						land_use_name, 
						LAST_DAY (
							ADD_MONTHS (spmon, 1)
						) spmon, 
						maturity_status 
					FROM 
						tap_dw.tr_hs_land_use@dwh_link 
					WHERE 
						spmon = (
							SELECT 
								MAX (spmon) 
							FROM 
								tap_dw.tr_hs_land_use@dwh_link
						)
				) lu ON hd.werks = lu.werks 
				AND hd.afd_code = lu.afd_code 
				AND hd.block_code = lu.land_use_code 
				AND TRUNC (
					LAST_DAY (hd.inspection_date)
				) = lu.spmon 
				LEFT JOIN (
					SELECT 
						block_inspection_code, 
						\"Pokok Panen\", 
						\"Buah Tinggal\", 
						\"Brondolan di Piringan\", 
						\"Brondolan di TPH\", 
						\"Pokok Tidak di Pupuk\", 
						\"Piringan\", 
						\"Pasar Pikul\", 
						\"TPH\", 
						\"Gawangan\", 
						\"Prunning\", 
						\"Titi Panen\", 
						\"Sistem Penaburan\", 
						\"Kondisi Pupuk\", 
						\"Kastrasi\", 
						\"Sanitasi\", 
						\"Nilai Piringan\", 
						\"Nilai Pasar Pikul\", 
						\"Nilai TPH\", 
						\"Nilai Gawangan\", 
						\"Nilai Prunning\" 
					FROM 
						(
							SELECT 
								block_inspection_code, 
								content_name, 
								VALUE 
							FROM 
								(
									SELECT 
										1 sort_by, 
										master.*, 
										detail.VALUE 
									FROM 
										(
											SELECT 
												hd.block_inspection_code, 
												cont.* 
											FROM 
												mobile_inspection.tr_block_inspection_h hd 
												LEFT JOIN tm_content cont ON 1 = 1 
											WHERE 
												group_category = 'INSPEKSI'
											AND hd.werks = nvl('$BA_CODE',hd.werks)
										) master 
										LEFT JOIN (
											SELECT 
												det.block_inspection_code, 
												det.content_inspection_code, 
												det.VALUE, 
												TO_NUMBER (
													NVL (
														TO_CHAR (cont_lab.label_score), 
														VALUE
													)
												) score 
											FROM 
												mobile_inspection.tr_block_inspection_d det 
												LEFT JOIN mobile_inspection.tm_content_label cont_lab ON det.content_inspection_code = cont_lab.content_code 
												AND det.VALUE = cont_lab.label_name
										) detail ON master.block_inspection_code = detail.block_inspection_code 
										AND master.content_code = detail.content_inspection_code 
									UNION 
									SELECT 
										2 sort_by, 
										master.block_inspection_code, 
										master.bobot, 
										master.category, 
										master.content_code, 
										'Nilai ' || master.content_name content_name, 
										master.content_type, 
										master.flag_type, 
										master.group_category, 
										master.tbm0, 
										master.tbm1, 
										master.tbm2, 
										master.tbm3, 
										master.tm, 
										master.uom, 
										master.urutan, 
										TO_CHAR (detail.score) score 
									FROM 
										(
											SELECT 
												hd.block_inspection_code, 
												cont.* 
											FROM 
												mobile_inspection.tr_block_inspection_h hd 
												LEFT JOIN tm_content cont ON 1 = 1 
												INNER JOIN (
													SELECT 
														DISTINCT content_code 
													FROM 
														mobile_inspection.tm_content_label
												) cont_lab ON cont.content_code = cont_lab.content_code 
											WHERE 
												cont.group_category = 'INSPEKSI' 
												AND cont.bobot > 0
												AND hd.werks = nvl('$BA_CODE',hd.werks)
										) master 
										LEFT JOIN (
											SELECT 
												det.block_inspection_code, 
												det.content_inspection_code, 
												det.VALUE, 
												TO_NUMBER (
													NVL (
														TO_CHAR (cont_lab.label_score), 
														VALUE
													)
												) score 
											FROM 
												mobile_inspection.tm_content_label cont_lab 
												LEFT JOIN mobile_inspection.tr_block_inspection_d det ON cont_lab.content_code = det.content_inspection_code 
												AND cont_lab.label_name = det.VALUE
										) detail ON master.block_inspection_code = detail.block_inspection_code 
										AND master.content_code = detail.content_inspection_code
								)
								) PIVOT (
							MAX (VALUE) FOR content_name IN (
								'Pokok Panen' AS \"Pokok Panen\", 'Buah Tinggal' AS \"Buah Tinggal\", 
								'Brondolan di Piringan' AS \"Brondolan di Piringan\", 
								'Brondolan di TPH' AS \"Brondolan di TPH\", 
								'Pokok Tidak di Pupuk' AS \"Pokok Tidak di Pupuk\", 
								'Piringan' AS \"Piringan\", 'Pasar Pikul' AS \"Pasar Pikul\", 
								'TPH' AS \"TPH\", 'Gawangan' AS \"Gawangan\", 
								'Prunning' AS \"Prunning\", 'Titi Panen' AS \"Titi Panen\", 
								'Sistem Penaburan' AS \"Sistem Penaburan\", 
								'Kondisi Pupuk' AS \"Kondisi Pupuk\", 
								'Kastrasi' AS \"Kastrasi\", 'Sanitasi' AS \"Sanitasi\", 
								'Nilai Piringan' AS \"Nilai Piringan\", 
								'Nilai Pasar Pikul' AS \"Nilai Pasar Pikul\", 
								'Nilai TPH' AS \"Nilai TPH\", 'Nilai Gawangan' AS \"Nilai Gawangan\", 
								'Nilai Prunning' AS \"Nilai Prunning\"
							)
						)
				) detail ON hd.block_inspection_code = detail.block_inspection_code 
				LEFT JOIN (
					SELECT 
						block_inspection_code 
					FROM 
						mobile_inspection.tr_inspection_genba 
					GROUP BY 
						block_inspection_code
				) genba ON hd.block_inspection_code = genba.block_inspection_code 
			WHERE
				genba.block_inspection_code IS NULL
				AND TRUNC( hd.INSPECTION_DATE ) BETWEEN TRUNC( TO_DATE( '$START_DATE', 'RRRR-MM-DD' ) ) AND TRUNC( TO_DATE( '$END_DATE', 'RRRR-MM-DD' ) )
				$where
			ORDER BY 
				hd.inspection_date ASC,
				emp.employee_name ASC
		" );
		$get = $this->db_mobile_ins->select( $sql );

		return $get;
	}

	public function INSPECTION_HEADER( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE ) {
		$START_DATE = date( 'Y-m-d', strtotime( $START_DATE ) );
		$END_DATE = date( 'Y-m-d', strtotime( $END_DATE ) );
		$where = "";
		$where .= ( $REGION_CODE != "" && $COMP_CODE == "" ) ? " AND est.REGION_CODE = '$REGION_CODE'  ": "";
		$where .= ( $COMP_CODE != "" && $BA_CODE == "" ) ? " AND est.COMP_CODE = '$COMP_CODE'  ": "";
		$where .= ( $BA_CODE != "" && $AFD_CODE == "" ) ? " AND est.WERKS = '$BA_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE == "" ) ? " AND hd.WERKS||hd.AFD_CODE = '$AFD_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE != "" ) ? " AND hd.WERKS||hd.AFD_CODE||hd.BLOCK_CODE = '$BLOCK_CODE'  ": "";
		$sql = ( "
			SELECT 
				\"NIK Reporter\", 
				\"Nama Reporter\", 
				\"Jabatan Reporter\", 
				\"Kode BA\", 
				\"Business Area\", 
				afd, 
				\"Kode Block\", 
				\"Block Deskripsi\", 
				\"Maturity Status\", 
				\"Tanggal Inspeksi\", 
				\"Jumlah Baris\", 
				\"Periode\", 
				\"Lama Inspeksi\", 
				\"Pokok Panen\", 
				\"Buah Tinggal\", 
				\"Brondolan di Piringan\", 
				\"Brondolan di TPH\", 
				\"Pokok Tidak di Pupuk\", 
				\"Rata-rata Sistem Penaburan\", 
				\"Rata-rata Kondisi Pupuk\", 
				\"Rata-rata Piringan\", 
				\"Rata-rata Pasar Pikul\", 
				\"Rata-rata TPH\", 
				\"Rata-rata Gawangan\", 
				\"Rata-rata Prunning\", 
				\"Rata-rata Titi Panen\", 
				\"Rata-rata Kastrasi\", 
				\"Rata-rata Sanitasi\", 
				\"Bobot Piringan\", 
				\"Bobot Pasar Pikul\", 
				\"Bobot TPH\", 
				\"Bobot Gawangan\", 
				\"Bobot Prunning\", 
				\"Avg Bobot Piringan\" AS \"Rata-rata x Bobot Piringan\", 
				\"Avg Pasar Pikul\" AS \"Rata-rata x Pasar Pikul\", 
				\"Avg Bobot TPH\" AS \"Rata-rata x Bobot TPH\", 
				\"Avg Bobot Gawangan\" AS \"Rata-rata x Bobot Gawangan\", 
				\"Avg Bobot Prunning\" AS \"Rata-rata x Bobot Prunning\", 
				ROUND (nilai, 2) \"Nilai Inspeksi\", 
				grade \"Hasil Inspeksi\" 
			FROM 
				(
					SELECT 
						\"NIK Reporter\", 
						\"Nama Reporter\", 
						REPLACE (\"Jabatan Reporter\", '_', ' ') AS \"Jabatan Reporter\", 
						\"Kode BA\", 
						\"Business Area\", 
						\"AFD\", 
						\"Kode Block\", 
						\"Block Deskripsi\", 
						\"Maturity Status\", 
						\"Tanggal Inspeksi\", 
						COUNT (*) \"Jumlah Baris\", 
						\"Periode\", 
						TO_CHAR (
							TRUNC (
								MOD (
									SUM (inspection_second), 
									3600
								) / 60
							), 
							'FM00'
						) || ':' || TO_CHAR (
							MOD (
								SUM (inspection_second), 
								60
							), 
							'FM00'
						) \"Lama Inspeksi\", 
						SUM (\"Pokok Panen_VAL\") \"Pokok Panen\", 
						SUM (\"Buah Tinggal_VAL\") \"Buah Tinggal\", 
						SUM (\"Brondolan di Piringan_VAL\") \"Brondolan di Piringan\", 
						SUM (\"Brondolan di TPH_VAL\") \"Brondolan di TPH\", 
						SUM (\"Pokok Tidak di Pupuk_VAL\") \"Pokok Tidak di Pupuk\", 
						AVG (\"Sistem Penaburan_VAL\") \"Rata-rata Sistem Penaburan\", 
						AVG (\"Kondisi Pupuk_VAL\") \"Rata-rata Kondisi Pupuk\", 
						AVG (\"Piringan_VAL\") \"Rata-rata Piringan\", 
						AVG (\"Pasar Pikul_VAL\") \"Rata-rata Pasar Pikul\", 
						AVG (\"TPH_VAL\") \"Rata-rata TPH\", 
						AVG (\"Gawangan_VAL\") \"Rata-rata Gawangan\", 
						AVG (\"Prunning_VAL\") \"Rata-rata Prunning\", 
						AVG (\"Titi Panen_VAL\") \"Rata-rata Titi Panen\", 
						AVG (\"Kastrasi_VAL\") \"Rata-rata Kastrasi\", 
						AVG (\"Sanitasi_VAL\") \"Rata-rata Sanitasi\", 
						MAX (\"Piringan_BOBOT\") \"Bobot Piringan\", 
						MAX (\"Pasar Pikul_BOBOT\") \"Bobot Pasar Pikul\", 
						MAX (\"TPH_BOBOT\") \"Bobot TPH\", 
						MAX (\"Gawangan_BOBOT\") \"Bobot Gawangan\", 
						MAX (\"Prunning_BOBOT\") \"Bobot Prunning\", 
						AVG (\"Piringan_VAL\") * MAX (\"Piringan_BOBOT\") \"Avg Bobot Piringan\", 
						AVG (\"Pasar Pikul_VAL\") * MAX (\"Pasar Pikul_BOBOT\") \"Avg Pasar Pikul\", 
						AVG (\"TPH_VAL\") * MAX (\"TPH_BOBOT\") \"Avg Bobot TPH\", 
						AVG (\"Gawangan_VAL\") * MAX (\"Gawangan_BOBOT\") \"Avg Bobot Gawangan\", 
						AVG (\"Prunning_VAL\") * MAX (\"Prunning_BOBOT\") \"Avg Bobot Prunning\", 
						(
							AVG (\"Piringan_VAL\") * MAX (\"Piringan_BOBOT\") + AVG (\"Pasar Pikul_VAL\") * MAX (\"Pasar Pikul_BOBOT\") + AVG (\"TPH_VAL\") * MAX (\"TPH_BOBOT\") + AVG (\"Gawangan_VAL\") * MAX (\"Gawangan_BOBOT\") + AVG (\"Prunning_VAL\") * MAX (\"Prunning_BOBOT\")
						) / (
							MAX (\"Piringan_BOBOT\") + MAX (\"Pasar Pikul_BOBOT\") + MAX (\"TPH_BOBOT\") + MAX (\"Gawangan_BOBOT\") + MAX (\"Prunning_BOBOT\")
						) nilai 
					FROM 
						(
							SELECT 
								hd.block_inspection_code, 
								user_auth.employee_nik \"NIK Reporter\", 
								emp.employee_name \"Nama Reporter\", 
								user_auth.user_role \"Jabatan Reporter\", 
								hd.werks \"Kode BA\", 
								est.est_name \"Business Area\", 
								hd.afd_code \"AFD\", 
								hd.block_code \"Kode Block\", 
								sub_block.block_name \"Block Deskripsi\", 
								lu.maturity_status \"Maturity Status\", 
								TO_CHAR (
									hd.inspection_date, 'YYYY-MM-DD'
								) \"Tanggal Inspeksi\", 
								TO_CHAR (hd.inspection_date, 'yyyy.mm') \"Periode\", 
								SUM (
									(
										hd.end_inspection - hd.start_inspection
									) * 24 * 60 * 60
								) inspection_second 
							FROM 
								mobile_inspection.tr_block_inspection_h hd 
								LEFT JOIN tap_dw.tm_est@dwh_link est ON hd.werks = est.werks 
								LEFT JOIN tap_dw.tm_sub_block@dwh_link sub_block ON hd.werks = sub_block.werks 
								AND hd.afd_code = sub_block.afd_code 
								AND hd.block_code = sub_block.sub_block_code 
								AND hd.inspection_date BETWEEN sub_block.start_valid 
								AND sub_block.end_valid 
								LEFT JOIN mobile_inspection.tm_user_auth user_auth ON hd.insert_user = user_auth.user_auth_code 
								LEFT JOIN (
									SELECT 
										nik, 
										employee_name, 
										job_code, 
										start_valid, 
										CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid 
									FROM 
										tap_dw.tm_employee_sap@dwh_link 
									UNION 
									SELECT 
										employee_nik, 
										employee_fullname, 
										employee_position, 
										employee_joindate, 
										CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD') ELSE employee_resigndate END employee_regisndate 
									FROM 
										tap_dw.tm_employee_hris@dwh_link
								) emp ON user_auth.employee_nik = emp.nik 
								AND inspection_date BETWEEN emp.start_valid 
								AND emp.end_valid 
								LEFT JOIN (
									SELECT 
										werks, 
										afd_code, 
										land_use_code, 
										land_use_name, 
										spmon, 
										maturity_status 
									FROM 
										tap_dw.tr_hs_land_use@dwh_link 
									WHERE 
										spmon >= TRUNC (SYSDATE - 1, 'YYYY') 
									UNION ALL 
									SELECT 
										werks, 
										afd_code, 
										land_use_code, 
										land_use_name, 
										LAST_DAY (
											ADD_MONTHS (spmon, 1)
										) spmon, 
										maturity_status 
									FROM 
										tap_dw.tr_hs_land_use@dwh_link 
									WHERE 
										spmon = (
											SELECT 
												MAX (spmon) 
											FROM 
												tap_dw.tr_hs_land_use@dwh_link
										)
								) lu ON hd.werks = lu.werks 
								AND hd.afd_code = lu.afd_code 
								AND hd.block_code = lu.land_use_code 
								AND TRUNC (
									LAST_DAY (hd.inspection_date)
								) = lu.spmon 
								LEFT JOIN (
									SELECT 
										block_inspection_code 
									FROM 
										mobile_inspection.tr_inspection_genba 
									GROUP BY 
										block_inspection_code
								) genba ON hd.block_inspection_code = genba.block_inspection_code 
							WHERE
								genba.block_inspection_code IS NULL
								AND TRUNC( hd.INSPECTION_DATE ) BETWEEN TRUNC( TO_DATE( '$START_DATE', 'RRRR-MM-DD' ) ) AND TRUNC( TO_DATE( '$END_DATE', 'RRRR-MM-DD' ) )
								AND hd.werks = nvl('$BA_CODE',hd.werks)
								$where
							GROUP BY 
								hd.block_inspection_code, 
								user_auth.employee_nik, 
								emp.employee_name, 
								user_auth.user_role, 
								hd.werks, 
								est.est_name, 
								hd.afd_code, 
								hd.block_code, 
								sub_block.block_name, 
								lu.maturity_status, 
								TO_CHAR (
									hd.inspection_date, 'YYYY-MM-DD'
								), 
								TO_CHAR (hd.inspection_date, 'yyyy.mm')
						) hd 
						LEFT JOIN (
							SELECT 
								* 
							FROM 
								(
									SELECT 
										master.werks, 
										master.afd_code, 
										master.block_code, 
										block_inspection_code, 
										content_name, 
										CASE WHEN CASE WHEN lu.maturity_status = 'TBM 0' THEN tbm0 WHEN lu.maturity_status = 'TBM 1' THEN tbm1 WHEN lu.maturity_status = 'TBM 2' THEN tbm2 WHEN lu.maturity_status = 'TBM 3' THEN tbm3 WHEN lu.maturity_status = 'TM' THEN tm END = 'NO' THEN NULL ELSE bobot END bobot, 
										CASE WHEN CASE WHEN lu.maturity_status = 'TBM 0' THEN tbm0 WHEN lu.maturity_status = 'TBM 1' THEN tbm1 WHEN lu.maturity_status = 'TBM 2' THEN tbm2 WHEN lu.maturity_status = 'TBM 3' THEN tbm3 WHEN lu.maturity_status = 'TM' THEN tm END = 'NO' THEN NULL ELSE VALUE END VALUE 
									FROM 
										(
											SELECT 
												1 sort_by, 
												master.*, 
												detail.VALUE 
											FROM 
												(
													SELECT 
														hd.block_inspection_code, 
														hd.werks, 
														hd.afd_code, 
														hd.block_code, 
														hd.inspection_date, 
														cont.* 
													FROM 
														mobile_inspection.tr_block_inspection_h hd 
														LEFT JOIN tm_content cont ON 1 = 1 
														AND cont.content_code NOT IN (
															SELECT 
																DISTINCT content_code 
															FROM 
																mobile_inspection.tm_content_label
														) 
													WHERE 
														1 = 1 
														AND group_category = 'INSPEKSI'
														AND hd.werks = nvl('$BA_CODE',hd.werks)
												) master 
												LEFT JOIN (
													SELECT 
														det.block_inspection_code, 
														det.content_inspection_code, 
														det.VALUE, 
														TO_NUMBER (
															NVL (
																TO_CHAR (cont_lab.label_score), 
																VALUE
															)
														) score 
													FROM 
														mobile_inspection.tr_block_inspection_d det 
														LEFT JOIN mobile_inspection.tm_content_label cont_lab ON det.content_inspection_code = cont_lab.content_code 
														AND det.VALUE = cont_lab.label_name
												) detail ON master.block_inspection_code = detail.block_inspection_code 
												AND master.content_code = detail.content_inspection_code 
											UNION 
											SELECT 
												2 sort_by, 
												master.block_inspection_code, 
												master.werks, 
												master.afd_code, 
												master.block_code, 
												master.inspection_date, 
												master.bobot, 
												master.category, 
												master.content_code, 
												master.content_name content_name, 
												master.content_type, 
												master.flag_type, 
												master.group_category, 
												master.tbm0, 
												master.tbm1, 
												master.tbm2, 
												master.tbm3, 
												master.tm, 
												master.uom, 
												master.urutan, 
												TO_CHAR (detail.score) VALUE 
											FROM 
												(
													SELECT 
														hd.block_inspection_code, 
														hd.werks, 
														hd.afd_code, 
														hd.block_code, 
														hd.inspection_date, 
														cont.* 
													FROM 
														mobile_inspection.tr_block_inspection_h hd 
														LEFT JOIN tm_content cont ON 1 = 1 
														INNER JOIN (
															SELECT 
																DISTINCT content_code 
															FROM 
																mobile_inspection.tm_content_label
														) cont_lab ON cont.content_code = cont_lab.content_code 
													WHERE 
														1 = 1 
														AND cont.group_category = 'INSPEKSI'
														AND hd.werks = nvl('$BA_CODE',hd.werks)
												) master 
												LEFT JOIN (
													SELECT 
														det.block_inspection_code, 
														det.content_inspection_code, 
														det.VALUE, 
														TO_NUMBER (
															NVL (
																TO_CHAR (cont_lab.label_score), 
																VALUE
															)
														) score 
													FROM 
														mobile_inspection.tm_content_label cont_lab 
														LEFT JOIN mobile_inspection.tr_block_inspection_d det ON cont_lab.content_code = det.content_inspection_code 
														AND cont_lab.label_name = det.VALUE
												) detail ON master.block_inspection_code = detail.block_inspection_code 
												AND master.content_code = detail.content_inspection_code
										) master 
										LEFT JOIN (
											SELECT 
												werks, 
												afd_code, 
												land_use_code, 
												land_use_name, 
												spmon, 
												maturity_status 
											FROM 
												tap_dw.tr_hs_land_use@dwh_link lu
											WHERE 
												spmon >= TRUNC (SYSDATE - 1, 'YYYY') 
											AND lu.werks = nvl('$BA_CODE',lu.werks)
											UNION ALL 
											SELECT 
												werks, 
												afd_code, 
												land_use_code, 
												land_use_name, 
												LAST_DAY (
													ADD_MONTHS (spmon, 1)
												) spmon, 
												maturity_status 
											FROM 
												tap_dw.tr_hs_land_use@dwh_link lu 
											WHERE 
												spmon = (
													SELECT 
														MAX (spmon) 
													FROM 
														tap_dw.tr_hs_land_use@dwh_link
												)
											AND lu.werks = nvl('$BA_CODE',lu.werks)
										) lu ON master.werks = lu.werks 
										AND master.afd_code = lu.afd_code 
										AND master.block_code = lu.land_use_code 
										AND TRUNC (
											LAST_DAY (master.inspection_date)
										) = lu.spmon
								) PIVOT (
									MAX (bobot) AS bobot, 
									SUM (VALUE) AS val FOR content_name IN (
										'Pokok Panen' AS \"Pokok Panen\", 'Buah Tinggal' AS \"Buah Tinggal\", 
										'Brondolan di Piringan' AS \"Brondolan di Piringan\", 
										'Brondolan di TPH' AS \"Brondolan di TPH\", 
										'Pokok Tidak di Pupuk' AS \"Pokok Tidak di Pupuk\", 
										'Titi Panen' AS \"Titi Panen\", 'Sistem Penaburan' AS \"Sistem Penaburan\", 
										'Kondisi Pupuk' AS \"Kondisi Pupuk\", 
										'Kastrasi' AS \"Kastrasi\", 'Sanitasi' AS \"Sanitasi\", 
										'Piringan' AS \"Piringan\", 'Pasar Pikul' AS \"Pasar Pikul\", 
										'TPH' AS \"TPH\", 'Gawangan' AS \"Gawangan\", 
										'Prunning' AS \"Prunning\"
									)
								)
						) det ON hd.block_inspection_code = det.block_inspection_code 
					GROUP BY 
						\"NIK Reporter\", 
						\"Nama Reporter\", 
						\"Jabatan Reporter\", 
						\"Kode BA\", 
						\"Business Area\", 
						\"AFD\", 
						\"Kode Block\", 
						\"Block Deskripsi\", 
						\"Maturity Status\", 
						\"Tanggal Inspeksi\", 
						\"Periode\"
				) 
				LEFT JOIN mobile_inspection.tm_kriteria ON ROUND (nilai, 2) BETWEEN batas_bawah 
				AND batas_atas 
			ORDER BY 
				\"Tanggal Inspeksi\" ASC, 
				\"Nama Reporter\" ASC
		" );
		$get = $this->db_mobile_ins->select( $sql );

		return $get;
	}

	public function INSPECTION_GENBA( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE ) {
		$START_DATE = date( 'Y-m-d', strtotime( $START_DATE ) );
		$END_DATE = date( 'Y-m-d', strtotime( $END_DATE ) );
		$where = "";
		$where .= ( $REGION_CODE != "" && $COMP_CODE == "" ) ? " AND est.REGION_CODE = '$REGION_CODE'  ": "";
		$where .= ( $COMP_CODE != "" && $BA_CODE == "" ) ? " AND est.COMP_CODE = '$COMP_CODE'  ": "";
		$where .= ( $BA_CODE != "" && $AFD_CODE == "" ) ? " AND est.WERKS = '$BA_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE == "" ) ? " AND hd.WERKS||hd.AFD_CODE = '$AFD_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE != "" ) ? " AND hd.WERKS||hd.AFD_CODE||hd.BLOCK_CODE = '$BLOCK_CODE'  ": "";
		$sql = "
			SELECT 
				genba.block_inspection_code \"Kode Inspeksi\", 
				\"Kode BA\", 
				\"Business Area\", 
				\"AFD\", 
				\"Kode Block\", 
				\"Block Deskripsi\", 
				\"Tanggal Inspeksi\", 
				\"Lama Inspeksi\", 
				\"Baris\", 
				\"NIK Reporter\", 
				\"Nama Reporter\", 
				\"Jabatan Reporter\", 
				\"Periode\", 
				user_auth.employee_nik \"NIK Participant\", 
				emp.employee_name \"Nama Participant\", 
				REPLACE(user_auth.user_role, '_', ' ') \"Jabatan Participant\" 
			FROM 
				(SELECT block_inspection_code, genba_user FROM mobile_inspection.tr_inspection_genba
				  UNION
				  SELECT hd.block_inspection_code, insert_user
					FROM mobile_inspection.tr_block_inspection_h hd JOIN mobile_inspection.tr_inspection_genba genba
							ON hd.block_inspection_code = genba.block_inspection_code
				   WHERE 1 = 1 AND TRUNC( hd.inspection_date ) BETWEEN TRUNC( TO_DATE( '$START_DATE', 'RRRR-MM-DD' ) ) AND TRUNC( TO_DATE( '$END_DATE', 'RRRR-MM-DD' ))
				   AND hd.werks = nvl('$BA_CODE',hd.werks)) genba 
				INNER JOIN (
					SELECT 
						hd.block_inspection_code \"Kode Inspeksi\", 
						hd.werks \"Kode BA\", 
						est.est_name \"Business Area\", 
						hd.afd_code \"AFD\", 
						hd.block_code \"Kode Block\", 
						sub_block.block_name \"Block Deskripsi\", 
						hd.inspection_date, 
						TO_CHAR (
							hd.inspection_date, 'YYYY-MM-DD'
						) \"Tanggal Inspeksi\", 
						TO_CHAR (
							TRUNC (
								MOD (
									(
										hd.end_inspection - hd.start_inspection
									) * 24 * 60 * 60, 
									3600
								) / 60
							), 
							'FM00'
						) || ':' || TO_CHAR (
							MOD (
								(
									hd.end_inspection - hd.start_inspection
								) * 24 * 60 * 60, 
								60
							), 
							'FM00'
						) \"Lama Inspeksi\", 
						hd.areal \"Baris\", 
						user_auth.employee_nik \"NIK Reporter\", 
						emp.employee_name \"Nama Reporter\", 
						REPLACE(user_auth.user_role, '_', ' ') \"Jabatan Reporter\", 
						TO_CHAR (hd.inspection_date, 'yyyy.mm') \"Periode\" 
					FROM 
						mobile_inspection.tr_block_inspection_h hd 
						LEFT JOIN tap_dw.tm_est@DWH_LINK est ON hd.werks = est.werks 
						LEFT JOIN tap_dw.tm_sub_block@DWH_LINK sub_block ON hd.werks = sub_block.werks 
						AND hd.afd_code = sub_block.afd_code 
						AND hd.block_code = sub_block.sub_block_code 
						AND hd.inspection_date BETWEEN sub_block.start_valid 
						AND sub_block.end_valid 
						LEFT JOIN mobile_inspection.tm_user_auth user_auth ON hd.insert_user = user_auth.user_auth_code 
						LEFT JOIN (
							SELECT 
								nik, 
								employee_name, 
								job_code, 
								start_valid, 
								CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid 
							FROM 
								tap_dw.tm_employee_sap@DWH_LINK 
							UNION 
							SELECT 
								employee_nik, 
								employee_fullname, 
								employee_position, 
								employee_joindate, 
								CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('9999-12-31', 'RRRR-MM-DD') ELSE employee_resigndate END employee_regisndate 
							FROM 
								tap_dw.tm_employee_hris@DWH_LINK
						) emp ON user_auth.employee_nik = emp.nik 
					WHERE
						1 = 1
						AND TRUNC( hd.inspection_date ) BETWEEN TRUNC( TO_DATE( '$START_DATE', 'RRRR-MM-DD' ) ) AND TRUNC( TO_DATE( '$END_DATE', 'RRRR-MM-DD' ) )
						$where
				) inspeksi ON genba.block_inspection_code = inspeksi.\"Kode Inspeksi\" 
				LEFT JOIN mobile_inspection.tm_user_auth user_auth ON genba.genba_user = user_auth.user_auth_code 
				LEFT JOIN (
					SELECT 
						nik, 
						employee_name, 
						job_code, 
						start_valid, 
						CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid 
					FROM 
						tap_dw.tm_employee_sap@DWH_LINK 
					UNION 
					SELECT 
						employee_nik, 
						employee_fullname, 
						employee_position, 
						employee_joindate, 
						CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('9999-12-31', 'RRRR-MM-DD') ELSE employee_resigndate END employee_regisndate 
					FROM 
						tap_dw.tm_employee_hris@DWH_LINK
				) emp ON user_auth.employee_nik = emp.nik AND inspeksi.inspection_date BETWEEN emp.start_valid AND emp.end_valid
			ORDER BY 
				\"Tanggal Inspeksi\" ASC, 
				\"Nama Reporter\" ASC
		";

		
		$get = $this->db_mobile_ins->select( $sql );

		return $get;
	}

	public function INSPECTION_GENBA_HEADER( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE ) {
		$START_DATE = date( 'Y-m-d', strtotime( $START_DATE ) );
		$END_DATE = date( 'Y-m-d', strtotime( $END_DATE ) );
		$where = "";
		$where .= ( $REGION_CODE != "" && $COMP_CODE == "" ) ? " AND est.REGION_CODE = '$REGION_CODE'  ": "";
		$where .= ( $COMP_CODE != "" && $BA_CODE == "" ) ? " AND est.COMP_CODE = '$COMP_CODE'  ": "";
		$where .= ( $BA_CODE != "" && $AFD_CODE == "" ) ? " AND est.WERKS = '$BA_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE == "" ) ? " AND hd.WERKS||hd.AFD_CODE = '$AFD_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE != "" ) ? " AND hd.WERKS||hd.AFD_CODE||hd.BLOCK_CODE = '$BLOCK_CODE'  ": "";
		$sql = ( "
			SELECT 
				\"NIK Reporter\", 
				\"Nama Reporter\", 
				\"Jabatan Reporter\", 
				\"Kode BA\", 
				\"Business Area\", 
				afd, 
				\"Kode Block\", 
				\"Block Deskripsi\", 
				\"Maturity Status\", 
				\"Tanggal Inspeksi\", 
				\"Jumlah Baris\", 
				\"Periode\", 
				\"Lama Inspeksi\", 
				\"Pokok Panen\", 
				\"Buah Tinggal\", 
				\"Brondolan di Piringan\", 
				\"Brondolan di TPH\", 
				\"Pokok Tidak di Pupuk\", 
				\"Rata-rata Sistem Penaburan\", 
				\"Rata-rata Kondisi Pupuk\", 
				\"Rata-rata Piringan\", 
				\"Rata-rata Pasar Pikul\", 
				\"Rata-rata TPH\", 
				\"Rata-rata Gawangan\", 
				\"Rata-rata Prunning\", 
				\"Rata-rata Titi Panen\", 
				\"Rata-rata Kastrasi\", 
				\"Rata-rata Sanitasi\", 
				\"Bobot Piringan\", 
				\"Bobot Pasar Pikul\", 
				\"Bobot TPH\", 
				\"Bobot Gawangan\", 
				\"Bobot Prunning\", 
				\"Avg Bobot Piringan\" AS \"Rata-rata x Bobot Piringan\", 
				\"Avg Pasar Pikul\" AS \"Rata-rata x Pasar Pikul\", 
				\"Avg Bobot TPH\" AS \"Rata-rata x Bobot TPH\", 
				\"Avg Bobot Gawangan\" AS \"Rata-rata x Bobot Gawangan\", 
				\"Avg Bobot Prunning\" AS \"Rata-rata x Bobot Prunning\", 
				ROUND (nilai, 2) \"Nilai Inspeksi\", 
				grade \"Hasil Inspeksi\" 
			FROM 
				(
					SELECT 
						\"NIK Reporter\", 
						\"Nama Reporter\", 
						REPLACE (\"Jabatan Reporter\", '_', ' ') AS \"Jabatan Reporter\", 
						\"Kode BA\", 
						\"Business Area\", 
						\"AFD\", 
						\"Kode Block\", 
						\"Block Deskripsi\", 
						\"Maturity Status\", 
						\"Tanggal Inspeksi\", 
						COUNT (*) \"Jumlah Baris\", 
						\"Periode\", 
						TO_CHAR (
							TRUNC (
								MOD (
									SUM (inspection_second), 
									3600
								) / 60
							), 
							'FM00'
						) || ':' || TO_CHAR (
							MOD (
								SUM (inspection_second), 
								60
							), 
							'FM00'
						) \"Lama Inspeksi\", 
						SUM (\"Pokok Panen_VAL\") \"Pokok Panen\", 
						SUM (\"Buah Tinggal_VAL\") \"Buah Tinggal\", 
						SUM (\"Brondolan di Piringan_VAL\") \"Brondolan di Piringan\", 
						SUM (\"Brondolan di TPH_VAL\") \"Brondolan di TPH\", 
						SUM (\"Pokok Tidak di Pupuk_VAL\") \"Pokok Tidak di Pupuk\", 
						AVG (\"Sistem Penaburan_VAL\") \"Rata-rata Sistem Penaburan\", 
						AVG (\"Kondisi Pupuk_VAL\") \"Rata-rata Kondisi Pupuk\", 
						AVG (\"Piringan_VAL\") \"Rata-rata Piringan\", 
						AVG (\"Pasar Pikul_VAL\") \"Rata-rata Pasar Pikul\", 
						AVG (\"TPH_VAL\") \"Rata-rata TPH\", 
						AVG (\"Gawangan_VAL\") \"Rata-rata Gawangan\", 
						AVG (\"Prunning_VAL\") \"Rata-rata Prunning\", 
						AVG (\"Titi Panen_VAL\") \"Rata-rata Titi Panen\", 
						AVG (\"Kastrasi_VAL\") \"Rata-rata Kastrasi\", 
						AVG (\"Sanitasi_VAL\") \"Rata-rata Sanitasi\", 
						MAX (\"Piringan_BOBOT\") \"Bobot Piringan\", 
						MAX (\"Pasar Pikul_BOBOT\") \"Bobot Pasar Pikul\", 
						MAX (\"TPH_BOBOT\") \"Bobot TPH\", 
						MAX (\"Gawangan_BOBOT\") \"Bobot Gawangan\", 
						MAX (\"Prunning_BOBOT\") \"Bobot Prunning\", 
						AVG (\"Piringan_VAL\") * MAX (\"Piringan_BOBOT\") \"Avg Bobot Piringan\", 
						AVG (\"Pasar Pikul_VAL\") * MAX (\"Pasar Pikul_BOBOT\") \"Avg Pasar Pikul\", 
						AVG (\"TPH_VAL\") * MAX (\"TPH_BOBOT\") \"Avg Bobot TPH\", 
						AVG (\"Gawangan_VAL\") * MAX (\"Gawangan_BOBOT\") \"Avg Bobot Gawangan\", 
						AVG (\"Prunning_VAL\") * MAX (\"Prunning_BOBOT\") \"Avg Bobot Prunning\", 
						(
							AVG (\"Piringan_VAL\") * MAX (\"Piringan_BOBOT\") + AVG (\"Pasar Pikul_VAL\") * MAX (\"Pasar Pikul_BOBOT\") + AVG (\"TPH_VAL\") * MAX (\"TPH_BOBOT\") + AVG (\"Gawangan_VAL\") * MAX (\"Gawangan_BOBOT\") + AVG (\"Prunning_VAL\") * MAX (\"Prunning_BOBOT\")
						) / (
							MAX (\"Piringan_BOBOT\") + MAX (\"Pasar Pikul_BOBOT\") + MAX (\"TPH_BOBOT\") + MAX (\"Gawangan_BOBOT\") + MAX (\"Prunning_BOBOT\")
						) nilai 
					FROM 
						(
							SELECT 
								hd.block_inspection_code, 
								user_auth.employee_nik \"NIK Reporter\", 
								emp.employee_name \"Nama Reporter\", 
								user_auth.user_role \"Jabatan Reporter\", 
								hd.werks \"Kode BA\", 
								est.est_name \"Business Area\", 
								hd.afd_code \"AFD\", 
								hd.block_code \"Kode Block\", 
								sub_block.block_name \"Block Deskripsi\", 
								lu.maturity_status \"Maturity Status\", 
								TO_CHAR (
									hd.inspection_date, 'YYYY-MM-DD'
								) \"Tanggal Inspeksi\", 
								TO_CHAR (hd.inspection_date, 'yyyy.mm') \"Periode\", 
								SUM (
									(
										hd.end_inspection - hd.start_inspection
									) * 24 * 60 * 60
								) inspection_second 
							FROM 
								mobile_inspection.tr_block_inspection_h hd 
								LEFT JOIN tap_dw.tm_est@dwh_link est ON hd.werks = est.werks 
								LEFT JOIN tap_dw.tm_sub_block@dwh_link sub_block ON hd.werks = sub_block.werks 
								AND hd.afd_code = sub_block.afd_code 
								AND hd.block_code = sub_block.sub_block_code 
								AND hd.inspection_date BETWEEN sub_block.start_valid 
								AND sub_block.end_valid 
								LEFT JOIN mobile_inspection.tm_user_auth user_auth ON hd.insert_user = user_auth.user_auth_code 
								LEFT JOIN (
									SELECT 
										nik, 
										employee_name, 
										job_code, 
										start_valid, 
										CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid 
									FROM 
										tap_dw.tm_employee_sap@dwh_link 
									UNION 
									SELECT 
										employee_nik, 
										employee_fullname, 
										employee_position, 
										employee_joindate, 
										CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD') ELSE employee_resigndate END employee_regisndate 
									FROM 
										tap_dw.tm_employee_hris@dwh_link
								) emp ON user_auth.employee_nik = emp.nik 
								AND inspection_date BETWEEN emp.start_valid 
								AND emp.end_valid 
								LEFT JOIN (
									SELECT 
										werks, 
										afd_code, 
										land_use_code, 
										land_use_name, 
										spmon, 
										maturity_status 
									FROM 
										tap_dw.tr_hs_land_use@dwh_link 
									WHERE 
										spmon >= TRUNC (SYSDATE - 1, 'YYYY') 
									UNION ALL 
									SELECT 
										werks, 
										afd_code, 
										land_use_code, 
										land_use_name, 
										LAST_DAY (
											ADD_MONTHS (spmon, 1)
										) spmon, 
										maturity_status 
									FROM 
										tap_dw.tr_hs_land_use@dwh_link 
									WHERE 
										spmon = (
											SELECT 
												MAX (spmon) 
											FROM 
												tap_dw.tr_hs_land_use@dwh_link
										)
								) lu ON hd.werks = lu.werks 
								AND hd.afd_code = lu.afd_code 
								AND hd.block_code = lu.land_use_code 
								AND TRUNC (
									LAST_DAY (hd.inspection_date)
								) = lu.spmon 
								LEFT JOIN (
									SELECT 
										block_inspection_code 
									FROM 
										mobile_inspection.tr_inspection_genba 
									GROUP BY 
										block_inspection_code
								) genba ON hd.block_inspection_code = genba.block_inspection_code 
							WHERE
								genba.block_inspection_code IS NOT NULL
								AND TRUNC( hd.INSPECTION_DATE ) BETWEEN TRUNC( TO_DATE( '$START_DATE', 'RRRR-MM-DD' ) ) AND TRUNC( TO_DATE( '$END_DATE', 'RRRR-MM-DD' ) )
								$where
							GROUP BY 
								hd.block_inspection_code, 
								user_auth.employee_nik, 
								emp.employee_name, 
								user_auth.user_role, 
								hd.werks, 
								est.est_name, 
								hd.afd_code, 
								hd.block_code, 
								sub_block.block_name, 
								lu.maturity_status, 
								TO_CHAR (
									hd.inspection_date, 'YYYY-MM-DD'
								), 
								TO_CHAR (hd.inspection_date, 'yyyy.mm')
						) hd 
						LEFT JOIN (
							SELECT 
								* 
							FROM 
								(
									SELECT 
										master.werks, 
										master.afd_code, 
										master.block_code, 
										block_inspection_code, 
										content_name, 
										CASE WHEN CASE WHEN lu.maturity_status = 'TBM 0' THEN tbm0 WHEN lu.maturity_status = 'TBM 1' THEN tbm1 WHEN lu.maturity_status = 'TBM 2' THEN tbm2 WHEN lu.maturity_status = 'TBM 3' THEN tbm3 WHEN lu.maturity_status = 'TM' THEN tm END = 'NO' THEN NULL ELSE bobot END bobot, 
										CASE WHEN CASE WHEN lu.maturity_status = 'TBM 0' THEN tbm0 WHEN lu.maturity_status = 'TBM 1' THEN tbm1 WHEN lu.maturity_status = 'TBM 2' THEN tbm2 WHEN lu.maturity_status = 'TBM 3' THEN tbm3 WHEN lu.maturity_status = 'TM' THEN tm END = 'NO' THEN NULL ELSE VALUE END VALUE 
									FROM 
										(
											SELECT 
												1 sort_by, 
												master.*, 
												detail.VALUE 
											FROM 
												(
													SELECT 
														hd.block_inspection_code, 
														hd.werks, 
														hd.afd_code, 
														hd.block_code, 
														hd.inspection_date, 
														cont.* 
													FROM 
														mobile_inspection.tr_block_inspection_h hd 
														LEFT JOIN tm_content cont ON 1 = 1 
														AND cont.content_code NOT IN (
															SELECT 
																DISTINCT content_code 
															FROM 
																mobile_inspection.tm_content_label
														) 
													WHERE 
														1 = 1 
														AND group_category = 'INSPEKSI'
												) master 
												LEFT JOIN (
													SELECT 
														det.block_inspection_code, 
														det.content_inspection_code, 
														det.VALUE, 
														TO_NUMBER (
															NVL (
																TO_CHAR (cont_lab.label_score), 
																VALUE
															)
														) score 
													FROM 
														mobile_inspection.tr_block_inspection_d det 
														LEFT JOIN mobile_inspection.tm_content_label cont_lab ON det.content_inspection_code = cont_lab.content_code 
														AND det.VALUE = cont_lab.label_name
												) detail ON master.block_inspection_code = detail.block_inspection_code 
												AND master.content_code = detail.content_inspection_code 
											UNION 
											SELECT 
												2 sort_by, 
												master.block_inspection_code, 
												master.werks, 
												master.afd_code, 
												master.block_code, 
												master.inspection_date, 
												master.bobot, 
												master.category, 
												master.content_code, 
												master.content_name content_name, 
												master.content_type, 
												master.flag_type, 
												master.group_category, 
												master.tbm0, 
												master.tbm1, 
												master.tbm2, 
												master.tbm3, 
												master.tm, 
												master.uom, 
												master.urutan, 
												TO_CHAR (detail.score) VALUE 
											FROM 
												(
													SELECT 
														hd.block_inspection_code, 
														hd.werks, 
														hd.afd_code, 
														hd.block_code, 
														hd.inspection_date, 
														cont.* 
													FROM 
														mobile_inspection.tr_block_inspection_h hd 
														LEFT JOIN tm_content cont ON 1 = 1 
														INNER JOIN (
															SELECT 
																DISTINCT content_code 
															FROM 
																mobile_inspection.tm_content_label
														) cont_lab ON cont.content_code = cont_lab.content_code 
													WHERE 
														1 = 1 
														AND cont.group_category = 'INSPEKSI'
												) master 
												LEFT JOIN (
													SELECT 
														det.block_inspection_code, 
														det.content_inspection_code, 
														det.VALUE, 
														TO_NUMBER (
															NVL (
																TO_CHAR (cont_lab.label_score), 
																VALUE
															)
														) score 
													FROM 
														mobile_inspection.tm_content_label cont_lab 
														LEFT JOIN mobile_inspection.tr_block_inspection_d det ON cont_lab.content_code = det.content_inspection_code 
														AND cont_lab.label_name = det.VALUE
												) detail ON master.block_inspection_code = detail.block_inspection_code 
												AND master.content_code = detail.content_inspection_code
										) master 
										LEFT JOIN (
											SELECT 
												werks, 
												afd_code, 
												land_use_code, 
												land_use_name, 
												spmon, 
												maturity_status 
											FROM 
												tap_dw.tr_hs_land_use@dwh_link 
											WHERE 
												spmon >= TRUNC (SYSDATE - 1, 'YYYY') 
											UNION ALL 
											SELECT 
												werks, 
												afd_code, 
												land_use_code, 
												land_use_name, 
												LAST_DAY (
													ADD_MONTHS (spmon, 1)
												) spmon, 
												maturity_status 
											FROM 
												tap_dw.tr_hs_land_use@dwh_link 
											WHERE 
												spmon = (
													SELECT 
														MAX (spmon) 
													FROM 
														tap_dw.tr_hs_land_use@dwh_link
												)
										) lu ON master.werks = lu.werks 
										AND master.afd_code = lu.afd_code 
										AND master.block_code = lu.land_use_code 
										AND TRUNC (
											LAST_DAY (master.inspection_date)
										) = lu.spmon
								) PIVOT (
									MAX (bobot) AS bobot, 
									SUM (VALUE) AS val FOR content_name IN (
										'Pokok Panen' AS \"Pokok Panen\", 'Buah Tinggal' AS \"Buah Tinggal\", 
										'Brondolan di Piringan' AS \"Brondolan di Piringan\", 
										'Brondolan di TPH' AS \"Brondolan di TPH\", 
										'Pokok Tidak di Pupuk' AS \"Pokok Tidak di Pupuk\", 
										'Titi Panen' AS \"Titi Panen\", 'Sistem Penaburan' AS \"Sistem Penaburan\", 
										'Kondisi Pupuk' AS \"Kondisi Pupuk\", 
										'Kastrasi' AS \"Kastrasi\", 'Sanitasi' AS \"Sanitasi\", 
										'Piringan' AS \"Piringan\", 'Pasar Pikul' AS \"Pasar Pikul\", 
										'TPH' AS \"TPH\", 'Gawangan' AS \"Gawangan\", 
										'Prunning' AS \"Prunning\"
									)
								)
						) det ON hd.block_inspection_code = det.block_inspection_code 
					GROUP BY 
						\"NIK Reporter\", 
						\"Nama Reporter\", 
						\"Jabatan Reporter\", 
						\"Kode BA\", 
						\"Business Area\", 
						\"AFD\", 
						\"Kode Block\", 
						\"Block Deskripsi\", 
						\"Maturity Status\", 
						\"Tanggal Inspeksi\", 
						\"Periode\"
				) 
				LEFT JOIN mobile_inspection.tm_kriteria ON ROUND (nilai, 2) BETWEEN batas_bawah 
				AND batas_atas 
			ORDER BY 
				\"Tanggal Inspeksi\" ASC, 
				\"Nama Reporter\" ASC
		" );

		$get = $this->db_mobile_ins->select( $sql );

		return $get;
	}

	public function INSPECTION_GENBA_BARIS( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE ) {
		$START_DATE = date( 'Y-m-d', strtotime( $START_DATE ) );
		$END_DATE = date( 'Y-m-d', strtotime( $END_DATE ) );
		$where = "";
		$where .= ( $REGION_CODE != "" && $COMP_CODE == "" ) ? " AND est.REGION_CODE = '$REGION_CODE'  ": "";
		$where .= ( $COMP_CODE != "" && $BA_CODE == "" ) ? " AND est.COMP_CODE = '$COMP_CODE'  ": "";
		$where .= ( $BA_CODE != "" && $AFD_CODE == "" ) ? " AND est.WERKS = '$BA_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE == "" ) ? " AND hd.WERKS||hd.AFD_CODE = '$AFD_CODE'  ": "";
		$where .= ( $AFD_CODE != "" && $BLOCK_CODE != "" ) ? " AND hd.WERKS||hd.AFD_CODE||hd.BLOCK_CODE = '$BLOCK_CODE'  ": "";
		$sql = ( "
			SELECT 
				hd.block_inspection_code \"Kode Inspeksi\", 
				hd.werks \"Kode BA\", 
				est.est_name \"Business Area\", 
				hd.afd_code \"AFD\", 
				hd.block_code \"Kode Block\", 
				sub_block.block_name \"Block Deskripsi\", 
				TO_CHAR (
					hd.inspection_date, 'YYYY-MM-DD'
				) \"Tanggal Inspeksi\", 
				TO_CHAR (
					TRUNC (
						MOD (
							(
								hd.end_inspection - hd.start_inspection
							) * 24 * 60 * 60, 
							3600
						) / 60
					), 
					'FM00'
				) || ':' || TO_CHAR (
					MOD (
						(
							hd.end_inspection - hd.start_inspection
						) * 24 * 60 * 60, 
						60
					), 
					'FM00'
				) \"Lama Inspeksi\", 
				hd.areal \"Baris\", 
				user_auth.employee_nik \"NIK Reporter\", 
				emp.employee_name \"Nama Reporter\", 
				REPLACE (user_auth.user_role, '_', ' ') \"Jabatan Reporter\", 
				TO_CHAR (hd.inspection_date, 'yyyy.mm') \"Periode\", 
				lu.maturity_status \"Maturity Status\", 
				hd.lat_start_inspection \"Lat Start\", 
				hd.long_start_inspection \"Long Start\", 
				\"Pokok Panen\", 
				\"Buah Tinggal\", 
				\"Brondolan di Piringan\", 
				\"Brondolan di TPH\", 
				\"Pokok Tidak di Pupuk\", 
				\"Piringan\", 
				\"Pasar Pikul\", 
				\"TPH\", 
				\"Gawangan\", 
				\"Prunning\", 
				\"Titi Panen\", 
				\"Sistem Penaburan\", 
				\"Kondisi Pupuk\", 
				\"Kastrasi\", 
				\"Sanitasi\", 
				\"Nilai Piringan\", 
				\"Nilai Pasar Pikul\", 
				\"Nilai TPH\", 
				\"Nilai Gawangan\", 
				\"Nilai Prunning\" 
			FROM 
				mobile_inspection.tr_block_inspection_h hd 
				LEFT JOIN tap_dw.tm_est@dwh_link est ON hd.werks = est.werks 
				LEFT JOIN tap_dw.tm_sub_block@dwh_link sub_block ON hd.werks = sub_block.werks 
				AND hd.afd_code = sub_block.afd_code 
				AND hd.block_code = sub_block.sub_block_code 
				AND hd.inspection_date BETWEEN sub_block.start_valid 
				AND sub_block.end_valid 
				LEFT JOIN mobile_inspection.tm_user_auth user_auth ON hd.insert_user = user_auth.user_auth_code 
				LEFT JOIN (
					SELECT 
						nik, 
						employee_name, 
						job_code, 
						start_valid, 
						CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid 
					FROM 
						tap_dw.tm_employee_sap@dwh_link 
					UNION 
					SELECT 
						employee_nik, 
						employee_fullname, 
						employee_position, 
						employee_joindate, 
						CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD') ELSE employee_resigndate END employee_regisndate 
					FROM 
						tap_dw.tm_employee_hris@dwh_link
				) emp ON user_auth.employee_nik = emp.nik 
				AND inspection_date BETWEEN emp.start_valid 
				AND emp.end_valid 
				LEFT JOIN (
					SELECT 
						werks, 
						afd_code, 
						land_use_code, 
						land_use_name, 
						spmon, 
						maturity_status 
					FROM 
						tap_dw.tr_hs_land_use@dwh_link 
					WHERE 
						spmon >= TRUNC (SYSDATE - 1, 'YYYY') 
					UNION ALL 
					SELECT 
						werks, 
						afd_code, 
						land_use_code, 
						land_use_name, 
						LAST_DAY (
							ADD_MONTHS (spmon, 1)
						) spmon, 
						maturity_status 
					FROM 
						tap_dw.tr_hs_land_use@dwh_link 
					WHERE 
						spmon = (
							SELECT 
								MAX (spmon) 
							FROM 
								tap_dw.tr_hs_land_use@dwh_link
						)
				) lu ON hd.werks = lu.werks 
				AND hd.afd_code = lu.afd_code 
				AND hd.block_code = lu.land_use_code 
				AND TRUNC (
					LAST_DAY (hd.inspection_date)
				) = lu.spmon 
				LEFT JOIN (
					SELECT 
						block_inspection_code, 
						\"Pokok Panen\", 
						\"Buah Tinggal\", 
						\"Brondolan di Piringan\", 
						\"Brondolan di TPH\", 
						\"Pokok Tidak di Pupuk\", 
						\"Piringan\", 
						\"Pasar Pikul\", 
						\"TPH\", 
						\"Gawangan\", 
						\"Prunning\", 
						\"Titi Panen\", 
						\"Sistem Penaburan\", 
						\"Kondisi Pupuk\", 
						\"Kastrasi\", 
						\"Sanitasi\", 
						\"Nilai Piringan\", 
						\"Nilai Pasar Pikul\", 
						\"Nilai TPH\", 
						\"Nilai Gawangan\", 
						\"Nilai Prunning\" 
					FROM 
						(
							SELECT 
								block_inspection_code, 
								content_name, 
								VALUE 
							FROM 
								(
									SELECT 
										1 sort_by, 
										master.*, 
										detail.VALUE 
									FROM 
										(
											SELECT 
												hd.block_inspection_code, 
												cont.* 
											FROM 
												mobile_inspection.tr_block_inspection_h hd 
												LEFT JOIN tm_content cont ON 1 = 1 
											WHERE 
												group_category = 'INSPEKSI'
										) master 
										LEFT JOIN (
											SELECT 
												det.block_inspection_code, 
												det.content_inspection_code, 
												det.VALUE, 
												TO_NUMBER (
													NVL (
														TO_CHAR (cont_lab.label_score), 
														VALUE
													)
												) score 
											FROM 
												mobile_inspection.tr_block_inspection_d det 
												LEFT JOIN mobile_inspection.tm_content_label cont_lab ON det.content_inspection_code = cont_lab.content_code 
												AND det.VALUE = cont_lab.label_name
										) detail ON master.block_inspection_code = detail.block_inspection_code 
										AND master.content_code = detail.content_inspection_code 
									UNION 
									SELECT 
										2 sort_by, 
										master.block_inspection_code, 
										master.bobot, 
										master.category, 
										master.content_code, 
										'Nilai ' || master.content_name content_name, 
										master.content_type, 
										master.flag_type, 
										master.group_category, 
										master.tbm0, 
										master.tbm1, 
										master.tbm2, 
										master.tbm3, 
										master.tm, 
										master.uom, 
										master.urutan, 
										TO_CHAR (detail.score) score 
									FROM 
										(
											SELECT 
												hd.block_inspection_code, 
												cont.* 
											FROM 
												mobile_inspection.tr_block_inspection_h hd 
												LEFT JOIN tm_content cont ON 1 = 1 
												INNER JOIN (
													SELECT 
														DISTINCT content_code 
													FROM 
														mobile_inspection.tm_content_label
												) cont_lab ON cont.content_code = cont_lab.content_code 
											WHERE 
												cont.group_category = 'INSPEKSI' 
												AND cont.bobot > 0
										) master 
										LEFT JOIN (
											SELECT 
												det.block_inspection_code, 
												det.content_inspection_code, 
												det.VALUE, 
												TO_NUMBER (
													NVL (
														TO_CHAR (cont_lab.label_score), 
														VALUE
													)
												) score 
											FROM 
												mobile_inspection.tm_content_label cont_lab 
												LEFT JOIN mobile_inspection.tr_block_inspection_d det ON cont_lab.content_code = det.content_inspection_code 
												AND cont_lab.label_name = det.VALUE
										) detail ON master.block_inspection_code = detail.block_inspection_code 
										AND master.content_code = detail.content_inspection_code
								)
								) PIVOT (
							MAX (VALUE) FOR content_name IN (
								'Pokok Panen' AS \"Pokok Panen\", 'Buah Tinggal' AS \"Buah Tinggal\", 
								'Brondolan di Piringan' AS \"Brondolan di Piringan\", 
								'Brondolan di TPH' AS \"Brondolan di TPH\", 
								'Pokok Tidak di Pupuk' AS \"Pokok Tidak di Pupuk\", 
								'Piringan' AS \"Piringan\", 'Pasar Pikul' AS \"Pasar Pikul\", 
								'TPH' AS \"TPH\", 'Gawangan' AS \"Gawangan\", 
								'Prunning' AS \"Prunning\", 'Titi Panen' AS \"Titi Panen\", 
								'Sistem Penaburan' AS \"Sistem Penaburan\", 
								'Kondisi Pupuk' AS \"Kondisi Pupuk\", 
								'Kastrasi' AS \"Kastrasi\", 'Sanitasi' AS \"Sanitasi\", 
								'Nilai Piringan' AS \"Nilai Piringan\", 
								'Nilai Pasar Pikul' AS \"Nilai Pasar Pikul\", 
								'Nilai TPH' AS \"Nilai TPH\", 'Nilai Gawangan' AS \"Nilai Gawangan\", 
								'Nilai Prunning' AS \"Nilai Prunning\"
							)
						)
				) detail ON hd.block_inspection_code = detail.block_inspection_code 
				LEFT JOIN (
					SELECT 
						block_inspection_code 
					FROM 
						mobile_inspection.tr_inspection_genba 
					GROUP BY 
						block_inspection_code
				) genba ON hd.block_inspection_code = genba.block_inspection_code 
			WHERE
				genba.block_inspection_code IS NOT NULL
				AND TRUNC( hd.INSPECTION_DATE ) BETWEEN TRUNC( TO_DATE( '$START_DATE', 'RRRR-MM-DD' ) ) AND TRUNC( TO_DATE( '$END_DATE', 'RRRR-MM-DD' ) )
				$where
			ORDER BY 
				hd.inspection_date ASC,
				emp.employee_name ASC
		" );
		$get = $this->db_mobile_ins->select( $sql );

		return $get;
	}

	public function INSPECTION_CLASS_BLOCK( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE, $DATE_MONTH ) {
		$where = "";
		$DATE_MONTH = $DATE_MONTH.'-01';
		# BA_CODE, DATE_MONTH

		$sql = "
			SELECT \"Estate\",
			         \"Afd\",
			         \"Kode Blok\",
			         \"Nama Blok\",
			         bs_periode,
			         \"Kelas Blok Bulan Ini\",
			         \"Total Nilai/Jumlah\",
			         b1_periode,
			         \"Ke 1\",
			         b1_total_nilai,
			         b2_periode,
			         \"Ke 2\",
			         b2_total_nilai,
			         b3_periode,
			         \"Ke 3\",
			         b3_total_nilai,
			         b4_periode,
			         \"Ke 4\",
			         b4_total_nilai,
			         b5_periode,
			         \"Ke 5\",
			         b5_total_nilai,
			         b6_periode,
			         \"Ke 6\",
			         b6_total_nilai
			    FROM (SELECT '1' sort_by,
			                 werks \"Estate\",
			                 afd_code \"Afd\",
			                 sub_block_code \"Kode Blok\",
			                 block_name \"Nama Blok\",
			                 CASE WHEN periode IS NOT NULL THEN TO_CHAR (TO_DATE (periode, 'YYYY-MM'), 'MON-YYYY') END bs_periode,
			                 kelas_blok \"Kelas Blok Bulan Ini\",
			                 total_nilai \"Total Nilai/Jumlah\",
			                 CASE WHEN b1_periode IS NOT NULL THEN TO_CHAR (TO_DATE (b1_periode, 'YYYY-MM'), 'MON-YYYY') END b1_periode,
			                 b1_kelas_blok \"Ke 1\",
			                 b1_total_nilai,
			                 CASE WHEN b2_periode IS NOT NULL THEN TO_CHAR (TO_DATE (b2_periode, 'YYYY-MM'), 'MON-YYYY') END b2_periode,
			                 b2_kelas_blok \"Ke 2\",
			                 b2_total_nilai,
			                 CASE WHEN b3_periode IS NOT NULL THEN TO_CHAR (TO_DATE (b3_periode, 'YYYY-MM'), 'MON-YYYY') END b3_periode,
			                 b3_kelas_blok \"Ke 3\",
			                 b3_total_nilai,
			                 CASE WHEN b4_periode IS NOT NULL THEN TO_CHAR (TO_DATE (b4_periode, 'YYYY-MM'), 'MON-YYYY') END b4_periode,
			                 b4_kelas_blok \"Ke 4\",
			                 b4_total_nilai,
			                 CASE WHEN b5_periode IS NOT NULL THEN TO_CHAR (TO_DATE (b5_periode, 'YYYY-MM'), 'MON-YYYY') END b5_periode,
			                 b5_kelas_blok \"Ke 5\",
			                 b5_total_nilai,
			                 CASE WHEN b6_periode IS NOT NULL THEN TO_CHAR (TO_DATE (b6_periode, 'YYYY-MM'), 'MON-YYYY') END b6_periode,
			                 b6_kelas_blok \"Ke 6\",
			                 b6_total_nilai
			            FROM mobile_inspection.tr_kelas_blok blok
			           WHERE werks = '$BA_CODE' AND TRUNC (spmon, 'MON') = TO_DATE ('$DATE_MONTH', 'RRRR-MM-DD')
			          UNION ALL
			          SELECT '2' sort_by,
			                 werks \"Estate\",
			                 afd_code \"Afd\",
			                 NULL \"Kode Blok\",
			                 NULL \"Nama Blok\",
			                 periode bs_periode,
			                 kelas_afd \"Kelas Blok Bulan Ini\",
			                 total_nilai \"Total Nilai/Jumlah\",
			                 b1_periode,
			                 b1_kelas_afd \"Ke 1\",
			                 b1_total_nilai,
			                 b2_periode,
			                 b2_kelas_afd \"Ke 2\",
			                 b2_total_nilai,
			                 b3_periode,
			                 b3_kelas_afd \"Ke 3\",
			                 b3_total_nilai,
			                 b4_periode,
			                 b4_kelas_afd \"Ke 4\",
			                 b4_total_nilai,
			                 b5_periode,
			                 b5_kelas_afd \"Ke 5\",
			                 b5_total_nilai,
			                 b6_periode,
			                 b6_kelas_afd \"Ke 6\",
			                 b6_total_nilai
			            FROM mobile_inspection.tr_kelas_afd afd
			           WHERE werks = '$BA_CODE' AND TRUNC (spmon, 'MON') = TO_DATE ('$DATE_MONTH', 'RRRR-MM-DD')
			          UNION ALL
			          SELECT '3' sort_by,
			                 werks \"Estate\",
			                 NULL \"Afd\",
			                 NULL \"Kode Blok\",
			                 NULL \"Nama Blok\",
			                 periode bs_periode,
			                 kelas_est \"Kelas Blok Bulan Ini\",
			                 total_nilai \"Total Nilai/Jumlah\",
			                 b1_periode,
			                 b1_kelas_est \"Ke 1\",
			                 b1_total_nilai,
			                 b2_periode,
			                 b2_kelas_est \"Ke 2\",
			                 b2_total_nilai,
			                 b3_periode,
			                 b3_kelas_est \"Ke 3\",
			                 b3_total_nilai,
			                 b4_periode,
			                 b4_kelas_est \"Ke 4\",
			                 b4_total_nilai,
			                 b5_periode,
			                 b5_kelas_est \"Ke 5\",
			                 b5_total_nilai,
			                 b6_periode,
			                 b6_kelas_est \"Ke 6\",
			                 b6_total_nilai
			            FROM mobile_inspection.tr_kelas_est est
			           WHERE werks = '$BA_CODE' AND TRUNC (spmon, 'MON') = TO_DATE ('$DATE_MONTH', 'RRRR-MM-DD'))
			ORDER BY \"Estate\",
			         \"Afd\",
			         \"Kode Blok\",
			         sort_by
		";

		$get = $this->db_mobile_ins->select( $sql );
		return $get;
	}
	
	public function POINT_BULANAN( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE, $DATE_MONTH ) {
		$where = "";
		date_default_timezone_set('Asia/Jakarta');
		$timezone='Asia/Jakarta';
		$last_day = date_create($DATE_MONTH.'-01');
		$client = new \GuzzleHttp\Client();
		$get = $client->request( 'GET', //$this->url_api_ins_msa_point.'api/v1.1/point/report/'.date_format($last_day,'Ymt'),
										'http://apisqa.tap-agri.com/mobileinspectionqa/ins-msa-qa-point/api/v1.1/point/report/'.date_format($last_day,'Ymt'),
										[
										 'headers' => [
											'Accept' => 'application/json',
											'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' ),
													]
										]
										);
		$get = json_decode( $get->getBody(), true );
		// echo '<pre>';
		// print_r($get);
		// die;
		return $get['data'];
	}
	
	public function HISTORY_POINT_BULANAN( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE, $DATE_MONTH ) {
		$where = "";
		date_default_timezone_set('Asia/Jakarta');
		$timezone='Asia/Jakarta';
		$DATE_MONTH = $DATE_MONTH.'-01';
		$last_day = date_create($DATE_MONTH);
		$client = new \GuzzleHttp\Client();
		$get = $client->request( 'GET', //$this->url_api_ins_msa_point.'api/v1.0/history/report/'.date_format($last_day,'Ymt'),
										'http://apisqa.tap-agri.com/mobileinspectionqa/ins-msa-qa-point/api/v1.0/history/report/'.date_format($last_day,'Ymt'),
										[
										 'headers' => [
											'Accept' => 'application/json',
											'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' ),
													]
										]
										);
		$get = json_decode( $get->getBody(), true );
		// echo '<pre>';
		// print_r($point_data);
		// die;
		return $get['data'];
	}
	
	public function PENCAPAIAN_INSPEKSI( $REPORT_TYPE , $START_DATE , $END_DATE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE, $DATE_MONTH ) {
		$where = "";
		$DATE_MONTH = $DATE_MONTH.'-01';
		# BA_CODE, DATE_MONTH

		$sql = "
			SELECT employee_name,
                   user_role,
                   jlh_afd,
                   libur,
                   target,
                   jlh_inspeksi,
                   jlh_genba,
                   tgl_genba,
                   total_actual,
                   CASE WHEN achievement > 100 THEN 100 ELSE achievement END achievement
              FROM (SELECT                                                                                                                                                                  
						   /*user_auth_code,employee_nik,*/
                           employee_name,
                           user_role,
                           /*ref_role,location_code_raw,*/
                           jlh_afd,
                           libur,
                           CASE
                              WHEN user_role IN ('SEM', 'GM') THEN NULL
                              WHEN user_role IN ('EM') THEN 2
                              WHEN user_role IN ('KEPALA_KEBUN') THEN 2 * jlh_afd
                              WHEN user_role IN ('ASISTEN_LAPANGAN') THEN (7 - libur) * 2
                           END
                              target,
                           jlh_inspeksi,
                           jlh_genba,
                           tgl_genba,
                           CASE WHEN user_role = 'ASISTEN_LAPANGAN' THEN NVL (jlh_inspeksi, 0) + (NVL (jlh_genba, 0) * 2) ELSE NVL (jlh_inspeksi, 0) + NVL (jlh_genba, 0) END total_actual,
                             CASE WHEN user_role = 'ASISTEN_LAPANGAN' THEN NVL (jlh_inspeksi, 0) + (NVL (jlh_genba, 0) * 2) ELSE NVL (jlh_inspeksi, 0) + NVL (jlh_genba, 0) END
                           / NULLIF (CASE
                                        WHEN user_role IN ('SEM_GM') THEN NULL
                                        WHEN user_role IN ('EM') THEN 2
                                        WHEN user_role IN ('KEPALA_KEBUN') THEN 2 * jlh_afd
                                        WHEN user_role IN ('ASISTEN_LAPANGAN') THEN (7 - libur) * 2
                                     END, 0)
                           * 100
                              achievement
                      FROM (  SELECT user_auth_code,
                                     employee_nik,
                                     user_role,
                                     ref_role,
                                     location_code_raw,
                                     COUNT (DISTINCT werks || afd_code) jlh_afd,
                                     MAX (libur) libur
                                FROM (SELECT user_auth_code,
                                             employee_nik,
                                             user_role,
                                             ref_role,
                                             location_code_raw,
                                             location_code,
                                             lu.werks,
                                             afd_code,
                                             FIRST_VALUE (libur) OVER (PARTITION BY user_auth_code ORDER BY lu.werks) libur
                                        FROM (    SELECT user_auth_code,
														 employee_nik,
														 user_role,
														 ref_role,
														 location_code_raw,
														 location_code
													FROM mobile_inspection.v_tm_user_auth@proddb_link hd
												   WHERE (delete_time IS NULL OR delete_time > TO_DATE ('$START_DATE', 'dd-mm-yyyy'))
														 AND (   CASE
																	WHEN hd.ref_role = 'AFD_CODE' THEN SUBSTR (location_code, 1, 4)
																	WHEN hd.ref_role = 'BA_CODE' THEN location_code
																	ELSE '9999'
																 END = NVL ('$BA_CODE', location_code)
															  OR CASE WHEN hd.ref_role = 'COMP_CODE' THEN location_code END IN (SELECT comp_code
																																  FROM tap_dw.tm_est@proddw_link
																																 WHERE werks = NVL ('$BA_CODE', werks))
															  OR CASE WHEN hd.ref_role = 'REGION_CODE' THEN location_code END IN (SELECT region_code
																																	FROM tap_dw.tm_est@proddw_link
																																   WHERE werks = NVL ('$BA_CODE', werks))
															  OR CASE WHEN hd.ref_role = 'NATIONAL' THEN location_code END = 'ALL')
												GROUP BY user_auth_code,
														 employee_nik,
														 user_role,
														 ref_role,
														 location_code_raw,
														 location_code) hd
                                             LEFT JOIN (  SELECT region_code,
                                                                 comp_code,
                                                                 werks,
                                                                 afd_code,
                                                                 spmon
                                                            FROM tap_dw.tr_hs_land_use@dwh_link
                                                           WHERE     spmon BETWEEN TRUNC (ADD_MONTHS (SYSDATE, -1), 'YEAR') AND LAST_DAY (ADD_MONTHS (SYSDATE, -1))
                                                        GROUP BY region_code,
                                                                 comp_code,
                                                                 werks,
                                                                 afd_code,
                                                                 spmon
                                                        UNION
                                                          SELECT region_code,
                                                                 comp_code,
                                                                 werks,
                                                                 afd_code,
                                                                 ADD_MONTHS (spmon, 1) spmon
                                                            FROM tap_dw.tr_hs_land_use@dwh_link
                                                           WHERE     spmon = (  SELECT MAX (spmon) FROM tap_dw.tr_hs_land_use@dwh_link)
                                                        GROUP BY region_code,
                                                                 comp_code,
                                                                 werks,
                                                                 afd_code,
                                                                 spmon
														 UNION
                                                          SELECT region_code,
                                                                 comp_code,
                                                                 werks,
                                                                 afd_code,
                                                                 ADD_MONTHS (spmon, 2) spmon
                                                            FROM tap_dw.tr_hs_land_use@dwh_link
                                                           WHERE     spmon = (  SELECT MAX (spmon) FROM tap_dw.tr_hs_land_use@dwh_link)
                                                        GROUP BY region_code,
                                                                 comp_code,
                                                                 werks,
                                                                 afd_code,
                                                                 spmon) lu
                                                ON TRUNC (LAST_DAY (TO_DATE ('$START_DATE', 'dd-mm-yyyy'))) = lu.spmon
                                                   AND CASE
                                                         WHEN hd.ref_role = 'AFD_CODE' THEN lu.werks || lu.afd_code
                                                         WHEN hd.ref_role = 'BA_CODE' THEN lu.werks
                                                         WHEN hd.ref_role = 'COMP_CODE' THEN lu.comp_code
                                                         WHEN hd.ref_role = 'REGION_CODE' THEN lu.region_code
                                                         WHEN hd.ref_role = 'NATIONAL' THEN 'ALL'
                                                      END = hd.location_code
                                             LEFT JOIN (  SELECT werks, COUNT (DISTINCT tanggal) libur
                                                            FROM tap_dw.tm_time_daily@dwh_link
                                                           WHERE tanggal BETWEEN TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 7 AND TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 1 AND flag_hk = 'N'
                                                        GROUP BY werks) libur
                                                ON lu.werks = libur.werks) mst
                            GROUP BY user_auth_code,
                                     employee_nik,
                                     user_role,
                                     ref_role,
                                     location_code_raw) hd
                           LEFT JOIN (  SELECT insp_h.insert_user, COUNT (DISTINCT TRUNC (insp_h.inspection_date) || werks || afd_code || block_code) jlh_inspeksi
                                          FROM mobile_inspection.tr_block_inspection_h@proddb_link insp_h LEFT JOIN mobile_inspection.tm_user_auth@proddb_link user_auth
                                                  ON insp_h.insert_user = user_auth.user_auth_code
											   LEFT JOIN (  SELECT DISTINCT block_inspection_code FROM mobile_inspection.tr_inspection_genba@proddb_link) genba
												ON insp_h.block_inspection_code = genba.block_inspection_code
                                         WHERE     insp_h.block_inspection_code NOT IN (  SELECT block_inspection_code FROM mobile_inspection.tr_inspection_genba)
                                               AND genba.block_inspection_code is null
											   AND CASE WHEN user_role = 'ASISTEN_LAPANGAN' AND insp_h.areal < 2 THEN 0 ELSE 1 END = 1
                                               AND TRUNC (inspection_date) BETWEEN TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 7 AND TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 1
                                               AND werks = NVL ('$BA_CODE', werks)
                                               AND afd_code = NVL ('$AFD_CODE', afd_code)
                                               AND block_code = NVL ('$BLOCK_CODE', block_code)
                                      GROUP BY insp_h.insert_user) inspeksi
                              ON hd.user_auth_code = inspeksi.insert_user
                           LEFT JOIN (  SELECT genba_user, COUNT (DISTINCT inspection_date || genba_user) jlh_genba, RTRIM (XMLAGG (XMLELEMENT (e, inspection_date || ',')).EXTRACT ('//text()'), ',') tgl_genba
                                          FROM (SELECT DISTINCT insp_h.werks,
                                                                TO_CHAR (insp_h.inspection_date, 'dd-mm-yyyy') inspection_date,
                                                                genba.genba_user,
                                                                user_auth.user_role
                                                  FROM (SELECT DISTINCT block_inspection_code, genba_user FROM mobile_inspection.tr_inspection_genba@proddb_link
                                                        UNION
                                                        SELECT DISTINCT hd.block_inspection_code, insert_user
                                                          FROM mobile_inspection.tr_block_inspection_h@proddb_link hd JOIN mobile_inspection.tr_inspection_genba@proddb_link genba
                                                                  ON hd.block_inspection_code = genba.block_inspection_code
                                                         WHERE     1 = 1
                                                               AND TRUNC (inspection_date) BETWEEN TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 7 AND TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 1
                                                               AND hd.werks = NVL ('$BA_CODE', hd.werks)
                                                               AND hd.afd_code = NVL ('$AFD_CODE', hd.afd_code)
                                                               AND hd.block_code = NVL ('$BLOCK_CODE', hd.block_code)) genba
                                                       INNER JOIN (SELECT DISTINCT werks, TRUNC (inspection_date) inspection_date, block_inspection_code
                                                                     FROM mobile_inspection.tr_block_inspection_h@proddb_link hd
                                                                    WHERE     TRUNC (hd.inspection_date) BETWEEN TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 7 AND TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 1
                                                                          AND hd.werks = NVL ('$BA_CODE', hd.werks)
                                                                          AND hd.afd_code = NVL ('$AFD_CODE', hd.afd_code)
                                                                          AND hd.block_code = NVL ('$BLOCK_CODE', hd.block_code)) insp_h
                                                          ON genba.block_inspection_code = insp_h.block_inspection_code
                                                       LEFT JOIN mobile_inspection.tm_user_auth user_auth
                                                          ON genba.genba_user = user_auth.user_auth_code
                                                 WHERE TRUNC (inspection_date) BETWEEN TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 7 AND TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 1)
                                      GROUP BY genba_user) genba
                              ON hd.user_auth_code = genba.genba_user
                           INNER JOIN (SELECT nik,
                                             employee_name,
                                             job_code,
                                             start_valid,
                                             CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid
                                        FROM tap_dw.tm_employee_sap@dwh_link
                                      UNION
                                      SELECT employee_nik,
                                             employee_fullname,
                                             employee_position,
                                             employee_joindate,
                                             CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('9999-12-31', 'RRRR-MM-DD') ELSE employee_resigndate END employee_regisndate
                                        FROM tap_dw.tm_employee_hris@dwh_link) emp
                              ON hd.employee_nik = emp.nik 
							WHERE TO_DATE ('$START_DATE', 'dd-mm-yyyy') BETWEEN emp.start_valid AND emp.end_valid
							AND user_role in ('SEM_GM','EM','KEPALA_KEBUN','ASISTEN_LAPANGAN'))
							 ORDER BY CASE
										WHEN user_role = 'SEM_GM' THEN 1
										WHEN user_role = 'EM' THEN 2
										WHEN user_role = 'KEPALA_KEBUN' THEN 3
										WHEN user_role = 'ASISTEN_LAPANGAN' THEN 4
										ELSE 5
									 END, employee_name
		";
		// echo $sql;
		// die;
		$get = $this->db_mobile_ins->select( $sql );
		return $get;
	}
	
	
}
