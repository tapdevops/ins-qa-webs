@extends( 'layouts.default.page-login-main' )
@section( 'title', 'Login' )
@section( 'content' )
	<div class="m-grid m-grid--hor m-grid--root m-page">
		<div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor m-login m-login--signin m-login--2 m-login-2--skin-2" id="m_login" style="background-image: url({{ url( 'assets/default-template/assets/app/media/img//bg/bg-3.jpg') }};">
			<div class="m-grid__item m-grid__item--fluid	m-login__wrapper">
				<div class="m-login__container">
					<!--div class="m-login__logo">
						<a href="#">
							<img src="{{ url( 'assets/default-template/assets/app/media/img//logos/logo-1.png' ) }}">
						</a>
					</div-->
					<div class="m-login__signin">
						<div class="m-login__head">
							<h3 class="m-login__title">
								Mobile Inspection
							</h3>
						</div>
						<form class="m-login__form m-form" action="{{ url( '/login' ) }}" method="post" id="form">
							<input type="hidden" name="_token" value="{{ csrf_token() }}">
							<div class="form-group m-form__group">
								<input class="form-control m-input" type="text" placeholder="Username" name="USERNAME" autocomplete="off">
							</div>
							<div class="form-group m-form__group">
								<input class="form-control m-input m-login__form-input--last" type="password" placeholder="Password" name="PASSWORD">
							</div>
							<!--div class="row m-login__form-sub">
								<div class="col m--align-left m-login__form-left">
									<label class="m-checkbox  m-checkbox--focus">
										<input type="checkbox" name="remember">
										Remember me
										<span></span>
									</label>
								</div>
							</div-->
							<div class="m-login__form-action">
								<button id="m_login_signin_submit" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air m-login__btn m-login__btn--primary">
									Sign In
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section( 'script' )
	<script type="text/javascript">
		$( document ).ready( function() {
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

			var form = $( "#form" );
			form.submit( function( ev ) {
				$( '#form' ).waitMe( {
					effect : 'win8',
					text : 'Memproses...',
					bg : '#ffffff',
					color : '#3d3d3d'
				} );
				$("#m_login_signin_submit").hide();
				$.ajax ({
					url: form.attr( 'action' ),
					type: form.attr( 'method' ),
					data: form.serialize(),
					success: function( result ) {
						if ( result.status == true ) {
							toastr.success( result.message , "Info");
							window.setTimeout( function() {
								form.waitMe( 'hide' );
								window.location.replace( '{{ url( "" ) }}' );
							}, 500 );
						}
						else {
							toastr.warning( result.message , "Info");
							window.setTimeout( function() {
								form.waitMe( 'hide' );
								$("#m_login_signin_submit").show();
							}, 500 );
						}
					},
					error: function() {
						toastr.error( result.message , "Info");
						window.setTimeout( function() {
							form.waitMe( 'hide' );
							$("#m_login_signin_submit").show();
						}, 500 );
					}
				});

				ev.preventDefault();
			} );
		} );
	</script>
@endsection