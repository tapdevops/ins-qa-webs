<!DOCTYPE html>
<html>
<table>
	<tr>
		<th colspan="10">MONITORING VALIDASI DESKTOP</th>
	</tr>
	<tr>
		<td colspan="10">REGION : {{ $region }}</td>
	</tr>
	<tr>
		<td colspan="10">COMPANY : {{ $company }}</td>
	</tr>
	<tr>
		<td colspan="10">DATE : {{ $date }}</td>
	</tr>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Tgl</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>PT</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>BA</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>AFD</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>PIC</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Jabatan</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Mulai validasi</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Selesai validasi</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Durasi [menit]</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b># BCC tersedia</b></td>
	</tr>
	@if( !empty( $data ) )
		@foreach( $data as $key=>$dt )
			<tr>
				<td>
					@isset($dt['tanggal'])
						{{ $dt['tanggal'] }}
					@endisset
				</td>
				<td>
					@isset($dt['comp'])
						{{ $dt['comp'] }}
					@endisset
				</td>
				<td>
					@isset($dt['ba'])
						{{ $dt['ba'] }}
					@endisset
				</td>
				<td>
					@isset($dt['afd'])
						{{ $dt['afd'] }}
					@endisset
				</td>
				<td>
					@isset($dt['pic'])
						{{ $dt['pic'] }}
					@endisset
				</td>
				<td>
					@isset($dt['job'])
						{{ $dt['job'] }}
					@endisset
				</td>
				<td>
					@isset($dt['mulai'])
						{{ $dt['mulai'] }}
					@endisset
				</td>
				<td>
					@isset($dt['selesai'])
						{{ $dt['selesai'] }}
					@endisset
				</td>
				<td>
					@isset($dt['durasi'])
						{{ $dt['durasi'] }}
					@endisset
				</td>
				<td>
					@isset($dt['bcc'])
						{{ $dt['bcc'] }}
					@endisset
				</td>
			</tr>
		@endforeach
	@else	
		<tr>
			<td colspan="10">Data Not Found</td>
		</tr>
	@endif
</table>
</html>