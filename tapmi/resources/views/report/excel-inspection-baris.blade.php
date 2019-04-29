<table>
	<tr>
		<th>Periode : {{ $periode }}</th>
	</tr>
	<tr>
		<th style="text-align: center;">Kode Inspeksi</th>
		<th style="text-align: center;">Kode BA</th>
		<th style="text-align: center;">Business Area</th>
		<th style="text-align: center;">AFD</th>
		<th style="text-align: center;">Kode Block</th>
		<th style="text-align: center;">Block Deskripsi</th>
		<th style="text-align: center;">Tanggal Inspeksi</th>
		<th style="text-align: center;">Lama Inspeksi</th>
		<th style="text-align: center;">Baris</th>
		<th style="text-align: center;">NIK Reporter</th>
		<th style="text-align: center;">Nama Reporter</th>
		<th style="text-align: center;">Jabatan Reporter</th>
		<th style="text-align: center;">Periode</th>
		<th style="text-align: center;">Maturity Status</th>
		<th style="text-align: center;">Lat Start</th>
		<th style="text-align: center;">Long Start</th>

		@foreach ( $content_panen as $content )
			<th style="text-align: center;">{{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		@foreach ( $content_pemupukan as $content )
			<th style="text-align: center;">{{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		@foreach ( $content_perawatan_bobot as $content )
			<th style="text-align: center;">{{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		@foreach ( $content_perawatan as $content )
			<th style="text-align: center;">{{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		@foreach ( $content_perawatan_bobot as $content )
			<th style="text-align: center;">Nilai {{ $content['CONTENT_NAME'] }}</th>
		@endforeach
	</tr>
	@if ( count( $inspection_baris ) > 0 )
		@foreach ( $inspection_baris as $inspection )
			@if ( isset( $inspection['BLOCK_INSPECTION_CODE'] ) )
				<tr>
					<td style="text-align: center;">{{ $inspection['BLOCK_INSPECTION_CODE'] }}</td>
					<td style="text-align: center;">{{ $inspection['WERKS'] }}</td>
					<td>{{ $inspection['EST_NAME'] }}</td>
					<td style="text-align: center;">{{ $inspection['AFD_CODE'] }}</td>
					<td style="text-align: center;">{{ $inspection['BLOCK_CODE'] }}</td>
					<td>{{ $inspection['BLOCK_NAME'] }}</td>
					<td style="text-align: center;">{{ date( 'Y-m-d', strtotime( $inspection['INSPECTION_DATE'] ) ) }}</td>
					<td style="text-align: center;">{{ ( strlen( intval( ( $inspection['LAMA_INSPEKSI'] / 60 ) ) ) == 1 ? '0'.intval( ( $inspection['LAMA_INSPEKSI'] / 60 ) ) : intval( ( $inspection['LAMA_INSPEKSI'] / 60 ) ) ).':'.( strlen( ( $inspection['LAMA_INSPEKSI'] % 60 ) ) == 1 ? '0'.( $inspection['LAMA_INSPEKSI'] % 60 ) : ( $inspection['LAMA_INSPEKSI'] % 60 ) ) }}</td>
					<td style="text-align: center;">{{ $inspection['AREAL'] }}</td>
					<td style="text-align: center;">{{ $inspection['REPORTER_NIK'] }}</td>
					<td>{{ $inspection['REPORTER_FULLNAME'] }}</td>
					<td>{{ $inspection['REPORTER_JOB'] }}</td>
					<td style="text-align: center;">{{ date( 'Y.m', strtotime( $inspection['INSPECTION_DATE'] ) ) }}</td>
					<td style="text-align: center;">{{ $inspection['MATURITY_STATUS'] }}</td>
					<td>{{ $inspection['LAT_START_INSPECTION'] }}</td>
					<td>{{ $inspection['LONG_START_INSPECTION'] }}</td>

					@foreach ( $content_panen as $kcp => $cp )
						@if ( isset( $inspection['CONTENT_PANEN'][0][$kcp] ) )
							<td style="text-align: center;">{{ $inspection['CONTENT_PANEN'][0][$kcp] }}</td>
						@else
							<td style="text-align: center;">0</td>
						@endif
					@endforeach
					@foreach ( $content_pemupukan as $kcp => $cp )
						@if ( isset( $inspection['CONTENT'][0][$kcp] ) )
							<td style="text-align: center;">{{ $inspection['CONTENT'][0][$kcp] }}</td>
						@else
							<td style="text-align: center;"></td>
						@endif
					@endforeach
					@foreach ( $content_perawatan_bobot as $kcp => $cp )
						@if ( isset( $inspection['CONTENT_PERAWATAN'][0][$kcp] ) )
							<td style="text-align: center;">{{ ( $inspection['CONTENT'][0][$kcp] != '0' ? $inspection['CONTENT'][0][$kcp] : '' ) }}</td>
						@else
							<td style="text-align: center;"></td>
						@endif
					@endforeach
					@foreach ( $content_perawatan as $kcp => $cp )
						@if ( isset( $inspection['CONTENT'][0][$kcp] ) )
							<td style="text-align: center;">{{ ( $inspection['CONTENT'][0][$kcp] == '0' ? '' : $inspection['CONTENT'][0][$kcp] ) }}</td>
						@else
							<td style="text-align: center;"></td>
						@endif
					@endforeach
					@foreach ( $content_perawatan_bobot as $kcp => $cp )
						@if ( isset( $inspection['CONTENT_PERAWATAN'][0][$kcp] ) )
							<td style="text-align: center;">{{ $inspection['CONTENT_PERAWATAN'][0][$kcp] }}</td>
						@else
							<td style="text-align: center;">0</td>
						@endif
					@endforeach
				</tr>
			@endif
		@endforeach
	@endif
</table>