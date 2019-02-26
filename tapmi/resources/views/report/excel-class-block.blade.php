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
	@if ( !empty( $inspeksi_block ) )
		@foreach ( $inspeksi_block as $dt )
			<tr>
				<td style="text-align:center;">{{ $dt['EST_NAME'] }}</td>
				<td style="text-align:center;">{{ $dt['AFD_CODE'] }}</td>
				<td style="text-align:center;">{{ $dt['BLOCK_CODE'] }}</td>
				<td style="text-align:center;">{{ $dt['BLOCK_NAME'] }}</td>
				<td style="text-align:center;">{{ $dt['HASIL_INSPEKSI']['GRADE'] }}</td>
				<td style="text-align:center;">{{ $dt['NILAI_INSPEKSI'] }}</td>
			</tr>
		@endforeach
	@endif
</table>