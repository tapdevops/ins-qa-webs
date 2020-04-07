@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Validasi BCC oleh Kepala Kebun' )

@section( 'subheader' )
@endsection

@section( 'content' )
<div class="row">
	<div class="col-md-8">
		<div class="row">
			<div class="col-md-4">
				{{-- <div class="m-input-icon m-input-icon--left">
					<input type="date" class="form-control m-input m-input--solid" max="{{ $dmin }}" placeholder="Select date..." id="generalSearch">
				</div> --}}
				<div class="input-daterange input-group" id="m_datepicker_5">
					<label for="TGL_PANEN">Tanggal &nbsp; &nbsp; </label>
					<input type="text" class="form-control m-input" id="tgl_panel" name="TGL_PANEN" autocomplete="off" readonly="readonly" />
					<div class="input-group-append">
						<span class="input-group-text">
							<i class="la la-calendar"></i>
						</span>
					</div>
				</div>
			</div>
			<div class="col-md-4">
			</div>
			<div class="col-md-4"></div>
		</div>
	</div>
	
</div>

<table class="m-datatable" id="html_table" width="100%" style="margin-top:20px;">
	<thead>
		<tr>
			<th>Krani Buah</th>
			<th>Afdeling</th>
			<th>Mandor Panen</th>
			<th>Jumlah BCC yang Divalidasi</th>
			<th>Aksi</th>
		</tr>
	</thead>
	<tbody>
		
		@foreach ( $data_header as $key => $q )
			<tr>
				<td>{{ $q['nama_kerani_buah'] }} - {{$q['nik_kerani_buah'] }}</td>
				<td>{{ $q['id_afd'] }}</td>
				<td>{{ $q['nama_mandor'] }} - {{$q['nik_mandor']  }}</td>
				@foreach ( $validated as $k => $v )
					@if($v['no_bcc_mi'] == $q['no_bcc'])
					<?php  $valid = $v['jumlah_ebcc_validated'] ?>
					@else 
					<?php $valid = '0' ?>
					@endif
				@endforeach
				<td> {{ $valid }} / 3 </td>
				<td><button type="button" class="btn btn-primary btn-sm">Validasi</button></td>
			</tr>
		@endforeach
	</tbody>
</table>
@endsection

@section( 'scripts' )
<script src="{{ url( 'assets/default-template/assets/custom/components/forms/widgets/bootstrap-daterangepicker.js' ) }}" type="text/javascript"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript">
	
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
					input: $( "#tgl_panel" )
				},


				columns: [
				{
					field: "Krani Buah",
					width: 300
				}, {
					field: "Afdeling",
					width: 120
				},{
					field: "Mandor Panen",
					width: 300
				}, {
					field: "Jumlah BCC yang Divalidasi",
					width: 300
				}, {
					field: "Actions",
					width: 200,
					title: "Actions",
					sortable: !1,
					overflow: "visible",
					template: function(e, a, i) {
						return '\t\t\t\t\t\t<a href="' + base_url + '/validate/insert_detail/' + e['Auth Code'] + '" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="Validasi "><i class="la la-list"></i> Validasi</a>\t\t\t\t\t'
					}
				}],

			})
			, $("#m_form_status").on("change", function() {
				e.search($(this).val().toLowerCase(), "Status")
			}), $("#m_form_type").on("change", function() {
				e.search($(this).val().toLowerCase(), "Type")
			}), $("#m_form_status, c#m_form_type").selectpicker()
		}
	};

	

	$(document).ready(function() {

		$("#m_datepicker_5").datepicker({
			todayHighlight: !0,
			templates: {
				leftArrow: '<i class="la la-angle-left"></i>',
				rightArrow: '<i class="la la-angle-right"></i>'
			},
			endDate: "-1d",
			format: 'yyyy-M-dd'
		});
		
	});

	jQuery(document).ready(function() {
		datatable.init()
		MobileInspection.set_active_menu( '{{ $active_menu }}' );
	});
</script>
@endsection