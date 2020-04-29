@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Validasi BCC oleh Kepala Kebun' )
@section('style')
<style>
input[type="radio"]{
    visibility:hidden;
	display: none;
}
.w-50{
	width: 50%;
}
.fields{
	
	border-color: #000000;
}
.btn-radio {
    white-space: normal !important;
    height: 70px;
    width: 150px; 
  	background-color: white;
}
.btn-next {
	white-space: normal !important;
}
.btnselect {
  border-color: #000000;
  color: black;
}

.btnselect:focus {
  background-color:#4d9925;
  color: white;
}
.btnselect:focus:active{
  background-color:#4d9925;
  color: white;
}

.borderless td, .borderless th {
    border: none;
}
tr
{
    line-height:30px;
}

#container {
  width: 620px;
  height: 500px;
  overflow: hidden;
}
#container.rotate90,
#container.rotate270 {
  width: 500px;
  height: 620px
}
#image {
  transform-origin: top left;
  /* IE 10+, Firefox, etc. */
  -webkit-transform-origin: top left;
  /* Chrome */
  -ms-transform-origin: top left;
  /* IE 9 */
}
#container.rotate90 #image {
  transform: rotate(90deg) translateY(-100%);
  -webkit-transform: rotate(90deg) translateY(-100%);
  -ms-transform: rotate(90deg) translateY(-100%);
}
#container.rotate180 #image {
  transform: rotate(180deg) translate(-100%, -100%);
  -webkit-transform: rotate(180deg) translate(-100%, -100%);
  -ms-transform: rotate(180deg) translateX(-100%, -100%);
}
#container.rotate270 #image {
  transform: rotate(270deg) translateX(-100%);
  -webkit-transform: rotate(270deg) translateX(-100%);
  -ms-transform: rotate(270deg) translateX(-100%);
}
</style>
@endsection

