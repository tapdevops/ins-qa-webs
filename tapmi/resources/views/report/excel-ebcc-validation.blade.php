<table>
	<tr>
		<th style="text-align: left;back">Kode EBCC Validation</th>
		<th style="text-align: center;">Kode BA</th>
		<th style="text-align: left;">Business Area</th>
		<th style="text-align: center;">AFD</th>
		<th style="text-align: center;">Kode Block</th>
		<th style="text-align: left;">Block Deskripsi</th>
		<th style="text-align: center;">TPH</th>
		<th style="text-align: left;">Inputan TPH</th>
		<th style="text-align: left;">Alasan Input Manual</th>
		<th style="text-align: center;">Tanggal Validasi</th>
		<th style="text-align: left;">NIK Validator</th>
		<th style="text-align: left;">Nama Validator</th>
		<th style="text-align: left;">Jabatan Validator</th>
		<th style="text-align: left;">Maturity Status</th>
		<th style="text-align: left;">Periode</th>
		<th style="text-align: left;">Lat</th>
		<th style="text-align: left;">Long</th>

		<!-- Kualitas: { UOM: "JJG", GROUP_KUALITAS: "HASIL PANEN" } -->
		@foreach ( $kualitas_jjg_hasilpanen as $jjg_hasilpanen )
			<th style="text-align: left;">{{ $jjg_hasilpanen['NAMA_KUALITAS'] }}</th>
		@endforeach
		<th style="text-align: center;">Total Janjang Panen</th>

		<!-- Kualitas: { UOM: "TPH", GROUP_KUALITAS: "PENALTY DI TPH" } -->
		@foreach ( $kualitas_penalty_tph as $penalty_tph )
			<th style="text-align: left;">{{ $penalty_tph['NAMA_KUALITAS'] }}</th>
		@endforeach

		<!-- Kualitas: { UOM: "JJG", GROUP_KUALITAS: "KONDISI BUAH" } -->
		@foreach ( $kualitas_jjg_kondisibuah as $jjg_kondisibuah )
			<th style="text-align: left;">{{ $jjg_kondisibuah['NAMA_KUALITAS'] }}</th>
		@endforeach
	</tr>
	@if ( count( $data ) > 0 )
		@foreach ( $data as $dt )
			<tr>
				<td style="text-align: left;">{{ $dt['EBCC_VALIDATION_CODE'] }}</td>
				<td style="text-align: center;">{{ $dt['WERKS'] }}</td>
				<td style="text-align: left;">{{ $dt['EST_NAME'] }}</td>
				<td style="text-align: center;">{{ $dt['AFD_CODE'] }}</td>
				<td style="text-align: center;">{{ $dt['BLOCK_CODE'] }}</td>
				<td style="text-align: left;">{{ $dt['BLOCK_NAME'] }}</td>
				<td style="text-align: center;">{{ $dt['NO_TPH'] }}</td>
				<td style="text-align: left;">{{ $dt['STATUS_TPH_SCAN'] }}</td>
				<td style="text-align: left;">{{ ( $dt['ALASAN_MANUAL'] == null ? '' : ( $dt['ALASAN_MANUAL'] == '1' ? 'QR Code TPH Rusak' : 'QR Code TPH Hilang' ) ) }}</td>
				<td style="text-align: center;">{{ $dt['TANGGAL_VALIDASI'] }}</td>
				<td style="text-align: left;">{{ $dt['NIK_VALIDATOR'] }}</td>
				<td style="text-align: left;">{{ $dt['NAMA_VALIDATOR'] }}</td>
				<td style="text-align: left;">{{ $dt['JABATAN_VALIDATOR'] }}</td>
				<td style="text-align: left;">{{ $dt['MATURITY_STATUS'] }}</td>
				<td style="text-align: left;">{{ $periode }}</td>
				<td style="text-align: left;">{{ $dt['LAT_TPH'] }}</td>
				<td style="text-align: left;">{{ $dt['LON_TPH'] }}</td>

				<!-- Kualitas: { UOM: "JJG", GROUP_KUALITAS: "HASIL PANEN" } -->
				@foreach ( $dt['HASIL_JJG_HASILPANEN'] as $hasil_jjg_hasilpanen )
					<td style="text-align: center;">{{ $hasil_jjg_hasilpanen }}</td>
				@endforeach
				<td style="text-align: center;">{{ array_sum( $dt['HASIL_JJG_HASILPANEN'] ) }}</td>

				<!-- Kualitas: { UOM: "TPH", GROUP_KUALITAS: "PENALTY DI TPH" } -->
				@foreach ( $dt['PENALTY_DI_TPH'] as $hasil_penalty_tph )
					<td style="text-align: center;">{{ ( $hasil_penalty_tph == 1 ? "ADA" : "TIDAK ADA" ) }}</td>
				@endforeach

				<!-- Kualitas: { UOM: "JJG", GROUP_KUALITAS: "KONDISI BUAH" } -->
				@foreach ( $dt['HASIL_JJG_KONDISIBUAH'] as $hasil_jjg_kondisibuah )
					<td style="text-align: center;">{{ $hasil_jjg_kondisibuah }}</td>
				@endforeach

			</tr>
		@endforeach
	@endif
</table>