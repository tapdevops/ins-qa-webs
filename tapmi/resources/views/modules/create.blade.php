@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Modules' )

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
			<a href="{{ 'modules/create' }}" class="m-nav__link">
				<span class="m-nav__link-text">
					Tambah
				</span>
			</a>
		</li>
	</ul>
@endsection

@section( 'content' )
	<form id="form" method="post" action="{{ url( '/modules/create' ) }}" class="m-form m-form--fit m-form--label-align-right m-form--group-seperator-dashed">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="m-portlet__body">
			<!--div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Parent Module:</label>
					<select class="form-control m-select2" id="m_select2_4" name="PARENT_MODULE" data-placeholder="...">
						<option></option>
						<option value="01.00.00.00.00">01.00.00.00.00 - TEST</option>
						<option value="02.00.00.00.00">02.00.00.00.00 - ABCD</option>
					</select>
				</div>
			</div-->

			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Module Code:</label>
					<input type="text" class="form-control m-input" name="MODULE_CODE" autocomplete="off" placeholder="...">
					<span class="m-form__help">
						Contoh: 02.00.00.00.00
					</span>
				</div>
				<div class="col-lg-6">
					<label class="">Module Name:</label>
					<input type="text" class="form-control m-input" name="MODULE_NAME" autocomplete="off" placeholder="...">
					<span class="m-form__help">
						Masukkan nama modul
					</span>
				</div>
			</div>

			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Item Name:</label>
					<input type="text" class="form-control m-input" name="ITEM_NAME" autocomplete="off" placeholder="...">
					<span class="m-form__help">
						Contoh: first/second/etc
					</span>
				</div>
				<div class="col-lg-6">
					<label class="">Icon:</label>
					<input type="text" class="form-control m-input" name="ICON" autocomplete="off" placeholder="...">
					<span class="m-form__help">
						<a href="javascript:;">Klik disini</a> untuk referensi icon.
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

				//form.waitMe( {
				//	effect : 'win8',
				//	text : 'Memproses...',
				//	bg : '#ffffff',
				//	color : '#3d3d3d'
				//} );

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
							//window.setTimeout( function() {
							//	window.location.replace( '{{ url( "/modules" ) }}' );
							//}, 1000 );
						}
						else {
							toastr.warning( response.message , "Info");
							//window.setTimeout( function() {
							//	form.waitMe( 'hide' );
							//}, 1000 );
						}
						
					},
					error: function() {
						toastr.error( result.message , "Info");
						//window.setTimeout( function() {
						//	form.waitMe( 'hide' );
						//}, 1000 );
					}
				} );

				ev.preventDefault();

			} );

		} );
	</script>
@endsection