<style>
	td, tr > th { border: 1px solid #000000; }
	tr > th { text-align: center; }
</style>
<table>
	<tr>
		<td colspan="40">
			<b>Periode {{ $periode }}</b>
		</td>
	</tr>
	<tr>
		<th style="text-align: center;">NIK Reporter</th>
		<th style="text-align: center;">Nama Reporter</th>
		<th style="text-align: center;">User Role</th>
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
		<th style="text-align: center;">Pokok Panen</th>
		<th style="text-align: center;">Buah Tinggal</th>
		<th style="text-align: center;">Brondolan di Piringan</th>
		<th style="text-align: center;">Brondolan di TPH</th>
		<th style="text-align: center;">Pokok Tidak di Pupuk</th>
		<th style="text-align: center;">Rata-rata Sistem Penaburan</th>
		<th style="text-align: center;">Rata-rata Kondisi Pupuk</th>
		<th style="text-align: center;">Rata-rata Piringan</th>
		<th style="text-align: center;">Rata-rata Pasar Pikul</th>
		<th style="text-align: center;">Rata-rata TPH</th>
		<th style="text-align: center;">Rata-rata Gawangan</th>
		<th style="text-align: center;">Rata-rata Prunning</th>
		<th style="text-align: center;">Rata-rata Titi Panen</th>
		<th style="text-align: center;">Rata-rata Kastrasi</th>
		<th style="text-align: center;">Rata-rata Sanitasi</th>
		<th style="text-align: center;">Bobot Piringan</th>
		<th style="text-align: center;">Bobot Pasar Pikul</th>
		<th style="text-align: center;">Bobot TPH</th>
		<th style="text-align: center;">Bobot Gawangan</th>
		<th style="text-align: center;">Bobot Prunning</th>
		<th style="text-align: center;">Rata-rata * Bobot Piringan</th>
		<th style="text-align: center;">Rata-rata * Bobot Pasar Pikul</th>
		<th style="text-align: center;">Rata-rata * Bobot TPH</th>
		<th style="text-align: center;">Rata-rata * Bobot Gawangan</th>
		<th style="text-align: center;">Rata-rata * Bobot Prunning</th>
		<th style="text-align: center;">Nilai Inspeksi</th>
		<th style="text-align: center;">Hasil Inspeksi</th>
	</tr>
	@if( !empty( $data_header ) )
		@foreach( $data_header as $data )
			<tr>
				<td style="text-align: center;">{{ $data['nik reporter'] }}</td>
				<td style="text-align: center;">{{ $data['nama reporter'] }}</td>
				<td style="text-align: center;">{{ $data['jabatan reporter'] }}</td>
				<td style="text-align: center;">{{ $data['kode ba'] }}</td>
				<td style="text-align: center;">{{ $data['business area'] }}</td>
				<td style="text-align: center;">{{ $data['afd'] }}</td>
				<td style="text-align: center;">{{ $data['kode block'] }}</td>
				<td style="text-align: center;">{{ $data['block deskripsi'] }}</td>
				<td style="text-align: center;">{{ $data['maturity status'] }}</td>
				<td style="text-align: center;">{{ $data['tanggal inspeksi'] }}</td>
				<td style="text-align: center;">{{ $data['jumlah baris'] }}</td>
				<td style="text-align: center;">{{ $data['periode'].'\'' }}</td>
				<td style="text-align: center;">{{ $data['lama inspeksi'] }}</td>
				<td style="text-align: center;">{{ ( $data['pokok panen'] == '' ? '0' : $data['pokok panen'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['buah tinggal'] == '' ? '0' : $data['buah tinggal'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['brondolan di piringan'] == '' ? '0' : $data['brondolan di piringan'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['brondolan di tph'] == '' ? '0' : $data['brondolan di tph'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['pokok tidak di pupuk'] == '' ? '0' : $data['pokok tidak di pupuk'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata sistem penaburan'] == '' ? '0' : $data['rata-rata sistem penaburan'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata kondisi pupuk'] == '' ? '0' : $data['rata-rata kondisi pupuk'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata piringan'] == '' ? '0' : $data['rata-rata piringan'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata pasar pikul'] == '' ? '0' : $data['rata-rata pasar pikul'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata tph'] == '' ? '0' : $data['rata-rata tph'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata gawangan'] == '' ? '0' : $data['rata-rata gawangan'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata prunning'] == '' ? '0' : $data['rata-rata prunning'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata titi panen'] == '' ? '0' : $data['rata-rata titi panen'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata kastrasi'] == '' ? '0' : $data['rata-rata kastrasi'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata sanitasi'] == '' ? '0' : $data['rata-rata sanitasi'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['bobot piringan'] == '' ? '0' : number_format( floatval( $data['bobot piringan'] ), 2, '.', '' ) ) }}</td>
				<td style="text-align: center;">{{ ( $data['bobot pasar pikul'] == '' ? '0' : number_format( floatval( $data['bobot pasar pikul'] ), 2, '.', '' ) ) }}</td>
				<td style="text-align: center;">{{ ( $data['bobot tph'] == '' ? '0' : number_format( floatval( $data['bobot tph'] ), 2, '.', '' ) ) }}</td>
				<td style="text-align: center;">{{ ( $data['bobot gawangan'] == '' ? '0' : number_format( floatval( $data['bobot gawangan'] ), 2, '.', '' ) ) }}</td>
				<td style="text-align: center;">{{ ( $data['bobot prunning'] == '' ? '0' : number_format( floatval( $data['bobot prunning'] ), 2, '.', '' ) ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata x bobot piringan'] == '' ? '0' : number_format( floatval( $data['rata-rata x bobot piringan'] ), 2, '.', '' ) ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata x pasar pikul'] == '' ? '0' : number_format( floatval( $data['rata-rata x pasar pikul'] ), 2, '.', '' ) ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata x bobot tph'] == '' ? '0' : number_format( floatval( $data['rata-rata x bobot tph'] ), 2, '.', '' ) ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata x bobot gawangan'] == '' ? '0' : number_format( floatval( $data['rata-rata x bobot gawangan'] ), 2, '.', '' ) ) }}</td>
				<td style="text-align: center;">{{ ( $data['rata-rata x bobot prunning'] == '' ? '0' : number_format( floatval( $data['rata-rata x bobot prunning'] ), 2, '.', '' ) ) }}</td>

				<td style="text-align: center;">{{ $data['nilai inspeksi'] }}</td>
				<td style="text-align: center;">{{ $data['hasil inspeksi'] }}</td>
			</tr>
		@endforeach
	@endif
</table>


number_format((float)$foo, 2, '.', '')
number_format( floatval(  ), 2, '.', '' )