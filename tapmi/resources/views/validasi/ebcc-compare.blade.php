<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<title>Preview - LAPORAN EBCC vs KEPALA KEBUN</title>
	<style type="text/css">
		.north {
		transform:rotate(0deg);
		-ms-transform:rotate(0deg); /* IE 9 */
		-webkit-transform:rotate(0deg); /* Safari and Chrome */
		}
		.west {
		transform:rotate(90deg);
		-ms-transform:rotate(90deg); /* IE 9 */
		-webkit-transform:rotate(90deg); /* Safari and Chrome */
		}
		.south {
		transform:rotate(180deg);
		-ms-transform:rotate(180deg); /* IE 9 */
		-webkit-transform:rotate(180deg); /* Safari and Chrome */
			
		}
		.east {
		transform:rotate(270deg);
		-ms-transform:rotate(270deg); /* IE 9 */
		-webkit-transform:rotate(270deg); /* Safari and Chrome */
		}
		.disable-select {
			user-select: none;
			-webkit-user-select: none;
			-khtml-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
		}
		img.rotate {
			transform: rotate(90deg) translateY(-100%);
			transform-origin:top left;
		}
	</style>
</head>
<!-- <body onload="return rotate_image();"> -->
<body>
@foreach ($data as $key => $dt)
	<div class="container disable-select" id="capture" oncontextmenu="return false;">
		<br />
		<h4 class="text-center">HASIL VALIDASI BCC OLEH KEPALA KEBUN</h4>
		<p class="text-center">PT: {{ $dt['nama_pt'] }}; BISNIS AREA: {{ $dt['bisnis_area'] }}; AFD: {{ $dt['afd'] }}; BLOCK: {{ $dt['blok'].'/'.$dt['nama_blok'] }}; TPH: {{ $dt['tph'] }}</p>

		<div class="row" style="margin-top: 20px;">
		<div class="col-md-2"></div>
		<div class="col-md-8">
			<div class="card">
				<div class="card-body" style="background-position: center center; background-repeat: no-repeat;overflow: hidden; padding-left: 30px;">
					<div style="position:absolute;z-index: 1000">
						<input id="input1" type="image" src="http://inspectiondev.tap-agri.com/storage/rotate_45.png" >
					</div>	
					<!-- <img id="sampling_ebcc_img_jjg" onerror="this.onerror=null;this.src='https://webhostingmedia.net/wp-content/uploads/2018/01/http-error-404-not-found.png'"  src="http://10.20.1.59/ebcc/array/uploads/{{$dt['picture_name']}}" width="655px" height="496px" class="rounded mx-auto d-block north"> -->
					<img id="sampling_ebcc_img_jjg" onerror="this.onerror=null;this.src='http://inspectiondev.tap-agri.com/storage/notfound.jpg'"  src="http://tap-motion.tap-agri.com/ebcc/array/uploads/{{$dt['picture_name']}}" width="655px" height="496px" class="rounded mx-auto d-block north">	
				</div>
			</div>
		</div>
		<div class="col-md-2"></div>
		<div class="row" style="margin-top: 20px;">
			<div class="col-md-6">
				<div class="card">
					<div class="card-header text-center bg-warning">
						<b>EBCC</b>
						</div>
					<div class="card-body" style="background-position: center center; background-repeat: no-repeat;overflow: hidden; ">
							<br />
						<table class="table table-bordered" style="font-weight: bold;">
							<tr style="font-size:14px;">
								<td class="text-center">Mentah (jjg)</td>
								<!-- <td class="text-center">BK (jjg)</td> -->
								<td class="text-center">Masak (jjg)</td>
								<td class="text-center">Terlalu Masak (jjg)</td>
								<td class="text-center">Busuk (jjg)</td>
								<td class="text-center">Janjang Kosong (jjg)</td>
								<!-- <td class="text-center">BA (jjg)</td> -->
								<td class="text-center">Total<br />Janjang<br />Panen</td>
							</tr>
							<tr>
								<td class="text-center" >{{ $dt['ebcc_jml_bm'] }}</td>
								<!-- <td class="text-center" style="color:{{ ( $dt['ebcc_jml_bk'] == $dt['jjg_validate_bk'] ? 'green' : 'red' ) }};">{{ $dt['ebcc_jml_bk'] }}</td> -->
								<td class="text-center" >{{ $dt['ebcc_jml_ms'] }}</td>
								<td class="text-center" >{{ $dt['ebcc_jml_or'] }}</td>
								<td class="text-center" >{{ $dt['ebcc_jml_bb'] }}</td>
								<td class="text-center" >{{ $dt['ebcc_jml_jk'] }}</td>
								<!-- <td class="text-center" style="color:{{ ( $dt['ebcc_jml_ba'] == $dt['jjg_validate_ba'] ? 'green' : 'red' ) }};">{{ $dt['ebcc_jml_ba'] }}</td> -->
								<td class="text-center" style="background-color:{{ ( $dt['ebcc_total'] == $dt['jjg_validate_total'] ? 'green' : 'red' ) }}; color:white;">{{ $dt['ebcc_total'] }}</td>
							</tr>
						</table>
						<div class="row">
							<div class="col-md-8">
								<table cellpadding="2px;" style="font-size: 12px;">
									<tr>
										<td width="40%">NIK</td>
										<td width="5%">:</td>
										<td width="55%">{{ $dt['nik_kerani_buah'] }}</td>
									</tr>
									<tr>
										<td>Nama Lengkap</td>
										<td>:</td>
										<td>{{ $dt['nama_kerani_buah'] }}</td>
									</tr>
									<tr>
										<td>Jabatan</td>
										<td>:</td>
										<td>KRANI BUAH</td>
									</tr>
									<tr>
										<td>Waktu Pencatatan</td>
										<td>:</td>
										<td>{{ date( 'd-M-Y', strtotime( $dt['tanggal_ebcc'] ) ) }}</td>
									</tr>
									<tr>
										<td>Kode EBCC</td>
										<td>:</td>
										<td>{{ $dt['no_bcc'] }}</td>
									</tr>
								</table>
							</div>
							<div class="col-md-4">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card">
					<div class="card-header text-center bg-success" style="color:white !important">
						<b>KEPALA KEBUN</b>
					</div>
					<div class="card-body" style="background-position: center center; background-repeat: no-repeat;overflow: hidden;">
						<br />
						<table class="table table-bordered" style="font-weight: bold;">
								<tr style="font-size:14px;">
									<!-- <td class="text-center">Mentah (jjg)</td> -->
									<!-- <td class="text-center">BK (jjg)</td> -->
									<!-- <td class="text-center">Masak (jjg)</td> -->
									<!-- <td class="text-center">Terlalu Masak (jjg)</td> -->
									<!-- <td class="text-center">Busuk (jjg)</td> -->
									<!-- <td class="text-center">Janjang Kosong (jjg)</td> -->
									<!-- <td class="text-center">BA (jjg)</td> -->
									<td class="text-center">Total Janjang<br />Panen<br /><br /></td>
								</tr>
								<tr>
								<!-- <td class="text-center" style="color:{{ ( $dt['ebcc_jml_bm'] == $dt['jjg_validate_bm'] ? 'green' : 'red' ) }};">{{ $dt['jjg_validate_bm'] }}</td> -->
								<!-- <td class="text-center" style="color:{{ ( $dt['ebcc_jml_bk'] == $dt['jjg_validate_bk'] ? 'green' : 'red' ) }};">{{ $dt['jjg_validate_bk'] }}</td> -->
								<!-- <td class="text-center" style="color:{{ ( $dt['ebcc_jml_ms'] == $dt['jjg_validate_ms'] ? 'green' : 'red' ) }};">{{ $dt['jjg_validate_ms'] }}</td> -->
								<!-- <td class="text-center" style="color:{{ ( $dt['ebcc_jml_or'] == $dt['jjg_validate_or'] ? 'green' : 'red' ) }};">{{ $dt['jjg_validate_or'] }}</td> -->
								<!-- <td class="text-center" style="color:{{ ( $dt['ebcc_jml_bb'] == $dt['jjg_validate_bb'] ? 'green' : 'red' ) }};">{{ $dt['jjg_validate_bb'] }}</td> -->
								<!-- <td class="text-center" style="color:{{ ( $dt['ebcc_jml_jk'] == $dt['jjg_validate_jk'] ? 'green' : 'red' ) }};">{{ $dt['jjg_validate_jk'] }}</td> -->
								<!-- <td class="text-center" style="color:{{ ( $dt['ebcc_jml_ba'] == $dt['jjg_validate_ba'] ? 'green' : 'red' ) }};">{{ $dt['jjg_validate_ba'] }}</td> -->
								<td class="text-center" style="background-color:{{ ( $dt['ebcc_total'] == $dt['jjg_validate_total'] ? 'green' : ( $dt['jjg_validate_total'] == '0' ? 'grey' : 'red' ) ) }}; color:white;">{{ ( $dt['jjg_validate_total'] == '0' ? '-' : $dt['jjg_validate_total'] )  }}</td>
								</tr>
							</table>
							<div class="row">
								<div class="col-md-8">
									<table cellpadding="2px;" style="font-size: 12px;">
										<tr>
											<td width="40%">NIK</td>
											<td width="5%">:</td>
											<td width="55%">{{ $dt['nik_pembuat'] }}</td>
										</tr>
										<tr>
											<td>Nama Lengkap</td>
											<td>:</td>
											<td>{{ $dt['nama_pembuat'] }}</td>
										</tr>
										<tr>
											<td>Jabatan</td>
											<td>:</td>
											<td>KEPALA KEBUN</td>
										</tr>
										<tr>
											<td>Waktu Validasi</td>
											<td>:</td>
											<td>{{ date( 'd-M-Y H:i', strtotime( $dt['tanggal_validasi'] ) ) }}</td>
										</tr>
										@if(strtoupper($dt['kondisi_foto']) != "BISA DIVALIDASI")
											<tr>
												<td valign="top">Kondisi Foto</td>
												<td valign="top">:</td>
												<td valign="top">{{ $dt['kondisi_foto'] }}</td>
											</tr>
										@endif
										<tr>
											<td></td>
											<td></td>
											<td></td>
										</tr>
									</table>
								</div>
								<div class="col-md-4">
								</div>
							</div>
					</div>
				</div>
			</div>
		</div>
		<br />
	</div>
