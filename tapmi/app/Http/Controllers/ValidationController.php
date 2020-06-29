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
		$this->db_mobile_ins = DB::connection('mobile_ins');
      $this->db_ebcc = DB::connection('ebcc');
	}

	#   		 									  		            ▁ ▂ ▄ ▅ ▆ ▇ █ Index
    # -------------------------------------------------------------------------------------
   
   public function index($tgl = null){
      if(empty($tgl)){
         $day =  date("Y-m-d", strtotime("yesterday"));
      }else{
         $day =  date("Y-m-d", strtotime($tgl));
      }
      $ba_afd_code =explode(",",session('LOCATION_CODE'));
      $code = implode("','", $ba_afd_code);
      $data['active_menu'] = $this->active_menu;
      $result = ( new ValidasiHeader() )->validasi_header($day);
      $res = json_encode($result);
      $data['data_header'] = json_decode($res,true);
      $data['tgl_validasi'] = $day;
      $data['records'] = $result;
      $valid = ( new ValidasiHeader() )->count_valid($day);
      $count_valid = count($valid);
      $status_validasi = 0;
      if($count_valid == 1){
         $status = $valid['0']->status_validasi;
         if($status == "unfinished"){
               $status_validasi = 1;
         }
         else{
               $status_validasi = 0;
         }        
      }else{
         $status_validasi = 1;
      }
      $data['status'] = $status_validasi;
      return view( 'validasi.listheader', $data );
   }


   public function getEbccValHeader(request $request){
      $data['active_menu'] = $this->active_menu;
      $day = $request->tanggal;
      $res = json_encode(( new ValidasiHeader() )->validasi_header($day));
      $data['data_header'] = json_decode($res,true);
      $data['session'] = session();
      return view( 'validasi.listheader', $data );
   }

   public function getValHeader(request $request){
      $data['active_menu'] = $this->active_menu;
      $day = $request->tanggal;
      $result = ( new ValidasiHeader() )->validasi_header($day);
      $res = json_encode( $result);
      $data['tgl_validasi'] = $day;
      $data['data_header'] = json_decode($res,true);
      $data['records'] = $result;
      $valid = ( new ValidasiHeader() )->count_valid($day);
      $count_valid = count($valid);
      $status_validasi = 0;
      if($count_valid == 1){
         $status = $valid['0']->status_validasi;
         if($status == "unfinished"){
               $status_validasi = 1;
         }
         else{
               $status_validasi = 0;
         }        
      }else{
         $status_validasi = 1;
      }
      $data['status'] = $status_validasi;
      return view( 'validasi.filtertable', $data );
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
	    
    public function xx_create($id) 
    {   
       
      $data['active_menu'] = $this->active_menu;

       //jika arr_id != null, maka explode utk daptkan id
            //for check if id di tr_validasi_header ada dan validate < target? got next id, jika tidak ada / kurang dari target maka buka form validasi,
            // get query berdasarkan kombinasi.
            // break
            //else kembali ke halaman list


        $string = str_replace(".","/",$id);
        $arr = explode("-", $string, 5);
        $nik_kerani = $arr[0];
        $nik_mandor = $arr[1];
        $tanggal = date("Y-m-d",strtotime($arr[2]));
        $tgl = $arr[2];
        $ba_code = $arr[3];
        $afd = $arr[4];
        $id_validasi = $nik_kerani."-".$nik_mandor."-".$tgl;

        $valid_data = json_encode(( new ValidasiHeader() )->validasi_askep($id));
        $result = json_decode( $valid_data,true);
                   
        $i = 1; //start jumlah validasi
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

    public function create($tgl) 
    {   
      $data['active_menu'] = $this->active_menu;
      
      $target = TMParameter::select('PARAMETER_DESC')->where('PARAMETER_NAME','TARGET_VALIDASI')->get();
      $target_validasi = $target[0]->parameter_desc;
      $result = array();
      $result = ( new ValidasiHeader() )->validasi_header($tgl);
      $res = json_encode( $result);
      
      //check apakah semua sudah divalidasi
      $valid = ( new ValidasiHeader() )->count_valid($tgl);
      $count_valid = count($valid);
      $status_validasi = 0;
      if($count_valid == 1){
         $status = $valid['0']->status_validasi;
         if($status == "unfinished"){
               $status_validasi = 1;
         }
         else{
               $status_validasi = 0;
         }        
      }else{
         $status_validasi = 1;
      }
      if($status_validasi == 1){
         $dtval=json_decode($res,true);
         foreach ( $dtval as $dt) {
            $jml[] =  $dt['jumlah_ebcc_validated'];
            $target_id[] =  $dt['target_validasi'];
            $nik_kerani[] = $dt['nik_kerani_buah'];
            $nik_mandor[] = $dt['nik_mandor'];
            $tgl_rencana[] = date("Y-m-d",strtotime($dt['tanggal_rencana']));
            $ba_code[] = $dt['id_ba'];
            $afd[] = $dt['id_afd'];
            $id_validasi[] = $dt['id_validasi'];
         }
         for($i=0; $i < count($dtval); $i++){
                  // jika kurang dari target validasi
                  if ($jml[$i] == $target_id[$i]){
                     continue;
                  }else{
                     $nik_kerani_val = $nik_kerani[$i];
                     $nik_mandor_val  = $nik_mandor[$i];
                     $tgl_rencana_val  = $tgl_rencana[$i];
                     $ba_code_val  = $ba_code[$i];
                     $afd_val  = $afd[$i];
                     $id_validasi_val  = $id_validasi[$i];
                     $trg = $target_id[$i];

                     $valid_data = json_encode(( new ValidasiHeader() )->validasi_askep($ba_code_val,$afd_val,$nik_kerani_val,$nik_mandor_val,$tgl_rencana_val));
                     $data_validasi = json_decode( $valid_data,true);
                                 
                     $i = 1; //start jumlah validasi
                     $no_val = TRValidasiHeader::select('JUMLAH_EBCC_VALIDATED')->where('ID_VALIDASI',$id_validasi_val)->first();

                     // dd($no_val,$no_val['jumlah_ebcc_validated']);
                     if($no_val == null){
                           $val = 1;
                     }else{
                           $val = $i + $no_val['jumlah_ebcc_validated'];
                     }
                     $data['data_validasi'] = $data_validasi;
                     $data['no_validasi'] = $val;
                     $data['target'] = $trg;
                     return view('validasi.image_preview',$data);
                  }        
            }
      }
      else{
         return Redirect::to('listvalidasi/'.$tgl);
      }
       
   }

    public function create_action(Request $request)
    { 
        // dd($request);
        date_default_timezone_set('Asia/Jakarta');
        $id_val = $request->id_validasi."-".$request->ba_code."-".$request->afd_code;
        $id = str_replace("/",".",$id_val);
        $tgl = $request->tanggal_ebcc;
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
         
         $TRValidasiDetail = TRValidasiDetail::create($request->except('last_updated','target')+$data);

         // INSERT LOG TO EBCC
         $this->db_ebcc->table('T_VALIDASI')->insert([
            'NO_BCC'=>$TRValidasiDetail->no_bcc,
            'TANGGAL_VALIDASI' => date('Y-m-d H:i:s'),
            'ROLES' => session('USER_ROLE'),
            'NIK' => session('NIK'),
            'NAMA' => $fullname
         ]);

         // UPDATE BCC HASIL PANEN KUALITAS 
         if($request->jumlah_ebcc_validated != $request->jjg_ebcc_total)
         {
            if($request->jumlah_ebcc_validated >= $request->jjg_ebcc_total)
            {
               $selisih = $request->jumlah_ebcc_validated - $request->jjg_ebcc_total;
               $this->db_ebcc->table('T_HASILPANEN_KUALTAS')->where([
                  'ID_BCC'=>$TRValidasiDetail->no_bcc,
                  'ID_KUALITAS' => 3
               ])->update(['QTY'=>DB::raw('QTY + '.$selisih)]);
            }
            else 
            {
               $selisih = $request->jjg_ebcc_total - $request->jumlah_ebcc_validated;
               $data = $this->db_ebcc->table('T_HASILPANEN_KUALTAS')->
                                       where(['ID_BCC'=>$TRValidasiDetail->no_bc])->
                                       whereIn('ID_KUALITAS',[1,3,4,6,15])->
                                       get()->pluck('QTY','ID_KUALITAS')->toArray();
               // PENGURANGAN QUANTITY MENTAH
               if(ISSET($data[1]) && $selisih>0)
               {
                  $pengurangan = $data[1] - $selisih;
                  $selisih -= $data[1]>=$selisih?$selisih:$data[1];
                  $data[1] = $pengurangan>=0?$pengurangan:0;
                  $this->db_ebcc->table('T_HASILPANEN_KUALTAS')->where([
                     'ID_BCC'=>$TRValidasiDetail->no_bcc,
                     'ID_KUALITAS' => 1
                  ])->update(['QTY'=>$data[1]]);
               }
               // PENGURANGAN QUANTITY BUSUK
               if(ISSET($data[6]) && $selisih>0)
               {
                  $pengurangan = $data[6] - $selisih;
                  $selisih -= $data[6]>=$selisih?$selisih:$data[6];
                  $data[6] = $pengurangan>=0?$pengurangan:0;
                  $this->db_ebcc->table('T_HASILPANEN_KUALTAS')->where([
                     'ID_BCC'=>$TRValidasiDetail->no_bcc,
                     'ID_KUALITAS' => 6
                  ])->update(['QTY'=>$data[6]]);
               }
               // PENGURANGAN QUANTITY JAJANG KOSONG
               if(ISSET($data[15]) && $selisih>0)
               {
                  $pengurangan = $data[15] - $selisih;
                  $selisih -= $data[15]>=$selisih?$selisih:$data[6];
                  $data[15] = $pengurangan>=0?$pengurangan:0;
                  $this->db_ebcc->table('T_HASILPANEN_KUALTAS')->where([
                     'ID_BCC'=>$TRValidasiDetail->no_bcc,
                     'ID_KUALITAS' => 15
                  ])->update(['QTY'=>$data[15]]);
               }
               // PENGURANGAN QUANTITY OVERRIPE
               if(ISSET($data[4]) && $selisih>0)
               {
                  $pengurangan = $data[4] - $selisih;
                  $selisih -= $data[4]>=$selisih?$selisih:$data[6];
                  $data[4] = $pengurangan>=0?$pengurangan:0;
                  $this->db_ebcc->table('T_HASILPANEN_KUALTAS')->where([
                     'ID_BCC'=>$TRValidasiDetail->no_bcc,
                     'ID_KUALITAS' => 4
                  ])->update(['QTY'=>$data[4]]);
               }
               // PENGURANGAN QUANTITY MASAK
               if(ISSET($data[3]) && $selisih>0)
               {
                  $data[3] = $data[3] - $selisih;
                  $this->db_ebcc->table('T_HASILPANEN_KUALTAS')->where([
                     'ID_BCC'=>$TRValidasiDetail->no_bcc,
                     'ID_KUALITAS' => 3
                  ])->update(['QTY'=>$data[3]]);
               }
            }
         }
         
         return Redirect::to('validasi/create/'.$tgl);

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
        nvl(jjg_validate_total,0) as jjg_validate_total
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
                TO_CHAR(validasi.insert_time, 'YYYY-MM-DD HH24:MI') AS tanggal_validasi,
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
              WHERE ROWNUM = '1'
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