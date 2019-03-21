@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Report' )

@section( 'subheader' )
	<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
		<li class="m-nav__item">
			<a href="{{ url( 'report' ) }}" class="m-nav__link">
				<span class="m-nav__link-text">
					Report
				</span>
			</a>
		</li>
		<li class="m-nav__separator">
			-
		</li>
		<li class="m-nav__item">
			<a href="{{ url( 'report/download' ) }}" class="m-nav__link">
				<span class="m-nav__link-text">
					Download
				</span>
			</a>
		</li>
	</ul>
@endsection

@section( 'content' )
	<form id="form" method="post" action="{{ url( '/report/download' ) }}" class="m-form m-form--fit m-form--label-align-right m-form--group-seperator-dashed">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="m-portlet__body">

			<div class="form-group m-form__group row">
				<div class="col-lg-6">
					<label>Pilih Report <span class="text-danger">*</span></label>
					<select id="report-select" class="form-control m-select2 mi-select2" name="REPORT_TYPE" onchange="return reportGroup( this.value )" data-placeholder="...">
						<option value="FINDING" selected="selected">TEMUAN</option>
						<option value="INSPEKSI">INSPEKSI</option>
						<option value="CLASS_BLOCK_AFD_ESTATE">CLASS, BLOCK, AFD, &amp; ESTATE</option>
					</select>
				</div>
			</div>

			<div class="form-group m-form__group row" id="report-date-month">
				<div class="col-lg-6">
					<label>Pilih Bulan <span class="text-danger">*</span></label>
					<div class="input-group date">
						<input type="text" class="form-control m-input" name="DATE_MONTH" readonly="readonly" autocomplete="off"  placeholder="..." id="m_datepicker_2"/>
						<div class="input-group-append">
							<span class="input-group-text">
								<i class="la la-calendar-check-o"></i>
							</span>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group m-form__group row" id="report-date-full">
				<div class="col-lg-6">
					<label>Pilih Tanggal <span class="text-danger">*</span></label>
					<div class="input-daterange input-group" id="m_datepicker_5">
						<input type="text" class="form-control m-input" id="report-start-date" name="START_DATE" autocomplete="off" readonly="readonly" />
						<div class="input-group-append">
							<span class="input-group-text">
								<i class="la la-ellipsis-h"></i>
							</span>
						</div>
						<input type="text" class="form-control" id="report-end-date" name="END_DATE" autocomplete="off" readonly="readonly" />
					</div>
					<span class="m-form__help">
						Linked pickers for date range selection
					</span>
				</div>
			</div>

			<div class="form-group m-form__group row" id="report-hs-region">
				<div class="col-lg-6">
					<label>Pilih Region <span class="text-danger">*</span></label>
					<select class="form-control m-select2 mi-select2" onchange="return ajaxSelect( 'comp', this.value )" id="select-region" name="REGION_CODE" data-placeholder="...">
						@foreach ( $region_data['data'] as $region )
							<option value="{{ $region['REGION_CODE'] }}">{{ $region['REGION_CODE'].' - '.$region['REGION_NAME'] }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="form-group m-form__group row" id="report-hs-comp">
				<div class="col-lg-6">
					<label>Pilih Company <span class="text-danger">*</span></label>
					<select class="form-control m-select2 mi-select2" id="select-comp" onchange="return ajaxSelect( 'ba', this.value )" name="COMP_CODE">
					</select>
				</div>
			</div>

			<div class="form-group m-form__group row" id="report-hs-est">
				<div class="col-lg-6">
					<label>Pilih Estate <span class="text-danger">*</span></label>
					<select class="form-control m-select2 mi-select2" id="select-ba" onchange="return ajaxSelect( 'afd', this.value )" name="BA_CODE">
					</select>
				</div>
			</div>

			<div class="form-group m-form__group row" id="report-hs-afd">
				<div class="col-lg-6">
					<label>Pilih Afd <span class="text-danger">*</span></label>
					<select class="form-control m-select2 mi-select2" id="select-afd" onchange="return ajaxSelect( 'block', this.value )" name="AFD_CODE">
					</select>
				</div>
			</div>

			<div class="form-group m-form__group row" id="report-hs-block">
				<div class="col-lg-6">
					<label>Pilih Block <span class="text-danger">*</span></label>
					<select class="form-control m-select2 mi-select2" id="select-block" name="BLOCK_CODE">
					</select>
				</div>
			</div>
		</div>
		<div class="m-portlet__foot m-portlet__no-border m-portlet__foot--fit">
			<div class="m-form__actions m-form__actions--solid">
				<div class="row">
					<div class="col-lg-6">
						
					</div>
					<div class="col-lg-6 m--align-right">
						<a id="generate-report" href="javascript:;" class="btn btn-warning"><i class="fa fa-refresh"></i> Generate Report</a>
						<a id="submit-report" href="javascript:;" class="btn btn-primary"><i class="fa fa-file-excel-o"></i> Download Excel</a>
						<a href="{{ url( '/report/' ) }}" class="btn btn-secondary">Cancel</a>
					</div>
				</div>
			</div>
		</div>
	</form>
@endsection

@section( 'scripts' )
	<script src="{{ url( 'assets/default-template/assets/custom/components/forms/widgets/bootstrap-daterangepicker.js' ) }}" type="text/javascript"></script>
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
					processResults: function( e, t ) {
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

		function ajaxSelect( type, value ) {
			var url = '';
			var target = '';

			if ( type == 'comp' ) {
				target = "#select-comp";
				url = "{{ url('report/search-comp?q=') }}";
				ajaxSelect( 'ba', $( "#select-comp" ).val() );
				window.setTimeout( function() {
					ajaxSelect( 'afd', $( "#select-ba" ).val() );
					window.setTimeout( function() {
						ajaxSelect( 'block', $( "#select-afd" ).val() );
					}, 400 );
				}, 400 );
				
			}
			else if ( type == 'ba' ) {
				target = "#select-ba";
				url = "{{ url('report/search-est?q=') }}";
				ajaxSelect( 'afd', $( "#select-ba" ).val() );
				window.setTimeout( function() {
					ajaxSelect( 'block', $( "#select-afd" ).val() );
				}, 400 );
			}
			else if ( type == 'afd' ) {
				target = "#select-afd";
				url = "{{ url('report/search-afd?q=') }}";
				ajaxSelect( 'block', $( "#select-afd" ).val() );
			}
			else if ( type == 'block' ) {
				target = "#select-block";
				url = "{{ url('report/search-block?q=') }}";
			}

			$.get( url + value, function( jsondata ) {
				console.log( "JSONDATA :" );
				console.log(jsondata)
				$( target ).html( '' );
				$( target ).append( '<option value="">Semua Data</option>' );

				for ( i = 0; i <= jsondata.total_count; i++ ) {
					var each = jsondata.items[i];
					if ( each ) {
						$( target ).append( '<option value="' + each.id + '">' + each.id + ' - ' + each.text + '</option>' );
					}
				}
			}, "JSON" )
			.fail( function() {
				alert( "error" );
			} );
		}

		function reportGroup( value ) {

			switch( value ) {
				case 'TEMUAN':
					$( "#report-date-month" ).hide();
					$( "#report-date-full" ).show();
				break;
				case 'INSPEKSI':
					$( "#report-date-month" ).hide();
					$( "#report-date-full" ).show();
				break;
				case 'CLASS_BLOCK_AFD_ESTATE':
					$( "#report-date-month" ).show();
					$( "#report-date-full" ).hide();
				break;
			}
		}

		$( document ).ready( function() {

			$( "#report-date-month" ).hide();
			$( "#report-date-full" ).hide();
			$(".mi-select2").select2({
				placeholder: "...",
				allowClear: !0
			});
			$( "#m_datepicker_5" ).datepicker( {
				todayHighlight: !0,
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				},
				maxDate: "+1m +1w",
				format: 'dd-mm-yyyy'
			} );

			$("#m_datepicker_2").datepicker({
				todayHighlight: !0,
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				},
				autoclose: true,
				minViewMode: 1,
				format: 'yyyy-mm'
			})

			$( '#form' ).waitMe( {
				effect : 'win8',
				text : '',
				bg : '#ffffff',
				color : '#3d3d3d'
			} );

			// Set Comp
			window.setTimeout( function() {
				ajaxSelect( 'comp', $( "#select-region" ).val() );
			}, 500 );

			reportGroup( 'TEMUAN' );

			window.setTimeout( function() {
				$( "#form" ).waitMe( 'hide' );
				
			}, 1500 );

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

			$( "#submit-report" ).click( function() {

				var next = false;
				if ( $( "#report-select" ).val() != 'CLASS_BLOCK_AFD_ESTATE' ) {
					if ( $( "#report-start-date" ).val() != '' && $( "#report-end-date" ).val() != '' ) {
						next = true;
					}
					else {
						toastr.error( "Periksa kembali inputan tanggal anda.", "Validasi Gagal!");
					}
					
				}
				else if ( $( "#report-select" ).val() == 'CLASS_BLOCK_AFD_ESTATE' ) {
					if ( $( "#select-ba" ).val().length > 0 && $( "#select-afd" ).val() == 0 && $( "#select-block" ).val() == 0 ) {
						next = true;
					}
					else {
						toastr.error( "Report Class Block hanya bisa diambil dari BA Code.", "Validasi Gagal!");
					}
				}
				
				if ( next == true ) {
					form.waitMe( {
						effect : 'win8',
						text : 'Memproses...',
						bg : '#ffffff',
						color : '#3d3d3d'
					} );
					toastr.success( "Mendownload report..." , "Info");
					window.setTimeout( function() {
						form.waitMe( 'hide' );
					}, 1000 );
					form.submit();
				}
			} );
			
			$( "#generate-report" ).click( function() {
				form.attr( "action", "{{ url( '/report/generate' ) }}" );
				form.submit();
				/*
				$.ajax({
					type: "POST",
					url: "{{ url( '/report/generate' ) }}",
					data: "_token={{ csrf_token() }}&REPORT_TYPE=" + $( "#report-select" ).val() + "&DATE_MONTH=" + $( "#m_datepicker_2" ).val() + "&START_DATE=" + $( "#report-start-date" ).val() + "&END_DATE=" + $( "#report-end-date" ).val() + "&REGION_CODE=" + $( "#select-region" ).val() + "&COMP_CODE=" + $( "#select-comp" ).val() + "&BA_CODE=" + $( "#select-ba" ).val() + "&AFD_CODE=" + $( "#select-afd" ).val() + "&BLOCK_CODE=" + $( "#select-block" ).val(),
					success: function( results ) {
					    alert(results); // show response from the php script.
					},
					error: function() {
						alert( 'Error Cuy' );
					}
				});*/

				//return false;
			} );

		} );
	</script>
@endsection