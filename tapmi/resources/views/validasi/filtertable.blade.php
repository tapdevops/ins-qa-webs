<div id="table">
<div class="row">
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-4"></div>
			<div class="col-md-4"></div>
			@if(!empty($records) && $status == 1)
			<div class="col-md-4 m--align-right" style="white-space:nowrap;margin-bottom:20px;">
				<div id="cekaslap" class="btn btn-danger m-btn m-btn--custom m-btn--icon m-btn--air m-btn--pill">
					<span>
						<i class="fa fa-refresh"></i>
						<span>Cek Validasi</span>
					</span>
				</div>
				@if($status_validasi_aslap==1)
				<a href="{{ URL::to('/validasi/create/'.$tgl_validasi) }}" class="btn btn-focus m-btn m-btn--custom m-btn--icon m-btn--air m-btn--pill">
					<span>
						<i class="fa fa-clipboard"></i>
						<span>Validasi</span>
					</span>
				</a>
				@else
				<div class="btn btn-dark m-btn m-btn--custom m-btn--icon m-btn--air m-btn--pil disabled" style="border-radius: 60px;">
					<span>
						<i class="fa fa-clipboard"></i>
						<span>Validasi</span>
					</span>
				</div>
				@endif
				<div class="m-separator m-separator--dashed d-xl-none"></div>
			</div>
			@endif
			@if($status_validasi_aslap==0 && !empty($records))
			<div class="col-md-12 m--align-center" style="white-space:nowrap;">
				<h5 class="m-subheader__title m-subheader__title--separator text-danger">Anda harus melakukan "Cek Validasi Aslap" terlebih dulu</h5>
			</div>
			@endif
		</div>
	</div>
	
</div>
<table class="m-datatable" id="html_table" width="100%">
	<thead>
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
				<td>{{ $q['aslap_validation'] }}</td>
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
	$(document).on('click','#cekaslap',function(){
		$('#cekaslap>span>i').addClass('fa-spin');
		$('#cekaslap').addClass('disabled');
		$('#cekaslap>span>span').html('Proses pengecekan');
		cekaslap();
	});
	function cekaslap(){
		const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
		var search = document.getElementById('generalSearch').value;
		$.ajax({
			url:"{{ URL::to('/validasi/cek_aslap/') }}",
			type:'get',
			data:{
				CSRF_TOKEN,
				'tanggal' : search
			},
			success:function(data){
				// console.log(data);
				$("#tampilkan").trigger('click');
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
				toastr.success( 'Pengecekan validasi Aslap selesai' , "Sukses");
			}
		})
	}
</script>