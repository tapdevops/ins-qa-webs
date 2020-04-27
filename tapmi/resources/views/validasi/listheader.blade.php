@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Validasi BCC oleh Kepala Kebun' )

@section( 'subheader' )
@endsection

@section( 'content' )
<div class="row">
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-4">
				<div class="input-daterange input-group">
					<label for="tanggal_rencana">Tanggal &nbsp; &nbsp; </label>
					<?php $tgl = date("d-M-Y", strtotime($tgl_validasi));
					?>
					<input type="text" class="form-control m-input" id="generalSearch" name="tanggal_rencana" value="{{$tgl}}" autocomplete="off" readonly="readonly" />
					
					<div class="input-group-append">
						<span class="input-group-text">
							<i class="la la-calendar"></i>
						</span>
					</div>&nbsp;&nbsp;&nbsp;
					<button type="button" id="tampilkan" name="btsearch" class="btn btn-primary btn-sm btsearch">Tampilkan</button>
				</div>

			</div>
			<div class="col-md-4"></div>
			<div class="col-md-4"></div>
		</div>
	</div>
	
</div>
<div id="table">

<div class="row">
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-4"></div>
			<div class="col-md-4"></div>
			<div class="col-md-4 m--align-right">
			<!-- '.$id.'-'.$q['id_ba'].'-'.$q['id_afd']) ba afd dari session -->
			@if(!empty($records) && $status == 1)
				<a href="{{ URL::to('/validasi/create/'.$tgl_validasi) }}" class="btn btn-focus m-btn m-btn--custom m-btn--icon m-btn--air m-btn--pill">
					<span>
						<i class="fa fa-clipboard"></i>
						<span>Validasi</span>
					</span>
				</a>
			@endif
				<div class="m-separator m-separator--dashed d-xl-none"></div>
			</div>
		</div>
	</div>
	
</div>
<table class="m-datatable" id="html_table" width="100%" style="margin-top:20px;">
	<thead>
		<tr>
			<th>Tanggal</th>
			<th>Krani Buah</th>
			<th>Afdeling</th>
			<th>Mandor Panen</th>
			<th>Jumlah BCC yang Divalidasi</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
	@foreach(array_chunk($data_header, 10) as $row)
		@foreach ( $row as $key => $q )
			<tr>
				<td>{{ $q['tanggal_rencana'] }}</td>
				<td>{{ $q['nama_krani_buah'] }} - {{$q['nik_kerani_buah']}}</td>
				<td>{{ $q['id_afd'] }}</td>
				<td>{{ $q['nama_mandor'] }} - {{$q['nik_mandor']}}</td>
				<td>{{ $q['jumlah_ebcc_validated'] }} / {{ $q['target_validasi'] }}  </td>
				<?php 
					$id = str_replace("/",".",$q['id_validasi']);
				?>
				@if ($q['jumlah_ebcc_validated'] === $q['target_validasi'])
				<td><p class="text-success">Selesai divalidasi</p></td>
				@else
				<td><p class="text-danger">Belum divalidasi</p></td>
				@endif	
			</tr>
		@endforeach
		@endforeach
	</tbody>
</table>
</div>
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
					},
					
					autoColumns: false
				},
				search: {
					input: $( "#generalSearch" )
				},


				columns: [
					{
						field: "Tanggal",
						filterable: true,
						sortable: false,
						width: 0,
						visibility: false,
					},{
						field: "Krani Buah",
						filterable: true,
						sortable: false,
						width: 300
					}, {
						field: "Afdeling",
						width: 120,
						sortable: false,
					},{
						field: "Mandor Panen",
						width: 300,
						sortable: false,
					}, {
						field: "Jumlah BCC yang Divalidasi",
						width: 100,
						sortable: false,
					}, {
						field: "Aksi",
						width: 100,
						sortable: false,
					}
				]

			})
		}
	};

	jQuery(document).ready(function() {
		datatable.init()
		MobileInspection.set_active_menu( '{{ $active_menu }}' );
		var year = (new Date).getFullYear();

		$("#generalSearch").datepicker({
			todayHighlight: !0,
			templates: {
				leftArrow: '<i class="la la-angle-left"></i>',
				rightArrow: '<i class="la la-angle-right"></i>'
			},
			endDate: "-1d",
			format: 'dd-M-yyyy',
            orientation: 'bottom'
			
		});
		

		
	});
	
	// $(document).ready(function () {
	// 	$('#generalSearch').datepicker().on('click', function(){
	// 			var selected = $(this).val();
	// 			var date_val = selected.toUpperCase();
	// 			// datatable.init();
	// 	});
		

	// });
	

	$("#tampilkan").click(function(){
		var search = document.getElementById('generalSearch').value;
		// $(".m-datatable").mDatatable().reload();
		// $(".m-datatable").mDatatable().search(search, "Tanggal");
		
		// $(".m-datatable").mDatatable().destroy();
		refreshData();
	});

	function refreshData(){
		var search = document.getElementById('generalSearch').value;
		var datatable = {
							init: function() {
								var e;
								e = $(".m-datatable").mDatatable({
									data: {
										saveState: {
											cookie: !1
										},
										
										autoColumns: false
									},
									search: {
										input: $( "#generalSearch" )
									},


									columns: [
										{
											field: "Tanggal",
											filterable: true,
											sortable: false,
											width: 0,
											visibility: false,
										},{
											field: "Krani Buah",
											filterable: true,
											sortable: false,
											width: 300
										}, {
											field: "Afdeling",
											width: 120,
											sortable: false,
										},{
											field: "Mandor Panen",
											width: 300,
											sortable: false,
										}, {
											field: "Jumlah BCC yang Divalidasi",
											width: 100,
											sortable: false,
										}, {
											field: "Aksi",
											width: 100,
											sortable: false,
										}
									]

								})
							}
						};
		// event.preventDefault();
		const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
		$.ajax({
			url:'/getNewdata2',
			type:'get',
			data:{
				CSRF_TOKEN,
				'tanggal' : search
			},
			success:function(data){
				$("div#table").html(data);
				datatable.init();
				$("#generalSearch").val(search);
			}
		})
	}
	
</script>
@endsection