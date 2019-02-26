@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Report' )

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
					<label>Pilih Region <span class="text-danger">*</span></label>
					<select class="form-control m-select2 mi-select2" name="REFFERENCE_ROLE" data-placeholder="...">
						@foreach ( $region_data['data'] as $region )
							<option value="{{ $region['REGION_CODE'] }}">{{ $region['REGION_CODE'].' - '.$region['REGION_NAME'] }}</option>
						@endforeach
					</select>
				</div>
				<!--
				<div class="col-lg-4">
					<label>Region <span class="text-danger">*</span></label>
					<select class="form-control m-select2" id="select-region" name="EMPLOYEE_NIK">
						<option value="">...</option>
					</select>
				</div>
		 		-->
			</div>

			<div class="form-group m-form__group row">
				
				<div class="col-lg-4">
					<label>Company <span class="text-danger">*</span></label>
					<select class="form-control m-select2" id="select-comp" name="EMPLOYEE_NIK">
						<option value="">...</option>
					</select>
				</div>
			</div>

			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Pilih Report <span class="text-danger">*</span></label>
					<select class="form-control m-select2 mi-select2" name="REFFERENCE_ROLE" data-placeholder="...">
						<option value="TEMUAN">TEMUAN</option>
						<option value="INSPEKSI">INSPEKSI</option>

						<option value="CLASS_BLOCK_AFD_ESTATE">CLASS, BLOCK, AFD, &amp; ESTATE</option>
					</select>
				</div>
			</div>

			<!--div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Location <span class="text-danger">*</span></label>
					<input type="text" class="form-control m-input" name="LOCATION" autocomplete="off" placeholder="...">
					<span class="m-form__help">
						Contoh: 4121A / 2121,4121 / ALL
					</span>
				</div>
			</div-->

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

		function select2ajax( id, url, placeholder ) {
			$( id ).select2( {
				placeholder: placeholder,
				allowClear: !0,
				ajax: {
					url: url,
				
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
					var t = "<div class='select2-result-repository clearfix'><div class='select2-result-repository__meta'><div class='select2-result-repository__title'><b>" + e.id + "</b></</div>";
					return e.description && (
						t += "<div class='select2-result-repository__description'>" + e.text + "</div>"
					)
				},
				templateSelection: function(e) {
					return e.text
				}

			} );
		}

		$( document ).ready( function() {

			$(".mi-select2").select2({
				placeholder: "...",
				allowClear: !0
			})

			select2ajax( "#select-comp", "{{ url('report/search-comp') }}", "Pilih Company" );

			var form = $( "#form" );

		} );
	</script>
@endsection