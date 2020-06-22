<table>
	<tr>
		<th style="text-align: left;back">Kode EBCC Validation</th>
		<th style="text-align: left;back">Kode BA</th>
		<th style="text-align: left;back">Business Area</th>
		<th style="text-align: left;back">AFD</th>
		<th style="text-align: left;back">Kode Block</th>
		<th style="text-align: left;back">TPH</th>
		<th style="text-align: left;back">Inputan TPH</th>
		<th style="text-align: left;back">Alasan Input Manual</th>
		<th style="text-align: left;back">NIK Validator</th>
		<th style="text-align: left;back">Nama Validator</th>
		<th style="text-align: left;back">Jabatan Validator</th>
		<th style="text-align: left;back">Lat</th>
		<th style="text-align: left;back">Long</th>
		<th style="text-align: left;back">Delivery Code</th>
		<th style="text-align: left;back">Status Delivery Code</th>
		@foreach($head as $hd)
		<th style="text-align: left;back">{{ $hd->nama_kualitas }}</th>
		@endforeach
		
		
	</tr>
	@if ( count( $data ) > 0 )
	@foreach ( $data as $dt )
	@php
		$dt = (array) $dt;
		
	@endphp
	<tr>
		<td style="text-align: left;">{{ $dt['ebcc_validation_code'] }}</td>
		<td style="text-align: left;">{{ $dt['werks'] }}</td>
		<td style="text-align: left;">{{ $dt['est_name'] }}</td>
		<td style="text-align: left;">{{ $dt['afd_code'] }}</td>
		<td style="text-align: left;">{{ $dt['block_code'] }}</td>
		<td style="text-align: left;">{{ $dt['no_tph'] }}</td>
		<td style="text-align: left;">{{ $dt['status_tph_scan'] }}</td>
		<td style="text-align: left;">{{ $dt['alasan_manual'] }}</td>
		<td style="text-align: left;">{{ $dt['nik_validator'] }}</td>
		<td style="text-align: left;">{{ $dt['nama_validator'] }}</td>
		<td style="text-align: left;">{{ $dt['jabatan_validator'] }}</td>
		<td style="text-align: left;">{{ $dt['lat_tph'] }}</td>
		<td style="text-align: left;">{{ $dt['lon_tph'] }}</td>
		<td style="text-align: left;">{{ $dt['delivery_code'] }}</td>
		<td style="text-align: left;">{{ $dt['status_delivery_code'] }}</td>
		
		@foreach($head as $hd)
		<td style="text-align: left;">{{ @$dt['id_kualitas_'.$hd->id_kualitas] }}</td>
		@endforeach
		{{--
		<td style="text-align: left;">{{ $dt['id_kualitas_1'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_2'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_3'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_4'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_5'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_6'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_7'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_8'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_9'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_10'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_11'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_12'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_13'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_14'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_15'] }}</td>
		<td style="text-align: left;">{{ $dt['id_kualitas_16'] }}</td>
		--}}

	</tr>
	@endforeach
	@endif
</table>