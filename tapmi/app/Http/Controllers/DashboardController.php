<?php

# Namespace
	namespace App\Http\Controllers;

# Default Laravel Vendor Setup
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

class DashboardController extends Controller {

	protected $active_menu;

	public function __construct() {
		$this->active_menu = '_'.str_replace( '.', '', '02.01.00.00.00' ).'_';
	}

	#   		 									  		            ▁ ▂ ▄ ▅ ▆ ▇ █ Index
	# -------------------------------------------------------------------------------------
	public function index() {
		$data['active_menu'] = $this->active_menu;
		return view( 'dashboard.index', $data );
	}

}