<!DOCTYPE html>
<html>
<table>
	<tr>
		<th colspan="10">VALIDASI EBCC COMPARE EHARVESTING</th>
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
		<td colspan="20"></td>
	</tr>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;" colspan="2">Hasil Panen</td>
		<td style="text-align:center;color: #FFF; background-color: #043077;" colspan="5">Kualitas Buah</td>
		<td style="text-align:center;color: #FFF; background-color: #043077;" colspan="8">Kondisi Buah</td>
	</tr>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Company</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Estate</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Afdeling</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Tanggal Panen</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nik Mandor</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nik Krani Buah</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nik Pemanen</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nama Pemanen</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nik Gandeng</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nama Gandeng</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Block</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Luas Panen</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>TPH</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Status TPH</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Radius TPH</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Jarak Scan TPH</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>No BCC</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Status BCC</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Status BCC Sync</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>BJR Block</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>TBS</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>BRD</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>BM</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>MS</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>BB</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>JK</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>OR</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>TP</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>BS</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>AB</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>MH</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>PB</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>BL</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>SF</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>BT</b></td>
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