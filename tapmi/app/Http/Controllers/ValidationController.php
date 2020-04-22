<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Response;
	use Illuminate\Routing\Controller;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Input;
	use Illuminate\Support\Collection;
	use Illuminate\Support\Facades\File;
   use Illuminate\Support\Facades\Storage;
   use GuzzleHttp\Exception\GuzzleException;
   use GuzzleHttp\Client;
	use View;
	use Validator;
	use Redirect;
	use Session;
	use Config;
	use URL;
	use DateTime;
	use Maatwebsite\Excel\Facades\Excel;
	use App\Validation;
	use App\Employee;
	use App\TMParameter;
	use App\TRValidasiHeader;
	use App\TRValidasiDetail;
   use DataTables;
   use Ramsey\Uuid\Uuid;
   use App\ValidasiHeader;


   # API Setup
   use App\APISetup;
   use App\APIData as Data;


class ValidationController extends Controller {

	protected $active_menu;

	public function __construct() {
		$this->active_menu = '_'.str_replace( '.', '', '02.04.00.00.00' ).'_';
		$this->db_ebcc = DB::connection('ebcc');
		$this->db_mobile_ins = DB::connection('mobile_ins');
	}

	#   		 									  		            ▁ ▂ ▄ ▅ ▆ ▇ █ Index
    # -------------------------------------------------------------------------------------
   
   public function index(){
      $day =  date("Y-m-d", strtotime("yesterday"));
      $ba_afd_code =explode(",",session('LOCATION_CODE'));
      $code = implode("','", $ba_afd_code);
      $data['active_menu'] = $this->active_menu;
      $res = json_encode(( new ValidasiHeader() )->validasi_header($day));
      $data['data_header'] = json_decode($res,true);
      return view( 'validasi.listheader', $data );
   }

   public function ss_index() {
      // $day =  date("Y-m-d", strtotime("yesterday"));
		$data['active_menu'] = $this->active_menu;
		// if ( in_array( session('USER_ROLE'), $allowed_role ) ) {
			$data['data_header'] = array();
			if ( !empty( Data::val_header_find() ) ) {
				$i = 0;
				foreach ( Data::val_header_find() as $q ) {
					if ( isset( $q['nama_krani_buah'] ) && isset( $q['tanggal_rencana'] ) ) {
						$data['data_header'][$i]['tanggal_rencana'] = $q['tanggal_rencana'];
						$data['data_header'][$i]['nama_krani_buah'] = $q['nama_krani_buah'];
						$data['data_header'][$i]['id_afd'] = $q['id_afd'];
						$data['data_header'][$i]['nama_mandor'] = $$q['nama_mandor'];
						$data['data_header'][$i]['jumlah_ebcc_validated'] = $q['jumlah_ebcc_validated'];
						$data['data_header'][$i]['target_validasi'] =  $q['target_validasi'];
						$data['data_header'][$i]['id_validasi'] = $q['id_validasi'];
						$data['data_header'][$i]['id_ba'] = $q['id_ba'];
						$i++;
					}
					
				}
			}

			return view( 'validasi.listheader', $data );
		// }
		
	}

   public function getAllfilter($date){
      $day =  date("Y-m-d", strtotime($date));
      $res = ( new ValidasiHeader() )->validasi_header($day);
      return response()->json($res);
   }
   public function getAll(){
      $ba_afd_code =explode(",",session('LOCATION_CODE'));
      $code = implode("','", $ba_afd_code);
      $res = ( new ValidasiHeader() )->data();
      return response()->json($res);
      // return response()->json([
      //       "data" => $res
      // ], 201);
   }

