<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<title>Preview - FINDING</title>
	<style type="text/css">
		
		#table1 { 
			width: 100%;
			padding: 0px 8px 0px 8px;
			background-color: #ffffff; 
			filter: alpha(opacity=40); 
			opacity: 0.95;
			border:1px red solid;
			border-spacing: 5px;
			border-collapse: separate;
		}
		
		.containertable {
			margin: 3px;
			border: 0px solid blue;
			height: auto;
			display: table;
			table-layout: fixed;
			width: 100%;
		}
		.itemtable {
			border: 0px solid red;
			display: table-cell;
		}
		.itemtable button{
			background:#e6f5ea;
			padding:0;
			border:1;
			width:50px;
			height:auto;
		}
		
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
		
		body {font-family: Arial, Helvetica, sans-serif;}

		img {
		  border-radius: 5px;
		  cursor: pointer;
		  transition: 0.3s;
		}

		img:hover {opacity: 0.7;}

		/* The Modal (background) */
		.modal {
		  display: none; /* Hidden by default */
		  position: fixed; /* Stay in place */
		  z-index: 1; /* Sit on top */
		  padding-top: 100px; /* Location of the box */
		  left: 0;
		  top: 0;
		  width: 100%; /* Full width */
		  height: 100%; /* Full height */
		  overflow: auto; /* Enable scroll if needed */
		  background-color: rgb(0,0,0); /* Fallback color */
		  background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
		}

		/* Modal Content (image) */
		.modal-content {
		  margin: auto;
		  display: block;
		  width: 80%;
		  max-width: 700px;
		}

		/* Caption of Modal Image */
		#caption {
		  margin: auto;
		  display: block;
		  width: 80%;
		  max-width: 700px;
		  text-align: center;
		  color: #ccc;
		  padding: 10px 0;
		  height: 150px;
		}

		/* Add Animation */
		.modal-content, #caption {  
		  -webkit-animation-name: zoom;
		  -webkit-animation-duration: 0.6s;
		  animation-name: zoom;
		  animation-duration: 0.6s;
		}

		@-webkit-keyframes zoom {
		  from {-webkit-transform:scale(0)} 
		  to {-webkit-transform:scale(1)}
		}

		@keyframes zoom {
		  from {transform:scale(0)} 
		  to {transform:scale(1)}
		}

		/* The Close Button */
		.close {
		  position: absolute;
		  top: 15px;
		  right: 35px;
		  color: #f1f1f1;
		  font-size: 40px;
		  font-weight: bold;
		  transition: 0.3s;
		}

		.close:hover,
		.close:focus {
		  color: #bbb;
		  text-decoration: none;
		  cursor: pointer;
		}

		/* 100% Image Width on Smaller Screens */
		@media only screen and (max-width: 700px){
		  .modal-content {
			width: 100%;
		  }
		}
	</style>
