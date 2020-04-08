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

# API Setup
	use App\APIData as Data;

class ValidationController extends Controller {

	protected $active_menu;

	public function __construct() {
		$this->active_menu = '_'.str_replace( '.', '', '02.01.00.00.00' ).'_';
		$this->db_ebcc = DB::connection('ebcc');
		$this->db_mobile_ins = DB::connection('mobile_ins');
	}

	#   		 									  		            ▁ ▂ ▄ ▅ ▆ ▇ █ Index
	# -------------------------------------------------------------------------------------
	// public function list() {
	// 	$data['active_menu'] = $this->active_menu;
	// 	return view( 'validasi.listheader', $data );
	// }
	public function index() {
		
		ini_set('memory_limit', '-1');
		$data['active_menu'] = $this->active_menu;


		$query_result = json_encode( $this->db_ebcc->select( "SELECT * from EBCC_HASIL_PANEN ORDER BY TGL_PANEN DESC" ));
		$result = json_decode( $query_result,true);
		foreach ( $result as $key => $q ){
			$dt_bcc[] = $q['no_bcc'];
		}
		$valid_data = json_encode( $this->db_mobile_ins->select( "SELECT a.JUMLAH_EBCC_VALIDATED, TRIM('.' FROM b.NO_BCC)as NO_BCC_MI
		from TR_VALIDASI_HEADER a left join TR_VALIDASI_DETAIL b on a.ID_VALIDASI = b.ID_VALIDASI" ));
		$valid_result = json_decode( $valid_data,true);

		$data['data_header'] = $result;
		$data['validated'] = $valid_result;
		
		return view( 'validasi.listheader', $data );
		
	}

}