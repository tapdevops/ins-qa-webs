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
<form action="{{ route( 'create_validation' ) }}" method="post">
{{ csrf_field() }}
<?php $tgl = date_format(date_create($q['tanggal_rencana']),'Ymd');?>
<input type="hidden" name="id_validasi" value="{{$q['nik_kerani_buah']}}-{{$q['nik_mandor']}}-{{$tgl}}">
<input type="hidden" name="tanggal_ebcc" value="{{$q['tanggal_rencana']}}">
<input type="hidden" name="nik_krani_buah" value="{{$q['nik_kerani_buah']}}">
<input type="hidden" name="nama_krani_buah" value="{{$q['emp_name']}}">
<input type="hidden" name="ba_code" value="{{ substr($q['id_ba_afd_blok'], 0, 4) }}">
<input type="hidden" name="ba_name" value="{{$q['nama_ba']}}">
<input type="hidden" name="afd_code" value="{{ substr($q['id_ba_afd_blok'], 4, 1) }}">
<input type="hidden" name="block_code" value="{{$q['id_blok']}}">
<input type="hidden" name="block_name" value="{{$q['blok_name']}}">
<input type="hidden" name="no_tph" value="{{$q['no_tph']}}">
<input type="hidden" name="no_bcc" value="{{$q['no_bcc']}}">
<input type="hidden" name="jumlah_ebcc_validated" value="{{$no_validasi}}">
<input type="hidden" name="last_update" value="{{ date('Y-M-d') }}">
	<tr>
		<td  rowspan="7" width="45%">
			<div style="position:absolute;z-index: 1000">
			<?php	$img = str_replace("/","",$q['picture_name']);
			 ?>
				<img src="http://tap-motion.tap-agri.com/ebcc/array/uploads/{{$img}}" >
			</div>
		</td>
		<td><h4>Validasi ke {{$no_validasi}} dari {{$target}}</h4></td>
	</tr>
	<tr>
		<td colspan="8">Kriteria Buah Berdasarkan Foto eBCC</td>
	</tr>
	<tr>
		<td>
			<table class="table table-bordered">
  				<thead style="background-color:#dadbd5">
				<tr>
					<td>BM<input type="hidden" name="jjg_ebcc_bm" value="{{$q['ebcc_jml_bm']}}"></td>
					<td>BK<input type="hidden" name="jjg_ebcc_bk" value="{{$q['ebcc_jml_bk']}}"></td>
					<td>MS<input type="hidden" name="jjg_ebcc_ms" value="{{$q['ebcc_jml_ms']}}"></td>
					<td>OR<input type="hidden" name="jjg_ebcc_or" value="{{$q['ebcc_jml_or']}}"></td>
					<td>BB<input type="hidden" name="jjg_ebcc_bb" value="{{$q['ebcc_jml_bb']}}"></td>
					<td>JK<input type="hidden" name="jjg_ebcc_jk" value="{{$q['ebcc_jml_jk']}}"></td>
					<td>BA<input type="hidden" name="jjg_ebcc_ba" value="{{$q['ebcc_jml_ba']}}"></td>
					<td>Total Janjang Panen<input type="hidden" name="jjg_ebcc_total" value="{{$q['jjg_panen']}}"></td>
				</tr>
				</thead>
 				<tbody>
				<tr>
					<td><input type="number" min=0 class="form-control" required name="jjg_validate_bm" id="bm" onkeyup="sum()"></td>
					<td><input type="number" min=0 class="form-control" required name="jjg_validate_bk" id="bk" onkeyup="sum()"></td>
					<td><input type="number" min=0 class="form-control" required name="jjg_validate_ms" id="ms" onkeyup="sum()"></td>
					<td><input type="number" min=0 class="form-control" required name="jjg_validate_or" id="or" onkeyup="sum()"></td>
					<td><input type="number" min=0 class="form-control" required name="jjg_validate_bb" id="bb" onkeyup="sum()"></td>
					<td><input type="number" min=0 class="form-control" required name="jjg_validate_jk" id="jk" onkeyup="sum()"></td>
					<td><input type="number" min=0 class="form-control" required name="jjg_validate_ba" id="ba" onkeyup="sum()"></td>
					<td><input type="text" min=0 class="form-control" required  readonly="readonly" name="jjg_validate_total" id="total_jjg"></td>
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
						<button class="btn btn-radio btnselect" name="kondisi_foto" id="btnr">
							<input type="radio" value="Foto Tidak Muncul">Foto Tidak Muncul</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" name="kondisi_foto" id="btnr">
							<input type="radio" value="Blur">Blur</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" name="kondisi_foto" id="btnr">
							<input type="radio" value="Jauh">Jauh</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" name="kondisi_foto" id="btnr">
							<input type="radio" value="Gambar Terpotong">Gambar Terpotong</button>
						</div>
				</div>
				<br>
				<div class="row">
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" name="kondisi_foto" id="btnr">
							<input type="radio" value="Gelap/ Tidak Terlihat">Gelap/ Tidak Terlihat</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" name="kondisi_foto" id="btnr">
							<input type="radio" value="Angle Pengambilan Gambar">Angle Pengambilan Gambar</button>
					</div>
					<div class="col-md-3">
						<button class="btn btn-radio btnselect" name="kondisi_foto" id="btnr">
							<input type="radio" value="Penyusunan TBS tidak sesuai SOP">Penyusunan TBS tidak sesuai SOP</button>
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
						<a class="btn btn-block btn-dark" href={{URL::to('/listvalidasi')}}>KEMBALI</a>
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

	function sum() {
       var bm = document.getElementById('bm').value;
       var bk = document.getElementById('bk').value;
       var ms = document.getElementById('ms').value;
       var or = document.getElementById('or').value;
       var bb = document.getElementById('bb').value;
       var jk = document.getElementById('jk').value;
       var ba = document.getElementById('ba').value;
       if (bm == "")
           bm = 0;
       if (bk == "")
           bk = 0;
       if (ms == "")
           ms = 0;
       if (or == "")
           or = 0;
       if (bb == "")
           bb = 0;
       if (jk == "")
           jk = 0;
       if (ba == "")
           ba = 0;

       var result = parseInt(bm) + parseInt(bk) + parseInt(ms) + parseInt(or) + parseInt(bb) + parseInt(jk) + parseInt(ba);
       if (!isNaN(result)) {
           document.getElementById('total_jjg').value = result;
       }
   }

</script>
<!-- 
	ID_VALIDASI
	TANGGAL_EBCC
	NIK_KRANI_BUAH
	NAMA_KRANI_BUAH
	BA_CODE
	BA_NAME
	AFD_CODE
	BLOCK_CODE
	BLOCK_NAME
	NO_TPH
	NO_BCC
	JJG_EBCC_BM
	JJG_EBCC_BK
	JJG_EBCC_MS
	JJG_EBCC_OR
	JJG_EBCC_BB
	JJG_EBCC_JK
	JJG_EBCC_BA
	JJG_EBCC_TOTAL
	JJG_EBCC_1
	JJG_EBCC_2
	JJG_VALIDATE_BM
	JJG_VALIDATE_BK
	JJG_VALIDATE_MS
	JJG_VALIDATE_OR
	JJG_VALIDATE_BB
	JJG_VALIDATE_JK
	JJG_VALIDATE_BA
	JJG_VALIDATE_TOTAL
	JJG_VALIDATE_1
	JJG_VALIDATE_2
	KONDISI_FOTO
	INSERT_TIME
	INSERT_USER
	INSERT_USER_FULLNAME
	INSERT_USER_USERROLE
 -->

@endsection