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
		<th style="background-color: #ff9933;" colspan="20">Sampling EBCC</th>
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
			<tr>
				<td>{{ $dt['val_date_time'] }}</td>
				<td>{{ $dt['val_nik_validator'] }}</td>
				<td>{{ $dt['val_nama_validator'] }}</td>
				<td>{{ $dt['val_jabatan_validator'] }}</td>
				<td>{{ $dt['val_werks'] }}</td>
				<td>{{ $dt['val_est_name'] }}</td>
				<td>{{ $dt['val_afd_code'] }}</td>
				<td>{{ $dt['val_block_code'] }}</td>
				<td>{{ $dt['val_block_name'] }}</td>
				<td>{{ $dt['val_tph_code'] }}</td>
				<td>{{ $dt['val_ebcc_code'] }}</td>
				<td></td>
				<td>{{ $dt['val_jml_bm'] }}</td>
				<td>{{ $dt['val_jml_bk'] }}</td>
				<td>{{ $dt['val_jml_ms'] }}</td>
				<td>{{ $dt['val_jml_or'] }}</td>
				<td>{{ $dt['val_jml_bb'] }}</td>
				<td>{{ $dt['val_jml_jk'] }}</td>
				<td>{{ $dt['val_jml_ba'] }}</td>
				<td>{{ $dt['val_jjg_panen'] }}</td>

				<!-- EBCC -->
				<td>{{ $dt['ebcc_nik_kerani_buah'] }}</td>
				<td>{{ $dt['ebcc_nama_kerani_buah'] }}</td>
				<td>{{ $dt['ebcc_no_bcc'] }}</td>
				<td></td>
				<td>{{ $dt['ebcc_jml_bm'] }}</td>
				<td>{{ $dt['ebcc_jml_bk'] }}</td>
				<td>{{ $dt['ebcc_jml_ms'] }}</td>
				<td>{{ $dt['ebcc_jml_or'] }}</td>
				<td>{{ $dt['ebcc_jml_bb'] }}</td>
				<td>{{ $dt['ebcc_jml_jk'] }}</td>
				<td>{{ $dt['ebcc_jml_ba'] }}</td>
				<td>{{ $dt['ebcc_jjg_panen'] }}</td>

				<!-- Etc -->
				<td></td>
				<td style="color:#fff;background-color:@if( $dt['match_status']=='MATCH' ) #27ae60 @else #e74c3c @endif;">{{ $dt['match_status'] }}</td>
				<td>{{ $dt['akurasi_kualitas_ms'] }}</td>

			</tr>
		@endforeach
	@endif
</table>

