@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'User Authorization' )

@section( 'subheader' )
	<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
		<li class="m-nav__item">
			<a href="{{ url( '/modules' ) }}" class="m-nav__link">
				<span class="m-nav__link-text">
					Modules
				</span>
			</a>
		</li>
		<li class="m-nav__separator">
			-
		</li>
		<li class="m-nav__item">
			<a href="" class="m-nav__link">
				<span class="m-nav__link-text">
					User Authorization
				</span>
			</a>
		</li>
	</ul>
@endsection

@section( 'content' )
	<div class="row">
		<div class="col-md-8">
			<div class="row">
				<div class="col-md-4">
					<div class="m-input-icon m-input-icon--left">
						<input type="text" class="form-control m-input m-input--solid" placeholder="Search..." id="generalSearch">
						<span class="m-input-icon__icon m-input-icon__icon--left">
							<span>
								<i class="la la-search"></i>
							</span>
						</span>
					</div>
				</div>
				<div class="col-md-4">
					<!--a href="{{ url( '/modules/user-authorization' ) }}" style="color:inherit;" class="btn btn-default btn-block"><i class="fa fa-lock"></i> User Authorization</a-->
				</div>
				<div class="col-md-4"></div>
			</div>
		</div>
		
		<div class="col-md-4 m--align-right">
			<a href="{{ url( '/user/create' ) }}" class="btn btn-focus m-btn m-btn--custom m-btn--icon m-btn--air m-btn--pill">
				<span>
					<i class="fa fa-plus"></i>
					<span>Tambah</span>
				</span>
			</a>
			<div class="m-separator m-separator--dashed d-xl-none"></div>
		</div>
	</div>

	<table class="m-datatable" id="html_table" width="100%" style="margin-top:20px;">
		<thead>
			<tr>
				<th>Module Code</th>
				<th>Module Name</th>
				@foreach ( $parameter as $q )
					<th>{{ $q['DESC'] }}</th>
				@endforeach
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			@foreach ( $modules as $q )
				<tr>
					<td>{{ $q['MODULE_CODE'] }}</td>
					<td>{{ $q['MODULE_NAME'] }}</td>
					@foreach ( $parameter as $z )
						<td width="240px;">
							@if ( isset( $user_authorization[$q['MODULE_CODE']][$z['PARAMETER_NAME']] ) )
								@if ( $user_authorization[$q['MODULE_CODE']][$z['PARAMETER_NAME']]['STATUS'] == 1 )
									<center><input id="" onchange="return update( '{{ $q['MODULE_CODE'] }}', '{{ $z['PARAMETER_NAME'] }}' )" type="checkbox" checked></center>
								@else
									<center><input id="" onchange="return update( '{{ $q['MODULE_CODE'] }}', '{{ $z['PARAMETER_NAME'] }}' )" type="checkbox"></center>
								@endif
							@else
								<center><input onchange="return update( '{{ $q['MODULE_CODE'] }}', '{{ $z['PARAMETER_NAME'] }}' )" type="checkbox"></center>
							@endif
						</td>
					@endforeach
				</tr>
			@endforeach
		</tbody>
	</table>
	
@endsection

@section( 'scripts' )
	<script type="text/javascript">
		function update( MODULE_CODE, PARAMETER_NAME ) {
			$("#contents").waitMe( {
				effect : 'win8',
				text : 'Memproses...',
				bg : '#ffffff',
				color : '#3d3d3d'
			} );
			$.ajax( {
				url: '{{ url( "modules/user-authorization" ) }}',
				type: 'POST',
				headers: {
					'X-CSRF-TOKEN': $( 'meta[name="csrf-token"]' ).attr( 'content' )
				},
				data: {
					MODULE_CODE: MODULE_CODE,
					PARAMETER_NAME: PARAMETER_NAME
				},
				success: function( response ) {
					window.setTimeout( function() {
						$.get( '{{ url( "modules/setup-menu" ) }}/' + PARAMETER_NAME, function( jsondata ) {
							if ( jsondata.status == false ) {
								alert( "error" );
							}
						}, "JSON" )
						.fail( function() {
							alert( "error MODULES" );
							alert( '{{ url( "modules/setup-menu" ) }}/' + PARAMETER_NAME );
						} );

						if( response.status == true ) {
							console.log( 'Success' );
						}
						else {
							console.log( 'Error Response' );
						}
						$("#contents").waitMe( 'hide' );
					}, 500 );
				},
				error: function() {
					alert( 'Error AJAX' );
					$("#contents").waitMe( 'hide' );
				}
			} );
		}

		var base_url = "{{ url( '' ) }}";
		var datatable = {
			init: function() {
				var e;
				e = $(".m-datatable").mDatatable({
					data: {
						saveState: {
							cookie: !1
						}
					},
					search: {
						input: $( "#generalSearch" )
					},
					columns: [
					{
						field: "Module Code",
						width: 125,
						template: function(e, a, i) {
							return '<span style="font-weight:bold;font-family: \'Courier New\';">' + e['Module Code'] + '</span>'
						}
					}, {
						field: "Module Name",
						width: 300
					}, {
						field: "Actions",
						width: 120,
						title: "Actions",
						sortable: !1,
						overflow: "visible",
						/*
						template: function(e, a, i) {
							return '\t\t\t\t\t\t<div class="dropdown ' + (i.getPageSize() - a <= 4 ? "dropup" : "") + '">\t\t\t\t\t\t\t<a href="#" class="btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown"><i class="la la-ellipsis-h"></i></a>\t\t\t\t\t\t  \t<div class="dropdown-menu dropdown-menu-right">\t\t\t\t\t\t    \t<a class="dropdown-item" href="' + base_url + '/modules/user-authorization/' + e['Auth Code'] + '"><i class="fa fa-lock"></i> User Authorization' + '</a>\t\t\t\t\t\t <a class="dropdown-item" href="' + base_url + '/modules/user-authorization/' + e['Auth Code'] + '"><i class="fa fa-lock"></i> User Authorization' + '</a>\t\t\t\t\t\t    \t</div>\t\t\t\t\t\t</div>\t\t\t\t\t\t<a href="#" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t\t\t\t\t'
						}
						*/
						template: function(e, a, i) {
							return '\t\t\t\t\t\t<a href="' + base_url + '/user/edit/' + e['Auth Code'] + '" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t\t\t\t\t'
						}
					}],

				}), $("#m_form_status").on("change", function() {
					e.search($(this).val().toLowerCase(), "Status")
				}), $("#m_form_type").on("change", function() {
					e.search($(this).val().toLowerCase(), "Type")
				}), $("#m_form_status, c#m_form_type").selectpicker()
			}
		};

		jQuery(document).ready(function() {
			datatable.init()
		});
	</script>
@endsection