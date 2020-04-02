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
		<a href="{{ url( 'report-oracle/kafka-control00' ) }}" class="m-nav__link">
			<span class="m-nav__link-text">
				Kafka Control
			</span>
		</a>
	</li>
</ul>
@endsection

@section( 'content' )
<form id="form" method="post" action="{{ url( '/report-oracle/download' ) }}" class="m-form m-form--fit m-form--label-align-right m-form--group-seperator-dashed">
	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	<div class="m-portlet__body">


		<div class="form-group m-form__group row">
			<div class="col-lg-6">
				<label>Pilih Bulan <span class="text-danger">*</span></label>
				<div class="input-group date">
					<input type="text" class="form-control m-input" name="DATE_MONTH" readonly="readonly" autocomplete="off" placeholder="..." id="m_datepicker_2" />
					<div class="input-group-append">
						<span class="input-group-text">
							<i class="la la-calendar-check-o"></i>
						</span>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<label><span class="text-danger">*</span></label>
				<div class="input-group">
					<a id="generate-report" href="javascript:;" class="btn btn-warning"><i class="fa fa-refresh"></i> Check Data</a>
				</div>
			</div>
		</div>

		<div class="form-group m-form__group row">
			<div class="col-lg-12">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>MSA</th>
							<th>Table Name</th>
							<th>Oracle</th>
							<th>MongoDB</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>EBCCVAL</td>
							<td>TR_EBCC_VALIDATION_H</td>
							<td id="VALUE-EBCCVAL-TR_EBCC_VALIDATION_H-MongoDB">0</td>
							<td id="VALUE-EBCCVAL-TR_EBCC_VALIDATION_H-Oracle">0</td>
						</tr>
						<tr>
							<td>EBCCVAL</td>
							<td>TR_EBCC_VALIDATION_D</td>
							<td id="VALUE-EBCCVAL-TR_EBCC_VALIDATION_D-MongoDB">0</td>
							<td id="VALUE-EBCCVAL-TR_EBCC_VALIDATION_D-Oracle">0</td>
						</tr>
						<tr>
							<td>FINDING</td>
							<td>TR_FINDING</td>
							<td id="VALUE-FINDING-TR_FINDING-MongoDB">0</td>
							<td id="VALUE-FINDING-TR_FINDING-Oracle">0</td>
						</tr>
						<tr>
							<td>INSPECTION</td>
							<td>TR_BLOCK_INSPECTION_H</td>
							<td id="VALUE-FINDING-TR_FINDING-MongoDB">0</td>
							<td id="VALUE-FINDING-TR_FINDING-Oracle">0</td>
						</tr>
						<tr>
							<td>INSPECTION</td>
							<td>TR_BLOCK_INSPECTION_D</td>
							<td id="VALUE-FINDING-TR_FINDING-MongoDB">0</td>
							<td id="VALUE-FINDING-TR_FINDING-Oracle">0</td>
						</tr>
						<tr>
							<td>INSPECTION</td>
							<td>TR_INSPECTION_GENBA</td>
							<td id="VALUE-FINDING-TR_FINDING-MongoDB">0</td>
							<td id="VALUE-FINDING-TR_FINDING-Oracle">0</td>
						</tr>
						<tr>
							<td>INSPECTION</td>
							<td>TR_TRACK_INSPECTION</td>
							<td id="VALUE-FINDING-TR_FINDING-MongoDB">0</td>
							<td id="VALUE-FINDING-TR_FINDING-Oracle">0</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</form>
@endsection

@section( 'scripts' )
<script src="{{ url( 'assets/default-template/assets/custom/components/forms/widgets/bootstrap-daterangepicker.js' ) }}" type="text/javascript"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$( "#m_datepicker_2" ).datepicker( {
			todayHighlight: !0,
			orientation: "bottom left",
			templates: {
				leftArrow: '<i class="la la-angle-left"></i>',
				rightArrow: '<i class="la la-angle-right"></i>'
			},
			autoclose: true,
			minViewMode: 1,
			format: 'yyyy-mm',
			endDate: "-1M"
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
		// toastr.error("Field jenis report harus dipilih.", "Validasi Gagal!");
	});

	MobileInspection.set_active_menu( '{{ $active_menu }}' );
</script>
@endsection