@foreach ( $data_validasi as $key => $q )
	

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
	<form action="{{ route( 'create_validation' ) }}" method="post">
	{{ csrf_field() }}
	<?php $tgl = date_format(date_create($q['tanggal_rencana']),'Ymd');?>
	<input type="hidden" name="id_validasi" value="{{$q['nik_kerani_buah']}}-{{$q['nik_mandor']}}-{{$tgl}}">
	<input type="hidden" name="tanggal_ebcc" value="{{$q['tanggal_rencana']}}">
	<input type="hidden" name="nik_krani_buah" value="{{$q['nik_kerani_buah']}}">
	<input type="hidden" name="nama_krani_buah" value="{{$q['emp_name']}}">
	<input type="hidden" name="nik_mandor" value="{{$q['nik_mandor']}}">
	<input type="hidden" name="nama_mandor" value="{{$q['nama_mandor']}}">
	<input type="hidden" name="ba_code" value="{{ substr($q['id_ba_afd_blok'], 0, 4) }}">
	<input type="hidden" name="ba_name" value="{{$q['nama_ba']}}">
	<input type="hidden" name="afd_code" value="{{ substr($q['id_ba_afd_blok'], 4, 1) }}">
	<input type="hidden" name="block_code" value="{{$q['id_blok']}}">
	<input type="hidden" name="block_name" value="{{$q['blok_name']}}">
	<input type="hidden" name="no_tph" value="{{$q['no_tph']}}">
	<input type="hidden" name="no_bcc" value="{{$q['no_bcc']}}">
	<input type="hidden" name="jumlah_ebcc_validated" value="{{$no_validasi}}">
	<input type="hidden" name="last_update" value="{{ date('Y-M-d') }}">
	<input type="hidden" name="target" value="{{$target}}">
		<tr>
			<td  rowspan="7" width="45%"  style="vertical-align: top;">
				<div style="position:absolute;z-index: 1000">
				<?php	$img = str_replace("/","",$q['picture_name']);
						$os = PHP_OS; 
						if( $os != "WINNT" ){
							$img_backup = 'app/public/notfound.jpg';
						}else{
							$img_backup = 'app\public\notfound.jpg';
						}
				?>
					<div style="position:absolute;z-index: 1000">
					<input id="button" type="image" src="http://inspectiondev.tap-agri.com/storage/rotate_45.png" >
					</div>
					<!-- <img onerror="this.onerror=null;this.src='https://webhostingmedia.net/wp-content/uploads/2018/01/http-error-404-not-found.png'"  src="http://10.20.1.59/ebcc/array/uploads/{{$img}}" style="display:block;" width="80%" height="80%" > -->
					<div id="container"  style="background-position: center center; background-repeat: no-repeat;overflow: hidden;">
					<img onerror="this.onerror=null;this.src='http://inspectiondev.tap-agri.com/storage/notfound.jpg'"  src="http://tap-motion.tap-agri.com/ebcc/array/uploads/{{$img}}" style="display:block;" width="80%" height="80%" id="image" >
					</div> 
					
				</div>
			</td>
			<td><h4>Validasi ke {{$no_validasi}} dari {{$target}}</h4></td>
		</tr>
		<tr>
		<!-- Kriteria Buah Berdasarkan Foto eBCC -->
			<td colspan="8"></td>
		</tr>
		<tr>
			<td>
				<table class="borderless" width = "100%" id="tform">
				<!-- <table class="table table-bordered" id="tform"> -->
					<!-- <thead style="background-color:#dadbd5">
					<tr> -->
						<input type="hidden" name="jjg_ebcc_bk" value="{{$q['ebcc_jml_bk']}}">
						<input type="hidden" name="jjg_ebcc_ba" value="{{$q['ebcc_jml_ba']}}">
						<input type="hidden" name="jjg_ebcc_bm" value="{{$q['ebcc_jml_bm']}}">
						<input type="hidden" name="jjg_ebcc_ms" value="{{$q['ebcc_jml_ms']}}">
						<input type="hidden" name="jjg_ebcc_or" value="{{$q['ebcc_jml_or']}}">
						<input type="hidden" name="jjg_ebcc_bb" value="{{$q['ebcc_jml_bb']}}">
						<input type="hidden" name="jjg_ebcc_jk" value="{{$q['ebcc_jml_jk']}}">
						
					<!-- </tr>
					</thead> -->
					<tbody>
					
						<!-- <input type="hidden" min=0 class="form-control fields" required name="jjg_validate_bk" id="bk" value="0" onkeyup="sum()">
						<input type="hidden" min=0 class="form-control fields" required name="jjg_validate_ba" id="ba" value="0" onkeyup="sum()">
						<td><input type="number" min=0 class="form-control fields" required name="jjg_validate_bm" id="bm" onkeyup="sum()" autofocus></td>
						<td><input type="number" min=0 class="form-control fields" required name="jjg_validate_ms" id="ms" onkeyup="sum()"></td>
						<td><input type="number" min=0 class="form-control fields" required name="jjg_validate_or" id="or" onkeyup="sum()"></td>
						<td><input type="number" min=0 class="form-control fields" required name="jjg_validate_bb" id="bb" onkeyup="sum()"></td>
						<td><input type="number" min=0 class="form-control fields" required name="jjg_validate_jk" id="jk" onkeyup="sum()"></td>
						<td><input type="text" min=0 class="form-control" required  readonly="readonly" name="jjg_validate_total" id="total_jjg"></td> -->
						<tr><td>Nama Krani Buah </td><td>: </td><td colspan="2"><b> {{$q['emp_name']}}</b></tr>
						<tr><td>Nama Mandor </td><td>: </td><td colspan="2"><b>{{$q['nama_mandor']}}</b></tr>
						<tr><td>Afdeling </td><td>: </td><td colspan="2"><b>{{$q['id_afd']}}</b></tr>
						<tr><td>Total Janjang Panen<input type="hidden" name="jjg_ebcc_total" value="{{$q['jjg_panen']}}"></td><td>:</td>
							<td> <div class="w-50"><input type="number" min=0 class="form-control fields" required autofocus name="jjg_validate_total" id="total_jjg"></div></td>
							<td align="right">
								<button type="submit" class="btn btn-block btn-success pull-right btn-next">SIMPAN & LANJUT BERIKUTNYA</button>
							</td>
						</tr>
					</tbody>
				</table>
			</tr>
		<tr>
			<td><br/>Foto Tidak Bisa Divalidasi Karena:</td>
		</tr>
		<tr>
			<td>
				<div data-toggle="buttons">
					<div class="row">
						<div class="col-md-3">
							<button class="btn btn-radio btnselect" id="btnr">
								<input  type="radio"  name="kondisi_foto" value="Foto Tidak Muncul">Foto Tidak Muncul</button>
						</div>
						<div class="col-md-3">
							<button class="btn btn-radio btnselect" id="btnr">
								<input  type="radio"  name="kondisi_foto" value="Blur">Blur</button>
						</div>
						<div class="col-md-3">
							<button class="btn btn-radio btnselect" id="btnr">
								<input  type="radio"  name="kondisi_foto" value="Jauh">Jauh</button>
						</div>
						<div class="col-md-3">
							<button class="btn btn-radio btnselect" id="btnr">
								<input  type="radio"  name="kondisi_foto" value="Gambar Terpotong">Gambar Terpotong</button>
							</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md-3">
							<button class="btn btn-radio btnselect" id="btnr">
								<input  type="radio"  name="kondisi_foto" value="Gelap/ Tidak Terlihat">Gelap/ Tidak Terlihat</button>
						</div>
						<div class="col-md-3">
							<button class="btn btn-radio btnselect" id="btnr">
								<input  type="radio"  name="kondisi_foto" value="Angle Pengambilan Gambar">Angle Pengambilan Gambar</button>
						</div>
						<div class="col-md-3">
							<button class="btn btn-radio btnselect" id="btnr">
								<input  type="radio"  name="kondisi_foto" value="Penyusunan TBS tidak sesuai SOP">Penyusunan TBS tidak sesuai SOP</button>
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
			</td>
		</tr>
		</form>
	<table>
	<?php 
	// print_r($data_validasi);

	?>
	@endsection

