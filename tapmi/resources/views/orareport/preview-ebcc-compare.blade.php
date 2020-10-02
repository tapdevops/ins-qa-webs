<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<title>Preview - LAPORAN SAMPLING EBCC vs EBCC</title>
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
<body onload="return rotate_image();">
	<div class="container disable-select" id="capture" oncontextmenu="return false;">
		<br />
		<h4 class="text-center">LAPORAN SAMPLING EBCC vs EBCC</h4>
		<p class="text-center">PT: {{ $data['val_est_name'] }}; BISNIS AREA: {{ $data['val_werks'] }}; AFD: {{ $data['val_afd_code'] }}; BLOCK: {{ $data['val_block_code'].'/'.$data['val_block_name'] }}; TPH: {{ $data['val_tph_code'] }}</p>

		<div class="row" style="margin-top: 20px;">
			<div class="col-md-6">
				<div class="card">
					<div class="card-header text-center bg-warning">
						<b>SAMPLING EBCC</b>
					</div>
					<div class="card-body" style="background-position: center center; background-repeat: no-repeat;overflow: hidden; ">
						<div style="position:absolute;z-index: 1000">
						<input id="input1" type="image" src="http://inspectiondev.tap-agri.com/storage/rotate_45.png" >
						</div>
						<img id="sampling_ebcc_img_jjg" src="{{ $data['val_image_janjang'] }}" width="496px" height="600px" class="rounded mx-auto d-block north">
						<br />
						<table class="table table-bordered" style="font-weight: bold;">
							<tr style="font-size:14px;">
								<td class="text-center">BM (jjg)</td>
								<td class="text-center">BK (jjg)</td>
								<td class="text-center">MS (jjg)</td>
								<td class="text-center">OR (jjg)</td>
								<td class="text-center">BB (jjg)</td>
								<td class="text-center">JK (jjg)</td>
								<td class="text-center">BA (jjg)</td>
								<td class="text-center">Total<br />Janjang<br />Panen</td>
							</tr>
							<tr>
								<td class="text-center" style="color:{{ ( $data['ebcc_jml_bm'] == $data['val_jml_bm'] ? 'green' : 'red' ) }};">{{ $data['val_jml_bm'] }}</td>
								<td class="text-center" style="color:{{ ( $data['ebcc_jml_bk'] == $data['val_jml_bk'] ? 'green' : 'red' ) }};">{{ $data['val_jml_bk'] }}</td>
								<td class="text-center" style="color:{{ ( $data['ebcc_jml_ms'] == $data['val_jml_ms'] ? 'green' : 'red' ) }};">{{ $data['val_jml_ms'] }}</td>
								<td class="text-center" style="color:{{ ( $data['ebcc_jml_or'] == $data['val_jml_or'] ? 'green' : 'red' ) }};">{{ $data['val_jml_or'] }}</td>
								<td class="text-center" style="color:{{ ( $data['ebcc_jml_bb'] == $data['val_jml_bb'] ? 'green' : 'red' ) }};">{{ $data['val_jml_bb'] }}</td>
								<td class="text-center" style="color:{{ ( $data['ebcc_jml_jk'] == $data['val_jml_jk'] ? 'green' : 'red' ) }};">{{ $data['val_jml_jk'] }}</td>
								<td class="text-center" style="color:{{ ( $data['ebcc_jml_ba'] == $data['val_jml_ba'] ? 'green' : 'red' ) }};">{{ $data['val_jml_ba'] }}</td>
								<td class="text-center" style="color:{{ ( $data['ebcc_jjg_panen'] == $data['val_jjg_panen'] ? 'green' : 'red' ) }};">{{ $data['val_jjg_panen'] }}</td>
							</tr>
						</table>
						<div class="row">
							<div class="col-md-8">
								<table cellpadding="2px;" style="font-size: 12px;">
									<tr>
										<td width="40%">NIK</td>
										<td width="5%">:</td>
										<td width="55%">{{ $data['val_nik_validator'] }}</td>
									</tr>
									<tr>
										<td>Nama Lengkap</td>
										<td>:</td>
										<td>{{ $data['val_nama_validator'] }}</td>
									</tr>
									<tr>
										<td>Jabatan</td>
										<td>:</td>
										<td>{{ $data['val_jabatan_validator'] }}</td>
									</tr>
									<tr>
										<td>Waktu Pencatatan</td>
										<td>:</td>
										<td>{{ $data['val_date_time'] }}</td>
									</tr>
									<tr>
										<td>Status Scan QR Code</td>
										<td>:</td>
										<td>{{ $data['val_status_tph_scan'].' '.$data['val_alasan_manual'] }}</td>
									</tr>
								</table>
							</div>
							<div class="col-md-4">
								<img id="sampling_ebcc_img_selfie" src="{{ $data['val_image_selfie'] }}" width="100%;">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card">
					<div class="card-header text-center bg-success" style="color:white !important">
						<b>EBCC</b>
					</div>
					<div class="card-body" style="background-position: center center; background-repeat: no-repeat;overflow: hidden;">
					<div style="position:absolute;z-index: 1000">
						<input id="input2" type="image" src="http://inspectiondev.tap-agri.com/storage/rotate_45.png" >
					</div>
						@if ( $data['ebcc_no_bcc'] == '' )
							<img id="ebcc" src="{{ url( 'assets/notfound.jpg' ) }}" width="496px" height="600px" class="rounded mx-auto d-block north">
							<h3 class="text-center">EBCC tidak ditemukan</h3><br />
						@else
							<img id="ebcc" src="{{ $data['ebcc_picture_name'] }}" width="496px" height="600px" class="rounded mx-auto d-block north"><br />
							<table class="table table-bordered" style="font-weight: bold;">
								<tr style="font-size:14px;">
									<td class="text-center">BM (jjg)</td>
									<td class="text-center">BK (jjg)</td>
									<td class="text-center">MS (jjg)</td>
									<td class="text-center">OR (jjg)</td>
									<td class="text-center">BB (jjg)</td>
									<td class="text-center">JK (jjg)</td>
									<td class="text-center">BA (jjg)</td>
									<td class="text-center">Total<br />Janjang<br />Panen</td>
								</tr>
								<tr>
									<td class="text-center" style="color:{{ ( $data['ebcc_jml_bm'] == $data['val_jml_bm'] ? 'green' : 'red' ) }};">{{ $data['ebcc_jml_bm'] }}</td>
									<td class="text-center" style="color:{{ ( $data['ebcc_jml_bk'] == $data['val_jml_bk'] ? 'green' : 'red' ) }};">{{ $data['ebcc_jml_bk'] }}</td>
									<td class="text-center" style="color:{{ ( $data['ebcc_jml_ms'] == $data['val_jml_ms'] ? 'green' : 'red' ) }};">{{ $data['ebcc_jml_ms'] }}</td>
									<td class="text-center" style="color:{{ ( $data['ebcc_jml_or'] == $data['val_jml_or'] ? 'green' : 'red' ) }};">{{ $data['ebcc_jml_or'] }}</td>
									<td class="text-center" style="color:{{ ( $data['ebcc_jml_bb'] == $data['val_jml_bb'] ? 'green' : 'red' ) }};">{{ $data['ebcc_jml_bb'] }}</td>
									<td class="text-center" style="color:{{ ( $data['ebcc_jml_jk'] == $data['val_jml_jk'] ? 'green' : 'red' ) }};">{{ $data['ebcc_jml_jk'] }}</td>
									<td class="text-center" style="color:{{ ( $data['ebcc_jml_ba'] == $data['val_jml_ba'] ? 'green' : 'red' ) }};">{{ $data['ebcc_jml_ba'] }}</td>
									<td class="text-center" style="color:{{ ( $data['ebcc_jjg_panen'] == $data['val_jjg_panen'] ? 'green' : 'red' ) }};">{{ $data['ebcc_jjg_panen'] }}</td>
								</tr>
							</table>
							<div class="row">
								<div class="col-md-8">
									<table cellpadding="2px;" style="font-size: 12px;">
										<tr>
											<td width="40%">NIK</td>
											<td width="5%">:</td>
											<td width="55%">{{ $data['ebcc_nik_kerani_buah'] }}</td>
										</tr>
										<tr>
											<td>Nama Lengkap</td>
											<td>:</td>
											<td>{{ $data['ebcc_nama_kerani_buah'] }}</td>
										</tr>
										<tr>
											<td>Jabatan</td>
											<td>:</td>
											<td></td>
										</tr>
										<tr>
											<td>Waktu Pencatatan</td>
											<td>:</td>
											<td>{{ date( 'Y-m-d', strtotime( $data['val_date_time'] ) ) }}</td>
										</tr>
										<tr>
											<td>Status Scan QR Code</td>
											<td>:</td>
											<td>{{ $data['ebcc_status_tph'].' '.$data['ebcc_keterangan_qrcode'] }}</td>
										</tr>
									</table>
								</div>
								<div class="col-md-4">
									<img src="{{ url( 'assets/user.jpg' ) }}" width="100%;">
								</div>
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
		<br />
	</div>
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
		
		$('#input2').click(function(){
			var img = $('#ebcc');
			if(img.hasClass('rounded mx-auto d-block north')){
				img.attr('class',' rounded mx-auto d-block west');
			}else if(img.hasClass('rounded mx-auto d-block west')){
				img.attr('class','rounded mx-auto d-block south');
			}else if(img.hasClass('rounded mx-auto d-block south')){
				img.attr('class','rounded mx-auto d-block east');
			}else if(img.hasClass('rounded mx-auto d-block east')){
				img.attr('class','rounded mx-auto d-block north');
			}
		});
		
		function rotate_image() {
			// Rotasi Image Selfie
			var sampling_ebcc_img_selfie = document.getElementById( 'sampling_ebcc_img_selfie' );
			var sampling_ebcc_img_selfie_width = sampling_ebcc_img_selfie.clientWidth;
			var sampling_ebcc_img_selfie_height = sampling_ebcc_img_selfie.clientHeight;
	
			if ( parseInt( sampling_ebcc_img_selfie_height ) < parseInt( sampling_ebcc_img_selfie_width ) ) {
				$( "#sampling_ebcc_img_selfie" ).addClass( 'rotate' );
			}

			// Rotasi Image Janjang
			var sampling_ebcc_img_jjg = document.getElementById( 'sampling_ebcc_img_jjg' );
			var sampling_ebcc_img_jjg_width = sampling_ebcc_img_jjg.clientWidth;
			var sampling_ebcc_img_jjg_height = sampling_ebcc_img_jjg.clientHeight;
			
			if ( parseInt( sampling_ebcc_img_jjg_height ) > parseInt( sampling_ebcc_img_jjg_width ) ) {
				//$( "#sampling_ebcc_img_jjg" ).addClass( 'rotate' );
			}
		}

		function saveAs( uri, filename ) {
			var link = document.createElement( 'a' );
			if ( typeof link.download === 'string' ) {
				link.href = uri;
				link.download = filename;
				document.body.appendChild( link );
				link.click();
				document.body.removeChild( link );
			} 
			else {
				window.open( uri );
			}
		}

		$( document ).ready( function() {
			$( "#download-jpg" ).click( function() {

				html2canvas( document.querySelector( "#capture" ) ).then( canvas => {
					var filename = "{{ $data['val_est_name'].' ('.$data['val_werks'].$data['val_afd_code'].$data['val_block_code'].')-'.$data['val_ebcc_code'].'-'.$data['val_tph_code'].'-'.date( 'Ymd', strtotime( $data['val_date_time'] ) ).'-'.$data['val_nik_validator'].'-'.$data['val_nama_validator'] }}";
					saveAs( canvas.toDataURL(), filename + '.png' );
				}, { 
					allowTaint: true,
					width: 1200,
					height: 1200
				} );
			} );

			var images = {
				'sampling_ebcc': {
					'img_selfie': $( "#sampling_ebcc_img_selfie" )
				}
			};
		} );
	</script>
</body>
</html>