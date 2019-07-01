@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Edit User' )

@section( 'subheader' )
	<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
		<li class="m-nav__item">
			<a href="{{ url( '/user' ) }}" class="m-nav__link">
				<span class="m-nav__link-text">
					Master User
				</span>
			</a>
		</li>
		<li class="m-nav__separator">
			-
		</li>
		<li class="m-nav__item">
			<a href="javascript:;" class="m-nav__link">
				<span class="m-nav__link-text">
					Edit
				</span>
			</a>
		</li>
	</ul>
@endsection

@section( 'content' )
	<form id="form" method="post" action="{{ url( '/user/edit/'.$id ) }}" class="m-form m-form--fit m-form--label-align-right m-form--group-seperator-dashed">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<input type="hidden" name="USER_AUTH_CODE" value="{{ $user['USER_AUTH_CODE'] }}">
		<input type="hidden" name="EMPLOYEE_NIK" value="{{ $user['EMPLOYEE_NIK'] }}">
		<div class="m-portlet__body">
			
			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>NIK</label>
					<input type="text" class="form-control" value="{{ $user['EMPLOYEE_NIK'] }}" readonly="readonly">
				</div>
			</div>
			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>NAMA LENGKAP</label>
					<input type="text" class="form-control" value="{{ $user['FULLNAME'] }}" readonly="readonly">
				</div>
			</div>
			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>JOB</label>
					<input type="text" class="form-control" value="{{ $user['JOB'] }}" readonly="readonly">
				</div>
			</div>
			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Role <span class="text-danger">*</span></label>
					<select class="form-control m-select2" id="m_select2_4" name="USER_ROLE" data-placeholder="...">
						@foreach ( $parameter as $q )
							@if ( $user['USER_ROLE'] == $q['PARAMETER_NAME'] )
								<option value="{{ $q['PARAMETER_NAME'] }}" selected>{{ $q['DESC'] }}</option>
							@else
								<option value="{{ $q['PARAMETER_NAME'] }}">{{ $q['DESC'] }}</option>
							@endif
							
						@endforeach
					</select>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Refference Role <span class="text-danger">*</span></label>
					<select class="form-control m-select2 mi-select2" name="REFFERENCE_ROLE" data-placeholder="...">
						@foreach ( $refrole as $q )
							@if ( $user['REF_ROLE'] == $q['ID'] )
								<option value="{{ $q['ID'] }}" selected>{{ $q['TEXT'] }}</option>
							@else
								<option value="{{ $q['ID'] }}">{{ $q['TEXT'] }}</option>
							@endif
							
						@endforeach
					</select>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Location <span class="text-danger">*</span></label>
					<input type="text" class="form-control m-input" name="LOCATION_CODE" value="{{ $user['LOCATION_CODE'] }}" autocomplete="off" placeholder="...">
					<span class="m-form__help">
						Contoh: 4121A / 2121,4121 / ALL
					</span>
				</div>
			</div>

		</div>
		<div class="m-portlet__foot m-portlet__no-border m-portlet__foot--fit">
			<div class="m-form__actions m-form__actions--solid">
				<div class="row">
					<div class="col-lg-6">
						
					</div>
					<div class="col-lg-6 m--align-right">
						<button type="submit" class="btn btn-primary">Save</button>
						<a href="{{ url( '/modules/' ) }}" class="btn btn-secondary">Cancel</a>
					</div>
				</div>
			</div>
		</div>
	</form>
@endsection

@section( 'scripts' )
	<script type="text/javascript">
		$( document ).ready( function() {

			$(".mi-select2").select2({
				placeholder: "...",
				allowClear: !0
			})

			toastr.options = {
				"closeButton": false,
				"debug": false,
				"newestOnTop": false,
				"progressBar": false,
				"positionClass": "toast-top-right",
				"preventDuplicates": false,
				"onclick": null,
				"showDuration": "300",
				"hideDuration": "1000",
				"timeOut": "5000",
				"extendedTimeOut": "1000",
				"showEasing": "swing",
				"hideEasing": "linear",
				"showMethod": "fadeIn",
				"hideMethod": "fadeOut"
			};

			var form = $( "#form" );

			form.submit( function( ev ) {

				form.waitMe( {
					effect : 'win8',
					text : 'Memproses...',
					bg : '#ffffff',
					color : '#3d3d3d'
				} );

				$.ajax( {
					url: form.attr( 'action' ),
					type: form.attr( 'method' ),
					headers: {
						'X-CSRF-TOKEN': $( 'meta[name="csrf-token"]' ).attr( 'content' )
					},
					data: form.serialize(),
					success: function( response ) {
						if( response.status == true ) {
							toastr.success( response.message , "Info");
							window.setTimeout( function() {
								window.location.replace( '{{ url( "/user" ) }}' );
							}, 1000 );
						}
						else {
							toastr.warning( response.message , "Info");
							window.setTimeout( function() {
								form.waitMe( 'hide' );
							}, 1000 );
						}
						
					},
					error: function() {
						toastr.error( result.message , "Info");
						window.setTimeout( function() {
							form.waitMe( 'hide' );
						}, 1000 );
					}
				} );

				ev.preventDefault();

			} );

		} );
	</script>
@endsection