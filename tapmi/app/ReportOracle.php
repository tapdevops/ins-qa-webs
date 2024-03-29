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
		$this->db_ebcc = DB::connection('ebcc');
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
									   ebcc_header.lon_tph AS val_lon_tph
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
										  ON subblock.werks = ebcc_header.werks 
										  AND subblock.afd_code= ebcc_header.afd_code 
										  AND subblock.sub_block_code = ebcc_header.block_code
										  AND TRUNC (ebcc_header.insert_time) BETWEEN subblock.start_valid AND subblock.end_valid
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
																															'16' AS jml_16))
								 WHERE ebcc_validation_code IS NOT NULL) detail
						ON header.val_ebcc_code = detail.ebcc_validation_code
					 LEFT JOIN (SELECT *
								  FROM mobile_inspection.tm_status_tph
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
				),
				HEADER.VAL_DELIVERY_TICKET
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
								ON subblock.werks = ebcc_header.werks 
							    AND subblock.afd_code= ebcc_header.afd_code 
							    AND subblock.sub_block_code = ebcc_header.block_code
							    AND TRUNC (ebcc_header.insert_time) BETWEEN subblock.start_valid AND subblock.end_valid
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
						'EBCC' source,
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
						HP.NO_BCC = '{$get->ebcc_no_bcc}'
						
					UNION 
					SELECT
					    'EHARVESTING' source,
						TO_CHAR(thah.TRANSACTION_TIME,'YYYYMMDD') || 
						MAX (CASE WHEN thaud.activity_user_type = 'MANDOR' THEN thaud.user_nik ELSE NULL END ) ||
						MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_nik ELSE NULL END ) id_rencana,
						TRUNC(thah.TRANSACTION_TIME) tanggal_rencana,
						MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_nik ELSE NULL END ) nik_kerani_buah,
						MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_fullname ELSE NULL END ) EMP_NAME,
						thah.BLOCK_CODE ID_BA_AFD_BLOK,
						MAX (SUBSTR(REPLACE(thah.HARVEST_ACTIVITY_CODE,'.'),1,11)) NO_REKAP_BCC,
						thah.TPH_NAME NO_TPH,
						MAX (REPLACE(thah.HARVEST_ACTIVITY_CODE,'.'))  NO_BCC,
						MAX (CASE WHEN thah.TPH_REMARK IS NULL THEN 'AUTOMATIC' ELSE NULL END) STATUS_TPH,
						MAX (TO_CHAR(ATTACHMENT)) PICTURE_NAME,
						MAX (TO_CHAR(thah.TPH_REMARK)) KETERANGAN_QRCODE,
                         SUM (CASE WHEN thaid.CATEGORY_CODE IN('BM','BK','MS','OR','BB','JK','BA') THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) JJG_PANEN,
                         SUM (CASE WHEN thaid.CATEGORY_CODE = 'BM' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) EBCC_JML_BM,
                         SUM (CASE WHEN thaid.CATEGORY_CODE = 'BK' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) EBCC_JML_BK,
                         SUM (CASE WHEN thaid.CATEGORY_CODE = 'MS' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) EBCC_JML_MS,
                         SUM (CASE WHEN thaid.CATEGORY_CODE = 'OR' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) EBCC_JML_OR,
                         SUM (CASE WHEN thaid.CATEGORY_CODE = 'BB' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) EBCC_JML_BB,
                         SUM (CASE WHEN thaid.CATEGORY_CODE = 'JK' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) EBCC_JML_JK,
                         SUM (CASE WHEN thaid.CATEGORY_CODE = 'BA' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) EBCC_JML_BA,
                         SUM (CASE WHEN thaid.CATEGORY_CODE = 'BRD' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) EBCC_JML_BRD
					FROM EHARVESTING.TR_HARVEST_ACTIVITY_H thah
                     LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_USER_D thaud
                        ON thaud.HARVEST_ACTIVITY_ID = thah.ID 
                     LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_ITEM_D thaid 
                        ON thaid.HARVEST_ACTIVITY_ID = thah.ID AND thaid.HARVEST_ACTIVITY_ITEM_TYPE = 'HRV' AND CATEGORY_IS_HARVESTED = 1
					WHERE
						REPLACE(thah.HARVEST_ACTIVITY_CODE,'.') = '{$get->ebcc_no_bcc}'
					GROUP BY TO_CHAR(thah.TRANSACTION_TIME,'YYYYMMDD'),TRUNC(thah.TRANSACTION_TIME),thah.BLOCK_CODE,thah.TPH_NAME,thah.HARVEST_ACTIVITY_CODE,
	                                          thah.BLOCK_NAME,thah.BA_NAME,thah.AFDELING_NAME ";
						
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
				if($query_ebcc->source=='EBCC'){
					$joindata['ebcc_picture_name'] = ( $query_ebcc->picture_name != null ? 'http://tap-motion.tap-agri.com/ebcc/array/uploads'.$query_ebcc->picture_name : url( 'assets/dummy-janjang.jpg' ) );
				}else{
					$joindata['ebcc_picture_name'] = ( $query_ebcc->picture_name != null ? $query_ebcc->picture_name : url( 'assets/dummy-janjang.jpg' ) );
				}
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
		$where2_hrv = str_replace('id_ba_afd_blok','thah.BLOCK_CODE',$where2);
		
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
                                                FROM mobile_inspection.tr_ebcc_validation_h/**/ ebcc_header LEFT JOIN mobile_inspection.tm_user_auth/**/ user_auth
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
                                                FROM tap_dw.t_kualitas_panen@dwh_link kualitas LEFT JOIN mobile_inspection.tr_ebcc_validation_d/**/ ebcc_detail
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
       NVL (ebcc.f_get_hasil_panen_bunch                                                                                                                                                            /**/
                                         (id_ba,
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
  FROM    (  SELECT hrp.tanggal_rencana,
                    SUBSTR (id_ba_afd_blok, 1, 4) id_ba,
                    SUBSTR (id_ba_afd_blok, 5, 1) id_afd,
                    SUBSTR (id_ba_afd_blok, 6, 3) id_blok,
                    /*SUBSTR (no_bcc, 12, 3) no_tph,*/
                    hp.no_tph no_tph,
                    CASE WHEN hrp.tanggal_rencana <= cut_off_date THEN NULL ELSE NVL (hp.kode_delivery_ticket, '-') END kode_delivery_ticket,
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
               FROM ebcc.t_header_rencana_panen                                                                                                                                                     /**/
                                               hrp
                    LEFT JOIN ebcc.t_detail_rencana_panen                                                                                                                                           /**/
                                                         drp
                       ON hrp.id_rencana = drp.id_rencana
                    LEFT JOIN ebcc.t_hasil_panen                                                                                                                                                    /**/
                                                hp
                       ON hp.id_rencana = drp.id_rencana AND hp.no_rekap_bcc = drp.no_rekap_bcc
                    LEFT JOIN ebcc.t_employee                                                                                                                                                       /**/
                                             emp_ebcc
                       ON emp_ebcc.nik = hrp.nik_kerani_buah
                    LEFT JOIN ebcc.t_employee                                                                                                                                                       /**/
                                             emp_ebcc1
                       ON emp_ebcc1.nik = hrp.nik_mandor
                    LEFT JOIN ebcc.t_hasilpanen_kualtas                                                                                                                                             /**/
                                                       thk
                       ON hp.no_bcc = thk.id_bcc AND hp.id_rencana = thk.id_rencana
                    LEFT JOIN (SELECT TO_DATE (parameter_desc, 'dd-mon-yyyy') cut_off_date
                                 FROM tm_parameter
                                WHERE parameter_name = 'CUT_OFF_DELIVERY_TICKET') param
                       ON 1 = 1
              WHERE hrp.tanggal_rencana BETWEEN TO_DATE ('$START_DATE', 'YYYY-MM-DD') AND TO_DATE ('$END_DATE', 'YYYY-MM-DD')
                    AND SUBSTR (id_ba_afd_blok, 1, 4) = NVL ('$BA_CODE', SUBSTR (id_ba_afd_blok, 1, 4))
                    $where2
           GROUP BY hrp.tanggal_rencana,
                    id_ba_afd_blok,
                    hp.no_tph,
                    CASE WHEN hrp.tanggal_rencana <= cut_off_date THEN NULL ELSE NVL (hp.kode_delivery_ticket, '-') END) ebcc_hrv
       LEFT JOIN
          mobile_inspection.tm_parameter param_hrv
       ON ebcc_hrv.tanggal_rencana >= TO_DATE (parameter_desc, 'dd-mon-yyyy') AND ebcc_hrv.id_ba = param_hrv.parameter_name AND param_hrv.parameter_group = 'VALIDASI_ASKEP_EHARVESTING'
 WHERE param_hrv.parameter_name IS NULL
UNION
SELECT tanggal_rencana,
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
       ebcc_jjg_panen jjg_panen,
       ebcc_jml_bm,
       ebcc_jml_bk,
       ebcc_jml_ms,
       ebcc_jml_or,
       ebcc_jml_bb,
       ebcc_jml_jk,
       ebcc_jml_ba,
       cut_off_date
  FROM (  SELECT TRUNC (thah.TRANSACTION_TIME) tanggal_rencana,
                 thah.BA_CODE id_ba,
                 SUBSTR (thah.BLOCK_CODE, 5, 1) id_afd,
                 SUBSTR (thah.BLOCK_CODE, 6, 3) id_blok,
                 /*SUBSTR (no_bcc, 12, 3) no_tph,*/
                 thah.TPH_NAME no_tph,
                 NVL (thah.DELIVERY_TICKET_CODE, '-') kode_delivery_ticket,
                 COUNT (DISTINCT thah.HARVEST_ACTIVITY_CODE) jlh_ebcc,
                 thaud_krani.user_nik nik_kerani_buah,
                 thaud_krani.user_fullname nama_kerani_buah,
                 thaud_mandor.user_nik nik_mandor,
                 thaud_mandor.user_fullname nama_mandor,
                 MAX (REPLACE (thah.HARVEST_ACTIVITY_CODE, '.')) no_bcc,
                 MAX (CASE WHEN thah.TPH_REMARK IS NULL THEN 'AUTOMATIC' ELSE NULL END) status_tph,
                 MAX (SUBSTR (REPLACE (thah.HARVEST_ACTIVITY_CODE, '.'), 1, 11)) no_rekap_bcc,
                 SUM (CASE WHEN thaid.CATEGORY_CODE = 'BM' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) ebcc_jml_bm,
                 SUM (CASE WHEN thaid.CATEGORY_CODE = 'BK' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) ebcc_jml_bk,
                 SUM (CASE WHEN thaid.CATEGORY_CODE = 'MS' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) ebcc_jml_ms,
                 SUM (CASE WHEN thaid.CATEGORY_CODE = 'OR' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) ebcc_jml_or,
                 SUM (CASE WHEN thaid.CATEGORY_CODE = 'BB' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) ebcc_jml_bb,
                 SUM (CASE WHEN thaid.CATEGORY_CODE = 'JK' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) ebcc_jml_jk,
                 SUM (CASE WHEN thaid.CATEGORY_CODE = 'BA' THEN thaid.HARVEST_ACTIVITY_ITEM_VALUE ELSE 0 END) ebcc_jml_ba,
                 SUM (thaid.HARVEST_ACTIVITY_ITEM_VALUE) ebcc_jjg_panen,
                 cut_off_date
            FROM EHARVESTING.TR_HARVEST_ACTIVITY_H thah
                 LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_ITEM_D thaid
                    ON thaid.HARVEST_ACTIVITY_ID = thah.ID AND thaid.HARVEST_ACTIVITY_ITEM_TYPE = 'HRV' AND CATEGORY_IS_HARVESTED = 1
                 LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_USER_D thaud_krani
                    ON thaud_krani.HARVEST_ACTIVITY_ID = thah.ID AND thaud_krani.ACTIVITY_USER_TYPE = 'KRANI'
                 LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_USER_D thaud_mandor
                    ON thaud_mandor.HARVEST_ACTIVITY_ID = thah.ID AND thaud_mandor.ACTIVITY_USER_TYPE = 'MANDOR'
                 LEFT JOIN (SELECT TO_DATE (parameter_desc, 'dd-mon-yyyy') cut_off_date
                              FROM tm_parameter
                             WHERE parameter_name = 'CUT_OFF_DELIVERY_TICKET') param
                    ON 1 = 1
           WHERE TRUNC (thah.TRANSACTION_TIME) BETWEEN TO_DATE ('$START_DATE', 'YYYY-MM-DD') AND TO_DATE ('$END_DATE', 'YYYY-MM-DD') AND thah.BA_CODE = NVL ('$BA_CODE', thah.BA_CODE)
                 $where2_hrv
        GROUP BY TRUNC (thah.TRANSACTION_TIME),
                 thah.BA_CODE,
                 BLOCK_CODE,
                 thah.tph_name,
                 NVL (thah.DELIVERY_TICKET_CODE, '-'),
                 thaud_krani.user_nik,
                 thaud_krani.user_fullname,
                 thaud_mandor.user_nik,
                 thaud_mandor.user_fullname,
                 cut_off_date) ebcc_hrv LEFT JOIN mobile_inspection.tm_parameter param_hrv
          ON ebcc_hrv.tanggal_rencana >= TO_DATE (parameter_desc, 'dd-mon-yyyy') AND ebcc_hrv.id_ba = param_hrv.parameter_name AND param_hrv.parameter_group = 'VALIDASI_ASKEP_EHARVESTING'
 WHERE param_hrv.parameter_name IS NOT NULL) ebcc_me
                              ON     TRUNC (ebcc_me.tanggal_rencana) = TRUNC (val_date_time)
                                 AND ebcc_me.id_ba = val_werks                                                                                     
                                 AND ebcc_me.id_afd = val_afd_code
                                 AND ebcc_me.id_blok = val_block_code
                                 AND ebcc_me.no_tph = val_tph_code
                                 AND CASE WHEN TRUNC (ebcc_me.tanggal_rencana) <= TRUNC (cut_off_date) THEN NVL (val_delivery_ticket, '-') ELSE nvl(ebcc_me.kode_delivery_ticket,'-') END = NVL (val_delivery_ticket, '-')
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
               CASE WHEN tph.werks is not null then 'INACTIVE' ELSE 'ACTIVE' END status_tph,
			   end_ins_time jam_input_ebcc
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
				LEFT JOIN ebcc.t_timestamp
			 ON ebcc_no_bcc = id_timestamp           
";


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
				$joindata[$i]['jam_input_sampling'] = date( 'Y-m-d H:i:s', strtotime( $ec->val_date_time ) );
				$joindata[$i]['jam_input_ebcc'] = $ec->jam_input_ebcc;
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
	
	public function FINDING( $REPORT_TYPE , $START_DATE , $END_DATE , $FINDING_TYPE , $REGION_CODE , $COMP_CODE , $BA_CODE , $AFD_CODE , $BLOCK_CODE ) {
		$START_DATE = date( 'Y-m-d', strtotime( $START_DATE ) );
		$END_DATE = $END_DATE ? date( 'Y-m-d', strtotime( $END_DATE ) ) : date( 'Y-m-d', strtotime( $START_DATE ) );
		$where = "";
		$where .= ( $FINDING_TYPE != "" ) ? " AND FINDING.FINDING_CATEGORY $FINDING_TYPE  ": "";
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
                ROAD_CODE,
                ROAD_NAME,
                CASE WHEN FINDING_CATEGORY LIKE 'IF%' THEN 'INFRA' ELSE 'BLOCK' END finding_type,
				STATUS, '".url( 'preview/finding/' )."/'||FINDING_CODE link_foto
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
						FINDING.ROAD_CODE,
						FINDING.ROAD_NAME,
                        FINDING.FINDING_CATEGORY,
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
										regexp_replace((CASE WHEN CASE WHEN lu.maturity_status = 'TBM 0' THEN tbm0 WHEN lu.maturity_status = 'TBM 1' THEN tbm1 WHEN lu.maturity_status = 'TBM 2' THEN tbm2 WHEN lu.maturity_status = 'TBM 3' THEN tbm3 WHEN lu.maturity_status = 'TM' THEN tm END = 'NO' THEN NULL ELSE VALUE END), '[^0-9]+', '') VALUE
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
		// echo $sql;
		// die;
		
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
										regexp_replace((CASE WHEN CASE WHEN lu.maturity_status = 'TBM 0' THEN tbm0 WHEN lu.maturity_status = 'TBM 1' THEN tbm1 WHEN lu.maturity_status = 'TBM 2' THEN tbm2 WHEN lu.maturity_status = 'TBM 3' THEN tbm3 WHEN lu.maturity_status = 'TM' THEN tm END = 'NO' THEN NULL ELSE VALUE END), '[^0-9]+', '') VALUE
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
		$get = $client->request( 'GET', $this->url_api_ins_msa_point.'api/v1.1/point/report/'.date_format($last_day,'Ymt'),
									//'http://apisqa.tap-agri.com/mobileinspectionqa/ins-msa-qa-point/api/v1.1/point/report/'.date_format($last_day,'Ymt'),
									[
									 'headers' => [
										'Accept' => 'application/json',
										'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' ),
												]		
									]
									);
		if ($get->getStatusCode() == '200'){
			$get = json_decode( $get->getBody(), true );
		};	
		
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
		$get = $client->request( 'GET', $this->url_api_ins_msa_point.'/api/v1.1/history/report/'.date_format($last_day,'Ymt'),
										[
										 'headers' => [
											'Accept' => 'application/json',
											'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' ),
													]
										]
										);
		$get = json_decode( $get->getBody(), true );
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
                              WHEN user_role IN ('SEM_GM') THEN 2
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
                                        WHEN user_role IN ('SEM_GM') THEN 2
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
                                                    FROM mobile_inspection.v_tm_user_auth/**/ hd
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
                                          FROM mobile_inspection.tr_block_inspection_h/**/ insp_h LEFT JOIN mobile_inspection.tm_user_auth/**/ user_auth
                                                  ON insp_h.insert_user = user_auth.user_auth_code
                                                LEFT JOIN (  SELECT DISTINCT block_inspection_code FROM mobile_inspection.tr_inspection_genba/**/) genba
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
                                                  FROM (SELECT DISTINCT block_inspection_code, genba_user FROM mobile_inspection.tr_inspection_genba/**/
                                                        UNION
                                                        SELECT DISTINCT hd.block_inspection_code, insert_user
                                                          FROM mobile_inspection.tr_block_inspection_h/**/ hd JOIN mobile_inspection.tr_inspection_genba/**/ genba
                                                                  ON hd.block_inspection_code = genba.block_inspection_code
                                                         WHERE     1 = 1
                                                               AND TRUNC (inspection_date) BETWEEN TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 7 AND TO_DATE ('$START_DATE', 'dd-mm-yyyy') - 1
                                                               AND hd.werks = NVL ('$BA_CODE', hd.werks)
                                                               AND hd.afd_code = NVL ('$AFD_CODE', hd.afd_code)
                                                               AND hd.block_code = NVL ('$BLOCK_CODE', hd.block_code)) genba
                                                       INNER JOIN (SELECT DISTINCT werks, TRUNC (inspection_date) inspection_date, block_inspection_code
                                                                     FROM mobile_inspection.tr_block_inspection_h/**/ hd
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
	
	public function FINDING_PREVIEW( $id ) {
		$sql = "
			SELECT finding_code,
				   region_code,
				   comp_code,
				   werks,
				   est_name,
				   afd_code,
				   block_code,
				   block_name,
				   tanggal_temuan,
				   creator_employee_nik,
				   creator_employee_fullname,
				   creator_employee_position,
				   maturity_status,
				   lat_finding,
				   long_finding,
				   category_code,
				   nvl(category_name,'Tidak ditentukan')category_name,
				   category_icon,
				   finding_priority,
				   nvl(to_char(due_date,'YYYY-MM-DD'),'Tidak ditentukan') due_date,
				   pic_employee_nik,
				   pic_employee_fullname,
				   pic_employee_position,
				   end_time,
				   rating_value,
				   rating_message,
				   update_time,
				   progress,
				   replace(finding_desc ,chr(92)||'n','') finding_desc,
				   status
			  FROM (SELECT finding.finding_code,
						   est.region_code,
						   est.comp_code,
						   est.est_code,
						   finding.werks,
						   est.est_name,
						   finding.afd_code,
						   block.block_code,
						   block.block_name,
						   finding.insert_time AS tanggal_temuan,
						   emp_creator.nik AS creator_employee_nik,
						   emp_creator.employee_name AS creator_employee_fullname,
						   REPLACE (user_auth_creator.user_role, '_', ' ') AS creator_employee_position,
						   land_use.maturity_status AS maturity_status,
						   finding.lat_finding,
						   finding.long_finding,
						   category.category_code,
						   category.category_name,
						   category.icon AS category_icon,
						   finding.finding_priority,
						   finding.due_date,
						   emp_pic.nik AS pic_employee_nik,
						   emp_pic.employee_name AS pic_employee_fullname,
						   REPLACE (user_auth_pic.user_role, '_', ' ') AS pic_employee_position,
						   finding.end_time,
						   finding.rating_value,
						   finding.rating_message,
						   finding.update_time,
						   finding.progress,
						   finding.finding_desc,
						   CASE WHEN finding.progress = 100 THEN 'SELESAI' ELSE 'BELUM SELESAI' END AS status
					  FROM mobile_inspection.tr_finding finding
						   LEFT JOIN mobile_inspection.tm_category category
							  ON category.category_code = finding.finding_category
						   LEFT JOIN tap_dw.tm_block@dwh_link block
							  ON block.werks = finding.werks AND block.block_code = finding.block_code AND TRUNC (finding.insert_time) BETWEEN block.start_valid AND block.end_valid
						   LEFT JOIN tap_dw.tm_est@dwh_link est
							  ON est.werks = finding.werks AND TRUNC (finding.insert_time) BETWEEN est.start_valid AND est.end_valid
						   LEFT JOIN mobile_inspection.tm_user_auth user_auth_creator
							  ON user_auth_creator.user_auth_code = (CASE WHEN LENGTH (finding.insert_user) = 3 THEN '0' || finding.insert_user ELSE finding.insert_user END)
						   LEFT JOIN (SELECT employee_nik nik,
											 employee_fullname employee_name,
											 employee_position job_code,
											 employee_joindate AS start_date,
											 CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD') ELSE employee_resigndate END AS end_date
										FROM tap_dw.tm_employee_hris@dwh_link
									  UNION ALL
									  SELECT nik,
											 employee_name,
											 job_code,
											 start_valid start_date,
											 CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_date
										FROM tap_dw.tm_employee_sap@dwh_link) emp_creator
							  ON emp_creator.nik = user_auth_creator.employee_nik AND TRUNC (finding.insert_time) BETWEEN emp_creator.start_date AND emp_creator.end_date
						   LEFT JOIN mobile_inspection.tm_user_auth user_auth_pic
							  ON user_auth_pic.user_auth_code = (CASE WHEN LENGTH (finding.assign_to) = 3 THEN '0' || finding.assign_to ELSE finding.assign_to END)
						   LEFT JOIN (SELECT employee_nik nik,
											 employee_fullname employee_name,
											 employee_position job_code,
											 employee_joindate AS start_date,
											 CASE WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD') ELSE employee_resigndate END AS end_date
										FROM tap_dw.tm_employee_hris@dwh_link
									  UNION ALL
									  SELECT nik,
											 employee_name,
											 job_code,
											 start_valid start_date,
											 CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_date
										FROM tap_dw.tm_employee_sap@dwh_link) emp_pic
							  ON emp_pic.nik = user_auth_pic.employee_nik AND TRUNC (finding.insert_time) BETWEEN emp_pic.start_date AND emp_pic.end_date
						   LEFT JOIN (  SELECT werks,
											   afd_code,
											   land_use_code block_code,
											   block_name,
											   maturity_status,
											   spmon
										  FROM tap_dw.tr_hs_land_use@dwh_link
										 WHERE spmon BETWEEN TRUNC (ADD_MONTHS (SYSDATE, -1), 'YEAR') AND LAST_DAY (ADD_MONTHS (SYSDATE, -1))
									  GROUP BY werks,
											   afd_code,
											   land_use_code,
											   block_name,
											   maturity_status,
											   spmon
									  UNION
										SELECT werks,
											   afd_code,
											   land_use_code block_code,
											   block_name,
											   maturity_status,
											   ADD_MONTHS (spmon, 1) spmon
										  FROM tap_dw.tr_hs_land_use@dwh_link
										 WHERE spmon = (  SELECT MAX (spmon) FROM tap_dw.tr_hs_land_use@dwh_link)
									  GROUP BY werks,
											   afd_code,
											   land_use_code,
											   block_name,
											   maturity_status,
											   spmon) land_use
							  ON finding.werks = land_use.werks AND land_use.afd_code = finding.afd_code AND land_use.block_code = finding.block_code AND spmon = TRUNC (LAST_DAY (finding.insert_time))
					 WHERE finding.finding_code = '".$id."') finding
		";
		$get = collect( $this->db_mobile_ins->select( $sql ) )->first();
		$joindata = array();
		if ( !empty( $get ) ) {
			$client = new \GuzzleHttp\Client();
			$image_finding = $client->request( 'GET', $this->url_api_ins_msa_image.'/api/v2.2/finding/'.$get->finding_code,
									[
									 'headers' => [
										'Accept' => 'application/json',
										'Authorization' => 'Bearer '.session( 'ACCESS_TOKEN' ),
												]		
									] );
			$image_finding = json_decode( $image_finding->getBody(), true );
			$joindata['finding_code'] = $get->finding_code;
			$joindata['region_code'] = $get->region_code;
			$joindata['comp_code'] = $get->comp_code;
			$joindata['werks'] = $get->werks;
			$joindata['est_name'] = $get->est_name;
			$joindata['afd_code'] = $get->afd_code;
			$joindata['block_code'] = $get->block_code;
			$joindata['block_name'] = $get->block_name;
			$joindata['tanggal_temuan'] = $get->tanggal_temuan;
			$joindata['creator_employee_nik'] = $get->creator_employee_nik;
			$joindata['creator_employee_fullname'] = $get->creator_employee_fullname;
			$joindata['creator_employee_position'] = $get->creator_employee_position;
			$joindata['maturity_status'] = $get->maturity_status;
			$joindata['lat_finding'] = $get->lat_finding;
			$joindata['long_finding'] = $get->long_finding;
			$joindata['category_code'] = $get->category_code;
			$joindata['category_name'] = $get->category_name;
			$joindata['category_icon'] = $get->category_icon;
			$joindata['finding_priority'] = $get->finding_priority;
			$joindata['due_date'] = $get->due_date;
			$joindata['pic_employee_nik'] = $get->pic_employee_nik;
			$joindata['pic_employee_fullname'] = $get->pic_employee_fullname;
			$joindata['pic_employee_position'] = $get->pic_employee_position;
			$joindata['end_time'] = $get->end_time;
			$joindata['rating_value'] = $get->rating_value;
			$joindata['rating_message'] = $get->rating_message;
			$joindata['update_time'] = $get->update_time;
			$joindata['progress'] = $get->progress;
			$joindata['finding_desc'] = $get->finding_desc;
			if (isset($image_finding['message']) & $image_finding['message'] == "Invalid Token"){
				$joindata['finding_desc'] .= " (Silahkan login untuk lihat photo)";
			}
			$joindata['status'] = $get->status;
			$joindata['image'] = ( isset( $image_finding['data'] ) ? $image_finding['data'] : url( 'assets/user.jpg' ) );
		}
		return $joindata;
	} 
	
	public function MONITORING_UPLOAD_EBCC($tgl,$reg,$comp) {
		$reg_hrv = strlen($reg)<1?'':"AND thah.REGION_CODE = '$reg'";
		$comp_hrv = strlen($comp)<1?'':"AND thah.COMPANY_CODE = '$comp'";
		$reg = strlen($reg)<1?'':"AND comp.REGION_CODE = '$reg'";
		$comp = strlen($comp)<1?'':"AND COMP.comp_code = '$comp'";
		$get = $this->db_mobile_ins->select("SELECT to_char(TANGGAL_RENCANA,'DD-MON') TANGGAL, COMP_NAME, ID_BA, AFD, NIK_KERANI_BUAH,COUNT(DISTINCT NIK_MANDOR) MANDOR,
												EMPLOYEE_NAME NAME,
												JOB_CODE USER_ROLE,
												SUM(CASE WHEN SYNC_SERVER BETWEEN TRUNC(TANGGAL_RENCANA) AND TRUNC(TANGGAL_RENCANA)+1+1/24 THEN 1 ELSE 0 END) COUNT1,
												SUM(CASE WHEN SYNC_SERVER BETWEEN TRUNC(TANGGAL_RENCANA)+1+1/24 AND TRUNC(TANGGAL_RENCANA)+1+14/24 THEN 1 ELSE 0 END) COUNT2,
												COUNT(NO_BCC) total
										FROM (
												SELECT 
													tlhp.SYNC_SERVER SYNC_SERVER, 
													thrp.TANGGAL_RENCANA TANGGAL_RENCANA,
													(SUBSTR(MIN(tdrp.ID_BA_AFD_BLOK),1,4) || ' - ' || NAMA_BA) ID_BA,
													SUBSTR(MIN(tdrp.ID_BA_AFD_BLOK),5,1) AFD,
													thrp.NIK_KERANI_BUAH,
													thrp.NIK_MANDOR,
													(comp.COMP_CODE || ' - ' || comp.COMP_NAME) COMP_NAME,
													JOB_CODE,
													(EMPLOYEE_NAME || ' - ' || thrp.NIK_KERANI_BUAH) EMPLOYEE_NAME,
													THP.NO_BCC 
												FROM EBCC.T_HASIL_PANEN thp 
												INNER JOIN EBCC.T_HEADER_RENCANA_PANEN thrp ON thrp.ID_RENCANA = thp.ID_RENCANA  
												INNER JOIN EBCC.T_DETAIL_RENCANA_PANEN tdrp ON tdrp.ID_RENCANA = thrp.ID_RENCANA 
												INNER JOIN EBCC.T_BUSSINESSAREA tb ON tb.ID_BA = SUBSTR(tdrp.ID_BA_AFD_BLOK,1,4) 
												LEFT JOIN EBCC.T_LOG_HASIL_PANEN tlhp ON tlhp.ON_NO_BCC = thp.NO_BCC AND tlhp.INSERTUPDATE = 'INSERT'
												LEFT JOIN tap_dw.tm_comp@dwh_link comp ON comp.comp_code = SUBSTR(tdrp.ID_BA_AFD_BLOK,1,2)
												LEFT JOIN tap_dw.tm_employee_sap@dwh_link emp ON emp.NIK = NIK_KERANI_BUAH AND start_valid <= sysdate AND end_valid >= sysdate 
												WHERE 
													thrp.TANGGAL_RENCANA = TO_DATE ('$tgl', 'dd-mm-yyyy')  $reg $comp
												GROUP BY tlhp.SYNC_SERVER,NAMA_BA,thrp.TANGGAL_RENCANA,thrp.NIK_KERANI_BUAH,thrp.NIK_MANDOR,JOB_CODE,EMPLOYEE_NAME,thp.NO_BCC,comp.COMP_CODE,comp.COMP_NAME,THP.NO_BCC,SUBSTR(tdrp.ID_BA_AFD_BLOK,1,5) 

												UNION 
																							        
												SELECT 
													thah.SYNC_TIME SYNC_SERVER, 
													TRUNC(thah.TRANSACTION_TIME) TANGGAL_RENCANA,
													(SUBSTR(thah.BLOCK_CODE,1,4) || ' - ' || thah.BA_NAME) ID_BA,
													SUBSTR(thah.BLOCK_CODE,5,1) AFD,
													MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_nik ELSE NULL END ) NIK_KERANI_BUAH,
													MAX (CASE WHEN thaud.activity_user_type = 'MANDOR' THEN thaud.user_nik ELSE NULL END ) NIK_MANDOR,
													(thah.COMPANY_CODE || ' - ' || thah.COMPANY_NAME) COMP_NAME,
													MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.JOB_CODE ELSE NULL END ) JOB_CODE,
													MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_fullname ELSE NULL END ) || ' - ' || 
														MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_nik ELSE NULL END ) EMPLOYEE_NAME,
													REPLACE(thah.HARVEST_ACTIVITY_CODE,'.') NO_BCC 
												FROM EHARVESTING.TR_HARVEST_ACTIVITY_H thah
									            LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_USER_D thaud
									            ON thaud.HARVEST_ACTIVITY_ID = thah.ID  
												WHERE 
													TRUNC(thah.TRANSACTION_TIME) = TO_DATE ('$tgl', 'dd-mm-yyyy')  $reg_hrv $comp_hrv
												GROUP BY thah.SYNC_TIME,
														 thah.HARVEST_ACTIVITY_CODE,thah.LHM_PRINTED_TIME,
												         thah.TRANSACTION_TIME,thah.BLOCK_CODE,thah.BA_NAME,
												         thah.COMPANY_CODE,thah.COMPANY_NAME
											) head
										GROUP BY TANGGAL_RENCANA, COMP_NAME, ID_BA,AFD,NIK_KERANI_BUAH,EMPLOYEE_NAME,JOB_CODE
										ORDER BY 1,2,3,4,5 ASC
		");
		return $get;
	}
	
	public function MONITORING_SYNC_MI($tgl,$reg,$comp) {
		$where = strlen($reg)<1?'':"AND COMP.REGION_CODE = '$reg'";
		$where .= strlen($comp)<1?'':"AND COMP.COMP_CODE = '$comp'";
		$get = $this->db_mobile_ins->select("SELECT 	to_char(TGL_PANEN,'DD-MON-YYYY') as TANGGAL,
														COMP as COMP_NAME,
														BA ID_BA,
														AFD_PANEN as AFD,
														PIC NAME,
														JABATAN as USER_ROLE,
														JML_KEMANDORAN as MANDOR,
														GROUP_SYNC_1 as COUNT1,
														GROUP_SYNC_2 as COUNT2,
														TOTAL_BCC as TOTAL
												FROM (
													SELECT	VALIDATION.TGL_PANEN,
															VALIDATION.COMP,
															VALIDATION.BA,
															VALIDATION.AFD_PANEN,
															VALIDATION.PIC,
															VALIDATION.JABATAN,
															MAX(EBCC.JML_KEMANDORAN) as JML_KEMANDORAN,
															SUM(GROUP_SYNC_1) as GROUP_SYNC_1,
															SUM(GROUP_SYNC_2) as GROUP_SYNC_2,
															SUM(TOTAL_BCC) as TOTAL_BCC
													FROM(
														SELECT	TGL_PANEN,
																COMP,
																BA,
																AFD_PANEN,
																PIC,
																JABATAN,
																SUM(
																	CASE
																		WHEN SYNC_SERVER BETWEEN TRUNC(TGL_PANEN) AND TRUNC(TGL_PANEN)+1+1/24 
																		THEN 1
																		ELSE 0
																	END
																) as GROUP_SYNC_1,
																SUM( 
																	CASE
																		WHEN SYNC_SERVER BETWEEN TRUNC(TGL_PANEN)+1+1/24 + INTERVAL '1' SECOND AND TRUNC(TGL_PANEN)+1+14/24
																		THEN 1
																		ELSE 0
																	END 
																) as GROUP_SYNC_2,
																SUM(1) as TOTAL_BCC
														FROM (		
															SELECT 	TRUNC(HEADER.INSERT_TIME) as TGL_PANEN,
																	EST.COMP_CODE || ' - ' || COMP.COMP_NAME as COMP,
																	HEADER.WERKS || ' - ' || EST.EST_NAME as BA,
																	HEADER.AFD_CODE as AFD_PANEN,
																	EMP.EMPLOYEE_FULLNAME || ' - ' || AUTH.EMPLOYEE_NIK as PIC,
																	REPLACE(AUTH.USER_ROLE, '_', ' ') as JABATAN,
																	HEADER.EBCC_VALIDATION_CODE,
																	HEADER.SYNC_TIME as SYNC_SERVER
															FROM(
																SELECT *
																FROM MOBILE_INSPECTION.TR_EBCC_VALIDATION_H
																/* FILTER TANGGAL & LOKASI DISINI */
																WHERE TRUNC(INSERT_TIME) = TO_DATE ('$tgl', 'dd-mm-yyyy')
															) HEADER
															LEFT JOIN MOBILE_INSPECTION.TM_USER_AUTH AUTH
																ON AUTH.USER_AUTH_CODE =
																(CASE
																	WHEN LENGTH (HEADER.INSERT_USER) = 3 THEN '0' || HEADER.INSERT_USER
																	ELSE HEADER.INSERT_USER
																END)
															LEFT JOIN (
																SELECT 	EMPLOYEE_NIK,
																		EMPLOYEE_FULLNAME,
																		EMPLOYEE_POSITION,
																		EMPLOYEE_JOINDATE as START_DATE,
																		CASE 
																			WHEN EMPLOYEE_RESIGNDATE IS NULL 
																				THEN TO_DATE ('99991231', 'RRRRMMDD')
																			ELSE EMPLOYEE_RESIGNDATE
																		END as END_DATE
																FROM TAP_DW.TM_EMPLOYEE_HRIS@DWH_LINK
																UNION ALL
																SELECT	NIK,
																		EMPLOYEE_NAME,
																		JOB_CODE,
																		START_VALID,
																		CASE
																			WHEN RES_DATE IS NOT NULL
																				THEN RES_DATE
																			ELSE END_VALID
																		END as END_VALID
																FROM TAP_DW.TM_EMPLOYEE_SAP@DWH_LINK
															) EMP
																ON EMP.EMPLOYEE_NIK = AUTH.EMPLOYEE_NIK
																AND TRUNC(HEADER.INSERT_TIME) BETWEEN TRUNC(EMP.START_DATE) AND TRUNC(EMP.END_DATE)
															LEFT JOIN TAP_DW.TM_EST@DWH_LINK EST
																ON EST.WERKS = HEADER.WERKS
																AND TRUNC(HEADER.INSERT_TIME) BETWEEN TRUNC(EST.START_VALID) AND TRUNC(EST.END_VALID)
															LEFT JOIN TAP_DW.TM_COMP@DWH_LINK COMP
																ON COMP.COMP_CODE = EST.COMP_CODE
															WHERE AUTH.USER_ROLE IN ('SEM_GM', 'EM', 'KEPALA_KEBUN', 'ASISTEN_LAPANGAN')
															$where
															GROUP BY TRUNC(HEADER.INSERT_TIME),
																	EST.COMP_CODE || ' - ' || COMP.COMP_NAME,
																	HEADER.WERKS || ' - ' || EST.EST_NAME,
																	HEADER.AFD_CODE,
																	EMP.EMPLOYEE_FULLNAME || ' - ' || AUTH.EMPLOYEE_NIK,
																	REPLACE(AUTH.USER_ROLE, '_', ' '),
																	HEADER.EBCC_VALIDATION_CODE,
																	HEADER.SYNC_TIME
														)
														GROUP BY TGL_PANEN,
																COMP,
																BA,
																AFD_PANEN,
																PIC,
																JABATAN
													) VALIDATION				
													LEFT JOIN (
														SELECT 	BA_AFD,
																COUNT(1) as JML_KEMANDORAN
														FROM (
															SELECT 	SUBSTR(ID_BA_AFD_BLOK,1,5) as BA_AFD, 
																	NIK_MANDOR
															FROM(
																SELECT *
																FROM EBCC.T_HEADER_RENCANA_PANEN 
																/* FILTER TANGGAL */
																WHERE TRUNC(TANGGAL_RENCANA) = TO_DATE ('$tgl', 'dd-mm-yyyy')
															) THRP
															INNER JOIN EBCC.T_DETAIL_RENCANA_PANEN TDRP
																ON TDRP.ID_RENCANA = THRP. ID_RENCANA
															GROUP BY SUBSTR(ID_BA_AFD_BLOK,1,5), NIK_MANDOR
														)
														/* FILTER LOKASI DISINI */
														GROUP BY BA_AFD
														UNION 
														SELECT 	BA_AFD, COUNT(1) as JML_KEMANDORAN
														FROM (
															SELECT 	SUBSTR(thah.BLOCK_CODE,1,5) as BA_AFD, 
																	thaud.user_nik NIK_MANDOR
															FROM(
																SELECT *
																FROM EHARVESTING.TR_HARVEST_ACTIVITY_H
																/* FILTER TANGGAL */
																WHERE TRUNC(TRANSACTION_TIME) = TO_DATE ('$tgl', 'dd-mm-yyyy')
															) thah
															INNER JOIN EHARVESTING.TR_HARVEST_ACTIVITY_USER_D thaud
																ON thaud.HARVEST_ACTIVITY_ID = thah.ID AND thaud.activity_user_type = 'MANDOR'
															GROUP BY SUBSTR(thah.BLOCK_CODE,1,5), thaud.user_nik
														)
														/* FILTER LOKASI DISINI */
														GROUP BY BA_AFD
													) EBCC
														ON EBCC.BA_AFD = SUBSTR(VALIDATION.BA,1,4) || VALIDATION.AFD_PANEN	
													GROUP BY VALIDATION.TGL_PANEN,
															VALIDATION.COMP,
															VALIDATION.BA,
															VALIDATION.AFD_PANEN,
															VALIDATION.PIC,
															VALIDATION.JABATAN	
												)
												ORDER BY 1,2,3,4,5
		");
		return $get;
	}
	
	public function MONITORING_CETAK_LHM($tgl,$reg,$comp) {
		$reg_hrv = strlen($reg)<1?'':"AND thah.REGION_CODE = '$reg'";
		$comp_hrv = strlen($comp)<1?'':"AND thah.COMPANY_CODE = '$comp'";
		$reg = strlen($reg)<1?'':"AND comp.REGION_CODE = '$reg'";
		$comp = strlen($comp)<1?'':"AND comp.COMP_CODE = '$comp'";
		$get = $this->db_mobile_ins->select("SELECT TANGGAL,COMP_NAME,ID_BA,AFD,MANDOR,EM_EXCEPTION,
												SUM(CASE WHEN VALIDATION.USER_ROLE IS NULL THEN 0 WHEN VALIDATION.USER_ROLE LIKE 'ASISTEN%' THEN 1 ELSE 0 end) ASLAP, 
												SUM(CASE WHEN VALIDATION.USER_ROLE IS NULL THEN 0 WHEN VALIDATION.USER_ROLE IN('SEM_GM', 'EM', 'KEPALA_KEBUN') THEN 1 ELSE 0 end) KABUN,
												ALASAN,CETAK
										FROM 
										(
											SELECT 	to_char(TANGGAL_RENCANA,'DD-MON-YYYY') TANGGAL, COMP_NAME, ID_BA, AFD,COUNT(DISTINCT NIK_MANDOR) MANDOR,
													COUNT(DISTINCT approval_em.BA) EM_EXCEPTION,
													MIN(approval_em.ALASAN) ALASAN,
													CASE WHEN MIN(head.CETAK_DATE) BETWEEN TRUNC(TANGGAL_RENCANA) AND TRUNC(TANGGAL_RENCANA)+1+14/24 THEN 'Sudah' ELSE 'Belum' END CETAK,
													head.WERKS || head.AFD BA_AFD
											FROM (
													SELECT 
														thp.NO_BCC,
														thp.CETAK_DATE, 
														thrp.TANGGAL_RENCANA,
														SUBSTR(tdrp.ID_BA_AFD_BLOK,1,4) WERKS,
														(SUBSTR(tdrp.ID_BA_AFD_BLOK,1,4) || ' - ' || NAMA_BA) ID_BA,
														SUBSTR(tdrp.ID_BA_AFD_BLOK,5,1) AFD,
														thrp.NIK_KERANI_BUAH,
														thrp.NIK_MANDOR,
														(comp.COMP_CODE || ' - ' || comp.COMP_NAME) COMP_NAME,
														JOB_CODE,
														(EMPLOYEE_NAME || ' - ' || thrp.NIK_KERANI_BUAH) EMPLOYEE_NAME
													FROM EBCC.T_HASIL_PANEN thp 
													LEFT JOIN EBCC.T_HEADER_RENCANA_PANEN thrp ON thrp.ID_RENCANA = thp.ID_RENCANA  
													INNER JOIN EBCC.T_DETAIL_RENCANA_PANEN tdrp ON tdrp.ID_RENCANA = thrp.ID_RENCANA 
													INNER JOIN EBCC.T_BUSSINESSAREA tb ON tb.ID_BA = SUBSTR(tdrp.ID_BA_AFD_BLOK,1,4) 
													INNER JOIN EBCC.T_LOG_HASIL_PANEN tlhp ON tlhp.ON_NO_BCC = thp.NO_BCC AND tlhp.INSERTUPDATE = 'INSERT'
													INNER JOIN tap_dw.tm_comp@dwh_link comp ON comp.comp_code = SUBSTR(tdrp.ID_BA_AFD_BLOK,1,2)
													LEFT JOIN tap_dw.tm_employee_sap@dwh_link emp ON emp.NIK = NIK_KERANI_BUAH AND start_valid <= sysdate AND end_valid >= sysdate 
													WHERE 
														thrp.TANGGAL_RENCANA = TO_DATE ('$tgl', 'dd-mm-yyyy')  $reg $comp
														GROUP BY thp.CETAK_DATE,(SUBSTR(tdrp.ID_BA_AFD_BLOK,1,4) || ' - ' || NAMA_BA),SUBSTR(tdrp.ID_BA_AFD_BLOK,1,4), 
														SUBSTR(tdrp.ID_BA_AFD_BLOK,5,1) ,NAMA_BA,thrp.TANGGAL_RENCANA,thrp.NIK_KERANI_BUAH,thrp.NIK_MANDOR,JOB_CODE,EMPLOYEE_NAME,thp.NO_BCC,comp.COMP_CODE,comp.COMP_NAME
													UNION 
													SELECT 
														REPLACE(thah.HARVEST_ACTIVITY_CODE,'.') NO_BCC,
														thah.LHM_PRINTED_TIME CETAK_DATE, 
														TRUNC(thah.TRANSACTION_TIME) TANGGAL_RENCANA,
														SUBSTR(thah.BLOCK_CODE,1,4) WERKS,
														(SUBSTR(thah.BLOCK_CODE,1,4) || ' - ' || thah.BA_NAME) ID_BA,
														SUBSTR(thah.BLOCK_CODE,5,1) AFD,
														MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_nik ELSE NULL END ) NIK_KERANI_BUAH,
														MAX (CASE WHEN thaud.activity_user_type = 'MANDOR' THEN thaud.user_nik ELSE NULL END ) NIK_MANDOR,
														(thah.COMPANY_CODE || ' - ' || thah.COMPANY_NAME) COMP_NAME,
														MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.JOB_CODE ELSE NULL END ) JOB_CODE,
														MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_fullname ELSE NULL END ) || ' - ' || 
														MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_nik ELSE NULL END ) EMPLOYEE_NAME
													FROM EHARVESTING.TR_HARVEST_ACTIVITY_H thah
									                LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_USER_D thaud
									                ON thaud.HARVEST_ACTIVITY_ID = thah.ID 
													WHERE trunc(thah.TRANSACTION_TIME) = TO_DATE ('$tgl', 'dd-mm-yyyy') $comp_hrv $reg_hrv
													GROUP BY thah.HARVEST_ACTIVITY_CODE,thah.LHM_PRINTED_TIME,
													         thah.TRANSACTION_TIME,thah.BLOCK_CODE,thah.BA_NAME,
													         thah.COMPANY_CODE,thah.COMPANY_NAME
												) head  
											--LEFT JOIN MOBILE_INSPECTION.TR_EBCC_COMPARE compare ON compare.EBCC_NO_BCC = head.NO_BCC 
											LEFT JOIN EBCC.T_APPROVAL_CETAK_LHM approval_em ON approval_em.BA = head.WERKS AND approval_em.AFDELING = head.AFD AND TRUNC(approval_em.TANGGAL_EBCC) = TRUNC(head.TANGGAL_RENCANA)
											GROUP BY TANGGAL_RENCANA, COMP_NAME, ID_BA,AFD,head.WERKS || head.AFD
										)
										LEFT JOIN 
											( SELECT EBCC_VALIDATION_CODE,AUTH.USER_ROLE, WERKS, AFD_CODE
											FROM MOBILE_INSPECTION.TR_EBCC_VALIDATION_H tevh
											LEFT JOIN MOBILE_INSPECTION.TM_USER_AUTH AUTH ON AUTH.USER_AUTH_CODE = (CASE
																														WHEN LENGTH (tevh.INSERT_USER) = 3 THEN '0' || tevh.INSERT_USER
																														ELSE tevh.INSERT_USER
																													END)
											WHERE TRUNC(tevh.INSERT_TIME) =  TRUNC(TO_DATE ('$tgl', 'dd-mm-yyyy'))
											GROUP BY EBCC_VALIDATION_CODE,AUTH.USER_ROLE, WERKS, AFD_CODE
											) VALIDATION
										ON VALIDATION.WERKS || VALIDATION.AFD_CODE = BA_AFD
										GROUP BY TANGGAL,COMP_NAME,ID_BA,AFD,MANDOR,EM_EXCEPTION,ALASAN,CETAK
										ORDER BY 1,2,3,4 ASC
		");
		return $get;
	}
	
	public function MONITORING_VALIDASI_DESKTOP($tgl,$reg,$comp) {
		$reg_hrv = strlen($reg)<1?'':"AND thah.REGION_CODE = '$reg'";
		$comp_hrv = strlen($comp)<1?'':"AND thah.COMPANY_CODE = '$comp'";
		$reg = strlen($reg)<1?'':"WHERE comp.REGION_CODE = '$reg'";
		$comp = strlen($comp)<1?'':"WHERE COMP.ID_CC = '$comp'";
		$get = $this->db_mobile_ins->select("SELECT TANGGAL,
													comp, 
													BA,
													AFD,
													COUNT(MANDOR) MANDOR,
													MAX(pic) pic,
													MAX(job) job,
													to_char(MIN(mulai),'HH24:mi') mulai,
													to_char(MAX(selesai),'HH24:mi') selesai,
													(24* 60*(TO_DATE(TO_CHAR(MAX(selesai), 'YYYY-MM-DD hh24:mi'), 'YYYY-MM-DD hh24:mi') - 
															TO_DATE(TO_CHAR(MIN(mulai), 'YYYY-MM-DD hh24:mi'), 'YYYY-MM-DD hh24:mi'))) durasi,
													SUM(BCC) BCC
											FROM 
											(
												SELECT 
												to_char(TGL_PANEN,'DD-MON-YYYY') TANGGAL, 
												(comp.comp_code || ' - ' || comp.comp_name) comp, 
												head.BA, 
												head.AFD_PANEN AFD,
												head.NIK_MANDOR MANDOR, 
												(MAX(tvd.INSERT_USER_FULLNAME) || ' - ' || MAX(tvd.INSERT_USER)) pic,
												MAX(INSERT_USER_USERROLE) job,
												MIN(tvd.INSERT_TIME) mulai,
												MAX(tvd.INSERT_TIME) selesai,
												COUNT(tvd.NO_BCC) BCC
												FROM (
														SELECT 	THRP.TANGGAL_RENCANA as TGL_PANEN,
																COMP.ID_CC,
																COMP.ID_CC || ' - ' || COMP.COMP_NAME as COMP,
																BA.ID_BA || ' - ' || NAMA_BA as BA,
																AFD.ID_AFD as AFD_PANEN,
																EMP.EMP_NAME || ' - ' || THRP.NIK_KERANI_BUAH as PIC,
																EMP.JOB_CODE as JABATAN,
																THRP.NIK_MANDOR,
																TDRP.ID_BA_AFD_BLOK,
																THP.NO_TPH,
																THP.NO_BCC,
																THP.KODE_DELIVERY_TICKET
														FROM(
															SELECT *
															FROM EBCC.T_HEADER_RENCANA_PANEN
															WHERE TANGGAL_RENCANA = TO_DATE ('$tgl', 'dd-mm-yyyy')
														) THRP
														INNER JOIN EBCC.T_DETAIL_RENCANA_PANEN TDRP
															ON TDRP.ID_RENCANA = THRP. ID_RENCANA
														INNER JOIN EBCC.T_HASIL_PANEN THP
															ON THP.ID_RENCANA = TDRP.ID_RENCANA
															AND THP.NO_REKAP_BCC = TDRP.NO_REKAP_BCC
														LEFT JOIN EBCC.T_BLOK BLK
															ON BLK.ID_BA_AFD_BLOK = TDRP.ID_BA_AFD_BLOK
														LEFT JOIN EBCC.T_AFDELING AFD
															ON AFD.ID_BA_AFD = BLK.ID_BA_AFD
														LEFT JOIN EBCC.T_BUSSINESSAREA BA
															ON BA.ID_BA = AFD.ID_BA
														LEFT JOIN EBCC.T_COMPANYCODE COMP
															ON COMP.ID_CC = BA.ID_CC
														LEFT JOIN EBCC.T_EMPLOYEE EMP
															ON EMP.NIK = THRP.NIK_KERANI_BUAH
														$comp
														GROUP BY THRP.TANGGAL_RENCANA,
																COMP.ID_CC,
																COMP.ID_CC || ' - ' || COMP.COMP_NAME,
																BA.ID_BA || ' - ' || NAMA_BA,
																AFD.ID_AFD,
																THRP.NIK_KERANI_BUAH,
																EMP.EMP_NAME, 
																EMP.JOB_CODE,
																THRP.NIK_MANDOR,
																TDRP.ID_BA_AFD_BLOK,
																THP.NO_TPH,
																THP.NO_BCC,
																THP.KODE_DELIVERY_TICKET
														UNION  
														SELECT 	TRUNC(thah.TRANSACTION_TIME) as TGL_PANEN,
																thah.COMPANY_CODE ID_CC,
																thah.COMPANY_CODE || ' - ' || thah.COMPANY_NAME as COMP,
																thah.BA_CODE || ' - ' || thah.BA_NAME as BA,
																thah.AFDELING_NAME as AFD_PANEN,
																MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_fullname ELSE NULL END ) || ' - ' || 
																MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_nik ELSE NULL END ) as PIC,
																MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.JOB_CODE ELSE NULL END ) as JABATAN,
																MAX (CASE WHEN thaud.activity_user_type = 'MANDOR' THEN thaud.user_nik ELSE NULL END ) NIK_MANDOR,
																thah.BLOCK_CODE ID_BA_AFD_BLOK,
																thah.TPH_NAME NO_TPH,
																MAX (REPLACE(thah.HARVEST_ACTIVITY_CODE,'.')) NO_BCC,
																thah.DELIVERY_TICKET_CODE
														FROM EHARVESTING.TR_HARVEST_ACTIVITY_H thah
									                    LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_USER_D thaud
									                    ON thaud.HARVEST_ACTIVITY_ID = thah.ID 
									                    WHERE trunc(thah.TRANSACTION_TIME) = TO_DATE ('$tgl', 'dd-mm-yyyy')
														$comp_hrv $reg_hrv
														GROUP BY TRUNC(thah.TRANSACTION_TIME),
																thah.COMPANY_CODE,
																thah.COMPANY_NAME,
																thah.BA_CODE || ' - ' || thah.BA_NAME,
																thah.AFDELING_NAME,
																thah.BLOCK_CODE,
																thah.TPH_NAME,
																thah.HARVEST_ACTIVITY_CODE,
																thah.DELIVERY_TICKET_CODE
													) head 
												LEFT JOIN tap_dw.tm_comp@dwh_link comp ON comp.comp_code = head.ID_CC
												LEFT JOIN MOBILE_INSPECTION.TR_VALIDASI_DETAIL tvd  
												ON tvd.NO_BCC = head.NO_BCC
												AND tvd.KONDISI_FOTO IS NOT NULL 
											    $reg
												GROUP BY TGL_PANEN, COMP, BA,AFD_PANEN, (comp.comp_code || ' - ' || comp.comp_name),head.NIK_MANDOR
											) GROUP BY TANGGAL,comp,BA,AFD
											ORDER BY 1,2,3,4 ASC
		");
		return $get;
	}
	
}
