<!DOCTYPE html>
<html>
<table>
	<tr>
		<th colspan="10">REGISTRASI TPH</th>
	</tr>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>QRCODE TPH</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>WERKS</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>AFD</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>BLOCK CODE</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>NO TPH</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>LAT</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>LONG</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>INSERT BY</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>INSERT TIME</b></td>
	</tr>
	@if( !empty( $data ) )
		@foreach( $data as $key=>$dt )
			<tr>
				<td>
					@isset($dt['QRCODE_TPH'])
						{{ $dt['QRCODE_TPH'] }}
					@endisset
				</td>
				<td>
					@isset($dt['WERKS'])
						{{ $dt['WERKS'] }}
					@endisset
				</td>
				<td>
					@isset($dt['AFD_CODE'])
						{{ $dt['AFD_CODE'] }}
					@endisset
				</td>
				<td>
					@isset($dt['BLOCK_CODE'])
						{{ $dt['BLOCK_CODE'] }}
					@endisset
				</td>
				<td>
					@isset($dt['NO_TPH'])
						{{ $dt['NO_TPH'] }}
					@endisset
				</td>
				<td>
					@isset($dt['LAT'])
						{{ $dt['LAT'] }}
					@endisset
				</td>
				<td>
					@isset($dt['LONG'])
						{{ $dt['LONG'] }}
					@endisset
				</td>
				<td>
					@isset($dt['INSERT_USER'])
						{{ $dt['INSERT_USER'] }}
					@endisset
				</td>
				<td>
					@isset($dt['INSERT_TIME'])
						{{ $dt['INSERT_TIME'] }}
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