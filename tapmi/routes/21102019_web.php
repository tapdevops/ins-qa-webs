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
		
		#ora report
		Route::group(['prefix'=>'report-oracle'], function () {
			Route::get('download', ['as'=>'orareport.download', 'uses'=>'ReportOracleController@download']);
			Route::post('download', ['as'=>'orareport.download_proses', 'uses'=>'ReportOracleController@download_proses']);
			Route::get('nohup', ['as'=>'orareport.read_nohup', 'uses'=>'ReportOracleController@read_nohup']);
			Route::get('repair', ['as'=>'orareport.testing', 'uses'=>'ReportOracleController@testing']);
			
		});
	});

	// Cron URL
	Route::get( '/cron/generate/inspeksi', 'ReportController@cron_generate_inspeksi' );
	Route::get( '/cron/generate/token', 'ReportController@generate_token' );

});

Route::get( '/preview/compare-ebcc', 'ReportOracleController@view_page_report_ebcc_compare' );
Route::get( '/repair', 'ReportOracleController@testing' );