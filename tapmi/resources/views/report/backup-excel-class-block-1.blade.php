<table>
	<tr>
		<td><b>Periode : {{ $periode }}</b></td>
	</tr>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Estate</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Afd</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Block Code</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Block Name</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Kelas Block Bulan ini</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Total Nilai / Jumlah</b></td>
	</tr>
	@if ( !empty( $data ) )
		@foreach ( $data as $dt )
			@if ( !empty( $dt['DATA'] ) && isset( $dt['DATA'] ) )
				@foreach ( $dt['DATA'] as $subdt_afd )
					@if ( !empty( $subdt_afd['DATA'] ) && isset( $subdt_afd['DATA'] ) )
						@foreach ( $subdt_afd['DATA'] as $subdt_block )
							<tr>
								<td style="text-align:center;">{{ $subdt_block['BA_NAME'] }}</td>
								<td style="text-align:center;">{{ $subdt_block['AFD_CODE'] }}</td>
								<td style="text-align:center;">{{ $subdt_block['BLOCK_CODE'] }}</td>
								<td style="text-align:center;">{{ $subdt_block['BLOCK_NAME'] }}</td>
								<td style="text-align:center;">{{ $subdt_block['NILAI_HURUF_INSPEKSI'] }}</td>
								<td style="text-align:center;">{{ $subdt_block['RATA2_INSPEKSI'] }}</td>
							</tr>
						@endforeach
					@endif
					<tr>
						<td style="text-align:center;background-color: #7fb0ff;">{{ $subdt_afd['BA_NAME'] }}</td>
						<td style="text-align:center;background-color: #7fb0ff;">{{ $subdt_afd['AFD_CODE'] }}</td>
						<td style="text-align:center;background-color: #000;"></td>
						<td style="text-align:center;background-color: #000;"></td>
						<td style="text-align:center;background-color: #7fb0ff;">{{ $subdt_afd['NILAI_INSPEKSI_HURUF'] }}</td>
						<td style="text-align:center;background-color: #7fb0ff;">{{ $subdt_afd['NILAI_INSPEKSI'].' / '.$dt['COUNT'] }}</td>

					</tr>
				@endforeach
			@endif
			<tr>
				<td style="text-align:center;background-color: #4f81d1;">{{ $dt['BA_NAME'] }}</td>
				<td style="text-align:center;background-color: #000;"></td>
				<td style="text-align:center;background-color: #000;"></td>
				<td style="text-align:center;background-color: #000;"></td>
				<td style="text-align:center;background-color: #4f81d1;">{{ $dt['NILAI_INSPEKSI_HURUF'] }}</td>
				<td style="text-align:center;background-color: #4f81d1;">{{ $dt['NILAI_INSPEKSI'].' / '.$dt['COUNT'] }}</td>

			</tr>
		@endforeach
	@endif
	
</table>