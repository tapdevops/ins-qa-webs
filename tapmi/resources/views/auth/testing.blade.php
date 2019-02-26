@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'Testing' )
@section( 'content' )
	<div class="row">
		<div class="col-sm-12">
			<div class="page-title-box">
				<h4 class="page-title">Testing Page</h4>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<table class="table">
				<tr>
					<th>Werks</th>
					<th>Werks AFD Code</th>
					<th>Ins.Time DW</th>
					<th>Start Valid</th>
				</tr>
				@foreach ( $result as $data )
					<tr>
						<td>{{ $data->WERKS }}</td>
						<td>{{ $data->WERKS_AFD_CODE }}</td>
						<td>{{ date( 'Y-m-d H:i:s', strtotime( $data->INSERT_TIME_DW.'+8hour' ) ) }}</td>
						<td>{{ date( 'Y-m-d H:i:s', strtotime( $data->INSERT_TIME_DW ) ) }}</td>
						<td>{{ date( 'Y-m-d H:i:s', strtotime( $data->START_VALID.'+8hour' ) ) }}</td>
						<td>{{ date( 'Y-m-d H:i:s', strtotime( $data->START_VALID ) ) }}</td>
					</tr>
				@endforeach
			</table>
		</div>
	</div>
@endsection