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

	#   		 									  		            ▁ ▂ ▄ ▅ ▆ ▇ █ Index
	# -------------------------------------------------------------------------------------
	public function index() {
		return view( 'dashboard.index' );
	}

}