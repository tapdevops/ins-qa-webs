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
                        count(valid_detail.id_validasi) AS aslap_validation,
                        count(valid_detail2.id_validasi) AS aslap_validated
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
                           ON valid_detail.id_validasi = ebcc.id_validasi
                           AND valid_detail.insert_user_userrole <> 'KEPALA_KEBUN'
                        LEFT JOIN mobile_inspection.tr_validasi_detail valid_detail2
                           ON valid_detail2.id_validasi = valid_detail.id_validasi
                           AND valid_detail2.insert_user_userrole = 'KEPALA_KEBUN'
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

   public function validasi_askep($ba_code,$afd,$nik_kerani,$nik_mandor,$tgl_rencana,$no_val){
   	  $get_max = $this->db_mobile_ins->select("SELECT PARAMETER_DESC
                              FROM mobile_inspection.tm_parameter 
                              WHERE PARAMETER_GROUP = 'VALIDASI_ASKEP'
                              AND PARAMETER_NAME = 'TARGET_VALIDASI'");
   	  if($get_max[0]->parameter_desc==$no_val)
   	  {
   	  	$in_query = "HP.NO_BCC IN (SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE ID_VALIDASI = HDP.NIK_KERANI_BUAH || '-' || HDP.NIK_MANDOR || '-'  || to_char(HDP.TANGGAL_RENCANA,'YYYYMMDD') AND 
               				 INSERT_USER_USERROLE <> 'KEPALA_KEBUN' AND NO_BCC NOT IN ( SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE INSERT_USER_USERROLE = 'KEPALA_KEBUN'))";
   	  }
   	  else 
   	  {
   	  	$in_query = "HP.NO_BCC NOT IN (SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE ID_VALIDASI = 
               HDP.NIK_KERANI_BUAH || '-' || HDP.NIK_MANDOR || '-'  || to_char(HDP.TANGGAL_RENCANA,'YYYYMMDD'))";
   	  }
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
                                             NVL(V_DETAIL.JJG_EBCC_TOTAL,NVL( EBCC.F_GET_HASIL_PANEN_BUNCH ( TBA.ID_BA, HP.NO_REKAP_BCC, HP.NO_BCC, 'BUNCH_HARVEST' ), 0 )) as JJG_PANEN,
                                             NVL(V_DETAIL.JJG_EBCC_BM,NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 1 ), 0 )) AS EBCC_JML_BM,
                                             NVL(V_DETAIL.JJG_EBCC_BK,NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 2 ), 0 )) AS EBCC_JML_BK,
                                             NVL(V_DETAIL.JJG_EBCC_MS,NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 3 ), 0 )) AS EBCC_JML_MS,
                                             NVL(V_DETAIL.JJG_EBCC_OR,NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 4 ), 0 )) AS EBCC_JML_OR,
                                             NVL(V_DETAIL.JJG_EBCC_BB,NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 6 ), 0 )) AS EBCC_JML_BB,
                                             NVL(V_DETAIL.JJG_EBCC_JK,NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 15 ), 0 )) AS EBCC_JML_JK,
                                             NVL(V_DETAIL.JJG_EBCC_BA,NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 16 ), 0 )) AS EBCC_JML_BA,   
                                             NVL( EBCC.F_GET_HASIL_PANEN_BRDX ( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC ), 0 ) AS EBCC_JML_BRD,
                                             DATA_SOURCE,
                                             VAL_EBCC_CODE
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
                                             LEFT JOIN TR_VALIDASI_DETAIL V_DETAIL ON HP.NO_BCC = V_DETAIL.NO_BCC
                                             -- JOIN EBCC.T_STATUS_TO_SAP_EBCC STAT_EBCC ON STAT_EBCC.NO_BCC = HP.NO_BCC
         WHERE
               HDP.NIK_KERANI_BUAH = '$nik_kerani' AND
               HDP.NIK_MANDOR = '$nik_mandor' AND
               HDP.TANGGAL_RENCANA = '$tgl_rencana' AND
               SUBSTR (HDP.ID_BA_AFD_BLOK, 1, 4) = '$ba_code' AND --id_ba
               SUBSTR (HDP.ID_BA_AFD_BLOK, 5, 1) = '$afd'  -- id_afd
               AND
               $in_query
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
      $get = $this->db_mobile_ins->select(" WITH tbl
										        AS (SELECT header.*,
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
										                   ebcc.jlh_ebcc ebcc_count,
										                   CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.nik_kerani_buah END ebcc_nik_kerani_buah,
										                   CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.nama_kerani_buah END ebcc_nama_kerani_buah,
										                   CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.nik_mandor END ebcc_nik_mandor,
										                   CASE WHEN ebcc.jlh_ebcc = 1 THEN ebcc.nama_mandor END ebcc_nama_mandor,
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
										                           AND (NVL (detail.jml_1, 0) + NVL (detail.jml_2, 0) + NVL (detail.jml_3, 0) + NVL (detail.jml_4, 0) + NVL (detail.jml_6, 0) + NVL (detail.jml_15, 0) + NVL (detail.jml_16, 0)) =
										                                 ebcc.jjg_panen
										                      THEN
										                         'MATCH'
										                      ELSE
										                         'NOT_MATCH'
										                   END
										                      akurasi_kuantitas,
										                   CASE
										                      WHEN ebcc.jlh_ebcc = 1
										                           AND (NVL (detail.jml_1, 0) + NVL (detail.jml_2, 0) + NVL (detail.jml_3, 0) + NVL (detail.jml_4, 0) + NVL (detail.jml_6, 0) + NVL (detail.jml_15, 0) + NVL (detail.jml_16, 0)) =
										                                 ebcc.jjg_panen
										                      THEN
										                         CASE WHEN NVL (detail.jml_3, 0) = NVL (ebcc.ebcc_jml_ms, 0) THEN 0 ELSE ABS (NVL (detail.jml_3, 0) - NVL (ebcc.ebcc_jml_ms, 0)) END
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
										                             --                             ebcc_val.val_maturity_status,
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
										                                     --                                     land_use.maturity_status AS val_maturity_status,
										                                     --                                     land_use.spmon AS val_spmon,
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
										                                        FROM mobile_inspection.tr_ebcc_validation_h ebcc_header LEFT JOIN mobile_inspection.tm_user_auth user_auth
										                                                ON user_auth.user_auth_code = (CASE WHEN LENGTH (ebcc_header.insert_user) = 3 THEN '0' || ebcc_header.insert_user ELSE ebcc_header.insert_user END)
										                                       WHERE TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (TO_DATE ('$day', 'RRRR-MM-DD')) AND TRUNC (TO_DATE ('$day', 'RRRR-MM-DD'))
										                                      UNION
										                                      SELECT ebcc_code ebcc_validation_code,
										                                             werks,
										                                             afd_code,
										                                             block_code,
										                                             tph_code no_tph,
										                                             status_tph_scan,
										                                             user_lat lat_tph,
										                                             user_long lon_tph,
										                                             insert_user,
										                                             insert_time,
										                                             sync_flag status_sync,
										                                             NULL sync_time,
										                                             update_time,
										                                             delivery_ticket delivery_code,
										                                             alasan_manual,
										                                             NULL user_role,
										                                             'ME' sumber
										                                        FROM mobile_estate.tr_ebcc
										                                       WHERE TRUNC (insert_time) BETWEEN TRUNC (TO_DATE ('$day', 'RRRR-MM-DD')) AND TRUNC (TO_DATE ('$day', 'RRRR-MM-DD'))) ebcc_header
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
										                                        ON est.werks = ebcc_header.werks AND TRUNC (ebcc_header.insert_time) BETWEEN est.start_valid AND est.end_valid                                     -- revisi sab
										                                     --                                     LEFT JOIN (SELECT werks,
										                                     --                                                       afd_code,
										                                     --                                                       block_code,
										                                     --                                                       block_name,
										                                     --                                                       maturity_status,
										                                     --                                                       spmon
										                                     --                                                  FROM tap_dw.tr_hs_land_use@dwh_link
										                                     --                                                 WHERE 1 = 1 AND ROWNUM < 2 AND maturity_status IS NOT NULL
										                                     --                                                       AND spmon BETWEEN CASE
										                                     --                                                                            WHEN TO_CHAR (TO_DATE ('$day', 'RRRR-MM-DD'), 'MM') = '01'
										                                     --                                                                            THEN
										                                     --                                                                               TRUNC (ADD_MONTHS (TO_DATE ('$day', 'RRRR-MM-DD'), -1), 'MM')
										                                     --                                                                            ELSE
										                                     --                                                                               TRUNC (TO_DATE ('$day', 'RRRR-MM-DD'), 'MM')
										                                     --                                                                         END
										                                     --                                                                     AND  CASE
										                                     --                                                                             WHEN TO_CHAR (TO_DATE ('$day', 'RRRR-MM-DD'), 'MM') = '01'
										                                     --                                                                             THEN
										                                     --                                                                                LAST_DAY (ADD_MONTHS (TO_DATE ('$day', 'RRRR-MM-DD'), -1))
										                                     --                                                                             ELSE
										                                     --                                                                                LAST_DAY (TO_DATE ('$day', 'RRRR-MM-DD'))
										                                     --                                                                          END) land_use
										                                     --                                        ON land_use.werks = ebcc_header.werks AND land_use.afd_code = ebcc_header.afd_code AND land_use.block_code = ebcc_header.block_code
										                                     LEFT JOIN tap_dw.tm_sub_block@dwh_link subblock
										                                        ON subblock.werks = ebcc_header.werks AND subblock.sub_block_code = ebcc_header.block_code
										                               WHERE TRUNC (ebcc_header.insert_time) BETWEEN TRUNC (TO_DATE ('$day', 'RRRR-MM-DD')) AND TRUNC (TO_DATE ('$day', 'RRRR-MM-DD')) -- AND ebcc_header.werks = '4122'
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
										                             --                             ebcc_val.val_maturity_status,
										                             --                             ebcc_val.val_spmon,
										                             ebcc_val.val_user_role,
										                             ebcc_val.val_sumber) header
										                   LEFT JOIN (SELECT *
										                                FROM (SELECT kualitas.id_kualitas AS idk, ebcc_detail.ebcc_validation_code, ebcc_detail.jumlah
										                                        FROM tap_dw.t_kualitas_panen@dwh_link kualitas LEFT JOIN mobile_inspection.tr_ebcc_validation_d ebcc_detail
										                                                ON ebcc_detail.id_kualitas = kualitas.id_kualitas
										                                       WHERE kualitas.active_status = 'YES'
										                                      UNION
										                                      SELECT kualitas.id_kualitas AS idk, ebcc_detail.ebcc_code ebcc_validation_code, ebcc_detail.qty jumlah
										                                        FROM tap_dw.t_kualitas_panen@dwh_link kualitas LEFT JOIN mobile_estate.tr_ebcc_kualitas ebcc_detail
										                                                ON ebcc_detail.id_kualitas = kualitas.id_kualitas
										                                       WHERE kualitas.active_status = 'YES') PIVOT (SUM (jumlah)
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
										                                     NVL (ebcc.f_get_hasil_panen_bunch (id_ba,
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
										                                     ebcc_jml_ba
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
										                                               SUM (CASE WHEN thk.id_kualitas = 16 THEN thk.qty END) ebcc_jml_ba
										                                          FROM ebcc.t_header_rencana_panen hrp
										                                               LEFT JOIN ebcc.t_detail_rencana_panen drp
										                                                  ON hrp.id_rencana = drp.id_rencana
										                                               LEFT JOIN ebcc.t_hasil_panen hp
										                                                  ON hp.id_rencana = drp.id_rencana AND hp.no_rekap_bcc = drp.no_rekap_bcc
										                                               LEFT JOIN ebcc.t_employee emp_ebcc
										                                                  ON emp_ebcc.nik = hrp.nik_kerani_buah
										                                               LEFT JOIN ebcc.t_employee emp_ebcc1
										                                                  ON emp_ebcc1.nik = hrp.nik_mandor
										                                               LEFT JOIN ebcc.t_hasilpanen_kualtas thk
										                                                  ON hp.no_bcc = thk.id_bcc AND hp.id_rencana = thk.id_rencana
										                                         WHERE hrp.tanggal_rencana BETWEEN TO_DATE ('$day', 'YYYY-MM-DD') AND TO_DATE ('$day', 'YYYY-MM-DD')
										                                      -- and substr(ID_BA_AFD_BLOK,1,4) = '4122'
										                                      GROUP BY hrp.tanggal_rencana,
										                                               id_ba_afd_blok,
										                                               hp.no_tph,
										                                               NVL (hp.kode_delivery_ticket, '-'))) ebcc
										                      ON     TRUNC (ebcc.tanggal_rencana) = TRUNC (val_date_time)
										                         AND ebcc.id_ba = val_werks
										                         AND ebcc.id_afd = val_afd_code
										                         AND ebcc.id_blok = val_block_code
										                         AND ebcc.no_tph = val_tph_code
										                         AND ebcc.kode_delivery_ticket = CASE WHEN val_sumber = 'ME' THEN val_delivery_ticket ELSE ebcc.kode_delivery_ticket END)
										SELECT *
										  FROM tbl
										 WHERE tbl.akurasi_sampling_ebcc = 'MATCH'
      ");
		return $get;
   }
  
}