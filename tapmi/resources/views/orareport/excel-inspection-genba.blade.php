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
		<th style="text-align: center;">NIK Reporter</th>
		<th style="text-align: center;">Nama Reporter</th>
		<th style="text-align: center;">User Role</th>
		<th style="text-align: center;">Periode</th>
		<th style="text-align: center;">NIK Participant</th>
		<th style="text-align: center;">Nama Participant</th>
		<th style="text-align: center;">User Role Participant</th>
	</tr>
	@if( !empty( $data_genba ) )
		@foreach( $data_genba as $data )
			<tr>
				<td style="text-align: center;">{{ $data['kode inspeksi'] }}</td>
				<td style="text-align: center;">{{ $data['kode ba'] }}</td>
				<td style="text-align: center;">{{ $data['business area'] }}</td>
				<td style="text-align: center;">{{ $data['afd'] }}</td>
				<td style="text-align: center;">{{ $data['kode block'] }}</td>
				<td style="text-align: center;">{{ $data['block deskripsi'] }}</td>
				<td style="text-align: center;">{{ $data['tanggal inspeksi'] }}</td>
				<td style="text-align: center;">{{ $data['lama inspeksi'] }}</td>
				<td style="text-align: center;">{{ $data['nik reporter'] }}</td>
				<td style="text-align: center;">{{ $data['nama reporter'] }}</td>
				<td style="text-align: center;">{{ $data['jabatan reporter'] }}</td>
				<td style="text-align: center;">{{ $data['periode'].'\'' }}</td>
				<td style="text-align: center;">{{ $data['nik participant'] }}</td>
				<td style="text-align: center;">{{ $data['nama participant'] }}</td>
				<td style="text-align: center;">{{ $data['jabatan participant'] }}</td>
			</tr>
		@endforeach
	@endif
</table>