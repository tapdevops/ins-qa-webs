<!DOCTYPE html>
<html>
<table>
	<tr>
		<th colspan="11">PENILAIAN INSPEKSI LAPANGAN</th>
	</tr>
	<tr>
		<td colspan="11">BISNIS AREA : {{ $ba_name }}</td>
	</tr>
	<tr>
		<td colspan="11"></td>
	</tr>
	<tr>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>No</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Nama</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Role</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Jumlah Afdeling</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Hari Libur</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Target Inspeksi (per minggu)</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Aktual Inspeksi (jumlah blok)</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Jumlah Genba</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Tanggal Genba</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Total Aktual Inspeksi</b></td>
		<td style="text-align:center;color: #FFF; background-color: #043077;"><b>Achievement Inspeksi</b></td>
	</tr>
	@if( !empty( $data ) )
		@foreach( $data as $key=>$dt )
			<tr>
				<td>{{ $key+1 }}</td>
				<td>
					@isset($dt['employee_name'])
						{{ $dt['employee_name'] }}
					@endisset
				</td>
				<td>
					@isset($dt['user_role'])
						{{ $dt['user_role'] }}
					@endisset
				</td>
				<td>
					@isset($dt['jlh_afd'])
						{{ $dt['jlh_afd'] }}
					@endisset
				</td>
				<td>
					@isset($dt['libur'])
						{{ $dt['libur'] }}
					@endisset
				</td>
				<td>
					@isset($dt['target'])
						{{ $dt['target'] }}
					@endisset
				</td>
				<td>
					@isset($dt['jlh_inspeksi'])
						{{ $dt['jlh_inspeksi'] }}
					@endisset
				</td>
				<td>
					@isset($dt['jlh_genba'])
						{{ $dt['jlh_genba'] }}
					@endisset
				</td>
				<td>
					@isset($dt['tgl_genba'])
						{{ $dt['tgl_genba'] }}
					@endisset
				</td>
				<td>
					@isset($dt['total_actual'])
						{{ $dt['total_actual'] }}
					@endisset
				</td>
				<td>
					@isset($dt['achievement'])
						{{ $dt['achievement'] }}
					@endisset
				</td>
			</tr>
		@endforeach
	@else	
		<tr>
			<td colspan="11">Data Not Found</td>
		</tr>
	@endif
</table>
</html>