@endforeach
	<!--footer>
		<br />
		<center>
			<button id="download-jpg" class="btn btn-primary"><i class="fa fa-cloud-download"></i> Download as PNG</button>
		</center>
	</footer-->
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.10.1.min.js" integrity="sha256-SDf34fFWX/ZnUozXXEH0AeB+Ip3hvRsjLwp6QNTEb3k="  crossorigin="anonymous" ></script>
	<script type="text/javascript">
		$('#input1').click(function(){
			var img = $('#sampling_ebcc_img_jjg');
			if(img.hasClass('north')){
				img.attr('class','west');
			}else if(img.hasClass('west')){
				img.attr('class','south');
			}else if(img.hasClass('south')){
				img.attr('class','east');
			}else if(img.hasClass('east')){
				img.attr('class','north');
			}
		});
		
		// $('#input2').click(function(){
		// 	var img = $('#ebcc');
		// 	if(img.hasClass('rounded mx-auto d-block north')){
		// 		img.attr('class',' rounded mx-auto d-block west');
		// 	}else if(img.hasClass('rounded mx-auto d-block west')){
		// 		img.attr('class','rounded mx-auto d-block south');
		// 	}else if(img.hasClass('rounded mx-auto d-block south')){
		// 		img.attr('class','rounded mx-auto d-block east');
		// 	}else if(img.hasClass('rounded mx-auto d-block east')){
		// 		img.attr('class','rounded mx-auto d-block north');
		// 	}
		// });
		
		// function rotate_image() {
		// 	// Rotasi Image Selfie
		// 	var sampling_ebcc_img_selfie = document.getElementById( 'sampling_ebcc_img_selfie' );
		// 	var sampling_ebcc_img_selfie_width = sampling_ebcc_img_selfie.clientWidth;
		// 	var sampling_ebcc_img_selfie_height = sampling_ebcc_img_selfie.clientHeight;
	
		// 	if ( parseInt( sampling_ebcc_img_selfie_height ) < parseInt( sampling_ebcc_img_selfie_width ) ) {
		// 		$( "#sampling_ebcc_img_selfie" ).addClass( 'rotate' );
		// 	}

		// 	// Rotasi Image Janjang
		// 	var sampling_ebcc_img_jjg = document.getElementById( 'sampling_ebcc_img_jjg' );
		// 	var sampling_ebcc_img_jjg_width = sampling_ebcc_img_jjg.clientWidth;
		// 	var sampling_ebcc_img_jjg_height = sampling_ebcc_img_jjg.clientHeight;
			
		// 	if ( parseInt( sampling_ebcc_img_jjg_height ) > parseInt( sampling_ebcc_img_jjg_width ) ) {
		// 		//$( "#sampling_ebcc_img_jjg" ).addClass( 'rotate' );
		// 	}
		// }

		// function saveAs( uri, filename ) {
		// 	var link = document.createElement( 'a' );
		// 	if ( typeof link.download === 'string' ) {
		// 		link.href = uri;
		// 		link.download = filename;
		// 		document.body.appendChild( link );
		// 		link.click();
		// 		document.body.removeChild( link );
		// 	} 
		// 	else {
		// 		window.open( uri );
		// 	}
		// }

		// $( document ).ready( function() {
		// 	$( "#download-jpg" ).click( function() {

		// 		html2canvas( document.querySelector( "#capture" ) ).then( canvas => {
		// 			var filename = "{{ $dt['nama_pt'].' ('.$dt['bisnis_area'].$dt['afd'].$dt['blok'].')-'.$dt['no_bcc'].'-'.$dt['tph'].'-'.date( 'Ymd', strtotime( $dt['tanggal_ebcc'] ) ).'-'.$dt['nik_kerani_buah'].'-'.$dt['nama_kerani_buah'] }}";
		// 			saveAs( canvas.toDataURL(), filename + '.png' );
		// 		}, { 
		// 			allowTaint: true,
		// 			width: 1200,
		// 			height: 1200
		// 		} );
		// 	} );

		// 	var images = {
		// 		'sampling_ebcc': {
		// 			'img_selfie': $( "#sampling_ebcc_img_selfie" )
		// 		}
		// 	};
		// } );
	</script>
</body>
</html>