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
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>AFD</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b># Kemandoran</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b># Sampling Aslap</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b># Sampling Kabun</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Exception</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Exception Reason</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>cetak LHM</b></td>
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
					@isset($dt['mandor'])
						{{ $dt['mandor'] }}
					@endisset
				</td>
				<td>
					@isset($dt['aslap'])
						{{ $dt['aslap'] }}
					@endisset
				</td>
				<td>
					@isset($dt['kabun'])
						{{ $dt['kabun'] }}
					@endisset
				</td>
				<td>
					@isset($dt['em_exception'])
						{{ $dt['em_exception'] }}
					@endisset
				</td>
				<td>
					@isset($dt['alasan'])
						{{ $dt['alasan'] }}
					@endisset
				</td>
				<td>
					@isset($dt['cetak'])
						{{ $dt['cetak'] }}
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