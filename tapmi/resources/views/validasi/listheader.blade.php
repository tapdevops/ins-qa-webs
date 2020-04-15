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
				<div class="input-daterange input-group">
					<label for="tanggal_rencana">Tanggal &nbsp; &nbsp; </label>
					<input type="text" class="form-control m-input" id="generalSearch" name="tanggal_rencana" autocomplete="off" readonly="readonly" />
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
				<td>{{ $q['nama_krani_buah'] }}</td>
				<td>{{ $q['id_afd'] }}</td>
				<td>{{ $q['nama_mandor'] }}</td>
				<td>{{ $q['jumlah_ebcc_validated'] }} /{{ $q['target_validasi'] }}  </td>
				<?php 
				$id = str_replace("/",".",$q['id_validasi']);
				 ?>
				<td><a href={{ URL::to('/validasi/create/'.$id.'-'.$q['id_ba'].'-'.$q['id_afd']) }}><button type="button" class="btn btn-primary btn-sm">Validasi</button></a></td>
			</tr>
		@endforeach
	</tbody>
</table>
@endsection

@section( 'scripts' )
<script src="{{ url( 'assets/default-template/assets/custom/components/forms/widgets/bootstrap-daterangepicker.js' ) }}" type="text/javascript"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
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
					input: $( "#generalSearch" )
				},


				columns: [
				{
					field: "Krani Buah",
        			filterable: true,
					width: 300
				}, {
					field: "Afdeling",
					width: 120
				},{
					field: "Mandor Panen",
					width: 300
				}, {
					field: "Jumlah Divalidasi",
					width: 100
				}
			]

			})
		}
	};

	jQuery(document).ready(function() {
		datatable.init()
		MobileInspection.set_active_menu( '{{ $active_menu }}' );
	});

	$(document).ready(function () {
		$("#generalSearch").datepicker({
			todayHighlight: !0,
			templates: {
				leftArrow: '<i class="la la-angle-left"></i>',
				rightArrow: '<i class="la la-angle-right"></i>'
			},
			endDate: "-1d",
			format: 'yyyy-mm-dd	',
			
		});

		
		$('#generalSearch').datepicker().on('change', function(){
				var selected = $(this).val();
				var date_val = selected.toUpperCase();
				// load_data(date_val);
			});

			
		// function load_data(tanggal=''){

		// 	$.ajaxSetup({
		// 			headers: {
		// 				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		// 			}
		// 		});

		// 		$.ajax({
		// 			url: "{{URL::to('validasi/filter_date')}}/"+tanggal,
		// 				type: "POST",
		// 				data: tanggal,
		// 				dataType: "json",
		// 				success: function(response){
		// 					// Retrieve data
		// 					var data = response;

		// 					// Modify data
		// 					$.each(datatable.data, function(){
		// 						this[0] = 'John Smith';
		// 					});
		// 					dataTable = $(".m-datatable").mDatatable();

		// 					// Clear table
		// 					dataTable.fnClearTable();

		// 					// Add updated data
		// 					dataTable.fnAddData(datatable.data);

		// 					// Redraw table
		// 					dataTable.draw();
		// 				}				
		// 			});
		// }

	});

	
</script>
@endsection