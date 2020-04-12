@extends( 'layouts.default.page-normal-main' )
@section( 'style' )
<link rel="stylesheet" href="http://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
@endsection    


@section( 'title', 'Validasi BCC oleh Kepala Kebun' )

@section( 'subheader' )
@endsection

@section( 'content' )
<div class="row">
	<div class="col-md-8">
		<div class="row">
			<div class="col-md-4">
				{{-- <div class="m-input-icon m-input-icon--left">
					<input type="date" class="form-control m-input m-input--solid" max="{{ $dmin }}" placeholder="Select date..." id="generalSearch">
				</div> --}}
				<div class="input-daterange input-group" id="m_datepicker_5">
					<label for="TGL_PANEN">Tanggal &nbsp; &nbsp; </label>
					<input type="text" class="form-control m-input" id="tgl_panel" name="TGL_PANEN" autocomplete="off" readonly="readonly" />
					<div class="input-group-append">
						<span class="input-group-text">
							<i class="la la-calendar"></i>
						</span>
					</div>
				</div>
			</div>
			<div class="col-md-4">
			</div>
			<div class="col-md-4"></div>
		</div>
	</div>
	
</div>
 <br><br>
        <table class="table table-condensed" width="100%" style="margin-top:20px;" id="product-table">
            <thead>
                <tr>
                    <th>Krani Buah</th>
                    <th>Afdeling</th>
                    <th>Mandor Panen</th>
                    <th>Jumlah Divalidasi</th>
                    <th>Target Validasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
          </table>

@endsection
@section( 'scripts' )  
<script src="http://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>

<script>
// $(function() {
//     $('#product-table').DataTable({
//         processing: true,
//         serverSide: true,
//         ajax: '{!! route('header.data') !!}',
//         columns: [
//             { data: 'id', name: 'id' },
//             { data: 'kerani', name: 'kerani' },
//             { data: 'id_afd', name: 'id_afd' },
//             { data: 'mandor', name: 'mandor' },
//             { data: 'nik_mandor', name: 'nik_mandor' },
//             { data: '', name: '' },
//         ]
//     });
// });


$(document).ready(function() {
    var t = $('#product-table').DataTable({
        // "columnDefs": [ {
        //     "searchable": false,
        //     "orderable": false,
        //     "targets": 0
        // } ],
        // "order": [[ 1, 'asc' ]],
        processing: true,
        serverSide: true,
        ajax: '{!! route('header.data') !!}',
        columns: [
            { data: 'nama_krani_buah', name: 'nama_krani_buah'},
            { data: 'id_afd', name: 'id_afd' },
            { data: 'nama_mandor', name: 'nama_mandor' },
            { data: 'jumlah_ebcc_validated', name: 'jumlah_ebcc_validated' },
            { data: 'target_validasi', name: 'target_validasi' },
            { data: 'id_validasi',
                "render": function(data, type, row) 
                {
                    var str = data; 
                    var res = str.replace(/\//g, '.');
                    var content = '<a href="{{ url("validasi/create") }}/'+res+'"><button class="btn btn-sm btn-primary " data-toggle="tooltip" data-placement="top" title="Edit" return false;">Validasi</button></a>';
                  
                    return content;
                }
            }
        ]
    } );
 
    // t.on( 'order.dt search.dt', function () {
    //     t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
    //         cell.innerHTML = i+1;
    //     } );
    // } ).draw();
    
    
    $("#m_datepicker_5").datepicker({
        todayHighlight: !0,
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        },
        endDate: "-1d",
        format: 'yyyy-M-dd'
    });


    MobileInspection.set_active_menu( '{{ $active_menu }}' );
});


</script>

@endsection