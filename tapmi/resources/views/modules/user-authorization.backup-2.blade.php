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
		<li class="m-nav__separator">
			-
		</li>
		<li class="m-nav__item">
			<a href="" class="m-nav__link">
				<span class="m-nav__link-text">
					User Authorization
				</span>
			</a>
		</li>
	</ul>
@endsection

@section( 'content' )
	<table>
		<tr>
			<td>Module Code</td>
			<td>Module Name</td>
			@foreach ( $parameter->data as $q )
				<td><center>{{ $q->DESC }}</center></td>
			@endforeach
		</tr>
		@foreach ( $modules->data as $q )
			<tr>
				<td style="font-family: 'Courier New';" width="150px;">{{ $q->MODULE_CODE }}</td>
				<td width="240px;">{{ $q->MODULE_NAME }}</td>
				@foreach ( $parameter->data as $z )
					<td width="240px;">
						@if ( isset( $user_authorization[$q->MODULE_CODE][$z->PARAMETER_NAME] ) )
							@if ( $user_authorization[$q->MODULE_CODE][$z->PARAMETER_NAME]['STATUS'] == 1 )
								<center><input id="" onchange="return update( '{{ $q->MODULE_CODE }}', '{{ $z->PARAMETER_NAME }}' )" type="checkbox" checked></center>
							@else
								<center><input id="" onchange="return update( '{{ $q->MODULE_CODE }}', '{{ $z->PARAMETER_NAME }}' )" type="checkbox"></center>
							@endif
						@else
							<center><input onchange="return update( '{{ $q->MODULE_CODE }}', '{{ $z->PARAMETER_NAME }}' )" type="checkbox"></center>
						@endif
					</td>
				@endforeach
			</tr>
		@endforeach
	</table>
@endsection

@section( 'scripts' )
	<script type="text/javascript">
		// return update( '{{ $q->MODULE_CODE }}', '{{ $z->DESC }}' )
		function update( MODULE_CODE, PARAMETER_NAME ) {
			//alert( MODULE_CODE + '/' + PARAMETER_NAME )
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

					$.get( '{{ url( "modules/setup-menu" ) }}/' + PARAMETER_NAME, function( jsondata ) {
						if ( jsondata.status == false ) {
							alert( "error" );
						}
					}, "JSON" )
					.fail( function() {
						alert( "error" );
					} );

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