@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Validasi BCC oleh Kepala Kebun' )
@section('style')
<style>
input[type="radio"]{
    visibility:hidden;
}
.btn-radio {
    white-space: normal !important;
    height: 70px;
    width: 150px; 
  	background-color: white;
}
.btnselect {
  border-color: #e9ebe4;
  color: black;
}

.btnselect:focus {
  background-color:#4d9925;
  color: white;
}
.borderless td, .borderless th {
    border: none;
}
</style>
@endsection
@section( 'subheader' )
@endsection

@section( 'content' )
<div class="row">
	<div class="col-md-8">
		<div class="row">
			<div class="col-md-4">
			</div>
			<div class="col-md-4">
			</div>
			<div class="col-md-4"></div>
		</div>
	</div>
</div>
<table class="borderles">
@foreach ( $data_validasi as $key => $q )
<form method="post" action="">
	<tr>
		<td  rowspan="7" width="45%">
			<div style="position:absolute;z-index: 1000">
				<img src="http://tap-motion.tap-agri.com/ebcc/array/uploads/{{$q['picture_name']}}" >
			</div>
		</td>
		<td><h4>Validasi ke 1 dari 3</h4></td>
	</tr>
	<tr>
		<td colspan="8">Kriteria Buah Berdasarkan Foto eBCC</td>
	</tr>
	<tr>
		<td>
			<table class="table table-bordered">
  				<thead style="background-color:#dadbd5">
				<tr>
					<td>BM</td>
					<td>BK</td>
					<td>MS</td>
					<td>OR</td>
					<td>BB</td>
					<td>JK</td>
				</tr>
				</thead>
 				<tbody>
				<tr>
					<td><input type="text" class="form-control" required name="bm"></td>
					<td><input type="text" class="form-control" required name="bk"></td>
					<td><input type="text" class="form-control" required name="ms"></td>
					<td><input type="text" class="form-control" required name="or"></td>
					<td><input type="text" class="form-control" required name="bb"></td>
					<td><input type="text" class="form-control" required name="jk"></td>
				</tr>
				</tbody>
			</table>
		</tr>
	<tr>
		<td>Foto Tidak Bisa Divalidasi Karena:</td>
	</tr>
	<tr>
		<td>
			<div data-toggle="buttons">
				<div class="row">
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" id="btnr">
							<input type="radio" name="options">Foto Tidak Muncul</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" id="btnr">
							<input type="radio" name="options">Blur</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" id="btnr">
							<input type="radio" name="options">Jauh</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" id="btnr">
							<input type="radio" name="options">Gambar Terpotong</button>
						</div>
				</div>
				<br>
				<div class="row">
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" id="btnr">
							<input type="radio" name="options">Gelap/ Tidak Terlihat</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" id="btnr">
							<input type="radio" name="options">Angle Pengambilan Gambar</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" id="btnr">
							<input type="radio" name="options">Penyusunan TBS tidak sesuai SOP</button>
					</div>
					</div>
			</div>
		</td>
	</tr>
	<tr><td><br><br></td></tr>
	<tr>
		<td>
		<div class="row">
		</div>
			<div class="row">
					<div class="col-md-4">
						<button class="btn btn-block btn-dark">KEMBALI</button>
					</div>
					<div class="col-md-4">
					</div>
					<div class="col-md-4">
						<button type="submit" class="btn btn-block btn-success pull-right">SIMPAN</button>
					</div>
			</div>
		</td>
	</tr>
	</form>
	@endforeach
<table>
<?php 
// print_r($data_validasi);

?>
@endsection

@section( 'scripts' )
<script type="text/javascript">
	
	$(document).ready(function() {
		MobileInspection.set_active_menu( '{{ $active_menu }}' );
	});

</script>
@endsection