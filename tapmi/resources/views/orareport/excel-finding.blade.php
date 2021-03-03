<table>
	<tr>
		<th style="text-align:center;">Kode Finding</th>
		<th style="text-align:center;">Kode BA</th>
		<th style="text-align:center;">Business Area</th>
		<th style="text-align:center;">Kode AFD</th>
		<th style="text-align:center;">Kode Block</th>
		<th style="text-align:center;">Block Deskripsi</th>
		<th style="text-align:center;">Tanggal Temuan</th>
		<th style="text-align:center;">NIK Pembuat</th>
		<th style="text-align:center;">Nama Pembuat</th>
		<th style="text-align:center;">User Role</th>
		<th style="text-align:center;">Maturity Status</th>
		<th style="text-align:center;">Periode</th>
		<th style="text-align:center;">Lat</th>
		<th style="text-align:center;">Long</th>
		<th style="text-align:center;">Tipe Temuan</th>
		<th style="text-align:center;">Kategori Temuan</th>
		<th style="text-align:center;">Road</th>
		<th style="text-align:center;">Prioritas</th>
		<th style="text-align:center;">Batas Waktu</th>
		<th style="text-align:center;">NIK PIC</th>
		<th style="text-align:center;">Nama PIC</th>
		<th style="text-align:center;">User Role PIC</th>
		<th style="text-align:center;">Deskripsi Temuan</th>
		<th style="text-align:center;">Status Temuan</th>
		<th style="text-align:center;">End Time</th>
		<th style="text-align:center;">Progress (%)</th>
		<th style="text-align:center;">Last Update</th>
		<th style="text-align:center;">Lihat Foto</th>
	</tr>
	@if ( count( $data ) > 0 )
		@foreach ( $data as $finding )
			<tr>
				<td style="text-align:left;">{{ $finding['finding_code'] }}</td>
				<td style="text-align:center;">{{ $finding['werks'] }}</td>
				<td style="text-align:center;">{{ $finding['est_name'] }}</td>
				<td style="text-align:center;">{{ $finding['afd_code'] }}</td>
				<td style="text-align:center;">{{ $finding['block_code'] }}</td>
				<td style="text-align:left;">{{ $finding['block_name'] }}</td>
				<td style="text-align:center;">{{ $finding['tanggal_temuan'] }}</td>
				<td style="text-align:center;">{{ $finding['creator_employee_nik'] }}</td>
				<td style="text-align:center;">{{ $finding['creator_employee_fullname'] }}</td>
				<td style="text-align:center;">{{ $finding['creator_employee_position'] }}</td>
				<td style="text-align:center;">{{ $finding['maturity_status'] }}</td>
				<td style="text-align:center;">{{ date( 'Y.m', strtotime( $finding['tanggal_temuan'] ) ) }}</td>
				<td style="text-align:left;">{{ $finding['lat_finding'] }}</td>
				<td style="text-align:left;">{{ $finding['long_finding'] }}</td>
				<td style="text-align:center;">{{ isset($finding['finding_type'])?$finding['finding_type']:'' }}</td>
				<td style="text-align:center;">{{ $finding['category_name'] }}</td>
				<td style="text-align:center;">{{ $finding['road_code'].' - '.$finding['road_name'] }}</td>
				<td style="text-align:center;">{{ $finding['finding_priority'] }}</td>
				<td style="text-align:center;">{{ $finding['due_date'] }}</td>
				<td style="text-align:center;">{{ $finding['pic_employee_nik'] }}</td>
				<td style="text-align:center;">{{ $finding['pic_employee_fullname'] }}</td>
				<td style="text-align:center;">{{ $finding['pic_employee_position'] }}</td>
				<td style="text-align:left;">{{ $finding['finding_desc'] }}</td>
				<td style="text-align:center;">{{ $finding['status'] }}</td>
				<td style="text-align:center;">{{ $finding['end_time'] }}</td>
				@if ( $finding['progress'] == null )
					<td style="text-align:center;">0 %</td>
				@else
					<td style="text-align:center;">{{ $finding['progress'] }} %</td>
				@endif
				<td style="text-align:center;">{{ $finding['update_time'] }}</td>
				<td><a href="{{ $finding['link_foto'] }}">Link Foto</a></td>
			</tr>
		@endforeach
	@endif
</table>