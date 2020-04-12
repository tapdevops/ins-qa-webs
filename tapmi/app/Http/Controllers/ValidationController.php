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
	use View;
	use Validator;
	use Redirect;
	use Session;
	use Config;
	use URL;
	use DateTime;
	use Maatwebsite\Excel\Facades\Excel;
	use App\Validation;
	use App\ValidationEbcc;
	use DataTables;

# API Setup
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
	public function index() {
		
		ini_set('memory_limit', '-1');
		$data['active_menu'] = $this->active_menu;
        $sql = "select distinct ebcc.id_ba,
                                ebcc.id_afd,
                                ebcc.tanggal_rencana,
                                ebcc.nik_kerani_buah,
                                ebcc.nama_krani_buah,
                                ebcc.nik_mandor,
                                ebcc.nama_mandor,
                                ebcc.id_validasi,
                                case when valid.jumlah_ebcc_validated is null then 0 else valid.jumlah_ebcc_validated end as jumlah_ebcc_validated,
                                param.target_validasi 
                from (SELECT SUBSTR (drp.id_ba_afd_blok, 1, 4) AS id_ba,
                                SUBSTR (drp.id_ba_afd_blok, 5, 1) AS id_afd,
                                hrp.id_rencana,
                                hrp.tanggal_rencana,
                                hrp.nik_kerani_buah,
                                hrp.nik_mandor,
                                emp_krani.emp_name 
                                || ' - ' 
                                || hrp.nik_kerani_buah AS nama_krani_buah,
                                emp_mandor.emp_name
                                || ' - '
                                || hrp.nik_mandor AS nama_mandor,
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
                                JOIN mobile_inspection.t_parameter param
                                ON 1=1
                                WHERE SUBSTR (ID_BA_AFD_BLOK, 1, 2) IN (SELECT comp_code FROM tap_dw.tm_comp@dwh_link)
                )  ebcc 
                    left join MOBILE_INSPECTION.TR_VALIDASI_HEADER valid on EBCC.ID_VALIDASI = VALID.ID_VALIDASI 
                    inner join MOBILE_INSPECTION.T_PARAMETER param on 1 = 1
                    WHERE ebcc.tanggal_rencana >= trunc(sysdate, 'yyyy') - interval '1' year
                    and ebcc.tanggal_rencana <  trunc(sysdate, 'yyyy')
                    order by ebcc.tanggal_rencana desc";
        $valid_data = json_encode($this->db_mobile_ins->select($sql));
		$result = json_decode( $valid_data,true);

		$data['data_header'] = $result;
		
		return view( 'validasi.listheader', $data );
		
	}

	// public function xx_index(){
		
	// 	$data['active_menu'] = $this->active_menu;
    //     return view('validasi.datatable',$data);
    // }

    // public function headerList(){
        
    //     $model = Validation::query()->orderBy('tanggal_rencana','DESC');
    //     return DataTables::eloquent($model)
    //             ->make(true);
        
    // }
    
    public function create($id)
    {   
        $data['active_menu'] = $this->active_menu;
        $string = str_replace(".","/",$id);
        $arr = explode("-", $string, 3);
        $nik_kerani = $arr[0];
        $nik_mandor = $arr[1];
        $tanggal = date("Y-m-d",strtotime($arr[2]));
        $data['data_validasi'] = Validation::where('NIK_KERANI_BUAH', $nik_kerani)
                                    ->where('NIK_MANDOR',$nik_mandor)
                                    ->whereDate('TGL_PANEN','=',$tanggal)
                                    ->orderByRaw('DBMS_RANDOM.VALUE FETCH NEXT 1 ROWS ONLY')
                                    ->get();
        return view('validasi.image_preview',$data);
    }

    public function filter_date($date) {
		ini_set('memory_limit', '-1');
		$data['active_menu'] = $this->active_menu;
        $sql = "select distinct ebcc.id_ba,
                                ebcc.id_afd,
                                ebcc.tanggal_rencana,
                                ebcc.nik_kerani_buah,
                                ebcc.nama_krani_buah,
                                ebcc.nik_mandor,
                                ebcc.nama_mandor,
                                ebcc.id_validasi,
                                case when valid.jumlah_ebcc_validated is null then 0 else valid.jumlah_ebcc_validated end as jumlah_ebcc_validated,
                                param.target_validasi 
                from (SELECT SUBSTR (drp.id_ba_afd_blok, 1, 4) AS id_ba,
                                SUBSTR (drp.id_ba_afd_blok, 5, 1) AS id_afd,
                                hrp.id_rencana,
                                hrp.tanggal_rencana,
                                hrp.nik_kerani_buah,
                                hrp.nik_mandor,
                                emp_krani.emp_name 
                                || ' - ' 
                                || hrp.nik_kerani_buah AS nama_krani_buah,
                                emp_mandor.emp_name
                                || ' - '
                                || hrp.nik_mandor AS nama_mandor,
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
                                JOIN mobile_inspection.t_parameter param
                                ON 1=1
                                WHERE SUBSTR (ID_BA_AFD_BLOK, 1, 2) IN (SELECT comp_code FROM tap_dw.tm_comp@dwh_link)
                )  ebcc 
                    left join MOBILE_INSPECTION.TR_VALIDASI_HEADER valid on EBCC.ID_VALIDASI = VALID.ID_VALIDASI 
                    inner join MOBILE_INSPECTION.T_PARAMETER param on 1 = 1
                    WHERE ebcc.tanggal_rencana = '$date'";
        $valid_data = json_encode($this->db_mobile_ins->select($sql));
		$result = json_decode( $valid_data,true);

		$data['data_header'] = $result;
		
		return view( 'validasi.listheader', $data );
		
	}


}