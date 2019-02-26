@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Master User' )

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
			<a href="{{ url( 'user/create' ) }}" class="m-nav__link">
				<span class="m-nav__link-text">
					Tambah
				</span>
			</a>
		</li>
	</ul>
@endsection

@section( 'content' )
	<form id="form" method="post" action="{{ url( '/user/create' ) }}" class="m-form m-form--fit m-form--label-align-right m-form--group-seperator-dashed">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="m-portlet__body">
			
			<div class="form-group m-form__group row">

				<div class="col-lg-6">
					<label>NIK <span class="text-danger">*</span></label>
					<select class="form-control m-select2" id="select-user" name="EMPLOYEE_NIK">
						<option value="">...</option>
					</select>
				</div>
				<div class="col-lg-6">
					<label>Email / Username <span class="text-danger">*</span></label>
					<input type="text" class="form-control m-input" id="input-username" name="USERNAME" autocomplete="off" placeholder="...">
					<span class="m-form__help">
						Gunakan email/username yang telah terdaftar di LDAP.
					</span>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Role <span class="text-danger">*</span></label>
					<select class="form-control m-select2" id="m_select2_4" name="ROLES" data-placeholder="...">
						@foreach ( $parameter as $q )
							<option value="{{ $q['PARAMETER_NAME'] }}">{{ $q['DESC'] }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Refference Role <span class="text-danger">*</span></label>
					<select class="form-control m-select2 mi-select2" name="REFFERENCE_ROLE" data-placeholder="...">
						<option value="NATIONAL">NATIONAL</option>
						<option value="REGION_CODE">REGION_CODE</option>
						<option value="COMP_CODE">COMP_CODE</option>
						<option value="BA_CODE">BA_CODE</option>
						<option value="AFD_CODE">AFD_CODE</option>
					</select>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Location <span class="text-danger">*</span></label>
					<input type="text" class="form-control m-input" name="LOCATION" autocomplete="off" placeholder="...">
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

			//$( "#input-username" ).hide();
			$( ".mi-select2").select2( {
				placeholder: "...",
				allowClear: !0
			} );

			$( "#select-user" ).select2( {
				//data: data

				placeholder: "Cari NIK",
				allowClear: !0,
				ajax: {
				url: "{{ url('user/search-user') }}",
				
				dataType: "json",
				delay: 250,
				data: function(e) {
					return {
						q: e.term,
						page: e.page
					}
				},
				processResults: function(e, t) {
					return t.page = t.page || 1, {
						results: e.items,
						pagination: {
							more: 30 * t.page < e.total_count
						}
					}
				},
				cache: !0
			},
			escapeMarkup: function(e) {
			    return e
			},
			minimumInputLength: 1,
			templateResult: function(e) {
				if (e.loading) return e.text;
				var t = "<div class='select2-result-repository clearfix'><div class='select2-result-repository__meta'><div class='select2-result-repository__title'>" + e.id + "</div>";
				return e.description && (
					t += "<div class='select2-result-repository__description'><b>" + e.text + "</b></div>"), 
					t += "<div class='select2-result-repository__statistics'><div class='select2-result-repository__stargazers'>" + e.description + "</div></div></div></div>"
			},
			templateSelection: function(e) {
				return e.text
			}

			} );

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

				var select_user_val = $( "#select-user" ).val().split( '-' );
				var execute_form = true;
				if ( select_user_val[0] == 'SAP' ) {
					if ( $( "#input-username" ).val() == '' ) {
						toastr.error( "Input username kosong. Isi dengan data username yang sudah terdaftar di LDAP." , "Info");
						execute_form = false;
					}
				}


				//form.waitMe( {
				//	effect : 'win8',
				//	text : 'Memproses...',
				//	bg : '#ffffff',
				//	color : '#3d3d3d'
				//} );
				
				if ( execute_form == true ) {
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
				}

				ev.preventDefault();

			} );

		} );
	</script>
@endsection