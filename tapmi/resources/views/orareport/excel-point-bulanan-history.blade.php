<table>
	<tr>
		<th colspan="7">Laporan History Point</th>
	</tr>
	<tr>
		<td colspan="7">Periode : {{ $date_month }}</td>
	</tr>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nik</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nama</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Role</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Tanggal</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Kode BA</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Business Area</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Points</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Tipe</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Keterangan</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Referensi</b></td>
	</tr>
	@if( !empty( $data_history ) )
		@foreach( $data_history as $key=>$dt )
			<tr>
				<td>{{ $dt['NIK'] }}</td>
				<td>
					@isset($dt['FULLNAME'])
						{{ $dt['FULLNAME'] }}
					@endisset
				</td>
				<td>{{ $dt['JOB'] }}</td>
				<td>{{ $dt['DATE'] }}</td>
				<td align="left">{{ $dt['BA_CODE'] }}</td>
				<td>{{ $dt['BUSINESS_AREA'] }}</td>
				<td align="left">{{ $dt['POINT'] }}</td>
				<td>
					@isset($dt['TYPE'])
						{{ $dt['TYPE'] }}
					@endisset
				</td>
				<td>{{ $dt['REMARKS'] }}</td>
				<td>
					@isset($dt['REFERENCE'])
						{{ $dt['REFERENCE'] }}
					@endisset
				</td>
			</tr>
		@endforeach
	@else	
		<tr>
			<td colspan="10">else</td>
		</tr>
	@endif
</table>