	public function xx_index() {
      // if (env('APP_ENV') == 'local'){
      //    $env = 'dwh_link';
      // } else{
      //    $env = 'proddw_link';
      // }
      $day =  date("Y-m-d", strtotime("yesterday"));

        $ba_afd_code =explode(",",session('LOCATION_CODE'));
        $code = implode("','", $ba_afd_code);
        // dd($code);
		   $data['active_menu'] = $this->active_menu;
         $sql = "SELECT ebcc.id_ba,
                        ebcc.id_afd,
                        to_char(ebcc.tanggal_rencana,'DD-MON-YY') AS tanggal_rencana,
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
                           ON 1 = 1  
                           ";
                    
		   ini_set('memory_limit', '-1');
         $valid_data = json_encode($this->db_mobile_ins->select($sql));
         $result = json_decode( $valid_data,true);
         $data['data_header'] = $result;         
         return view( 'validasi.listheader', $data );
		
	}

    
    public function create($id)
    {   
        $data['active_menu'] = $this->active_menu;
        $string = str_replace(".","/",$id);
        $arr = explode("-", $string, 5);
        $nik_kerani = $arr[0];
        $nik_mandor = $arr[1];
        $tanggal = date("Y-m-d",strtotime($arr[2]));
        $tgl = $arr[2];
        $ba_code = $arr[3];
        $afd = $arr[4];
        $id_validasi = $nik_kerani."-".$nik_mandor."-".$tgl;

        $sql = " SELECT 
                        HDP.TANGGAL_RENCANA,
                        HDP.NIK_MANDOR,
                        HDP.NIK_KERANI_BUAH,
                        EMP_EBCC.EMP_NAME,
                        HDP.ID_BA_AFD_BLOK,
                        HP.NO_TPH,
                        HP.NO_BCC,
                        HP.PICTURE_NAME,
                        TB.ID_BLOK,
                        TB.BLOK_NAME,
                        TBA.NAMA_BA,
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
                                HRP.NIK_MANDOR AS NIK_MANDOR,
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
                        HDP.NIK_KERANI_BUAH = '$nik_kerani' AND
                        HDP.NIK_MANDOR = '$nik_mandor' AND
                        HDP.TANGGAL_RENCANA = '$tanggal' AND
                        SUBSTR (HDP.ID_BA_AFD_BLOK, 1, 4) = '$ba_code' AND --id_ba
                        SUBSTR (HDP.ID_BA_AFD_BLOK, 5, 1) = '$afd'  -- id_afd
                        AND
                        HP.NO_BCC NOT IN (SELECT NO_BCC FROM TR_VALIDASI_DETAIL WHERE ID_VALIDASI = 
                        HDP.NIK_KERANI_BUAH || '-' || HDP.NIK_MANDOR || '-'  || to_char(HDP.TANGGAL_RENCANA,'YYYYMMDD'))
                ORDER BY DBMS_RANDOM.VALUE FETCH NEXT 1 ROWS ONLY ";
        $valid_data = json_encode($this->db_mobile_ins->select($sql));
        $result = json_decode( $valid_data,true);
                   
        $i = 1;
        $no_val = TRValidasiHeader::select('JUMLAH_EBCC_VALIDATED')->where('ID_VALIDASI',$id_validasi)->first();
        
        // dd($no_val,$no_val['jumlah_ebcc_validated']);
        if($no_val == null){
            $val = 1;
        }else{
            $val = $i + $no_val['jumlah_ebcc_validated'];
        }
        $target = TMParameter::select('PARAMETER_DESC')->where('PARAMETER_NAME','TARGET_VALIDASI')->get();

        $data['data_validasi'] = $result;
        $data['no_validasi'] = $val;
        $data['target'] = $target[0]->parameter_desc;

        return view('validasi.image_preview',$data);
    }

    public function create_action(Request $request)
    { 
        // dd($request);
        $id_val = $request->id_validasi."-".$request->ba_code."-".$request->afd_code;
        $id = str_replace("/",".",$id_val);
        $jml = $request->jumlah_ebcc_validated;
            if($request->kondisi_foto == null){
                if($request->jjg_validate_total == null or $request->jjg_validate_total == "0" ){
                    $foto = "TIDAK BISA DIVALIDASI, KARENA ".strtoupper($request->kondisi_foto);
                    // $jml_validate = $jml;
                    $jml_validate = $jml-1;
                }else{
                    $foto = "BISA DIVALIDASI";
                    $jml_validate = $jml;
                }
            }else{
                $foto = "TIDAK BISA DIVALIDASI, KARENA ".strtoupper($request->kondisi_foto) ;
                $jml_validate = $jml - 1;
                // $jml_validate = $jml;
            }
            
            $jmlh['jumlah_ebcc_validated'] = $jml_validate;
            // $result1 =TRValidasiHeader::firstOrCreate($request->only('id_validasi','last_update')+$jmlh);     
            // dd($request);       
            $request->merge([ 'jumlah_ebcc_validated' => $jml_validate ]);
            $request->merge([ 'kondisi_foto' => $foto ]);
            $request->merge([ 'last_update' => date('Y-M-d H.i.s') ]);
            // ,$request->only('id_validasi','jumlah_ebcc_validated','last_update')
            TRValidasiHeader::updateOrCreate(['id_validasi'=>$request->id_validasi],[ 'jumlah_ebcc_validated'=>$request->jumlah_ebcc_validated, 'last_update' => $request->last_update]);            
            $emp = Employee::where('EMPLOYEE_NIK',session('NIK'))->first();
            $fullname = $emp['employee_fullname'];
            $data['insert_time'] = date('Y-M-d H.i.s');
            $data['insert_user'] = session('NIK');
            $data['insert_user_fullname'] = $fullname;
            $data['insert_user_userrole'] = session('USER_ROLE');
            $data['uuid']	= Uuid::uuid1()->toString();
			// $result = TRValidasiDetail::create($request->except('jumlah_ebcc_validated','last_updated','kodisi_foto')+$data);
			TRValidasiDetail::create($request->except('last_updated')+$data);
            // dd($result1);
            if($jml_validate < 3){
                return Redirect::to('validasi/create/'.$id);
            }else{
                return Redirect::to('listvalidasi');
            }

    }


