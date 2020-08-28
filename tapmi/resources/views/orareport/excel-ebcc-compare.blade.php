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
		<th style="background-color: #33cc33;" colspan="11">EBCC</th>

		<!-- Etc -->
		<th rowspan="2">Lihat Foto</th>
		<th rowspan="2">Akurasi Sampling EBCC</th>
		<th rowspan="2">Akurasi Kuantitas</th>
		<th rowspan="2">Akurasi Kualitas MS</th>
	</tr>
	<tr>
		<!-- EBCC Validation -->
		<th style="background-color: #ff9933;">Tanggal</th>
		<th style="background-color: #ff9933;">NIK Pembuat</th>
		<th style="background-color: #ff9933;">Nama Pembuat</th>
		<th style="background-color: #ff9933;">User Role</th>
		<th style="background-color: #ff9933;">Kode BA</th>
		<th style="background-color: #ff9933;">Business Area</th>
		<th style="background-color: #ff9933;">Kode Afd</th>
		<th style="background-color: #ff9933;">Kode Block</th>
		<th style="background-color: #ff9933;">Block Deskripsi</th>
		<th style="background-color: #ff9933;">Tph</th>
		<th style="background-color: #ff9933;">Status Tph</th>
		<th style="background-color: #ff9933;">Ticket</th>
		<th style="background-color: #ff9933;">Kode Sampling EBCC</th>
		<th style="background-color: #ff9933;">Status QR Code Tph</th>
		<th style="background-color: #ff9933;">BM (jjg)</th>
		<th style="background-color: #ff9933;">MS (jjg)</th>
		<th style="background-color: #ff9933;">OR (jjg)</th>
		<th style="background-color: #ff9933;">BB (jjg)</th>
		<th style="background-color: #ff9933;">JK (jjg)</th>
		<th style="background-color: #ff9933;">Total Janjang Panen</th>

		<!-- EBCC -->
		<th style="background-color: #33cc33;">Jumlah EBCC</th>
		<th style="background-color: #33cc33;">NIK Kerani Buah</th>
		<th style="background-color: #33cc33;">Nama Kerani Buah</th>
		<th style="background-color: #33cc33;">No BCC</th>
		<th style="background-color: #33cc33;">Status QR Code TPH</th>
		<th style="background-color: #33cc33;">BM (jjg)</th>
		<th style="background-color: #33cc33;">MS (jjg)</th>
		<th style="background-color: #33cc33;">OR (jjg)</th>
		<th style="background-color: #33cc33;">BB (jjg)</th>
		<th style="background-color: #33cc33;">JK (jjg)</th>
		<th style="background-color: #33cc33;">Total Janjang Panen</th>
	</tr>

	@if ( !empty( $data ) )
		@php
			$i = 0;
		@endphp
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
				<td>{{ $dt['status_tph'] }}</td>
				<td>{{ $dt['val_delivery_ticket'] }}</td>
				<td>{{ $dt['val_ebcc_code'] }}</td>
				<td>{{ $dt['val_status_tph_scan'].' '.$dt['val_alasan_manual'] }}</td>
				<td>{{ $dt['val_jml_bm'] }}</td>
				<td>{{ $dt['val_jml_ms'] }}</td>
				<td>{{ $dt['val_jml_or'] }}</td>
				<td>{{ $dt['val_jml_bb'] }}</td>
				<td>{{ $dt['val_jml_jk'] }}</td>
				<td>{{ $dt['val_jjg_panen'] }}</td>

				<!-- EBCC -->
				<td>{{ $dt['ebcc_count'] }}</td>
				<td>{{ $dt['ebcc_nik_kerani_buah'] }}</td>
				<td>{{ $dt['ebcc_nama_kerani_buah'] }}</td>
				<td>{{ $dt['ebcc_no_bcc'] }}</td>
				<td>{{ $dt['ebcc_status_tph'].' '.$dt['ebcc_keterangan_qrcode'] }}</td>
				<td>{{ $dt['ebcc_jml_bm'] }}</td>
				<td>{{ $dt['ebcc_jml_ms'] }}</td>
				<td>{{ $dt['ebcc_jml_or'] }}</td>
				<td>{{ $dt['ebcc_jml_bb'] }}</td>
				<td>{{ $dt['ebcc_jml_jk'] }}</td>
				<td>{{ $dt['ebcc_jjg_panen'] }}</td>

				<!-- Etc -->
				<td><a href="{{ $dt['link_foto'] }}">Link Foto</a></td>
				<td style="color:#fff;background-color:@if( $dt['akurasi_sampling_ebcc']=='MATCH' ) #27ae60 @else #e74c3c @endif;">{{ $dt['akurasi_sampling_ebcc'] }}</td>
				<td style="color:#fff;background-color:@if( $dt['akurasi_kuantitas']=='MATCH' ) #27ae60 @else #e74c3c @endif;">{{ $dt['akurasi_kuantitas'] }}</td>
				<td>{{ $dt['akurasi_kualitas_ms'] }}</td>

			</tr>

			<?php
			/*	$j = $i + 1;
				if ( isset( $data[$j] ) ) {
					$summary_code_a = $data[$i]['summary_code'];
					$summary_code_b = $data[$j]['summary_code'];
					if ( $summary_code_a != $summary_code_b ) {
						$match_percent = intval( ( $summary[$summary_code_a]['match'] * 100 ) / $summary[$summary_code_a]['jumlah_data'] );
						$match_akurasi = intval( $summary[$summary_code_a]['val_jml_ms'] ) - intval( $summary[$summary_code_a]['akurasi'] );
						$match_akurasi = ( $match_akurasi > 0 ? abs( $match_akurasi / $summary[$summary_code_a]['val_jml_ms'] ) : 0 ) * 100;
						$match_akurasi = number_format( $match_akurasi, 2, '.', '' );
						print '<tr>
							<td colspan="10" style="background-color:#6faaf2;text-align:center;color:white;">
								TOTAL : '.$summary[$summary_code_a]['nama'].' - TANGGAL : '.$summary[$summary_code_a]['tanggal'].'
							</td>
							<td colspan="2" style="background-color:#000;"></td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['val_jml_bm'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['val_jml_bk'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['val_jml_ms'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['val_jml_or'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['val_jml_bb'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['val_jml_jk'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['val_jml_ba'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['val_jjg_panen'].'</td>
							<td colspan="4" style="background-color:#000;"></td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['ebcc_jml_bm'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['ebcc_jml_bk'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['ebcc_jml_ms'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['ebcc_jml_or'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['ebcc_jml_bb'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['ebcc_jml_jk'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['ebcc_jml_ba'].'</td>
							<td style="background-color:#6faaf2;">'.$summary[$summary_code_a]['ebcc_jjg_panen'].'</td>
							<td style="background-color:#000;"></td>
							<td style="text-align:center;"background-color:#6faaf2;">'.$match_percent.'%</td>
							<td style="text-align:center;"background-color:#6faaf2;">'.( $summary[$summary_code_a]['match'] > 0 ? $match_akurasi.'%' :'' ).'</td>
						</tr>';
					}
					
				}
				else {
					$summary_code = $data[$i]['summary_code'];
					$match_percent = intval( ( $summary[$summary_code]['match'] * 100 ) / $summary[$summary_code]['jumlah_data'] );
					$match_akurasi = intval( $summary[$summary_code]['val_jml_ms'] ) - intval( $summary[$summary_code]['akurasi'] );
					$match_akurasi = ( $match_akurasi > 0 ? abs( $match_akurasi / $summary[$summary_code]['val_jml_ms'] ) : 0 ) * 100;
					$match_akurasi = number_format( $match_akurasi, 2, '.', '' );
					print '<tr>
						<td colspan="10" style="background-color:#6faaf2;text-align:center;color:white;">
							TOTAL : '.$summary[$summary_code]['nama'].' - TANGGAL : '.$summary[$summary_code]['tanggal'].'
						</td>
						<td colspan="2" style="background-color:#000;"></td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['val_jml_bm'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['val_jml_bk'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['val_jml_ms'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['val_jml_or'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['val_jml_bb'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['val_jml_jk'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['val_jml_ba'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['val_jjg_panen'].'</td>
						<td colspan="4" style="background-color:#000;"></td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['ebcc_jml_bm'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['ebcc_jml_bk'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['ebcc_jml_ms'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['ebcc_jml_or'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['ebcc_jml_bb'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['ebcc_jml_jk'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['ebcc_jml_ba'].'</td>
						<td style="background-color:#6faaf2;">'.$summary[$summary_code]['ebcc_jjg_panen'].'</td>
						<td style="background-color:#000;"></td>
						<td style="text-align:center;"background-color:#6faaf2;">'.$match_percent.'%</td>
						<td style="text-align:center;"background-color:#6faaf2;">'.( $summary[$summary_code]['match'] > 0 ? $match_akurasi.'%' :'' ).'</td>
					</tr>';
				}
				$i++;
			*/	
			?>
		@endforeach
	@endif


</table>

<table>
	<tr>
		<td colspan="2">Keterangan :</td>
	</tr>
	<tr><td>BM</td><td>a. Mentah</td></tr></tr>
	<tr><td>MS</td><td>b. Masak</td></tr>
	<tr><td>OR</td><td>c. Overripe/Terlalu Masak</td></tr>
	<tr><td>BB</td><td>d. Busuk</td></tr>
	<tr><td>JK</td><td>e. Janjang Kosong</td></tr>
</table>



