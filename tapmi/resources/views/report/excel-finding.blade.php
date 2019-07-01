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
		<th style="text-align:center;">Jabatan Pembuat</th>
		<th style="text-align:center;">Maturity Status</th>
		<th style="text-align:center;">Periode</th>
		<th style="text-align:center;">Lat</th>
		<th style="text-align:center;">Long</th>
		<th style="text-align:center;">Kategori Temuan</th>
		<th style="text-align:center;">Prioritas</th>
		<th style="text-align:center;">Batas Waktu</th>
		<th style="text-align:center;">NIK PIC</th>
		<th style="text-align:center;">Nama PIC</th>
		<th style="text-align:center;">Deskripsi Temuan</th>
		<th style="text-align:center;">Status Temuan</th>
		<th style="text-align:center;">Progress (%)</th>
		<th style="text-align:center;">Last Update</th>
	</tr>
	@if ( count( $finding_data ) > 0 )
		@foreach ( $finding_data as $finding )
			<tr>
				<td style="text-align:left;">{{ $finding['FINDING_CODE'] }}</td>
				<td style="text-align:center;">{{ $finding['WERKS'] }}</td>
				<td style="text-align:center;">{{ $finding['EST_NAME'] }}</td>
				<td style="text-align:center;">{{ $finding['AFD_CODE'] }}</td>
				<td style="text-align:center;">{{ $finding['BLOCK_CODE'] }}</td>
				<td style="text-align:left;">{{ $finding['BLOCK_NAME'] }}</td>
				<td style="text-align:center;">{{ $finding['INSERT_TIME'] }}</td>
				<td style="text-align:center;">{{ $finding['INSPEKTOR']['EMPLOYEE_NIK'] }}</td>
				<td style="text-align:left;">{{ $finding['INSPEKTOR']['FULLNAME'] }}</td>
				<td style="text-align:left;">{{ $finding['INSPEKTOR']['USER_ROLE'] }}</td>
				<td style="text-align:center;">{{ $finding['MATURITY_STATUS'] }}</td>
				<td style="text-align:center;">{{ date( 'Y.m', strtotime( $finding['INSERT_TIME'] ) ) }}</td>
				<td style="text-align:left;">{{ $finding['LAT_FINDING'] }}</td>
				<td style="text-align:left;">{{ $finding['LONG_FINDING'] }}</td>
				<td style="text-align:center;">{{ $finding['FINDING_CATEGORY'] }}</td>
				<td style="text-align:center;">{{ $finding['FINDING_PRIORITY'] }}</td>
				<td style="text-align:center;">{{ $finding['DUE_DATE'] }}</td>
				<td style="text-align:center;">{{ $finding['PIC']['EMPLOYEE_NIK'] }}</td>
				<td style="text-align:left;">{{ $finding['PIC']['FULLNAME'] }}</td>
				<td style="text-align:left;">{{ $finding['FINDING_DESC'] }}</td>
				<td style="text-align:center;">{{ $finding['STATUS'] }}</td>
				@if ( $finding['PROGRESS'] == null )
					<td style="text-align:center;">0 %</td>
				@else
				<td style="text-align:center;">{{ $finding['PROGRESS'] }} %</td>
				@endif
				<td style="text-align:center;">{{ $finding['UPDATE_TIME'] }}</td>
			</tr>
		@endforeach
	@endif
</table>