<table>
	<tr>
		<th>Periode : {{ $periode }}</th>
	</tr>
	<tr>
		<th style="text-align: center;">NIK Reporter</th>
		<th style="text-align: center;">Nama Reporter</th>
		<th style="text-align: center;">Jabatan Reporter</th>
		<th style="text-align: center;">Kode BA</th>
		<th style="text-align: center;">Business Area</th>
		<th style="text-align: center;">AFD</th>
		<th style="text-align: center;">Kode Block</th>
		<th style="text-align: center;">Block Deskripsi</th>
		<th style="text-align: center;">Maturity Status</th>
		<th style="text-align: center;">Tanggal Inspeksi</th>
		<th style="text-align: center;">Jumlah Baris</th>
		<th style="text-align: center;">Periode</th>
		<th style="text-align: center;">Lama Inspeksi</th>

		@foreach ( $content_panen as $content )
			<th style="text-align: center;">{{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		@foreach ( $content_pemupukan as $content )
			<th style="text-align: center;">Rata-rata {{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		@foreach ( $content_perawatan_bobot as $content )
			<th style="text-align: center;">Rata-rata {{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		@foreach ( $content_perawatan as $content )
			<th style="text-align: center;">Rata-rata {{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		@foreach ( $content_perawatan_bobot as $content )
			<th style="text-align: center;">Bobot {{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		@foreach ( $content_perawatan_bobot as $content )
			<th style="text-align: center;">Rata-rata * Bobot {{ $content['CONTENT_NAME'] }}</th>
		@endforeach

		<th style="text-align: center;">Nilai Inspeksi</th>
		<th style="text-align: center;">Hasil Inspeksi</th>

	</tr>
	@if ( count( $inspection_header ) > 0 )
		@foreach ( $inspection_header as $inspection )
			<tr>
				<td style="text-align: center;">{{ $inspection['NIK_REPORTER'] }}</td>
				<td style="text-align: left;">{{ $inspection['NAMA_REPORTER'] }}</td>
				<td style="text-align: left;">{{ $inspection['JABATAN'] }}</td>
				<td style="text-align: center;">{{ $inspection['BA_CODE'] }}</td>
				<td>{{ $inspection['BA_NAME'] }}</td>
				<td style="text-align: center;">{{ $inspection['AFD_CODE'] }}</td>
				<td style="text-align: center;">{{ $inspection['BLOCK_CODE'] }}</td>
				<td>{{ $inspection['BLOCK_NAME'] }}</td>
				<td style="text-align: center;">{{ $inspection['MATURITY_STATUS'] }}</td>
				<td style="text-align: center;">{{ date( 'd-m-Y', strtotime( $inspection['INSPECTION_DATE'] ) ) }}</td>
				<td style="text-align: center;">{{ $inspection['JUMLAH_INSPEKSI'] }}</td>
				<td style="text-align: center;">{{ date( 'Y.m', strtotime( $inspection['INSPECTION_DATE'] ) ) }}</td>
				<td style="text-align: center;">{{ ( strlen( intval( ( $inspection['LAMA_INSPEKSI'] / 60 ) ) ) == 1 ? '0'.intval( ( $inspection['LAMA_INSPEKSI'] / 60 ) ) : intval( ( $inspection['LAMA_INSPEKSI'] / 60 ) ) ).':'.( strlen( ( $inspection['LAMA_INSPEKSI'] % 60 ) ) == 1 ? '0'.( $inspection['LAMA_INSPEKSI'] % 60 ) : ( $inspection['LAMA_INSPEKSI'] % 60 ) ) }}</td>

				@foreach ( $content_panen as $kcp => $cp )
					@if ( isset( $inspection['DATA_JUMLAH_PANEN'][$kcp] ) )
						<td style="text-align: center;">{{ $inspection['DATA_JUMLAH_PANEN'][$kcp] }}</td>
					@else
						<td style="text-align: center;"></td>
					@endif
				@endforeach

				@foreach ( $content_pemupukan as $kcp => $cp )
					@if ( isset( $inspection['DATA_RATA2_PEMUPUKAN'][$kcp] ) )
						<td style="text-align: center;">{{ number_format( $inspection['DATA_RATA2_PEMUPUKAN'][$kcp], 2, '.', '' ) }}</td>
					@else
						<td style="text-align: center;"></td>
					@endif
				@endforeach

				@foreach ( $content_perawatan_bobot as $kcp => $cp )
					@if ( isset( $inspection['DATA_RATA2'][$kcp] ) )
						<td style="text-align: center;">{{ number_format( $inspection['DATA_RATA2'][$kcp], 2, '.', '' ) }}</td>
					@else
						<td style="text-align: center;"></td>
					@endif
				@endforeach

				@foreach ( $content_perawatan as $kcp => $cp )
					@if ( isset( $inspection['DATA_RATA2'][$kcp] ) )
						<td style="text-align: center;">{{ number_format( $inspection['DATA_RATA2'][$kcp], 2, '.', '' ) }}</td>
					@else
						<td style="text-align: center;"></td>
					@endif
				@endforeach

				@foreach ( $content_perawatan_bobot as $kcp => $cp )
					@if ( isset( $inspection['DATA_BOBOT_RAWAT'][$kcp] ) )
						<td style="text-align: center;">{{ $inspection['DATA_BOBOT_RAWAT'][$kcp] }}</td>
					@else
						<td style="text-align: center;"></td>
					@endif
				@endforeach

				@foreach ( $content_perawatan_bobot as $kcp => $cp )
					@if ( isset( $inspection['DATA_RATAXBOBOT'][$kcp] ) )
						<td style="text-align: center;">{{ number_format( $inspection['DATA_RATAXBOBOT'][$kcp], 2, '.', '' ) }}</td>
					@else
						<td style="text-align: center;"></td>
					@endif
				@endforeach

				<td style="text-align: center;">{{ number_format( $inspection['NILAI_INSPEKSI'], 2, '.', '' ) }}</td>
				<td style="text-align: center;">{{ ( isset( $inspection['HASIL_INSPEKSI']['GRADE'] ) ) ? $inspection['HASIL_INSPEKSI']['GRADE'] : "" }}</td>

			</tr>
		@endforeach
	@endif
</table>