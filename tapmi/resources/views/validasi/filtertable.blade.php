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
	<!-- <thead bgcolor="#f0f0f0"> -->
		<tr>
			<th>Tanggal</th>
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
				<td>{{ $q['tanggal_rencana'] }}</td>
				<td>{{ $q['nama_krani_buah'] }}</td>
				<td>{{ $q['id_afd'] }}</td>
				<td>{{ $q['nama_mandor'] }}</td>
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