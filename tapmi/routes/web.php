<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get( '/test', function(){
	dd(DB::connection('mongodb_hectarstatment')->collection('TM_ROAD')->get());
} );

Route::group( [ 'middleware' => 'web' ], function() {
	# Auth
	Route::get( '/login', 'AuthController@login_form' );
	Route::post( '/login', 'AuthController@login_proses' );
	Route::group( [ 'middleware' => 'session' ], function() {
		# Auth
		Route::get( '/logout', 'AuthController@logout' );

		# Dashboard
		Route::get( '/', 'DashboardController@index' );
		Route::get( '/dashboard', 'DashboardController@index' );

		# Modules
		Route::get( '/modules', 'ModulesController@index' );
		Route::get( '/modules/data', 'ModulesController@index' );
		Route::get( '/modules/user-authorization/{id}', 'ModulesController@user_authorization_detail' );
		Route::get( '/modules/user-authorization', 'ModulesController@user_authorization' );
		Route::post( '/modules/user-authorization', 'ModulesController@user_authorization_proses' );
		Route::get( '/modules/create', 'ModulesController@create' );
		Route::post( '/modules/create', 'ModulesController@create_proses' );
		Route::get( '/modules/setup-menu/{id}', 'ModulesController@setup_menu' );
		Route::get( '/modules/setup-menu', 'ModulesController@setup_menu' );

		# Master User
		Route::get( '/user', 'UserController@index' );
		Route::get( '/user/create', 'UserController@create' );
		Route::post( '/user/create', 'UserController@create_proses' );
		Route::get( '/user/edit/{id}', 'UserController@edit' );
		Route::post( '/user/edit/{id}', 'UserController@edit_proses' );
		Route::get( '/user/search-user', 'UserController@search_user' );
		Route::get('/user/export', 'UserController@user_download');
		
		# Report
		Route::get( '/report', 'ReportController@index' );
		Route::get( '/report/download', 'ReportController@download' );
		Route::post( '/report/download', 'ReportController@download_proses' );
		Route::post( '/report/generate', 'ReportController@generate_proses' );
		Route::get( '/report/search-region', 'ReportController@search_region' );
		Route::get( '/report/search-comp', 'ReportController@search_comp' );
		Route::get( '/report/search-est', 'ReportController@search_est' );
		Route::get( '/report/search-afd', 'ReportController@search_afd' );
		Route::get( '/report/search-block', 'ReportController@search_block' );
		Route::get( '/data/user-search', 'DataController@user_search_find' );

		# Upload
		Route::get( '/upload', 'UploadController@import_data' );
		Route::post( '/upload', 'UploadController@import_data_process' );
		Route::get( '/upload/photo', 'UploadController@import_photo' );
		Route::post( '/upload/photo', 'UploadController@import_photo_process' );
		
		#ora report
		Route::group( [ 'prefix' => 'report-oracle' ], function () {
			Route::get( 'kafka-control', ['as'=>'orareport.download', 'uses'=>'ReportOracleController@kafka_control']);
			Route::get( 'download', ['as'=>'orareport.download', 'uses'=>'ReportOracleController@download']);
			Route::post( 'download', ['as'=>'orareport.download_proses', 'uses'=>'ReportOracleController@download_proses']);
			Route::get( 'nohup', ['as'=>'orareport.read_nohup', 'uses'=>'ReportOracleController@read_nohup']);
			Route::get( 'repair', ['as'=>'orareport.testing', 'uses'=>'ReportOracleController@testing']);
			Route::get( 'repair', ['as'=>'orareport.testing', 'uses'=>'ReportOracleController@testing']);
			Route::get( 'import-db', ['as'=>'orareport.import_db', 'uses'=>'ReportOracleController@import_data']);
			Route::get( 'import-db/log', ['as'=>'orareport.import_db', 'uses'=>'ReportOracleController@import_data_log']);
			Route::post( 'import-db', ['as'=>'orareport.import_db', 'uses'=>'ReportOracleController@import_data_process']);
			Route::get('/summary/{ba_code}/{start_date}/{end_date}', 'TempController@download_proses')->name('download_temp/{ba_code}/{start_date}/{end_date}');
			Route::get('/summarykrani/{ba_code}/{start_date}/{end_date}', 'Temp2Controller@download_proses')->name('download_temp2/{ba_code}/{start_date}/{end_date}');
		});


		#validation_image_data
		Route::get( '/listvalidasi/{tgl?}', 'ValidationController@index'); 
		Route::get( '/validasi/create/{id}', 'ValidationController@create' );
		Route::get( '/validasi/cek_aslap', 'ValidationController@cek_aslap'); 
		Route::post( '/validasi/create_action', 'ValidationController@create_action')->name('create_validation');
	});

	// Cron URL
	Route::get( '/cron/generate/inspeksi', 'ReportController@cron_generate_inspeksi' );
	Route::get( '/cron/generate/token', 'ReportController@generate_token' );

});

Route::get( '/preview/compare-ebcc/{id}', 'ReportOracleController@view_page_report_ebcc_compare' );
Route::get( '/pdf/compare-ebcc/{id}', 'ReportOracleController@pdf_report_ebcc_compare' );
Route::get( '/preview/finding/{id}', 'ReportOracleController@view_page_report_finding' );
Route::get( '/repair', 'ReportOracleController@testing' );
Route::get( '/nohup', 'ReportOracleController@nohup' );
Route::get( '/phpinfo', 'ReportOracleController@phpinfo' );
Route::get( '/testings', 'KafkaProducerController@test' );

Route::get( '/kafka/tm_user_auth', 'KafkaController@RUN_INS_MSA_AUTH_TM_USER_AUTH' );


// Route::get( '/getGuzzleRequest', 'ValidationController@getGuzzleRequest' );
Route::get( '/getNewdata', 'ValidationController@getEbccValHeader' );
Route::get( '/getNewdata2', 'ValidationController@getValHeader' );

Route::get( '/validasi/compare-ebcc/{id}', 'ValidationController@compare_ebcc');


#image
Route::get( '/storage/{filename}', 'StorageController@image' );

#json

Route::get('/filter/{date}', 'ValidationController@getAllfilter');
Route::get('/filter', 'ValidationController@getAll');