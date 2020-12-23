<!DOCTYPE html>
<html>
<table>
	<tr>
		<th colspan="10">MONITORING SYNC MI</th>
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
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>AFD Panen</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>PIC</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Jabatan</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b># Kemandoran</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Jam Upload H+0 - H+1 &lt; 1AM</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Jam Upload H+1 1AM - 2PM</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b># Sampling</b></td>
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
					@isset($dt['comp_name'])
						{{ $dt['comp_name'] }}
					@endisset
				</td>
				<td>
					@isset($dt['id_ba'])
						{{ $dt['id_ba'] }}
					@endisset
				</td>
				<td>
					@isset($dt['afd'])
						{{ $dt['afd'] }}
					@endisset
				</td>
				<td>
					@isset($dt['name'])
						{{ $dt['name'] }}
					@endisset
				</td>
				<td>
					@isset($dt['user_role'])
						{{ $dt['user_role'] }}
					@endisset
				</td>
				<td>
					@isset($dt['mandor'])
						{{ $dt['mandor'] }}
					@endisset
				</td>
				<td>
					@isset($dt['count1'])
						{{ $dt['count1'] }}
					@endisset
				</td>
				<td>
					@isset($dt['count2'])
						{{ $dt['count2'] }}
					@endisset
				</td>
				<td>
					@isset($dt['total'])
						{{ $dt['total'] }}
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