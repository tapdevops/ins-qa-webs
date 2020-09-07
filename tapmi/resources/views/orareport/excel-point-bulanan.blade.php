<table>
	<tr>
		<th colspan="7">Laporan Point Bulanan</th>
	</tr>
	<tr>
		<td colspan="7">Periode : {{ $date_month }}</td>
	</tr>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Periode</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>NIK</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nama Lengkap</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>User Role</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Kode BA</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Business Area</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Total Points</b></td>
	</tr>
	@if( !empty( $data ) )
		@foreach( $data as $key=>$dt )
			<tr>
				<td>{{ $dt['PERIODE'] }}</td>
				<td>{{ $dt['NIK'] }}</td>
				<td>{{ $dt['FULLNAME'] }}</td>
				<td>{{ $dt['JOB'] }}</td>
				<td align="center">{{ $dt['LOCATION_CODE'] }}</td>
				<td>{{ $dt['BUSINESS_AREA'] }}</td>
				<td align="left">{{ $dt['POINT'] }}</td>
			</tr>
		@endforeach
	@else	
		<tr>
			<td colspan="7"> </td>
		</tr>
	@endif
</table>