@endforeach
@section( 'scripts' )
<script type="text/javascript">
	
	$(document).ready(function() {
		MobileInspection.set_active_menu( '{{ $active_menu }}' );
	
	});

// 	function sum() {
//        var bm = document.getElementById('bm').value;
//        var bk = document.getElementById('bk').value;
//        var ms = document.getElementById('ms').value;
//        var or = document.getElementById('or').value;
//        var bb = document.getElementById('bb').value;
//        var jk = document.getElementById('jk').value;
//        var ba = document.getElementById('ba').value;
//        if (bm == "")
//            bm = 0;
//        if (bk == "")
//            bk = 0;
//        if (ms == "")
//            ms = 0;
//        if (or == "")
//            or = 0;
//        if (bb == "")
//            bb = 0;
//        if (jk == "")
//            jk = 0;
//        if (ba == "")
//            ba = 0;

//        var result = parseInt(bm) + parseInt(bk) + parseInt(ms) + parseInt(or) + parseInt(bb) + parseInt(jk) + parseInt(ba);
//        if (!isNaN(result)) {
//            document.getElementById('total_jjg').value = result;
//        }
//    }


   $('.btnselect').change(function(){
		$('.fields').removeAttr('required');
		$('.fields').val('');
		// $('#total_jjg').val('');
	});

	$("input").change(function () {
		$("input.fields").prop('required',true);
	});

	
	// function rotateElem() { 
	// 	// document.querySelector('.box').style.transform 
	// 	// = 'rotate(90deg)'; 
	// 	var img=document.getElementById('image');
	// 	img.setAttribute('style','transform:rotate(90deg)');
	// 	img.removeAttr('style','transform:rotate(90deg)');
	// } 
    

	var angle = 0,
  img = document.getElementById('container');
	document.getElementById('button').onclick = function() {
	angle = (angle + 90) % 360;
	img.className = "rotate" + angle;
}

</script>

@endsection