    public function compare_ebcc($id) {
        $sql = " SELECT tanggal_rencana AS tanggal_ebcc,
        nik_kerani_buah,
        nama_kerani_buah,
        id_ba AS kode_ba,
        est.comp_name AS nama_pt,
        est.est_name AS bisnis_area,
        id_afd AS afd,
        id_blok AS blok,
        est.block_name AS nama_blok,
        no_tph AS tph,
        no_bcc AS no_bcc,
        picture_name,
        ebcc_jml_bm,
        ebcc_jml_bk,
        ebcc_jml_ms,
        ebcc_jml_or,
        ebcc_jml_bb,
        ebcc_jml_jk,
        ebcc_jml_ba,
        ebcc_total,
        tanggal_validasi,
        kondisi_foto,
        nik_pembuat,
        nama_pembuat,
        user_role,
        jjg_validate_bm,
        jjg_validate_bk,
        jjg_validate_ms,
        jjg_validate_or,
        jjg_validate_bb,
        jjg_validate_jk,
        jjg_validate_ba,
        jjg_validate_total
   FROM (SELECT ebcc.tanggal_rencana,
                ebcc.nik_kerani_buah,
                ebcc.nama_kerani_buah,
                ebcc.id_ba,
                ebcc.id_afd,
                ebcc.id_blok,
                ebcc.no_tph,
                ebcc.no_bcc,
                ebcc.picture_name,
                ebcc.ebcc_jml_bm,
                ebcc.ebcc_jml_bk,
                ebcc.ebcc_jml_ms,
                ebcc.ebcc_jml_or,
                ebcc.ebcc_jml_bb,
                ebcc.ebcc_jml_jk,
                ebcc.ebcc_jml_ba,
                  ebcc.ebcc_jml_bm
                + ebcc.ebcc_jml_bk
                + ebcc.ebcc_jml_ms
                + ebcc.ebcc_jml_or
                + ebcc.ebcc_jml_bb
                + ebcc.ebcc_jml_jk
                + ebcc.ebcc_jml_ba
                   AS ebcc_total,
                validasi.jjg_validate_bm,
                validasi.jjg_validate_bk,
                validasi.jjg_validate_ms,
                validasi.jjg_validate_or,
                validasi.jjg_validate_bb,
                validasi.jjg_validate_jk,
                validasi.jjg_validate_ba,
                validasi.jjg_validate_total,
                validasi.kondisi_foto,
                TRUNC (validasi.insert_time) AS tanggal_validasi,
                validasi.insert_user AS nik_pembuat,
                validasi.insert_user_fullname AS nama_pembuat,
                validasi.insert_user_userrole AS user_role
           FROM (SELECT tanggal_rencana,
                        nik_kerani_buah,
                        nama_kerani_buah,
                        id_ba,
                        id_afd,
                        id_blok,
                        no_tph,
                        no_bcc,
                        ebcc_jml_bm,
                        ebcc_jml_bk,
                        ebcc_jml_ms,
                        ebcc_jml_or,
                        ebcc_jml_bb,
                        ebcc_jml_jk,
                        ebcc_jml_ba,
                        picture_name
                   FROM (  SELECT hrp.tanggal_rencana,
                                  SUBSTR (id_ba_afd_blok, 1, 4) id_ba,
                                  SUBSTR (id_ba_afd_blok, 5, 1) id_afd,
                                  SUBSTR (id_ba_afd_blok, 6, 3) id_blok,
                                  hp.no_tph no_tph,
                                  hp.picture_name,
                                  COUNT (DISTINCT hp.no_bcc) jlh_ebcc,
                                  MAX (hrp.nik_kerani_buah) nik_kerani_buah,
                                  MAX (emp_ebcc.emp_name) nama_kerani_buah,
                                  MAX (no_bcc) no_bcc,
                                  MAX (hp.no_rekap_bcc) no_rekap_bcc,
                                  SUM (
                                     CASE
                                        WHEN thk.id_kualitas = 1 THEN thk.qty
                                     END)
                                     ebcc_jml_bm,
                                  SUM (
                                     CASE
                                        WHEN thk.id_kualitas = 2 THEN thk.qty
                                     END)
                                     ebcc_jml_bk,
                                  SUM (
                                     CASE
                                        WHEN thk.id_kualitas = 3 THEN thk.qty
                                     END)
                                     ebcc_jml_ms,
                                  SUM (
                                     CASE
                                        WHEN thk.id_kualitas = 4 THEN thk.qty
                                     END)
                                     ebcc_jml_or,
                                  SUM (
                                     CASE
                                        WHEN thk.id_kualitas = 6 THEN thk.qty
                                     END)
                                     ebcc_jml_bb,
                                  SUM (
                                     CASE
                                        WHEN thk.id_kualitas = 15 THEN thk.qty
                                     END)
                                     ebcc_jml_jk,
                                  SUM (
                                     CASE
                                        WHEN thk.id_kualitas = 16 THEN thk.qty
                                     END)
                                     ebcc_jml_ba
                             FROM ebcc.t_header_rencana_panen hrp
                                  LEFT JOIN ebcc.t_detail_rencana_panen drp
                                     ON hrp.id_rencana = drp.id_rencana
                                  LEFT JOIN ebcc.t_hasil_panen hp
                                     ON     hp.id_rencana = drp.id_rencana
                                        AND hp.no_rekap_bcc = drp.no_rekap_bcc
                                  LEFT JOIN ebcc.t_employee emp_ebcc
                                     ON emp_ebcc.nik = hrp.nik_kerani_buah
                                  LEFT JOIN ebcc.t_hasilpanen_kualtas thk
                                     ON     hp.no_bcc = thk.id_bcc
                                        AND hp.id_rencana = thk.id_rencana
                            WHERE     SUBSTR (id_ba_afd_blok, 1, 2) IN (SELECT comp_code
                                                                          FROM tap_dw.tm_comp@proddw_link)
                                  AND hp.no_bcc = '$id' --tinggal ganti nomor bcc nya
                         GROUP BY hrp.tanggal_rencana,
                                  SUBSTR (id_ba_afd_blok, 1, 4),
                                  SUBSTR (id_ba_afd_blok, 5, 1),
                                  SUBSTR (id_ba_afd_blok, 6, 3),
                                  hp.no_tph,
                                  hp.picture_name)) ebcc
                LEFT JOIN mobile_inspection.tr_validasi_detail validasi
                   ON     ebcc.no_bcc = REPLACE (validasi.no_bcc, '.')
                      AND ebcc.nik_kerani_buah = validasi.nik_krani_buah
                      AND ebcc.tanggal_rencana = validasi.tanggal_ebcc
                      AND ebcc.id_ba = validasi.ba_code
                      AND ebcc.id_afd = validasi.afd_code
                      AND ebcc.id_blok = validasi.block_code
                      AND ebcc.no_tph = validasi.no_tph) mst
        LEFT JOIN
        (SELECT DISTINCT tc.comp_name,
                         est.werks,
                         est.est_name,
                         afd.afd_code,
                         afd.afd_name,
                         blok.block_code,
                         blok.block_name
           FROM tap_dw.tm_comp@proddw_link tc
                LEFT JOIN tap_dw.tm_est@proddw_link est
                   ON tc.comp_code = est.comp_code
                LEFT JOIN tap_dw.tm_afd@proddw_link afd
                   ON est.werks = afd.werks
                LEFT JOIN tap_dw.tm_block@proddw_link blok
                   ON afd.werks = blok.werks AND afd.afd_code = blok.afd_code
          WHERE TRUNC (SYSDATE) BETWEEN est.start_valid AND est.end_valid) est
           ON     mst.id_ba = est.werks
              AND mst.id_afd = est.afd_code
              AND mst.id_blok = est.block_code
        ";
        $valid_data = json_encode($this->db_mobile_ins->select($sql));
        $results['data'] =  json_decode($valid_data,true);
        // dd($result['data']== null);
		if ( !empty( $results['data']) ) {
			return view( 'validasi/ebcc-compare', $results );
		}
		else {
			return 'Data not found.';
		}
	}



}