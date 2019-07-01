@extends( 'layouts.default.page-normal-main' )
@section( 'title', 'User Authorization' )

@section( 'subheader' )
	<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
		<li class="m-nav__item">
			<a href="{{ url( '/modules' ) }}" class="m-nav__link">
				<span class="m-nav__link-text">
					Modules
				</span>
			</a>
		</li>
		<li class="m-nav__separator">-</li>
		<li class="m-nav__item">
			<a href="{{ url( '/modules/user-authorization' ) }}" class="m-nav__link">
				<span class="m-nav__link-text">
					User Authorization
				</span>
			</a>
		</li>
		<li class="m-nav__separator">-</li>
		<li class="m-nav__item">
			<a href="" class="m-nav__link">
				<span class="m-nav__link-text">
					{{ $modules->data->MODULE_CODE.' | '.$modules->data->MODULE_NAME }}
				</span>
			</a>
		</li>
	</ul>
@endsection

@section( 'content' )
	<a href="{{ url( '/modules' ) }}" class="btn btn-primary">Kembali</a> <br /><br /><br />
	@foreach ( $parameter->data as $z )
		<div class="row">
			<label class="col-lg-3 col-sm-3">{{ $z->DESC }}</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				@if ( isset( $user_authorization[$modules->data->MODULE_CODE][$z->PARAMETER_NAME] ) )
					@if ( $user_authorization[$modules->data->MODULE_CODE][$z->PARAMETER_NAME]['STATUS'] == 1 )
						<input data-switch="true" onchange="return update( '{{ $modules->data->MODULE_CODE }}', '{{ $z->PARAMETER_NAME }}' )" type="checkbox" checked="checked">
					@else
						<input data-switch="true" onchange="return update( '{{ $modules->data->MODULE_CODE }}', '{{ $z->PARAMETER_NAME }}' )" type="checkbox" >
					@endif
				@else
					<input data-switch="true" onchange="return update( '{{ $modules->data->MODULE_CODE }}', '{{ $z->PARAMETER_NAME }}' )" type="checkbox" >
				@endif
				
			</div>
		</div>
	@endforeach
	
@endsection

@section( 'scripts' )

	<script type="text/javascript">
		
		function update( MODULE_CODE, PARAMETER_NAME ) {
			$.ajax( {
				url: '{{ url( "modules/user-authorization" ) }}',
				type: 'POST',
				headers: {
					'X-CSRF-TOKEN': $( 'meta[name="csrf-token"]' ).attr( 'content' )
				},
				data: {
					MODULE_CODE: MODULE_CODE,
					PARAMETER_NAME: PARAMETER_NAME
				},
				success: function( response ) {
					if( response.status == true ) {
						console.log( 'Success' );
					}
					else {
						console.log( 'Error Response' );
					}
				},
				error: function() {
					console.log( 'Error' );
				}
			} );
		}
	</script>
@endsection