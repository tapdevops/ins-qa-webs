<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class ValidasiHeader extends Model{
	
	protected $env;
	protected $db_mobile_ins;
	
	public function __construct() {
		$this->db_mobile_ins = DB::connection( 'mobile_ins' );
		$this->env = 'QA';
	}

   //filter H-1
	public function validasi_header($date){
      $day =  date("Y-m-d", strtotime($date));
      $ba_afd_code = explode(",",session('LOCATION_CODE'));
      $code = implode("','", $ba_afd_code);
		$get = $this->db_mobile_ins->select("
		SELECT ebcc.id_ba,
                        ebcc.id_afd,
                        to_char(ebcc.tanggal_rencana,'DD-MON-YYYY') AS tanggal_rencana,
                        ebcc.nik_kerani_buah,
                        ebcc.nama_krani_buah,
                        ebcc.nik_mandor,
                        ebcc.nama_mandor,
                        ebcc.id_validasi,
                        case when valid.jumlah_ebcc_validated is null then 0 else valid.jumlah_ebcc_validated end as jumlah_ebcc_validated,
                        param.parameter_desc AS target_validasi,
                        count(valid_detail.id_validasi) AS aslap_validation
                     FROM (SELECT SUBSTR (drp.id_ba_afd_blok, 1, 4) AS id_ba,
                                 SUBSTR (drp.id_ba_afd_blok, 5, 1) AS id_afd, 
                                 hrp.tanggal_rencana,
                                 hrp.nik_kerani_buah,
                                 emp_krani.emp_name AS nama_krani_buah,
                                 hrp.nik_mandor,
                                 emp_mandor.emp_name AS nama_mandor,
                                    hrp.nik_kerani_buah
                                 || '-'
                                 || hrp.nik_mandor
                                 || '-'
                                 || to_char(hrp.tanggal_rencana,'YYYYMMDD')
                                    AS id_validasi
                           FROM ebcc.t_header_rencana_panen hrp
                                 LEFT JOIN ebcc.t_detail_rencana_panen drp
                                    ON hrp.id_rencana = drp.id_rencana
                                 LEFT JOIN ebcc.t_employee emp_krani
                                    ON emp_krani.nik = hrp.nik_kerani_buah
                                 LEFT JOIN ebcc.t_employee emp_mandor
                                    ON emp_mandor.nik = hrp.nik_mandor
                           WHERE     SUBSTR (ID_BA_AFD_BLOK, 1, 2) IN (SELECT comp_code
                                                                        FROM tap_dw.tm_comp@dwh_link)
                                 AND hrp.tanggal_rencana = TO_DATE ('$day', 'YYYY-MM-DD')
                                 AND SUBSTR (drp.id_ba_afd_blok, 1, 5) in ('$code')
                                 -- SID - tambahin group by
                           GROUP BY SUBSTR (drp.id_ba_afd_blok, 1, 4),
                                 SUBSTR (drp.id_ba_afd_blok, 5, 1),
                                 hrp.tanggal_rencana,
                                 hrp.nik_kerani_buah,
                                 emp_krani.emp_name ,
                                 hrp.nik_mandor,
                                 emp_mandor.emp_name,
                                    hrp.nik_kerani_buah
                                 || '-'
                                 || hrp.nik_mandor
                                 || '-'
                                 || to_char(hrp.tanggal_rencana,'YYYYMMDD')
                           order by hrp.tanggal_rencana desc
                                 ) 
                        ebcc
                        LEFT JOIN mobile_inspection.tr_validasi_header valid
                           ON ebcc.id_validasi = valid.id_validasi
                        LEFT JOIN mobile_inspection.tr_validasi_detail valid_detail
                           ON valid_detail.id_validasi = valid.id_validasi
                           AND valid_detail.insert_user_userrole <> 'KEPALA_KEBUN'
                           -- SID - ubah
                        JOIN (
                              SELECT PARAMETER_DESC
                              FROM mobile_inspection.tm_parameter 
                              WHERE PARAMETER_GROUP = 'VALIDASI_ASKEP'
                              AND PARAMETER_NAME = 'TARGET_VALIDASI'
                        )param 
                           ON 1 = 1
                     group by 
                        ebcc.id_ba,
                        ebcc.id_afd,
                        to_char(ebcc.tanggal_rencana,'DD-MON-YYYY'),
                        ebcc.nik_kerani_buah,
                        ebcc.nama_krani_buah,
                        ebcc.nik_mandor,
                        ebcc.nama_mandor,
                        ebcc.id_validasi,
                        jumlah_ebcc_validated,
                        param.parameter_desc
      ");
		return $get;
   }
   
   // All Data
   public function data(){
      $ba_afd_code = explode(",",session('LOCATION_CODE'));
      $code = implode("','", $ba_afd_code);
		$get = $this->db_mobile_ins->select("
		SELECT ebcc.id_ba,
                        ebcc.id_afd,
                        to_char(ebcc.tanggal_rencana,'DD-MON-YYYY') AS tanggal_rencana,
                        ebcc.nik_kerani_buah,
                        ebcc.nama_krani_buah,
                        ebcc.nik_mandor,
                        ebcc.nama_mandor,
                        ebcc.id_validasi,
                        case when valid.jumlah_ebcc_validated is null then 0 else valid.jumlah_ebcc_validated end as jumlah_ebcc_validated,
                        param.parameter_desc AS target_validasi 
                     FROM (SELECT SUBSTR (drp.id_ba_afd_blok, 1, 4) AS id_ba,
                                 SUBSTR (drp.id_ba_afd_blok, 5, 1) AS id_afd, 
                                 hrp.tanggal_rencana,
                                 hrp.nik_kerani_buah,
                                 emp_krani.emp_name AS nama_krani_buah,
                                 hrp.nik_mandor,
                                 emp_mandor.emp_name AS nama_mandor,
                                    hrp.nik_kerani_buah
                                 || '-'
                                 || hrp.nik_mandor
                                 || '-'
                                 || to_char(hrp.tanggal_rencana,'YYYYMMDD')
                                    AS id_validasi
                           FROM ebcc.t_header_rencana_panen hrp
                                 LEFT JOIN ebcc.t_detail_rencana_panen drp
                                    ON hrp.id_rencana = drp.id_rencana
                                 LEFT JOIN ebcc.t_employee emp_krani
                                    ON emp_krani.nik = hrp.nik_kerani_buah
                                 LEFT JOIN ebcc.t_employee emp_mandor
                                    ON emp_mandor.nik = hrp.nik_mandor
                           WHERE     SUBSTR (ID_BA_AFD_BLOK, 1, 2) IN (SELECT comp_code
                                                                        FROM tap_dw.tm_comp@dwh_link)
                                 AND SUBSTR (drp.id_ba_afd_blok, 1, 5) in ('$code')
                                 -- SID - tambahin group by
                           GROUP BY SUBSTR (drp.id_ba_afd_blok, 1, 4),
                                 SUBSTR (drp.id_ba_afd_blok, 5, 1),
                                 hrp.tanggal_rencana,
                                 hrp.nik_kerani_buah,
                                 emp_krani.emp_name ,
                                 hrp.nik_mandor,
                                 emp_mandor.emp_name,
                                    hrp.nik_kerani_buah
                                 || '-'
                                 || hrp.nik_mandor
                                 || '-'
                                 || to_char(hrp.tanggal_rencana,'YYYYMMDD')
                           order by hrp.tanggal_rencana desc
                                 ) 
                        ebcc
                        LEFT JOIN mobile_inspection.tr_validasi_header valid
                           ON ebcc.id_validasi = valid.id_validasi
                           -- SID - ubah
                        JOIN (
                              SELECT PARAMETER_DESC
                              FROM mobile_inspection.tm_parameter 
                              WHERE PARAMETER_GROUP = 'VALIDASI_ASKEP'
                              AND PARAMETER_NAME = 'TARGET_VALIDASI'
                        )param 
                           ON 1 = 1
		");
		return $get;
   }

   public function validasi_askep($ba_code,$afd,$nik_kerani,$nik_mandor,$tgl_rencana){
      $get = $this->db_mobile_ins->select(" SELECT 
                                             HDP.TANGGAL_RENCANA,
                                             HDP.NIK_MANDOR,
                                             HDP.NIK_KERANI_BUAH,
                                             EMP_EBCC.EMP_NAME,
                                             HDP.NAMA_MANDOR,
                                             HDP.ID_BA_AFD_BLOK,
                                             HP.NO_TPH,
                                             HP.NO_BCC,
                                             HP.PICTURE_NAME,
                                             TB.ID_BLOK,
                                             TB.BLOK_NAME,
                                             TBA.NAMA_BA,
                                             TA.ID_AFD,
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
                                                   EMP.EMP_NAME AS NAMA_MANDOR,
                                                   HRP.NIK_MANDOR AS NIK_MANDOR,
                                                   DRP.ID_BA_AFD_BLOK AS ID_BA_AFD_BLOK,
                                                   DRP.NO_REKAP_BCC AS NO_REKAP_BCC
                                                FROM
                                                   EBCC.T_HEADER_RENCANA_PANEN HRP 
                                                   LEFT JOIN EBCC.T_DETAIL_RENCANA_PANEN DRP ON HRP.ID_RENCANA = DRP.ID_RENCANA
                                                   LEFT JOIN EBCC.T_EMPLOYEE EMP ON EMP.NIK = HRP.NIK_MANDOR
                                             ) HDP
                                             LEFT JOIN EBCC.T_HASIL_PANEN HP ON HP.ID_RENCANA = HDP.ID_RENCANA AND HP.NO_REKAP_BCC = HDP.NO_REKAP_BCC
                                             LEFT JOIN EBCC.T_BLOK TB ON TB.ID_BA_AFD_BLOK = HDP.ID_BA_AFD_BLOK
                                             LEFT JOIN EBCC.T_AFDELING TA ON TA.ID_BA_AFD = TB.ID_BA_AFD
                                             LEFT JOIN EBCC.T_BUSSINESSAREA TBA ON TBA.ID_BA = TA.ID_BA
                                             LEFT JOIN EBCC.T_EMPLOYEE EMP_EBCC ON EMP_EBCC.NIK = HDP.NIK_KERANI_BUAH
                                             -- JOIN EBCC.T_STATUS_TO_SAP_EBCC STAT_EBCC ON STAT_EBCC.NO_BCC = HP.NO_BCC
         WHERE
               HDP.NIK_KERANI_BUAH = '$nik_kerani' AND
               HDP.NIK_MANDOR = '$nik_mandor' AND
               HDP.TANGGAL_RENCANA = '$tgl_rencana' AND
               SUBSTR (HDP.ID_BA_AFD_BLOK, 1, 4) = '$ba_code' AND --id_ba
               SUBSTR (HDP.ID_BA_AFD_BLOK, 5, 1) = '$afd'  -- id_afd
               AND
               HP.NO_BCC NOT IN (SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE ID_VALIDASI = 
               HDP.NIK_KERANI_BUAH || '-' || HDP.NIK_MANDOR || '-'  || to_char(HDP.TANGGAL_RENCANA,'YYYYMMDD'))
               -- AND POST_STATUS is null
         ORDER BY DBMS_RANDOM.VALUE FETCH NEXT 1 ROWS ONLY ");
         
		return $get;
   }

   public function count_valid($date){
      $day =  date("Y-m-d", strtotime($date));
      $ba_afd_code = explode(",",session('LOCATION_CODE'));
      $code = implode("','", $ba_afd_code);
      $get = $this->db_mobile_ins->select("
      SELECT hdp.status_validasi
      FROM (
                  SELECT ebcc.id_ba,
                                    ebcc.id_afd,
                                    to_char(ebcc.tanggal_rencana,'DD-MON-YYYY') AS tanggal_rencana,
                                    ebcc.nik_kerani_buah,
                                    ebcc.nama_krani_buah,
                                    ebcc.nik_mandor,
                                    ebcc.nama_mandor,
                                    ebcc.id_validasi,
                                    case when valid.jumlah_ebcc_validated is null then 0 else valid.jumlah_ebcc_validated end as jumlah_ebcc_validated,
                                    param.parameter_desc AS target_validasi,
                                    case when valid.jumlah_ebcc_validated = param.parameter_desc then 'finished' else 'unfinished' end as status_validasi
                                 FROM (SELECT SUBSTR (drp.id_ba_afd_blok, 1, 4) AS id_ba,
                                             SUBSTR (drp.id_ba_afd_blok, 5, 1) AS id_afd, 
                                             hrp.tanggal_rencana,
                                             hrp.nik_kerani_buah,
                                             emp_krani.emp_name AS nama_krani_buah,
                                             hrp.nik_mandor,
                                             emp_mandor.emp_name AS nama_mandor,
                                                hrp.nik_kerani_buah
                                             || '-'
                                             || hrp.nik_mandor
                                             || '-'
                                             || to_char(hrp.tanggal_rencana,'YYYYMMDD')
                                                AS id_validasi
                                       FROM ebcc.t_header_rencana_panen hrp
                                             LEFT JOIN ebcc.t_detail_rencana_panen drp
                                                ON hrp.id_rencana = drp.id_rencana
                                             LEFT JOIN ebcc.t_employee emp_krani
                                                ON emp_krani.nik = hrp.nik_kerani_buah
                                             LEFT JOIN ebcc.t_employee emp_mandor
                                                ON emp_mandor.nik = hrp.nik_mandor
                                       WHERE     SUBSTR (ID_BA_AFD_BLOK, 1, 2) IN (SELECT comp_code
                                                                                    FROM tap_dw.tm_comp@dwh_link)
                                             AND hrp.tanggal_rencana = TO_DATE ('$day', 'YYYY-MM-DD')
                                             AND SUBSTR (drp.id_ba_afd_blok, 1, 5) in ('$code')
                                             -- SID - tambahin group by
                                       GROUP BY SUBSTR (drp.id_ba_afd_blok, 1, 4),
                                             SUBSTR (drp.id_ba_afd_blok, 5, 1),
                                             hrp.tanggal_rencana,
                                             hrp.nik_kerani_buah,
                                             emp_krani.emp_name ,
                                             hrp.nik_mandor,
                                             emp_mandor.emp_name,
                                                hrp.nik_kerani_buah
                                             || '-'
                                             || hrp.nik_mandor
                                             || '-'
                                             || to_char(hrp.tanggal_rencana,'YYYYMMDD')
                                       order by hrp.tanggal_rencana desc
                                             ) 
                                    ebcc
                                    LEFT JOIN mobile_inspection.tr_validasi_header valid
                                       ON ebcc.id_validasi = valid.id_validasi
                                       -- SID - ubah
                                    JOIN (
                                          SELECT PARAMETER_DESC
                                          FROM mobile_inspection.tm_parameter 
                                          WHERE PARAMETER_GROUP = 'VALIDASI_ASKEP'
                                          AND PARAMETER_NAME = 'TARGET_VALIDASI'
                                    )param 
                                       ON 1 = 1 ) hdp
                                    GROUP BY hdp.status_validasi
      ");
		return $get;
   }

   public function validasi_cek_aslap($date){
      $day =  date("Y-m-d", strtotime($date));
      $ba_afd_code = explode(",",session('LOCATION_CODE'));
      $code = implode("','", $ba_afd_code);
      $get = $this->db_mobile_ins->select("  SELECT  *
                                                FROM (
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
                                                         CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.NIK_MANDOR END ebcc_NIK_MANDOR,
                                                         CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.NAMA_MANDOR END ebcc_NAMA_MANDOR,
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
                                                   FROM (	  		  
                                                      SELECT ebcc_val.val_ebcc_code,
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
                                                            ebcc_val.val_delivery_ticket,
                                                            ebcc_val.val_user_role
                                                      FROM (
                                                         SELECT ebcc_header.ebcc_validation_code AS val_ebcc_code,
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
                                                               user_auth.USER_ROLE as val_user_role,
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
                                                         LEFT JOIN (
                                                            SELECT 	employee_nik,
                                                                  employee_fullname,
                                                                  employee_position,
                                                                  employee_joindate AS start_date,
                                                                  CASE
                                                                     WHEN employee_resigndate IS NULL THEN TO_DATE ('99991231', 'RRRRMMDD')
                                                                     ELSE employee_resigndate
                                                                  END AS end_date
                                                            FROM tap_dw.tm_employee_hris@dwh_link
                                                            UNION ALL
                                                            SELECT 	nik,
                                                                  employee_name,
                                                                  job_code,
                                                                  start_valid,
                                                                  CASE WHEN res_date IS NOT NULL THEN res_date ELSE end_valid END end_valid
                                                            FROM tap_dw.tm_employee_sap@dwh_link
                                                         ) emp
                                                            ON emp.employee_nik = user_auth.employee_nik
                                                            AND TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (emp.start_date) AND TRUNC (emp.end_date)
                                                         LEFT JOIN tap_dw.tm_est@dwh_link est
                                                            ON est.werks = ebcc_header.werks 
                                                            AND trunc(ebcc_header.insert_time) BETWEEN est.start_valid AND est.end_valid -- revisi sab
                                                         LEFT JOIN (
                                                            SELECT 	werks,
                                                                  afd_code,
                                                                  block_code,
                                                                  block_name,
                                                                  maturity_status,
                                                                  spmon
                                                            FROM tap_dw.tr_hs_land_use@dwh_link
                                                            WHERE 1 = 1 
                                                               AND ROWNUM < 2 
                                                               AND maturity_status IS NOT NULL
                                                               AND spmon BETWEEN (
                                                                  SELECT 	CASE
                                                                           WHEN TO_CHAR (TO_DATE ('$day', 'RRRR-MM-DD'), 'MM') = '01'
                                                                           THEN TRUNC (ADD_MONTHS (TO_DATE ('$day', 'RRRR-MM-DD'), -1), 'MM')
                                                                           ELSE TRUNC (TO_DATE ('$day', 'RRRR-MM-DD'), 'MM')
                                                                        END
                                                                  FROM DUAL
                                                               )
                                                               AND ( 
                                                                  SELECT CASE
                                                                           WHEN TO_CHAR (TO_DATE ('$day', 'RRRR-MM-DD'), 'MM') = '01'
                                                                           THEN LAST_DAY (ADD_MONTHS (TO_DATE ('$day', 'RRRR-MM-DD'), -1))
                                                                           ELSE LAST_DAY (TO_DATE ('$day', 'RRRR-MM-DD'))
                                                                        END
                                                                  FROM DUAL
                                                               )								 
                                                         ) land_use
                                                            ON land_use.werks = ebcc_header.werks
                                                            AND land_use.afd_code = ebcc_header.afd_code
                                                            AND land_use.block_code = ebcc_header.block_code
                                                         LEFT JOIN tap_dw.tm_sub_block@dwh_link subblock
                                                            ON subblock.werks = ebcc_header.werks 
                                                            AND subblock.sub_block_code = ebcc_header.block_code
                                                         WHERE SUBSTR (ebcc_header.ebcc_validation_code, 0, 1) = 'V'
                                                            AND TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (TO_DATE ('$day', 'RRRR-MM-DD')) AND  TRUNC (TO_DATE ('$day', 'RRRR-MM-DD'))
                                                            -- AND ebcc_header.werks = '4122'
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
                                                            ebcc_val.val_spmon,
                                                            ebcc_val.val_user_role
                                                   ) header
                                                   left join (
                                                      select *
                                                      from (
                                                         select 	kualitas.id_kualitas as idk, 
                                                               ebcc_detail.ebcc_validation_code, 
                                                               ebcc_detail.jumlah
                                                         from tap_dw.t_kualitas_panen@dwh_link kualitas
                                                         left join mobile_inspection.tr_ebcc_validation_d ebcc_detail
                                                            on ebcc_detail.id_kualitas = kualitas.id_kualitas
                                                         where kualitas.active_status = 'YES'
                                                      ) 
                                                      pivot (
                                                         SUM (jumlah)
                                                         for idk
                                                         in (
                                                            '1' as jml_1,
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
                                                            '16' as jml_16
                                                         )
                                                      )
                                                      where ebcc_validation_code is NOT NULL
                                                   ) detail
                                                      on header.val_ebcc_code = detail.ebcc_validation_code
                                                   left join (
                                                      select 	tanggal_rencana,
                                                            id_ba,
                                                            id_afd,
                                                            id_blok,
                                                            no_tph,
                                                            jlh_ebcc,
                                                            nik_kerani_buah,
                                                            nama_kerani_buah,
                                                            NIK_MANDOR,
                                                            NAMA_MANDOR,
                                                            no_bcc,
                                                            status_tph,
                                                            NVL (ebcc.f_get_hasil_panen_bunch (id_ba, no_rekap_bcc, no_bcc, 'BUNCH_HARVEST'), 0) as jjg_panen,
                                                            ebcc_jml_bm,
                                                            ebcc_jml_bk,
                                                            ebcc_jml_ms,
                                                            ebcc_jml_or,
                                                            ebcc_jml_bb,
                                                            ebcc_jml_jk,
                                                            ebcc_jml_ba
                                                      from (  
                                                         select hrp.tanggal_rencana,
                                                               substr(ID_BA_AFD_BLOK,1,4) id_ba,
                                                               substr(ID_BA_AFD_BLOK,5,1) id_afd,
                                                               substr(ID_BA_AFD_BLOK,6,3)id_blok,
                                                               /*SUBSTR (no_bcc, 12, 3) no_tph,*/
                                                               hp.no_tph no_tph,
                                                               COUNT (distinct hp.no_bcc) jlh_ebcc,
                                                               MAX (hrp.nik_kerani_buah) nik_kerani_buah,
                                                               MAX (emp_ebcc.emp_name) nama_kerani_buah,
                                                               MAX (hrp.NIK_MANDOR) NIK_MANDOR,
                                                               MAX (emp_ebcc1.emp_name) NAMA_MANDOR,
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
                                                            on hp.id_rencana = drp.id_rencana 
                                                            and hp.no_rekap_bcc = drp.no_rekap_bcc
                                                         left join ebcc.t_employee emp_ebcc
                                                            on emp_ebcc.nik = hrp.nik_kerani_buah
                                                         left join ebcc.t_employee emp_ebcc1
                                                            on emp_ebcc1.nik = hrp.NIK_MANDOR
                                                         left join ebcc.t_hasilpanen_kualtas thk
                                                            on hp.no_bcc = thk.id_bcc 
                                                            and hp.id_rencana = thk.id_rencana
                                                         where hrp.tanggal_rencana between TO_DATE ('$day', 'YYYY-MM-DD') and  TO_DATE ('$day', 'YYYY-MM-DD')
                                                            -- and substr(ID_BA_AFD_BLOK,1,4) = '4122'
                                                         group by hrp.tanggal_rencana, 
                                                            id_ba_afd_blok,
                                                            hp.no_tph
                                                      )										   
                                                   ) ebcc
                                                      on TRUNC (ebcc.tanggal_rencana) = TRUNC (val_date_time)
                                                      and ebcc.id_ba = val_werks
                                                      and ebcc.id_afd = val_afd_code
                                                      and ebcc.id_blok = val_block_code
                                                      and ebcc.no_tph = val_tph_code
                                                ) TBL
                                                WHERE TBL.akurasi_sampling_ebcc = 'MATCH'
      ");
		return $get;
   }
  
}