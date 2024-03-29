<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class ValidasiHeader extends Model{
   
   protected $env;
   protected $db_mobile_ins;
   
   public function __construct() {
      $this->db_mobile_ins = DB::connection( 'mobile_ins' );
		$this->env = 'DEV';
   }

   //filter H-1
   public function validasi_header($date){
      $day =  date("Y-m-d", strtotime($date));
      $code = session('werks');
      $ba_afd_data = session('ba_afd_data');
      $afd_data = $ba_afd_data[$code];
      $substr_id_ba_afd_blok = 4;
      $check_eharvesting_param = $this->db_mobile_ins->select("SELECT PARAMETER_DESC
                                 FROM mobile_inspection.tm_parameter
                                 WHERE PARAMETER_GROUP = 'VALIDASI_ASKEP_EHARVESTING'
                                 AND PARAMETER_NAME = '$code' and TO_DATE(PARAMETER_DESC,'dd-mon-yyyy') < TO_DATE('$day','YYYY-MM-DD')");
      if(session('afd')!='')
      {
         $code .= session('afd');
         $substr_id_ba_afd_blok = 5;
      }
      if(ISSET($check_eharvesting_param[0])){
         // USING EHARVESTING DATA
         $get = $this->db_mobile_ins->select("
         SELECT hrv.id_ba,
                        hrv.id_afd,
                        hrv.tanggal_rencana,
                        hrv.nik_kerani_buah,
                        hrv.nama_krani_buah,
                        hrv.nik_mandor,
                        hrv.nama_mandor,
                        hrv.id_validasi,
                        case when valid.jumlah_ebcc_validated is null then 0 else valid.jumlah_ebcc_validated end as jumlah_ebcc_validated,
                        param.parameter_desc AS target_validasi,
                        count(valid_detail.id_validasi) AS aslap_validation
                     FROM (SELECT thah.BA_CODE id_ba,
		                          thah.AFDELING_NAME id_afd,
		                          to_char(thah.TRANSACTION_TIME,'DD-MON-YYYY') AS tanggal_rencana,
		                          thaud_krani.user_nik nik_kerani_buah,
		                          thaud_krani.user_fullname nama_krani_buah,
		                          thaud_mandor.user_nik nik_mandor,
		                          thaud_mandor.user_fullname nama_mandor,
		                          thaud_krani.user_nik || '-' || thaud_mandor.user_nik  || '-' || to_char(thah.TRANSACTION_TIME,'YYYYMMDD') id_validasi
                           FROM EHARVESTING.TR_HARVEST_ACTIVITY_H thah
	                        LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_USER_D thaud_krani
                           ON thaud_krani.HARVEST_ACTIVITY_ID = thah.ID AND thaud_krani.ACTIVITY_USER_TYPE = 'KRANI'
                           LEFT JOIN EHARVESTING.TR_HARVEST_ACTIVITY_USER_D thaud_mandor
                           ON thaud_mandor.HARVEST_ACTIVITY_ID = thah.ID AND thaud_mandor.ACTIVITY_USER_TYPE = 'MANDOR'
                           WHERE thah.COMPANY_CODE IN (SELECT comp_code FROM tap_dw.tm_comp@dwh_link)
                                 AND TRUNC(thah.TRANSACTION_TIME) = TO_DATE ('$day', 'YYYY-MM-DD')
                                 AND SUBSTR (thah.AFDELING_CODE, 1, $substr_id_ba_afd_blok) IN('$code')
                                 AND AFDELING_NAME IN($afd_data)
                                 -- SID - tambahin group by
                           GROUP BY thah.BA_CODE,
                                 thah.AFDELING_NAME,
                                 to_char(thah.TRANSACTION_TIME,'DD-MON-YYYY'),
                                 to_char(thah.TRANSACTION_TIME,'YYYYMMDD'),
                                 thaud_krani.user_nik,
		                           thaud_krani.user_fullname,
		                           thaud_mandor.user_nik,
		                           thaud_mandor.user_fullname
                           order by to_char(thah.TRANSACTION_TIME,'DD-MON-YYYY') desc
                                 ) hrv
                        LEFT JOIN mobile_inspection.tr_validasi_header valid
                           ON hrv.id_validasi = valid.id_validasi
                        LEFT JOIN mobile_inspection.tr_validasi_detail valid_detail
                           ON valid_detail.id_validasi = hrv.id_validasi
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
                        hrv.id_ba,
                        hrv.id_afd,
                        hrv.tanggal_rencana,
                        hrv.nik_kerani_buah,
                        hrv.nama_krani_buah,
                        hrv.nik_mandor,
                        hrv.nama_mandor,
                        hrv.id_validasi,
                        jumlah_ebcc_validated,
                        param.parameter_desc
                     order by hrv.id_afd,hrv.nama_krani_buah    
         ");
      }
      else{
         // USING EBCC DATA
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
                                    AND SUBSTR (drp.id_ba_afd_blok, 1, $substr_id_ba_afd_blok) in ('$code')
                                    AND SUBSTR (drp.id_ba_afd_blok, 5, 1) IN($afd_data)
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
                        order by ebcc.id_afd,ebcc.nama_krani_buah 
         ");
      }
      return $get;
   }
   
   // All Data
   public function data(){
      $ba_afd_code = explode(",",session('LOCATION_CODE'));
      $code = implode("','", $ba_afd_code);
      if(session('REFFERENCE_ROLE')=='COMP_CODE')
      {
         $code = session('werks').session('afd');
      }
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
        $check_eharvesting_param = $this->db_mobile_ins->select("SELECT PARAMETER_DESC
                              FROM mobile_inspection.tm_parameter 
                              WHERE PARAMETER_GROUP = 'VALIDASI_ASKEP_EHARVESTING'
                              AND PARAMETER_NAME = '$ba_code' and TO_DATE(PARAMETER_DESC,'dd-mon-yyyy') < TO_DATE('$tgl_rencana','YYYY-MM-DD')");
         // USING EHARVESTING
         if(ISSET($check_eharvesting_param[0]))
         {
            $sql = " SELECT HP.*, DATA_SOURCE, VAL_EBCC_CODE,  ME_IMAGE.IMAGE_NAME, INSERT_USER_USERROLE, JJG_VALIDATE_TOTAL,'EHARVESTING' primary_source 
                  FROM (
                        SELECT * FROM (
                           SELECT 
                                          TRUNC(thah.TRANSACTION_TIME) TANGGAL_RENCANA,
                                    MAX (CASE WHEN thaud.activity_user_type = 'MANDOR' THEN thaud.user_nik ELSE NULL END ) NIK_MANDOR,
                                    MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_nik ELSE NULL END) NIK_KERANI_BUAH,
                                    MAX (CASE WHEN thaud.activity_user_type = 'KRANI' THEN thaud.user_fullname ELSE NULL END ) EMP_NAME,
                                    MAX (CASE WHEN thaud.activity_user_type = 'MANDOR' THEN thaud.user_fullname ELSE NULL END ) NAMA_MANDOR,
                                          thah.BLOCK_CODE ID_BA_AFD_BLOK,
                                          thah.TPH_NAME NO_TPH,
                                          REPLACE(thah.HARVEST_ACTIVITY_CODE,'.') NO_BCC,
                                          MAX (TO_CHAR(thah.ATTACHMENT)) PICTURE_NAME,
                                          SUBSTR (thah.BLOCK_CODE, 6, 3) ID_BLOK,
                                          thah.BLOCK_NAME BLOK_NAME,
                                          thah.BA_NAME NAMA_BA,
                                          thah.AFDELING_NAME ID_AFD,
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
                                    ON thaid.HARVEST_ACTIVITY_ID = thah.ID
                                    WHERE to_char(thah.TRANSACTION_TIME,'YYYY-MM-DD') = '$tgl_rencana'
                                    GROUP BY TRUNC(thah.TRANSACTION_TIME),thah.BLOCK_CODE,thah.TPH_NAME,thah.HARVEST_ACTIVITY_CODE,
                                       thah.BLOCK_NAME,thah.BA_NAME,thah.AFDELING_NAME 
                           )
                           WHERE NIK_KERANI_BUAH = '$nik_kerani' AND
                              NIK_MANDOR = '$nik_mandor' AND
                              SUBSTR (ID_BA_AFD_BLOK, 1, 4) = '$ba_code' AND --id_ba
                              SUBSTR (ID_BA_AFD_BLOK, 5, 1) = '$afd'  -- id_afd 
                        ) HP
                        LEFT JOIN TR_VALIDASI_DETAIL V_DETAIL ON HP.NO_BCC = V_DETAIL.NO_BCC
                        LEFT JOIN mobile_estate.TR_IMAGE ME_IMAGE ON ME_IMAGE.TR_CODE = V_DETAIL.VAL_EBCC_CODE
                                 -- JOIN EBCC.T_STATUS_TO_SAP_EBCC STAT_EBCC ON STAT_EBCC.NO_BCC = HP.NO_BCC
                        WHERE ";
         }
         // USING EBCC
         else 
         {
            $sql = "SELECT HDP.TANGGAL_RENCANA,HDP.NIK_MANDOR,HDP.NIK_KERANI_BUAH,EMP_EBCC.EMP_NAME,HDP.NAMA_MANDOR,HDP.ID_BA_AFD_BLOK,
                           HP.NO_TPH,HP.NO_BCC,HP.PICTURE_NAME,TB.ID_BLOK,TB.BLOK_NAME,TBA.NAMA_BA,TA.ID_AFD,
                           NVL( EBCC.F_GET_HASIL_PANEN_BUNCH ( TBA.ID_BA, HP.NO_REKAP_BCC, HP.NO_BCC, 'BUNCH_HARVEST' ), 0 ) as JJG_PANEN,
                           NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 1 ), 0 ) AS EBCC_JML_BM,
                           NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 2 ), 0 ) AS EBCC_JML_BK,
                           NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 3 ), 0 ) AS EBCC_JML_MS,
                           NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 4 ), 0 ) AS EBCC_JML_OR,
                           NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 6 ), 0 ) AS EBCC_JML_BB,
                           NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 15 ), 0 ) AS EBCC_JML_JK,
                           NVL( EBCC.F_GET_HASIL_PANEN_NUMBERX( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC, 16 ), 0 ) AS EBCC_JML_BA,   
                           NVL( EBCC.F_GET_HASIL_PANEN_BRDX ( HDP.ID_RENCANA, HP.NO_REKAP_BCC, HP.NO_BCC ), 0 ) AS EBCC_JML_BRD,
                           DATA_SOURCE,VAL_EBCC_CODE,ME_IMAGE.IMAGE_NAME,INSERT_USER_USERROLE,JJG_VALIDATE_TOTAL,'EBCC' primary_source 
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
                           LEFT JOIN mobile_estate.TR_IMAGE ME_IMAGE ON ME_IMAGE.TR_CODE = V_DETAIL.VAL_EBCC_CODE
                           -- JOIN EBCC.T_STATUS_TO_SAP_EBCC STAT_EBCC ON STAT_EBCC.NO_BCC = HP.NO_BCC
               WHERE
               HDP.NIK_KERANI_BUAH = '$nik_kerani' AND
               HDP.NIK_MANDOR = '$nik_mandor' AND
               HDP.TANGGAL_RENCANA = '$tgl_rencana' AND
               SUBSTR (HDP.ID_BA_AFD_BLOK, 1, 4) = '$ba_code' AND --id_ba
               SUBSTR (HDP.ID_BA_AFD_BLOK, 5, 1) = '$afd'  -- id_afd
               AND ";
         }

        if($get_max[0]->parameter_desc==$no_val) // GET DATA ASLAP
        {
         $in_query = "HP.NO_BCC IN (
                              SELECT NO_BCC 
                              FROM TR_VALIDASI_DETAIL 
                              WHERE ID_VALIDASI = NIK_KERANI_BUAH || '-' || NIK_MANDOR || '-'  || to_char(TANGGAL_RENCANA,'YYYYMMDD') 
                              AND NO_BCC IN ( 
                                                SELECT NO_BCC 
                                                FROM TR_VALIDASI_DETAIL 
                                                WHERE VAL_EBCC_CODE IS NOT NULL AND INSERT_USER_USERROLE LIKE 'ASISTEN%'
                                               )
                                    AND NO_BCC NOT IN ( SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE VAL_EBCC_CODE IS NULL ) 
                                   ) 
                              ORDER BY DBMS_RANDOM.VALUE FETCH NEXT 1 ROWS ONLY";
        }
        else // GET DATA KRANI
        {
         $in_query = "HP.NO_BCC NOT IN (SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE ID_VALIDASI = 
               NIK_KERANI_BUAH || '-' || NIK_MANDOR || '-'  || to_char(TANGGAL_RENCANA,'YYYYMMDD'))
               ORDER BY DBMS_RANDOM.VALUE FETCH NEXT 1 ROWS ONLY";
        }
        $get = $this->db_mobile_ins->select("$sql $in_query");

         if($get_max[0]->parameter_desc==$no_val && count($get)==0) // GET DATA KABUN WHEN DATA ASLAP IS NULL
         {
            $in_query = "HP.NO_BCC IN (
                                       SELECT 
                                          NO_BCC FROM TR_VALIDASI_DETAIL WHERE ID_VALIDASI = NIK_KERANI_BUAH || '-' || NIK_MANDOR || '-'  || to_char(TANGGAL_RENCANA,'YYYYMMDD') 
                                       AND 
                                          NO_BCC IN ( SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE VAL_EBCC_CODE IS NOT NULL AND INSERT_USER_USERROLE NOT LIKE 'ASISTEN%') 
                                       AND 
                                          NO_BCC NOT IN ( SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE VAL_EBCC_CODE IS NULL ) 
                                      ) ORDER BY DBMS_RANDOM.VALUE FETCH NEXT 1 ROWS ONLY";
            $get = $this->db_mobile_ins->select("$sql $in_query");
         }
         if($get_max[0]->parameter_desc==$no_val && count($get)==0) // GET DATA KRANI WHEN DATA KABUN IS NULL
         {
            $in_query = "HP.NO_BCC NOT IN (SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE ID_VALIDASI = 
                  NIK_KERANI_BUAH || '-' || NIK_MANDOR || '-'  || to_char(TANGGAL_RENCANA,'YYYYMMDD')) ORDER BY DBMS_RANDOM.VALUE FETCH NEXT 1 ROWS ONLY";
            $get = $this->db_mobile_ins->select("$sql $in_query");
         }
         
      return $get;
   }

   public function count_valid($date){
      $day =  date("Y-m-d", strtotime($date));
      $ba_afd_code = explode(",",session('LOCATION_CODE'));
      $code = implode("','", $ba_afd_code);
      $substr_id_ba_afd_blok = 5;
      if(session('REFFERENCE_ROLE')=='COMP_CODE')
      {
         $substr_id_ba_afd_blok = 2;
      }
      if(session('REFFERENCE_ROLE')=='BA_CODE')
      {
         $substr_id_ba_afd_blok = 4;
      }
      if(session('REFFERENCE_ROLE')=='REGION_CODE')
      {
         $substr_id_ba_afd_blok = 2;
      }
      if(session('werks') && session('afd'))
      {
         if(session('werks')!='')
         {
            $code = session('werks');
            $substr_id_ba_afd_blok = 4;
            if(session('werks')!='')
            {
               $code .= session('afd');
               $substr_id_ba_afd_blok = 5;
            }
         }
      }
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
                                             AND SUBSTR (drp.id_ba_afd_blok, 1, $substr_id_ba_afd_blok) in ('$code')
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
      $user = session('USERNAME');
      $user_pt = session('ba_afd_data');
      foreach($user_pt as $key => $val) 
      {
            $procedure = $this->db_mobile_ins->statement(" BEGIN 
                                                mobile_inspection.prc_tr_ebcc_compare (
                                                   '$day',
                                                   '$day',
                                                   '$key',
                                                   'MI Web : $user'
                                                ) ;
                                          END;");
      }
      $get = $this->db_mobile_ins->select(" SELECT tr_ebcc_compare.*,sap.export_status FROM tr_ebcc_compare
                                            LEFT JOIN ebcc.t_status_to_sap_ebcc sap ON sap.no_bcc = tr_ebcc_compare.ebcc_no_bcc
                                            WHERE  
                                               ( 
                                                 val_jabatan_validator IN ('KEPALA KEBUN',
                                                                           'KEPALA_KEBUN',
                                                                           'ASISTEN KEPALA',
                                                                           'ASISTEN_KEPALA',
                                                                           'EM',
                                                                           'SEM GM',
                                                                           'SENIOR ESTATE MANAGER') 
                                                   OR 
                                                 val_jabatan_validator LIKE 'ASISTEN%' 
                                               )
                                            AND to_char(val_date_time,'YYYY-MM-DD') =  '$day'
                                            AND akurasi_sampling_ebcc = 'MATCH'
                                            AND status_tph = 'ACTIVE'
                                            AND NVL (val_ebcc_code, 'x') NOT IN (SELECT NVL (val_ebcc_code, 'x') FROM tr_validasi_detail) 
                                            AND (( val_sumber = 'MI' AND val_ebcc_code NOT LIKE 'M%' ) OR ( val_sumber = 'ME' ))
      ");
      return $get;
   }
  
}