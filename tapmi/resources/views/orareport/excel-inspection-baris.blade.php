<style>
	tr > td, tr > th { border: 1px solid #000000; }
	tr > th { text-align: center; }
</style>
<table>
	<tr>
		<td colspan="36">
			<b>Periode {{ $periode }}</b>
		</td>
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
		<th style="text-align: center;">User Role Reporter</th>
		<th style="text-align: center;">Periode</th>
		<th style="text-align: center;">Maturity Status</th>
		<th style="text-align: center;">Lat Start</th>
		<th style="text-align: center;">Long Start</th>
		<th style="text-align: center;">Pokok Panen</th>
		<th style="text-align: center;">Buah Tinggal</th>
		<th style="text-align: center;">Brondolan di Piringan</th>
		<th style="text-align: center;">Brondolan di TPH</th>
		<th style="text-align: center;">Pokok Tidak di Pupuk</th>
		<th style="text-align: center;">Sistem Penaburan</th>
		<th style="text-align: center;">Kondisi Pupuk</th>
		<th style="text-align: center;">Piringan</th>
		<th style="text-align: center;">Pasar Pikul</th>
		<th style="text-align: center;">TPH</th>
		<th style="text-align: center;">Gawangan</th>
		<th style="text-align: center;">Prunning</th>
		<th style="text-align: center;">Titi Panen</th>
		<th style="text-align: center;">Kastrasi</th>
		<th style="text-align: center;">Sanitasi</th>
		<th style="text-align: center;">Nilai Piringan</th>
		<th style="text-align: center;">Nilai Pasar Pikul</th>
		<th style="text-align: center;">Nilai TPH</th>
		<th style="text-align: center;">Nilai Gawangan</th>
		<th style="text-align: center;">Nilai Prunning</th>
	</tr>
	@if( !empty( $data_baris ) )
		@foreach( $data_baris as $data )
			<tr>
				<td style="text-align: center;">{{ $data['kode inspeksi'] }}</td>
				<td style="text-align: center;">{{ $data['kode ba'] }}</td>
				<td style="text-align: center;">{{ $data['business area'] }}</td>
				<td style="text-align: center;">{{ $data['afd'] }}</td>
				<td style="text-align: center;">{{ $data['kode block'] }}</td>
				<td style="text-align: center;">{{ $data['block deskripsi'] }}</td>
				<td style="text-align: center;">{{ $data['tanggal inspeksi'] }}</td>
				<td style="text-align: center;">{{ $data['lama inspeksi'] }}</td>
				<td style="text-align: center;">{{ $data['baris'] }}</td>
				<td style="text-align: center;">{{ $data['nik reporter'] }}</td>
				<td style="text-align: center;">{{ $data['nama reporter'] }}</td>
				<td style="text-align: center;">{{ $data['jabatan reporter'] }}</td>
				<td style="text-align: center;">{{ $data['periode'] }}</td>
				<td style="text-align: center;">{{ $data['maturity status'] }}</td>
				<td style="text-align: center;">{{ $data['lat start'] }}</td>
				<td style="text-align: center;">{{ $data['long start'] }}</td>
				<td style="text-align: center;">{{ ( $data['pokok panen'] == '' ? '0' : $data['pokok panen'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['buah tinggal'] == '' ? '0' : $data['buah tinggal'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['brondolan di piringan'] == '' ? '0' : $data['brondolan di piringan'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['brondolan di tph'] == '' ? '0' : $data['brondolan di tph'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['pokok tidak di pupuk'] == '' ? '0' : $data['pokok tidak di pupuk'] ) }}</td>
				
				<td style="text-align: center;">{{ $data['sistem penaburan'] }}</td>
				<td style="text-align: center;">{{ $data['kondisi pupuk'] }}</td>
				<td style="text-align: center;">{{ $data['piringan'] }}</td>
				<td style="text-align: center;">{{ $data['pasar pikul'] }}</td>
				<td style="text-align: center;">{{ $data['tph'] }}</td>
				<td style="text-align: center;">{{ $data['gawangan'] }}</td>
				<td style="text-align: center;">{{ $data['prunning'] }}</td>
				<td style="text-align: center;">{{ $data['titi panen'] }}</td>
				<td style="text-align: center;">{{ $data['kastrasi'] }}</td>
				<td style="text-align: center;">{{ $data['sanitasi'] }}</td>
				<td style="text-align: center;">{{ ( $data['nilai piringan'] == '' ? '0' : $data['nilai piringan'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['nilai pasar pikul'] == '' ? '0' : $data['nilai pasar pikul'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['nilai tph'] == '' ? '0' : $data['nilai tph'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['nilai gawangan'] == '' ? '0' : $data['nilai gawangan'] ) }}</td>
				<td style="text-align: center;">{{ ( $data['nilai prunning'] == '' ? '0' : $data['nilai prunning'] ) }}</td>
			</tr>
		@endforeach
	@endif
</table>