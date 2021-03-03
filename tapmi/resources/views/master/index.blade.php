@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Master - Category Finding' )

@section( 'scripts' )
<script src="{{ url( 'assets/default-template/assets/custom/components/forms/widgets/bootstrap-daterangepicker.js' ) }}" type="text/javascript"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>

@section( 'content' )
<div id="table" style="padding:20px;">
    @include($master_type)
</div>
@endsection

<script type="text/javascript">
	
	var base_url = "{{ url( '' ) }}";
	var datatable = {
		init: function() {
			var e;
			e = $(".m-datatable").mDatatable({
				data: {
					saveState: {
						cookie: !1
					},
					
					autoColumns: false
				},
				search: {
					input: $( "#generalSearch" )
				},
				columns: [
					{
						field: "Tanggal",
						filterable: true,
						sortable: false,
						width: 0,
						visibility: false,
					},{
						field: "Krani Buah",
						filterable: true,
						sortable: false,
						width: 300
					}, {
						field: "Afdeling",
						width: 120,
						sortable: false,
					},{
						field: "Mandor Panen",
						width: 300,
						sortable: false,
					}, {
						field: "Jumlah BCC yang Divalidasi",
						width: 100,
						sortable: false,
					}, {
						field: "Keteranan",
						width: 100,
						sortable: false,
					}
				]

			})
		}
	};

	jQuery(document).ready(function() {
		datatable.init()
		MobileInspection.set_active_menu( '{{ $active_menu }}' );
		var year = (new Date).getFullYear();

		
	});
	
    $('.data-afd').hide();
    // $('#afd .'+$("#werks").val()).show();
   	// $("#afd").val($("#afd ."+$("#werks").val()+":first").val());
	$("#werks").change(function () {
        var val = $(this).val();
        $('.data-afd').hide();
        $('#afd .'+val).show();
        $("#afd").val($("#afd ."+$("#werks").val()+":first").val());
    });
 	$('#afd .'+$("#werks").val()).show();
	// $(document).ready(function () {
	// 	$('#generalSearch').datepicker().on('click', function(){
	// 			var selected = $(this).val();
	// 			var date_val = selected.toUpperCase();
	// 			// datatable.init();
	// 	});
		

	// });
	

	$("#tampilkan").click(function(){
		var search = document.getElementById('generalSearch').value;
		// $(".m-datatable").mDatatable().reload();
		// $(".m-datatable").mDatatable().search(search, "Tanggal");
		
		// $(".m-datatable").mDatatable().destroy();
		refreshData();
	});

	function refreshData(){
		var search = document.getElementById('generalSearch').value;
		var werks = document.getElementById('werks').value;
		var afd = document.getElementById('afd').value;
		var datatable = {
							init: function() {
								var e;
								e = $(".m-datatable").mDatatable({
									data: {
										saveState: {
											cookie: !1
										},
										
										autoColumns: false
									},
									search: {
										input: $( "#generalSearch" )
									},


									columns: [
										{
											field: "Tanggal",
											filterable: true,
											sortable: false,
											width: 0,
											visibility: false,
										},{
											field: "Krani Buah",
											filterable: true,
											sortable: false,
											width: 300
										}, {
											field: "Afdeling",
											width: 120,
											sortable: false,
										},{
											field: "Mandor Panen",
											width: 300,
											sortable: false,
										}, {
											field: "Jumlah BCC yang Divalidasi",
											width: 100,
											sortable: false,
										}, {
											field: "Keterangan",
											width: 100,
											sortable: false,
										}
									]

								})
							}
						};
		// event.preventDefault();
		const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
		$.ajax({
			url:'/getNewdata2',
			type:'get',
			data:{
				CSRF_TOKEN,
				'tanggal' : search,
				'werks' : werks,
				'afd' : afd
			},
			success:function(data){
				$("div#table").html(data);
				datatable.init();
				$("#generalSearch").val(search);
				$("#werks").val(werks);
				$("#afd").val(afd);
			}
		})
	}
	
</script>
<script>
	$(document).on('click','#cekaslap_index',function(){
		$('#cekaslap_index>span>i').addClass('fa-spin');
		$('#cekaslap_index').addClass('disabled');
		$('#cekaslap_index>span>span').html('Proses pengecekan');
		cekaslap_index();
	});
	function cekaslap_index(){
		const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
		var search = document.getElementById('generalSearch').value;
		$.ajax({
			url:"{{ URL::to('/validasi/cek_aslap/') }}",
			type:'get',
			data:{
				CSRF_TOKEN,
				'tanggal' : search
			},
			success:function(data){
				// console.log(data);
				$("#tampilkan").trigger('click');
				toastr.options = {
					"closeButton": false,
					"debug": false,
					"newestOnTop": false,
					"progressBar": false,
					"positionClass": "toast-top-right",
					"preventDuplicates": false,
					"onclick": null,
					"showDuration": "300",
					"hideDuration": "1000",
					"timeOut": "5000",
					"extendedTimeOut": "1000",
					"showEasing": "swing",
					"hideEasing": "linear",
					"showMethod": "fadeIn",
					"hideMethod": "fadeOut"
				};
				toastr.success( 'Pengecekan validasi Aslap selesai' , "Sukses");
			}
		})
	}
</script>
@endsection