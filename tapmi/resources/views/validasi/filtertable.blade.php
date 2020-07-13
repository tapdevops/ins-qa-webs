<div id="table">
<div class="row">
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-4"></div>
			<div class="col-md-4"></div>
				@if(!empty($records) && $status == 1)
			<div class="col-md-4 m--align-right" style="white-space:nowrap;">
				<div id="cekaslap" class="btn btn-danger m-btn m-btn--custom m-btn--icon m-btn--air m-btn--pill">
					<span>
						<i class="fa fa-refresh"></i>
						<span>Cek Validasi Aslap</span>
					</span>
				</div>
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
	<!-- <thead bgcolor="#f0f0f0"> -->
		<tr>
			<th>Tanggal</th>
			<th>Krani Buah</th>
			<th>Afdeling</th>
			<th>Mandor Panen</th>
			<th>BCC berhasil Divalidasi Aslap</th>
			<th>Jumlah BCC yang Divalidasi</th>
			<th>Keterangan</th>
		</tr>
	</thead>
	<tbody>
		@foreach ( $data_header as $key => $q )
			<tr>
				<td>{{ $q['tanggal_rencana'] }}</td>
				<td>{{ $q['nama_krani_buah'] }} - {{$q['nik_kerani_buah']}}</td>
				<td>{{ $q['id_afd'] }}</td>
				<td>{{ $q['nama_mandor'] }} - {{$q['nik_mandor']}}</td>
				<td>0</td>
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
	</tbody>
</table>
</div>
<script>
	$("#cekaslap").click(function(){
		cekaslap();
	});
	function cekaslap(){
		var search = document.getElementById('generalSearch').value;
		var datatable = {
							init: function() {
								var e;
								e = $(".m-datatable").mDatatable({
									data: {
										saveState: {
											cookie: !1
										},
										
										autoColumns: true
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
											width: '90px',
											sortable: false,
										},{
											field: "Mandor Panen",
											width: 300,
											sortable: false,
										}, {
											field: "BCC berhasil Divalidasi Aslap",
											width: 100,
											sortable: false,
										}, {
											field: "Jumlah BCC yang Divalidasi",
											width: 100,
											sortable: false,
										}, {
											field: "Keterangan",
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