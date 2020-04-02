<style>
tr > td, tr > th {
    border: 1px solid #000000;
}
tr > th {
    text-align: center;
}
</style>
<table>
	<tr class="mythead">
		<th style="background-color: #ff9933;" colspan="21">Sampling EBCC</th>
		<th style="background-color: #33cc33;" colspan="12">Sampling EBCC</th>

		<!-- Etc -->
		<th rowspan="2">Lihat Foto</th>
		<th rowspan="2">Akurasi Sampling EBCC</th>
		<th rowspan="2">Akurasi Kualitas MS</th>
	</tr>
	<tr>
		<!-- EBCC Validation -->
		<th style="background-color: #ff9933;">Tanggal</th>
		<th style="background-color: #ff9933;">NIK Pembuat</th>
		<th style="background-color: #ff9933;">Nama Pembuat</th>
		<th style="background-color: #ff9933;">Jabatan Pembuat</th>
		<th style="background-color: #ff9933;">Kode BA</th>
		<th style="background-color: #ff9933;">Business Area</th>
		<th style="background-color: #ff9933;">Kode Afd</th>
		<th style="background-color: #ff9933;">Kode Block</th>
		<th style="background-color: #ff9933;">Block Deskripsi</th>
		<th style="background-color: #ff9933;">Tph</th>
		<th style="background-color: #ff9933;">Kode Sampling EBCC</th>
		<th style="background-color: #ff9933;">Status QR Code Tph</th>
		<th style="background-color: #ff9933;">BM (jjg)</th>
		<th style="background-color: #ff9933;">BK (jjg)</th>
		<th style="background-color: #ff9933;">MS (jjg)</th>
		<th style="background-color: #ff9933;">OR (jjg)</th>
		<th style="background-color: #ff9933;">BB (jjg)</th>
		<th style="background-color: #ff9933;">JK (jjg)</th>
		<th style="background-color: #ff9933;">BA (jjg)</th>
		<th style="background-color: #ff9933;">BRD (jjg)</th>
		<th style="background-color: #ff9933;">Total Janjang Panen</th>

		<!-- EBCC -->
		<th style="background-color: #33cc33;">NIK Kerani Buah</th>
		<th style="background-color: #33cc33;">Nama Kerani Buah</th>
		<th style="background-color: #33cc33;">No BCC</th>
		<th style="background-color: #33cc33;">Status QR Code TPH</th>
		<th style="background-color: #33cc33;">BM (jjg)</th>
		<th style="background-color: #33cc33;">BK (jjg)</th>
		<th style="background-color: #33cc33;">MS (jjg)</th>
		<th style="background-color: #33cc33;">OR (jjg)</th>
		<th style="background-color: #33cc33;">BB (jjg)</th>
		<th style="background-color: #33cc33;">JK (jjg)</th>
		<th style="background-color: #33cc33;">BA (jjg)</th>
		<th style="background-color: #33cc33;">Total Janjang Panen</th>
	</tr>
	@if ( !empty( $data ) )
		@foreach ( $data as $dt )
			@php
				$dtt = (array) $dt; 
			@endphp
			<tr>
				<td>{{ $dtt['sampling_tgl'] }}</td>
				<td>{{ $dtt['sampling_nik_pelaku'] }}</td>
				<td>{{ $dtt['sampling_nama_pelaku'] }}</td>
				<td>{{ $dtt['sampling_role_pelaku'] }}</td>
				<td>{{ $dtt['sampling_ba_code'] }}</td>
				<td>{{ $dtt['sampling_ba_name'] }}</td>
				<td>{{ $dtt['sampling_afd_code'] }}</td>
				<td>{{ $dtt['sampling_block_code'] }}</td>
				<td>{{ $dtt['sampling_block_name'] }}</td>
				<td>{{ $dtt['sampling_no_tph'] }}</td>
				<td>{{ $dtt['sampling_code'] }}</td>
				<td>{{ $dtt['sampling_status_qrcode_tph'] }}</td>
				<td>{{ $dtt['sampling_jml_bm'] }}</td>
				<td>{{ $dtt['sampling_jml_bk'] }}</td>
				<td>{{ $dtt['sampling_jml_ms'] }}</td>
				<td>{{ $dtt['sampling_jml_or'] }}</td>
				<td>{{ $dtt['sampling_jml_bb'] }}</td>
				<td>{{ $dtt['sampling_jml_jk'] }}</td>
				<td>{{ $dtt['sampling_jml_ba'] }}</td>
				<td>{{ $dtt['sampling_jml_brd'] }}</td>
				<td>{{ $dtt['sampling_total_jjg'] }}</td>

				<!-- EBCC -->
				<td>{{ $dtt['ebcc_nik_pelaku'] }}</td>
				<td>{{ $dtt['ebcc_nama_pelaku'] }}</td>
				<td>{{ $dtt['ebcc_code'] }}</td>
				<td>{{ $dtt['ebcc_status_qrcode_tph'] }}</td>
				<td>{{ $dtt['ebcc_jml_bm'] }}</td>
				<td>{{ $dtt['ebcc_jml_bk'] }}</td>
				<td>{{ $dtt['ebcc_jml_ms'] }}</td>
				<td>{{ $dtt['ebcc_jml_or'] }}</td>
				<td>{{ $dtt['ebcc_jml_bb'] }}</td>
				<td>{{ $dtt['ebcc_jml_jk'] }}</td>
				<td>{{ $dtt['ebcc_jml_ba'] }}</td>
				<td>{{ $dtt['ebcc_total_jjg_panen'] }}</td>

				<!-- Etc -->
				<td>{{ $dtt['link_foto'] }}</td>
				<td style="color:#fff;background-color:@if( $dtt['akurasi_sampling']=='MATCH' ) #27ae60 @else #e74c3c @endif;">{{ $dtt['akurasi_sampling'] }}</td>
				<td>{{ $dtt['akurasi_ms'] }}</td>
			</tr>
		@endforeach
	@endif
</table>

