@extends( 'layouts.default.page-normal-main' )
@section('style')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
  <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>  
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.js"></script>
 
@endsection
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
					<label for="TANGGAL_RENCANA">Tanggal &nbsp; &nbsp; </label>
					<input type="text" class="form-control m-input" id="m_datepicker_5" name="tanggal" autocomplete="off" readonly="readonly" />
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
	
</table>
@endsection

@section( 'scripts' )
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
  
<script src="{{ url( 'assets/default-template/assets/custom/components/forms/widgets/bootstrap-daterangepicker.js' ) }}" type="text/javascript"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script> -->
<script type="text/javascript">
	
	var base_url = "{{ url( '' ) }}";
	// var datatable = $(".m-datatable").mDatatable({
	// 	data: {
	// 				saveState: {
	// 					cookie: !1
	// 				}
	// 			},
	// 			search: {
	// 				input: $( "#tgl_rencana" )
	// 			},


	// 			columns: [
	// 			{
	// 				field: "Krani Buah",
	// 				width: 300
	// 			}, {
	// 				field: "Afdeling",
	// 				width: 120
	// 			},{
	// 				field: "Mandor Panen",
	// 				width: 300
	// 			}, {
	// 				field: "Jumlah Divalidasi",
	// 				width: 100
	// 			}
	// 		]
	// });
	// var datatable = {
	// 	init: function() {
	// 		var e;
	// 		e = $(".m-datatable").mDatatable({
	// 			data: {
	// 				saveState: {
	// 					cookie: !1
	// 				}
	// 			},
	// 			search: {
	// 				input: $( "#tgl_rencana" )
	// 			},


	// 			columns: [
	// 			{
	// 				field: "Krani Buah",
	// 				width: 300
	// 			}, {
	// 				field: "Afdeling",
	// 				width: 120
	// 			},{
	// 				field: "Mandor Panen",
	// 				width: 300
	// 			}, {
	// 				field: "Jumlah Divalidasi",
	// 				width: 100
	// 			}
	// 		]

	// 		})
	// 		// , $("#m_form_status").on("change", function() {
	// 		// 	e.search($(this).val().toLowerCase(), "Status")
	// 		// }), $("#m_form_type").on("change", function() {
	// 		// 	e.search($(this).val().toLowerCase(), "Type")
	// 		// }), $("#m_form_status, c#m_form_type").selectpicker()
	// 	}
	// };

	// jQuery(document).ready(function() {
	// 	datatable.init()
	// 	MobileInspection.set_active_menu( '{{ $active_menu }}' );
	// });

	$(document).ready(function(){
		$("#m_datepicker_5").datepicker({
			todayHighlight: !0,
			templates: {
				leftArrow: '<i class="la la-angle-left"></i>',
				rightArrow: '<i class="la la-angle-right"></i>'
			},
			endDate: "-1d",
			format: 'dd-M-yy',
			autoclose : true
			
		});

		 load_data();
		 MobileInspection.set_active_menu( '{{ $active_menu }}' );

		function load_data(date)
		{
			var datatable = $('#html_table').mDatatable({
				processing: true,
				serverSide: true,
				ajax: {
					url:'{{ route("listval.index") }}',
					data:{date:date}
				},
				
				columns: [
					{
					data:'id_ba',
					name:'id_ba'
					},
					{
					data:'nama_krani_buah',
					name:'nama_krani_buah'
					},
					{
					data:'id_afd',
					name:'id_afd'
					},
					{
					data:'nama_mandor',
					name:'nama_mandor'
					},
					{
						data:'jumlah_ebcc_validated',
						"render":function(data, type, row) {
								var jml_target = data.jumlah_ebcc_validated+'/'+data.target_validasi;
								return jml_target;
						}
					},
					{
						data	:  'id_validasi',
						"render": function(data, type, row) 
						{
							var str = data.id_validasi; 
							var res = str.replace(/\//g, '.')+'-'+data.id_ba+'-'+data.id_afd;
							var content = '<a href="{{ url("validasi/create") }}/'+res+'"><button class="btn btn-sm btn-primary ">Validasi</button></a>';
						
							return content;
						}
					}
				]
				});
		}
 
 $('#m_datepicker_5').datepicker().on('change', function(){
		var selected = $(this).val();
		var date_val = selected.toUpperCase();
		if(date_val != '')
		{
			$('#html_table').mDatatable().destroy();
			load_data(date_val);
		}
});
	});
	
</script>
@endsection