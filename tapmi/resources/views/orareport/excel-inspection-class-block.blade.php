<table>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Estate</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Afd</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Kode Blok</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nama Blok</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Kelas Blok Bulan ini</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Total Nilai / Jumlah</b></td>
		<td colspan="6" style="text-align:center;color: #FFF; background-color: #043077;"><b>Kelas Blok 6 Bulan Sebelumnya</b></td>
	</tr>
	<tr>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<td style="background-color: #043077;"></td>
		<?php
			$date_now = date( "Ymd", strtotime( $date_month ) );
			for ( $i = 1; $i <= 6; $i++ ) {
				print '<td style="text-align:center;"><b>'.date( 'M Y', strtotime( $date_now." - ".$i." month" ) ).'</b></td>';
			}
		?>
	</tr>
	@if( !empty( $data ) )
		@foreach( $data as $dt )
			@php
				$nilai = number_format( $dt['total nilai/jumlah'], 2, '.', '' );
			@endphp
			<tr style="background-color: {{ $dt['kode blok'] == '' ? '#dbdbdb': '#e1ffde' }};">
				<td>{{ $dt['estate'] }}</td>
				<td>{{ $dt['afd'] }}</td>
				<td>{{ $dt['kode blok'] }}</td>
				<td>{{ $dt['nama blok'] }}</td>
				<td>{{ $dt['kelas blok bulan ini'] }}</td>
				<td>{{ $nilai }}</td>
				<td>{{ $dt['ke 1'] }}</td>
				<td>{{ $dt['ke 2'] }}</td>
				<td>{{ $dt['ke 3'] }}</td>
				<td>{{ $dt['ke 4'] }}</td>
				<td>{{ $dt['ke 5'] }}</td>
				<td>{{ $dt['ke 6'] }}</td>
			</tr>
		@endforeach
	@endif
</table>