</head>
<body>
	<div class="container disable-select" id="capture" oncontextmenu="return false - ">
		<br />
		<h4 class="text-center">FINDING No. {{ $data['finding_code'] }} </h4>
		<p class="text-center"><b>PT: {{ $data['est_name'] }} | Bisnis Area: {{ $data['werks'] }} | Afd: {{ $data['afd_code'] }} | Block: {{ $data['block_code'].'/'.$data['block_name'] }} | Maturity: {{$data['maturity_status']}}</b></p>
		<table id="table1">
			<tr>
				<td width="15%">Tanggal Temuan</td>
				<td>:</td>
				<td>{{$data['tanggal_temuan']}}</td>
				<td></td>
				<td></td>
				<td width="13%">PIC</td>
				<td>:</td>
				<td>{{$data['pic_employee_nik']}} - {{$data['pic_employee_fullname']}}</td>
			</tr>
			<tr>
				<td>Pembuat</td>
				<td>:</td>
				<td>{{$data['creator_employee_nik']}} - {{$data['creator_employee_fullname']}}</td>
				<td></td>
				<td></td>
				<td>Batas Waktu</td>
				<td>:</td>
				<td>{{$data['due_date']}}</td>
			</tr>
			<tr>
				<td>Kategori Temuan</td>
				<td>:</td>
				<td>{{$data['category_name']}}</td>
				<td></td>
				<td></td>
				<td>Status Temuan</td>
				<td>:</td>
				<td>{{$data['status']}} - {{$data['progress']}}%</td>
			</tr>
			<tr>
				<td>Prioritas</td>
				<td>:</td>
				<td>{{$data['finding_priority']}}</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td valign="top">Keterangan</td>
				<td valign="top">:</td>
				<td colspan="6">{{$data['finding_desc']}}</td>
			</tr>
			<tr>
				<td colspan="8">
					<table id="table2">
						<tr style="background: linear-gradient(to bottom, #ffff66 0%, #ff9933 100%);">
							<td></td>
							<td colspan = "3" valign="middle" align="center">
								<div class="containertable">
									<div class="itemtable"><button onclick="PrevBefore();">Prev</button></div>
									<div class="itemtable"><b>BEFORE 
									@isset($data['image']['BEFORE'][0])
										(<label id="index_before">1</label>/{{ count($data['image']['BEFORE']) }}) 
									@endisset
									</b>
									</div>
									<div class="itemtable"><button onclick="NextBefore();">Next</button></div>
								</div>
							</td>
							<td></td>
							<td colspan = "3" valign="middle" align="center">
								<div class="containertable">
									<div class="itemtable"><button onclick="PrevAfter();">Prev</button></div>
									<div class="itemtable"><b>AFTER
									@isset($data['image']['AFTER'][0])        
										 (<label id="index_after">1</label>/{{ count($data['image']['AFTER']) }})
									@endisset
									</b>
									</div>
									<div class="itemtable"><button onclick="NextAfter();">Next</button></div>
								</div>
							</td>
							<td></td>
						</tr>
						<tr>
							<td colspan="4" valign="middle" align="center" width="496px" height="600px">
							<div style="position:absolute;z-index: 1000">
							<input id="input1" type="image" src="/storage/rotate_45.png" >
							</div>
							<img id="image_before" width="400px"
							@isset($data['image']['BEFORE'][0])
								src={{ $data['image']['BEFORE'][0] }}
							@endisset
							class="north"></td>
							<td ></td>
							<td colspan="4" valign="middle" align="center" width="496px" height="600px">
							<div style="position:absolute;z-index: 1000">
							<input id="input2" type="image" src="/storage/rotate_45.png" >
							</div>
							<img id="image_after" width="400px"
							@isset($data['image']['AFTER'][0])
								src={{ $data['image']['AFTER'][0] }}
							@endisset
							class="north"></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>	
		
	</div>
	<!--<div id="textHere"></div>-->
	
	<!-- The Modal -->
	<div id="myModal" class="modal">
	  <span class="close">&times;</span>
	  <img class="modal-content" id="img01">
	  <div id="caption"></div>
	</div>
	
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.10.1.min.js" integrity="sha256-SDf34fFWX/ZnUozXXEH0AeB+Ip3hvRsjLwp6QNTEb3k="  crossorigin="anonymous" ></script>
	<script type="text/javascript"> 
	
		$('#input1').click(function(){
				var img = $('#image_before');
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
				var img = $('#image_after');
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
		
		
		@if(isset($data['image']['BEFORE']))
			var text = {!! json_encode($data['image']['BEFORE'], JSON_HEX_TAG) !!};
		@else
			var text = [];
		@endisset
		
		@if(isset($data['image']['BEFORE']))
			var arrBefore = {!! json_encode($data['image']['BEFORE'], JSON_HEX_TAG) !!};
		@else
			var arrBefore = [];
		@endif
		
		@if(isset($data['image']['AFTER']))
			var arrAfter = {!! json_encode($data['image']['AFTER'], JSON_HEX_TAG) !!};
		@else
			var arrAfter = [];
		@endif
		
		var CurrentBefore = 0;
		var CurrentAfter = 0;

		//document.getElementById("textHere").innerHTML = text[CurrentBefore];

		function PrevBefore(){
			if (arrBefore.length > 0){
				if(CurrentBefore == 0){
					CurrentBefore = arrBefore.length - 1;}
				else{
					CurrentBefore--;}
				document.getElementById("image_before").src = arrBefore[CurrentBefore];
				document.getElementById("index_before").innerHTML = CurrentBefore+1;
				// document.getElementById("textHere").innerHTML = text[CurrentBefore];
			}
		}

		function NextBefore(){
			if (arrBefore.length > 0){
				if(CurrentBefore == arrBefore.length - 1){
					CurrentBefore = 0}
				else{
					CurrentBefore++;}
				document.getElementById("image_before").src = arrBefore[CurrentBefore];
				document.getElementById("index_before").innerHTML = CurrentBefore+1;
			}
		}
		
		function PrevAfter(){
			if (arrAfter.length > 0){
				if(CurrentAfter == 0){
					CurrentAfter = arrAfter.length - 1;}
				else{
					CurrentAfter--;}
				document.getElementById("image_after").src = arrAfter[CurrentAfter];
				document.getElementById("index_after").innerHTML = CurrentAfter+1;
			}
		}

		function NextAfter(){
			if (arrAfter.length > 0){
				if(CurrentAfter == arrAfter.length - 1){
					CurrentAfter = 0}
				else{
					CurrentAfter++;}
					document.getElementById("image_after").src = arrAfter[CurrentAfter];
					document.getElementById("index_after").innerHTML = CurrentAfter+1;
				}
		}
		
		// Get the modal
		var modal = document.getElementById("myModal");

		// Get the image and insert it inside the modal - use its "alt" text as a caption
		var imgBefore = document.getElementById("image_before");
		var imgAfter = document.getElementById("image_after");
		var modalImg = document.getElementById("img01");
		var captionText = document.getElementById("caption");
		imgBefore.onclick = function(){
		  modal.style.display = "block";
		  modalImg.src = this.src;
		  captionText.innerHTML = this.alt;
		}
		
		imgAfter.onclick = function(){
		  modal.style.display = "block";
		  modalImg.src = this.src;
		  captionText.innerHTML = this.alt;
		}

		// Get the <span> element that closes the modal
		var span = document.getElementsByClassName("close")[0];

		// When the user clicks on <span> (x), close the modal
		span.onclick = function() { 
		  modal.style.display = "none";
		}
	</script>

	
</body>
</html>